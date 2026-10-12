<?php
require_once 'config.php';

// Eventos próximos (máximo 6)
$eventos = $pdo->query("SELECT * FROM eventos WHERE Fecha_evento >= CURDATE() AND (Estado IS NULL OR Estado = 'Activo') ORDER BY Fecha_evento ASC LIMIT 6")->fetchAll();

// Últimas 20 fotos para la galería pública
$fotos = $pdo->query("
    SELECT i.ruta, i.nombre_imagen, e.Nombre_evento, i.fecha_subida 
    FROM imagenes i 
    LEFT JOIN eventos e ON i.ID_evento = e.ID_evento 
    WHERE e.Estado = 'Activo' OR e.Estado IS NULL 
    ORDER BY i.fecha_subida DESC 
    LIMIT 20
")->fetchAll();

// === RESERVA DE SEDE (nuevo código) ===
$mensaje_reserva = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservar_sede'])) {
    try {
        $nombre      = trim($_POST['nombre'] ?? '');
        $rut         = trim($_POST['rut'] ?? '');
        $actividad   = trim($_POST['actividad'] ?? '');
        $fecha       = $_POST['fecha'] ?? '';
        $hora_inicio = $_POST['hora'] ?? '';
        $duracion    = (int)($_POST['duracion'] ?? 2);

        $errores = [];
        if (empty($nombre))      $errores[] = "Nombre obligatorio";
        if (empty($rut))         $errores[] = "RUT obligatorio";
        if (empty($actividad))   $errores[] = "Actividad obligatoria";
        if (empty($fecha) || $fecha < date('Y-m-d')) $errores[] = "Fecha inválida o pasada";
        if (empty($hora_inicio)) $errores[] = "Hora de inicio obligatoria";

        $hora_fin = date('H:i:s', strtotime("$hora_inicio + $duracion hours"));

        if (empty($errores)) {
            $check = $pdo->prepare("SELECT idReserva FROM reserva 
                                    WHERE fecha_reserva = ? 
                                      AND estado_reserva != 'Rechazada'
                                      AND hora_inicio < ? AND hora_fin > ?");
            $check->execute([$fecha, $hora_fin, $hora_inicio]);
            if ($check->fetch()) {
                $errores[] = "Ya existe una reserva en ese horario";
            }
        }

        if (empty($errores)) {
            $sql = "INSERT INTO reserva 
                    (nombre_reservante, RUT, tipo_reserva, fecha_reserva, hora_inicio, hora_fin, estado_reserva) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Pendiente')";
            $pdo->prepare($sql)->execute([$nombre, $rut, $actividad, $fecha, $hora_inicio, $hora_fin]);
            $mensaje_reserva = "¡Tu solicitud fue enviada con éxito! La directiva la revisará pronto.";
        } else {
            $mensaje_reserva = "Por favor corrige:<br>• " . implode("<br>• ", $errores);
        }
    } catch (Exception $e) {
        $mensaje_reserva = "Error del sistema. Inténtalo más tarde.";
    }
}

// Cargar calendario público
$reservas_publicas = $pdo->query("SELECT * FROM reserva WHERE estado_reserva != 'Rechazada' ORDER BY fecha_reserva ASC, hora_inicio ASC")->fetchAll();
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
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">

    <div class="container mx-auto px-4 py-8 max-w-7xl">

        <!-- HEADER -->
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

            <!-- CONTENIDO PRINCIPAL -->
            <div class="space-y-10 order-1 lg:order-2">

                <!-- PESTAÑAS -->
                <div class="flex gap-4 flex-wrap justify-center mb-6">
                    <button onclick="showTab('actividades')" class="tab-btn active px-8 py-4 rounded-full bg-white/20 glass font-black text-xl">Próximas Actividades</button>
                    <button onclick="showTab('galeria')" class="tab-btn px-8 py-4 rounded-full bg-white/20 glass font-black text-xl">Galería de Imágenes</button>
                    <button onclick="showTab('reserva')" class="tab-btn px-8 py-4 rounded-full bg-white/20 glass font-black text-xl">Reserva la Sede</button>
                </div>

                <!-- TAB ACTIVIDADES -->
                <div id="actividades" class="tab-content">
                    <section class="glass rounded-3xl p-8 card">
                        <h2 class="text-4xl font-black mb-8 flex items-center gap-4">
                            <i class="fas fa-calendar-star text-yellow-400 text-5xl"></i>
                            Próximas Actividades
                        </h2>
                        <?php if (empty($eventos)): ?>
                            <div class="text-center py-12">
                                <i class="fas fa-calendar-times text-8xl text-gray-400 mb-6"></i>
                                <p class="text-2xl text-gray-300">No hay eventos próximos</p>
                            </div>
                        <?php else: ?>
                            <div class="space-y-5">
                                <?php foreach ($eventos as $i => $e): ?>
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
                </div>

                <!-- TAB GALERÍA -->
                <div id="galeria" class="tab-content hidden">
                    <section class="glass rounded-3xl p-8 card">
                        <h2 class="text-4xl font-black mb-8 flex items-center gap-4">
                            <i class="fas fa-images text-pink-400 text-5xl"></i>
                            Galería de la Comunidad
                        </h2>
                        <?php if (empty($fotos)): ?>
                            <p class="text-center text-xl text-gray-300 py-10">Pronto subiremos las primeras fotos</p>
                        <?php else: ?>
                            <div class="grid grid-cols-3 md:grid-cols-5 gap-4">
                                <?php foreach($fotos as $f): ?>
                                    <div class="group relative overflow-hidden rounded-2xl shadow-xl">
                                        <img src="imagenes_eventos/<?= htmlspecialchars($f['ruta']) ?>" 
                                             class="w-full h-40 object-cover transform group-hover:scale-110 transition duration-500">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition flex items-end p-4">
                                            <div class="text-white text-sm">
                                                <p class="font-bold"><?= htmlspecialchars($f['nombre_imagen'] ?: 'Foto') ?></p>
                                                <p class="text-xs opacity-80"><?= htmlspecialchars($f['Nombre_evento']) ?></p>
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
                </div>

                <!-- TAB RESERVA LA SEDE -->
                <div id="reserva" class="tab-content hidden">
                    <section class="glass rounded-3xl p-8 card">
                        <h2 class="text-4xl font-black mb-8 flex items-center gap-4">
                            <i class="fas fa-key text-purple-300 text-5xl"></i>
                            Reserva la Sede Social
                        </h2>

                        <?php if ($mensaje_reserva): ?>
                            <div class="mb-8 p-6 rounded-2xl text-center text-xl font-bold <?= strpos($mensaje_reserva,'éxito')!==false ? 'bg-green-500/30 border-4 border-green-400' : 'bg-red-500/30 border-4 border-red-400' ?>">
                                <?= $mensaje_reserva ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="reservar_sede" value="1">
                            <div class="grid md:grid-cols-2 gap-6">
                                <input type="text" name="nombre" required placeholder="Nombre completo" class="px-6 py-5 rounded-2xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none text-xl">
                                <input type="text" name="rut" required placeholder="RUT (12.345.678-9)" class="px-6 py-5 rounded-2xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none text-xl">
                            </div>
                            <input type="text" name="actividad" required placeholder="Actividad (ej: bingo, cumpleaños...)" class="w-full px-6 py-5 rounded-2xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none text-xl">
                            <div class="grid md:grid-cols-2 gap-6">
                                <input type="date" name="fecha" required min="<?= date('Y-m-d') ?>" class="px-6 py-5 rounded-2xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none text-xl">
                                <input type="time" name="hora" required class="px-6 py-5 rounded-2xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none text-xl">
                            </div>
                            <select name="duracion" class="w-full px-6 py-5 rounded-2xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none text-xl">
                                <option value="1">1 hora</option>
                                <option value="2" selected>2 horas</option>
                                <option value="3">3 horas</option>
                                <option value="4">4 horas</option>
                                <option value="5">5 horas</option>
                            </select>
                            <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-purple-600 hover:from-cyan-400 hover:to-purple-500 text-white font-black text-3xl py-6 rounded-3xl shadow-2xl transform hover:scale-105 transition">
                                ENVIAR SOLICITUD DE RESERVA
                            </button>
                        </form>

                        <h3 class="text-3xl font-black mt-12 mb-6 text-cyan-200">Calendario de Reservas</h3>
                        <div class="bg-white/10 rounded-2xl p-6 max-h-96 overflow-y-auto">
                            <?php if (empty($reservas_publicas)): ?>
                                <p class="text-center text-xl text-gray-300">No hay reservas aún</p>
                            <?php else: ?>
                                <div class="space-y-3">
                                    <?php foreach ($reservas_publicas as $r): ?>
                                        <div class="bg-white/10 rounded-xl p-4 flex justify-between items-center">
                                            <div>
                                                <span class="font-black"><?= date('d/m/Y', strtotime($r['fecha_reserva'])) ?></span>
                                                <span class="mx-3">•</span>
                                                <span><?= substr($r['hora_inicio'],0,5) ?> → <?= substr($r['hora_fin'],0,5) ?></span>
                                                <span class="mx-3">•</span>
                                                <span class="font-bold"><?= htmlspecialchars($r['tipo_reserva']) ?></span>
                                            </div>
                                            <span class="px-4 py-2 rounded-full text-sm font-bold <?= $r['estado_reserva']==='Aprobada'?'bg-green-400/30':'bg-yellow-400/30' ?>">
                                                <?= $r['estado_reserva'] ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>

                <!-- CONTACTO RÁPIDO -->
                <div class="grid md:grid-cols-2 gap-6">
                    <a href="mailto:juntalaflecha@gmail.com" class="bg-gradient-to-r from-indigo-600 to-purple-700 glass rounded-3xl p-8 text-center card flex items-center justify-center gap-6 pulse">
                        <i class="fas fa-envelope text-6xl"></i>
                        <div>
                            <div class="text-xl opacity-90">Correo</div>
                            <div class="text-2xl font-black">juntalaflecha@gmail.com</div>
                        </div>
                    </a>
                    <a href="https://www.facebook.com/profile.php?id=100091532520182" target="_blank" 
                       class="bg-gradient-to-r from-blue-700 to-blue-800 glass rounded-3xl p-8 text-center card flex items-center justify-center gap-6">
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

    <script>
        function showTab(tab) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.getElementById(tab).classList.remove('hidden');
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }
        // Mostrar Actividades por defecto
        document.querySelector('.tab-btn').classList.add('active');
    </script>
</body>
</html>