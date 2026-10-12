<?php

require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// VALIDACIÓN AJAX EN TIEMPO REAL (primero, para que no moleste al cargar)
if (isset($_GET['check_duplicado'])) {
    $nombre = trim(strtolower($_GET['nombre'] ?? ''));
    $edit_id = (int)($_GET['edit'] ?? 0);
    if ($nombre !== '') {
        $stmt = $pdo->prepare("SELECT 1 FROM inventario WHERE LOWER(TRIM(nombre_material)) = ? AND ID_material != ?");
        $stmt->execute([$nombre, $edit_id]);
        echo $stmt->rowCount() > 0 ? '1' : '0';
    } else {
        echo '0';
    }
    exit;
}

$mensaje = isset($_GET['m']) ? $_GET['m'] : '';
$search  = trim(isset($_GET['q']) ? $_GET['q'] : '');

/* BUSCADOR */
$where = ''; $params = [];
if ($search !== '') {
    $like = "%$search%";
    $where = " WHERE i.nombre_material LIKE ? OR i.ubicacion LIKE ? OR i.estado LIKE ? OR u.nombre LIKE ? OR i.historial LIKE ?";
    $params = [$like, $like, $like, $like, $like];
}

/* EDITAR */
$edit_material = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT i.*, u.nombre AS nombre_usuario FROM inventario i LEFT JOIN usuario u ON TRIM(i.rut_usuario) = TRIM(u.rut_usuario) WHERE i.ID_material = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_material = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* GUARDAR / ACTUALIZAR */
if ($_POST && isset($_POST['guardar'])) {
    $id         = $_POST['id'] ?? null;
    $nombre     = trim(ucwords(strtolower($_POST['nombre'] ?? '')));
    $cantidad   = (int)($_POST['cantidad'] ?? 1);
    $estado     = $_POST['estado'] ?? 'Regular';
    $ubicacion  = trim($_POST['ubicacion'] ?? '');
    $comentario = trim($_POST['comentario'] ?? '');

    $rut_usuario = $_SESSION['rut_usuario'] ?? null;

    if (!$rut_usuario || $nombre === '') {
        $mensaje = "Error: datos inválidos";
    } else {
        $stmt = $stmt = $pdo->prepare("SELECT nombre FROM usuario WHERE rut_usuario = ?");
        $stmt->execute([$rut_usuario]);
        $nombre_usuario = $stmt->fetchColumn() ?: 'Admin';

        $log = $comentario !== '' ? "\n" . date('d/m/Y H:i') . " - $nombre_usuario: $comentario" : '';
        $historial_final = $edit_material ? $edit_material['historial'] . $log : $log;

        try {
            if ($id) {
                $pdo->prepare("UPDATE inventario SET nombre_material=?, cantidad=?, estado=?, ubicacion=?, historial=?, rut_usuario=? WHERE ID_material=?")
                    ->execute([$nombre, $cantidad, $estado, $ubicacion, trim($historial_final), $rut_usuario, $id]);
                $mensaje = "Material actualizado correctamente";
            } else {
                // CONTROL FINAL (por si alguien desactiva JS)
                $stmt = $pdo->prepare("SELECT nombre_material FROM inventario WHERE LOWER(TRIM(nombre_material)) = LOWER(TRIM(?))");
                $stmt->execute([$nombre]);
                if ($stmt->fetchColumn()) {
                    $mensaje = "ERROR! Ya existe \"$nombre\". Edítalo en vez de duplicarlo.";
                } else {
                    $pdo->prepare("INSERT INTO inventario (rut_usuario, nombre_material, cantidad, estado, ubicacion, historial, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, CURDATE())")
                        ->execute([$rut_usuario, $nombre, $cantidad, $estado, $ubicacion, trim($log)]);
                    $mensaje = "Material agregado correctamente";
                }
            }
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
        }
    }
    header("Location: inventario.php?m=" . urlencode($mensaje) . ($search ? "&q=" . urlencode($search) : ""));
    exit;
}

/* ELIMINAR */
if (isset($_GET['del'])) {
    $pdo->prepare("DELETE FROM inventario WHERE ID_material = ?")->execute([$_GET['del']]);
    header("Location: inventario.php?m=Material eliminado" . ($search ? "&q=" . urlencode($search) : ""));
    exit;
}

/* LISTADO */
$sql = "SELECT i.*, COALESCE(u.nombre, 'Admin') AS nombre_usuario FROM inventario i LEFT JOIN usuario u ON TRIM(i.rut_usuario) = TRIM(u.rut_usuario) $where ORDER BY i.fecha_registro DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nombre_usuario_sesion = $_SESSION['nombre'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(to bottom right, #ccfbf1, #99f6e4); }
        .historial-corto { max-height: 60px; overflow: hidden; transition: all 0.4s; }
        .historial-abierto { max-height: 1000px; }
    </style>
</head>
<body class="min-h-screen">

<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="dashboard.php" class="text-2xl font-black hover:scale-105 transition">Panel Principal</a>
        <div class="flex items-center gap-6">
            <span class="text-xl"><?= htmlspecialchars($nombre_usuario_sesion) ?></span>
            <a href="logout=1" class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-bold text-xl transition hover:scale-105">Cerrar Sesión</a>
        </div>
    </div>
</div>
<div class="pt-24"></div>

<div class="container mx-auto px-6 py-12 max-w-7xl">
    <h1 class="text-7xl font-black text-center mb-12 text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">
        INVENTARIO DE MATERIALES
    </h1>

    <?php if ($mensaje): ?>
        <div class="text-center mb-10 p-8 rounded-3xl text-3xl font-bold <?= strpos($mensaje,'correctamente') !== false || $mensaje == 'Material eliminado' ? 'bg-emerald-100 text-emerald-800 border-4 border-emerald-400' : 'bg-red-100 text-red-800 border-4 border-red-400' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- BUSCADOR SIN TEXTO FEO -->
    <div class="flex flex-col md:flex-row gap-6 mb-12 items-center justify-between">
        <div class="w-full md:w-96 relative">
            <form method="get">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Buscar material, ubicación, estado, usuario..."
                       class="w-full pl-14 pr-6 py-5 text-xl rounded-full border-4 border-emerald-300 focus:border-emerald-500 focus:outline-none shadow-lg">
                <button type="submit" class="absolute left-5 top-1/2 -translate-y-1/2 text-emerald-600 text-2xl">
                    
                </button>
            </form>
        </div>

        <button onclick="abrirModal()" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-2xl px-12 py-5 rounded-full shadow-2xl hover:scale-105 transition">
            + AGREGAR MATERIAL
        </button>
    </div>

    <!-- TABLA -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8">
            <h2 class="text-4xl font-black text-center">Listado de Materiales</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-8 py-6">Material</th>
                        <th class="px-8 py-6 text-center">Cant.</th>
                        <th class="px-8 py-6 text-center">Estado</th>
                        <th class="px-8 py-6">Ubicación</th>
                        <th class="px-8 py-6 text-center">Registrado por</th>
                        <th class="px-8 py-6">Historial</th>
                        <th class="px-8 py-6 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($materiales as $m):
                        $historial = trim($m['historial'] ?? '');
                        $tiene_historial = $historial !== '';
                        $historial_corto = $tiene_historial ? substr($historial, 0, 150) . (strlen($historial) > 150 ? '...' : '') : 'Sin historial';
                    ?>
                    <tr class="hover:bg-emerald-50 transition">
                        <td class="px-8 py-6 font-bold text-xl"><?= htmlspecialchars($m['nombre_material']) ?></td>
                        <td class="px-8 py-6 text-center text-3xl font-black text-teal-600"><?= $m['cantidad'] ?></td>
                        <td class="px-8 py-6 text-center">
                            <?php $colores = ["Nuevo"=>"bg-emerald-100 text-emerald-800","Bueno"=>"bg-green-100 text-green-800","Regular"=>"bg-yellow-100 text-yellow-800","Dañado"=>"bg-orange-200 text-orange-900","No funciona"=>"bg-red-200 text-red-900"]; ?>
                            <span class="px-6 py-3 rounded-full font-bold <?= $colores[$m['estado']] ?? 'bg-gray-200' ?>"><?= $m['estado'] ?></span>
                        </td>
                        <td class="px-8 py-6"><?= htmlspecialchars($m['ubicacion']) ?></td>
                        <td class="px-8 py-6 text-center font-bold text-emerald-700"><?= htmlspecialchars($m['nombre_usuario']) ?></td>
                        <td class="px-8 py-6 text-sm leading-relaxed">
                            <?php if ($tiene_historial): ?>
                                <div class="historial-corto cursor-pointer text-gray-700" onclick="this.classList.toggle('historial-abierto')">
                                    <?= nl2br(htmlspecialchars($historial_corto)) ?>
                                    <?php if (strlen($historial) > 150): ?>
                                        <span class="text-emerald-600 font-bold block mt-2">Mostrar todo</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (strlen($historial) > 150): ?>
                                    <div class="hidden historial-abierto text-gray-700">
                                        <?= nl2br(htmlspecialchars($historial)) ?>
                                        <span class="text-emerald-600 font-bold block mt-2 cursor-pointer" onclick="this.parentElement.classList.add('hidden'); this.parentElement.previousElementSibling.classList.remove('historial-abierto')">
                                            Ocultar
                                        </span>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-gray-400 italic">Sin historial</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-8 py-6 text-center space-x-6">
                            <a href="?edit=<?= $m['ID_material'] ?><?= $search ? '&q='.urlencode($search) : '' ?>" class="text-emerald-600 font-bold hover:underline">Editar</a>
                            <a href="?del=<?= $m['ID_material'] ?><?= $search ? '&q='.urlencode($search) : '' ?>" onclick="return confirm('Eliminar permanentemente?')" class="text-red-600 font-bold hover:underline">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($materiales)): ?>
                    <tr><td colspan="7" class="text-center py-20 text-4xl text-gray-400">No hay materiales registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-70 hidden items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl p-10 w-full max-w-3xl mx-4 max-h-screen overflow-y-auto">
        <h3 class="text-4xl font-black text-center mb-10 text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">
            <?= $edit_material ? 'EDITAR MATERIAL' : 'NUEVO MATERIAL' ?>
        </h3>
        <form method="post" class="space-y-8">
            <input type="hidden" name="id" value="<?= $edit_material ? $edit_material['ID_material'] : '' ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label class="block text-xl font-bold text-gray-700 mb-3">Nombre del material *</label>
                    <input type="text" name="nombre" id="nombre-material" required maxlength="100" 
                           value="<?= htmlspecialchars($edit_material ? $edit_material['nombre_material'] : '') ?>"
                           class="w-full px-6 py-4 rounded-xl border-4 border-emerald-200 focus:border-emerald-500 focus:outline-none text-xl"
                           autocomplete="off">
                    <div id="error-duplicado" class="mt-2 text-lg font-semibold"></div>
                </div>
                <div>
                    <label class="block text-xl font-bold text-gray-700 mb-3">Cantidad</label>
                    <input type="number" name="cantidad" min="1" value="<?= $edit_material ? $edit_material['cantidad'] : 1 ?>"
                           class="w-full px-6 py-4 rounded-xl border-4 border-emerald-200 focus:border-emerald-500 focus:outline-none text-xl">
                </div>
                <div>
                    <label class="block text-xl font-bold text-gray-700 mb-3">Estado</label>
                    <select name="estado" class="w-full px-6 py-4 rounded-xl border-4 border-emerald-200 focus:border-emerald-500 focus:outline-none text-xl">
                        <?php $estados = ['Nuevo','Bueno','Regular','Dañado','No funciona']; ?>
                        <?php foreach($estados as $e): ?>
                            <option value="<?= $e ?>" <?= ($edit_material && $edit_material['estado']==$e) || (!$edit_material && $e=='Regular') ? 'selected' : '' ?>><?= $e ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xl font-bold text-gray-700 mb-3">Ubicación</label>
                    <input type="text" name="ubicacion" value="<?= htmlspecialchars($edit_material ? $edit_material['ubicacion'] : '') ?>"
                           class="w-full px-6 py-4 rounded-xl border-4 border-emerald-200 focus:border-emerald-500 focus:outline-none text-xl">
                </div>
            </div>

            <div>
                <label class="block text-xl font-bold text-gray-700 mb-3">Comentario (se guarda en historial)</label>
                <textarea name="comentario" rows="4" class="w-full px-6 py-4 rounded-xl border-4 border-emerald-200 focus:border-emerald-500 focus:outline-none text-xl" placeholder="Ej: Se prestó, se reparó..."></textarea>
            </div>

            <div class="flex justify-end gap-6 pt-6">
                <button type="button" onclick="cerrarModal()" class="px-10 py-5 text-xl font-bold text-gray-600 hover:text-gray-800">Cancelar</button>
                <button type="submit" name="guardar" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-xl px-12 py-5 rounded-full shadow-xl hover:scale-105 transition">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('modal');
    const inputNombre = document.getElementById('nombre-material');
    const errorDiv = document.getElementById('error-duplicado');
    let timeout;

    function abrirModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        errorDiv.textContent = '';
        if (inputNombre) inputNombre.focus();
    }

    function cerrarModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
        if (window.location.search.includes('edit=')) {
            history.replaceState({}, '', 'inventario.php<?= $search ? '?q=' . urlencode($search) : '' ?>');
        }
    }

    // VALIDACIÓN EN TIEMPO REAL
    if (inputNombre) {
        inputNombre.addEventListener('input', function() {
            clearTimeout(timeout);
            const valor = this.value.trim();
            errorDiv.textContent = '';

            if (valor.length < 3) return;

            timeout = setTimeout(() => {
                fetch(`inventario.php?check_duplicado=1&nombre=${encodeURIComponent(valor.toLowerCase())}&edit=<?= $edit_material ? $edit_material['ID_material'] : 0 ?>`)
                    .then(r => r.text())
                    .then(result => {
                        if (result === '1') {
                            errorDiv.innerHTML = '<span class="text-red-600">Este material ya existe. Edítalo en vez de duplicarlo</span>';
                        } else {
                            errorDiv.textContent = '';
                        }
                    });
            }, 600);
        });
    }

    <?php if ($edit_material): ?> abrirModal(); <?php endif; ?>
    modal.addEventListener('click', e => { if (e.target === modal) cerrarModal(); });
</script>

</body>
</html>