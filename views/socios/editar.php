<?php if (!is_admin()) redirect('login'); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Socio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include '../layout/header.php'; ?>
    <div class="container mx-auto p-6 max-w-2xl">
        <h1 class="text-3xl font-bold mb-6">Editar Socio</h1>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-white p-6 rounded-lg shadow space-y-4">
            <div>
                <label class="block font-medium text-gray-700">RUT *</label>
                <input type="text" name="rut" value="<?= htmlspecialchars($socio['RUT']) ?>" required 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-gray-700">Nombre *</label>
                    <input type="text" name="nombre" value="<?= htmlspecialchars($socio['Nombre']) ?>" required 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Apellido Paterno *</label>
                    <input type="text" name="apellido1" value="<?= htmlspecialchars($socio['Apellido1']) ?>" required 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block font-medium text-gray-700">Apellido Materno</label>
                <input type="text" name="apellido2" value="<?= htmlspecialchars($socio['Apellido2'] ?? '') ?>" 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block font-medium text-gray-700">Fecha Nacimiento</label>
                    <input type="date" name="fecha_nac" value="<?= $socio['Fecha_nacimiento'] ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block font-medium text-gray-700">Ocupación</label>
                    <input type="text" name="ocupacion" value="<?= htmlspecialchars($socio['Ocupación'] ?? '') ?>" 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block font-medium text-gray-700">Domicilio</label>
                <input type="text" name="domicilio" value="<?= htmlspecialchars($socio['Domicilio'] ?? '') ?>" 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block font-medium text-gray-700">Estado</label>
                <select name="estado" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="activo" <?= ($socio['estado'] ?? '') == 'activo' ? 'selected' : '' ?>>Activo</option>
                    <option value="inactivo" <?= ($socio['estado'] ?? '') == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                    Actualizar
                </button>
                <a href="<?= BASE_URL ?>/socios" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</body>
</html>