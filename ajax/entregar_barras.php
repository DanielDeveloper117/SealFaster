<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();

// Validar sesion
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}

// Manejo de errores como excepciones
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    header('Content-Type: application/json');

    // Validar metodo
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Metodo no permitido']);
        exit();
    }

    if (!isset($_POST['id_requisicion']) || empty(trim($_POST['id_requisicion']))) {
        throw new Exception("Id de requisicion faltante o vacío");
    }

    $id_requisicion = trim($_POST['id_requisicion']);

    if (!preg_match('/^\d+$/', $id_requisicion)) {
        throw new Exception("Parámetro 'id_requisicion' no es un número válido.");
    }

    //  Validar si existen registros en control_almacen con esta requisicion
    // Antes de proceder, verificar que no haya barras pendientes por autorizar
    $stmtBarraPendiente = $conn->prepare("SELECT barra_pendiente FROM requisiciones WHERE id_requisicion = :id_requisicion");
    $stmtBarraPendiente->bindValue(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtBarraPendiente->execute();
    $rowPendiente = $stmtBarraPendiente->fetch(PDO::FETCH_ASSOC);
    if ($rowPendiente && isset($rowPendiente['barra_pendiente']) && intval($rowPendiente['barra_pendiente']) === 1) {
        // Hay barras pendientes de autorización, no se puede entregar
        echo json_encode([
            'success' => false,
            'message' => 'No es posible entregar las barras porque la requisición tiene autorizaciones de barra pendientes.'
        ]);
        exit;
    }

    $sqlCheck = "SELECT COUNT(*) as total FROM control_almacen WHERE id_requisicion = :id_requisicion";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bindValue(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtCheck->execute();
    $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$row || $row['total'] == 0) {
        throw new Exception("Antes de enviar a producción, debe haber al menos una clave en control de inventario en la requisición.");
    }

    // Empezar transacción: actualizaremos control_almacen (si vienen registros) y luego actualizamos inventario
    $conn->beginTransaction();

    // Si el frontend envía los registros con los campos editados, los procesamos para mantener control_almacen sincronizado
    if (isset($_POST['registros']) && !empty($_POST['registros'])) {
        $registros = json_decode($_POST['registros'], true);
        if (!is_array($registros)) {
            $conn->rollBack();
            throw new Exception('Formato de registros inválido.');
        }

        $sqlUpdate = "UPDATE control_almacen SET perfil_sello = :perfil_sello, material = :material, clave = :clave, lote_pedimento = :lote_pedimento, medida = :medida, pz_teoricas = :pz_teoricas, altura_pz = :altura_pz, mm_entrega = :mm_entrega, mm_teoricos = :mm_teoricos WHERE id_control = :id_control";
        $stmtUpdate = $conn->prepare($sqlUpdate);

        foreach ($registros as $reg) {
            // Normalizar
            $id_control = isset($reg['id_control']) && is_numeric($reg['id_control']) ? intval($reg['id_control']) : null;
            $perfil_sello = isset($reg['perfil_sello']) ? trim($reg['perfil_sello']) : null;
            $material = isset($reg['material']) ? trim($reg['material']) : null;
            $clave = isset($reg['clave']) ? trim($reg['clave']) : null;
            $lote_pedimento = isset($reg['lote_pedimento']) ? trim($reg['lote_pedimento']) : null;
            $medida = isset($reg['medida']) ? trim($reg['medida']) : null;
            $pz_teoricas = isset($reg['pz_teoricas']) ? floatval($reg['pz_teoricas']) : 0;
            $altura_pz = isset($reg['altura_pz']) ? floatval($reg['altura_pz']) : 0;
            $mm_entrega = isset($reg['mm_entrega']) ? floatval($reg['mm_entrega']) : 0;
            $mm_teoricos = isset($reg['mm_teoricos']) ? floatval($reg['mm_teoricos']) : 0;

            if ($id_control) {
                // Intentar actualizar por id_control
                $stmtUpdate->bindValue(':perfil_sello', $perfil_sello, PDO::PARAM_STR);
                $stmtUpdate->bindValue(':material', $material, PDO::PARAM_STR);
                $stmtUpdate->bindValue(':clave', $clave, PDO::PARAM_STR);
                $stmtUpdate->bindValue(':lote_pedimento', $lote_pedimento, PDO::PARAM_STR);
                $stmtUpdate->bindValue(':medida', $medida, PDO::PARAM_STR);
                $stmtUpdate->bindValue(':pz_teoricas', $pz_teoricas);
                $stmtUpdate->bindValue(':altura_pz', $altura_pz);
                $stmtUpdate->bindValue(':mm_entrega', $mm_entrega);
                $stmtUpdate->bindValue(':mm_teoricos', $mm_teoricos);
                $stmtUpdate->bindValue(':id_control', $id_control, PDO::PARAM_INT);
                $stmtUpdate->execute();

                // Si no afectó filas, intentamos ubicar la fila por requisición + lote original
                if ($stmtUpdate->rowCount() === 0 && !empty($lote_pedimento)) {
                    $sqlFallback = "UPDATE control_almacen SET perfil_sello = :perfil_sello2, material = :material2, clave = :clave2, lote_pedimento = :lote_pedimento2, medida = :medida2, pz_teoricas = :pz_teoricas2, altura_pz = :altura_pz2, mm_entrega = :mm_entrega2, mm_teoricos = :mm_teoricos2 WHERE id_requisicion = :id_requisicion AND lote_pedimento = :lote_pedimento2";
                    $stmtFb = $conn->prepare($sqlFallback);
                    $stmtFb->bindValue(':perfil_sello2', $perfil_sello, PDO::PARAM_STR);
                    $stmtFb->bindValue(':material2', $material, PDO::PARAM_STR);
                    $stmtFb->bindValue(':clave2', $clave, PDO::PARAM_STR);
                    $stmtFb->bindValue(':lote_pedimento2', $lote_pedimento, PDO::PARAM_STR);
                    $stmtFb->bindValue(':medida2', $medida, PDO::PARAM_STR);
                    $stmtFb->bindValue(':pz_teoricas2', $pz_teoricas);
                    $stmtFb->bindValue(':altura_pz2', $altura_pz);
                    $stmtFb->bindValue(':mm_entrega2', $mm_entrega);
                    $stmtFb->bindValue(':mm_teoricos2', $mm_teoricos);
                    $stmtFb->bindValue(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
                    $stmtFb->execute();
                }
            } else {
                // No viene id_control: intentar actualizar por id_requisicion + lote_pedimento
                if (!empty($lote_pedimento)) {
                    $sqlUpdateByLote = "UPDATE control_almacen SET perfil_sello = :perfil_sello3, material = :material3, clave = :clave3, medida = :medida3, pz_teoricas = :pz_teoricas3, altura_pz = :altura_pz3, mm_entrega = :mm_entrega3, mm_teoricos = :mm_teoricos3 WHERE id_requisicion = :id_requisicion AND lote_pedimento = :lote_pedimento3";
                    $stmtLote = $conn->prepare($sqlUpdateByLote);
                    $stmtLote->bindValue(':perfil_sello3', $perfil_sello, PDO::PARAM_STR);
                    $stmtLote->bindValue(':material3', $material, PDO::PARAM_STR);
                    $stmtLote->bindValue(':clave3', $clave, PDO::PARAM_STR);
                    $stmtLote->bindValue(':pz_teoricas3', $pz_teoricas);
                    $stmtLote->bindValue(':altura_pz3', $altura_pz);
                    $stmtLote->bindValue(':mm_entrega3', $mm_entrega);
                    $stmtLote->bindValue(':mm_teoricos3', $mm_teoricos);
                    $stmtLote->bindValue(':medida3', $medida, PDO::PARAM_STR);
                    $stmtLote->bindValue(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
                    $stmtLote->bindValue(':lote_pedimento3', $lote_pedimento, PDO::PARAM_STR);
                    $stmtLote->execute();
                }
            }
        }
    }

    // Recuperar nuevamente los lotes (actualizados) para afectar inventario
    $sqlLotesPedimento = "SELECT * FROM control_almacen WHERE id_requisicion = :id_requisicion";
    $stmtLP = $conn->prepare($sqlLotesPedimento);
    $stmtLP->bindParam(':id_requisicion', $id_requisicion);
    $stmtLP->execute();
    $arrayLP = $stmtLP->fetchAll();

    $missingLotes = [];
    $updatedLotes = 0;

    foreach ($arrayLP as $LP) {
        if($LP['es_remplazo'] == 1 && $LP['es_remplazo_auth'] == 1){
            $lote = trim($LP['lp_remplazo']);
        }else{
            $lote = trim($LP['lote_pedimento']);   
        }

        // Preparar y ejecutar update una vez
        $sqlEstatusLP = "UPDATE inventario_cnc 
                        SET estatus = 'Maquinado en curso'
                        WHERE lote_pedimento = :lote_pedimento";
        $stmtEstatusLP = $conn->prepare($sqlEstatusLP);
        $stmtEstatusLP->bindParam(':lote_pedimento', $lote);
        $stmtEstatusLP->execute();

        // Verificar si se afectó alguna fila
        if ($stmtEstatusLP->rowCount() === 0) {
            // No se encontró el lote; registramos y continuamos
            $missingLotes[] = $lote;
            continue;
        }

        $updatedLotes++;
    }

    if (count($missingLotes) > 0) {
        //$msjLotes = "No se encontraron las siguientes barras para Deshabilitarlas: " . implode(', ', $missingLotes);
        $msjLotes = "";
    } else {
        $msjLotes = "";
    }

    //  Actualizar requisicion
    $sql = "UPDATE requisiciones 
            SET estatus = 'Producción', fecha_entrega_barras = NOW()
            WHERE id_requisicion = :id_requisicion";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmt->execute();

    // Commit de la transacción antes de enviar correos (no queremos que fallos de correo deshagan las actualizaciones)
    if ($conn->inTransaction()) {
        $conn->commit();
    }

    ////////////////////////////PHP MAILER -> cotizador a CNC ////////////////
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

        $sqlCorreoProduccion = "SELECT usuario FROM login WHERE lider = 2 AND rol = 'Gerente'";
        $stmt = $conn->prepare($sqlCorreoProduccion);
        $stmt->execute();
        $correosProduccion = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$correosProduccion || count($correosProduccion) === 0) {
            throw new Exception("No se encontro ningún correo de producción.");
        }

        $clave_encriptacion = $PASS_UNCRIPT ?? '';
        $contadorCorreos = 0;

        foreach ($correosProduccion as $fila) {
            if (!empty($fila['usuario'])) {
                $correo = openssl_decrypt($fila['usuario'], 'AES-128-ECB', $clave_encriptacion);
                $correo = trim($correo);
                if ($correo) {
                    if($DEV_MODE === false){
                        $mail->addAddress($correo);
                    }
                    $contadorCorreos++;
                }
            }
        }

        if ($contadorCorreos === 0) {
            throw new Exception("No se pudo agregar ningún destinatario valido para producción.");
        }
        if($DEV_MODE === true){
            $mail->addAddress($DEV_EMAIL); 
        }
        $mail->Subject = 'Nueva requisición para maquinado. Folio: '.$id_requisicion;
        $mail->Body = "Inventarios ha liberado una nueva requisición de maquinado de sellos con las barras solicitadas.<br>
                    Se ha cambiado el estatus a <b>Producción</b>.<br>
                    Folio de requisición: <b>".$id_requisicion."</b>";
        if($SEND_MAIL === true){
            if (!$mail->send()) {
                throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
            }   
        }

    } catch (Throwable $e) {
        echo json_encode([
            'success' => false,
            'message' => "Error al enviar correo: " . $e->getMessage()
        ]);
        exit;
    }
    //////////////////////////////////////////////////////////////////////


    // Verificar si se actualizó
    if ($stmt->rowCount() > 0) {
        if($SEND_MAIL === true){
            echo json_encode([
                'success' => true,
                'message' => "Correo enviado exitosamente a CNC. Estatus de requisición cambiado a Producción. ".$msjLotes
            ]);

        }else{
            echo json_encode([
                'success' => true,
                'message' => "Estatus de requisición cambiado a Producción. Envío de correos no disponible. ".$msjLotes
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => "No se encontró la requisición o ya estaba en Producción."
        ]);
    }

} catch (Exception $e) {
    // Si hay una transacción abierta, revertirla
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
