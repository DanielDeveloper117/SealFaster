<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');
    if (isset($_GET['materialValue'])) {
        $di = $_GET['di'];
        $materialValue = "mu".$_GET['materialValue'];
        
        // Preparar la consulta
        $stmt = $conn->prepare("SELECT valor FROM parametros2 WHERE caso = :caso AND limite_inferior <= :di AND :di <= limite_superior");
        $stmt->bindParam(':caso', $materialValue);
        $stmt->bindParam(':di', $di);
        $stmt->execute();
        
        // Obtener resultados
        $arregloSelectMultiploUtilidad = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Devolver los resultados en formato JSON
        echo json_encode($arregloSelectMultiploUtilidad);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>