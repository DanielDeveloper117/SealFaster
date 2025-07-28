<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache'); // Para HTTP/1.0
header('Expires: 0'); // Fecha de expiracion en el pasado

// Evitar que la pagina se almacene en cache por proxies intermedios
header('Cache-Control: private, no-store, no-cache, must-revalidate');

function controlCache($filePath) {
    if (file_exists($filePath)) {
        return $filePath . '?v=' . filemtime($filePath);
    } else {
        return $filePath; // Si el archivo no existe, lo deja normal
    }
}
?>