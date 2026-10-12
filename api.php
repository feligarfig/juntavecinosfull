<?php
// api.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Necesario para Flutter en desarrollo
require_once 'config.php';

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'avisos':
            $eventos = $pdo->query("
                SELECT 'evento' as tipo, Nombre_evento as titulo, Descripción as detalle, 
                       Fecha_evento as fecha, Hora_inicio, Hora_fin, NULL as tipo_reserva
                FROM eventos 
                ORDER BY Fecha_evento DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            $reservas = $pdo->query("
                SELECT 'reserva' as tipo, tipo_reserva as titulo, motivo as detalle,
                       fecha_reserva as fecha, hora_inicio, hora_fin, tipo_reserva
                FROM reserva WHERE estado_reserva = 'Aprobada'
                ORDER BY fecha_reserva DESC
            ")->fetchAll(PDO::FETCH_ASSOC);

            $avisos = array_merge($eventos, $reservas);
            usort($avisos, function($a, $b) {
                if ($a['fecha'] === $b['fecha']) {
                    return ($b['hora_inicio'] ?? '00:00') <=> ($a['hora_inicio'] ?? '00:00');
                }
                return $b['fecha'] <=> $a['fecha'];
            });

            echo json_encode($avisos, JSON_UNESCAPED_UNICODE);
            break;

        case 'galeria':
            $stmt = $pdo->query("
                SELECT i.ruta, i.nombre_imagen, e.Nombre_evento 
                FROM imagenes i 
                LEFT JOIN eventos e ON i.ID_evento = e.ID_evento 
                ORDER BY i.fecha_subida DESC
            ");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
            break;

        case 'eventos_calendario':
            $eventos = $pdo->query("
                SELECT Fecha_evento, Nombre_evento, Hora_inicio, Hora_fin, Descripción
                FROM eventos
                WHERE YEAR(Fecha_evento) >= YEAR(CURDATE())
                ORDER BY Fecha_evento
            ")->fetchAll(PDO::FETCH_ASSOC);

            $por_fecha = [];
            foreach ($eventos as $e) {
                $por_fecha[$e['Fecha_evento']][] = $e;
            }
            echo json_encode($por_fecha, JSON_UNESCAPED_UNICODE);
            break;

        case 'proximos_eventos':
            $eventos = $pdo->query("
                SELECT * FROM eventos 
                WHERE Fecha_evento >= CURDATE() 
                ORDER BY Fecha_evento ASC LIMIT 6
            ")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($eventos, JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}