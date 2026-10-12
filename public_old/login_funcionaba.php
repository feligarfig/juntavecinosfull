<?php
session_start();

require_once "../app/controllers/AuthController.php";
$auth = new AuthController();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $auth->login($_POST["usuario"], $_POST["password"]);
}


?>

<form method="POST">
    <input type="email" name="email" placeholder="Correoblablabla">
    <input type="password" name="password" placeholder="Contraseña">
    <button type="submit">Ingresar</button>
</form>
