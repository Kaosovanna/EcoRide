<?php
// noyau_backend/api/v1/reservations.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../noyau_backend/configuration/db.php';
require_once __DIR__ . '/../noyau_backend/controllers/ReservationController.php';

$controller = new ReservationController($pdo);
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if ($action === 'create') {
            $res = $controller->create($data);
        }
        else {
            $res = ["status" => 400, "message" => "Action inconnue."];
        }
        break;

    case 'GET':
        if ($action === 'list_by_passenger') {
            $res = $controller->listByPassenger($_GET['passager_id'] ?? 0);
            http_response_code($res['status']);
            echo json_encode($res['reservations'] ?? []);
            exit;
        }
        else {
            $res = ["status" => 400, "message" => "Action inconnue."];
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if ($action === 'update_status') {
            // Logic would go to reservation model updateStatus
            $reservation = new Reservation($pdo);
            if ($reservation->updateStatus($data->reservation_id, $data->statut)) {
                $res = ["status" => 200, "message" => "Statut mis à jour."];
            }
            else {
                $res = ["status" => 500, "message" => "Erreur mise à jour."];
            }
        }
        else {
            $res = ["status" => 400, "message" => "Action inconnue."];
        }
        break;

    default:
        $res = ["status" => 405, "message" => "Méthode non autorisée."];
        break;
}

http_response_code($res['status']);
echo json_encode($res);
