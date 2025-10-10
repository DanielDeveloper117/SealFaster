<?php
// partner_form_handler.php

require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Configuración de cabeceras para CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
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

    // Sanitizar los datos
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST['subject']), ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8');

    // Validar archivo PDF
    $cvFile = $_FILES['cvFile'];
    $allowedTypes = ['application/pdf'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB

    // Verificar tipo de archivo
    if (!in_array($cvFile['type'], $allowedTypes)) {
        throw new Exception('Solo se permiten archivos PDF');
    }

    // Verificar tamaño del archivo
    if ($cvFile['size'] > $maxFileSize) {
        throw new Exception('El archivo no debe superar los 5MB');
    }

    // Verificar que sea un PDF real
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $cvFile['tmp_name']);
    finfo_close($fileInfo);

    if ($mimeType !== 'application/pdf') {
        throw new Exception('El archivo debe ser un PDF válido');
    }

    // Configuración del servidor SMTP
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'sellosyretenes.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'plat_autorizaciones@sellosyretenes.com';
    $mail->Password = 'MA9zxx@#8wN'; // pon aquí tu variable segura
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->setFrom('plat_autorizaciones@sellosyretenes.com', 'Sellos y Retenes de San Luis');
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $mail->addAddress('desarrollo2.sistemas@sellosyretenes.com');

    // Adjuntar archivo PDF
    $mail->addAttachment(
        $cvFile['tmp_name'],
        'CV_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $cvFile['name']),
        'base64',
        'application/pdf'
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
                        <strong>Tamaño:</strong> " . round($cvFile['size'] / 1024 / 1024, 2) . " MB<br>
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
                     "Tamaño: " . round($cvFile['size'] / 1024 / 1024, 2) . " MB\n" .
                     "Tipo: " . $cvFile['type'] . "\n\n" .
                     "Enviado desde: www.sellosyretenes.com\n" .
                     "Fecha: " . date('d/m/Y H:i:s');

    // Enviar el correo
    if ($mail->send()) {
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