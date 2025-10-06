<?php
// search.php — recherche de trajets par ville + date (simplifié)
require_once __DIR__.'/config.php';

$depart = $_GET['depart'] ?? '';
$arrivee = $_GET['arrivee'] ?? '';
$date = $_GET['date'] ?? '';

if(!$depart || !$arrivee || !$date){
  echo json_encode(['trajets'=>[]]);
  exit;
}

// trajets avec au moins une place
$sql = "SELECT t.*, u.pseudo, u.note, u.photo, v.energie 
        FROM trajets t
        JOIN utilisateurs u ON u.id = t.chauffeur_id
        JOIN vehicules v ON v.id = t.vehicule_id
        WHERE t.ville_depart LIKE :depart
          AND t.ville_arrivee LIKE :arrivee
          AND DATE(t.date_depart) = :date
          AND t.places_restantes > 0
        ORDER BY t.date_depart ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([
  ':depart' => "%$depart%",
  ':arrivee' => "%$arrivee%",
  ':date' => $date
]);
$rows = $stmt->fetchAll();

if(!$rows){
  // proposer la date du prochain trajet dispo
  $sql2 = "SELECT DATE(t.date_depart) as date_prochaine FROM trajets t 
           WHERE t.ville_depart LIKE :depart AND t.ville_arrivee LIKE :arrivee AND t.places_restantes > 0
           ORDER BY t.date_depart ASC LIMIT 1";
  $st2 = $pdo->prepare($sql2);
  $st2->execute([':depart'=>"%$depart%", ':arrivee'=>"%$arrivee%"]);
  $sugg = $st2->fetch();
  echo json_encode(['trajets'=>[], 'suggestion'=> $sugg ? ['date'=>$sugg['date_prochaine']] : null]);
  exit;
}

$trajets = array_map(function($r){
  return [
    'id' => (int)$r['id'],
    'ville_depart' => $r['ville_depart'],
    'ville_arrivee' => $r['ville_arrivee'],
    'date_depart' => substr($r['date_depart'],0,10),
    'heure_depart' => substr($r['date_depart'],11,5),
    'heure_arrivee' => substr($r['date_arrivee'],11,5),
    'places_restantes' => (int)$r['places_restantes'],
    'prix' => (int)$r['prix'],
    'ecologique' => strtolower($r['energie']) === 'électrique' || strtolower($r['energie']) === 'electrique',
    'chauffeur' => ['pseudo'=>$r['pseudo'], 'note'=> (float)$r['note'], 'photo'=>$r['photo']]
  ];
}, $rows);

echo json_encode(['trajets'=>$trajets]);
