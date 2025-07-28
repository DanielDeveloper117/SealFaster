<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');
    if (isset($_POST['perfil'])) {
        $perfil = $_POST['perfil'];
        
        // Preparar la consulta
        $stmt = $conn->prepare("SELECT * FROM perfiles WHERE perfil = :perfil");
        $stmt->bindParam(':perfil', $perfil);
        $stmt->execute();
        
        // Obtener resultados
        $arregloSelectPerfil = $stmt->fetch(PDO::FETCH_ASSOC);

        // Devolver los resultados en formato JSON
        echo json_encode($arregloSelectPerfil);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>