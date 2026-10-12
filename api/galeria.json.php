<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once '../includes/db.php';

// Solo devuelve las rutas completas de las imágenes
$stmt = $pdo->query("SELECT ruta FROM imagenes ORDER BY fecha_subida DESC");
$fotos = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ruta = $row['ruta'];
    // Si la ruta ya empieza con http, la deja igual. Si no, le pone la base
    if (!str_starts_with($ruta, 'http')) {
        $ruta = "http://10.0.2.2/juntavecinos/" . ltrim($ruta, '/');
    }
    $fotos[] = $ruta;
}

echo json_encode($fotos);
?>