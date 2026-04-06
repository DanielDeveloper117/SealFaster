<?php 
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');

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

    if (isset($_POST['ids_cotizaciones']) && is_array($_POST['ids_cotizaciones']) && count($_POST['ids_cotizaciones']) > 0) {
        $idsCotizaciones = $_POST['ids_cotizaciones'];
        $id_usuario = $_SESSION['id'] ?? null;

        if (!$id_usuario) {
            echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
            exit;
        }

        // Generar un id_fusion aleatorio (puedes cambiarlo si deseas otra lógica)
        $id_fusion = random_int(100000, 999999);

        // Convertir arreglo a string para IN() con parámetros preparados
        $placeholders = implode(',', array_fill(0, count($idsCotizaciones), '?'));

        // Preparar SQL para actualizar
        $sqlUpdate = "UPDATE cotizacion_materiales 
                      SET id_fusion = ?
                      WHERE id_usuario = ?
                      AND id_cotizacion IN ($placeholders)";

        $stmt = $conn->prepare($sqlUpdate);

        // Bind dinámico de parámetros
        $params = array_merge([$id_fusion, $id_usuario], $idsCotizaciones);
        $stmt->execute($params);

        // Contar cuántas id_cotizacion únicas se afectaron
        $totalUnicos = count(array_unique($idsCotizaciones));

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => "Se fusionaron {$totalUnicos} cotizacion(es) en la agrupacion con id: {$id_fusion}.",
                'id_fusion' => $id_fusion
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => "No se pudieron fusionar las cotizaciones, verifique los datos."
            ]);
        }

    } else {
        echo json_encode([
            'success' => false, 
            'error' => "Parámetros incompletos o inválidos. 'ids_cotizaciones' es requerido."
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
