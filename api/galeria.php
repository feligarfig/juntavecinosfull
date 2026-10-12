<?php
header("Content-Type: application/json");
require_once '../includes/db.php';

$stmt = $pdo->query("SELECT * FROM imagenes ORDER BY fecha_subida DESC LIMIT 20");
$fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success" => true,
    "data" => $fotos
]);
?>