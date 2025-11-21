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
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables -->
    <link href="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.js"></script>
    <!-- DataTables Buttons -->
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" rel="stylesheet">

    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>

    <!-- JSZip para Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

    <!-- Botones HTML5 -->
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/modal_add_billet.js'); ?>"></script>
    <!-- <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-table.css'); ?>">    -->
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css"'); ?>"> 

    <title>Inventario CNC</title>

</head>
<body class="scroll-disablado">
<style>
    .buttons-excel{
        display: none !important;
    }
    .dt-scroll{
        margin-top:10px !important;
        margin-bottom:10px !important;
    }
</style>
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<?php

// Obtener los datos del registro para edicion
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_data') {
    $id = $_GET['id'];
    $sql = "SELECT * FROM inventario_cnc WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($registro);
    exit;
}

// Verifica si se recibio el parametro 'material' mediante GET
if (isset($_GET['material']) && !empty($_GET['material']) && isset($_GET['proveedor']) && !empty($_GET['proveedor'])) {
    $material = $_GET['material'];
    $proveedor = $_GET['proveedor'];

    if($proveedor == "all"){
        $sqlInventario = "SELECT * FROM inventario_cnc WHERE material = :material ORDER BY interior DESC";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':material', $material, PDO::PARAM_STR);
    }else{
        $sqlInventario = "SELECT * FROM inventario_cnc WHERE material = :material AND proveedor = :proveedor ORDER BY interior DESC";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':material', $material, PDO::PARAM_STR);
        $stmtInventario->bindParam(':proveedor', $proveedor, PDO::PARAM_STR);
    }
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

}else if (isset($_GET['clave']) && !empty($_GET['clave'])) {
    // Eliminar todos los espacios en blanco de la clave antes de consultar
    $clave = preg_replace('/\s+/', '', trim($_GET['clave']));

    $sqlInventario = "SELECT * FROM inventario_cnc WHERE Clave = :clave ";
    $stmtInventario = $conn->prepare($sqlInventario);
    $stmtInventario->bindParam(':clave', $clave, PDO::PARAM_STR);
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

}else if (isset($_GET['lp']) && !empty($_GET['lp'])) {
    // Eliminar todos los espacios en blanco del lote pedimento antes de consultar
    $lp = preg_replace('/\s+/', '', trim($_GET['lp']));

    $sqlInventario = "SELECT * FROM inventario_cnc WHERE lote_pedimento = :lp ";
    $stmtInventario = $conn->prepare($sqlInventario);
    $stmtInventario->bindParam(':lp', $lp, PDO::PARAM_STR);
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

}else if (isset($_GET['corregir'])) {
    // esto ya no se usa porque ya se actualizaron todos los registros y porque se valida que ese campo sea correcto
    $sqlInventario = "SELECT * FROM inventario_cnc WHERE max_usable = 0.00 ";
    $stmtInventario = $conn->prepare($sqlInventario);
    //$stmtInventario->bindParam(':lp', $lp, PDO::PARAM_STR);
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

}else if (isset($_GET['pendientes'])) {
    
    $sqlInventario = "SELECT 
                            i.id, 
                            i.Clave, 
                            i.Medida, 
                            i.proveedor, 
                            i.material, 
                            i.max_usable, 
                            i.stock, 
                            i.lote_pedimento
                        ,estatus, updated_at FROM inventario_cnc i
                        LEFT JOIN parametros p ON i.Clave = p.clave
                        WHERE p.clave IS NULL OR i.estatus = 'Clave incorrecta' ORDER BY stock DESC;
                        ";
                        $stmtInventario = $conn->prepare($sqlInventario);
                        $stmtInventario->execute();
                        $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);
}else{

    $sqlInventario = "SELECT * FROM inventario_cnc ";
    $stmtInventario = $conn->prepare($sqlInventario);
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);
}
?>
<div id="overlay">
    <div class="loading-message">
        <span>Cargando datos de inventario, por favor, espere...</span>    
    </div>
</div>

<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Inventario CNC</h1>
            <div class="d-flex flex-row justify-content-start col-3">
                <button type="button" id="btnAgregar" class="btn-general d-flex justify-content-center align-items-center gap-2" 
                        data-bs-toggle="modal" data-bs-target="#modalInventario">
                    <i class="bi bi-file-plus" style="font-size:24px;"></i>
                    Agregar Registro
                </button>
            </div>
        </div>
        <div class="table-container">
            <div class="row mb-3">
                <div class="d-flex justify-content-start gap-3 col-12 col-md-8 ">
                    <button id="btnExportarDatos" type="button" 
                            class="btn btn-success" >
                        <i class="bi bi-file-earmark-spreadsheet"></i> Exportar datos
                    </button>
                </div>
            </div>
            <table id="inventarioTable" class="table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Acciones</th>
                        <th>Clave</th>
                        <th>Lote/Pedimento</th>
                        <th>Material</th>
                        <th>Proveedor</th>
                        <th>Medida</th>
                        <th>Max. Usable</th>
                        <th>Stock</th>
                        <th>Existencia</th>
                        <th>Usabilidad</th>
                        <th>Estatus</th>
                        <th>Fecha de Ingreso</th>
                        <th>Actualización</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arregloSelectInventario as $row):
                        $stock = $row['stock'];
                        $usableStyle="";
                        $usableText="";
                        // iluminar si stock es menor a 15 o esta eliminado
                        if($stock < 15 || $row['estatus'] == "Eliminado"){
                            $usableStyle = "background-color:#ff00002e !important;";
                            $usableText = "No usable";
                        }else{
                            $usableText = "Usable";
                        }
                        $max_usable = $row['max_usable'];
                        $width = $max_usable > 0 ? ($stock / $max_usable) * 100 : 0; // Calcular el porcentaje
                        // Determinar la clase de la barra según el stock
                        if ($stock >= $max_usable * 0.75) { // 75% o más
                            $class = 'bar-alto';
                        } elseif ($stock >= $max_usable * 0.25) { // Entre 25% y 75%
                            $class = 'bar-medio';
                        } else { // Menos de 25%
                            $class = 'bar-bajo';
                        }
                        
                    ?>
                    <tr id="tr_<?= $row['id']; ?>" class="fila-inventario" style="<?= $usableStyle; ?>">
                        <td class="acciones d-flex flex-column">
                            <div class="d-flex flex-column">
                                <button class="btn-general edit-btn mb-1" 
                                    data-id="<?= $row['id']; ?>"
                                    data-clave="<?= $row['Clave']; ?>"
                                    data-medida="<?= $row['Medida']; ?>"
                                    data-proveedor="<?= $row['proveedor']; ?>"
                                    data-material="<?= $row['material']; ?>"
                                    data-max_usable="<?= $row['max_usable']; ?>"
                                    data-stock="<?= $row['stock']; ?>"
                                    data-lote_pedimento="<?= $row['lote_pedimento']; ?>"
                                    data-estatus="<?= $row['estatus']; ?>"
                                >
                                    Editar<i class="bi bi-pencil-square px-2"></i>
                                </button>
                                <form class="form-delete">
                                    <button type="button" class="btn-eliminar delete-btn" data-id="<?= $row['id']; ?>">
                                        Eliminar<i class="bi bi-trash3 px-2"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <td class="td-clave"><?= htmlspecialchars($row['Clave']); ?></td>
                        <td class="td-lote"><?= htmlspecialchars($row['lote_pedimento']); ?></td>
                        <td class="td-material"><?= htmlspecialchars($row['material']); ?></td>
                        <td class="td-proveedor"><?= htmlspecialchars($row['proveedor']); ?></td>
                        <td class="td-medida"><?= htmlspecialchars($row['Medida']); ?></td>
                        <td class="td-max_usable"><?= htmlspecialchars($row['max_usable']); ?></td>
                        <td class="td-stock"><?= htmlspecialchars($row['stock']); ?></td>
                        <td class="td-barra">
                            <div class="existencia-barra">
                                <span class="bar <?= $class; ?>" style="width: <?= htmlspecialchars($width); ?>%;"></span>
                            </div>
                        </td>
                        <td class="td-usable"><?= $usableText; ?></td>
                        <td class="td-estatus"><?= htmlspecialchars($row['estatus']); ?></td>
                        <td class="td-created">
                            <?php
                                if (!empty($row['created_at'])) {
                                    echo date("d/m/Y h:i:s A", strtotime($row['created_at']));
                                } else {
                                    echo "fecha no disponible";
                                }
                            ?>
                        </td>
                        <td class="td-updated">
                            <?php
                                if (!empty($row['updated_at'])) {
                                    echo date("d/m/Y h:i:s A", strtotime($row['updated_at']));
                                } else {
                                    echo "No actualizado aún";
                                }
                            ?>
                        </td>

                    </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include(ROOT_PATH . 'includes/modal_add_billet.php'); ?>

<script>
    $(document).ready(function(){
        $("#overlay").addClass("d-none");
        $("body").removeClass("scroll-disablado");

        $('.dt-length, .dt-search').wrapAll('<div class="d-flex flex-row justify-content-between"></div>');
        $('.dt-info, .dt-paging').wrapAll('<div class="d-flex flex-row justify-content-between"></div>');

        $('#btnExportarDatos').on('click', function() {
            $(".buttons-excel").trigger("click");
        });

    });
</script>
</body>
</html>

