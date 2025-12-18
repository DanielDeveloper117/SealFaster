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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-selectTipoSello.css'); ?>">

    <title>Tipo de sello</title>
</head>
<body>

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>
    
    <div class="titulo d-flex col-12 justify-content-center">
        <h1 class="display-5 fw-bold">Seleccione familia del perfil</h1>
    </div>
    
    <section class="d-flex flex-column col-12 justify-content-center align-items-center">        
        <div id="containerFamily" class="cards gap-3">
            <a class="card" href="tipo.php?tipo=rotary">
                <img class="card_img" src="../assets/img/family/rotary/rotary.jpg" alt="Sello Rotary">
                <div class="card_info">
                    <span class="card_title">Rotary<br><span class="card_subtitle">Rotativo</span></span>
                </div>  
            </a>
            <a class="card" href="tipo.php?tipo=piston">
                <img class="card_img" src="../assets/img/family/piston/piston.jpeg" alt="Sello Piston">
                <div class="card_info">
                    <span class="card_title">Piston<br><span class="card_subtitle">Pistón</span></span>
                </div>
            </a>
            <a class="card" href="tipo.php?tipo=backup">
                <img class="card_img" src="../assets/img/family/backup/backup.jpg" alt="Sello Backup">
                <div class="card_info">
                    <span class="card_title">Backup<br><span class="card_subtitle">Respaldo</span></span>
                </div>
            </a>
            <a class="card" href="tipo.php?tipo=guide">
                <img class="card_img" src="../assets/img/family/guide/guide.jpg" alt="Sello Guide">
                <div class="card_info">
                    <span class="card_title">Guide<br><span class="card_subtitle">Guía</span></span>
                </div>
            </a>
            <a class="card" href="tipo.php?tipo=wipers">
                <img class="card_img" src="../assets/img/family/wiper/wiper.jpg" alt="Sello Wipers">
                <div class="card_info">
                    <span class="card_title">Wipers<br><span class="card_subtitle">Limpiadores</span></span>
                </div>
            </a>
            <a class="card" href="tipo.php?tipo=rod">
                <img class="card_img" src="../assets/img/family/rod/rod.jpg" alt="Sello Rod">
                <div class="card_info">
                    <span class="card_title">Rod<br><span class="card_subtitle">Vástago</span></span>
                </div>
            </a>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="infoModalLabel">Información de Sello</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalBodyContent">
                    <!-- Contenido se actualizará dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    <a href="" id="modalRedirectButton" class="btn-general">Seleccionar tipo de sello</a>
                </div>
            </div>
        </div>
    </div>
<div style="height:300px;"></div>    
<?php include(ROOT_PATH . 'includes/footer.php'); ?>
</body>
</html>