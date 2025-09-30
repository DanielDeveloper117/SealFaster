<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');
    if (isset($_GET['clave'], $_GET['stock'])) {
        $clave = $_GET['clave'];
        $stock = $_GET['stock'];
        
        // Preparar la consulta
        $stmt = $conn->prepare("SELECT * FROM inventario_cnc WHERE Clave = :clave AND stock >= :stock");
        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':stock', $stock);
        $stmt->execute();
        
        // Obtener resultados
        $arregloSelectClave = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Devolver los resultados en formato JSON
        echo json_encode($arregloSelectClave);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>