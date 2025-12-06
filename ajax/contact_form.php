<?php
// contact_handler.php
session_start();
require_once(__DIR__ . '/../../../secure_config/rate_limiter.php');
require_once(__DIR__ . '/../../../secure_config/ip_blocker.php');

// Función para registrar intentos de usuarios
function logUserAttempt($postData) {
    $logDir = __DIR__ . '/../../../logs';
    $logFile = $logDir . '/contact_form_users.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    if (!file_exists($logFile)) {
        file_put_contents($logFile, "=== LOG DE USUARIOS INICIADO ===\n");
    }

    $entry = date('Y-m-d H:i:s') . ' | ' .
        'IP=' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | ' .
        'UA=' . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . ' | ' .
        'POST=' . json_encode($postData) . PHP_EOL;

    file_put_contents($logFile, $entry, FILE_APPEND);
}

// Función para registrar intentos de bots
function logBotAttempt($data) {
    $logDir = __DIR__ . '/../../../logs';
    $logFile = $logDir . '/contact_form_bots.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    if (!file_exists($logFile)) {
        file_put_contents($logFile, "=== LOG DE BOTS INICIADO ===\n");
    }

    $entry = date('Y-m-d H:i:s') . ' | ' .
        'IP=' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | ' .
        'UA=' . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . ' | ' .
        'POST=' . json_encode($data) . PHP_EOL;

    file_put_contents($logFile, $entry, FILE_APPEND);
}

// Función para registrar intentos sospechosos
function logSuspiciousFileAttempt($reason, $postData) {
    $logDir = __DIR__ . '/../../../logs';
    $logFile = $logDir . '/contact_form_suspicious.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    if (!file_exists($logFile)) {
        file_put_contents($logFile, "=== LOG DE INTENTOS SOSPECHOSOS INICIADO ===\n");
    }

    $entry = date('Y-m-d H:i:s') . ' | ' .
        'REASON=' . $reason . ' | ' .
        'IP=' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | ' .
        'UA=' . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . ' | ' .
        'POST=' . json_encode($postData) . PHP_EOL;

    file_put_contents($logFile, $entry, FILE_APPEND);
}

// Verificar límite de tasa
if (!rate_limit_allow()) {
    logSuspiciousFileAttempt("rate_limit_exceeded", $_POST);
    logBotAttempt($_POST);
    block_ip($_SERVER['REMOTE_ADDR']);
    echo json_encode([
        "success" => false,
        "message" => "Demasiadas solicitudes. Intenta más tarde."
    ]);
    exit;
}

// Verificar IP bloqueada
if (!block_ip_check()) {
    logSuspiciousFileAttempt("Blocked IP intento de acceso", $_POST);
    logBotAttempt($_POST);
    echo json_encode([
        "success" => false,
        "message" => "Acceso denegado"
    ]);
    exit;
}

// Incluir PHPMailer
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Cargar credenciales SMTP
$config = require_once(__DIR__ . '/../../../secure_config/credentials.php');

// Configuración de cabeceras para CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Respuesta por defecto
$response = [
    'success' => false,
    'message' => 'Error desconocido'
];

try {
    // Verificar que la petición sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Verificar captcha
    if (!isset($_POST['captcha_valid']) || $_POST['captcha_valid'] !== 'yes') {
        echo json_encode([
            "success" => false,
            "message" => "Captcha no validado"
        ]);
        exit;
    }

    // Verificar CSRF token
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        echo json_encode([
            "success" => false,
            "message" => "Token CSRF inválido"
        ]);
        exit;
    }

    // Verificar que se recibieron datos POST
    if (empty($_POST)) {
        throw new Exception('Datos del formulario no recibidos');
    }

    // Validar campos requeridos
    $required_fields = ['email', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    // Validar formato de email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo electrónico no es válido');
    }

    // Honeypot: campo oculto para bots
    if (!empty($_POST['phone_number'])) {
        // Bot detectado
        logBotAttempt($_POST);
        http_response_code(200);
        echo json_encode([
            'success' => true, 
            'message' => 'Los datos se han enviado correctamente. ¡Gracias por contactarnos! Buen intento bot'
        ]);
        exit;
    }

    // Sanitizar los datos
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST['subject']), ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');

    // Registrar intento válido
    logUserAttempt($_POST);

    // Configuración del servidor SMTP
    $mail = new PHPMailer(true);
    $mail->Host = $config['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['SMTP_USER'];
    $mail->Password = $config['SMTP_PASS'];
    $mail->SMTPSecure = $config['SMTP_SECURE'];
    $mail->Port = $config['SMTP_PORT'];
    $mail->setFrom($config['SMTP_FROM'], $config['SMTP_DOMAIN_NAME']);
    $mail->isSMTP();
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->addAddress($config['SMTP_DEV_EMAIL']);

    $mail->Subject = "Formulario de Contacto: $subject";
    
    $mailBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
            .container { background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #001236 0%, #0066cc 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
            .content { margin: 20px 0; }
            .field { margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; }
            .label { font-weight: bold; color: #495057; }
            .value { color: #212529; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Nuevo Mensaje de Contacto</h1>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>De:</div>
                    <div class='value'>$email</div>
                </div>
                <div class='field'>
                    <div class='label'>Asunto:</div>
                    <div class='value'>$subject</div>
                </div>
                <div class='field'>
                    <div class='label'>Mensaje:</div>
                    <div class='value'>$message</div>
                </div>
            </div>
            <div class='footer'>
                <p>Los datos fueron enviados desde el formulario de contacto de www.sellosyretenes.com.</p>
                <p>Fecha: " . date('d/m/Y H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $mail->Body = $mailBody;
    
    // Versión alternativa en texto plano
    $mail->AltBody = "Nuevo mensaje de contacto:\n\nDe: $email\nAsunto: $subject\nMensaje: $message\n\nEnviado el: " . date('d/m/Y H:i:s');

    $mailsend = true;
    // Enviar el correo
    //if ($mail->send()) {
    if ($mailsend) {
        $response = [
            'success' => true,
            'message' => 'Los datos se han enviado correctamente'
        ];
    } else {
        throw new Exception('Error al enviar el correo: ' . $mail->ErrorInfo);
    }

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Devolver respuesta en formato JSON
echo json_encode($response);
?>