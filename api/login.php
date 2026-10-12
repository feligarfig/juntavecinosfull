<?php
header("Content-Type: application/json");
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $usuario = $data['usuario'] ?? '';
    $pass = $data['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE rut_usuario = ? OR correo = ? LIMIT 1");
    $stmt->execute([$usuario, $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($pass, $user['contraseña'])) {
        echo json_encode([
            "success" => true,
            "user" => [
                "rut" => $user['rut_usuario'],
                "nombre" => $user['nombre'],
                "rol" => $user['rol']
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "error" => "Credenciales incorrectas"]);
    }
}
?>