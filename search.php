<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$depart = $_GET['depart'] ?? '';
$arrivee = $_GET['arrivee'] ?? '';
$date = $_GET['date'] ?? '';
$eco = isset($_GET['eco']) && $_GET['eco']=='1';
$prix_max = $_GET['prix_max'] ?? null;
$duree_max = $_GET['duree_max'] ?? null;
$note_min = $_GET['note_min'] ?? null;

$query = "SELECT t.*, u.pseudo AS chauffeur_pseudo, v.energie,
  (SELECT AVG(r.note) FROM reviews r WHERE r.driver_id = t.driver_id AND r.statut='valide') AS note_moyenne
  FROM trips t
  JOIN users u ON u.id = t.driver_id
  JOIN vehicles v ON v.id = t.vehicle_id
  WHERE t.places_restantes > 0";

$params = [];
if($depart){ $query .= " AND t.ville_depart LIKE :depart"; $params[':depart'] = "%$depart%"; }
if($arrivee){ $query .= " AND t.ville_arrivee LIKE :arrivee"; $params[':arrivee'] = "%$arrivee%"; }
if($date){ $query .= " AND DATE(t.date_depart) = :dd"; $params[':dd'] = $date; }
if($eco){ $query .= " AND v.energie = 'electrique'"; }
if($prix_max){ $query .= " AND t.prix <= :prix_max"; $params[':prix_max'] = (float)$prix_max; }
if($duree_max){ $query .= " AND TIMESTAMPDIFF(HOUR, t.date_depart, t.date_arrivee) <= :duree_max"; $params[':duree_max'] = (int)$duree_max; }
if($note_min){ $query .= " HAVING (note_moyenne IS NULL OR note_moyenne >= :note_min)"; $params[':note_min'] = (int)$note_min; }

$query .= " ORDER BY t.date_depart ASC LIMIT 50";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$trajets = $stmt->fetchAll();

// Suggestion de date si aucun trajet
$suggestion = null;
if(!$trajets && $date){
  $stmt2 = $pdo->prepare("SELECT DATE(t.date_depart) AS d FROM trips t
    WHERE t.ville_depart LIKE :depart AND t.ville_arrivee LIKE :arrivee
    AND t.places_restantes > 0 AND DATE(t.date_depart) >= :dd
    ORDER BY t.date_depart ASC LIMIT 1");
  $stmt2->execute([
    ':depart'=>"%$depart%", ':arrivee'=>"%$arrivee%", ':dd'=>$date
  ]);
  $row = $stmt2->fetch();
  if($row){ $suggestion = $row['d']; }
}

foreach($trajets as &$t){
  $t['eco'] = ($t['energie'] === 'electrique') ? 1 : 0;
}

echo json_encode(['ok'=>true, 'trajets'=>$trajets, 'suggestion'=>$suggestion]);
?>
