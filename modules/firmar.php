<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="../assets/img/general/favicon.ico?v=2" />
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <script src="<?= controlCache('../assets/dependencies/signature_pad.umd.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-firmar.css'); ?>">
    
    <title>Firma de Requisición</title>

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
require_once(ROOT_PATH . 'config/config.php');
        require_once(ROOT_PATH . 'vendor/autoload.php');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['token'] ?? '';
    $id_requisicion = $_POST['id_requisicion'] ?? '';
    $autoriza = $_POST['t'] ?? '';
    $id_usuario = $_POST["id_usuario"] ?? null;

    try {
        // Validar token
        $stmtToken = $conn->prepare("SELECT COUNT(*) FROM tokens_autorizacion WHERE token = :token");
        $stmtToken->bindParam(':token', $token);
        $stmtToken->execute();

        if ($stmtToken->fetchColumn() == 0) {
            echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("warning", "Token inválido", "Este enlace ya fue usado o es inválido.", "none");
            });</script><h3 class="p-5">Proceso finalizado, puede cerrar esta pestaña.</h3>';
            exit;
        }

        // Validar parámetros
        if (empty($id_requisicion) || !ctype_digit($id_requisicion) || !in_array($autoriza, ['g','a'])) {
            echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("error", "Error", "Parámetros inválidos.", "none");
            });</script><h3 class="p-5">Proceso finalizado, puede cerrar esta pestaña.</h3>';
            exit;
        }
        
        // Iniciar transacción para consistencia
        $conn->beginTransaction();
        $sqlUserInfo = "SELECT * FROM login WHERE id = :id_usuario";
        $stmtUserInfo = $conn->prepare($sqlUserInfo);
        $stmtUserInfo->bindParam(':id_usuario', $id_usuario);
        $stmtUserInfo->execute();
        $arregloUser = $stmtUserInfo->fetch(PDO::FETCH_ASSOC);

        $clave_encriptacion = $PASS_UNCRIPT;
        $nombre_encriptado = $arregloUser['nombre'];
        $nombreUser = openssl_decrypt($nombre_encriptado, 'AES-128-ECB', $clave_encriptacion);

        // 1. Actualizar requisición
        $sql = "UPDATE requisiciones SET estatus = 'Autorizada',
                    autorizo = :autorizo,
                    fecha_autorizacion = NOW() 
                WHERE id_requisicion = :id_requisicion";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':autorizo', $nombreUser);
        $stmt->bindParam(':id_requisicion', $id_requisicion);
        $stmt->execute();

        // 2. Eliminar token
        $stmtDeleteToken = $conn->prepare("DELETE FROM tokens_autorizacion WHERE token = :token");
        $stmtDeleteToken->bindParam(':token', $token);
        $stmtDeleteToken->execute();

        // Función para escribir en el log
        function writeToLog($message) {
            $logFile = ROOT_PATH . 'logs/debug_autorizacion.log';
            $timestamp = date('Y-m-d H:i:s');
            $formattedMessage = "[$timestamp] $message\n";
            file_put_contents($logFile, $formattedMessage, FILE_APPEND | LOCK_EX);
        }

        writeToLog("=== INICIO PROCESO AUTORIZACIÓN ===");
        writeToLog("ID Requisición: $id_requisicion");

        // 3. Obtener cotizaciones asociadas para actualizar pre-stock
        $sqlRequisicion = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
        $stmtRequisicion = $conn->prepare($sqlRequisicion);
        $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion);
        $stmtRequisicion->execute();
        $result = $stmtRequisicion->fetch(PDO::FETCH_ASSOC);

        writeToLog("Cotizaciones encontradas: " . ($result['cotizaciones'] ?? 'VACIO'));

        if ($result && !empty($result['cotizaciones'])) {
            $cotizacion_ids = explode(', ', $result['cotizaciones']);
            
            writeToLog("IDs de cotizaciones: " . implode(', ', $cotizacion_ids));

            // 4. Actualizar estado de cada cotización
            $sqlUpdateCotizacion = "UPDATE cotizacion_materiales SET estatus_completado = 'Autorizada', fecha_actualizacion = NOW() WHERE id_cotizacion = :id_cotizacion";
            $stmtUpdateCotizacion = $conn->prepare($sqlUpdateCotizacion);

            // 5. Preparar consulta para actualizar inventario
            $sqlUpdatePreStock = "UPDATE inventario_cnc SET pre_stock = pre_stock - :consumo_total, estatus = 'En uso', updated_at = NOW() WHERE lote_pedimento = :lote_pedimento";
            $stmtUpdatePreStock = $conn->prepare($sqlUpdatePreStock);

            // 6. Array para acumular consumo por lote_pedimento
            $consumoPorLote = [];

            foreach ($cotizacion_ids as $id_cotizacion) {
                writeToLog("Procesando cotización ID: $id_cotizacion");
                
                // Actualizar estado de la cotización
                $stmtUpdateCotizacion->bindValue(':id_cotizacion', $id_cotizacion);
                $updateResult = $stmtUpdateCotizacion->execute();
                writeToLog("Actualización estado cotización $id_cotizacion: " . ($updateResult ? 'ÉXITO' : 'FALLÓ'));

                // Obtener todas las estimaciones de esta cotización
                $sqlEstimaciones = "SELECT id_cotizacion, a_sello, material, billets_lotes FROM cotizacion_materiales WHERE id_cotizacion = :id_cotizacion";
                $stmtEstimaciones = $conn->prepare($sqlEstimaciones);
                $stmtEstimaciones->bindValue(':id_cotizacion', $id_cotizacion);
                $stmtEstimaciones->execute();
                $estimaciones = $stmtEstimaciones->fetchAll(PDO::FETCH_ASSOC);

                writeToLog("Cotización ID $id_cotizacion tiene " . count($estimaciones) . " estimaciones");

                foreach ($estimaciones as $estimacion) {
                    $a_sello = floatval($estimacion['a_sello']);
                    $material = $estimacion['material'];
                    $billets_lotes = $estimacion['billets_lotes'];

                    writeToLog("Estimación - a_sello: $a_sello, material: $material");
                    writeToLog("Estimación - billets_lotes: " . ($billets_lotes ?: 'VACIO'));

                    // Determinar desbaste por material
                    $desbaste = 2.50; // Valor por defecto (material duro)
                    
                    $materialesBlandos = ['H-ECOPUR', 'ECOSIL', 'ECORUBBER 1', 'ECORUBBER 2', 'ECORUBBER 3', 'ECOPUR'];
                    $materialesDuros = ['ECOTAL', 'ECOMID', 'ECOFLON 1', 'ECOFLON 2', 'ECOFLON 3'];
                    
                    if (in_array($material, $materialesBlandos)) {
                        $desbaste = 2.00;
                    } elseif (in_array($material, $materialesDuros)) {
                        $desbaste = 2.50;
                    }

                    writeToLog("Material: $material -> Desbaste: $desbaste");

                    // Procesar cada billet/lote
                    if (!empty($billets_lotes)) {
                        $billets = array_map('trim', explode(',', $billets_lotes));
                        
                        writeToLog("Billets encontrados: " . count($billets));
                        
                        foreach ($billets as $index => $billet) {
                            writeToLog("Procesando billet $index: $billet");
                            
                            // Extraer lote_pedimento y cantidad de piezas
                            // Formato: "R2T047062-1 (47/62) 1 pz"
                            if (preg_match('/^([^\s]+)\s+\([^)]+\)\s+(\d+)\s+pz$/i', $billet, $matches)) {
                                $lote_pedimento = trim($matches[1]);
                                $cantidad_piezas = intval($matches[2]);
                                
                                // Calcular consumo para este billet
                                $altura_por_pieza = $a_sello + $desbaste;
                                $consumo_total = $altura_por_pieza * $cantidad_piezas;
                                
                                // Acumular consumo por lote_pedimento
                                if (!isset($consumoPorLote[$lote_pedimento])) {
                                    $consumoPorLote[$lote_pedimento] = 0;
                                }
                                $consumoPorLote[$lote_pedimento] += $consumo_total;
                                
                                writeToLog("LOTE PROCESADO - Lote: $lote_pedimento, Piezas: $cantidad_piezas, Altura+pz: $altura_por_pieza, Consumo: $consumo_total");
                            } else {
                                writeToLog("ERROR - Formato no válido en billet: $billet");
                            }
                        }
                    } else {
                        writeToLog("ADVERTENCIA - billets_lotes está vacío para cotización $id_cotizacion");
                    }
                }
            }

            writeToLog("Consumo total acumulado por lote: " . print_r($consumoPorLote, true));

            // 7. Actualizar pre_stock en inventario_cnc para cada lote
            if (!empty($consumoPorLote)) {
                foreach ($consumoPorLote as $lote_pedimento => $consumo_total) {
                    writeToLog("Intentando actualizar inventario - Lote: $lote_pedimento, Restar: $consumo_total");
                    
                    // Primero verificar si el lote existe
                    $sqlCheckLote = "SELECT pre_stock FROM inventario_cnc WHERE lote_pedimento = :lote_pedimento";
                    $stmtCheckLote = $conn->prepare($sqlCheckLote);
                    $stmtCheckLote->bindValue(':lote_pedimento', $lote_pedimento);
                    $stmtCheckLote->execute();
                    $loteExistente = $stmtCheckLote->fetch(PDO::FETCH_ASSOC);
                    
                    if ($loteExistente) {
                        $pre_stock_actual = $loteExistente['pre_stock'];
                        writeToLog("Lote encontrado - Pre_stock actual: $pre_stock_actual");
                        
                        $stmtUpdatePreStock->bindValue(':consumo_total', $consumo_total);
                        $stmtUpdatePreStock->bindValue(':lote_pedimento', $lote_pedimento);
                        $result = $stmtUpdatePreStock->execute();
                        
                        $rowsAffected = $stmtUpdatePreStock->rowCount();
                        writeToLog("Actualización lote $lote_pedimento - Rows affected: $rowsAffected, Éxito: " . ($result ? 'SI' : 'NO'));
                        
                        if ($rowsAffected === 0) {
                            writeToLog("ERROR - No se pudo actualizar el lote $lote_pedimento");
                        }
                    } else {
                        writeToLog("ERROR - Lote no encontrado en inventario_cnc: $lote_pedimento");
                    }
                }
            } else {
                writeToLog("ADVERTENCIA - No hay consumo acumulado para actualizar");
            }
        } else {
            writeToLog("ERROR - No se encontraron cotizaciones para la requisición $id_requisicion");
        }

        writeToLog("=== FIN PROCESO AUTORIZACIÓN ===");

        // Confirmar transacción
        $conn->commit();

        // 8. Preparar correos
        $sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6";
        $stmtCorreos = $conn->prepare($sqlCorreoInventarios);
        $stmtCorreos->execute();
        $correosInventarios = $stmtCorreos->fetchAll(PDO::FETCH_ASSOC);

        $clave_encriptacion = $PASS_UNCRIPT;
        $arregloCorreos = [];
        foreach ($correosInventarios as $fila) {
            if (!empty($fila['usuario'])) {
                $correo = openssl_decrypt($fila['usuario'], 'AES-128-ECB', $clave_encriptacion);
                if ($correo) {
                    $arregloCorreos[] = $correo;
                }
            }
        }

        $text_alert = count($arregloCorreos) > 0
            ? "Requisición autorizada correctamente. Correo enviado exitosamente al área de Inventarios."
            : "Requisición autorizada correctamente. No se encontró ningún correo de Inventarios.";

        // 9. Enviar correo
        if (!empty($arregloCorreos)) {
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
            $mail->Subject = 'Nueva requisición pendiente. Folio: '.$id_requisicion;
            $body = "Se ha autorizado el maquinado de sello de una nueva requisición.<br>
                        Se necesita su ingreso al sistema para agregar y entregar los billets correspondientes.<br>
                        Folio de requisición: <b>" . $id_requisicion . "</b>";
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            if($SEND_MAIL === true){
                foreach ($arregloCorreos as $correo) {
                    if($DEV_MODE === false){
                        $mail->addAddress($correo);
                    }
                }
                if($DEV_MODE === true){
                    $mail->addAddress($DEV_EMAIL); // destinatario de control
                }
                if (!$mail->send()) {
                    $text_alert .= " Pero hubo un error al enviar el correo: " . $mail->ErrorInfo;
                }

            }
        }
        if($SEND_MAIL === false){
            $text_alert = "Requisición autorizada correctamente. Envío de correos no disponible.";
        }
        // Mostrar resultado en frontend
        echo '<script>document.addEventListener("DOMContentLoaded", function () {
            sweetAlertResponse("success", "Proceso finalizado", "'.$text_alert.'", "none");
        });</script><h3 class="p-5">Proceso finalizado, puede cerrar esta pestaña.</h3>';
        exit;

    } catch (Throwable $e) {
        // Revertir transacción en caso de error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        echo '<script>document.addEventListener("DOMContentLoaded", function () {
            sweetAlertResponse("error", "Error", "Ocurrió un error: ' . addslashes($e->getMessage()) . '", "none");
        });</script><h3 class="p-5">Proceso finalizado, puede cerrar esta pestaña.</h3>';
        exit;
    } finally {
        $conn = null;
    }

}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['token']) || empty($_GET['token'])) {
        echo '<script>document.addEventListener("DOMContentLoaded", function () {
            sweetAlertResponse("warning", "Acceso denegado", "Falta token de autorización", "none");
        });</script><h3 class="p-5">Proceso finalizado, puede cerrar esta pestaña.</h3>';
        exit;
    }

    $token = $_GET['token'];

    if (isset($_GET['id_requisicion'], $_GET['t'], $_GET['u']) && !empty($_GET['id_requisicion']) && !empty($_GET['t']) && !empty($_GET['u'])) {
        $id_requisicion = $_GET['id_requisicion'];
        $autoriza = $_GET['t'];

        if ($autoriza != "g" && $autoriza != "a" && $autoriza != "cnc") {
            echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("error", "Error", "Parametro no válido", "none");
            });</script>';
            exit;
        }

        // ✅ Validar que la requisición exista
        $stmt = $conn->prepare("SELECT COUNT(*) FROM requisiciones WHERE id_requisicion = :id");
        $stmt->bindParam(':id', $id_requisicion);
        $stmt->execute();
        $existe = $stmt->fetchColumn();

        if (!$existe) {
            echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("error", "Error", "La requisición no existe", "none");
            });</script><h3 class="p-5">Proceso finalizado, puede cerrar esta pestaña.</h3>';
            exit;
        }

        // ✅ Validar token después de tener todos los datos necesarios
        $stmt = $conn->prepare("SELECT * FROM tokens_autorizacion WHERE token = :token AND id_requisicion = :id AND autoriza = :autoriza AND TIMESTAMPDIFF(MINUTE, fecha_generado, NOW()) < 5");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':id', $id_requisicion);
        $stmt->bindParam(':autoriza', $autoriza);
        $stmt->execute();
        $tokenValido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$tokenValido) {
            echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("warning", "Token inválido o expirado", "Este enlace ya no es válido o ya fue utilizado.", "none");
            });</script><h3 class="p-5">Proceso finalizado, puede cerrar esta pestaña.</h3>';
            exit;
        }

    } else {
        echo '<script>document.addEventListener("DOMContentLoaded", function () {
            sweetAlertResponse("error", "Error", "Error en parámetros", "none");
        });</script><h3 class="p-5">Proceso finalizado, puede cerrar esta pestaña.</h3>';
        exit;
    }
}
?>
</head>
<body>
    <div class="d-flex flex-column justify-content-center align-items-center" >
        <h1 class="mt-2">Firmar requisición</h1>
        <h4>Folio: <?= htmlspecialchars($id_requisicion); ?></h4>
        <main class="d-flex flex-column justify-content-center align-items-center col-11" >
            <div class="d-flex justify-content-center align-items-center">
                <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modalInstrucciones">Instrucciones</button>
            </div>
            <section class="container-firma d-flex flex-column col-12 justify-content-center align-items-center">
                <canvas id="canvasFirma" ></canvas>
            </section>
            <section class="d-flex flex-column col-12 justify-content-center align-items-center mt-3">
                <button type="button" id="btnLimpiar" class="btn btn-secondary mb-2">Limpiar firma</button>
                <?php if ($autoriza == "g" || $autoriza == "a"): ?>
                    <div class="form-check mb-3 ">
                        <input class="form-check-input" type="checkbox" id="checkFirmaPredeterminada">
                        <label class="form-check-label" for="checkFirmaPredeterminada">
                            Marcar como firma predeterminada
                        </label>
                    </div>
                <?php endif; ?>
                <button type="button" id="btnAutorizar" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalEstasSeguro">Autorizar</button>
            </section>

        </main>
    </div>
    <footer>
        <p>&copy; <?= date("Y"); ?>Sellos y Retenes de San Luis S.A. de C.V. Todos los derechos reservados.</p>
    </footer>
<!-- //////////////////////////MODAL: Instrucciones /////////////////////// -->
<div class="modal fade" id="modalInstrucciones" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">Instrucciones para autorizar</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="d-flex flex-column gap-3">
                    <li>Dibuje su firma en el recuadro, después haga click en el boton Autorizar y luego en Continuar para confirmar.</li>
                    <li>Si lo desea puede marcar la firma como predeterminada y usarla para autorizar futuras requisiciones sin dibujar una nueva.</li>
                    <li>Al firmar siempre podrá decidir si desea actualizar la firma predeterminada.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
<!-- //////////////////////////MODAL: ENVIAR ESTAS SEGURO DE AUTORIZAR? /////////////////////// -->
<div class="modal fade" id="modalEstasSeguro" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">¿Desea continuar?</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><?php if($autoriza=="g"){
                    echo 'Esta acción notificará al área de Inventarios para continuar con el proceso de entrega de billets.';
                }else if($autoriza=="a"){
                    echo 'Esta acción notificará al área de Inventarios para continuar con el proceso de entrega de billets.';
                }else{
                    echo 'Esta acción actualizara el estatus a En producción y habilitará el control de almacen de la requisición.';
                }?></p>
                <form id="formContinuarAutorizar" action="" method="POST">
                    <input id="inputRequisicionAutorizada" type="hidden" name="id_requisicion" value="<?= htmlspecialchars($id_requisicion); ?>">
                    <input type="hidden" name="t" value="<?= htmlspecialchars($autoriza); ?>">
                    <input type="hidden" name="action" value="autorizada">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
                    <input type="hidden" id="inputPredeterminada" name="predeterminada" value="0">
                    <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($_GET['u']); ?>">
                    <button id="btnContinuar" type="button" class="btn-general">Continuar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
<div id="nada"></div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const checkboxFirma = document.getElementById('checkFirmaPredeterminada');
        if (checkboxFirma) {
            checkboxFirma.addEventListener('change', function () {
                document.querySelector("#inputPredeterminada").value = checkboxFirma.checked ? "1" : "0";
                console.log("✅ Valor predeterminada:", document.querySelector("#inputPredeterminada").value);
            });
        }


        const container = document.querySelector('.container-firma');
        const canvas = document.getElementById('canvasFirma');
        const signaturePad = new SignaturePad(canvas);
        
        function ajustarSizeCanvas() {
            const ancho = 400; // en píxeles reales
            const alto = 200;  // estándar para firma
            
            canvas.width = ancho; // tamaño interno del canvas
            canvas.height = alto;
            
            // también tamaño visual para evitar distorsión
            canvas.style.width = ancho + 'px';
            canvas.style.height = alto + 'px';
        }
        ajustarSizeCanvas();
        
        // Ajustar al cargar
        window.addEventListener('load', ajustarSizeCanvas);

        // Ajustar también si la ventana se redimensiona (opcional)
        window.addEventListener('resize', () => {
            ajustarSizeCanvas();
            signaturePad.clear(); // Borra la firma si redimensiona, evita deformación
        });

        document.getElementById('btnLimpiar').addEventListener('click', () => {
            signaturePad.clear();
        });

        document.getElementById('btnContinuar').addEventListener('click', () => {
            if (signaturePad.isEmpty()) {
                sweetAlertResponse("warning", "Atención", "Por favor firme antes de autorizar.", "none");
                return;
            }

            // Convertir la firma base64 a Blob
            function base64ToBlob(base64) {
                const parts = base64.split(',');
                const mime = parts[0].match(/:(.*?);/)[1];
                const byteChars = atob(parts[1]);
                const byteNumbers = new Array(byteChars.length);
                for (let i = 0; i < byteChars.length; i++) {
                    byteNumbers[i] = byteChars.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                return new Blob([byteArray], { type: mime });
            }

            const firmaBase64 = signaturePad.toDataURL('image/png');
            const blobFirma = base64ToBlob(firmaBase64);
            const esPredeterminada = document.querySelector("#inputPredeterminada").value;

            const formData = new FormData();
            formData.append('id_requisicion', <?= json_encode($id_requisicion) ?>);
            formData.append('autoriza', <?= json_encode($autoriza) ?>);
            formData.append('firma', blobFirma, 'firma.png');
            formData.append('predeterminada', esPredeterminada);
            formData.append('u', <?= $_GET['u']?>);
            $(this).addClass("d-none");
            $.ajax({
                url: '../ajax/ajax_guardar_firma.php',
                type: 'POST',
                dataType: 'json',
                data: formData,
                processData: false,  // importante para FormData
                contentType: false,  // importante para FormData
                success: function(response) {
                    if (response.success) {
                        console.log("✅ Firma guardada correctamente:", response);
                        // Enviar formulario solo si se guardó la firma
                        document.getElementById('formContinuarAutorizar').submit();
                    } else {
                        console.error("⚠️ Error al guardar firma:", response);
                        sweetAlertResponse("error", "Error", response.error || "Error inesperado al guardar la firma", "none");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("❌ Error AJAX - Detalles:");
                    console.error("Estado HTTP:", xhr.status);
                    console.error("Texto de estado:", status);
                    console.error("Mensaje del servidor:", error);
                    console.error("Respuesta completa:", xhr.responseText);
                    sweetAlertResponse("error", "Error", "No se pudo guardar la firma", "none");
                }
            });
        });
    });
</script>

</body>
</html>
