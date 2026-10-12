<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

// ================== GENERAR PDF (también en verde) ==================
if (isset($_GET['pdf'])) {
    require_once 'tcpdf/tcpdf.php';

    $ingresos = $pdo->query("SELECT COALESCE(SUM(monto),0) FROM movimientos_financieros WHERE tipo='ingreso'")->fetchColumn();
    $egresos = $pdo->query("SELECT COALESCE(SUM(monto),0) FROM movimientos_financieros WHERE tipo='egreso'")->fetchColumn();
    $saldo = $ingresos - $egresos;

    $movimientos = $pdo->query("SELECT * FROM movimientos_financieros ORDER BY fecha_movimiento DESC, id_movimiento DESC")
        ->fetchAll(PDO::FETCH_ASSOC);

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetCreator('JJVV La Flecha');
    $pdf->SetTitle('Reporte de Caja - JJVV La Flecha');
    $pdf->SetMargins(15, 20, 15);
    $pdf->AddPage();

    // Encabezado verde
    $pdf->SetFont('helvetica', 'B', 22);
    $pdf->SetTextColor(0, 100, 0);
    $pdf->Cell(0, 15, 'REPORTE DE CAJA GENERAL', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 14);
    $pdf->Cell(0, 10, 'Junta de Vecinos La Flecha', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Generado el ' . date('d/m/Y \a \l\a\s H:i'), 0, 1, 'C');
    $pdf->Ln(10);

    // Resumen en verdes
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetFillColor(200, 255, 200);   // Verde muy claro
    $pdf->Cell(60, 12, 'INGRESOS', 1, 0, 'C', true);
    $pdf->SetFillColor(144, 238, 144);   // Verde claro
    $pdf->Cell(60, 12, 'EGRESOS', 1, 0, 'C', true);
    $pdf->SetFillColor($saldo >= 0 ? 173 : 255, $saldo >= 0 ? 255 : 204, $saldo >= 0 ? 173 : 204);
    $pdf->Cell(60, 12, 'SALDO', 1, 1, 'C', true);

    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(0, 100, 0);
    $pdf->Cell(60, 14, '$' . number_format($ingresos, 0, ',', '.'), 1, 0, 'C', true);
    $pdf->Cell(60, 14, '$' . number_format($egresos, 0, ',', '.'), 1, 0, 'C', true);
    $pdf->Cell(60, 14, '$' . number_format($saldo, 0, ',', '.'), 1, 1, 'C', true);
    $pdf->Ln(15);

    // Tabla
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetFillColor(220, 255, 220);
    $pdf->Cell(25, 10, 'Fecha', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Tipo', 1, 0, 'C', true);
    $pdf->Cell(90, 10, 'Descripción', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Monto', 1, 1, 'C', true);

    $pdf->SetFont('helvetica', '', 10);
    foreach ($movimientos as $m) {
        $pdf->Cell(25, 8, date('d/m/Y', strtotime($m['fecha_movimiento'])), 1);
        $pdf->Cell(25, 8, $m['tipo']=='ingreso'?'INGRESO':'EGRESO', 1, 0, 'C');
        $pdf->Cell(90, 8, mb_substr($m['descripción']?:'Sin descripción',0,55), 1);
        $pdf->Cell(35, 8, '$'.number_format($m['monto'],0,',','.'), 1, 1, 'R');
    }

    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 10);
    $pdf->SetTextColor(0, 120, 0);
    $pdf->Cell(0, 10, 'Sistema JJVV La Flecha', 0, 1, 'C');

    $pdf->Output('Reporte_Caja_Verde_' . date('d-m-Y') . '.pdf', 'D');
    exit;
}

// ================== LÓGICA NORMAL (igual que antes) ==================
$mensaje = $_GET['m'] ?? '';
if (isset($_GET['logout'])) { session_destroy(); header("Location: login.php"); exit; }

$editar = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editar = $pdo->query("SELECT * FROM movimientos_financieros WHERE id_movimiento = $id")->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $monto = (int)$_POST['monto'];
    $descripcion = trim($_POST['descripcion'] ?? '');
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $id_edit = (int)($_POST['id_edit'] ?? 0);

    if ($monto > 0 && in_array($tipo, ['ingreso', 'egreso'])) {
        if ($id_edit > 0) {
            $pdo->prepare("UPDATE movimientos_financieros SET tipo=?, monto=?, descripción=?, fecha_movimiento=? WHERE id_movimiento=?")
                ->execute([$tipo, $monto, $descripcion, $fecha, $id_edit]);
            $mensaje = "Movimiento actualizado";
        } else {
            $pdo->prepare("INSERT INTO movimientos_financieros (RUT_admin, tipo, monto, descripción, fecha_movimiento) VALUES (?, ?, ?, ?, ?)")
                ->execute([$_SESSION['rut_usuario']??null, $tipo, $monto, $descripcion, $fecha]);
            $mensaje = "Movimiento registrado";
        }
    } else $mensaje = "Error en los datos";
    header("Location: finanzas.php?m=" . urlencode($mensaje));
    exit;
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM movimientos_financieros WHERE id_movimiento = ?")->execute([(int)$_GET['delete']]);
    header("Location: finanzas.php?m=Movimiento eliminado");
    exit;
}

$ingresos = $pdo->query("SELECT COALESCE(SUM(monto),0) FROM movimientos_financieros WHERE tipo='ingreso'")->fetchColumn();
$egresos = $pdo->query("SELECT COALESCE(SUM(monto),0) FROM movimientos_financieros WHERE tipo='egreso'")->fetchColumn();
$saldo = $ingresos - $egresos;

$movimientos = $pdo->query("SELECT * FROM movimientos_financieros ORDER BY fecha_movimiento DESC, id_movimiento DESC")
    ->fetchAll(PDO::FETCH_ASSOC);

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
    <style>
        body {font-family:'Nunito',sans-serif; background: linear-gradient(135deg, #d4fce4, #a7f3d0);}
    </style>
</head>
<body class="min-h-screen">

<!-- BARRA SUPERIOR VERDE OSCURO -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-800 to-green-800 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="dashboard.php" class="text-2xl font-black hover:scale-110 transition">Panel Principal</a>
        <div class="flex items-center gap-6">
            <span class="text-xl"><i class="fas fa-user-circle"></i> <?=htmlspecialchars($nombre_usuario)?></span>
            <a href="?logout=1" onclick="return confirm('¿Cerrar sesión?')" 
               class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-bold transition">Cerrar Sesión</a>
        </div>
    </div>
</div>
<div class="pt-24"></div>

<div class="container mx-auto px-6 py-12 max-w-7xl">

    <h1 class="text-6xl font-black text-center mb-8 text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-green-600">
        CAJA GENERAL JJVV LA FLECHA
    </h1>

    <!-- BOTÓN PDF VERDE -->
    <div class="text-center mb-10">
        <a href="?pdf=1" class="inline-block bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-black text-2xl py-5 px-20 rounded-full shadow-2xl transform hover:scale-110 transition">
            <i class="fas fa-file-pdf mr-3"></i> DESCARGAR REPORTE PDF
        </a>
    </div>

    <?php if ($mensaje): ?>
        <div class="text-center mb-8 p-5 rounded-3xl text-2xl font-bold bg-emerald-100 text-emerald-800 border-4 border-emerald-300">
            <?=htmlspecialchars($mensaje)?>
        </div>
    <?php endif; ?>

    <!-- RESUMEN COMPACTO TODO VERDE -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-gradient-to-br from-emerald-500 to-green-600 text-white rounded-3xl shadow-2xl p-6 text-center">
            <p class="text-2xl font-bold mb-1">INGRESOS</p>
            <p class="text-4xl font-black">$<?=number_format($ingresos,0,',','.')?></p>
        </div>
        <div class="bg-gradient-to-br from-lime-500 to-emerald-600 text-white rounded-3xl shadow-2xl p-6 text-center">
            <p class="text-2xl font-bold mb-1">EGRESOS</p>
            <p class="text-4xl font-black">$<?=number_format($egresos,0,',','.')?></p>
        </div>
        <div class="bg-gradient-to-br <?= $saldo >= 0 ? 'from-teal-600 to-emerald-700' : 'from-amber-500 to-lime-600' ?> text-white rounded-3xl shadow-2xl p-6 text-center ring-8 ring-white/30">
            <p class="text-3xl font-black mb-1">SALDO</p>
            <p class="text-5xl font-black">$<?=number_format($saldo,0,',','.')?></p>
        </div>
    </div>

    <!-- BOTONES +INGRESO / +EGRESO VERDES -->
    <div class="flex justify-center gap-12 mb-12">
        <button onclick="openModal('ingreso')" 
                class="bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-black text-3xl py-6 px-24 rounded-full shadow-2xl transform hover:scale-110 transition">
            + INGRESO
        </button>
        <button onclick="openModal('egreso')" 
                class="bg-gradient-to-r from-lime-600 to-emerald-700 hover:from-lime-700 hover:to-emerald-800 text-white font-black text-3xl py-6 px-24 rounded-full shadow-2xl transform hover:scale-110 transition">
            − EGRESO
        </button>
    </div>

    <!-- TABLA -->
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="bg-gradient-to-r from-emerald-700 to-green-700 text-white p-6">
            <h2 class="text-4xl font-black text-center">Historial de Movimientos</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-emerald-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold text-emerald-800">Fecha</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-emerald-800">Tipo</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-emerald-800">Descripción</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-emerald-800">Monto</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-emerald-800">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-100">
                    <?php foreach ($movimientos as $m): ?>
                    <tr class="hover:bg-emerald-50 transition">
                        <td class="px-6 py-4"><?=date('d/m/Y', strtotime($m['fecha_movimiento']))?></td>
                        <td class="px-6 py-4">
                            <span class="px-4 py-2 rounded-full font-bold text-sm <?= $m['tipo']=='ingreso'?'bg-emerald-200 text-emerald-800':'bg-lime-200 text-lime-800' ?>">
                                <?=strtoupper($m['tipo'])?>
                            </span>
                        </td>
                        <td class="px-6 py-4"><?=htmlspecialchars($m['descripción']?:'Sin descripción')?></td>
                        <td class="px-6 py-4 text-right font-bold text-emerald-700 text-xl">
                            $<?=number_format($m['monto'],0,',','.')?>
                        </td>
                        <td class="px-6 py-4 text-center space-x-6">
                            <a href="?edit=<?=$m['id_movimiento']?>" class="text-emerald-600 hover:text-emerald-800 font-bold">Editar</a>
                            <a href="?delete=<?=$m['id_movimiento']?>" onclick="return confirm('¿ELIMINAR este movimiento?')" class="text-red-600 hover:text-red-800 font-bold">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($movimientos)): ?>
                    <tr><td colspan="5" class="text-center py-20 text-3xl text-emerald-300">No hay movimientos aún</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL VERDE -->
<div id="modal" class="fixed inset-0 bg-black/70 hidden flex items-center justify-center z-50" onclick="if(event.target===this) closeModal()">
    <div class="bg-white rounded-3xl shadow-3xl p-10 max-w-2xl w-full mx-6">
        <h2 id="titulo" class="text-5xl font-black text-center mb-10 text-emerald-600">Registrar Movimiento</h2>
        <form method="POST">
            <input type="hidden" name="tipo" id="tipo">
            <input type="hidden" name="id_edit" id="id_edit" value="0">

            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-3">Descripción</label>
                    <input type="text" name="descripcion" id="desc" required placeholder="Ej: Cuota socio • Compra de pintura" 
                           class="w-full px-6 py-4 border-4 border-emerald-200 rounded-3xl text-xl focus:border-emerald-600 outline-none">
                </div>
                <div>
                    <label class="block text-2xl font-bold text-gray-700 mb-3">Monto</label>
                    <input type="number" name="monto" id="monto" required min="1" 
                           class="w-full px-6 py-4 border-4 border-emerald-200 rounded-3xl text-2xl font-bold text-center focus:border-emerald-600 outline-none">
                </div>
            </div>
            <div class="mb-10">
                <label class="block text-2xl font-bold text-gray-700 mb-3">Fecha</label>
                <input type="date" name="fecha" id="fecha" required value="<?=date('Y-m-d')?>" 
                       class="w-full px-6 py-4 border-4 border-emerald-200 rounded-3xl text-xl text-center focus:border-emerald-600 outline-none">
            </div>

            <div class="flex gap-8 justify-center">
                <button type="submit" class="bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-black text-3xl py-6 px-20 rounded-3xl shadow-2xl transform hover:scale-105 transition">
                    Guardar
                </button>
                <button type="button" onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-black text-3xl py-6 px-16 rounded-3xl">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(tipo, id=0, desc='', monto='', fecha='<?=date('Y-m-d')?>') {
    document.getElementById('modal').classList.remove('hidden');
    document.getElementById('tipo').value = tipo;
    document.getElementById('id_edit').value = id;
    document.getElementById('desc').value = desc;
    document.getElementById('monto').value = monto;
    document.getElementById('fecha').value = fecha;

    const titulo = document.getElementById('titulo');
    titulo.textContent = (id > 0 ? 'Editar' : 'Registrar') + ' ' + (tipo === 'ingreso' ? 'Ingreso' : 'Egreso');
    titulo.className = 'text-5xl font-black text-center mb-10 text-emerald-600';
}
function closeModal() {
    document.getElementById('modal').classList.add('hidden');
    document.getElementById('id_edit').value = '0';
}
<?php if ($editar): ?>
    openModal('<?=$editar['tipo']?>', <?=$editar['id_movimiento']?>, <?=json_encode($editar['descripción'])?>, <?=$editar['monto']?>, '<?=$editar['fecha_movimiento']?>');
<?php endif; ?>
</script>

</body>
</html>