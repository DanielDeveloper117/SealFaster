<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');
try {

    $lote_pedimento = isset($_POST['lote_pedimento']) ? trim($_POST['lote_pedimento']) : '';
    
    if (empty($lote_pedimento)) {
        throw new Exception('Lote/Pedimento no proporcionado');
    }

    // Actualizar el estatus de la barra a "Disponible para cotizar"
    $sql = "UPDATE inventario_cnc 
            SET estatus = 'Disponible para cotizar', 
                updated_at = NOW()
            WHERE lote_pedimento LIKE :lote_pedimento";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':lote_pedimento', '%' . $lote_pedimento . '%', PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'mensaje' => 'Barra liberada correctamente'
        ]);
    } else {
        throw new Exception('No se encontró la barra o ya estaba liberada');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ]);
}
