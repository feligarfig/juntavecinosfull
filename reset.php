<?php
require_once 'config.php';
$token = $_GET['token'] ?? '';
$mensaje = '';

if ($_POST && isset($_POST['nueva'])) {
    $pass = $_POST['nueva'];
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE reset_token = ? AND reset_expira > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE usuario SET contraseña = ?, reset_token = NULL, reset_expira = NULL WHERE rut_usuario = ?")
            ->execute([$hash, $user['rut_usuario']]);
        $mensaje = "¡Contraseña cambiada con éxito! Ya puedes iniciar sesión.";
    } else {
        $mensaje = "Enlace inválido o expirado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Nunito',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-purple-900 to-pink-600 min-h-screen flex items-center justify-center">
<div class="bg-white rounded-3xl shadow-3xl p-12 max-w-md w-full mx-6 text-center">
    <h1 class="text-5xl font-black text-purple-600 mb-8">Nueva Contraseña</h1>

    <?php if($mensaje): ?>
        <div class="mb-8 p-6 rounded-2xl text-2xl <?= strpos($mensaje,'éxito')!==false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= $mensaje ?>
            <?php if(strpos($mensaje,'éxito')!==false): ?>
                <br><a href="index.php" class="inline-block mt-6 bg-purple-600 text-white px-10 py-4 rounded-xl text-xl">Ir al Login</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <form method="POST" class="space-y-8">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <input type="password" name="nueva" required minlength="6" placeholder="Nueva contraseña (mínimo 6 caracteres)"
                   class="w-full px-8 py-6 rounded-2xl border-4 border-purple-300 text-2xl">
            <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white font-black text-3xl py-6 rounded-2xl transform hover:scale-105 transition">
                CAMBIAR CONTRASEÑA
            </button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>