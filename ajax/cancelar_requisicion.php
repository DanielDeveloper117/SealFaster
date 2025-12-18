<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
        exit;
    }

    // Validación básica del parámetro
    if (!isset($_POST['id_requisicion']) || !ctype_digit($_POST['id_requisicion'])) {
        echo json_encode(['success' => false, 'message' => "Parámetros incompletos o inválidos."]);
        exit;
    }

    $id_requisicion = intval($_POST['id_requisicion']);

    // Actualizar estatus a Pendiente
    $stmt = $conn->prepare("UPDATE requisiciones 
                            SET estatus = 'Pendiente',
                            ruta_firma = null,
                            ruta_firma_admin = null,
                            autorizo = null,
                            fecha_autorizacion = null
                            WHERE id_requisicion = :id_requisicion");
    $stmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró la requisición o no se realizaron cambios.'
        ]);
        exit;
    }

    // 1. Obtener cotizaciones asociadas para actualizar inventario
    $sqlRequisicion = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
    $stmtRequisicion = $conn->prepare($sqlRequisicion);
    $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion);
    $stmtRequisicion->execute();
    $result = $stmtRequisicion->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['cotizaciones'])) {
        $cotizacion_ids = explode(', ', $result['cotizaciones']);

        // 2. Actualizar estado de cada cotización
        $sqlUpdateCotizacion = "UPDATE cotizacion_materiales SET estatus_completado = 'Cotización', fecha_actualizacion = NOW() WHERE id_cotizacion = :id_cotizacion";
        $stmtUpdateCotizacion = $conn->prepare($sqlUpdateCotizacion);

        // 3. Obtener todos los lotes pedimento únicos de las cotizaciones
        $placeholders = str_repeat('?,', count($cotizacion_ids) - 1) . '?';
        $sqlBillets = "SELECT DISTINCT billets FROM cotizacion_materiales WHERE id_cotizacion IN ($placeholders)";
        $stmtBillets = $conn->prepare($sqlBillets);
        $stmtBillets->execute($cotizacion_ids);
        $billetsResults = $stmtBillets->fetchAll(PDO::FETCH_ASSOC);

        // 4. Extraer todos los lotes pedimento únicos
        $lotesUnicos = [];
        foreach ($billetsResults as $row) {
            if (!empty($row['billets'])) {
                $billets = array_map('trim', explode(',', $row['billets']));
                foreach ($billets as $billet) {
                    $lotesUnicos[$billet] = true;
                }
            }
        }

        // 5. Actualizar inventario para cada lote único
        if (!empty($lotesUnicos)) {
            $lotesArray = array_keys($lotesUnicos);
            $placeholdersLotes = str_repeat('?,', count($lotesArray) - 1) . '?';
            
            $sqlUpdateInventario = "UPDATE inventario_cnc 
                                   SET pre_stock = stock, 
                                       estatus = 'Disponible para cotizar', 
                                       updated_at = NOW() 
                                   WHERE lote_pedimento IN ($placeholdersLotes)";
            
            $stmtUpdateInventario = $conn->prepare($sqlUpdateInventario);
            $stmtUpdateInventario->execute($lotesArray);
        }

        // 6. Actualizar estado de cada cotización (esto se mantiene igual)
        foreach ($cotizacion_ids as $id_cotizacion) {
            $stmtUpdateCotizacion->bindValue(':id_cotizacion', $id_cotizacion);
            $stmtUpdateCotizacion->execute();
        }
    }

    // Preparar correo (esta parte se mantiene igual)
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
        $mail->addAddress($DEV_EMAIL);
        $mail->Subject = 'Requisición cancelada. Folio: '.$id_requisicion;
        $mail->Body = "Se ha cancelado la autorización de una requisición.<br>Folio: <b>$id_requisicion</b>";
        $mail->AltBody = "Se ha cancelado la autorización de una requisición. Folio: $id_requisicion";

        if (!$mail->send()) {
            throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
        }

    } catch (Throwable $e) {
        // Si falla el correo, igual devolvemos éxito pero con advertencia
        echo json_encode([
            'success' => true,
            'message' => "Requisición cancelada correctamente, pero hubo un error al enviar correo: " . addslashes($e->getMessage())
        ]);
        exit;
    }

    // Respuesta exitosa general
    echo json_encode([
        'success' => true,
        'message' => 'Requisición cancelada correctamente y correos enviados con éxito.'
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Error inesperado: ' . $e->getMessage()]);
} finally {
    $conn = null;
}