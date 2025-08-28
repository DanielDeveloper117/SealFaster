<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getMailer(PDO $conn): PHPMailer {
    $clave_encriptacion = 'SRS2024#tides';

    $sqlCorreoCotizador = "SELECT usuario, password FROM login WHERE rol = 'CORREO_COTIZADOR'";
    $stmtCorreoCotizador = $conn->prepare($sqlCorreoCotizador);
    $stmtCorreoCotizador->execute();
    $arregloCorreoCotizador = $stmtCorreoCotizador->fetch(PDO::FETCH_ASSOC);

    if (!$arregloCorreoCotizador) {
        throw new Exception("No se encontró correo de cotizador.");
    }

    $correo_cotizador = openssl_decrypt($arregloCorreoCotizador['usuario'], 'AES-128-ECB', $clave_encriptacion);
    $pass_correo_cotizador = openssl_decrypt($arregloCorreoCotizador['password'], 'AES-128-ECB', $clave_encriptacion);

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

    return $mail;
}
?>
