<?php
// noyau_backend/configuration/mongo.php
require_once __DIR__ . '/env_loader.php';

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php'; // If using Composer for MongoDB driver
}

// Fallback logic for when MongoDB native driver is used instead of the library
$mongoUri = getenv('MONGO_URI');

try {
    if (class_exists('MongoDB\Client')) {
        $mongoClient = new MongoDB\Client($mongoUri);
        $mongoDb = $mongoClient->selectDatabase('ecoride');
    }
    else if (class_exists('MongoDB\Driver\Manager')) {
        $mongoManager = new MongoDB\Driver\Manager($mongoUri);
    // Using standard driver manager directly
    }
    else {
        throw new Exception("No suitable MongoDB driver found.");
    }
}
catch (Exception $e) {
    error_log("MongoDB Connection Error: " . $e->getMessage());
    header('Content-Type: application/json', true, 500);
    echo json_encode(["status" => "error", "message" => "NoSQL Database connection failed"]);
    exit;
}
