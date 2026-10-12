<?php
require_once 'includes/auth.php';
session_start();
session_unset();    // Limpia todas las variables de sesión
session_destroy();  // Destruye la sesión actual

# if ($_SESSION['rol'] != 'Admin') header('location: ./?error=Usted+no+tiene+permisos');
require_once 'includes/db.php';
?>
<!-- BARRA SUPERIOR -->
<div class="topbar">
    <div class="logo">
            <i class="fas fa-home text-3xl"></i>
            <span class="text-2xl font-black">JJVV La Flecha</span>
        </a>
    </div>

    <div class="user-area">
        <span class="text-lg font-medium mr-6">
            Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Admin') ?>
        </span>
        <a href="logout.php" class="salir">
            <i class="fas fa-sign-out-alt mr-2"></i> CERRAR SESIÓN
        </a>
    </div>
</div>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJVV La Flecha - Panel Administrativo</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/8b0e5a0d5d.js" crossorigin="anonymous"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        /* BARRA SUPERIOR */
        .topbar {
            background: linear-gradient(90deg, #5b3adf, #7b5aff);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1000;
        }
        .logo { font-size: 24px; font-weight: 700; }
        .logo i { margin-right: 10px; }
        .nav-menu a {
            color: white; text-decoration: none; margin: 0 20px; font-weight: 500;
            transition: all 0.3s;
        }
        .nav-menu a:hover { opacity: 0.8; text-decoration: underline; }
        .user-area { display: flex; align-items: center; gap: 15px; }
        .salir {
            background: #e74c3c; color: white; padding: 10px 20px; border-radius: 30px;
            text-decoration: none; font-weight: 600; font-size: 14px;
        }
        .salir:hover { background: #c0392b; }

        /* CONTENIDO PRINCIPAL */
        .main {
            margin-top: 90px;
            padding: 40px 20px;
            text-align: center;
        }
        .welcome {
            background: white;
            max-width: 800px;
            margin: 0 auto 50px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .welcome h1 { font-size: 32px; margin-bottom: 10px; color: #2c3e50; }
        .welcome p { color: #7f8c8d; font-size: 18px; }

        /* BOTONES GRANDES */
        .buttons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
            max-width: 1100px;
            margin: 0 auto;
        }
        .btn-large {
            display: block;
            padding: 25px;
            border-radius: 18px;
            color: white;
            font-size: 20px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        .btn-large:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 15px 30px rgba(0,0,0,0.3); 
        }

        /* COLORES EXACTOS AL ORIGINAL + 2 NUEVOS */
        .btn-eventos   { background: linear-gradient(135deg, #4e54c8, #8f94fb); }
        .btn-finanzas  { background: linear-gradient(135deg, #11998e, #38ef7d); }
        .btn-socios    { background: linear-gradient(135deg, #ff6b6b, #feca57); }
        .btn-cobranza  { background: linear-gradient(135deg, #6a11cb, #2575fc); }
        .btn-reservas  { background: linear-gradient(135deg, #ff9a9e, #fad0c4); }
        .btn-usuarios  { background: linear-gradient(135deg, #8E2DE2, #4A00E0); }
        .btn-galeria   { background: linear-gradient(135deg, #FA8BFF, #2BD2FF); }
        .btn-avisos    { background: linear-gradient(135deg, #a8edea, #fed6e3); color: #333; }
    </style>
</head>
<body>


    <!-- CONTENIDO -->
    <div class="main">
        <div class="welcome">
            <h1>Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Administrador') ?></h1>
            <p>Panel de control de la Junta de Vecinos La Flecha</p>
        </div>

        <div class="buttons-grid">
            <a href="noticias.php" class="btn-large btn-eventos">
                Gestionar Eventos
            </a>
            <a href="/juntavecinos/finanzas.php" class="btn-large btn-finanzas">
                Finanzas
            </a>
            <a href="socios.php" class="btn-large btn-socios">
                Socios
            </a>
            <a href="pagos.php" class="btn-large btn-cobranza">
                Pagos
            </a>
            <a href="reservas.php" class="btn-large btn-reservas">
                Reservas
            </a>
            <a href="gestion_usuarios.php" class="btn-large btn-usuarios">
                Gestión de Usuarios
            </a>
            <a href="galeria_admin.php" class="btn-large btn-galeria">
                Galería de Fotos
            </a>
            <a href="avisos.php" class="btn-large btn-avisos">
                Avisos
            </a>
        </div>
    </div>

</body>
</html>