<?php
session_start();
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
// Cargar SecureLoader
require_once(ROOT_PATH . 'config/secure_loader.php');

// Importar sistemas de seguridad (fallará con mensaje simple si no existen)
secure_require_once('rate_limiter.php');
secure_require_once('ip_blocker.php');

// ============ CONSTANTES DE SEGURIDAD ============
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_TIME', 60); // 900 = 15 minutos
define('MIN_PASSWORD_LENGTH', 3);
define('MAX_PASSWORD_LENGTH', 50);
define('MIN_USERNAME_LENGTH', 3);
define('MAX_USERNAME_LENGTH', 100);

// ============ FUNCIONES DE SEGURIDAD ============

/**
 * Registrar intento de login en logs
 */
function logLoginAttempt($username, $success, $reason = '') {
    $logDir = __DIR__ . '/../../../secure_config';
    $logFile = $logDir . '/login_security.log';
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    if (!file_exists($logFile)) {
        file_put_contents($logFile, "=== LOG DE SEGURIDAD LOGIN INICIADO ===\n");
    }
    
    $entry = date('Y-m-d H:i:s') . ' | ' .
        'IP=' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | ' .
        'UA=' . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . ' | ' .
        'USER=' . (empty($username) ? 'unknown' : substr($username, 0, 50)) . ' | ' .
        'STATUS=' . ($success ? 'SUCCESS' : 'FAILED') . ' | ' .
        'REASON=' . $reason . PHP_EOL;
    
    file_put_contents($logFile, $entry, FILE_APPEND);
}

/**
 * Registrar intento sospechoso
 */
function logSuspiciousAttempt($reason, $postData) {
    $logDir = __DIR__ . '/../../../secure_config';
    $logFile = $logDir . '/login_suspicious.log';
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    if (!file_exists($logFile)) {
        file_put_contents($logFile, "=== LOG DE INTENTOS SOSPECHOSOS LOGIN ===\n");
    }
    
    $entry = date('Y-m-d H:i:s') . ' | ' .
        'REASON=' . $reason . ' | ' .
        'IP=' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | ' .
        'UA=' . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . ' | ' .
        'POST=' . json_encode(array_map(function($v) {
            return is_string($v) ? substr($v, 0, 100) : $v;
        }, $postData)) . PHP_EOL;
    
    file_put_contents($logFile, $entry, FILE_APPEND);
}

/**
 * Validar y sanitizar input
 */
function validateLoginInput($input, $type = 'username') {
    if (!is_string($input)) {
        return false;
    }
    
    $input = trim($input);
    $input = stripslashes($input);
    
    switch ($type) {
        case 'username':
            // Validar formato de email
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            
            // Prevenir header injection
            if (preg_match('/[\r\n]/', $input)) {
                return false;
            }
            
            // Longitud máxima
            if (strlen($input) > MAX_USERNAME_LENGTH) {
                return false;
            }
            
            break;
            
        case 'password':
            // Longitud mínima y máxima
            if (strlen($input) < MIN_PASSWORD_LENGTH || 
                strlen($input) > MAX_PASSWORD_LENGTH) {
                return false;
            }
            
            // Prevenir caracteres peligrosos
            if (preg_match('/[\x00-\x1F\x7F]/', $input)) {
                return false;
            }
            
            break;
    }
    
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

/**
 * Verificar rate limiting de sesión
 */
function checkSessionRateLimit() {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['login_blocked_until'] = 0;
        $_SESSION['last_failed_time'] = 0;
    }
    
    $current_time = time();
    
    // Si está bloqueado, verificar tiempo
    if ($_SESSION['login_blocked_until'] > $current_time) {
        return [
            'blocked' => true,
            'remaining' => $_SESSION['login_blocked_until'] - $current_time
        ];
    }
    
    // Resetear intentos si pasó mucho tiempo desde el último fallo
    if ($_SESSION['last_failed_time'] > 0 && 
        ($current_time - $_SESSION['last_failed_time']) > LOCKOUT_TIME) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_failed_time'] = 0;
    }
    
    return ['blocked' => false];
}

/**
 * Incrementar contador de intentos fallidos
 */
function incrementFailedAttempts() {
    $_SESSION['login_attempts']++;
    $_SESSION['last_failed_time'] = time();
    
    if ($_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION['login_blocked_until'] = time() + LOCKOUT_TIME;
        
        // Bloquear IP si hay muchos intentos
        if ($_SESSION['login_attempts'] >= 5) {
            block_ip($_SERVER['REMOTE_ADDR']);
        }
        
        return true;
    }
    
    return false;
}

/**
 * Detectar actividad de bot
 */
function detectBotActivity($postData) {
    // 1. Tiempo de respuesta demasiado rápido (< 0.5 segundos)
    if (isset($postData['submit_timestamp'])) {
        $responseTime = microtime(true) - floatval($postData['submit_timestamp']);
        if ($responseTime < 0.5) {
            return true;
        }
    }
    
    // 2. User-Agent sospechoso
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $suspiciousPatterns = [
        '/bot/i', '/crawl/i', '/spider/i', '/scanner/i', 
        '/curl/i', '/wget/i', '/python/i', '/java/i',
        '/phantom/i', '/headless/i'
    ];
    
    foreach ($suspiciousPatterns as $pattern) {
        if (preg_match($pattern, $userAgent)) {
            return true;
        }
    }
    
    // 3. Sin user-agent
    if (empty($userAgent)) {
        return true;
    }

    // 4. Trampa honeypot
    if (!empty($postData['phone_number'])) {
        return true;
    }
    return false;
}

/**
 * Simular tiempo constante para evitar timing attacks
 */
function simulateConstantTime($minMicroseconds = 500000) {
    usleep($minMicroseconds + rand(0, 200000)); // 0.5-0.7 segundos
}

/**
 * Función para enviar respuesta JSON
 */
function sendJsonResponse($status, $title, $message, $redirect = null) {
    $response = [
        'status' => $status,
        'title' => $title,
        'message' => $message
    ];
    
    if ($redirect !== null) {
        $response['redirect'] = $redirect;
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// ============ EJECUCIÓN PRINCIPAL ============

// Headers de seguridad
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Content-Type: application/json");

try {
    // 1. Verificar IP bloqueada
    if (!block_ip_check()) {
        logSuspiciousAttempt("IP bloqueada intentando acceso", $_POST);
        sendJsonResponse('error', 'Acceso Denegado', 'Tu IP ha sido bloqueada por actividades sospechosas');
    }
    
    // 2. Verificar rate limiting global
    if (!rate_limit_allow()) {
        logSuspiciousAttempt("Rate limit excedido", $_POST);
        block_ip($_SERVER['REMOTE_ADDR']);
        sendJsonResponse('error', 'Demasiados Intentos', 'Has excedido el número máximo de intentos. IP bloqueada temporalmente');
    }
    
    // 3. Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logSuspiciousAttempt("Método no permitido: " . $_SERVER['REQUEST_METHOD'], $_POST);
        sendJsonResponse('error', 'Método No Permitido', 'Acceso denegado');
    }
    
    // 4. Verificar rate limiting de sesión
    $rateCheck = checkSessionRateLimit();
    if ($rateCheck['blocked']) {
        logLoginAttempt('', false, 'Session rate limited');
        sendJsonResponse('warning', 'Acceso Bloqueado', 'Demasiados intentos fallidos. Espera ' . ceil($rateCheck['remaining'] / 60) . ' minutos.');
    }
    
    // 5. Detectar actividad de bot
    if (detectBotActivity($_POST)) {
        logSuspiciousAttempt("Bot detectado", $_POST);
        logLoginAttempt('', false, 'Bot activity detected');
        
        // Responder con éxito falso para engañar al bot
        sendJsonResponse('success', 'Acceso Concedido', 'Redirigiendo...', 'self');
    }
    
    // 6. Validar campos requeridos
    if (!isset($_POST['usuario'], $_POST['password'])) {
        logSuspiciousAttempt("Campos faltantes", $_POST);
        logLoginAttempt('', false, 'Missing fields');
        incrementFailedAttempts();
        
        sendJsonResponse('warning', 'Campos Faltantes', 'Todos los campos son requeridos.');
    }
    
    // 7. Validar y sanitizar inputs
    $usuario = validateLoginInput($_POST['usuario'], 'username');
    $password = validateLoginInput($_POST['password'], 'password');
    
    if (!$usuario || !$password) {
        logLoginAttempt($usuario ?: 'invalid', false, 'Invalid input format');
        incrementFailedAttempts();
        
        // Tiempo constante para evitar timing attacks
        simulateConstantTime();
        
        sendJsonResponse('warning', 'Credenciales Inválidas', 'Usuario o contraseña incorrectos.');
    }
    
    // 8. Usar la clave de encriptación de credentials.php
    $clave_encriptacion = $PASS_UNCRIPT  ?? 'clave_secreta_por_defecto';
    
    // 9. Encriptar credenciales para comparación
    $usuario_encriptado = openssl_encrypt($usuario, 'AES-128-ECB', $clave_encriptacion);
    $password_encriptada = openssl_encrypt($password, 'AES-128-ECB', $clave_encriptacion);
    
    if (!$usuario_encriptado || !$password_encriptada) {
        logLoginAttempt($usuario, false, 'Encryption failed');
        incrementFailedAttempts();
        
        sendJsonResponse('error', 'Error del Sistema', 'Error al procesar credenciales');
    }
    
    // 10. Buscar usuario en base de datos (con tiempo constante)
    $startTime = microtime(true);
    
    $stmt = $conn->prepare("SELECT id, area, activo, lider, failed_login_attempts, last_failed_login 
                            FROM login 
                            WHERE usuario = :usuario");
    $stmt->bindParam(':usuario', $usuario_encriptado);
    $stmt->execute();
    
    $endTime = microtime(true);
    $queryTime = ($endTime - $startTime) * 1000000;
    
    // Asegurar tiempo constante mínimo
    if ($queryTime < 500000) {
        usleep(500000 - $queryTime);
    }
    
    if ($stmt->rowCount() === 0) {
        // Usuario no existe
        logLoginAttempt($usuario, false, 'User not found');
        incrementFailedAttempts();
        
        simulateConstantTime();
        
        sendJsonResponse('warning', 'Credenciales Inválidas', 'Usuario o contraseña incorrectos.');
    }
    
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 11. Verificar si la cuenta está bloqueada por intentos fallidos
    if ($row['failed_login_attempts'] >= 5 && 
        strtotime($row['last_failed_login']) > (time() - 3600)) {
        logLoginAttempt($usuario, false, 'Account locked');
        
        sendJsonResponse('warning', 'Cuenta Bloqueada', 'Demasiados intentos fallidos. Contacta al administrador.');
    }
    
    // 12. Verificar contraseña (con prepared statement seguro)
    $stmt2 = $conn->prepare("SELECT id FROM login 
                             WHERE usuario = :usuario 
                             AND password = :password");
    $stmt2->bindParam(':usuario', $usuario_encriptado);
    $stmt2->bindParam(':password', $password_encriptada);
    $stmt2->execute();
    
    if ($stmt2->rowCount() === 0) {
        // Contraseña incorrecta o usuario incorrecto
        // Incrementar contador de intentos fallidos en BD
        $updateStmt = $conn->prepare("UPDATE login 
                                     SET failed_login_attempts = failed_login_attempts + 1, 
                                         last_failed_login = NOW() 
                                     WHERE id = :id");
        $updateStmt->bindParam(':id', $row['id']);
        $updateStmt->execute();
        
        logLoginAttempt($usuario, false, 'Invalid password or inactive');
        incrementFailedAttempts();
        
        simulateConstantTime();
        
        sendJsonResponse('warning', 'Credenciales Inválidas', 'Usuario o contraseña incorrectos.');
    }
    if($stmt2->rowCount() != 0 && $row["activo"] == 0){
        // Incrementar contador de intentos fallidos en BD
        $updateStmt = $conn->prepare("UPDATE login 
                                     SET failed_login_attempts = failed_login_attempts + 1, 
                                         last_failed_login = NOW() 
                                     WHERE id = :id");
        $updateStmt->bindParam(':id', $row['id']);
        $updateStmt->execute();
        
        logLoginAttempt($usuario, false, 'User disabled');
        incrementFailedAttempts();
        
        simulateConstantTime();
        
        sendJsonResponse('warning', 'Usuario desactivado', 'Esta cuenta esta desactivada.');
    }
    
    // 13. ÉXITO - Login válido
    
    // Resetear contadores de intentos fallidos en BD
    $resetStmt = $conn->prepare("UPDATE login 
                                SET failed_login_attempts = 0, 
                                    last_login = NOW() 
                                WHERE id = :id");
    $resetStmt->bindParam(':id', $row['id']);
    $resetStmt->execute();
    
    // Resetear contadores de sesión
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_blocked_until'] = 0;
    $_SESSION['last_failed_time'] = 0;
    
    // Regenerar ID de sesión
    session_regenerate_id(true);
    
    // Configurar variables de sesión
    $_SESSION['id'] = $row['id'];
    $_SESSION['area'] = $row['area'];
    $_SESSION['lider'] = $row['lider'];
    $_SESSION['login_time'] = time();
    $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // 14. Registrar éxito en logs
    logLoginAttempt($usuario, true, 'Login successful');
    
    // 15. Registrar en log_usuarios (manteniendo tu lógica original)
    $username = $usuario;
    $sql_login_string = "SELECT id, area, activo, lider FROM login WHERE usuario = :usuario AND password = :password";
    $instruccion_encriptada = openssl_encrypt($sql_login_string, 'AES-128-ECB', $clave_encriptacion);
    
    $sql_log = "INSERT INTO log_usuarios (Usuario, Accion, Instruccion, ip_address) 
                VALUES (?, 'Ha iniciado sesión exitosamente', ?, ?)";
    $stmt_log = $conn->prepare($sql_log);
    $stmt_log->execute([
        $username, 
        $instruccion_encriptada,
        $_SERVER['REMOTE_ADDR']
    ]);

    define('ACCESO_PERMITIDO', true);
    // 16. Enviar respuesta de éxito
    sendJsonResponse('success', 'Acceso correcto', 'Inicio de sesión exitoso. Redirigiendo...', 'includes/animacionsvg.php');

} catch (Throwable $e) {
    // Log error sin mostrar detalles
    error_log("Login error [IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "]: " . $e->getMessage());
    logLoginAttempt('unknown', false, 'System error: ' . $e->getMessage());
    
    // Incrementar intentos fallidos
    incrementFailedAttempts();
    // Mostrar error genérico
    sendJsonResponse('error', 'Error del Sistema', 'Ocurrió un error inesperado. Intenta más tarde'.$e->getMessage());
}