<?php
// noyau_backend/api/v1/users.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../noyau_backend/configuration/db.php';
require_once __DIR__ . '/../noyau_backend/controllers/UtilisateurController.php';

$controller = new UtilisateurController($pdo);
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if ($action === 'register') {
            $res = $controller->register($data);
        }
        else if ($action === 'login') {
            $res = $controller->login($data);
        }
        else {
            $res = ["status" => 400, "message" => "Action inconnue."];
        }
        break;

    case 'GET':
        if ($action === 'list') {
            $res = $controller->listAll();
            // Traditional format for admin.html compatibility
            http_response_code($res['status']);
            echo json_encode($res['utilisateurs'] ?? []);
            exit;
        }
        else if ($action === 'get_profile') {
            $res = $controller->getProfile($_GET['utilisateur_id'] ?? 0);
            if ($res['status'] === 200) {
                http_response_code(200);
                echo json_encode($res['profile']);
                exit;
            }
        }
        else {
            $res = ["status" => 400, "message" => "Action inconnue."];
        }
        break;


    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if ($action === 'update_role') {
            $res = $controller->updateRole($data);
        }
        else if ($action === 'update_profile') {
            $res = $controller->updateProfile($data);
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
