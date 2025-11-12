<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.js"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-formulario.css'); ?>">
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-configuracion.css'); ?>">

    <title>Configuración</title>
</head>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formulario = $_POST['formulario'];

    switch ($formulario) {
        case "changename":
            if (isset($_POST['password']) && isset($_POST['nombre'])) {
                $clave_encriptacion = 'SRS2024#tides';

                $password_ingresada = $_POST['password'];
                $nuevo_nombre = $_POST['nombre'];

                // Encriptar la contraseña proporcionada
                $password_encriptada = openssl_encrypt($password_ingresada, 'AES-128-ECB', $clave_encriptacion);
                $nuevo_nombre_encriptado = openssl_encrypt($nuevo_nombre, 'AES-128-ECB', $clave_encriptacion);

                // Verificar si la contraseña actual es correcta
                $stmt = $conn->prepare("SELECT password FROM login WHERE id = :id");
                $stmt->bindParam(':id', $_SESSION['id']);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row && $row['password'] === $password_encriptada) {
                    // Actualizar el nombre
                    $sql = "UPDATE login SET nombre = :nuevo_nombre WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':nuevo_nombre', $nuevo_nombre_encriptado);
                    $stmt->bindParam(':id', $_SESSION['id']);
                    $stmt->execute();

                    echo '<script>$(document).ready(function(){
                    sweetAlertResponse("success", "Proceso exitoso", "Información actualizada correctamente.", "none");
                    });</script>';
                } else {
                    echo '<script>$(document).ready(function(){
                    sweetAlertResponse("warning", "Ocurrió un problema.", "Contraseña incorrecta.", "none");
                    });</script>';
                }
            } else {
                echo '<script>$(document).ready(function(){
                sweetAlertResponse("warning", "Ocurrió un problema.", "Faltan datos.", "none");
                });</script>';
            }
        break;

        case "changepass":
            if (isset($_POST['password1']) && isset($_POST['password2'])) {
                $clave_encriptacion = 'SRS2024#tides';

                $password_actual_ingresada = $_POST['password1'];
                $nueva_password = $_POST['password2'];

                // Encriptar ambas
                $password_actual_encriptada = openssl_encrypt($password_actual_ingresada, 'AES-128-ECB', $clave_encriptacion);
                $nueva_password_encriptada = openssl_encrypt($nueva_password, 'AES-128-ECB', $clave_encriptacion);

                // Verificar si la contraseña actual es correcta
                $stmt = $conn->prepare("SELECT password FROM login WHERE id = :id");
                $stmt->bindParam(':id', $_SESSION['id']);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row && $row['password'] === $password_actual_encriptada) {
                    // Actualizar nueva contraseña
                    $sql = "UPDATE login SET password = :nueva_pass WHERE id = :id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':nueva_pass', $nueva_password_encriptada);
                    $stmt->bindParam(':id', $_SESSION['id']);
                    $stmt->execute();

                    echo '<script>$(document).ready(function(){
                    sweetAlertResponse("success", "Proceso exitoso", "Información actualizada correctamente.", "none");
                    });</script>';
                } else {
                    echo '<script>$(document).ready(function(){
                    sweetAlertResponse("warning", "Ocurrió un problema.", "Contraseña incorrecta.", "none");
                    });</script>';
                }
            } else {
                echo '<script>$(document).ready(function(){
                sweetAlertResponse("warning", "Ocurrió un problema.", "Faltan datos.", "none");
                });</script>';
            }
        break;

        default:
            echo '<script>$(document).ready(function(){
            sweetAlertResponse("warning", "Ocurrió un problema.", "El formulario no es valido.", "none");
            });</script>';
        break;
    }
}

?>

<body>
    
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>
<?php include(ROOT_PATH . 'includes/backend_info_user.php'); ?>


<section class="d-flex flex-row col-12 justify-content-center align-items-center mt-3 mb-3">
    <div class="d-flex col-10 flex-column form-general">
        <div id="" class="titulo mt-3">
            <h2>Mi cuenta</h2>
        </div>
        <div id="containerUserInfo" class="mb-5 px-md-5">
            <div class="mb-4">
                <h4>Informacion de usuario</h4>
                <div class="d-flex flex-column">
                    <div class=" d-flex justify-content-center justify-content-md-start">
                        <img class="img-fluid" src="../assets/img/general/usuario.png" alt="" style="width: 110px;">
                    </div>
                    <div>
                        <ul>
                            <li><strong>Correo:</strong> <?= $usuarioUser; ?></li>
                            <li><strong>Nombre:</strong> <?= $nombreUser; ?></li>
                            <?php if ($tipoUsuario != 5): ?>
                                <li><strong>Area:</strong> <?= $areaUser; ?></li>
                                <li><strong>Rol de area:</strong> <?= $rolUser; ?></li>
                                <li><strong>Tipo de usuario/privilegios:</strong> <?= $tipo_usuario; ?></li>
                            <?php endif; ?>
                            <li><strong>Fecha de creación:</strong> <?= $fecha_creacion . ' a las '. $hora_creacion; ?></li>
                        </ul>
                    </div>
                    <div id="config" class=""></div>
                </div>
            </div>
        </div>
        <div class="titulo mt-3">
            <h2>Configuración</h2>
        </div>
        <div id="containerChangeName" class="mb-5 px-md-5">
            <div class="mb-4">
                <h4>Cambiar nombre</h4>
            </div>
            <form id="formChangeName" action="" method="POST">
                <input type="hidden" name="formulario" value="changename">
                <div class="" style="width:48%;">
                    <label for="inputPassCN" class="lbl-general">Contraseña actual</label>
                    <input id="inputPassCN" type="password" class="input-text" name="password" placeholder="" required>
                </div>
                <div class="" style="width:48%;">
                    <label for="inputNombre" class="lbl-general">Nuevo nombre</label>
                    <input id="inputNombre" type="text" class="input-text" name="nombre" placeholder="" required>
                </div>
                <div class="d-flex col-12 justify-content-end mt-4">
                    <div class="col-3 d-flex justify-content-end" >
                        <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                    </div>
                </div>
            </form>
        </div>
        <div id="containerChangePass" class="mb-5 px-md-5">
            <div class="mb-4">
                <h4>Cambiar contraseña</h4>
            </div>
            <form id="formChangePass" action="" method="POST">
                <input type="hidden" name="formulario" value="changepass">
                <div class="" style="width:48%;">
                    <label for="inputPass1" class="lbl-general">Contraseña actual</label>
                    <input id="inputPass1" type="password" class="input-text" name="password1" placeholder="" required>
                </div>
                <div class="" style="width:48%;">
                    <label for="inputPass2" class="lbl-general">Nueva contraseña</label>
                    <input id="inputPass2" type="password" class="input-text" name="password2" placeholder="" required>
                </div>
                <div class="d-flex col-12 justify-content-end mt-4">
                    <div class="col-3 d-flex justify-content-end" >
                        <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
<?php include(ROOT_PATH . 'includes/footer.php'); ?>
<!-- ------------------------------------------------------------------------------------------------------- -->
<script>
$(document).ready(function() {

    // Ejecuta el script PHP que actualiza billets_lotes
    // $.ajax({
    //     url: '../ajax/claveslp.php', // ruta del script
    //     type: 'POST', // aunque no enviamos datos, usamos POST para consistencia
    //     dataType: 'json',
    //     success: function(response) {
    //         console.log(response);

    //         if (response.success) {
    //             Swal.fire({
    //                 icon: 'success',
    //                 title: 'Proceso completado',
    //                 html: `
    //                     <b>Actualizados:</b> ${response.actualizados}<br>
    //                     <b>Omitidos:</b> ${response.omitidos}<br>
    //                     <b>Errores:</b> ${response.errores}
    //                 `,
    //                 confirmButtonText: 'Aceptar'
    //             });
    //         } else {
    //             Swal.fire({
    //                 icon: 'error',
    //                 title: 'Error',
    //                 text: response.error || response.message
    //             });
    //         }
    //     },
    //     error: function(xhr, status, error) {
    //         Swal.fire({
    //             icon: 'error',
    //             title: 'Error AJAX',
    //             text: 'No se pudo ejecutar la actualizacion. Revisa la consola.'
    //         });
    //         console.error("AJAX Error:", status, error);
    //     }
    // });

});
</script>


</body>
</html>