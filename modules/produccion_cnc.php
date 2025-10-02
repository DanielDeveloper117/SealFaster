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
    <link rel="stylesheet" href="<?= controlCache('../assets/css/modal-status.css'); ?>">

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
    label{
        font-size:12px;
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
            <!-- <div class="d-flex flex-column mb-2 justify-content-start w-100" style="">
                <div>
                    <label for="selectorEstatus">Filtro estatus:</label>
                    <select id="selectorEstatus" class="input-selector mt-2">
                        <option disabled selected>Seleccionar</option>
                        <?php if ($rol_usuario=="Gerente"): ?>
                            <button type="button" class="btn-terracota btn-finalizar" 
                                data-bs-toggle="modal" data-bs-target="#modalFinalizar"
                                data-id-requisicion="<?= htmlspecialchars($row['id_requisicion']); ?>"
                                >Finalizar</button>
                            <option value="Pendiente">Pendiente de maquinar</option>
                            <option value="Maquinando">Pendienre de finalizar</option>
                        <?php elseif ($rol_usuario!="Gerente"): ?>
                            <span class="span-terracota">Gerencia debe finalizarla</span>
                            <option value="Pendiente2">Pendiente de maquinar</option>
                            <option value="Maquinando2">Pendiente de finalizar</option>
                        <?php endif; ?>
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
                                            echo '<button class="btn-thunder btn-control-almacen" 
                                                    data-bs-toggle="modal" data-bs-target="#modalControlAlmacenInventario"
                                                    data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Agregar clave a control de almacen">
                                                    <i class="bi bi-plus-square"></i>
                                                </button>';
                                            echo '<button class="btn-auth btn-salida-barras" 
                                                    data-bs-toggle="modal" data-bs-target="#modalDarSalida"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Dar salida a barras de esta requisición">
                                                    <i class="bi bi-list-check"></i>
                                                </button>';
                                        }
                                        break;

                                    case "Producción":
                                        $estatusString = "Producción";

                                        if ($tipo_usuario === "CNC" && $rol_usuario == "Gerente") {
                                            echo '<button type="button" class="btn-blue btn-cnc-firma" 
                                                    data-bs-toggle="modal" data-bs-target="#modalGuardarOperador"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-autoriza="cnc"
                                                    title="Cambiar estatus a maquinado CNC iniciado">
                                                    <i class="bi bi-file-play"></i>
                                                </button>';
                                        } elseif ($tipo_usuario === "CNC" && $rol_usuario != "Gerente") {
                                            // aqui no span, solo controlado por estatusString
                                        }
                                        break;

                                    case "En producción":
                                        $estatusString = "Maquinado";

                                        if ($rol_usuario == "Gerente") {
                                            echo '<button type="button" class="btn-terracota btn-finalizar" 
                                                    data-bs-toggle="modal" data-bs-target="#modalFinalizar"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Finalizar maquinado">
                                                    <i class="bi bi-check-square"></i>
                                                </button>';
                                        } else {
                                            // solo mensaje via estatusString
                                        }
                                        break;

                                    case "Finalizada":
                                        $estatusString = "Finalizada";
                                        // Inventarios puede agregar clave al almacen
                                        if ($tipo_usuario === "Inventarios") {
                                            echo '<button class="btn-thunder btn-control-almacen" 
                                                    data-bs-toggle="modal" data-bs-target="#modalControlAlmacenInventario"
                                                    data-id_requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Agregar clave extra al control de almacen">
                                                    <i class="bi bi-plus-square"></i>
                                                </button>';
                                            echo '<button class="btn-auth btn-bar-entry" 
                                                    data-bs-toggle="modal" data-bs-target="#modalDarSalida"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    title="Dar entrada a barras de retorno de esta requisición">
                                                    <i class="bi bi-save"></i>
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
<!-- //////////////////////////MODAL CNC DEBE FIRMAR /////////////////////// -->
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
                <span class="title-form">Primero debe agregar el operador cnc que realizará el maquinado</span>
                <button type="button" id="btn-closeOperador" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="" style="width:100%;">
                    <label for="inputOperadorCNC" class="lbl-general">Nombre del operador CNC</label>
                    <input id="inputOperadorCNC" type="text" class="input-text"  name="operador_cnc" required>
                    <input type="hidden" id="inputIdRequisicionOperador" name="id_requisicion">
                </div>  
            </div>
            <div class="modal-footer justify-content-end">
                <button id="btnGuardarOperador" type="button" class="btn-general" tabindex="-1">Guardar</button>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////MODAL AGREGAR CONTROL ALMACEN INVENTARIO/////////////////////// -->
<div class="modal fade" id="modalControlAlmacenInventario" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex justify-content-between" style="width:90%;">
                    <h5 id="titleModal" class="modal-title" id="modalLabel">CONTROL DE ALMACEN</h5>
                    <button id="btnTablaControlAlmacenInventario" type="button" class="btn btn-primary">
                    Ver barras
                    </button>
                </div>
                <button id="btnCloseModal" type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formControlAlmacenInventario" action="" method="POST">                        
                    <input type="hidden" id="inputIdRequisicion" name="id_requisicion">
                    <div class="d-flex justify-content-between">
                        <div class="" style="width:35%;">
                            <label for="inputCantidadBarras" class="lbl-general">CANTIDAD DE BARRAS</label>
                            <input id="inputCantidadBarras" type="number" class="input-text"  min="0" step="1" name="cantida_barras" required>
                        </div>
                        <div class="" style="width:63%;">
                            <label for="inputClave" class="lbl-general">CLAVE</label>
                            <input type="text" class="input-text" id="inputClave" name="clave" placeholder="Ingrese una clave valida" required>
                        </div>
                    </div>
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
                        <div class="" style="width:35%;">
                            <label for="inputEntrada" class="lbl-general">MM ENTREGA</label>
                            <input id="inputEntrada" type="number" class="input-text"  min="0" step="0.01" name="mm_entrega" required>
                        </div>
                        <div class="" style="width:63%;">
                            <label for="inputLotePedimento" class="lbl-general">LOTE PEDIMENTO</label>
                            <input id="inputLotePedimento" type="text" class="input-text"  name="lote_pedimento" required>
                            <p id="pLotePedimento" class="d-none p-invalida">Ese Lote pedimento no existe.</p>
                        </div>  
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:35%;">
                            <label for="inputExtra" class="lbl-general">Barra extra</label>
                            <input 
                                type="checkbox" 
                                id="inputExtra"
                                name="barra_extra" 
                                value="0"
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
<!-- //////////////////////////MODAL TABLA CONTROL ALMACEN INVENTARIO/////////////////////// -->
<div class="modal fade" id="modalTableControAlmacenInventario" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Barras de control de almacen</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="width:100%;">
                    <table id="miniTableBarrasInventario" class="table table-bordered border border-2 tabla-billets">
                        <thead>
                            <tr><th scope="col"></th>
                                <th scope="col">BARRAS</th>
                                <th scope="col">CLAVE</th>
                                <th scope="col">LOTE PEDIMENTO</th>
                                <th scope="col">MM ENTREGA</th>
                                <!-- <th scope="col">MM SALIDA</th>
                                <th scope="col">LONG. TOTAL SELLOS</th>
                                <th scope="col">MERMA POR CORTE</th>
                                <th scope="col">SCRAP PZ</th>
                                <th scope="col">SCRAP MM</th> -->
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////MODAL DAR SALIDA A BILLETS DE LA REQUISICION /////////////////////// -->
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
                    <button id="btnDarSalidaBillets" type="button" class="btn-general">Continuar</button>
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
<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Para finalizar llene los campos solicitados</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="width:100%; margin-bottom:20px;">
                    <h5 class="modal-title">Claves de requisición con folio: <span></span></h5>
<div style="overflow-x: auto; width: 100%;">
    <table class="table table-bordered border border-2 tabla-billets" style="table-layout: fixed; width: max-content;">
        <thead>
            <tr>
                <th style="width: 70px;">Barra mermada</th>
                <th style="width: 100px;">Cantidad</th>
                <th style="width: 280px;">Clave</th>
                <th style="width: 250px;">Lote pedimento</th>
                <th style="width: 120px;">MM Entrega</th>
                <th style="width: 120px;">MM Usados</th>
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

                </div>
            </div>
            <div class="modal-footer">
                <button id="finalizarRequisicion" type="button" class="btn-general">Finalizar</button>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
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
                <table class="table table-bordered align-middle">
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
            <div id="cadenaEstatusModal" class="status-chain">
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
