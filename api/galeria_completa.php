<?php
require_once '../config.php';

header('Content-Type: application/json; charset=utf-8');

$stmt = $pdo->query("
    SELECT 
        i.id_imagen,
        i.ruta,              
        i.fecha_subida,
        i.ID_evento,
        e.Nombre_evento
    FROM imagenes i
    LEFT JOIN eventos e ON i.ID_evento = e.ID_evento
    ORDER BY i.fecha_subida DESC
");

$imagenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Convertir ruta en URL completa
$baseUrl = "http://juntavecinos.test/uploads/";

foreach ($imagenes as &$img) {
    $img['url'] = $baseUrl . $img['ruta'];
}

echo json_encode($imagenes, JSON_UNESCAPED_UNICODE);
exit;
