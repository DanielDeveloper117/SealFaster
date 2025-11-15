<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }

    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;

    set_error_handler(function($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    $acciones_validas = ['insert', 'update', 'delete', 'insert2'];
    if (!in_array($action, $acciones_validas)) {
        throw new Exception("Acción no válida.");
    }

    // Limpiar espacios automáticamente sin notificar al usuario
    $clave          = preg_replace('/\s+/', '', trim($_POST['clave'] ?? ''));
    $medida         = preg_replace('/\s+/', '', trim($_POST['medida'] ?? ''));
    $proveedor      = trim($_POST['proveedor'] ?? '');
    $material       = trim($_POST['material'] ?? '');
    $max_usable     = preg_replace('/\s+/', '', trim($_POST['max_usable'] ?? ''));
    $stock          = preg_replace('/\s+/', '', trim($_POST['stock'] ?? ''));
    $lote_pedimento = preg_replace('/\s+/', '', trim($_POST['lote_pedimento'] ?? ''));
    $estatus        = trim($_POST['estatus'] ?? '');
    $inputClaveAlterna = preg_replace('/\s+/', '', trim($_POST['inputClaveAlterna'] ?? ''));

    if ($action !== 'delete') {
        $errores = [];
        if ($action === '') $errores[] = "Falta la acción";
        if ($id === null && $action === 'update') $errores[] = "Falta el id";
        if ($estatus === '') $errores[] = "Falta el estatus";
        if ($clave === '') $errores[] = "Falta la clave";
        if ($material === '') $errores[] = "Falta el material";
        if ($proveedor === '') $errores[] = "Falta el proveedor";
        if ($medida === '') $errores[] = "Falta la medida";
        if ($max_usable === '') $errores[] = "Falta el maximo usable";
        if ($stock === '') $errores[] = "Falta el stock";
        if ($lote_pedimento === '') $errores[] = "Falta el lote/pedimento";

        if (!empty($errores)) {
            echo json_encode([
                'success' => false,
                'message' => $errores[0]
            ]);
            exit;
        }
    }

    if (in_array($action, ['insert', 'insert2', 'update'])) {
        if (!preg_match('/^\d+\/\d+$/', $medida)) {
            throw new Exception("Formato de medida inválido. Usa formato interior/exterior.");
        }

        list($interior, $exterior) = explode('/', $medida);
        $interior = (int)$interior;
        $exterior = (int)$exterior;
    }

    // Variables para acciones posteriores
    $insertId = null;
    $claveParaCorreo = $clave;
    $mensajeCorreo = ""; // Para almacenar el estado del envío de correo

    if ($action === 'insert' || $action === 'insert2') {
        $sql = "INSERT INTO inventario_cnc 
                (clave, medida, interior, exterior, proveedor, material, max_usable, pre_stock, stock, lote_pedimento, estatus)
                VALUES 
                (:clave, :medida, :interior, :exterior, :proveedor, :material, :max_usable, :pre_stock, :stock, :lote_pedimento, :estatus)";
        $stmt = $conn->prepare($sql);
    }

    if ($action === 'update') {
        if (empty($id)) throw new Exception("ID requerido para actualizar.");
        $sql = "UPDATE inventario_cnc SET 
                    clave = :clave, 
                    medida = :medida, 
                    interior = :interior, 
                    exterior = :exterior, 
                    proveedor = :proveedor, 
                    material = :material, 
                    max_usable = :max_usable, 
                    pre_stock = :pre_stock,
                    stock = :stock, 
                    lote_pedimento = :lote_pedimento,
                    estatus = :estatus,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
    }

    if ($action === 'insert' || $action === 'insert2' || $action === 'update') {
        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':medida', $medida);
        $stmt->bindParam(':interior', $interior);
        $stmt->bindParam(':exterior', $exterior);
        $stmt->bindParam(':proveedor', $proveedor);
        $stmt->bindParam(':material', $material);
        $stmt->bindParam(':max_usable', $max_usable);
        $stmt->bindParam(':pre_stock', $stock);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':lote_pedimento', $lote_pedimento);
        $stmt->bindParam(':estatus', $estatus);
        $stmt->execute();

        // Obtener el ID del registro insertado (solo para inserts)
        if ($action === 'insert' || $action === 'insert2') {
            $insertId = $conn->lastInsertId();
        }

        // MANEJO DE ESTATUS ESPECIALES
        if (in_array($estatus, ['Relación pendiente', 'Clave nueva pendiente', 'Clave SRS inexistente'])) {
            
            // CASO 1: "Relación pendiente" - Solo insertar/actualizar en inventario_cnc y enviar correo
            if ($estatus === 'Relación pendiente') {
                $asunto = "Nuevo billet con clave alterna existente - Relación pendiente";
                $mensaje = "Se ha agregado un nuevo billet con clave alterna existente, relación pendiente de clave SRS. Clave: " . $clave;
                
                // Envío de correo directo (sin función)
                $correoEnviado = false;
                try {
                    require_once(ROOT_PATH . 'includes/PHPMailer.php');
                    $mail = getMailer($conn);
                    //$mail->addAddress("aux.sistemas@sellosyretenes.com");
                    $mail->addAddress("desarrollo2.sistemas@sellosyretenes.com");
                    $mail->isHTML(true);
                    $mail->Subject = $asunto;
                    $mail->Body = $mensaje;

                    if ($mail->send()) {
                        $correoEnviado = true;
                        $msjExtra = "Correo enviado a Inventarios correctamente.";
                    } else {
                        $msjExtra = "No se pudo enviar el correo: " . $mail->ErrorInfo;
                    }
                } catch (Throwable $e) {
                    $msjExtra = "Error al enviar correo: " . $e->getMessage();
                }
                
                $mensajeCorreo = $correoEnviado ? "" : " (Error al enviar correo)";
                error_log("INTENTO DE CORREO - Asunto: " . $asunto . " - Éxito: " . ($correoEnviado ? "Sí" : "No"));
            }
            
            // CASO 2: "Clave nueva pendiente" - Insertar en claves_alternas y enviar correo
            else if ($estatus === 'Clave nueva pendiente') {
                // Insertar en claves_alternas (clave_srs como NULL inicialmente)
                $sqlClavesAlternas = "INSERT INTO claves_alternas (clave_alterna, clave_srs, fecha_registro) 
                                     VALUES (:clave_alterna, NULL, NOW()) 
                                     ON DUPLICATE KEY UPDATE fecha_registro = NOW()";
                $stmtClavesAlternas = $conn->prepare($sqlClavesAlternas);
                $claveAlterna = !empty($inputClaveAlterna) ? $inputClaveAlterna : $clave;
                $stmtClavesAlternas->bindParam(':clave_alterna', $claveAlterna);
                $stmtClavesAlternas->execute();
                
                $asunto = "Nuevo billet con clave nueva - Relación pendiente";
                $mensaje = "Se ha agregado un nuevo billet con clave nueva, relación pendiente de clave SRS. Clave: " . $clave;
                
                // Envío de correo directo (sin función)
                $correoEnviado = false;
                try {
                    require_once(ROOT_PATH . 'includes/PHPMailer.php');
                    $mail = getMailer($conn);
                    //$mail->addAddress("aux.sistemas@sellosyretenes.com");
                    $mail->addAddress("desarrollo2.sistemas@sellosyretenes.com");
                    $mail->isHTML(true);
                    $mail->Subject = $asunto;
                    $mail->Body = $mensaje;

                    if ($mail->send()) {
                        $correoEnviado = true;
                        $msjExtra = "Correo enviado a Inventarios correctamente.";
                    } else {
                        $msjExtra = "No se pudo enviar el correo: " . $mail->ErrorInfo;
                    }
                } catch (Throwable $e) {
                    $msjExtra = "Error al enviar correo: " . $e->getMessage();
                }
                
                $mensajeCorreo = $correoEnviado ? "" : " (Error al enviar correo)";
                error_log("INTENTO DE CORREO - Asunto: " . $asunto . " - Éxito: " . ($correoEnviado ? "Sí" : "No"));
                
                $claveParaCorreo = $claveAlterna;
            }
            
            // CASO 3: "Clave SRS inexistente" - Insertar en claves_alternas y enviar correo
            else if ($estatus === 'Clave SRS inexistente') {
                // Insertar en claves_alternas (clave_srs como NULL)
                $sqlClavesAlternas = "INSERT INTO claves_alternas (clave_alterna, clave_srs, fecha_registro) 
                                     VALUES (:clave_alterna, NULL, NOW()) 
                                     ON DUPLICATE KEY UPDATE fecha_registro = NOW()";
                $stmtClavesAlternas = $conn->prepare($sqlClavesAlternas);
                $claveAlterna = !empty($inputClaveAlterna) ? $inputClaveAlterna : $clave;
                $stmtClavesAlternas->bindParam(':clave_alterna', $claveAlterna);
                $stmtClavesAlternas->execute();
                
                $asunto = "Nuevo billet con clave nueva - Clave SRS no encontrada";
                $mensaje = "Se ha agregado un nuevo billet con clave nueva, no se encontró la clave SRS. Clave: " . $clave;
                
                // Envío de correo directo (sin función)
                $correoEnviado = false;
                try {
                    require_once(ROOT_PATH . 'includes/PHPMailer.php');
                    $mail = getMailer($conn);
                    //$mail->addAddress("aux.sistemas@sellosyretenes.com");
                    $mail->addAddress("desarrollo2.sistemas@sellosyretenes.com");
                    $mail->isHTML(true);
                    $mail->Subject = $asunto;
                    $mail->Body = $mensaje;

                    if ($mail->send()) {
                        $correoEnviado = true;
                        $msjExtra = "Correo enviado a Inventarios correctamente.";
                    } else {
                        $msjExtra = "No se pudo enviar el correo: " . $mail->ErrorInfo;
                    }
                } catch (Throwable $e) {
                    $msjExtra = "Error al enviar correo: " . $e->getMessage();
                }
                
                $mensajeCorreo = $correoEnviado ? "" : " (Error al enviar correo)";
                error_log("INTENTO DE CORREO - Asunto: " . $asunto . " - Éxito: " . ($correoEnviado ? "Sí" : "No"));
                
                $claveParaCorreo = $claveAlterna;
            }
        }
        
        $mensajeBase = $action === 'update' ? 'Registro actualizado correctamente.' : 'Registro agregado correctamente.';
        $mensajeCompleto = $mensajeBase . $mensajeCorreo;
        
        echo json_encode([
            'success' => true,
            'message' => $mensajeCompleto,
            'id' => $insertId
        ]);
        exit;
    }

    if ($action === 'delete') {
        if (empty($id)) throw new Exception("ID requerido para eliminar.");

        $stmt = $conn->prepare("UPDATE inventario_cnc SET estatus = 'Eliminado', deleted_at = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => "Registro eliminado correctamente."
        ]);
        exit;
    }

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    restore_error_handler();
    $conn = null;
}