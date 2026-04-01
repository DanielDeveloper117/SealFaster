<?php
    include(ROOT_PATH . 'includes/backend_info_user.php');
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= controlCache('../assets/css/root.css'); ?>">
<link rel="stylesheet" href="<?= controlCache('../assets/css/menu.css'); ?>">
<script src="<?= controlCache('../assets/js/menu.js'); ?>"></script>

<div id="topbar">
    <div class="logo-area">
        <img src="../assets/img/general/sealfaster.png" alt="Logo SealFaster" />
    </div>

    <button id="burgerBtn" class="bi bi-list"></button>

    <nav id="navbar">
    <ul class="nav-links">
        <li><a href="../modules/welcome.php">Inicio</a></li>
        <?php
            switch($lider_usuario){
                case 1:
                    # administrador/directivo
                    echo '
                        <li><a href="../modules/panel_inventario.php">Inventario CNC</a></li>
                        <li><a href="../modules/traspasos.php">Traspasos</a></li>
                        <li><a href="../modules/select_familia.php">Cotizador</a></li>
                        <li><a id ="enlaceCotizaciones" href="../modules/cotizaciones.php">Cotizaciones</a></li>
                        <li><a class="" href="../modules/produccion_vn.php">Requisiciónes</a></li>
                        <li><a class="" href="../modules/produccion_cnc.php">Producción CNC</a></li>
                        <li><a class="" href="../modules/parametros_cotizador.php" >Parametros</a></li>
                        <li><a class="" href="../modules/precios.php">Claves SRS</a></li>
                        <li><a class="" href="../modules/claves_alternas.php">Claves alternas</a></li>
                        
                        <li><a class="" href="../modules/users.php" >Usuarios</a></li>
                        
                    ';
                break;
                case 2:
                    # cnc
                    if($rol_usuario == "Gerente"){
                        echo '
                            <li><a href="../modules/panel_inventario.php">Inventario CNC</a></li>
                            <li><a href="../modules/select_familia.php">Cotizador</a></li>
                            <li><a id ="enlaceCotizaciones" href="../modules/cotizaciones.php">Cotizaciones</a></li>
                            <li><a href="../modules/produccion_cnc.php">Producción</a></li>
                        ';
                    }else{
                        echo '
                            <li><a href="../modules/panel_inventario.php">Inventario CNC</a></li>
                            <li><a href="../modules/produccion_cnc.php">Producción</a></li>
                        ';

                    }
                break;
                case 3:
                    # ventas
                    echo '
                        <li><a href="../modules/panel_inventario.php">Inventario CNC</a></li>
                        <li><a href="../modules/select_familia.php">Cotizador</a></li>
                        <li><a id ="enlaceCotizaciones" href="../modules/cotizaciones.php">Cotizaciones</a></li>
                        <li><a href="../modules/produccion_vn.php">Requisiciones</a></li>
                        <li><a href="'.controlCache("../files/GUIA-PARA-COTIZACION-Y-REQUISICION-DE-MAQUINADO-DE-SELLOS.pdf").'" target="_blank" class="pe-4 position-relative">
                                <img id="imgGuia" class="position-absolute" src="../assets/img/general/ug.png" title="Ver guía de usuario vendedor">
                            </a>
                        </li>
                        ';        
                        //<i class="bi bi-journal-text position-absolute" style="top: 10px;left:20px;"></i>
                break;
                case 0:
                    # sistemas
                    echo '
                        <li><a href="../modules/panel_inventario.php">Inventario CNC</a></li>
                        <li><a href="../modules/traspasos.php">Traspasos</a></li>
                        <li><a href="../modules/select_familia.php">Cotizador</a></li>
                        <li><a id ="enlaceCotizaciones" href="../modules/cotizaciones.php">Cotizaciones</a></li>
                        <li><a class="" href="../modules/parametros_cotizador.php" >Parametros</a></li>
                        <li><a class="" href="../modules/precios.php">Claves SRS</a></li>
                        <!-- <li><a class="" href="../modules/precios_compras.php">Precios pendientes</a></li> -->
                        <li><a class="" href="../modules/claves_alternas.php">Claves alternas</a></li>
                        <li><a class="" href="../modules/desencriptar.php" >Desencriptar</a></li>
                        <li><a class="" href="../modules/users.php" >Usuarios</a></li>
                    ';
                break;
                case 4:
                    # compras
                    echo '
                        <li><a class="" href="../modules/precios_compras.php" >Precios pendientes</a></li>
                    ';
                break;
                case 6:
                    # Inventarios
                    echo '
                        <li><a href="../modules/panel_inventario.php">Inventario CNC</a></li>
                        <li><a href="../modules/traspasos.php">Traspasos</a></li>
                        <li><a href="../modules/produccion_cnc.php">Requisiciónes</a></li>
                    ';
                break;
                case 5:
                    # externo
                    echo '
                        <li><a href="../modules/select_familia.php">Cotizador</a></li>
                        <li><a id ="enlaceCotizaciones" href="../modules/cotizaciones.php">Cotizaciones</a></li>
                    ';
                break;
                default:
                    # otro
                    echo '
                        <li><a href="../modules/panel_inventario.php">Inventario CNC</a></li>
                        <li><a href="../modules/select_familia.php">Cotizador</a></li>
                        <li><a id ="enlaceCotizaciones" href="../modules/cotizaciones.php">Cotizaciones</a></li>
                    ';
                break;
            } 
        ?>

    </ul>

    <div class="user-menu">
        <i class="bi bi-person-circle" id="userIcon"></i>
        <div id="userDropdown">
            <span><?= htmlspecialchars($nombreUser); ?></span>
            <a href="../modules/configuracion.php">Mi cuenta</a>
            <a href="../modules/configuracion.php#config">Configuración</a>
            <a href="../auth/cerrar_sesion.php" class="logout">Cerrar sesión</a>
        </div>
    </div>
    </nav>
</div>

