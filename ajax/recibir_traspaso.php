<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
try {
    header('Content-Type: application/json');
    
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        exit;
    }
    
    // Verificar que se recibió el ID del traspaso
    if (!isset($_POST['id']) || empty($_POST['id']) || !preg_match('/^\d+$/', $_POST['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de traspaso inválido']);
        exit;
    }
    
    $operacion_id = (int)$_POST['id'];
    
    // =============== VALIDAR IMÁGENES ===============
    if (empty($_FILES['img_recepcion_paquete'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Debe cargar la fotografía del paquete recibido']);
        exit;
    }
    
    if (empty($_FILES['img_recepcion_barras'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Debe cargar la fotografía de las barras recibidas']);
        exit;
    }
    
    // Validar tamaño de archivo (5MB)
    if ($_FILES['img_recepcion_paquete']['size'] > 5242880) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'La imagen del paquete no debe exceder 5MB']);
        exit;
    }
    
    if ($_FILES['img_recepcion_barras']['size'] > 5242880) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'La imagen de las barras no debe exceder 5MB']);
        exit;
    }
    
    // Validar tipo MIME
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $tipoArchivoPaquete = mime_content_type($_FILES['img_recepcion_paquete']['tmp_name']);
    $tipoArchivoBarras = mime_content_type($_FILES['img_recepcion_barras']['tmp_name']);
    
    if (!in_array($tipoArchivoPaquete, $tiposPermitidos)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El archivo del paquete debe ser una imagen válida (JPEG, PNG, GIF, WebP)']);
        exit;
    }
    
    if (!in_array($tipoArchivoBarras, $tiposPermitidos)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El archivo de las barras debe ser una imagen válida (JPEG, PNG, GIF, WebP)']);
        exit;
    }
    
    // =============== OBTENER DATOS DEL TRASPASO ===============
    $sqlObtener = "SELECT id, almacen_destino_id FROM operaciones_inv 
                   WHERE id = :id AND tipo = 'Traspaso' AND recibido = 0";
    $stmtObtener = $conn->prepare($sqlObtener);
    $stmtObtener->bindValue(':id', $operacion_id, PDO::PARAM_INT);
    $stmtObtener->execute();
    
    $operacion = $stmtObtener->fetch(PDO::FETCH_ASSOC);
    
    if (!$operacion) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'El traspaso no existe o ya ha sido recibido']);
        exit;
    }
    
    $almacen_destino_id = $operacion['almacen_destino_id'];
    
    // =============== PROCESAR IMÁGENES ===============
    $extensionPaquete = strtolower(pathinfo($_FILES['img_recepcion_paquete']['name'], PATHINFO_EXTENSION));
    $nombreArchivoPaquete = uniqid('img_') . '.' . $extensionPaquete;
    
    $extensionBarras = strtolower(pathinfo($_FILES['img_recepcion_barras']['name'], PATHINFO_EXTENSION));
    $nombreArchivoBarras = uniqid('img_') . '.' . $extensionBarras;
    
    $rutaCarpeta = ROOT_PATH . 'files/operaciones_inv/';
    
    // Crear carpeta si no existe
    if (!is_dir($rutaCarpeta)) {
        mkdir($rutaCarpeta, 0755, true);
    }
    
    $rutaCompletaPaquete = $rutaCarpeta . $nombreArchivoPaquete;
    $rutaCompletaBarras = $rutaCarpeta . $nombreArchivoBarras;
    
    // =============== INICIAR TRANSACCIÓN ===============
    $conn->beginTransaction();
    
    try {
        // =============== ACTUALIZAR REGISTRO DE OPERACIONES_INV ===============
        $sqlActualizarOperacion = "UPDATE operaciones_inv 
                                   SET recibido = 1,
                                       recibio_id = :recibio_id,
                                       fecha_recibido = NOW(),
                                       img_recepcion_paquete = :img_recepcion_paquete,
                                       img_recepcion_barras = :img_recepcion_barras,
                                       updated_at = NOW()
                                   WHERE id = :id";
        
        $stmtActualizarOperacion = $conn->prepare($sqlActualizarOperacion);
        $stmtActualizarOperacion->bindValue(':id', $operacion_id, PDO::PARAM_INT);
        $stmtActualizarOperacion->bindValue(':recibio_id', $_SESSION['id'], PDO::PARAM_INT);
        $stmtActualizarOperacion->bindValue(':img_recepcion_paquete', "files/operaciones_inv/" . $nombreArchivoPaquete, PDO::PARAM_STR);
        $stmtActualizarOperacion->bindValue(':img_recepcion_barras', "files/operaciones_inv/" . $nombreArchivoBarras, PDO::PARAM_STR);
        
        if (!$stmtActualizarOperacion->execute()) {
            throw new Exception("Error al actualizar el registro de operación");
        }
        
        // =============== OBTENER BARRAS ASOCIADAS ===============
        $sqlBarras = "SELECT id FROM inventario_cnc WHERE operacion_id = :operacion_id";
        $stmtBarras = $conn->prepare($sqlBarras);
        $stmtBarras->bindValue(':operacion_id', $operacion_id, PDO::PARAM_INT);
        $stmtBarras->execute();
        $barras = $stmtBarras->fetchAll(PDO::FETCH_ASSOC);
        
        // =============== ACTUALIZAR BARRAS EN INVENTARIO_CNC ===============
        if (count($barras) > 0) {
            $sqlActualizarBarras = "UPDATE inventario_cnc 
                                    SET almacen_id = :almacen_destino_id,
                                        estatus = 'Disponible para cotizar',
                                        updated_at = NOW()
                                    WHERE operacion_id = :operacion_id";
            
            $stmtActualizarBarras = $conn->prepare($sqlActualizarBarras);
            $stmtActualizarBarras->bindValue(':almacen_destino_id', $almacen_destino_id, PDO::PARAM_INT);
            $stmtActualizarBarras->bindValue(':operacion_id', $operacion_id, PDO::PARAM_INT);
            
            if (!$stmtActualizarBarras->execute()) {
                throw new Exception("Error al actualizar las barras");
            }
        }
        
        // =============== GUARDAR IMÁGENES ===============
        if (!move_uploaded_file($_FILES['img_recepcion_paquete']['tmp_name'], $rutaCompletaPaquete)) {
            throw new Exception("Error al guardar la imagen del paquete. Por favor intente más tarde.");
        }
        
        if (!move_uploaded_file($_FILES['img_recepcion_barras']['tmp_name'], $rutaCompletaBarras)) {
            throw new Exception("Error al guardar la imagen de las barras. Por favor intente más tarde.");
        }
        
        // =============== CONFIRMAR TRANSACCIÓN ===============
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Traspaso recibido correctamente',
            'operacion_id' => $operacion_id,
            'barras_actualizadas' => count($barras)
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        
        // Limpiar archivos si fueron creados
        if (file_exists($rutaCompletaPaquete)) {
            unlink($rutaCompletaPaquete);
        }
        if (file_exists($rutaCompletaBarras)) {
            unlink($rutaCompletaBarras);
        }
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al procesar la recepción: ' . $e->getMessage()
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>
