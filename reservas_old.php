<?php
// 🚨 Nota: La lógica asume que la conexión está funcionando y que ya hay un login
require_once '_conexion.php'; 

$mensaje = null;
$reservas = [];

try {
    $conn = Conexion::conectar();

    // LÓGICA PARA GUARDAR UNA NUEVA RESERVA (desde el modal)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $socio_nombre = $_POST['nombre'] ?? '';
        $socio_correo = $_POST['correo'] ?? '';
        $actividad = $_POST['actividad'] ?? '';
        $fecha = $_POST['fecha'] ?? '';
        $hora_inicio = $_POST['hora'] ?? '';
        $duracion_horas = (int)($_POST['duracion'] ?? 0);
        
        if (!empty($socio_nombre) && !empty($socio_correo) && !empty($actividad) && !empty($fecha) && $duracion_horas > 0) {
            
            $sql = "INSERT INTO reserva (socio_nombre, socio_correo, actividad, fecha, hora_inicio, duracion_horas, estado) 
                    VALUES (:nombre, :correo, :actividad, :fecha, :hora, :duracion, 'Pendiente')";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':nombre' => $socio_nombre,
                ':correo' => $socio_correo,
                ':actividad' => $actividad,
                ':fecha' => $fecha,
                ':hora' => $hora_inicio,
                ':duracion' => $duracion_horas
            ]);

            $mensaje = "✅ Reserva creada y enviada a la administración para aprobación.";
            // Evitar que el formulario se envíe de nuevo al recargar
            header("Location: reservas.php?success=" . urlencode($mensaje));
            exit();

        } else {
            $mensaje = "Error: Faltan campos obligatorios en la reserva.";
        }
    }
    
    // LÓGICA PARA LEER LAS RESERVAS
    // Los socios ven solo las reservas aprobadas y las propias (si tuviéramos login de socios)
    // Pero por ahora, mostraremos todas las reservas activas (no rechazadas)
    $sql_read = "SELECT * FROM reserva ORDER BY fecha_reserva DESC, hora_inicio DESC";
    $stmt_read = $conn->query($sql_read);
    $reservas = $stmt_read->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Si la conexión falla, se atrapa aquí
    $mensaje = "Error de Base de Datos: " . $e->getMessage();
}

// Mostrar el mensaje de éxito después de la redirección
if (isset($_GET['success'])) {
    $mensaje = htmlspecialchars($_GET['success']);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas de la Sede - Junta de Vecinos La Flecha</title>
    <style>
        /* Estilos básicos CSS (Reservas.css simulado aquí para no crear otro archivo) */
        body { font-family: 'Roboto', sans-serif; background-color: #f4f7f6; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn { padding: 8px 15px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; }
        .btn.primary { background-color: #3b82f6; color: white; }
        .btn.primary:hover { background-color: #2563eb; }
        .btn.edit { background-color: #facc15; color: #333; }
        .btn.delete { background-color: #ef4444; color: white; }
        .reservas-table { width: 100%; border-collapse: collapse; background-color: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .reservas-table th, .reservas-table td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        .reservas-table th { background-color: #e5e7eb; font-weight: 700; color: #4b5563; }
        .estado-pendiente { color: #f97316; font-weight: bold; }
        .estado-aprobada { color: #10b981; font-weight: bold; }
        .estado-rechazada { color: #ef4444; font-weight: bold; }
        /* Estilos del Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
        .close:hover, .close:focus { color: #000; text-decoration: none; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        /* Mensajes de Alerta */
        .alerta { padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: 500; }
        .alerta-exito { background-color: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .alerta-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reservas de la Sede</h1>
            <p>Administra o realiza reservas para actividades en la sede</p>
            <button class="btn primary" onclick="openModal()">Nueva Reserva</button>
        </div>

        <?php if ($mensaje): ?>
            <div class="alerta <?= strpos($mensaje, 'Error') !== false ? 'alerta-error' : 'alerta-exito' ?>">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <table class="reservas-table">
            <thead>
                <tr>
                    <th>ID Reserva</th>
                    <th>Nombre Solicitante</th>
                    <th>Correo</th>
                    <th>Actividad</th>
                    <th>Fecha y Hora</th>
                    <th>Duración</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservas)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">No hay reservas registradas.</td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($reservas as $reserva): ?>
                    <tr>
                        <td><?= htmlspecialchars($reserva['id']) ?></td>
                        <td><?= htmlspecialchars($reserva['socio_nombre']) ?></td>
                        <td><?= htmlspecialchars($reserva['socio_correo']) ?></td>
                        <td><?= htmlspecialchars($reserva['actividad']) ?></td>
                        <td><?= htmlspecialchars($reserva['fecha']) ?> a las <?= htmlspecialchars($reserva['hora_inicio']) ?></td>
                        <td><?= htmlspecialchars($reserva['duracion_horas']) ?> hrs</td>
                        <td class="estado-<?= strtolower($reserva['estado']) ?>"><?= htmlspecialchars($reserva['estado']) ?></td>
                        <td>
                            <button class="btn edit" onclick="alert('Funcionalidad de edición: Redirigir a editar_reserva.php?id=<?= $reserva['id'] ?>')">Editar</button>
                            <button class="btn delete" onclick="alert('Funcionalidad de cancelación: Redirigir a cancelar_reserva.php?id=<?= $reserva['id'] ?>')">Cancelar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div id="modal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2 id="modal-title">Nueva Reserva</h2>
                <form method="POST" action="reservas.php" id="form-reserva">
                    <div class="form-group">
                        <label for="nombre">Nombre Solicitante</label>
                        <input type="text" name="nombre" placeholder="Ej: Juan Pérez" required>
                    </div>
                    <div class="form-group">
                        <label for="correo">Correo</label>
                        <input type="email" name="correo" placeholder="Ej: tu@email.com" required>
                    </div>
                    <div class="form-group">
                        <label for="actividad">Actividad</label>
                        <input type="text" name="actividad" placeholder="Ej: Bingo Comunitario" required>
                    </div>
                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="date" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label for="hora">Hora de Inicio</label>
                        <input type="time" name="hora" required>
                    </div>
                    <div class="form-group">
                        <label for="duracion">Duración (horas)</label>
                        <input type="number" name="duracion" min="1" max="6" required>
                    </div>
                    <button type="submit" class="btn primary">Guardar Reserva</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Funciones JavaScript para el modal
        function openModal() {
            const modal = document.getElementById('modal');
            modal.style.display = 'block';
        }

        function closeModal() {
            document.getElementById('modal').style.display = 'none';
        }
        
        // Cierra el modal si el usuario hace clic fuera de él
        window.onclick = function(event) {
            const modal = document.getElementById('modal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>