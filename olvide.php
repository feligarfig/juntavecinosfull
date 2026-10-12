<?php 
require_once 'config.php'; 

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

$mensaje = '';

if ($_POST && isset($_POST['dato'])) {
    $dato = trim($_POST['dato']);

    // Busca por RUT o correo
    $stmt = $pdo->prepare("SELECT * FROM usuario WHERE rut_usuario = ? OR correo = ?");
    $stmt->execute([$dato, $dato]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));
        $pdo->prepare("UPDATE usuario SET reset_token=?, reset_expira=? WHERE rut_usuario=?")
            ->execute([$token, $expira, $user['rut_usuario']]);

        $enlace = "http://localhost/juntavecinos/reset.php?token=$token";

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp-relay.brevo.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'juntalaflecha@gmail.com';                    // ← TU CORREO EN BREVO
            $mail->Password   = 'xsmtpsib-xDs1jnEFm4tI2fGz';       // ← TU CLAVE BREVO
            $mail->SMTPSecure = false;        // ← para Laragon
            $mail->SMTPAutoTLS = false;       // ← para Laragon
            $mail->Port       = 587;

            // ← SOLUCIÓN SSL PARA LARAGON
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom('juntalaflecha@gmail.com', 'JJVV La Flecha');
            $mail->addAddress($user['correo']);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Recuperar contraseña - JJVV La Flecha';

            $mail->Body = "
                <div style='font-family:Arial,sans-serif;max-width:600px;margin:30px auto;background:#f8f9fa;padding:30px;border-radius:15px;text-align:center;'>
                    <h2 style='color:#06b6d4;'>¡Hola {$user['nombre']}! 👋</h2>
                    <p>Recibimos una solicitud para restablecer tu contraseña.</p>
                    <p style='margin:40px 0;'>
                        <a href='$enlace' style='background:#06b6d4;color:white;padding:18px 45px;text-decoration:none;border-radius:12px;font-weight:bold;font-size:18px;'>
                            RESTABLECER CONTRASEÑA
                        </a>
                    </p>
                    <p>Este enlace vence en <strong>30 minutos</strong>.</p>
                    <hr><small>JJVV La Flecha - Coihueco • " . date('Y') . "</small>
                </div>
            ";

            $mail->send();
            $mensaje = "¡Listo! Revisa tu correo (incluida la carpeta spam)";
        } catch (Exception $e) {
            $mensaje = "Error al enviar: " . $mail->ErrorInfo;
        }
    } else {
        $mensaje = "No encontramos ese RUT ni correo en el sistema.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - JJVV La Flecha</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@700;900&display=swap" rel="stylesheet">
    <style>body{font-family:'Nunito',sans-serif;}</style>
</head>
<body class="bg-gradient-to-br from-cyan-900 to-blue-900 min-h-screen flex items-center justify-center p-4">
<div class="bg-white rounded-3xl shadow-3xl p-12 max-w-lg w-full text-center">
    <h1 class="text-6xl font-black text-cyan-600 mb-8">¿Olvidaste tu contraseña?</h1>
    
    <p class="text-gray-700 text-xl mb-8">Ingresa tu RUT o tu correo registrado</p>

    <?php if($mensaje): ?>
        <div class="p-6 rounded-2xl text-xl mb-8 <?= strpos($mensaje,'Listo')!==false ? 'bg-green-100 text-green-800 border-2 border-green-400' : 'bg-red-100 text-red-800 border-2 border-red-400' ?>">
            <?= nl2br(htmlspecialchars($mensaje)) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-8">
        <input type="text" name="dato" required placeholder="12.345.678-9 o tucorreo@mail.cl" 
               class="w-full px-8 py-6 rounded-2xl border-4 border-cyan-400 text-2xl focus:outline-none focus:border-cyan-500">
        <button type="submit" class="w-full bg-gradient-to-r from-cyan-600 to-blue-600 text-white font-black text-3xl py-6 rounded-2xl hover:scale-105 transition shadow-2xl">
            ENVIAR ENLACE
        </button>
    </form>

    <a href="index.php" class="block mt-10 text-cyan-300 hover:text-white text-xl underline">
        ← Volver al inicio
    </a>
</div>
</body>
</html>