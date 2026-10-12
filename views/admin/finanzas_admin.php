<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login.php');
    exit;
}
require_once 'config.php';

$mensaje = '';

// === GUARDAR NUEVO MOVIMIENTO ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut      = $_SESSION['rut'] ?? '00.000.000-0';
    $tipo     = $_POST['tipo'];
    $monto    = (int)$_POST['monto'];
    $desc     = trim($_POST['descripcion']);

    if ($monto > 0 && $desc && in_array($tipo, ['Ingreso','Egreso'])) {
        $pdo->prepare("INSERT INTO movimientos_financieros (RUT_admin, Tipo, Monto, Descripcion, Fecha) VALUES (?, ?, ?, ?, CURDATE())")
            ->execute([$rut, $tipo, $monto, $desc]);
        $mensaje = "Movimiento registrado con éxito";
    }
    header("Location: finanzas_admin.php?m=" . urlencode($mensaje));
    exit;
}

// === ELIMINAR MOVIMIENTO ===
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM movimientos_financieros WHERE ID_movimiento = ?")
        ->execute([$_GET['delete']]);
    header("Location: finanzas_admin.php?m=Movimiento eliminado");
    exit;
}

// === OBTENER TODOS LOS MOVIMIENTOS (LÍNEA CORREGIDA) ===
$movimientos = $pdo->query("SELECT * FROM movimientos_financieros ORDER BY Fecha DESC, ID_movimiento DESC")->fetchAll();

$ingresos = $pdo->query("SELECT COALESCE(SUM(Monto),0) FROM movimientos_financieros WHERE Tipo='Ingreso'")->fetchColumn();
$egresos  = $pdo->query("SELECT COALESCE(SUM(Monto),0) FROM movimientos_financieros WHERE Tipo='Egreso'")->fetchColumn();
$saldo    = $ingresos - $egresos;

$mensaje = $_GET['m'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finanzas - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>body{font-family:'Nunito',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">

    <!-- MENÚ FIJO SUPERIOR -->
    <div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-indigo-700 to-purple-800 text-white shadow-2xl z-50">
        <div class="container mx-auto px-6 py-5 flex justify-between items-center">
            <div class="flex items-center space-x-10">
                <h2 class="text-3xl font-black">JJVV La Flecha</h2>
                <nav class="hidden md:flex space-x-8 text-xl font-bold">
                    <a href="finanzas_admin.php" class="hover:text-yellow-300">Finanzas</a>
                    <a href="pagos.php" class="hover:text-yellow-300">Cobranza</a>
                    <a href="eventos_admin.php" class="hover:text-yellow-300">Eventos</a>
                    <a href="reservas.php" class="hover:text-yellow-300">Reservas</a>
                    <a href="socios.php" class="hover:text-yellow-300">Socios</a>
                </nav>
            </div>
            <div class="flex items-center space-x-6">
                <span class="text-xl font-semibold">Admin</span>
                <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-8 py-4 rounded-xl font-black text-xl transition transform hover:scale-110">
                    SALIR
                </a>
            </div>
        </div>
    </div>
    <div class="pt-28"></div>

    <div class="container mx-auto px-6 py-12 max-w-7xl">

        <h1 class="text-7xl font-black text-center text-indigo-800 mb-12">Finanzas Generales</h1>

        <?php if ($mensaje): ?>
            <div class="text-center mb-8 bg-green-100 border border-green-400 text-green-800 px-10 py-6 rounded-3xl text-2xl font-bold inline-block">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <!-- RESUMEN -->
        <div class="grid md:grid-cols-3 gap-10 mb-16">
            <div class="bg-green-600 text-white p-12 rounded-3xl text-center shadow-2xl transform hover:scale-105 transition">
                <p class="text-7xl font-black">$<?= number_format($ingresos,0,',','.') ?></p>
                <p class="text-3xl mt-4">INGRESOS</p>
            </div>
            <div class="bg-red-600 text-white p-12 rounded-3xl text-center shadow-2xl transform hover:scale-105 transition">
                <p class="text-7xl font-black">$<?= number_format($egresos,0,',','.') ?></p>
                <p class="text-3xl mt-4">EGRESOS</p>
            </div>
            <div class="bg-gradient-to-r <?= $saldo>=0?'from-cyan-600 to-blue-700':'from-orange-600 to-red-700' ?> text-white p-12 rounded-3xl text-center shadow-2xl transform hover:scale-105 transition">
                <p class="text-8xl font-black">$<?= number_format($saldo,0,',','.') ?></p>
                <p class="text-4xl mt-4">SALDO</p>
            </div>
        </div>

        <!-- FORMULARIO RÁPIDO -->
        <form method="POST" class="bg-white p-10 rounded-3xl shadow-2xl mb-12">
            <div class="grid md:grid-cols-4 gap-8">
                <input name="descripcion" placeholder="Descripción (ej: Compra pintura)" required class="px-8 py-6 border-2 rounded-2xl text-xl">
                <select name="tipo" required class="px-8 py-6 border-2 rounded-2xl text-xl">
                    <option value="Ingreso">Ingreso</option>
                    <option value="Egreso">Egreso</option>
                </select>
                <input name="monto" type="number" placeholder="Monto" min="1" required class="px-8 py-6 border-2 rounded-2xl text-xl">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-black text-2xl py-6 rounded-2xl transition transform hover:scale-105">
                    GUARDAR
                </button>
            </div>
        </form>

        <!-- TABLA -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-indigo-700 text-white">
                    <tr>
                        <th class="p-6 text-left">Fecha</th>
                        <th class="p-6 text-left">Descripción</th>
                        <th class="p-6 text-left">Tipo</th>
                        <th class="p-6 text-right">Monto</th>
                        <th class="p-6 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($movimientos as $m): ?>
                    <tr class="border-b hover:bg-indigo-50 transition">
                        <td class="p-6"><?= date('d/m/Y', strtotime($m['Fecha'])) ?></td>
                        <td class="p-6 font-medium"><?= htmlspecialchars($m['Descripcion']) ?></td>
                        <td class="p-6">
                            <span class="<?= $m['Tipo']=='Ingreso'?'text-green-600':'text-red-600' ?> font-bold text-xl">
                                <?= $m['Tipo'] ?>
                            </span>
                        </td>
                        <td class="p-6 text-right font-black text-2xl <?= $m['Tipo']=='Ingreso'?'text-green-600':'text-red-600' ?>">
                            <?= $m['Tipo']=='Ingreso'?'+' : '-' ?>$<?= number_format($m['Monto'],0,',','.') ?>
                        </td>
                        <td class="p-6 text-center">
                            <a href="?delete=<?= $m['ID_movimiento'] ?>" 
                               onclick="return confirm('¿Eliminar este movimiento?')"
                               class="text-red-600 hover:text-red-800 font-bold text-lg">
                                Eliminar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($movimientos)): ?>
                    <tr><td colspan="5" class="text-center py-20 text-3xl text-gray-500">No hay movimientos aún</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>