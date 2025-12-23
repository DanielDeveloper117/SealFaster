<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

    // Iniciar transacción para consistencia
    $conn->beginTransaction();
    $sqlUserInfo = "SELECT * FROM login WHERE id = :id_usuario";
    $stmtUserInfo = $conn->prepare($sqlUserInfo);
    $stmtUserInfo->bindParam(':id_usuario', $id_usuario);
    $stmtUserInfo->execute();
    $arregloUser = $stmtUserInfo->fetch(PDO::FETCH_ASSOC);

    $clave_encriptacion = $PASS_UNCRIPT;
    $nombre_encriptado = $arregloUser['nombre'];
    $nombreUser = openssl_decrypt($nombre_encriptado, 'AES-128-ECB', $clave_encriptacion);
    // Actualizacion de requisicion
    if ($autoriza === "g") {
        $sql = "UPDATE requisiciones SET 
                    estatus = 'Autorizada',
                    ruta_firma = :ruta,
                    autorizo = :autorizo,
                    fecha_autorizacion = NOW()
                WHERE id_requisicion = :id_requisicion";
    } elseif ($autoriza === "a") {
        $sql = "UPDATE requisiciones SET 
                    estatus = 'Autorizada',
                    ruta_firma_admin = :ruta,
                    autorizo = :autorizo,
                    fecha_autorizacion = NOW()
                WHERE id_requisicion = :id_requisicion";
    } else {
        echo json_encode(['error' => "Parametro 't' no valido"]);
        exit;
    }

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':ruta', $rutaBD);
    $stmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmt->bindParam(':autorizo', $nombreUser);
    $stmt->execute();

    // 1. Obtener cotizaciones asociadas para actualizar pre-stock
    $sqlRequisicion = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
    $stmtRequisicion = $conn->prepare($sqlRequisicion);
    $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion);
    $stmtRequisicion->execute();
    $result = $stmtRequisicion->fetch(PDO::FETCH_ASSOC);

    if ($result && !empty($result['cotizaciones'])) {
        $cotizacion_ids = explode(', ', $result['cotizaciones']);

        // 2. Actualizar estado de cada cotización
        $sqlUpdateCotizacion = "UPDATE cotizacion_materiales SET estatus_completado = 'Autorizada', fecha_actualizacion = NOW() WHERE id_cotizacion = :id_cotizacion";
        $stmtUpdateCotizacion = $conn->prepare($sqlUpdateCotizacion);

        // 3. Preparar consulta para actualizar inventario
        $sqlUpdatePreStock = "UPDATE inventario_cnc SET pre_stock = pre_stock - :consumo_total, estatus = 'En uso', updated_at = NOW() WHERE lote_pedimento = :lote_pedimento";
        $stmtUpdatePreStock = $conn->prepare($sqlUpdatePreStock);

        // 4. Array para acumular consumo por lote_pedimento
        $consumoPorLote = [];

        foreach ($cotizacion_ids as $id_cotizacion) {
            // Actualizar estado de la cotización
            $stmtUpdateCotizacion->bindValue(':id_cotizacion', $id_cotizacion);
            $stmtUpdateCotizacion->execute();

            // Obtener todas las estimaciones de esta cotización
            $sqlEstimaciones = "SELECT id_cotizacion, a_sello, material, billets_lotes FROM cotizacion_materiales WHERE id_cotizacion = :id_cotizacion";
            $stmtEstimaciones = $conn->prepare($sqlEstimaciones);
            $stmtEstimaciones->bindValue(':id_cotizacion', $id_cotizacion);
            $stmtEstimaciones->execute();
            $estimaciones = $stmtEstimaciones->fetchAll(PDO::FETCH_ASSOC);

            foreach ($estimaciones as $estimacion) {
                $a_sello = floatval($estimacion['a_sello']);
                $material = $estimacion['material'];
                $billets_lotes = $estimacion['billets_lotes'];

                // Determinar desbaste por material
                $desbaste = 2.50; // Valor por defecto (material duro)
                
                $materialesBlandos = ['H-ECOPUR', 'ECOSIL', 'ECORUBBER 1', 'ECORUBBER 2', 'ECORUBBER 3', 'ECOPUR'];
                $materialesDuros = ['ECOTAL', 'ECOMID', 'ECOFLON 1', 'ECOFLON 2', 'ECOFLON 3'];
                
                if (in_array($material, $materialesBlandos)) {
                    $desbaste = 2.00;
                } elseif (in_array($material, $materialesDuros)) {
                    $desbaste = 2.50;
                }

                // Procesar cada billet/lote
                if (!empty($billets_lotes)) {
                    $billets = array_map('trim', explode(',', $billets_lotes));
                    
                    foreach ($billets as $billet) {
                        // Extraer lote_pedimento y cantidad de piezas
                        // Formato: "R2T047062-1 (47/62) 1 pz"
                        if (preg_match('/^([^\s]+)\s+\([^)]+\)\s+(\d+)\s+pz$/i', $billet, $matches)) {
                            $lote_pedimento = trim($matches[1]);
                            $cantidad_piezas = intval($matches[2]);
                            
                            // Calcular consumo para este billet
                            $altura_por_pieza = $a_sello + $desbaste;
                            $consumo_total = $altura_por_pieza * $cantidad_piezas;
                            
                            // Acumular consumo por lote_pedimento
                            if (!isset($consumoPorLote[$lote_pedimento])) {
                                $consumoPorLote[$lote_pedimento] = 0;
                            }
                            $consumoPorLote[$lote_pedimento] += $consumo_total;
                        }
                    }
                }
            }
        }

        // 5. Actualizar pre_stock en inventario_cnc para cada lote
        foreach ($consumoPorLote as $lote_pedimento => $consumo_total) {
            $stmtUpdatePreStock->bindValue(':consumo_total', $consumo_total);
            $stmtUpdatePreStock->bindValue(':lote_pedimento', $lote_pedimento);
            $stmtUpdatePreStock->execute();
        }
    }

    // Confirmar transacción
    $conn->commit();

    ////////////////////////////PHP MAILER -> cotizador a Inventarios ////////////////
    $mail = null; // Inicializar para evitar "undefined variable" en catch

    try {
        //require_once(ROOT_PATH . 'includes/PHPMailer.php');
        //$mail = getMailer($conn);
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $HOST;
        $mail->SMTPAuth = true;
        $mail->Username = $USER;
        $mail->Password = $PASS; 
        $mail->SMTPSecure = $SECURE;
        $mail->Port = $PORT;
        $mail->setFrom($FROM, $DOMAIN_NAME);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        //$sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6 AND rol = 'Gerente'";
        $sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6";
        $stmt = $conn->prepare($sqlCorreoInventarios);
        $stmt->execute();
        $correosInventarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$correosInventarios || count($correosInventarios) === 0) {
            throw new Exception("No se encontro ningun correo de inventarios.");
        }

        $clave_encriptacion = $PASS_UNCRIPT ?? ''; 
        $contadorCorreos = 0;

        foreach ($correosInventarios as $fila) {
            if (!empty($fila['usuario'])) {
                $correo = openssl_decrypt($fila['usuario'], 'AES-128-ECB', $clave_encriptacion);
                if ($correo) {
                    if($DEV_MODE === false){
                        $mail->addAddress($correo);
                    }
                    $contadorCorreos++;
                }
            }
        }

        if ($contadorCorreos === 0) {
            throw new Exception("No se pudo agregar ningun destinatario valido para inventarios.");
        }

        // Agregar correo visible de prueba o destinatario unico
        if($DEV_MODE === true){
            $mail->addAddress($DEV_EMAIL);
        }
        $mail->Subject = 'Nueva requisición pendiente. Folio: '.$id_requisicion;
        $mail->Body = "Se ha autorizado el maquinado de sello de una nueva requisición.<br>
                        Se necesita su ingreso al sistema para agregar y entregar los billets correspondientes.<br>
                        Folio de requisición: <b>" . $id_requisicion . "</b>";
        ///////////////////////////////////////////////////////////////////////////////////////
        // Respuesta exitosa
        if($SEND_MAIL === true){
            if (!$mail->send()) {
                throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
            }
            echo json_encode([
                'success' => true,
                'message' => "Requisición autorizada correctamente. Correo enviado exitosamente a Inventarios para continuar con el siguiente proceso."
            ]);
        }else{
            echo json_encode([
                'success' => true,
                'message' => "Requisición autorizada correctamente. Envío de correos no disponible."
            ]);
        }

    } catch (Throwable $e) {
        if($SEND_MAIL === true){
            echo json_encode([
                'success' => true,
                'message' => "Requisicion autorizada correctamente, pero error al enviar correo: " .
                            addslashes($e->getMessage()) .
                            (($mail && $mail->ErrorInfo) ? " - " . $mail->ErrorInfo : "")
            ]);
        }else{
            echo json_encode([
                'success' => true,
                'message' => "Requisición autorizada correctamente. Envío de correos no disponible."
            ]);
            
        }
    }

} catch (PDOException $e) {
    // Revertir transacción en caso de error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Throwable $e) {
    // Revertir transacción en caso de error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['error' => 'Error inesperado: ' . $e->getMessage()]);
} finally {
    $conn = null;
}