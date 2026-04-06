<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    if (!isset($_GET['id_requisicion'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Falta parámetro: id_requisicion es requerido'
        ]);
        exit;
    }

    $id_requisicion = $_GET['id_requisicion'];

    // 1. Obtener información de la requisición
    $stmtRequisicion = $conn->prepare("
        SELECT * FROM requisiciones 
        WHERE id_requisicion = :id_requisicion
    ");
    $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtRequisicion->execute();
    $requisicion = $stmtRequisicion->fetch(PDO::FETCH_ASSOC);

    if (!$requisicion) {
        echo json_encode([
            'success' => false,
            'error' => 'No se encontró la requisición con ID: ' . $id_requisicion
        ]);
        exit;
    }

    // 2. Obtener todas las barras del control_almacen para esta requisición
    $stmtControlAlmacen = $conn->prepare("
        SELECT *
        FROM control_almacen 
        WHERE id_requisicion = :id_requisicion
        AND NOT (es_eliminacion = 1 AND es_eliminacion_auth = 1)
    ");
    $stmtControlAlmacen->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtControlAlmacen->execute();
    $barras = $stmtControlAlmacen->fetchAll(PDO::FETCH_ASSOC);

    if (!$barras) {
        echo json_encode([
            'success' => true,
            'id_requisicion' => $id_requisicion,
            'requisicion' => $requisicion,
            'barras' => [],
            'message' => 'No se encontraron barras en control_almacen para esta requisición'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'id_requisicion' => $id_requisicion,
        'total_barras' => count($barras),
        'requisicion' => $requisicion,
        'billets' => $barras
    ]);

} catch (PDOException $e) {
    error_log("Error en obtener_resultados_maquinado: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor: ' . $e->getMessage()
    ]);
    http_response_code(500);
} finally {
    $conn = null;
}
?>