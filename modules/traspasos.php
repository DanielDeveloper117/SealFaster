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

    <title>Traspasos</title>

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
$arregloSelectTraspasos = [];
// *** METODO GET DE FILTROS RECIBIDOS ***

    $sqlTraspasos = "
        SELECT 
            o.*,
            a_origen.almacen AS origen,       
            a_destino.almacen AS destino,     
            COUNT(i.id) AS cantidad_barras   
        FROM sellosyr_sellosctd.operaciones_inv AS o
        INNER JOIN sellosyr_sellosctd.almacenes AS a_origen 
            ON o.almacen_origen_id = a_origen.id
        INNER JOIN sellosyr_sellosctd.almacenes AS a_destino 
            ON o.almacen_destino_id = a_destino.id
        LEFT JOIN sellosyr_sellosctd.inventario_cnc AS i 
            ON i.operacion_id = o.id
        WHERE o.tipo = 'Traspaso'
        GROUP BY o.id
        ORDER BY o.created_at DESC";
    $stmtTraspasos = $conn->prepare($sqlTraspasos);
    $stmtTraspasos->execute();
    $arregloSelectTraspasos = $stmtTraspasos->fetchAll(PDO::FETCH_ASSOC);

?>
<div id="overlay">
    <div class="loading-message">
        <span>Cargando datos de traspasos, por favor, espere...</span>    
    </div>
</div>

<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Traspasos</h1>
            <div class="d-flex flex-row justify-content-start col-12 col-md-6 gap-2">
                <div>

                    
                </div>
                
            </div>
        </div>
        <div class="table-container">
            <table id="traspasosTable" class="mainTable table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="background-color:#55ad9b52;"></th>
                        <th>Id</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Estatus</th>
                        <th>Justificación</th>
                        <th>Cantidad Barras</th>
                        <th>Fecha Recepción</th>
                        <th>Fecha Creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arregloSelectTraspasos as $row):
                     
                    ?>
                    <tr id="tr_<?= $row['id']; ?>" class="fila-inventario" >
                        <td class="td-first-actions" >
                            <div class="d-flex gap-2 container-actions">
                                <?php
                                if($row['recibido'] == "0"
                                    && (($tipo_usuario === "Inventarios" && $rol_usuario == "Gerente") 
                                    || ($tipo_usuario === "Administrador") 
                                    || ($tipo_usuario == "Sistemas"))){                                      
                                    echo '
                                        <button type="button" class="btn-cancel delete-btn" 
                                                data-id='.htmlspecialchars($row["id"]).'
                                                title="Eliminar traspaso y liberar barras">
                                            <i class="bi bi-trash px-2"></i>
                                        </button>
                                    ';
                                }
                                ?>
                                <form action="../includes/functions/generar_traspaso.php" method="GET" target="_blank" style="width: stretch;">
                                    <input id="idTraspasoPDF" type="hidden" name="id" value="<?= htmlspecialchars($row['id']??""); ?>">
                                    <button type="submit" class="btn-pdf"
                                        title="Generar PDF del traspaso">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </button>
                                </form>
                                <?php
                                echo '<button type="button" class="btn-thunder btn-detalles" 
                                        data-id="' . htmlspecialchars($row['id']) . '"
                                        title="Detalles del traspaso">
                                        <i class="bi bi-box-seam"></i>
                                    </button>';
                                ?>
                                <?php if ($row['recibido']=="0"
                                    && (($tipo_usuario === "Inventarios" && $rol_usuario == "Gerente") 
                                    || ($tipo_usuario === "Administrador") 
                                    || ($tipo_usuario == "Sistemas"))): ?>
                                    <button type="button" class="btn-auth btn-recibir" 
                                            data-bs-toggle="modal" data-bs-target="#modalRecepcion"
                                            data-id="<?= htmlspecialchars($row['id']); ?>"
                                            title="Recibir traspaso y actualizar estatus">
                                        <i class="bi bi-clipboard2-check"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php 
                                   
                                ?>
                            </div>
                        </td>
                        <td class="td-id" ><?= htmlspecialchars($row['id']); ?></td>
                        <td class="td-origen" ><?= htmlspecialchars($row['origen']); ?></td>
                        <td class="td-destino" ><?= htmlspecialchars($row['destino']); ?></td>
                        <td class="td-estatus fw-bold" >
                            <div class="d-flex align-items-center gap-1">
                                <?php 
                                    if($row['recibido']=="0"){
                                        echo("Recepción pendiente");
                                    }else{
                                        echo("Recibido");
                                    } 
                                ?>
                            </div>
                        </td>
                        <td class="td-justificacion" ><?= htmlspecialchars($row['justificacion']); ?></td>
                        <td class="td-cantidad_barras" ><?= htmlspecialchars($row['cantidad_barras']); ?></td>
                        <td class="td-fecha_recibido" >
                            <?php
                                if (!empty($row['fecha_recibido'])) {
                                    echo date("d/m/Y h:i:s A", strtotime($row['fecha_recibido']));
                                } else {
                                    echo "No recibido aún";
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

<!-- Incluir Modal de Recepción del Traspaso -->
<?php include(ROOT_PATH . 'includes/modal_recepcion_traspaso.php'); ?>
<!-- Incluir Modal de Detalles del Traspaso -->
<?php include(ROOT_PATH . 'includes/modal_detalles_traspaso.php'); ?>

<!-- Scripts para DataTable y funcionalidades -->
<script>
    $(document).ready(function(){
       
    });
</script>
</body>
</html>

