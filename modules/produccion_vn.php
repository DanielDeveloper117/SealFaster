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
    <script src="<?= controlCache('../assets/js/produccion_vn.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-table.css'); ?>">

    <?php include(ROOT_PATH . 'includes/backend/produccion_vn.php'); 
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

</style>
<section class="section-table flex-column mt-2 mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-1 mb-3">
            <h1>Requisiciones para Maquinado de Sellos</h1>
            <div class="d-flex flex-row justify-content-between col-3 gap-5 mt-5">
                <button type="button" id="btnAgregar" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalAgregarEditar">Nueva requisicion</button>
            </div>
        </div>
        <div class="table-container">
            <div class="d-flex flex-column mb-2 justify-content-start w-100" style="">
                <div>
                    <label for="selectorEstatus">Filtro estatus:</label>
                    <select id="selectorEstatus" class="input-selector mt-2">
                        <option disabled selected>Seleccionar</option>
                        <?php 
                            if($tipo_usuario=="Vendedor" && $rol_usuario=="Gerente"){
                                echo '<option value="Autorizar1">Autorizacion de gerencia pendiente</option>';
                                echo '<option value="Autorizada1">Autorizacion de dirección pendiente</option>';
                                echo '<option value="Produccion">En producción</option>';
                            }elseif($tipo_usuario=="Administrador"){
                                echo '<option value="Autorizar2">Autorizacion de dirección pendiente</option>';
                                echo '<option value="Produccion">En producción</option>';
                            }else{
                                echo '<option value="Pendiente">Autorizacion de gerencia pendiente</option>';
                                echo '<option value="Autorizada1">Autorizacion de dirección pendiente</option>';
                                echo '<option value="Produccion">En producción</option>';
                            }
                        ?>
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
                        <th>Cliente</th>
                        <th>Cotizaciones</th>
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
                                <button type="submit" class="btn-general">Generar PDF</button>
                            </form>
                            <div class="mt-1">
                                <?php if (($row['estatus'] == "Pendiente" ) && $row['id_vendedor'] == $_SESSION['id']): ?>
                                    <button class="btn-thunder edit-btn" 
                                        data-id_requisicion="<?= $row['id_requisicion']; ?>"
                                        data-nombre_vendedor="<?= $row['nombre_vendedor']; ?>"
                                        data-sucursal="<?= $row['sucursal']; ?>"
                                        data-cliente="<?= $row['cliente']; ?>"
                                        data-folio="<?= $row['folio']; ?>"
                                        data-num_pedido="<?= $row['num_pedido']; ?>"
                                        data-factura="<?= $row['factura']; ?>"
                                        data-paqueteria="<?= $row['paqueteria']; ?>"
                                        data-comentario="<?= $row['comentario']; ?>"
                                        data-cotizaciones="<?= $row['cotizaciones']; ?>"
                                    >Editar</button>
                                <?php endif; ?>
                            </div>
                            <div class="mt-1">
                                <?php if($row['estatus']=="Producción"){
                                        echo '<span class="span-terracota">Producción pendiente</span>';
                                    }else if($row['estatus']=="En producción"){
                                        echo '<span class="span-terracota">Maquinando sellos</span>';
                                    }else if($row['estatus']=="Autorizada"){
                                        if($tipo_usuario=="Administrador"){
                                            echo '
                                            <button type="button" class="btn-terracota btn-admin-autoriza" 
                                            data-bs-toggle="modal" data-bs-target="#modalAdminAutoriza"
                                            data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                            data-autoriza="a"
                                            >Autorizar maquinado</button>
                                            ';
                                            
                                        }else{
                                            echo '<span class="span-terracota">Dirección debe autorizar</span>';
                                        }
                                    }else if($row['estatus']=="Pendiente"){
                                        if($tipo_usuario=="Vendedor"){
                                            if($rol_usuario=="Gerente"){ 
                                                echo '
                                                    <button type="button" class="btn-terracota btn-gerente-autoriza" 
                                                        data-bs-toggle="modal" data-bs-target="#modalGerenteAutoriza"
                                                        data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                        data-autoriza="g"
                                                    >Autorizar</button>
                                                    ';
                                            }else{
                                                echo '<span class="span-terracota">Gerencia debe autorizar</span>';
                                                            
                                            }
                    
                                        }else if($tipo_usuario=="Administrador"){
                                            echo '
                                                <button type="button" class="btn-terracota btn-admin-autoriza" 
                                                    data-bs-toggle="modal" data-bs-target="#modalAdminAutoriza"
                                                    data-id-requisicion="' . htmlspecialchars($row['id_requisicion']) . '"
                                                    data-autoriza="a"
                                                >Autorizar maquinado</button>
                                            ';
                                    
                                        }
                                    }else{
                                       
                                    }
                                ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['id_requisicion']??""); ?></td>
                        <td><?= htmlspecialchars($row['estatus']??""); ?></td>
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

                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:48%;">
                            <label for="inputVendedor" class="lbl-general">Vendedor *</label>
                            <input id="inputVendedor" type="text" class="input-disabled" name="nombre_vendedor" value="<?= $nombreUser ?>" required readonly tabindex="-1">
                        </div>
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
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:48%;">
                            <label for="inputCliente" class="lbl-general">Cliente *</label>
                            <input id="inputCliente" type="text" class="input-text" name="cliente" required>
                        </div>
                        <div style="width:48%;">
                            <label for="inputFecha" class="lbl-general">Fecha y hora *</label>
                            <input id="inputFecha" type="text" class="input-disabled" name="fechahora" required readonly tabindex="-1">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:48%;">
                            <label for="inputFolio" class="lbl-general">Folio *</label>
                            <input id="inputFolio" type="text" class="input-text" name="folio" required>
                        </div>
                        <div style="width:48%;">
                            <label for="inputPedido" class="lbl-general">Num. Pedido *</label>
                            <input id="inputPedido" type="text" class="input-text" name="num_pedido" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:48%;">
                            <label for="inputFactura" class="lbl-general">Factura/remision/nota</label>
                            <input id="inputFactura" type="text" class="input-text" name="factura">
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
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <div style="width:100%;">
                            <label for="inputComentario" class="lbl-general">Comentario (opcional)</label>
                            <input id="inputComentario" type="text" maxlength="75" class="input-text" name="comentario">
                            <small id="contadorComentario" style="display:block; text-align:right; font-size:12px; color:#555;">0 / 76 caracteres</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:100%;">
                            <label for="buscadorCotizaciones" class="lbl-general">Agregar cotizaciones *</label>
                            <select id="buscadorCotizaciones">
                                <option value="" selected disabled>Seleccione una cotizacion</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:100%;">
                            <table id="miniTableCotizaciones" class="table table-bordered border border-2 tabla-billets">
                                <thead>
                                    <tr>
                                        <th scope="col">Remover</th>
                                        <th scope="col">Id cotizacion</th>
                                        <th scope="col">Perfil</th>
                                        <th scope="col">Tipo medida</th>
                                        <th scope="col">Medida</th>
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
                <p>Escanea el código QR con tu dispositivo movil, luego en la ventana dibuja tu firma y toca el boton Continuar. Caducará en 5 minutos.</p>
                <div id="ContainerQR" class="d-flex justify-content-center">

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
                <p>Escanea el código QR con tu dispositivo movil, luego en la ventana dibuja tu firma y toca el boton Continuar.</p>
                <div id="ContainerQR2" class="d-flex justify-content-center">

                </div>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->

</body>
</html>
