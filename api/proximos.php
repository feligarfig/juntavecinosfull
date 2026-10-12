<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Ruta correcta a config.php
require_once '../config.php';

try {
    $stmt = $pdo->query("
        SELECT 
            Fecha_evento as fecha,
            Nombre_evento as nombre,
            Hora_inicio as inicio,
            Hora_fin as fin
        FROM eventos 
        WHERE Fecha_evento >= CURDATE() 
        ORDER BY Fecha_evento ASC 
        LIMIT 10
    ");
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay eventos, devolvemos array vacío (no error)
    echo json_encode($result ?: [], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    // Si hay error, lo mostramos (útil para depurar)
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>