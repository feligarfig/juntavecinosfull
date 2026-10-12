<?php
class AuthController {
    public function login() {
        $error = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        if ($_POST) {
            $email = $_POST['email'] ?? '';
            $pass = $_POST['password'] ?? '';

            if ($email === 'juntalaflcha@gmail.com' && $pass === '12345') {
                $_SESSION['admin'] = true;
                redirect('admin');
            } else {
                $error = "Credenciales incorrectasxxx";
                redirect('../?error');
            }
        }
        require 'views/auth/login.php';
    }

    public function logout() {
        unset($_SESSION['admin']);
        redirect('home');
    }
}