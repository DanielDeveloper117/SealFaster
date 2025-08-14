<?php 
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');

session_start();

// Manejo de errores
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
        exit();
    }

    if (isset($_POST['id_fusion']) && $_POST['id_fusion'] !== '') {
        $id_fusion = $_POST['id_fusion'];
        $id_usuario = $_SESSION['id'] ?? null;

        if (!$id_usuario) {
            echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
            exit;
        }

        // Contar id_cotizacion únicas antes de hacer el update
        $sqlCount = "SELECT COUNT(DISTINCT id_cotizacion) AS total_unicos
                     FROM cotizacion_materiales
                     WHERE id_fusion = :id_fusion
                     AND id_usuario = :id_usuario";
        $stmtCount = $conn->prepare($sqlCount);
        $stmtCount->bindParam(':id_fusion', $id_fusion, PDO::PARAM_INT);
        $stmtCount->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmtCount->execute();
        $totalUnicos = (int)$stmtCount->fetchColumn();

        // Actualizar las filas para desvincular
        $sqlUpdate = "UPDATE cotizacion_materiales 
                      SET id_fusion = NULL
                      WHERE id_fusion = :id_fusion
                      AND id_usuario = :id_usuario";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bindParam(':id_fusion', $id_fusion, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmtUpdate->execute();

        if ($stmtUpdate->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Se desvincularon {$totalUnicos} cotizacion(es) únicas de la fusión {$id_fusion}."
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => "No se encontraron cotizaciones para la fusión {$id_fusion} o ya estaban desvinculadas."
            ]);
        }

    } else {
        echo json_encode([
            'success' => false, 
            'error' => "Parámetros incompletos o inválidos. 'id_fusion' es requerido."
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Error inesperado: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>
