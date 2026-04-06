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
    $msjExtra = "";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Metodo no permitido']);
        exit();
    }

    if (!isset($_POST['registros']) || empty($_POST['registros'])) {
        echo json_encode(['success' => false, 'error' => 'No se recibieron registros']);
        exit();
    }

    $data = json_decode($_POST['registros'], true);

    if (!is_array($data) || count($data) === 0) {
        echo json_encode(['success' => false, 'error' => 'Formato de datos invalido']);
        exit();
    }

    // Contar cuántas de las barras recibidas vienen como merma
    $barrasMermaRecibidas = 0;
    foreach ($data as $fila) {
        if (isset($fila['es_merma']) && $fila['es_merma'] == 1) {
            $barrasMermaRecibidas++;
        }
    }

    // Validar que no todas las barras recibidas sean merma
    if ($barrasMermaRecibidas === count($data)) {
        echo json_encode([
            'success' => false,
            'error' => 'No se puede marcar todas las barras recibidas como merma. Debe quedar al menos una barra no marcada.'
        ]);
        exit();
    }
    
    // Obtener id_requisicion usando el primer id_control
    $firstIdControl = $data[0]['id_control'];
    $sqlGetRequisicion = "SELECT id_requisicion FROM control_almacen WHERE id_control = :id_control LIMIT 1";
    $stmtGetRequisicion = $conn->prepare($sqlGetRequisicion);
    $stmtGetRequisicion->bindParam(':id_control', $firstIdControl, PDO::PARAM_INT);
    $stmtGetRequisicion->execute();
    $row = $stmtGetRequisicion->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception("No se encontro requisicion asociada al id_control {$firstIdControl}");
    }

    $id_requisicion = $row['id_requisicion'];

    // Validar que no existan barras pendientes por autorizar en la requisición
    $stmtBarraPendiente = $conn->prepare("SELECT barra_pendiente FROM requisiciones WHERE id_requisicion = :id_requisicion");
    $stmtBarraPendiente->bindValue(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtBarraPendiente->execute();
    $rowPendiente = $stmtBarraPendiente->fetch(PDO::FETCH_ASSOC);
    if ($rowPendiente && isset($rowPendiente['barra_pendiente']) && intval($rowPendiente['barra_pendiente']) === 1) {
        echo json_encode([
            'success' => false,
            'error' => 'No es posible finalizar la requisición porque existen autorizaciones de barra pendientes.'
        ]);
        exit();
    }

    $conn->beginTransaction();

    // Actualizar con todos los campos que vienen del frontend
    $sqlUpdate = "UPDATE control_almacen 
                  SET es_merma = :es_merma,
                      perfil_sello = :perfil_sello,
                      pz_maquinadas = :pz_maquinadas,
                      altura_pz = :altura_pz,
                      mm_usados = :mm_usados,
                      total_sellos = :total_sellos,
                      merma_corte = :merma_corte,
                      scrap_pz = :scrap_pz,
                      scrap_mm = :scrap_mm,
                      mm_total_usados = :mm_total_usados,
                      mm_teoricos = :mm_teoricos,
                      mm_merma_real = :mm_merma_real,
                      id_cotizacion = :id_cotizacion,
                      id_estimacion = :id_estimacion,
                      pz_teoricas = :pz_teoricas,
                      h_componente = :h_componente,
                      causa_merma = :causa_merma,
                      justificacion_merma = :justificacion_merma
                  WHERE id_control = :id_control";
    $stmtUpdate = $conn->prepare($sqlUpdate);

    $registrosActualizados = 0;
    $registrosSinCambios = 0;

    foreach ($data as $fila) {
        $stmtUpdate->execute([
            ':es_merma' => $fila['es_merma'] ?? 0,
            ':perfil_sello' => $fila['perfil_sello'] ?? '',
            ':pz_maquinadas' => $fila['pz_maquinadas'] ?? 0,
            ':altura_pz' => $fila['altura_pz'] ?? 0,
            ':mm_usados' => $fila['mm_usados'] ?? 0,
            ':total_sellos' => $fila['total_sellos'] ?? 0,
            ':merma_corte' => $fila['merma_corte'] ?? 0,
            ':scrap_pz' => $fila['scrap_pz'] ?? 0,
            ':scrap_mm' => $fila['scrap_mm'] ?? 0,
            ':mm_total_usados' => $fila['mm_total_usados'] ?? 0,
            ':mm_teoricos' => $fila['mm_teoricos'] ?? 0,
            ':mm_merma_real' => $fila['mm_merma_real'] ?? 0,
            ':id_cotizacion' => $fila['id_cotizacion'] ?? null,
            ':id_estimacion' => $fila['id_estimacion'] ?? null,
            ':pz_teoricas' => $fila['pz_teoricas'] ?? 0,
            ':h_componente' => $fila['h_componente'] ?? 0,
            ':causa_merma' => $fila['causa_merma'] ?? '',
            ':justificacion_merma' => $fila['justificacion_merma'] ?? '',
            ':id_control' => $fila['id_control']
        ]);

        if ($stmtUpdate->rowCount() > 0) {
            $registrosActualizados++;
        } else {
            $registrosSinCambios++;
            // No es un error, simplemente no hubo cambios
            // Podemos verificar si el registro existe
            $sqlCheck = "SELECT id_control FROM control_almacen WHERE id_control = :id_control";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->bindParam(':id_control', $fila['id_control'], PDO::PARAM_INT);
            $stmtCheck->execute();
            
            if (!$stmtCheck->fetch()) {
                throw new Exception("El registro con id_control: " . $fila['id_control'] . " no existe");
            }
            // Si existe pero no hubo cambios, es válido (probablemente ya se guardó antes)
        }
    }

    // Actualizar el estatus de la requisición
    $sqlRequisicion = "UPDATE requisiciones 
                       SET estatus = 'Finalizada', fin_maquinado = NOW() 
                       WHERE id_requisicion = :id_requisicion";
    $stmtRequisicion = $conn->prepare($sqlRequisicion);
    $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtRequisicion->execute();

    $conn->commit();

    // Obtener información de cotizaciones para la respuesta
    $sqlCot = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
    $stmtCot = $conn->prepare($sqlCot);
    $stmtCot->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtCot->execute();
    $cot = $stmtCot->fetch(PDO::FETCH_ASSOC);

    // Envio de correo
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

        $sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6 OR (lider = 2 AND rol = 'Gerente')";
        $stmt = $conn->prepare($sqlCorreoInventarios);
        $stmt->execute();
        $correosInventarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

        if($DEV_MODE === true){
            $mail->addAddress($DEV_EMAIL);
        }
        $mail->isHTML(true);
        $mail->Subject = 'Requisicion finalizada. Folio: '.$id_requisicion;
        $mail->Body = "Se ha finalizado el maquinado de sellos.<br>
                    Se requiere revisión de merma y actualizar el stock de los billets correspondientes en el retorno de barras.<br>
                    Folio de requisicion: <b>".$id_requisicion."</b>";

        if($SEND_MAIL === true){
            if ($mail->send()) {
                $msjExtra = "Correo enviado a Inventarios correctamente.";
            } else {
                $msjExtra = "No se pudo enviar el correo: " . $mail->ErrorInfo;
            }
        }

    } catch (Throwable $e) {
        $msjExtra = "Error al enviar correo: " . $e->getMessage();
    }

    if($SEND_MAIL === true){
        echo json_encode([
            'success' => true,
            'message' => 'Requisicion finalizada correctamente. ' . $msjExtra,
            'cotizaciones' => $cot['cotizaciones'] ?? null,
            'id_requisicion' => $id_requisicion
        ]);
    }else{
        echo json_encode([
            'success' => true,
            'message' => 'Requisicion finalizada correctamente. Envío de correos no disponible.',
            'cotizaciones' => $cot['cotizaciones'] ?? null,
            'id_requisicion' => $id_requisicion
        ]);           
    }

} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>