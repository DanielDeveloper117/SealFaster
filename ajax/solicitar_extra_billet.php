<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');

session_start();

try {
    header('Content-Type: application/json');

    // Validar que todos los campos POST estén presentes
    $camposRequeridos = [
        'id_requisicion', 
        'lote_pedimento', 
        'perfil', 
        'pz_teoricas', 
        'altura_pz', 
        'mm_entrega',
        'justificacion_extra'
    ];
    
    foreach ($camposRequeridos as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo json_encode([
                'success' => false,
                'message' => "Campo requerido faltante: $campo"
            ]);
            exit;
        }
    }

    $id_requisicion = trim($_POST['id_requisicion']);
    $lote_pedimento = trim($_POST['lote_pedimento']);
    $perfil = trim($_POST['perfil']);
    $pz_teoricas = trim($_POST['pz_teoricas']);
    $altura_pz = trim($_POST['altura_pz']);
    $mm_entrega = trim($_POST['mm_entrega']);
    $justificacion_extra = trim($_POST['justificacion_extra']);

    // Validaciones específicas
    if (!is_numeric($pz_teoricas) || $pz_teoricas <= 0) {
        echo json_encode([
            'success' => false,
            'message' => "Las piezas teóricas deben ser un número mayor a 0"
        ]);
        exit;
    }

    if (!is_numeric($altura_pz) || $altura_pz <= 0) {
        echo json_encode([
            'success' => false,
            'message' => "La altura de pieza debe ser un número mayor a 0"
        ]);
        exit;
    }

    if (!is_numeric($mm_entrega) || $mm_entrega < 0) {
        echo json_encode([
            'success' => false,
            'message' => "MM entrega debe ser un número mayor o igual a 0"
        ]);
        exit;
    }

    if (strlen($justificacion_extra) < 10) {
        echo json_encode([
            'success' => false,
            'message' => "La justificación debe tener al menos 10 caracteres"
        ]);
        exit;
    }

    // Iniciar transacción
    $conn->beginTransaction();

    try {
        // 1. Consultar inventario_cnc para validar existencia y estatus
        $stmtInventario = $conn->prepare("
            SELECT Clave, material, Medida, estatus 
            FROM inventario_cnc 
            WHERE lote_pedimento = :lote_pedimento 
            LIMIT 1
        ");
        $stmtInventario->bindParam(':lote_pedimento', $lote_pedimento);
        $stmtInventario->execute();
        $inventario_cnc = $stmtInventario->fetch(PDO::FETCH_ASSOC);

        if (!$inventario_cnc) {
            throw new Exception("La barra con lote pedimento '$lote_pedimento' no existe en el inventario");
        }

        if ($inventario_cnc['estatus'] !== "Disponible para cotizar") {
            throw new Exception("El estatus de la barra debe ser 'Disponible para cotizar'. Estatus actual: " . $inventario_cnc['estatus']);
        }

        // 2. Insertar en control_almacen
        $stmtInsert = $conn->prepare("
            INSERT INTO control_almacen (
                id_requisicion, material, clave, lote_pedimento, medida, 
                perfil_sello, pz_teoricas, altura_pz, mm_entrega, justificacion_extra, 
                es_extra, fecha_registro
            ) VALUES (
                :id_requisicion, :material, :clave, :lote_pedimento, :medida,
                :perfil_sello, :pz_teoricas, :altura_pz, :mm_entrega, :justificacion_extra, 
                1, NOW()
            )
        ");

        $stmtInsert->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        $stmtInsert->bindParam(':material', $inventario_cnc['material']);
        $stmtInsert->bindParam(':clave', $inventario_cnc['Clave']);
        $stmtInsert->bindParam(':lote_pedimento', $lote_pedimento);
        $stmtInsert->bindParam(':medida', $inventario_cnc['Medida']);
        $stmtInsert->bindParam(':perfil_sello', $perfil);
        $stmtInsert->bindParam(':pz_teoricas', $pz_teoricas, PDO::PARAM_INT);
        $stmtInsert->bindParam(':altura_pz', $altura_pz);
        $stmtInsert->bindParam(':mm_entrega', $mm_entrega);
        $stmtInsert->bindParam(':justificacion_extra', $justificacion_extra);

        if (!$stmtInsert->execute()) {
            throw new Exception("Error al insertar la barra extra en control_almacen");
        }

        // 3. Actualizar estatus en inventario_cnc
        $stmtUpdateInventario = $conn->prepare("
            UPDATE inventario_cnc 
            SET estatus = 'En uso' 
            WHERE lote_pedimento = :lote_pedimento
        ");
        $stmtUpdateInventario->bindParam(':lote_pedimento', $lote_pedimento);
        
        if (!$stmtUpdateInventario->execute()) {
            throw new Exception("Error al actualizar el estatus del inventario");
        }

        // 4. Actualizar barra_pendiente en requisiciones
        $stmtUpdateRequisicion = $conn->prepare("
            UPDATE requisiciones 
            SET barra_pendiente = 1 
            WHERE id_requisicion = :id_requisicion
        ");
        $stmtUpdateRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        
        if (!$stmtUpdateRequisicion->execute()) {
            throw new Exception("Error al actualizar el estado de la requisición");
        }

        // Confirmar transacción
        $conn->commit();

        // 5. Preparar y enviar correo (no crítico para la operación)
        $mensajeCorreo = "";
        try {
            require_once(ROOT_PATH . 'includes/PHPMailer.php');
            $mail = getMailer($conn);

            // Obtener correos de dirección comercial
            $sqlCorreoDireccion = "SELECT usuario FROM login WHERE rol = 'CORREO_DIRECCION'";
            $stmtCorreos = $conn->prepare($sqlCorreoDireccion);
            $stmtCorreos->execute();
            $correosDireccion = $stmtCorreos->fetchAll(PDO::FETCH_ASSOC);

            if ($correosDireccion && count($correosDireccion) > 0) {
                $clave_encriptacion = $CLAVE_ENCRIPTACION ?? 'SRS2024#tides';
                $contadorCorreos = 0;

                foreach ($correosDireccion as $fila) {
                    if (!empty($fila['usuario'])) {
                        $correo = openssl_decrypt($fila['usuario'], 'AES-128-ECB', $clave_encriptacion);
                        if ($correo) {
                            //$mail->addAddress($correo);
                            $contadorCorreos++;
                        }
                    }
                }
                
                if ($contadorCorreos > 0) {
                    // Preparar contenido del correo
                    $barraCompleta = $inventario_cnc['Clave'] . " " . $lote_pedimento . " (" . $inventario_cnc['Medida'] . ")";
                    $asunto = "Solicitud de barra extra. Folio: " . $id_requisicion;
                    
                    $cuerpo = "Inventarios ha solicitado la autorización de barra extra para la requisición de maquinado con folio: " . $id_requisicion . ".\n\n";
                    $cuerpo .= "Ingrese al sistema en el módulo de Requisiciones para autorizar.\n\n";
                    $cuerpo .= "Barra: " . $barraCompleta . "\n\n";
                    $cuerpo .= "Justificación:\n";
                    $cuerpo .= $justificacion_extra;

                    $mail->Subject = $asunto;
                    $mail->Body = $cuerpo;

                    // Agregar correo de prueba
                    $mail->addAddress("desarrollo2.sistemas@sellosyretenes.com");

                    if (!$mail->send()) {
                        throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
                    }
                    
                    $mensajeCorreo = " y correo enviado para autorización";
                } else {
                    throw new Exception("No se pudieron agregar destinatarios para el correo");
                }
            }
        } catch (Throwable $e) {
            $mensajeCorreo = ", pero error al enviar correo: " . $e->getMessage();
        }

        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => "Solicitud de barra extra registrada correctamente" . $mensajeCorreo
        ]);

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log("Error en add_extra_billet: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
    http_response_code(500);
} catch (Exception $e) {
    error_log("Error en add_extra_billet: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    http_response_code(400);
} finally {
    $conn = null;
}
?>