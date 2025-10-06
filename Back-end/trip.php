<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT t.*, u.pseudo AS chauffeur_pseudo, v.marque, v.modele, v.energie,
  (SELECT GROUP_CONCAT(p.pref SEPARATOR ', ') FROM driver_prefs p WHERE p.user_id=t.driver_id) AS preferences,
  (SELECT AVG(r.note) FROM reviews r WHERE r.driver_id = t.driver_id AND r.statut='valide') AS note_moyenne
  FROM trips t
  JOIN users u ON u.id=t.driver_id
  JOIN vehicles v ON v.id=t.vehicle_id
  WHERE t.id=:id");
$stmt->execute([':id'=>$id]);
$trajet = $stmt->fetch();
if(!$trajet){ echo json_encode(['ok'=>false]); exit; }

$avis = $pdo->prepare("SELECT r.*, a.pseudo AS auteur FROM reviews r JOIN users a ON a.id=r.author_id WHERE r.driver_id=:d AND r.statut='valide' ORDER BY r.id DESC LIMIT 10");
$avis->execute([':d'=>$trajet['driver_id']]);
$avisList = $avis->fetchAll();

$trajet['eco'] = ($trajet['energie']==='electrique') ? 1 : 0;

echo json_encode(['ok'=>true, 'trajet'=>$trajet, 'avis'=>$avisList]);
?>
