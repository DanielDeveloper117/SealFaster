<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        exit();
    }

    if (!isset($_POST['registros']) || empty($_POST['registros'])) {
        echo json_encode(['success' => false, 'error' => 'No se recibieron registros']);
        exit();
    }

    $data = json_decode($_POST['registros'], true);

    if (!is_array($data) || count($data) === 0) {
        echo json_encode(['success' => false, 'error' => 'Formato de datos inválido']);
        exit();
    }

    $conn->beginTransaction();

    $sqlUpdate = "UPDATE control_almacen 
                  SET pz_teoricas = :pz_teoricas,
                      mm_entrega = :mm_entrega
                  WHERE id_control = :id_control";
    $stmtUpdate = $conn->prepare($sqlUpdate);

    $registrosActualizados = 0;
    $errores = [];

    foreach ($data as $fila) {
        try {
            $stmtUpdate->execute([
                ':pz_teoricas' => $fila['pz_teoricas'] ?? 0,
                ':mm_entrega' => $fila['mm_entrega'] ?? 0,
                ':id_control' => $fila['id_control']
            ]);

            if ($stmtUpdate->rowCount() > 0) {
                $registrosActualizados++;
            }
        } catch (PDOException $e) {
            $errores[] = "Error al actualizar id_control {$fila['id_control']}: " . $e->getMessage();
        }
    }

    $conn->commit();

    $mensaje = "Progreso guardado correctamente. Registros actualizados: $registrosActualizados";
    if (count($errores) > 0) {
        $mensaje .= ". Errores: " . implode(', ', $errores);
    }

    echo json_encode([
        'success' => true,
        'message' => $mensaje,
        'registros_actualizados' => $registrosActualizados,
        'errores' => $errores
    ]);

} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Error al guardar progreso: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>
