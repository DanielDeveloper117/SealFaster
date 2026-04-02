<?php
require_once(__DIR__ . '/../../config/rutes.php');

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

function controlCache($webPath) {
    // 1. Normalizar la entrada: quitar slashes de más y ajustar al OS
    $cleanPath = ltrim($webPath, '/\\'); 
    $osPath = str_replace(['/', '\\'], DS, $cleanPath);

    // 2. Construir la ruta física usando la constante ROOT_PATH
    // rtrim asegura que no haya doble separador entre ROOT_PATH y osPath
    $fullPath = rtrim(ROOT_PATH, DS) . DS . $osPath;

    // 3. Debugging (Opcional: Descomenta la línea de abajo si sigue fallando para ver la ruta en el HTML)
    // return $webPath . '';

    if (@file_exists($fullPath)) {
        return $webPath . '?v=' . filemtime($fullPath);
    }

    // 4. Fallback: Si no lo encuentra físicamente, devuelve la ruta original
    return $webPath;
}