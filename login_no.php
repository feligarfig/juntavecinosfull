<?php
session_start();
require_once 'includes/auth.php';

// Si ya está logueado → muestra el dashboard directamente
if (is_admin()) {
    // === AQUÍ VA TODO EL DASHBOARD BONITO ===
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>JJVV La Flecha - Panel</title>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
        <script src="https://kit.fontawesome.com/8b0e5a0d5d.js" crossorigin="anonymous"></script>
        <style>
            *{margin:0;padding:0;box-sizing:border-box;}
            body{font-family:'Roboto',sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;color:#fff;}
            .topbar{background:linear-gradient(90deg,#5b3adf,#7b5aff);padding:15px 30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 4px 20px rgba(0,0,0,0.3);position:fixed;top:0;left:0;right:0;z-index:1000;}
            .logo{font-size:24px;font-weight:700;}
            .logo i{margin-right:10px;}
            .nav-menu a{color:white;text-decoration:none;margin:0 20px;font-weight:500;}
            .nav-menu a:hover{opacity:0.8;text-decoration:underline;}
            .salir{background:#e74c3c;padding:10px 20px;border-radius:30px;text-decoration:none;font-weight:600 14px 'Roboto';}
            .main{margin-top:90px;padding:40px;text-align:center;}
            .welcome{background:rgba(255,255,255,0.95);color:#2c3e50;border-radius:20px;padding:40px;max-width:800px;margin:0 auto 50px;box-shadow:0 15px 35px rgba(0,0,0,0.2);}
            .welcome h1{font-size:32px;margin-bottom:10px;}
            .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:25px;max-width:1100px;margin:0 auto;}
            .card{background:white;color:#333;padding:30px;border-radius:18px;box-shadow:0 10px 30px rgba(0,0,0,0.2);text-align:center;text-decoration:none;font-size:20px;font-weight:600;transition:.3s;}
            .card:hover{transform:translateY(-10px);box-shadow:0 20px 40px rgba(0,0,0,0.3);}
            .c1{background:#4e54c8;} .c2{background:#11998e;} .c3{background:#ff6b6b;}
            .c4{background:#6a11cb;} .c5{background:#ff9a9e;} .c6{background:#a8edea;color:#333;}
        </style>
    </head>
    <body>
        <div class="topbar">
            <div class="logo">JJVV La Flecha</div>
            <div class="nav-menu">
                <a href="finanzas.php">Finanzas</a>
                <a href="cobranza.php">Cobranza</a>
                <a href="eventos.php">Eventos</a>
                <a href="reservas.php">Reservas</a>
                <a href="socios.php">Socios</a>
            </div>
            <div>
                <span>Bienvenido <?= htmlspecialchars($_SESSION['nombre'] ?? 'Admin') ?></span>
                <a href="?logout=1" class="salir">SALIR</a>
            </div>
        </div>

        <div class="main">
            <div class="welcome">
                <h1>Bienvenido al Panel de Control</h1>
                <p>Junta de Vecinos La Flecha</p>
            </div>
            <div class="grid">
                <a href="eventos.php" class="card c1">Gestionar Eventos</a>
                <a href="finanzas.php" class="card c2">Finanzas</a>
                <a href="socios.php" class="card c3">Socios</a>
                <a href="cobranza.php" class="card c4">Cobranza</a>
                <a href="reservas.php" class="card c5">Reservas</a>
                <a href="avisos.php" class="card c6">Avisos</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit; // termina aquí si ya está logueado
}

// === SI NO ESTÁ LOGUEADO → MUESTRA EL LOGIN ===
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$error = '';
if ($_POST) {
    $user = $_POST['usuario'] ?? '';
    $pass = $_POST['pass'] ?? '';

    // Cambia estos datos por los que tú uses realmente
    if ($user === 'admin' && $pass === '12345') {
        $_SESSION['admin'] = true;
        $_SESSION['nombre'] = 'Evelyn Cisternas';
        header("Location: index.php"); // recarga y muestra el dashboard
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!-- Aquí va tu formulario de login actual (el que ya tenías en la portada) -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJVV La Flecha</title>
    <!-- Tus estilos actuales del login -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        /* Pega aquí todos los estilos que ya tenías para el login */
        body {font-family:'Roboto'; background:linear-gradient(135deg,#667eea,#764ba2); height:100vh; display:flex; justify-content:center; align-items:center; margin:0;}
        .login-box {background:white; padding:50px; border-radius:15px; box-shadow:0 20px 40px rgba(0,0,0,0.3); width:90%; max-width:420px; text-align:center;}
        /* ... el resto de tus estilos ... */
    </style>
</head>
<body>
    <div class="login-box">
        <h1>JJVV La Flecha</h1>
        <p style="color:#666; margin:20px 0;">Panel Administrativo</p>
        
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required style="width:100%; padding:14px; margin:10px 0; border-radius:8px; border:1px solid #ddd;">
            <input type="password" name="pass" placeholder="Contraseña" required style="width:100%; padding:14px; margin:10px 0; border-radius:8px; border:1px solid #ddd;">
            <button type="submit" style="width:100%; padding:14px; background:#5b3adf; color:white; border:none; border-radius:8px; font-size:18px; margin-top:15px;">INGRESAR</button>
        </form>
        
        <?php if($error): ?>
            <p style="color:#e74c3c; margin-top:20px; font-weight:500;"><?= $error ?></p>
        <?php endif; ?>
        
        <p style="margin-top:30px; color:#95a5a6; font-size:14px;">
            Usuario: <strong>admin</strong><br>
            Contraseña: <strong>12345</strong>
        </p>
    </div>
</body>
</html>