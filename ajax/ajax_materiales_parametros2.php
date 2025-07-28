<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');

    // Preparar la consulta
    $stmt = $conn->prepare("SELECT caso FROM parametros2 WHERE descripcion = 'Material'");
    $stmt->execute();
    
    // Obtener resultados
    $arregloSelectMaterialesParametros2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los resultados en formato JSON
    echo json_encode($arregloSelectMaterialesParametros2);

} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>