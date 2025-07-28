<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}

// Verificar si se ha enviado un formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el código de verificación enviado por el administrador
    $codigo = $_POST["codigo"];

    try {
        // Verificar si el código de verificación coincide con algún registro y si está activo
        $stmt_verificar = $conn->prepare("SELECT activo FROM login WHERE codigoVerificacion = :codigo");
        $stmt_verificar->bindParam(':codigo', $codigo);
        $stmt_verificar->execute();
        $resultado = $stmt_verificar->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            if ($resultado['activo'] == 0) {
                // El usuario está inactivo, actualizar el estado a activo
                $stmt_actualizar = $conn->prepare("UPDATE login SET activo = 1 WHERE codigoVerificacion = :codigo");
                $stmt_actualizar->bindParam(':codigo', $codigo);
                if ($stmt_actualizar->execute()) {
                    // Mostrar modal de éxito
                    echo '				
                        <div class="modal fade" id="ModalSuccess" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1">
                            <!-- Contenedor del header, body y footer del modal -->
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <!-- contenedor del titulo -->
                                    <div class="modal-header justify-content-between">
                                        <h5 class="modal-title text-success">Proceso exitoso</h5>
                                        <a href="#">
                                            <button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                        </a>
                                    </div>
                                    <!-- contenedor del body -->
                                    <div class="modal-body">
                                        <p>El código de verificación ha sido validado y activado correctamente.</p>
                                    </div>
                                    <!-- contenedor del footer -->
                                    <div class="modal-footer">
                                        <a href="#">
                                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <script>
                            // Mostrar el modal al recibir el formulario
                            window.onload = function() {
                                var modal = new bootstrap.Modal(document.getElementById("ModalSuccess"));
                                modal.show();
                            };
                        </script>';
                }
            } else {
                // El usuario está activo, mostrar modal de advertencia
                echo '				
                    <div class="modal fade" id="ModalWarning" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1">
                        <!-- Contenedor del header, body y footer del modal -->
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <!-- contenedor del titulo -->
                                <div class="modal-header justify-content-between">
                                    <h5 class="modal-title text-warning">Aviso</h5>
                                    <a href="#">
                                        <button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                    </a>
                                </div>
                                <!-- contenedor del body -->
                                <div class="modal-body">
                                    <p>El usuario ya ha sido activado anteriormente.</p>
                                </div>
                                <!-- contenedor del footer -->
                                <div class="modal-footer">
                                    <a href="#">
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
                                    </a>                        
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        // Mostrar el modal al recibir el formulario
                        window.onload = function() {
                            var modal = new bootstrap.Modal(document.getElementById("ModalWarning"));
                            modal.show();
                        };
                    </script>';
            }
        } else {
            // El código de verificación no coincide con ningún registro
            echo '				
                <div class="modal fade" id="ModalFailed" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" >
                    <!-- Contenedor del header, body y footer del modal -->
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <!-- contenedor del titulo -->
                            <div class="modal-header justify-content-between">
                                <h5 class="modal-title text-danger">Algo salió mal</h5>
                                <a href="#">
                                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </a>
                            </div>
                            <!-- contenedor del body -->
                            <div class="modal-body">
                                <p>El código de verificación no coincide con ningún usuario.</p>
                            </div>
                            <!-- contenedor del footer -->
                            <div class="modal-footer">
                                <a href="#">
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Volver a intentar</button>
                                </a>                        
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    // Mostrar el modal al recibir el formulario
                    window.onload = function() {
                        var modal = new bootstrap.Modal(document.getElementById("ModalFailed"));
                        modal.show();
                    };
                </script>';
        }
    } catch (PDOException $e) {
        // Manejar errores de la base de datos
        echo 'Error: ' . $e->getMessage();
        // Mostrar modal de error
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <link rel="stylessheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-formulario.css'); ?>">

    <title>Validar código de verificación</title>
</head>
<body>
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>
<div class="titulo mt-3">
    <h1>Validar código de verificación</h1>
</div>

<section class="section-form mt-5 mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="container-form d-flex col-11 col-md-4 justify-content-center align-items-center">
        <form class="form-general w-100 d-flex p-4 flex-column justify-content-center" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

            <label class="mb-3 lbl-general" for="codigo">Código de verificación:</label>
            <input class="input-text" type="text" id="codigo" name="codigo" placeholder="Ingresar código de verificación" required>

            <button type="submit" class="btn-general">Validar código</button>
        </form>
    </div>
</section>


</body>
</html>
