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
    <!-- jQuery -->
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>

    <!-- Bootstrap -->
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>"></script>

    <!-- SweetAlert -->
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/bootstrap-icons.min.css'); ?>">

    <!-- DataTables -->
    <link href="<?= controlCache('../assets/dependencies/datatables.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/datatables.min.js'); ?>"></script>

    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css"'); ?>"> 
    
    <title>Inventario CNC</title>

    <?php include(ROOT_PATH . 'includes/exportar_datatable_excel.php'); ?>
</head>

<body>
<style>
    .dt-scroll{
        margin-top:10px !important;
        margin-bottom:10px !important;
    }
</style>
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<?php
    $arregloSelectInventario = [];
    // *** METODO GET DE FILTROS RECIBIDOS ***
    if (isset($_GET['origen']) && !empty($_GET['origen']) && isset($_GET['material']) && !empty($_GET['material']) && isset($_GET['proveedor']) && !empty($_GET['proveedor'])) {
        $origen = $_GET['origen'];
        $material = $_GET['material'];
        $proveedor = $_GET['proveedor'];

        if($proveedor == "all"){
            $sqlInventario = "
                SELECT i.*, 
                        (CASE WHEN i.almacen_id = a.id THEN a.almacen ELSE 'Desconocido' END) AS almacen
                FROM sellosyr_sellosctd.inventario_cnc AS i
                INNER JOIN sellosyr_sellosctd.almacenes AS a
                    ON i.almacen_id = a.id
                WHERE i.almacen_id = :origen AND i.material = :material ORDER BY i.stock ASC
            ";
            $stmtInventario = $conn->prepare($sqlInventario);
            $stmtInventario->bindParam(':origen', $origen, PDO::PARAM_STR);
            $stmtInventario->bindParam(':material', $material, PDO::PARAM_STR);
        }else{
            $sqlInventario = "
                SELECT i.*, 
                        (CASE WHEN i.almacen_id = a.id THEN a.almacen ELSE 'Desconocido' END) AS almacen
                FROM sellosyr_sellosctd.inventario_cnc AS i
                INNER JOIN sellosyr_sellosctd.almacenes AS a
                    ON i.almacen_id = a.id
                WHERE i.almacen_id = :origen AND i.material = :material AND i.proveedor = :proveedor 
                ORDER BY i.stock ASC
            ";        
            $stmtInventario = $conn->prepare($sqlInventario);
            $stmtInventario->bindParam(':origen', $origen, PDO::PARAM_STR);
            $stmtInventario->bindParam(':material', $material, PDO::PARAM_STR);
            $stmtInventario->bindParam(':proveedor', $proveedor, PDO::PARAM_STR);
        }
        $stmtInventario->execute();
        $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

    }else if (isset($_GET['origen']) && !empty($_GET['origen']) && isset($_GET['clave']) && !empty($_GET['clave'])) {
        // Eliminar todos los espacios en blanco de la clave antes de consultar
        $clave = preg_replace('/\s+/', '', trim($_GET['clave']));

        $sqlInventario = "
            SELECT i.*, 
                    (CASE WHEN i.almacen_id = a.id THEN a.almacen ELSE 'Desconocido' END) AS almacen
            FROM sellosyr_sellosctd.inventario_cnc AS i
            INNER JOIN sellosyr_sellosctd.almacenes AS a
                ON i.almacen_id = a.id
            WHERE i.almacen_id = :origen AND i.Clave = :clave
            ORDER BY i.stock ASC
        ";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':origen', $_GET['origen'], PDO::PARAM_STR);
        $stmtInventario->bindParam(':clave', $clave, PDO::PARAM_STR);
        $stmtInventario->execute();
        $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

    }else if (isset($_GET['lp']) && !empty($_GET['lp'])) {
        // Eliminar todos los espacios en blanco del lote pedimento antes de consultar
        $lp = preg_replace('/\s+/', '', trim($_GET['lp']));

        $sqlInventario = "SELECT id, almacen_id, lote_pedimento FROM inventario_cnc WHERE lote_pedimento = :lp ";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':lp', $lp, PDO::PARAM_STR);
        $stmtInventario->execute();
        $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

        $origen = $arregloSelectInventario[0]['almacen_id'] ?? null; // Obtener el origen del primer resultado
        $sqlInventario = "
        SELECT i.*, 
            (CASE WHEN i.almacen_id = a.id THEN a.almacen ELSE 'Desconocido' END) AS almacen
        FROM sellosyr_sellosctd.inventario_cnc AS i
        INNER JOIN sellosyr_sellosctd.almacenes AS a
            ON i.almacen_id = a.id
        WHERE i.almacen_id = :origen AND i.lote_pedimento = :lp";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':lp', $lp, PDO::PARAM_STR);
        $stmtInventario->bindParam(':origen', $origen, PDO::PARAM_STR);
        $stmtInventario->execute();
        $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

    }else if (isset($_GET['pendientes'])) {
        
        $sqlInventario = 
            "SELECT 
                i.id, 
                i.almacen_id,
                i.Clave, 
                i.Medida, 
                i.proveedor, 
                i.material, 
                i.max_usable, 
                i.stock, 
                i.lote_pedimento,
                i.estatus, 
                i.updated_at,
                (CASE WHEN i.almacen_id = a.id THEN a.almacen ELSE 'Desconocido' END) AS almacen
            FROM inventario_cnc AS i
            INNER JOIN sellosyr_sellosctd.almacenes AS a
                ON i.almacen_id = a.id
            LEFT JOIN parametros p ON i.Clave = p.clave
            WHERE p.clave IS NULL OR i.estatus = 'Clave incorrecta' 
            ORDER BY i.interior DESC;
        ";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->execute();
        $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);
    }elseif(isset($_GET['archivados'])){
        $sqlInventario = "
            SELECT i.*, 
                (CASE WHEN i.almacen_id = a.id THEN a.almacen ELSE 'Desconocido' END) AS almacen
            FROM sellosyr_sellosctd.inventario_cnc AS i
            INNER JOIN sellosyr_sellosctd.almacenes AS a
                ON i.almacen_id = a.id
            WHERE (i.solicita_archivado = 1 AND i.estatus = 'Eliminado') OR i.estatus = 'Venta' 
            ORDER BY i.interior DESC
        ";
        $stmtInventario = $conn->prepare($sqlInventario);
        //$stmtInventario->bindParam(':lp', $lp, PDO::PARAM_STR);
        $stmtInventario->execute();
        $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);
    }elseif(isset($_GET['data']) && $_GET['data'] == "all" && isset($_GET['origen']) && !empty($_GET['origen'])){

        $sqlInventario = "
            SELECT i.*, 
                (CASE WHEN i.almacen_id = a.id THEN a.almacen ELSE 'Desconocido' END) AS almacen
            FROM sellosyr_sellosctd.inventario_cnc AS i
            INNER JOIN sellosyr_sellosctd.almacenes AS a
                ON i.almacen_id = a.id
            WHERE i.almacen_id = :origen
            ORDER BY i.interior DESC";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':origen', $_GET['origen'], PDO::PARAM_STR);
        $stmtInventario->execute();
        $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);
    }else{
        $arregloSelectInventario = [];
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
            <table id="inventarioTable" class="mainTable table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Lote</th>
                        <th>Medida</th>
                        <th>Estatus</th>
                        <th>Proveedor</th>
                        <th>Material</th>
                        <!-- <th>Máx Usable</th> -->
                        <th>Pre Stock</th> 
                        <th>Existencia</th>
                        <!-- <th>Stock</th> -->
                        <th>Usabilidad</th>
                        <th>Almacén</th>
                        <th>Fecha de Ingreso</th>
                        <th>Actualización</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    foreach ($arregloSelectInventario as $row) {
                        $max_usable = $row['max_usable'];
                        $stock = $row['pre_stock'];
                        $width = $max_usable > 0 ? ($stock / $max_usable) * 100 : 0; // Calcular el porcentaje
                        $usableStyle="";
                        $usableText="";
                        if ($stock >= $max_usable * 0.75) { 
                            $class = 'bar-alto';
                        } elseif ($stock >= $max_usable * 0.25) { 
                            $class = 'bar-medio';
                        } else { 
                            $class = 'bar-bajo';
                        }
                        // iluminar segun su estatus y stock
                        if($stock == 0){
                            $usableStyle = "background-color:#ff00002e !important;";
                            $usableText = "No usable";
                        }elseif($row['estatus'] != "Eliminado" && $stock > 0 && $stock < 15){
                            $usableText = "No usable";
                            $usableStyle = "background-color:#ff572263 !important;";
                        }elseif($row['estatus'] == "Eliminado"){
                            $usableText = "No usable";
                            $usableStyle = "background-color:#9e9e9e90 !important;";
                        }else{
                            $usableText = "Usable";
                        }

                ?>
                    <tr style="<?php echo $usableStyle; ?>" >
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['Clave']); ?></td>
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['lote_pedimento']); ?></td>
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['Medida']); ?></td>
                        <td class="td-estatus fw-bold" style="<?php echo $usableStyle; ?>">
                            <div class="d-flex align-items-center gap-1">
                                <?php 
                                    if($row['stock']==0){
                                        echo("No disponible, sin stock");
                                    }elseif($row['estatus']=="Eliminado"){
                                        echo("Archivado");
                                    }else{
                                        echo($row['estatus']);
                                    } 
                                ?>
                                <?php if ($row['estatus']=="En uso" || $row['estatus']=="Maquinado en curso"): ?>
                                    <button class="btn btn-md btn-outline-success btn-localizacion" 
                                            data-lote="<?= htmlspecialchars($row['lote_pedimento']??""); ?>" 
                                            title="Localizar barra">
                                        <i class="bi bi-search fw-bold "></i>
                                    </button>
                                <?php endif ?>
                            </div>
                        </td>
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['proveedor']); ?></td>
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['material']); ?></td>
                        <!-- <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['max_usable']); ?></td> -->
                        <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['pre_stock']); ?></td> 
                        <td style="<?php echo $usableStyle; ?>">
                            <div class="existencia-barra">
                                <span class="bar <?php echo $class; ?>" style="width: <?php echo htmlspecialchars($width); ?>%;"></span>
                            </div>
                        </td>
                        <!-- <td style="<?php echo $usableStyle; ?>"><?php echo htmlspecialchars($row['stock']); ?></td> -->
                        <td style="<?php echo $usableStyle; ?>"><?php echo $usableText; ?></td>
                        <td class="td-almacen" style="<?php echo $usableStyle; ?>"><?= htmlspecialchars($row['almacen']); ?></td>
                        <td class="td-created" style="<?php echo $usableStyle; ?>">
                            <?php
                                if (!empty($row['created_at'])) {
                                    echo date("d/m/Y h:i:s A", strtotime($row['created_at']));
                                } else {
                                    echo "fecha no disponible";
                                }
                            ?>
                        </td>
                        <td class="td-updated" style="<?php echo $usableStyle; ?>">
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

<?php include(ROOT_PATH . 'includes/modal_localizar_barra.php'); ?>

<script>
    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function(){
        // =================================
        //  ****** INICIALIZACIONES ****** 
        $.ajax({
            url: "../ajax/ajax_notificacion.php",
            type: "POST",
            data: { mensaje: "Se ha cargado el inventario vn" },
            success: function(response) {
                console.log("Notificacion enviada: ", response);
            },
            error: function(error) {
                console.error("Error al enviar la notificacion: ", error);
            }
        });
        // =================================
    });
</script>
</body>
</html>
