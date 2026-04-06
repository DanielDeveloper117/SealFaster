<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    if(!isset($_GET['excluir'])){
        throw new Exception("Falta el almacen de origen.");
    }
    $excluirId = $_GET['excluir'];
    // Obtener todos los almacenes disponibles
    $sql = "SELECT id, almacen, descripcion FROM almacenes WHERE id != :id_almacen ORDER BY almacen ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id_almacen', $excluirId, PDO::PARAM_INT);
    $stmt->execute();
    $almacenes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($almacenes)) {
        throw new Exception("No hay almacenes disponibles.");
    }
    
    echo json_encode([
        'success' => true,
        'almacenes' => $almacenes
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar almacenes. ' . $e->getMessage()
    ]);
}
?>
