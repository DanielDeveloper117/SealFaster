<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');
    
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        exit;
    }
    
    // Verificar datos requeridos
    if (!isset($_POST['id_cotizacion']) || empty($_POST['id_cotizacion'])) {
        echo json_encode(['success' => false, 'error' => 'ID de cotización requerido']);
        exit;
    }
    
    if (!isset($_POST['comentario']) || empty(trim($_POST['comentario']))) {
        echo json_encode(['success' => false, 'error' => 'Comentario requerido']);
        exit;
    }
    
    $id_cotizacion = $_POST['id_cotizacion'];
    $comentario = trim($_POST['comentario']);
    
    // Procesar archivo adjunto
    $ruta_adjunto = null;
    
    if (isset($_FILES['nombre_archivo']) && $_FILES['nombre_archivo']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['nombre_archivo'];
        
        // Validar tipo de archivo (opcional - ajustar según necesidades)
        $extensiones_permitidas = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensiones_permitidas)) {
            echo json_encode([
                'success' => false, 
                'error' => 'Tipo de archivo no permitido. Extensiones permitidas: ' . implode(', ', $extensiones_permitidas)
            ]);
            exit;
        }
        
        // Validar tamaño (máximo 10MB)
        if ($archivo['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'error' => 'El archivo no puede ser mayor a 10MB']);
            exit;
        }
        
        // Crear directorio si no existe
        $directorio_base = ROOT_PATH . "files/adjuntos_cotizaciones/" . $id_cotizacion . "/";
        if (!is_dir($directorio_base)) {
            if (!mkdir($directorio_base, 0755, true)) {
                echo json_encode(['success' => false, 'error' => 'No se pudo crear el directorio para archivos']);
                exit;
            }
        }
        
        // Generar nombre único para el archivo
        $nombre_archivo = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $archivo['name']);
        $ruta_completa = $directorio_base . $nombre_archivo;
        
        // Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            echo json_encode(['success' => false, 'error' => 'Error al guardar el archivo']);
            exit;
        }
        
        // Guardar ruta relativa para la base de datos
        $ruta_adjunto = "files/adjuntos_cotizaciones/" . $id_cotizacion . "/" . $nombre_archivo;
    } else {
        echo json_encode(['success' => false, 'error' => 'Archivo adjunto requerido']);
        exit;
    }
    
    // Insertar en base de datos
    $sql = "INSERT INTO comentarios_adjuntos (id_cotizacion, comentario, ruta_adjunto, fecha_creacion) 
            VALUES (:id_cotizacion, :comentario, :ruta_adjunto, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_cotizacion', $id_cotizacion);
    $stmt->bindParam(':comentario', $comentario);
    $stmt->bindParam(':ruta_adjunto', $ruta_adjunto);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Comentario y archivo agregados correctamente',
            'id_registro' => $conn->lastInsertId()
        ]);
    } else {
        // Si falla la inserción, eliminar el archivo subido
        if ($ruta_adjunto && file_exists(ROOT_PATH . $ruta_adjunto)) {
            unlink(ROOT_PATH . $ruta_adjunto);
        }
        echo json_encode(['success' => false, 'error' => 'Error al guardar en base de datos']);
    }
    
} catch (PDOException $e) {
    // Eliminar archivo si hubo error en BD
    if (isset($ruta_adjunto) && $ruta_adjunto && file_exists(ROOT_PATH . $ruta_adjunto)) {
        unlink(ROOT_PATH . $ruta_adjunto);
    }
    
    echo json_encode([
        'success' => false, 
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>