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

    // Validar parametros
    if (!isset($_POST['registros']) || empty($_POST['registros'])) {
        echo json_encode(['success' => false, 'error' => 'No se recibieron registros']);
        exit();
    }

    // Decodificar JSON
    $data = json_decode($_POST['registros'], true);

    // Tomar id_requisicion de la primera fila
    $id_requisicion = $data[0]['id_requisicion'] ?? null;
    if (!$id_requisicion) {
        throw new Exception("No se pudo determinar id_requisicion");
    }

    if (!is_array($data) || count($data) === 0) {
        echo json_encode(['success' => false, 'error' => 'Formato de datos invalido']);
        exit();
    }

    // Iniciar transaccion
    $conn->beginTransaction();

    // Preparar statements
    $sqlUpdateControl = "UPDATE control_almacen 
                         SET mm_retorno = :mm_retorno 
                         WHERE id_control = :id_control";
    $stmtControl = $conn->prepare($sqlUpdateControl);

    $sqlUpdateInventario = "UPDATE inventario_cnc 
                            SET stock = :stock, pre_stock = :pre_stock,
                                updated_at = NOW() 
                            WHERE lote_pedimento = :lote_pedimento";
    $stmtInventario = $conn->prepare($sqlUpdateInventario);

    foreach ($data as $fila) {
        // Validaciones por registro
        if (!isset($fila['id_control']) || !isset($fila['mm_retorno']) || !isset($fila['lote_pedimento'])) {
            throw new Exception("Faltan campos obligatorios en un registro");
        }

        $id_control = (int)$fila['id_control'];
        //$mm_retorno = (float)$fila['mm_retorno'];
        $mm_retorno = isset($fila['mm_retorno']) ? (float)$fila['mm_retorno'] : 0.00;
        $lote_pedimento = trim($fila['lote_pedimento']);

        // Actualizar control_almacen
        $stmtControl->execute([
            ':mm_retorno' => $mm_retorno,
            ':id_control' => $id_control
        ]);

        // if ($stmtControl->rowCount() === 0) {
        //     throw new Exception("No se actualizo control_almacen con id_control {$id_control}");
        // }

        // Actualizar inventario_cnc
        $stmtInventario->execute([
            ':stock' => $mm_retorno,
            ':pre_stock' => $mm_retorno,
            ':lote_pedimento' => $lote_pedimento
        ]);

        // if ($stmtInventario->rowCount() === 0) {
        //     throw new Exception("No se encontro lote_pedimento {$lote_pedimento} en inventario CNC");
        // }
    }

    
    // Actualizar requisicion
    $sqlRequisicion = "UPDATE requisiciones 
                       SET estatus = 'Completada', fin_maquinado = NOW() 
                       WHERE id_requisicion = :id_requisicion";
    $stmtRequisicion = $conn->prepare($sqlRequisicion);
    $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtRequisicion->execute();

    if ($stmtRequisicion->rowCount() === 0) {
        throw new Exception("No se pudo actualizar requisicion {$id_requisicion}");
    }

    $sqlLotesPedimento = "SELECT * FROM control_almacen WHERE id_requisicion = :id_requisicion";
    $stmtLP = $conn->prepare($sqlLotesPedimento);
    $stmtLP->bindParam(':id_requisicion', $id_requisicion);
    $stmtLP->execute();
    $arrayLP = $stmtLP->fetchAll();

    $missingLotes = [];
    $alreadyEnabled = [];
    $updatedLotes = 0;

    foreach ($arrayLP as $LP) {
        $lote = trim($LP['lote_pedimento']);

        // 1. Verificar existencia y estado actual
        $sqlCheck = "SELECT estatus FROM inventario_cnc WHERE lote_pedimento = :lote_pedimento LIMIT 1";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':lote_pedimento', $lote);
        $stmtCheck->execute();

        $registro = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$registro) {
            // No existe el lote
            $missingLotes[] = $lote;
            continue;
        }

        if ($registro['estatus'] === 'Habilitado') {
            // Ya estaba habilitado
            $alreadyEnabled[] = $lote;
            continue;
        }

        // 2. Actualizar solo si estaba deshabilitado
        $sqlEstatusLP = "UPDATE inventario_cnc 
                         SET estatus = 'Habilitado'
                         WHERE lote_pedimento = :lote_pedimento";
        $stmtEstatusLP = $conn->prepare($sqlEstatusLP);
        $stmtEstatusLP->bindParam(':lote_pedimento', $lote);
        $stmtEstatusLP->execute();

        if ($stmtEstatusLP->rowCount() > 0) {
            $updatedLotes++;
        }
    }

    // Construir mensajes detallados
    $msjLotes = "";
    if (count($missingLotes) > 0) {
        $msjLotes .= "No se encontraron las siguientes barras: " . implode(', ', $missingLotes) . ". ";
    }
    if (count($alreadyEnabled) > 0) {
        $msjLotes .= "Las siguientes barras ya estaban habilitadas: " . implode(', ', $alreadyEnabled) . ". ";
    }

    // Confirmar transaccion
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Stock actualizado correctamente en inventario CNC. Billets habilitados para cotizar. ' . $msjLotes
    ]);


} catch (Throwable $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn = null;
}
