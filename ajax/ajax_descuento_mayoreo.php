<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');
    if (isset($_POST['q'])) {
        $q = $_POST['q'];
        
        // Preparar la consulta
        $stmt = $conn->prepare("SELECT * FROM parametros2 WHERE limite_inferior <= :q1 AND :q2 <= limite_superior  AND  caso = 'dm'");
        $stmt->bindParam(':q1', $q);
        $stmt->bindParam(':q2', $q);
        $stmt->execute();
        
        // Obtener resultados
        $arregloSelectDescuentoMayoreo = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Devolver los resultados en formato JSON
        echo json_encode($arregloSelectDescuentoMayoreo);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; 
}
?>