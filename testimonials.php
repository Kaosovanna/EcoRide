<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$stmt = $pdo->query("SELECT a.pseudo AS auteur, r.note, r.commentaire
  FROM reviews r JOIN users a ON a.id=r.author_id
  WHERE r.statut='valide' ORDER BY r.id DESC LIMIT 6");
$avis = $stmt->fetchAll();

echo json_encode(['ok'=>true, 'avis'=>$avis]);
?>
