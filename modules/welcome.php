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
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-welcome.css'); ?>">

    <title>Inicio</title>

</head>
<body style="padding-top: 0px !important;">

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>
<div></div>
<div id="containerMain">
    <div id="welcomeCard" class="d-flex justify-content-center">
      <div class="d-flex flex-column flex-md-row align-items-center">
        <div id="containerImg" class="col-md-4 text-center">
          <img src="../assets/img/general/sellocoti.png" class="img-fluid rounded-start p-3 animate-img" alt="Bienvenida">
        </div>
        <div id="containerCardBody" class="">
          <div class="card-body">
            <?php include(ROOT_PATH . 'includes/info_user.php'); ?>
          </div>
        </div>
      </div>
    </div>
</div>

<?php include(ROOT_PATH . 'includes/footer.php'); ?>

<script>
    $(document).ready(function(){
        document.querySelector('.animate-img').classList.add('visible');
        // $.ajax({
        //     url: "../ajax/ajax_notificacion.php",
        //     type: "POST",
        //     data: { mensaje: "El usuario ha cargado welcome. <?= $usuario_desencriptado; ?>" },
        //     success: function(response) {
        //         console.log("Notificacion enviada: ", response);
        //     },
        //     error: function(error) {
        //         console.error("Error al enviar la notificacion: ", error);
        //     }
        // });
    });
    Swal.fire({
      title: 'Novedades',
      text: 'Ahora puede seleccionar el tipo de medida (Sello, Metal o Muestra) para cada medida individualmente, también se mostrarán en las requisiciones. Se corrigieron bugs al generar PDF y al archivar cotizaciones.',
      icon: 'info',
      confirmButtonText: 'Entendido',
      width: '400px',  // Tamaño pequeño del modal
      padding: '10px',  // Relleno para que se vea agradable
      position: 'bottom-end', // Coloca el modal en la esquina superior derecha (puedes cambiarlo)
      toast: true, // Mostrar como un "toast", que es una notificación pequeña
      //timer: 5000, // El modal desaparece automáticamente después de 5 segundos (opcional)
      showConfirmButton: true // Mostrar el botón de confirmación
  });
</script>
</body>
</html>
