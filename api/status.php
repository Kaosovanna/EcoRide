<?php
// api/status.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../noyau_backend/configuration/db.php';

try {
    // 1. Lire schema.sql
    $schemaFile = __DIR__ . '/../noyau_backend/configuration/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Fichier schema.sql introuvable.");
    }
    $schemaSql = file_get_contents($schemaFile);

    // 2. Supprimer les déclarations de base de données spécifiques pour pouvoir l'exécuter sur n'importe quelle BDD
    $schemaSql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $schemaSql);
    $schemaSql = preg_replace('/USE [^;]+;/i', '', $schemaSql);

    // 3. Exécuter le schéma table par table
    $queries = explode(';', $schemaSql);
    foreach ($queries as $query) {
        $trimmed = trim($query);
        if (!empty($trimmed)) {
            $pdo->exec($trimmed);
        }
    }

    // 4. Lire et exécuter seed_data.sql
    $seedFile = __DIR__ . '/../noyau_backend/configuration/seed_data.sql';
    if (file_exists($seedFile)) {
        $seedSql = file_get_contents($seedFile);
        $seedSql = preg_replace('/USE [^;]+;/i', '', $seedSql);
        
        $queriesSeed = explode(';', $seedSql);
        foreach ($queriesSeed as $querySeed) {
            $trimmedSeed = trim($querySeed);
            if (!empty($trimmedSeed)) {
                $pdo->exec($trimmedSeed);
            }
        }
    }

    // 5. Insérer l'utilisateur de test spécifié par l'utilisateur
    // Email: milya.maxwell@test.fr, Mot de passe: password123
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute(['milya.maxwell@test.fr']);
    if (!$stmt->fetch()) {
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $stmtInsert = $pdo->prepare("INSERT INTO utilisateurs (pseudo, email, mot_de_passe_hash, role, credits) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->execute(['Milya Maxwell', 'milya.maxwell@test.fr', $hash, 'passager', 100]);
    }

    echo json_encode(["status" => 200, "message" => "Base de données initialisée avec succès !"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "message" => "Erreur d'initialisation", "error" => $e->getMessage()]);
}
