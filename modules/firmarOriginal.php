<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
header('Content-Type: text/html; charset=UTF-8');
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
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.6/dist/signature_pad.umd.min.js"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-firmar.css'); ?>">
    <title>Firma de Requisición</title>
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['token'] ?? '';
    $id_requisicion = $_POST['id_requisicion'] ?? '';
    $autoriza = $_POST['t'] ?? '';

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

        // Actualizar requisición
        $sql = "UPDATE requisiciones SET estatus = 'Autorizada' WHERE id_requisicion = :id_requisicion";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_requisicion', $id_requisicion);
        $stmt->execute();

        // Eliminar token
        $stmtDeleteToken = $conn->prepare("DELETE FROM tokens_autorizacion WHERE token = :token");
        $stmtDeleteToken->bindParam(':token', $token);
        $stmtDeleteToken->execute();

        // Preparar correos
        //$sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6 AND rol = 'Gerente'";
        $sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6";
        $stmtCorreos = $conn->prepare($sqlCorreoInventarios);
        $stmtCorreos->execute();
        $correosInventarios = $stmtCorreos->fetchAll(PDO::FETCH_ASSOC);

        $clave_encriptacion = 'SRS2024#tides';
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

        // Enviar correo
        if (!empty($arregloCorreos)) {
            require_once(ROOT_PATH . 'includes/PHPMailer.php');
            $mail = getMailer($conn);
            $mail->Subject = 'Nueva requisición pendiente. Folio: '.$id_requisicion;
            $body = "Se ha autorizado el maquinado de sello de una nueva requisición.<br>
                        Se necesita su ingreso al sistema para agregar y entregar los billets correspondientes.<br>
                        Folio de requisición: <b>" . $id_requisicion . "</b>";
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);

            foreach ($arregloCorreos as $correo) {
                $mail->addAddress($correo);
            }
            $mail->addAddress("desarrollo2.sistemas@sellosyretenes.com"); // destinatario de control

            if (!$mail->send()) {
                $text_alert .= " Pero hubo un error al enviar el correo: " . $mail->ErrorInfo;
            }
        }

        // Mostrar resultado en frontend
        echo '<script>document.addEventListener("DOMContentLoaded", function () {
            sweetAlertResponse("success", "Proceso finalizado", "'.$text_alert.'", "none");
        });</script><h3 class="p-5">Proceso finalizado, puede cerrar esta pestaña.</h3>';
        exit;

    } catch (Throwable $e) {
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
