<?php
// Asegúrate de que el archivo _conexion.php exista y funcione (una vez que MySQL esté estable)
require_once '_conexion.php'; 

// 1. Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'usuario'; // Por seguridad, si viene vacío, es 'usuario'

    if (!empty($email) && !empty($password)) {
        try {
            // 2. Cifrar la contraseña ANTES de guardar
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            // 3. Conexión a la base de datos
            $conn = Conexion::conectar();
            
            // 4. Preparar la consulta
            $sql = "INSERT INTO usuarios (email, password, rol) VALUES (:email, :password, :rol)";
            $stmt = $conn->prepare($sql);
            
            // 5. Ejecutar y guardar
            $stmt->execute([
                ':email' => $email,
                ':password' => $hashed_password,
                ':rol' => $rol
            ]);

            $mensaje = "¡Usuario '{$email}' creado exitosamente!";

        } catch (PDOException $e) {
            // Si el email ya existe o hay otro error de DB
            $mensaje = "Error al crear el usuario: " . $e->getMessage();
        }
    } else {
        $mensaje = "El email y la contraseña son requeridos.";
    }
}
?>