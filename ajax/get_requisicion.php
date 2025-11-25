<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json'); // Asegurar respuesta JSON
    if (isset($_GET['id_requisicion'])) {
        $id_requisicion = $_GET['id_requisicion'];

        // Consulta optimizada con `LIMIT 1`
        $stmt = $conn->prepare("SELECT * FROM requisiciones WHERE id_requisicion = :id_requisicion LIMIT 1");
        $stmt->bindParam(':id_requisicion', $id_requisicion);
        $stmt->execute();

        // Verificar si existe el registro
        $requisicionData = $stmt->fetch();
        if($stmt->rowCount() > 0){
            // Enviar respuesta JSON indicando que se encontró el lote
            echo json_encode([
                'success' => true,
                'message' => 'Requisición encontrada',
                'data' => $requisicionData
            ]);

        }else{
            // Enviar respuesta JSON indicando que no se encontró el lote
            echo json_encode([
                'success' => false,
                'message' => 'Requisición no encontrada',
                'data' => null
            ]);
        }
        
    }else{
        echo json_encode([
            'success' => false,
            'message' => 'Falta el parámetro id_requisicion'
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