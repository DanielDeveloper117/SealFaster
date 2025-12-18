<?php
session_start();
require_once(__DIR__ . '/../../../secure_config/rate_limiter.php');
require_once(__DIR__ . '/../../../secure_config/ip_blocker.php');

function logUserAttempt($postData, $fileData) {
    $logDir = __DIR__ . '/../../../logs';
    $logFile = $logDir . '/partner_form_users.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    if (!file_exists($logFile)) {
        file_put_contents($logFile, "=== LOG DE USUARIOS INICIADO ===\n");
    }

    $entry = date('Y-m-d H:i:s') . ' | ' .
        'IP=' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | ' .
        'UA=' . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . ' | ' .
        'POST=' . json_encode($postData) . ' | ' .
        'FILE=' . json_encode([
            'name' => $fileData['name'] ?? null,
            'size' => $fileData['size'] ?? null,
            'type' => $fileData['type'] ?? null
        ]) .
        PHP_EOL;

    file_put_contents($logFile, $entry, FILE_APPEND);
}

function logBotAttempt($data) {
    // Carpeta de logs (en base al archivo actual)
    $logDir = __DIR__ . '/../../../logs';
    $logFile = $logDir . '/partner_form_bots.log';

    // Crear carpeta si no existe
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true); // crear con permisos
    }

    // Crear archivo si no existe
    if (!file_exists($logFile)) {
        file_put_contents($logFile, "=== LOG DE BOTS INICIADO ===\n");
    }

    // Construir entrada
    $entry = date('Y-m-d H:i:s') . ' | ' .
        'IP=' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | ' .
        'UA=' . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . ' | ' .
        'POST=' . json_encode($data) . PHP_EOL;

    // Escribir entrada
    file_put_contents($logFile, $entry, FILE_APPEND);
}

function logSuspiciousFileAttempt($reason, $postData, $fileData = null) {
    $logDir = __DIR__ . '/../../../logs';
    $logFile = $logDir . '/partner_form_suspicious.log';

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
        'POST=' . json_encode($postData) . ' | ' .
        'FILE=' . json_encode($fileData) .
        PHP_EOL;

    file_put_contents($logFile, $entry, FILE_APPEND);
}

if (!rate_limit_allow()) {
    logSuspiciousFileAttempt("rate_limit_exceeded", $_POST, $_FILES['cvFile'] ?? null);
    logBotAttempt($_POST);
    block_ip($_SERVER['REMOTE_ADDR']);
    echo json_encode([
        "success" => false,
        "message" => "Demasiadas solicitudes. Intenta mas tarde."
    ]);
    exit;
}
if (!block_ip_check()) {
    logSuspiciousFileAttempt("Blocked IP intento de acceso", $_POST, $_FILES['cvFile'] ?? null);
    logBotAttempt($_POST);
    echo json_encode([
        "success" => false,
        "message" => "Acceso denegado"
    ]);
    exit;
}
// partner_form.php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// cargar credenciales SMTP
$config = require_once('../../../secure_config/credentials.php');
//var_dump($config);
//exit;
// Configuración de cabeceras para CORS
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: http://localhost:3000");
//header("Access-Control-Allow-Origin: https://tu-dominio.com");
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

    if (!isset($_POST['captcha_valid']) || $_POST['captcha_valid'] !== 'yes') {
        echo json_encode([
            "success" => false,
            "message" => "Captcha no validado"
        ]);
        exit;
    }

    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        echo json_encode([
            "success" => false,
            "message" => "Token CSRF invalido"
        ]);
        exit;
    }


    // Verificar que sea una petición multipart/form-data
    if (empty($_FILES) && empty($_POST)) {
        throw new Exception('Datos del formulario no recibidos');
    }

    // Validar campos requeridos
    $required_fields = ['email', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    // Validar archivo adjunto
    if (empty($_FILES['cvFile']) || $_FILES['cvFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('El archivo CV es requerido');
    }

    // Validar formato de email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo electrónico no es válido');
    }

    if (!empty($_POST['phone_number'])) {
        // bot detectado
        logBotAttempt($_POST);
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Tu CV se ha enviado correctamente. ¡Gracias por tu interés! Buen intento bot']);
        exit;
    }

    // Sanitizar los datos
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST['subject']), ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');
    // ---------- funciones de apoyo ----------
    /**
     * sanitize_filename
     * devuelve un nombre seguro sin rutas ni caracteres raros
     */
    function sanitize_filename($filename) {
        // eliminar ruta y dejar solo basename
        $base = basename($filename);
        // reemplazar espacios y caracteres no alfanum/._- por _
        $base = preg_replace('/[^A-Za-z0-9._-]/', '_', $base);
        // limitar largo razonable
        return substr($base, 0, 200);
    }

    /**
     * validarExtensionArchivo mejorada
     * permite puntos en el nombre, valida la extension final y comprueba que
     * no existan extensiones peligrosas en el nombre (ej: archivo.php.pdf)
     */
    function validarExtensionArchivo($fileName) {
        $allowed = ['pdf','doc','docx'];

        $base = basename($fileName);
        $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            return false;
        }

        // comprobar que no exista ninguna extension peligrosa en el nombre:
        $dangerous = ['php','php5','phtml','phar','exe','sh','pl','py','js','jsp','asp','aspx','cer','jar'];
        $parts = explode('.', $base);
        foreach ($parts as $p) {
            if (in_array(strtolower($p), $dangerous)) {
                return false;
            }
        }

        return true;
    }

    /**
     * validarMimeReal
     * devuelve el mime real o false
     */
    function validarMimeReal($tmpFilePath) {
        $allowedMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $tmpFilePath);
        finfo_close($finfo);

        return in_array($realMime, $allowedMimes) ? $realMime : false;
    }

    /**
     * check_pdf_magic_bytes
     * check que el archivo comience con %PDF
     */
    function check_pdf_magic_bytes($tmpFilePath) {
        $fh = fopen($tmpFilePath, 'rb');
        if (!$fh) return false;
        $bytes = fread($fh, 4);
        fclose($fh);
        return $bytes === '%PDF';
    }

    /**
     * store_uploaded_file
     * mueve el archivo a carpeta segura fuera de public, con nombre aleatorio y permisos.
     * devuelve ruta absoluta del archivo guardado o false si falla.
     */
    // function store_uploaded_file($tmpPath, $origName) {
    //     // carpeta fuera de htdocs (ajusta segun tu estructura)
    //     $uploadDir = __DIR__ . '/../../../private_uploads/cvs';
    //     if (!is_dir($uploadDir)) {
    //         mkdir($uploadDir, 0750, true);
    //         // fijar permisos si es necesario
    //     }

    //     // generar nombre seguro unico
    //     $safeName = bin2hex(random_bytes(16)) . '_' . sanitize_filename($origName);
    //     $dest = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

    //     if (!move_uploaded_file($tmpPath, $dest)) {
    //         return false;
    //     }

    //     // permisos restrictivos: lectura para owner y grupo
    //     @chmod($dest, 0640);
    //     return $dest;
    // }
    /**
     * scan_pdf_for_malware
     * Escaneo basico de seguridad para PDF
     * Busca patrones de JS, acciones automaticas,
     * streams sospechosos, launchers, etc.
     */
    function scan_pdf_for_malware($filePath) {

        $content = file_get_contents($filePath);
        if ($content === false) {
            return "no_se_pudo_leer";
        }

        // 1) Detectar solo JavaScript REAL, no referencias
        // Busca JS con codigo dentro, no simples labels.
        $patterns_js_real = [
            '/\/JS\s*\(\s*[^)]{5,}\)/i',            // /JS (contenido)
            '/\/JavaScript\s*\(\s*[^)]{5,}\)/i',    // /JavaScript (contenido)
            '/eval\s*\(/i',
            '/this\.submitForm/i',
            '/function\s+[a-z0-9_]+\s*\(/i'
        ];

        foreach ($patterns_js_real as $p) {
            if (preg_match($p, $content)) {
                return "javascript_activo_sospechoso";
            }
        }

        // 2) Launch peligroso
        if (preg_match('/\/Launch\s*\(/i', $content)) {
            return "launch_sospechoso";
        }

        // 3) Archivos peligrosos incrustados
        if (preg_match('/\/EmbeddedFile/i', $content)) {
            if (preg_match('/\.(exe|js|vbs|scr|zip|bat)\b/i', $content)) {
                return "archivo_incrustado_peligroso";
            }
        }

        // 4) Streams extremadamente grandes (PDF polimorficos)
        // if (preg_match('/stream([\s\S]{8000000,})endstream/i', $content)) {
        //     return "stream_excesivo_sospechoso";
        // }

        // 5) Codificaciones peligrosas solo en streams excesivos
        // if (preg_match('/(ASCIIHexDecode|ASCII85Decode|LZWDecode|FlateDecode)/i', $content)) {
        //     if (preg_match('/stream([\s\S]{1000,})endstream/i', $content)) {
        //         return "codificacion_sospechosa_en_stream_largo";
        //     }
        // }

        return "ok";
    }



    /**
     * scan_pdf_advanced
     * Analisis de segunda capa para detectar PDF con estructuras anormales,
     * payloads ocultos, entropia sospechosa o binarios incrustados.
     */
    function scan_pdf_advanced($filePath) {

        $content = file_get_contents($filePath);
        if ($content === false) {
            return "no_se_pudo_leer";
        }

        $size = strlen($content);

        // 1. cabecera PDF real
        if (!str_starts_with($content, "%PDF")) {
            return "no_pdf_real";
        }

        // 2. EOF correcto (regex corregido)
        if (!preg_match('/%%EOF\s*$/i', $content)) {
            return "sin_eof_correcto";
        }

        // 3. entropia
        $len = strlen($content);
        $freq = count_chars($content, 1);
        $entropy = 0;

        foreach ($freq as $f) {
            $p = $f / $len;
            $entropy -= $p * log($p, 2);
        }

        // if ($entropy > 8.2) {
        //     return "entropia_sospechosa";
        // }

        // 4. objetos PDF
        preg_match_all('/\d+\s+\d+\s+obj/i', $content, $objs);
        $objCount = count($objs[0]);

        if ($objCount < 1) {
            return "sin_objetos_pdf";
        }

        if ($objCount > 1500) {
            return "exceso_objetos_sospechosos";
        }

        // 5. binarios PE/ELF/ZIP incrustados
        $binaryMarkers = [
            "MZ",
            "\x7F\x45\x4C\x46",
            "PK\x03\x04",
            "PK\x05\x06",
            "PK\x07\x08",
        ];

        // foreach ($binaryMarkers as $marker) {
        //     if (strpos($content, $marker) !== false) {
        //         return "binario_embebido_detectado";
        //     }
        // }

        // 6. codificaciones sospechosas (regex corregido)
        if (preg_match('/((ASCII85Decode|JBIG2Decode|DCTDecode)\s*){3,}/i', $content)) {
            return "encodings_excesivos";
        }

        // 7. multiples xref
        $xrefCount = preg_match_all('/\bxref\b/i', $content, $tmp);
        if ($xrefCount > 4) {
            return "multiples_xref_sospechosos";
        }

        // 8. contenido despues de EOF
        $eofPos = strrpos($content, "%%EOF");
        if ($eofPos !== false && $eofPos < $size - 50) {
            return "contenido_despues_de_eof";
        }

        return "ok";
    }






    $cvFile = $_FILES['cvFile'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    // validar PHP upload error
    if ($cvFile['error'] !== UPLOAD_ERR_OK) {
        logSuspiciousFileAttempt('Error interno de subida: code ' . $cvFile['error'], $_POST, $cvFile);
        echo json_encode(["success" => false, "message" => "Error al subir archivo"]);
        exit;
    }

    // usar tamaño real provisto por PHP
    if ($cvFile['size'] > $maxFileSize) {
        logSuspiciousFileAttempt('Archivo excede tamano permitido', $_POST, $cvFile);
        echo json_encode(["success" => false, "message" => "El archivo excede el limite permitido"]);
        exit;
    }
    if ($cvFile['size'] < 100) {
        logSuspiciousFileAttempt('Archivo extremadamente pequeno o vacio', $_POST, $cvFile);
        echo json_encode(["success" => false, "message" => "El archivo esta vacio o es sospechoso"]);
        exit;
    }

    // verificar extension final y que no haya extensiones peligrosas
    if (!validarExtensionArchivo($cvFile['name'])) {
        logSuspiciousFileAttempt('Extension no permitida o doble extension', $_POST, $cvFile);
        echo json_encode(["success" => false, "message" => "Archivo no permitido"]);
        exit;
    }

    // validar MIME real
    $mimeReal = validarMimeReal($cvFile['tmp_name']);
    if ($mimeReal === false) {
        logSuspiciousFileAttempt('MIME no permitido', $_POST, $cvFile);
        echo json_encode(["success" => false, "message" => "Archivo no valido"]);
        exit;
    }

    // si es pdf, verificar magic bytes
    if ($mimeReal === 'application/pdf' && !check_pdf_magic_bytes($cvFile['tmp_name'])) {
        logSuspiciousFileAttempt('PDF sin magic bytes', $_POST, $cvFile);
        echo json_encode(["success" => false, "message" => "Archivo PDF corrupto o invalido"]);
        exit;
    }
    // analisis basico de malware en PDF
    if ($mimeReal === 'application/pdf') {
        $scan = scan_pdf_for_malware($cvFile['tmp_name']);
        if ($scan !== 'ok') {
            logSuspiciousFileAttempt('PDF malware detection: ' . $scan, $_POST, $cvFile);
            echo json_encode(["success" => false, "message" => "El archivo PDF parece ser inseguro"]);
            exit;
        }
    }
    // segunda capa avanzada solo para PDFs
    if ($mimeReal === 'application/pdf') {
        $adv = scan_pdf_advanced($cvFile['tmp_name']);
        if ($adv !== 'ok') {
            logSuspiciousFileAttempt('PDF advanced scan: ' . $adv, $_POST, $cvFile);
            echo json_encode(["success" => false, "message" => "El archivo PDF parece inseguro para la segunda capa"]);
            exit;
        }
    }

    // Registrar intento válido
    logUserAttempt($_POST, $cvFile);

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

    // Adjuntar archivo PDF
    $mail->addAttachment(
        $cvFile['tmp_name'],
        'CV_' . sanitize_filename($cvFile['name']),
        'base64',
        $mimeReal // El MIME real ya validado
    );


    $mail->Subject = "Nuevo CV Recibido: $subject";
    
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
            .file-info { background-color: #e7f3ff; border-left: 4px solid #0066cc; padding: 10px; margin: 10px 0; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Nuevo CV Recibido</h1>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Correo del candidato:</div>
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
                <div class='file-info'>
                    <div class='label'>Archivo adjunto:</div>
                    <div class='value'>
                        <strong>Nombre:</strong> " . htmlspecialchars($cvFile['name']) . "<br>
                        <strong>Tamaño:</strong> " . round($cvFile['size'] / 1024, 2) . " KB<br>
                        <strong>Tipo:</strong> " . $cvFile['type'] . "
                    </div>
                </div>
            </div>
            <div class='footer'>
                <p>Este CV fue enviado desde el formulario de trabajo de www.sellosyretenes.com</p>
                <p>Fecha: " . date('d/m/Y H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $mail->Body = $mailBody;
    
    // Versión alternativa en texto plano
    $mail->AltBody = "NUEVO CV RECIBIDO\n\n" .
                     "Correo del candidato: $email\n" .
                     "Asunto: $subject\n" .
                     "Mensaje: $message\n\n" .
                     "ARCHIVO ADJUNTO:\n" .
                     "Nombre: " . $cvFile['name'] . "\n" .
                     "Tamaño: " . round($cvFile['size'] / 1024, 2) . " KB\n" .
                     "Tipo: " . $cvFile['type'] . "\n\n" .
                     "Enviado desde: oculto\n" .
                     "Fecha: " . date('d/m/Y H:i:s');

    // Enviar el correo
    $mailsend = true;
    //if ($mail->send()) {
    if ($mailsend) {
        $response = [
            'success' => true,
            'message' => 'Tu CV se ha enviado correctamente. ¡Gracias por tu interés!'
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