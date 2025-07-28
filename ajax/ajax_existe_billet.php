<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json'); // Asegurar respuesta JSON
    if (isset($_POST['billet'])) {
        $billet = $_POST['billet'];

        // Consulta optimizada con `LIMIT 1`
        $stmt = $conn->prepare("SELECT 1 FROM inventario_cnc WHERE lote_pedimento = :billet LIMIT 1");
        $stmt->bindParam(':billet', $billet, PDO::PARAM_STR);
        $stmt->execute();

        // Verificar si existe el registro
        $existe = $stmt->fetchColumn() !== false;
        
        // Enviar respuesta JSON
        echo json_encode(['existe' => $existe]);
    }else{
        echo json_encode(['error' => 'Falta el parámetro billet']);
        exit;

    }


} catch (PDOException $e) {
    error_log("Error en la consulta: " . $e->getMessage());
    echo json_encode(['error' => 'Error en el servidor']);
    http_response_code(500);
} finally {
    $conn = null; // Cerrar la conexión
}

?>