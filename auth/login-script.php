<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <title>CARGANDO...</title>
</head>
<body>

<?php
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
// Verificar si se han enviado los datos del formulario
if (isset($_POST['usuario'], $_POST['password'])) {
    try {
        // Acceder a los datos del formulario
        $usuario = $_POST['usuario'];
        $password = $_POST['password'];

        if($usuario == "usercnc"){
            $usuario = "usercnc@sellosyretenes.com";
        }

        // Clave de encriptación
        $clave_encriptacion = 'SRS2024#tides';

        // Encriptar los valores ingresados
        $usuario_encriptado = openssl_encrypt($usuario, 'AES-128-ECB', $clave_encriptacion);
        $password_encriptada = openssl_encrypt($password, 'AES-128-ECB', $clave_encriptacion);

        // Preparar la consulta SQL para evitar inyección de SQL
        $stmt = $conn->prepare("SELECT id, area, activo, lider FROM login WHERE usuario = :usuario AND password = :password");
        $stmt->bindParam(':usuario', $usuario_encriptado);
        $stmt->bindParam(':password', $password_encriptada);
        $stmt->execute();

        // Verificar si se encontraron resultados
        if ($stmt->rowCount() > 0) {
            // Obtener el id_usuario, área y estado de activo del usuario autenticado
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id = $row['id'];
            $area_encriptada = $row['area'];
            $activo = $row['activo'];
            $lider = $row['lider'];

            $area = openssl_decrypt($area_encriptada, 'AES-128-ECB', $clave_encriptacion);

            // Verificar si el usuario está activo
            if ($activo == 1) {
                session_start();
                $_SESSION['id'] = $id;
                include(ROOT_PATH . 'includes/animacionsvg.php');
                echo '<script>
                        setTimeout(function() {
                            window.location.href = "../modules/welcome.php";
                        }, 2000); 
                    </script>';

                // FUNCION PARA GUARDAR ENCRIPTADAMENTE LO QUE VA HACIENDO EL USUARIO EN log_usuarios
                $username = $_POST['usuario'];
                // Construir manualmente la consulta SQL como una cadena
                $sql_login_string = "SELECT id, area, activo, lider FROM login WHERE usuario = :usuario AND password = :password";
                $instruccion_encriptada = openssl_encrypt($sql_login_string, 'AES-128-ECB', $clave_encriptacion);

                $sql_log = "INSERT INTO log_usuarios (Usuario, Accion, Instruccion) VALUES (?, 'Ha iniciado sesión', ?)";
                $stmt_log = $conn->prepare($sql_log);
                $stmt_log->execute([$username, $instruccion_encriptada]);
                // FIN FUNCION PARA GUARDAR ENCRIPTADAMENTE LO QUE VA HACIENDO EL USUARIO EN log_usuarios
            } else { 
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("warning", "Aviso", "Su usuario está desactivado, debe ser activado por un administrador.", "../auth/cerrar_sesion.php");
                });</script>';
                exit;
            }
        } else {  
            echo '<script>document.addEventListener("DOMContentLoaded", function () {
            sweetAlertResponse("warning", "Algo salió mal", "Contraseña o usuario incorrectos ", "../auth/cerrar_sesion.php");
            });</script>';
            exit;
        }
    } catch (Throwable $e) {
        echo '<script>document.addEventListener("DOMContentLoaded", function () {
        sweetAlertResponse("error", "Error", "Error al intentar iniciar sesión. '. addslashes($e->getMessage()).'", "../auth/cerrar_sesion.php");
        });</script>';
        exit;
    }
}else{
    echo '<script>document.addEventListener("DOMContentLoaded", function () {
    sweetAlertResponse("warning", "Advertencia", "Acceso denegado.", "../auth/cerrar_sesion.php");
    });</script>';
    exit;
}
?>
</body>
</html>
