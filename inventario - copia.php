<?php
require_once 'config.php';


$mensaje = '';

// ============================================
// CARGAR SOCIOS PARA EL COMBOBOX
// ============================================
$socios = $pdo->query("SELECT RUT, CONCAT(RUT, ' - ', Nombre, ' ', Apellido1) AS nombre_completo 
                       FROM socios ORDER BY Apellido1, Nombre")->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// AGREGAR / EDITAR MATERIAL
// ============================================
if ($_POST) {
    $nombre     = trim($_POST['nombre'] ?? '');
    $cantidad   = (int)($_POST['cantidad'] ?? 0);
    $estado     = trim($_POST['estado'] ?? '');
    $ubicacion  = trim($_POST['ubicacion'] ?? '');
    $historial  = trim($_POST['historial'] ?? '');
    $rut_usuario = trim($_POST['rut_usuario'] ?? '');
    $id_editar   = !empty($_POST['id']) ? (int)$_POST['id'] : null;

    if (empty($nombre) || empty($estado) || empty($ubicacion) || empty($rut_usuario)) {
        $mensaje = "Todos los campos son obligatorios (incluido el socio)";
    } else {
        try {
            if ($id_editar) {
                // EDITAR
                $sql = "UPDATE inventario_materiales SET 
                        Nombre_material = ?, Cantidad = ?, Estado = ?, Ubicacion = ?, Historial = ?, RUT_usuario = ?
                        WHERE ID_material = ?";
                $pdo->prepare($sql)->execute([$nombre, $cantidad, $estado, $ubicacion, $historial, $rut_usuario, $id_editar]);
                $mensaje = "Material actualizado correctamente";
            } else {
                // NUEVO
                $sql = "INSERT INTO inventario_materiales 
                        (RUT_usuario, Nombre_material, Cantidad, Estado, Ubicacion, Historial, Fecha_registro)
                        VALUES (?, ?, ?, ?, ?, ?, CURDATE())";
                $pdo->prepare($sql)->execute([$rut_usuario, $nombre, $cantidad, $estado, $ubicacion, $historial]);
                $mensaje = "Material agregado correctamente";
            }
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
        }
    }
    header("Location: inventario.php?m=" . urlencode($mensaje));
    exit;
}

// ============================================
// ELIMINAR MATERIAL
// ============================================
if (isset($_GET['del']) && is_numeric($_GET['del'])) {
    $id = (int)$_GET['del'];
    try {
        $pdo->prepare("DELETE FROM inventario_materiales WHERE ID_material = ?")->execute([$id]);
        $mensaje = "Material eliminado correctamente";
    } catch (Exception $e) {
        $mensaje = "Error al eliminar: " . $e->getMessage();
    }
    header("Location: inventario.php?m=" . urlencode($mensaje));
    exit;
}

// ============================================
// CARGAR MATERIALES (ahora con nombre del socio)
// ============================================
$materiales = $pdo->query("
    SELECT im.*, CONCAT(s.Nombre, ' ', s.Apellido_paterno) AS nombre_socio
    FROM inventario_materiales im
    LEFT JOIN socios s ON im.RUT_usuario = s.RUT
    ORDER BY im.Fecha_registro DESC, im.ID_material DESC
")->fetchAll(PDO::FETCH_ASSOC);

$nombre_usuario = $_SESSION['nombre'] ?? 'Administrador';
$mensaje = $_GET['m'] ?? '';
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario de Materiales - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>body{font-family:'Nunito',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-green-50 to-teal-100 min-h-screen">

<!-- BARRA SUPERIOR -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-green-700 to-teal-800 text-white shadow-2xl z-50">
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
        <h1 class="text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-600 to-teal-600 mb-4">
            Inventario de Materiales
        </h1>
        <p class="text-3xl text-gray-700 font-bold">Control total de los bienes de la JJVV</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="text-center mb-10 bg-<?= (strpos($mensaje,'correctamente')!==false) ? 'emerald' : 'red' ?>-100 border border-<?= (strpos($mensaje,'correctamente')!==false) ? 'emerald' : 'red' ?>-400 text-<?= (strpos($mensaje,'correctamente')!==false) ? 'emerald' : 'red' ?>-800 px-12 py-6 rounded-3xl text-3xl font-bold">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="text-center mb-12">
        <button onclick="openModal()" class="bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-500 hover:to-teal-500 text-white font-black text-4xl py-8 px-32 rounded-3xl shadow-2xl transform hover:scale-110 transition">
            <i class="fas fa-plus-circle mr-4"></i> AGREGAR MATERIAL
        </button>
    </div>

    <?php if (empty($materiales)): ?>
        <p class="text-center text-5xl text-gray-600 font-bold">No hay materiales registrados aún</p>
    <?php else: ?>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <?php foreach ($materiales as $m): ?>
                <div class="bg-white rounded-3xl shadow-2xl p-8 transform hover:scale-105 transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-4xl <?= $m['Estado']=='Nuevo'?'text-green-600':($m['Estado']=='Regular'?'text-yellow-600':'text-red-600') ?>">
                            <i class="fas fa-box"></i>
                        </span>
                        <span class="text-3xl font-black text-gray-800">#<?= $m['ID_material'] ?></span>
                    </div>
                    <h3 class="text-2xl font-black text-gray-800 mb-3"><?= htmlspecialchars($m['Nombre_material']) ?></h3>
                    <div class="space-y-3 text-lg">
                        <p><strong>Cantidad:</strong> <span class="font-bold text-2xl text-indigo-600"><?= $m['Cantidad'] ?></span></p>
                        <p><strong>Estado:</strong> <span class="px-4 py-2 rounded-full text-white font-bold <?= $m['Estado']=='Nuevo'?'bg-green-500':($m['Estado']=='Regular'?'bg-yellow-500':'bg-red-500') ?>"><?= htmlspecialchars($m['Estado']) ?></span></p>
                        <p><strong>Ubicación:</strong> <?= htmlspecialchars($m['Ubicacion']) ?></p>
                        <p><strong>Registrado por:</strong> <?= htmlspecialchars($m['nombre_socio'] ?: 'Sin socio') ?></p>
                        <?php if ($m['Historial']): ?>
                            <p class="text-sm text-gray-600 mt-3"><strong>Historial:</strong> <?= htmlspecialchars($m['Historial']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button onclick='edit(<?= json_encode($m, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <a href="?del=<?= $m['ID_material'] ?>" onclick="return confirm('¿Eliminar este material permanentemente?')"
                           class="bg-red-600 hover:bg-red-700 text-white font-bold py-4 px-6 rounded-xl transition">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL AGREGAR / EDITAR -->
<div id="modal" class="fixed inset-0 bg-black/80 hidden flex items-center justify-center z-50" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-3xl shadow-3xl p-12 max-w-5xl w-full mx-6 overflow-y-auto max-h-screen">
        <h2 class="text-6xl font-black text-center text-green-600 mb-12">Agregar Material</h2>
        <form method="POST" class="space-y-10">
            <input type="hidden" name="id" id="edit-id">
            
            <div class="grid md:grid-cols-2 gap-10">
                <div class="md:col-span-2">
                    <input type="text" name="nombre" id="nombre" required placeholder="Nombre del material"
                           class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-green-500 outline-none">
                </div>

                <!-- SELECT DE SOCIOS -->
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-4 text-center">Registrado por (Socio)</label>
                    <select name="rut_usuario" id="rut_usuario" required class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-2xl focus:border-green-500 outline-none">
                        <option value="">-- Seleccionar socio --</option>
                        <?php foreach ($socios as $s): ?>
                            <option value="<?= $s['RUT'] ?>"><?= htmlspecialchars($s['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <input type="number" name="cantidad" id="cantidad" required min="1" value="1"
                           class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-green-500 outline-none">
                    <label class="block text-center mt-2 text-xl text-gray-600">Cantidad</label>
                </div>

                <div>
                    <select name="estado" id="estado" required class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-2xl focus:border-green-500 outline-none">
                        <option value="Nuevo">Nuevo</option>
                        <option value="Regular">Funciona con desgastes</option>
                        <option value="Dañado">Dañado</option>
                        <option value="No funciona">No funciona</option>
                    </select>
                    <label class="block text-center mt-2 text-xl text-gray-600">Estado</label>
                </div>

                <div class="md:col-span-2">
                    <input type="text" name="ubicacion" id="ubicacion" required placeholder="Ubicación física"
                           class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-green-500 outline-none">
                </div>

                <div class="md:col-span-2">
                    <textarea name="historial" id="historial" rows="3" placeholder="Historial o comentarios (opcional)"
                              class="w-full px-10 py-7 border-4 border-gray-300 rounded-3xl text-2xl focus:border-green-500 outline-none"></textarea>
                </div>
            </div>

            <div class="flex gap-8 justify-center mt-10">
                <button type="submit" class="bg-gradient-to-r from-green-600 to-teal-600 text-white font-black text-4xl py-10 px-40 rounded-3xl shadow-2xl transform hover:scale-110 transition">
                    GUARDAR MATERIAL
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
    document.getElementById('nombre').value = '';
    document.getElementById('rut_usuario').value = '';
    document.getElementById('cantidad').value = '1';
    document.getElementById('estado').value = 'Nuevo';
    document.getElementById('ubicacion').value = '';
    document.getElementById('historial').value = '';
    document.querySelector('h2').textContent = 'Agregar Material';
    document.querySelector('button[type="submit"]').textContent = 'GUARDAR MATERIAL';
}

function edit(m) {
    openModal();
    document.getElementById('edit-id').value = m.ID_material;
    document.getElementById('nombre').value = m.Nombre_material;
    document.getElementById('rut_usuario').value = m.RUT_usuario;
    document.getElementById('cantidad').value = m.Cantidad;
    document.getElementById('estado').value = m.Estado;
    document.getElementById('ubicacion').value = m.Ubicacion;
    document.getElementById('historial').value = m.Historial || '';
    document.querySelector('h2').textContent = 'Editar Material';
    document.querySelector('button[type="submit"]').textContent = 'ACTUALIZAR MATERIAL';
}
</script>
</body>
</html>