<?php

class Auth
{
    public static function checkAdmin()
    {
        session_start();

        if (!isset($_SESSION['usuario'])) {
            header("Location: ../");
            exit;
        }

        if ($_SESSION['rol'] !== 'admin') {
            echo "Acceso denegado";
            exit;
        }
    }
}
