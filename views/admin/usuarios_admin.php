
<!-- MENÚ FIJO SUPERIOR + SALIR -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-indigo-700 to-purple-800 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-5 flex justify-between items-center">
        <div class="flex items-center space-x-10">
            <h2 class="text-3xl font-black tracking-wider">
                <i class="fas fa-home mr-3"></i> JJVV La Flecha
            </h2>
            <nav class="hidden md:flex space-x-8 text-xl font-bold">
                <a href="finanzas.php" class="hover:text-yellow-300 transition">Finanzas</a>
                <a href="pagos.php" class="hover:text-yellow-300 transition">Cobranza</a>
                <a href="eventos_admin.php" class="hover:text-yellow-300 transition">Eventos</a>
                <a href="reservas.php" class="hover:text-yellow-300 transition">Reservas</a>
                <a href="socios.php" class="hover:text-yellow-300 transition">Socios</a>
            </nav>
        </div>
        <div class="flex items-center space-x-6">
            <span class="text-xl font-semibold">
                <i class="fas fa-user mr-2"></i> <?= htmlspecialchars($_SESSION['nombre'] ?? 'Admin') ?>
            </span>
            <a href="logout.php" class="bg-red-600 hover:bg-red-700 px-8 py-4 rounded-xl font-black text-xl transition transform hover:scale-110">
                SALIR
            </a>
        </div>
    </div>
</div>

<!-- Espacio para que no tape el contenido -->
<div class="pt-28"></div>
<?php
require_once 'config.php';

// Verifica acceso
if (!isset($_SESSION['loggedin']) || $_SESSION['rol'] !== 'admin') {
    redirect('login.php');
}

// Obtener usuarios
$usuarios = [];
try {
    $stmt = $pdo->query("SELECT rut, nombre, correo, rol, activo FROM usuario ORDER BY nombre ASC");
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="estilos_admin.css"> <!-- Asegúrate de que este archivo contenga tu CSS -->
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gestión de Usuarios</h1>
            <p>Panel de administración de vecinos registrados</p>
        </div>

        <table class="socios-table">
            <thead>
                <tr>
                    <th>RUT</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="6">No hay usuarios registrados.</td></tr>
                <?php else: foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['rut']) ?></td>
                        <td><?= htmlspecialchars($u['nombre']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['rol']) ?></td>
                        <td><?= $u['activo'] ? 'Sí' : 'No' ?></td>
                        <td>
                            <button class="btn edit">Editar</button>
                            <button class="btn delete">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn primary">Volver al Panel</a>
    </div>
</body>
</html>