<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once '../config.php';

// === 1. EVENTOS ===
$eventos = $pdo->query("
    SELECT 'evento' as tipo, 
           Nombre_evento as titulo, 
           Descripción as detalle, 
           Fecha_evento as fecha, 
           Hora_inicio, 
           Hora_fin
    FROM eventos 
    ORDER BY Fecha_evento DESC
")->fetchAll(PDO::FETCH_ASSOC);

// === 2. RESERVAS APROBADAS ===
$reservas = $pdo->query("
    SELECT 'reserva' as tipo, 
           tipo_reserva as titulo, 
           motivo as detalle,
           fecha_reserva as fecha, 
           hora_inicio, 
           hora_fin
    FROM reserva 
    WHERE estado_reserva = 'Aprobada'
    ORDER BY fecha_reserva DESC
")->fetchAll(PDO::FETCH_ASSOC);

// === 3. UNIR Y ORDENAR CRONOLÓGICAMENTE ===
$avisos = array_merge($eventos, $reservas);
usort($avisos, function($a, $b) {
    $fechaA = $a['fecha'] . ' ' . ($a['Hora_inicio'] ?? '00:00');
    $fechaB = $b['fecha'] . ' ' . ($b['Hora_inicio'] ?? '00:00');
    return strtotime($fechaB) - strtotime($fechaA); // más reciente arriba
});

echo json_encode($avisos, JSON_UNESCAPED_UNICODE);
?>