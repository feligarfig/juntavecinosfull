<?php
require_once 'config.php';
header('Content-Type: application/json');

$fecha = $_GET['fecha'] ?? '';

if (!$fecha || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode(['ocupados' => 0]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM reserva 
        WHERE fecha_reserva = ? 
          AND tipo_reserva = 'Feria artesanal'
          AND estado_reserva != 'Rechazada'
    ");
    $stmt->execute([$fecha]);
    $ocupados = (int)$stmt->fetchColumn();

    echo json_encode(['ocupados' => $ocupados]);
} catch (Exception $e) {
    echo json_encode(['ocupados' => 0]);
}
?>