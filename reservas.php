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

        // Solo continuamos si no hubo error en la validación anterior
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

                // === INSERTAR RESERVA ===
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


    // Validaciones
    if (empty($nombre) || empty($rut) || empty($telefono) || empty($ecorreo) || empty($fecha) || empty($hora_inicio) || empty($hora_fin) || empty($tipo)) {
        $error = "Todos los campos marcados con * son obligatorios";
    } elseif ($hora_fin <= $hora_inicio) {
        $error = "La hora de fin debe ser después de la hora de inicio";
    } elseif (!filter_var($ecorreo, FILTER_VALIDATE_EMAIL)) {
        $error = "Ingresa un correo electrónico válido";
    } else {
        // Verificar choque de horarios (solo reservas aprobadas o pendientes)
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
        $check->execute([$fecha, $hora_fin, $hora_inicio, $hora_fin, $hora_inicio, $hora_inicio, $hora_fin]);

        if ($check->fetch()) {
            $error = "Lo siento, ese horario ya está reservado o en espera. Elige otro.";
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
            $msg = "¡RESERVA ENVIADA CON ÉXITO! Queda pendiente de aprobación. Te avisaremos pronto.";
        }
    }


// Próximas reservas aprobadas (para mostrar al público)
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
    <style>
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass { background: rgba(255,255,255,0.15); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.3); }
    </style>
</head>
<body class="min-h-screen text-white">

<div class="container mx-auto px-6 py-16 max-w-6xl">
    <h1 class="text-8xl font-black text-center mb-8 drop-shadow-2xl">RESERVAR SEDE COMUNAL</h1>
    <p class="text-3xl text-center mb-16 opacity-90">Completa el formulario y te contactaremos para confirmar</p>

    <?php if ($msg): ?>
        <div class="text-center mb-12 glass p-12 rounded-3xl text-5xl font-black bg-emerald-600 shadow-2xl">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="text-center mb-12 glass p-12 rounded-3xl text-4xl font-black bg-red-600 shadow-2xl">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="glass rounded-3xl p-12 shadow-2xl">
        <form method="POST" class="space-y-10">
            <div class="grid md:grid-cols-2 gap-10">
                <input type="text" name="nombre" required placeholder="Nombre completo *" class="w-full px-8 py-6 rounded-2xl bg-white/20 text-white placeholder-white/70 text-2xl font-bold text-center focus:outline-none focus:ring-4 focus:ring-white/50">
                <input type="text" name="rut" required placeholder="RUT (ej: 12.345.678-9) *" class="w-full px-8 py-6 rounded-2xl bg-white/20 text-white placeholder-white/70 text-2xl font-bold text-center focus:outline-none focus:ring-4 focus:ring-white/50">
            </div>

            <div class="grid md:grid-cols-2 gap-10">
                <input type="tel" name="telefono" required placeholder="Teléfono *" class="w-full px-8 py-6 rounded-2xl bg-white/20 text-white placeholder-white/70 text-2xl font-bold text-center focus:outline-none focus:ring-4 focus:ring-white/50">
                <input type="email" name="correo" required placeholder="Correo electrónico *" class="w-full px-8 py-6 rounded-2xl bg-white/20 text-white placeholder-white/70 text-2xl font-bold text-center focus:outline-none focus:ring-4 focus:ring-white/50">
            </div>

            <div class="grid md:grid-cols-3 gap-10">
                <div>
                    <label class="block text-2xl font-bold mb-3 text-center">Fecha</label>
                    <input type="date" name="fecha_reserva" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="w-full px-8 py-6 rounded-2xl bg-white/20 text-white text-2xl text-center focus:outline-none focus:ring-4 focus:ring-white/50">
                </div>
                <div>
                    <label class="block text-2xl font-bold mb-3 text-center">Desde</label>
                    <input type="time" name="hora_inicio" required class="w-full px-8 py-6 rounded-2xl bg-white/20 text-white text-2xl text-center focus:outline-none focus:ring-4 focus:ring-white/50">
                </div>
                <div>
                    <label class="block text-2xl font-bold mb-3 text-center">Hasta</label>
                    <input type="time" name="hora_fin" required class="w-full px-8 py-6 rounded-2xl bg-white/20 text-white text-2xl text-center focus:outline-none focus:ring-4 focus:ring-white/50">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-10">
                <div>
                    <label class="block text-2xl font-bold mb-3 text-center">Tipo de reserva</label>
                    <select name="tipo_reserva" required class="w-full px-8 py-6 rounded-2xl bg-white/20 text-black text-2xl text-center focus:outline-none focus:ring-4 focus:ring-white/50">
                        <option value="">-- Selecciona --</option>
                        <option value="Reunión">Reunión de vecinos</option>
                        <option value="Feria artesanal">Feria artesanal</option>
                        <option value="Cumpleaños">Cumpleaños</option>
                        <option value="Taller">Taller o capacitación</option>
                        <option value="Actividad deportiva">Actividad deportiva</option>
                        <option value="Velorio">Velorio</option>
                        <option value="Velorio">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-2xl font-bold mb-3 text-center">Motivo / Detalle (opcional)</label>
                    <input type="text" name="motivo" placeholder="Ej: Cumpleaños de Mateo" class="w-full px-8 py-6 rounded-2xl bg-white/20 text-white placeholder-white/70 text-xl text-center focus:outline-none focus:ring-4 focus:ring-white/50">
                </div>
            </div>

            <div class="text-center">
                <button type="submit" name="reservar" class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-black text-5xl py-10 px-40 rounded-3xl shadow-2xl transform hover:scale-110 transition">
                    ENVIAR RESERVA
                </button>
            </div>
        </form>
    </div>

    <!-- PRÓXIMAS RESERVAS CONFIRMADAS -->
    <?php if ($proximas): ?>
    <div class="mt-24">
        <h2 class="text-6xl font-black text-center mb-12">Próximas Reservas Confirmadas</h2>
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($proximas as $r): ?>
            <div class="glass rounded-3xl p-10 text-center shadow-2xl">
                <p class="text-4xl font-black"><?= date('d/m/Y', strtotime($r['fecha_reserva'])) ?></p>
                <p class="text-3xl mt-4"><i class="fas fa-clock"></i> <?= substr($r['hora_inicio'],0,5) ?> - <?= substr($r['hora_fin'],0,5) ?></p>
                <p class="text-2xl mt-4 opacity-90"><?= htmlspecialchars($r['tipo_reserva']) ?></p>
                <p class="text-xl mt-2 opacity-80"><?= htmlspecialchars($r['nombre_reservante']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>

<!-- BOTÓN VOLVER AL INICIO FIJO -->
<div class="fixed top-8 left-8 z-50">
    <a href="index.php" class="bg-white/20 hover:bg-white/30 backdrop-blur-lg px-10 py-6 rounded-full text-3xl font-black shadow-2xl transition transform hover:scale-110 flex items-center gap-4">
        <i class="fas fa-home text-4xl"></i>
        VOLVER AL INICIO
    </a>
</div>

</html>