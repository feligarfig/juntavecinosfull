<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}


$mensaje = $_GET['m'] ?? '';
$nombre_usuario = $_SESSION['nombre'] ?? 'Administrador';

// === SUBIR FOTO ===
if ($_POST && isset($_FILES['foto'])) {
    $target_dir = "uploads/eventos/";
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

// === CREAR / EDITAR EVENTO ===
if ($_POST && isset($_POST['guardar'])) {
    $id          = $_POST['id'] ?? null;
    $titulo      = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $fecha       = $_POST['fecha'];

    // ESTADOS QUE TÚ QUIERES
    $estados_validos = ['Agendado', 'En progreso', 'Finalizado', 'Cancelado'];
    $estado = (isset($_POST['estado']) && in_array($_POST['estado'], $estados_validos)) ? $_POST['estado'] : 'Agendado';

    $rut_admin = $_SESSION['rut_usuario'];

    if (empty($titulo) || empty($fecha)) {
        $mensaje = "Título y fecha son obligatorios";
    } else {
        try {
            if ($id) {
                // EDITAR
                $sql = "UPDATE eventos SET 
                            Nombre_evento = ?, Descripción = ?, Fecha_evento = ?, 
                            Estado = ?, RUT_admin = ? 
                        WHERE ID_evento = ?";
                $pdo->prepare($sql)->execute([$titulo, $descripcion, $fecha, $estado, $rut_admin, $id]);
                $mensaje = "Evento actualizado correctamente";
            } else {
                // CREAR NUEVO → SIN ID_evento (es AUTO_INCREMENT)
                $sql = "INSERT INTO eventos 
                            (Nombre_evento, Descripción, Fecha_evento, Estado, RUT_admin) 
                        VALUES (?, ?, ?, ?, ?)";
                $pdo->prepare($sql)->execute([$titulo, $descripcion, $fecha, $estado, $rut_admin]);
                $mensaje = "Evento creado correctamente";
            }
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
        }
    }
    header("Location: noticias.php?m=" . urlencode($mensaje));
    exit;
}

// === ELIMINAR EVENTO ===
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    $pdo->prepare("DELETE FROM imagenes WHERE ID_evento = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM eventos WHERE ID_evento = ?")->execute([$id]);
    $mensaje = "Evento eliminado";
    header("Location: noticias.php?m=" . urlencode($mensaje));
    exit;
}

// === ELIMINAR FOTO ===
if (isset($_GET['del_foto'])) {
    $stmt = $pdo->prepare("SELECT ruta FROM imagenes WHERE ID_imagen = ?");
    $stmt->execute([$_GET['del_foto']]);
    $ruta = $stmt->fetchColumn();
    if ($ruta && file_exists($ruta)) unlink($ruta);

    $pdo->prepare("DELETE FROM imagenes WHERE ID_imagen = ?")->execute([$_GET['del_foto']]);
    $mensaje = "Foto eliminada";
    header("Location: noticias.php?m=" . urlencode($mensaje));
    exit;
}

// === CARGAR EVENTOS ===
$eventos = $pdo->query("SELECT * FROM eventos ORDER BY Fecha_evento DESC, ID_evento DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Noticias y Eventos - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>body{font-family:'Nunito',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-purple-50 to-pink-100 min-h-screen">

<!-- BARRA SUPERIOR -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-purple-700 to-pink-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="dashboard.php" class="flex items-center gap-3 text-2xl font-black hover:scale-110 transition">
            <i class="fas fa-home"></i> Panel Principal
        </a>
        <div class="flex items-center gap-6">
            <span class="text-xl"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nombre_usuario) ?></span>
            <a href="login.php?logout=1" class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-bold text-xl transition hover:scale-105">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</div>
<div class="pt-24"></div>

<div class="container mx-auto px-6 py-12 max-w-7xl">
    <div class="text-center mb-12">
        <h1 class="text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600 mb-4">
            Noticias y Eventos
        </h1>
    </div>

    <?php if ($mensaje): ?>
        <div class="text-center mb-10 bg-<?= strpos($mensaje,'correctamente')!==false ? 'emerald' : 'red' ?>-100 border border-<?= strpos($mensaje,'correctamente')!==false ? 'emerald' : 'red' ?>-400 text-<?= strpos($mensaje,'correctamente')!==false ? 'emerald' : 'red' ?>-800 px-12 py-6 rounded-3xl text-3xl font-bold">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="text-center mb-12">
        <button onclick="openModal()" class="bg-gradient-to-r from-purple-600 to-pink-600 text-white font-black text-4xl py-8 px-32 rounded-3xl shadow-2xl transform hover:scale-110 transition">
            NUEVO EVENTO
        </button>
    </div>

    <div class="grid gap-10 md:grid-cols-1 lg:grid-cols-2">
        <?php foreach ($eventos as $e): 
            $fotos = $pdo->prepare("SELECT * FROM imagenes WHERE ID_evento = ?")->execute([$e['ID_evento']]);
            $fotos = $pdo->query("SELECT * FROM imagenes WHERE ID_evento = " . $e['ID_evento'])->fetchAll();
        ?>
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden transform hover:scale-105 transition-all duration-300">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white p-8">
                <h3 class="text-4xl font-black"><?= htmlspecialchars($e['Nombre_evento']) ?></h3>
                <p class="text-xl mt-2 opacity-90">
                    <i class="fas fa-calendar-alt mr-2"></i> <?= date('d/m/Y', strtotime($e['Fecha_evento'])) ?>
                    <span class="ml-6 px-6 py-2 rounded-full font-black text-lg
                        <?= $e['Estado']=='Finalizado' ? 'bg-green-600' : 
                           ($e['Estado']=='Cancelado' ? 'bg-red-600' : 
                           ($e['Estado']=='En progreso' ? 'bg-orange-600' : 'bg-blue-600')) ?>">
                        <?= $e['Estado'] ?>
                    </span>
                </p>
            </div>
            <div class="p-10">
                <p class="text-xl text-gray-700 leading-relaxed"><?= nl2br(htmlspecialchars($e['Descripción'] ?: 'Sin descripción')) ?></p>

                <?php if ($fotos): ?>
                <div class="grid grid-cols-3 gap-4 mt-8">
                    <?php foreach ($fotos as $f): ?>
                    <div class="relative group">
                        <img src="<?= htmlspecialchars($f['ruta']) ?>" class="w-full h-32 object-cover rounded-2xl shadow-lg">
                        <a href="?del_foto=<?= $f['ID_imagen'] ?>" class="absolute top-2 right-2 bg-red-600 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition"
                           onclick="return confirm('¿Eliminar esta foto?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <div class="flex gap-4 mt-10">
                    <button onclick='edit(<?= json_encode($e, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-black py-5 rounded-2xl text-xl transition transform hover:scale-105">
                        Editar
                    </button>
                    <button onclick="openUpload(<?= $e['ID_evento'] ?>)" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-black py-5 rounded-2xl text-xl transition transform hover:scale-105">
                        + Foto
                    </button>
                    <a href="?del=<?= $e['ID_evento'] ?>" 
                       onclick="return confirm('¿Eliminar permanentemente este evento y todas sus fotos?')"
                       class="bg-red-600 hover:bg-red-700 text-white font-black py-5 px-8 rounded-2xl text-xl transition transform hover:scale-105">
                        Eliminar
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL CREAR/EDITAR -->
<div id="modal" class="fixed inset-0 bg-black/80 hidden flex items-center justify-center z-50" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-3xl shadow-3xl p-12 max-w-4xl w-full mx-6">
        <h2 class="text-6xl font-black text-center text-purple-600 mb-12">Nuevo Evento / Noticia</h2>
        <form method="POST" class="space-y-10">
            <input type="hidden" name="id" id="edit-id">
            <div class="grid md:grid-cols-2 gap-10">
                <div class="md:col-span-2">
                    <input type="text" name="titulo" id="titulo" required placeholder="Título del evento" 
                           class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-purple-500 outline-none">
                </div>
                <div class="md:col-span-2">
                    <textarea name="descripcion" id="descripcion" rows="5" placeholder="Descripción completa..." 
                              class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-2xl focus:border-purple-500 outline-none"></textarea>
                </div>
                <div>
                    <input type="date" name="fecha" id="fecha" required 
                           class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-purple-500 outline-none">
                </div>
                <div>
                    <select name="estado" id="estado" class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-3xl font-bold focus:border-purple-500 outline-none">
                        <option value="Agendado">Agendado</option>
                        <option value="En progreso">En progreso</option>
                        <option value="Finalizado">Finalizado</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-8 justify-center mt-10">
                <button type="submit" name="guardar" class="bg-gradient-to-r from-purple-600