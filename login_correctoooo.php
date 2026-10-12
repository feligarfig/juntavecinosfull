<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db.php';

if (!isset($_POST['usuario'], $_POST['password'])) {
    header("Location: index.php?error=Debe ingresar usuario y contraseña");
    exit;
}

$usuario = trim($_POST['usuario']);
$password = trim($_POST['password']);

try {
    // Buscar usuario por RUT o email (opcional)
    #$stmt = $pdo->prepare(query: "
    #    SELECT * FROM usuario
    #    WHERE  rut_usuario = :u OR correo = :u
    #    LIMIT 1
    #");
    #$stmt->execute(['u' => $usuario]);
    #$user = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
    SELECT * FROM usuario
    WHERE (rut_usuario = :u OR correo = :u)
      AND contraseña = :p
    LIMIT 1
    ");
    $stmt->execute([
    'u' => $usuario,
    'p' => $password
    ]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);



    if (!$user) {
        header("Location: index.php?error=Usuario o contraseña incorrectos");
        exit;
    }

  #  if ($user['activo'] != 1) {
  #      header("Location: index.php?error=Usuario inactivo");
  #      exit;
  #  }

  echo "Contraseña ingresada: " . $password . "<br>";
  echo "Contraseña en BD: " . $user['contraseña'] . "<br>";

    // Contraseña en texto plano
    if ($password == $user['password']) {
        header("Location: index.php?error=Contraseña incorrecta");
        exit;
    }

    // Guardar SESIÓN usando solo RUT
    $_SESSION['rut_usuario'] = $user['rut'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['rol'] = $user['rol'];

    header("Location: dashboard.php");
    exit;

 // } catch (Exception $e) {
  //   header("Location: index.php?error=Error interno en el servidor"); 
  //   exit;
 // }


} catch (Exception $e) {
    die("Error capturado: " . $e->getMessage());
}