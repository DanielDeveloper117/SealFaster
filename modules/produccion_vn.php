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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/produccion_vn.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/middleware_deteccion_cambios.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css'); ?>"> 

    <?php 
          include(ROOT_PATH . 'includes/backend_info_user.php');
          include(ROOT_PATH . 'includes/backend/produccion_vn.php'); 
          ?>


    <title>Requisiciones</title>
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
        <span>Cargando datos, por favor, espere...</span>    
    </div>
</div>
<section class="section-table flex-column mt-2 mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-1 mb-3">
            <h1>Requisiciones para Maquinado de Sellos</h1>
            <div class="d-flex flex-row justify-content-between col-12 col-md-3 gap-5 mt-5">
                <button type="button" id="btnAgregar" class="btn-general d-flex justify-content-center align-items-center gap-2" 
                    data-bs-toggle="modal" data-bs-target="#modalAgregarEditar">
                    <i class="bi bi-file-plus" style="font-size:24px;"></i>
                    Nueva requisición
                </button>
            </div>
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
                        <th>Sucursal</th>
                        <th>Cliente</th>
                        <th>Cotizaciones</th>
                        <th>Fecha</th>
                        <th>Num. pedido</th>
                        <th>Paqueteria</th>
                        <th>Factura/remision/nota</th>
                        <th>Comentario</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    foreach ($arregloSelectRequisiciones as $row) {
                ?>
                    <tr data-id-requisicion="<?= htmlspecialchars($row['id_requisicion'] ?? ''); ?>" data-estatus="<?= htmlspecialchars($row['estatus'] ?? ''); ?>">
                        <td class="td-first-actions">
                            <div class="d-flex gap-2 container-actions">
                                <form action="../includes/functions/generar_requisicion.php" method="GET" target="_blank">
                                    <input id="hiddenIdRequisicionPDF" type="hidden" name="id_requisicion" value="<?= htmlspecialchars($row['id_requisicion']??""); ?>">
                                    <button type="submit" class="btn-pdf"
                                        title="Generar PDF de esta requisición">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </button>
                                </form>

                                <?php
                                $esMia = "0";
                                $estatusString = "";
                                $estatusClass = "span-status";
                                if ($row['estatus'] === "Pendiente" && $row['id_vendedor'] == $_SESSION['id']) {
                                    $esMia = "1";
                                    // Definimos el icono y el titulo antes para limpiar el echo

                                    echo '
                                        <button class="btn-thunder edit-btn"
                                            data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                            title="Editar requisición">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <button type="button" class="btn-archive btn-archivar-requisicion" 
                                            data-bs-toggle="modal" data-bs-target="#modalArchivar"
                                            data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                            title="Archivar/desactivar este folio">
                                            <i class="bi bi-archive"></i>
                                        </button>
                                    ';
                                        
                                }else{
                                    // echo '<button class="btn-disabled2"
                                    //         title="No se puede editar esta requisición">
                                    //         <i class="bi bi-pencil-square"></i>
                                    //     </button>';                                    
                                }

                                
                                echo '<div class="comentarios-wrapper">';
                                    echo '  <button type="button" class="btn-general btn-modal-comentarios-adjuntos"
                                                data-origen="requi"
                                                data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                data-es-mia="' . $esMia . '"
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
                                    case "Pendiente":
                                        $estatusString = "Pendiente";
                                        $estatusClass = "span-status-yellow";
                                        if ($tipo_usuario === "Vendedor" && $rol_usuario === "Gerente") {
                                            echo '<button type="button" class="btn-auth btn-gerente-autoriza" 
                                                    data-bs-toggle="modal" data-bs-target="#modalGerenteAutoriza"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-autoriza="g"
                                                    title="Autorizar maquinado de sellos">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>';
                                        } elseif ($tipo_usuario === "Administrador") {
                                            echo '<button type="button" class="btn-auth btn-admin-autoriza" 
                                                    data-bs-toggle="modal" data-bs-target="#modalAdminAutoriza"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-autoriza="a"
                                                    title="Autorizar maquinado de sellos">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>';
                                        } else {
                                        }
                                        break;
                                    case "Autorizada":
                                        $estatusString = "Autorizada";
                                        if ($tipo_usuario === "Administrador" || $rol_usuario === "Gerente") {
                                            echo '<button type="button" class="btn-cancel btn-cancelar" 
                                                    data-bs-toggle="modal" data-bs-target="#modalCancelar"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Cancelar maquinado de sellos">
                                                    <i class="bi bi-ban"></i>
                                                </button>';
                                            if($row['barra_pendiente']==1){
                                                echo '<button class="btn-amber btn-barras-pendientes" 
                                                        data-bs-toggle="modal" data-bs-target="#modalTableBarrasPendientes"
                                                        data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                        title="Barras pendientes de autorizar">
                                                        <i class="bi bi-clock"></i>
                                                    </button>';
                                            }
                                        }
                                        break;
                                    case "Producción":
                                        $estatusString = "Producción";
                                        if ($tipo_usuario === "Administrador" || $rol_usuario === "Gerente") {
                                            // echo '<button type="button" class="btn-disabled2" 
                                            //         title="No se puede cancelar una requisición en producción">
                                            //         <i class="bi bi-ban"></i>
                                            //     </button>';
                                            if($row['barra_pendiente']==1){
                                                echo '<button class="btn-amber btn-barras-pendientes" 
                                                        data-bs-toggle="modal" data-bs-target="#modalTableBarrasPendientes"
                                                        data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                        title="Barras pendientes de autorizar">
                                                        <i class="bi bi-clock"></i>
                                                    </button>';
                                            }
                                        }else{

                                        }
                                        break;

                                    case "En producción":
                                        $estatusString = "En maquinado";
                                        if ($tipo_usuario === "Administrador" || $rol_usuario === "Gerente") {
                                            // echo '<button type="button" class="btn-disabled2" 
                                            //         title="No se puede cancelar una requisición en producción">
                                            //         <i class="bi bi-ban"></i>
                                            //     </button>';
                                            if($row['barra_pendiente']==1){
                                                echo '<button class="btn-amber btn-barras-pendientes" 
                                                        data-bs-toggle="modal" data-bs-target="#modalTableBarrasPendientes"
                                                        data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                        title="Barras pendientes de autorizar">
                                                        <i class="bi bi-clock"></i>
                                                    </button>';
                                            }
                                        }else{

                                        }
                                        break;
                                    case "Finalizada":
                                        $estatusString = "Finalizada";
                                        if ($tipo_usuario === "Administrador" || $rol_usuario === "Gerente") {
                                            // echo '<button type="button" class="btn-disabled2" 
                                            //         title="No se puede cancelar una requisición en producción">
                                            //         <i class="bi bi-ban"></i>
                                            //     </button>';
                                        }else{

                                        }
                                        break;
                                    case "Completada":
                                        $estatusString = "Completada";

                                        break;
                                    case "Detenida":
                                        $estatusString = "Detenida";
                                        $estatusClass = "span-status-red";
                                        if ($tipo_usuario === "CNC" && $rol_usuario == $row['maquina']) {
                                            echo '<button type="button" class="btn-terracota btn-finalizar" 
                                                    data-bs-toggle="modal" data-bs-target="#modalFinalizar"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Finalizar maquinado">
                                                    <i class="bi bi-flag"></i>
                                                </button>';
                                        } elseif ($tipo_usuario === "Inventarios") {
                                        }
                                        break;
                                    case "Archivada":
                                        $estatusString = "Archivada";
                                        $estatusClass = "span-status-gray";

                                        break;
                                    default:
                                        // Nada que mostrar
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
                        <td><?= htmlspecialchars($row['sucursal']??""); ?></td>
                        <td><?= htmlspecialchars($row['cliente']??""); ?></td>
                        <td>
                            <?php
                                $cotizaciones = $row['cotizaciones'] ?? '';
                                $ids = explode(', ', $cotizaciones);
                                foreach ($ids as $id) {
                                    if (trim($id) !== '') {
                                        echo '<a href="../includes/functions/generar_pdf.php?id_cotizacion=' . htmlspecialchars($id) . '" target="_blank">' . htmlspecialchars($id) . '</a><br>';
                                    }
                                }
                            ?>
                        </td>

                        <td><?= htmlspecialchars($row['fecha_insercion']??""); ?></td>
                        <td><?= htmlspecialchars($row['num_pedido']??""); ?></td>
                        <td><?= htmlspecialchars($row['paqueteria']??""); ?></td>
                        <td><?= htmlspecialchars($row['factura']??""); ?></td>
                        <td><?= htmlspecialchars($row['comentario']??""); ?></td>
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
                        <h5>Filtros por estatus</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estatus" class="lbl-general">
                                    <i class="bi bi-list-ol"></i> Estatus de requisición
                                </label>
                                <select class="form-select" id="estatus" name="estatus">
                                    <option value="">Todos los estatus</option>
                                    <option value="pendiente" <?= ($preferencias['estatus'] == 'pendiente') ? 'selected' : '' ?>>Pendiente de autorizar</option>
                                    <option value="autorizada" <?= ($preferencias['estatus'] == 'autorizada') ? 'selected' : '' ?>>Autorizada</option>
                                    <option value="produccion" <?= ($preferencias['estatus'] == 'produccion') ? 'selected' : '' ?>>En producción</option>
                                    <option value="finalizada" <?= ($preferencias['estatus'] == 'finalizada') ? 'selected' : '' ?>>Finalizada</option>
                                    <option value="detenida" <?= ($preferencias['estatus'] == 'detenida') ? 'selected' : '' ?>>Detenida (producción cancelada)</option>
                                    <option value="archivada" <?= ($preferencias['estatus'] == 'archivada') ? 'selected' : '' ?>>Archivada</option>
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
                                        <label class="form-check-label" for="
                                        ">
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
                                            <?= ($preferencias['default'] == '2') ? 'checked' : '' ?>>
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
<!-- //////////////////////////////////////////////////////////////////////// -->
<!-- Modal para agregar/editar registro -->
<div class="modal fade" id="modalAgregarEditar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="titleModalAddEdit" class="modal-title" id="modalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCrearRequisicion" class="form-post" action="" method="POST">                        
                    <input type="hidden" id="inputAction" name="action">
                    <input type="hidden" id="inputIdRequisicion" name="id_requisicion" >
                    <input type="hidden" id="inputId" value="<?= $_SESSION['id'] ?>" name="id_vendedor" >
                    <input type="hidden" id="estatusPendiente" value="Pendiente" name="estatus">
                    <input id="inputCotizaciones" type="hidden" name="cotizaciones">
                    <input id="inputVendedor" type="hidden" name="nombre_vendedor" value="<?= $nombreUser ?>" readonly tabindex="-1">

                    <div class="d-flex justify-content-between ">
                        <div style="width:48%;">
                            <label for="inputSucursal" class="lbl-general">Sucursal/origen *</label>
                            <select id="inputSucursal" class="selector" name="sucursal" required >
                                <option value="" selected disabled>Seleccionar</option>
                                <option value="Ventas Nacionales">Ventas Nacionales</option>
                                <option value="Ventas Internacionales">Ventas Internacionales</option>
                                <option value="Ventas Industriales">Ventas Industriales</option>
                                <option value="Sucursal Industrias">Sucursal Industrias</option>
                                <option value="Sucursal Monterrey">Sucursal Monterrey</option>
                                <option value="Sucursal Queretaro">Sucursal Queretaro</option>
                                <option value="Sucursal Saltillo">Sucursal Saltillo</option>
                                <option value="Sucursal Toluca">Sucursal Toluca</option>
                                <option value="Sucursal Veracruz">Sucursal Veracruz</option>
                                <option value="Taller">Taller</option>
                            </select>
                        </div>
                        <div style="width:48%;">
                            <label for="inputCliente" class="lbl-general">Cliente *</label>
                            <input id="inputCliente" type="text" class="input-text" name="cliente" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between ">
                        <div style="width:48%;">
                            <label for="inputPedido" class="lbl-general">Num. Pedido *</label>
                            <input id="inputPedido" type="text" class="input-text" name="num_pedido" required>
                        </div>
                        <div style="width:48%;">
                            <label for="inputPaqueteria" class="lbl-general">Paqueteria *</label>
                            <select id="inputPaqueteria" class="selector" name="paqueteria" required >
                                <option value="" selected disabled>Seleccionar</option>
                                <option value="INBOX">INBOX</option>
                                <option value="PAQUETE EXPRESS">PAQUETE EXPRESS</option>
                                <option value="PRIMERA PLUS">PRIMERA PLUS</option>
                                <option value="DHL">DHL</option>
                                <option value="ESTRELLA BLANCA">ESTRELLA BLANCA</option>
                                <option value="VENCEDOR">VENCEDOR</option>
                                <option value="TRES GUERRAS">TRES GUERRAS</option>
                                <option value="FEDEX">FEDEX</option>
                                <option value="ODM">ODM</option>
                                <option value="ESTAFETA">ESTAFETA</option>
                                <option value="CASTORES">CASTORES</option>
                                <option value="FUTURA">FUTURA</option>
                                <option value="JR">JR</option>
                                <option value="POTOSINOS">POTOSINOS</option>
                            </select>
                        </div>                        
                    </div>
                    <div class="d-flex justify-content-between ">
                        <div style="width:48%;">
                            <label for="inputFactura" class="lbl-general text-break">Factura/remision/nota <?php if($tipo_usuario == "Vendedor" && $areaUser != "Ventas Nacionales"){ echo "*"; } ?></label>
                            <input id="inputFactura" type="text" class="input-text" name="factura" <?php if($tipo_usuario == "Vendedor" && $areaUser != "Ventas Nacionales"){ echo "required"; } ?>>
                        </div>
                        <div style="width:48%;">
                            <label for="inputComentario" class="lbl-general">Comentario (opcional)</label>
                            <input id="inputComentario" type="text" maxlength="50" class="input-text" name="comentario" placeholder="Solo comentarios generales...">
                            <small id="contadorComentario" style="display:block; text-align:right; font-size:12px; color:#555;">0 / 50 caracteres</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <div style="width:100%;">
                            <div id="alertaAdjunto" class="mt-2 p-2" style="display:none; background-color: #fff3cd; border: 1px solid #ffe69c; border-radius: 5px; font-size: 16px; color: #856404;">
                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                <strong>¿Vas a adjuntar algo?</strong> Parece que mencionas adjuntar algun archivo, recuerda subirlo después de crear la requisición haciendo clic en el icono <i class="bi bi-chat-left-text"></i>.
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:100%;">
                            <label for="buscadorCotizaciones" class="lbl-general">Agregar cotizaciones *</label>
                            <select id="buscadorCotizaciones">
                                <option value="" selected disabled>Seleccione una cotización</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between ">
                        <div class="mb-3" style="width:100%;overflow-x:auto;">
                            <table id="miniTableCotizaciones" class="table table-bordered border border-2 tabla-billets">
                                <thead>
                                    <tr>
                                        <th scope="col">Remover</th>
                                        <th scope="col">Id cotizacion</th>
                                        <th scope="col">Perfil</th>
                                        <!-- <th scope="col">Tipo medida</th> -->
                                        <th scope="col">Medidas</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <button id="btnGuardar" type="submit" class="btn-general">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- //////////////////////////MODAL GERENTE DEBE AUTORIZAR /////////////////////// -->
<div class="modal fade" id="modalGerenteAutoriza" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Siga las instrucciónes para autorizar</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Escanea el código QR con tu dispositivo movil o haz click en el enlace, luego en el recuadro dibuja tu firma para autorizar. Caducará en 5 minutos.</p>
                <div class="d-flex flex-column flex-md-row justify-content-evenly justify-content-md-center align-items-center">
                    <!-- CONTENEDOR QR -->
                    <div id="containerQRLink" 
                        class="d-flex flex-column align-items-center text-center p-2"
                        >
                        
                        <div id="ContainerQR" 
                            class="d-none d-md-flex justify-content-center align-items-center"
                            >
                        </div>

                        <div id="qrLinkContainer" 
                            class="d-flex d-md-flex justify-content-center text-break mb-md-3"
                            style="word-break: break-all;">
                        </div>
                    </div>
                    <?php 
                        $id_usuario = $_SESSION['id'];
                        $nombreArchivo = $id_usuario . ".png";
                        $carpeta = '../files/signatures/';
                        $rutaCompleta = $carpeta . $nombreArchivo;
                        if(file_exists($rutaCompleta)){
                            echo '
                                <div class="d-flex flex-column justify-content-center">
                                    <h5 class="text-center text-md-start">¿Autorizar con firma predeterminada?</h5>
                                    <img src="'.$rutaCompleta.'?v='.time().'" width="150" height="100" class="align-self-center mb-3">
                                    <button type="button" class="btnFirmaPredeterminada btn-auth d-none" 
                                    data-id-requisicion="" data-autoriza="">Aceptar</button>
                                </div>                            
                            ';
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
<!-- //////////////////////////MODAL ADMIN DEBE AUTORIZAR /////////////////////// -->
<div class="modal fade" id="modalAdminAutoriza" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Siga las instrucciónes para autorizar maquinado</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Escanea el código QR con tu dispositivo movil o haz click en el enlace, luego en el recuadro dibuja tu firma para autorizar. Caducará en 5 minutos.</p>
                <div class="d-flex flex-column flex-md-row justify-content-evenly justify-content-md-center align-items-center">
                    <!-- CONTENEDOR QR -->
                    <div id="containerQRLink" 
                        class="d-flex flex-column align-items-center text-center p-2"
                        >
                        
                        <div id="ContainerQR2" 
                            class="d-none d-md-flex justify-content-center align-items-center"
                            >
                        </div>

                        <div id="qrLinkContainer2" 
                            class="d-flex d-md-flex justify-content-center text-break mb-md-3"
                            style="word-break: break-all;">
                        </div>
                    </div>
                    <?php 
                        $id_usuario = $_SESSION['id'];
                        $nombreArchivo = $id_usuario . ".png";
                        $carpeta = '../files/signatures/';
                        $rutaCompleta = $carpeta . $nombreArchivo;
                        if(file_exists($rutaCompleta)){
                            echo '
                                <div class="d-flex flex-column justify-content-center">
                                    <h5 class="text-center text-md-start">¿Autorizar con firma predeterminada?</h5>
                                    <img src="'.$rutaCompleta.'?v='.time().'" width="150" height="100" class="align-self-center mb-3">
                                    <button type="button" class="btnFirmaPredeterminada btn-auth d-none" 
                                    data-id-requisicion="" data-autoriza="">Aceptar</button>
                                </div>                            
                            ';
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
<!-- //////////////////////////MODAL: ENVIAR ESTAS SEGURO DE CANCELAR? /////////////////////// -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">¿Desea continuar?</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta acción cancelará la requisición con Folio: <strong></strong></p>
                <form action="" method="POST">
                    <input id="inputRequisicionCancelar" type="hidden" name="id_requisicion">
                    <button id="btnContinuarCancelar" type="button" class="btn-cancel">Continuar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- ///////////////////////MODAL TABLA DE BARRAS PENDIENTES POR AUTORIZAR /////////////////////// -->
<div class="modal fade" id="modalTableBarrasPendientes" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form d-flex gap-2 align-items-center"><span>Barras pendientes por autorizar. Folio de requisición: </span>                    
                    <form action="../includes/functions/generar_requisicion.php" method="GET" target="_blank">
                        <input id="hiddenIdRequisicionBarrasPendientes" type="hidden" name="id_requisicion">
                        <button type="submit" class="btn btn-link p-0 border-0 text-decoration-underline fs-5"></button>
                    </form>
                </span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="overflow-x: auto; width: 100%; max-height:300px !important; overflow-y:auto;">
                    <table id="tableBarrasPendientes" class="table table-bordered border border-2 tabla-billets mb-3" style="table-layout: fixed; width: max-content;">
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
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>
<!-- ///////////////////////MODAL CONFIRMAR AUTORIZAR BARRA /////////////////////// -->
<div class="modal fade" id="modalAutorizarBarra" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-autorizar-barra" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="label-modal-autorizar-barra">Confirmar autorización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p></p>
                <form id="formAutorizarBarra">
                    <input type="hidden" name="id_requisicion" id="autorizarIdRequisicion" value="">
                    <input type="hidden" name="id_control" id="autorizarIdControl" value="">
                    <input type="hidden" name="accion" id="autorizarAccion" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnConfirmAutorizarBarra" class="btn-auth">Si, continuar</button>
                <button type="button" id="btnCancelAutorizarBarra" class="btn-cancel" data-bs-dismiss="modal">No, cancelar</button>
            </div>
        </div>
    </div>
</div>
<!-- ///////////////////////MODAL CONFIRMAR RECHAZAR BARRA /////////////////////// -->
<div class="modal fade" id="modalRechazarBarra" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-rechazar-barra" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="label-modal-rechazar-barra">Rechazar barra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formRechazarBarra">
                    <input type="hidden" id="idRequisicionRechazo" name="id_requisicion" value="">
                    <input type="hidden" id="inputControlRechazo" name="id_control" value="">
                    <input type="hidden" id="inputAccionRechazo" name="accion" value="">

                    <div class="mb-3">
                        <label for="inputRazonRechazo" class="form-label">Razón del rechazo</label>
                        <textarea id="inputRazonRechazo" name="razon" class="form-control" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnEnviarRechazo" class="btn-general">Enviar</button>
            </div>
        </div>
    </div>
</div>
<!-- ////////////////////////// ARCHIVAR LA REQUISICION, ES IRREVERSIBLE /////////////////////// -->
<div class="modal fade" id="modalArchivar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">¿Desea continuar?</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta acción cambiará el estatus de la requisición a "Archivada", esta acción es irreversible y el folio solo se mostrará filtrando por requisiciones archivadas.</p>
                <div class="my-3">
                    <hr>
                    <h6>Justificación *</h6>
                    <textarea id="justificacionArchivar" class="form-control" rows="3" placeholder="Ingrese justificación para archivar..." required></textarea>
                </div>
                <div>
                    <input id="inputRequisicionArchivar" type="hidden" name="id_requisicion" >
                    <button id="btnConfirmarArchivar" type="button" class="btn-general">Continuar</button>
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
        
        // 2. Lógica de Filtros (Solo si existen en el DOM)
        const estatusElem = document.getElementById('estatus');
        if (estatusElem) {
            mostrarFiltrosActivos();
            // Agregar listeners solo si los elementos existen
            estatusElem.addEventListener('change', mostrarFiltrosActivos);
            document.getElementById('fecha_inicio')?.addEventListener('change', mostrarFiltrosActivos);
            document.getElementById('fecha_fin')?.addEventListener('change', mostrarFiltrosActivos);
            document.querySelectorAll('input[name="default"]').forEach(radio => {
                radio.addEventListener('change', mostrarFiltrosActivos);
            });
            document.getElementById('orden')?.addEventListener('change', mostrarFiltrosActivos);
        }

        // 3. Lógica del Contador y Alerta (Aseguramos que existan)
        const inputComentario = document.getElementById('inputComentario');
        const contador = document.getElementById('contadorComentario');
        const alertaAdjunto = document.getElementById('alertaAdjunto');

        if (inputComentario && contador) {
            const keywords = [/adjunto/i, /imagen/i, /dibujo/i, /plano/i, /foto/i, /archivo/i];

            inputComentario.addEventListener('input', function() {
                const valor = this.value;
                contador.textContent = `${valor.length} / 50 caracteres`;

                const tieneKeyword = keywords.some(regex => regex.test(valor));
                if (tieneKeyword) {
                    //alertaAdjunto.style.display = tieneKeyword ? 'block' : 'none';
                    alertaAdjunto.style.display = 'block';
                    setTimeout(() => {
                        $("#inputComentario").val("");
                        $("#inputComentario").attr("placeholder","Archivos adjuntos van por cotización individual..");
                    }, 500);
                }
            });
        }

        // 4. SweetAlert (Solo si no se ha aceptado antes)
        if (localStorage.getItem("recomendacionComentarios") != "1") {
            Swal.fire({
                title: 'Recomendación para maquinado óptimo',
                html: 'Recuerda que si tienes comentarios o archivos/imágenes útiles para los operadores CNC, <strong>NO OLVIDES AGREGARLOS</strong> (disponible si el folio aún esta pendiente de autorizar). ' +
                    'Haz clic en el botón "<i class="bi bi-chat-left-text" style="color: #55AD9B; font-weight: bold;"></i>" en las acciones del folio.',
                icon: 'info',
                confirmButtonText: 'Entendido',
                width: '600px',
                position: 'bottom-end',
                toast: true,
                showConfirmButton: true,
                input: 'checkbox',
                inputPlaceholder: 'No mostrar nuevamente',
                inputAttributes: { id: 'recomendacionComentarios' }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    localStorage.setItem("recomendacionComentarios", "1");
                }
            });
        }
    });
</script>
</body>
</html>
