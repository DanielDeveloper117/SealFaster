<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}
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

    // Validación corregida: isset y verificar que sean valores válidos (0 o 1)
    if (isset($_POST['id_requisicion'])) {
        
        try {
            $id_requisicion = $_POST['id_requisicion'];

            $sql = "UPDATE requisiciones SET 
                        estatus = 'Pendiente'
                    WHERE id_requisicion = :id_requisicion";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_requisicion', $id_requisicion);
            $stmt->execute();

            // Verificar si se actualizó algún registro
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cotización cancelada exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'error' => 'No se encontró la requisición o sesión incorrecta. No se realizaron cambios'
                ]);
            }

        } catch (Throwable $e) {
            echo json_encode([
                'success' => false, 
                'error' => "Error al intentar actualizar: " . $e->getMessage()
            ]);
        }

    } else {
        echo json_encode(['success' => false, 'error' => "Parámetros incompletos o inválidos."]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Error inesperado: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>