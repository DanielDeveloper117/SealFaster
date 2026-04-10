<?php
/**
 * AJAX endpoint para guardar (insertar/actualizar) requisiciones.
 * Reemplaza el flujo síncrono de POST que tenía includes/backend/produccion_vn.php
 * para las acciones 'insert' y 'update'.
 * 
 * Retorna JSON: { success: bool, message: string, id_requisicion?: int }
 */
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }

    $action = $_POST['action'] ?? '';
    
    if (!in_array($action, ['insert', 'update'])) {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        exit;
    }

    // ========== RECOGER DATOS ==========
    $id_vendedor = $_POST['id_vendedor'] ?? '';
    $estatus = $_POST['estatus'] ?? 'Pendiente';
    $cotizaciones = trim($_POST['cotizaciones'] ?? '');
    $nombre_vendedor = $_POST['nombre_vendedor'] ?? '';
    $sucursal = $_POST['sucursal'] ?? '';
    $cliente = $_POST['cliente'] ?? '';
    $num_pedido = $_POST['num_pedido'] ?? '';
    $factura = $_POST['factura'] ?? '';
    $paqueteria = $_POST['paqueteria'] ?? '';
    $comentario = mb_substr($_POST['comentario'] ?? '', 0, 75, 'UTF-8');
    $id_requisicion = ($action === 'update') ? intval($_POST['id_requisicion'] ?? 0) : 0;

    // ========== VALIDACIÓN: cotizaciones no vacías ==========
    if (empty($cotizaciones)) {
        echo json_encode([
            'success' => false, 
            'message' => 'La requisición debe tener al menos una cotización, intente nuevamente.'
        ]);
        exit;
    }

    // ========== VALIDACIÓN: cotizaciones no duplicadas entre requisiciones ==========
    $cotizacionesArray = array_map('trim', explode(',', $cotizaciones));
    $cotizacionesArray = array_filter($cotizacionesArray, function($v) { return $v !== ''; });

    // Obtener todas las requisiciones activas (excluyendo la actual en edición)
    $sqlReqActivas = "SELECT id_requisicion, cotizaciones FROM requisiciones WHERE estatus != 'Archivada'";
    if ($action === 'update' && $id_requisicion > 0) {
        $sqlReqActivas .= " AND id_requisicion != :exclude_req";
    }
    $stmtReq = $conn->prepare($sqlReqActivas);
    if ($action === 'update' && $id_requisicion > 0) {
        $stmtReq->bindParam(':exclude_req', $id_requisicion, PDO::PARAM_INT);
    }
    $stmtReq->execute();
    $requisicionesActivas = $stmtReq->fetchAll(PDO::FETCH_ASSOC);

    // Construir mapa de cotizaciones en uso
    $cotizacionesEnUso = [];
    foreach ($requisicionesActivas as $req) {
        $ids = array_map('trim', explode(',', $req['cotizaciones']));
        foreach ($ids as $idCot) {
            if ($idCot !== '') {
                $cotizacionesEnUso[$idCot] = $req['id_requisicion'];
            }
        }
    }

    // Verificar cada cotización enviada
    $conflictos = [];
    foreach ($cotizacionesArray as $idCot) {
        if (isset($cotizacionesEnUso[$idCot])) {
            $conflictos[] = "Cotización $idCot ya está en la requisición #{$cotizacionesEnUso[$idCot]}";
        }
    }

    if (!empty($conflictos)) {
        echo json_encode([
            'success' => false,
            'message' => 'No se puede guardar: ' . implode('. ', $conflictos)
        ]);
        exit;
    }

    // ========== INSERCION ==========
    if ($action === 'insert') {
        $sql = "INSERT INTO requisiciones (id_vendedor, estatus, cotizaciones, nombre_vendedor, sucursal, cliente, num_pedido, factura, paqueteria, comentario) 
                VALUES (:id_vendedor, :estatus, :cotizaciones, :nombre_vendedor, :sucursal, :cliente, :num_pedido, :factura, :paqueteria, :comentario)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_vendedor', $id_vendedor);
        $stmt->bindParam(':estatus', $estatus);
        $stmt->bindParam(':cotizaciones', $cotizaciones);
        $stmt->bindParam(':nombre_vendedor', $nombre_vendedor);
        $stmt->bindParam(':sucursal', $sucursal);
        $stmt->bindParam(':cliente', $cliente);
        $stmt->bindParam(':num_pedido', $num_pedido);
        $stmt->bindParam(':factura', $factura);
        $stmt->bindParam(':paqueteria', $paqueteria);
        $stmt->bindParam(':comentario', $comentario);
        $stmt->execute();

        // ACTUALIZAR QUE EL FOLIO SEA IGUAL A LA ID REQUISICION
        $id_requisicion = $conn->lastInsertId();
        $update = $conn->prepare("UPDATE requisiciones SET folio = :folio WHERE id_requisicion = :id");
        $update->execute([
            'folio' => $id_requisicion,
            'id' => $id_requisicion
        ]);

        // ======== ENVÍO DE CORREO (PHPMailer) ========
        $mensajeCorreo = '';
        try {
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

            // Clave de encriptacion
            $clave_encriptacion = $PASS_UNCRIPT ?? '';

            $area_desencriptada = $sucursal;
            $areaGerenteEncriptado = openssl_encrypt($area_desencriptada, 'AES-128-ECB', $clave_encriptacion);

            if ($sucursal == "Ventas Nacionales") {
                $sqlCorreoVentasGerencia = "SELECT usuario FROM login WHERE lider = 3 AND (rol = 'Gerente' AND area = :area) OR rol = 'CORREO_DIRECCION'";
                $stmtMail = $conn->prepare($sqlCorreoVentasGerencia);
                $stmtMail->bindParam(':area', $areaGerenteEncriptado);   
            } else {
                $sqlCorreoVentasGerencia = "SELECT usuario FROM login WHERE lider = 3 AND area = :area AND rol = 'Gerente'";
                $stmtMail = $conn->prepare($sqlCorreoVentasGerencia);
                $stmtMail->bindParam(':area', $areaGerenteEncriptado);                    
            }
            $stmtMail->execute();
            $correosGerencia = $stmtMail->fetchAll(PDO::FETCH_ASSOC);

            if (!$correosGerencia || count($correosGerencia) === 0) {
                throw new Exception("No se encontro ningun correo de gerencia.");
            }

            $contadorCorreos = 0;

            foreach ($correosGerencia as $fila) {
                if (!empty($fila['usuario'])) {
                    $correo = openssl_decrypt($fila['usuario'], 'AES-128-ECB', $clave_encriptacion);
                    if ($correo) {
                        if ($DEV_MODE === false) {
                            $mail->addAddress($correo);
                        }
                        $contadorCorreos++;
                    }
                }
            }

            if ($contadorCorreos === 0) {
                throw new Exception("No se pudo agregar ningún destinatario valido.");
            }
            if ($DEV_MODE === true) {
                $mail->addAddress($DEV_EMAIL);
            }
            $mail->Subject = 'Nueva requisición por autorizar. Folio: ' . $id_requisicion;
            $mail->Body = "$nombre_vendedor ha generado una requisición para el maquinado de sello. Vaya a la sección de <b>Requisiciones</b> para autorizarla con su firma.<br>Folio de requisición: <b>" . $id_requisicion . "</b>";
            
            if ($SEND_MAIL === true) {
                if (!$mail->send()) {
                    throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
                }
                $mensajeCorreo = ' Correo enviado a gerencia.';
            } else {
                $mensajeCorreo = ' Envio de correos no disponible.';
            }

        } catch (Throwable $e) {
            $mensajeCorreo = ' No se pudo enviar correo a gerencia: ' . $e->getMessage();
        }

        echo json_encode([
            'success' => true,
            'message' => 'Registro agregado correctamente.' . $mensajeCorreo,
            'id_requisicion' => $id_requisicion
        ]);
        exit;

    // ========== ACTUALIZACION ==========
    } elseif ($action === 'update') {
        $sql = "UPDATE requisiciones SET 
                    id_vendedor = :id_vendedor,
                    estatus = :estatus,
                    cotizaciones = :cotizaciones,
                    nombre_vendedor = :nombre_vendedor,
                    sucursal = :sucursal,
                    cliente = :cliente,
                    num_pedido = :num_pedido,
                    factura = :factura,
                    paqueteria = :paqueteria,
                    comentario = :comentario
                WHERE id_requisicion = :id_requisicion";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        $stmt->bindParam(':id_vendedor', $id_vendedor);
        $stmt->bindParam(':estatus', $estatus);
        $stmt->bindParam(':cotizaciones', $cotizaciones);
        $stmt->bindParam(':nombre_vendedor', $nombre_vendedor);
        $stmt->bindParam(':sucursal', $sucursal);
        $stmt->bindParam(':cliente', $cliente);
        $stmt->bindParam(':num_pedido', $num_pedido);
        $stmt->bindParam(':factura', $factura);
        $stmt->bindParam(':paqueteria', $paqueteria);
        $stmt->bindParam(':comentario', $comentario);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Registro actualizado correctamente.'
        ]);
        exit;
    }

} catch (Throwable $e) {
    error_log("Error en guardar_requisicion_vn: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
    ]);
    exit;
} finally {
    $conn = null;
}
?>
