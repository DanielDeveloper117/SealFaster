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
    .fila-inventario .form-delete p{
        margin-bottom:0px !important;
    }
    /* Agrega esto a tu CSS si quieres */
    .img-fluid[cursor="pointer"]:hover {
        opacity: 0.8;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
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
}elseif(isset($_GET['archivados'])){
    $sqlInventario = "SELECT * FROM inventario_cnc WHERE solicita_archivado = 1 AND estatus = 'Eliminado'";
    $stmtInventario = $conn->prepare($sqlInventario);
    //$stmtInventario->bindParam(':lp', $lp, PDO::PARAM_STR);
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
            <div class="d-flex flex-row justify-content-start col-12 col-md-3">
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
                        <th>Medida</th>
                        <th>Estatus</th>
                        <th>Material</th>
                        <th>Proveedor</th>
                        <th>Max. Usable</th>
                        <th>Stock</th>
                        <th>Existencia</th>
                        <th>Usabilidad</th>
                        <th>Fecha de Ingreso</th>
                        <th>Actualización</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arregloSelectInventario as $row):
                        $stock = $row['stock'];
                        $usableStyle="";
                        $usableText="";
                        $estatusString = "";
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
                        <td class="acciones d-flex flex-column" style="<?php echo $usableStyle; ?>">
                            <div class="d-flex flex-column">
                                <?php if ($row['estatus'] != "Eliminado"): ?>
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
                                <?php endif; ?>
                                <form class="form-delete d-flex flex-column gap-2">
                                    <?php
                                    if($row['estatus'] == "Disponible para cotizar"){                                      
                                        echo '
                                            <button type="button" class="btn-eliminar delete-btn" 
                                                    data-id='.$row["id"].'
                                                    data-lp='.$row["lote_pedimento"].'
                                                    title="Archivar registro (marcar como no disponible para cotizar y solicitar archivar la barra)">
                                                Archivar<i class="bi bi-archive px-2"></i>
                                            </button>
                                        ';
                                    }else if($row['estatus'] == "Eliminado"){
                                        if($tipo_usuario === "Administrador"){
                                            echo '';
                                            if($row['archivado_auth'] == 0){
                                                echo '
                                                    <button type="button" class="btn-auth btn-autorizar-archivado" 
                                                            data-id='.$row["id"].'
                                                            data-lp='.$row["lote_pedimento"].'
                                                            title="Autorizar">
                                                        Autorizar archivado<i class="bi bi-archive-fill px-2"></i>
                                                    </button>
                                                '; 
                                            }
                                            if($row['archivado_auth'] == 1){
                                                echo '<p>';
                                                echo 'Autorizado para archivar<i class="bi bi-archive-fill px-2"></i>';
                                                echo '</p>';

                                            }
                                            echo '
                                                <button type="button" class="btn-general btn-ver-justificacion" 
                                                        data-id="'.$row["id"].'"
                                                        data-jus="'.htmlspecialchars($row["justificacion_archivado"]).'"
                                                        data-ruta="'.htmlspecialchars($row["ruta_foto_barra"] ?? '').'"
                                                        data-lote="'.htmlspecialchars($row["lote_pedimento"]).'"
                                                        data-fecha="'.htmlspecialchars($row["deleted_at"] ?? $row["updated_at"]).'"
                                                        title="Ver la justificación y fotografía de la solicitud">
                                                    Ver justificación <i class="bi bi-chat-text px-2"></i>
                                                </button>
                                            '; 
                                            
                                            
                                        }else{
                                            
                                            echo '
                                                <button type="button" class="btn-general btn-ver-justificacion" 
                                                        data-id="'.$row["id"].'"
                                                        data-jus="'.htmlspecialchars($row["justificacion_archivado"]).'"
                                                        data-ruta="'.htmlspecialchars($row["ruta_foto_barra"] ?? '').'"
                                                        data-lote="'.htmlspecialchars($row["lote_pedimento"]).'"
                                                        data-fecha="'.htmlspecialchars($row["deleted_at"] ?? $row["updated_at"]).'"
                                                        title="Ver la justificación y fotografía de la solicitud">
                                                    Ver justificación <i class="bi bi-chat-text px-2"></i>
                                                </button>
                                            '; 
                                            echo '<p>';
                                            if($row['archivado_auth'] == 0){
                                                echo 'Solicitud enviada para archivar';
                                            }elseif($row['archivado_auth'] == 1){
                                                echo 'Autorizado para archivar<i class="bi bi-archive-fill px-2"></i>';
                                                
                                            }
                                            echo '</p>';
                                        }
                                    }else{
                                        echo '<p>'.htmlspecialchars($row['estatus']).'</p>';
                                    }
                                    ?>
                                    
                                </form>
                            </div>
                        </td>
                        <td class="td-clave" style="<?php echo $usableStyle; ?>"><?= htmlspecialchars($row['Clave']); ?></td>
                        <td class="td-lote" style="<?php echo $usableStyle; ?>"><?= htmlspecialchars($row['lote_pedimento']); ?></td>
                        <td class="td-medida" style="<?php echo $usableStyle; ?>"><?= htmlspecialchars($row['Medida']); ?></td>
                        <td class="td-estatus fw-bold" style="<?php echo $usableStyle; ?>"><?php 
                        if($row['stock']==0){
                            echo("No disponible, sin stock");
                        }elseif($row['estatus']=="Eliminado"){
                            echo("Archivado");
                        }else{echo($row['estatus']);
                        } ?></td>
                        <td class="td-material" style="<?php echo $usableStyle; ?>"><?= htmlspecialchars($row['material']); ?></td>
                        <td class="td-proveedor" style="<?php echo $usableStyle; ?>"><?= htmlspecialchars($row['proveedor']); ?></td>
                        <td class="td-max_usable" style="<?php echo $usableStyle; ?>"><?= htmlspecialchars($row['max_usable']); ?></td>
                        <td class="td-stock" style="<?php echo $usableStyle; ?>"><?= htmlspecialchars($row['stock']); ?></td>
                        <td class="td-barra" style="<?php echo $usableStyle; ?>">
                            <div class="existencia-barra">
                                <span class="bar <?= $class; ?>" style="width: <?= htmlspecialchars($width); ?>%;"></span>
                            </div>
                        </td>
                        <td class="td-usable" style="<?php echo $usableStyle; ?>"><?= $usableText; ?></td>
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

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<!-- //////////////////////////MODAL: FORMULARIO SOLICITAR ARCHIVAR BARRA /////////////////////// -->
<div class="modal fade" id="modalSolicitarArchivar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Solicitar archivar barra a dirección</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Describa la razón por la cual desea archivar la barra: <strong></strong></p>
                <form id="formSolicitarArchivar" enctype="multipart/form-data">
                    <input id="inputIdBarra" type="hidden">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:100%;">
                            <label for="inputJustificacionSolicitarArchivar" class="lbl-general">Justificación *</label>
                            <textarea id="inputJustificacionSolicitarArchivar" class="form-control" rows="3" placeholder="Ingrese la justificación..." required></textarea>
                        </div>  
                    </div>
                    <div class="mb-3">
                        <label for="inputFotoArchivar" class="lbl-general">Fotografía de la barra *</label>
                        <input type="file" id="inputFotoArchivar" class="form-control" accept="image/*" capture="environment" required>
                        <small class="form-text text-muted">Suba una foto que muestre el estado actual de la barra (máx. 5MB)</small>
                        <div id="previewFotoArchivar" class="mt-2"></div>
                    </div>
                    <button id="btnContinuarSolicitarArchivar" type="button" class="btn-general">Continuar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal para ver justificación y foto -->
<div class="modal fade" id="modalVerJustificacionFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Solicitud de archivado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Justificación:</h6>
                        <div id="justificacionTexto" class="border rounded p-3 mb-3" style="min-height: 150px; max-height: 300px; overflow-y: auto;">
                            <!-- La justificación se insertará aquí -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Fotografía de la barra:</h6>
                        <div id="fotoContenedor" class="text-center">
                            <div id="sinFoto" class="d-none">
                                <i class="bi bi-image text-muted" style="font-size: 5rem;"></i>
                                <p class="text-muted mt-2">No hay fotografía disponible</p>
                            </div>
                            <img id="fotoBarra" src="" alt="Foto de la barra" 
                                 class="img-fluid rounded border" 
                                 style="max-height: 300px; display: none;">
                        </div>
                        <div id="infoFoto" class="mt-2 small text-muted">
                            <!-- Información de la foto se insertará aquí -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- ///////////////////////MODAL CONFIRMAR AUTORIZAR ARCHIVADO BARRA /////////////////////// -->
<div class="modal fade" id="modalAutorizarBarraArchivada" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-autorizar-barra-archivada" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="label-modal-autorizar-barra-archivada">Confirmar autorización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Desea continuar con la autorización para archivar esta barra?</p>
                <form id="formAutorizarBarraArchivada">
                    <input id="inputIdBarraArchivada" type="hidden" name="id"  value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnConfirmAutorizarBarraArchivada" class="btn-auth">Si, continuar</button>
                <button type="button" id="btnCancelAutorizarBarraArchivada" class="btn-cancel" data-bs-dismiss="modal">No, cancelar</button>
            </div>
        </div>
    </div>
</div>
<!-- ////////////////////////////////////////////////////////////////////////////////////////// -->
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

