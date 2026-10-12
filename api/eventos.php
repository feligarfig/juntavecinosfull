<?php
header('Content-Type: application/json');
require_once '../config.php';

$eventos = $pdo->query("
    SELECT Fecha_evento as fecha, Nombre_evento as nombre, Hora_inicio as inicio, Hora_fin as fin, Descripción as descripcion
    FROM eventos 
    WHERE YEAR(Fecha_evento) >= YEAR(CURDATE())
    ORDER BY Fecha_evento
")->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($eventos as $e) {
    $fecha = $e['fecha'];
    if (!isset($result[$fecha])) $result[$fecha] = [];
    $result[$fecha][] = $e;
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
?>