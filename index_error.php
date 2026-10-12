<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Si ya está logueado, va al dashboard
if (is_admin()) {
    header("Location: dashboard.php");
    exit;
}

// Procesar el login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $pass = $_POST['pass'] ?? '';

    if ($usuario && $pass) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? LIMIT 1");
            $stmt->execute([$usuario]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['password'])) {
                $_SESSION['admin'] = true;
                $_SESSION['nombre'] = $user['nombre'] ?? 'Administrador';
                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
        } catch (PDOException $e) {
            $error = "Error al conectar con la base de datos: " . $e->getMessage();
        }
    } else {
        $error = "Por favor, completa todos los campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJVV La Flecha - Portada</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f0f4f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 380px;
            text-align: center;
        }
        h1 { color: #5b3adf; margin-bottom: 20px; }
        input, button { width: 100%; padding: 14px; margin: 10px 0; border-radius: 8px; }
        input { border: 1px solid #ddd; }
        button { background: #5b3adf; color: white; border: none; font-weight: 600; cursor: pointer; }
        button:hover { background: #4a2bc7; }
        .error { color: #e74c3c; margin-top: 10px; font-weight: 500; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>JJVV La Flecha</h1>
        <p>Acceso Administrativo</p>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required autofocus>
            <input type="password" name="pass" placeholder="Contraseña" required>
            <button type="submit">INGRESAR</button>
        </form>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <small style="color: #999;">Usuario: admin / Contraseña: 12345</small>
    </div>
</body>
</html>