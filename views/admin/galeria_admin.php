<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$mensaje = '';

// ============================================
// SUBIR O EDITAR FOTO
// ============================================
if ($_POST) {
    $titulo     = trim($_POST['titulo'] ?? '');
    $evento_id  = !empty($_POST['evento_id']) ? (int)$_POST['evento_id'] : null;
    $id_editar  = !empty($_POST['id']) ? (int)$_POST['id'] : null;

    if (empty($titulo)) {
        $mensaje = "El título es obligatorio";
    } elseif (!$id_editar && empty($_FILES['foto']['name'])) {
        $mensaje = "Debes seleccionar una foto";
    } else {
        try {
            if ($id_editar) {
                // Solo editar título y evento
                $sql = "UPDATE imagenes SET nombre_imagen = ?, ID_evento = ? WHERE ID_imagen = ?";
                $pdo->prepare($sql)->execute([$titulo, $evento_id, $id_editar]);
                $mensaje = "Foto actualizada correctamente";
            } else {
                // ✅ SUBIR A RAÍZ DEL PROYECTO
                $file = $_FILES['foto'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (!in_array($ext, $allowed)) {
                    $mensaje = "Formato no permitido. Usa JPG, PNG, GIF o WEBP";
                } elseif ($file['size'] > 10 * 1024 * 1024) {
                    $mensaje = "La foto es muy pesada (máx 10 MB)";
                } else {
                    // ✅ RUTA RAÍZ (igual que noticias.php)
                    $target_dir = "../../../imagenes_eventos/";
                    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

                    $nuevo_nombre = time() . "_" . rand(1000, 9999) . "." . $ext;
                    $ruta_final = $target_dir . $nuevo_nombre;

                    if (move_uploaded_file($file['tmp_name'], $ruta_final)) {
                        // ✅ GUARDAR SOLO NOMBRE ARCHIVO EN BD
                        $sql = "INSERT INTO imagenes (nombre_imagen, ruta, ID_evento, fecha_subida) 
                                VALUES (?, ?, ?, NOW())";
                        $pdo->prepare($sql)->execute([$titulo, $nuevo_nombre, $evento_id]);
                        $mensaje = "✅ Foto subida correctamente a RAÍZ";
                    } else {
                        $mensaje = "❌ Error al guardar la imagen";
                    }
                }
            }
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
        }
    }

    header("Location: galeria_admin.php?m=" . urlencode($mensaje));
    exit;
}

// ============================================
// ELIMINAR IMAGEN
// ============================================
if (isset($_GET['del']) && is_numeric($_GET['del'])) {
    $id = (int)$_GET['del'];

    try {
        $stmt = $pdo->prepare("SELECT ruta FROM imagenes WHERE ID_imagen = ?");
        $stmt->execute([$id]);
        $ruta = $stmt->fetchColumn();

        if ($ruta) {
            // ✅ ELIMINAR DE RAÍZ
            $ruta_completa = "../../../imagenes_eventos/" . $ruta;
            if (file_exists($ruta_completa)) {
                unlink($ruta_completa);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM imagenes WHERE ID_imagen = ?");
        $stmt->execute([$id]);

        $mensaje = "✅ Imagen eliminada correctamente";
    } catch (Exception $e) {
        $mensaje = "❌ Error al eliminar: " . $e->getMessage();
    }

    header("Location: galeria_admin.php?m=" . urlencode($mensaje));
    exit;
}

// ============================================
// CARGAR DATOS
// ============================================
$imagenes = $pdo->query("
    SELECT 
        i.ID_imagen AS ID_imagen,
        i.nombre_imagen AS nombre_imagen,
        i.ruta AS ruta,
        i.ID_evento AS ID_evento,
        e.Nombre_evento AS Nombre_evento
    FROM imagenes i
    LEFT JOIN eventos e ON i.ID_evento = e.ID_evento
    ORDER BY i.ID_imagen DESC
")->fetchAll(PDO::FETCH_ASSOC);

$eventos = $pdo->query("
    SELECT ID_evento, Nombre_evento 
    FROM eventos 
    ORDER BY Fecha_evento DESC
")->fetchAll(PDO::FETCH_ASSOC);

$nombre_usuario = $_SESSION['nombre'] ?? 'Administrador';
$mensaje = $_GET['m'] ?? '';

// ✅ DEBUG CARPETA
$dir_raiz = "../../../imagenes_eventos/";
$total_fotos = count(array_diff(scandir($dir_raiz), array('..', '.')));
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
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen">

<!-- BARRA SUPERIOR -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="dashboard.php" class="flex items-center gap-3 text-2xl font-black hover:scale-110 transition">
            <i class="fas fa-home"></i> Panel Principal
        </a>
        <div class="flex items-center gap-6">
            <span class="text-xl"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nombre_usuario) ?></span>
            <a href="../../controllers/logout.php" onclick="return confirm('¿Cerrar sesión?')"
               class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-bold text-xl transition hover:scale-105">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</div>
<div class="pt-24"></div>

<div class="container mx-auto px-6 py-10 max-w-7xl">

    <div class="text-center mb-12">
        <h1 class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-4">
            Galería de Fotos
        </h1>
        <p class="text-2xl text-gray-700">Junta de Vecinos Villa La Flecha - Coihueco</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="bg-<?= strpos($mensaje,'correctamente')!==false ? 'emerald' : 'red' ?>-100 border border-<?= strpos($mensaje,'correctamente')!==false ? 'emerald' : 'red' ?>-400 text-<?= strpos($mensaje,'correctamente')!==false ? 'emerald' : 'red' ?>-800 
                     px-8 py-5 rounded-3xl text-center text-2xl font-bold mb-8 shadow-lg">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- ✅ DEBUG CARPETA RAÍZ -->
    <div class="bg-emerald-50 border-2 border-emerald-300 rounded-2xl p-6 mb-8">
        <h3 class="font-bold text-lg mb-2 flex items-center"><i class="fas fa-folder-open text-emerald-600 mr-2"></i>CARPETA FOTOS:</h3>
        <p><strong>📁 Ruta:</strong> <code><?= realpath("../../../imagenes_eventos/") ?></code></p>
        <p><strong>✅ Total fotos:</strong> <span class="font-bold text-emerald-700"><?= $total_fotos ?></span></p>
        <p class="text-sm text-emerald-700 mt-2">✅ <strong>¡CONSISTENTE CON NOTICIAS.PHP!</strong></p>
    </div>

    <!-- BOTÓN SUBIR FOTO -->
    <div class="text-center mb-12">
        <button onclick="openModal()" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 
                 text-white font-black text-3xl py-8 px-24 rounded-3xl shadow-2xl transform hover:scale-110 transition">
            <i class="fas fa-camera mr-4"></i> SUBIR NUEVA FOTO
        </button>
    </div>

    <!-- GRID DE FOTOS -->
    <?php if (empty($imagenes)): ?>
        <p class="text-center text-4xl text-gray-600 font-bold py-20">Aún no hay fotos en la galería</p>
    <?php else: ?>
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <?php foreach ($imagenes as $img): ?>
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden transform hover:scale-105 transition-all duration-300 group">
                <!-- ✅ RUTA ABSOLUTA PARA MOSTRAR -->
                <img src="/../../..//imagenes_eventos/<?= htmlspecialchars($img['ruta']) ?>" 
                     class="w-full h-64 object-cover group-hover:brightness-110 transition-all duration-500"
                     onerror="console.log('❌ Error: ../../../imagenes_eventos/<?= $img['ruta'] ?>');
                              this.src='https://via.placeholder.com/400x300?text=Sin+imagen'"
                     alt="<?= htmlspecialchars($img['nombre_imagen']) ?>"
                     loading="lazy">
                
                <div class="p-6">
                    <h3 class="text-2xl font-black text-gray-800 mb-2 line-clamp-2">
                        <?= htmlspecialchars($img['nombre_imagen']) ?>
                    </h3>
                    <p class="text-lg text-gray-600 mb-4">
                        <i class="fas fa-calendar mr-2 text-emerald-600"></i>
                        <?= $img['Nombre_evento'] ? htmlspecialchars($img['Nombre_evento']) : 'Galería general' ?>
                    </p>
                    
                    <!-- DEBUG RUTA -->
                    <div class="mb-4 p-3 bg-gray-50 rounded-xl text-xs font-mono text-gray-600 border">
                        <strong>📁</strong> <code><?= htmlspecialchars($img['ruta']) ?></code>
                        <br><a href="/../../..//imagenes_eventos/<?= htmlspecialchars($img['ruta']) ?>" 
                               target="_blank" class="text-blue-600 hover:text-blue-800 text-xs">
                            🔗 Probar URL
                        </a>
                    </div>

                    <div class="flex gap-3">
                        <!-- EDITAR -->
                        <button onclick='edit(<?= json_encode($img, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'
                                class="flex-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 
                                       text-white font-bold py-4 rounded-xl transition transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-edit mr-2"></i> Editar
                        </button>

                        <!-- ELIMINAR -->
                        <a href="?del=<?= $img['ID_imagen'] ?>"
                           onclick="return confirm('¿Seguro que deseas eliminar esta foto?\n\n<?= htmlspecialchars($img['nombre_imagen']) ?>')"
                           class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-xl transition 
                                  transform hover:scale-105 flex items-center justify-center">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL -->
<div id="modal" class="fixed inset-0 bg-black/80 hidden flex items-center justify-center z-50" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-3xl shadow-3xl p-12 max-w-4xl w-full mx-6">
        <h2 class="text-6xl font-black text-center text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-12" id="modal-title">
            Subir Nueva Foto
        </h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-10">
            <input type="hidden" name="id" id="edit-id">
            <div class="grid md:grid-cols-2 gap-10">
                <div class="md:col-span-2">
                    <input type="text" name="titulo" id="titulo" required placeholder="Ej: Fiesta de Navidad, Clases de Zumba..."
                           class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center 
                                  focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="text-2xl font-bold text-gray-700 mb-4 block">Evento (opcional)</label>
                    <select name="evento_id" id="evento_id" class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-2xl 
                            focus:border-emerald-500 outline-none">
                        <option value="">-- Galería general --</option>
                        <?php foreach ($eventos as $e): ?>
                        <option value="<?= $e['ID_evento'] ?>"><?= htmlspecialchars($e['Nombre_evento']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="file-section">
                    <label class="text-2xl font-bold text-gray-700 mb-4 block">Seleccionar foto</label>
                    <p class="text-sm text-emerald-600 font-semibold mb-2">✅ Se guardará en: <code>/../../..//imagenes_eventos/</code></p>
                    <input type="file" name="foto" accept="image/*" id="file-input" class="hidden"
                           onchange="document.getElementById('file-name').textContent=this.files[0].name">
                    <button type="button" onclick="document.getElementById('file-input').click()"
                            class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 
                            text-white font-black text-3xl py-10 rounded-3xl">
                        <i class="fas fa-cloud-upload-alt mr-4"></i> Seleccionar Foto
                    </button>
                    <p class="text-center mt-4 text-xl text-gray-600" id="file-name">Ningún archivo seleccionado</p>
                </div>
            </div>
            <div class="flex gap-8 justify-center mt-10">
                <button type="submit" id="submit-btn" class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-black text-4xl 
                        py-10 px-40 rounded-3xl shadow-2xl transform hover:scale-110 transition">
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
    document.getElementById('file-section').style.display = 'block';
    document.getElementById('file-name').textContent = 'Ningún archivo seleccionado';
    document.getElementById('modal-title').textContent = 'Subir Nueva Foto';
    document.getElementById('submit-btn').textContent = 'SUBIR FOTO';
}

function edit(img) {
    openModal();
    document.getElementById('edit-id').value = img.ID_imagen;
    document.getElementById('titulo').value = img.nombre_imagen;
    document.getElementById('evento_id').value = img.ID_evento || '';
    document.getElementById('file-input').required = false;
    document.getElementById('file-section').style.display = 'none';
    document.getElementById('modal-title').textContent = 'Editar Foto';
    document.getElementById('submit-btn').textContent = 'GUARDAR CAMBIOS';
}
</script>
</body>
</html>