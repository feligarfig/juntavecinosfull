<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';  // ← aquí se crea $pdo y queda disponible

if (is_admin()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_POST) {
    $usuario = trim($_POST['usuario'] ?? '');
    $pass    = $_POST['pass'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE usuario = ? LIMIT 1");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['admin'] = true;
        $_SESSION['nombre'] = $user['nombre'] ?? 'Admin';
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJVV La Flecha</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Roboto',sans-serif;background:#f8f9ff;color:#333;display:flex;justify-content:center;align-items:center;height:100vh;}
        .login-box{background:white;padding:40px;border-radius:15px;box-shadow:0 15px 35px rgba(0,0,0,0.2);width:380px;text-align:center;}
        h1{color:#5b3adf;margin-bottom:10px;}
        input{width:100%;padding:14px;margin:10px 0;border-radius:8px;border:1px solid #ddd;font-size:16px;}
        button{width:100%;padding:14px;background:#5b3adf;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;}
        button:hover{background:#4a2bc7;}
        .error{color:#e74c3c;margin-top:15px;font-weight:500;}
    </style>
</head>
<body>
    <div class="login-box">
        <h1>JJVV La Flecha</h1>
        <p style="color:#777;margin-bottom:20px;">Acceso Administrativo</p>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required autofocus>
            <input type="password" name="pass" placeholder="Contraseña" required>
            <button type="submit">INGRESAR</button>
        </form>
        <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
        <small style="color:#999;display:block;margin-top:15px;">admin / 12345</small>
    </div>
</body>
</html>