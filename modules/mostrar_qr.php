<?php
require_once(__DIR__ . '/../config/rutes.php');
require ROOT_PATH . 'vendor/autoload.php';

use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\Output\QRGdImage;

$options = new QROptions([
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel'   => QRCode::ECC_M,
    'scale'      => 6,
]);

$url = 'https://sellosyretenes.com/sealfaster/modules/firma.php?id_requisicion=2';

$qr = new QRCode($options);
$matrix = $qr->getMatrix($url);
$renderer = new QRGdImage($options, $matrix);
$imageData = $renderer->dump();

header('Content-Type: image/png');
header('Content-Length: ' . strlen($imageData));
//echo $imageData;
$base64 = base64_encode($imageData);
echo "<img src='data:image/png;base64,{$base64}' />";

exit;
