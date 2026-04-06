<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');
    if (isset($_GET['id_cotizacion'])) {
        $id_cotizacion = $_GET['id_cotizacion'];

        $sql = "SELECT * FROM cotizacion_materiales WHERE id_cotizacion = :id_cotizacion";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_cotizacion', $id_cotizacion, PDO::PARAM_INT);
        $stmt->execute();
        
        // Obtener resultados
        $arregloSelectCotizacion = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Devolver los resultados en formato JSON
        echo json_encode($arregloSelectCotizacion);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>