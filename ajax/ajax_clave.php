<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');
    if (isset($_POST['clave'])) {
        $clave = $_POST['clave'];
        
        // Preparar la consulta
        $stmt = $conn->prepare("SELECT * FROM inventario_cnc WHERE Clave = :clave");
        $stmt->bindParam(':clave', $clave);
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