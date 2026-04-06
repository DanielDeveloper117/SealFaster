<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

include(ROOT_PATH . 'includes/backend_info_user.php');
header('Content-Type: application/json');

try {
    // =============== VALIDACIONES BÁSICAS ===============
    
    // Validar tipo de operación
    if (empty($_POST['tipo']) || !in_array($_POST['tipo'], ['Traspaso', 'Venta'])) {
        throw new Exception("Tipo de operación inválido.");
    }
    
    // Validar almacén de origen
    if (empty($_POST['almacen_origen_id']) || !preg_match('/^\d+$/', $_POST['almacen_origen_id']) || $_POST['almacen_origen_id'] == 0) {
        throw new Exception("Almacén de origen inválido.");
    }
    
    // Validar almacén de destino (solo para traspasos)
    $almacen_destino_id = 0;
    if ($_POST['tipo'] === 'Traspaso') {
        if (empty($_POST['almacen_destino_id']) || !preg_match('/^\d+$/', $_POST['almacen_destino_id'])) {
            throw new Exception("Almacén de destino inválido.");
        }
        $almacen_destino_id = (int) $_POST['almacen_destino_id'];
    }
    
    // Validar justificación
    if (empty($_POST['justificacion']) || strlen(trim($_POST['justificacion'])) < 10) {
        throw new Exception("La justificación debe tener al menos 10 caracteres.");
    }
    
    // Validar array de IDs
    if (empty($_POST['ids']) || !is_array($_POST['ids']) || count($_POST['ids']) === 0) {
        throw new Exception("Debe seleccionar al menos una barra.");
    }
    
    // Validar imagen
    if (empty($_FILES['img_envio_barras'])) {
        throw new Exception("Debe cargar una fotografía de las barras.");
    }
    
    // Validar tamaño de archivo (5MB)
    if ($_FILES['img_envio_barras']['size'] > 5242880) {
        throw new Exception("La imagen de barras no debe exceder 5MB.");
    }
   
    // Validar imagen
    if (empty($_FILES['img_envio_paquete'])) {
        throw new Exception("Debe cargar una fotografía del paquete.");
    }
    
    // Validar tamaño de archivo (5MB)
    if ($_FILES['img_envio_paquete']['size'] > 5242880) {
        throw new Exception("La imagen del paquete no debe exceder 5MB.");
    }

    // Validar tipo MIME
    $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $tipoArchivoBarras = mime_content_type($_FILES['img_envio_barras']['tmp_name']);
    $tipoArchivoPaquete = mime_content_type($_FILES['img_envio_paquete']['tmp_name']);

    if (!in_array($tipoArchivoBarras, $tiposPermitidos)) {
        throw new Exception("El archivo de barrasdebe ser una imagen válida (JPEG, PNG, GIF, WebP).");
    }
    if (!in_array($tipoArchivoPaquete, $tiposPermitidos)) {
        throw new Exception("El archivo del paquete debe ser una imagen válida (JPEG, PNG, GIF, WebP).");
    }
    
    // =============== SANITIZAR DATOS ===============
    $tipo = trim($_POST['tipo']);
    $almacen_origen_id = (int) $_POST['almacen_origen_id'];
    $justificacion = trim($_POST['justificacion']);
    $ids = array_map('intval', $_POST['ids']);
    
    // =============== VERIFICAR ALMACENES ===============
    // Verificar almacén de origen
    $sqlAlmacenOrigen = "SELECT id FROM almacenes WHERE id = :id LIMIT 1";
    $stmtAlmacenOrigen = $conn->prepare($sqlAlmacenOrigen);
    $stmtAlmacenOrigen->bindValue(':id', $almacen_origen_id, PDO::PARAM_INT);
    $stmtAlmacenOrigen->execute();
    
    if (!$stmtAlmacenOrigen->fetch()) {
        throw new Exception("El almacén de origen no existe.");
    }
    
    // Verificar almacén de destino si es traspaso
    if ($tipo === 'Traspaso') {
        $sqlAlmacenDestino = "SELECT id FROM almacenes WHERE id = :id LIMIT 1";
        $stmtAlmacenDestino = $conn->prepare($sqlAlmacenDestino);
        $stmtAlmacenDestino->bindValue(':id', $almacen_destino_id, PDO::PARAM_INT);
        $stmtAlmacenDestino->execute();
        
        if (!$stmtAlmacenDestino->fetch()) {
            throw new Exception("El almacén de destino no existe.");
        }
        
        // Validar que no sean el mismo almacén
        if ($almacen_origen_id === $almacen_destino_id) {
            throw new Exception("El almacén de origen y destino no pueden ser iguales.");
        }
    }
    
    // =============== VERIFICAR BARRAS ===============
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sqlBarras = "SELECT id, almacen_id FROM inventario_cnc WHERE id IN ($placeholders)";
    $stmtBarras = $conn->prepare($sqlBarras);
    $stmtBarras->execute($ids);
    $barras = $stmtBarras->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($barras) !== count($ids)) {
        throw new Exception("Una o más barras no existen.");
    }
    
    // Validar que todas las barras pertenezcan al almacén de origen
    foreach ($barras as $barra) {
        if ((int)$barra['almacen_id'] !== $almacen_origen_id) {
            throw new Exception("Todas las barras deben pertenecer al almacén de origen.");
        }
    }
    
    // =============== PROCESAR IMAGEN ===============
    $extensionBarras = strtolower(pathinfo($_FILES['img_envio_barras']['name'], PATHINFO_EXTENSION));
    $nombreArchivoBarras = uniqid('img_') . '.' . $extensionBarras;
    $extensionPaquete = strtolower(pathinfo($_FILES['img_envio_paquete']['name'], PATHINFO_EXTENSION));
    $nombreArchivoPaquete = uniqid('img_') . '.' . $extensionPaquete;
    $rutaCarpeta = ROOT_PATH . 'files/operaciones_inv/';
    
    // Crear carpeta si no existe
    if (!is_dir($rutaCarpeta)) {
        mkdir($rutaCarpeta, 0755, true);
    }
    
    $rutaCompletaBarras = $rutaCarpeta . $nombreArchivoBarras;
    $rutaCompletaPaquete = $rutaCarpeta . $nombreArchivoPaquete;
    
    // =============== INICIAR TRANSACCIÓN ===============
    $conn->beginTransaction();
    
    try {
        // =============== CREAR REGISTRO EN OPERACIONES_INV ===============
        $sqlOperacion = "INSERT INTO operaciones_inv (
            usuario_id,
            tipo,
            almacen_origen_id,
            almacen_destino_id,
            justificacion,
            img_envio_barras,
            img_envio_paquete,
            recibido,
            created_at,
            updated_at
        ) VALUES (
            :usuario_id,
            :tipo,
            :almacen_origen_id,
            :almacen_destino_id,
            :justificacion,
            :img_envio_barras,
            :img_envio_paquete,
            0,
            NOW(),
            NOW()
        )";
        
        $stmtOperacion = $conn->prepare($sqlOperacion);
        $stmtOperacion->bindValue(':usuario_id', $_SESSION['id'], PDO::PARAM_INT);
        $stmtOperacion->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        $stmtOperacion->bindValue(':almacen_origen_id', $almacen_origen_id, PDO::PARAM_INT);
        $stmtOperacion->bindValue(':almacen_destino_id', $almacen_destino_id, PDO::PARAM_INT);
        $stmtOperacion->bindValue(':justificacion', $justificacion, PDO::PARAM_STR);
        $stmtOperacion->bindValue(':img_envio_barras', "files/operaciones_inv/" . $nombreArchivoBarras, PDO::PARAM_STR);
        $stmtOperacion->bindValue(':img_envio_paquete', "files/operaciones_inv/" . $nombreArchivoPaquete, PDO::PARAM_STR);
        
        if (!$stmtOperacion->execute()) {
            throw new Exception("Error al crear el registro de operación.");
        }
        
        $operacion_id = $conn->lastInsertId();
        
        // =============== ACTUALIZAR BARRAS EN INVENTARIO_CNC ===============
        $sqlActualizar = "UPDATE inventario_cnc 
                         SET operacion_id = :operacion_id,
                             estatus = :estatus,
                             updated_at = NOW()
                         WHERE id = :id";
        
        $stmtActualizar = $conn->prepare($sqlActualizar);
        $estatus = ($tipo === 'Traspaso') ? 'Traspaso' : 'Venta';
        
        foreach ($ids as $id) {
            $stmtActualizar->bindValue(':operacion_id', $operacion_id, PDO::PARAM_INT);
            $stmtActualizar->bindValue(':estatus', $estatus, PDO::PARAM_STR);
            $stmtActualizar->bindValue(':id', $id, PDO::PARAM_INT);
            
            if (!$stmtActualizar->execute()) {
                throw new Exception("Error al actualizar las barras.");
            }
        }
        
        // =============== GUARDAR IMAGENES  ===============
        if (!move_uploaded_file($_FILES['img_envio_barras']['tmp_name'], $rutaCompletaBarras)) {
            throw new Exception("Error al guardar la imagen de barras. Por favor intente más tarde.");
        }
        if (!move_uploaded_file($_FILES['img_envio_paquete']['tmp_name'], $rutaCompletaPaquete)) {
            throw new Exception("Error al guardar la imagen de paquete. Por favor intente más tarde.");
        }
        
        // =============== CONFIRMAR TRANSACCIÓN ===============
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Operación registrada correctamente.',
            'operacion_id' => $operacion_id,
            'tipo' => $tipo,
            'cantidad_barras' => count($ids)
        ]);
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        
        // Limpiar archivo si fue creado
        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }
        
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
