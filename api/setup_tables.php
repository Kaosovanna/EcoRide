<?php
// api/setup_tables.php
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

    // 2. Supprimer les déclarations de base de données spécifiques
    $schemaSql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $schemaSql);
    $schemaSql = preg_replace('/USE [^;]+;/i', '', $schemaSql);

    // 3. Exécuter le schéma
    $queries = explode(';', $schemaSql);
    foreach ($queries as $query) {
        $trimmed = trim($query);
        if (!empty($trimmed)) {
            $pdo->exec($trimmed);
        }
    }

    // Force la colonne photo en LONGTEXT si la table existait déjà
    $pdo->exec("ALTER TABLE utilisateurs MODIFY COLUMN photo LONGTEXT DEFAULT NULL");

    // Force la colonne photos en LONGTEXT pour la table vehicules si elle existait déjà
    try {
        $pdo->exec("ALTER TABLE vehicules ADD COLUMN photos LONGTEXT DEFAULT NULL");
    } catch (Exception $e) {
        // Ignorer si la colonne existe déjà
    }

    // Renommer mot_de_passe en mot_de_passe_hash si nécessaire
    try {
        $pdo->exec("ALTER TABLE utilisateurs CHANGE COLUMN mot_de_passe mot_de_passe_hash VARCHAR(255) NOT NULL");
    } catch (Exception $e) {
        // Ignorer si déjà renommé
    }

    // Renommer ville_destination en ville_arrivee si nécessaire
    try {
        $pdo->exec("ALTER TABLE trajets CHANGE COLUMN ville_destination ville_arrivee VARCHAR(100) NOT NULL");
    } catch (Exception $e) {
        // Ignorer si déjà renommé
    }

    // 4. Lire et exécuter seed_data.sql (qui contient maintenant 'Max')
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

    // 5. Ré-insérer l'utilisateur de test milya.maxwell@test.fr
    $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
    $stmt->execute(['milya.maxwell@test.fr']);
    if (!$stmt->fetch()) {
        $hash = password_hash('password123', PASSWORD_BCRYPT);
        $stmtInsert = $pdo->prepare("INSERT INTO utilisateurs (pseudo, email, mot_de_passe_hash, role, credits) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->execute(['Milya Maxwell', 'milya.maxwell@test.fr', $hash, 'passager', 100]);
    }

    echo json_encode(["status" => 200, "message" => "Base de données initialisée avec succès (Max et photos corrigées) !"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => 500, "message" => "Erreur d'initialisation", "error" => $e->getMessage()]);
}
