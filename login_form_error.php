<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// --- YA LOGUEADO ---
if (isset($_SESSION['usuario'])) {
    echo '<div class="glass rounded-3xl p-8 text-center card">
            <i class="fas fa-user-check text-6xl text-cyan-300 mb-4"></i>
            <p class="text-2xl font-black mb-6">¡Bienvenido!<br>' . htmlspecialchars($_SESSION['nombre'] ?? $_SESSION['usuario']) . '</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="public/admin_reservas.php" class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-black px-10 py-4 rounded-full shadow-xl text-xl">
                    Panel Reservas
                </a>
                <a href="login_form.php?logout=1" class="bg-red-600 hover:bg-red-700 text-white font-black px-10 py-4 rounded-full shadow-xl text-xl">
                    Cerrar Sesión
                </a>
            </div>
          </div>';
    return;
}

// --- CERRAR SESIÓN ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: public/index.php");
    exit;
}

// --- PROCESAR LOGIN ---
$mensaje_login = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === "" || $password === "") {
        $mensaje_login = "Completa ambos campos";
    } else {
        try {
            require_once 'config.php';  // ← conexión desde raíz

            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? AND password = ? LIMIT 1");
            $stmt->execute([$usuario, $password]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['usuario'] = $user['usuario'];
                $_SESSION['nombre']  = $user['nombre'] ?? $user['usuario'];
                $_SESSION['rol']     = $user['rol'] ?? 'admin';
                header("Location: public/index.php");
                exit;
            } else {
                $mensaje_login = "Usuario o contraseña incorrectos";
            }
        } catch (Exception $e) {
            $mensaje_login = "Error de conexión";
        }
    }
}
?>

<div class="glass rounded-3xl p-8 card">
    <h3 class="text-4xl font-black mb-8 text-center flex items-center justify-center gap-4">
        Área de Administración
    </h3>

    <?php if ($mensaje_login): ?>
        <div class="bg-red-600/60 border-4 border-red-500 text-white p-5 rounded-2xl text-center mb-6 font-bold text-lg">
            <?= htmlspecialchars($mensaje_login) ?>
        </div>
    <?php endif; ?>

    <!-- action vacío = misma página (login_form.php) -->
    <form method="POST" action="" class="space-y-6">
        <input type="text" name="usuario" required placeholder="Usuario"
               class="w-full px-6 py-5 rounded-2xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none text-xl">
        <input type="password" name="password" required placeholder="Contraseña"
               class="w-full px-6 py-5 rounded-2xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none text-xl">
        <button type="submit" name="login" class="w-full bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-400 hover:to-purple-500 text-white font-black text-2xl py-6 rounded-3xl shadow-2xl transform hover:scale-105 transition">
            INGRESAR
        </button>
    </form>
</div>