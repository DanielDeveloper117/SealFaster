<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="../assets/img/general/favicon.ico?v=2" />
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>  
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-desencriptador.css'); ?>">

    <title>Desencriptador</title>
    
</head>
<body>

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

    <div class="titulo mt-3 mb-3">
        <h1>Desencriptar valor encriptado</h1>
    </div>
    <section class="section-form d-flex col-12 justify-content-center align-items-center">
        <div class="container-form d-flex col-11 col-md-4 justify-content-center align-items-center">
            <form id="formulario" class="form-general w-100 d-flex p-4 flex-column justify-content-center" action="" method="post">
                <label class="lbl-general mb-1" for="tabla">Seleccionar tabla:</label>
                <select class="selector mb-3" name="tabla" id="tabla">
                    <option value="" selected disabled>Seleccione una tabla</option>
                    <option value="login">Login</option>
                    <option value="log_usuarios">Log Usuarios</option>
                </select>
                <label class="lbl-general mb-1" for="campo">Nombre del campo (columna):</label>
                <select class="selector mb-3" name="campo" id="campo" required>
                    <option value="" selected disabled>Seleccione un campo</option>
                </select>
                <label class="lbl-general mb-1" for="valor_encriptado">Valor encriptado:</label>
                <input class="input-text mb-4" type="text" id="valor_encriptado" name="valor_encriptado" required>

                <button type="submit" class="btn-general mb-2" id="btnModal" data-bs-toggle="modal" data-bs-target="#OpenModal">Desencriptar</button>
            </form>
        </div>
    </section>


    <script>
        document.getElementById("formulario").addEventListener("submit", function(event) {
            // Evitar el envío predeterminado del formulario
            //event.preventDefault();
        });

        document.getElementById('tabla').addEventListener('change', function() {
            var tabla = this.value;
            var campoSelect = document.getElementById('campo');
            campoSelect.innerHTML = ''; // Limpiar opciones anteriores

            if (tabla === 'login') {
                var campos = ['usuario', 'password', 'nombre', 'area'];
            } else if (tabla === 'log_usuarios') {
                var campos = ['Usuario', 'Accion', 'Instruccion'];
            }

            campos.forEach(function(campo) {
                var option = document.createElement('option');
                option.value = campo;
                option.textContent = campo;
                campoSelect.appendChild(option);
            });
        });
    </script>

    <?php

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Obtener los datos del formulario
        $tabla = $_POST['tabla'];
        $campo = $_POST['campo'];
        $valor_encriptado_base64 = $_POST['valor_encriptado'];

        // Convertir el valor encriptado de base64 a binario
        $valor_encriptado = base64_decode($valor_encriptado_base64);

        // Clave de encriptación
        $clave_encriptacion = 'SRS2024#tides';

        // Conexión a la base de datos usando PDO
        try {
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Preparar la consulta para obtener el valor encriptado de la base de datos
            $query = "SELECT $campo FROM $tabla WHERE $campo = :valor";
            $stmt = $conn->prepare($query);

            // Ejecutar la consulta
            $stmt->bindParam(':valor', $valor_encriptado_base64, PDO::PARAM_STR);
            $stmt->execute();

            // Obtener el valor encriptado
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);


            if ($resultado) {
                // Desencriptar el valor utilizando OpenSSL
                $valor_desencriptado = openssl_decrypt($resultado[$campo], 'AES-128-ECB', $clave_encriptacion);
                // Mostrar el resultado desencriptado
                echo '				
                <div class="modal fade" id="OpenModal" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1">
                    <!-- Contenedor del header, body y footer del modal -->
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <!-- contenedor del titulo -->
                            <div class="modal-header">
                                <h5 class="modal-title text-success">El valor desencriptado es:</h5>
                                <button class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <!-- contenedor del body -->
                            <div class="modal-body">
                                <p>'. htmlspecialchars($valor_desencriptado) .'</p>
                            </div>
                            <!-- contenedor del footer -->
                            <div class="modal-footer">
                                <button type="button" class="btn-general" data-bs-dismiss="modal">Ok</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script>
                    // Mostrar el modal al recibir el formulario
                    window.onload = function() {
                        var modal = new bootstrap.Modal(document.getElementById("OpenModal"));
                        modal.show();
                    };
                </script>'
            ;

            } else {
                echo '				
                <div class="modal fade" id="ModalFailed" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static">
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
                                <p>No se encontró en la base de datos o la tabla seleccionada es incorrecta</p>
                            </div>
                            <!-- contenedor del footer -->
                            <div class="modal-footer">
                                <a href="#">
                                    <button type="button" class="btn-general" data-bs-dismiss="modal">Volver a intentar</button>
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
                </script>'
            ;            }
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    ?>
</body>
</html>
