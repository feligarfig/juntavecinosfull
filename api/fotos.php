<?php
header('Content-Type: application/json');
require_once '../config.php';

$fotos = $pdo->query("SELECT ruta FROM imagenes ORDER BY fecha_subida DESC LIMIT 15")
    ->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($fotos);
?>