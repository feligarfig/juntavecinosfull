<?php
require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM socios ORDER BY Apellido1, Apellido2, Nombre");
    $socios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar socios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Socios - Junta de Vecinos La Flecha</title>
    <link rel="stylesheet" href="socios.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Gestión de Socios</h1>
            <p>Administra los datos de los socios de la Junta de Vecinos La Flecha</p>
            <button class="btn primary" onclick="openModal('add')">Agregar Nuevo Socio</button>
        </div>

        <table class="socios-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Dirección</th>
                    <th>Estado</th>
                    <th>Ocupación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($socios as $socio): ?>
                <tr>
                    <td><?= htmlspecialchars($socio['Nombre'] . ' ' . $socio['Apellido1'] . ' ' . $socio['Apellido2']) ?></td>
                    <td><?= htmlspecialchars($socio['RUT']) ?></td>
                    <td><?= htmlspecialchars($socio['Fecha_nacimiento'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($socio['Domicilio']) ?></td>
                    <td><?= htmlspecialchars($socio['estado']) ?></td>
                    <td><?= htmlspecialchars($socio['Ocupación']) ?></td>
                    <td>
                        <button class="btn edit" onclick="openModal('edit')">Editar</button>
                        <button class="btn delete">Eliminar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Modal para Crear/Editar Socio -->
        <div id="modal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2 id="modal-title">Agregar Socio</h2>
                <form method="POST" action="guardar_socio.php">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre" id="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido1">Apellido Paterno</label>
                        <input type="text" name="apellido1" id="apellido1" required>
                    </div>
                    <div class="form-group">
                        <label for="apellido2">Apellido Materno</label>
                        <input type="text" name="apellido2" id="apellido2" required>
                    </div>
                    <div class="form-group">
                        <label for="rut">RUT</label>
                        <input type="text" name="rut" id="rut" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento">
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" name="direccion" id="direccion" required>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select name="estado" id="estado" required>
                            <option value="Activo">Activo</option>
                            <option value="Inactivo">Inactivo</option>
                            <option value="Fallecido">Fallecido</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="ocupacion">Ocupación</label>
                        <input type="text" name="ocupacion" id="ocupacion" required>
                    </div>
                    <button type="submit" class="btn primary">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(mode) {
            const modal = document.getElementById('modal');
            const title = document.getElementById('modal-title');
            title.textContent = mode === 'add' ? 'Agregar Socio' : 'Editar Socio';
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }
    </script>
</body>
</html>