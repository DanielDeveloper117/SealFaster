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
    
    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Sesión expirada']);
        exit;
    }
    
    // Verificar que se recibió el ID de venta
    if (!isset($_POST['id']) || empty($_POST['id']) || !preg_match('/^\d+$/', $_POST['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de venta inválido']);
        exit;
    }
    
    $operacion_id = (int)$_POST['id'];
    
    // =============== OBTENER DATOS DE VENTA ===============
    $sqlObtener = "SELECT id, img_envio_barras, img_envio_paquete
                   FROM operaciones_inv 
                   WHERE id = :id AND tipo = 'Venta'";
    $stmtObtener = $conn->prepare($sqlObtener);
    $stmtObtener->bindValue(':id', $operacion_id, PDO::PARAM_INT);
    $stmtObtener->execute();
    
    $operacion = $stmtObtener->fetch(PDO::FETCH_ASSOC);
    
    if (!$operacion) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'La venta no existe o no es una venta válida']);
        exit;
    }
    
    // =============== INICIAR TRANSACCIÓN ===============
    $conn->beginTransaction();
    
    try {
        // =============== OBTENER BARRAS ASOCIADAS ===============
        $sqlBarras = "SELECT id FROM inventario_cnc WHERE operacion_id = :operacion_id";
        $stmtBarras = $conn->prepare($sqlBarras);
        $stmtBarras->bindValue(':operacion_id', $operacion_id, PDO::PARAM_INT);
        $stmtBarras->execute();
        $barras = $stmtBarras->fetchAll(PDO::FETCH_ASSOC);
        
        // =============== ACTUALIZAR ESTATUS DE BARRAS ===============
        if (count($barras) > 0) {
            $sqlActualizar = "UPDATE inventario_cnc 
                              SET estatus = 'Disponible para cotizar'
                              WHERE operacion_id = :operacion_id";
            $stmtActualizar = $conn->prepare($sqlActualizar);
            $stmtActualizar->bindValue(':operacion_id', $operacion_id, PDO::PARAM_INT);
            $stmtActualizar->execute();
        }
        
        // =============== ELIMINAR REGISTRO DE OPERACIONES_INV ===============
        $sqlEliminar = "DELETE FROM operaciones_inv WHERE id = :id";
        $stmtEliminar = $conn->prepare($sqlEliminar);
        $stmtEliminar->bindValue(':id', $operacion_id, PDO::PARAM_INT);
        $stmtEliminar->execute();
        
        // =============== CONFIRMAR TRANSACCIÓN ===============
        $conn->commit();
        
        // =============== ELIMINAR ARCHIVOS FÍSICOS ===============
        $archivos_a_eliminar = [
            $operacion['img_envio_barras'],
            $operacion['img_envio_paquete']
        ];
        
        foreach ($archivos_a_eliminar as $ruta_archivo) {
            if (!empty($ruta_archivo)) {
                $ruta_completa = ROOT_PATH . $ruta_archivo;
                if (file_exists($ruta_completa)) {
                    unlink($ruta_completa);
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Venta eliminada correctamente',
            'barras_actualizadas' => count($barras)
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'error' => 'Error al eliminar la venta: ' . $e->getMessage()
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
