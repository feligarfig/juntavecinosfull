<?php
require_once 'config.php';

// ========================================
// 1. EVENTOS PRÓXIMOS
// ========================================
$hoy = date('Y-m-d');
try {
    $stmt = $pdo->query("SELECT * FROM eventos 
                         WHERE Fecha_evento >= '$hoy' 
                           AND (Estado IS NULL OR Estado NOT IN ('Cancelado'))
                         ORDER BY Fecha_evento ASC 
                         LIMIT 6");
    $proximos_eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $proximos_eventos = [];
}

// ========================================
// 2. ÚLTIMAS FOTOS
// ========================================
try {
    $fotos = $pdo->query("
        SELECT i.ruta, i.nombre_imagen, e.Nombre_evento 
        FROM imagenes i 
        LEFT JOIN eventos e ON i.ID_evento = e.ID_evento 
        ORDER BY i.fecha_subida DESC LIMIT 20
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $fotos = [];
}
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJVV La Flecha • Coihueco</title>
    <meta name="description" content="Junta de Vecinos Villa La Flecha - Coihueco">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .hover-scale { transition: all 0.4s ease; }
        .hover-scale:hover { transform: translateY(-12px) scale(1.03); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.35); }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen">

    <!-- BARRA DE NAVEGACIÓN (igual que galería pública) -->
    <nav class="bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto px-6 py-5 flex flex-wrap justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 text-3xl font-black hover:scale-110 transition">
                <i class="fas fa-home"></i> JJVV La Flecha
            </a>
            <div class="flex gap-8 text-xl font-bold mt-4 md:mt-0">
                <a href="index.php" class="hover:text-cyan-200">Inicio</a>
                <a href="noticias.php" class="hover:text-cyan-200">Noticias</a>
                <a href="eventos.php" class="hover:text-cyan-200">Eventos</a>
                <a href="galeria_publica.php" class="hover:text-cyan-200">Galería</a>
                <a href="documentos.php" class="hover:text-cyan-200">Documentos</a>
            </div>
        </div>
    </nav>

    <!-- EVENTOS DESTACADOS -->
    <section class="py-20 px-6">
        <div class="container mx-auto max-w-7xl text-center">
            <h1 class="text-6xl md:text-8xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-6">
                JJVV LA FLECHA
            </h1>
            <p class="text-3xl font-bold text-emerald-700 mb-16">Coihueco • Región de Ñuble</p>

            <h2 class="text-5xl md:text-6xl font-black text-emerald-800 mb-12">
                PRÓXIMOS EVENTOS
            </h2>

            <?php if (empty($proximos_eventos)): ?>
                <div class="bg-white/90 rounded-3xl p-16 shadow-2xl">
                    <p class="text-4xl font-bold text-emerald-700">No hay eventos próximos</p>
                    <p class="text-2xl text-gray-600 mt-4">¡Pronto tendremos nuevas actividades!</p>
                </div>
            <?php else: ?>
                <div class="grid gap-10 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($proximos_eventos as $e): 
                        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                        $dia = date('d', strtotime($e['Fecha_evento']));
                        $mes = $meses[date('n', strtotime($e['Fecha_evento']))-1];
                        $año = date('Y', strtotime($e['Fecha_evento']));
                    ?>
                        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden hover-scale">
                            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8 text-center">
                                <div class="text-6xl font-black"><?= $dia ?></div>
                                <div class="text-2xl font-bold"><?= $mes ?> <?= $año ?></div>
                            </div>
                            <div class="p-8">
                                <h3 class="text-3xl font-black text-emerald-800 mb-4"><?= htmlspecialchars($e['Nombre_evento']) ?></h3>
                                <p class="text-xl text-gray-700 mb-4">
                                    <i class="fas fa-clock text-emerald-600"></i>
                                    <?= substr($e['Hora_inicio'],0,5) ?> - <?= substr($e['Hora_fin'],0,5) ?> hrs
                                </p>
                                <?php if($e['Descripción']): ?>
                                    <p class="text-gray-600 line-clamp-3"><?= htmlspecialchars($e['Descripción']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container mx-auto px-6 py-16 max-w-7xl">
        <div class="grid lg:grid-cols-2 gap-12">

            <!-- LOGIN + CONTACTO -->
            <div class="space-y-12">
                <?php include 'login_form.php'; ?>

                <!-- CONTACTO (correo oculto + Facebook) -->
                <div class="bg-white rounded-3xl shadow-2xl p-10 hover-scale">
                    <h2 class="text-4xl font-black text-emerald-800 mb-8 text-center">
                        <i class="fas fa-envelope text-emerald-600"></i> Contáctanos
                    </h2>
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Botón correo oculto -->
                        <a href="mailto:juntalaflecha@gmail.com" 
                           class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-center py-12 rounded-3xl font-black text-3xl shadow-xl transform hover:scale-105 transition">
                            <i class="fas fa-envelope text-6xl mb-4 block"></i>
                            Enviar Correo
                        </a>
                        <!-- Facebook -->
                        <a href="https://www.facebook.com/profile.php?id=100091532520182" target="_blank"
                           class="bg-gradient-to-r from-blue-700 to-blue-900 hover:from-blue-600 hover:to-blue-800 text-white text-center py-12 rounded-3xl font-black text-3xl shadow-xl transform hover:scale-105 transition">
                            <i class="fab fa-facebook text-6xl mb-4 block"></i>
                            Síguenos en Facebook
                        </a>
                    </div>
                </div>
            </div>

            <!-- GALERÍA + RESERVA -->
            <div class="space-y-12">

                <!-- GALERÍA -->
                <div class="bg-white rounded-3xl shadow-2xl p-10 hover-scale">
                    <h2 class="text-4xl font-black text-emerald-800 mb-8 flex items-center gap-4">
                        <i class="fas fa-images text-teal-600"></i> Galería de Fotos
                    </h2>
                    <?php if (empty($fotos)): ?>
                        <p class="text-center text-2xl text-gray-500 py-10">Pronto compartiremos momentos de nuestra comunidad</p>
                    <?php else: ?>
                        <div class="grid grid-cols-3 md:grid-cols-4 gap-4 mb-8">
                            <?php foreach($fotos as $f): ?>
                                <div class="group relative overflow-hidden rounded-2xl shadow-lg">
                                    <img src="<?= htmlspecialchars($f['ruta']) ?>" class="w-full h-40 object-cover group-hover:scale-110 transition">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent opacity-0 group-hover:opacity-100 transition flex items-end p-3">
                                        <p class="text-white text-sm font-bold"><?= htmlspecialchars($f['nombre_imagen']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center">
                            <a href="galeria_publica.php" class="inline-block bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-2xl py-6 px-16 rounded-3xl shadow-xl transform hover:scale-105 transition">
                                VER TODAS LAS FOTOS
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- RESERVA DE SEDE -->
                <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-3xl shadow-2xl p-10 text-black hover-scale">
                    <h2 class="text-4xl font-black mb-8 flex items-center gap-4">
                        <i class="fas fa-key text-black-300"></i> Reserva de Sede Social
                    </h2>
                    <form method="POST" action="reservar_sede.php" class="space-y-6">
                        <input type="text" name="nombre" required placeholder="Tu nombre completo" class="w-full px-8 py-6 rounded-2xl text-xl">
                        <input type="date" name="fecha" required min="<?= date('Y-m-d') ?>" class="w-full px-8 py-6 rounded-2xl text-xl">
                        <textarea name="motivo" rows="4" required placeholder="Motivo de la reserva..." class="w-full px-8 py-6 rounded-2xl text-xl"></textarea>
                        <button type="submit" class="w-full bg-white text-emerald-700 font-black text-3xl py-6 rounded-3xl hover:bg-gray-100 transform hover:scale-105 transition">
                            ENVIAR SOLICITUD
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <footer class="text-center mt-20 py-12 bg-emerald-800/50 rounded-3xl">
            <p class="text-2xl font-bold text-white">
                © <?= date('Y') ?> Junta de Vecinos La Flecha • Km 5 Camino a Bustamante, Coihueco
            </p>
        </footer>
    </div>
</body>
</html>