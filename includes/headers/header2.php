<?php
    require_once(__DIR__ . '/../../config/rutes.php');
    require_once(ROOT_PATH . 'auth/session_manager.php');
    include(ROOT_PATH . 'includes/backend_info_user.php');
?>
<link rel="stylesheet" href="<?= controlCache('../assets/dependencies/bootstrap-icons.min.css'); ?>">
<link rel="stylesheet" href="<?= controlCache('../assets/css/root.css'); ?>">
<link rel="stylesheet" href="<?= controlCache('../assets/css/menu2.css'); ?>">
<script src="<?= controlCache('../assets/js/menu2.js'); ?>"></script>

<div id="topbar">
    <div class="logo-area">
        <img src="../assets/img/general/sealfaster.png" alt="Logo SealFaster" />
    </div>

    <button id="burgerBtn" class="bi bi-list" aria-label="Menú" aria-expanded="false"></button>

    <nav id="navbar" role="navigation" aria-label="Navegación principal">
        <ul class="nav-links">

            <!-- Inicio — visible para todos -->
            <li>
                <a href="../modules/welcome.php" class="nav-item-simple">
                    <i class="bi bi-house-door-fill nav-icon"></i>
                    <span>Inicio</span>
                </a>
            </li>

            <?php switch($lider_usuario):  
                case 0:
                case 1: ?>
                <!-- Administrador || Sistemas -->
                <!-- Grupo: Inventarios/almacen -->
                <li class="nav-group">
                    <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-table nav-icon"></i>
                        <span>Inventario</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="nav-dropdown" role="menu">
                        <li role="none">
                            <a href="../modules/panel_inventario.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-display nav-icon"></i> 
                                Panel de funciones
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/traspasos.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-arrow-left-right nav-icon"></i> 
                                Traspasos de barras
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/barras_venta.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-cash-stack nav-icon"></i> 
                                Barras vendidas
                            </a>
                        </li role="none">
                        <li role="none">
                            <a href="../modules/almacenes.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-buildings nav-icon"></i> 
                                Almacenes
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/inv_tools.php" role="menuitem" class="nav-item-simple d-none">
                                <i class="bi bi-tools nav-icon"></i> 
                                Herramientas
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Grupo: Ventas -->
                <li class="nav-group">
                    <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-cash-coin nav-icon"></i>
                        <span>Ventas</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="nav-dropdown" role="menu">
                        <li role="none">
                            <a href="../modules/select_familia.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-calculator nav-icon"></i> 
                                Cotizador
                            </a>
                        </li>
                        <li role="none">
                            <a id="enlaceCotizaciones" href="../modules/cotizaciones.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-card-heading nav-icon"></i> 
                                Cotizaciones
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Grupo: Producción -->
                <li class="nav-group">
                    <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                        <!-- gear-wide-connected motherboard disc cpu vinyl wrench-adjustable-circle feather2 columns-gap collection bullseye graph-up-arrow percent perplexity rulers screwdriver sliders sliders2-vertical -->
                        <i class="bi bi-graph-up-arrow nav-icon"></i>
                        <span>Producción</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="nav-dropdown" role="menu">
                        <li role="none">
                            <a href="../modules/produccion_vn.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-file-earmark-text nav-icon"></i> 
                                Requisiciones
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/produccion_cnc.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-motherboard nav-icon"></i> 
                                Maquinados
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Grupo: CNC Administracion -->
                <li class="nav-group">
                    <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-cpu nav-icon"></i>
                        <span>CNC</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="nav-dropdown" role="menu">
                        <li role="none">
                            <a href="../modules/perfiles.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-grid-3x3-gap nav-icon"></i> 
                                Perfiles - porcentajes y tolerancias
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/grupos_limitantes.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-columns-gap nav-icon"></i> 
                                Grupos y limitantes herramientas
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/herramientas.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-wrench-adjustable-circle nav-icon"></i> 
                                Catálogo herramientas
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Grupo: Configuración -->
                <li class="nav-group">
                    <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-sliders nav-icon"></i>
                        <span>Administración</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="nav-dropdown" role="menu">
                        <li role="none">
                            <a href="../modules/parametros_cotizador.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-toggles nav-icon"></i> 
                                Parámetros cotizador
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/panel_claves.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-display nav-icon"></i> 
                                Gestión de claves
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/claves_alternas.php" role="menuitem" class="nav-item-simple d-none">
                                <i class="bi bi-shuffle nav-icon"></i> 
                                Claves alternas
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/desencriptar.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-key nav-icon"></i> 
                                Desencriptar
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/users.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-people nav-icon"></i> 
                                Usuarios
                            </a>
                        </li>
                    </ul>
                </li>
            <?php break; ?>

            <?php case 3: /* Ventas */ ?>

                <!-- Grupo: Inventarios/almacen -->
                <li class="nav-group">
                    <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-table nav-icon"></i>
                        <span>Inventario</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="nav-dropdown" role="menu">
                        <li role="none">
                            <a href="../modules/panel_inventario.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-display nav-icon"></i> 
                                Panel de funciones
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/almacenes.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-buildings nav-icon"></i> 
                                Almacenes
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Grupo: Ventas -->
                <li class="nav-group">
                    <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-cash-coin nav-icon"></i>
                        <span>Ventas</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="nav-dropdown" role="menu">
                        <li role="none">
                            <a href="../modules/select_familia.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-calculator nav-icon"></i> 
                                Cotizador
                            </a>
                        </li>
                        <li role="none">
                            <a id="enlaceCotizaciones" href="../modules/cotizaciones.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-card-heading nav-icon"></i> 
                                Cotizaciones
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Grupo: Producción -->
                <li role="none">
                    <a href="../modules/produccion_vn.php" role="menuitem" class="nav-item-simple">
                        <i class="bi bi-file-earmark-text nav-icon"></i> 
                        Requisiciones
                    </a>
                </li>

                <!-- Guia de usuario -->
                <li role="none">
                    <?php 
                        echo '<a href="'.controlCache("../files/GUIA_VENTAS.pdf").'" target="_blank" class=" nav-item-simple ">
                                <img id="imgGuia" class="" src="../assets/img/general/ug.png" title="Ver guía de usuario vendedor" style="height:38px;">
                            </a>'; 
                    ?>
                </li>
                            
            <?php break; ?>  

            <?php case 2: /* CNC */ ?>

                <?php if($rol_usuario == "Gerente"): ?>
                    <!-- Grupo: Inventarios/almacen -->
                    <li class="nav-group">
                        <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-table nav-icon"></i>
                            <span>Inventario</span>
                            <i class="bi bi-chevron-down nav-chevron"></i>
                        </button>
                        <ul class="nav-dropdown" role="menu">
                            <li role="none">
                                <a href="../modules/panel_inventario.php" role="menuitem" class="nav-item-simple">
                                    <i class="bi bi-display nav-icon"></i> 
                                    Panel de funciones
                                </a>
                            </li>
                            <li role="none">
                                <a href="../modules/almacenes.php" role="menuitem" class="nav-item-simple">
                                    <i class="bi bi-buildings nav-icon"></i> 
                                    Almacenes
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Grupo: Ventas -->
                    <li class="nav-group">
                        <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-cash-coin nav-icon"></i>
                            <span>Ventas</span>
                            <i class="bi bi-chevron-down nav-chevron"></i>
                        </button>
                        <ul class="nav-dropdown" role="menu">
                            <li role="none">
                                <a href="../modules/select_familia.php" role="menuitem" class="nav-item-simple">
                                    <i class="bi bi-calculator nav-icon"></i> 
                                    Cotizador
                                </a>
                            </li>
                            <li role="none">
                                <a id="enlaceCotizaciones" href="../modules/cotizaciones.php" role="menuitem" class="nav-item-simple">
                                    <i class="bi bi-card-heading nav-icon"></i> 
                                    Cotizaciones
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Grupo: CNC Administracion -->
                    <li class="nav-group">
                        <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                            <i class="bi bi-cpu nav-icon"></i>
                            <span>CNC</span>
                            <i class="bi bi-chevron-down nav-chevron"></i>
                        </button>
                        <ul class="nav-dropdown" role="menu">
                            <li role="none">
                                <a href="../modules/perfiles.php" role="menuitem" class="nav-item-simple">
                                    <i class="bi bi-grid-3x3-gap nav-icon"></i> 
                                    Perfiles - porcentajes y tolerancias
                                </a>
                            </li>
                            <li role="none">
                                <a href="../modules/grupos_limitantes.php" role="menuitem" class="nav-item-simple">
                                    <i class="bi bi-columns-gap nav-icon"></i> 
                                    Grupos y limitantes herramientas
                                </a>
                            </li>
                            <li role="none">
                                <a href="../modules/herramientas.php" role="menuitem" class="nav-item-simple">
                                    <i class="bi bi-wrench-adjustable-circle nav-icon"></i> 
                                    Catálogo herramientas
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <!-- Grupo: Producción -->
                <li role="none">
                    <a href="../modules/produccion_cnc.php" role="menuitem" class="nav-item-simple">
                        <i class="bi bi-file-earmark-text nav-icon"></i> 
                        Producción
                    </a>
                </li>

            <?php break; ?>

            <?php case 6: /* Inventarios */ ?>

                <!-- Grupo: Inventarios/almacen -->
                <li class="nav-group">
                    <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-table nav-icon"></i>
                        <span>Inventario</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="nav-dropdown" role="menu">
                        <li role="none">
                            <a href="../modules/panel_inventario.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-display nav-icon"></i> 
                                Panel de funciones
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/traspasos.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-arrow-left-right nav-icon"></i> 
                                Traspasos de barras
                            </a>
                        </li>
                        <li role="none">
                            <a href="../modules/barras_venta.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-cash-stack nav-icon"></i> 
                                Barras vendidas
                            </a>
                        </li role="none">
                        <li role="none">
                            <a href="../modules/almacenes.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-buildings nav-icon"></i> 
                                Almacenes
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Grupo: Producción -->
                <!-- <li class="nav-group">
                    <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-gear-wide-connected nav-icon"></i>
                        <span>Producción</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="nav-dropdown" role="menu">
                        <li role="none">
                            <a href="../modules/produccion_cnc.php" role="menuitem" class="nav-item-simple">
                                <i class="bi bi-cpu nav-icon"></i> 
                                Producción CNC
                            </a>
                        </li>
                    </ul>
                </li> -->
                <li role="none">
                    <a href="../modules/produccion_cnc.php" role="menuitem" class="nav-item-simple">
                        <i class="bi bi-file-earmark-text nav-icon"></i> 
                        Producción
                    </a>
                </li>
                <?php if($rol_usuario == "Gerente"): ?>
                <!-- Guia de usuario -->
                <li role="none">
                    <?php 
                        echo '<a href="'.controlCache("../files/GUIA_VENTAS.pdf").'" target="_blank" class=" nav-item-simple ">
                                <img id="imgGuia" class="" src="../assets/img/general/ug.png" title="Ver guía de usuario" style="height:38px;">
                            </a>'; 
                    ?>
                </li>
                <?php endif; ?>
            <?php break; ?>

            <?php case 4: /* Compras */ ?>

                <li>
                    <a href="../modules/precios_compras.php" class="nav-item-simple">
                        <i class="bi bi-bag-check nav-icon"></i>
                        <span>Precios pendientes</span>
                    </a>
                </li>

            <?php break; ?>



            <?php case 5: /* Externo */ ?>

                <li class="nav-group">
                    <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-cart3 nav-icon"></i>
                        <span>Ventas</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="nav-dropdown" role="menu">
                        <li role="none">
                            <a href="../modules/select_familia.php" role="menuitem">
                                <i class="bi bi-calculator nav-icon"></i> Cotizador
                            </a>
                        </li>
                        <li role="none">
                            <a id="enlaceCotizaciones" href="../modules/cotizaciones.php" role="menuitem">
                                <i class="bi bi-file-earmark-text nav-icon"></i> Cotizaciones
                            </a>
                        </li>
                    </ul>
                </li>

            <?php break; ?>

            <?php default: /* Otro */ ?>

                <li>
                    <a href="../modules/panel_inventario.php" class="nav-item-simple">
                        <i class="bi bi-box-seam-fill nav-icon"></i>
                        <span>Inventario CNC</span>
                    </a>
                </li>

                <li class="nav-group">
                    <button class="nav-group-trigger" aria-haspopup="true" aria-expanded="false">
                        <i class="bi bi-cart3 nav-icon"></i>
                        <span>Ventas</span>
                        <i class="bi bi-chevron-down nav-chevron"></i>
                    </button>
                    <ul class="nav-dropdown" role="menu">
                        <li role="none">
                            <a href="../modules/select_familia.php" role="menuitem">
                                <i class="bi bi-calculator nav-icon"></i> Cotizador
                            </a>
                        </li>
                        <li role="none">
                            <a id="enlaceCotizaciones" href="../modules/cotizaciones.php" role="menuitem">
                                <i class="bi bi-file-earmark-text nav-icon"></i> Cotizaciones
                            </a>
                        </li>
                    </ul>
                </li>

            <?php break; ?>

            <?php endswitch; ?>

        </ul>

        <!-- Menú de usuario -->
        <div class="user-menu">
            <button id="userIcon" class="user-menu-trigger" aria-expanded="false" aria-label="Menú de usuario">
                <i class="bi bi-person-circle"></i>
                <span class="user-name-label"><?= htmlspecialchars($nombreUser) ?></span>
                <i class="bi bi-chevron-down user-chevron"></i>
            </button>
            <div id="userDropdown" role="menu" aria-label="Opciones de usuario">
                <div class="user-dropdown-header">
                    <i class="bi bi-person-circle user-dropdown-avatar"></i>
                    <span><?= htmlspecialchars($nombreUser) ?></span>
                </div>
                <div class="user-dropdown-divider"></div>
                <a href="../modules/configuracion.php" role="menuitem">
                    <i class="bi bi-person-gear"></i> Mi cuenta
                </a>
                <a href="../modules/configuracion.php#config" role="menuitem">
                    <i class="bi bi-gear"></i> Configuración
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="../auth/cerrar_sesion.php" class="logout" role="menuitem">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </div>
        </div>

    </nav>
</div>