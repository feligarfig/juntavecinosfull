<?php
class SociosController {
    public function index() {
        if (!is_admin()) redirect('login');
        global $pdo;
        $stmt = $pdo->query("SELECT * FROM socios ORDER BY Nombre");
        $socios = $stmt->fetchAll();
        require 'views/socios/index.php';
    }

    public function crear() {
        if (!is_admin()) redirect('login');
        $error = '';
        if ($_POST) {
            $rut = $_POST['rut'] ?? '';
            if (!validar_rut($rut)) {
                $error = "RUT inválido";
            } else {
                global $pdo;
                try {
                    $stmt = $pdo->prepare("INSERT INTO socios (RUT, Nombre, Apellido1, Apellido2, Fecha_nacimiento, Ocupación, Domicilio, Fecha_ingreso, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $rut,
                        $_POST['nombre'],
                        $_POST['apellido1'],
                        $_POST['apellido2'],
                        $_POST['fecha_nac'],
                        $_POST['ocupacion'],
                        $_POST['domicilio'],
                        date('d/m/Y'), // Formato chileno
                        'activo'
                    ]);
                    redirect('socios');
                } catch (PDOException $e) {
                    $error = "RUT ya existe o error en base de datos";
                }
            }
        }
        require 'views/socios/crear.php';
    }

    public function editar($id) {
        if (!is_admin()) redirect('login');
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM socios WHERE RUT = ?");
        $stmt->execute([$id]);
        $socio = $stmt->fetch();
        if (!$socio) redirect('socios');

        $error = '';
        if ($_POST) {
            if (!validar_rut($_POST['rut'])) {
                $error = "RUT inválido";
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE socios SET RUT=?, Nombre=?, Apellido1=?, Apellido2=?, Fecha_nacimiento=?, Ocupación=?, Domicilio=?, estado=? WHERE RUT=?");
                    $stmt->execute([
                        $_POST['rut'],
                        $_POST['nombre'],
                        $_POST['apellido1'],
                        $_POST['apellido2'],
                        $_POST['fecha_nac'],
                        $_POST['ocupacion'],
                        $_POST['domicilio'],
                        $_POST['estado'],
                        $id
                    ]);
                    redirect('socios');
                } catch (PDOException $e) {
                    $error = "Error al actualizar";
                }
            }
        }
        require 'views/socios/editar.php';
    }

    public function eliminar($id) {
        if (!is_admin()) redirect('login');
        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM socios WHERE RUT = ?");
        $stmt->execute([$id]);
        redirect('socios');
    }
}