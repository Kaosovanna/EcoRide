<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils.php';

$data = json_input();
$pseudo = trim($data['pseudo'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if(!$pseudo || !$email || strlen($password) < 8){
  respond(['ok'=>false, 'message'=>'Veuillez remplir tous les champs (mot de passe 8+).']);
}

$check = $pdo->prepare("SELECT id FROM users WHERE email=:email");
$check->execute([':email'=>$email]);
if($check->fetch()){ respond(['ok'=>false, 'message'=>'Email déjà utilisé.']); }

$stmt = $pdo->prepare("INSERT INTO users(pseudo, email, password_hash, credits, role, created_at) VALUES(:p,:e,:h,20,'utilisateur',NOW())");
$stmt->execute([':p'=>$pseudo, ':e'=>$email, ':h'=>password_hash($password, PASSWORD_DEFAULT)]);

respond(['ok'=>true, 'message'=>'Compte créé avec 20 crédits.']);
?>
