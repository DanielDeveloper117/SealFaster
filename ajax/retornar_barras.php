<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
session_start();

// Validar sesion
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}

// Manejo de errores como excepciones
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    header('Content-Type: application/json');

    // Validar metodo
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Metodo no permitido']);
        exit();
    }

    if (empty($_POST['registros'])) {
        echo json_encode(['success' => false, 'error' => 'No se recibieron registros']);
        exit();
    }

    $observaciones_inv = $_POST['observaciones_inv'] ?? '';
    $data = json_decode($_POST['registros'], true);

    if (!is_array($data) || count($data) === 0) {
        throw new Exception('Formato de datos invalido');
    }

    $id_requisicion = $data[0]['id_requisicion'] ?? null;
    if (!$id_requisicion) {
        throw new Exception("No se pudo determinar id_requisicion");
    }

    $conn->beginTransaction();

    /* =========================
       PREPARED STATEMENTS
    ========================= */

    $stmtControl = $conn->prepare("
        UPDATE control_almacen
        SET mm_retorno = :mm_retorno
        WHERE id_control = :id_control
    ");

    // inventario con stock
    $stmtInvStock = $conn->prepare("
        UPDATE inventario_cnc
        SET stock = :stock,
            pre_stock = :pre_stock,
            estatus = 'Disponible para cotizar',
            updated_at = NOW()
        WHERE lote_pedimento = :lote
    ");

    // inventario solo estatus
    $stmtInvStatus = $conn->prepare("
        UPDATE inventario_cnc
        SET estatus = 'Disponible para cotizar',
            updated_at = NOW()
        WHERE lote_pedimento = :lote
    ");

    // Obtener datos de control
    $stmtGetControl = $conn->prepare("
        SELECT lote_pedimento, lp_remplazo, es_remplazo
        FROM control_almacen
        WHERE id_control = :id_control
        FOR UPDATE
    ");

    /* =========================
       PROCESO PRINCIPAL
    ========================= */

    foreach ($data as $fila) {

        if (!isset($fila['id_control'], $fila['mm_retorno'])) {
            throw new Exception("Faltan campos obligatorios en un registro");
        }

        $id_control = (int)$fila['id_control'];
        $mm_retorno = (float)$fila['mm_retorno'];

        // Actualizar control_almacen
        $stmtControl->execute([
            ':mm_retorno' => $mm_retorno,
            ':id_control' => $id_control
        ]);

        // Obtener info real del control
        $stmtGetControl->execute([':id_control' => $id_control]);
        $control = $stmtGetControl->fetch(PDO::FETCH_ASSOC);

        if (!$control) {
            throw new Exception("No existe control_almacen {$id_control}");
        }

        if ((int)$control['es_remplazo'] == 1) {

            if (empty($control['lp_remplazo'])) {
                throw new Exception("Reemplazo sin lp_remplazo en control {$id_control}");
            }

            // Barra de reemplazo → stock
            $stmtInvStock->execute([
                ':stock'     => $mm_retorno,
                ':pre_stock' => $mm_retorno,
                ':lote'      => $control['lp_remplazo']
            ]);

            // Barra original → solo estatus
            $stmtInvStatus->execute([
                ':lote' => $control['lote_pedimento']
            ]);

        } else {
            // No reemplazo → barra original consume stock
            $stmtInvStock->execute([
                ':stock'     => $mm_retorno,
                ':pre_stock' => $mm_retorno,
                ':lote'      => $control['lote_pedimento']
            ]);
        }
    }

    /* =========================
       REQUISICION (Lógica de Estatus)
    ========================= */

    // 1. Primero consultamos el estatus actual
    $stmtCheckStatus = $conn->prepare("SELECT estatus FROM requisiciones WHERE id_requisicion = :id");
    $stmtCheckStatus->execute([':id' => $id_requisicion]);
    $currentStatus = $stmtCheckStatus->fetchColumn();

    if ($currentStatus === 'Detenida') {
        // Si está detenida, solo actualizamos observaciones, mantenemos estatus y no ponemos fin_maquinado
        $stmtReq = $conn->prepare("
            UPDATE requisiciones 
            SET observaciones_inv = :observaciones, 
            fecha_retorno_barras = NOW() 
            WHERE id_requisicion = :id
        ");
        $paramsReq = [
            ':observaciones' => $observaciones_inv,
            ':id' => $id_requisicion
        ];
    } else {
        // Si no está detenida, procedemos con el flujo normal (Completada + NOW)
        $stmtReq = $conn->prepare("
            UPDATE requisiciones 
            SET estatus = 'Completada',
            observaciones_inv = :observaciones, 
            fecha_retorno_barras = NOW() 
            WHERE id_requisicion = :id
        ");
        $paramsReq = [
            ':observaciones' => $observaciones_inv,
            ':id' => $id_requisicion
        ];
    }

    $stmtReq->execute($paramsReq);

    // Nota: Eliminamos el rowCount() == 0 check si es posible que no haya cambios en observaciones,
    // o simplemente verificamos que el query no falló.

    if ($stmtReq->rowCount() == 0) {
        throw new Exception("No se pudo actualizar requisicion {$id_requisicion}");
    }

    /* =========================
       MENSAJES DE LOTES (CASI IGUAL)
    ========================= */

    $stmtLP = $conn->prepare("
        SELECT lote_pedimento, lp_remplazo, es_remplazo
        FROM control_almacen
        WHERE id_requisicion = :id
    ");
    $stmtLP->execute([':id' => $id_requisicion]);
    $arrayLP = $stmtLP->fetchAll();

    $missingLotes = [];
    $alreadyEnabled = [];

    foreach ($arrayLP as $LP) {

        $lote = ((int)$LP['es_remplazo'] == 1)
              ? trim($LP['lp_remplazo'])
              : trim($LP['lote_pedimento']);

        $stmtCheck = $conn->prepare("
            SELECT estatus FROM inventario_cnc
            WHERE lote_pedimento = :lote LIMIT 1
        ");
        $stmtCheck->execute([':lote' => $lote]);
        $registro = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$registro) {
            $missingLotes[] = $lote;
            continue;
        }

        if ($registro['estatus'] == 'Disponible para cotizar') {
            $alreadyEnabled[] = $lote;
        }
    }

    $msjLotes = '';
    if ($missingLotes) {
        $msjLotes .= 'No se encontraron las siguientes barras: ' . implode(', ', $missingLotes) . '. ';
    }
    if ($alreadyEnabled) {
        $msjLotes .= 'Las siguientes barras ya estaban habilitadas: ' . implode(', ', $alreadyEnabled) . '. ';
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Stock actualizado correctamente en inventario CNC. Billets habilitados para cotizar. ' . $msjLotes
    ]);

} catch (Throwable $e) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);

} finally {
    $conn = null;
}
