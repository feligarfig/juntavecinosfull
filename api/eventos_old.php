<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once '../includes/db.php';

$action = $_GET['action'] ?? '';

if ($action === 'calendario' || $action === '') {
    // ← ESTE ES EL QUE USA EL CALENDARIO DE LA APP
    $stmt = $pdo->query("SELECT Fecha_evento, Nombre_evento, Hora_inicio, Hora_fin, Descripción FROM eventos ORDER BY Fecha_evento");
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($eventos, JSON_UNESCAPED_UNICODE);

} elseif ($action === 'proximos') {
    $stmt = $pdo->query("SELECT * FROM eventos WHERE Fecha_evento >= CURDATE() ORDER BY Fecha_evento ASC LIMIT 6");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);

} elseif ($action === 'galeria') {
    // (esto ya lo tenías en otro archivo, pero lo dejamos por si acaso)
    $stmt = $pdo->query("SELECT ruta FROM imagenes ORDER BY fecha_subida DESC");
    $fotos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $ruta = $row['ruta'];
        if (!str_starts_with($ruta, 'http')) {
            $ruta = "http://juntavecinos.test/" . ltrim($ruta, '/');
        }
        $fotos[] = $ruta;


    }

    } elseif ($action === 'avisos') {

    // Tomar los eventos y usarlos como avisos/noticias
    $stmt = $pdo->query("
        SELECT 
            ID_evento AS id,
            Nombre_evento AS titulo,
            Descripción AS contenido,
            Fecha_evento AS fecha,
            Hora_inicio,
            Hora_fin,
            Estado
        FROM eventos
        ORDER BY Fecha_evento DESC
    ");

    $avisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($avisos, JSON_UNESCAPED_UNICODE);
}

 
?>