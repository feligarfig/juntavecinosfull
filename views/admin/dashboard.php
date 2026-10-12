<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        body { font-family: 'Nunito', sans-serif; }
        .btn-hover { transition: all 0.4s ease; }
        .btn-hover:hover { transform: translateY(-12px) scale(1.05); box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen">

    <<!-- BARRA SUPERIOR CORREGIDA -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-5 flex justify-between items-center">
        <!-- BOTÓN IZQUIERDO: ahora lleva al inicio público -->
        <a href="../..index.php" class="flex items-center gap-4 text-3xl font-black hover:scale-110 transition">
            <i class="fas fa-home"></i> Volver al Inicio
        </a>

        <!-- DERECHA: nombre + cerrar sesión -->
        <div class="flex items-center gap-8">
            <span class="text-xl">
                <i class="fas fa-user-circle mr-3"></i>
                <?= htmlspecialchars($_SESSION['nombre'] ?? 'Administrador') ?>
            </span>
            <a href="../../logout.php" 
               onclick="return confirm('¿Cerrar sesión?')"
               class="bg-red-600 hover:bg-red-700 px-10 py-4 rounded-full font-black text-xl transition hover:scale-105 shadow-lg">
                <i class="fas fa-sign-out-alt mr-3"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</div>
    <div class="pt-32 pb-20 px-6	msg">
        <div class="container mx-auto max-w-7xl text-center">
            <h1 class="text-6xl md:text-8xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-6">
                Panel de Control
            </h1>
            <p class="text-3xl font-bold text-emerald-800 mb-16">
                Junta de Vecinos Villa La Flecha - Coihueco
            </p>

            <!-- GRID DE OPCIONES -->
            <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                
                <a href="noticias.php" class="bg-white rounded-3xl shadow-2xl p-12 btn-hover group">
                    <i class="fas fa-newspaper text-7xl text-emerald-600 mb-6 group-hover:scale-110 transition"></i>
                    <h3 class="text-3xl font-black text-emerald-800">Noticias y Eventos</h3>
                </a>

                <a href="finanzas.php" class="bg-white rounded-3xl shadow-2xl p-12 btn-hover group">
                    <i class="fas fa-chart-line text-7xl text-teal-600 mb-6 group-hover:scale-110 transition"></i>
                    <h3 class="text-3xl font-black text-teal-800">Finanzas</h3>
                </a>

                <a href="socios.php" class="bg-white rounded-3xl shadow-2xl p-12 btn-hover group">
                    <i class="fas fa-users text-7xl text-emerald-600 mb-6 group-hover:scale-110 transition"></i>
                    <h3 class="text-3xl font-black text-emerald-800">Socios</h3>
                </a>

                <a href="views/admin/pagos.php" class="bg-white rounded-3xl shadow-2xl p-12 btn-hover group">
                    <i class="fas fa-money-bill-wave text-7xl text-green-600 mb-6 group-hover:scale-110 transition"></i>
                    <h3 class="text-3xl font-black text-green-800">Cobranza y Pagos</h3>
                </a>

                <a href="admin_reservas.php" class="bg-white rounded-3xl shadow-2xl p-12 btn-hover group">
                    <i class="fas fa-calendar-check text-7xl text-cyan-600 mb-6 group-hover:scale-110 transition"></i>
                    <h3 class="text-3xl font-black text-cyan-800">Reservas de Sede</h3>
                </a>

                <a href="gestion_usuarios.php" class="bg-white rounded-3xl shadow-2xl p-12 btn-hover group">
                    <i class="fas fa-user-shield text-7xl text-purple-600 mb-6 group-hover:scale-110 transition"></i>
                    <h3 class="text-3xl font-black text-purple-800">Gestión de Usuarios</h3>
                </a>

                <a href="galeria_admin.php" class="bg-white rounded-3xl shadow-2xl p-12 btn-hover group">
                    <i class="fas fa-images text-7xl text-pink-600 mb-6 group-hover:scale-110 transition"></i>
                    <h3 class="text-3xl font-black text-pink-800">Galería de Fotos</h3>
                </a>

                <a href="inventario.php" class="bg-white rounded-3xl shadow-2xl p-12 btn-hover group">
                    <i class="fas fa-boxes text-7xl text-orange-600 mb-6 group-hover:scale-110 transition"></i>
                    <h3 class="text-3xl font-black text-orange-800">Inventario</h3>
                </a>

            </div>

            <!-- FOOTER -->
            <div class="mt-20 text-center">
                <p class="text-2xl font-bold text-emerald-700">
                    © <?= date('Y') ?> JJVV La Flecha • Panel Administrativo
                </p>
            </div>
        </div>
    </div>

</body>
</html>