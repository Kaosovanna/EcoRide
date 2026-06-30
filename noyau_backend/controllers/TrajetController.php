<?php
// noyau_backend/controllers/TrajetController.php
require_once __DIR__ . '/../models/Trajet.php';

class TrajetController
{
    private $db;
    private $trajet;

    public function __construct($db)
    {
        $this->db = $db;
        $this->trajet = new Trajet($db);
    }

    public function create($data)
    {
        if (empty($data->conducteur_id) || empty($data->vehicule_id) || empty($data->ville_depart) ||
        empty($data->ville_arrivee) || empty($data->date_depart) || empty($data->prix)) {
            return ["status" => 400, "message" => "Données incomplètes."];
        }

        $this->trajet->conducteur_id = $data->conducteur_id;
        $this->trajet->vehicule_id = $data->vehicule_id;
        $this->trajet->ville_depart = $data->ville_depart;
        $this->trajet->ville_arrivee = $data->ville_arrivee;
        $this->trajet->date_depart = $data->date_depart;
        $this->trajet->heure_depart = $data->heure_depart;
        $this->trajet->prix = $data->prix;
        $this->trajet->duree_max = !empty($data->duree_max) ? $data->duree_max : 120;
        $this->trajet->places_max = $data->places_max;
        $this->trajet->is_bon_plan = !empty($data->is_bon_plan) ? 1 : 0;
        $this->trajet->reduction_credit = !empty($data->reduction_credit) ? intval($data->reduction_credit) : 0;

        if ($this->trajet->create()) {
            return ["status" => 201, "message" => "Trajet créé (2 crédits prélevés)."];
        }
        else {
            return ["status" => 500, "message" => "Erreur lors de la création ou crédits insuffisants."];
        }
    }

    public function search($filters)
    {
        $stmt = $this->trajet->search($filters);
        $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ["status" => 200, "trajets" => $trajets];
    }

    public function getByDriver($conducteur_id)
    {
        $stmt = $this->trajet->getByDriver($conducteur_id);
        $trajets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ["status" => 200, "trajets" => $trajets];
    }

    public function updateStatus($data)
    {
        if (empty($data->trajet_id) || empty($data->conducteur_id) || empty($data->statut)) {
            return ["status" => 400, "message" => "Données incomplètes."];
        }

        if ($this->trajet->updateStatus($data->trajet_id, $data->conducteur_id, $data->statut)) {
            return ["status" => 200, "message" => "Statut mis à jour."];
        }
        else {
            return ["status" => 500, "message" => "Erreur lors de la mise à jour."];
        }
    }
}
