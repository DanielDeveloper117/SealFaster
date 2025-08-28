<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');

session_start();

// Convertir warnings/notices a excepciones
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    header('Content-Type: application/json');

    // Solo aceptar POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Metodo no permitido. Solo se acepta POST.']);
        exit;
    }

    // Validacion de parametros
    if (!isset($_POST['id_requisicion'], $_POST['t']) || empty($_POST['id_requisicion']) || empty($_POST['t'])) {
        echo json_encode(['error' => "Parametros faltantes o invalidos"]);
        exit;
    }

    $id_requisicion = $_POST['id_requisicion'];
    $autoriza = $_POST['t'];

    // Validar que id_requisicion sea numero
    if (!preg_match('/^\d+$/', $id_requisicion)) {
        echo json_encode(['error' => "Parametro id_requisicion invalido"]);
        exit;
    }

    $id_usuario = $_SESSION['id'] ?? null;
    if (!$id_usuario) {
        echo json_encode(['error' => "Sesion invalida o expirada"]);
        exit;
    }

    $nombreArchivo = $id_usuario . ".png";
    $rutaBD = 'files/signatures/' . $nombreArchivo;

    // Validar que exista el archivo de firma
    if (!file_exists(ROOT_PATH . $rutaBD)) {
        echo json_encode(['error' => "No existe la firma del usuario en el sistema"]);
        exit;
    }

    // Actualizacion de requisicion
    if ($autoriza === "g") {
        $sql = "UPDATE requisiciones SET 
                    estatus = 'Autorizada',
                    ruta_firma = :ruta
                WHERE id_requisicion = :id_requisicion";
    } elseif ($autoriza === "a") {
        $sql = "UPDATE requisiciones SET 
                    estatus = 'Autorizada',
                    ruta_firma_admin = :ruta
                WHERE id_requisicion = :id_requisicion";
    } else {
        echo json_encode(['error' => "Parametro 't' no valido"]);
        exit;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':ruta', $rutaBD);
    $stmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmt->execute();

    ////////////////////////////PHP MAILER -> cotizador a Inventarios ////////////////
    $mail = null; // Inicializar para evitar "undefined variable" en catch

    try {
        require_once(ROOT_PATH . 'includes/PHPMailer.php');
        $mail = getMailer($conn);

        $sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6 AND rol = 'Gerente'";
        $stmt = $conn->prepare($sqlCorreoInventarios);
        $stmt->execute();
        $correosInventarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$correosInventarios || count($correosInventarios) === 0) {
            throw new Exception("No se encontro ningun correo de inventarios.");
        }

        $clave_encriptacion = $CLAVE_ENCRIPTACION ?? 'SRS2024#tides'; // mejor mover a config.php
        $contadorCorreos = 0;

        foreach ($correosInventarios as $fila) {
            if (!empty($fila['usuario'])) {
                $correo = openssl_decrypt($fila['usuario'], 'AES-128-ECB', $clave_encriptacion);
                if ($correo) {
                    //$mail->addAddress($correo);
                    $contadorCorreos++;
                }
            }
        }

        if ($contadorCorreos === 0) {
            throw new Exception("No se pudo agregar ningun destinatario valido para inventarios.");
        }

        // Agregar correo visible de prueba o destinatario unico
        $mail->addAddress("desarrollo2.sistemas@sellosyretenes.com");
        $mail->Subject = 'Nueva requisición pendiente.';
        $mail->Body = "Se ha autorizado el maquinado de sello de una nueva requisición.<br>
                        Se necesita su ingreso al sistema para agregar las barras correspondientes al control de almacen.<br>
                        Folio de requisición: <b>" . $id_requisicion . "</b>";

        if (!$mail->send()) {
            throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
        }
        ///////////////////////////////////////////////////////////////////////////////////////
        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => "Requisición autorizada correctamente. Correo enviado exitosamente a Inventarios para continuar con el siguiente proceso."
        ]);

    } catch (Throwable $e) {
        echo json_encode([
            'success' => true,
            'message' => "Requisicion autorizada correctamente, pero error al enviar correo: " .
                         addslashes($e->getMessage()) .
                         (($mail && $mail->ErrorInfo) ? " - " . $mail->ErrorInfo : "")
        ]);
    }

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Throwable $e) {
    echo json_encode(['error' => 'Error inesperado: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
