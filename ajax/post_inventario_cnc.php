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

    $acciones_validas = ['insert', 'update', 'delete', 'insert2', 'autorizar_archivado'];
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

    if ($action !== 'delete' && $action !== 'autorizar_archivado') {
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
                    if($DEV_MODE === false){
                        $mail->addAddress($AUX_GESTOR_EMAIL);
                    }else{
                        $mail->addAddress($DEV_EMAIL);
                    }
                    $mail->isHTML(true);
                    $mail->Subject = $asunto;
                    $mail->Body = $mensaje;
                    if($SEND_MAIL === true){
                        if ($mail->send()) {
                            $correoEnviado = true;
                            $msjExtra = "Correo enviado a Inventarios correctamente.";
                        } else {
                            $msjExtra = "No se pudo enviar el correo: " . $mail->ErrorInfo;
                        }
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
                    if($DEV_MODE === false){
                        $mail->addAddress($AUX_GESTOR_EMAIL);
                    }else{
                        $mail->addAddress($DEV_EMAIL);
                    }
                    
                    $mail->isHTML(true);
                    $mail->Subject = $asunto;
                    $mail->Body = $mensaje;
                    if($SEND_MAIL === true){
                        if ($mail->send()) {
                            $correoEnviado = true;
                            $msjExtra = "Correo enviado a Inventarios correctamente.";
                        } else {
                            $msjExtra = "No se pudo enviar el correo: " . $mail->ErrorInfo;
                        }
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
                    if($DEV_MODE === false){
                        $mail->addAddress($AUX_GESTOR_EMAIL);
                    }else{
                        $mail->addAddress($DEV_EMAIL);
                    }
                    $mail->isHTML(true);
                    $mail->Subject = $asunto;
                    $mail->Body = $mensaje;
                    if($SEND_MAIL === true){
                        if ($mail->send()) {
                            $correoEnviado = true;
                            $msjExtra = "Correo enviado a Inventarios correctamente.";
                        } else {
                            $msjExtra = "No se pudo enviar el correo: " . $mail->ErrorInfo;
                        }
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
        if($SEND_MAIL === true){
            $mensajeCompleto = $mensajeBase . $mensajeCorreo;
        }else{
            $mensajeCompleto = $mensajeBase." Envío de correos no disponible.";
        }
        echo json_encode([
            'success' => true,
            'message' => $mensajeCompleto,
            'id' => $insertId
        ]);
        exit;
    }

    if ($action === 'delete') {
        // Validar que se haya subido una imagen
        if (!isset($_FILES['foto_archivar']) || $_FILES['foto_archivar']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Debe subir una fotografía de la barra para solicitar su archivado.");
        }
        
        $justificacion_archivado = $_POST['justificacion_archivado'];
        if (empty($id)) throw new Exception("ID requerido para archivar.");
        if (empty($justificacion_archivado)) throw new Exception("Justificación requerida para archivar.");
        if (strlen($justificacion_archivado) < 10) throw new Exception("Justificación debe tener mínimo 10 caracteres.");
        
        // Validar y procesar el archivo de imagen
        $file = $_FILES['foto_archivar'];
        
        // Validar tipo de archivo
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = mime_content_type($file['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception("Solo se permiten archivos de imagen (JPEG, PNG, GIF, WebP).");
        }
        
        // Validar tamaño (máx. 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $max_size) {
            throw new Exception("La imagen no debe superar los 5MB.");
        }
        
        // Crear directorio si no existe
        $upload_dir = ROOT_PATH . 'files/fotos/barras/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0775, true);
        }
        
        // Generar nombre único para el archivo
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'barra_' . $id . '_' . time() . '.' . strtolower($file_extension);
        $file_path = $upload_dir . $filename;
        
        // Mover archivo subido
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            throw new Exception("Error al guardar la imagen en el servidor.");
        }
        
        // Ruta relativa para guardar en la base de datos
        $ruta_foto_barra = '../files/fotos/barras/' . $filename;
        
        // Actualizar registro con la ruta de la foto
        $stmt = $conn->prepare("UPDATE inventario_cnc 
                                SET estatus = 'Eliminado', 
                                    solicita_archivado = 1, 
                                    justificacion_archivado = :justificacion_archivado,
                                    ruta_foto_barra = :ruta_foto_barra,
                                    deleted_at = NOW() 
                                WHERE id = :id");
        $stmt->bindParam(':justificacion_archivado', $justificacion_archivado);
        $stmt->bindParam(':ruta_foto_barra', $ruta_foto_barra);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        // Preparar y enviar correo (no crítico para la operación)
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

            // Obtener correos de dirección comercial
            $sqlCorreoDireccion = "SELECT usuario FROM login WHERE rol = 'CORREO_DIRECCION'";
            $stmtCorreos = $conn->prepare($sqlCorreoDireccion);
            $stmtCorreos->execute();
            $correosDireccion = $stmtCorreos->fetchAll(PDO::FETCH_ASSOC);

            if ($correosDireccion && count($correosDireccion) > 0) {
                $clave_encriptacion = $PASS_UNCRIPT ?? '';
                $contadorCorreos = 0;

                foreach ($correosDireccion as $fila) {
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
                
                if ($contadorCorreos > 0) {
                    // Preparar contenido del correo
                    $mail->isHTML(true);
                    if($DEV_MODE === true){
                        $mail->addAddress($DEV_EMAIL);
                    }
                    $asunto = "Solicitud para archivar barra";
                    
                    $cuerpo = "Inventarios ha solicitado la autorización para archivar una barra del inventario de billets.</br>";
                    $cuerpo .= "Ingrese al sistema en el módulo de Inventario CNC para autorizar.</br>";
                    $cuerpo .= "Barra: <b>" . $lote_pedimento . "</b></br>";
                    $cuerpo .= "Justificación:</br><b>";
                    $cuerpo .= $justificacion_archivado . "</b></br>";
                    $cuerpo .= "Fotografía adjunta en el sistema.</br>";
                    
                    // Mostrar imagen en el correo (opcional)
                    $cuerpo .= "<p>Vista previa de la foto:</p>";
                    $cuerpo .= "<img src='cid:barra_foto' alt='Foto de la barra' style='max-width: 300px; max-height: 300px;'>";
                    
                    $mail->Subject = $asunto;
                    $mail->Body = $cuerpo;
                    $mail->AddEmbeddedImage($file_path, 'barra_foto', $filename);
                    if($SEND_MAIL === true){
                        if (!$mail->send()) {
                            throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
                        }
                    }
                    $mensajeCorreo = " y correo enviado para autorización";
                } else {
                    throw new Exception("No se pudieron agregar destinatarios para el correo");
                }
            }
        } catch (Throwable $e) {
            $mensajeCorreo = ", pero error al enviar correo: " . $e->getMessage();
        }
        if($SEND_MAIL === true){
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => "Solicitud para archivar la barra enviada correctamente." . $mensajeCorreo,
                'ruta_foto' => $ruta_foto_barra // Opcional: devolver la ruta si es necesario
            ]);

        }else{
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => "Solicitud para archivar la barra creada correctamente. Envío de correos no disponible.",
                'ruta_foto' => $ruta_foto_barra // Opcional: devolver la ruta si es necesario
            ]);
        }
        exit;
    }

    if ($action === 'autorizar_archivado') {
        
        if (empty($id)) throw new Exception("ID requerido para archivar.");

        $stmt = $conn->prepare("UPDATE inventario_cnc 
                                    SET estatus = 'Eliminado', 
                                        archivado_auth = 1, 
                                        updated_at = NOW() 
                                    WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        // Preparar y enviar correo (no crítico para la operación)
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
                
                if ($contadorCorreos > 0) {
                    // Preparar contenido del correo
                    $mail->isHTML(true);
                    if($DEV_MODE === true){
                        $mail->addAddress($DEV_EMAIL);
                    }
                    $asunto = "Solicitud autorizada para archivar barra";
                    
                    $cuerpo = "Dirección comercial ha autorizado el archivado de una barra del inventario de billets.</br>";
                    $cuerpo .= "Barra: <b>" . $lote_pedimento . "</b></br>";

                    $mail->Subject = $asunto;
                    $mail->Body = $cuerpo;
                    // Agregar correo de prueba
                    if($SEND_MAIL === true){
                        if (!$mail->send()) {
                            throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
                        }
                        $mensajeCorreo = " y correo enviado a inventarios";
                    }
                } else {
                    throw new Exception("No se pudieron agregar destinatarios para el correo");
                }
            }
        } catch (Throwable $e) {
            $mensajeCorreo = ", pero error al enviar correo: " . $e->getMessage();
        }
        if($SEND_MAIL === true){
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => "Barra archivada correctamente." . $mensajeCorreo
            ]);
        }else{
            // Respuesta exitosa
            echo json_encode([
                'success' => true,
                'message' => "Barra archivada correctamente. Envío de correos no disponible."
            ]);            
        }
                       
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