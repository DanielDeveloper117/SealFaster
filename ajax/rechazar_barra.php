<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

try {
    header('Content-Type: application/json');

    // Validar que todos los campos POST estén presentes
    $camposRequeridos = ['id_requisicion', 'id_control', 'accion', 'razon'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo json_encode([
                'success' => false,
                'message' => "Campo requerido faltante: $campo"
            ]);
            exit;
        }
    }

    $id_requisicion = trim($_POST['id_requisicion']);
    $id_control = trim($_POST['id_control']);
    $accion = trim($_POST['accion']);
    $razon = trim($_POST['razon']);

    // Validar que la acción sea válida
    if (!in_array($accion, ['remplazo', 'extra'])) {
        echo json_encode([
            'success' => false,
            'message' => "Acción no válida: $accion"
        ]);
        exit;
    }

    // Validar longitud de la razón
    if (strlen($razon) < 10) {
        echo json_encode([
            'success' => false,
            'message' => "La razón del rechazo debe tener al menos 10 caracteres"
        ]);
        exit;
    }

    // Iniciar transacción
    $conn->beginTransaction();

    try {
        // 1. Obtener datos del registro en control_almacen
        $stmtControl = $conn->prepare("
            SELECT lote_pedimento, lp_remplazo, clave, medida, perfil_sello, material 
            FROM control_almacen 
            WHERE id_control = :id_control 
            AND id_requisicion = :id_requisicion
        ");
        $stmtControl->bindParam(':id_control', $id_control, PDO::PARAM_INT);
        $stmtControl->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        $stmtControl->execute();
        $registroControl = $stmtControl->fetch(PDO::FETCH_ASSOC);

        if (!$registroControl) {
            throw new Exception("No se encontró el registro con id_control: $id_control");
        }

        // 2. Determinar qué lote_pedimento actualizar en inventario_cnc
        $lote_a_actualizar = '';
        
        if ($accion === 'remplazo') {
            $lote_a_actualizar = $registroControl['lp_remplazo'];
        } else { // $accion === 'extra'
            $lote_a_actualizar = $registroControl['lote_pedimento'];
        }

        if (empty($lote_a_actualizar)) {
            throw new Exception("No se pudo determinar el lote pedimento a actualizar");
        }

        // 3. Actualizar estatus en inventario_cnc a "Disponible para cotizar"
        $stmtUpdateInventario = $conn->prepare("
            UPDATE inventario_cnc 
            SET estatus = 'Disponible para cotizar' 
            WHERE lote_pedimento = :lote_pedimento
        ");
        $stmtUpdateInventario->bindParam(':lote_pedimento', $lote_a_actualizar);
        
        if (!$stmtUpdateInventario->execute()) {
            throw new Exception("Error al actualizar el estatus del inventario");
        }

        // Verificar que se actualizó al menos un registro
        if ($stmtUpdateInventario->rowCount() === 0) {
            throw new Exception("El lote pedimento '$lote_a_actualizar' no existe en inventario_cnc");
        }

        // 4. Ejecutar acciones específicas según el tipo
        if ($accion === 'remplazo') {
            // Actualizar campos de reemplazo a null/0
            $stmtUpdateRemplazo = $conn->prepare("
                UPDATE control_almacen 
                SET clave_remplazo = NULL,
                    lp_remplazo = NULL,
                    medida_remplazo = NULL,
                    justificacion_remplazo = NULL,
                    es_remplazo = 0
                WHERE id_control = :id_control
            ");
            $stmtUpdateRemplazo->bindParam(':id_control', $id_control, PDO::PARAM_INT);
            
            if (!$stmtUpdateRemplazo->execute()) {
                throw new Exception("Error al actualizar los campos de reemplazo");
            }
            
        } else { // $accion === 'extra'
            // Eliminar el registro de control_almacen
            $stmtDeleteExtra = $conn->prepare("
                DELETE FROM control_almacen 
                WHERE id_control = :id_control
            ");
            $stmtDeleteExtra->bindParam(':id_control', $id_control, PDO::PARAM_INT);
            
            if (!$stmtDeleteExtra->execute()) {
                throw new Exception("Error al eliminar la barra extra");
            }
        }

        // 5. Verificar si quedan pendientes en la requisición
        $stmtPendientes = $conn->prepare("
            SELECT 
                es_remplazo, es_remplazo_auth, 
                es_extra, es_extra_auth 
            FROM control_almacen 
            WHERE id_requisicion = :id_requisicion
        ");
        $stmtPendientes->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        $stmtPendientes->execute();
        $registrosRequisicion = $stmtPendientes->fetchAll(PDO::FETCH_ASSOC);

        $hayPendientes = false;

        foreach ($registrosRequisicion as $reg) {
            // Verificar si hay reemplazos pendientes de autorizar
            if (isset($reg['es_remplazo']) && $reg['es_remplazo'] == 1 && 
                isset($reg['es_remplazo_auth']) && $reg['es_remplazo_auth'] == 0) {
                $hayPendientes = true;
                break;
            }
            
            // Verificar si hay extras pendientes de autorizar
            if (isset($reg['es_extra']) && $reg['es_extra'] == 1 && 
                isset($reg['es_extra_auth']) && $reg['es_extra_auth'] == 0) {
                $hayPendientes = true;
                break;
            }
        }

        // 6. Actualizar barra_pendiente en requisiciones si no hay pendientes
        if (!$hayPendientes) {
            $stmtUpdateRequisicion = $conn->prepare("
                UPDATE requisiciones 
                SET barra_pendiente = 0 
                WHERE id_requisicion = :id_requisicion
            ");
            $stmtUpdateRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
            
            if (!$stmtUpdateRequisicion->execute()) {
                throw new Exception("Error al actualizar el estado de la requisición");
            }
        }

        // Confirmar transacción
        $conn->commit();

        // 7. Preparar y enviar correo
        $mensajeCorreo = "";
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

            // Obtener correos de inventarios
            $sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6";
            $stmtCorreos = $conn->prepare($sqlCorreoInventarios);
            $stmtCorreos->execute();
            $correosInventarios = $stmtCorreos->fetchAll(PDO::FETCH_ASSOC);

            if ($correosInventarios && count($correosInventarios) > 0) {
                $clave_encriptacion = $PASS_UNCRIPT ?? '';
                $contadorCorreos = 0;

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
                $mail->isHTML(true);
                // Agregar correo de prueba
                if($DEV_MODE === true){
                    $mail->addAddress($DEV_EMAIL);
                }
                if ($contadorCorreos > 0) {
                    // Preparar contenido del correo
                    $tipoBarra = $accion === 'remplazo' ? 'remplazo de barra' : 'barra extra';
                    $barraCompleta = $registroControl['clave'] . " " . $lote_a_actualizar . " (" . $registroControl['medida'] . ")";
                    $asunto = "Solicitud rechazada para $tipoBarra. Folio: " . $id_requisicion;
                    
                    $cuerpo = "Se ha rechazado la solicitud de $tipoBarra para la requisición de maquinado con folio: <b>" . $id_requisicion . "</b>.</br>";
                    $cuerpo .= "Barra rechazada: <b>" . $barraCompleta . "</b></br>";
                    $cuerpo .= "Razón del rechazo:</br><b>";
                    $cuerpo .= $razon . "</b>";

                    $mail->Subject = $asunto;
                    $mail->Body = $cuerpo;

                    if($SEND_MAIL === true){
                        if (!$mail->send()) {
                            throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
                        }
                        $mensajeCorreo = " y correo de notificación enviado";
                    }
                } else {
                    throw new Exception("No se pudieron agregar destinatarios para el correo");
                }
            }
        } catch (Throwable $e) {
            $mensajeCorreo = ", pero error al enviar correo: " . $e->getMessage();
        }

        // Respuesta exitosa
        $tipoBarra = $accion === 'remplazo' ? 'reemplazo' : 'extra';
        if($SEND_MAIL === true){
            echo json_encode([
                'success' => true,
                'message' => "Barra $tipoBarra rechazada correctamente" . $mensajeCorreo,
                'no_hay_pendientes' => !$hayPendientes
            ]);
        }else{
            echo json_encode([
                'success' => true,
                'message' => "Barra $tipoBarra rechazada correctamente.  Envío de correos no disponible.",
                'no_hay_pendientes' => !$hayPendientes
            ]);            
        }

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log("Error en rechazar_barra: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
    http_response_code(500);
} catch (Exception $e) {
    error_log("Error en rechazar_barra: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    http_response_code(400);
} finally {
    $conn = null;
}
?>