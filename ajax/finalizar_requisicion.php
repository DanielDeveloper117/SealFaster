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

    $data = json_decode($_POST['registros'], true);
    if (!is_array($data)) {
        echo json_encode(['success' => false, 'error' => 'Formato de datos invalido']);
        exit();
    }

    // Iniciar transaccion
    $conn->beginTransaction();

    $sqlUpdate = "UPDATE control_almacen 
                  SET mm_salida = :mm_salida,
                      total_sellos = :total_sellos,
                      merma_corte = :merma_corte,
                      scrap_pz = :scrap_pz,
                      scrap_mm = :scrap_mm
                  WHERE id_control = :id_control";
    $stmtUpdate = $conn->prepare($sqlUpdate);

    $id_requisicion = null;

    foreach ($data as $fila) {
        if (!isset($fila['id_control'])) {
            throw new Exception("Falta id_control en un registro");
        }

        $stmtUpdate->execute([
            ':mm_salida'    => $fila['mm_salida'] ?? 0,
            ':total_sellos' => $fila['total_sellos'] ?? 0,
            ':merma_corte'  => $fila['merma_corte'] ?? 0,
            ':scrap_pz'     => $fila['scrap_pz'] ?? 0,
            ':scrap_mm'     => $fila['scrap_mm'] ?? 0,
            ':id_control'   => $fila['id_control']
        ]);

        if ($stmtUpdate->rowCount() === 0) {
            throw new Exception("No se pudo actualizar control_almacen con id_control {$fila['id_control']}");
        }

        // Tomar la requisicion desde el primer registro
        if ($id_requisicion === null) {
            $sqlGetReq = "SELECT id_requisicion FROM control_almacen WHERE id_control = :id_control LIMIT 1";
            $stmtGetReq = $conn->prepare($sqlGetReq);
            $stmtGetReq->bindParam(':id_control', $fila['id_control'], PDO::PARAM_INT);
            $stmtGetReq->execute();
            $row = $stmtGetReq->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $id_requisicion = $row['id_requisicion'];
            } else {
                throw new Exception("No se encontro requisicion asociada al id_control {$fila['id_control']}");
            }
        }
    }

    if (!$id_requisicion) {
        throw new Exception("No se pudo determinar la requisicion a finalizar");
    }

    // Actualizar requisicion
    $sqlRequisicion = "UPDATE requisiciones 
                       SET estatus = 'Finalizada', fin_maquinado = NOW() 
                       WHERE id_requisicion = :id_requisicion";
    $stmtRequisicion = $conn->prepare($sqlRequisicion);
    $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtRequisicion->execute();

    if ($stmtRequisicion->rowCount() === 0) {
        throw new Exception("No se pudo actualizar requisicion {$id_requisicion}");
    }

    // Confirmar transaccion
    $conn->commit();

    // Traer cotizaciones para respuesta
    $sqlCot = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
    $stmtCot = $conn->prepare($sqlCot);
    $stmtCot->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtCot->execute();
    $cot = $stmtCot->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Requisicion finalizada correctamente',
        'cotizaciones' => $cot['cotizaciones'] ?? null
    ]);

} catch (Throwable $e) {
    // Revertir si fallo algo
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn = null;
}
