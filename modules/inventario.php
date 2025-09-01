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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.js"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/modal_add_billet.js'); ?>"></script>
    <!-- <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-table.css'); ?>">    -->
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css"'); ?>"> 

    <title>Inventario CNC</title>

</head>
<body class="scroll-disablado">

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<?php

// Obtener los datos del registro para edicion
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_data') {
    $id = $_GET['id'];
    $sql = "SELECT * ,estatus FROM inventario_cnc WHERE id = :id";
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
        $sqlInventario = "SELECT id, clave, medida, proveedor, material, max_usable, stock, lote_pedimento, interior 
                          ,estatus FROM inventario_cnc WHERE material = :material ORDER BY interior DESC";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':material', $material, PDO::PARAM_STR);
    }else{
        $sqlInventario = "SELECT id, clave, medida, proveedor, material, max_usable, stock, lote_pedimento, interior 
        ,estatus FROM inventario_cnc WHERE material = :material AND proveedor = :proveedor ORDER BY interior DESC";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':material', $material, PDO::PARAM_STR);
        $stmtInventario->bindParam(':proveedor', $proveedor, PDO::PARAM_STR);
    }
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

}else if (isset($_GET['clave']) && !empty($_GET['clave'])) {
    $clave = $_GET['clave'];

    $sqlInventario = "SELECT id, clave, medida, proveedor, material, max_usable, stock, lote_pedimento 
                        ,estatus FROM inventario_cnc WHERE clave = :clave";
    $stmtInventario = $conn->prepare($sqlInventario);
    $stmtInventario->bindParam(':clave', $clave, PDO::PARAM_STR);
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

}else if (isset($_GET['lp']) && !empty($_GET['lp'])) {
    $lp = $_GET['lp'];

    $sqlInventario = "SELECT id, clave, medida, proveedor, material, max_usable, stock, lote_pedimento 
                        ,estatus FROM inventario_cnc WHERE lote_pedimento = :lp";
    $stmtInventario = $conn->prepare($sqlInventario);
    $stmtInventario->bindParam(':lp', $lp, PDO::PARAM_STR);
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

}else if (isset($_GET['corregir'])) {

    $sqlInventario = "SELECT id, clave, medida, proveedor, material, max_usable, stock, lote_pedimento 
                        ,estatus FROM inventario_cnc WHERE max_usable = 0.00";
    $stmtInventario = $conn->prepare($sqlInventario);
    //$stmtInventario->bindParam(':lp', $lp, PDO::PARAM_STR);
    $stmtInventario->execute();
    $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

}else if (isset($_GET['pendientes'])) {
    
    $sqlInventario = "SELECT 
                            i.id, 
                            i.clave, 
                            i.medida, 
                            i.proveedor, 
                            i.material, 
                            i.max_usable, 
                            i.stock, 
                            i.lote_pedimento
                        ,estatus FROM inventario_cnc i
                        LEFT JOIN parametros p ON i.clave = p.clave
                        WHERE p.clave IS NULL ORDER BY stock DESC;
                        ";
                        $stmtInventario = $conn->prepare($sqlInventario);
                        $stmtInventario->execute();
                        $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);
}else{

    $sqlInventario = "SELECT id, clave, medida, proveedor, material, max_usable, stock, lote_pedimento 
                        ,estatus FROM inventario_cnc";
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
                <button type="button" id="btnAgregar" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalInventario">Agregar Registro</button>
            </div>
        </div>
        <div class="table-container">
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arregloSelectInventario as $row):
                        $stock = $row['stock'];
                        $usableStyle="";
                        $usableText="";
                        // iluminar si stock es menor a 15
                        if($stock < 15){
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
                    <tr style="<?= $usableStyle; ?>" >
                        <td class="d-fex flex-column">
                            <div class="d-flex flex-column">
                                <button class="btn-general edit-btn mb-1" 
                                    data-id="<?= $row['id']; ?>"
                                    data-clave="<?= $row['clave']; ?>"
                                    data-medida="<?= $row['medida']; ?>"
                                    data-proveedor="<?= $row['proveedor']; ?>"
                                    data-material="<?= $row['material']; ?>"
                                    data-max_usable="<?= $row['max_usable']; ?>"
                                    data-stock="<?= $row['stock']; ?>"
                                    data-lote_pedimento="<?= $row['lote_pedimento']; ?>"
                                    >Editar</button>
                                <form class="form-delete">
                                    <button type="button" class="btn-eliminar delete-btn" data-id="<?= $row['id']; ?>">Eliminar</button>
                                </form>
                            </div>
                        </td>
                        <td style="<?= $usableStyle; ?>"><?= htmlspecialchars($row['clave']); ?></td>
                        <td style="<?= $usableStyle; ?>"><?= htmlspecialchars($row['lote_pedimento']); ?></td>
                        <td style="<?= $usableStyle; ?>"><?= htmlspecialchars($row['material']); ?></td>
                        <td style="<?= $usableStyle; ?>"><?= htmlspecialchars($row['proveedor']); ?></td>
                        <td style="<?= $usableStyle; ?>"><?= htmlspecialchars($row['medida']); ?></td>
                        <td style="<?= $usableStyle; ?>"><?= htmlspecialchars($row['max_usable']); ?></td>
                        <td style="<?= $usableStyle; ?>"><?= htmlspecialchars($row['stock']); ?></td>
                        <td style="<?= $usableStyle; ?>">
                            <div class="existencia-barra">
                                <span class="bar <?= $class; ?>" style="width: <?= htmlspecialchars($width); ?>%;"></span>
                            </div>
                        </td>
                        <td style="<?= $usableStyle; ?>"><?= $usableText; ?></td>
                        <td style="<?= $usableStyle; ?>"><?= htmlspecialchars($row['estatus']); ?> para cotizar</td>
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
    });
</script>
</body>
</html>

