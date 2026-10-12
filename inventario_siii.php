<?php
session_start();
require_once 'config.php';

// Debug (activar solo si necesitas ver errores)
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$mensaje = $_GET['m'] ?? '';

/* ===========================
    BUSCADOR
=========================== */
$search = trim($_GET['q'] ?? '');
$where = '';
$params = [];

if ($search !== '') {
    $like = "%$search%";
    $where = " WHERE 
        i.nombre_material LIKE ? OR
        i.ubicacion LIKE ? OR
        i.estado LIKE ? OR
        u.nombre LIKE ? OR
        i.historial LIKE ?";
    $params = [$like, $like, $like, $like, $like];
}

/* ===========================
    EDITAR MATERIAL
=========================== */
$edit_material = null;

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("
        SELECT i.*, u.nombre AS nombre_usuario
        FROM inventario i
        LEFT JOIN usuario u ON TRIM(i.rut_usuario) = TRIM(u.rut_usuario)
        WHERE i.ID_material = ?
    ");
    $stmt->execute([$_GET['edit']]);
    $edit_material = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ===========================
    GUARDAR / ACTUALIZAR
=========================== */
if ($_POST && isset($_POST['guardar'])) {

    $id         = $_POST['id'] ?? null;
    $nombre     = trim($_POST['nombre'] ?? '');
    $cantidad   = (int)($_POST['cantidad'] ?? 1);
    $estado     = $_POST['estado'] ?? 'Regular';
    $ubicacion  = trim($_POST['ubicacion'] ?? '');
    $comentario = trim($_POST['comentario'] ?? '');

    $rut_usuario = $_SESSION['rut_usuario'] ?? null;

    if (!$rut_usuario) {
        header("Location: inventario.php?m=" . urlencode("Error: Sesión inválida"));
        exit;
    }

    // Nombre del usuario
    $stmt = $pdo->prepare("SELECT nombre FROM usuario WHERE rut_usuario = ?");
    $stmt->execute([$rut_usuario]);
    $nombre_usuario = $stmt->fetchColumn() ?: 'Admin';

    // Historial
    $log = $comentario !== '' 
        ? "\n" . date('d/m/Y H:i') . " - $nombre_usuario: $comentario"
        : '';

    $historial_final = ($edit_material['historial'] ?? '') . $log;

    try {
        if ($id) {
            // ACTUALIZAR
            $sql = "UPDATE inventario 
                    SET nombre_material=?, cantidad=?, estado=?, ubicacion=?, historial=?, rut_usuario=? 
                    WHERE ID_material=?";
            $pdo->prepare($sql)->execute([
                $nombre, $cantidad, $estado, $ubicacion, trim($historial_final), $rut_usuario, $id
            ]);
            $mensaje = "Material actualizado correctamente";

        } else {
            // AGREGAR
            $sql = "INSERT INTO inventario 
                    (rut_usuario, nombre_material, cantidad, estado, ubicacion, historial, fecha_registro)
                    VALUES (?, ?, ?, ?, ?, ?, CURDATE())";
            $pdo->prepare($sql)->execute([
                $rut_usuario, $nombre, $cantidad, $estado, $ubicacion, $log
            ]);
            $mensaje = "Material agregado correctamente";
        }
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
    }

    header("Location: inventario.php?m=" . urlencode($mensaje) . ($search ? "&q=".urlencode($search) : ""));
    exit;
}

/* ===========================
    ELIMINAR MATERIAL
=========================== */
if (isset($_GET['del'])) {
    $pdo->prepare("DELETE FROM inventario WHERE ID_material = ?")
        ->execute([$_GET['del']]);
    header("Location: inventario.php?m=" . urlencode("Material eliminado"));
    exit;
}

/* ===========================
    LISTADO GENERAL
=========================== */

$sql = "
SELECT 
    i.*, 
    COALESCE(u.nombre, 'Admin') AS nombre_usuario
FROM inventario i
LEFT JOIN usuario u ON TRIM(i.rut_usuario) = TRIM(u.rut_usuario)
$where
ORDER BY i.fecha_registro DESC, i.ID_material DESC
";

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
    <style>
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(to bottom right, #ccfbf1, #99f6e4); }
    </style>
</head>
<body class="min-h-screen">

<!-- BARRA SUPERIOR -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="dashboard.php" class="flex items-center gap-3 text-2xl font-black hover:scale-105 transition">
            <i class="fas fa-arrow-left"></i> Panel Principal
        </a>
        <div class="flex items-center gap-6">
            <span class="text-xl"><?= htmlspecialchars($nombre_usuario_sesion) ?></span>
            <a href="logout.php"
               class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-bold text-xl transition hover:scale-105">
               Cerrar Sesión
            </a>
        </div>
    </div>
</div>
<div class="pt-24"></div>

<div class="container mx-auto px-6 py-12 max-w-7xl">

    <h1 class="text-7xl font-black text-center mb-12 text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">
        INVENTARIO DE MATERIALES
    </h1>

    <?php if ($mensaje): ?>
        <div class="text-center mb-10 p-8 rounded-3xl text-3xl font-bold
            <?= str_contains($mensaje,'correctamente') ? 'bg-emerald-100 text-emerald-800 border border-emerald-400' : 'bg-red-100 text-red-800 border border-red-400' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- BUSCADOR -->
    <div class="bg-white rounded-3xl shadow-2xl p-8 mb-10">
        <form method="get" class="max-w-3xl mx-auto">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Buscar material, ubicación, estado o responsable..."
                   class="w-full px-10 py-7 text-2xl rounded-3xl border-4 border-gray-300 focus:border-emerald-500 outline-none">
        </form>
    </div>

    <!-- FORMULARIO -->
    <div class="bg-white rounded-3xl shadow-2xl p-12 mb-12">
        <form method="POST" class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">

            <input type="hidden" name="id" value="<?= $edit_material['ID_material'] ?? '' ?>">

            <div>
                <label class="block text-2xl font-bold mb-4">Nombre del Material *</label>
                <input type="text" required name="nombre"
                       value="<?= htmlspecialchars($edit_material['nombre_material'] ?? '') ?>"
                       class="w-full px-8 py-6 rounded-3xl border-4 text-2xl">
            </div>

            <div>
                <label class="block text-2xl font-bold mb-4">Cantidad *</label>
                <input type="number" required min="1" name="cantidad"
                       value="<?= $edit_material['cantidad'] ?? '1' ?>"
                       class="w-full px-8 py-6 rounded-3xl border-4 text-2xl text-center">
            </div>

            <div>
                <label class="block text-2xl font-bold mb-4">Estado *</label>
                <select name="estado" class="w-full px-8 py-6 rounded-3xl border-4 text-2xl">
                    <?php
                    $estados = ["Nuevo","Bueno","Regular","Dañado","No funciona"];
                    foreach ($estados as $e):
                    ?>
                        <option value="<?= $e ?>" <?= (($edit_material['estado'] ?? '') == $e) ? "selected" : "" ?>><?= $e ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-2xl font-bold mb-4">Ubicación *</label>
                <input type="text" required name="ubicacion"
                       value="<?= htmlspecialchars($edit_material['ubicacion'] ?? '') ?>"
                       class="w-full px-8 py-6 rounded-3xl border-4 text-2xl">
            </div>

            <div class="md:col-span-2 lg:col-span-3">
                <label class="block text-2xl font-bold mb-4">Comentario (se agrega al historial)</label>
                <textarea name="comentario" rows="3"
                          placeholder="Ej: Prestado a Juan, falta pieza..."
                          class="w-full px-8 py-6 rounded-3xl border-4 text-xl"></textarea>
            </div>

            <div class="md:col-span-2 lg:col-span-3 text-center mt-8 flex gap-8 justify-center">
                <button type="submit" name="guardar"
                        class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-black text-3xl py-8 px-32 rounded-3xl">
                    <?= $edit_material ? "ACTUALIZAR MATERIAL" : "AGREGAR MATERIAL" ?>
                </button>

                <?php if ($edit_material): ?>
                    <a href="inventario.php"
                       class="bg-gray-600 text-white font-black text-3xl py-8 px-24 rounded-3xl">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>
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
                        <th class="px-8 py-6 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                <?php foreach ($materiales as $m): ?>
                    <tr class="hover:bg-emerald-50">
                        <td class="px-8 py-6 font-bold text-xl"><?= htmlspecialchars($m['nombre_material']) ?></td>
                        <td class="px-8 py-6 text-center text-3xl font-black text-teal-600"><?= $m['cantidad'] ?></td>

                        <td class="px-8 py-6 text-center">
                            <?php
                            $estado = $m['estado'];
                            $color = [
                                "Nuevo"       => "bg-emerald-100 text-emerald-800",
                                "Bueno"       => "bg-green-100 text-green-800",
                                "Regular"     => "bg-yellow-100 text-yellow-800",
                                "Dañado"      => "bg-orange-200 text-orange-900",
                                "No funciona" => "bg-red-200 text-red-900",
                            ][$estado] ?? "bg-gray-200";
                            ?>
                            <span class="px-6 py-3 rounded-full font-bold <?= $color ?>"><?= $estado ?></span>
                        </td>

                        <td class="px-8 py-6"><?= htmlspecialchars($m['ubicacion']) ?></td>

                        <td class="px-8 py-6 text-center font-bold text-emerald-700"><?= htmlspecialchars($m['nombre_usuario']) ?></td>

                        <td class="px-8 py-6 text-center space-x-6">
                            <a href="?edit=<?= $m['ID_material'] ?>" class="text-emerald-600 font-bold">Editar</a>
                            <a href="?del=<?= $m['ID_material'] ?>"
                               onclick="return confirm('¿Eliminar permanentemente?')"
                               class="text-red-600 font-bold">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($materiales)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-20 text-4xl text-gray-400">
                            No hay materiales registrados
                        </td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>
