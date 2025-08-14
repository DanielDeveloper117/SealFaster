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
    <link href="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.js"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/cotizaciones.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-table.css'); ?>">    

<?php
    require_once(ROOT_PATH . 'vendor/autoload.php');

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    // si se recibio un formulario con metodo POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // guardar id y folio
        $idCotizacion = $_POST["id_cotizacion"];
        set_error_handler(function($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        
        // si se recibio la id
        if (isset($idCotizacion)) {
            if($_POST["accion"]=="enviar_correo"){
                $correo_cliente = $_POST["correo_cliente"];
                $pdf_cotizacion = $_FILES['pdf_cotizacion'];
                $queryUpdate = "UPDATE cotizacion_materiales SET fecha_actualizacion = NOW() WHERE id_cotizacion = :id_cotizacion";
                ////////////////////////////PHP MAILER -> cotizador a cliente ////////////////
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'sellosyretenes.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'plat_autorizaciones@sellosyretenes.com';
                    $mail->Password = 'MA9zxx@#8wN';
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port = 465;
                    $mail->setFrom('plat_autorizaciones@sellosyretenes.com', 'Cotizacion');
                    $mail->addAddress($correo_cliente);
                    $mail->isHTML(true);
                    $mail->Subject = 'Cotizacion Sello SRS. ID: '.$idCotizacion;
                    $mail->Body = "Estimado cliente, en este correo se le envia la cotizacion solicitada.<br>Este es un envio automatico, favor de no contestarlo.<br>Cualquier situacion comunicarse con el agente de ventas.";
                    // Adjuntar el archivo directamente desde el array $_FILES
                    if (is_uploaded_file($pdf_cotizacion['tmp_name'])) {
                        $mail->addAttachment($pdf_cotizacion['tmp_name'], $pdf_cotizacion['name']);
                    }
                    $stmtUpdate = $conn->prepare($queryUpdate);
                    $stmtUpdate->bindParam(':id_cotizacion', $idCotizacion);
                    $stmtUpdate->execute();
                    $mail->send();
                    echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("success", "Proceso exitoso", "Correo enviado exitosamente al cliente.", "self");
                    });</script>';

                } catch (Throwable $e) {
                    echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("error", "Error", "Error al enviar correo. '. addslashes($e->getMessage()).' - '.$mail->ErrorInfo .'", "self");
                    });</script>';     
                    exit;        
                }
                ////////////////////////////////////////////////////////////////////////
            } 
        }else { 
            echo "<script>alert('No se recibio id o archivo');</script>";
        }
    }
    
    include(ROOT_PATH . 'includes/backend_info_user.php');



try {
    // Variables de filtro desde GET (sanitizadas)
    $cot = isset($_GET['cot']) && in_array($_GET['cot'], ['u', 'f']) ? $_GET['cot'] : 'u';

    $filtro_familia = isset($_GET['filtro_familia']) && $_GET['filtro_familia'] !== '' ? trim($_GET['filtro_familia']) : null;
    $filtro_tipo_medida = isset($_GET['filtro_tipo_medida']) && $_GET['filtro_tipo_medida'] !== '' ? trim($_GET['filtro_tipo_medida']) : null;
    $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : null;
    $fecha_fin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
    $archivadas = isset($_GET['archivadas']) && $_GET['archivadas'] !== '' ? trim($_GET['archivadas']) : null;

    // Consulta base y parámetros
    $params = [];
    $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE 1=1";

    // Filtrar por usuario si no es admin
    if ($_SESSION['id'] != 71) {
        $sqlCotizaciones .= " AND id_usuario = :id_usuario";
        $params[':id_usuario'] = $_SESSION['id'];
    }

    // Filtro por cotizaciones unicas o fusionadas
    if ($cot === 'u') {
        // Cotizaciones únicas: id_fusion debe ser NULL
        $sqlCotizaciones .= " AND id_fusion IS NULL";

        // Filtros adicionales válidos para únicas
        if ($filtro_familia) {
            $sqlCotizaciones .= " AND familia_perfil = :familia_perfil";
            $params[':familia_perfil'] = $filtro_familia;
        }
        if ($filtro_tipo_medida) {
            $sqlCotizaciones .= " AND tipo_medida = :tipo_medida";
            $params[':tipo_medida'] = $filtro_tipo_medida;
        }
    } elseif ($cot === 'f') {
        // Cotizaciones fusionadas: id_fusion NO debe ser NULL
        $sqlCotizaciones .= " AND id_fusion IS NOT NULL";

        // Filtro por familia sí aplica para fusionadas
        if ($filtro_familia) {
            $sqlCotizaciones .= " AND familia_perfil = :familia_perfil";
            $params[':familia_perfil'] = $filtro_familia;
        }
        // NO aplicar filtro_tipo_medida para fusionadas (ignorar)
    }

    // Filtros por fechas
    if ($fecha_inicio && $fecha_fin) {
        $sqlCotizaciones .= " AND fecha BETWEEN :fecha_inicio AND :fecha_fin";
        $params[':fecha_inicio'] = $fecha_inicio;
        $params[':fecha_fin'] = $fecha_fin;
    } elseif ($fecha_inicio) {
        $sqlCotizaciones .= " AND fecha >= :fecha_inicio";
        $params[':fecha_inicio'] = $fecha_inicio;
    } elseif ($fecha_fin) {
        $sqlCotizaciones .= " AND fecha <= :fecha_fin";
        $params[':fecha_fin'] = $fecha_fin;
    }

    // Filtro por archivadas
    if ($archivadas === '0' || $archivadas === '1') {
        $sqlCotizaciones .= " AND archivada = :archivada";
        $params[':archivada'] = (int)$archivadas;
    } elseif ($archivadas === 'n') {
        $sqlCotizaciones .= " AND (archivada = '0' OR archivada = '1')";
    }else{
        $sqlCotizaciones .= " AND archivada = '0'";
    }

    // Agrupación y orden según cot
    if ($cot === 'u') {
        // Agrupar por id_cotizacion para únicas
        $sqlCotizaciones .= " GROUP BY id_cotizacion ORDER BY fecha DESC, hora DESC";
    } else {
        // Agrupar por id_fusion para fusionadas
        // NOTA: es posible que quieras seleccionar campos representativos (e.g. MIN(fecha)) al agrupar
        $sqlCotizaciones .= " GROUP BY id_fusion ORDER BY fecha DESC, hora DESC";
    }

    // Ejecutar consulta
    $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
    $stmtCotizaciones->execute($params);
    $arregloSelectCotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "<script>alert('Ocurrio un error al buscar con los filtros.');</script>";

    // Consulta por defecto si hay error
    if ($_SESSION['id'] == 71) {
        if ($cot === 'f') {
            $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE id_fusion IS NOT NULL AND archivada = 0 GROUP BY id_fusion ORDER BY fecha DESC, hora DESC";
        } else {
            $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE id_fusion IS NULL AND archivada = 0 GROUP BY id_cotizacion ORDER BY fecha DESC, hora DESC";
        }
        $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
    } else {
        if ($cot === 'f') {
            $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE id_usuario = :id AND id_fusion IS NOT NULL AND archivada = 0 GROUP BY id_fusion ORDER BY fecha DESC, hora DESC";
        } else {
            $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE id_usuario = :id AND id_fusion IS NULL AND archivada = 0 GROUP BY id_cotizacion ORDER BY fecha DESC, hora DESC";
        }
        $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
        $stmtCotizaciones->bindParam(':id', $_SESSION['id']);
    }
    $stmtCotizaciones->execute();
    $arregloSelectCotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);
}


?>

    <title>Cotizaciónes</title>
</head>
<body>

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<section class="section-table flex-column mt-2 mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-1 mb-3">
            <h1>Mis cotizaciones</h1>
        </div>
        <ul id="cotTabs" class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link" data-target="unicas" href="#">Individuales</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-target="fusionadas" href="#">Agrupadas</a>
            </li>
        </ul>

        <div class="table-container" style="border-top-left-radius: 0px !important; border-top-right-radius: 0px !important;">
            <div class="row">
                <div class="d-flex justify-content-start gap-3 col-12 col-md-8">
                   
                    <button type="button" 
                            class="btn-purple" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalFiltrosBusqueda">
                        <i class="bi bi-funnel"></i> Filtros de busqueda
                    </button>
                    <a id="btnInitFusionar" class="btn-unlink" href="#">
                        <i class="bi bi-link" style="font-size:20px !important;"></i> Fusionar/agrupar cotizaciones
                    </a>
                </div>
            </div>
            <div id="containerUnicas" class="">
                <table id="cotizacionesTable" class="table table-striped table-bordered " style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="background-color:#55ad9b52;">Acciones</th>
                            <th>Id cotización</th>
                            <th>Familia</th>
                            <th>Perfil</th>
                            <th>Tipo medida</th>
                            <th>D. Interior</th>
                            <th>D. Exterior</th>
                            <th>Altura</th>
                            <th>Tipo cliente</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <!-- <th>Vendedor</th> -->
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        foreach ($arregloSelectCotizaciones as $row) {
                            // $perfil_sello = $row['perfil_sello'];
                            // $sqlPerfil = "SELECT tipo FROM perfiles WHERE perfil = :perfil";
                            // $stmtPerfil = $conn->prepare($sqlPerfil);
                            // $stmtPerfil->bindParam(':perfil', $perfil_sello);
                            // $stmtPerfil->execute();
                            // $arregoPerfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);
                    ?>
                        <tr>
                            <td class="td-first-actions">
                                <div class="d-flex gap-2 container-actions">
                                    <?php if (isset($_GET['agru']) && $_GET['agru'] == '1'): ?>
                                        <input
                                            type="checkbox"
                                            class="d-none btn-check-cute"
                                            val="<?= htmlspecialchars($row['id_cotizacion']); ?>"
                                            aria-label="Seleccionar cotizacion <?= htmlspecialchars($row['id_cotizacion']); ?>"
                                        />
                                    <?php endif; ?>

                                    <?php if ($tipoUsuario != 5): ?>
                                        <button type="button" class="btn-general btn-version-cotizacion" 
                                            data-bs-toggle='modal' data-bs-target='#modalVersionCotizacion'
                                            data-id-cotizacion="<?= htmlspecialchars($row['id_cotizacion']); ?>"
                                            title="Generar PDF de esta cotización">
                                            <i class="bi bi-filetype-pdf"></i>
                                        </button>
                                       
                                        <button type="button" class="btn-thunder btn-enviar-correo" 
                                            data-bs-toggle='modal' data-bs-target='#modalEnviarCorreo'
                                            data-id-cotizacion="<?= htmlspecialchars($row['id_cotizacion']); ?>"
                                            data-correo-cliente="<?= htmlspecialchars($row['correo_cliente']); ?>"
                                            title="Enviar correo a cliente">
                                            <i class="bi bi-envelope"></i>
                                        </button>
                                        
                                    <?php else: ?>
                                        <form action="../includes/functions/generar_cotizacion.php" method="GET" target="_blank">
                                            <input type="hidden" name="id_cotizacion" value="<?= htmlspecialchars($row['id_cotizacion']); ?>">
                                            <button type="submit" class="btn-general" >Generar PDF</button>
                                        </form>
    
                                        <button type="button" class="btn-thunder btn-enviar-correoX" 
                                            data-bs-toggle='modal' data-bs-target='#modalEnviarCorreoX'
                                            data-id-cotizacionX="<?= htmlspecialchars($row['id_cotizacion']); ?>"
                                            data-correo-clienteX="<?= htmlspecialchars($row['correo_cliente']); ?>">
                                            Funcion para cliente externo
                                        </button>
                                       
                                    <?php endif; ?>
                                                                    
                                    <button type="button" class="btn-archive btn-archivar-cotizacion" 
                                        data-bs-toggle='modal' data-bs-target='#modalArchivar'
                                        data-id-cotizacion="<?= htmlspecialchars($row['id_cotizacion']); ?>"
                                        data-archivada="<?= htmlspecialchars($row['archivada']); ?>"
                                        title="<?= ($row['archivada'] == 0) ? 'Archivar/desactivar esta cotización' : 'Desarchivar/activar esta cotización' ?>">                                    
                                        <i class="bi bi-<?= ($row['archivada'] == 0) ? 'archive' : 'archive-fill' ?>"></i>
                                    </button>
                              
                                </div>
                                
                            </td>
                            
                            <td><?= htmlspecialchars($row['id_cotizacion']); ?></td>
                            <td><?= htmlspecialchars($row['familia_perfil']); ?></td>
                            <td><?= htmlspecialchars($row['perfil_sello']); ?></td>
                            <td><?= htmlspecialchars($row['tipo_medida']); ?></td>
                            <?php
                                $di_sello = 0.00;
                                $de_sello = 0.00;
                                $a_sello = 0.00;
                                if($row['tipo_medida'] == "Sello"){
                                    $di_sello = $row['di_sello'];
                                    $de_sello = $row['de_sello'];
                                    $a_sello = $row['a_sello'];
                                }else{
                                    $di_sello = $row['di_sello2'];
                                    $de_sello = $row['de_sello2'];
                                    $a_sello = $row['a_sello2'];                               
                                }
                            ?>
                            <td><?= htmlspecialchars($di_sello); ?></td>
                            <td><?= htmlspecialchars($de_sello); ?></td>
                            <td><?= htmlspecialchars($a_sello); ?></td>
                            <td><?= htmlspecialchars($row['tipo_cliente']); ?></td>
                            <td><?= htmlspecialchars($row['fecha']); ?></td>
                            <td><?= htmlspecialchars($row['hora']); ?></td>
                            <!-- <td><?= htmlspecialchars($row['vendedor']); ?></td> -->
                        </tr>
                    <?php
                        }
                    ?>
    
                    </tbody>
                </table>
            </div>
            <div id="containerFusionadas" class="d-none">
                <table id="cotizacionesTableFusionadas" class="table table-striped table-bordered" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="background-color:#55ad9b52;">Acciones</th>
                            <th>Id</th>
                            <th>Cotizaciónes</th>
                            <th>Familia</th>
                            <th>Perfil</th>
                            <th>Tipo medida</th>
                            <th>D. Interior</th>
                            <th>D. Exterior</th>
                            <th>Altura</th>
                            <th>Tipo cliente</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        foreach ($arregloSelectCotizaciones as $row) {
                            $id_fusion = $row['id_fusion'];
                            $sqlFusion = "SELECT cm.*
                                        FROM cotizacion_materiales cm
                                        WHERE cm.id_fusion = :id_fusion
                                        GROUP BY cm.id_cotizacion";
                            $stmtFusion = $conn->prepare($sqlFusion);
                            $stmtFusion->bindParam(':id_fusion', $id_fusion, PDO::PARAM_INT);
                            $stmtFusion->execute();
                            $arregloFusion = $stmtFusion->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                        <tr>
                            <td class="td-first-actions">
                                <div class="d-flex gap-2 container-actions">
                                    <?php if ($tipoUsuario != 5): ?>
                                        <button type="button" class="btn-general btn-version-cotizacionF" 
                                            data-bs-toggle='modal' data-bs-target='#modalVersionCotizacionF'
                                            data-id-fusion="<?= htmlspecialchars($row['id_fusion']); ?>"
                                            title="Generar PDF de esta cotización">
                                            <i class="bi bi-filetype-pdf"></i>
                                        </button>
                                       
                                        <button type="button" class="btn-thunder btn-enviar-correo" 
                                            data-bs-toggle='modal' data-bs-target='#modalEnviarCorreo'
                                            data-id-fusion="<?= htmlspecialchars($row['id_fusion']); ?>"
                                            data-correo-cliente="<?= htmlspecialchars($row['correo_cliente']); ?>"
                                            title="Enviar correo a cliente">
                                            <i class="bi bi-envelope"></i>
                                        </button>
                                        
                                    <?php else: ?>
                                        <form action="../includes/functions/generar_cotizacion_f.php" method="GET" target="_blank">
                                            <input type="hidden" name="id_fusion" value="<?= htmlspecialchars($row['id_fusion']); ?>">
                                            <button type="submit" class="btn-general" >Generar PDF</button>
                                        </form>
    
                                        <button type="button" class="btn-thunder btn-enviar-correoX" 
                                            data-bs-toggle='modal' data-bs-target='#modalEnviarCorreoX'
                                            data-id-fusionX="<?= htmlspecialchars($row['id_fusion']); ?>"
                                            data-correo-clienteX="<?= htmlspecialchars($row['correo_cliente']); ?>">
                                            Funcion para cliente externo
                                        </button>
                                       
                                    <?php endif; ?>
                                                                    
                                    <button type="button" class="btn-archive btn-archivar-cotizacion" 
                                        data-bs-toggle='modal' data-bs-target='#modalArchivar'
                                        data-id-fusion="<?= htmlspecialchars($row['id_fusion']); ?>"
                                        data-archivada="<?= htmlspecialchars($row['archivada']); ?>"
                                        title="<?= ($row['archivada'] == 0) ? 'Archivar/desactivar esta cotización' : 'Desarchivar/activar esta cotización' ?>">                                    
                                        <i class="bi bi-<?= ($row['archivada'] == 0) ? 'archive' : 'archive-fill' ?>"></i>
                                    </button>

                                    <button type="button" class="btn-unlink btn-romper-fusion" 
                                        data-bs-toggle='modal' data-bs-target='#modalUnlink'
                                        data-id-fusion="<?= htmlspecialchars($row['id_fusion']); ?>"
                                        title="Romper agrupación/fusión">                                    
                                        <i class="bi bi-link"></i><i class="bi bi-x"></i>
                                    </button>
                              
                                </div>
                                
                            </td>
                            <td><?= htmlspecialchars($row['id_fusion']); ?></td>
                            <td>
                                <?php 
                                    foreach ($arregloFusion as $cot) {
                                        echo '<a href="../includes/functions/generar_pdf.php?id_cotizacion='.htmlspecialchars($cot['id_cotizacion']).'" target="_blank">' . htmlspecialchars($cot['id_cotizacion']) . '</a><br>';
                                    } 
                                ?>
                            </td>
                            <td>
                                <?php 
                                    foreach ($arregloFusion as $cot) {
                                        echo htmlspecialchars($cot['familia_perfil']).'<br>';
                                    } 
                                ?>
                            </td>
                            <td>
                                <?php 
                                    foreach ($arregloFusion as $cot) {
                                        echo htmlspecialchars($cot['perfil_sello']).'<br>';
                                    } 
                                ?>
                            </td>
                            <td>
                                <?php 
                                    foreach ($arregloFusion as $cot) {
                                        echo htmlspecialchars($cot['tipo_medida']).'<br>';
                                    } 
                                ?>
                            </td>
                            <td>
                                <?php 
                                foreach ($arregloFusion as $cot) {
                                    if ($cot['tipo_medida'] == "Sello") {
                                        $di_sello = $cot['di_sello'];
                                    } else {
                                        $di_sello = $cot['di_sello2'];
                                    }
                                    echo htmlspecialchars($di_sello) . '<br>';
                                } 
                                ?>
                            </td>
                            <td>
                                <?php 
                                foreach ($arregloFusion as $cot) {
                                    if ($cot['tipo_medida'] == "Sello") {
                                        $de_sello = $cot['de_sello'];
                                    } else {
                                        $de_sello = $cot['de_sello2'];
                                    }
                                    echo htmlspecialchars($de_sello) . '<br>';
                                } 
                                ?>
                            </td>
                            <td>
                                <?php 
                                foreach ($arregloFusion as $cot) {
                                    if ($cot['tipo_medida'] == "Sello") {
                                        $a_sello = $cot['a_sello'];
                                    } else {
                                        $a_sello = $cot['a_sello2'];
                                    }
                                    echo htmlspecialchars($a_sello) . '<br>';
                                } 
                                ?>
                            </td>

                            <td>
                                <?php 
                                    foreach ($arregloFusion as $cot) {
                                        echo htmlspecialchars($cot['tipo_cliente']).'<br>';
                                    } 
                                ?>
                            </td>                            
                            <td>
                                <?php 
                                    foreach ($arregloFusion as $cot) {
                                        echo htmlspecialchars($cot['fecha']).'<br>';
                                    } 
                                ?>
                            </td>
                            <td>
                                <?php 
                                    foreach ($arregloFusion as $cot) {
                                        echo htmlspecialchars($cot['hora']).'<br>';
                                    } 
                                ?>
                            </td>
                            <!-- <td><?= htmlspecialchars($row['vendedor']); ?></td> -->
                        </tr>
                    <?php
                        }
                    ?>
    
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<div id="agrupacionBar" class="agrupacion-bar d-none">
    <span class="agrupacion-texto">Seleccione las cotizaciones que desea agrupar</span>
    <div class="d-flex flex-column flex-md-row gap-3">
        <button id="btnContinuarAgrupar" class="btn-general">Continuar</button>
        <button id="btnCancelFusion" type="button" class="btn btn-secondary">
            Cancelar
        </button>
    </div>
</div>


<!-- ///////////MODAL SELECCIONAR FILTROS DE BUSQUEDA////////////////// -->
<div class="modal fade" id="modalFiltrosBusqueda" tabindex="-1" aria-hidden="false" aria-labelledby="modalLabel" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalLabel">
                    <i class="bi bi-funnel"></i> Filtros de búsqueda de cotizaciones
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
                        <h5>Filtros por categoría</h5>
                        
                        <div class="row">
                            <!-- Familia de Perfil -->
                            <div class="col-md-6 mb-3">
                                <label for="filtro_familia" class="lbl-general">
                                    <i class="bi bi-collection"></i> Familia de Perfil
                                </label>
                                <select class="form-select" id="filtro_familia" name="filtro_familia">
                                    <option value="">Todas las familias</option>
                                    <option value="Rotary (Rotativo)">Rotary (Rotativo)</option>
                                    <option value="Piston (Pistón)">Piston (Pistón)</option>
                                    <option value="Backup (Respaldo)">Backup (Respaldo)</option>
                                    <option value="Guide (Guía)">Guide (Guía)</option>
                                    <option value="Wiper (Limpiador)">Wiper (Limpiador)</option>
                                    <option value="Rod (Vástago)">Rod (Vástago)</option>
                                </select>
                            </div>

                            <!-- Tipo de Medida -->
                            <div class="col-md-6 mb-3">
                                <label for="filtro_tipo_medida" class="lbl-general">
                                    <i class="bi bi-rulers"></i> Tipo de Medida
                                </label>
                                <select class="form-select" id="filtro_tipo_medida" name="filtro_tipo_medida">
                                    <option value="">Todos los tipos</option>
                                    <option value="Sello">Sello</option>
                                    <option value="Metal">Metal</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Filtros por fecha -->
                    <div class="form-section mb-3">
                        <h5> Filtros por fecha</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="filtro_fecha_inicio" class="lbl-general">
                                    <i class="bi bi-calendar-check"></i> Fecha desde
                                </label>
                                <input type="date" class="form-control" id="filtro_fecha_inicio" name="fecha_inicio">
                                <small class="form-text text-muted">Fecha de inicio del rango</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="filtro_fecha_fin" class="lbl-general">
                                    <i class="bi bi-calendar-x"></i> Fecha hasta
                                </label>
                                <input type="date" class="form-control" id="filtro_fecha_fin" name="fecha_fin">
                                <small class="form-text text-muted">Fecha de fin del rango</small>
                            </div>


                        </div>
                    </div>

                    <!-- Sección: Opciones adicionales -->
                    <div class="form-section mb-3">
                        <h5>Opciones adicionales</h5>
                        <div class="row">
                            <div class="checkbox-container col-md-6 mb-3">
                                <label class="form-check-label" >
                                    <i class="bi bi-archive"></i> <strong>Mostrar cotizaciones archivadas</strong>
                                </label>
                                <?php $archivadas = $_GET['archivadas'] ?? '0'; // valor por defecto ?>
                                <div class="form-check">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="archivadas" id="radioSoloActivas" value="0" 
                                            <?= ($archivadas == '0') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="radioSoloActivas">
                                            Mostrar solo activas
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="archivadas" id="radioSoloArchivadas" value="1" 
                                            <?= ($archivadas == '1') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="radioSoloArchivadas">
                                            Mostrar solo archivadas
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="archivadas" id="radioSinFiltroArchivadas" value="n" 
                                            <?= ($archivadas === 'n') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="radioSinFiltroArchivadas">
                                            Mostrar activas y archivadas
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Botones del formulario -->
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="submit" class="btn-general" >
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
<!-- //////////////////////////////////////////////////////////////////////// -->
<!-- ///////////MODAL SELECCIONAR VERSION DE FORMATO DE UNA COTIZACION////////////////// -->
<div class="modal fade" id="modalVersionCotizacion" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Versión de cotización</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formVersionCotizacion" action="" method="GET" target="_blank">     
                    <input type="hidden" id="inputIdCotizacionFormato" name="id_cotizacion">                    
                    <div class="mb-3">
                        <label class="lbl-general">Selecciona el formato del archivo:</label><br>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="formato" id="formatoCliente" value="cliente" checked>
                            <label class="form-check-label" for="formatoCliente">
                                Formato para cliente
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="formato" id="formatoInterno" value="interno">
                            <label class="form-check-label" for="formatoInterno">
                                Formato interno
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn-general">Generar PDF</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
 <!-- ///////////MODAL SELECCIONAR VERSION DE FORMATO DE UNA COTIZACIONES FUSIONADAS////////////////// -->
<div class="modal fade" id="modalVersionCotizacionF" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Versión de cotización</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formVersionCotizacionF" action="" method="GET" target="_blank">     
                    <input type="hidden" id="inputIdCotizacionFormatoF" name="id_fusion">                    
                    <div class="mb-3">
                        <label class="lbl-general">Selecciona el formato del archivo:</label><br>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="formato" id="formatoClienteF" value="cliente" checked>
                            <label class="form-check-label" for="formatoClienteF">
                                Formato para cliente
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="formato" id="formatoInternoF" value="interno">
                            <label class="form-check-label" for="formatoInternoF">
                                Formato interno
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn-general">Generar PDF</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
<!-- //////////////////////////MODAL ENVIAR CORREO/////////////////////// -->
<div class="modal fade" id="modalEnviarCorreo" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Adjunte la cotización con id: <span id="spanIdCotizacion"></span></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <form id="formEnviarCorreo" class="d-flex flex-column" action="" method="post" enctype="multipart/form-data">
                    <div id="" class="input-container d-flex flex-column">
                        <p class="lbl-general">Remitente:</p>
                        <p>plat_autorizaciones@sellosyretenes.com</p>
                        <p class="lbl-general">Destinatario (correo del cliente):</p>
                        <input type="email" class="input-text" id="inputCorreoCliente" name="correo_cliente" required>
                        <p class="lbl-general">Asunto:</p>
                        <p>Cotizacion de sello SRS. ID: <span id="spanIdCotizacion2"></span></p>
                        <p class="lbl-general">Cuerpo del correo:</p>
                        <p>Estimado cliente, en este correo se le envia la cotizacion que ha solicitado.<br>Este es un envio automatico, favor de no contestarlo.<br>Cualquier situacion comunicarse con el agente de ventas.</p>
                    </div>      
                    <div class="d-flex flex-column mb-3">
                        <label for="inputCotizacionPDF" class="lbl-general">Pdf de cotización</label>
                        <input type="file" id="inputCotizacionPDF" class="input-file" name="pdf_cotizacion" accept="application/pdf" required>
                    </div>              
                    
                    <input type="hidden" id="inputIdCotizacion" name="id_cotizacion">
                    <input type="hidden" id="inputAccion" name="accion" value="enviar_correo">
                    <button class="btn-general" type="submit">Enviar</button>
                </form>

            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
 <!-- ///////////MODAL DESEA ARCHIVAR COTIZACION////////////////// -->
<div class="modal fade" id="modalArchivar" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">¿Desea continuar?</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input id="inputArchivar" type="hidden" name="id_requisicion">
                <input id="inputNextValor" type="hidden" name="archive">
                <p id="infoArchivada"></p>
                <div class="d-flex col-12 w-100 gap-3">
                    <button id="btnArchivar" type="button" class="btn-general">Continuar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
<!-- ///////////MODAL DESEA DESAGRUPAR/UNLINK COTIZACIONES ////////////////// -->
<div class="modal fade" id="modalUnlink" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">¿Desea continuar?</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input id="inputIdRomperFusion" type="hidden" name="id_fusion">
                <p>Esta acción desagrupará las cotizaciones de la fusión. Después las cotizaciones estarán disponibles de manera individual.</p>
                <div class="d-flex col-12 w-100 gap-3">
                    <button id="btnUnlink" type="button" class="btn-general">Continuar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
<?php include(ROOT_PATH . 'includes/footer.php'); ?>
</body>
</html>
