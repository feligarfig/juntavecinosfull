<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';;

// === REGISTRAR PAGO ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut   = trim($_POST['rut'] ?? '');
    $monto = (int)($_POST['monto'] ?? 0);
    $fecha = $_POST['fecha'] ?? date('Y-m-d');

    if ($rut && $monto > 0 && $fecha) {
        try {
            $stmt = $pdo->prepare("INSERT INTO pagos (RUT, Monto, Fecha_pago, Estado_pago) 
                                   VALUES (?, ?, ?, 'Pagado')");
            $stmt->execute([$rut, $monto, $fecha]);
            $mensaje = "Pago registrado con éxito para RUT $rut";
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
        }
    } else {
        $mensaje = "Faltan datos obligatorios";
    }
    header("Location: pagos.php?m=" . urlencode($mensaje));
    exit;
}

// === ELIMINAR PAGO ===
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM pagos WHERE idPagos = ?")->execute([$id]);
    header("Location: pagos.php?m=Pago eliminado");
    exit;
}

// === LISTAR PAGOS Y TOTAL ===
$pagos = $pdo->query("SELECT * FROM pagos ORDER BY Fecha_pago DESC, idPagos DESC")->fetchAll(PDO::FETCH_ASSOC);
$total = $pdo->query("SELECT COALESCE(SUM(Monto),0) FROM pagos")->fetchColumn();

// Nombre del usuario (ajústalo según tu sistema de login)
$nombre_usuario = $_SESSION['nombre'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cobranza de Cuotas - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>body{font-family:'Nunito',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen">

<!-- BARRA SUPERIOR FIJA -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-8">
            <a href="dashboard.php" class="flex items-center gap-3 text-2xl font-black hover:scale-110 transition transform">
                <i class="fas fa-home"></i> Panel Principal
            </a>
        </div>
        
        <div class="flex items-center gap-6">
            <span class="text-xl">
                <i class="fas fa-user-circle"></i> <?= htmlspecialchars($nombre_usuario) ?>
            </span>
            <a href="?logout=1" 
               onclick="return confirm('¿Cerrar sesión?')"
               class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-bold text-xl transition transform hover:scale-105">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</div>

<!-- Espacio para que no tape el contenido -->
<div class="pt-24"></div>

<div class="container mx-auto px-4 py-8 max-w-7xl">

    <div class="text-center mb-10">
        <h1 class="text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-4">
            Cobranza de Cuotas
        </h1>
        <p class="text-2xl text-gray-700">Junta de Vecinos Villa La Flecha</p>
    </div>

    <!-- Total Recaudado -->
    <div class="bg-white rounded-3xl shadow-2xl p-10 text-center mb-10">
        <p class="text-3xl font-bold text-gray-600">TOTAL RECAUDADO EN CUOTAS</p>
        <p class="text-8xl font-black text-emerald-600 mt-4">$<?= number_format($total, 0, ',', '.') ?></p>
        <button onclick="openModal()" class="mt-8 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-2xl py-6 px-16 rounded-3xl shadow-2xl transform hover:scale-110 transition">
            Registrar Pago de Cuota
        </button>
    </div>

    <!-- Mensaje -->
    <?php if (isset($mensaje)): ?>
        <div class="bg-<?= strpos($mensaje,'éxito')!==false || strpos($mensaje,'eliminado')!==false ? 'emerald' : 'red' ?>-100 border border-<?= strpos($mensaje,'éxito')!==false || strpos($mensaje,'eliminado')!==false ? 'emerald' : 'red' ?>-400 text-<?= strpos($mensaje,'éxito')!==false || strpos($mensaje,'eliminado')!==false ? 'emerald' : 'red' ?>-800 px-8 py-5 rounded-3xl text-center text-2xl font-bold mb-8">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <!-- Tabla de pagos -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-8">
            <h2 class="text-4xl font-bold text-center">Historial de Cuotas Pagadas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">RUT Socio</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Monto</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Fecha Pago</th>
                        <th class="px-8 py-6 text-left font-bold text-gray-700">Estado</th>
                        <th class="px-8 py-6 text-center font-bold text-gray-700">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if (empty($pagos)): ?>
                        <tr><td colspan="5" class="text-center py-20 text-gray-500 text-3xl">No hay pagos registrados aún</td></tr>
                    <?php else: foreach ($pagos as $p): ?>
                        <tr class="hover:bg-emerald-50 transition">
                            <td class="px-8 py-6 font-bold text-lg"><?= htmlspecialchars($p['RUT'] ?? '-') ?></td>
                            <td class="px-8 py-6 font-bold text-2xl text-emerald-600">$<?= number_format($p['Monto'], 0, ',', '.') ?></td>
                            <td class="px-8 py-6 text-lg"><?= $p['Fecha_pago'] ? date('d/m/Y', strtotime($p['Fecha_pago'])) : '-' ?></td>
                            <td class="px-8 py-6">
                                <span class="px-6 py-3 bg-emerald-100 text-emerald-800 rounded-full font-bold">
                                    <?= htmlspecialchars($p['Estado_pago'] ?? 'Pagado') ?>
                                </span>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <a href="?delete=<?= $p['idPagos'] ?>" 
                                   onclick="return confirm('¿Eliminar este pago?')"
                                   class="text-red-600 hover:text-red-800 font-bold text-xl">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Registrar Pago -->
<div id="modal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-3xl shadow-3xl p-12 max-w-2xl w-full mx-6">
        <h2 class="text-5xl font-black text-emerald-600 text-center mb-10">Registrar Pago de Cuota</h2>
        <form method="POST" class="space-y-10">
            <div class="grid md:grid-cols-3 gap-8 text-center">
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-4">RUT del Socio</label>
                    <input type="text" name="rut" required placeholder="12.345.678-9" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-4">Monto</label>
                    <input type="number" name="monto" required min="1" placeholder="10000" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-4">Fecha del Pago</label>
                    <input type="date" name="fecha" required value="<?= date('Y-m-d') ?>" class="w-full px-8 py-6 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-emerald-500 outline-none">
                </div>
            </div>
            <div class="flex gap-6 justify-center mt-10">
                <button type="submit" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-3xl py-8 px-20 rounded-3xl shadow-2xl transform hover:scale-110 transition">
                    Confirmar Pago
                </button>
                <button type="button" onclick="document.getElementById('modal').classList.add('hidden')" class="bg-gray-400 hover:bg-gray-500 text-white font-black text-3xl py-8 px-16 rounded-3xl">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modal').classList.remove('hidden');
}
</script>
</body>
</html>