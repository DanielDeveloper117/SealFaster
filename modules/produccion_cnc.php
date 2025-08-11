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
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-table.css'); ?>">

    <?php include(ROOT_PATH . 'includes/backend/produccion_cnc.php'); 
          include(ROOT_PATH . 'includes/backend_info_user.php');
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
            <div class="d-flex flex-column mb-2 justify-content-start w-100" style="">
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
            </div> 
            <table id="productionTable" class="table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="background-color:#55ad9b52;">Acciones</th>
                        <th>Id</th>
                        <th>Estatus</th>
                        <th>Vendedor</th>
                        <th>Cliente</th>
                        <!-- <th>Cotizaciones</th> -->
                        <th>Fecha</th>
                        <th>Folio</th>
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
                            <form action="../includes/functions/generar_requisicion.php" method="GET" target="_blank">
                                <input type="hidden" name="id_requisicion" value="<?= htmlspecialchars($row['id_requisicion']??""); ?>">
                                <button type="submit" class="btn-general">Ver PDF</button>
                            </form>
                            <div class="mt-1">
                                <?php 
                                    if($rol_usuario=="Gerente" && $row['estatus']=="Producción"){
                                        echo '
                                            <button type="button" class="btn-thunder btn-editar-medidas" 
                                            data-bs-toggle="modal" data-bs-target="#modalEditarMedidas"
                                            data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                            >Editar medidas</button>
                                        ';
                                    }                                
                                ?>
                            </div>
                            <div class="mt-1">
                                <?php
                                    if($row['estatus']=="Producción" && $rol_usuario=="Gerente"){
                                        echo '
                                            <button type="button" class="btn-blue btn-cnc-firma" 
                                            data-bs-toggle="modal" data-bs-target="#modalCncFirma"
                                            data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                            data-autoriza="cnc"
                                            >Comenzar maquinado</button>
                                        ';
                                    }elseif($row['estatus']=="Producción" && $rol_usuario!="Gerente"){
                                        echo '<span class="span-terracota">En revisión</span>';
                                    }
                                ?>
                            </div>
                            <div class="mt-1">
                                <?php if ($row['estatus'] == "En producción"): ?>
                                    <button class="btn-thunder btn-control-almacen" 
                                        data-bs-toggle="modal" data-bs-target="#modalControlAlmacen"
                                        data-id_requisicion="<?= htmlspecialchars($row['id_requisicion']); ?>"
                                    >Agregar clave</button>
                                <?php endif; ?>
                            </div>

                            <div class="mt-1">
                                <?php if ($rol_usuario=="Gerente" && $row['estatus'] == "En producción"): ?>
                                    <button type="button" class="btn-terracota btn-finalizar" 
                                        data-bs-toggle="modal" data-bs-target="#modalFinalizar"
                                        data-id-requisicion="<?= htmlspecialchars($row['id_requisicion']); ?>"
                                    >Finalizar</button>
                                <?php elseif ($rol_usuario!="Gerente" && $row['estatus'] == "En producción"): ?>
                                    <span class="span-terracota">Gerencia debe finalizarla</span>
                                <?php endif; ?>
                            </div>

                        </td>
                        <td><?= htmlspecialchars($row['id_requisicion']??""); ?></td>
                        <td><?= htmlspecialchars($row['estatus']??""); ?></td>
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
                        <td><?= htmlspecialchars($row['folio']??""); ?></td>
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
<!-- //////////////////////////MODAL AGREGAR CONTROL ALMACEN/////////////////////// -->
<div class="modal fade" id="modalControlAlmacen" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex justify-content-between" style="width:90%;">
                    <h5 id="titleModal" class="modal-title" id="modalLabel">CONTROL DE ALMACEN</h5>
                    <button id="btnVerTabla" type="button" class="btn btn-primary">
                    Ver barras
                    </button>
                </div>
                <button id="btnCloseModal" type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formControlAlmacen" action="" method="POST">                        
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
                            <p id="pInvalida" class="d-none p-invalida" style="margin-bottom:0px;">Clave no valida, revise el archivo excel de claves validas.</p>
                            <p id="pValida" class="d-none p-valida" style="margin-bottom:0px;"></p>
                        </div>
                        <a href="../files/CNC_CLAVES.xlsx" download="CNC_CLAVES.xlsx" class="btn btn-success">
                            Descargar Excel de claves validas
                            <i class ="bi bi-download"></i>
                        </a>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputEntrada" class="lbl-general">MM ENTRADA</label>
                            <input id="inputEntrada" type="number" class="input-text"  min="0" step="0.01" name="mm_entrada" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputSalida" class="lbl-general">MM SALIDA</label>
                            <input id="inputSalida" type="number" class="input-text"  min="0" step="0.01" name="mm_salida" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
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
                    </div>

                    <button id="btnAgregarBarra" type="button" class="btn-disabled" tabindex="-1">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- //////////////////////////MODAL TABLA CONTROL ALMACEN /////////////////////// -->
<div class="modal fade" id="modalTableControAlmacen" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Barras de control de almacen</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="width:100%;">
                    <table id="miniTableBarras" class="table table-bordered border border-2 tabla-billets">
                        <thead>
                            <tr><th scope="col"></th>
                                <th scope="col">BARRAS</th>
                                <th scope="col">CLAVE</th>
                                <th scope="col">MM ENTRADA</th>
                                <th scope="col">MM SALIDA</th>
                                <th scope="col">LONG. TOTAL SELLOS</th>
                                <th scope="col">MERMA POR CORTE</th>
                                <th scope="col">SCRAP PZ</th>
                                <th scope="col">SCRAP MM</th>
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

<!-- //////////////////////////MODAL FINALIZAR REQUISICION /////////////////////// -->
<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
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
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->

</body>
</html>
