<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$mensaje = $_GET['m'] ?? '';
$nombre_usuario = $_SESSION['nombre'] ?? 'Administrador';

// === SUBIR FOTO ===
if ($_POST && isset($_FILES['foto'])) {
    $target_dir = "imagenes_eventos/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file = $_FILES['foto'];
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = time() . "_" . rand(1000,9999) . "." . strtolower($ext);
    $target = $target_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        $stmt = $pdo->prepare("INSERT INTO imagenes (ID_evento, nombre_imagen, ruta) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['evento_id'], $file['name'], $target]);
        $mensaje = "Foto subida correctamente";
    } else {
        $mensaje = "Error al subir la foto";
    }
    header("Location: noticias.php?m=" . urlencode($mensaje));
    exit;
}

// === CREAR / EDITAR EVENTO (AHORA CON HORAS OBLIGATORIAS) ===
if ($_POST && isset($_POST['guardar'])) {
    $id          = $_POST['id'] ?? null;
    $titulo      = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha       = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_fin    = $_POST['hora_fin'] ?? '';
    $estado      = $_POST['estado'] ?? 'Agendado';
    $rut_admin   = $_SESSION['rut_usuario'] ?? '';

    // Validación completa
    if (empty($titulo) || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
        $mensaje = "Todos los campos con * son obligatorios (incluye horas)";
    } else {
        try {
            if ($id) {
                // EDITAR
                $sql = "UPDATE eventos SET 
                            Nombre_evento=?, Descripción=?, Fecha_evento=?, 
                            Hora_inicio=?, Hora_fin=?, Estado=?, RUT_admin=?
                        WHERE ID_evento=?";
                $pdo->prepare($sql)->execute([$titulo, $descripcion, $fecha, $hora_inicio, $hora_fin, $estado, $rut_admin, $id]);
                $mensaje = "Evento actualizado correctamente";
            } else {
                // CREAR NUEVO
                $sql = "INSERT INTO eventos 
                        (Nombre_evento, Descripción, Fecha_evento, Hora_inicio, Hora_fin, Estado, RUT_admin)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql)->execute([$titulo, $descripcion, $fecha, $hora_inicio, $hora_fin, $estado, $rut_admin]);
                $mensaje = "Evento creado y publicado en el inicio";
            }
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
        }
    }
    header("Location: noticias.php?m=" . urlencode($mensaje));
    exit;
}

// === ELIMINAR ===
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $pdo->prepare("DELETE FROM imagenes WHERE ID_evento = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM eventos WHERE ID_evento = ?")->execute([$id]);
    header("Location: noticias.php?m=Evento eliminado");
    exit;
}
if (isset($_GET['del_foto'])) {
    $id = (int)$_GET['del_foto'];
    $stmt = $pdo->prepare("SELECT ruta FROM imagenes WHERE ID_imagen = ?");
    $stmt->execute([$id]);
    $ruta = $stmt->fetchColumn();
    if ($ruta && file_exists($ruta)) unlink($ruta);
    $pdo->prepare("DELETE FROM imagenes WHERE ID_imagen = ?")->execute([$id]);
    header("Location: noticias.php?m=Foto eliminada");
    exit;
}

// === CARGAR EVENTOS ===
$eventos = $pdo->query("SELECT * FROM eventos ORDER BY Fecha_evento DESC, Hora_inicio DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Eventos - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass { background: rgba(255,255,255,0.1); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.2); }
    </style>
</head>
<body class="min-h-screen text-white">

<!-- BARRA SUPERIOR -->
<div class="fixed top-0 left-0 right-0 glass shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="index.php" class="flex items-center gap-3 text-2xl font-black hover:scale-110 transition">
            <i class="fas fa-home"></i> Volver al Inicio
        </a>
        <div class="flex items-center gap-6">
            <span class="text-xl"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nombre_usuario) ?></span>
            <a href="?logout=1" class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-bold text-xl transition hover:scale-105">
                Cerrar Sesión
            </a>
        </div>
    </div>
</div>
<div class="pt-24"></div>

<div class="container mx-auto px-6 py-12 max-w-7xl">
    <h1 class="text-7xl font-black text-center mb-12 text-white drop-shadow-lg">GESTIÓN DE EVENTOS</h1>

    <?php if ($mensaje): ?>
        <div class="text-center mb-10 glass p-8 rounded-3xl text-3xl font-bold <?= strpos($mensaje,'correctamente')!==false || strpos($mensaje,'publicado')!==false ? 'bg-emerald-600' : 'bg-red-600' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="text-center mb-12">
        <button onclick="openModal()" class="bg-gradient-to-r from-cyan-500 to-purple-600 text-white font-black text-4xl py-8 px-32 rounded-3xl shadow-2xl transform hover:scale-110 transition">
            NUEVO EVENTO
        </button>
    </div>

    <div class="grid gap-10 lg:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($eventos as $e): 
            $stmt = $pdo->prepare("SELECT ruta FROM imagenes WHERE ID_evento = ? LIMIT 1");
            $stmt->execute([$e['ID_evento']]);
            $foto = $stmt->fetchColumn();
        ?>
        <div class="glass rounded-3xl overflow-hidden shadow-2xl transform hover:scale-105 transition-all">
            <?php if ($foto): ?>
                <img src="<?= htmlspecialchars($foto) ?>" class="w-full h-64 object-cover" alt="Foto">
            <?php else: ?>
                <div class="bg-gray-700 h-64 flex items-center justify-center text-6xl">
                    <i class="fas fa-image"></i>
                </div>
            <?php endif; ?>

            <div class="p-8">
                <h3 class="text-3xl font-black mb-3 text-white"><?= htmlspecialchars($e['Nombre_evento']) ?></h3>
                <p class="text-xl opacity-90 mb-4">
                    <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($e['Fecha_evento'])) ?>
                    <i class="fas fa-clock ml-4"></i> <?= substr($e['Hora_inicio'],0,5) ?> - <?= substr($e['Hora_fin'],0,5) ?>
                </p>
                <p class="mb-4">
                    <span class="px-4 py-2 rounded-full font-bold text-sm
                        <?= $e['Estado']=='Finalizado'?'bg-green-500':($e['Estado']=='Cancelado'?'bg-red-500':($e['Estado']=='En progreso'?'bg-orange-500':'bg-blue-500')) ?>">
                        <?= $e['Estado'] ?? 'Agendado' ?>
                    </span>
                </p>
                <p class="text-lg opacity-80 mb-6"><?= nl2br(htmlspecialchars(substr($e['Descripción']??'',0,120))) ?>...</p>

                <div class="flex gap-3">
                    <button onclick='edit(<?= json_encode($e) ?>)' class="flex-1 bg-blue-600 hover:bg-blue-700 py-4 rounded-xl font-bold text-xl text-white">
                        Editar
                    </button>
                    <button onclick="openUpload(<?= $e['ID_evento'] ?>)" class="flex-1 bg-green-600 hover:bg-green-700 py-4 rounded-xl font-bold text-xl text-white">
                        + Foto
                    </button>
                    <a href="?del=<?= $e['ID_evento'] ?>" onclick="return confirm('¿Eliminar este evento?')" 
                       class="bg-red-600 hover:bg-red-700 py-4 px-6 rounded-xl font-bold text-xl text-white">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL CON HORAS OBLIGATORIAS Y text-black -->
<div id="modal" class="fixed inset-0 bg-black/90 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl p-12 max-w-5xl w-full mx-6 max-h-screen overflow-y-auto text-gray-800">
        <h2 class="text-6xl font-black text-center text-purple-600 mb-10">Nuevo / Editar Evento</h2>
        <form method="POST" class="space-y-8">
            <input type="hidden" name="id" id="edit-id">
            
            <input type="text" name="titulo" id="titulo" required placeholder="Título del evento *" 
                   class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-3xl font-bold text-center focus:border-purple-600 outline-none text-black">

            <textarea name="descripcion" id="descripcion" rows="5" placeholder="Descripción (opcional)" 
                      class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-xl focus:border-purple-600 outline-none text-black"></textarea>

            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-xl font-bold mb-2 text-purple-600">Fecha *</label>
                    <input type="date" name="fecha" id="fecha" required 
                           class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-2xl text-center focus:border-purple-600 outline-none text-black">
                </div>
                <div>
                    <label class="block text-xl font-bold mb-2 text-purple-600">Hora inicio *</label>
                    <input type="time" name="hora_inicio" id="hora_inicio" required 
                           class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-2xl text-center focus:border-purple-600 outline-none text-black">
                </div>
                <div>
                    <label class="block text-xl font-bold mb-2 text-purple-600">Hora fin *</label>
                    <input type="time" name="hora_fin" id="hora_fin" required 
                           class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-2xl text-center focus:border-purple-600 outline-none text-black">
                </div>
                <div>
                    <label class="block text-xl font-bold mb-2 text-purple-600">Estado</label>
                    <select name="estado" id="estado" 
                            class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-2xl font-bold focus:border-purple-600 outline-none text-black">
                        <option value="Agendado">Agendado</option>
                        <option value="En progreso">En progreso</option>
                        <option value="Finalizado">Finalizado</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-8 justify-center mt-10">
                <button type="submit" name="guardar" class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-black text-3xl py-6 px-24 rounded-3xl shadow-2xl hover:scale-110 transition">
                    GUARDAR Y PUBLICAR
                </button>
                <button type="button" onclick="document.getElementById('modal').classList.add('hidden')" class="bg-gray-600 text-white font-black text-3xl py-6 px-20 rounded-3xl">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL SUBIR FOTO -->
<div id="upload-modal" class="fixed inset-0 bg-black/90 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl p-12 max-w-2xl w-full mx-6">
        <h2 class="text-5xl font-black text-center text-purple-600 mb-10">Subir Foto al Evento</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="evento_id" id="evento_id">
            <div class="border-4 border-dashed border-purple-400 rounded-3xl p-20 text-center">
                <input type="file" name="foto" accept="image/*" required class="block w-full text-2xl file:mr-6 file:py-6 file:px-12 file:rounded-full file:border-0 file:text-2xl file:font-black file:bg-gradient-to-r file:from-purple-600 file:to-pink-600 file:text-white">
            </div>
            <div class="flex gap-6 justify-center mt-10">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-black text-3xl py-6 px-20 rounded-3xl">SUBIR</button>
                <button type="button" onclick="document.getElementById('upload-modal').classList.add('hidden')" class="bg-gray-600 text-white font-black text-3xl py-6 px-16 rounded-3xl">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('edit-id').value = '';
    document.getElementById('titulo').value = '';
    document.getElementById('descripcion').value = '';
    document.getElementById('fecha').value = '';
    document.getElementById('hora_inicio').value = '';
    document.getElementById('hora_fin').value = '';
    document.getElementById('estado').value = 'Agendado';
}

function edit(e) {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('edit-id').value = e.ID_evento;
    document.getElementById('titulo').value = e.Nombre_evento;
    document.getElementById('descripcion').value = e.Descripción || '';
    document.getElementById('fecha').value = e.Fecha_evento;
    document.getElementById('hora_inicio').value = e.Hora_inicio;
    document.getElementById('hora_fin').value = e.Hora_fin;
    document.getElementById('estado').value = e.Estado || 'Agendado';
}

function openUpload(id) {
    document.getElementById('upload-modal').classList.remove('hidden');
    document.getElementById('evento_id').value = id;
}

document.querySelectorAll('#modal, #upload-modal').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); });
});
</script>
</body>
</html>