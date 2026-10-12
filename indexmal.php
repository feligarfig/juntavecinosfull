<?php

define( 'WP_USE_THEMES', true );
require __DIR__ . '/wp-blog-header.php';



!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Socios</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include '../layout/header.php'; ?>
    <div class="container mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Gestión de Socios</h1>
            <a href="<?= BASE_URL ?>/socios/crear" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                + Nuevo Socio
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">RUT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre Completo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ocupación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($socios as $s): ?>
                    <tr>
                        <td class="px-6 py-4 font-medium"><?= htmlspecialchars($s['RUT']) ?></td>
                        <td class="px-6 py-4">
                            <?= htmlspecialchars($s['Nombre'] . ' ' . $s['Apellido1'] . ' ' . ($s['Apellido2'] ?? '')) ?>
                        </td>
                        <td class="px-6 py-4"><?= htmlspecialchars($s['Ocupación'] ?? '—') ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= ($s['estado'] ?? '') == 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= ucfirst($s['estado'] ?? '—') ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="<?= BASE_URL ?>/socios/editar/<?= $s['RUT'] ?>" class="text-blue-600 hover:text-blue-900">Editar</a>
                            <a href="<?= BASE_URL ?>/socios/eliminar/<?= $s['RUT'] ?>" class="text-red-600 hover:text-red-900 ml-3" onclick="return confirm('¿Eliminar socio?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>