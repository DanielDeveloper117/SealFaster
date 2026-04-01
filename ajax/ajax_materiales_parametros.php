<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
// Verificar sesión activa
session_start();
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
    exit;
}
try{
    header('Content-Type: application/json');      
    // Preparar la consulta
    $stmt = $conn->prepare("SELECT DISTINCT(material) FROM parametros ORDER BY material DESC");
    $stmt->execute();
    
    // Obtener resultados
    $materiales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Limpiar bytes nulos de las cadenas de material
    foreach ($materiales as &$mat) {
        $mat['material'] = trim(str_replace("\0", '', $mat['material']));
    }
    unset($mat);

    // Devolver los resultados en formato JSON
    echo json_encode($materiales);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>