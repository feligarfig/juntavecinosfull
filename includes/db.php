<?php
// includes/db.php → FUNCIONA SÍ O SÍ EN TU LARAGON
try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;port=3306;dbname=juntavecinos;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("<h3 style='color:red;text-align:center;margin-top:100px;'>
            Error de base de datos: " . $e->getMessage() . 
         "<br><small>Asegúrate de que la base se llame exactamente: <b>juntavecinos</b></small></h3>");
}