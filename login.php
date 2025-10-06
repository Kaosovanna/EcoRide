<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils.php';
session_start();

$data = json_input();
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

$stmt = $pdo->prepare("SELECT id, pseudo, password_hash FROM users WHERE email=:e");
$stmt->execute([':e'=>$email]);
$u = $stmt->fetch();
if(!$u || !password_verify($password, $u['password_hash'])){
  respond(['ok'=>false, 'message'=>'Identifiants invalides.']);
}
$_SESSION['user_id'] = $u['id'];
respond(['ok'=>true, 'message'=>'Connecté en tant que '.$u['pseudo']]);
?>
