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
    
    <?php include(ROOT_PATH . 'includes/exportar_datatable_excel.php'); ?>
    
    <title>Inventario CNC</title>

</head>
<body>
    <div id="overlay">
        <div class="loading-message">
            <span>Cargando datos de inventario, por favor, espere...</span>    
        </div>
    </div>
<style>
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

    /* Estilo para wrapper del checkbox */
    .checkbox-wrapper {
        position: relative;
        display: flex; 
        align-items: stretch;
        height: stretch; 
    }

    .badge-checkbox {
        position: absolute;
        top: 0px;
        right: 6px;
        font-size: 32px !important;
        pointer-events: none;
    }
</style>
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<?php
    // *** METODO GET DE FILTROS RECIBIDOS ***
    $arregloSelectInventario = [];
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
                i.id, i.almacen_id, i.Clave, i.Medida, i.proveedor, 
                i.material, i.max_usable, i.stock, i.lote_pedimento,
                i.estatus, i.updated_at, a.almacen
            FROM sellosyr_sellosctd.inventario_cnc AS i
            INNER JOIN sellosyr_sellosctd.almacenes AS a 
                ON i.almacen_id = a.id
            WHERE 
                -- Condición 1: El estatus es incorrecto
                (i.estatus = 'Clave incorrecta' OR i.estatus = 'Clave nueva pendiente' OR i.estatus = 'Relación pendiente')
                OR 
                -- Condición 2: NO existe ni en clave ni en clave_alterna
                NOT EXISTS (
                    SELECT 1 FROM sellosyr_sellosctd.parametros p 
                    WHERE p.clave = i.Clave OR p.clave_alterna = i.Clave
                )
            ORDER BY i.id DESC -- Cambiado de 'interior' a 'id' para usar índice primario
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

    }elseif(isset($_GET['traspaso']) && !empty($_GET['traspaso'])){
        
        $sqlInventario = "
            SELECT i.*, 
                (CASE WHEN i.almacen_id = a.id THEN a.almacen ELSE 'Desconocido' END) AS almacen
            FROM sellosyr_sellosctd.inventario_cnc AS i
            INNER JOIN sellosyr_sellosctd.almacenes AS a
                ON i.almacen_id = a.id
            WHERE i.operacion_id = :traspaso
            ORDER BY i.interior DESC";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':traspaso', $_GET['traspaso'], PDO::PARAM_STR);
        $stmtInventario->execute();
        $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

    }elseif(isset($_GET['venta']) && !empty($_GET['venta'])){
        
        $sqlInventario = "
            SELECT i.*, 
                (CASE WHEN i.almacen_id = a.id THEN a.almacen ELSE 'Desconocido' END) AS almacen
            FROM sellosyr_sellosctd.inventario_cnc AS i
            INNER JOIN sellosyr_sellosctd.almacenes AS a
                ON i.almacen_id = a.id
            WHERE i.operacion_id = :venta
            ORDER BY i.interior DESC";
        $stmtInventario = $conn->prepare($sqlInventario);
        $stmtInventario->bindParam(':venta', $_GET['venta'], PDO::PARAM_STR);
        $stmtInventario->execute();
        $arregloSelectInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);

    }else{
        $arregloSelectInventario = [];
    }
?>

<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Inventario CNC</h1>
            <div class="d-flex flex-row justify-content-start col-12 col-md-6 gap-2">
                <div>

                    <button type="button" id="btnAgregar" class="btn-general d-flex justify-content-center align-items-center gap-2" 
                            data-bs-toggle="modal" data-bs-target="#modalInventario">
                        <i class="bi bi-file-plus" style="font-size:24px;"></i>
                        Agregar Registro
                    </button>
                </div>
                <?php if (!isset($_GET['pendientes']) 
                            && !isset($_GET['archivados']) 
                            && !isset($_GET['traspaso'])
                            && !isset($_GET['venta'])  
                            && (($tipo_usuario === "Inventarios" && $rol_usuario == "Gerente") 
                                || ($tipo_usuario === "Administrador") 
                                || ($tipo_usuario == "Sistemas"))): ?>
                    <a id="btnInitOperacion" class="btn-unlink d-flex justify-content-center align-items-center gap-2" href="#">
                        <i class="bi bi-box-seam" style="font-size:24px;"></i>
                        Iniciar Operación
                    </a>
                <?php endif; ?>
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
            <table id="inventarioTable" class="mainTable table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="background-color:#55ad9b52;"></th>
                        <th>Clave</th>
                        <th>Lote</th>
                        <th>Medida</th>
                        <th>Estatus</th>
                        <th>Material</th>
                        <th>Proveedor</th>
                        <th>Max. Usable</th>
                        <th>Stock</th>
                        <th>Existencia</th>
                        <th>Usabilidad</th>
                        <th>Almacén</th>
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
                        <td class="td-first-actions" style="<?php echo $usableStyle; ?>">
                            <div class="d-flex gap-2 container-actions">
                                <?php if (isset($_GET['oper']) 
                                        && $_GET['oper'] == '1' 
                                        && $row['estatus'] == "Disponible para cotizar" 
                                        && $row['stock']!=0
                                        && (($tipo_usuario == "Inventarios" && $rol_usuario == "Gerente")
                                            || $tipo_usuario == "Administrador") ): ?>
                                    <div class="checkbox-wrapper">
                                        <input
                                            type="checkbox"
                                            class="btn-check-cute"
                                            val="<?= htmlspecialchars($row['id']); ?>"
                                            data-lp="<?= htmlspecialchars($row['lote_pedimento']); ?>"
                                            data-almacen-id="<?= htmlspecialchars($row['almacen_id']); ?>"
                                            title="Seleccionar barra <?= htmlspecialchars($row['lote_pedimento']); ?>"
                                        />
                                        <i class="bi bi-check2 badge-checkbox"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($row['estatus'] != "Eliminado" 
                                        && $row['estatus'] != "En uso" 
                                        && $row['estatus'] != "Traspaso"
                                        && $row['estatus'] != "Venta"
                                        && $row['estatus'] != "Maquinado en curso"): ?>
                                    <button class="btn-general edit-btn" 
                                        data-id="<?= $row['id']; ?>"
                                        data-almacen_id="<?= $row['almacen_id']; ?>"
                                        data-clave="<?= $row['Clave']; ?>"
                                        data-medida="<?= $row['Medida']; ?>"
                                        data-proveedor="<?= $row['proveedor']; ?>"
                                        data-material="<?= $row['material']; ?>"
                                        data-max_usable="<?= $row['max_usable']; ?>"
                                        data-stock="<?= $row['stock']; ?>"
                                        data-lote_pedimento="<?= $row['lote_pedimento']; ?>"
                                        data-estatus="<?= $row['estatus']; ?>"
                                    >
                                        <i class="bi bi-pencil-square px-2"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <?php
                                if($row['estatus'] == "Disponible para cotizar"){                                      
                                    echo '
                                        <button type="button" class="btn-eliminar delete-btn" 
                                                data-id='.$row["id"].'
                                                data-lp='.$row["lote_pedimento"].'
                                                title="Archivar registro (marcar como no disponible para cotizar y solicitar archivar la barra)">
                                            <i class="bi bi-archive px-2"></i>
                                        </button>
                                    ';
                                }else if($row['estatus'] == "Eliminado"){
                                    if($tipo_usuario === "Administrador"){
                                        echo '
                                            <button type="button" class="btn-general btn-ver-justificacion" 
                                                    data-id="'.$row["id"].'"
                                                    data-jus="'.htmlspecialchars($row["justificacion_archivado"]).'"
                                                    data-ruta="'.htmlspecialchars($row["ruta_foto_barra"] ?? '').'"
                                                    data-lote="'.htmlspecialchars($row["lote_pedimento"]).'"
                                                    data-fecha="'.htmlspecialchars($row["deleted_at"] ?? $row["updated_at"]).'"
                                                    title="Ver la justificación y fotografía de la solicitud">
                                                 <i class="bi bi-chat-text px-2"></i>
                                            </button>
                                        '; 
                                        if($row['archivado_auth'] == 0){
                                            echo '
                                                <button type="button" class="btn-auth btn-autorizar-archivado gap-2" 
                                                        data-id='.$row["id"].'
                                                        data-lp='.$row["lote_pedimento"].'
                                                        title="Autorizar el archivado de esta barra">
                                                    <i class="bi bi-archive-fill px-2"></i><i class="bi bi-check-circle"></i>
                                                </button>
                                            '; 
                                        }
                                        if($row['archivado_auth'] == 1){
                                            $htmlP = '
                                                <p>
                                                Autorizado para archivar<i class="bi bi-archive-fill px-2"></i>
                                                </p>
                                            ';

                                        }
                                        
                                        
                                        
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
                                        $htmlP = '<p>';
                                        if($row['archivado_auth'] == 0){
                                            $htmlP .=  'Solicitud enviada para archivar';
                                        }elseif($row['archivado_auth'] == 1){
                                            $htmlP .= 'Autorizado para archivar<i class="bi bi-archive-fill px-2"></i>';
                                        }
                                        $htmlP .= '</p>';
                                    }
                                }else{
                                    $htmlP =  '<p>'.htmlspecialchars($row['estatus']).'</p>';
                                }
                                ?>
                            </div>
                            <div>
                                <?php 
                                    if(isset($htmlP)){
                                        echo $htmlP;
                                        unset($htmlP);
                                    }
                                ?>
                            </div>
                        </td>
                        <td class="td-clave" style="<?php echo $usableStyle; ?>"><?= htmlspecialchars($row['Clave']); ?></td>
                        <td class="td-lote" style="<?php echo $usableStyle; ?>"><?= htmlspecialchars($row['lote_pedimento']); ?></td>
                        <td class="td-medida" style="<?php echo $usableStyle; ?>"><?= htmlspecialchars($row['Medida']); ?></td>
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

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<div id="agrupacionBar" class="agrupacion-bar d-none">
    <span class="agrupacion-texto">Seleccione las barras a las que desea aplicar una operacion de almacen</span>
    <div class="d-flex flex-column flex-md-row gap-3">
        <button id="btnContinuarOperacion" class="btn-general">Continuar</button>
        <button id="btnCancelOperacion" type="button" class="btn btn-secondary">
            Cancelar
        </button>
    </div>
</div>

<!-- ////////////////////////////////////////////////////////////////////////////////////////// -->
<?php include(ROOT_PATH . 'includes/modales_actions_billet.php'); ?>
<script src="<?= controlCache('../assets/js/modales_actions_billet.js'); ?>"></script>
<?php include(ROOT_PATH . 'includes/modal_operacion_inventario.php'); ?>
<?php include(ROOT_PATH . 'includes/modal_localizar_barra.php'); ?>

<script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    const urlInicial = new URL(window.location.href);
    const params = new URLSearchParams(window.location.search);
    const operActual = urlInicial.searchParams.get('oper') || '0';

    let selectedLotes = []; // Array para almacenar los lotes pedimento seleccionados
    if (params.get('oper') === '1') {
        document.querySelectorAll('.btn-check-cute').forEach(el => {
            el.classList.remove('d-none');
            // Forzar reflow para que la transición se aplique correctamente
            void el.offsetWidth;
            el.classList.add('show-cute');
        });
        const bar = document.getElementById('agrupacionBar');
        if (bar) {
            bar.classList.remove('d-none');
            // Forzar reflow para activar animación
            void bar.offsetWidth;
            bar.classList.add('show-bar');
        }
    }


    // ============================================================
    //              ******** FUNCIONES ********
    // ============================================================  
    /**
     * Crea una URL con el parámetro 'oper' modificado
     * @param {string} valorOper - El valor del parámetro oper
     * @returns {string} URL modificada
     */
    function crearHrefConOper(valorOper) {
        const nuevaUrl = new URL(urlInicial);
        nuevaUrl.searchParams.set('oper', valorOper);
        return nuevaUrl.toString();
    }
    /**
     * Actualiza la visibilidad de los checkboxes según el modo de operación
     */
    function mostrarCheckboxesPorOper(oper) {
        const checkboxes = document.querySelectorAll('.btn-check-cute');

        if (oper === '1') {
            checkboxes.forEach(checkbox => {
                checkbox.classList.remove('d-none');
            });
        } else {
            checkboxes.forEach(checkbox => {
                checkbox.classList.add('d-none');
            });
        }
    }
    /**
     * Recopila los lotes pedimento de las barras seleccionadas
     * @returns {Array} Array de lotes pedimento seleccionados
     */
    function obtenerLotesSeleccionados() {
        const lotes = [];
        document.querySelectorAll('.btn-check-cute:checked').forEach(checkbox => {
            const lp = checkbox.getAttribute('data-lp');
            if (lp) {
                lotes.push(lp);
            }
        });
        return lotes;
    }
    /**
     * Recopila los IDs de las barras seleccionadas
     * @returns {Array} Array de IDs seleccionados
     */
    function obtenerIdsSeleccionados() {
        const ids = [];
        document.querySelectorAll('.btn-check-cute:checked').forEach(checkbox => {
            const id = checkbox.getAttribute('val');
            if (id) {
                ids.push(id);
            }
        });
        return ids;
    }
    /**
     * Obtiene el almacen_id de la primera barra seleccionada
     * @returns {string|null} ID del almacén o null si no hay barras seleccionadas
     */
    function obtenerAlmacenIdSeleccionado() {
        const checkbox = document.querySelector('.btn-check-cute:checked');
        if (checkbox) {
            // El almacen_id debe estar en el atributo data-almacen-id
            return checkbox.getAttribute('data-almacen-id');
        }
        return null;
    }
    /**
     * Actualiza la visibilidad de la barra de operación
     */
    function actualizarBarraOperacion() {
        const checkboxesChecked = document.querySelectorAll('.btn-check-cute:checked').length;
        const agrupacionBar = document.getElementById('agrupacionBar');

        if (checkboxesChecked > 0) {
            agrupacionBar.classList.remove('d-none');
        } else {
            //agrupacionBar.classList.add('d-none');
        }
    }
    /**
     * Cancela la operación en masa
     */
    function cancelarOperacion() {
        // Obtener todos los parámetros GET de la URL
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.delete('oper');
        // Reconstruir la URL sin 'oper'
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.location.href = newUrl;
    }


    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function() {
        // =================================
        //  ****** INICIALIZACIONES ****** 
        // Inicializar la vista según el parámetro 'oper'
        if (!urlInicial.searchParams.has('oper')) {
            urlInicial.searchParams.set('oper', '0');
            history.replaceState({}, '', urlInicial.toString());
        }
        // Mostrar/ocultar checkboxes según el estado actual
        mostrarCheckboxesPorOper(operActual);
        // Si estamos en modo de operación, mostrar la barra si hay selecciones
        if (operActual === '1') {
            // Esperar a que la tabla esté lista antes de actualizar
            setTimeout(() => {
                //actualizarBarraOperacion();
            }, 500);
        }    
        // =================================
        
        /**
         * Evento: Click en btnInitOperacion para iniciar el modo de operación
         */
        $('#btnInitOperacion').on('click', function(e) {
            e.preventDefault();
            let url = new URL(window.location.href);
            url.searchParams.set('oper', '1');
            window.location.href = url.toString();
        });
        /**
         * Evento: Cambio en los checkboxes para seleccionar/deseleccionar barras
         */
        $(document).on('change', '.btn-check-cute', function() {
            // Agregar animación "pop"
            $(this).addClass('pop');
            setTimeout(() => {
                $(this).removeClass('pop');
            }, 220);

            // Actualizar la barra de operación
            actualizarBarraOperacion();
        });
        /**
         * Evento: Click en btnContinuarOperacion para proceder con la operación
         */
        $('#btnContinuarOperacion').on('click', function(e) {
            e.preventDefault();

            // Obtener los datos seleccionados
            selectedLotes = obtenerLotesSeleccionados();
            const selectedIds = obtenerIdsSeleccionados();
            const almacenId = obtenerAlmacenIdSeleccionado();

            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin selección',
                    text: 'Por favor, seleccione al menos una barra',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            if (!almacenId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo determinar el almacén de origen',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Guardar los datos seleccionados en sessionStorage para usar en el modal
            sessionStorage.setItem('inventarioOperacionIds', JSON.stringify(selectedIds));
            sessionStorage.setItem('inventarioOperacionLotes', JSON.stringify(selectedLotes));
            sessionStorage.setItem('inventarioOperacionAlmacenId', almacenId);

            // Establecer el almacen_id en el formulario
            $('#inputOrigenId').val(almacenId);

            // Abrir el modal
            const modalOperacionInventario = new bootstrap.Modal(document.getElementById('modalOperacionInventario'), {
                backdrop: 'static',
                keyboard: false
            });
            modalOperacionInventario.show();
        });
        /**
         * Evento: Click en btnCancelOperacion para cancelar la operación
         */
        $('#btnCancelOperacion').on('click', function(e) {
            e.preventDefault();
            cancelarOperacion();
        });
        /**
         * Evento: Cuando se cierra el modal, limpiar la selección
         */
        $('#modaOperacionlInventario').on('hidden.bs.modal', function() {
            // Si no se confirmó la operación, regresa al estado normal
            if (operActual === '1') {
                // El usuario vuelve a la lista de selección
            }
        });
        /**
         * Evento: Al cargar la página con parámetro ?oper=1, activar los checkboxes
         */
        if (operActual === '1') {
            document.querySelectorAll('.btn-check-cute').forEach(el => {
                el.style.display = 'inline-block';
            });
        }   
    });
</script>
</body>
</html>

