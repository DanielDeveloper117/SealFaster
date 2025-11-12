<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}

try {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Metodo no permitido']);
        exit();
    }

    if (!isset($_POST['registros']) || empty($_POST['registros'])) {
        echo json_encode(['success' => false, 'error' => 'No se recibieron registros']);
        exit();
    }

    $data = json_decode($_POST['registros'], true);

    if (!is_array($data) || count($data) === 0) {
        echo json_encode(['success' => false, 'error' => 'Formato de datos invalido']);
        exit();
    }

    $conn->beginTransaction();

    $sqlUpdate = "UPDATE control_almacen 
                  SET perfil_sello = :perfil_sello,
                      pz_maquinadas = :pz_maquinadas,
                      altura_pz = :altura_pz,
                      mm_usados = :mm_usados,
                      total_sellos = :total_sellos,
                      merma_corte = :merma_corte,
                      scrap_pz = :scrap_pz,
                      scrap_mm = :scrap_mm,
                      mm_total_usados = :mm_total_usados,
                      mm_teoricos = :mm_teoricos,
                      mm_merma_real = :mm_merma_real,
                      id_cotizacion = :id_cotizacion,
                      id_estimacion = :id_estimacion,
                      pz_teoricas = :pz_teoricas,
                      causa_merma = :causa_merma,
                      justificacion_merma = :justificacion_merma
                  WHERE id_control = :id_control";
    $stmtUpdate = $conn->prepare($sqlUpdate);

    $registrosActualizados = 0;
    $errores = [];

    foreach ($data as $fila) {
        try {
            $stmtUpdate->execute([
                ':perfil_sello' => $fila['perfil_sello'] ?? '',
                ':pz_maquinadas' => $fila['pz_maquinadas'] ?? 0,
                ':altura_pz' => $fila['altura_pz'] ?? 0,
                ':mm_usados' => $fila['mm_usados'] ?? 0,
                ':total_sellos' => $fila['total_sellos'] ?? 0,
                ':merma_corte' => $fila['merma_corte'] ?? 0,
                ':scrap_pz' => $fila['scrap_pz'] ?? 0,
                ':scrap_mm' => $fila['scrap_mm'] ?? 0,
                ':mm_total_usados' => $fila['mm_total_usados'] ?? 0,
                ':mm_teoricos' => $fila['mm_teoricos'] ?? 0,
                ':mm_merma_real' => $fila['mm_merma_real'] ?? 0,
                ':id_cotizacion' => $fila['id_cotizacion'] ?? null,
                ':id_estimacion' => $fila['id_estimacion'] ?? null,
                ':pz_teoricas' => $fila['pz_teoricas'] ?? 0,
                ':causa_merma' => $fila['causa_merma'] ?? '',
                ':justificacion_merma' => $fila['justificacion_merma'] ?? '',
                ':id_control' => $fila['id_control']
            ]);

            if ($stmtUpdate->rowCount() > 0) {
                $registrosActualizados++;
            }
        } catch (PDOException $e) {
            $errores[] = "Error al actualizar id_control {$fila['id_control']}: " . $e->getMessage();
        }
    }

    $conn->commit();

    $mensaje = "Progreso guardado correctamente. Registros actualizados: $registrosActualizados";
    if (count($errores) > 0) {
        $mensaje .= ". Errores: " . implode(', ', $errores);
    }

    echo json_encode([
        'success' => true,
        'message' => $mensaje,
        'registros_actualizados' => $registrosActualizados,
        'errores' => $errores
    ]);

} catch (Throwable $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'error' => 'Error al guardar progreso: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>