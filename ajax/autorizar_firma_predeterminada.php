<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
session_start();
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
try {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido. Solo se acepta POST.']);
        exit();
    }


    if (isset($_POST['id_requisicion'], $_POST['t']) && !empty($_POST['id_requisicion']) && !empty($_POST['t'])) {
        try {
            $id_requisicion = $_POST['id_requisicion'];
            $autoriza = $_POST['t'];
            $correo_destinatario = "desarrollo2.sistemas@sellosyretenes.com";
            $subject = "";
            $body = "";
            $text_alert = "";

            $id_usuario = $_SESSION['id'];
            $nombreArchivo = $id_usuario . ".png";
            $rutaBD = 'files/signatures/' . $nombreArchivo;

            if($autoriza == "g"){
                $sql = "UPDATE requisiciones SET 
                            estatus = 'Autorizada',
                            ruta_firma = :ruta
                        WHERE id_requisicion = :id_requisicion";

                $sqlCorreoDireccion = "SELECT usuario FROM login WHERE rol = 'CORREO_DIRECCION'";
                $stmtCorreoDireccion = $conn->prepare($sqlCorreoDireccion);
                $stmtCorreoDireccion->execute();
                $arregloCorreoDireccion = $stmtCorreoDireccion->fetch(PDO::FETCH_ASSOC);
                if (!$arregloCorreoDireccion || empty($arregloCorreoDireccion['usuario'])) {
                    $text_alert = "Requisicion autorizada correctamente. No se encontró correo de dirección.";
                }else{
                    $text_alert = "Requisicion autorizada correctamente. Correo enviado exitosamente a direccion para solicitar su autorizacion.";
                }
                $clave_encriptacion = 'SRS2024#tides';
                //$correo_destinatario = openssl_decrypt($arregloCorreoDireccion['usuario'], 'AES-128-ECB', $clave_encriptacion);
                $correo_destinatario = "desarrollo2.sistemas@sellosyretenes.com";

                $subject = "Nueva requisicion por autorizar.";
                $body = "Gerencia ha autorizado una requisicion para el maquinado de sello, valla a la seccion de produccion VN para finalizar la autorizacion con su firma."; 

            }elseif($autoriza == "a"){
                $sql = "UPDATE requisiciones SET 
                            estatus = 'Producción',
                            ruta_firma_admin = :ruta
                        WHERE id_requisicion = :id_requisicion";

                $sqlCorreoProduccion = "SELECT usuario FROM login WHERE rol = 'CORREO_PRODUCCION'";
                $stmtCorreoProduccion = $conn->prepare($sqlCorreoProduccion);
                $stmtCorreoProduccion->execute();
                $arregloCorreoProduccion = $stmtCorreoProduccion->fetch(PDO::FETCH_ASSOC);
                if (!$arregloCorreoProduccion || empty($arregloCorreoProduccion['usuario'])) {
                    $text_alert = "Requisicion autorizada correctamente. No se encontró correo de producción.";
                }else{
                    $text_alert = "Requisicion autorizada correctamente. Correo enviado exitosamente a CNC para comenzar el maquinado de sellos.";
                }
                $clave_encriptacion = 'SRS2024#tides';
                //$correo_destinatario = openssl_decrypt($arregloCorreoProduccion['usuario'], 'AES-128-ECB', $clave_encriptacion);
                $correo_destinatario = "desarrollo2.sistemas@sellosyretenes.com";

                $subject = "Requisicion de maquinado autorizada.";
                $body = "Direccion ha autorizado una requisicion para el maquinado de sello. La requisicion ya se encuentra disponible en el modulo de Produccion."; 
            }elseif($autoriza == "cnc"){
                $sql = "UPDATE requisiciones SET 
                            estatus = 'En producción',
                            inicio_maquinado = NOW()
                        WHERE id_requisicion = :id_requisicion";
                $correo_destinatario = "";
                $subject = "";
                $body = ""; 
                $text_alert = "Se guardo la firma correctamente, estatus actualizado correctamente.";
            }else{
                echo json_encode(['error' => "Parametro no valido "]);
                exit;
            }

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':ruta', $rutaBD);
            $stmt->bindParam(':id_requisicion', $id_requisicion);
            $stmt->execute();

        } catch (Throwable $e) {
            echo json_encode(['error' => "Error al intentar autorizar. " . addslashes($e->getMessage())]);
            exit;
        }
        ////////////////////////////PHP MAILER -> cotizador a admin ////////////////
        require_once(ROOT_PATH . 'includes/PHPMailer.php');
        $mail = getMailer($conn);
        try {
            if($autoriza != "cnc"){
                $id_requisicion = $_POST['id_requisicion'];

                $clave_encriptacion = 'SRS2024#tides';

                $mail->addAddress($correo_destinatario);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body =  $body."<br>Id de requisicion: ".$id_requisicion;
                $mail->send();
            }
            echo json_encode(['success' => true, 'message' => $text_alert]);
            exit; 
        }catch (Throwable $e) {
            echo json_encode([
                'success' => true,
                'message' => "Requisicion autorizada correctamente sin errores, pero hubo un error al enviar correo. " . addslashes($e->getMessage()) . ' - ' . $mail->ErrorInfo
            ]);
            exit;        
        }

        ////////////////////////////////////////////////////////////////////////
    }else{
        echo json_encode(['error' => "Error en parametros "]);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Throwable $e) {
    echo json_encode(['error' => 'Error inesperado: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>
