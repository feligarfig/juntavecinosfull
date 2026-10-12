<?php
require 'Conexion.php';

try {
    $db = Conexion::conectar();
    echo "✔ Conexión correcta a MySQL";
} catch (Exception $e) {
    echo "❌ " . $e->getMessage();
}
