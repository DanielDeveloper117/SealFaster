<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.', 405);
    }

    if (!isset($_POST['id_requisicion']) || !ctype_digit($_POST['id_requisicion'])) {
        throw new Exception("Parámetros incompletos o inválidos.");
    }

    $id_requisicion = intval($_POST['id_requisicion']);

    // --- INICIO DE TRANSACCIÓN ---
    $conn->beginTransaction();

    // 1. ELIMINAR registros en control_almacen coincidentes
    // Borramos los registros que se crearon cuando la requisición fue autorizada.
    $stmtDelControl = $conn->prepare("DELETE FROM control_almacen WHERE id_requisicion = :id_requisicion");
    $stmtDelControl->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtDelControl->execute();

    // 2. Actualizar estatus de la Requisición a Pendiente y limpiar firmas
    $stmtReq = $conn->prepare("UPDATE requisiciones 
                                SET estatus = 'Pendiente',
                                    ruta_firma = null,
                                    ruta_firma_admin = null,
                                    autorizo = null,
                                    fecha_autorizacion = null
                                WHERE id_requisicion = :id_requisicion");
    $stmtReq->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtReq->execute();

    if ($stmtReq->rowCount() === 0) {
        throw new Exception('No se encontró la requisición o no se realizaron cambios.');
    }

    // 3. Obtener cotizaciones para revertir inventario
    $sqlRequisicion = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
    $stmtRequisicion = $conn->prepare($sqlRequisicion);
    $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion);
    $stmtRequisicion->execute();
    $result = $stmtRequisicion->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['cotizaciones'])) {
        $cotizacion_ids = explode(', ', $result['cotizaciones']);

        // 4. Actualizar estado de cotizaciones
        $sqlUpdateCot = "UPDATE cotizacion_materiales SET estatus_completado = 'Cotización', fecha_actualizacion = NOW() WHERE id_cotizacion = :id_cotizacion";
        $stmtUpdateCot = $conn->prepare($sqlUpdateCot);

        // 5. Revertir Inventario (pre_stock = stock)
        $placeholders = str_repeat('?,', count($cotizacion_ids) - 1) . '?';
        $sqlBillets = "SELECT DISTINCT billets FROM cotizacion_materiales WHERE id_cotizacion IN ($placeholders)";
        $stmtBillets = $conn->prepare($sqlBillets);
        $stmtBillets->execute($cotizacion_ids);
        $billetsResults = $stmtBillets->fetchAll(PDO::FETCH_ASSOC);

        $lotesUnicos = [];
        foreach ($billetsResults as $row) {
            if (!empty($row['billets'])) {
                $billetsArr = array_map('trim', explode(',', $row['billets']));
                foreach ($billetsArr as $billet) { $lotesUnicos[$billet] = true; }
            }
        }

        if (!empty($lotesUnicos)) {
            $lotesArray = array_keys($lotesUnicos);
            $placeholdersLotes = str_repeat('?,', count($lotesArray) - 1) . '?';
            $sqlInv = "UPDATE inventario_cnc SET pre_stock = stock, estatus = 'Disponible para cotizar', updated_at = NOW() 
                       WHERE lote_pedimento IN ($placeholdersLotes)";
            $stmtInv = $conn->prepare($sqlInv);
            $stmtInv->execute($lotesArray);
        }

        foreach ($cotizacion_ids as $id_cot) {
            $stmtUpdateCot->execute([':id_cotizacion' => $id_cot]);
        }
    }

    // Si todo salió bien hasta aquí, confirmamos los cambios
    $conn->commit();

    $mail = null;

    try {
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

        // Obtener id_vendedor de la requisición
        $stmt = $conn->prepare("SELECT id_vendedor FROM requisiciones WHERE id_requisicion = :id_requisicion");
        $stmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        $stmt->execute();
        $idVendedor = $stmt->fetch(PDO::FETCH_ASSOC)['id_vendedor'] ?? null;

        if (!$idVendedor) {
            throw new Exception("No se encontró el vendedor asociado.");
        }

        // Obtener correo del vendedor
        $stmt = $conn->prepare("SELECT usuario FROM login WHERE id = :id_usuario");
        $stmt->bindParam(':id_usuario', $idVendedor, PDO::PARAM_INT);
        $stmt->execute();
        $correoVendedor = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$correoVendedor || count($correoVendedor) === 0) {
            throw new Exception("No se encontró correo de vendedor.");
        }

        $clave_encriptacion = $PASS_UNCRIPT ?? '';
        $contadorCorreos = 0;

        foreach ($correoVendedor as $fila) {
            if (!empty($fila['usuario'])) {
                $correo = openssl_decrypt($fila['usuario'], 'AES-128-ECB', $clave_encriptacion);
                if ($correo) {
                    if($DEV_MODE === false){
                        $mail->addAddress($correo);
                    }
                    $contadorCorreos++;
                }
            }
        }

        $sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6";
        $stmt = $conn->prepare($sqlCorreoInventarios);
        $stmt->execute();
        $correosInventarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$correosInventarios || count($correosInventarios) === 0) {
            throw new Exception("No se encontro ningun correo de inventarios.");
        }

        foreach ($correosInventarios as $fila) {
            if (!empty($fila['usuario'])) {
                $correo = openssl_decrypt($fila['usuario'], 'AES-128-ECB', $clave_encriptacion);
                if ($correo) {
                    if($DEV_MODE === false){
                        $mail->addAddress($correo);
                    }
                    $contadorCorreos++;
                }
            }
        }

        if ($contadorCorreos === 0) {
            throw new Exception("No se pudo agregar ningún destinatario válido.");
        }

        // Correo visible de prueba
        if($DEV_MODE === true){
            $mail->addAddress($DEV_EMAIL);
        }
        $mail->Subject = 'Requisición cancelada. Folio: '.$id_requisicion;
        $mail->Body = "Se ha cancelado la autorización de una requisición.<br>Folio: <b>$id_requisicion</b>";
        $mail->AltBody = "Se ha cancelado la autorización de una requisición. Folio: $id_requisicion";
        if($SEND_MAIL === true){
            if (!$mail->send()) {
                throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
            }
        }

    } catch (Throwable $e) {
        // Si falla el correo, igual devolvemos éxito pero con advertencia
        if($SEND_MAIL === true){
            echo json_encode([
                'success' => true,
                'message' => "Requisición cancelada correctamente, pero hubo un error al enviar correo: " . addslashes($e->getMessage())
            ]);
        }else{
            echo json_encode([
                'success' => true,
                'message' => "Requisición cancelada correctamente"
            ]);
        }
        exit;
    }

    // Respuesta exitosa general
    if($SEND_MAIL === true){
        echo json_encode([
            'success' => true,
            'message' => 'Requisición cancelada correctamente y correos enviados con éxito.'
        ]);
    }else{
        echo json_encode([
            'success' => true,
            'message' => 'Requisición cancelada correctamente. Envío de correos no disponible.'
        ]);
    }
    exit;

} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>