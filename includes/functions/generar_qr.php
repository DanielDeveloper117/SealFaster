<?php
require_once(__DIR__ . '/../../config/rutes.php');
require ROOT_PATH . 'vendor/autoload.php';
require_once(ROOT_PATH . 'config/config.php');
session_start();

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;
use Endroid\QrCode\Color\Color;

try {
    if (!isset($_GET['id_requisicion'], $_GET['t'])) {
        throw new Exception('Faltan parametros requeridos');
    }

    $id_usuario = $_SESSION['id'];
    $id = urlencode($_GET['id_requisicion']);
    $autoriza = urlencode($_GET['t']);

    // Token seguro
    $token = bin2hex(random_bytes(32));

    // URL que se codificará
    $url = "https://sellosyretenes.com/sealfaster/modules/firmar.php?id_requisicion={$id}&t={$autoriza}&token={$token}&u={$id_usuario}";
    //$url = "http://localhost/cotizador/modules/firmar.php?id_requisicion={$id}&t={$autoriza}&token={$token}&u={$id_usuario}";


    // Guardar token en base de datos
    $stmt = $conn->prepare("INSERT INTO tokens_autorizacion (token, id_requisicion, autoriza, url) VALUES (:token, :id, :autoriza, :url)");
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':id', $_GET['id_requisicion']);
    $stmt->bindParam(':autoriza', $_GET['t']);
    $stmt->bindParam(':url', $url);
    $stmt->execute();

    // Generar QR en memoria
    $qr = QrCode::create($url)
        ->setEncoding(new Encoding('UTF-8'))
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelMedium())
        ->setSize(200)
        ->setMargin(10)
        ->setForegroundColor(new Color(0, 0, 0))
        ->setBackgroundColor(new Color(255, 255, 255));

    $writer = new PngWriter();
    $result = $writer->write($qr);
    $qrBase64 = base64_encode($result->getString());

    // Responder con JSON
    echo json_encode([
        'success' => true,
        'url' => $url,           // URL real que contiene el token
        'qrBase64' => $qrBase64  // Imagen QR en Base64
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
$conn = null;
