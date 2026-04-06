<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');
    
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        exit;
    }
    
    // Verificar que se recibió el ID del registro
    if (!isset($_POST['id']) || empty($_POST['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID de registro no proporcionado']);
        exit;
    }
    
    $id_registro = $_POST['id'];
    
    // Primero obtener la ruta del archivo para eliminarlo
    $sql_select = "SELECT ruta_adjunto FROM comentarios_adjuntos WHERE id = :id";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bindParam(':id', $id_registro);
    $stmt_select->execute();
    
    $registro = $stmt_select->fetch(PDO::FETCH_ASSOC);
    
    if (!$registro) {
        echo json_encode(['success' => false, 'error' => 'Registro no encontrado']);
        exit;
    }
    
    // Eliminar el registro de la base de datos
    $sql_delete = "DELETE FROM comentarios_adjuntos WHERE id = :id";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bindParam(':id', $id_registro);
    
    if ($stmt_delete->execute()) {
        // Si se eliminó de la BD, eliminar el archivo físico si existe
        if ($registro['ruta_adjunto'] && file_exists(ROOT_PATH . $registro['ruta_adjunto'])) {
            unlink(ROOT_PATH . $registro['ruta_adjunto']);
            
            // Opcional: Eliminar directorio si está vacío
            $directorio = dirname(ROOT_PATH . $registro['ruta_adjunto']);
            if (is_dir($directorio) && count(scandir($directorio)) == 2) { // Solo . y ..
                rmdir($directorio);
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Registro eliminado correctamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al eliminar el registro']);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>