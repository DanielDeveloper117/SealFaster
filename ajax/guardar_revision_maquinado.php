<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}

try {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        exit();
    }

    if (!isset($_POST['id_requisicion']) || empty($_POST['id_requisicion'])) {
        echo json_encode(['success' => false, 'error' => 'ID de requisición es requerido']);
        exit();
    }

    $id_requisicion = $_POST['id_requisicion'];
    $observaciones = $_POST['observaciones'] ?? '';

    // Validar que la requisición existe y está finalizada
    $sqlVerificar = "SELECT id_requisicion, estatus FROM requisiciones WHERE id_requisicion = :id_requisicion";
    $stmtVerificar = $conn->prepare($sqlVerificar);
    $stmtVerificar->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtVerificar->execute();
    $requisicion = $stmtVerificar->fetch(PDO::FETCH_ASSOC);

    if (!$requisicion) {
        echo json_encode(['success' => false, 'error' => 'La requisición no existe']);
        exit();
    }

    if ($requisicion['estatus'] !== 'Finalizada') {
        echo json_encode(['success' => false, 'error' => 'La requisición debe estar en estado "Finalizada" para poder revisarla']);
        exit();
    }

    // Actualizar la requisición con la revisión
    $sqlUpdate = "UPDATE requisiciones 
                  SET fecha_revision_maquinado = NOW(),
                      observacion_maquinado = :observaciones
                  WHERE id_requisicion = :id_requisicion";
    
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->execute([
        ':observaciones' => $observaciones,
        ':id_requisicion' => $id_requisicion
    ]);

    if ($stmtUpdate->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Revisión guardada correctamente',
            'id_requisicion' => $id_requisicion,
            'fecha_revision' => date('Y-m-d H:i:s'),
            'observaciones' => $observaciones
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No se pudo guardar la revisión. La requisición puede no existir o no haber cambios.'
        ]);
    }

} catch (PDOException $e) {
    error_log("Error en guardar_revision_maquinado: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor: ' . $e->getMessage()
    ]);
    http_response_code(500);
} finally {
    $conn = null;
}
?>