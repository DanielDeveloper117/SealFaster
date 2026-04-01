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

    <title>Almacenes</title>

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
    $arregloSelectAlmacenes = [];
    // *** METODO GET DE FILTROS RECIBIDOS ***

    $sqlAlmacenes = "
        SELECT *
        FROM almacenes
        ORDER BY created_at DESC
    ";
    $stmtAlmacenes = $conn->prepare($sqlAlmacenes);
    $stmtAlmacenes->execute();
    $arregloSelectAlmacenes = $stmtAlmacenes->fetchAll(PDO::FETCH_ASSOC);

?>
<div id="overlay">
    <div class="loading-message">
        <span>Cargando datos de almacenes, por favor, espere...</span>    
    </div>
</div>

<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Almacenes</h1>
            <div class="d-flex flex-row justify-content-start col-12 col-md-6 gap-2">
                <div>
                    <?php if ((($tipo_usuario === "Inventarios" && $rol_usuario == "Gerente") 
                            || ($tipo_usuario === "Administrador") 
                            || ($tipo_usuario == "Sistemas"))): ?>
                        <button type="button" class="btnAgregarAlmacen btn-general d-flex justify-content-center align-items-center gap-2" 
                                data-bs-toggle="modal" data-bs-target="#modalAlmacen">
                            <i class="bi bi-file-plus" style="font-size:24px;"></i>
                            Agregar Registro
                        </button>
                    <?php endif; ?>
                </div>
                
            </div>
        </div>
        <div class="table-container">
            <table id="almacenesTable" class="mainTable table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="background-color:#55ad9b52;"></th>
                        <th>Id</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Actualización</th>
                        <th>Fecha Creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arregloSelectAlmacenes as $row):
                     
                    ?>
                    <tr id="tr_<?= $row['id']; ?>" class="fila-inventario" >
                        <td class="td-first-actions" >
                            <div class="d-flex gap-2 container-actions">
                                <?php if ((($tipo_usuario === "Inventarios" && $rol_usuario == "Gerente") 
                                        || ($tipo_usuario === "Administrador") 
                                        || ($tipo_usuario == "Sistemas"))): ?>
                                    <button class="btn-general edit-btn" 
                                        data-id="<?= $row['id']; ?>"
                                        data-almacen="<?= $row['almacen']; ?>"
                                        data-descripcion="<?= $row['descripcion']; ?>"
                                    >
                                        <i class="bi bi-pencil-square px-2"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="td-id" ><?= htmlspecialchars($row['id']); ?></td>
                        <td class="td-almacen" ><?= htmlspecialchars($row['almacen']); ?></td>
                        <td class="td-justificacion" ><?= htmlspecialchars($row['descripcion']); ?></td>
                        <td class="td-updated" > 
                            <?php
                                if (!empty($row['updated_at'])) {
                                    echo date("d/m/Y h:i:s A", strtotime($row['updated_at']));
                                } else {
                                    echo "fecha no disponible";
                                }
                            ?>
                        </td>
                        <td class="td-created" > 
                            <?php
                                if (!empty($row['created_at'])) {
                                    echo date("d/m/Y h:i:s A", strtotime($row['created_at']));
                                } else {
                                    echo "fecha no disponible";
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

<!-- Incluir Modal de Detalles de venta-->
<?php include(ROOT_PATH . 'includes/modal_almacen.php'); ?>

<!-- Scripts para DataTable y funcionalidades -->
<script>
    $(document).ready(function(){
       
    });
</script>
</body>
</html>

