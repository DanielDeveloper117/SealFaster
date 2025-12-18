
<?php
require_once(__DIR__ . '/../config/rutes.php');
// Cargar SecureLoader
require_once(ROOT_PATH . 'config/secure_loader.php');

// Importar sistemas de seguridad (fallará con mensaje simple si no existen)
secure_require_once('EnvLoader.php');

// Obtener variables de entorno
$servername = EnvLoader::get('DB_HOST');
$username = EnvLoader::get('DB_USER');
$password = EnvLoader::get('DB_PASS');
$dbname = EnvLoader::get('DB_NAME');

// Configuración SMTP
$HOST = EnvLoader::get('SMTP_HOST');
$USER = EnvLoader::get('SMTP_USER');
$PASS = EnvLoader::get('SMTP_PASS');
$SECURE = EnvLoader::get('SMTP_SECURE', 'ssl');
$PORT = EnvLoader::get('SMTP_PORT', 465);
$FROM = EnvLoader::get('SMTP_FROM');
$DOMAIN_NAME = EnvLoader::get('SMTP_COMPANY_NAME');

// sistema
$DEV_EMAIL = EnvLoader::get('SMTP_DEV_EMAIL');
$DEV_MODE = EnvLoader::get('APP_DEV_MODE', false);
$PASS_UNCRIPT = EnvLoader::get('APP_ENCRYPTION_KEY');
$BASE_URL = EnvLoader::get('APP_BASE_URL');

// Conexión a base de datos
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Configuración adicional opcional
    $conn->setAttribute(PDO::ATTR_PERSISTENT, false);
    $conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
    // Configurar timezone si es necesario
    // $conn->exec("SET time_zone = '-06:00'");
    
} catch(PDOException $e) {
    // Manejo de errores según configuración
    if($DEV_MODE === true){
        error_log("Error de conexión DB [" . date('Y-m-d H:i:s') . "]: " . $e->getMessage());
        
        if (EnvLoader::get('APP_DEBUG', false)) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        } else {
            die("Error del sistema. Contacte al administrador.");
        }
    }
}
?>