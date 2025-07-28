<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    if (!isset($_POST['id_control']) || !is_numeric($_POST['id_control'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID inválido o no enviado.'
        ]);
        exit;
    }

    $id_control = intval($_POST['id_control']);

    // Preparar y ejecutar DELETE
    $stmt = $conn->prepare("DELETE FROM control_almacen WHERE id_control = :id_control");
    $stmt->bindParam(':id_control', $id_control, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Registro eliminado correctamente.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el registro para eliminar.'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
