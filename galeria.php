
<?php



$mensaje = $_GET['m'] ?? '';

// === ELIMINAR IMAGEN ===
if (isset($_GET['del'])) {
    $stmt = $pdo->prepare("SELECT ruta FROM imagenes WHERE ID_imagen = ?");
    $stmt->execute([$_GET['del']]);
$ruta = $stmt->fetchColumn();
    if ($ruta && file_exists($ruta)) unlink($ruta);

    $pdo->prepare("DELETE FROM imagenes WHERE ID_imagen = ?")->execute([$_GET['del']]);
    $mensaje = "Imagen eliminada";
    header("Location: galeria.php?m=" . urlencode($mensaje));
    exit;
}

// === CARGAR DATOS ===
$imagenes = $pdo->query("SELECT i.*, e.Nombre_evento FROM imagenes i LEFT JOIN eventos e ON i.ID_evento = e.ID_evento ORDER BY i.ID_imagen DESC")->fetchAll();
$eventos = $pdo->query("SELECT ID_evento, Nombre_evento FROM eventos WHERE Estado = 'Publicado' OR Estado IS NULL ORDER BY Fecha_evento DESC")->fetchAll();
$nombre_usuario = $_SESSION['nombre'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería de Fotos - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>body{font-family:'Nunito',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-purple-100 min-h-screen">

<!-- BARRA SUPERIOR -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-indigo-700 to-purple-800 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="dashboard.php" class="flex items-center gap-3 text-2xl font-black hover:scale-110 transition">
            <i class="fas fa-home"></i> Panel Principal
        </a>
        <div class="flex items-center gap-6">
            <span class="text-xl"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nombre_usuario) ?></span>
            <a href="login.php?logout=1" onclick="return confirm('¿Cerrar sesión?')" 
               class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-bold text-xl transition hover:scale-105">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</div>
<div class="pt-24"></div>

<div class="container mx-auto px-6 py-12 max-w-7xl">
    <div class="text-center mb-12">
        <h1 class="text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 mb-4">
            Galería de Fotos
        </h1>
        <p class="text-3xl text-gray-700 font-bold">Recuerdos de nuestra comunidad</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="text-center mb-10 bg-<?= strpos($mensaje,'correctamente')!==false ? 'emerald' : 'red' ?>-100 border border-<?= strpos($mensaje,'correctamente')!==false ? 'emerald' : 'red' ?>-400 text-<?= strpos($mensaje,'correctamente')!==false ? 'emerald' : 'red' ?>-800 px-12 py-6 rounded-3xl text-3xl font-bold">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="text-center mb-12">
        <button onclick="openModal()" class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-black text-4xl py-8 px-32 rounded-3xl shadow-2xl transform hover:scale-110 transition">
            <i class="fas fa-camera mr-4"></i> SUBIR NUEVA FOTO
        </button>
    </div>

    <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        <?php foreach ($imagenes as $img): ?>
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden transform hover:scale-105 transition-all duration-300">
            <img src="<?= htmlspecialchars($img['ruta']) ?>" class="w-full h-64 object-cover">
            <div class="p-6">
                <h3 class="text-2xl font-black text-gray-800 mb-2"><?= htmlspecialchars($img['nombre_imagen']) ?></h3>
                <p class="text-lg text-gray-600 mb-4">
                    <i class="fas fa-calendar mr-2"></i>
                    <?= $img['Nombre_evento'] ? htmlspecialchars($img['Nombre_evento']) : 'Galería general' ?>
                </p>
                <div class="flex gap-3">
                    <button onclick='edit(<?= json_encode($img) ?>)' 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition transform hover:scale-105">
                        <i class="fas fa-edit"></i> Editar título
                    </button>
                    <a href="?del=<?= $img['ID_imagen'] ?>" 
                       onclick="return confirm('¿Eliminar esta foto permanentemente?')"
                       class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-xl transition transform hover:scale-105">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL SUBIR / EDITAR -->
<div id="modal" class="fixed inset-0 bg-black/80 hidden flex items-center justify-center z-50" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-3xl shadow-3xl p-12 max-w-4xl w-full mx-6">
        <h2 class="text-6xl font-black text-center text-indigo-600 mb-12">Subir Nueva Foto</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-10">
            <input type="hidden" name="id" id="edit-id">
            <div class="grid md:grid-cols-2 gap-10">
                <div class="md:col-span-2">
                    <input type="text" name="titulo" id="titulo" required placeholder="Título de la foto (ej: Fiesta de Navidad 2025)"
                           class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="text-2xl font-bold text-gray-700 mb-4 block">Relacionar con evento (opcional)</label>
                    <select name="evento_id" id="evento_id" class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-2xl focus:border-indigo-500 outline-none">
                        <option value="">-- Sin evento (galería general) --</option>
                        <?php foreach ($eventos as $e): ?>
                        <option value="<?= $e['ID_evento'] ?>"><?= htmlspecialchars($e['Nombre_evento']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="text-2xl font-bold text-gray-700 mb-4 block">Seleccionar imagen</label>
                    <input type="file" name="foto" accept="image/*" required id="file-input" class="hidden"
                           onchange="document.getElementById('file-name').textContent = this.files[0].name">
                    <button type="button" onclick="document.getElementById('file-input').click()" 
                            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-black text-3xl py-10 rounded-3xl">
                        <i class="fas fa-cloud-upload-alt mr-4"></i> Seleccionar Foto
                    </button>
                    <p class="text-center mt-4 text-xl text-gray-600" id="file-name">Ningún archivo seleccionado</p>
                </div>
            </div>
            <div class="flex gap-8 justify-center mt-10">
                <button type="submit" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-black text-4xl py-10 px-40 rounded-3xl shadow-2xl transform hover:scale-110 transition">
                    SUBIR FOTO
                </button>
                <button type="button" onclick="document.getElementById('modal').classList.add('hidden')" 
                        class="bg-gray-500 text-white font-black text-4xl py-10 px-32 rounded-3xl">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('edit-id').value = '';
    document.getElementById('titulo').value = '';
    document.getElementById('evento_id').value = '';
    document.getElementById('file-input').required = true;
    document.getElementById('file-name').textContent = 'Ningún archivo seleccionado';
}

function edit(img) {
    openModal();
    document.getElementById('edit-id').value = img.ID_imagen;
    document.getElementById('titulo').value = img.nombre_imagen;
    document.getElementById('evento_id').value = img.ID_evento || '';
    document.getElementById('file-input').required = false;
    document.querySelector('h2').textContent = 'Editar Foto';
    document.querySelector('button[type="submit"]').textContent = 'GUARDAR CAMBIOS';
}
</script>
</body>
</html>