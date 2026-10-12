<?php require_once 'config.php'; 

// Procesar login
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = trim($_POST['rut'] ?? '');
    $pass = $_POST['pass'] ?? '';

    // Ajusta esta consulta según cómo se llame tu tabla y columnas
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE rut_usuario = ? LIMIT 1");
    $stmt->execute([$rut]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['contrasena'])) {
        $_SESSION['admin'] = true;
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['rol'] = $user['rol'];
        redirect('/socios'); // o /dashboard si lo creas
    } else {
        $mensaje = "RUT o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Junta de Vecinos La Flecha 20251118 </title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Montserrat',sans-serif}</style>
</head>
<body class="bg-gradient-to-br from-green-50 to-emerald-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-10 rounded-2xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Junta de Vecinos La Flecha</h1>
            <p class="text-gray-600 mt-2">Acceso Directiva</p>
        </div>

        <?php if ($mensaje): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 text-center">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">RUT (sin puntos ni guión)</label>
                <input type="text" name="rut" required placeholder="112