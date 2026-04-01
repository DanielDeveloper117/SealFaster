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
        
        // 1. Validar extensión
        $extensiones_permitidas = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $extensiones_permitidas)) {
            echo json_encode([
                'success' => false, 
                'error' => 'Tipo de archivo no permitido. Extensiones permitidas: ' . implode(', ', $extensiones_permitidas)
            ]);
            exit;
        }

        // 2. Validar MIME type (Ciberseguridad)
        $allowed_mime_types = [
            'application/pdf',
            'application/msword', // .doc
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
            'image/jpeg',
            'image/png',
            'application/vnd.ms-excel', // .xls
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' // .xlsx
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_mime_types)) {
            echo json_encode([
                'success' => false, 
                'error' => 'El contenido del archivo no coincide con su extensión permitida (MIME type no válido: ' . $mime_type . ')'
            ]);
            exit;
        }
        
        // 3. Validar tamaño (máximo 10MB)
        if ($archivo['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'error' => 'El archivo no puede ser mayor a 10MB']);
            exit;
        }
        
        // 4. Sanitizar y Renombrar (Ciberseguridad)
        // Eliminamos caracteres extraños y prevenimos ataques de doble extensión
        $nombre_limpio = preg_replace('/[^a-zA-Z0-9]/', '_', pathinfo($archivo['name'], PATHINFO_FILENAME));
        // Generamos un nombre completamente único e impredecible
        $nombre_unico = time() . '_' . bin2hex(random_bytes(4)) . '_' . $nombre_limpio . '.' . $extension;
        
        // 5. Crear directorio si no existe
        $directorio_base = ROOT_PATH . "files/adjuntos_cotizaciones/" . $id_cotizacion . "/";
        if (!is_dir($directorio_base)) {
            if (!mkdir($directorio_base, 0755, true)) {
                echo json_encode(['success' => false, 'error' => 'No se pudo crear el directorio para archivos']);
                exit;
            }
        }
        
        $ruta_completa = $directorio_base . $nombre_unico;
        
        // 6. Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            echo json_encode(['success' => false, 'error' => 'Error al guardar el archivo']);
            exit;
        }
        
        // Guardar ruta relativa para la base de datos
        $ruta_adjunto = "files/adjuntos_cotizaciones/" . $id_cotizacion . "/" . $nombre_unico;
    }
    // else {
    //     echo json_encode(['success' => false, 'error' => 'Archivo adjunto requerido']);
    //     exit;
    // }
    
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
            'message' => 'Comentario agregado correctamente',
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