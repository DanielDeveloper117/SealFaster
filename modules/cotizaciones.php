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
        $filtro_familia = isset($_GET['filtro_familia']) && $_GET['filtro_familia'] !== '' ? trim($_GET['filtro_familia']) : null;
        $filtro_tipo_medida = isset($_GET['filtro_tipo_medida']) && $_GET['filtro_tipo_medida'] !== '' ? trim($_GET['filtro_tipo_medida']) : null;
        $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : null;
        $fecha_fin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
        $archivadas = isset($_GET['archivadas']) && $_GET['archivadas'] !== '' ? trim($_GET['archivadas']) : null;

        // Consulta base y parámetros
        $params = [];
        $where = [];

        // Solo si hay al menos un filtro, se arma dinámica
        if ($filtro_familia || $filtro_tipo_medida || $fecha_inicio || $fecha_fin || $archivadas !== null) {
            $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE 1=1";

            // Si el usuario no es admin (ID 71), filtra por su ID
            if ($_SESSION['id'] != 71) {
                $sqlCotizaciones .= " AND id_usuario = :id_usuario";
                $params[':id_usuario'] = $_SESSION['id'];
            }

            // Filtro por familia
            if ($filtro_familia) {
                $sqlCotizaciones .= " AND familia_perfil = :familia_perfil";
                $params[':familia_perfil'] = $filtro_familia;
            }

            // Filtro por tipo de medida
            if ($filtro_tipo_medida) {
                $sqlCotizaciones .= " AND tipo_medida = :tipo_medida";
                $params[':tipo_medida'] = $filtro_tipo_medida;
            }

            // Filtro por fechas
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
            }

            if ($archivadas === 'n') {
                $sqlCotizaciones .= " AND (archivada = '0' OR archivada = '1')";
            }

            // Orden
            $sqlCotizaciones .= " GROUP BY id_cotizacion ORDER BY fecha DESC, hora DESC";

            // Ejecutar consulta
            $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
            $stmtCotizaciones->execute($params);
            $arregloSelectCotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Si no hay parámetros => ejecutar consultas por defecto
            if ($_SESSION['id'] == 71) {
                $sqlCotizaciones = "SELECT * FROM cotizacion_materiales GROUP BY id_cotizacion AND archivada = 0 ORDER BY fecha DESC, hora DESC";
                $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
            } else {
                $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE id_usuario = :id AND archivada = 0 GROUP BY id_cotizacion ORDER BY fecha DESC, hora DESC";
                $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
                $stmtCotizaciones->bindParam(':id', $_SESSION['id']);
            }
            $stmtCotizaciones->execute();
            $arregloSelectCotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        // Si ocurre error => alert y consultas por defecto
        echo "<script>alert('Ocurrio un error al buscar con los filtros.');</script>";

        if ($_SESSION['id'] == 71) {
            $sqlCotizaciones = "SELECT * FROM cotizacion_materiales GROUP BY id_cotizacion AND archivada = 0 ORDER BY fecha DESC, hora DESC";
            $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
        } else {
            $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE id_usuario = :id AND archivada = 0 GROUP BY id_cotizacion ORDER BY fecha DESC, hora DESC";
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
            <h1>Mis cotizaciónes</h1>
        </div>
        <div class="table-container">
            <div class="row">
                <div class="col-12">
                    <div class=" bg-light rounded">
                        <!-- Botón con data attributes para Bootstrap modal -->
                        <button type="button" 
                                class="btn-purple" 
                                data-bs-toggle="modal" 
                                data-bs-target="#modalFiltrosBusqueda">
                            <i class="bi bi-funnel"></i> Filtros de busqueda
                        </button>
                    </div>
                </div>
            </div>
            <table id="cotizacionesTable" class="table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="background-color:#55ad9b52;">Acciones</th>
                         <th>Id cotización</th>
                        <!--<th>Tipo de cliente</th> -->
                        <!-- <th>Correo del cliente</th> -->
                        <!-- <th>Estatus</th> -->
                        <th>Familia</th>
                        <th>Perfil</th>
                        <th>Tipo medida</th>
                        <th>D. Interior</th>
                        <th>D. Exterior</th>
                        <th>Altura</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <!-- <th>Vendedor</th> -->
                    </tr>
                </thead>
                <tbody>
                <?php
                    foreach ($arregloSelectCotizaciones as $row) {
                        $perfil_sello = $row['perfil_sello'];
                        $sqlPerfil = "SELECT tipo FROM perfiles WHERE perfil = :perfil";
                        $stmtPerfil = $conn->prepare($sqlPerfil);
                        $stmtPerfil->bindParam(':perfil', $perfil_sello);
                        $stmtPerfil->execute();
                        $arregoPerfil = $stmtPerfil->fetch(PDO::FETCH_ASSOC);
                        $familiPerfilR = "";
                        switch($arregoPerfil['tipo']){
                            case "rotary":
                                $familiPerfilR = "Rotary (Rotativo)";
                            break;
                            case "piston":
                                $familiPerfilR = "Piston (Pistón)";
                            break;
                            case "backup":
                                $familiPerfilR = "Backup (Respaldo)";
                            break;
                            case "guide":
                                $familiPerfilR = "Guide (Guía)";
                            break;
                            case "wipers":
                                $familiPerfilR = "Wiper (Limpiador)";
                            break;
                            case "rod":
                                $familiPerfilR = "Rod (Vástago)";
                            break;
                            default:
                                $familiPerfilR = "";        
                            break;
                        }
                ?>
                    <tr>
                        <td class="td-first-actions">
                            <div class="d-flex gap-2 container-actions">
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
                        <!-- <td><?= htmlspecialchars($row['cliente']); ?></td> -->
                        <!-- <td><?= htmlspecialchars($row['correo_cliente']); ?></td> -->
                        <!-- <td><?= htmlspecialchars($row['estatus_completado']); ?></td> -->
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
    </div>
</section>

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
<?php include(ROOT_PATH . 'includes/footer.php'); ?>
<script>
    $(document).ready(function() {
        // Cargar filtros activos al abrir el modal
        $('#modalFiltrosBusqueda').on('show.bs.modal', function() {
            cargarFiltrosActuales();
            mostrarFiltrosActivos();
        });

        // Validación del formulario
        $('#formFiltros').on('submit', function(e) {
            var fechaInicio = $('#filtro_fecha_inicio').val();
            var fechaFin = $('#filtro_fecha_fin').val();
            
            // Validar que la fecha de inicio no sea mayor que la fecha de fin
            if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
                e.preventDefault();
                alert('La fecha de inicio no puede ser mayor que la fecha de fin.');
                $('#filtro_fecha_inicio').focus();
                return false;
            }
            
            // El formulario se enviará normalmente si pasa la validación
            console.log('Formulario válido, enviando...');
        });

        // Limpiar formulario
        $('#btnLimpiarFormulario').on('click', function() {
            $('#formFiltros')[0].reset();
            $('#filtrosActivosContainer').hide();
        });

        // Auto-completar fecha fin cuando se selecciona fecha inicio
        $('#filtro_fecha_inicio').on('change', function() {
            var fechaInicio = $(this).val();
            var fechaFin = $('#filtro_fecha_fin').val();
            
            if (fechaInicio && !fechaFin) {
                // Sugerir la fecha actual como fecha fin
                var hoy = new Date().toISOString().split('T')[0];
                $('#filtro_fecha_fin').val(hoy);
            }
        });

        // Función para cargar filtros desde la URL
        function cargarFiltrosActuales() {
            var urlParams = new URLSearchParams(window.location.search);
            
            // Cargar valores en el formulario
            $('#filtro_familia').val(urlParams.get('filtro_familia') || '');
            $('#filtro_tipo_medida').val(urlParams.get('filtro_tipo_medida') || '');
            $('#filtro_fecha_inicio').val(urlParams.get('fecha_inicio') || '');
            $('#filtro_fecha_fin').val(urlParams.get('fecha_fin') || '');
            $('#archivadas').prop('checked', urlParams.get('archivadas') === '1');
        }

        // Función para mostrar filtros activos
        function mostrarFiltrosActivos() {
            var urlParams = new URLSearchParams(window.location.search);
            var filtrosActivos = [];
            
            // Verificar cada filtro y crear etiquetas
            if (urlParams.get('filtro_familia')) {
                var familias = {
                    'rotary': 'Rotary (Rotativo)',
                    'piston': 'Piston (Pistón)',
                    'backup': 'Backup (Respaldo)',
                    'guide': 'Guide (Guía)',
                    'wipers': 'Wiper (Limpiador)',
                    'rod': 'Rod (Vástago)'
                };
                var familiaTexto = urlParams.get('filtro_familia') || familias[urlParams.get('filtro_familia')];
                filtrosActivos.push('Familia: ' + familiaTexto);
            }
            
            if (urlParams.get('filtro_tipo_medida')) {
                filtrosActivos.push('Tipo: ' + urlParams.get('filtro_tipo_medida'));
            }
            
            if (urlParams.get('fecha_inicio')) {
                filtrosActivos.push('Desde: ' + urlParams.get('fecha_inicio'));
            }
            
            if (urlParams.get('fecha_fin')) {
                filtrosActivos.push('Hasta: ' + urlParams.get('fecha_fin'));
            }

            if (urlParams.get('archivadas') === '0') {
                filtrosActivos.push('Solo activas');
            }

            if (urlParams.get('archivadas') === '1') {
                filtrosActivos.push('Solo archivadas');
            }
            
            // Mostrar los filtros activos
            if (filtrosActivos.length > 0) {
                var tagsHtml = filtrosActivos.map(function(filtro) {
                    return '<span class="filtro-tag">' + filtro + '</span>';
                }).join(' ');
                
                $('#filtrosActivosList').html(tagsHtml);
                $('#filtrosActivosContainer').show();
            } else {
                $('#filtrosActivosContainer').hide();
            }
        }

        // Función global para limpiar todos los filtros
        window.limpiarTodosFiltros = function() {
            if (confirm('¿Estás seguro de que deseas limpiar todos los filtros?')) {
                window.location.href = window.location.pathname;
            }
        };

        // Mostrar filtros activos al cargar la página
        mostrarFiltrosActivos();
    });
</script>
<script>
    $(document).ready(function(){
        // CLICK para seleccionar versión del formato de cotización
        $(".btn-version-cotizacion").on('click', function () {
            const dataIdCotizacion = $(this).data('id-cotizacion');
            $("#inputIdCotizacionFormato").val(dataIdCotizacion);
        });

        $("#formVersionCotizacion").on("submit", function (e) {
            e.preventDefault(); // evita que el form se envíe inmediatamente

            const valorSeleccionado = $('input[name="formato"]:checked').val();

            if (valorSeleccionado === 'cliente') {
                $(this).attr("action", "../includes/functions/generar_cotizacion.php");
            } else if (valorSeleccionado === 'interno') {
                $(this).attr("action", "../includes/functions/generar_pdf.php");
            } else {
                alert("Selecciona una opción de formato.");
                return;
            }

            // Ahora sí, enviar el formulario con el action actualizado
            this.submit();
        });


        //CLICK A Enviar correo modal
        $(".btn-enviar-correo").on('click', function(){
            //$dataId=$("#btnValidar").data('id');//lo toma y lo almacena en cache
            $dataIdCotizacion=$(this).data('id-cotizacion');
            $dataCorreoCliente=$(this).data('correo-cliente');

            $("#inputIdCotizacion").val($dataIdCotizacion);
            $("#spanIdCotizacion, #spanIdCotizacion2").text($dataIdCotizacion);
        });

        //CLICK A Enviar a produccion
        $(".btn-enviar-produccion").on('click', function(){
            $dataIdCotizacionProduccion=$(this).data('id-cotizacion');

            $("#inputCotizacionProduccion").val($dataIdCotizacionProduccion);
        });
    
        // CLICK para archivar una cotizacion
        $(".btn-archivar-cotizacion").on('click', function () {
            const dataIdCotizacionA = $(this).data('id-cotizacion');
            var dataArchivada = $(this).data('archivada');
            if(dataArchivada == 0){
                dataArchivada = 1;
                $("#infoArchivada").text("Si archiva la cotización no podrá usarla al crear nuevas requisiciones.");
            }else{
                dataArchivada = 0;
                $("#infoArchivada").text("Ya podrá usar la cotizacion al crear nuevas requisiciones.");
            }
            $("#inputArchivar").val(dataIdCotizacionA);
            $("#inputNextValor").val(dataArchivada);
        });

        $("#btnArchivar").on("click", function(){
            var idCotizacionArchivar = $("#inputArchivar").val();
            var nextValue = $("#inputNextValor").val();
            $.ajax({
                url: '../ajax/archivar.php',
                method: 'POST',
                data: {
                    id_cotizacion: idCotizacionArchivar,
                    archivada: nextValue
                },
                success: function(data) {
                    if (data.success) {
                        sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                    } else {
                        sweetAlertResponse("warning", "Advertencia", data.error, "self");
                    }
                },
                error: function () {
                    sweetAlertResponse("error", "Error", "Ocurrio algo inesperado al autorizar ", "none");
                    console.error("Error al consultar el estatus de autorización.");
                }
            });
        });

        // CLICK CERRAR MODAL 
        $(".btn-close").on("click", function(){
            $("#formEnviarCorreo, #formEnviarAProduccion")[0].reset();
        });
    });
</script>
</body>
</html>
