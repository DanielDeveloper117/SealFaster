<?php
// contact_handler.php

// Incluir autoload de Composer (si usas Composer)
require '../vendor/autoload.php';

// O incluir manualmente las clases de PHPMailer

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

    // Obtener y decodificar los datos JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validar que los datos existan
    if (!$data) {
        throw new Exception('Datos no válidos');
    }

    // Validar campos requeridos
    $required_fields = ['email', 'subject', 'message'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    // Validar formato de email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo electrónico no es válido');
    }

    // Sanitizar los datos
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($data['subject']), ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars(trim($data['message']), ENT_QUOTES, 'UTF-8');

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

    // Enviar el correo
    if ($mail->send()) {
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