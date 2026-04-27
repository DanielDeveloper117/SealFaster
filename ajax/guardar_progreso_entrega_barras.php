<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

function esDecimalValido($valor) {
    if ($valor === '' || $valor === null) return true;
    return preg_match('/^\d+(\.\d{1,2})?$/', (string)$valor);
}

try {
    header('Content-Type: application/json');
    $data = json_decode($_POST['registros'] ?? '[]', true);

    if (empty($data)) {
        echo json_encode(['success' => false, 'error' => 'No se recibieron registros']);
        exit();
    }

    $conn->beginTransaction();
    $registrosActualizados = 0;
    $registrosOmitidos = 0;
    $errores = [];

    foreach ($data as $fila) {
        try {
            $id_control = isset($fila['id_control']) ? (int)$fila['id_control'] : 0;
            $pz_raw = $fila['pz_teoricas'] ?? '';
            $mm_raw = $fila['mm_entrega'] ?? '';

            // EVALUACIÓN DE CAMBIOS: ¿Se envió al menos uno de los dos?
            $tiene_pz = ($pz_raw !== '' && $pz_raw !== null);
            $tiene_mm = ($mm_raw !== '' && $mm_raw !== null);

            if (!$tiene_pz && !$tiene_mm) {
                $registrosOmitidos++;
                continue;
            }

            // CONSTRUCCIÓN DINÁMICA DE LA CONSULTA
            $campos = [];
            $params = [':id' => $id_control];

            if ($tiene_pz) {
                $campos[] = "pz_teoricas = :pz";
                $params[':pz'] = (int)$pz_raw;
            }
            if ($tiene_mm && esDecimalValido($mm_raw)) {
                $campos[] = "mm_entrega = :mm";
                $params[':mm'] = (float)$mm_raw;
            }

            if (!empty($campos)) {
                $sql = "UPDATE control_almacen SET " . implode(', ', $campos) . " WHERE id_control = :id";
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                
                if ($stmt->rowCount() > 0) {
                    $registrosActualizados++;
                }
            }

        } catch (PDOException $e) {
            $errores[] = "ID $id_control: " . $e->getMessage();
        }
    }

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => "Barras actualizadas: $registrosActualizados",
        'actualizados' => $registrosActualizados,
        'errores' => $errores
    ]);

} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}