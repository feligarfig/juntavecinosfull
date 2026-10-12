<?php
require_once __DIR__ . '/_server.php';

class Conexion{
    private $server = DB_SERVER;
    private $db = DB_NAME;
    private $user = DB_USER;
    private $pass = DB_PASS;

    private static $instancia = NULL;

    public static function conectar(){
        // conexion a la baase de datos 
        if (!isset(self::$instancia)) {
            $optionsPDO[PDO::ATTR_ERRMODE]= PDO::ERRMODE_EXCEPTION;

            // una vez creada las opciones del pdo tenemos que crear una instancia


            self::$instancia= new PDO("mysql:host=".DB_SERVER.";dbname=".DB_NAME,DB_USER,DB_PASS);
            self::$instancia->exec("SET CHARACTER SET utf8");
            // echo "conexion realizada";
        }
        return self::$instancia;
    }
}

