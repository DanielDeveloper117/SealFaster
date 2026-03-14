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
    <script src="<?= controlCache('../assets/js/cotizaciones.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css'); ?>"> 

<?php
    require_once(ROOT_PATH . 'vendor/autoload.php');
    include(ROOT_PATH . 'includes/backend_info_user.php');
    // si se recibio un formulario con metodo POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // guardar id y folio
        //$idCotizacion = $_POST["id_cotizacion"];
        set_error_handler(function($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        
        // si se recibio la id
        //if (isset($idCotizacion)) {
            if($_POST["accion"]=="enviar_correo"){
                $correo_cliente = $_POST["correo_cliente"];
                $pdf_cotizacion = $_FILES['pdf_cotizacion'];
                $otro_archivo = $_FILES['otro_archivo'];
                //$queryUpdate = "UPDATE cotizacion_materiales SET fecha_actualizacion = NOW() WHERE id_cotizacion = :id_cotizacion";
                ////////////////////////////PHP MAILER -> cotizador/vendedor a cliente ////////////////
                try {
                    if($_POST["remitente"] == "cotizador"){
                        //require_once(ROOT_PATH . 'includes/PHPMailer.php');
                        //$mail = getMailer($conn);
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host = $HOST;
                        $mail->SMTPAuth = true;
                        $mail->Username = $USER;
                        $mail->Password = $PASS; 
                        $mail->SMTPSecure = $SECURE;
                        $mail->Port = $PORT;
                        $mail->setFrom($FROM, $DOMAIN_NAME);
                        $mail->isHTML(true);
                        $mail->CharSet = 'UTF-8';
                        $mail->Encoding = 'base64';
                        $mail->addAddress($correo_cliente);
                        //$mail->Subject = 'Cotizacion Sello SRS. ID: '.$idCotizacion;
                        $mail->Subject = 'Cotización de Maquinado de Sello SRS.';
                        $mail->Body = "Estimado cliente, en este correo se le envía la cotización solicitada.<br>Este es un envío automático, favor de no contestarlo.<br>Cualquier situación comunicarse con el agente de ventas.";
                        // Adjuntar el archivo directamente desde el array $_FILES
                        if (is_uploaded_file($pdf_cotizacion['tmp_name'])) {
                            $mail->addAttachment($pdf_cotizacion['tmp_name'], $pdf_cotizacion['name']);
                        }
                        if (is_uploaded_file($otro_archivo['tmp_name'])) {
                            $mail->addAttachment($otro_archivo['tmp_name'], $otro_archivo['name']);
                        }
                        //$stmtUpdate = $conn->prepare($queryUpdate);
                        //$stmtUpdate->bindParam(':id_cotizacion', $idCotizacion);
                        //$stmtUpdate->execute();
                        $mail->send();
                        // Enviar el correo primero

                        $passMail = $PASS;
                        // Conectarse por IMAP a la cuenta
                        $imapStream = imap_open(
                            '{'.$HOST.':993/imap/ssl}INBOX.Sent',
                            $USER,
                            $passMail
                        );

                        //$asunto = 'Cotizacion Sello SRS. ID: '.$idCotizacion;
                        $asunto = 'Cotizacion de Maquinado de Sello SRS.';
                        $cuerpoCorreo = "Estimado cliente, en este correo se le envia la cotizacion del maquinado del sello que ha solicitado.<br>Este es un envio automatico, favor de no contestarlo.<br>Cualquier situacion comunicarse con el agente de ventas.";
                        // Crear el mensaje completo
                        $message = "From: {$USER}\r\n";
                        $message .= "To: {$correo_cliente}\r\n";
                        $message .= "Subject: {$asunto}\r\n";
                        $message .= "Date: " . date('r') . "\r\n";
                        $message .= "MIME-Version: 1.0\r\n";
                        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
                        $message .= "\r\n";
                        $message .= $cuerpoCorreo;

                        // Guardarlo en la carpeta "Sent"
                        imap_append($imapStream, "{".$HOST.":993/imap/ssl}INBOX.Sent", $message);
                        imap_close($imapStream);

                        // echo '<script>document.addEventListener("DOMContentLoaded", function () {
                        // sweetAlertResponse("success", "Proceso exitoso", "Correo enviado exitosamente al cliente.", "self");
                        //     $.ajax({
                        //         url: "../ajax/ajax_notificacion.php",
                        //         type: "POST",
                        //         data: { mensaje: "Correo enviado con correo del sistema"},
                        //         success: function(response) {
                        //             console.log("Notificacion enviada: ", response);
                        //         },
                        //         error: function(error) {
                        //             console.error("Error al enviar la notificacion: ", error);
                        //         }
                        //     });
                        // });</script>';
                        
                        echo '<script>document.addEventListener("DOMContentLoaded", function () {
                        sweetAlertResponse("warning", "Advertencia", "Envío de correos no disponible.", "self");
                            $.ajax({
                                url: "../ajax/ajax_notificacion.php",
                                type: "POST",
                                data: { mensaje: "Intento de envío de correo con correo del sistema"},
                                success: function(response) {
                                    console.log("Notificacion enviada: ", response);
                                },
                                error: function(error) {
                                    console.error("Error al enviar la notificacion: ", error);
                                }
                            });
                        });</script>';
                        exit;

                    }elseif($_POST["remitente"] == "sesion"){
                        $correo_sesion = $usuarioUser ?? null;
                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                        $asunto_correo = $_POST['asunto_correo'] ?? '';
                        $cuerpo_correo = $_POST['cuerpo_correo'] ?? '';
                        
                        if (!$correo_sesion) {
                            echo '<script>document.addEventListener("DOMContentLoaded", function () {
                                sweetAlertResponse("error", "Error", "Error de sesion", "self");
                                });</script>';        
                            exit;
                        }
                        if (!filter_var($correo_sesion, FILTER_VALIDATE_EMAIL) || !filter_var($correo_cliente, FILTER_VALIDATE_EMAIL)) {
                            echo '<script>document.addEventListener("DOMContentLoaded", function () {
                            sweetAlertResponse("error", "Error", "Correo invalido.", "self");
                            });</script>';        
                            exit;
                        }

                        // Verificar que el correo del remitente sea del mismo dominio
                        $dominio_servidor = substr(strrchr($_SERVER['SERVER_NAME'], "."), 1); 
                        if (!str_ends_with($correo_sesion, $dominio_servidor)) {
                            echo '<script>document.addEventListener("DOMContentLoaded", function () {
                            sweetAlertResponse("error", "Error", "No se puede enviar correo desde un dominio externo.", "self");
                            });</script>';        
                            exit;
                        }
                        // Configuracion para usar sendmail local (sin SMTP ni password)
                        $mail->isMail();
                        $mail->CharSet = 'UTF-8';
                        $mail->setFrom($correo_sesion, $DOMAIN_NAME);
                        $mail->addAddress($correo_cliente);
                        $mail->Subject = $asunto_correo;
                        $mail->isHTML(true);
                        $mail->Body = $cuerpo_correo;

                        // Adjuntar el archivo directamente desde el array $_FILES
                        if (is_uploaded_file($pdf_cotizacion['tmp_name'])) {
                            $mail->addAttachment($pdf_cotizacion['tmp_name'], $pdf_cotizacion['name']);
                        }
                        if (is_uploaded_file($otro_archivo['tmp_name'])) {
                            $mail->addAttachment($otro_archivo['tmp_name'], $otro_archivo['name']);
                        }
                        //$stmtUpdate = $conn->prepare($queryUpdate);
                        //$stmtUpdate->bindParam(':id_cotizacion', $idCotizacion);
                        //$stmtUpdate->execute();
                        $mail->send();
                        echo '<script>document.addEventListener("DOMContentLoaded", function () {
                        sweetAlertResponse("success", "Proceso exitoso", "Correo enviado exitosamente al cliente. Nota: este correo no se mostrará en el apartado de correos enviados en tu cuenta de correo.", "self");
                            $.ajax({
                                url: "../ajax/ajax_notificacion.php",
                                type: "POST",
                                data: { mensaje: "Correo enviado con correo en sesion"},
                                success: function(response) {
                                    console.log("Notificacion enviada: ", response);
                                },
                                error: function(error) {
                                    console.error("Error al enviar la notificacion: ", error);
                                }
                            });
                        });</script>';
                        exit;
                    }else{

                    }

                } catch (Throwable $e) {
                    echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("error", "Error", "Error al enviar correo'. addslashes($e->getMessage()).' - '.$mail->ErrorInfo .'", "self");
                    });</script>';     
                    exit;        
                }
                ////////////////////////////////////////////////////////////////////////
            } 
        //}else { 
            //echo "<script>alert('No se recibio id o archivo');</script>";
        //}
    }

    try {
        // --------- LECTURA DE GET / GET INPUTS ----------
        $cot = isset($_GET['cot']) && in_array($_GET['cot'], ['u', 'f']) ? $_GET['cot'] : 'u';

        $familia      = isset($_GET['familia']) && $_GET['familia'] !== '' ? trim($_GET['familia']) : null;
        $tipo_medida  = isset($_GET['tipo_medida']) && $_GET['tipo_medida'] !== '' ? trim($_GET['tipo_medida']) : null;
        $tipo_cliente = isset($_GET['tipo_cliente']) && $_GET['tipo_cliente'] !== '' ? trim($_GET['tipo_cliente']) : null;
        $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : null;
        $fecha_fin    = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
        $archivadas   = isset($_GET['archivadas']) && $_GET['archivadas'] !== '' ? trim($_GET['archivadas']) : null;
        $default      = isset($_GET['default']) ? (int)$_GET['default'] : 2;

        $isAdmin = (($_SESSION['id'] ?? null) == 71);
        $params  = [];

        // --------- ARMADO SQL / SQL CONSTRUCTION ----------
        if ($cot === 'u') {
            // ÚNICAS: fila más reciente por id_cotizacion
            $sqlCotizaciones = "
                SELECT 
                    cm.id_estimacion, cm.id_cotizacion, cm.familia_perfil, cm.perfil_sello, 
                    cm.di_sello, cm.de_sello, cm.a_sello, cm.tipo_medida_di, 
                    cm.tipo_medida_de, cm.tipo_medida_h, cm.tipo_cliente, cm.fecha, 
                    cm.hora, cm.fecha_vencimiento, cm.archivada, cm.id_usuario,
                    COUNT(ca.id) AS total_comentarios
                FROM cotizacion_materiales cm
                INNER JOIN (
                SELECT id_cotizacion, MAX(id_estimacion) as max_id
                FROM cotizacion_materiales
                WHERE id_fusion IS NULL
            ";

            if (!$isAdmin) {
                $sqlCotizaciones .= " AND id_usuario = :id_usuario";
                $params[':id_usuario'] = (int)$_SESSION['id'];
            }
            if ($familia) {
                $sqlCotizaciones .= " AND familia_perfil = :familia_perfil";
                $params[':familia_perfil'] = $familia;
            }
            if ($tipo_medida) {
                $sqlCotizaciones .= " AND tipo_medida = :tipo_medida";
                $params[':tipo_medida'] = $tipo_medida;
            }
            if ($tipo_cliente) {
                $sqlCotizaciones .= " AND tipo_cliente = :tipo_cliente";
                $params[':tipo_cliente'] = $tipo_cliente;
            }

            // Filtros de Fechas
            if ($fecha_inicio && $fecha_fin) {
                $sqlCotizaciones .= " AND fecha BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin']    = $fecha_fin;
            } elseif ($fecha_inicio) {
                $sqlCotizaciones .= " AND fecha >= :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio;
            } elseif ($fecha_fin) {
                $sqlCotizaciones .= " AND fecha <= :fecha_fin";
                $params[':fecha_fin'] = $fecha_fin;
            } elseif ($default > 0) {
                switch ($default) {
                    case 1: $sqlCotizaciones .= " AND fecha = CURDATE()"; break;
                    case 2: $sqlCotizaciones .= " AND YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)"; break;
                    case 3: $sqlCotizaciones .= " AND YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())"; break;
                }
            }

            // Archivadas
            if ($archivadas === '0' || $archivadas === '1') {
                $sqlCotizaciones .= " AND archivada = :archivada";
                $params[':archivada'] = (int)$archivadas;
            } elseif ($archivadas === 'n') {
                $sqlCotizaciones .= " AND (archivada = '0' OR archivada = '1')";
            } else {
                $sqlCotizaciones .= " AND archivada = '0'";
            }

            $sqlCotizaciones .= "
                GROUP BY id_cotizacion
                    ) t ON cm.id_estimacion = t.max_id
                LEFT JOIN comentarios_adjuntos ca ON cm.id_cotizacion = ca.id_cotizacion
                GROUP BY cm.id_estimacion
                ORDER BY cm.id_estimacion DESC
            ";

        } else {
            // FUSIONADAS: fila más reciente por id_fusion
            $sqlCotizaciones = "
                SELECT cm.*, COUNT(ca.id) AS total_comentarios
                FROM cotizacion_materiales cm
                INNER JOIN (
                SELECT id_fusion, MAX(id_estimacion) as max_id
                FROM cotizacion_materiales
                WHERE id_fusion IS NOT NULL
            ";

            if (!$isAdmin) {
                $sqlCotizaciones .= " AND id_usuario = :id_usuario";
                $params[':id_usuario'] = (int)$_SESSION['id'];
            }
            if ($familia) {
                $sqlCotizaciones .= " AND familia_perfil = :familia_perfil";
                $params[':familia_perfil'] = $familia;
            }

            // Filtros de Fechas (Reutilizados)
            if ($fecha_inicio && $fecha_fin) {
                $sqlCotizaciones .= " AND fecha BETWEEN :fecha_inicio AND :fecha_fin";
                $params[':fecha_inicio'] = $fecha_inicio;
                $params[':fecha_fin']    = $fecha_fin;
            } elseif ($fecha_inicio) {
                $sqlCotizaciones .= " AND fecha >= :fecha_inicio";
                $params[':fecha_inicio'] = $fecha_inicio;
            } elseif ($fecha_fin) {
                $sqlCotizaciones .= " AND fecha <= :fecha_fin";
                $params[':fecha_fin'] = $fecha_fin;
            } elseif ($default > 0) {
                switch ($default) {
                    case 1: $sqlCotizaciones .= " AND fecha = CURDATE()"; break;
                    case 2: $sqlCotizaciones .= " AND YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)"; break;
                    case 3: $sqlCotizaciones .= " AND YEAR(fecha) = YEAR(CURDATE()) AND MONTH(fecha) = MONTH(CURDATE())"; break;
                }
            }

            if ($archivadas === '0' || $archivadas === '1') {
                $sqlCotizaciones .= " AND archivada = :archivada";
                $params[':archivada'] = (int)$archivadas;
            } elseif ($archivadas === 'n') {
                $sqlCotizaciones .= " AND (archivada = '0' OR archivada = '1')";
            } else {
                $sqlCotizaciones .= " AND archivada = '0'";
            }

            $sqlCotizaciones .= "
                GROUP BY id_fusion
                    ) t ON cm.id_estimacion = t.max_id
                LEFT JOIN comentarios_adjuntos ca ON cm.id_cotizacion = ca.id_cotizacion
                GROUP BY cm.id_estimacion
                ORDER BY cm.id_estimacion DESC
            ";
        }

        // --------- EJECUCIÓN / EXECUTION ----------
        $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
        foreach ($params as $k => $v) {
            $type = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmtCotizaciones->bindValue($k, $v, $type);
        }
        $stmtCotizaciones->execute();
        $arregloSelectCotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);

    } catch (Throwable $e) {
        // No fallback, just error handling
        $arregloSelectCotizaciones = [];
        
        // Log error for the developer (Opcional pero recomendado)
        error_log("Error in " . __FILE__ . ": " . $e->getMessage());
        
        // Feedback for the user
       // echo "<script>alert('Ocurrió un error al cargar los datos. Por favor, intente de nuevo.');</script>";
        echo $e->getMessage();
    }
?>

    <title>Cotizaciones</title>
</head>
<body>
    <style>
        /* Estilo para filas vencidas */
        .fila-vencida {
            background-color: #ffd54bff !important; /* Amarillo suave */
            border-left: 4px solid #ffc107; /* Borde izquierdo amarillo */
        }

        /* Efecto hover para mantener la interactividad */
        .fila-vencida:hover {
            background-color: #ddb532b0 !important; /* Amarillo un poco más intenso al hover */
        }

        /* Badge para estado vencido */
        .badge.bg-warning {
            font-size: 0.75em;
            padding: 0.35em 0.65em;
        }

        /* Opcional: Diferenciar botones en filas vencidas */
        .fila-vencida .btn-pdf,
        .fila-vencida .btn-archive,
        .fila-vencida .btn-thunder {
            opacity: 0.8;
        }

        .fila-vencida .btn-pdf:hover,
        .fila-vencida .btn-archive:hover,
        .fila-vencida .btn-thunder:hover {
            opacity: 1;
        }

        /* Estilo para comentarios wrapper */
        .comentarios-wrapper {
            position: relative;
            display: inline-block;
        }

        .badge-comentarios {
            position: absolute;
            top: -6px;
            right: -6px;
            background-color: #0d6efd; /* azul bootstrap */
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            min-width: 18px;
            height: 18px;
            line-height: 18px;
            text-align: center;
            border-radius: 50%;
            padding: 0 4px;
            pointer-events: none;
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
<div id="overlay">
    <div class="loading-message">
        <span>Cargando cotizaciones, por favor, espere...</span>    
    </div>
</div>

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<section class="section-table flex-column mt-2 mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-1 mb-3">
            <h1>Mis cotizaciones</h1>
        </div>
        <ul id="cotTabs" class="nav nav-tabs gap-3">
            <li class="nav-item">
                <a class="nav-link" data-target="unicas" href="#">Individuales</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-target="fusionadas" href="#">Agrupadas</a>
            </li>
        </ul>

        <div class="table-container" style="border-top-left-radius: 0px !important; border-top-right-radius: 0px !important;">
            <div class="row mb-3">
                <div class="d-flex justify-content-start gap-3 col-12 col-md-8">
                    <button id="btnFiltrosBusqueda" type="button" 
                            class="btn-purple" 
                            data-bs-toggle="modal" 
                            data-bs-target="#modalFiltrosBusqueda">
                        <i class="bi bi-funnel"></i> Filtros de busqueda
                    </button>
                    <a id="btnInitFusionar" class="btn-unlink" href="#">
                        <i class="bi bi-link" style="font-size:20px !important;"></i> Fusionar/agrupar cotizaciones
                    </a>
                    <button id="btnEnviarCorreo" type="button" class="btn-thunder btn-enviar-correo d-none" 
                        data-bs-toggle='modal' data-bs-target='#modalEnviarCorreo'
                        title="Enviar correo a cliente" style="width:auto !important;">
                        <i class="bi bi-envelope"></i> Enviar correo a cliente
                    </button>
                </div>
            </div>
            <div id="containerUnicas" class="">
                <table id="cotizacionesTable" class="mainTable table table-striped table-bordered " style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="background-color:#55ad9b52;"></th>
                            <th>Id cotización</th>
                            <th>Familia Perfil</th>
                            <th>Perfil</th>
                            <!-- <th>Tipo medida</th> -->
                            <th>Diametro Interior</th>
                            <th>Diametro Exterior</th>
                            <th>Altura Total</th>
                            <th>Tipo cliente</th>
                            <th>Fecha de cotización</th>
                            <th>Hora</th>
                            <th>Fecha vencimiento</th>
                            <!-- <th>Vendedor</th> -->
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        // Configurar timezone
                        date_default_timezone_set('America/Mexico_City');
                        $fecha_actual = new DateTime();
                        
                        foreach ($arregloSelectCotizaciones as $row) {
                            // Calcular si está vencida
                            $esta_vencida = false;
                            if (!empty($row['fecha_vencimiento']) && $row['fecha_vencimiento'] != '0000-00-00 00:00:00') {
                                $fecha_vencimiento = new DateTime($row['fecha_vencimiento']);
                                $esta_vencida = ($fecha_vencimiento < $fecha_actual);
                            }
                            
                            // Clase CSS condicional
                            $clase_fila = $esta_vencida ? 'fila-vencida' : '';
                        ?>
                        <tr class="<?= $clase_fila ?>">
                            <td class="td-first-actions">
                                <div class="d-flex gap-2 container-actions">
                                    <?php if (isset($_GET['agru']) && $_GET['agru'] == '1'): ?>
                                        <div class="checkbox-wrapper">
                                            <input
                                                type="checkbox"
                                                class="btn-check-cute"
                                                val="<?= htmlspecialchars($row['id_cotizacion']); ?>"
                                                title="Seleccionar cotizacion <?= htmlspecialchars($row['id_cotizacion']); ?>"
                                            />
                                        <i class="bi bi-check2 badge-checkbox"></i>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($tipoUsuario != 5): ?>
                                        <button type="button" class="btn-pdf btn-version-cotizacion" 
                                            data-bs-toggle='modal' data-bs-target='#modalVersionCotizacion'
                                            data-id-cotizacion="<?= htmlspecialchars($row['id_cotizacion']); ?>"
                                            title="Generar PDF de esta cotización">
                                            <i class="bi bi-filetype-pdf"></i>
                                        </button>
                                    <?php else: ?>
                                        <form action="../includes/functions/generar_cotizacion.php" method="GET" target="_blank">
                                            <input type="hidden" name="id_cotizacion" value="<?= htmlspecialchars($row['id_cotizacion']); ?>">
                                            <button type="submit" class="btn-pdf" >Generar PDF</button>
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
                                    <?php
                                        $esMia = "0";
                                        if ($row['id_usuario'] == $_SESSION['id']) {
                                            $esMia = "1";
                                        }
                                        if($esta_vencida == false){
                                            $totalComentarios = $row['total_comentarios'] ?? 0;

                                            echo '<div class="comentarios-wrapper">
                                                    <button type="button" class="btn-general btn-modal-comentarios-adjuntos" 
                                                        data-origen="coti"
                                                        data-es-mia="' . $esMia . '"
                                                        data-id_cotizacion="' . htmlspecialchars($row['id_cotizacion']) . '"
                                                        title="Comentarios y archivos adjuntos para esta cotización">
                                                        <i class="bi bi-chat-left-text"></i>
                                                    </button>';
                                            
                                            if ($totalComentarios > 0) {
                                                echo '<span class="badge-comentarios">' . $totalComentarios . '</span>';
                                            }
                                            
                                            echo '</div>';
                                        }

                                    ?>
                                    <?php if ($tipoUsuario == 1 || $tipoUsuario == 2 ): ?>
                                        <button type="button" class="btn-blue btn-asignar-cotizacion" 
                                            data-id-cotizacion="<?= htmlspecialchars($row['id_cotizacion']); ?>"
                                            title="Asignarle esta cotización a un vendedor">
                                            <i class="bi bi-send"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                            </td>
                            
                            <td><?= htmlspecialchars($row['id_cotizacion']); ?></td>
                            <td><?= htmlspecialchars($row['familia_perfil']); ?></td>
                            <td><?= htmlspecialchars($row['perfil_sello']); ?></td>
                            <?php
                                $di_sello = 0.00;
                                $de_sello = 0.00;
                                $a_sello = 0.00;
                                $di_sello = $row['di_sello'];
                                $de_sello = $row['de_sello'];
                                $a_sello = $row['a_sello'];
                            ?>
                            <td><?= htmlspecialchars($di_sello.' '.$row['tipo_medida_di']); ?></td>
                            <td><?= htmlspecialchars($de_sello.' '.$row['tipo_medida_de']); ?></td>
                            <td><?= htmlspecialchars($a_sello.' '.$row['tipo_medida_h']); ?></td>
                            <td><?= htmlspecialchars($row['tipo_cliente']); ?></td>
                            <td><?= htmlspecialchars($row['fecha']); ?></td>
                            <td><?= htmlspecialchars($row['hora']); ?></td>
                            <td>
                                <?php if ($esta_vencida): ?>
                                    <span class="badge bg-warning text-dark" title="Cotización vencida">
                                        <i class="bi bi-exclamation-triangle"></i> Vencida
                                    </span>
                                <?php else: ?>
                                    <?= htmlspecialchars($row['fecha_vencimiento']); ?>
                                <?php endif; ?>
                            </td>
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
                            <th style="background-color:#55ad9b52;"></th>
                            <th>Id</th>
                            <th>Cotizaciónes</th>
                            <th>Familia Perfil</th>
                            <th>Perfil</th>
                            <!-- <th>Tipo medida</th> -->
                            <th>Diametro Interior</th>
                            <th>Diametro Exterior</th>
                            <th>Altura Total</th>
                            <th>Tipo cliente</th>
                            <th>Fecha de cotización</th>
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
                                        <button type="button" class="btn-pdf btn-version-cotizacionF" 
                                            data-bs-toggle='modal' data-bs-target='#modalVersionCotizacionF'
                                            data-id-fusion="<?= htmlspecialchars($row['id_fusion']); ?>"
                                            title="Generar PDF de esta cotización">
                                            <i class="bi bi-filetype-pdf"></i>
                                        </button>
                                       
                                        <!-- <button type="button" class="btn-thunder btn-enviar-correo" 
                                            data-bs-toggle='modal' data-bs-target='#modalEnviarCorreo'
                                            data-id-fusion="<?= htmlspecialchars($row['id_fusion']); ?>"
                                            data-correo-cliente="<?= htmlspecialchars($row['correo_cliente']); ?>"
                                            title="Enviar correo a cliente">
                                            <i class="bi bi-envelope"></i>
                                        </button> -->
                                        
                                    <?php else: ?>
                                        <form action="../includes/functions/generar_cotizacion_f.php" method="GET" target="_blank">
                                            <input type="hidden" name="id_fusion" value="<?= htmlspecialchars($row['id_fusion']); ?>">
                                            <button type="submit" class="btn-pdf" >Generar PDF</button>
                                        </form>
    
                                        <button type="button" class="btn-thunder btn-enviar-correoX" 
                                            data-bs-toggle='modal' data-bs-target='#modalEnviarCorreoX'
                                            data-id-fusionX="<?= htmlspecialchars($row['id_fusion']); ?>"
                                            data-correo-clienteX="<?= htmlspecialchars($row['correo_cliente']); ?>">
                                            Funcion para cliente externo
                                        </button>
                                       
                                    <?php endif; ?>
                                                                    
                                    <button type="button" class="btn-archive btn-archivar-cotizacion2" 
                                        data-bs-toggle='modal' data-bs-target='#modalArchivar2'
                                        data-id-fusion="<?= htmlspecialchars($row['id_fusion']); ?>"
                                        data-archivada="<?= htmlspecialchars($row['archivada']); ?>"
                                        title="<?= ($row['archivada'] == 0) ? 'Archivar/desactivar esta agrupación' : 'Desarchivar/activar esta agrupación' ?>">                                    
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
                            <!-- <td>
                                <?php 
                                    foreach ($arregloFusion as $cot) {
                                        //echo htmlspecialchars($cot['tipo_medida']).'<br>';
                                    } 
                                ?>
                            </td> -->
                            <td>
                                <?php 
                                foreach ($arregloFusion as $cot) {
                                    // if ($cot['tipo_medida'] == "Sello") {
                                    //     $di_sello = $cot['di_sello'];
                                    // } else {
                                    //     $di_sello = $cot['di_sello2'];
                                    // }
                                    echo htmlspecialchars($cot['di_sello']) . '<br>';
                                } 
                                ?>
                            </td>
                            <td>
                                <?php 
                                foreach ($arregloFusion as $cot) {
                                    // if ($cot['tipo_medida'] == "Sello") {
                                    //     $de_sello = $cot['de_sello'];
                                    // } else {
                                    //     $de_sello = $cot['de_sello2'];
                                    // }
                                    echo htmlspecialchars($cot['de_sello']) . '<br>';
                                } 
                                ?>
                            </td>
                            <td>
                                <?php 
                                foreach ($arregloFusion as $cot) {
                                    // if ($cot['tipo_medida'] == "Sello") {
                                    //     $a_sello = $cot['a_sello'];
                                    // } else {
                                    //     $a_sello = $cot['a_sello2'];
                                    // }
                                    echo htmlspecialchars($cot['a_sello']) . '<br>';
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
                                <label for="familia" class="lbl-general">
                                    <i class="bi bi-collection"></i> Familia del Perfil
                                </label>
                                <select class="form-select" id="familia" name="familia">
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
                            <div class="d-none col-md-6 mb-3">
                                <label for="tipo_medida" class="lbl-general">
                                    <i class="bi bi-rulers"></i> Tipo de Medida
                                </label>
                                <select class="form-select" id="tipo_medida" name="tipo_medida">
                                    <option value="">Todos los tipos</option>
                                    <option value="Sello">Sello</option>
                                    <option value="Metal">Metal</option>
                                </select>
                            </div>
                            <!-- Tipo de cliene -->
                            <div class="col-md-6 mb-3">
                                <label for="tipo_cliente" class="lbl-general">
                                    <i class="bi bi-person-workspace"></i> Tipo de Cliente
                                </label>
                                <select class="form-select" id="tipo_cliente" name="tipo_cliente">
                                    <option value="">Todos los tipos</option>
                                    <option value="PUBLICO">PUBLICO</option>
                                    <option value="MASTER">MASTER</option>
                                    <option value="DISTRIBUIDOR">DISTRIBUIDOR</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Sección: Filtros por fecha -->
                    <div class="form-section mb-3">
                        <h5> Filtros por fecha</h5>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inicio" class="lbl-general">
                                    <i class="bi bi-calendar-check"></i> Fecha desde
                                </label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                                <small class="form-text text-muted">Fecha de inicio del rango</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin" class="lbl-general">
                                    <i class="bi bi-calendar-x"></i> Fecha hasta
                                </label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
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
                            <div class="checkbox-container col-md-6 mb-3">
                                <label class="form-check-label" >
                                    <i class="bi bi-table"></i> <strong>Default al cargar la tabla</strong>
                                </label>
                                <?php $default = $_GET['default'] ?? '2'; // valor por defecto ?>
                                <div class="form-check">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="default" id="radioDefault0" value="0" 
                                            <?= ($default == '0') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="radioDefault0">
                                            Todas
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="default" id="radioDefault1" value="1" 
                                            <?= ($default == '1') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="radioDefault1">
                                            Solo las de hoy
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="default" id="radioDefault2" value="2" 
                                            <?= ($default == '2') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="radioDefault2">
                                            Solo de esta semana
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="default" id="radioDefault3" value="3" 
                                            <?= ($default == '3') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="radioDefault3">
                                            Solo de este mes
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
                <!-- <span class="title-form">Adjunte la cotización con id: <span id="spanIdCotizacion"></span></span> -->
                <span class="title-form">Adjunte el PDF de la cotización.<span id="spanIdCotizacion"></span></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                <form id="formEnviarCorreo" class="d-flex flex-column" action="" method="post" enctype="multipart/form-data">
                    <div id="" class="input-container d-flex flex-column">
                        <div class="col-md-12 mb-3">
                            <label for="correoRemitente" class="lbl-general">
                                Remitente
                            </label>
                            <select class="form-select" id="correoRemitente" name="remitente">
                                <option value="cotizador" selected>plat_autorizaciones@sellosyretenes.com (correo del sistema)</option>
                                <option value="sesion"><?= htmlspecialchars($usuarioUser) ?></option>
                            </select>
                        </div>
                        <p class="lbl-general">Destinatario (correo del cliente):</p>
                        <input type="email" class="input-text" id="inputCorreoCliente" name="correo_cliente" required>
                        <p class="lbl-general">Asunto:</p>
                        <!-- <p id="pAsunto">Cotizacion de sello SRS. ID: <span id="spanIdCotizacion2"></span></p> -->
                         <p id="pAsunto">Cotizacion de Maquinado de Sello SRS.<span id="spanIdCotizacion2"></span></p>
                        <input type="text" class="input-text d-none" id="inputAsuntoCorreo" value="Cotizacion de Maquinado de Sello SRS." name="asunto_correo" required>
                        <p class="lbl-general">Cuerpo del correo:</p>
                        <p id="pCuerpo">Estimado cliente, en este correo se le envia la cotizacion que ha solicitado.<br>Este es un envio automatico, favor de no contestarlo.<br>Cualquier situacion comunicarse con el agente de ventas.</p>
                        <textarea id="inputCuerpoCorreo" class="input-text d-none" name="cuerpo_correo"></textarea>
                    </div>      
                    <div class="d-flex flex-column mb-3">
                        <label for="inputCotizacionPDF" class="lbl-general">Pdf de cotización</label>
                        <input type="file" id="inputCotizacionPDF" class="input-file" name="pdf_cotizacion" accept="application/pdf" required>
                    </div>              
                    <div class="d-flex flex-column mb-3">
                        <label for="inputOtroArchivo" class="lbl-general">Otro archivo (opcional)</label>
                        <input type="file" id="inputOtroArchivo" class="input-file" name="otro_archivo" accept="*" >
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
<!-- ///////////MODAL DESEA ARCHIVAR FUSION////////////////// -->
<div class="modal fade" id="modalArchivar2" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">¿Desea continuar?</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input id="inputArchivar2" type="hidden" name="id_fusion">
                <input id="inputNextValor2" type="hidden" name="archive">
                <p id="infoArchivada2"></p>
                <div class="d-flex col-12 w-100 gap-3">
                    <button id="btnArchivar2" type="button" class="btn-general">Continuar</button>
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
 <!-- //////////////////////////MODAL ASIGNAR COTIZACION A UN VENDEDOR /////////////////////// -->
<div class="modal fade" id="modalAsignarCotizacion" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 50% !important;margin-top:10%;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Asignación de cotización</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between ">
                    <input type="hidden" id="inputIdCotizacionAsignar">
                    <div class="" style="width:100%;">
                        <label for="inputIdVendedor" class="lbl-general">Vendedor*</label>
                        <select id="inputIdVendedor" class="selector" required >
                            <option value="" selected disabled>Seleccione vendedor</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end">
                <button id="btnAsignarCotizacion" type="button" class="btn-general" tabindex="-1">Enviar</button>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->

<?php include(ROOT_PATH . 'includes/modal_comentarios_adjuntos.php'); ?>
<script src="<?= controlCache('../assets/js/modal_comentarios_adjuntos.js'); ?>"></script>
<?php include(ROOT_PATH . 'includes/footer.php'); ?>
   <script>
        document.addEventListener('DOMContentLoaded', function() {
            //$("#overlay").addClass("d-none");
            // Verificar si ya existe la preferencia en localStorage
            /*
            if (!localStorage.getItem("ocultarInfoVigencias")) {
                Swal.fire({
                    title: 'Información importante',
                    text: 'Ahora las cotizaciones tendrán una vigencia de 72 horas a partir de su creación. Las cotizaciones vencidas ya no podran ser usadas en futuras requisiciones.',
                    icon: 'info',
                    confirmButtonText: 'Entendido',
                    width: '400px',
                    padding: '10px',
                    position: 'bottom-end',
                    toast: true,
                    showConfirmButton: true,
                    showCloseButton: false,
                    input: 'checkbox',
                    inputPlaceholder: 'No mostrar nuevamente',
                    inputAttributes: {
                    id: 'noMostrarCheckbox'
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                    // Guardar preferencia en localStorage
                    localStorage.setItem("ocultarInfoVigencias", "1");
                    }
                });
            }
            */
            // Add hover effects to action buttons
            const actionButtons = document.querySelectorAll('.container-actions button');
            actionButtons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.05)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
                
                // Add ripple effect on click
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255, 255, 255, 0.3)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.pointerEvents = 'none';
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Add smooth scrolling and parallax effects
            let ticking = false;
            
            function updateParallax() {
                const scrolled = window.pageYOffset;
                const parallaxElements = document.querySelectorAll('.section-table');
                
                parallaxElements.forEach(element => {
                    const speed = 0.5;
                    const yPos = -(scrolled * speed);
                    element.style.transform = `translateY(${yPos}px)`;
                });
                
                ticking = false;
            }
            
            function requestTick() {
                if (!ticking) {
                    requestAnimationFrame(updateParallax);
                    ticking = true;
                }
            }
            
            window.addEventListener('scroll', requestTick);
            
            // Initialize table animations
            // setTimeout(() => {
            //     animateTableEntrance(containerUnicas);
            // }, 800);
            
            // Add glow effect to table rows on data change
            function addRowGlow(row) {
                row.style.boxShadow = '0 0 20px rgba(85, 173, 155, 0.6)';
                setTimeout(() => {
                    row.style.boxShadow = '';
                }, 1000);
            }
            
            // Simulate data updates (for demo purposes)
            setInterval(() => {
                const rows = document.querySelectorAll('tbody tr');
                if (rows.length > 0) {
                    const randomRow = rows[Math.floor(Math.random() * rows.length)];
                    addRowGlow(randomRow);
                }
            }, 1000);
            
            // Add dynamic theme detection
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addListener(handleThemeChange);
            
            function handleThemeChange(e) {
                document.body.classList.toggle('light-theme', !e.matches);
                
                // Trigger re-animation for smooth transition
                const elements = document.querySelectorAll('.loading-effect');
                elements.forEach((el, index) => {
                    el.style.animation = 'none';
                    setTimeout(() => {
                        el.style.animation = `fadeInUp 0.6s ease-out forwards`;
                        el.style.animationDelay = (index * 0.1) + 's';
                    }, 50);
                });
            }
            
            // Add interactive table sorting simulation
            const headers = document.querySelectorAll('th');
            headers.forEach(header => {
                if (header.textContent !== 'Acciones') {
                    header.style.cursor = 'pointer';
                    header.addEventListener('click', function() {
                        // Add visual feedback for sorting
                        this.style.background = 'var(--glow-color)';
                        this.style.transform = 'scale(1.02)';
                        
                        setTimeout(() => {
                            this.style.background = 'var(--accent-bg)';
                            this.style.transform = 'scale(1)';
                        }, 300);
                        
                        // Animate table rows for sorting effect
                        const tbody = this.closest('table').querySelector('tbody');
                        const rows = Array.from(tbody.querySelectorAll('tr'));
                        
                        rows.forEach((row, index) => {
                            row.style.transform = 'translateX(-10px)';
                            row.style.opacity = '0.7';
                            
                            setTimeout(() => {
                                row.style.transform = 'translateX(0)';
                                row.style.opacity = '1';
                            }, index * 50);
                        });
                    });
                }
            });
        });
        
        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
