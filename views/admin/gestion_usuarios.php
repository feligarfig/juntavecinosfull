<?php
require_once '../../includes/auth.php';
require_once '../../includes/db.php';
// Protección: si no está logueado lo manda al login
if (empty($_SESSION['rut_usuario'])) {
    header("Location: login.php");
    exit;
}

$mensaje = $_GET['m'] ?? '';

// ============================
// CREAR O EDITAR USUARIO
// ============================
if ($_POST) {
    $rut       = trim($_POST['rut_usuario']);
    $nombre    = trim($_POST['nombre']);
    $apellido1 = trim($_POST['apellido1']);
    $apellido2 = trim($_POST['apellido2'] ?? '');
    $correo    = trim($_POST['correo']);
    $rol       = $_POST['rol'];
    $pass      = $_POST['contrasena'] ?? '';

    try {
        if (!empty($_POST['rut_original'])) {
            // === EDITAR ===
            $original = $_POST['rut_original'];

            if (!empty($pass)) {
                // Cambia contraseña → hashear
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $sql = "UPDATE usuario SET rut_usuario=?, nombre=?, apellido1=?, apellido2=?, correo=?, contraseña=?, rol=? WHERE rut_usuario=?";
                $pdo->prepare($sql)->execute([$rut, $nombre, $apellido1, $apellido2, $correo, $hash, $rol, $original]);
            } else {
                // Solo datos básicos
                $sql = "UPDATE usuario SET rut_usuario=?, nombre=?, apellido1=?, apellido2=?, correo=?, rol=? WHERE rut_usuario=?";
                $pdo->prepare($sql)->execute([$rut, $nombre, $apellido1, $apellido2, $correo, $rol, $original]);
            }
            $mensaje = "Usuario actualizado correctamente";

        } else {
            // === CREAR NUEVO ===
            if (empty($pass)) {
                $mensaje = "La contraseña es obligatoria para usuarios nuevos";
            } else {
                // Verificar duplicados
                $check = $pdo->prepare("SELECT 1 FROM usuario WHERE rut_usuario=? OR correo=? LIMIT 1");
                $check->execute([$rut, $correo]);
                if ($check->fetch()) {
                    $mensaje = "Este RUT o correo ya está registrado";
                } else {
                    $hash = password_hash($pass, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO usuario (rut_usuario, nombre, apellido1, apellido2, correo, contraseña, rol) 
                            VALUES (?,?,?,?,?,?,?)";
                    $pdo->prepare($sql)->execute([$rut, $nombre, $apellido1, $apellido2, $correo, $hash, $rol]);
                    $mensaje = "Usuario creado correctamente";
                }
            }
        }
    } catch (Exception $e) {
        $mensaje = "Error: " . ($e->getCode() == '23000' ? "RUT o correo ya existe" : "del servidor");
    }

    header("Location: gestion_usuarios.php?m=" . urlencode($mensaje));
    exit;
}

// ============================
// ELIMINAR USUARIO
// ============================
if (isset($_GET['delete']) && $_GET['delete'] !== $_SESSION['rut_usuario']) {
    $pdo->prepare("DELETE FROM usuario WHERE rut_usuario=?")->execute([$_GET['delete']]);
    header("Location: gestion_usuarios.php?m=Usuario eliminado correctamente");
    exit;
}

// Cargar todos los usuarios
$usuarios = $pdo->query("SELECT * FROM usuario ORDER BY rol DESC, nombre")->fetchAll(PDO::FETCH_ASSOC);
$nombre_usuario = $_SESSION['nombre'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>body{font-family:'Nunito',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen">

<!-- BARRA SUPERIOR -->
<div class="fixed top-0 left-0 right-0 bg-gradient-to-r from-emerald-700 to-teal-700 text-white shadow-2xl z-50">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <a href="dashboard.php" class="flex items-center gap-3 text-2xl font-black hover:scale-110 transition">
            <i class="fas fa-home"></i> Panel Principal
        </a>
        <div class="flex items-center gap-6">
            <span class="text-xl"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($nombre_usuario) ?></span>
            <a href="logout.php" onclick="return confirm('¿Cerrar sesión?')" 
               class="bg-red-600 hover:bg-red-700 px-8 py-3 rounded-full font-bold text-xl transition hover:scale-105">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
</div>
<div class="pt-24"></div>

<div class="container mx-auto px-6 py-12 max-w-7xl">
    <div class="text-center mb-12">
        <h1 class="text-7xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-4">
            Gestión de Usuarios
        </h1>
        <p class="text-3xl text-gray-700 font-bold">Junta de Vecinos Villa La Flecha</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="text-center mb-10 bg-<?= strpos($mensaje,'correctamente')!==false || strpos($mensaje,'eliminado')!==false ? 'emerald' : 'red' ?>-100 
             border border-<?= strpos($mensaje,'correctamente')!==false || strpos($mensaje,'eliminado')!==false ? 'emerald' : 'red' ?>-400 
             text-<?= strpos($mensaje,'correctamente')!==false || strpos($mensaje,'eliminado')!==false ? 'emerald' : 'red' ?>-800 
             px-12 py-6 rounded-3xl text-3xl font-bold shadow-lg">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <div class="text-center mb-12">
        <button onclick="openModal()" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 
                 text-white font-black text-4xl py-8 px-32 rounded-3xl shadow-2xl transform hover:scale-110 transition">
            <i class="fas fa-user-plus mr-4"></i> NUEVO USUARIO
        </button>
    </div>

    <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
        <?php foreach($usuarios as $u): ?>
        <div class="bg-white rounded-3xl shadow-2xl p-1 transform hover:scale-105 transition-all duration-300">
            <div class="p-8">
                <div class="flex items-center gap-6 mb-6">
                    <div class="w-24 h-24 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center text-white text-5xl font-black">
                        <?= strtoupper(substr($u['nombre'],0,1)) ?>
                    </div>
                    <div>
                        <h3 class="text-3xl font-black text-gray-800"><?= htmlspecialchars($u['nombre'].' '.$u['apellido1']) ?></h3>
                        <p class="text-xl text-gray-600"><?= htmlspecialchars($u['rut_usuario']) ?></p>
                    </div>
                </div>
                <p class="text-lg text-gray-700 mb-4"><i class="fas fa-envelope text-emerald-600 mr-3"></i> <?= htmlspecialchars($u['correo']) ?></p>
                <div class="mb-8">
                    <span class="inline-block px-8 py-4 rounded-full text-white font-black text-xl 
                        <?= $u['rol']=='Admin' ? 'bg-red-600' : 'bg-gradient-to-r from-emerald-600 to-teal-600' ?>">
                        <?= htmlspecialchars($u['rol']) ?>
                    </span>
                </div>
                <div class="flex gap-4">
                    <button onclick='edit(<?= json_encode($u, JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)' 
                            class="flex-1 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 
                                   text-white font-black py-5 rounded-2xl text-xl transition transform hover:scale-105">
                        <i class="fas fa-edit mr-2"></i> Editar
                    </button>
                    <?php if($u['rut_usuario'] !== $_SESSION['rut_usuario']): ?>
                    <a href="?delete=<?= urlencode($u['rut_usuario']) ?>" 
                       onclick="return confirm('¿ELIMINAR PERMANENTEMENTE este usuario?\n\n<?= addslashes($u['nombre'].' '.$u['apellido1']) ?>')"
                       class="flex-1 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-500 hover:to-red-600 
                              text-white font-black py-5 rounded-2xl text-center text-xl transition transform hover:scale-105">
                        <i class="fas fa-trash-alt mr-2"></i> Eliminar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL -->
<div id="modal" class="fixed inset-0 bg-black/80 hidden flex items-center justify-center z-50" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-3xl shadow-3xl p-12 max-w-5xl w-full mx-6 overflow-y-auto max-h-screen">
        <h2 class="text-6xl font-black text-center text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-600 mb-12">
            Nuevo / Editar Usuario
        </h2>
        <form method="POST" class="grid md:grid-cols-2 gap-10">
            <input type="hidden" name="rut_original" id="rut_original">
            <input type="text" name="rut_usuario" id="rut_usuario" placeholder="RUT (sin puntos ni guión)" required 
                   class="col-span-2 px-10 py-7 border-4 border-gray-300 rounded-3xl text-3xl font-bold text-center focus:border-emerald-500 outline-none">
            <input type="text" name="nombre" id="nombre" placeholder="Nombre" required class="px-10 py-7 border-4 border-gray-300 rounded-3xl text-2xl focus:border-emerald-500 outline-none">
            <input type="text" name="apellido1" id="apellido1" placeholder="Apellido Paterno" required class="px-10 py-7 border-4 border-gray-300 rounded-3xl text-2xl focus:border-emerald-500 outline-none">
            <input type="text" name="apellido2" id="apellido2" placeholder="Apellido Materno" class="px-10 py-7 border-4 border-gray-300 rounded-3xl text-2xl focus:border-emerald-500 outline-none">
            <input type="email" name="correo" id="correo" placeholder="Correo electrónico" required class="px-10 py-7 border-4 border-gray-300 rounded-3xl text-2xl focus:border-emerald-500 outline-none">
            <input type="password" name="contrasena" id="contrasena" placeholder="Contraseña (solo si quieres cambiarla)" 
                   class="col-span-2 px-10 py-7 border-4 border-gray-300 rounded-3xl text-2xl text-center focus:border-emerald-500 outline-none">
            <select name="rol" id="rol" class="col-span-2 px-10 py-7 border-4 border-gray-300 rounded-3xl text-3xl font-bold focus:border-emerald-500 outline-none">
                <option value="Admin">Administrador</option>
                <option value="Tesorero">Tesorero</option>
                <option value="Secretario">Secretario</option>
                <option value="Directivo">Directivo</option>
                <option value="Socio">Socio</option>
            </select>
            <div class="col-span-2 flex gap-8 justify-center mt-10">
                <button type="submit" class="bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 
                    text-white font-black text-4xl py-10 px-40 rounded-3xl shadow-2xl transform hover:scale-110 transition">
                    GUARDAR
                </button>
                <button type="button" onclick="document.getElementById('modal').classList.add('hidden')" 
                        class="bg-gray-500 hover:bg-gray-600 text-white font-black text-4xl py-10 px-32 rounded-3xl">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modal').classList.remove('hidden');
    document.querySelector('form').reset();
    document.getElementById('rut_original').value = '';
    document.getElementById('contrasena').placeholder = 'Contraseña (obligatoria para nuevos usuarios)';
    document.getElementById('contrasena').required = true;
}
function edit(u) {
    openModal();
    document.getElementById('rut_original').value = u.rut_usuario;
    document.getElementById('rut_usuario').value = u.rut_usuario;
    document.getElementById('nombre').value = u.nombre;
    document.getElementById('apellido1').value = u.apellido1;
    document.getElementById('apellido2').value = u.apellido2 || '';
    document.getElementById('correo').value = u.correo;
    document.getElementById('rol').value = u.rol;
    document.getElementById('contrasena').value = '';
    document.getElementById('contrasena').placeholder = 'Dejar vacío para NO cambiar contraseña';
    document.getElementById('contrasena').required = false;
}
</script>
</body>
</html>