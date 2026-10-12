<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$nombre_usuario = $_SESSION['nombre'] ?? 'Administrador';
$rut_actual = $_SESSION['rut_usuario'] ?? '';
$mensaje = $_GET['m'] ?? '';

// === CARGAR USUARIOS ACTIVOS ===
$ruts_activos = $pdo->query("SELECT rut_usuario, nombre FROM usuario WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// ✅ RUTA RAÍZ DEL PROYECTO (para público Y admin)
$target_dir = "../../imagenes_eventos/";
if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

// === SUBIR FOTOS ===
if ($_POST && isset($_POST['guardar']) && !empty($_FILES['fotos']['name'][0])) {
    foreach ($_FILES['fotos']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['fotos']['error'][$key] === 0) {
            $ext = pathinfo($_FILES['fotos']['name'][$key], PATHINFO_EXTENSION);
            $filename = time() . "_" . rand(1000,9999) . "_" . $key . "." . strtolower($ext);
            $target = $target_dir . $filename;
            if (move_uploaded_file($tmp_name, $target)) {
                $_SESSION['fotos_pendientes'][] = ['ruta' => $filename, 'nombre' => $_FILES['fotos']['name'][$key]];
            }
        }
    }
}

// === GUARDAR / EDITAR EVENTO ===
if ($_POST && isset($_POST['guardar'])) {
    $id = $_POST['id'] ?? null;
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $estado = $_POST['estado'] ?? 'Agendado';
    $rut_usuario = $_POST['rut_admin'] ?? $rut_actual;

    if (empty($titulo) || empty($fecha) || empty($hora_inicio) || empty($hora_fin)) {
        $mensaje = "Todos los campos obligatorios deben estar completos";
    } elseif ($hora_fin <= $hora_inicio) {
        $mensaje = "¡ERROR! La hora de fin ($hora_fin) debe ser DESPUÉS de la hora de inicio ($hora_inicio)";
    } else {
        try {
            if ($id) {
                $pdo->prepare("UPDATE eventos SET Nombre_evento=?, Descripción=?, Fecha_evento=?, Hora_inicio=?, Hora_fin=?, Estado=?, rut_usuario=? WHERE ID_evento=?")
                    ->execute([$titulo, $descripcion, $fecha, $hora_inicio, $hora_fin, $estado, $rut_usuario, $id]);
                $evento_id = $id;
                $mensaje = "Evento actualizado correctamente";
            } else {
                $stmt = $pdo->prepare("INSERT INTO eventos (Nombre_evento, Descripción, Fecha_evento, Hora_inicio, Hora_fin, Estado, rut_usuario) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$titulo, $descripcion, $fecha, $hora_inicio, $hora_fin, $estado, $rut_usuario]);
                $evento_id = $pdo->lastInsertId();
                $mensaje = "Evento creado y publicado";

                if (!empty($_SESSION['fotos_pendientes'])) {
                    $stmt = $pdo->prepare("INSERT INTO imagenes (ID_evento, nombre_imagen, ruta) VALUES (?, ?, ?)");
                    foreach ($_SESSION['fotos_pendientes'] as $foto) {
                        $stmt->execute([$evento_id, $foto['nombre'], $foto['ruta']]);
                    }
                    unset($_SESSION['fotos_pendientes']);
                }
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
    $fotos = $pdo->query("SELECT ruta FROM imagenes WHERE ID_evento = $id")->fetchAll();
    foreach ($fotos as $foto) {
        $ruta_completa = "../../imagenes_eventos/" . $foto['ruta'];
        if (file_exists($ruta_completa)) unlink($ruta_completa);
    }
    $pdo->prepare("DELETE FROM imagenes WHERE ID_evento = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM eventos WHERE ID_evento = ?")->execute([$id]);
    header("Location: noticias.php?m=Evento eliminado correctamente");
    exit;
}

if (isset($_GET['del_foto'])) {
    $id = (int)$_GET['del_foto'];
    $ruta = $pdo->query("SELECT ruta FROM imagenes WHERE ID_imagen = $id")->fetchColumn();
    if ($ruta) {
        $ruta_completa = "../../imagenes_eventos/" . $ruta;
        if (file_exists($ruta_completa)) unlink($ruta_completa);
    }
    $pdo->prepare("DELETE FROM imagenes WHERE ID_imagen = ?")->execute([$id]);
    header("Location: noticias.php?m=Foto eliminada");
    exit;
}

// === CARGAR EVENTOS ===
$eventos = $pdo->query("
    SELECT e.*, 
           (SELECT ruta FROM imagenes WHERE ID_evento = e.ID_evento LIMIT 1) as foto_principal,
           u.nombre as admin_nombre
    FROM eventos e 
    LEFT JOIN usuario u ON e.rut_usuario = u.rut_usuario
    ORDER BY Fecha_evento DESC, Hora_inicio DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Eventos • JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(135deg, #ecfdf5 0%, #ccfbf1 100%); }
        .hover-scale { transition: all 0.4s ease; }
        .hover-scale:hover { transform: translateY(-12px) scale(1.03); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
    </style>
</head>
<body class="min-h-screen">

<!-- BARRA SUPERIOR -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-5 flex justify-between items-center">
        <a href="dashboard.php" class="flex items-center gap-3 text-3xl font-black hover:scale-110 transition">
            <i class="fas fa-home"></i> Panel Admin
        </a>
        <div class="flex items-center gap-8">
            <span class="text-xl font-bold"><i class="fas fa-user-circle mr-2"></i> <?= htmlspecialchars($nombre_usuario) ?></span>
            <a href="../../controllers/logout.php" onclick="return confirm('¿Cerrar sesión?')"
               class="bg-red-600 hover:bg-red-700 px-8 py-4 rounded-full font-black transition hover:scale-110">
                <i class="fas fa-sign-out-alt mr-2"></i>SALIR
            </a>
        </div>
    </div>
</div>
<div class="pt-28"></div>

<div class="container mx-auto px-6 max-w-7xl py-12">
    <h1 class="text-8xl font-black text-center text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-12">
        GESTIÓN DE EVENTOS
    </h1>

    <?php if ($mensaje): ?>
        <div class="text-center mb-12">
            <div class="inline-block px-16 py-8 rounded-3xl text-3xl font-black shadow-2xl
                <?= strpos($mensaje,'correctamente')!==false ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white' ?>">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="text-center mb-16">
        <button onclick="openModal()" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-5xl py-10 px-40 rounded-full shadow-2xl transform hover:scale-110 transition">
            + NUEVO EVENTO
        </button>
    </div>

    <!-- ✅ VERIFICACIÓN CARPETA RAÍZ -->
    <div class="bg-emerald-50 border-2 border-emerald-300 rounded-2xl p-6 mb-8">
        <h3 class="font-bold text-lg mb-2 flex items-center"><i class="fas fa-check-circle text-emerald-600 mr-2"></i>CARPETA FOTOS:</h3>
        <?php 
        $dir_raiz = "../../imagenes_eventos/";
        $archivos = array_diff(scandir($dir_raiz), array('..', '.'));
        echo "<p><strong>📁 Ruta:</strong> <code>" . realpath($dir_raiz) . "</code></p>";
        echo "<p><strong>✅ Fotos:</strong> <span class='font-bold text-emerald-700'>" . count($archivos) . "</span></p>";
        if (count($archivos) > 0) {
            echo "<p class='text-sm text-emerald-700 mt-2'>✅ <strong>¡CARPETA CORRECTA!</strong> Listo para público y admin</p>";
        }
        ?>
    </div>

    <!-- LISTA DE EVENTOS -->
    <div class="grid gap-10 md:grid-cols-2 xl:grid-cols-3">
        <?php foreach ($eventos as $e): 
            $fotos = $pdo->query("SELECT * FROM imagenes WHERE ID_evento = {$e['ID_evento']}")->fetchAll();
        ?>
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden hover-scale border-8 border-emerald-100">
            <?php if ($e['foto_principal']): ?>
                <!-- ✅ RUTA ABSOLUTA PARA ADMIN Y PÚBLICO -->
                <img src="/juntavecinos/imagenes_eventos/<?= htmlspecialchars($e['foto_principal']) ?>" 
                     class="w-full h-64 object-cover" 
                     onerror="console.log('❌ Error cargando: /juntavecinos/imagenes_eventos/<?= $e['foto_principal'] ?>');
                              this.style.display='none'; 
                              this.nextElementSibling.style.display='flex'"
                     alt="<?= htmlspecialchars($e['Nombre_evento']) ?>"
                     loading="lazy">
                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 h-64 flex items-center justify-center hidden">
                    <i class="fas fa-image text-9xl text-emerald-300"></i>
                </div>
            <?php else: ?>
                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 h-64 flex items-center justify-center">
                    <i class="fas fa-image text-9xl text-emerald-300"></i>
                </div>
            <?php endif; ?>

            <div class="p-8">
                <h3 class="text-3xl font-black text-emerald-800 mb-3"><?= htmlspecialchars($e['Nombre_evento']) ?></h3>
                <p class="text-xl text-gray-700 mb-4">
                    <i class="fas fa-calendar text-emerald-600"></i> <?= date('d/m/Y', strtotime($e['Fecha_evento'])) ?>
                    <i class="fas fa-clock ml-4 text-teal-600"></i> <?= substr($e['Hora_inicio'],0,5) ?> - <?= substr($e['Hora_fin'],0,5) ?>
                </p>
                <p class="text-lg text-gray-600 mb-6"><?= nl2br(htmlspecialchars(substr($e['Descripción'] ?? '', 0, 120))) ?>...</p>
                
                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-gray-500 italic">
                        <i class="fas fa-user-tie mr-2 text-emerald-600"></i>
                        <?= htmlspecialchars($e['admin_nombre'] ?? 'Admin') ?>
                    </p>
                    <span class="bg-blue-100 text-blue-800 px-4 py-2 rounded-full text-sm font-bold">
                        📸 <?= count($fotos) ?> foto(s)
                    </span>
                </div>

                <div class="flex gap-4">
                    <button onclick='edit(<?= json_encode($e) ?>)' 
                            class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-5 rounded-xl text-xl transition">
                        <i class="fas fa-edit mr-2"></i>Editar
                    </button>
                    <button onclick="openUpload(<?= $e['ID_evento'] ?>)" 
                            class="flex-1 bg-teal-600 hover:bg-teal-700 text-white font-bold py-5 rounded-xl text-xl transition">
                        <i class="fas fa-camera mr-2"></i>+ Fotos
                    </button>
                    <a href="?del=<?= $e['ID_evento'] ?>" 
                       onclick="return confirm('¿ELIMINAR PERMANENTEMENTE este evento y TODAS sus fotos?\n\n<?= htmlspecialchars($e['Nombre_evento']) ?>')"
                       class="bg-red-600 hover:bg-red-700 text-white font-bold py-5 px-8 rounded-xl">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL CREAR/EDITAR -->
<div id="modal" class="fixed inset-0 bg-black/80 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-3xl p-12 max-w-6xl w-full mx-6 max-h-screen overflow-y-auto">
        <h2 class="text-7xl font-black text-center text-emerald-700 mb-10">NUEVO / EDITAR EVENTO</h2>
        <form method="POST" enctype="multipart/form-data" class="space-y-10" id="form-evento">
            <input type="hidden" name="id" id="edit-id">

            <input type="text" name="titulo" id="titulo" required placeholder="Título del evento *" 
                   class="w-full px-10 py-8 rounded-3xl border-4 border-emerald-300 text-4xl font-black text-center focus:border-emerald-600 outline-none">

            <textarea name="descripcion" id="descripcion" rows="5" placeholder="Descripción (opcional)"
                      class="w-full px-10 py-8 rounded-3xl border-4 border-emerald-300 text-2xl focus:border-emerald-600 outline-none"></textarea>

            <div class="grid md:grid-cols-2 gap-10">
                <div>
                    <label class="block text-2xl font-black text-emerald-700 mb-4">🖼️ Fotos (múltiples)</label>
                    <input type="file" name="fotos[]" multiple accept="image/*"
                           class="w-full px-10 py-8 rounded-3xl border-4 border-dashed border-emerald-400 text-xl file:mr-6 file:py-6 file:px-12 file:rounded-full file:border-0 file:text-xl file:font-black file:bg-gradient-to-r file:from-emerald-600 file:to-teal-600 file:text-white">
                    <p class="text-sm text-gray-500 mt-2">✅ Se guardan en: <code>/juntavecinos/imagenes_eventos/</code> (visible en público)</p>
                </div>
                <div>
                    <label class="block text-2xl font-black text-emerald-700 mb-4">👤 Administrador</label>
                    <select name="rut_admin" id="rut_admin" required class="w-full px-10 py-8 rounded-3xl border-4 border-emerald-300 text-2xl font-bold focus:border-emerald-600 outline-none">
                        <?php foreach ($ruts_activos as $u): ?>
                            <option value="<?= $u['rut_usuario'] ?>"><?= $u['rut_usuario'] ?> - <?= $u['nombre'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-10">
                <div>
                    <label class="block text-2xl font-black text-emerald-700 mb-4">📅 Fecha *</label>
                    <input type="date" name="fecha" id="fecha" required class="w-full px-10 py-8 rounded-3xl border-4 border-emerald-300 text-3xl text-center focus:border-emerald-600 outline-none">
                </div>
                <div>
                    <label class="block text-2xl font-black text-emerald-700 mb-4">🕐 Desde *</label>
                    <input type="time" name="hora_inicio" id="hora_inicio" required class="w-full px-10 py-8 rounded-3xl border-4 border-emerald-300 text-3xl text-center focus:border-emerald-600 outline-none">
                </div>
                <div>
                    <label class="block text-2xl font-black text-emerald-700 mb-4">🕛 Hasta *</label>
                    <input type="time" name="hora_fin" id="hora_fin" required class="w-full px-10 py-8 rounded-3xl border-4 border-emerald-300 text-3xl text-center focus:border-emerald-600 outline-none">
                </div>
            </div>

            <div class="flex gap-10 justify-center mt-12">
                <button type="submit" name="guardar" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-5xl py-10 px-40 rounded-full shadow-2xl transition hover:scale-110">
                    <i class="fas fa-save mr-4"></i>GUARDAR Y PUBLICAR
                </button>
                <button type="button" onclick="closeModal()" class="bg-gray-600 hover:bg-gray-700 text-white font-black text-5xl py-10 px-32 rounded-full">
                    <i class="fas fa-times mr-4"></i>Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('form-evento').addEventListener('submit', function(e) {
    const inicio = document.getElementById('hora_inicio').value;
    const fin = document.getElementById('hora_fin').value;
    if (fin && inicio && fin <= inicio) {
        alert(`❌ ¡ERROR DE HORARIO!\n\n🕐 Inicio: ${inicio}\n🕛 Fin: ${fin}\n\nLa hora de FIN debe ser DESPUÉS de INICIO`);
        e.preventDefault();
        return false;
    }
});

function openModal() {
    document.getElementById('modal').classList.remove('hidden');
    document.querySelector('#modal form').reset();
    document.getElementById('edit-id').value = '';
    document.getElementById('rut_admin').value = '<?= $rut_actual ?>';
    document.getElementById('fecha').valueAsDate = new Date();
}

function edit(e) {
    openModal();
    document.getElementById('edit-id').value = e.ID_evento;
    document.getElementById('titulo').value = e.Nombre_evento || '';
    document.getElementById('descripcion').value = e.Descripción || '';
    document.getElementById('fecha').value = e.Fecha_evento;
    document.getElementById('hora_inicio').value = e.Hora_inicio;
    document.getElementById('hora_fin').value = e.Hora_fin;
    document.getElementById('rut_admin').value = e.rut_usuario;
}

function closeModal() { document.getElementById('modal').classList.add('hidden'); }
</script>

</body>
</html>