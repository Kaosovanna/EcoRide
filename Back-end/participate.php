<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../utils.php';
session_start();

$data = json_input();
$trip_id = (int)($data['trip_id'] ?? 0);
$places = (int)($data['places'] ?? 1);
$user_id = $_SESSION['user_id'] ?? null;

if(!$user_id){ respond(['ok'=>false, 'message'=>'Veuillez vous connecter.']); }

$pdo->beginTransaction();
try{
  $t = $pdo->prepare("SELECT * FROM trips WHERE id=:id FOR UPDATE");
  $t->execute([':id'=>$trip_id]);
  $trip = $t->fetch();
  if(!$trip){ throw new Exception('Trajet introuvable'); }
  if($trip['places_restantes'] < $places){ throw new Exception('Plus assez de places'); }

  $u = $pdo->prepare("SELECT credits FROM users WHERE id=:id FOR UPDATE");
  $u->execute([':id'=>$user_id]);
  $user = $u->fetch();
  $cout = (int)$places; // 1 place = 1 crédit (exemple simple)
  if($user['credits'] < $cout){ throw new Exception('Crédits insuffisants'); }

  $pdo->prepare("INSERT INTO bookings(trip_id, user_id, places, status, created_at) VALUES(:t,:u,:p,'confirme',NOW())")
      ->execute([':t'=>$trip_id, ':u'=>$user_id, ':p'=>$places]);

  $pdo->prepare("UPDATE users SET credits=credits-:c WHERE id=:id")->execute([':c'=>$cout, ':id'=>$user_id]);
  $pdo->prepare("UPDATE trips SET places_restantes=places_restantes-:p WHERE id=:id")->execute([':p'=>$places, ':id'=>$trip_id]);

  $pdo->commit();
  respond(['ok'=>true, 'message'=>'Participation confirmée, crédits mis à jour.']);
}catch(Exception $e){
  $pdo->rollBack();
  respond(['ok'=>false, 'message'=>$e->getMessage()]);
}
?>
