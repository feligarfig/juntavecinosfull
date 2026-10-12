<?php
require_once '_conexion.php';
session_start();


$mensaje = '';
$reservas = [];

try {
    $conn = Conexion::conectar();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['success'])) {
        $mensaje = htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8');
    }

    // === NUEVA RESERVA ===
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre       = trim($_POST['nombre'] ?? '');
        $rut          = trim($_POST['rut'] ?? '');
        $tipo_reserva = trim($_POST['actividad'] ?? '');
        $fecha        = $_POST['fecha'] ?? '';
        $hora_inicio  = $_POST['hora'] ?? '';
        $duracion     = (int)($_POST['duracion'] ?? 0);

        $errores = [];

        if (!$nombre)       $errores[] = "Nombre obligatorio";
        if (!$rut)          $errores[] = "RUT obligatorio";
        if (!$tipo_reserva) $errores[] = "Tipo de reserva obligatorio";
        if (!$fecha || $fecha < date('Y-m-d')) $errores[] = "Fecha inválida o pasada";
        if (!$hora_inicio)  $errores[] = "Hora de inicio obligatoria";
        if ($duracion < 1 || $duracion > 8) $errores[] = "Duración entre 1 y 8 horas";

        // Calcular hora_fin
        $hora_fin = date('H:i:s', strtotime("$hora_inicio + $duracion hours"));

        // Verificar solapamiento
        if (empty($errores)) {
            $sql_check = "SELECT idReserva FROM reserva
                          WHERE fecha_reserva = ? 
                            AND estado_reserva != 'Rechazada'
                            AND hora_inicio < ? 
                            AND hora_fin > ?";

            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->execute([$fecha, $hora_fin, $hora_inicio]);

            if ($stmt_check->fetch()) {
                $errores[] = "Ya existe una reserva en ese horario.";
            }
        }

        if (empty($errores)) {
            $sql = "INSERT INTO reserva
                    (nombre_reservante, RUT, tipo_reserva, fecha_reserva, hora_inicio, hora_fin, estado_reserva)
                    VALUES (?, ?, ?, ?, ?, ?, 'Pendiente')";

            $stmt = $conn->prepare($sql);
            $stmt->execute([$nombre, $rut, $tipo_reserva, $fecha, $hora_inicio, $hora_fin]);

            header("Location: reservas.php?success=" . urlencode("¡Reserva enviada con éxito! Será revisada por la directiva."));
            exit;
        } else {
            $mensaje = implode("<br>• ", $errores);
        }
    }

    // === LISTADO DE RESERVAS ===
    $sql = "SELECT idReserva, nombre_reservante, RUT, tipo_reserva, fecha_reserva, 
                   hora_inicio, hora_fin, estado_reserva 
            FROM reserva
            WHERE estado_reserva != 'Rechazada' 
            ORDER BY fecha_reserva DESC, hora_inicio ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $mensaje = "Error: " . $e->getMessage();
    error_log("Error reservas.php: " . $e->getMessage());
}

$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas Sede - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>body{font-family:'Nunito',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen">

<!-- BARRA SUPERIOR -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="dashboard.php" class="flex items-center gap-3 text-2xl font-black hover:scale-110 transition">
            <i class="fas fa-home"></i> Panel Principal
        </a>
        <div class="flex items-center gap-8">
            <span class="text-xl"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nombre_usuario) ?></span>
            <a href="?logout=1" onclick="return confirm('¿Cerrar sesión?')"
               class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-bold text-xl transition hover:scale-105">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</div>
<div class="pt-24"></div>

<div class="container mx-auto px-6 py-10 max-w-6xl">
    <div class="text-center mb-12">
        <h1 class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-4">
            Reservas de la Sede Social
        </h1>
        <p class="text-2xl text-gray-700">Junta de Vecinos Villa La Flecha - Coihueco</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="bg-<?= (strpos($mensaje,'éxito')!==false || strpos($mensaje,'enviada')!==false) ? 'emerald' : 'red' ?>-100 
                        border border-<?= (strpos($mensaje,'éxito')!==false || strpos($mensaje,'enviada')!==false) ? 'emerald' : 'red' ?>-400 
                        text-<?= (strpos($mensaje,'éxito')!==false || strpos($mensaje,'enviada')!==false) ? 'emerald' : 'red' ?>-800 
                        px-8 py-6 rounded-3xl text-center text-2xl font-bold mb-10 shadow-lg">
            <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <div class="text-center mb-12">
        <button onclick="openModal()" 
                class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 
                       text-white font-black text-3xl py-8 px-20 rounded-3xl shadow-2xl transform hover:scale-110 transition">
            + Nueva Reserva
        </button>
    </div>

    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8">
            <h2 class="text-4xl font-bold text-center">Calendario de Reservas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Fecha</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Horario</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Actividad</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Reservante</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">RUT</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($reservas)): ?>
                        <tr><td colspan="6" class="text-center py-20 text-gray-500 text-3xl">No hay reservas registradas</td></tr>
                    <?php else: foreach ($reservas as $r): ?>
                        <tr class="hover:bg-emerald-50 transition">
                            <td class="px-8 py-6 font-bold text-xl"><?= date('d/m/Y', strtotime($r['fecha_reserva'])) ?></td>
                            <td class="px-8 py-6 text-xl"><?= substr($r['hora_inicio'],0,5) ?> → <?= substr($r['hora_fin'],0,5) ?></td>
                            <td class="px-8 py-6 text-xl font-semibold"><?= htmlspecialchars($r['tipo_reserva']) ?></td>
                            <td class="px-8 py-6"><?= htmlspecialchars($r['nombre_reservante']) ?></td>
                            <td class="px-8 py-6 font-mono"><?= htmlspecialchars($r['RUT']) ?></td>
                            <td class="px-8 py-6">
                                <span class="px-6 py-3 rounded-full font-bold text-lg
                                    <?= $r['estado_reserva'] === 'Aprobada' ? 'bg-emerald-100 text-emerald-800' : 
                                        ($r['estado_reserva'] === 'Pendiente' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                                    <?= $r['estado_reserva'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-3xl shadow-3xl p-10 max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">
                Nueva Reserva
            </h2>
            <button onclick="closeModal()" class="text-5xl text-gray-500 hover:text-gray-800">×</button>
        </div>

        <form method="POST" class="grid md:grid-cols-2 gap-8">
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-3">Nombre Completo *</label>
                <input type="text" name="nombre" required class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-xl focus:border-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-3">RUT *</label>
                <input type="text" name="rut" required placeholder="12.345.678-9" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-xl focus:border-emerald-500 outline-none">
            </div>
            <div class="md:col-span-2">
                <label class="block text-2xl font-bold text-gray-700 mb-3">Tipo de Actividad *</label>
                <input type="text" name="actividad" required placeholder="Ej: Bingo, Reunión, Cumpleaños..." class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-xl focus:border-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-3">Fecha *</label>
                <input type="date" name="fecha" min="<?= date('Y-m-d') ?>" required class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-xl text-center focus:border-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-3">Hora Inicio *</label>
                <input type="time" name="hora" required class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-xl text-center focus:border-emerald-500 outline-none">
            </div>
            <div class="md:col-span-2">
                <label class="block text-2xl font-bold text-gray-700 mb-3">Duración</label>
                <select name="duracion" required class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-xl font-bold focus:border-emerald-500 outline-none">
                    <option value="1">1 hora</option>
                    <option value="2" selected>2 horas</option>
                    <option value="3">3 horas</option>
                    <option value="4">4 horas</option>
                    <option value="5">5 horas</option>
                </select>
            </div>

            <div class="md:col-span-2 flex gap-6 justify-center mt-6">
                <button type="submit" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-3xl py-8 px-20 rounded-3xl shadow-2xl transform hover:scale-110 transition">
                    Enviar Reserva
                </button>
                <button type="button" onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-black text-3xl py-8 px-16 rounded-3xl">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() { document.getElementById('modal').classList.remove('hidden'); }
function closeModal() { document.getElementById('modal').classList.add('hidden'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
</script>
</body>
</html>