<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
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
    $sqlCheck = "SELECT COUNT(*) as total FROM control_almacen WHERE id_requisicion = :id_requisicion";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bindValue(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtCheck->execute();
    $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$row || $row['total'] == 0) {
        throw new Exception("Antes de enviar a producción, debe haber al menos una clave en control de inventario en la requisición.");
    }

    $sqlLotesPedimento = "SELECT * FROM control_almacen WHERE id_requisicion = :id_requisicion";
    $stmtLP = $conn->prepare($sqlLotesPedimento);
    $stmtLP->bindParam(':id_requisicion', $id_requisicion);
    $stmtLP->execute();
    $arrayLP = $stmtLP->fetchAll();

    $missingLotes = [];
    $updatedLotes = 0;

    foreach ($arrayLP as $LP) {
        $lote = trim($LP['lote_pedimento']);

        // Preparar y ejecutar update una vez
        $sqlEstatusLP = "UPDATE inventario_cnc 
                        SET estatus = 'Deshabilitado'
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
            SET estatus = 'Producción'
            WHERE id_requisicion = :id_requisicion";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmt->execute();

    ////////////////////////////PHP MAILER -> cotizador a CNC ////////////////
    try {
        require_once(ROOT_PATH . 'includes/PHPMailer.php');
        $mail = getMailer($conn);

        $sqlCorreoProduccion = "SELECT usuario FROM login WHERE lider = 2 AND rol = 'Gerente'";
        $stmt = $conn->prepare($sqlCorreoProduccion);
        $stmt->execute();
        $correosProduccion = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$correosProduccion || count($correosProduccion) === 0) {
            throw new Exception("No se encontro ningún correo de producción.");
        }

        $clave_encriptacion = 'SRS2024#tides';
        $contadorCorreos = 0;

        foreach ($correosProduccion as $fila) {
            if (!empty($fila['usuario'])) {
                $correo = openssl_decrypt($fila['usuario'], 'AES-128-ECB', $clave_encriptacion);
                $correo = trim($correo);
                if ($correo) {
                    //$mail->addAddress($correo);
                    $contadorCorreos++;
                }
            }
        }

        if ($contadorCorreos === 0) {
            throw new Exception("No se pudo agregar ningún destinatario valido para producción.");
        }

        $mail->addAddress("desarrollo2.sistemas@sellosyretenes.com"); // Correo principal visible
        //$mail->addAddress("sistemas@sellosyretenes.com");
        $mail->Subject = 'Nueva requisición para maquinado. Folio: '.$id_requisicion;
        $mail->Body = "Inventarios ha liberado una nueva requisición de maquinado de sellos con las barras solicitadas.<br>
                    Se ha cambiado el estatus a <b>Producción</b>.<br>
                    Folio de requisición: <b>".$id_requisicion."</b>";

        if (!$mail->send()) {
            throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
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
        echo json_encode([
            'success' => true,
            'message' => "Correo enviado exitosamente a CNC. Estatus de requisición cambiado a Producción. ".$msjLotes
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => "No se encontró la requisición o ya estaba en Producción."
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
