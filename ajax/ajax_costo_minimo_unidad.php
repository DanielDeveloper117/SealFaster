<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');
    if (isset($_POST['di'])) {
        $di = $_POST['di'];

        // Preparar la consulta
        $stmt = $conn->prepare("SELECT * FROM parametros2 WHERE limite_inferior <= :di AND :di <= limite_superior  AND  caso = 'cmu'");
        $stmt->bindParam(':di', $di);
        $stmt->execute();
        
        // Obtener resultados
        $costoMinimoUnidad = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Devolver los resultados en formato JSON
        echo json_encode($costoMinimoUnidad);
    }
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>