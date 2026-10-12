<?php
require_once 'config.php';


$mensaje = '';

// === REGISTRAR NUEVO MOVIMIENTO ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut_admin   = trim($_POST['rut_admin'] ?? '');
    $tipo        = $_POST['tipo'] ?? 'Ingreso';
    $monto       = (int)($_POST['monto'] ?? 0);
    $descripcion = trim($_POST['descripción'] ?? '');
    $fecha       = $_POST['fecha'] ?? date('Y-m-d');

    if ($monto > 0 && $fecha) {
        try {
            $stmt = $pdo->prepare("INSERT INTO movimientos_financieros 
                (RUT_admin, tipo, monto, descripción, fecha_movimiento) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$rut_admin, $tipo, $monto, $descripcion, $fecha]);
            $mensaje = "Movimiento registrado con éxito";
        } catch (Exception $e) {
            $mensaje = "Error al registrar: " . $e->getMessage();
        }
    } else {
        $mensaje = "Faltan datos obligatorios";
    }
    header("Location: finanzas.php?m=" . urlencode($mensaje));
    exit;
}

// === ELIMINAR MOVIMIENTO ===
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM movimientos_financieros WHERE id_movimiento = ?")
            ->execute([$id]);
        $mensaje = "Movimiento eliminado";
    } catch (Exception $e) {
        $mensaje = "Error al eliminar";
    }
    header("Location: finanzas.php?m=" . urlencode($mensaje));
    exit;
}

// === OBTENER TODOS LOS MOVIMIENTOS (seguro aunque la tabla esté vacía) ===
try {
    $stmt = $pdo->query("SELECT * FROM movimientos_financieros 
                         ORDER BY fecha_movimiento DESC, id_movimiento DESC");
    $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $movimientos = [];
    error_log("Error consulta movimientos_financieros: " . $e->getMessage());
}

// === TOTAL RECAUDADO (solo ingresos) ===
try {
    $totalIngresos = $pdo->query("SELECT COALESCE(SUM(monto), 0) 
                                  FROM movimientos_financieros 
                                  WHERE tipo = 'Ingreso'")->fetchColumn();
} catch (Exception $e) {
    $totalIngresos = 0;
}

$mensaje = $_GET['m'] ?? '';
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cobranza y Movimientos - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>body{font-family:'Nunito',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen">

<div class="container mx-auto px-4 py-8 max-w-7xl">

    <!-- Header -->
    <div class="text-center mb-10">
        <h1 class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-4">
            Cobranza y Movimientos
        </h1>
        <p class="text-2xl text-gray-700">Junta de Vecinos La Flecha</p>
    </div>

    <!-- Total Recaudado -->
    <div class="bg-white rounded-3xl shadow-2xl p-10 text-center mb-10">
        <p class="text-3xl font-bold text-gray-600">TOTAL RECAUDADO (INGRESOS)</p>
        <p class="text-8xl font-black text-emerald-600 mt-4">$<?= number_format($totalIngresos, 0, ',', '.') ?></p>
        <button onclick="openModal()" class="mt-8 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-2xl py-6 px-16 rounded-3xl shadow-2xl transform hover:scale-110 transition">
            + Registrar Movimiento
        </button>
    </div>

    <!-- Mensaje -->
    <?php if ($mensaje): ?>
        <div class="bg-<?= (strpos($mensaje,'éxito')!==false || strpos($mensaje,'eliminado')!==false) ? 'emerald' : 'red' ?>-100 border border-<?= (strpos($mensaje,'éxito')!==false || strpos($mensaje,'eliminado')!==false) ? 'emerald' : 'red' ?>-400 text-<?= (strpos($mensaje,'éxito')!==false || strpos($mensaje,'eliminado')!==false) ? 'emerald' : 'red' ?>-800 px-8 py-5 rounded-3xl text-center text-2xl font-bold mb-8">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- Tabla de movimientos -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8">
            <h2 class="text-4xl font-bold text-center">Historial de Movimientos</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Tipo</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">RUT Admin</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Monto</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Descripción</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Fecha</th>
                        <th class="px-8 py-6 text-center font-bold text-gray-700">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($movimientos)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-20 text-gray-500 text-3xl">
                                No hay movimientos registrados aún
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($movimientos as $m): ?>
                        <tr class="hover:bg-emerald-50 transition">
                            <td class="px-8 py-6">
                                <span class="px-6 py-3 rounded-full font-bold <?= $m['tipo']=='Ingreso' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= htmlspecialchars($m['tipo']) ?>
                                </span>
                            </td>
                            <td class="px-8 py-6 font-bold text-lg"><?= htmlspecialchars($m['RUT_admin'] ?: '-') ?></td>
                            <td class="px-8 py-6 font-bold text-2xl <?= $m['tipo']=='Ingreso' ? 'text-emerald-600' : 'text-red-600' ?>">
                                <?= $m['tipo']=='Ingreso' ? '+' : '-' ?>$<?= number_format($m['monto'], 0, ',', '.') ?>
                            </td>
                            <td class="px-8 py-6 text-lg"><?= htmlspecialchars($m['descripción'] ?: '-') ?></td>
                            <td class="px-8 py-6 text-lg"><?= date('d/m/Y', strtotime($m['fecha_movimiento'])) ?></td>
                            <td class="px-8 py-6 text-center">
                                <a href="?delete=<?= $m['id_movimiento'] ?>" 
                                   onclick="return confirm('¿Eliminar este movimiento de $<?= number_format($m['monto'],0,',','.') ?>?')"
                                   class="text-red-600 hover:text-red-800 font-bold text-xl">
                                    Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Registrar Movimiento -->
<div id="modal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-3xl shadow-3xl p-12 max-w-4xl w-full mx-6">
        <h2 class="text-5xl font-black text-emerald-600 text-center mb-10">Registrar Nuevo Movimiento</h2>
        <form method="POST" class="space-y-10">
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-4">Tipo</label>
                    <select name="tipo" required class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl font-bold focus:border-emerald-500 outline-none">
                        <option value="Ingreso">Ingreso (Cuota)</option>
                        <option value="Egreso">Egreso (Gasto)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-4">RUT Admin </label>
                    <input type="text" name="rut_admin" placeholder="12.345.678-9" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl font-bold text-center focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-4">Monto</label>
                    <input type="number" name="monto" required min="1" placeholder="10000" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-4">Fecha</label>
                    <input type="date" name="fecha" required value="<?= date('Y-m-d') ?>" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl font-bold text-center focus:border-emerald-500 outline-none">
                </div>
            </div>
            <div class="px-8">
                <label class="block text-2xl font-bold text-gray-700 mb-4">Descripción</label>
                <input type="text" name="descripción" placeholder="Ej: Cuota mensual - Juan Pérez" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-2xl focus:border-emerald-500 outline-none">
            </div>
            <div class="flex gap-6 justify-center mt-10">
                <button type="submit" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-3xl py-8 px-20 rounded-3xl shadow-2xl transform hover:scale-110 transition">
                    Confirmar Movimiento
                </button>
                <button type="button" onclick="document.getElementById('modal').classList.add('hidden')" class="bg-gray-400 hover:bg-gray-500 text-white font-black text-3xl py-8 px-16 rounded-3xl">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>
<div class="text-center mt-8">
    <a href="reporte_pdf.php" target="_blank"
       class="inline-block bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-black text-2xl py-6 px-16 rounded-3xl shadow-2xl transform hover:scale-110 transition">
        Generar Reporte PDF
    </a>
</div>

<script>
function openModal() {
    document.getElementById('modal').classList.remove('hidden');
}
</script>
</body>
</html>