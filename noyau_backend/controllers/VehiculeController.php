<?php
// noyau_backend/controllers/VehiculeController.php
require_once __DIR__ . '/../models/Vehicule.php';

class VehiculeController
{
    private $db;
    private $vehicule;

    public function __construct($db)
    {
        $this->db = $db;
        $this->vehicule = new Vehicule($db);
    }

    public function create($data)
    {
        if (empty($data->utilisateur_id) || empty($data->immatriculation) || empty($data->modele) || empty($data->couleur)) {
            return ["status" => 400, "message" => "Données incomplètes."];
        }

        $this->vehicule->utilisateur_id = $data->utilisateur_id;
        $this->vehicule->immatriculation = $data->immatriculation;
        $this->vehicule->modele = $data->modele;
        $this->vehicule->couleur = $data->couleur;
        $this->vehicule->est_electrique = $data->est_electrique ?? 0;
        $this->vehicule->photos = isset($data->photos) ? json_encode($data->photos) : '[]';

        if ($this->vehicule->create()) {
            return ["status" => 201, "message" => "Véhicule ajouté."];
        }
        else {
            return ["status" => 500, "message" => "Erreur lors de l'ajout."];
        }
    }

    public function listByUser($utilisateur_id)
    {
        $stmt = $this->vehicule->getByUser($utilisateur_id);
        $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ["status" => 200, "vehicules" => $vehicules];
    }

    public function delete($vehicule_id, $utilisateur_id)
    {
        if ($this->vehicule->delete($vehicule_id, $utilisateur_id)) {
            return ["status" => 200, "message" => "Véhicule supprimé."];
        }
        else {
            return ["status" => 500, "message" => "Erreur lors de la suppression."];
        }
    }
}
