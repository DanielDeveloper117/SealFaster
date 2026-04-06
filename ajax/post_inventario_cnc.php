<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }

    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';

    set_error_handler(function($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    $acciones_validas = ['insert', 'update', 'delete', 'insert2', 'autorizar_archivado'];
    if (!in_array($action, $acciones_validas)) {
        throw new Exception("Acción no válida.");
    }

    // Limpiar espacios automáticamente sin notificar al usuario
    $almacen_id     = preg_replace('/\s+/', '', trim($_POST['almacen_id'] ?? ''));
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
        if ($action === '' || $action === null) $errores[] = "Falta la acción";
        if (($id === '' || $id === null) && $action === 'update') $errores[] = "Falta el id";
        if ($estatus === '' || $estatus === null) $errores[] = "Faltan datos";
        if ($almacen_id === '' || $almacen_id === null) $errores[] = "Falta el almacén";
        if ($clave === '' || $clave === null) $errores[] = "Falta la clave";
        if ($material === '' || $material === null) $errores[] = "Falta el material";
        if ($proveedor === '' || $proveedor === null) $errores[] = "Falta el proveedor";
        if ($medida === '' || $medida === null) $errores[] = "Falta la medida";
        if ($max_usable === '' || $max_usable === null) $errores[] = "Falta el maximo usable";
        if ($stock === '' || $stock === null) $errores[] = "Falta el stock";
        if ($lote_pedimento === '' || $lote_pedimento === null) $errores[] = "Falta el lote";

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

        // --- NUEVA LÓGICA DE ESTATUS Y CREACIÓN DE PARÁMETRO ---
        $sqlP = "SELECT id, precio FROM parametros WHERE clave = :c OR (clave_alterna != '' AND clave_alterna = :ca)";
        $stmtP = $conn->prepare($sqlP);
        $stmtP->bindParam(':c', $clave);
        $stmtP->bindParam(':ca', $clave);
        $stmtP->execute();
        $paramRow = $stmtP->fetch(PDO::FETCH_ASSOC);

        if (!$paramRow) {
            // NO EXISTE: insertar automáticamente en parámetros
            $sqlIns = "INSERT INTO parametros (clave, clave_alterna, material, proveedor, tipo, interior, exterior, max_usable, precio, usuario_id, created_at, updated_at)
                       VALUES (:c, NULL, :m, :p, 'S/T', :i, :e, :mu, 0.00, :uid, NOW(), NOW())";
            $stmtIns = $conn->prepare($sqlIns);
            $stmtIns->bindParam(':c', $clave);
            $stmtIns->bindParam(':m', $material);
            $stmtIns->bindParam(':p', $proveedor);
            $stmtIns->bindParam(':i', $interior, PDO::PARAM_INT);
            $stmtIns->bindParam(':e', $exterior, PDO::PARAM_INT);
            $muParams = (float)($max_usable ?? 0);
            $stmtIns->bindParam(':mu', $muParams);
            $stmtIns->bindParam(':uid', $_SESSION['id']);
            $stmtIns->execute();
            
            $estatus = "Clave nueva pendiente";
        } else {
            // SÍ EXISTE
            $precioParam = (float)$paramRow['precio'];
            if ($precioParam <= 0.00) {
                $estatus = "Clave nueva pendiente";
            } else {
                $estatus = "Disponible para cotizar";
            }
        }
        // --------------------------------------------------------
    }

    // Variables para acciones posteriores
    $insertId = null;
    $claveParaCorreo = $clave;
    $mensajeCorreo = ""; // Para almacenar el estado del envío de correo

    if ($action === 'insert' || $action === 'insert2') {
        $sql = "INSERT INTO inventario_cnc 
                (almacen_id, clave, medida, interior, exterior, proveedor, material, max_usable, pre_stock, stock, lote_pedimento, estatus)
                VALUES 
                (:almacen_id, :clave, :medida, :interior, :exterior, :proveedor, :material, :max_usable, :pre_stock, :stock, :lote_pedimento, :estatus)";
        $stmt = $conn->prepare($sql);
    }

    if ($action === 'update') {
        if (empty($id)) throw new Exception("ID requerido para actualizar.");
        $sql = "UPDATE inventario_cnc SET 
                    almacen_id = :almacen_id,
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
        $stmt->bindParam(':almacen_id', $almacen_id);
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

        // MANEJO DE ESTATUS Y ENVÍO DE CORREO (Reducido a Clave nueva pendiente)
        if ($estatus === 'Clave nueva pendiente') {
            $asunto = "Nuevo billet con clave nueva - Relación pendiente o Sin Precio";
            $mensaje = "Se ha agregado o detectado un billet con clave nueva o costo cero, pendiente de validación o captura de tarifa en catálogo de parámetros. Clave: " . $clave;
            
            $correoEnviado = false;
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
                if($DEV_MODE === false){
                    $mail->addAddress($AUX_GESTOR_EMAIL);
                }else{
                    $mail->addAddress($DEV_EMAIL);
                }
                
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
        $justificacion_archivado = $_POST['justificacion_archivado'];
        if (empty($id)) throw new Exception("ID requerido para archivar.");
        if (empty($justificacion_archivado)) throw new Exception("Justificación requerida para archivar.");
        if (strlen($justificacion_archivado) < 10) throw new Exception("Justificación debe tener mínimo 10 caracteres.");
        
        // Validar y procesar el archivo de imagen
        $file = $_FILES['foto_archivar'];
        
        // Validar que se haya subido una imagen
        if (!isset($_FILES['foto_archivar']) || $_FILES['foto_archivar']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Debe subir una fotografía de la barra para solicitar su archivado.");
        }

        // 1. CIBERSEGURIDAD: Validar tamaño ANTES de procesar
        $max_size = 5 * 1024 * 1024; // 5MB
        if ($_FILES['foto_archivar']['size'] > $max_size) {
            throw new Exception("La imagen es demasiado pesada (Máx 5MB).");
        }
        
        try {
            // Importar la clase (ajusta la ruta según tu estructura)
            require_once(ROOT_PATH . 'includes/webp_conversor.php');

            $upload_dir = ROOT_PATH . 'files/fotos/barras/';
            
            // 2. USAR LA FUNCIÓN MODULAR
            // Esto convierte a WebP, comprime y genera un nombre seguro automáticamente
            $newFileName = ImageHelper::processAndConvertToWebP(
                $_FILES['foto_archivar'], 
                $upload_dir, 
                'barra_' . $id
            );

            // 3. Ruta para la DB
            $ruta_foto_barra = '../files/fotos/barras/' . $newFileName;
            $file_path = $upload_dir . $newFileName; // Para PHPMailer

        } catch (Exception $e) {
            throw new Exception("Error procesando imagen: " . $e->getMessage());
        }

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
                    $mail->AddEmbeddedImage($file_path, 'barra_foto', $newFileName);
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