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
    if($tipo_usuario == "Administrador"){
        $sqlCotizaciones = "SELECT * FROM cotizacion_materiales GROUP BY id_cotizacion ORDER BY fecha DESC, hora DESC";
        $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
    }else{
        $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE id_usuario = :id  GROUP BY id_cotizacion ORDER BY fecha DESC, hora DESC";
        $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
        $stmtCotizaciones->bindParam(':id', $_SESSION['id']);
    }
    $stmtCotizaciones->execute();
    $arregloSelectCotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);
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
                            <form action="../includes/functions/generar_pdf.php" method="GET" target="_blank">
                                <input type="hidden" name="id_cotizacion" value="<?= htmlspecialchars($row['id_cotizacion']); ?>">
                                <button type="submit" class="btn-general" >Generar PDF</button>
                            </form>
                            <div class="mt-1">
                                <button type="button" class="btn-thunder btn-enviar-correo" 
                                data-bs-toggle='modal' data-bs-target='#modalEnviarCorreo'
                                data-id-cotizacion="<?= htmlspecialchars($row['id_cotizacion']); ?>"
                                data-correo-cliente="<?= htmlspecialchars($row['correo_cliente']); ?>">Enviar correo a cliente</button>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['id_cotizacion']); ?></td>
                        <!-- <td><?= htmlspecialchars($row['cliente']); ?></td> -->
                        <!-- <td><?= htmlspecialchars($row['correo_cliente']); ?></td> -->
                        <!-- <td><?= htmlspecialchars($row['estatus_completado']); ?></td> -->
                        <td><?= htmlspecialchars($familiPerfilR); ?></td>
                        <td><?= htmlspecialchars($row['perfil_sello']); ?></td>
                        <td><?= htmlspecialchars($row['tipo_medida']); ?></td>
                        <td><?= htmlspecialchars($row['di_sello']); ?></td>
                        <td><?= htmlspecialchars($row['de_sello']); ?></td>
                        <td><?= htmlspecialchars($row['a_sello']); ?></td>
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
<?php include(ROOT_PATH . 'includes/footer.php'); ?>
<script>
    $(document).ready(function(){
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

        // CLICK CERRAR MODAL 
        $(".btn-close").on("click", function(){
            $("#formEnviarCorreo, #formEnviarAProduccion")[0].reset();
        });
    });
</script>
</body>
</html>
