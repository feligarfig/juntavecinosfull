<?php require 'config.php'; 
$mensaje = '';

if ($_POST) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ? AND activo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE rut = ? AND activo = 1");
        $stmt->execute([$rut]);
        $user = $stmt->fetch();       
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_rut']  = $user['rut'];
            $_SESSION['nombre']    = $user['nombre'];
            $_SESSION['email']     = $user['email'];
            $_SESSION['rol']       = $user['rol'];
            redirect('panel.php');
        } else {
            $mensaje = "Correo o contraseña incorrecta";
        }
    } else {
        $mensaje = "Completa ambos campos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Junta La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-green-600 to-emerald-800 min-h-screen flex items-center justify-center">
    <div class="bg-white p-10 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-green-700">Junta de Vecinos</h1>
            <p class="text-2xl text-green-600">La Flecha</p>
        </div>

        <?php if($mensaje): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 text-center">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Correo electrónico</label>
                <input type="email" name="email" placeholder="admin@laflecha.cl" required 
                       class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:border-green-600">
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Contraseña</label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-3 border rounded-lg focus:outline-none focus:border-green-600">
            </div>
            <button type="submit" 
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-lg transition">
                Ingresar al Panel
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            <p>¿Problemas de acceso? Contacta a la presidenta</p>
        </div>
    </div>
</body>
</html>