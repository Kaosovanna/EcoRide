<?php
// noyau_backend/models/Reservation.php
class Reservation
{
    private $conn;
    private $table_name = "reservations";

    public $id;
    public $trajet_id;
    public $passager_id;
    public $statut;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($prix)
    {
        try {
            $this->conn->beginTransaction();

            $checkCreditsQuery = "SELECT credits FROM utilisateurs WHERE id = ? FOR UPDATE";
            $stmtCred = $this->conn->prepare($checkCreditsQuery);
            $stmtCred->execute([$this->passager_id]);
            $userRow = $stmtCred->fetch(PDO::FETCH_ASSOC);

            if (!$userRow || $userRow['credits'] < $prix) {
                $this->conn->rollBack();
                return false;
            }

            // Deduct credits from passenger
            $deductQuery = "UPDATE utilisateurs SET credits = credits - ? WHERE id = ?";
            $stmtDeduct = $this->conn->prepare($deductQuery);
            $stmtDeduct->execute([$prix, $this->passager_id]);

            // Add credits to driver
            $driverQuery = "SELECT conducteur_id FROM trajets WHERE id = ?";
            $stmtDriver = $this->conn->prepare($driverQuery);
            $stmtDriver->execute([$this->trajet_id]);
            $tripRow = $stmtDriver->fetch(PDO::FETCH_ASSOC);
            if ($tripRow) {
                $addQuery = "UPDATE utilisateurs SET credits = credits + ? WHERE id = ?";
                $stmtAdd = $this->conn->prepare($addQuery);
                $stmtAdd->execute([$prix, $tripRow['conducteur_id']]);
            }

            // Insert reservation
            $query = "INSERT INTO " . $this->table_name . " 
                      SET trajet_id=:trajet_id, passager_id=:passager_id, statut='en_attente'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":trajet_id", $this->trajet_id);
            $stmt->bindParam(":passager_id", $this->passager_id);
            $stmt->execute();

            // Decrement available seats (places_max)
            $updateSeatsQuery = "UPDATE trajets SET places_max = places_max - 1 WHERE id = ? AND places_max > 0";
            $stmtSeats = $this->conn->prepare($updateSeatsQuery);
            $stmtSeats->execute([$this->trajet_id]);

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

    public function updateStatus($id, $statut)
    {
        $query = "UPDATE " . $this->table_name . " SET statut = :statut WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':statut', $statut);
        $stmt->bindParam(':id', $id);
        return $stmt->execute() && $stmt->rowCount() > 0;
    }

    public function getByPassenger($passager_id)
    {
        $query = "
            SELECT r.id as reservation_id, r.statut as reservation_statut, 
                   t.id as trajet_id, t.ville_depart, t.ville_arrivee, t.date_depart, t.heure_depart, t.prix, t.statut as trajet_statut,
                   u.pseudo as conducteur_pseudo
            FROM " . $this->table_name . " r
            JOIN trajets t ON r.trajet_id = t.id
            JOIN utilisateurs u ON t.conducteur_id = u.id
            WHERE r.passager_id = :passager_id
            ORDER BY r.id DESC
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':passager_id', $passager_id);
        $stmt->execute();
        return $stmt;
    }
}

