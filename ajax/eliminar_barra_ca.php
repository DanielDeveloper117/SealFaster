<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    if (!isset($_POST['id_control']) || !is_numeric($_POST['id_control'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID invalido o no enviado.'
        ]);
        exit;
    }

    $id_control = intval($_POST['id_control']);

    // Obtener el lote_pedimento antes de eliminar
    $stmtGet = $conn->prepare("
        SELECT lote_pedimento 
        FROM control_almacen 
        WHERE id_control = :id_control
    ");
    $stmtGet->bindParam(':id_control', $id_control, PDO::PARAM_INT);
    $stmtGet->execute();

    $registro = $stmtGet->fetch(PDO::FETCH_ASSOC);

    if (!$registro) {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontro el registro.'
        ]);
        exit;
    }

    $lote_pedimento = $registro['lote_pedimento'];

    // Eliminar el registro
    $stmtDelete = $conn->prepare("
        DELETE FROM control_almacen 
        WHERE id_control = :id_control
    ");
    $stmtDelete->bindParam(':id_control', $id_control, PDO::PARAM_INT);
    $stmtDelete->execute();

    if ($stmtDelete->rowCount() > 0) {
        // Rehabilitar el registro en inventario_cnc
        $stmtUpdate = $conn->prepare("
            UPDATE inventario_cnc 
            SET estatus = 'Habilitado' 
            WHERE lote_pedimento = :lote_pedimento
        ");
        $stmtUpdate->bindParam(':lote_pedimento', $lote_pedimento);
        $stmtUpdate->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Registro eliminado correctamente. Barra habilitada para cotizar.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontro el registro para eliminar.'
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
?>
