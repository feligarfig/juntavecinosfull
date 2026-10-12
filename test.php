<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=juntavecinos", "root", "");
    echo "✅ Conexión exitosa.";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}