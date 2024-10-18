<?php
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$db   = 'your_database_name';
$user = 'your_username';
$pass = 'your_password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Search query
$query = isset($_GET['q']) ? $_GET['q'] : '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

// Prepare and execute the search query
$sql = "SELECT id, name, price FROM products WHERE name LIKE :query";
$stmt = $pdo->prepare($sql);
$stmt->execute(['query' => "%$query%"]);
$results = $stmt->fetchAll();

echo json_encode($results);