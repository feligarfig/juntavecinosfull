<?php
header("Content-Type: application/json");
require_once '../includes/db.php';

$stmt = $pdo->query("SELECT * FROM noticias ORDER BY fecha DESC LIMIT 10");
$noticias = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "data" => $noticias
]);
?>