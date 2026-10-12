<?php
require_once 'includes/auth.php';
if (!is_admin()) {
    header("Location: index.php");
    exit;
}
require_once 'includes/db.php';

// Contadores rápidos (opcional, para mostrar números)
$total_socios = $pdo->query("SELECT COUNT(*) FROM socios")->fetchColumn();
$total_eventos = $pdo->query(" equiv SELECT COUNT(*) FROM eventos")->fetchColumn(); // ajusta si tienes tabla eventos
$saldo_actual = $pdo->query("SELECT SUM(CASE WHEN tipo='Ingreso' THEN monto ELSE -monto END) FROM finanzas")->fetchColumn() ?? 0;
?>

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
        .logo {
            font-size: 24px; font-weight: 700;
        }
        .logo i { margin-right: 10px; }
        .nav-menu a {
            color: white; text-decoration: none; margin: 0 20px; font-weight: 500;
            transition: all 0.3s;
        }
        .nav-menu a:hover { opacity: 0.8; text-decoration: underline; }
        .user-area {
            display: flex; align-items: center; gap: 15px;
        }
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
        .welcome h1 {
            font-size: 32px; margin-bottom: 10px; color: #2c3e50;
        }
        .welcome p { color: #7f8c8d; font-size: 18px; }

        /* BOTONES GRANDES */
        .buttons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
            max-width: 1000px;
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
        .btn-large:hover { transform: translateY(-8px); box-shadow: 0 15px 30px rgba(0,0,0,0.3); }
        .btn-eventos { background: linear-gradient(135deg, #4e54c8, #8f94fb); }
        .btn-finanzas { background: linear-gradient(135deg, #11998e, #38ef7d); }
        .btn-socios { background: linear-gradient(135deg, #ff6b6b, #feca57); }
        .btn-cobranza { background: linear-gradient(135deg, #6a11cb, #2575fc); }
        .btn-reservas { background: linear-gradient(135deg, #ff9a9e, #fad0c4); }
        .btn-avisos { background: linear-gradient(135deg, #a8edea, #fed6e3); color: #333; }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR -->
    <div class="topbar">
        <div class="logo">
            <i class="fa-solid fa-house"></i> JJVV La Flecha
        </div>
        <div class="nav-menu">
            <a href="finanzas.php">Finanzas</a>
            <a href="cobranza.php">Cobranza</a>
            <a href="eventos.php">Eventos</a>
            <a href="reservas.php">Reservas</a>
            <a href="socios.php">Socios</a>
        </div>
        <div class="user-area">
            <span><i class="fa-solid fa-user"></i> Admin</span>
            <a href="logout.php" class="salir">SALIR</a>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="main">
        <div class="welcome">
            <h1>Bienvenido, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Administrador') ?></h1>
            <p>Panel de control de la Junta de Vecinos La Flecha</p>
        </div>

        <div class="buttons-grid">
            <a href="eventos.php" class="btn-large btn-eventos">
                Gestionar Eventos
            </ Ties>
            </a>
            <a href="finanzas.php" class="btn-large btn-finanzas">
                Finanzas
            </a>
            <a href="socios.php" class="btn-large btn-socios">
                Socios
            </a>
            <a href="cobranza.php" class="btn-large btn-cobranza">
                Cobranza
            </a>
            <a href="reservas.php" class="btn-large btn-reservas">
                Reservas
            </a>
            <a href="avisos.php" class="btn-large btn-avisos">
                Avisos
            </a>
        </div>
    </div>

</body>
</html>