<?php
require_once 'config.php';

$msg = '';
$error = '';

// === PROCESAR RESERVA PÚBLICA ===
if ($_POST && isset($_POST['reservar'])) {
    $nombre     = trim($_POST['nombre'] ?? '');
    $rut        = trim($_POST['rut'] ?? '');
    $telefono   = trim($_POST['telefono'] ?? '');
    $ecorreo    = trim($_POST['correo'] ?? '');
    $fecha      = $_POST['fecha_reserva'] ?? '';
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_fin   = $_POST['hora_fin'] ?? '';
    $tipo       = $_POST['tipo_reserva'] ?? '';
    $motivo     = trim($_POST['motivo'] ?? '');

    // === VALIDACIÓN ESPECIAL: FERIA ARTESANAL (máx. 10 por día) ===
    if ($tipo === "Feria artesanal") {
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) AS total 
            FROM reserva 
            WHERE fecha_reserva = ?
              AND tipo_reserva = 'Feria artesanal'
              AND estado_reserva != 'Rechazada'
        ");
        $countStmt->execute([$fecha]);
        $cantidad = $countStmt->fetchColumn();

        if ($cantidad >= 10) {
            $error = "Lo sentimos, ya se alcanzó el máximo de 10 reservas de Feria Artesanal para ese día.";
        }
    }

    if (!$error) {
        // === VERIFICAR CHOQUE DE HORARIOS ===
        $check = $pdo->prepare("
            SELECT idReserva FROM reserva
            WHERE fecha_reserva = ? 
              AND estado_reserva != 'Rechazada'
              AND (
                    (hora_inicio < ? AND hora_fin > ?) OR
                    (hora_inicio < ? AND hora_fin > ?) OR
                    (? < hora_fin AND ? > hora_inicio)
              )
        ");

        $check->execute([
            $fecha,
            $hora_fin, $hora_inicio,
            $hora_fin, $hora_inicio,
            $hora_inicio, $hora_fin
        ]);

        if ($check->fetch()) {
            $error = "Ese horario ya está reservado o en espera. Elige otro.";
        } else {
            // INSERTAR RESERVA
            $stmt = $pdo->prepare("
                INSERT INTO reserva 
                (fecha_reserva, tipo_reserva, estado_reserva, RUT, hora_inicio, hora_fin, nombre_reservante, correo, telefono, motivo)
                VALUES (?, ?, 'Pendiente', ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $fecha,
                $tipo,
                $rut,
                $hora_inicio,
                $hora_fin,
                $nombre,
                $ecorreo,
                $telefono,
                $motivo
            ]);

            $msg = "¡RESERVA ENVIADA CON ÉXITO! Queda pendiente de aprobación.";
        }
    }
}

// Próximas reservas aprobadas
$proximas = $pdo->query("
    SELECT fecha_reserva, hora_inicio, hora_fin, tipo_reserva, nombre_reservante 
    FROM reserva
    WHERE estado_reserva = 'Aprobada' 
      AND fecha_reserva >= CURDATE()
    ORDER BY fecha_reserva ASC, hora_inicio ASC 
    LIMIT 12
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Sede - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        body { 
            font-family: 'Nunito', sans-serif; 
            background: linear-gradient(135deg, #e2e8f0 0%, #f8fafc 100%);
        }
        .glass { 
            background: rgba(255,255,255,0.7); 
            backdrop-filter: blur(14px); 
            border: 1px solid rgba(0,0,0,0.1); 
        }
    </style>
</head>

<body class="min-h-screen text-gray-900">

<div class="container mx-auto px-6 py-16 max-w-6xl">

    <h1 class="text-7xl font-black text-center mb-8 drop-shadow-xl text-gray-800">RESERVAR SEDE COMUNAL</h1>
    <p class="text-2xl text-center mb-12 text-gray-700">Formulario oficial para solicitar uso de la sede</p>

    <?php if ($msg): ?>
        <div class="text-center mb-12 glass p-8 rounded-3xl text-3xl font-black bg-emerald-100 border border-emerald-300 text-emerald-900 shadow-xl">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="text-center mb-12 glass p-8 rounded-3xl text-3xl font-black bg-red-100 border border-red-300 text-red-900 shadow-xl">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="glass rounded-3xl p-10 shadow-2xl">
        <form method="POST" class="space-y-8">

            <div class="grid md:grid-cols-2 gap-8">
                <input type="text" name="nombre" required placeholder="Nombre completo *"
                       class="w-full px-6 py-4 rounded-2xl bg-white text-gray-900 placeholder-gray-500 text-xl font-bold text-center border border-gray-300">
                <input type="text" name="rut" required placeholder="RUT (ej: 12.345.678-9) *"
                       class="w-full px-6 py-4 rounded-2xl bg-white text-gray-900 placeholder-gray-500 text-xl font-bold text-center border border-gray-300">
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <input type="tel" name="telefono" required placeholder="Teléfono *"
                       class="w-full px-6 py-4 rounded-2xl bg-white text-gray-900 placeholder-gray-500 text-xl font-bold text-center border border-gray-300">
                <input type="email" name="correo" required placeholder="Correo electrónico *"
                       class="w-full px-6 py-4 rounded-2xl bg-white text-gray-900 placeholder-gray-500 text-xl font-bold text-center border border-gray-300">
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <label class="block text-xl font-bold mb-2 text-center">Fecha</label>
                    <input type="date" name="fecha_reserva" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                           class="w-full px-6 py-4 rounded-2xl bg-white text-gray-900 text-xl text-center border border-gray-300">
                </div>
                <div>
                    <label class="block text-xl font-bold mb-2 text-center">Desde</label>
                    <input type="time" name="hora_inicio" required
                           class="w-full px-6 py-4 rounded-2xl bg-white text-gray-900 text-xl text-center border border-gray-300">
                </div>
                <div>
                    <label class="block text-xl font-bold mb-2 text-center">Hasta</label>
                    <input type="time" name="hora_fin" required
                           class="w-full px-6 py-4 rounded-2xl bg-white text-gray-900 text-xl text-center border border-gray-300">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-xl font-bold mb-2 text-center">Tipo de reserva</label>
                    <select name="tipo_reserva" required
                            class="w-full px-6 py-4 rounded-2xl bg-white text-gray-900 text-xl text-center border border-gray-300">
                        <option value="">-- Selecciona --</option>
                        <option value="Reunión">Reunión de vecinos</option>
                        <option value="Feria artesanal">Feria artesanal</option>
                        <option value="Cumpleaños">Cumpleaños</option>
                        <option value="Taller">Taller o capacitación</option>
                        <option value="Actividad deportiva">Actividad deportiva</option>
                        <option value="Velorio">Velorio</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xl font-bold mb-2 text-center">Motivo / Detalle (opcional)</label>
                    <input type="text" name="motivo" placeholder="Ej: Cumpleaños de Mateo"
                           class="w-full px-6 py-4 rounded-2xl bg-white text-gray-900 placeholder-gray-500 text-lg text-center border border-gray-300">
                </div>
            </div>

            <!-- CONTADOR EN VIVO DE PUESTOS PARA FERIA ARTESANAL -->
            <div id="contador-puestos" class="mt-12 hidden"></div>

            <div class="text-center">
                <button type="submit" name="reservar"
                        class="bg-green-600 hover:bg-green-700 text-white font-black text-3xl py-6 px-24 rounded-3xl shadow-xl transition transform hover:scale-105">
                    ENVIAR RESERVA
                </button>
            </div>

        </form>
    </div>

    <!-- PRÓXIMAS RESERVAS -->
    <?php if ($proximas): ?>
        <div class="mt-20">
            <h2 class="text-5xl font-black text-center mb-10 text-gray-800">Próximas Reservas Confirmadas</h2>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($proximas as $r): ?>
                    <div class="glass rounded-3xl p-8 text-center shadow-xl">
                        <p class="text-3xl font-black"><?= date('d/m/Y', strtotime($r['fecha_reserva'])) ?></p>
                        <p class="text-2xl mt-3"><?= substr($r['hora_inicio'],0,5) ?> - <?= substr($r['hora_fin'],0,5) ?></p>
                        <p class="text-xl mt-3 text-gray-700"><?= htmlspecialchars($r['tipo_reserva']) ?></p>
                        <p class="text-lg mt-2 text-gray-600"><?= htmlspecialchars($r['nombre_reservante']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- BOTÓN VOLVER -->
<div class="fixed top-6 left-6 z-50">
    <a href="index.php" class="bg-white/80 hover:bg-white text-gray-900 px-8 py-4 rounded-full text-xl font-black shadow-lg transition transform hover:scale-110 flex items-center gap-3">
        INICIO
    </a>
</div>

<!-- JAVASCRIPT DEL CONTADOR EN VIVO -->
<script>
async function actualizarPuestosDisponibles() {
    const fecha = document.querySelector('[name="fecha_reserva"]').value;
    const tipoSelect = document.querySelector('[name="tipo_reserva"]');
    const contadorDiv = document.getElementById('contador-puestos');

    if (!fecha || tipoSelect.value !== 'Feria artesanal') {
        if (contadorDiv) contadorDiv.style.display = 'none';
        return;
    }

    try {
        const response = await fetch(`contar_feria.php?fecha=${fecha}`);
        const data = await response.json();
        const ocupados = data.ocupados || 0;
        const disponibles = 10 - ocupados;

        contadorDiv.style.display = 'block';
        contadorDiv.innerHTML = `
            <div class="text-center p-6 rounded-3xl text-4xl font-black shadow-2xl border-4
                ${disponibles > 3 ? 'bg-emerald-100 border-emerald-400 text-emerald-800' : 
                  disponibles > 0 ? 'bg-orange-100 border-orange-400 text-orange-800' : 
                  'bg-red-100 border-red-500 text-red-800'}">
                <i class="fas fa-store mr-4"></i>
                ${disponibles > 0 
                    ? `¡Quedan <span class="text-6xl">${disponibles}</span> de 10 puestos disponibles!` 
                    : 'LO SENTIMOS: No quedan puestos disponibles para esta fecha'}
            </div>
        `;

        const boton = document.querySelector('button[name="reservar"]');
        if (disponibles <= 0) {
            boton.disabled = true;
            boton.classList.add('opacity-50', 'cursor-not-allowed');
            boton.innerHTML = 'NO HAY PUESTOS DISPONIBLES';
        } else {
            boton.disabled = false;
            boton.classList.remove('opacity-50', 'cursor-not-allowed');
            boton.innerHTML = 'ENVIAR RESERVA';
        }

    } catch (e) {
        console.error("Error al contar puestos");
    }
}

// Ejecutar al cargar y al cambiar fecha o tipo
document.addEventListener('DOMContentLoaded', actualizarPuestosDisponibles);
document.querySelector('[name="fecha_reserva"]').addEventListener('change', actualizarPuestosDisponibles);
document.querySelector('[name="tipo_reserva"]').addEventListener('change', actualizarPuestosDisponibles);
</script>

</body>
</html>