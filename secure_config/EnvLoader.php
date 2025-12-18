<?php
/**
 * EnvLoader - Cargador simple de variables de entorno
 * Métodos estáticos para fácil acceso
 */
class EnvLoader {
    private static $env = null;
    private static $envPath = null;
    private static $loaded = false;

    /**
     * Cargar variables de entorno (se ejecuta automáticamente la primera vez)
     */
    private static function load() {
        if (self::$loaded) {
            return self::$env;
        }

        // Buscar archivo .env
        self::$envPath = self::findEnvFile();
        
        if (!self::$envPath) {
            throw new Exception('Archivo .env no encontrado');
        }

        // Leer archivo
        self::$env = [];
        $lines = file(self::$envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if ($lines === false) {
            throw new Exception("Error leyendo archivo .env: " . self::$envPath);
        }

        foreach ($lines as $line) {
            self::parseLine($line);
        }

        self::$loaded = true;
        return self::$env;
    }

    /**
     * Buscar archivo .env en ubicaciones comunes
     */
    private static function findEnvFile() {
        // Obtener rutas base para construir paths
        $currentDir = __DIR__; // Donde está EnvLoader.php
        $scriptDir = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : getcwd();
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        
        // Lista exhaustiva de posibles ubicaciones del .env
        $possiblePaths = [
            // 1. Rutas relativas desde EnvLoader.php (desarrollo local)
            $currentDir . '/.env',
            $currentDir . '/../.env',
            $currentDir . '/../../.env',
            $currentDir . '/../../../.env',
            $currentDir . '/../../../../.env',
            
            // 2. Rutas específicas de secure_config
            $currentDir . '/secure_config/.env',
            dirname($currentDir) . '/secure_config/.env',
            dirname($currentDir, 2) . '/secure_config/.env',
            dirname($currentDir, 3) . '/secure_config/.env',
            dirname($currentDir, 4) . '/secure_config/.env',
            
            // 3. Desde el directorio del script que se está ejecutando
            $scriptDir . '/.env',
            $scriptDir . '/../.env',
            $scriptDir . '/../../.env',
            $scriptDir . '/../../../.env',
            $scriptDir . '/../secure_config/.env',
            $scriptDir . '/../../secure_config/.env',
            $scriptDir . '/../../../secure_config/.env',
            
            // 4. Desde document root (hosting web)
            $documentRoot . '/.env',
            $documentRoot . '/../.env',
            $documentRoot . '/../secure_config/.env',
            $documentRoot . '/../../.env',
            $documentRoot . '/../../secure_config/.env',
            $documentRoot . '/../../../secure_config/.env',
            
            // 5. Rutas absolutas comunes para hosting
            '/home/' . (get_current_user() ?: 'user') . '/.env',
            '/home/' . (get_current_user() ?: 'user') . '/secure_config/.env',
            '/home/' . (get_current_user() ?: 'user') . '/public_html/../.env',
            '/home/' . (get_current_user() ?: 'user') . '/public_html/../secure_config/.env',
            
            // 6. Directorio de trabajo actual
            getcwd() . '/.env',
            getcwd() . '/../.env',
            getcwd() . '/../secure_config/.env',
            getcwd() . '/../../secure_config/.env',
            
            // 7. Rutas especiales para XAMPP/WAMP
            'C:/xampp/secure_config/.env',
            'C:/wamp/secure_config/.env',
            'C:/xampp/htdocs/secure_config/.env',
            'C:/wamp/www/secure_config/.env',
            
            // 8. Para Mac/Linux desarrollo
            '/Users/' . (get_current_user() ?: 'user') . '/Sites/secure_config/.env',
            '/var/www/secure_config/.env',
            '/opt/lampp/secure_config/.env',
        ];
        
        // 9. Extraer usuario de document_root si es posible
        if ($documentRoot && strpos($documentRoot, '/home/') === 0) {
            $parts = explode('/', $documentRoot);
            if (count($parts) >= 3) {
                $username = $parts[2];
                $possiblePaths[] = "/home/{$username}/.env";
                $possiblePaths[] = "/home/{$username}/secure_config/.env";
                $possiblePaths[] = "/home/{$username}/public_html/../secure_config/.env";
            }
        }
        
        // 10. Intentar adivinar usuario basado en dominio (para tu caso sellosyretenes.com)
        $domainUsers = ['sellosyret', 'sellosyretenes', 'sellos_com', 'sellos', 'sellosye'];
        foreach ($domainUsers as $domainUser) {
            $possiblePaths[] = "/home/{$domainUser}/secure_config/.env";
            $possiblePaths[] = "/home/{$domainUser}/.env";
        }
        
        // Eliminar rutas duplicadas y vacías
        $possiblePaths = array_unique(array_filter($possiblePaths));
        
        // Intentar encontrar el archivo
        foreach ($possiblePaths as $path) {
            // Limpiar la ruta (resolver ../, ./ etc)
            $cleanPath = realpath($path);
            if ($cleanPath && file_exists($cleanPath) && is_readable($cleanPath)) {
                // Verificar que sea un archivo .env (opcional)
                if (pathinfo($cleanPath, PATHINFO_EXTENSION) === 'env' || 
                    basename($cleanPath) === '.env') {
                    error_log("EnvLoader: .env encontrado en: " . $cleanPath);
                    return $cleanPath;
                }
            }
            
            // También intentar con la ruta original (por si realpath falla)
            if (file_exists($path) && is_readable($path)) {
                error_log("EnvLoader: .env encontrado en (sin realpath): " . $path);
                return realpath($path) ?: $path;
            }
        }
        
        // 11. Búsqueda recursiva como último recurso
        $searchDirs = [
            $currentDir,
            $scriptDir,
            $documentRoot ?: getcwd(),
            dirname($currentDir),
            dirname($scriptDir),
        ];
        
        foreach ($searchDirs as $startDir) {
            $found = self::searchEnvRecursive($startDir);
            if ($found) {
                error_log("EnvLoader: .env encontrado recursivamente en: " . $found);
                return $found;
            }
        }
        
        error_log("EnvLoader: No se encontró archivo .env en ninguna ubicación");
        return false;
    }

    /**
     * Búsqueda recursiva de archivo .env
     */
    private static function searchEnvRecursive($startDir, $maxDepth = 5) {
        if (!$startDir || !is_dir($startDir) || $maxDepth <= 0) {
            return false;
        }
        
        // Buscar en el directorio actual
        $directFiles = ['.env', '.env.example', 'env.example'];
        foreach ($directFiles as $filename) {
            $filePath = $startDir . '/' . $filename;
            if (file_exists($filePath) && is_readable($filePath)) {
                return realpath($filePath) ?: $filePath;
            }
        }
        
        // Buscar en subdirectorios secure_config, config, etc.
        $specialDirs = ['secure_config', 'config', 'configuration', 'settings', 'env'];
        foreach ($specialDirs as $dir) {
            $dirPath = $startDir . '/' . $dir;
            if (is_dir($dirPath)) {
                $envPath = $dirPath . '/.env';
                if (file_exists($envPath) && is_readable($envPath)) {
                    return realpath($envPath) ?: $envPath;
                }
            }
        }
        
        // Buscar recursivamente en directorio padre
        $parentDir = dirname($startDir);
        if ($parentDir && $parentDir !== $startDir) {
            return self::searchEnvRecursive($parentDir, $maxDepth - 1);
        }
        
        return false;
    }


    /**
     * Parsear una línea del archivo .env
     */
    private static function parseLine($line) {
        $line = trim($line);
        
        // Saltar comentarios y líneas vacías
        if (empty($line) || $line[0] === '#' || $line[0] === ';') {
            return;
        }

        // Separar clave=valor
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Eliminar comillas
            $value = self::stripQuotes($value);

            // Convertir tipos básicos
            $value = self::convertType($value);

            self::$env[$key] = $value;
            
            // También poner en $_ENV para compatibilidad
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    /**
     * Eliminar comillas simples o dobles
     */
    private static function stripQuotes($value) {
        if (($value[0] === '"' && substr($value, -1) === '"') ||
            ($value[0] === "'" && substr($value, -1) === "'")) {
            return substr($value, 1, -1);
        }
        return $value;
    }

    /**
     * Convertir string a tipo PHP apropiado
     */
    private static function convertType($value) {
        $lower = strtolower($value);
        
        // Booleanos
        if ($lower === 'true') return true;
        if ($lower === 'false') return false;
        
        // Null
        if ($lower === 'null' || $value === '') return null;
        
        // Números
        if (is_numeric($value)) {
            return (strpos($value, '.') !== false) ? (float)$value : (int)$value;
        }
        
        return $value; // String
    }

    /**
     * Obtener valor de variable de entorno
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$env[$key] ?? $default;
    }

    /**
     * Obtener todas las variables
     */
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$env;
    }

    /**
     * Verificar si una variable existe
     */
    public static function has($key) {
        if (!self::$loaded) {
            self::load();
        }
        
        return isset(self::$env[$key]);
    }

    /**
     * Obtener ruta del archivo .env
     */
    public static function getPath() {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$envPath;
    }

    /**
     * Establecer variable temporalmente (útil para testing)
     */
    public static function set($key, $value) {
        if (!self::$loaded) {
            self::load();
        }
        
        self::$env[$key] = $value;
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        
        return true;
    }

    /**
     * Forzar recarga del archivo .env
     */
    public static function reload() {
        self::$loaded = false;
        self::$env = null;
        return self::load();
    }
}

/**
 * Función helper global para acceso rápido
 */
function env($key, $default = null) {
    return EnvLoader::get($key, $default);
}
?>