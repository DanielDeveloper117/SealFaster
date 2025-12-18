<?php
/**
 * SecureLoader - Cargador simple para archivos fuera de public_html
 * Manejo de errores minimalista
 */

class SecureLoader {
    private static $loadedFiles = [];
    
    /**
     * Cargar archivo desde secure_config
     */
    public static function require($filename) {
        $path = self::findSecureFile($filename);
        if ($path) {
            require $path;
            return true;
        }
        self::showError($filename, "No se pudo cargar archivo: {$filename}");
        return false;
    }
    
    /**
     * Cargar archivo desde secure_config una sola vez
     */
    public static function requireOnce($filename) {
        if (isset(self::$loadedFiles[$filename])) {
            return true;
        }
        
        $path = self::findSecureFile($filename);
        if ($path) {
            require_once $path;
            self::$loadedFiles[$filename] = $path;
            return true;
        }
        self::showError($filename, "No se pudo cargar archivo: {$filename}");
        return false;
    }
    
    /**
     * Buscar archivo en secure_config
     */
    private static function findSecureFile($filename) {
        $callerDir = dirname(debug_backtrace()[0]['file']);
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        
        $possiblePaths = [
            // Desde auth/login-script.php
            $callerDir . '/../../../secure_config/' . $filename,
            $callerDir . '/../../../../secure_config/' . $filename,
            
            // Rutas absolutas comunes
            'C:/xampp/secure_config/' . $filename,
            'C:/wamp/secure_config/' . $filename,
            '/home/sellosyret/secure_config/' . $filename,
            
            // Desde document root
            $documentRoot . '/../secure_config/' . $filename,
            
            // Si ya se definió la ruta
            defined('SECURE_CONFIG_PATH') ? SECURE_CONFIG_PATH . '/' . $filename : null,
        ];
        
        $possiblePaths = array_filter($possiblePaths);
        
        foreach ($possiblePaths as $path) {
            $cleanPath = str_replace('//', '/', $path);
            if (file_exists($cleanPath) && is_readable($cleanPath)) {
                if (!defined('SECURE_CONFIG_PATH')) {
                    define('SECURE_CONFIG_PATH', dirname($cleanPath));
                }
                return realpath($cleanPath) ?: $cleanPath;
            }
        }
        
        return false;
    }
    
    /**
     * Mostrar error minimalista
     */
    private static function showError($filename, $errorMessage) {
        // Determinar entorno
        $isDevelopment = ($_SERVER['SERVER_NAME'] ?? '') === 'localhost' || 
                        ($_SERVER['HTTP_HOST'] ?? '') === 'localhost' ||
                        ($_SERVER['SERVER_ADDR'] ?? '') === '127.0.0.1';
        
        // Log del error
        error_log("SECURE_LOADER: {$errorMessage}");
        
        if ($isDevelopment) {
            // En desarrollo: texto simple con información básica
            $callerDir = dirname(debug_backtrace()[1]['file'] ?? '');
            
            $output = "=== ERROR DE CONFIGURACIÓN ===\n\n";
            $output .= "Archivo requerido: {$filename}\n";
            $output .= "Error: {$errorMessage}\n\n";
            $output .= "Desde: {$callerDir}\n";
            $output .= "Ruta esperada: {$callerDir}/../../../secure_config/{$filename}\n\n";
            $output .= "Solución:\n";
            $output .= "1. Verifica que el archivo existe en secure_config/\n";
            $output .= "2. Comprueba la ruta: C:/xampp/secure_config/{$filename}\n";
            
            die($output);
            
        } else {
            // En producción: mensaje simple
            header('HTTP/1.1 503 Service Unavailable');
            header('Content-Type: text/plain; charset=utf-8');
            
            $output = "Sistema en mantenimiento\n";
            $output .= "========================\n\n";
            $output .= "Estamos realizando tareas de mantenimiento.\n";
            $output .= "El sistema estará disponible nuevamente en breve.\n\n";
            $output .= "Sellos y Retenes de San Luis S.A. de C.V.\n";
            
            die($output);
        }
    }
    
    /**
     * Verificar si archivo existe
     */
    public static function exists($filename) {
        return self::findSecureFile($filename) !== false;
    }
    
    /**
     * Obtener ruta
     */
    public static function path($filename) {
        return self::findSecureFile($filename);
    }
}

/**
 * Funciones helper
 */
function secure_require($filename) {
    return SecureLoader::require($filename);
}

function secure_require_once($filename) {
    return SecureLoader::requireOnce($filename);
}

function secure_file_exists($filename) {
    return SecureLoader::exists($filename);
}

function secure_file_path($filename) {
    return SecureLoader::path($filename);
}
?>