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
    <!-- <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-table.css'); ?>">    -->
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css"'); ?>"> 
    <!-- <link rel="stylesheet" href="<?= controlCache('../assets/css/modal-status.css'); ?>"> -->

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
<section class="section-table flex-column mt-2 mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-1 mb-3">
            <h1>Requisiciones para Maquinado de Sellos</h1>
            <!-- <div class="d-flex flex-row justify-content-between col-3 gap-5 mt-5">
                <button type="button" id="btnAgregar" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalAgregarEditar">Nueva requisicion</button>
            </div> -->
        </div>
        <div class="table-container">
            <table id="productionTable" class="table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th></th>
                        <!-- <th>Id</th> -->
                        <th>Folio</th>
                        <th>Estatus</th>
                        <th>Vendedor</th>
                        <th>Cliente</th>
                        <!-- <th>Cotizaciones</th> -->
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
                                <!-- PDF -->
                                <form action="../includes/functions/generar_requisicion.php" method="GET" target="_blank">
                                    <input type="hidden" name="id_requisicion" value="<?= htmlspecialchars($row['id_requisicion'] ?? ""); ?>">
                                    <button type="submit" class="btn-pdf" title="Generar PDF de esta cotizacion">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </button>
                                </form>

                                <?php
                                $estatusString = "";

                                switch ($row['estatus']) {
                                    case "Autorizada":
                                        $estatusString = "Autorizada";

                                        // CNC Gerente puede editar medidas
                                        if ($tipo_usuario === "CNC" && $rol_usuario == "Gerente") {
                                            echo '<button type="button" class="btn-thunder btn-editar-medidas" 
                                                    data-bs-toggle="modal" data-bs-target="#modalEditarMedidas"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Editar medidas de las cotizaciones">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>';
                                        }

                                        // Inventarios puede agregar clave al almacen
                                        if ($tipo_usuario === "Inventarios") {
                                            echo '<button class="btn-thunder btn-entregar-barras" 
                                                    data-bs-toggle="modal" data-bs-target="#modalTableControlAlmacenEntrega"
                                                    data-es_extra = "0"
                                                    data-estatus = "Autorizada"  
                                                    data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Agregar/remplazar barras de control de almacen">
                                                    <i class="bi bi-database-add"></i>
                                                </button>';
                                            // echo '<button class="btn-auth btn-salida-barras" 
                                            //         data-bs-toggle="modal" data-bs-target="#modalDarSalida"
                                            //         data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                            //         title="Dar salida a barras de esta requisición">
                                            //         <i class="bi bi-database-fill-check"></i>
                                            //     </button>';
                                        }
                                        break;

                                    case "Producción":
                                        $estatusString = "Producción";

                                        if ($tipo_usuario === "CNC" && $rol_usuario == "Gerente") {
                                            echo '<button type="button" class="btn-blue btn-iniciar-maquinado" 
                                                    data-bs-toggle="modal" data-bs-target="#modalGuardarOperador"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-autoriza="cnc"
                                                    title="Cambiar estatus a maquinado CNC iniciado">
                                                    <i class="bi bi-file-play"></i>
                                                </button>';
                                        } elseif ($tipo_usuario === "Inventarios") {
                                           
                                            echo '<button class="btn-thunder btn-entregar-barras" 
                                                    data-bs-toggle="modal" data-bs-target="#modalTableControlAlmacenEntrega"
                                                    data-es_extra = "1"
                                                    data-estatus = "Producción"  
                                                    data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Agregar/remplazar barras de control de almacen">
                                                    <i class="bi bi-database-add"></i>
                                                </button>';
                                            //  echo '<button class="btn-thunder btn-control-almacen" 
                                            //         data-bs-toggle="modal" data-bs-target="#modalControlAlmacenInventario"
                                            //         data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                            //         data-es_extra = "1"
                                            //         data-estatus = "Producción"                                                   
                                            //         title="Agregar/remplazar barras de control de almacen">
                                            //         <i class="bi bi-database-add"></i>
                                            //     </button>';    
                                        }elseif ($tipo_usuario === "CNC" && $rol_usuario != "Gerente") {
                                            // aqui no span, solo controlado por estatusString
                                        }
                                        break;

                                    case "En producción":
                                        $estatusString = "Maquinado";

                                        //if ($tipo_usuario === "CNC" && $rol_usuario == "Gerente") {
                                        if ($tipo_usuario === "CNC") {
                                            echo '<button type="button" class="btn-terracota btn-finalizar" 
                                                    data-bs-toggle="modal" data-bs-target="#modalFinalizar"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Finalizar maquinado">
                                                    <i class="bi bi-flag"></i>
                                                </button>';
                                        } elseif ($tipo_usuario === "Inventarios") {
                                            echo '<button class="btn-thunder btn-entregar-barras" 
                                                    data-bs-toggle="modal" data-bs-target="#modalTableControlAlmacenEntrega"
                                                    data-es_extra = "1"
                                                    data-estatus = "En producción""  
                                                    data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Agregar/remplazar barras de control de almacen">
                                                    <i class="bi bi-database-add"></i>
                                                </button>';
                                            // echo '<button class="btn-thunder btn-control-almacen" 
                                            //         data-bs-toggle="modal" data-bs-target="#modalControlAlmacenInventario"
                                            //         data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                            //         data-es_extra = "1"
                                            //         data-estatus = "En producción"                                                   
                                            //         title="Agregar clave extra al control de almacen">
                                            //         <i class="bi bi-database-add"></i>
                                            //     </button>';
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
                                                    title="Retornar barras al inventario">
                                                    <i class="bi bi-database-fill-down"></i>
                                                </button>';
                                        }else if ($tipo_usuario === "CNC") {
                                            if($row["fecha_revision_maquinado"] == Null){
                                                $colorBtn = "btn-general";
                                                $iconStatus = '<i class="bi bi-card-list me-1"></i><i class="bi bi-clock"></i>';
                                            }else{
                                                $colorBtn = "btn-auth";
                                                $iconStatus = '<i class="bi bi-card-list"></i><i class="bi bi-check2-all"></i>';
                                            }
                                            echo '<button type="button" class="'.$colorBtn.' btn-tabla-maquinado-mermas" 
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
                                                $colorBtn = "btn-general";
                                                $iconStatus = '<i class="bi bi-card-list me-1"></i><i class="bi bi-clock"></i>';
                                            }else{
                                                $colorBtn = "btn-auth";
                                                $iconStatus = '<i class="bi bi-card-list"></i><i class="bi bi-check2-all"></i>';
                                            }
                                            echo '<button type="button" class="'.$colorBtn.' btn-tabla-maquinado-mermas" 
                                                    data-bs-toggle="modal" data-bs-target="#modalTablaMaquinadoMermas"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-rol = "'.$rol_usuario.'"
                                                    title="Ver registros de maquinado y merma">
                                                    '.$iconStatus.'
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
                                <span class="span-status"><?= htmlspecialchars($estatusString ?? '') ?></span>
                                <button class="btn btn-sm btn-outline-success" onclick="pintarCadenaEstatus('<?= $row['estatus'] ?? '' ?>')" 
                                    data-bs-toggle="modal" data-bs-target="#modalEstatusInfo">
                                    <i class="bi bi-info-circle"></i>
                                </button>

                            </div>
                        </td>
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
<!-- ??????????????????????????MODAL CNC DEBE FIRMAR /////////////////////// -->
<div class="modal fade" id="modalCncFirma" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Siga las instrucciónes para firmar</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Escanea el código QR con tu dispositivo movil, luego en la ventana dibuja tu firma y toca el boton Continuar. Caducará en 5 minutos.</p>
                <div id="ContainerQR" class="d-flex justify-content-center">

                </div>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////MODAL GUARDAR OPERADOR CNC /////////////////////// -->
<div class="modal fade" id="modalGuardarOperador" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 50% !important;margin-top:10%;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Primero debe agregar la máquina que realizará el maquinado</span>
                <button type="button" id="btn-closeOperador" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between ">
                    <input type="hidden" id="inputIdRequisicionOperador" name="id_requisicion">
                    <div class="" style="width:48%;">
                        <label for="inputMaquina" class="lbl-general">Máquina CNC*</label>
                        <select id="inputMaquina" class="selector" required >
                            <option value="" selected disabled>Seleccione máquina</option>
                            <option value="Máquina 1">Máquina 1</option>
                            <option value="Máquina 2">Máquina 2</option>
                            <option value="Máquina 3">Máquina 3</option>
                            <option value="Máquina 4">Máquina 4</option>
                            <option value="Máquina 5">Máquina 5</option>
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
<!-- ????????????????????????MODAL AGREGAR CONTROL ALMACEN INVENTARIO/////////////////////// -->
<div class="modal fade" id="modalControlAlmacenInventario" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex justify-content-between" style="width:90%;">
                    <h5 id="titleModal" class="modal-title" id="modalLabel">CONTROL DE ALMACEN</h5>
                    <button id="btnTablaControlAlmacenInventario" type="button" class="btn btn-primary" data-estatus-requi="">
                    Ver barras
                    </button>
                </div>
                <button id="btnCloseModal" type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formControlAlmacenInventario" action="" method="POST">                        
                    <input id="inputIdRequisicion" type="hidden" name="id_requisicion">
                    <input id="inputClave" type="hidden"  name="clave" placeholder="Ingrese una clave valida" required>
                    <input id="inputCantidadBarras" type="hidden" value="1" min="0" step="1" name="cantida_barras" required>
                    <input id="inputMaterial" type="hidden" name="material" required>
                    <input id="inputMedida" type="hidden" name="medida" required>
                    <div class="d-flex justify-content-between">
                        <div class="" style="width:63%;">
                            <label for="inputLotePedimento" class="lbl-general">LOTE PEDIMENTO</label>
                            <input id="inputLotePedimento" type="text" class="input-text"  name="lote_pedimento" required>
                        </div>  
                        <div class="" style="width:35%;">
                            <label for="inputEntrada" class="lbl-general">MM ENTREGA</label>
                            <input id="inputEntrada" type="number" class="input-text"  min="0" step="0.01" name="mm_entrega" required>
                        </div>
                    </div>  
                    <div class="d-flex justify-content-between">
                        <div class="" style="width:100%;">
                            <p id="pLotePedimento" class="d-none"></p>
                        </div>
                    </div>                  
                    <!-- <div class="d-flex justify-content-between">
                        <div class="" style="width:35%;">
                            <label for="inputCantidadBarras" class="lbl-general">CANTIDAD DE BARRAS</label>
                        </div>
                        <div class="" style="width:63%;">
                            <label for="inputClave" class="lbl-general">CLAVE</label>
                        </div>
                    </div> -->
                    <div class="d-flex flex-column justify-content-between mb-3">
                        <div class="d-flex flex-column justify-content-between ">
                            <p id="pInvalida2" class="d-none p-invalida2" style="margin-bottom:0px;">La clave debe ser valida para optimizar el control de almacen.</p>
                            <p id="pInvalida" class="d-none p-invalida" style="margin-bottom:0px;">Clave no valida.</p>
                            <p id="pValida" class="d-none p-valida" style="margin-bottom:0px;"></p>
                        </div>
                        <!-- <a href="../files/CNC_CLAVES.xlsx" download="CNC_CLAVES.xlsx" class="btn btn-success">
                            Descargar Excel de claves validas
                            <i class ="bi bi-download"></i>
                        </a> -->
                    </div>
  
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:35%;">
                            <label id="lblInputExtra" for="inputExtra" class="lbl-general">Barra extra</label>
                            <input 
                                type="checkbox" 
                                id="inputExtra"
                                name="barra_extra" 
                                
                                onclick="this.value = this.checked ? 1 : 0"
                                style="transform: scale(1.5); margin-left: 10px;"
                            >
                        </div>
                    <!-- <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputTotalSellos" class="lbl-general">LONG. TOTAL DE SELLOS</label>
                            <input id="inputTotalSellos" type="number" class="input-text"  min="0" step="0.01" name="total_sellos" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputMermaCorte" class="lbl-general">MERMA POR CORTE</label>
                            <input id="inputMermaCorte" type="number" class="input-text"  min="0" step="0.01" name="merma_corte" required>
                        </div>                        
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputScrapPz" class="lbl-general">SCRAP PZ</label>
                            <input id="inputScrapPz" type="number" class="input-text"  min="0" step="0.01" name="scrap_pz" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputScrapMm" class="lbl-general">SCRAP MM</label>
                            <input id="inputScrapMm" type="number" class="input-text"  min="0" step="0.01" name="scrap_mm" required>
                            <p id="pInvalida3" class="d-none p-invalida">Ese Lote pedimento ya existe.</p>
                        </div>                        
                    </div> -->
                    </div> 
                    <div class="d-flex justify-content-between mb-3">
                        <button id="btnAgregarBarra" type="button" class="btn-disabled" tabindex="-1">Agregar</button>
                    </div> 
                </form>
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
                    <button id="btnEntregarBarras" type="button" class="btn-general btn-success col-3">
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
                        <!-- <small class="text-muted d-block mt-1">Se guarda automáticamente cada 30 seg</small> -->
                    </div>

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
                <span class="title-form">Indique el nuevo stock en el campo MM de Retorno</span>
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

<?php include("../includes/modal_estatus_requisicion.php"); ?>
</body>
</html>
