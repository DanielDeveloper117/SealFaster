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
    $camposRequeridos = ['id_requisicion', 'id_control', 'barra_a', 'barra_b', 'justificacion_remplazo'];
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
    $barra_a = trim($_POST['barra_a']);
    $barra_b = trim($_POST['barra_b']);
    $justificacion_remplazo = trim($_POST['justificacion_remplazo']);

    // Validar longitud de justificación
    if (strlen($justificacion_remplazo) < 10) {
        echo json_encode([
            'success' => false,
            'message' => "La justificación debe tener al menos 10 caracteres"
        ]);
        exit;
    }

    // Validar estatus de la requisición
    $stmtRequisicion = $conn->prepare("SELECT estatus FROM requisiciones WHERE id_requisicion = :id_requisicion LIMIT 1");
    $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtRequisicion->execute();
    $requisicion = $stmtRequisicion->fetch(PDO::FETCH_ASSOC);

    if (!$requisicion) {
        echo json_encode([
            'success' => false,
            'message' => "No se encontró la requisición con ID: $id_requisicion"
        ]);
        exit;
    }

    $estatusPermitidos = ['Autorizada', 'Producción', 'En producción'];
    if (!in_array($requisicion['estatus'], $estatusPermitidos)) {
        echo json_encode([
            'success' => false,
            'message' => "La requisición debe estar 'Autorizada' en 'Producción' o 'En producción'. Estatus actual: " . $requisicion['estatus']
        ]);
        exit;
    }

    // Consultar ambas barras en inventario_cnc
    $stmtBarraA = $conn->prepare("SELECT * FROM inventario_cnc WHERE lote_pedimento = :barra_a LIMIT 1");
    $stmtBarraA->bindParam(':barra_a', $barra_a);
    $stmtBarraA->execute();
    $inventario_cnc_a = $stmtBarraA->fetch(PDO::FETCH_ASSOC);

    $stmtBarraB = $conn->prepare("SELECT * FROM inventario_cnc WHERE lote_pedimento = :barra_b LIMIT 1");
    $stmtBarraB->bindParam(':barra_b', $barra_b);
    $stmtBarraB->execute();
    $inventario_cnc_b = $stmtBarraB->fetch(PDO::FETCH_ASSOC);

    // Validar existencia de ambas barras
    if (!$inventario_cnc_a) {
        echo json_encode([
            'success' => false,
            'message' => "La barra con lote pedimento '$barra_a' no existe en el inventario"
        ]);
        exit;
    }

    if (!$inventario_cnc_b) {
        echo json_encode([
            'success' => false,
            'message' => "La barra con lote pedimento '$barra_b' no existe en el inventario"
        ]);
        exit;
    }

    // Validar que los materiales sean iguales
    if ($inventario_cnc_a['material'] !== $inventario_cnc_b['material']) {
        echo json_encode([
            'success' => false,
            'message' => "Los materiales deben ser los mismos. El material de ".$inventario_cnc_a['lote_pedimento']." es ". $inventario_cnc_a['material'] . " pero el material de ".$inventario_cnc_b['lote_pedimento']." es " . $inventario_cnc_b['material']
        ]);
        exit;
    }

    // Validar estatus de la barra B
    if ($inventario_cnc_b['estatus'] !== "Disponible para cotizar") {
        echo json_encode([
            'success' => false,
            'message' => "La barra de reemplazo debe estar 'Disponible para cotizar'. Estatus actual: " . $inventario_cnc_b['estatus']
        ]);
        exit;
    }

    // Actualizar control_almacen con los datos de la barra B
    $stmtUpdate = $conn->prepare("
        UPDATE control_almacen 
        SET es_remplazo = 1,
            clave_remplazo = :clave_remplazo,
            lp_remplazo = :lp_remplazo,
            medida_remplazo = :medida_remplazo,
            justificacion_remplazo = :justificacion_remplazo
        WHERE id_control = :id_control
    ");

    $stmtUpdate->bindParam(':clave_remplazo', $inventario_cnc_b['Clave']);
    $stmtUpdate->bindParam(':lp_remplazo', $inventario_cnc_b['lote_pedimento']);
    $stmtUpdate->bindParam(':medida_remplazo', $inventario_cnc_b['Medida']);
    $stmtUpdate->bindParam(':justificacion_remplazo', $justificacion_remplazo);
    $stmtUpdate->bindParam(':id_control', $id_control, PDO::PARAM_INT);

    if (!$stmtUpdate->execute()) {
        throw new Exception("Error al actualizar control_almacen");
    }

    // --- Nueva consulta: marcar la barra de reemplazo como 'En uso' en inventario_cnc ---
    $stmtUpdateInventario = $conn->prepare("
        UPDATE inventario_cnc
        SET estatus = 'En uso'
        WHERE lote_pedimento = :lp_remplazo
    ");

    $stmtUpdateInventario->bindParam(':lp_remplazo', $inventario_cnc_b['lote_pedimento']);

    if (!$stmtUpdateInventario->execute()) {
        throw new Exception("Error al actualizar estatus de inventario_cnc para la barra de reemplazo");
    }

    if($inventario_cnc_a['Clave'] == $inventario_cnc_b['Clave']){
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

            if (!$stmtUpdate->execute()) {
                $conn->rollBack(); 
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
                    $conn->rollBack(); 
                    throw new Exception("Error al actualizar el estado de la requisición");
                }
            }

            // Confirmar transacción
            $conn->commit();

            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => "Barra remplazada correctamente, autorización automática "
            ]);
            exit;

        } catch (Exception $e) {
            error_log("Error en solicitar_remplazo_barra: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            http_response_code(500);
            exit;
        }
    }

    // --- Nueva consulta: marcar en requisiciones que hay una barra pendiente (barra_pendiente = 1)
    $stmtUpdateRequisicion = $conn->prepare("
        UPDATE requisiciones 
        SET barra_pendiente = 1
        WHERE id_requisicion = :id_requisicion
    ");

    $stmtUpdateRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);

    if (!$stmtUpdateRequisicion->execute()) {
        throw new Exception("Error al actualizar el campo barra_pendiente en requisiciones");
    }

    // Preparar datos para el correo
    $barraCompletaA = $inventario_cnc_a['Clave'] . " " . $inventario_cnc_a['lote_pedimento'] . " (" . $inventario_cnc_a['Medida'] . ")";
    $barraCompletaB = $inventario_cnc_b['Clave'] . " " . $inventario_cnc_b['lote_pedimento'] . " (" . $inventario_cnc_b['Medida'] . ")";
    $asunto = "Solicitud de reemplazo de barra. Folio: " . $id_requisicion;
    
    $cuerpo = "Inventarios ha solicitado la autorización de un reemplazo de barra para la requisición de maquinado con folio: <b>" . $id_requisicion . "</b>.</br>";
    $cuerpo .= "Ingrese al sistema en el módulo de Requisiciones para autorizar.</br>";
    $cuerpo .= "La barra: <b>" . $barraCompletaA . "</b></br>";
    $cuerpo .= "solicita ser reemplazada por: <b>" . $barraCompletaB . "</b></br>";
    $cuerpo .= " Justificación de reemplazo de barra:</br><b>";
    $cuerpo .= $justificacion_remplazo."</b>";

    // Enviar correo
    $mail = null;
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

        $sqlCorreoInventarios = "SELECT usuario FROM login WHERE rol = 'CORREO_DIRECCION'";
        $stmt = $conn->prepare($sqlCorreoInventarios);
        $stmt->execute();
        $correosInventarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$correosInventarios || count($correosInventarios) === 0) {
            throw new Exception("No se encontró ningún correo de dirección comercial.");
        }

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

        if ($contadorCorreos === 0) {
            throw new Exception("No se pudo agregar ningún destinatario válido para dirección comercial.");
        }

        $mail->isHTML(true);
        // Agregar correo visible de prueba o destinatario único
        if($DEV_MODE === true){
            $mail->addAddress($DEV_EMAIL);
        }
        $mail->Subject = $asunto;
        $mail->Body = $cuerpo;
        if($SEND_MAIL === true){
            if (!$mail->send()) {
                throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
            }
            $mensajeCorreo = "Solicitud de reemplazo de barra enviada correctamente y correo enviado a dirección comercial.";
        }else{
            $mensajeCorreo = "Solicitud de reemplazo de barra creada correctamente. Envío de correos no disponible.";
        }

    } catch (Throwable $e) {
        if($SEND_MAIL === true){
            $mensajeCorreo = "Solicitud de reemplazo de barra procesada correctamente, pero error al enviar correo: " . 
                            addslashes($e->getMessage()) .
                            (($mail && $mail->ErrorInfo) ? " - " . $mail->ErrorInfo : "");
        }else{
            $mensajeCorreo = "Solicitud de reemplazo de barra procesada correctamente. Envío de correos no disponible.";
        }
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => $mensajeCorreo
    ]);

} catch (PDOException $e) {
    error_log("Error en solicitar_remplazo_barra: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
    http_response_code(500);
} catch (Exception $e) {
    error_log("Error en solicitar_remplazo_barra: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    http_response_code(500);
} finally {
    $conn = null;
}
?>