<?php
require_once 'config.php';

// Próximos eventos y fotos
$proximos_eventos = $pdo->query("
    SELECT * FROM eventos 
    WHERE Fecha_evento >= CURDATE() 
    ORDER BY Fecha_evento ASC LIMIT 6
")->fetchAll();

try {
    $fotos = $pdo->query("
        SELECT ruta, nombre_imagen FROM imagenes 
        ORDER BY fecha_subida DESC LIMIT 20
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $fotos = [];
}

// Detectar si hay usuario logueado
$usuario_logueado = isset($_SESSION['rut']) && isset($_SESSION['nombre']);
$nombre_usuario = $_SESSION['nombre'] ?? '';
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJVV La Flecha • Coihueco</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .hover-scale { transition: all 0.4s ease; }
        .hover-scale:hover { transform: translateY(-10px) scale(1.02); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.3); }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen">

    <!-- NAVEGACIÓN CON LOGIN -->
    <nav class="bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto px-6 py-5 flex flex-wrap justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 text-3xl font-black hover:scale-110 transition">
                <i class="fas fa-home"></i> JJVV La Flecha
            </a>

            <div class="flex gap-6 text-xl font-bold items-center mt-4 md:mt-0">

                <a href="index.php" class="hover:text-cyan-200">Inicio</a>
                <a href="noticias.php" class="hover:text-cyan-200">Noticias</a>
                <a href="galeria_publica.php" class="hover:text-cyan-200">Galería</a>
                <a href="reservar_sede.php" class="bg-cyan-400 text-emerald-900 px-6 py-3 rounded-full font-black hover:bg-cyan-300 transition">
                    Reserva de Sede
                </a>

                <!-- LOGIN O BIENVENIDA -->
                <?php if ($usuario_logueado): ?>
                    <div class="flex items-center gap-4 border-l-2 border-white/30 pl-6">
                        <span class="text-lg">
                            <i class="fas fa-user-circle text-2xl"></i> <?= htmlspecialchars($nombre_usuario) ?>
                        </span>
                        <a href="dashboard.php" class="bg-white/20 hover:bg-white/30 px-6 py-3 rounded-full font-bold transition">
                            Panel Admin
                        </a>
                        <a href="login.php?logout=1" class="text-red-300 hover:text-red-100 text-sm">
                            <i class="fas fa-sign-out-alt"></i> Salir
                        </a>
                    </div>
                <?php else: ?>
                    <a href="#" onclick="document.getElementById('modal-login').classList.remove('hidden')" 
                       class="bg-white/20 hover:bg-white/30 px-8 py-3 rounded-full font-bold transition hover:scale-105">
                        <i class="fas fa-user-lock"></i> Login Admin
                    </a>
                <?php endif; ?>

            </div>
        </div>
    </nav>

    <!-- MODAL LOGIN (solo si no está logueado) -->
    <?php if (!$usuario_logueado): ?>
    <div id="modal-login" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-3xl p-10 max-w-md w-full mx-6 shadow-2xl">
            <h3 class="text-4xl font-black text-emerald-800 text-center mb-8">Acceso Administrativo</h3>
            <?php include 'login_form.php'; ?>
            <button onclick="document.getElementById('modal-login').classList.add('hidden')" 
                    class="mt-4 text-gray-500 hover:text-gray-700 underline block mx-auto">
                Cerrar
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- HERO -->
    <section class="py-28 px-6 text-center">
        <div class="container mx-auto max-w-5xl">
            <h1 class="text-7xl md:text-9xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-6">
                JJVV LA FLECHA
            </h1>
            <p class="text-3xl md:text-5xl font-bold text-emerald-800 mb-4">Coihueco • Región de Ñuble</p>
            <p class="text-xl md:text-2xl text-emerald-700">Juntos hacemos comunidad</p>
        </div>
    </section>

    <!-- PRÓXIMOS EVENTOS -->
    <?php if (!empty($proximos_eventos)): ?>
    <section class="py-16 px-6 bg-white/80">
        <div class="container mx-auto max-w-7xl">
            <h2 class="text-5xl font-black text-center text-emerald-800 mb-12">Próximos Eventos</h2>
            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($proximos_eventos as $e): 
                    $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    $dia = date('d', strtotime($e['Fecha_evento']));
                    $mes = $meses[date('n', strtotime($e['Fecha_evento']))-1];
                ?>
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden hover-scale">
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-6 text-center">
                        <div class="text-6xl font-black"><?= $dia ?></div>
                        <div class="text-2xl font-bold"><?= $mes ?></div>
                    </div>
                    <div class="p-8">
                        <h3 class="text-2xl font-black text-emerald-800 mb-3"><?= htmlspecialchars($e['Nombre_evento']) ?></h3>
                        <p class="text-lg text-gray-700">
                            <i class="fas fa-clock text-emerald-600"></i>
                            <?= substr($e['Hora_inicio'],0,5) ?> - <?= substr($e['Hora_fin'],0,5) ?> hrs
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-10">
                <a href="noticias.php" class="bg-emerald-600 text-white font-black text-2xl py-5 px-12 rounded-full hover:bg-emerald-700 transition inline-block">
                    Ver todos los eventos →
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- GALERÍA RÁPIDA -->
    <?php if (!empty($fotos)): ?>
    <section class="py-20 px-6">
        <div class="container mx-auto max-w-7xl">
            <h2 class="text-5xl font-black text-center text-emerald-800 mb-12">Galería Reciente</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <?php foreach (array_slice($fotos, 0, 8) as $f): ?>
                <div class="group relative overflow-hidden rounded-3xl shadow-lg hover-scale">
                    <img src="<?= htmlspecialchars($f['ruta']) ?>" class="w-full h-64 object-cover group-hover:scale-110 transition">
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-10">
                <a href="galeria_publica.php" class="bg-teal-600 text-white font-black text-2xl py-5 px-12 rounded-full hover:bg-teal-700 transition inline-block">
                    Ver toda la galería →
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- CONTACTO -->
    <section class="py-20 px-6 bg-emerald-800/90 text-white">
        <div class="container mx-auto max-w-4xl text-center">
            <h2 class="text-5xl font-black mb-8">Contáctanos</h2>
            <div class="grid md:grid-cols-2 gap-8 max-w-2xl mx-auto">
                <a href="mailto:juntalaflecha@gmail.com" class="bg-white/20 backdrop-blur hover:bg-white/30 p-10 rounded-3xl transition hover-scale">
                    <i class="fas fa-envelope text-6xl mb-4"></i>
                    <p class="text-2xl font-black">Correo</p>
                </a>
                <a href="https://www.facebook.com/profile.php?id=100091532520182" target="_blank" class="bg-white/20 backdrop-blur hover:bg-white/30 p-10 rounded-3xl transition hover-scale">
                    <i class="fab fa-facebook text-6xl mb-4"></i>
                    <p class="text-2xl font-black">Facebook</p>
                </a>
            </div>
        </div>
    </section>

    <footer class="text-center py-12 bg-emerald-900 text-white text-xl font-bold">
        © <?= date('Y') ?> Junta de Vecinos La Flecha • Coihueco
    </footer>
</body>
</html>