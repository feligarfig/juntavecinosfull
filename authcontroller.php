<?php
require_once __DIR__ . "/../config/database.php";

class AuthController {

    public function login($email, $password)
    {
        global $pdo;

        //exit("$email | $password");

        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ? LIMIT 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || !password_verify($password, $usuario['password'])) {
            echo "Credenciales inválidasxxx";
            return;
        }

        session_start();
        $_SESSION["usuario"] = $usuario["email"];
        $_SESSION["rol"] = $usuario["rol"];

        if ($usuario["rol"] === "admin") {
            header("Location: /admin/");
        } else {
            header("Location: /");
        }
    }
}
