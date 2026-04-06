<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    // Validar que llegue el id
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de parametro no recibido.'
        ]);
        exit;
    }

    $id = (int) $_POST['id'];

    // Verificar que exista
    $stmt = $conn->prepare("SELECT id FROM parametros2 WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El registro no existe o ya fue eliminado.'
        ]);
        exit;
    }

    // Eliminar el registro
    $stmt = $conn->prepare("DELETE FROM parametros2 WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Registro eliminado correctamente.'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
