<?php

require_once 'includes/auth.php';
require_once 'config.php';
$mensaje = '';

// === GUARDAR O EDITAR ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id   = $_POST['id'] ?? null;
    $nombre = trim($_POST['nombre'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    $fecha  = $_POST['fecha'] ?? '';
    $estado = $_POST['estado'] ?? 'programado';

    if ($nombre && $desc && $fecha) {
        try {
            if ($id) {
                $pdo->prepare("UPDATE eventos SET Nombre_evento=?, Descripción=?, Fecha_evento=?, Estado=? WHERE ID_evento=?")
                    ->execute([$nombre, $desc, $fecha, $estado, $id]);
                $mensaje = "Evento actualizado con éxito";
            } else {
                $pdo->prepare("INSERT INTO eventos (Nombre_evento, Descripción, Fecha_evento, Estado) VALUES (?, ?, ?, ?)")
                    ->execute([$nombre, $desc, $fecha, $estado]);
                $mensaje = "_Evento creado y publicado en el inicio_";
            }
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
        }
    } else {
        $mensaje = "Completa todos los campos";
    }
    header("Location: eventos_admin.php?m=" . urlencode($mensaje));
    exit;
}

// === ELIMINAR ===
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM eventos WHERE ID_evento = ?")->execute([$_GET['delete']]);
    header("Location: eventos_admin.php?m=Evento eliminado");
    exit;
}

// === CARGAR EVENTOS ===
$eventos = $pdo->query("SELECT * FROM eventos ORDER BY Fecha_evento DESC")->fetchAll();
$mensaje = $_GET['m'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos • Panel Admin - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); }
        .hover-scale { transition: all 0.3s ease; }
        .hover-scale:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 20px 40px -10px rgba(0,0,0,0.15); }
    </style>
</head>
<body class="min-h-screen">

<!-- MENÚ FIJO SUPERIOR (coherente con el resto del sitio) -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-5 flex justify-between items-center">
        <div class="flex items-center space-x-10">
            <a href="dashboard.php" class="flex items-center gap-3 text-3xl font-black hover:scale-110 transition">
                <i class="fas fa-home"></i> JJVV La Flecha
            </a>
            <nav class="hidden md:flex space-x-8 text-xl font-bold">
                <a href="finanzas.php" class="hover:text-cyan-200 transition">Finanzas</a>
                <a href="pagos.php" class="hover:text-cyan-200 transition">Cobranza</a>
                <a href="eventos_admin.php" class="text-cyan-300 border-b-4 border-cyan-300">Eventos</a>
                <a href="reservas.php" class="hover:text-cyan-200 transition">Reservas</a>
                <a href="socios.php" class="hover:text-cyan-200 transition">Socios</a>
            </nav>
        </div>
        <div class="flex items-center space-x-6">
            <span class="text-xl font-semibold hidden sm:block">
                <i class="fas fa-user-circle mr-2"></i> <?= htmlspecialchars($_SESSION['nombre'] ?? 'Admin') ?>
            </span>
            <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-8 py-4 rounded-full font-black text-lg transition transform hover:scale-110">
                <i class="fas fa-sign-out-alt mr-2"></i> SALIR
            </a>
        </div>
    </div>
</div>
<div class="pt-28"></div>

<div class="container mx-auto px-6 max-w-7xl py-12">

    <h1 class="text-7xl font-black text-center text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-8">
        Gestión de Eventos
    </h1>

    <?php if ($mensaje): ?>
        <div class="text-center mb-10">
            <div class="inline-block <?= strpos($mensaje,'éxito')!==false || strpos($mensaje,'publicado')!==false ? 'bg-emerald-100 border-emerald-500 text-emerald-800' : 'bg-red-100 border-red-500 text-red-800' ?> 
                 border-4 px-12 py-6 rounded-3xl text-2xl font-bold shadow-lg">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center mb-12">
        <button onclick="openModal()" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-4xl py-8 px-32 rounded-3xl shadow-2xl transform hover:scale-110 transition">
            + NUEVO EVENTO
        </button>
    </div>

    <?php if (empty($eventos)): ?>
        <div class="text-center py-32">
            <p class="text-5xl text-gray-400 font-bold">Aún no hay eventos registrados</p>
            <p class="text-2xl text-gray-500 mt-4">¡Crea el primero!</p>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach ($eventos as $e): ?>
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden hover-scale">
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8">
                        <h3 class="text-3xl font-black"><?= htmlspecialchars($e['Nombre_evento']) ?></h3>
                        <p class="text-2xl mt-4 opacity-90">
                            <i class="far fa-calendar-alt mr-3"></i>
                            <?= date('d/m/Y', strtotime($e['Fecha_evento'])) ?>
                        </p>
                    </div>
                    <div class="p-8">
                        <p class="text-lg text-gray-700 leading-relaxed line-clamp-4">
                            <?= nl2br(htmlspecialchars($e['Descripción'])) ?>
                        </p>
                        <div class="mt-6">
                            <span class="inline-block px-6 py-3 rounded-full font-bold text-white
                                <?= $e['Estado']=='realizado' ? 'bg-emerald-600' : ($e['Estado']=='cancelado' ? 'bg-red-600' : 'bg-amber-500') ?>">
                                <?= ucfirst($e['Estado']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 flex justify-center gap-6">
                        <button onclick='edit(<?= json_encode($e) ?>)'
                                class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 px-12 rounded-xl transition transform hover:scale-105">
                            Editar
                        </button>
                        <a href="?delete=<?= $e['ID_evento'] ?>"
                           onclick="return confirm('¿Seguro que quieres eliminar este evento?')"
                           class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-12 rounded-xl transition transform hover:scale-105">
                            Eliminar
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL -->
<div id="modal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-3xl shadow-3xl p-12 max-w-4xl w-full mx-6 max-h-screen overflow-y-auto">
        <h2 class="text-6xl font-black text-center text-emerald-600 mb-10">Nuevo / Editar Evento</h2>
        <form method="POST" class="space-y-8">
            <input type="hidden" name="id" id="id">
            
            <input type="text" name="nombre" id="nombre" placeholder="Título del evento *" required
                   class="w-full px-8 py-6 border-4 border-emerald-300 rounded-3xl text-2xl font-bold focus:border-emerald-600 outline-none">

            <textarea name="descripcion" id="descripcion" rows="6" placeholder="Descripción completa del evento *" required
                      class="w-full px-8 py-6 border-4 border-emerald-300 rounded-3xl text-xl focus:border-emerald-600 outline-none"></textarea>

            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-xl font-bold text-emerald-700 mb-3">Fecha del evento *</label>
                    <input type="date" name="fecha" id="fecha" required
                           class="w-full px-8 py-6 border-4 border-emerald-300 rounded-3xl text-2xl focus:border-emerald-600 outline-none">
                </div>
                <div>
                    <label class="block text-xl font-bold text-emerald-700 mb-3">Estado</label>
                    <select name="estado" id="estado" class="w-full px-8 py-6 border-4 border-emerald-300 rounded-3xl text-2xl focus:border-emerald-600 outline-none">
                        <option value="programado">Programado</option>
                        <option value="realizado">Realizado</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-center gap-10 mt-10">
                <button type="submit" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-4xl py-8 px-32 rounded-3xl shadow-2xl transform hover:scale-110 transition">
                    GUARDAR Y PUBLICAR
                </button>
                <button type="button" onclick="document.getElementById('modal').classList.add('hidden')"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-black text-4xl py-8 px-20 rounded-3xl transition">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modal').classList.remove('hidden');
    document.querySelector('form').reset();
    document.getElementById('id').value = '';
    document.getElementById('fecha').valueAsDate = new Date();
    document.getElementById('estado').value = 'programado';
}
function edit(data) {
    openModal();
    document.getElementById('id').value = data.ID_evento;
    document.getElementById('nombre').value = data.Nombre_evento || '';
    document.getElementById('descripcion').value = data.Descripción || '';
    document.getElementById('fecha').value = data.Fecha_evento;
    document.getElementById('estado').value = data.Estado || 'programado';
}
</script>
</body>
</html>