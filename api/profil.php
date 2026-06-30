<?php
// api/profil.php — Public user profile + reviews
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../noyau_backend/configuration/db.php';

$user_id = intval($_GET['user_id'] ?? 0);
if (!$user_id) {
    http_response_code(400);
    echo json_encode(["status" => 400, "message" => "user_id manquant."]);
    exit;
}

// 1. Profil public (sans email, sans crédits)
$stmt = $pdo->prepare("
    SELECT id, pseudo, photo, biographie, role, pref_fumeur, pref_animaux, pref_musique, date_creation
    FROM utilisateurs WHERE id = ? LIMIT 1
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $user['biographie'] = htmlspecialchars_decode($user['biographie'] ?? '', ENT_QUOTES);
}

if (!$user) {
    http_response_code(404);
    echo json_encode(["status" => 404, "message" => "Utilisateur non trouvé."]);
    exit;
}

// 2. Stats trajets (nb de trajets en tant que conducteur)
$stmt2 = $pdo->prepare("SELECT COUNT(*) as total_trajets FROM trajets WHERE conducteur_id = ?");
$stmt2->execute([$user_id]);
$stats = $stmt2->fetch(PDO::FETCH_ASSOC);

// 3. Avis & Moyenne (SQL fallback if mongo not used or for mixed data)
$stmt3 = $pdo->prepare("
    SELECT a.note, a.commentaire, a.date_creation,
           u.pseudo as auteur_pseudo, u.photo as auteur_photo
    FROM avis a
    JOIN utilisateurs u ON u.id = a.auteur_id
    WHERE a.cible_id = ? AND a.statut = 'approuve'
    ORDER BY a.date_creation DESC
");
$stmt3->execute([$user_id]);
$reviews = $stmt3->fetchAll(PDO::FETCH_ASSOC);

$avg_rating = 0;
if (count($reviews) > 0) {
    $avg_rating = round(array_sum(array_column($reviews, 'note')) / count($reviews), 1);
}

echo json_encode([
    "status"         => 200,
    "utilisateur"    => $user,
    "total_trajets"  => (int)$stats['total_trajets'],
    "note_moyenne"   => $avg_rating,
    "nb_avis"        => count($reviews),
    "avis"           => $reviews
]);
