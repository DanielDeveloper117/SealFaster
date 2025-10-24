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
// Verifica si se recibió el parámetro 'material' mediante GET
if (isset($_GET['material']) && !empty($_GET['material']) && isset($_GET['proveedor']) && !empty($_GET['proveedor'])) {
    $material = $_GET['material'];
    $proveedor = $_GET['proveedor'];

    if($proveedor == "all"){
        $sqlInventario = "SELECT id, clave, medida, proveedor, material, max_usable, pre_stock, lote_pedimento,estatus, updated_at 
                          FROM inventario_cnc WHERE material = :material";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':material', $material, PDO::PARAM_STR);
    }else{
        $sqlInventario = "SELECT id, clave, medida, proveedor, material, max_usable, pre_stock, lote_pedimento,estatus, updated_at 
        FROM inventario_cnc WHERE material = :material AND proveedor = :proveedor";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':material', $material, PDO::PARAM_STR);
        $stmtInventario->bindParam(':proveedor', $proveedor, PDO::PARAM_STR);
    }
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

}else if (isset($_GET['clave']) && !empty($_GET['clave'])) {
    $clave = $_GET['clave'];

    $sqlInventario = "SELECT id, clave, medida, proveedor, material, max_usable, pre_stock, lote_pedimento,estatus, updated_at 
                        FROM inventario_cnc WHERE clave = :clave";
    $stmtInventario = $conn->prepare($sqlInventario);
    $stmtInventario->bindParam(':clave', $clave, PDO::PARAM_STR);
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

}else if (isset($_GET['lp']) && !empty($_GET['lp'])) {
    $lp = $_GET['lp'];

    $sqlInventario = "SELECT id, clave, medida, proveedor, material, max_usable, pre_stock, lote_pedimento 
                        ,estatus, updated_at FROM inventario_cnc WHERE lote_pedimento = :lp";
    $stmtInventario = $conn->prepare($sqlInventario);
    $stmtInventario->bindParam(':lp', $lp, PDO::PARAM_STR);
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

}else{

    $sqlInventario = "SELECT id, clave, medida, proveedor, material, max_usable, pre_stock, lote_pedimento ,estatus, updated_at
                        FROM inventario_cnc";
    $stmtInventario = $conn->prepare($sqlInventario);
    $stmtInventario->execute();
    // Obtener los resultados
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div id="overlay">
    <div class="loading-message">
        <span>Cargando datos de inventario, por favor, espere...</span>    
    </div>
</div>

<section class="section-table flex-column mt-2 mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-1 mb-3">
            <h1>Inventario CNC</h1>
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
                        <th>Clave</th>
                        <th>Medida</th>
                        <th>Proveedor</th>
                        <th>Material</th>
                        <!-- <th>Máx Usable</th> -->
                        <th>Pre Stock</th> 
                        <th>Estatus</th>
                        <th>Existencia</th>
                        <!-- <th>Stock</th> -->
                        <th>Lote/Pedimento</th>
                        <th>Usabilidad</th>
                        <th>Actualización</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    foreach ($arregloSelectInventario as $row) {
                        $max_usable = $row['max_usable'];
                        $pre_stock = $row['pre_stock'];
                        $width = $max_usable > 0 ? ($pre_stock / $max_usable) * 100 : 0; // Calcular el porcentaje
                        $usableStyle="";
                        $usableText="";
                        if ($pre_stock >= $max_usable * 0.75) { 
                            $class = 'bar-alto';
                        } elseif ($pre_stock >= $max_usable * 0.25) { 
                            $class = 'bar-medio';
                        } else { 
                            $class = 'bar-bajo';
                        }
                        if($pre_stock < 15){
                            $usableStyle = "background-color:#ff00002e !important;";
                            $usableText = "No usable";
                        }else{
                            $usableText = "Usable";
                        }


                        // $stock = $row['stock'];
                        // $usableStyle="";
                        // $usableText="";
                        // // iluminar si stock es menor a 15
                        // if($stock < 15){
                        //     $usableStyle = "background-color:#ff00002e !important;";
                        //     $usableText = "No usable";
                        // }else{
                        //     $usableText = "Usable";
                        // }
                        // $max_usable = $row['max_usable'];
                        // $width = $max_usable > 0 ? ($stock / $max_usable) * 100 : 0; // Calcular el porcentaje
                        // // Determinar la clase de la barra según el stock
                        // if ($stock >= $max_usable * 0.75) { // 75% o más
                        //     $class = 'bar-alto';
                        // } elseif ($stock >= $max_usable * 0.25) { // Entre 25% y 75%
                        //     $class = 'bar-medio';
                        // } else { // Menos de 25%
                        //     $class = 'bar-bajo';
                        // }
                ?>
                    <tr style="<?php echo $usableStyle; ?>" >
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['clave']); ?></td>
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['medida']); ?></td>
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['proveedor']); ?></td>
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['material']); ?></td>
                        <!-- <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['max_usable']); ?></td> -->
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['pre_stock']); ?></td>
                        <td style="<?php echo $usableStyle; ?>" class="td-estatus"><?= htmlspecialchars($row['estatus']); ?></td>
                        <td style="<?php echo $usableStyle; ?>">
                            <div class="existencia-barra">
                                <span class="bar <?php echo $class; ?>" style="width: <?php echo htmlspecialchars($width); ?>%;"></span>
                            </div>
                        </td>
                        <!-- <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['stock']); ?></td> -->
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['lote_pedimento']); ?></td>
                        <td style="<?php echo $usableStyle; ?>"><?php echo $usableText; ?></td>
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
                <?php
                    }
                ?>

                </tbody>
            </table>
        </div>
    </div>
</section>

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
