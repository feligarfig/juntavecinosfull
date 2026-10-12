<?php
require_once 'config.php';

// ========================================
// 1. EVENTOS PRÓXIMOS (sección grande + lista lateral)
// ========================================
$hoy = date('Y-m-d');
try {
    $stmt = $pdo->query("SELECT * FROM eventos 
                         WHERE Fecha_evento >= '$hoy' 
                           AND (Estado IS NULL OR Estado NOT IN ('Cancelado'))
                         ORDER BY Fecha_evento ASC, Hora_inicio ASC 
                         LIMIT 6");
    $proximos_eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $proximos_eventos = [];
}

// ========================================
// 2. ÚLTIMAS FOTOS PARA GALERÍA
// ========================================
try {
    $fotos = $pdo->query("
        SELECT i.ruta, i.nombre_imagen, e.Nombre_evento, i.fecha_subida
        FROM imagenes i
        LEFT JOIN eventos e ON i.ID_evento = e.ID_evento
        ORDER BY i.fecha_subida DESC
        LIMIT 20
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
    <meta name="description" content="Portal oficial de la Junta de Vecinos Villa La Flecha - Coihueco">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 30%, #0891b2 60%, #0ea5e9 100%); }
        .card { transition: all 0.4s ease; }
        .card:hover { transform: translateY(-12px); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.4); }
        .pulse { animation: pulse 3s infinite; }
        @keyframes pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        .glass { background: rgba(255,255,255,0.1); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.2); }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">

    <!-- SECCIÓN GRANDE DE PRÓXIMOS EVENTOS -->
    <section class="py-20 px-6">
        <div class="container mx-auto max-w-7xl">
            <h2 class="text-6xl md:text-8xl font-black text-center text-white mb-16 drop-shadow-lg">
                PRÓXIMOS EVENTOS
            </h2>

            <?php if (empty($proximos_eventos)): ?>
                <div class="text-center glass p-12 rounded-3xl">
                    <p class="text-4xl font-bold text-white">No hay eventos próximos por ahora</p>
                    <p class="text-2xl mt-4 opacity-80">¡Vuelve pronto!</p>
                </div>
            <?php else: ?>
                <div class="grid gap-10 md:grid-cols-2 xl:grid-cols-3">
                    <?php foreach ($proximos_eventos as $e):
                        $foto_stmt = $pdo->prepare("SELECT ruta FROM imagenes WHERE ID_evento = ? LIMIT 1");
                        $foto_stmt->execute([$e['ID_evento']]);
                        $ruta_foto = $foto_stmt->fetchColumn();
                        $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
                    ?>
                        <div class="glass rounded-3xl overflow-hidden shadow-2xl hover:scale-105 transition-all duration-300">
                            <?php if ($ruta_foto): ?>
                                <img src="<?= htmlspecialchars($ruta_foto) ?>" class="w-full h-64 object-cover">
                            <?php else: ?>
                                <div class="bg-gray-700 h-64 flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-9xl text-gray-500 opacity-50"></i>
                                </div>
                            <?php endif; ?>

                            <div class="p-8 text-white">
                                <h3 class="text-3xl font-black mb-3"><?= htmlspecialchars($e['Nombre_evento']) ?></h3>
                                <p class="text-xl opacity-90 mb-4">
                                    <i class="fas fa-calendar-day mr-2"></i>
                                    <?= date('d', strtotime($e['Fecha_evento'])) ?> de <?= $meses[date('n', strtotime($e['Fecha_evento']))-1] ?> del <?= date('Y', strtotime($e['Fecha_evento'])) ?>
                                </p>
                                <p class="text-xl opacity-90 mb-4">
                                    <i class="fas fa-clock mr-2"></i>
                                    <?= substr($e['Hora_inicio'],0,5) ?> - <?= substr($e['Hora_fin'],0,5) ?> hrs
                                </p>
                                <?php if (!empty($e['Descripción'])): ?>
                                    <p class="text-lg opacity-80 mb-6 line-clamp-3">
                                        <?= nl2br(htmlspecialchars($e['Descripción'])) ?>
                                    </p>
                                <?php endif; ?>
                                <span class="inline-block px-4 py-2 rounded-full text-sm font-bold <?= $e['Estado']=='En progreso' ? 'bg-orange-500' : 'bg-emerald-500' ?>">
                                    <?= $e['Estado'] ?: 'Agendado' ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-12">
                    <a href="noticias.php" class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white font-black text-3xl py-6 px-20 rounded-3xl shadow-2xl hover:scale-110 transition">
                        VER TODOS LOS EVENTOS
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CONTENIDO PRINCIPAL (login + tarjetas) -->
    <div class="container mx-auto px-4 py-8 max-w-7xl">

        <header class="text-center mb-12">
            <div class="inline-flex items-center gap-6 bg-white/10 glass rounded-full px-10 py-6 mb-8 shadow-2xl">
                <i class="fas fa-home text-6xl text-cyan-300"></i>
                <div>
                    <h1 class="text-5xl md:text-7xl font-black text-white drop-shadow-lg">JJVV LA FLECHA</h1>
                    <p class="text-2xl text-cyan-200 font-bold">Coihueco • Región de Ñuble</p>
                </div>
            </div>
        </header>

        <div class="grid lg:grid-cols-2 gap-10">

            <!-- LOGIN -->
            <div class="order-2 lg:order-1">
                <?php include 'login_form.php'; ?>
            </div>

            <!-- TARJETAS DERECHA -->
            <div class="space-y-10 order-1 lg:order-2">

                <!-- Próximas actividades (lista pequeña) -->
                <section class="glass rounded-3xl p-8 card">
                    <h2 class="text-4xl font-black mb-8 flex items-center gap-4">
                        <i class="fas fa-calendar-star text-yellow-400 text-5xl"></i> Próximas Actividades
                    </h2>
                    <?php if (empty($proximos_eventos)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-calendar-times text-8xl text-gray-400 mb-6"></i>
                            <p class="text-2xl text-gray-300">No hay eventos próximos</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-5">
                            <?php foreach ($proximos_eventos as $i => $e): ?>
                                <div class="bg-white/10 glass rounded-2xl p-6 flex items-center gap-6 <?= $i===0 ? 'ring-4 ring-yellow-400 shadow-2xl' : '' ?>">
                                    <div class="text-center bg-gradient-to-br from-pink-600 to-red-600 rounded-2xl p-5 text-white font-black text-4xl">
                                        <?= date('d', strtotime($e['Fecha_evento'])) ?>
                                        <div class="text-lg uppercase font-bold"><?= date('M', strtotime($e['Fecha_evento'])) ?></div>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-2xl font-black"><?= htmlspecialchars($e['Nombre_evento']) ?></h3>
                                        <p class="text-lg opacity-90 mt-2">
                                            <?= date('l d \d\e F \d\e Y', strtotime($e['Fecha_evento'])) ?>
                                        </p>
                                        <?php if($e['Descripción']): ?>
                                            <p class="mt-3 text-cyan-200"><?= htmlspecialchars(substr($e['Descripción'], 0, 100)) ?>...</p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($i===0): ?>
                                        <span class="bg-yellow-400 text-black px-6 py-3 rounded-full text-xl font-black pulse">PRÓXIMO</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- GALERÍA -->
                <section class="glass rounded-3xl p-8 card">
                    <h2 class="text-4xl font-black mb-8 flex items-center gap-4">
                        <i class="fas fa-images text-pink-400 text-5xl"></i> Galería de la Comunidad
                    </h2>
                    <?php if (empty($fotos)): ?>
                        <p class="text-center text-xl text-gray-300 py-10">Pronto subiremos las primeras fotos</p>
                    <?php else: ?>
                        <div class="grid grid-cols-3 md:grid-cols-5 gap-4">
                            <?php foreach($fotos as $f): ?>
                                <div class="group relative overflow-hidden rounded-2xl shadow-xl">
                                    <img src="<?= htmlspecialchars($f['ruta']) ?>" class="w-full h-40 object-cover transform group-hover:scale-110 transition duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition flex items-end p-4">
                                        <div class="text-white text-sm">
                                            <p class="font-bold"><?= htmlspecialchars(basename($f['ruta'])) ?></p>
                                            <?php if($f['Nombre_evento']): ?>
                                                <p class="text-xs opacity-80"><?= htmlspecialchars($f['Nombre_evento']) ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-8">
                            <a href="galeria_publica.php" class="inline-block bg-gradient-to-r from-pink-600 to-purple-600 text-white font-black text-2xl py-6 px-16 rounded-3xl shadow-2xl hover:scale-110 transition">
                                VER TODAS LAS FOTOS
                            </a>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- RESERVA DE SEDE -->
                <section class="bg-gradient-to-r from-purple-800/50 to-pink-800/50 glass rounded-3xl p-8 border border-purple-400/50 card">
                    <h2 class="text-4xl font-black mb-8 flex items-center gap-4">
                        <i class="fas fa-key text-purple-300 text-5xl"></i> Reserva de Sede Social
                    </h2>
                    <form method="POST" action="reservar_sede.php" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <input type="date" name="fecha" required min="<?= date('Y-m-d') ?>" class="px-6 py-5 rounded-2xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none text-xl">
                            <input type="text" name="nombre" required placeholder="Tu nombre completo" class="px-6 py-5 rounded-2xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none text-xl">
                        </div>
                        <textarea name="motivo" rows="4" required placeholder="Motivo del evento..." class="w-full px-6 py-5 rounded-2xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none text-xl"></textarea>
                        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-400 hover:to-purple-500 text-white font-black text-3xl py-6 rounded-3xl shadow-2xl transform hover:scale-105 transition">
                            ENVIAR SOLICITUD
                        </button>
                    </form>
                </section>

                <!-- CONTACTO RÁPIDO (CORREO Y FACEBOOK) -->
                <div class="grid md:grid-cols-2 gap-6">
                    <a href="mailto:juntalaflecha@gmail.com" class="bg-gradient-to-r from-indigo-600 to-purple-700 glass rounded-3xl p-8 text-center card flex items-center justify-center gap-6 pulse">
                        <i class="fas fa-envelope text-6xl"></i>
                        <div>
                            <div class="text-xl opacity-90">Correo</div>
                            <div class="text-2xl font-black">juntalaflecha@gmail.com</div>
                        </div>
                    </a>
                    <a href="https://www.facebook.com/profile.php?id=100091532520182" target="_blank" class="bg-gradient-to-r from-blue-700 to-blue-800 glass rounded-3xl p-8 text-center card flex items-center justify-center gap-6">
                        <i class="fab fa-facebook text-6xl"></i>
                        <div>
                            <div class="text-xl">Síguenos en</div>
                            <div class="text-3xl font-black">Facebook</div>
                        </div>
                    </a>
                </div>

            </div>
        </div>

        <!-- FOOTER -->
        <footer class="text-center mt-20 py-10 border-t border-white/30">
            <p class="text-xl opacity-90">
                © <?= date('Y') ?> Junta de Vecinos La Flecha • Coihueco • Todos los derechos reservados
            </p>
        </footer>
    </div>
</body>
</html>