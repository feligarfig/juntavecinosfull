<?php
require_once 'config.php';

// === 1. TODOS LOS EVENTOS (pasados y futuros) ===
$eventos = $pdo->query("
    SELECT 'evento' as tipo, 
           Nombre_evento as titulo, 
           Descripción as detalle, 
           Fecha_evento as fecha, 
           Hora_inicio, 
           Hora_fin,
           NULL as tipo_reserva
    FROM eventos 
    ORDER BY Fecha_evento DESC
")->fetchAll();

// === 2. TODAS LAS RESERVAS APROBADAS (pasadas y futuras) ===
$reservas = $pdo->query("
    SELECT 'reserva' as tipo, 
           tipo_reserva as titulo, 
           motivo as detalle,
           fecha_reserva as fecha, 
           hora_inicio, 
           hora_fin,
           tipo_reserva
    FROM reserva 
    WHERE estado_reserva = 'Aprobada'
    ORDER BY fecha_reserva DESC, hora_inicio ASC
")->fetchAll();

// === 3. UNIR TODO Y ORDENAR CRONOLÓGICAMENTE ===
$avisos = array_merge($eventos, $reservas);
usort($avisos, function($a, $b) {
    if ($a['fecha'] === $b['fecha']) {
        return ($b['Hora_inicio'] ?? '00:00') <=> ($a['Hora_inicio'] ?? '00:00'); // más reciente arriba
    }
    return $b['fecha'] <=> $a['fecha']; // del más reciente al más antiguo
});

// === FUNCIÓN PARA MES EN ESPAÑOL ===
function mesEnEspañol($fecha) {
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    $mes = date('n', strtotime($fecha));
    return $meses[$mes];
}
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avisos y Historial • JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); }
        .hover-scale { transition: all 0.3s ease; }
        .hover-scale:hover { transform: translateY(-10px) scale(1.02); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
    </style>
</head>
<body class="min-h-screen">

    <!-- NAV -->
    <nav class="bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl sticky top-0 z-50">
        <div class="container mx-auto px-6 py-5 flex flex-wrap justify-between items-center">
            <a href="index.php" class="flex items-center gap-3 text-3xl font-black hover:scale-110 transition">
                <i class="fas fa-home"></i> JJVV La Flecha
            </a>
            <div class="flex gap-6 text-xl font-bold items-center mt-4 md:mt-0">
                <a href="index.php" class="hover:text-cyan-200">Inicio</a>
                <a href="avisos.php" class="bg-cyan-400 text-emerald-900 px-8 py-4 rounded-full font-black">Avisos</a>
                <a href="galeria_publica.php" class="hover:text-cyan-200">Galería</a>
                <a href="reservar_sede.php" class="bg-white/20 hover:bg-white/30 px-6 py-3 rounded-full">Reservar Sede</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-7xl py-20">
        <h1 class="text-8xl font-black text-center text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-16">
            AVISOS E HISTORIAL DE ACTIVIDADES
        </h1>

        <?php if (empty($avisos)): ?>
            <p class="text-center text-6xl text-gray-400 font-bold py-32">Aún no hay actividades registradas</p>
        <?php else: ?>
            <div class="space-y-16">
                <?php 
                $ultimo_mes = '';
                foreach ($avisos as $a): 
                    $fecha = $a['fecha'];
                    $mes_actual = mesEnEspañol($fecha) . ' ' . date('Y', strtotime($fecha));
                    
                    if ($mes_actual !== $ultimo_mes):
                        if ($ultimo_mes !== '') echo '</div></div>'; // cerrar mes anterior
                ?>
                        <div class="bg-white/90 rounded-3xl shadow-2xl overflow-hidden">
                            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8 text-center">
                                <h2 class="text-6xl font-black">
                                    <i class="fas fa-calendar-alt mr-4"></i>
                                    <?= $mes_actual ?>
                                </h2>
                            </div>
                            <div class="p-10 space-y-8">
                <?php 
                        $ultimo_mes = $mes_actual;
                    endif; 
                ?>

                    <!-- CADA ACTIVIDAD -->
                    <div class="bg-white rounded-3xl shadow-xl p-10 hover-scale border-l-8 
                        <?= $a['tipo']==='evento' ? 'border-emerald-500' : 'border-teal-500' ?>">
                        <div class="flex flex-wrap items-center justify-between gap-6">
                            <div class="flex-1">
                                <div class="flex items-center gap-4 mb-4">
                                    <p class="text-3xl font-black text-gray-600">
                                        <?= date('d', strtotime($fecha)) ?>
                                    </p>
                                    <div>
                                        <?php if ($a['tipo']==='evento'): ?>
                                            <h3 class="text-4xl font-black text-emerald-800">
                                                <i class="fas fa-star text-yellow-500 mr-3"></i>
                                                <?= htmlspecialchars($a['titulo']) ?>
                                            </h3>
                                        <?php else: ?>
                                            <h3 class="text-4xl font-black text-teal-800">
                                                <i class="fas fa-building mr-3"></i>
                                                <?= htmlspecialchars($a['titulo']) ?>
                                                <?php if ($a['tipo_reserva']==='Feria artesanal'): ?>
                                                    <span class="ml-4 inline-block px-6 py-2 bg-orange-600 text-white rounded-full text-xl font-bold">
                                                        FERIA ARTESANAL
                                                    </span>
                                                <?php endif; ?>
                                            </h3>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if ($a['detalle']): ?>
                                    <p class="text-xl text-gray-700 italic">"<?= htmlspecialchars($a['detalle']) ?>"</p>
                                <?php endif; ?>
                            </div>

                            <div class="text-right">
                                <p class="text-3xl font-black text-emerald-600">
                                    <i class="fas fa-clock"></i>
                                    <?= substr($a['Hora_inicio']??'00:00',0,5) ?>
                                    <?php if ($a['Hora_fin']??false): ?> - <?= substr($a['Hora_fin'],0,5) ?><?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php 
                endforeach; 
                if ($ultimo_mes !== '') echo '</div></div>'; // cerrar último mes
                ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Botón Inicio fijo -->
    <div class="fixed bottom-8 right-8 z-50">
        <a href="index.php" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-4xl py-8 px-16 rounded-full shadow-2xl transition hover:scale-110 flex items-center gap-4">
            <i class="fas fa-home"></i> INICIO
        </a>
    </div>
</body>
</html>