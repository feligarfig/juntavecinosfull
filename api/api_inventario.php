<?php
require_once 'config.php';

header("Content-Type: application/json; charset=utf-8");

$sql = "
SELECT 
    i.*, 
    COALESCE(u.nombre, 'Admin') AS nombre_usuario
FROM inventario i
LEFT JOIN usuario u ON TRIM(i.rut_usuario) = TRIM(u.rut_usuario)
ORDER BY i.fecha_registro DESC, i.ID_material DESC
";

$stmt = $pdo->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);
