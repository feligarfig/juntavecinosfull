<?php
require_once 'config.php';

// Eventos próximos (máximo 6)
$eventos = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM eventos WHERE fecha >= CURDATE() ORDER BY fecha ASC LIMIT 6");
    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $eventos = [];
}
?>
<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JJVV La Flecha • Villa La Flecha</title>
    <meta name="description" content="Junta de Vecinos Villa La Flecha - Portal oficial de la comunidad">
    
    <!-- Tailwind + Animations -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/upgrade-preview@0.2.0/dist/upgrade-preview.umd.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 30%, #0891b2 70%, #06b6d4 100%); }
        .card-hover { transition: all 0.3s ease; }
        .card-hover:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); }
        .pulse-wa { animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.05); } }
    </style>
</head>
<body class="gradient-bg min-h-screen text-white">

    <div class="container mx-auto px-4 py-8 max-w-7xl">

        <!-- Header con logo y título -->
        <header class="text-center mb-10 animate-fade-in">
            <div class="inline-flex items-center gap-4 bg-white/10 backdrop-blur-md rounded-full px-8 py-4 mb-6">
                <i class="fas fa-home text-4xl text-cyan-300"></i>
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold">JJVV La Flecha</h1>
                    <p class="text-lg text-cyan-200">Coihueco • Región de Ñuble</p>
                </div>
            </div>
        </header>

        <div class="grid lg:grid-cols-2 gap-8">

            <!-- LOGIN (izquierda) -->
            <div class="order-2 lg:order-1">
                <?php include 'login_form.php'; ?>
            </div>

            <!-- CONTENIDO PRINCIPAL (derecha) -->
            <div class="space-y-8 order-1 lg:order-2">

                <!-- Próximas Actividades -->
                <section class="bg-white/10 backdrop-blur-xl rounded-3xl p-8 card-hover border border-white/20">
                    <h2 class="text-3xl font-bold mb-6 flex items-center gap-3">
                        <i class="fas fa-calendar-star text-yellow-400 text-4xl"></i>
                        Próximas Actividades
                    </h2>
                    <?php if (empty($eventos)): ?>
                        <div class="text-center py-10">
                            <i class="fas fa-calendar-times text-6xl text-gray-400 mb-4"></i>
                            <p class="text-xl text-gray-300">No hay eventos próximos programados</p>
                        </div>
                    <?php else: ?>
                        <div class="grid gap-4">
                            <?php foreach ($eventos as $i => $e): ?>
                                <div class="bg-white/15 rounded-2xl p-5 flex items-center gap-4 hover:bg-white/25 transition <?= $i===0 ? 'ring-4 ring-yellow-400' : '' ?>">
                                    <div class="text-center bg-gradient-to-br from-red-600 to-pink-600 rounded-xl p-4 text-white font-bold">
                                        <div class="text-3xl"><?= date('d', strtotime($e['fecha'])) ?></div>
                                        <div class="text-sm uppercase"><?= date('M', strtotime($e['fecha'])) ?></div>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-bold text-lg"><?= htmlspecialchars($e['titulo']) ?></h3>
                                        <p class="text-sm opacity-90">
                                            <i class="far fa-clock"></i> <?= date('d/m/Y \a \l\a\s H:i', strtotime($e['fecha'])) ?>
                                            <br><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($e['lugar']) ?>
                                        </p>
                                    </div>
                                    <?php if($i===0): ?>
                                        <span class="bg-yellow-400 text-black px-3 py-1 rounded-full text-xs font-bold animate-pulse">PRÓXIMO</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Avisos Importantes -->
                <section class="bg-red-600/20 backdrop-blur-xl rounded-3xl p-8 border border-red-500/50 card-hover">
                    <h2 class="text-3xl font-bold mb-6 flex items-center gap-3">
                        <i class="fas fa-bullhorn text-red-400 text-4xl"></i>
                        Avisos Importantes
                    </h2>
                    <div class="space-y-4 text-lg">
                        <div class="bg-red-900/50 border-l-4 border-red-500 p-5 rounded-r-xl">
                            <strong class="text-red-300">Corte de Agua Programado</strong><br>
                            Jueves 20 de noviembre • 09:00 a 17:00 hrs
                        </div>
                        <div class="bg-orange-900/50 border-l-4 border-orange-500 p-5 rounded-r-xl">
                            <strong class="text-orange-300">Asamblea General Ordinaria</strong><br>
                            Sábado 30 de noviembre • 19:30 hrs • Salón comunitario
                        </div>
                        <div class="bg-green-900/50 border-l-4 border-green-500 p-5 rounded-r-xl">
                            <strong class="text-green-300">Gastos Comunes Noviembre</strong><br>
                            Ya disponible el detalle en el portal de socios
                        </div>
                    </div>
                </section>

                <!-- Reserva de Sede -->
                <section class="bg-gradient-to-r from-purple-700/50 to-pink-700/50 backdrop-blur-xl rounded-3xl p-8 border border-purple-400/30 card-hover">
                    <h2 class="text-3xl font-bold mb-6 flex items-center gap-3">
                        <i class="fas fa-key text-purple-300 text-4xl"></i>
                        Reserva de Sede Social
                    </h2>
                    <form method="POST" action="reservar_sede.php" class="space-y-5">
                        <div class="grid md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-bold mb-2">Fecha deseada</label>
                                <input type="date" name="fecha" required min="<?= date('Y-m-d') ?>" 
                                       class="w-full px-4 py-3 rounded-xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2">Tu nombre completo</label>
                                <input type="text" name="nombre" required placeholder="Juan Pérez"
                                       class="w-full px-4 py-3 rounded-xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2">Motivo del evento</label>
                            <textarea name="motivo" rows="3" required placeholder="Cumpleaños de la abuela, baby shower, reunión de apoderados..."
                                      class="w-full px-4 py-3 rounded-xl bg-white/20 border border-white/30 focus:border-cyan-400 focus:outline-none transition"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold text-xl py-5 rounded-2xl transition transform hover:scale-105 shadow-2xl">
                            <i class="fas fa-paper-plane mr-3"></i>
                            Enviar Solicitud de Reserva
                        </button>
                    </form>
                </section>

                <!-- WhatsApp + Redes -->
                <div class="grid md:grid-cols-2 gap-6">
                    <a href="https://wa.me/56912345678" target="_blank" 
                       class="pulse-wa bg-gradient-to-r from-green-500 to-green-600 hover:from-green-400 hover:to-green-500 text-white font-bold text-2xl py-6 rounded-3xl text-center shadow-2xl transform hover:scale-105 transition flex items-center justify-center gap-4">
                        <i class="fab fa-whatsapp text-5xl"></i>
                        <div>
                            <div class="text-sm">Grupo Oficial</div>
                            <div>WhatsApp Vecinos</div>
                        </div>
                    </a>

                    <a href="https://facebook.com/jjvvlaflecha" target="_blank"
                       class="bg-gradient-to-r from-blue-700 to-blue-800 hover:from-blue-600 hover:to-blue-700 text-white font-bold text-2xl py-6 rounded-3xl text-center shadow-2xl transform hover:scale-105 transition flex items-center justify-center gap-4">
                        <i class="fab fa-facebook text-5xl"></i>
                        <div>
                            <div class="text-sm">Síguenos en</div>
                            <div>Facebook</div>
                        </div>
                    </a>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <footer class="text-center mt-16 py-8 border-t border-white/20">
            <p class="text-sm opacity-80">
                © <?= date('Y') ?> Junta de Vecinos Villa La Flecha • Todos los derechos reservados
            </p>
        </footer>
    </div>

</body>
</html>