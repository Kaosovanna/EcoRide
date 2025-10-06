<?php
// journeys.php — détail d'un trajet
require_once __DIR__.'/config.php';

$id = (int)($_GET['id'] ?? 0);
if(!$id){ echo json_encode(['error'=>'id manquant']); exit; }

$sql = "SELECT t.*, u.pseudo, u.note, v.marque, v.modele, v.energie
        FROM trajets t
        JOIN utilisateurs u ON u.id = t.chauffeur_id
        JOIN vehicules v ON v.id = t.vehicule_id
        WHERE t.id = :id";
$st = $pdo->prepare($sql);
$st->execute([':id'=>$id]);
$r = $st->fetch();
if(!$r){ echo json_encode(['error'=>'introuvable']); exit; }

$avis = $pdo->prepare("SELECT auteur, note, commentaire FROM avis WHERE chauffeur_id = :cid AND publie = 1 ORDER BY id DESC");
$avis->execute([':cid'=>$r['chauffeur_id']]);
$avisRows = $avis->fetchAll();

$pref = $pdo->prepare("SELECT intitule FROM preferences WHERE utilisateur_id = :uid");
$pref->execute([':uid'=>$r['chauffeur_id']]);
$preferences = array_map(fn($x)=>$x['intitule'], $pref->fetchAll());

echo json_encode([
  'ville_depart'=>$r['ville_depart'],
  'ville_arrivee'=>$r['ville_arrivee'],
  'date_depart'=>substr($r['date_depart'],0,10),
  'heure_depart'=>substr($r['date_depart'],11,5),
  'heure_arrivee'=>substr($r['date_arrivee'],11,5),
  'vehicule'=>['marque'=>$r['marque'], 'modele'=>$r['modele'], 'energie'=>$r['energie']],
  'preferences'=>$preferences,
  'avis'=> array_map(fn($a)=>['auteur'=>$a['auteur'], 'note'=>(float)$a['note'], 'commentaire'=>$a['commentaire']], $avisRows)
]);
