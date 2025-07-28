<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');

    // Preparar la consulta
    $stmt = $conn->prepare("SELECT DISTINCT proveedor FROM parametros ORDER BY proveedor DESC");
    $stmt->execute();
    
    // Obtener resultados
    $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los resultados en formato JSON
    echo json_encode($proveedores);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>