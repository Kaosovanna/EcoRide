<?php
// noyau_backend/models/Trajet.php
class Trajet
{
    private $conn;
    private $table_name = "trajets";

    public $id;
    public $conducteur_id;
    public $vehicule_id;
    public $ville_depart;
    public $ville_arrivee;
    public $date_depart;
    public $heure_depart;
    public $prix;
    public $duree_max;
    public $places_max;
    public $statut; // 'planifie', 'demarre', 'termine', 'annule'
    public $is_bon_plan;
    public $reduction_credit;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        try {
            $this->conn->beginTransaction();

            // Check driver credits
            $checkCreditsQuery = "SELECT credits FROM utilisateurs WHERE id = ? FOR UPDATE";
            $stmtCred = $this->conn->prepare($checkCreditsQuery);
            $stmtCred->execute([$this->conducteur_id]);
            $userRow = $stmtCred->fetch(PDO::FETCH_ASSOC);

            if (!$userRow || $userRow['credits'] < 2) {
                // Not enough credits for commission
                $this->conn->rollBack();
                return false;
            }

            // Deduct 2 credits
            $deductQuery = "UPDATE utilisateurs SET credits = credits - 2 WHERE id = ?";
            $stmtDeduct = $this->conn->prepare($deductQuery);
            $stmtDeduct->execute([$this->conducteur_id]);

            // Insert trip
            $query = "INSERT INTO " . $this->table_name . " 
                      SET conducteur_id=:conducteur_id, vehicule_id=:vehicule_id, ville_depart=:ville_depart, 
                          ville_arrivee=:ville_arrivee, date_depart=:date_depart, heure_depart=:heure_depart, 
                          prix=:prix, duree_max=:duree_max, places_max=:places_max, statut='planifie',
                          is_bon_plan=:is_bon_plan, reduction_credit=:reduction_credit";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":conducteur_id", $this->conducteur_id);
            $stmt->bindParam(":vehicule_id", $this->vehicule_id);
            $stmt->bindValue(":ville_depart", htmlspecialchars(strip_tags($this->ville_depart)));
            $stmt->bindValue(":ville_arrivee", htmlspecialchars(strip_tags($this->ville_arrivee)));
            $stmt->bindParam(":date_depart", $this->date_depart);
            $stmt->bindParam(":heure_depart", $this->heure_depart);
            $stmt->bindParam(":prix", $this->prix);
            $stmt->bindParam(":duree_max", $this->duree_max);
            $stmt->bindParam(":places_max", $this->places_max);
            $stmt->bindParam(":is_bon_plan", $this->is_bon_plan);
            $stmt->bindParam(":reduction_credit", $this->reduction_credit);

            $stmt->execute();

            $this->conn->commit();
            return true;
        }
        catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return false;
        }
    }

    public function updateStatus($trajet_id, $conducteur_id, $new_status)
    {
        $query = "UPDATE " . $this->table_name . " SET statut = :statut WHERE id = :id AND conducteur_id = :conducteur_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':statut', $new_status);
        $stmt->bindParam(':id', $trajet_id);
        $stmt->bindParam(':conducteur_id', $conducteur_id);
        if ($stmt->execute() && $stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function search($filters)
    {
        // Build base query
        $query = "
            SELECT t.id, t.conducteur_id, t.ville_depart, t.ville_arrivee, t.date_depart, t.heure_depart, 
                   t.prix, t.duree_max, t.places_max, t.statut, t.is_bon_plan, t.reduction_credit,
                   u.pseudo as conducteur_pseudo, u.photo as conducteur_photo, v.modele as vehicule_modele, v.est_electrique
            FROM " . $this->table_name . " t
            JOIN utilisateurs u ON t.conducteur_id = u.id
            JOIN vehicules v ON t.vehicule_id = v.id
            WHERE t.statut = 'planifie'
        ";

        $params = [];
        if (!empty($filters['ville_depart'])) {
            $query .= " AND t.ville_depart LIKE :ville_depart";
            $params[':ville_depart'] = "%" . $filters['ville_depart'] . "%";
        }
        if (!empty($filters['ville_arrivee'])) {
            $query .= " AND t.ville_arrivee LIKE :ville_arrivee";
            $params[':ville_arrivee'] = "%" . $filters['ville_arrivee'] . "%";
        }
        if (!empty($filters['date_depart'])) {
            $query .= " AND t.date_depart = :date_depart";
            $params[':date_depart'] = $filters['date_depart'];
        }
        if (!empty($filters['prix_max'])) {
            $query .= " AND t.prix <= :prix_max";
            $params[':prix_max'] = $filters['prix_max'];
        }
        if (!empty($filters['duree_max'])) {
            $query .= " AND t.duree_max <= :duree_max";
            $params[':duree_max'] = $filters['duree_max'];
        }
        if (!empty($filters['est_electrique']) && $filters['est_electrique'] == 'true') {
            $query .= " AND v.est_electrique = 1";
        }

        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        return $stmt;
    }

    public function getByDriver($conducteur_id)
    {
        $query = "
            SELECT t.id, t.ville_depart, t.ville_arrivee, t.date_depart, t.heure_depart, 
                   t.prix, t.duree_max, t.places_max, t.statut, t.is_bon_plan, t.reduction_credit,
                   v.modele as vehicule_modele, v.immatriculation
            FROM " . $this->table_name . " t
            JOIN vehicules v ON t.vehicule_id = v.id
            WHERE t.conducteur_id = :conducteur_id
            ORDER BY t.id DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':conducteur_id', $conducteur_id);
        $stmt->execute();
        return $stmt;
    }
    public function delete($id, $conducteur_id)
    {
        // Only allow deleting planned trips
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND conducteur_id = :conducteur_id AND statut = 'planifie'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':conducteur_id', $conducteur_id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }
}
