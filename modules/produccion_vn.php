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
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/produccion_vn.js'); ?>"></script>
    <!-- <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-table.css'); ?>"> -->
     <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css"'); ?>"> 
    <link rel="stylesheet" href="<?= controlCache('../assets/css/modal-status.css'); ?>">

    <?php include(ROOT_PATH . 'includes/backend/produccion_vn.php'); 
          include(ROOT_PATH . 'includes/backend_info_user.php');
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
            <!-- <div class="d-flex flex-column mb-2 justify-content-start w-100" style="">
                <div>
                    <label for="selectorEstatus">Filtro estatus:</label>
                    <select id="selectorEstatus" class="input-selector mt-2">
                        <option disabled selected>Seleccionar</option>
                        <?php 
                            if($tipo_usuario=="Vendedor" && $rol_usuario=="Gerente"){
                                echo '<option value="Autorizar1">Autorizacion de gerencia pendiente</option>';
                                echo '<option value="Autorizada1">Autorizacion de dirección pendiente</option>';
                                echo '<option value="Produccion">Maquinado CNC</option>';
                            }elseif($tipo_usuario=="Administrador"){
                                echo '<option value="Autorizar2">Autorizacion de dirección pendiente</option>';
                                echo '<option value="Produccion">Maquinado CNC</option>';
                            }else{
                                echo '<option value="Pendiente">Autorizacion de gerencia pendiente</option>';
                                echo '<option value="Autorizada1">Autorizacion de dirección pendiente</option>';
                                echo '<option value="Produccion">Maquinado CNC</option>';
                            }
                        ?>
                        <option value="Finalizada">Finalizada</option>
                        <option value="Todo">Todo</option>
                    </select>
    
                </div>
            </div>  -->
            <table id="productionTable" class="table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th></th>
                        <!-- <th>Id</th> -->
                        <th>Folio</th>
                        <th>Estatus</th>
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
                    <tr>
                        <td class="td-first-actions">
                            <div class="d-flex gap-2 container-actions">
                                <form action="../includes/functions/generar_requisicion.php" method="GET" target="_blank">
                                    <input type="hidden" name="id_requisicion" value="<?= htmlspecialchars($row['id_requisicion']??""); ?>">
                                    <button type="submit" class="btn-pdf"
                                        title="Generar PDF de esta requisición">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </button>
                                </form>

                                <?php
                                if ($row['estatus'] === "Pendiente" && $row['id_vendedor'] == $_SESSION['id']) {
                                    echo '<button class="btn-thunder edit-btn"
                                            data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                            data-folio="' . htmlspecialchars($row['folio']) . '"
                                            data-nombre_vendedor="' . htmlspecialchars($row['nombre_vendedor']) . '"
                                            data-sucursal="' . htmlspecialchars($row['sucursal']) . '"
                                            data-cliente="' . htmlspecialchars($row['cliente']) . '"
                                            data-num_pedido="' . htmlspecialchars($row['num_pedido']) . '"
                                            data-factura="' . htmlspecialchars($row['factura']) . '"
                                            data-paqueteria="' . htmlspecialchars($row['paqueteria']) . '"
                                            data-comentario="' . htmlspecialchars($row['comentario']) . '"
                                            data-cotizaciones="' . htmlspecialchars($row['cotizaciones']) . '"
                                            title="Editar requisición">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>';
                                }else{
                                    echo '<button class="btn-disabled2"
                                            title="No se puede editar esta requisición">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>';                                    
                                }
                                ?>

                                <?php
                                $estatusString = "";
                                switch ($row['estatus']) {
                                    case "Pendiente":
                                        $estatusString = "Pendiente";
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
                                        }
                                        break;
                                    case "Producción":
                                        $estatusString = "Producción";
                                        if ($tipo_usuario === "Administrador" || $rol_usuario === "Gerente") {
                                            echo '<button type="button" class="btn-disabled2" 
                                                    title="No se puede cancelar una requisición en producción">
                                                    <i class="bi bi-ban"></i>
                                                </button>';
                                        }else{

                                        }
                                        break;

                                    case "En producción":
                                        $estatusString = "Maquinado";
                                        if ($tipo_usuario === "Administrador" || $rol_usuario === "Gerente") {
                                            echo '<button type="button" class="btn-disabled2" 
                                                    title="No se puede cancelar una requisición en producción">
                                                    <i class="bi bi-ban"></i>
                                                </button>';
                                        }else{

                                        }
                                        break;
                                    case "Finalizada":
                                        $estatusString = "Finalizada";
                                        if ($tipo_usuario === "Administrador" || $rol_usuario === "Gerente") {
                                            echo '<button type="button" class="btn-disabled2" 
                                                    title="No se puede cancelar una requisición en producción">
                                                    <i class="bi bi-ban"></i>
                                                </button>';
                                        }else{

                                        }
                                        break;
                                    case "Completada":
                                        $estatusString = "Completada";

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
                                <span class="span-status"><?= htmlspecialchars($estatusString ?? '') ?></span>
                                <button class="btn btn-sm btn-outline-success" onclick="pintarCadenaEstatus('<?= $row['estatus'] ?? '' ?>')" 
                                    data-bs-toggle="modal" data-bs-target="#modalEstatusInfo">
                                    <i class="bi bi-info-circle"></i>
                                </button>

                            </div>
                        </td>
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

                        <td><?= htmlspecialchars($row['fechahora']??""); ?></td>
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
<!-- Modal para agregar/editar registro -->
<div class="modal fade" id="modalAgregarEditar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="titleModalAddEdit" class="modal-title" id="modalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="form-post" action="" method="POST">                        
                    <input type="hidden" id="inputAction" name="action">
                    <input type="hidden" id="inputIdRequisicion" name="id_requisicion" >
                    <input type="hidden" id="inputId" value="<?= $_SESSION['id'] ?>" name="id_vendedor" >
                    <input type="hidden" value="Pendiente" name="estatus">
                    <input id="inputCotizaciones" type="hidden" name="cotizaciones">
                    <input id="inputFecha" type="hidden" name="fechahora" required readonly tabindex="-1">
                    <input id="inputVendedor" type="hidden" name="nombre_vendedor" value="<?= $nombreUser ?>" readonly tabindex="-1">

                    <div class="d-flex justify-content-between ">
                        <!-- <div style="width:48%;">
                            <label for="inputVendedor" class="lbl-general">Vendedor *</label>
                            <input id="inputVendedor" type="text" class="input-disabled" name="nombre_vendedor" value="<?= $nombreUser ?>" required readonly tabindex="-1">
                        </div> -->
                        <div style="width:48%;">
                            <label for="inputSucursal" class="lbl-general">Sucursal *</label>
                            <select id="inputSucursal" class="selector" name="sucursal" required >
                                <option value="" selected disabled>Seleccionar</option>
                                <option value="Ventas Nacionales">Ventas Nacionales</option>
                                <option value="Ventas Industriales">Ventas Industriales</option>
                                <option value="Sucursal Industrias">Sucursal Industrias</option>
                                <option value="Sucursal Queretaro">Sucursal Queretaro</option>
                                <option value="Sucursal Monterrey">Sucursal Monterrey</option>
                                <option value="Sucursal Toluca">Sucursal Toluca</option>
                            </select>
                        </div>
                        <div style="width:48%;">
                            <label for="inputCliente" class="lbl-general">Cliente *</label>
                            <input id="inputCliente" type="text" class="input-text" name="cliente" required>
                        </div>
                    </div>
                    <!-- <div class="d-flex justify-content-between ">
                        <div style="width:48%;">
                            <label for="inputFecha" class="lbl-general">Fecha y hora *</label>
                            <input id="inputFecha" type="text" class="input-disabled" name="fechahora" required readonly tabindex="-1">
                        </div> 
                    </div> -->
                    <div class="d-flex justify-content-between ">
                        <!-- <div style="width:48%;">
                            <label for="inputFolio" class="lbl-general">Folio *</label>
                            <input id="inputFolio" type="text" class="input-text" name="folio" required>
                        </div> -->
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
                            <label for="inputFactura" class="lbl-general text-break">Factura/remision/nota</label>
                            <input id="inputFactura" type="text" class="input-text" name="factura">
                        </div>
                        <div style="width:48%;">
                            <label for="inputComentario" class="lbl-general">Comentario (opcional)</label>
                            <input id="inputComentario" type="text" maxlength="50" class="input-text" name="comentario">
                            <small id="contadorComentario" style="display:block; text-align:right; font-size:12px; color:#555;">0 / 50 caracteres</small>
                        </div>
                    </div>
                    <!-- <div class="d-flex justify-content-center mb-3">

                    </div> -->
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:100%;">
                            <label for="buscadorCotizaciones" class="lbl-general">Agregar cotizaciones *</label>
                            <select id="buscadorCotizaciones">
                                <option value="" selected disabled>Seleccione una cotizacion</option>
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
                                    <button type="button" class="btnFirmaPredeterminada btn-auth" 
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
                                    <button type="button" class="btnFirmaPredeterminada btn-auth" 
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
<!-- /////////////////////////////MODAL DETALLES DEL ESTATUS DE REQUISICION //////////////////////////////// -->
<div class="modal fade" id="modalEstatusInfo" tabindex="-1" aria-labelledby="modalEstatusLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEstatusLabel">Detalles de los estatus de requisiciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- boton mostrar/ocultar detalles -->
        <div class="text-start my-3">
            <button id="toggleDetalles" class="btn btn-outline-secondary btn-sm">
                Ver detalles del estatus de requisiciones
            </button>
        </div>
        <!-- contenedor oculto de visibilidad y tabla informativa -->
        <div id="contenedorDetalles" class="overflow-hidden" style="max-height: 0; transition: max-height 0.6s ease;">
            <!-- visibilidad -->
            <div class="mb-4">
                <h6 class="fw-bold">Visibilidad de Requisiciones</h6>
                <ul>
                    <li><strong>Gerencia y dirección:</strong> pueden ver <em>todas</em> las requisiciones.</li>
                    <li><strong>CNC:</strong> solo verán las requisiciones cuyo estatus sea a partir de autorizada.</li>
                    <li><strong>Vendedor:</strong> solo ve requisiciones que ha creado con su usuario.</li>
                </ul>
            </div>
            <!-- tabla informativa -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle tabla-billets">
                    <thead class="table-light">
                        <tr>
                            <th>Estatus</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Pendiente</td>
                            <td>Ventas gerencia o dirección deben autorizar la requisición.</td>
                        </tr>
                        <tr>
                            <td>Autorizada</td>
                            <td>Requisición autorizada. Inventarios debe dar salida a billets.</td>
                        </tr>
                        <tr>
                            <td>Producción</td>
                            <td>El maquinado del sello está pendiente de comenzar.</td>
                        </tr>
                        <tr>
                            <td>Maquinado CNC</td>
                            <td>El sello está siendo maquinado actualmente.</td>
                        </tr>
                        <tr>
                            <td>Finalizada</td>
                            <td>El proceso de maquinado ha concluido con éxito.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- progreso de estatus -->
        <div class="text-center mt-4 mb-4" style="font-size: 12px !important;">
            <div id="cadenaEstatusModal" class="status-chain" style="overflow-x:auto;min-height:120px;">
                <!-- Estatus: Pendiente -->
                <div class="d-flex flex-column align-items-center position-relative">
                    <i class="bi bi-check-circle-fill icon" data-step="1"></i>
                    <span class="label">Pendiente</span>
                </div>
                <i class="bi bi-dash icon" data-step="1-2"></i>

                <!-- Estatus: Autorizada -->
                <div class="d-flex flex-column align-items-center position-relative">
                    <i class="bi bi-check-circle-fill icon" data-step="2"></i>
                    <span class="label">Autorizada</span>
                </div>
                <i class="bi bi-dash icon" data-step="2-3"></i>

                <!-- Estatus: Produccion -->
                <div class="d-flex flex-column align-items-center position-relative">
                    <i class="bi bi-check-circle-fill icon" data-step="3"></i>
                    <span class="label">Producción</span>
                </div>
                <i class="bi bi-dash icon" data-step="3-4"></i>

                <!-- Estatus: En producción -->
                <div class="d-flex flex-column align-items-center position-relative">
                    <i class="bi bi-check-circle-fill icon" data-step="4"></i>
                    <span class="label">Maquinado CNC</span>
                </div>
                <i class="bi bi-dash icon" data-step="4-5"></i>

                <!-- Estatus: Finalizada -->
                <div class="d-flex flex-column align-items-center position-relative">
                    <i class="bi bi-check-circle-fill icon" data-step="5"></i>
                    <span class="label">Finalizada</span>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggleDetalles');
    const contenedor = document.getElementById('contenedorDetalles');

    let abierto = false;

    toggleBtn.addEventListener('click', function () {
        if (!abierto) {
            contenedor.style.maxHeight = contenedor.scrollHeight + "px";
            toggleBtn.textContent = "Ver menos detalles";
            abierto = true;
        } else {
            contenedor.style.maxHeight = "0";
            toggleBtn.textContent = "Ver detalles de los estatus de requisiciones";
            abierto = false;
        }
    });

});  
function pintarCadenaEstatus(estatusActual) {
    const orden = ['Creada', 'Pendiente', 'Autorizada', 'Producción', 'En producción', 'Finalizada'];
    const index = orden.findIndex(e => e.toLowerCase() === estatusActual.toLowerCase());

    const icons = document.querySelectorAll('#cadenaEstatusModal .icon');
    icons.forEach((icon) => {
        const step = icon.dataset.step;
        if (step !== undefined) {
            const isCircle = !step.includes('-');
            const pos = isCircle ? parseInt(step) : parseInt(step.split('-')[0]);
            if (pos <= index) {
                icon.classList.add('item-chain-active');
            } else {
                icon.classList.remove('item-chain-active');
            }
        }
    });
}
</script>


</body>
</html>
