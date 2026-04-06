<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'includes/webp_conversor.php'); // Importamos tu Helper

try {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    $id_cotizacion = $_POST['id_cotizacion'] ?? null;
    $comentario = trim($_POST['comentario'] ?? '');
    
    if (!$id_cotizacion) throw new Exception('ID de cotización requerido');
    if (empty($comentario)) throw new Exception('Comentario requerido');
    
    $ruta_adjunto = null;
    
    if (isset($_FILES['nombre_archivo']) && $_FILES['nombre_archivo']['error'] === UPLOAD_ERR_OK) {
        $archivo = $_FILES['nombre_archivo'];
        
        // 1. Ciberseguridad: Validar MIME Type Real
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $archivo['tmp_name']);
        finfo_close($finfo);

        $allowed_mimes = [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
            'application/pdf',
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        if (!in_array($mime_type, $allowed_mimes)) {
            throw new Exception("Tipo de archivo no permitido (MIME: $mime_type)");
        }

        // 2. Definir Directorio
        $directorio_base = ROOT_PATH . "files/adjuntos_cotizaciones/" . $id_cotizacion . "/";

        // 3. Lógica Híbrida: ¿Es imagen o documento?
        $es_imagen = strpos($mime_type, 'image/') === 0;

        if ($es_imagen) {
            // OPTIMIZACIÓN: Usar ImageHelper para convertir a WebP
            $nombre_final = ImageHelper::processAndConvertToWebP(
                $archivo, 
                $directorio_base, 
                'adjunto_img_' . bin2hex(random_bytes(4))
            );
        } else {
            // SEGURIDAD DOCUMENTOS: Sanitización estricta
            $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $nombre_limpio = preg_replace('/[^a-zA-Z0-9]/', '_', pathinfo($archivo['name'], PATHINFO_FILENAME));
            $nombre_final = time() . '_' . bin2hex(random_bytes(4)) . '_' . $nombre_limpio . '.' . $extension;
            
            if (!is_dir($directorio_base)) mkdir($directorio_base, 0755, true);
            
            if (!move_uploaded_file($archivo['tmp_name'], $directorio_base . $nombre_final)) {
                throw new Exception('Error al guardar el documento');
            }
        }
        
        $ruta_adjunto = "files/adjuntos_cotizaciones/" . $id_cotizacion . "/" . $nombre_final;
    }

    // 4. Inserción en BD
    $sql = "INSERT INTO comentarios_adjuntos (id_cotizacion, comentario, ruta_adjunto, fecha_creacion) 
            VALUES (:id_cotizacion, :comentario, :ruta_adjunto, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':id_cotizacion' => $id_cotizacion,
        ':comentario' => $comentario,
        ':ruta_adjunto' => $ruta_adjunto
    ]);

    echo json_encode(['success' => true, 'message' => 'Comentario y adjunto procesados con éxito']);

} catch (Exception $e) {
    // Limpieza en caso de error
    if (isset($ruta_adjunto) && file_exists(ROOT_PATH . $ruta_adjunto)) {
        unlink(ROOT_PATH . $ruta_adjunto);
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}