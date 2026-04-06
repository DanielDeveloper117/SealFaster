<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');

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
    if (isset($_POST['id_fusion'], $_POST['archivada']) && 
        $_POST['id_fusion'] !== '' && 
        in_array($_POST['archivada'], ['0', '1'], true)) {
        
        try {
            $id_fusion = $_POST['id_fusion'];
            $archivada = (int)$_POST['archivada']; // Convertir a entero
            $id_usuario = $_SESSION['id'] ?? null;

            if (!$id_usuario) {
                echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
                exit;
            }

            $sql = "UPDATE cotizacion_materiales SET 
                        archivada = :archivada
                    WHERE id_fusion = :id_fusion AND id_usuario = :id_usuario";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':archivada', $archivada, PDO::PARAM_INT);
            $stmt->bindParam(':id_fusion', $id_fusion);
            $stmt->bindParam(':id_usuario', $id_usuario);
            $stmt->execute();

            // Verificar si se actualizó algún registro
            if ($stmt->rowCount() > 0) {
                $mensaje = $archivada === 1 ? 'Agrupacion archivada exitosamente' : 'Agrupacion desarchivada exitosamente';
                echo json_encode([
                    'success' => true, 
                    'message' => $mensaje
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'error' => 'No se encontró la agrupación o no se realizaron cambios o sesión incorrecta'
                ]);
            }

        } catch (Throwable $e) {
            echo json_encode([
                'success' => false, 
                'error' => "Error al intentar actualizar: " . $e->getMessage()
            ]);
        }

    } else {
        echo json_encode(['success' => false, 'error' => "Parámetros incompletos o inválidos. 'archivada' debe ser 0 o 1"]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'Error inesperado: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>