<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');
    if (isset($_POST['di'])) {
        $di = $_POST['di'];
        $casoMaterial = "co".$_POST['material'];
        
        // Preparar la consulta
        $stmt = $conn->prepare("SELECT * FROM parametros2 WHERE caso = :caso AND limite_inferior <= :di AND :di <= limite_superior");
        $stmt->bindParam(':caso', $casoMaterial);
        $stmt->bindParam(':di', $di);
        $stmt->execute();
        
        // Obtener resultados
        $arregloSelectCO = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Devolver los resultados en formato JSON
        echo json_encode($arregloSelectCO);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>