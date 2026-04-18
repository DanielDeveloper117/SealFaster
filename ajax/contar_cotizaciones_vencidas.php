<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
        exit();
    }

    $id_usuario = $_SESSION['id'] ?? null;

    if (!$id_usuario) {
        echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
        exit();
    }

    // Configurar timezone
    date_default_timezone_set('America/Mexico_City');
    $fecha_actual = date('Y-m-d H:i:s');

    // Contar cotizaciones vencidas no archivadas del usuario
    $sql = "SELECT COUNT(*) as total_vencidas 
            FROM cotizacion_materiales 
            WHERE id_usuario = :id_usuario 
            AND archivada = 0 
            AND fecha_vencimiento IS NOT NULL 
            AND fecha_vencimiento != '0000-00-00 00:00:00'
            AND fecha_vencimiento < :fecha_actual";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':fecha_actual', $fecha_actual);
    $stmt->execute();

    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_vencidas = $resultado['total_vencidas'] ?? 0;

    echo json_encode([
        'success' => true,
        'total_vencidas' => (int)$total_vencidas
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error inesperado: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>
