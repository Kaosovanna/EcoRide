<?php
// noyau_backend/configuration/db.php
require_once __DIR__ . '/env_loader.php';

$host = getenv('MYSQL_HOST') ?: 'localhost';
$port = getenv('MYSQL_PORT') ?: '3306';
$db = getenv('MYSQL_DATABASE') ?: 'ecoride';
$user = getenv('MYSQL_USER') ?: 'root';
$pass = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : '';
$charset = 'utf8mb4';

// Special case: If host is e.g. 'db' (Docker) but no user/pass provided, 
// and we are NOT in the containers, this script might be failing.
// But we assume env vars are set properly now.

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Check for TiDB specific environment variables (often used in Vercel/TiDB integrations)
    // If TIDB_HOST is set, we use it, otherwise fall back to MYSQL_HOST
    $host = getenv('TIDB_HOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
    $port = getenv('TIDB_PORT') ?: (getenv('MYSQL_PORT') ?: '3306');
    $db = getenv('TIDB_DATABASE') ?: (getenv('MYSQL_DATABASE') ?: 'ecoride');
    $user = getenv('TIDB_USER') ?: (getenv('MYSQL_USER') ?: 'root');
    $pass = getenv('TIDB_PASSWORD') ?: (getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : '');
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

    // SSL Configuration for TiDB Cloud
    if (getenv('TIDB_HOST') || getenv('MYSQL_ATTR_SSL_CA')) {
        $ca_bundle = getenv('MYSQL_ATTR_SSL_CA') ?: '/etc/pki/tls/certs/ca-bundle.crt';
        if (file_exists($ca_bundle)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $ca_bundle;
        }
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, $user, $pass, $options);
}
catch (\PDOException $e) {
    error_log("DB Connection Failed: " . $e->getMessage());
    header('Content-Type: application/json', true, 500);
    echo json_encode(["status" => "error", "message" => "Connection Error", "details" => $e->getMessage()]);
    exit;
}
