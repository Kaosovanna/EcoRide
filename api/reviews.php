<?php
// noyau_backend/api/v1/reviews.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../noyau_backend/configuration/mongo.php';
require_once __DIR__ . '/../noyau_backend/controllers/AvisController.php';

$controller = isset($mongoDb) ? new AvisController($mongoDb) : null;
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if (!$controller) {
    http_response_code(503);
    echo json_encode(["message" => "MongoDB non disponible."]);
    exit;
}

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
        if ($action === 'approved') {
            $cible_id = $_GET['utilisateur_id'] ?? 0;
            $review = new Avis($mongoDb);
            $reviews = $review->getApprovedByReviewee($cible_id);
            http_response_code(200);
            echo json_encode($reviews);
            exit;
        }
        else if ($action === 'pending') {
            $res = $controller->listPending();
            http_response_code(200);
            echo json_encode($res['avis'] ?? []);
            exit;
        }
        else {
            $res = ["status" => 400, "message" => "Action inconnue."];
        }
        break;


    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if ($action === 'moderate') {
            $res = $controller->moderate($data);
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
