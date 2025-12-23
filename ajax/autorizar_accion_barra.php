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
    $camposRequeridos = ['id_requisicion', 'id_control', 'accion'];
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

    // Validar que la acción sea válida
    if (!in_array($accion, ['remplazo', 'extra'])) {
        echo json_encode([
            'success' => false,
            'message' => "Acción no válida: $accion"
        ]);
        exit;
    }

    // Iniciar transacción
    $conn->beginTransaction();

    try {
        // 1. Verificar que exista el registro en control_almacen
        $stmtCheck = $conn->prepare("
            SELECT * FROM control_almacen 
            WHERE id_control = :id_control 
            AND id_requisicion = :id_requisicion
        ");
        $stmtCheck->bindParam(':id_control', $id_control, PDO::PARAM_INT);
        $stmtCheck->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        $stmtCheck->execute();
        $registro = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$registro) {
            throw new Exception("No se encontró el registro con id_control: $id_control e id_requisicion: $id_requisicion");
        }

        // 2. Verificar si ya estaba autorizado según la acción
        if ($accion === 'remplazo') {
            if (isset($registro['es_remplazo_auth']) && $registro['es_remplazo_auth'] == 1) {
                throw new Exception("Esta barra de reemplazo ya estaba autorizada");
            }
            
            // Actualizar autorización de reemplazo
            $stmtUpdate = $conn->prepare("
                UPDATE control_almacen 
                SET es_remplazo_auth = 1 
                WHERE id_control = :id_control
            ");
            $stmtUpdate->bindParam(':id_control', $id_control, PDO::PARAM_INT);
            
        } else { // $accion === 'extra'
            if (isset($registro['es_extra_auth']) && $registro['es_extra_auth'] == 1) {
                throw new Exception("Esta barra extra ya estaba autorizada");
            }
            
            // Actualizar autorización de extra
            $stmtUpdate = $conn->prepare("
                UPDATE control_almacen 
                SET es_extra_auth = 1 
                WHERE id_control = :id_control
            ");
            $stmtUpdate->bindParam(':id_control', $id_control, PDO::PARAM_INT);
        }

        if (!$stmtUpdate->execute()) {
            throw new Exception("Error al actualizar la autorización de la barra");
        }

        // 3. Consultar todos los registros de la requisición para verificar pendientes
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

        // 4. Actualizar barra_pendiente en requisiciones si no hay pendientes
        if (!$hayPendientes) {
            $stmtRequisicion = $conn->prepare("
                UPDATE requisiciones 
                SET barra_pendiente = 0 
                WHERE id_requisicion = :id_requisicion
            ");
            $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
            
            if (!$stmtRequisicion->execute()) {
                throw new Exception("Error al actualizar el estado de la requisición");
            }
            
            $mensajeAdicional = " Ya no hay barras pendientes por autorizar para esta requisición.";
        } else {
            $mensajeAdicional = "";
        }

        // Confirmar transacción
        $conn->commit();

        // Preparar y enviar correo (no bloquear la respuesta si falla el correo)
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

            // Obtener correos de inventarios (lider = 6)
            $sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6";
            $stmtCorreos = $conn->prepare($sqlCorreoInventarios);
            $stmtCorreos->execute();
            $correosInventarios = $stmtCorreos->fetchAll(PDO::FETCH_ASSOC);

            if ($correosInventarios && count($correosInventarios) > 0) {
                $clave_encriptacion = $PASS_UNCRIPT ?? '';
                $contadorCorreos = 0;

                foreach ($correosInventarios as $fila) {
                    if (!empty($fila['usuario'])) {
                        $correo = @openssl_decrypt($fila['usuario'], 'AES-128-ECB', $clave_encriptacion);
                        if ($correo) {
                            if($DEV_MODE === false){
                                $mail->addAddress($correo);
                            }
                            $contadorCorreos++;
                        }
                    }
                }
                $mail->isHTML(true);
                // Agregar correo de prueba adicional si se desea
                if($DEV_MODE === true){
                    $mail->addAddress($DEV_EMAIL);
                }
                if ($contadorCorreos > 0) {
                    // Preparar contenido del correo
                    $tipoBarra = $accion === 'remplazo' ? 'reemplazo de barra' : 'barra extra';

                    // Determinar descripción de la barra autorizada
                    if ($accion === 'remplazo') {
                        $claveBarra = $registro['clave_remplazo'] ?? $registro['clave'];
                        $loteBarra = $registro['lp_remplazo'] ?? $registro['lote_pedimento'];
                        $medidaBarra = $registro['medida_remplazo'] ?? $registro['medida'];
                    } else {
                        $claveBarra = $registro['clave'] ?? '';
                        $loteBarra = $registro['lote_pedimento'] ?? '';
                        $medidaBarra = $registro['medida'] ?? '';
                    }

                    $barraCompleta = trim(($claveBarra ?? '') . ' ' . ($loteBarra ?? '') . ' (' . ($medidaBarra ?? '') . ')');
                    $asunto = "Barra autorizada: $tipoBarra. Folio: " . $id_requisicion;

                    $cuerpo = "Se ha autorizado $tipoBarra para la requisición de maquinado con folio: <b>" . $id_requisicion . "</b>.</br>";
                    $cuerpo .= "Barra autorizada: <b>" . $barraCompleta . "</b>";

                    $mail->Subject = $asunto;
                    $mail->Body = $cuerpo;

                    if($SEND_MAIL === true){
                        if (!$mail->send()) {
                            throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
                        }
                    }
                    $mensajeCorreo = " y correo de notificación enviado";
                } else {
                    throw new Exception("No se pudieron agregar destinatarios para el correo");
                }
            }
        } catch (Throwable $e) {
            // No romper la respuesta si falla el correo
            $mensajeCorreo = ", pero error al enviar correo: " . $e->getMessage();
        }

        // Preparar respuesta exitosa (incluyendo nota sobre correo)
        $tipoBarra = $accion === 'remplazo' ? 'reemplazo' : 'extra';
        if($SEND_MAIL === true){
            echo json_encode([
                'success' => true,
                'message' => "Barra $tipoBarra autorizada correctamente." . $mensajeAdicional . $mensajeCorreo,
                'no_hay_pendientes' => !$hayPendientes
            ]);
        }else{
            echo json_encode([
                'success' => true,
                'message' => "Barra $tipoBarra autorizada correctamente. Envío de correos no disponible." . $mensajeAdicional,
                'no_hay_pendientes' => !$hayPendientes
            ]);
        }

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log("Error en autorizar_accion_barra: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
    http_response_code(500);
} catch (Exception $e) {
    error_log("Error en autorizar_accion_barra: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    http_response_code(400);
} finally {
    $conn = null;
}
?>