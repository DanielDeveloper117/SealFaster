<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');
    $material = $_POST['material'];
    
    $stmt = $conn->prepare("SELECT dureza FROM inventario_cnc WHERE material = :material LIMIT 1");
    $stmt->bindParam(':material', $material);
    $stmt->execute();
    // Obtener el primer resultado
    $dureza = $stmt->fetch(PDO::FETCH_ASSOC);
    $valorDureza = "";

    if ($dureza) {
        if ($dureza['dureza'] === "suave") {  
            $valorDureza = "2.00";
        } else {
            $valorDureza = "2.50";
        }

        echo json_encode(['dureza' => $valorDureza]);
    } else {
        echo json_encode(['error' => 'No se encontró el material']);
    }

} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; 
}
?>

