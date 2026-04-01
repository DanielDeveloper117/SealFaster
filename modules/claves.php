<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../auth/cerrar_sesion.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claves</title>
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/datatables.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/datatables.min.js'); ?>"></script>
    <!-- xlsx para exportar a Excel desde cliente -->
    <script src="<?= controlCache('../assets/dependencies/xlsx.full.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css'); ?>">
</head>
<body>

<?php
// ============================================================
// CONTROL DE ACCESO
// Solo Administrador y Sistemas pueden acceder a claves SRS.
// user_control.php ya redirige si la sesion no es valida;
// aqui se añade una capa extra para el tipo de usuario.
// ============================================================
include(ROOT_PATH . 'includes/user_control.php');

if (!in_array($tipo_usuario, ['Administrador', 'Sistemas'], true)) {
    echo "<script>$(document).ready(function(){
        sweetAlertResponse('warning','Acceso denegado',
            'No cuenta con permisos para acceder a esta seccion.',
            '../modules/welcome.php');
    });</script>";
    // No hacer exit aqui para que el HTML de la pagina cargue
    // y el SweetAlert se pueda mostrar; la redireccion la hace el JS.
}

// ============================================================
// LECTURA Y SANEAMIENTO DE FILTROS GET
// ============================================================
$filtro_material  = isset($_GET['material'])  ? trim($_GET['material'])                          : '';
$filtro_clave     = isset($_GET['clave'])      ? preg_replace('/\s+/', '', trim($_GET['clave'])) : '';
$filtro_proveedor = isset($_GET['proveedor'])  ? trim($_GET['proveedor'])                        : '';
$filtro_interior  = isset($_GET['interior'])   ? trim($_GET['interior'])                         : '';
$filtro_exterior  = isset($_GET['exterior'])   ? trim($_GET['exterior'])                         : '';
$ver_todo         = isset($_GET['all']);
$filtro_sin_precio= isset($_GET['sin_precio']);

// Whitelists para valores enumerados (evitar inyeccion via filtros)
$proveedores_validos = ['SKF', 'SLM', 'TRYGONAL', 'CARVIFLON'];
if ($filtro_proveedor !== '' && !in_array($filtro_proveedor, $proveedores_validos, true)) {
    $filtro_proveedor = '';
}

// Los numericos deben ser positivos
if ($filtro_interior !== '' && (!ctype_digit($filtro_interior) || (int)$filtro_interior < 0)) {
    $filtro_interior = '';
}
if ($filtro_exterior !== '' && (!ctype_digit($filtro_exterior) || (int)$filtro_exterior < 0)) {
    $filtro_exterior = '';
}

$hay_filtro = $filtro_material !== '' || $filtro_clave !== '' || $filtro_proveedor !== '' ||
              $filtro_interior !== '' || $filtro_exterior !== '' || $ver_todo || $filtro_sin_precio;

// ============================================================
// CONSULTA A BD  (solo si hay algun filtro activo)
// ============================================================
$arregloSelectPrecios = [];

if ($hay_filtro) {
    $sql    = "SELECT id, clave, clave_alterna, proveedor, tipo, material, interior, exterior, max_usable, precio
               FROM parametros
               WHERE 1=1";
    $params = [];

    if ($filtro_material !== '') {
        $sql               .= " AND REPLACE(material, '\\0', '') = :material";
        $params[':material'] = $filtro_material;
    }
    if ($filtro_clave !== '') {
        $sql           .= ' AND (clave = :clave OR clave_alterna = :clave_alterna)';
        $params[':clave'] = $filtro_clave;
        $params[':clave_alterna'] = $filtro_clave;
    }
    if ($filtro_proveedor !== '') {
        $sql               .= ' AND proveedor = :proveedor';
        $params[':proveedor'] = $filtro_proveedor;
    }
    if ($filtro_interior !== '') {
        $sql               .= ' AND interior = :interior';
        $params[':interior'] = (int)$filtro_interior;
    }
    if ($filtro_exterior !== '') {
        $sql               .= ' AND exterior = :exterior';
        $params[':exterior'] = (int)$filtro_exterior;
    }
    if ($filtro_sin_precio) {
        $sql .= " AND (precio IS NULL OR precio <= 0.00)";
    }

    $sql .= ' ORDER BY clave ASC';

    try {
        
        $stmt = $conn->prepare($sql);
        foreach ($params as $k => $v) {
            $tipo_param = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($k, $v, $tipo_param);
        }
        $stmt->execute();
        $arregloSelectPrecios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Limpiar bytes nulos de la consulta antes de pintar la tabla
        foreach ($arregloSelectPrecios as &$row) {
            if (isset($row['material'])) {
                $row['material'] = trim(str_replace("\0", '', $row['material']));
            }
        }
        unset($row);
    } catch (PDOException $e) {
        error_log('claves.php PDOException: ' . $e->getMessage());
        $arregloSelectPrecios = [];
    }
}
?>

<!-- ============================================================
    OVERLAY DE CARGA
============================================================ -->
<?php if (!empty($arregloSelectPrecios)): ?>
<div id="overlay">
    <div class="loading-message">
        <span>Cargando claves, por favor espere...</span>
    </div>
</div>
<?php endif; ?>

<!-- ============================================================
     LAYOUT PRINCIPAL
     ============================================================ -->
<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">

        <!-- Encabezado -->
        <div class="titulo mt-3 mb-3">
            <h1>Resultados de búsqueda de clave(s)</h1>
            <div class="d-flex flex-wrap gap-2 mb-2">
                <a href="panel_claves.php"
                   class="btn-purple d-inline-flex align-items-center gap-2">
                    <i class="bi bi-arrow-left"></i> Volver al panel
                </a>
            </div>
            <div class="d-flex flex-row justify-content-start col-12 col-md-6 gap-2">
                <?php if (in_array($tipo_usuario, ['Administrador', 'Sistemas'], true)): ?>
                <button type="button" id="btnAbrirAgregar"
                        class="btn-general d-flex justify-content-center align-items-center gap-2"
                        data-bs-toggle="modal" data-bs-target="#modalClave">
                    <i class="bi bi-plus-circle"></i> Agregar registro
                </button>
                <?php endif; ?>
    
                <?php if ($hay_filtro && !empty($arregloSelectPrecios)): ?>
                <button type="button" id="btnExportarExcel"
                        class="btn-auth d-flex justify-content-center align-items-center gap-2">
                    <i class="bi bi-file-earmark-excel"></i> Exportar Excel
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Indicador de filtros activos -->
        <?php if ($hay_filtro): ?>
        <div class="filtros-activos mb-3 d-none">
            <h6><i class="bi bi-funnel-fill"></i> Filtros activos:</h6>
            <div class="d-flex flex-wrap gap-1 align-items-center">
                <?php if ($filtro_material  !== ''): ?><span class="filtro-tag">Material: <?= htmlspecialchars($filtro_material); ?></span><?php endif; ?>
                <?php if ($filtro_clave     !== ''): ?><span class="filtro-tag">Clave: <?= htmlspecialchars($filtro_clave); ?></span><?php endif; ?>
                <?php if ($filtro_proveedor !== ''): ?><span class="filtro-tag">Proveedor: <?= htmlspecialchars($filtro_proveedor); ?></span><?php endif; ?>
                <?php if ($filtro_interior  !== ''): ?><span class="filtro-tag">Interior: <?= htmlspecialchars($filtro_interior); ?></span><?php endif; ?>
                <?php if ($filtro_exterior  !== ''): ?><span class="filtro-tag">Exterior: <?= htmlspecialchars($filtro_exterior); ?></span><?php endif; ?>
                <?php if ($ver_todo): ?><span class="filtro-tag">Todos los registros</span><?php endif; ?>
                <?php if ($filtro_sin_precio): ?><span class="filtro-tag">Claves sin precio</span><?php endif; ?>
                <a href="claves.php" class="btn btn-sm btn-outline-danger ms-1">
                    <i class="bi bi-x-circle"></i> Limpiar filtros
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabla -->
        <div class="table-container">

            <?php if (!$hay_filtro): ?>
            <!-- Estado: sin filtros activos -->
            <div class="text-center py-5 d-none">
                <i class="bi bi-search" style="font-size:3rem; color:#55ad9b; opacity:0.5;"></i>
                <p class="mt-3 text-muted">
                    Acceda al panel y use los filtros de busqueda para consultar registros.
                </p>
                <a href="panel_claves.php" class="btn-general mt-2 d-inline-flex align-items-center gap-2"
                   style="width:auto; padding:10px 24px;">
                    <i class="bi bi-arrow-left"></i> Ir al panel
                </a>
            </div>

            <?php elseif (empty($arregloSelectPrecios)): ?>
            <!-- Estado: filtros activos pero sin resultados -->
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size:3rem; color:#55ad9b; opacity:0.5;"></i>
                <p class="mt-3 text-muted">No se encontraron registros con los filtros aplicados.</p>
                <a href="panel_claves.php" class="btn-general mt-2 d-inline-flex align-items-center gap-2"
                   style="width:auto; padding:10px 24px;">
                    <i class="bi bi-arrow-left"></i> Ir al panel
                </a>
            </div>

            <?php else: ?>
            <!-- Tabla de resultados -->
            <p class="text-muted mb-2">
                <i class="bi bi-list-ol me-1"></i>
                <strong id="contadorResultados"><?= count($arregloSelectPrecios); ?></strong> registro(s) encontrado(s).
            </p>

            <table id="clavesTable" class="mainTable table table-striped table-bordered" style="width:100%;">
                <thead>
                    <tr>
                        <th></th>
                        <th>Clave</th>
                        <th>Clave Alterna</th>
                        <th>Material</th>
                        <th>Proveedor</th>
                        <th>Tipo</th>
                        <th>Interior</th>
                        <th>Exterior</th>
                        <th>Max. Length</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arregloSelectPrecios as $row): ?>
                    <tr id="tr_clave_<?= (int)$row['id']; ?>">
                        <td class="td-first-actions">
                            <div class="d-flex gap-2 container-actions">
                                <?php if (in_array($tipo_usuario, ['Administrador', 'Sistemas'], true)): ?>
                                <button class="btn-general edit-btn"
                                        title="Editar registro"
                                        data-id="<?= (int)$row['id']; ?>"
                                        data-clave="<?=           htmlspecialchars($row['clave'],           ENT_QUOTES); ?>"
                                        data-clave_alterna="<?=   htmlspecialchars($row['clave_alterna'],   ENT_QUOTES); ?>"
                                        data-proveedor="<?=       htmlspecialchars($row['proveedor'],       ENT_QUOTES); ?>"
                                        data-tipo="<?=            htmlspecialchars($row['tipo'],            ENT_QUOTES); ?>"
                                        data-material="<?=        htmlspecialchars($row['material'],        ENT_QUOTES); ?>"
                                        data-interior="<?=        htmlspecialchars($row['interior'],        ENT_QUOTES); ?>"
                                        data-exterior="<?=        htmlspecialchars($row['exterior'],        ENT_QUOTES); ?>"
                                        data-max_usable="<?=      htmlspecialchars($row['max_usable'],      ENT_QUOTES); ?>"
                                        data-precio="<?=          htmlspecialchars($row['precio'],          ENT_QUOTES); ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn-cancel delete-btn"
                                        title="Eliminar registro"
                                        data-id="<?=    (int)$row['id']; ?>"
                                        data-clave="<?= htmlspecialchars($row['clave'], ENT_QUOTES); ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="td-clave"><?=           htmlspecialchars($row['clave']); ?></td>
                        <td class="td-clave_alterna"><?=   htmlspecialchars($row['clave_alterna']); ?></td>
                        <td class="td-material"><?=   htmlspecialchars($row['material']); ?></td>
                        <td class="td-proveedor"><?=       htmlspecialchars($row['proveedor']); ?></td>
                        <td class="td-tipo"><?=       htmlspecialchars($row['tipo']); ?></td>
                        <td class="td-interior"><?=   htmlspecialchars($row['interior']); ?></td>
                        <td class="td-exterior"><?=   htmlspecialchars($row['exterior']); ?></td>
                        <td class="td-max_usable"><?= htmlspecialchars($row['max_usable']); ?></td>
                        <td class="td-precio">$ <?=     htmlspecialchars($row['precio']); ?> MXN.</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

        </div>
    </div>
</section>


<!-- ============================================================
     MODAL AGREGAR / EDITAR CLAVE SRS
     ============================================================ -->
<?php include(ROOT_PATH . 'includes/modal_clave.php'); ?>


<!-- ============================================================
     JAVASCRIPT de claves.php
     ============================================================ -->
<script>
$(document).ready(function () {
    $('.dt-length, .dt-search').wrapAll('<div class="d-flex flex-row justify-content-between"></div>');
    $('.dt-info, .dt-paging').wrapAll('<div class="d-flex flex-row justify-content-between"></div>');
    // ---- Exportar a Excel con SheetJS ----
    $('#btnExportarExcel').on('click', function () {
        var wb  = XLSX.utils.book_new();
        var $tbl = $('#clavesTable');

        if ($tbl.length === 0) {
            sweetAlertResponse('warning', 'Sin datos', 'No hay tabla visible para exportar.', 'none');
            return;
        }

        // Encabezados (omitir la columna 0 = acciones)
        var encabezados = [];
        $tbl.find('thead tr th').each(function (i) {
            if (i > 0) { encabezados.push($(this).text().trim()); }
        });

        // Filas de datos (omitir la columna 0 = acciones)
        var filas = [];
        $tbl.find('tbody tr').each(function () {
            var fila = [];
            var valido = false;
            $(this).find('td').each(function (i) {
                if (i > 0) {
                    var val = $(this).text().trim();
                    fila.push(val);
                    if (val !== '') { valido = true; }
                }
            });
            if (valido) { filas.push(fila); }
        });

        if (filas.length === 0) {
            sweetAlertResponse('warning', 'Sin datos', 'No hay registros visibles para exportar.', 'none');
            return;
        }

        var ws   = XLSX.utils.aoa_to_sheet([encabezados].concat(filas));

        // Ajuste de ancho de columnas
        var anchos = encabezados.map(function (h) {
            return { wch: Math.max(h.length + 4, 12) };
        });
        ws['!cols'] = anchos;

        XLSX.utils.book_append_sheet(wb, ws, 'Claves SRS');

        // Nombre de archivo con fecha
        var hoy    = new Date();
        var fecha  = hoy.getFullYear() + '-' +
                     String(hoy.getMonth() + 1).padStart(2, '0') + '-' +
                     String(hoy.getDate()).padStart(2, '0');
        XLSX.writeFile(wb, 'claves_srs_' + fecha + '.xlsx');
    });

});
</script>

</body>
</html>