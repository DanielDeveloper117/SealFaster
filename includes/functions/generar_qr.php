<?php
require_once(__DIR__ . '/../../config/rutes.php');
require ROOT_PATH . 'vendor/autoload.php';
require_once(ROOT_PATH . 'config/config.php');

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;
use Endroid\QrCode\Color\Color;

// Ruta para log personalizado
$log = ROOT_PATH . 'debug/qr_debug.log';
file_put_contents($log, "== INICIO DE QR DINÁMICO ==\n", FILE_APPEND);

try {
    if ((!isset($_GET['id_requisicion']) || empty($_GET['id_requisicion'])) || (!isset($_GET['t']) || empty($_GET['t']))) {
        throw new Exception('Falta algun parametro');
    }
    $id = urlencode($_GET['id_requisicion']);
    $autoriza = urlencode($_GET['t']);
    // Generar token seguro
    $token = bin2hex(random_bytes(32)); // 64 caracteres

    // Guardar token en la base de datos
    $stmt = $conn->prepare("INSERT INTO tokens_autorizacion (token, id_requisicion, autoriza) VALUES (:token, :id, :autoriza)");
    $stmt->bindParam(':token', $token);
    $stmt->bindParam(':id', $_GET['id_requisicion']);
    $stmt->bindParam(':autoriza', $_GET['t']);
    $stmt->execute();

    // Ahora construye la URL incluyendo el token
    $url = "https://sellosyretenes.com/plataforma/estimador/cotizador/modules/firmar.php?id_requisicion={$id}&t={$autoriza}&token={$token}";

    file_put_contents($log, "URL a codificar: $url\n", FILE_APPEND);

    $qr = QrCode::create($url)
        ->setEncoding(new Encoding('UTF-8'))
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelMedium())
        ->setSize(300)
        ->setMargin(10)
        ->setForegroundColor(new Color(0, 0, 0))
        ->setBackgroundColor(new Color(255, 255, 255));

    $writer = new PngWriter();
    $result = $writer->write($qr);

    $pngBinary = $result->getString();

    // Validación mínima
    if (!$pngBinary || strlen($pngBinary) < 100) {
        throw new Exception('La imagen generada es muy pequeña o inválida.');
    }

    // Cabeceras para salida limpia
    header('Content-Type: image/png');
    header('Content-Length: ' . strlen($pngBinary));

    // Salida sin echo (respuesta binaria)
    print($pngBinary); // evita echo, pero sirve el binario correctamente

    file_put_contents($log, "✅ QR generado y enviado correctamente.\n", FILE_APPEND);

} catch (Throwable $e) {
    $mensaje = "[QR ERROR] " . date('Y-m-d H:i:s') . ' - ' . $e->getMessage();
    error_log($mensaje);
    file_put_contents($log, "❌ $mensaje\n", FILE_APPEND);
    http_response_code(500);
    // No echo, no texto al usuario
}
