<?php
// noyau_backend/api/v1/admin.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../noyau_backend/configuration/db.php';
require_once __DIR__ . '/../noyau_backend/configuration/mongo.php';
require_once __DIR__ . '/../noyau_backend/controllers/AdminController.php';
require_once __DIR__ . '/../noyau_backend/controllers/AvisController.php';

$adminCtrl = new AdminController($pdo);
$reviewCtrl = isset($mongoDb) ? new AvisController($mongoDb) : null;
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if ($action === 'dashboard_stats') {
            $res = $adminCtrl->getDashboardStats();
            http_response_code(200);
            echo json_encode($res['stats'] ?? []);
            exit;
        }
        else if ($action === 'list_incidents') {
            $res = $adminCtrl->listIncidents();
            http_response_code(200);
            echo json_encode($res['incidents'] ?? []);
            exit;
        }
        else if ($action === 'pending_reviews' && $reviewCtrl) {
            $res = $reviewCtrl->listPending();
            http_response_code(200);
            echo json_encode($res['avis'] ?? []);
            exit;
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if ($action === 'report_incident') {
            $res = $adminCtrl->reportIncident($data);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if ($action === 'moderate_review' && $reviewCtrl) {
            $res = $reviewCtrl->moderate($data);
        }
        else if ($action === 'resolve_incident') {
            $res = $adminCtrl->resolveIncident($data->incident_id ?? 0);
        }
        break;
}

if (isset($res)) {
    http_response_code($res['status']);
    echo json_encode($res);
}
else {
    http_response_code(400);
    echo json_encode(["message" => "Requête invalide."]);
}
