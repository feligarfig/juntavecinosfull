<?php

require_once '../../includes/auth.php';
require_once '../../includes/db.php';


$message = '';

// === CERRAR SESIÓN ===
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php"); // Cambia por tu login si es distinto
    exit;
}

// ---------- BÚSQUEDA ----------
$search = trim($_GET['q'] ?? '');
$where = '';
$params = [];
if ($search !== '') {
    $like = "%$search%";
    $where = " WHERE RUT LIKE ? 
                OR Nombre LIKE ? 
                OR Apellido1 LIKE ? 
                OR Apellido2 LIKE ? 
                OR Domicilio LIKE ? 
                OR Ocupación LIKE ? 
                OR Fecha_ingreso LIKE ?";
    $params = array_fill(0, 7, $like);
}

// ---------- CARGAR SOCIO PARA EDITAR ----------
$edit_socio = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM socios WHERE RUT = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_socio = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ---------- GUARDAR O ACTUALIZAR ----------
if ($_POST) {
    $rut        = trim($_POST['rut']);
    $nombre     = trim($_POST['nombre']);
    $apellido1  = trim($_POST['apellido1']);
    $apellido2  = trim($_POST['apellido2'] ?? '');
    $fecha_nac  = $_POST['fecha_nac'] ?? '';
    $ocupacion  = trim($_POST['ocupacion'] ?? '');
    $domicilio  = trim($_POST['domicilio'] ?? '');
    $fecha_ingreso = trim($_POST['fecha_ingreso'] ?? '');
    $estado     = $_POST['estado'];

    if ($fecha_nac !== '' && preg_match('#^\d{2}/\d{2}/\d{4}$#', $fecha_nac)) {
        $p = explode('/', $fecha_nac);
        $fecha_nac = "$p[2]-$p[1]-$p[0]";
    } else {
        $fecha_nac = $fecha_nac ?: null;
    }

    if (!preg_match('/^\d{1,2}(\.\d{3})*[-][0-9kK]$/', $rut) && !preg_match('/^\d{7,8}[-][0-9kK]$/', $rut)) {
        $message = "RUT inválido. Formato: 12.345.678-9 o 12345678-9";
    } else {
        $sql = "INSERT INTO socios 
                (RUT, Nombre, Apellido1, Apellido2, Fecha_nacimiento, Ocupación, Domicilio, Fecha_ingreso, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                Nombre=VALUES(Nombre), Apellido1=VALUES(Apellido1), Apellido2=VALUES(Apellido2),
                Fecha_nacimiento=VALUES(Fecha_nacimiento), Ocupación=VALUES(Ocupación),
                Domicilio=VALUES(Domicilio), Fecha_ingreso=VALUES(Fecha_ingreso), estado=VALUES(estado)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rut, $nombre, $apellido1, $apellido2, $fecha_nac, $ocupacion, $domicilio, $fecha_ingreso, $estado]);

        $message = $edit_socio ? "Socio actualizado correctamente" : "Socio creado correctamente";

        if ($edit_socio) {
            header("Location: ?edit=" . urlencode($rut) . ($search ? "&q=" . urlencode($search) : ''));
            exit;
        }
    }
}

// ---------- INACTIVAR ----------
if (isset($_GET['desactivar'])) {
    $stmt = $pdo->prepare("UPDATE socios SET estado = 'inactivo' WHERE RUT = ?");
    $stmt->execute([$_GET['desactivar']]);
    $message = "Socio pasado a inactivo";
    header("Location: " . $_SERVER['PHP_SELF'] . ($search ? "?q=" . urlencode($search) : ''));
    exit;
}

// ---------- LISTADO ----------
$sql = "SELECT * FROM socios $where ORDER BY Apellido1, Apellido2, Nombre";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$socios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$nombre_usuario = $_SESSION['nombre'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Socios - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <style>body{font-family:'Nunito',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen">

<!-- BARRA SUPERIOR FIJA -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="dashboard.php" class="flex items-center gap-3 text-2xl font-black hover:scale-110 transition">
            <i class="fas fa-home"></i> Panel Principal
        </a>
        <div class="flex items-center gap-6">
            <span class="text-xl">
                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($nombre_usuario) ?>
            </span>
            <a href="?logout=1" 
               onclick="return confirm('¿Cerrar sesión?')"
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
            Gestión de Socios
        </h1>
        <p class="text-2xl text-gray-700">Junta de Vecinos Villa La Flecha - Coihueco</p>
    </div>

    <?php if ($message): ?>
        <div class="bg-<?= strpos($message,'correctamente')!==false ? 'emerald' : 'red' ?>-100 border border-<?= strpos($message,'correctamente')!==false ? 'emerald' : 'red' ?>-400 text-<?= strpos($message,'correctamente')!==false ? 'emerald' : 'red' ?>-800 px-8 py-5 rounded-3xl text-center text-2xl font-bold mb-8">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- BUSCADOR -->
    <div class="bg-white rounded-3xl shadow-2xl p-8 mb-10">
        <form method="get" class="max-w-3xl mx-auto">
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" 
                   placeholder="Buscar por RUT, nombre, domicilio, fecha ingreso, ocupación…" 
                   class="w-full px-10 py-7 text-2xl border-4 border-gray-300 rounded-3xl focus:border-emerald-500 outline-none">
        </form>
    </div>

    <!-- FORMULARIO -->
    <div class="bg-white rounded-3xl shadow-2xl p-12 mb-12">
        <form method="POST" class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
            <!-- Campos -->
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-4">RUT *</label>
                <input type="text" name="rut" required value="<?= htmlspecialchars($edit_socio['RUT'] ?? '') ?>"
                       <?= $edit_socio ? 'readonly class="bg-gray-200 cursor-not-allowed"' : '' ?>
                       class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl font-bold text-center focus:border-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-4">Nombre *</label>
                <input type="text" name="nombre" required value="<?= htmlspecialchars($edit_socio['Nombre'] ?? '') ?>"
                       class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl focus:border-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-4">Apellido Paterno *</label>
                <input type="text" name="apellido1" required value="<?= htmlspecialchars($edit_socio['Apellido1'] ?? '') ?>"
                       class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl focus:border-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-4">Apellido Materno</label>
                <input type="text" name="apellido2" value="<?= htmlspecialchars($edit_socio['Apellido2'] ?? '') ?>"
                       class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl focus:border-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-4">Fecha de Nacimiento</label>
                <input type="text" name="fecha_nac" class="datepicker" placeholder="15/06/1985"
                       value="<?= $edit_socio && $edit_socio['Fecha_nacimiento'] ? date('d/m/Y', strtotime($edit_socio['Fecha_nacimiento'])) : '' ?>"
                       class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl text-center focus:border-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-4">Fecha de Ingreso</label>
                <input type="text" name="fecha_ingreso" placeholder="20/11/2023"
                       value="<?= htmlspecialchars($edit_socio['Fecha_ingreso'] ?? '') ?>"
                       class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl text-center focus:border-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-4">Ocupación</label>
                <input type="text" name="ocupacion" value="<?= htmlspecialchars($edit_socio['Ocupación'] ?? '') ?>"
                       class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl focus:border-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-4">Domicilio</label>
                <input type="text" name="domicilio" value="<?= htmlspecialchars($edit_socio['Domicilio'] ?? '') ?>"
                       class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl focus:border-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-2xl font-bold text-gray-700 mb-4">Estado</label>
                <select name="estado" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl font-bold focus:border-emerald-500 outline-none">
                    <option value="activo"   <?= (!$edit_socio && !isset($_POST['estado'])) || ($edit_socio && $edit_socio['estado'] === 'activo') ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= $edit_socio && $edit_socio['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="md:col-span-2 lg:col-span-3 flex gap-6 justify-center mt-8">
                <button type="submit" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-3xl py-8 px-20 rounded-3xl shadow-2xl transform hover:scale-110 transition">
                    <?= $edit_socio ? 'Actualizar Socio' : 'Crear Nuevo Socio' ?>
                </button>
                <?php if ($edit_socio): ?>
                    <a href="?" class="bg-gray-500 hover:bg-gray-600 text-white font-black text-3xl py-8 px-16 rounded-3xl flex items-center">
                        Cancelar Edición
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- TABLA DE SOCIOS -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8">
            <h2 class="text-4xl font-bold text-center">Listado de Socios</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">RUT</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Nombre Completo</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Fecha Nac.</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Fecha Ingreso</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Domicilio</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Estado</th>
                        <th class="px-8 py-6 text-center font-bold text-gray-700">Comenario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($socios as $s): 
                        $nombrec = trim($s['Nombre'] . ' ' . $s['Apellido1'] . ' ' . ($s['Apellido2'] ?? ''));
                    ?>
                    <tr class="hover:bg-emerald-50 transition">
                        <td class="px-8 py-6 font-bold text-lg"><?= htmlspecialchars($s['RUT']) ?></td>
                        <td class="px-8 py-6 text-xl"><?= htmlspecialchars($nombrec) ?></td>
                        <td class="px-8 py-6"><?= $s['Fecha_nacimiento'] ? date('d/m/Y', strtotime($s['Fecha_nacimiento'])) : '-' ?></td>
                        <td class="px-8 py-6"><?= htmlspecialchars($s['Fecha_ingreso'] ?? '-') ?></td>
                        <td class="px-8 py-6"><?= htmlspecialchars($s['Domicilio'] ?? '-') ?></td>
                        <td class="px-8 py-6">
                            <span class="px-6 py-3 rounded-full font-bold <?= $s['estado'] === 'activo' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' ?>">
                                <?= ucfirst($s['estado'] ?? 'activo') ?>
                            </span>
                        </td>
                        <td class="px-8 py-6 text-center space-x-6">
                            <a href="?edit=<?= urlencode($s['RUT']) ?><?= $search ? '&q='.urlencode($search) : '' ?>" class="text-emerald-600 hover:text-emerald-800 font-bold text-xl">Editar</a>
                            <?php if ($s['estado'] === 'activo'): ?>
                                <a href="?desactivar=<?= urlencode($s['RUT']) ?><?= $search ? '&q='.urlencode($search) : '' ?>" 
                                   class="text-red-600 hover:text-red-800 font-bold text-xl"
                                   onclick="return confirm('¿Pasar a inactivo a <?= htmlspecialchars($nombrec) ?>?')">Inactivar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($socios)): ?>
                    <tr><td colspan="7" class="text-center py-20 text-gray-500 text-3xl">No se encontraron socios.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    flatpickr(".datepicker", {
        dateFormat: "d/m/Y",
        altInput: true,
        altFormat: "d/m/Y",
        locale: "es",
        allowInput: true,
        maxDate: "today"
    });
</script>
</body>
</html>