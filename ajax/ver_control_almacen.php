<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['id_requisicion']) || empty(trim($_GET['id_requisicion']))) {
        echo json_encode([]);
        exit;
    }

    $id_requisicion = trim($_GET['id_requisicion']);

    if (!preg_match('/^\d+$/', $id_requisicion)) {
        echo json_encode([]);
        exit;
    }

    // Consulta principal: control_almacen
    $stmt = $conn->prepare("
        SELECT * 
        FROM control_almacen
        WHERE id_requisicion = :id_requisicion
        ORDER BY id_control ASC
    ");
    $stmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmt->execute();
    $billetsControlAlmacen = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($billetsControlAlmacen);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar la base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>
