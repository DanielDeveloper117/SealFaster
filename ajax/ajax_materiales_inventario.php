<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');      
    // Preparar la consulta
    $stmt = $conn->prepare("SELECT DISTINCT(material) FROM inventario_cnc WHERE material IS NOT NULL AND material != '' ORDER BY material ASC");
    $stmt->execute();
    
    // Obtener resultados
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Limpiar bytes nulos de las cadenas
    foreach ($resultados as &$res) {
        $res['material'] = trim(str_replace("\0", '', $res['material']));
    }
    unset($res);

    // Devolver los resultados en formato JSON
    echo json_encode($resultados);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>
