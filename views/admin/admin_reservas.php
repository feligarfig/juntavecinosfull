<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';

$nombre_usuario = $_SESSION['nombre'] ?? 'Admin';

// === ACCIONES DEL ADMIN ===
if ($_GET['aprobar'] ?? false) {
    $id = (int)$_GET['aprobar'];
    $pdo->prepare("UPDATE reserva SET estado_reserva = 'Aprobada' WHERE idReserva = ?")->execute([$id]);
    $msg = "Reserva aprobada";
}
if ($_GET['rechazar'] ?? false) {
    $id = (int)$_GET['rechazar'];
    $pdo->prepare("UPDATE reserva SET estado_reserva = 'Rechazada' WHERE idReserva = ?")->execute([$id]);
    $msg = "Reserva rechazada";
}

// === FILTROS ===
$where = "1=1";
$params = [];
if ($_GET['estado'] ?? '' !== '') {
    $where .= " AND estado_reserva = ?";
    $params[] = $_GET['estado'];
}
if ($_GET['tipo'] ?? '' !== '') {
    $where .= " AND tipo_reserva = ?";
    $params[] = $_GET['tipo'];
}
if ($_GET['buscar'] ?? '' !== '') {
    $buscar = "%" . $_GET['buscar'] . "%";
    $where .= " AND (nombre_reservante LIKE ? OR RUT LIKE ? OR telefono LIKE ?)";
    $params[] = $buscar; $params[] = $buscar; $params[] = $buscar;
}

// === CARGAR RESERVAS ===
$sql = "SELECT * FROM reserva WHERE $where ORDER BY 
        CASE WHEN estado_reserva = 'Pendiente' THEN 0 ELSE 1 END,
        fecha_reserva DESC, hora_inicio ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reservas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reservas • Panel Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); }
        .hover-scale { transition: all 0.3s ease; }
        .hover-scale:hover { transform: translateY(-8px); box-shadow: 0 20px 40px -12px rgba(0,0,0,0.2); }
    </style>
</head>
<body class="min-h-screen text-gray-800">

<!-- BARRA SUPERIOR -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-5 flex justify-between items-center">
        <a href="dashboard.php" class="flex items-center gap-3 text-3xl font-black hover:scale-110 transition">
            <i class="fas fa-home"></i> Panel Admin
        </a>
        <div class="flex items-center gap-8">
            <span class="hidden sm:block text-xl font-semibold">
                <i class="fas fa-user-circle mr-2"></i> <?= htmlspecialchars($nombre_usuario) ?>
            </span>
            <a href="login.php?logout=1" onclick="return confirm('¿Cerrar sesión?')"
               class="bg-red-600 hover:bg-red-700 px-8 py-4 rounded-full font-black transition hover:scale-110">
                <i class="fas fa-sign-out-alt mr-2"></i> SALIR
            </a>
        </div>
    </div>
</div>
<div class="pt-28"></div>

<div class="container mx-auto px-6 max-w-7xl py-12">
    <h1 class="text-7xl font-black text-center text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-10">
        GESTIÓN DE RESERVAS
    </h1>

    <?php if (isset($msg)): ?>
        <div class="text-center mb-8">
            <div class="inline-block bg-emerald-100 border-4 border-emerald-600 text-emerald-800 px-12 py-6 rounded-3xl text-2xl font-bold shadow-lg">
                <?= htmlspecialchars($msg) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- FILTROS -->
    <div class="bg-white rounded-3xl shadow-2xl p-8 mb-10">
        <form method="GET" class="grid md:grid-cols-4 gap-6">
            <input type="text" name="buscar" value="<?= $_GET['buscar']??'' ?>" placeholder="Buscar nombre, RUT o teléfono" 
                   class="px-6 py-4 rounded-2xl border-4 border-emerald-300 text-xl focus:border-emerald-600 outline-none">

            <select name="estado" class="px-6 py-4 rounded-2xl border-4 border-emerald-300 text-xl focus:border-emerald-600 outline-none">
                <option value="">Todos los estados</option>
                <option value="Pendiente" <?= ($_GET['estado']??'')==='Pendiente'?'selected':'' ?>>Pendientes</option>
                <option value="Aprobada" <?= ($_GET['estado']??'')==='Aprobada'?'selected':'' ?>>Aprobadas</option>
                <option value="Rechazada" <?= ($_GET['estado']??'')==='Rechazada'?'selected':'' ?>>Rechazadas</option>
            </select>

            <select name="tipo" class="px-6 py-4 rounded-2xl border-4 border-emerald-300 text-xl focus:border-emerald-600 outline-none">
                <option value="">Todos los tipos</option>
                <option value="Feria artesanal" <?= ($_GET['tipo']??'')==='Feria artesanal'?'selected':'' ?>>Feria artesanal</option>
                <option value="Cumpleaños" <?= ($_GET['tipo']??'')==='Cumpleaños'?'selected':'' ?>>Cumpleaños</option>
                <option value="Reunión" <?= ($_GET['tipo']??'')==='Reunión'?'selected':'' ?>>Reunión</option>
                <!-- Agrega más si quieres -->
            </select>

            <button type="submit" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-black text-xl py-4 rounded-2xl shadow-lg transition hover:scale-105">
                <i class="fas fa-search mr-2"></i> FILTRAR
            </button>
        </form>
    </div>

    <!-- TABLA DE RESERVAS -->
    <?php if (empty($reservas)): ?>
        <p class="text-center text-6xl text-gray-400 font-bold py-32">No hay reservas con estos filtros</p>
    <?php else: ?>
        <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($reservas as $r): 
                $color = $r['estado_reserva'] === 'Pendiente' ? 'border-amber-400 bg-amber-50' : 
                        ($r['estado_reserva'] === 'Aprobada' ? 'border-emerald-500 bg-emerald-50' : 'border-red-500 bg-red-50');
            ?>
                <div class="bg-white rounded-3xl shadow-2xl border-8 <?= $color ?> overflow-hidden hover-scale">
                    <div class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white p-6 text-center">
                        <p class="text-4xl font-black"><?= date('d/m/Y', strtotime($r['fecha_reserva'])) ?></p>
                        <p class="text-2xl mt-2"><i class="fas fa-clock"></i> <?= substr($r['hora_inicio'],0,5) ?> - <?= substr($r['hora_fin'],0,5) ?></p>
                    </div>

                    <div class="p-8">
                        <h3 class="text-3xl font-black text-emerald-800 mb-4"><?= htmlspecialchars($r['nombre_reservante']) ?></h3>
                        <p class="text-xl text-gray-700 mb-2"><i class="fas fa-id-card text-emerald-600"></i> <?= htmlspecialchars($r['RUT']) ?></p>
                        <p class="text-xl text-gray-700 mb-2"><i class="fas fa-phone text-emerald-600"></i> <?= htmlspecialchars($r['telefono']) ?></p>
                        <p class="text-xl text-gray-700 mb-6"><i class="fas fa-tag text-emerald-600"></i> <strong><?= htmlspecialchars($r['tipo_reserva']) ?></strong></p>
                        
                        <?php if ($r['motivo']): ?>
                            <p class="text-lg italic text-gray-600 mb-6">"<?= htmlspecialchars($r['motivo']) ?>"</p>
                        <?php endif; ?>

                        <p class="text-center mb-6">
                            <span class="px-6 py-3 rounded-full font-black text-xl
                                <?= $r['estado_reserva']==='Pendiente'?'bg-amber-500 text-white':
                                   ($r['estado_reserva']==='Aprobada'?'bg-emerald-600 text-white':'bg-red-600 text-white') ?>">
                                <?= $r['estado_reserva'] ?>
                            </span>
                        </p>

                        <?php if ($r['estado_reserva'] === 'Pendiente'): ?>
                            <div class="flex gap-4">
                                <a href="?aprobar=<?= $r['idReserva'] ?>" onclick="return confirm('¿Aprobar esta reserva?')"
                                   class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xl py-4 rounded-xl text-center transition">
                                    APROBAR
                                </a>
                                <a href="?rechazar=<?= $r['idReserva'] ?>" onclick="return confirm('¿Rechazar esta reserva?')"
                                   class="flex-1 bg-red-600 hover:bg-red-700 text-white font-black text-xl py-4 rounded-xl text-center transition">
                                    RECHAZAR
                                </a>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-xl font-bold text-gray-500">Ya gestionada</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>