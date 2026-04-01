<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
// Verificar sesión activa
session_start();
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
    exit;
}
try {
    header('Content-Type: application/json');      
    // Preparar la consulta
    $stmt = $conn->prepare("SELECT DISTINCT(proveedor) FROM parametros WHERE proveedor IS NOT NULL AND proveedor != '' ORDER BY proveedor ASC");
    $stmt->execute();
    
    // Obtener resultados
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Limpiar bytes nulos de las cadenas
    foreach ($resultados as &$res) {
        $res['proveedor'] = trim(str_replace("\0", '', $res['proveedor']));
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