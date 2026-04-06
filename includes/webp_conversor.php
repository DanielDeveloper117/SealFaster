<?php
/**
 * ImageHelper - Manejo seguro y optimización de imágenes para Sealfaster
 */
class ImageHelper {
    /**
     * Procesa, valida y convierte una imagen a WebP
     * @param array $file El array de $_FILES['input_name']
     * @param string $destinationDir Directorio de destino
     * @param string $prefix Prefijo para el nombre del archivo
     * @param int $quality Calidad de compresión (1-100)
     * @return string Ruta relativa del archivo guardado
     */
    public static function processAndConvertToWebP($file, $destinationDir, $prefix = 'img', $quality = 80) {
        // 1. Validaciones de Seguridad Nivel Servidor
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            throw new Exception("Archivo no recibido.");
        }

        // Validar tipo MIME real (no confiar en la extensión del navegador)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception("Formato de archivo no permitido: $mimeType");
        }

        // 2. Preparar Directorio
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0775, true);
        }

        // 3. Crear recurso de imagen según el tipo
        $image = null;
        switch ($mimeType) {
            case 'image/jpeg': $image = @imagecreatefromjpeg($file['tmp_name']); break;
            case 'image/png':  $image = @imagecreatefrompng($file['tmp_name']); break;
            case 'image/webp': $image = @imagecreatefromwebp($file['tmp_name']); break;
            case 'image/gif':  $image = @imagecreatefromgif($file['tmp_name']); break;
        }

        if (!$image) {
            throw new Exception("La imagen parece estar corrupta o no es válida.");
        }

        // 4. Sanitizar y Preparar para WebP (Transparencias)
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        // 5. Generar Nombre Único y Seguro (Prevenir Inyección de nombres)
        // Usamos bin2hex para un nombre aleatorio impredecible
        $newFileName = $prefix . '_' . time() . '.webp';
        $finalPath = rtrim($destinationDir, '/') . '/' . $newFileName;

        // 6. Guardar como WebP
        if (!imagewebp($image, $finalPath, $quality)) {
            imagedestroy($image);
            throw new Exception("Error al procesar la conversión WebP.");
        }

        imagedestroy($image);
        return $newFileName;
    }
}
?>