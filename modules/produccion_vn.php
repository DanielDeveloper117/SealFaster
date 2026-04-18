<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="../assets/img/general/favicon.ico?v=2" />
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/datatables.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/datatables.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/chosen.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/chosen.jquery.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
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
            <table id="productionTable" class="mainTable table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Acciones</th>
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
                                <button type="button" class="btn-unlink btn-maquinado-sellos" 
                                    data-bs-toggle="modal" data-bs-target="#modalMaquinadoSellos"
                                    data-id-requisicion="<?= htmlspecialchars($row['id_requisicion']); ?>"
                                    title="Ver maquinado de sellos respecto a las cotizaciones de esta requisición">
                                    <i class="bi bi-motherboard"></i>
                                </button>

                                <?php
                                $esMia = "0";
                                $estatusString = "";
                                $estatusClass = "span-status";
                                if ($row['estatus'] === "Pendiente" && $row['id_vendedor'] == $_SESSION['id']) {
                                    $esMia = "1";
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

                                    case "En producción":
                                        $estatusString = "En maquinado";
                                        if ($tipo_usuario === "Administrador" || $rol_usuario === "Gerente") {
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
                                    case "Finalizada":
                                        $estatusString = "Finalizada";
                                        
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

<?php include(ROOT_PATH . "includes/modal_acciones_requisicion.php"); ?>
<?php include(ROOT_PATH . 'includes/modal_comentarios_adjuntos.php'); ?>
<?php include(ROOT_PATH . 'includes/modal_maquinado_sellos.php'); ?>
<?php include(ROOT_PATH . "includes/modal_estatus_requisicion.php"); ?>
<?php include(ROOT_PATH . "includes/modal_barras_pendientes.php"); ?>


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

        // 4. SweetAlert (Solo si no se ha aceptado antes)
        // if (localStorage.getItem("recomendacionComentarios") != "1") {
        //     Swal.fire({
        //         title: 'Recomendación para maquinado óptimo',
        //         html: 'Recuerda que si tienes comentarios o archivos/imágenes útiles para los operadores CNC, <strong>NO OLVIDES AGREGARLOS</strong> (disponible si el folio aún esta pendiente de autorizar). ' +
        //             'Haz clic en el botón "<i class="bi bi-chat-left-text" style="color: #55AD9B; font-weight: bold;"></i>" en las acciones del folio.',
        //         icon: 'info',
        //         confirmButtonText: 'Entendido',
        //         width: '600px',
        //         position: 'bottom-end',
        //         toast: true,
        //         showConfirmButton: true,
        //         input: 'checkbox',
        //         inputPlaceholder: 'No mostrar nuevamente',
        //         inputAttributes: { id: 'recomendacionComentarios' }
        //     }).then((result) => {
        //         if (result.isConfirmed && result.value) {
        //             localStorage.setItem("recomendacionComentarios", "1");
        //         }
        //     });
        // }
    });
</script>
</body>
</html>
