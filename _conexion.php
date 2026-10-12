<?php
require_once __DIR__ . '/_server.php';

class Conexion{
    // Las propiedades privadas no son estrictamente necesarias si usas las constantes directamente
    // private $server = DB_SERVER;
    // ...

    private static $instancia = NULL;

    public static function conectar(){
        // 1. INCLUIMOS EL BLOQUE TRY...CATCH
        try {
            if (!isset(self::$instancia)) {
                
                // NO NECESITAS USAR $optionsPDO AQUÍ si no las estás pasando al constructor de PDO

                // CONEXIÓN CRÍTICA: Aquí es donde PHP intenta comunicarse con 'db'
                self::$instancia= new PDO("mysql:host=".DB_SERVER.";port=3306;dbname=".DB_NAME,DB_USER,DB_PASS);
                
                // Asegurar que los errores se reporten como excepciones
                self::$instancia->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                self::$instancia->exec("SET CHARACTER SET utf8");
            }
            return self::$instancia;

        } catch (PDOException $e) {
            // 2. CAPTURA DEL ERROR DE CONEXIÓN
            // El die() aquí detiene toda la ejecución y permite a login.php
            // mostrar el mensaje de error o simplemente fallar.
            // PERO: Si login.php usa try/catch, solo necesita el throw.
            
            // Para fines de depuración, es mejor lanzar una excepción
            throw new Exception("Error de conexión a la base de datos. Verifique _server.php o el estado de Docker.", 0, $e);
            
            // Si el error de login.php es "Error de conexión", es porque esta línea
            // antes no existía y el error llegaba al catch de login.php
            // Ahora, si falla, lanzamos una excepción clara.
        }
    }
}