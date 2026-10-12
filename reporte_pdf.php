<?php
require_once 'config.php';

// ESTA ES LA LÍNEA MÁGICA QUE FUNCIONA SIEMPRE
require_once 'tcpdf/tcpdf.php';

// === FILTROS (opcional) ===
$desde = $_GET['desde'] ?? '';
$hasta = $_GET['hasta'] ?? '';

$where = "WHERE 1=1";
$params = [];
if ($desde) { $where .= " AND fecha_movimiento >= ?"; $params[] = $desde; }
if ($hasta) { $where .= " AND fecha_movimiento <= ?"; $params[] = $hasta; }

$stmt = $pdo->prepare("SELECT * FROM movimientos_financieros $where ORDER BY fecha_movimiento DESC");
$stmt->execute($params);
$movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totales
$totalIngresos = $totalEgresos = 0;
foreach ($movimientos as $m) {
    if ($m['tipo'] === 'Ingreso') $totalIngresos += $m['monto'];
    else $totalEgresos += $m['monto'];
}
$saldo = $totalIngresos - $totalEgresos;

// Período
$periodo = $desde && $hasta ? "Del ".date('d/m/Y',strtotime($desde))." al ".date('d/m/Y',strtotime($hasta)) : "Todo el historial";

// ====== PDF ======
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('JJVV La Flecha');
$pdf->SetAuthor('Sistema Financiero');
$pdf->SetTitle('Reporte de Movimientos');
$pdf->SetMargins(15, 20, 15);
$pdf->SetAutoPageBreak(true, 20);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();

// Título
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(0, 102, 102);
$pdf->Cell(0, 15, 'JUNTA DE VECINOS LA FLECHA', 0, 1, 'C');
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'REPORTE DE MOVIMIENTOS FINANCIEROS', 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(100,100,100);
$pdf->Cell(0, 8, "Período: $periodo", 0, 1, 'C');
$pdf->Cell(0, 8, "Generado el " . date('d/m/Y \a \l\a\s H:i'), 0, 1, 'C');
$pdf->Ln(10);

// Tabla
$html = '<table border="1" cellpadding="8" cellspacing="0">
<thead>
<tr style="background-color:#064e3b;color:white;font-weight:bold;">
    <th width="15%">Fecha</th>
    <th width="12%">Tipo</th>
    <th width="18%">RUT Admin</th>
    <th width="35%">Descripción</th>
    <th width="20%" align="right">Monto</th>
</tr>
</thead><tbody>';

foreach ($movimientos as $m) {
    $bg = $m['tipo']=='Ingreso' ? '#d1fae5' : '#fee2e2';
    $signo = $m['tipo']=='Ingreso' ? '+' : '-';
    $monto = '$'.number_format($m['monto'],0,',','.');
    $desc = htmlspecialchars($m['Descripción'] ?? '-');
    $html .= "<tr style=\"background-color:$bg;\">
        <td>".date('d/m/Y', strtotime($m['fecha_movimiento']))."</td>
        <td><strong>{$m['tipo']}</strong></td>
        <td>".($m['RUT_admin']?:'-')."</td>
        <td>$desc</td>
        <td align=\"right\"><strong>$signo $monto</strong></td>
    </tr>";
}
$html .= '</tbody></table>';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Ln(15);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor(34,153,84);
$pdf->Cell(0,10,'Total Ingresos: $'.number_format($totalIngresos,0,',','.'),0,1,'C');
$pdf->SetTextColor(220,53,69);
$pdf->Cell(0,10,'Total Egresos:  $'.number_format($totalEgresos,0,',','.'),0,1,'C');
$pdf->SetTextColor(0,102,102);
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0,15,'SALDO NETO: $'.number_format($saldo,0,',','.'),0,1,'C');

$pdf->Ln(20);
$pdf->SetFont('helvetica', '', 11);
$pdf->Cell(0,10,'_______________________________',0,1,'C');
$pdf->Cell(0,10,'Firma Presidente / Tesorero',0,1,'C');

$pdf->Output('Reporte_Financiero_'.date('Y-m-d').'.pdf', 'D');