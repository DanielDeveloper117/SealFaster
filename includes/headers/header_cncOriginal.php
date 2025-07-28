<?php
    include(ROOT_PATH . 'includes/backend_info_user.php');
?>
<!-- USUARIO CNC -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= controlCache('../assets/css/styles-header.css'); ?>">
<script src="<?= controlCache('../assets/js/header.js'); ?>"></script>

<header class="d-none d-xl-flex col-12 justify-content-between text-white p-2 bg-dark ">
    <div class="d-flex justify-content-start align-items-center img-sealfaster-container">
        <!-- Capa de luz reflejada sobre la imagen -->
        <div class="light-reflection"></div>
        <img class="img-fluid img-sealfaster" src="../assets/img/general/sealfaster.png" alt="">
    </div>
    <nav class="d-flex">
        <div class="d-flex flex-column-reverse flex-xl-row flex-md-row justify-content-end align-items-start align-items-md-end align-items-md-center gap-3"> 
            <div class="mb-2 mb-md-0 p-1 p-md-3">
                <a class="" href="../modules/welcome.php" >Inicio</a>
            </div>
            <div class="mb-2 mb-md-0 p-1 p-md-3">
                <a class="" href="../modules/filtros_inventario_cnc.php" >Inventario CNC</a>
            </div>
            <div class="mb-2 mb-md-0 p-1 p-md-3">
                <a class="" href="../modules/selectTipoSello.php">Cotizador de sellos</a>
            </div>
            <div class="mb-2 mb-md-0 p-1 p-md-3">
                <a class="" href="../modules/cotizaciones.php">Cotizaciones</a>
            </div>
            <div class="mb-2 mb-md-0 p-1 p-md-3">
                <a class="" href="../modules/produccion_cnc.php">Producción</a>
            </div>
            <div id="containerUser" class="mb-2 mb-md-0 p-md-2 d-flex flex-md-column align-items-center align-self-start align-items-end justify-content-end">
                
                <i id="buttonUser" class="bi bi-person-circle"></i>
                <div id="dropdownUser" class=" flex-column align-items-start">
                    <a href="../modules/configuracion.php" class="pb-3">Configuración</a>
                    <a href="../auth/cerrar_sesion.php" class="logout">Cerrar sesión</a>
                </div>
                <h5 class="d-md-none mb-0 px-1"><?= htmlspecialchars($nombreUser); ?></h5>
            </div>
            
        </div>
    </nav>
</header>
<button id="btnBurguer" class="bi bi-list d-none" type="button"></button>
<script>
    $(document).ready(function () {
    });
</script>

