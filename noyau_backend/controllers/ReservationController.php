<?php
// noyau_backend/controllers/ReservationController.php
require_once __DIR__ . '/../models/Reservation.php';

class ReservationController
{
    private $db;
    private $reservation;

    public function __construct($db)
    {
        $this->db = $db;
        $this->reservation = new Reservation($db);
    }

    public function create($data)
    {
        if (empty($data->passager_id)) {
            return ["status" => 401, "message" => "Utilisateur non connecté. Veuillez vous connecter pour réserver."];
        }

        if (empty($data->trajet_id) || empty($data->prix)) {
            return ["status" => 400, "message" => "Données incomplètes."];
        }

        $this->reservation->trajet_id = $data->trajet_id;
        $this->reservation->passager_id = $data->passager_id;

        // Verify if the user already booked this trip
        $stmtDouble = $this->db->prepare("SELECT id FROM reservations WHERE trajet_id = ? AND passager_id = ?");
        $stmtDouble->execute([$data->trajet_id, $data->passager_id]);
        if ($stmtDouble->fetch()) {
            return ["status" => 409, "message" => "Vous avez déjà réservé ce trajet."];
        }

        // Verify available seats before attempting to create
        $stmtSeats = $this->db->prepare("SELECT places_max FROM trajets WHERE id = ?");
        $stmtSeats->execute([$data->trajet_id]);
        $trip = $stmtSeats->fetch(PDO::FETCH_ASSOC);

        if (!$trip || $trip['places_max'] <= 0) {
            return ["status" => 409, "message" => "Plus de places disponibles pour ce trajet."];
        }

        // Verify credits before attempting to create
        $stmtCred = $this->db->prepare("SELECT credits FROM utilisateurs WHERE id = ?");
        $stmtCred->execute([$data->passager_id]);
        $user = $stmtCred->fetch(PDO::FETCH_ASSOC);

        if (!$user || $user['credits'] < $data->prix) {
            return ["status" => 402, "message" => "Crédits insuffisants pour réserver ce trajet."];
        }

        if ($this->reservation->create($data->prix)) {
            return ["status" => 201, "message" => "Réservation effectuée."];
        }
        else {
            return ["status" => 500, "message" => "Erreur lors de la réservation."];
        }
    }

    public function listByPassenger($passager_id)
    {
        $stmt = $this->reservation->getByPassenger($passager_id);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return ["status" => 200, "reservations" => $reservations];
    }
}

