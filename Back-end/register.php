<?php
// register.php — inscription très simple (démo)
require_once __DIR__.'/config.php';

$pseudo = $_POST['pseudo'] ?? '';
$email = $_POST['email'] ?? '';
$pass = $_POST['password'] ?? '';

if(strlen($pass) < 8){
  http_response_code(400);
  echo json_encode(['error'=>'Mot de passe trop court (8+).']);
  exit;
}

$ex = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = :email");
$ex->execute([':email'=>$email]);
if($ex->fetch()){
  http_response_code(409);
  echo json_encode(['error'=>'Email déjà utilisé.']);
  exit;
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$ins = $pdo->prepare("INSERT INTO utilisateurs (pseudo, email, motdepasse, credits, note) VALUES (:p,:e,:m,20,5)");
$ins->execute([':p'=>$pseudo, ':e'=>$email, ':m'=>$hash]);

echo json_encode(['ok'=>true, 'message'=>'Compte créé. 20 crédits ajoutés.']);
