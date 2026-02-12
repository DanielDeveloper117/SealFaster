<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json'); // Asegurar respuesta JSON

    $id = $_GET['id'] ?? null;

    if ($id) {
        // 1. Obtener la requisición
        $stmt = $conn->prepare("SELECT * FROM requisiciones WHERE id_requisicion = ?");
        $stmt->execute([$id]);
        $requisicion = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Obtener los detalles de las cotizaciones asociadas
        // Convertimos la cadena "101, 102" en un array para la consulta
        $ids = array_map('trim', explode(',', $requisicion['cotizaciones']));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $stmtCot = $conn->prepare("SELECT * FROM cotizacion_materiales WHERE id_cotizacion IN ($placeholders)");
        $stmtCot->execute($ids);
        $detallesCot = $stmtCot->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'requisicion' => $requisicion,
            'cotizaciones_detalles' => $detallesCot
        ]);
    }else{
        echo json_encode([
            'success' => false,
            'message' => 'Falta el parámetro id'
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