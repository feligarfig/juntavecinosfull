<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = $_POST['rut'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $apellido1 = $_POST['apellido1'] ?? '';
    $apellido2 = $_POST['apellido2'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? null;
    $direccion = $_POST['direccion'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $ocupacion = $_POST['ocupacion'] ?? '';

    // Validación básica
    if ($rut && $nombre && $apellido1 && $direccion && $estado && $ocupacion) {
        try {
            $stmt = $pdo->prepare("INSERT INTO socios (RUT, Nombre, Apellido1, Apellido2, Fecha_nacimiento, Domicilio, estado, Ocupación)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $rut,
                $nombre,
                $apellido1,
                $apellido2,
                $fecha_nacimiento ?: null,
                $direccion,
                $estado,
                $ocupacion
            ]);

            // Redirigir con mensaje de éxito
            header("Location: socios.php?success=1");
            exit;
        } catch (PDOException $e) {
            die("Error al guardar el socio: " . $e->getMessage());
        }
    } else {
        die("Faltan campos obligatorios.");
    }
} else {
    header("Location: socios.php");
    exit;
}