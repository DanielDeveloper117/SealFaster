<?php
require 'db/config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $nombre = $_POST['nombre'];
    $area = $_POST['area'];
    $lider = $_POST['lider'];

    // Verificar el valor de lider antes de continuar
    if ($lider !== '0' && $lider !== '1'&& $lider !== '2' && $lider !== '3') {
        echo 'Valor de lider inválido';
    }

    // Convertir el valor de lider a entero
    $lider = (int)$lider;
    // Encriptación
    $clave_encriptacion = 'SRS2024#tides';
    $username_encriptado = openssl_encrypt($username, 'AES-128-ECB', $clave_encriptacion);
    $password_encriptada = openssl_encrypt($password, 'AES-128-ECB', $clave_encriptacion);
    $nombre_encriptado = openssl_encrypt($nombre, 'AES-128-ECB', $clave_encriptacion);
    $area_encriptada = openssl_encrypt($area, 'AES-128-ECB', $clave_encriptacion);

    // Generar código de verificación
    $codigoVerificacion = generarCodigoVerificacion();

    try {
        // Insertar datos en la base de datos
        $stmt = $conn->prepare("INSERT INTO login (usuario, password, nombre, area, fechalogin, horalogin, activo, lider, codigoVerificacion) VALUES (:username, :password, :nombre, :area, CURDATE(), CURTIME(), b'0', :lider, :codigoVerificacion)");
        $stmt->bindParam(':username', $username_encriptado, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password_encriptada, PDO::PARAM_STR);
        $stmt->bindParam(':nombre', $nombre_encriptado, PDO::PARAM_STR);
        $stmt->bindParam(':area', $area_encriptada, PDO::PARAM_STR);
        $stmt->bindParam(':lider', $lider, PDO::PARAM_INT);
        $stmt->bindParam(':codigoVerificacion', $codigoVerificacion, PDO::PARAM_STR);
        $stmt->execute();
        
        // Enviar correo de verificación
        enviarCorreoVerificacion($username, $password, $nombre, $area, $lider, $codigoVerificacion);

        // Mostrar mensaje de éxito en modal
        echo '				
        <div class="modal fade" id="ModalSuccess" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header justify-content-between">
                        <h5 class="modal-title text-success">Proceso exitoso</h5>
                        <a href="#">
                            <button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </a>
                    </div>
                    <div class="modal-body">
                        <p>Usuario registrado correctamente. Un administrador debe activar el usuario para poder usarlo.</p>
                    </div>
                    <div class="modal-footer">
                        <a href="#">
                            <button type="button" class="btn-general" data-bs-dismiss="modal">Aceptar</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            window.onload = function() {
                var modal = new bootstrap.Modal(document.getElementById("ModalSuccess"));
                modal.show();
            };
        </script>';

        // Registrar acción en log
        registrarLog($conn, $username_encriptado, $clave_encriptacion);

    } catch (PDOException $e) {
        // Manejar errores
        if ($e->getCode() == 23000) {
            echo '
            <div class="modal fade" id="ModalFailed" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header justify-content-between">
                            <h5 class="modal-title text-danger">Algo salió mal</h5>
                            <a href="index.html">
                                <button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </a>
                        </div>
                        <div class="modal-body">
                            <p>El usuario ya existe</p>
                        </div>
                        <div class="modal-footer">
                            <a href="index.html">
                                <button type="button" class="btn-general" data-bs-dismiss="modal">Volver a intentar</button>
                            </a>                        
                        </div>
                    </div>
                </div>
            </div>
            <script>
                window.onload = function() {
                    var modal = new bootstrap.Modal(document.getElementById("ModalFailed"));
                    modal.show();
                };
            </script>';
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}

function generarCodigoVerificacion() {
    return mt_rand(100000, 999999);
}

function enviarCorreoVerificacion($usuario, $password, $nombre, $area, $lider, $codigoVerificacion) {
    $mail = new PHPMailer(true);
    $tipoDeUsuario = "";
    if($lider == 1){
        $tipoDeUsuario = "Administrador";
    }else if($lider == 2){
        $tipoDeUsuario = "Usuario CNC";
    }else if($lider == 3){
        $tipoDeUsuario = "Vendedor";
    }else if($lider == 0){
        $tipoDeUsuario = "Sistemas";
    }
    try {
       // envío de correo
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'sellosyretenes.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'expresate@sellosyretenes.com';
        $mail->Password = 'RGBivR3.ciNaZ';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        
        // Configurar remitente y destinatario
        $mail->setFrom('expresate@sellosyretenes.com', 'Registro de usuario');
        $mail->addAddress('desarrollo2.sistemas@sellosyretenes.com', 'Registro de usuario');
        $mail->isHTML(true);
        $mail->Subject = 'NUEVO REGISTRO DE USUARIO PARA COTIZADOR';
        $mail->Body = "Se requiere ingreso al cotizador con un usuario director o sistemas para activar el nuevo usuario con el codigo de validacion.<br>Nombre Completo: $nombre <br>Area: $area <br>Usuario: $usuario<br>Tipo de usuario: $tipoDeUsuario <br>Contraseña: $password <br>Codigo de Validacion: $codigoVerificacion";
        $mail->send();
    } catch (Exception $e) {
        echo 'Error al enviar el correo: ' . $mail->ErrorInfo;
    }
}

function registrarLog($conn, $username, $clave_encriptacion) {
    $sql_login_string = "INSERT INTO login (usuario, password, nombre, area, fechalogin, horalogin, activo, lider, codigoVerificacion) VALUES (:username, :password, :nombre, :area, CURDATE(), CURTIME(), b'0', :lider, :codigoVerificacion)";
    $instruccion_encriptada = openssl_encrypt($sql_login_string, 'AES-128-ECB', $clave_encriptacion);
    $sql_log = "INSERT INTO log_usuarios (Usuario, Accion, Instruccion) VALUES (?, 'Se ha registrado un usuario', ?)";
    $stmt_log = $conn->prepare($sql_log);
    $stmt_log->execute([$username, $instruccion_encriptada]);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="jquery-3.7.1.min.js"></script>

    <link rel="stylesheet" href="css/styles-header.css">
    <link rel="stylesheet" href="css/styles-formulario.css">
    <title>Registrar usuario</title>
</head>
<body>
    <div class="titulo mt-3">
        <h3>Registra a tu usuario llenando el formulario</h3>
    </div>

    <section class="section-form mt-3 mb-5 d-flex col-12 justify-content-center align-items-center">
        <div class="container-form d-flex col-11 col-md-4 justify-content-center align-items-center">
            <form class="form-general w-100 d-flex p-4 flex-column justify-content-center" action="register.php" method="post">
                <label class="lbl-general mb-1" for="usuario">Correo:</label>
                <input class="input-text mb-4" type="text" id="username" name="username" required>
                <label class="lbl-general mb-1" for="password">Contraseña:</label>
                <input class="input-text mb-4" type="password" id="password" name="password" required>
                <label class="lbl-general mb-1" for="nombre">Nombre completo:</label>
                <input class="input-text mb-4" type="text" id="nombre" name="nombre" required>
                <label class="lbl-general mb-1" for="area">Area:</label>
                <select class="selector mb-4" name="area" id="area" required onchange="mostrarCampoEmpresa(this.value)">
                    <option value="" selected disabled>Seleccione área</option>
                    <option value="SISTEMAS">SISTEMAS</option>
                    <option value="VENTAS">VENTAS</option>
                    <option value="CNC">CNC</option>
                    <option value="DIRECION">DIRECION</option>
                    <!-- <option value="EMPRESA">Otra empresa</option> -->
                </select>
                <div id="campo_empresa" style="display: none;">
                    <label class="lbl-general mb-1" for="nombre_empresa">Nombre de la empresa:</label>
                    <input class="input-text mb-4" type="text" id="nombre_empresa" >
                </div>
                <label class="lbl-general mb-1" for="rol">Rol de usuario:</label>
                <select class="selector mb-4" name="lider" id="rol">
                    <option value="" selected disabled>Seleccione un rol</option>
                    <option value="0">Usuario sistemas</option>
                    <option value="2">Usuario CNC</option>
                    <option value="3">Usuario Ventas</option>
                    <option value="1">Administrador</option>
                </select>
                <input class="btn-general mb-2" type="submit" value="Enviar">
                <div class="mt-1 mb-2 text-center text-white">
                    <a href="index.html" class="div-a" style="font-size: 1.2rem;">¿Ya tienes una cuenta? Ir a Login</a>
                </div>
            </form>
        </div>
    </section>

    <script>
        function mostrarCampoEmpresa(valor) {
            var campoEmpresa = document.getElementById("campo_empresa");
            var areaSelect = document.getElementById("area");
            var nombreEmpresaInput = document.getElementById("nombre_empresa");

            if (valor === "EMPRESA") {
                campoEmpresa.style.display = "block";
                areaSelect.removeAttribute("name");
                nombreEmpresaInput.setAttribute("name", "area");
            } else {
                campoEmpresa.style.display = "none";
                nombreEmpresaInput.removeAttribute("name");
                areaSelect.setAttribute("name", "area");
            }
        }
    </script>
</body>
</html>
