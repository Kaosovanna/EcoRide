<?php
// testimonials.php — renvoie des témoignages passifs (exemple)
require_once __DIR__.'/config.php';
$rows = $pdo->query("SELECT auteur, ville, message FROM temoignages ORDER BY id DESC LIMIT 6")->fetchAll();
if(!$rows){
  // valeurs par défaut si la table est vide
  $rows = [
    ['auteur'=>'Camille','ville'=>'Paris','message'=>'Super expérience, conducteur à l’heure et trajet électrique !'],
    ['auteur'=>'Anis','ville'=>'Lyon','message'=>'Prix corrects et ambiance sympa.'],
    ['auteur'=>'Louise','ville'=>'Nantes','message'=>'Très pratique pour mes week-ends, je recommande.']
  ];
}
echo json_encode($rows);
