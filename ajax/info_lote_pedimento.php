<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json'); // Asegurar respuesta JSON
    if (isset($_POST['billet'])) {
        $billet = $_POST['billet'];

        // Consulta optimizada con `LIMIT 1`
        $stmt = $conn->prepare("SELECT * FROM inventario_cnc WHERE lote_pedimento = :billet LIMIT 1");
        $stmt->bindParam(':billet', $billet, PDO::PARAM_STR);
        $stmt->execute();

        // Verificar si existe el registro
        $billetResult = $stmt->fetch();
        if($stmt->rowCount() > 0){
            // Enviar respuesta JSON indicando que se encontró el lote
            echo json_encode([
                'success' => true,
                'message' => 'Lote encontrado',
                'billetResult' => $billetResult
            ]);

        }else{
            // Enviar respuesta JSON indicando que no se encontró el lote
            echo json_encode([
                'success' => false,
                'message' => 'Lote no encontrado',
                'billetResult' => null
            ]);
        }
        
    }else{
        echo json_encode([
            'success' => false,
            'message' => 'Falta el parámetro billet'
        ]);
        exit;

    }


} catch (PDOException $e) {
    error_log("Error en la consulta: " . $e->getMessage());
    // Devolver un mensaje más descriptivo para depuración sin exponer detalles sensibles
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor',
        'error_detail' => $e->getMessage()
    ]);
    http_response_code(500);
} finally {
    $conn = null; // Cerrar la conexión
}

?>