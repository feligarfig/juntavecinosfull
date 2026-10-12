<?php
session_start();
require_once 'includes/auth.php';

// Si el admin está logueado → lo mandamos al panel interno
if (is_admin()) {
    header("Location: dashboard.php");  // ← este será el panel completo que ya tienes
    exit;
}

// Procesar login (solo desde la portada)
$error = '';
if ($_POST) {
    $user = $_POST['usuario'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if ($user === 'admin' && $pass === '12345') {   // ← cambia esto por tu usuario real
        $_SESSION['admin'] = true;
        $_SESSION['nombre'] = 'Evelyn Cisternas';
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
    <title>JJVV La Flecha - Junta de Vecinos</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Roboto',sans-serif;background:#f4f7ff;color:#333;line-height:1.6;}
        header{
            background:linear-gradient(135deg,#5b3adf,#7b5aff);
            color:white;padding:20px 0;text-align:center;position:relative;
        }
        .logo{font-size:32px;font-weight:700;}
        .logo i{margin-right:12px;}
        .container{max-width:1200px;margin:0 auto;padding:0 20px;}

        /* Login flotante */
        .login-box{
            position:fixed;top:20px;right:20px;background:white;color:#333;
            padding:20px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.2);
            width:320px;z-index:1000;
        }
        .login-box h3{margin-bottom:15px;color:#5b3adf;}
        .login-box input{width:100%;padding:10px;margin:8px 0;border-radius:8px;border:1px solid #ddd;}
        .login-box button{width:100%;padding:12px;background:#5b3adf;color:white;border:none;border-radius:8px;font-weight:600;cursor:pointer;}
        .login-box button:hover{background:#4a2bc7;}
        .error{color:#e74c3c;margin-top:10px;font-size:14px;}

        main{padding:60px 0;}
        .section{padding:50px 0;}
        .section h2{font-size:32px;text-align:center;margin-bottom:40px;color:#5b3adf;}

        /* Próximos eventos */
        .eventos-grid{
            display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:25px;
        }
        .evento{
            background:white;border-radius:12px;overflow:hidden;box-shadow:0 8px 25px rgba(0,0,0,0.1);
            transition:.3s;
        }
        .evento:hover{transform:translateY(-10px);}
        .evento img{width:100%;height:180px;object-fit:cover;}
        .evento-info{padding:20px;}
        .evento-info h3{margin-bottom:10px;color:#5b3adf;}
        .fecha{background:#5b3adf;color:white;display:inline-block;padding:5px 12px;border-radius:20px;font-size:14px;}

        /* Contacto */
        .contacto{
            background:#5b3adf;color:white;text-align:center;padding:60px 20px;border-radius:15px;
        }
        .social a{color:white;font-size:28px;margin:0 15px;transition:.3s;}
        .social a:hover{transform:scale(1.2);}

        footer{
            background:#333;color:white;text-align:center;padding:30px 0;
        }
    </style>
</head>
<body>

    <!-- Login flotante (solo visible para admin) -->
    <div class="login-box">
        <h3>Acceso Administrativo</h3>
        <form method="POST">
            <input type="text" name="usuario" placeholder="Usuario" required>
            <input type="password" name="pass" placeholder="Contraseña" required>
            <button type="submit">Ingresar</button>
        </form>
        <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
        <p style="font-size:12px;margin-top:10px;color:#777;">
            admin / 12345
        </p>
    </div>

    <header>
        <div class="container">
            <div class="logo">
                JJVV La Flecha
            </div>
        </div>
    </header>

    <main class="container">

        <!-- Próximos Eventos -->
        <section class="section">
            <h2>Próximos Eventos</h2>
            <div class="eventos-grid">
                <div class="evento">
                    <img src="https://via.placeholder.com/400x200/5b3adf/white?text=Bingo+Comunitario" alt="Bingo">
                    <div class="evento-info">
                        <h3>Bingo Comunitario</h3>
                        <p class="fecha">09 noviembre 2025</p>
                        <p>Bingo en beneficio de la Junta Vecinal. ¡Habrán grandes premios!</p>
                    </div>
                </div>
                <div class="evento">
                    <img src="https://via.placeholder.com/400x200/7b5aff/white?text=Feria+Navideña" alt="Feria">
                    <div class="evento-info">
                        <h3>Feria Navideña</h3>
                        <p class="fecha">14 diciembre 2025</p>
                        <p>Ven con tu familia a disfrutar food trucks, artesanías y música en vivo.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contacto y Redes -->
        <section class="contacto">
            <h2>Contáctanos</h2>
            <p>Coihueco, Ñuble</p>
            <p>jjvvlaflecha@gmail.com | +56 9 8765 4321</p>
            <div class="social" style="margin-top:25px;">
                <a href="#"><i class="fab fa-facebook"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-whatsapp"></i></a>
            </div>
        </section>

    </main>

    <footer>
        <div class="container">
            <p>&copy; 2025 Junta de Vecinos La Flecha • Todos los derechos reservados</p>
        </div>
    </footer>

</body>
</html>