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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-formulario.css'); ?>">
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-welcome.css'); ?>">

    <title>Inicio</title>

</head>
<body>

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<div class="d-flex flex-column justify-content-between align-items-center" style="height:100vh; width:100%;">
    <main class="container main-animated mt-2 mb-2" style="position:relative; z-index:-1;">

        <section class="d-flex flex-column">
            <p class="speech-bubble">Nos alegra verte de nuevo. Ahora puedes acceder a todas las funcionalidades disponibles del Cotizador SealFaster para tu usuario. ¡Mucha suerte!</p>
            <img class="img-speach" src="../assets/img/general/speech2.jpg" alt="">
            <!-- Imagen de bienvenida -->
            <div class="mb-4 d-flex col-12 justify-content-between align-items-start">
                <div class="sello-img col-3">
                    <img src="../assets/img/general/sellocoti.png" class="img-fluid" alt="Bienvenida">
                </div>
                <div class="d-flex flex-column col-8 pt-4">
                    <?php include(ROOT_PATH . 'includes/info_user.php'); ?>
                </div>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy; <?= date("Y"); ?> Sellos y Retenes de San Luis S.A. de C.V. Todos los derechos reservados.</p>
    </footer>
</div>
<script>
    $(document).ready(function(){
        // $.ajax({
        //     url: "ajax_notificacion.php",
        //     type: "POST",
        //     data: { mensaje: "El usuario ha cargado welcome. <?= $usuario_desencriptado; ?>" },
        //     success: function(response) {
        //         console.log("Notificación enviada: ", response);
        //     },
        //     error: function(error) {
        //         console.error("Error al enviar la notificación: ", error);
        //     }
        // });

        // window.addEventListener("focus", function() {
        //     console.log("El usuario está en la ventana de la página.");
            
        //     $.ajax({
        //         url: "ajax_notificacion.php",
        //         type: "POST",
        //         data: { mensaje: "Focus en welcome" },
        //         success: function(response) {
        //             console.log("Notificación enviada: ", response);
        //         },
        //         error: function(error) {
        //             console.error("Error al enviar la notificación: ", error);
        //         }
        //     });
        // });
    });
</script>
</body>
</html>
