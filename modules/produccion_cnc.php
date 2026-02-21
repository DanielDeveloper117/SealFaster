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
    <script>
        window.ID_USUARIO_SESSION = "<?= $_SESSION['id'] ?>";
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/produccion_cnc.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/middleware_deteccion_cambios.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css"'); ?>"> 

    <?php 
        include(ROOT_PATH . 'includes/backend_info_user.php');
        include(ROOT_PATH . 'includes/backend/produccion_cnc.php'); 
    ?>

    <title>Producción</title>
</head>
<body>

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<style>
    .chosen-container-single .chosen-single {
        font-size: 16px;
        height: 50px;
        display: flex;
        align-items: center;
    }
    .chosen-container-single .chosen-results li {
        font-size: 15px;
    }
</style>
<div id="overlay">
    <div class="loading-message">
        <span>Cargando requisiciones, por favor, espere...</span>    
    </div>
</div>
<section class="section-table flex-column mt-2 mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-1 mb-3">
            <h1>Requisiciones para Maquinado de Sellos</h1>
            <!-- <div class="d-flex flex-row justify-content-between col-3 gap-5 mt-5">
                <button type="button" id="btnAgregar" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalAgregarEditar">Nueva requisicion</button>
            </div> -->
        </div>
        <div class="table-container">
            <div class="row mb-3">
                <div class="d-flex justify-content-start gap-3 col-12 col-md-8">
                    <button id="btnFiltrosBusqueda" type="button" 
                            class="btn-purple" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalFiltrosBusqueda">
                        <i class="bi bi-funnel"></i> Filtros de busqueda
                    </button>
                </div>
            </div>
            <table id="productionTable" class="table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th></th>
                        <!-- <th>Id</th> -->
                        <th>Folio</th>
                        <th>Estatus</th>
                        <th>Máquina CNC</th>
                        <th>Sucursal</th>
                        <th>Comentario</th>
                        <th>Vendedor</th>
                        <th>Cliente</th>
                        <!-- <th>Cotizaciones</th> -->
                        <th>Fecha</th>
                        <th>Num. pedido</th>
                        <th>Paqueteria</th>
                        <th>Factura/remision/nota</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    foreach ($arregloSelectRequisiciones as $row) {
                ?>
                    <tr data-id-requisicion="<?= htmlspecialchars($row['id_requisicion'] ?? ''); ?>" data-estatus="<?= htmlspecialchars($row['estatus'] ?? ''); ?>">
                        <td class="td-first-actions">
                            <div class="d-flex gap-2 container-actions">
                                <!-- PDF -->
                                <form action="../includes/functions/generar_requisicion.php" method="GET" target="_blank">
                                    <input type="hidden" name="id_requisicion" value="<?= htmlspecialchars($row['id_requisicion'] ?? ""); ?>">
                                    <button type="submit" class="btn-pdf" title="Generar PDF de esta cotizacion">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </button>
                                </form>

                                <?php
                                $estatusString = "";
                                $estatusClass = "span-status";
                                $titleButton="";
                                $iconButton="";
                                $classBtnColor = "";
                                echo '<div class="comentarios-wrapper">';
                                    echo '  <button type="button" class="btn-general btn-modal-comentarios-adjuntos"
                                                data-origen="requi"
                                                data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                data-es-mia="0"
                                                title="Comentarios y archivos adjuntos para esta requisición">
                                                <i class="bi bi-chat-left-text"></i>
                                            </button>';

                                    if ((int)$row['total_comentarios'] > 0) {
                                        echo '  <span class="badge-comentarios">'
                                                . (int)$row['total_comentarios'] .
                                            '</span>';
                                    }

                                echo '</div>';

                                switch ($row['estatus']) {
                                    case "Autorizada":
                                        $estatusString = "Autorizada";

                                        // CNC Gerente puede editar medidas
                                        //if ($tipo_usuario === "CNC" && $rol_usuario == "Gerente") {
                                        if ($tipo_usuario === "CNC" && $rol_usuario == "Gerente") {
                                            echo '<button type="button" class="btn-thunder btn-editar-medidas" 
                                                    data-bs-toggle="modal" data-bs-target="#modalEditarMedidas"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Editar medidas de las cotizaciones">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>';
                                                echo '<button type="button" class="btn-blue btn-iniciar-maquinado" 
                                                    data-bs-toggle="modal" data-bs-target="#modalGuardarOperador"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-autoriza="cnc"
                                                    title="Cambiar estatus a maquinado CNC iniciado">
                                                    <i class="bi bi-file-play"></i>
                                                </button>';
                                        } 
                                        if($tipo_usuario === "CNC" && $rol_usuario !== "Gerente"){
                                                echo '<button type="button" class="btn-blue btn-iniciar-maquinado" 
                                                    data-bs-toggle="modal" data-bs-target="#modalGuardarOperador"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-autoriza="cnc"
                                                    title="Cambiar estatus a maquinado CNC iniciado">
                                                    <i class="bi bi-file-play"></i>
                                                </button>';
                                        }

                                        // Inventarios puede agregar clave al almacen
                                        if ($tipo_usuario === "Inventarios") {
                                            $mostrarBtnEntregarBarras="NOMOSTRARBOTON";
                                            $claseBoton = "btn-thunder";
                                            $iconButton = "bi-database-add";
                                           if(!empty($row['fecha_entrega_barras'])){
                                                $mostrarBtnEntregarBarras="NOMOSTRARBOTON";
                                                $claseBoton = "btn-thunder";
                                                $iconButton = "bi-database-add";
                                                $titleButton = "Agregar/remplazar barras de control de almacen";
                                           }else{
                                                $mostrarBtnEntregarBarras="Producción";
                                                $claseBoton = "btn-amber";
                                                $iconButton = "bi-database";
                                                $titleButton = "Pendiente de entregar barras";
                                           }

                                            echo '<button class="'.$claseBoton.' btn-entregar-barras" 
                                                    data-bs-toggle="modal" data-bs-target="#modalTableControlAlmacenEntrega"
                                                    data-es_extra = "0"
                                                    data-estatus = "'.$mostrarBtnEntregarBarras.'"  
                                                    data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-maquina="' . htmlspecialchars($row['maquina'] ?? '') . '"
                                                    title="'.$titleButton.'">
                                                    <i class="bi '.$iconButton.'"></i>
                                                </button>';
                                        }
                                        break;

                                    case "Producción":
                                        $estatusString = "Producción";
                                        if ($tipo_usuario === "Inventarios") {
                                            $mostrarBtnEntregarBarras="NOMOSTRARBOTON";
                                            $claseBoton = "btn-thunder";
                                            $iconButton = "bi-database-add";
                                            
                                           if(!empty($row['fecha_entrega_barras'])){
                                                $mostrarBtnEntregarBarras="NOMOSTRARBOTON";
                                                $claseBoton = "btn-thunder";
                                                $iconButton = "bi-database-add";
                                                $titleButton = "Agregar/remplazar barras de control de almacen";
                                           }else{
                                                $mostrarBtnEntregarBarras="Producción";
                                                $claseBoton = "btn-amber";
                                                $iconButton = "bi-database";
                                                $titleButton = "Pendiente de entregar barras";
                                           }

                                            echo '<button class="'.$claseBoton.' btn-entregar-barras" 
                                                    data-bs-toggle="modal" data-bs-target="#modalTableControlAlmacenEntrega"
                                                    data-es_extra = "0"
                                                    data-estatus = "'.$mostrarBtnEntregarBarras.'"  
                                                    data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-maquina="' . htmlspecialchars($row['maquina'] ?? '') . '"
                                                    title="'.$titleButton.'">
                                                    <i class="bi '.$iconButton.'"></i>
                                                </button>';
   
                                        }elseif ($tipo_usuario === "CNC") {
                                            // aqui no span, solo controlado por estatusString
                                            if(empty($row['maquina']) && !empty($row['fecha_entrega_barras'])){

                                                echo '<button type="button" class="btn-blue btn-iniciar-maquinado" 
                                                    data-bs-toggle="modal" data-bs-target="#modalGuardarOperador"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-autoriza="cnc"
                                                    title="Cambiar estatus a maquinado CNC iniciado">
                                                    <i class="bi bi-file-play"></i>
                                                </button>';
                                            }
                                            if ($rol_usuario == "Gerente") {
                                                echo '<button type="button" class="btn-cancel btn-detener" 
                                                        data-bs-toggle="modal" data-bs-target="#modalDetener"
                                                        data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                        title="Detener/cancelar el maquinado de sellos">
                                                        <i class="bi bi-sign-stop"></i>
                                                    </button>';
                                            }
                                        }
                                        break;

                                    case "En producción":
                                        $estatusString = "En maquinado";

                                        //if ($tipo_usuario === "CNC" && $rol_usuario == "Gerente") {
                                        if ($tipo_usuario === "CNC") {
                                            if ($rol_usuario == $row['maquina']) {
                                                echo '<button type="button" class="btn-terracota btn-finalizar" 
                                                        data-bs-toggle="modal" data-bs-target="#modalFinalizar"
                                                        data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                        title="Finalizar maquinado">
                                                        <i class="bi bi-flag"></i>
                                                    </button>';
                                            }
                           
                                            if ($rol_usuario == "Gerente") {
                                                echo '<button type="button" class="btn-cancel btn-detener" 
                                                        data-bs-toggle="modal" data-bs-target="#modalDetener"
                                                        data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                        title="Detener/cancelar el maquinado de sellos">
                                                        <i class="bi bi-sign-stop"></i>
                                                    </button>';
                                            }
                                        } elseif ($tipo_usuario === "Inventarios") {
                                            echo '<button class="btn-thunder btn-entregar-barras" 
                                                    data-bs-toggle="modal" data-bs-target="#modalTableControlAlmacenEntrega"
                                                    data-es_extra = "1"
                                                    data-estatus = "En producción" 
                                                    data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-maquina="' . htmlspecialchars($row['maquina'] ?? '') . '"
                                                    title="Agregar/remplazar barras de control de almacen">
                                                    <i class="bi bi-database-add"></i>
                                                </button>';
 
                                        }else {
                                            // solo mensaje via estatusString
                                        }
                                        break;

                                    case "Finalizada":
                                        $estatusString = "Finalizada";
                                        // Inventarios puede agregar clave al almacen
                                        if ($tipo_usuario === "Inventarios") {
                                            echo '<button class="btn-general btn-bar-entry btn-claves-retorno" 
                                                    data-bs-toggle="modal" data-bs-target="#modalRetorno"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Retornar barras al inventario con nuevo stock">
                                                    <i class="bi bi-database-fill-down"></i>
                                                </button>';
                                        }else if ($tipo_usuario === "CNC") {
                                            if($row["fecha_revision_maquinado"] == Null){
                                                $classBtnColor = "btn-general";
                                                $iconStatus = '<i class="bi bi-flag me-1"></i><i class="bi bi-clock"></i>';
                                            }else{
                                                $classBtnColor = "btn-auth";
                                                $iconStatus = '<i class="bi bi-flag"></i><i class="bi bi-check2-all"></i>';
                                            }
                                            echo '<button type="button" class="'.$classBtnColor.' btn-tabla-maquinado-mermas" 
                                                    data-bs-toggle="modal" data-bs-target="#modalTablaMaquinadoMermas"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-rol = "'.$rol_usuario.'"
                                                    title="Ver registros de maquinado y merma">
                                                    '.$iconStatus.'
                                                </button>';
                                        }
                                        break;
                                    case "Completada":
                                        $estatusString = "Completada";
                                        if ($tipo_usuario === "CNC") {
                                            if($row["fecha_revision_maquinado"] == Null){
                                                $classBtnColor = "btn-general";
                                                $iconStatus = '<i class="bi bi-flag me-1"></i><i class="bi bi-clock"></i>';
                                            }else{
                                                $classBtnColor = "btn-auth";
                                                $iconStatus = '<i class="bi bi-flag"></i><i class="bi bi-check2-all"></i>';
                                            }
                                            echo '<button type="button" class="'.$classBtnColor.' btn-tabla-maquinado-mermas" 
                                                    data-bs-toggle="modal" data-bs-target="#modalTablaMaquinadoMermas"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-rol = "'.$rol_usuario.'"
                                                    title="Ver registros de maquinado y merma">
                                                    '.$iconStatus.'
                                                </button>';
                                        }else{
                                            $classBtnColor = "btn-general";
                                            $iconStatus = '<i class="bi bi-flag me-1"></i>';
                                            
                                            $classBtnColor = "btn-general";
                                            $titleButton = "Ver resultados de barras";
                                            $iconButton = "database";
                                            
                                            echo '<button class="'.$classBtnColor.' btn-claves-retorno" 
                                                    data-bs-toggle="modal" data-bs-target="#modalRetorno"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="'.$titleButton.'">
                                                    <i class="bi bi-'.$iconButton.'"></i>
                                                </button>';
                                        }
                                        break;
                                    case "Detenida":
                                        $estatusString = "Detenida";
                                        $estatusClass = "span-status-red";
                                        if ($tipo_usuario === "CNC" && $rol_usuario == $row['maquina']) {
                                            echo '<button type="button" class="btn-terracota btn-finalizar" 
                                                    data-bs-toggle="modal" data-bs-target="#modalFinalizar"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-estatus = "Detenida"
                                                    title="Registrar avances de maquinado">
                                                    <i class="bi bi-list-check"></i>
                                                </button>';
                                                if(!empty($row['fecha_retorno_barras'])){

                                                }else{

                                                }
                                        } elseif ($tipo_usuario === "Inventarios") {
                                            if(empty($row['fecha_retorno_barras'])){
                                                $classBtnColor = "btn-bar-entry";
                                                $titleButton = "Retornar barras al inventario con nuevo stock";
                                                $iconButton = "database-fill-down";
                                            }else{
                                                $classBtnColor = "btn-archive";
                                                $titleButton = "Ver resultados de barras";
                                                $iconButton = "database";
                                            }
                                            echo '<button class="'.$classBtnColor.' btn-claves-retorno" 
                                                    data-bs-toggle="modal" data-bs-target="#modalRetorno"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="'.$titleButton.'">
                                                    <i class="bi bi-'.$iconButton.'"></i>
                                                </button>';
                                        }
                                        break;

                                    default:
                                        // no mostrar nada
                                        break;
                                }
                                ?>
                            </div>
                        </td>

                        <!-- <td><?= htmlspecialchars($row['id_requisicion']??""); ?></td> -->
                        <td><?= htmlspecialchars($row['folio']??""); ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-1">
                                <span class="<?= htmlspecialchars($estatusClass ?? '') ?>"><?= htmlspecialchars($estatusString ?? '') ?></span>
                                <button class="btn btn-sm btn-outline-success btn-estatus" 
                                        data-id-requisicion="<?= htmlspecialchars($row['id_requisicion']??""); ?>" 
                                        title="Ver historial de estatus">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['maquina']." - ".$row['operador_cnc']??""); ?></td>
                        <td><?= htmlspecialchars($row['sucursal']??""); ?></td>
                        <td><?= htmlspecialchars($row['comentario']??""); ?></td>
                        <td><?= htmlspecialchars($row['nombre_vendedor']??""); ?></td>
                        <td><?= htmlspecialchars($row['cliente']??""); ?></td>
                        <!-- <td>
                            <?php
                                $cotizaciones = $row['cotizaciones'] ?? '';
                                $ids = explode(', ', $cotizaciones);
                                foreach ($ids as $id) {
                                    if (trim($id) !== '') {
                                        echo '<a href="../includes/functions/generar_pdf.php?id_cotizacion=' . htmlspecialchars($id) . '" target="_blank">' . htmlspecialchars($id) . '</a><br>';
                                    }
                                }
                            ?>
                        </td> -->

                        <td><?= htmlspecialchars($row['fechahora']??""); ?></td>
                        <td><?= htmlspecialchars($row['num_pedido']??""); ?></td>
                        <td><?= htmlspecialchars($row['paqueteria']??""); ?></td>
                        <td><?= htmlspecialchars($row['factura']??""); ?></td>
                    </tr>
                <?php
                    }
                ?>

                </tbody>
            </table>
        </div>
    </div>
</section>
<?php include(ROOT_PATH . 'includes/modal_comentarios_adjuntos.php'); ?>
<script src="<?= controlCache('../assets/js/modal_comentarios_adjuntos.js'); ?>"></script>
<!-- ///////////MODAL SELECCIONAR FILTROS DE BUSQUEDA////////////////// -->
<div class="modal fade" id="modalFiltrosBusqueda" tabindex="-1" aria-hidden="false" aria-labelledby="modalLabel" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalLabel">
                    <i class="bi bi-funnel"></i> Filtros de búsqueda de requisiciones
                </h4>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Mostrar filtros activos si existen -->
                <div id="filtrosActivosContainer" class="filtros-activos" style="display: none;">
                    <h6><i class="bi bi-funnel-fill"></i> Filtros activos:</h6>
                    <div id="filtrosActivosList"></div>
                </div>

                <form id="formFiltros" action="" method="GET">
                    <!-- Sección: Filtros por categoría -->
                    <div class="form-section mb-3">
                        <h5>Filtros por estatus y sucursal</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estatus" class="lbl-general">
                                    <i class="bi bi-list-ol"></i> Estatus de requisición
                                </label>
                                <select class="form-select" id="estatus" name="estatus">
                                    <option value="">Todos los estatus</option>
                                    <option value="autorizada" <?= ($preferencias['estatus'] == 'autorizada') ? 'selected' : '' ?>>Autorizada (asignación de máquina pendiente)</option>
                                    <option value="produccion" <?= ($preferencias['estatus'] == 'produccion') ? 'selected' : '' ?>>Producción (en procesos de maquinado)</option>
                                    <option value="finalizada" <?= ($preferencias['estatus'] == 'finalizada') ? 'selected' : '' ?>>Finalizada (maquinado finalizado)</option>
                                    <option value="completada" <?= ($preferencias['estatus'] == 'completada') ? 'selected' : '' ?>>Completada (barras retornadas)</option>
                                    <option value="detenida" <?= ($preferencias['estatus'] == 'detenida') ? 'selected' : '' ?>>Detenida (producción cancelada)</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="filtroSucursal" class="lbl-general">
                                    <i class="bi bi-houses"></i> Origen/Sucursal
                                </label>
                                <select id="filtroSucursal" class="selector" name="sucursal" >
                                    <option value="">Todos los origenes</option>
                                    <option value="Ventas Nacionales" <?= ($preferencias['sucursal'] == 'Ventas Nacionales') ? 'selected' : '' ?>>Ventas Nacionales</option>
                                    <option value="Ventas Internacionales" <?= ($preferencias['sucursal'] == 'Ventas Internacionales') ? 'selected' : '' ?>>Ventas Internacionales</option>
                                    <option value="Ventas Industriales" <?= ($preferencias['sucursal'] == 'Ventas Industriales') ? 'selected' : '' ?>>Ventas Industriales</option>
                                    <option value="Sucursal Industrias" <?= ($preferencias['sucursal'] == 'Sucursal Industrias') ? 'selected' : '' ?>>Sucursal Industrias</option>
                                    <option value="Sucursal Monterrey" <?= ($preferencias['sucursal'] == 'Sucursal Monterrey') ? 'selected' : '' ?>>Sucursal Monterrey</option>
                                    <option value="Sucursal Queretaro" <?= ($preferencias['sucursal'] == 'Sucursal Queretaro') ? 'selected' : '' ?>>Sucursal Queretaro</option>
                                    <option value="Sucursal Saltillo" <?= ($preferencias['sucursal'] == 'Sucursal Saltillo') ? 'selected' : '' ?>>Sucursal Saltillo</option>
                                    <option value="Sucursal Toluca" <?= ($preferencias['sucursal'] == 'Sucursal Toluca') ? 'selected' : '' ?>>Sucursal Toluca</option>
                                    <option value="Sucursal Veracruz" <?= ($preferencias['sucursal'] == 'Sucursal Veracruz') ? 'selected' : '' ?>>Sucursal Veracruz</option>
                                    <option value="Taller" <?= ($preferencias['sucursal'] == 'Taller') ? 'selected' : '' ?>>Taller</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Filtros por fecha -->
                    <div class="form-section mb-3">
                        <h5>Filtros por fecha</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inicio" class="lbl-general">
                                    <i class="bi bi-calendar-check"></i> Fecha desde
                                </label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= $preferencias['fecha_inicio'] ?>">
                                <small class="form-text text-muted">Fecha de inicio del rango</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin" class="lbl-general">
                                    <i class="bi bi-calendar-x"></i> Fecha hasta
                                </label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= $preferencias['fecha_fin'] ?>">
                                <small class="form-text text-muted">Fecha de fin del rango</small>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Opciones adicionales -->
                    <div class="form-section mb-3">
                        <h5>Opciones adicionales</h5>
                        <div class="row">
                            <div class="checkbox-container col-md-6 mb-3">
                                <label class="form-check-label">
                                    <i class="bi bi-table"></i> <strong>Default al cargar la tabla</strong>
                                </label>
                                <div class="form-check">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="default" id="radioDefault0" value="0" 
                                            <?= ($preferencias['default'] == '0') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="radioDefault0">
                                            Todas
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="default" id="radioDefault1" value="1" 
                                            <?= ($preferencias['default'] == '1') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="radioDefault1">
                                            Solo las de hoy
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="default" id="radioDefault2" value="2" 
                                            <?= ($preferencias['default'] == '2' || $preferencias['default'] === '') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="radioDefault2">
                                            Solo de esta semana
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="default" id="radioDefault3" value="3" 
                                            <?= ($preferencias['default'] == '3') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="radioDefault3">
                                            Solo de este mes
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!-- Orden de los registros según id_requisicion -->
                            <div class="col-md-6 mb-3">
                                <label for="orden" class="lbl-general">
                                    <i class="bi bi-arrow-down-up"></i> Orden
                                </label>
                                <select class="form-select" id="orden" name="orden">
                                    <option value="des" <?= ($preferencias['orden'] == 'des') ? 'selected' : '' ?>>Descendente (mas recientes primero)</option>
                                    <option value="asc" <?= ($preferencias['orden'] == 'asc') ? 'selected' : '' ?>>Ascendente</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Botones del formulario -->
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn-general">
                            <i class="bi bi-search"></i> Consultar
                        </button>
                        <button type="button" class="btn btn-outline-danger" id="btnLimpiarFormulario" onclick="limpiarTodosFiltros()">
                            <i class="bi bi-arrow-clockwise"></i> Limpiar filtros
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////MODAL EDITAR MEDIDAS DE COTIZACIONES/////////////////////// -->
<div class="modal fade" id="modalEditarMedidas" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex justify-content-between" style="width:90%;">
                    <h5 id="titleModal" class="modal-title" id="modalLabel">Editar medidas de las cotizaciones de la requisición</h5>
                </div>
                <button id="btnCloseModal" type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

            </div>
  
            <div class="modal-footer justify-content-end">
                <button id="btnGuardarMedidas" type="button" class="btn-general" tabindex="-1">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- //////////////////////////MODAL GUARDAR OPERADOR CNC /////////////////////// -->
<div class="modal fade" id="modalGuardarOperador" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 50% !important;margin-top:10%;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Asignación de máquina</span>
                <button type="button" id="btn-closeOperador" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between ">
                    <input type="hidden" id="inputIdRequisicionOperador" name="id_requisicion">
                    <div class="" style="width:48%;">
                        <label for="inputMaquina" class="lbl-general">Máquina CNC*</label>
                        <select id="inputMaquina" class="selector" required >
                            <option value="" selected disabled>Seleccione máquina</option>
                        </select>
                    </div>
                    
                    <div class="" style="width:48%;">
                        <label for="inputOperadorCNC" class="lbl-general">Nombre del operador CNC (opcional)</label>
                        <input id="inputOperadorCNC" type="text" class="input-text"  name="operador_cnc" >
                    </div>   
                    
                </div>
            </div>
            <div class="modal-footer justify-content-end">
                <button id="btnGuardarOperador" type="button" class="btn-general" tabindex="-1">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- ///////////////////////MODAL TABLA CONTROL ALMACEN INVENTARIO ENTREGAR BARRAS/////////////////////// -->
<div class="modal fade" id="modalTableControlAlmacenEntrega" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form d-flex gap-2 align-items-center"><span>Barras para entrega. Folio de requisición: </span>                    
                    <form action="../includes/functions/generar_requisicion.php" method="GET" target="_blank">
                        <input type="hidden" name="id_requisicion">
                        <button type="submit" class="btn btn-link p-0 border-0 text-decoration-underline fs-5"></button>
                    </form>
                </span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex col-12 gap-3 mb-2">
                    <div class="col-3">
                        <button id="addExtraBillet" type="button" class="btn-general">
                            <i class="bi bi-plus-circle"></i> Agregar barra extra
                        </button>
                    </div>
                </div>
                <div style="overflow-x: auto; width: 100%; max-height:300px !important; overflow-y:auto;">
                    <table id="tableEntregarBarras" class="table table-bordered border border-2 tabla-billets mb-3" style="table-layout: fixed; width: max-content;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">ACCIONES</th>
                                <th style="width: 130px;">PERFIL</th>
                                <th style="width: 160px;">MATERIAL</th>
                                <th style="width: 280px;">CLAVE</th>
                                <th style="width: 220px;">LOTE PEDIMENTO</th>
                                <th style="width: 130px;">MEDIDA</th>
                                <th style="width: 80px;">PZ TEÓRICAS</th>
                                <th style="width: 100px;">ALTURA DE PZ</th>
                                <th style="width: 100px;">MM TEÓRICOS</th>
                                <th style="width: 120px;">MM ENTREGA</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 justify-content-center gap-3">
                    <button id="btnGuardarProgresoEntregaBarras" type="button" class="btn-general">
                        <i class="bi bi-floppy"></i> Guardar progreso
                    </button>
                    <button id="btnEntregarBarras" type="button" class="btn-general">
                        <i class="bi bi-database-fill-check"></i> Entregar barras
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /////////////////////// MODAL: AGREGAR BARRA EXTRA /////////////////////// -->
<div class="modal fade" id="modalAddExtra" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-add-extra" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="label-modal-add-extra">Solicitar autorización de barra extra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAddExtraBillet">
                    <input type="hidden" id="idRequisicionExtra" name="id_requisicion">

                    <div class="mb-3">
                        <label for="lotePedimentoExtra" class="lbl-general">Lote pedimento *</label>
                        <input id="lotePedimentoExtra" name="lote_pedimento" type="text" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="perfilExtra" class="lbl-general">Perfil *</label>
                        <input id="perfilExtra" name="perfil" type="text" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-6">
                            <label for="pzTeoricasExtra" class="lbl-general">Piezas teóricas *</label>
                            <input id="pzTeoricasExtra" name="pz_teoricas" type="number" min="0" step="1" class="form-control" required>
                        </div>
                        <div class="mb-3 col-6">
                            <label for="alturaPzExtra" class="lbl-general">Altura de pieza *</label>
                            <input id="alturaPzExtra" name="altura_pz" type="number" min="0" step="0.01" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-6">
                            <label for="mmEntregaExtra" class="lbl-general">MM Entrega *</label>
                            <input id="mmEntregaExtra" name="mm_entrega" type="number" min="0" step="0.01" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="justificacionExtra" class="lbl-general">Justificación *</label>
                        <textarea id="justificacionExtra" name="justificacion" class="form-control" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="enviarAddBillet" class="btn-general">Agregar</button>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////MODAL SOLICITAR REMPLAZO DE BARRA A DIRECCION/////////////////////// -->
<div class="modal fade" id="modalSolicitarRemplazoBarra" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex justify-content-between" style="width:90%;">
                    <h5 class="modal-title">Solicitar autorización de remplazo de barra</h5>
                </div>
                <button id="btnCloseModal" type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formSolicitarRemplazo">                        
                    <input id="inputIdRequisicionRemplazo" type="hidden" >
                    <input id="inputIdControl" type="hidden">
                    <!-- <input id="inputLoteRemplazoA" type="hidden"> -->
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:100%;">
                            <label for="inputLoteRemplazoA" class="lbl-general">Barra a remplazar:</label>
                            <input id="inputLoteRemplazoA" type="text" class="input-disabled">
                        </div>  
                    </div>                     
                    <div class="d-flex justify-content-between ">
                        <div class="" style="width:100%;">
                            <label for="inputLoteRemplazoB" class="lbl-general">Nuevo lote pedimento *</label>
                            <input id="inputLoteRemplazoB" type="text" class="input-text" placeholder="Ingrese la barra de remplazo" required>
                        </div>  
                    </div> 
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:100%;">
                            <p id="pValidacionSolicitarRemplazo" class="d-none p-invalida"></p>
                        </div>  
                    </div>  
                    <div class="d-flex justify-content-between">
                        <div class="" style="width:100%;">
                            <label for="inputJustificacionRemplazo" class="lbl-general">Justificación de remplazo *</label>
                            <textarea id="inputJustificacionRemplazo" class="form-control" rows="3" placeholder="Ingrese la justificación del remplazo de barra..."></textarea>
                        </div>  
                    </div>                  

                    <div class="d-flex justify-content-between mt-3">
                        <button id="btnSolicitarRemplazoBarra" type="button" class="btn-general" tabindex="-1">Enviar</button>
                    </div> 
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ////////////////////////// MARCAR COMO BARRAS ENTREGADAS A CNC DE LA REQUISICION /////////////////////// -->
<div class="modal fade" id="modalDarSalida" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">¿Desea continuar?</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta acción notificará a Sellos Maquinados para comenzar el maquinado. Asegurese de entregar las barras correctas.</p>
                <div>
                    <input id="inputRequisicionDarSalida" type="hidden" name="id_requisicion" >
                    <button id="btnConfirmarDarSalidaBillets" type="button" class="btn-general">Continuar</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- //////////////////////////MODAL FINALIZAR REQUISICION /////////////////////// -->
<!-- <div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">¿Desea continuar?</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta acción cambiará el estatus de la requisicion a Finalizada.</p>
                <form action="" method="POST">
                    <input id="inputRequisicion" type="hidden" name="id_requisicion" >
                    <input type="hidden" name="action" value="finalizar">
                    <button type="submit" class="btn-general">Continuar</button>
                </form>
            </div>
        </div>
    </div>
</div> -->
<!-- ////////////////////////////// CNC DEBE LLENAR LOS CAMPOS DE CONTROL DE ALMACEN PARA FINALIZAR //////////////////////// -->
<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Para finalizar llene los campos solicitados</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="width:100%; margin-bottom:20px;">
                    <h5 class="modal-title">Barras de requisición con folio: <span></span></h5>
                    <div style="overflow-x: auto; width: 100%; max-height:300px !important; overflow-y:auto;">
                        <table class="table table-bordered border border-2 tabla-billets" style="table-layout: fixed; width: max-content;">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">Perfil</th>
                                    <th style="width: 200px;">Material</th>
                                    <th style="width: 250px;">Lote pedimento</th>
                                    <th style="width: 100px;">Medida</th>
                                    <th style="width: 120px;">MM Entrega</th>
                                    <th style="width: 100px;">Pz teóricas</th>
                                    <th style="width: 100px;">Pz maquinadas</th>
                                    <th style="width: 120px;">Altura Pz</th>
                                    <th style="width: 120px;">MM Usados</th>
                                    <th style="width: 120px;">LONG. TOTAL DE SELLOS</th>
                                    <th style="width: 120px;">MERMA POR CORTE</th>
                                    <th style="width: 120px;">SCRAP PZ</th>
                                    <th style="width: 120px;">SCRAP MM</th>
                                    <th style="width: 120px;">Total MM Usados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Aquí van tus registros -->
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 gap-3">
                    <div class="col-3">
                        <button id="saveChangesFinalizar" type="button" class="btn-general">
                            <i class="bi bi-floppy"></i> Guardar progreso
                        </button>
                    </div>
                    <small id="smallText" class="text-muted d-none mt-1">Las barras ya fueron retornadas, no es posible editar resultados de maquinado</small>

                    <button id="finalizarRequisicion" type="button" class="btn-general btn-success">
                        <i class="bi bi-check-circle"></i> Finalizar maquinado
                    </button>
             
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ////////////////////////////// VER REGISTROS DEL MAQUINADO RESULTANTE Y MERMAS PARA REVISION //////////////////////// -->
<div class="modal fade" id="modalTablaMaquinadoMermas" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Resultados de maquinado y mermas</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="width:100%; margin-bottom:20px;">
                    <h5 class="modal-title">Barras de requisición con folio: <span id="folioRequisicion"></span></h5>
                    <div style="overflow-x: auto; width: 100%; max-height:300px !important; overflow-y:auto;">
                        <table class="table table-bordered border border-2 tabla-billets" style="table-layout: fixed; width: max-content;">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">Barra mermada</th>
                                    <th style="width: 100px;">Perfil</th>
                                    <th style="width: 150px;">Material</th>
                                    <th style="width: 250px;">Lote pedimento</th>
                                    <th style="width: 100px;">Medida</th>
                                    <th style="width: 120px;">MM Entrega</th>
                                    <th style="width: 100px;">Pz teóricas</th>
                                    <th style="width: 100px;">Pz maquinadas</th>
                                    <th style="width: 120px;">Altura Pz</th>
                                    <th style="width: 120px;">MM Usados</th>
                                    <th style="width: 120px;">LONG. TOTAL DE SELLOS</th>
                                    <th style="width: 120px;">MERMA POR CORTE</th>
                                    <th style="width: 120px;">SCRAP PZ</th>
                                    <th style="width: 120px;">SCRAP MM</th>
                                    <th style="width: 120px;">Total MM Usados</th>
                                    <th style="width: 120px;">Merma Real</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyResultadosMaquinado">
                                <!-- Aquí van tus registros -->
                            </tbody>
                        </table>
                    </div>

                    <div id="badgeRevisionContainer">
                    </div>
                    <div id="infoRevisionContainer">
                    </div>
                    <!-- Sección de observaciones (solo para Gerente) -->
                    <div id="seccionObservaciones" class="mt-4 d-none">
                        <hr>
                        <h6>Observaciones de la revisión</h6>
                        <textarea id="observacionesGerente" class="form-control" rows="3" placeholder="Ingrese observaciones generales sobre los resultados del maquinado..."></textarea>
                        <input id="inputIdRequisicionResultadosMaquinado" type="hidden">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="terminarRevision" type="button" class="btn-general d-none">Terminar revisión</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// -->
<!-- //////////////////////// INVENTARIOS DEBE COMPLETAR MM RETORNO DE CONTROL DE ALMACEN PARA COMPLETAR //////////////////////// -->
<div class="modal fade" id="modalRetorno" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span id="spanTitleModalRetorno" class="title-form"></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="width:100%; margin-bottom:20px;">
                    <h5 class="modal-title">Claves de requisición con folio: <span></span></h5>
                    <div style="overflow-x: auto; width: 100%;">
                        <table class="table table-bordered border border-2 tabla-billets" style="table-layout: fixed; width: max-content;">
                            <thead>
                                <tr>
                                    <!-- <th style="width: 100px;">Cantidad</th> -->
                                     <th style="width: 150px;">Material</th>
                                    <th style="width: 260px;">Clave</th>
                                    <th style="width: 220px;">Lote pedimento</th>
                                    <th style="width: 120px;">Medida</th>
                                    <th style="width: 120px;">MM Entrega</th>
                                    <th style="width: 120px;">Total MM Usados</th>
                                    <th style="width: 120px;">MM Retorno (nuevo stock)</th>
                                    <th style="width: 120px;">LONG. TOTAL DE SELLOS</th>
                                    <th style="width: 120px;">MERMA POR CORTE</th>
                                    <th style="width: 120px;">SCRAP PZ</th>
                                    <th style="width: 120px;">SCRAP MM</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Aquí van tus registros -->
                            </tbody>
                        </table>


                    </div>
                    <!-- Sección de observaciones de inventarios -->
                    <div class="mt-4">
                        <hr>
                        <h6>Observaciones (opcional)</h6>
                        <textarea id="observacionesInventario" class="form-control" rows="3" placeholder="Ingrese observaciones generales..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="retornoFinalizado" type="button" class="btn-general">Listo</button>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
<!-- ////////////////////////// DETENER LA PRODUCCION DE LA REQUISICION /////////////////////// -->
<div class="modal fade" id="modalDetener" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">¿Desea continuar?</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta acción cambiará el estatus de la requisición a producción detenida, no sera posible finalizar el maquinado pero si registrar avances y retornar barras.</p>
                <!-- Sección de justificar la cancelacion del maquinado -->
                <div class="my-3">
                    <hr>
                    <div class="">
                        <label for="inputRazonDetener" class="lbl-general">Razón *</label>
                        <select id="inputRazonDetener" class="selector" required >
                            <option value="" selected disabled>Seleccione una opción</option>
                            <option value="cliente_cancelo">Cliente canceló maquinado</option>
                            <option value="error_vendedor">Error humano de vendedor</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <h6>Justificación *</h6>
                    <textarea id="justificacionDetener" class="form-control" rows="3" placeholder="Ingrese justificación de cancelacion..." required></textarea>
                </div>
                <div>
                    <input id="inputRequisicionDetener" type="hidden" name="id_requisicion" >
                    <button id="btnConfirmarDetener" type="button" class="btn-general">Continuar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
<?php include("../includes/modal_estatus_requisicion.php"); ?>
<script>
// JavaScript para mostrar filtros activos
function mostrarFiltrosActivos() {
    const filtros = [];
    const container = document.getElementById('filtrosActivosContainer');
    const list = document.getElementById('filtrosActivosList');
    
    // Estatus
    const estatusSelect = document.getElementById('estatus');
    if (estatusSelect.value) {
        const text = estatusSelect.options[estatusSelect.selectedIndex].text;
        filtros.push(`<span class="filtro-tag">Estatus: ${text}</span>`);
    }

    // Sucursal
    const filtroSucursal = document.getElementById('filtroSucursal');
    if (filtroSucursal.value) {
        const text = filtroSucursal.options[filtroSucursal.selectedIndex].text;
        filtros.push(`<span class="filtro-tag">Origen/Sucursal: ${text}</span>`);
    }

    // Fechas
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    if (fechaInicio && fechaFin) {
        filtros.push(`<span class="filtro-tag">Fecha: ${fechaInicio} a ${fechaFin}</span>`);
    } else if (fechaInicio) {
        filtros.push(`<span class="filtro-tag">Desde: ${fechaInicio}</span>`);
    } else if (fechaFin) {
        filtros.push(`<span class="filtro-tag">Hasta: ${fechaFin}</span>`);
    }
    
    // Default
    const defaultRadios = document.querySelectorAll('input[name="default"]:checked');
    if (defaultRadios.length > 0 && defaultRadios[0].value !== '1') {
        const labels = {
            '0': 'Todas',
            '2': 'Esta semana',
            '3': 'Este mes'
        };
        if (labels[defaultRadios[0].value]) {
            filtros.push(`<span class="filtro-tag">${labels[defaultRadios[0].value]}</span>`);
        }
    }
    
    // Orden
    const ordenSelect = document.getElementById('orden');
    if (ordenSelect.value === 'asc') {
        filtros.push(`<span class="filtro-tag">Orden: Ascendente</span>`);
    }
    
    // Mostrar u ocultar contenedor
    if (filtros.length > 0) {
        list.innerHTML = filtros.join('');
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

// Función para limpiar filtros
function limpiarTodosFiltros() {
    document.getElementById('formFiltros').reset();
    document.getElementById('estatus').value = '';
    document.getElementById('filtroSucursal').value = '';
    document.getElementById('fecha_inicio').value = '';
    document.getElementById('fecha_fin').value = '';
    document.querySelector('input[name="default"][value="2"]').checked = true;
    document.getElementById('orden').value = 'des';
    
    // Actualizar vista de filtros activos
    mostrarFiltrosActivos();
}

// Mostrar filtros activos al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    // 1. Manejo del Overlay (jQuery)
    // if (typeof $ !== 'undefined') {
    //     $("#overlay").addClass("d-none");
    // }
    mostrarFiltrosActivos();
    
    // Actualizar filtros activos cuando cambien los campos
    document.getElementById('estatus').addEventListener('change', mostrarFiltrosActivos);
    document.getElementById('filtroSucursal').addEventListener('change', mostrarFiltrosActivos);
    document.getElementById('fecha_inicio').addEventListener('change', mostrarFiltrosActivos);
    document.getElementById('fecha_fin').addEventListener('change', mostrarFiltrosActivos);
    document.querySelectorAll('input[name="default"]').forEach(radio => {
        radio.addEventListener('change', mostrarFiltrosActivos);
    });
    document.getElementById('orden').addEventListener('change', mostrarFiltrosActivos);
});
</script>
</body>
</html>
