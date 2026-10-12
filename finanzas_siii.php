<?php
require_once 'config.php';
session_start();

$mensaje = $_GET['m'] ?? '';

// === CERRAR SESIÓN ===
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// === REGISTRAR MOVIMIENTO ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo        = $_POST['tipo'];           // 'ingreso' o 'egreso'
    $monto       = (int)$_POST['monto'];
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha       = $_POST['fecha'];
    $rut_socio   = !empty($_POST['rut_socio']) ? trim($_POST['rut_socio']) : null;

    if ($monto > 0 && in_array($tipo, ['ingreso', 'egreso'])) {
        $sql = "INSERT INTO movimientos_financieros 
                (RUT_admin, tipo, monto, descripción, fecha_movimiento, rut_socio) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $pdo->prepare($sql)->execute([
            $_SESSION['rut'] ?? null,
            $tipo,
            $monto,
            $descripcion,
            $fecha,
            $rut_socio
        ]);
        $mensaje = "Movimiento registrado correctamente";
    } else {
        $mensaje = "Error en los datos";
    }
    header("Location: caja.php?m=" . urlencode($mensaje));
    exit;
}

// === ELIMINAR ===
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM movimientos_financieros WHERE id_movimiento = ?")->execute([$id]);
    $mensaje = "Movimiento eliminado";
    header("Location: caja.php?m=" . urlencode($mensaje));
    exit;
}

// === CÁLCULOS ===
$ingresos = $pdo->query("SELECT COALESCE(SUM(monto),0) FROM movimientos_financieros WHERE tipo = 'ingreso'")->fetchColumn();
$egresos  = $pdo->query("SELECT COALESCE(SUM(monto),0) FROM movimientos_financieros WHERE tipo = 'egreso'")->fetchColumn();
$saldo    = $ingresos - $egresos;

// === LISTADO COMPLETO ===
$movimientos = $pdo->query("
    SELECT m.*, s.Nombre, s.Apellido1, s.Apellido2 
    FROM movimientos_financieros m
    LEFT JOIN socios s ON m.rut_socio = s.RUT AND s.estado = 'activo'
    ORDER BY m.fecha_movimiento DESC, m.id_movimiento DESC
")->fetchAll(PDO::FETCH_ASSOC);

$nombre_usuario = $_SESSION['nombre'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caja JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>body{font-family:'Nunito',sans-serif; background: linear-gradient(to-br, #f0fdfa, #ccfbf1);}</style>
</head>
<body class="min-h-screen">

<!-- BARRA SUPERIOR -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="dashboard.php" class="flex items-center gap-3 text-2xl font-black hover:scale-110 transition">
            Panel Principal
        </a>
        <div class="flex items-center gap-6">
            <span class="text-xl"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nombre_usuario) ?></span>
            <a href="?logout=1" onclick="return confirm('¿Cerrar sesión?')" class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-bold text-xl transition hover:scale-105">
                Cerrar Sesión
            </a>
        </div>
    </div>
</div>
<div class="pt-24"></div>

<div class="container mx-auto px-6 py-12 max-w-7xl">

    <h1 class="text-7xl font-black text-center mb-12 text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600">
        CAJA GENERAL JJVV LA FLECHA
    </h1>

    <?php if ($mensaje): ?>
        <div class="text-center mb-8 p-6 rounded-3xl text-3xl font-bold <?= str_contains($mensaje,'correctamente') ? 'bg-emerald-100 text-emerald-800 border border-emerald-400' : 'bg-red-100 text-red-800 border border-red-400' ?>">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- RESUMEN -->
    <div class="grid md:grid-cols-3 gap-8 mb-12">
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 text-white rounded-3xl shadow-2xl p-10 text-center">
            <p class="text-3xl font-bold">INGRESOS</p>
            <p class="text-7xl font-black mt-4">$<?= number_format($ingresos, 0, ',', '.') ?></p>
        </div>
        <div class="bg-gradient-to-br from-red-500 to-pink-600 text-white rounded-3xl shadow-2xl p-10 text-center">
            <p class="text-3xl font-bold">EGRESOS</p>
            <p class="text-7xl font-black mt-4">$<?= number_format($egresos, 0, ',', '.') ?></p>
        </div>
        <div class="bg-gradient-to-br <?= $saldo >= 0 ? 'from-blue-600 to-indigo-700' : 'from-orange-600 to-red-700' ?> text-white rounded-3xl shadow-2xl p-10 text-center transform scale-110">
            <p class="text-4xl font-black">SALDO EN CAJA</p>
            <p class="text-8xl font-black mt-4">$<?= number_format($saldo, 0, ',', '.') ?></p>
            <?php if ($saldo < 0): ?><p class="text-3xl mt-4">¡Caja en negativo!</p><?php endif; ?>
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-8 mb-10">
        <button onclick="openModal('ingreso')" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-3xl py-10 rounded-3xl shadow-2xl transform hover:scale-105 transition">
            + Registrar Ingreso (Cuota)
        </button>
        <button onclick="openModal('egreso')" class="bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-500 hover:to-pink-500 text-white font-black text-3xl py-10 rounded-3xl shadow-2xl transform hover:scale-105 transition">
            + Registrar Egreso (Gasto)
        </button>
    </div>

    <!-- TABLA DE MOVIMIENTOS -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8">
            <h2 class="text-4xl font-black text-center">Historial de Movimientos</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left">Fecha</th>
                        <th class="px-6 py-4 text-left">Tipo</th>
                        <th class="px-6 py-4 text-left">Detalle</th>
                        <th class="px-6 py-4 text-right">Monto</th>
                        <th class="px-6 py-4 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($movimientos as $m): ?>
                    <tr class="hover:bg-<?= $m['tipo']=='ingreso' ? 'emerald' : 'red' ?>-50 transition">
                        <td class="px-6 py-4"><?= date('d/m/Y', strtotime($m['fecha_movimiento'])) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-4 py-2 rounded-full font-bold text-sm <?= $m['tipo']=='ingreso' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' ?>">
                                <?= strtoupper($m['tipo']) ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($m['tipo']=='ingreso' && $m['Nombre']): ?>
                                <?= htmlspecialchars($m['rut_socio'] . ' - ' . $m['Nombre'] . ' ' . ($m['Apellido1']??'') . ' ' . ($m['Apellido2']??'')) ?>
                            <?php else: ?>
                                <?= htmlspecialchars($m['descripción'] ?: 'Sin descripción') ?>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right text-xl font-bold <?= $m['tipo']=='ingreso' ? 'text-emerald-600' : 'text-red-600' ?>">
                            $<?= number_format($m['monto'], 0, ',', '.') ?>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="?delete=<?= $m['id_movimiento'] ?>" onclick="return confirm('¿Eliminar este movimiento?')" class="text-red-600 hover:text-red-800 font-bold">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($movimientos)): ?>
                    <tr><td colspan="5" class="text-center py-20 text-4xl text-gray-400">No hay movimientos registrados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL -->
<div id="modal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50" onclick="if(event.target===this) closeModal()">
    <div class="bg-white rounded-3xl shadow-3xl p-12 max-w-3xl w-full mx-6">
        <h2 id="titulo" class="text-5xl font-black text-center mb-10"></h2>
        <form method="POST">
            <input type="hidden" name="tipo" id="tipo">
            <div class="grid md:grid-cols-2 gap-8">
                <div id="campo-rut" class="hidden">
                    <label class="block text-2xl font-bold text-gray-700 mb-4">RUT del Socio (opcional)</label>
                    <input type="text" name="rut_socio" placeholder="12.345.678-9" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl focus:border-emerald-500 outline-none">
                </div>
                <div id="campo-desc" class="">
                    <label class="block text-2xl font-bold text-gray-700 mb-4">Descripción</label>
                    <input type="text" name="descripcion" placeholder="Ej: Cuota mensual / Compra de pintura" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl outline-none">
                </div>
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-4">Monto</label>
                    <input type="number" name="monto" required min="1" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-4">Fecha</label>
                    <input type="date" name="fecha" required value="<?= date('Y-m-d') ?>" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-emerald-500 outline-none">
                </div>
            </div>
            <div class="flex gap-6 justify-center mt-12">
                <button type="submit" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-3xl py-8 px-20 rounded-3xl shadow-2xl transform hover:scale-110 transition">
                    Confirmar
                </button>
                <button type="button" onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-black text-3xl py-8 px-16 rounded-3xl">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(tipo) {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('tipo').value = tipo;
    document.getElementById('titulo').textContent = tipo === 'ingreso' ? 'Registrar Ingreso' : 'Registrar Egreso';
    document.getElementById('titulo').className = 'text-5xl font-black text-center mb-10 ' + (tipo === 'ingreso' ? 'text-emerald-600' : 'text-red-600');
    document.getElementById('campo-rut').classList.toggle('hidden', tipo !== 'ingreso');
}
function closeModal() {
    document.getElementById('modal').classList.add('hidden');
}
</script>

</body>
</html>