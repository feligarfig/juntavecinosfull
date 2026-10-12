 <?php
// logout.php
session_start();

session_unset();      // borra todas las variables de sesión
session_destroy();    // destruye la sesión

// Borra también la cookie de sesión (para que no quede rastro)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirecciona al inicio del proyecto
header("Location: /juntavecinos/index.php");
exit;
?>