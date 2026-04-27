<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    if (!isset($_GET['id_requisicion'])) {
        echo json_encode(['success' => false, 'error' => 'Falta parámetro: id_requisicion']);
        exit;
    }

    $id_requisicion = intval($_GET['id_requisicion']);

    // INICIALIZACIÓN DE VARIABLES (Crucial para evitar Warnings)
    $agregados = 0;
    $eliminados = 0;
    $barrasProtegidas = 0;
    $hayDiscrepancia = false;

    // ============================================================
    // FUNCIONES AUXILIARES (MODIFICADAS)
    // ============================================================

    function procesarCotizacionesARegistros($cotizaciones) {
        $registros = [];
        foreach ($cotizaciones as $cotizacion) {
            if (empty($cotizacion['billets_claves_lotes'])) continue;

            $billets = explode(',', $cotizacion['billets_claves_lotes']);
            foreach ($billets as $billetItem) {
                $billetItem = trim($billetItem);
                if (empty($billetItem)) continue;

                /**
                 * EXPLICACIÓN DEL REGEX AJUSTADO:
                 * ^(\S+)           -> 1. Lote (Hasta el primer espacio)
                 * \s+              -> Espacio
                 * (.*?)            -> 2. Clave (Texto hasta encontrar el paréntesis)
                 * \s* -> Espacios opcionales
                 * \((.*?)\)        -> 3. Medida (Lo que esté dentro de los paréntesis)
                 * \s+              -> Espacio
                 * (\d+)\s*pz$      -> 4. Cantidad (Número antes de "pz")
                 */
                $pattern = '/^(\S+)\s+(.*?)\s*\((.*?)\)\s+(\d+)\s*pz$/i';

                if (preg_match($pattern, $billetItem, $matches)) {
                    $registros[] = [
                        'id_cotizacion'   => $cotizacion['id_cotizacion'],
                        'id_estimacion'   => $cotizacion['id_estimacion'],
                        'perfil_sello'    => $cotizacion['perfil_sello'],
                        'componente'      => $cotizacion['cantidad_material'],
                        'material'        => $cotizacion['material'],
                        'altura_pz'         => $cotizacion['a_sello'],
                        'di_sello'        => $cotizacion['diametro_int'],
                        'de_sello'        => $cotizacion['diametro_ext'],
                        'h_componente'    => $cotizacion['altura'],
                        // ASIGNACIÓN CORRECTA SEGÚN TU EXPLICACIÓN:
                        'lote_pedimento'  => trim($matches[1]), // "F1C000038-C25"
                        'clave'           => trim($matches[2]), // "TEF/VIR0/38"
                        'medida'          => trim($matches[3]), // "0/38"
                        'pz_teoricas'     => intval($matches[4]) // 8
                    ];
                } else {
                    // ALERTA: Si el formato no coincide, lanzamos error inmediatamente
                    // Esto evita los registros "fantasma" con campos vacíos.
                    throw new Exception("Formato inválido en la cotización {$cotizacion['id_cotizacion']}. Texto detectado: '$billetItem'. Se esperaba: 'LOTE CLAVE (MEDIDA) CANTIDAD pz'");
                }
            }
        }
        return $registros;
    }
    /**
     * Inserta registros procesados en la tabla control_almacen
     */
    function insertarRegistrosControlAlmacen($conn, $id_requisicion, $registros) {
        $sqlInsert = "INSERT INTO control_almacen (
            id_requisicion, id_estimacion, id_cotizacion, perfil_sello, 
            componente, material, clave, lote_pedimento, medida, 
            pz_teoricas, di_sello, de_sello, altura_pz, h_componente, fecha_registro
        ) VALUES (
            :id_req, :id_est, :id_cot, :perfil, 
            :comp, :mat, :clave, :lote, :medida, 
            :pz_t, :di, :de, :alt, :h_comp, NOW()
        )";
        
        $stmt = $conn->prepare($sqlInsert);
        
        foreach ($registros as $reg) {
            $stmt->execute([
                ':id_req'   => $id_requisicion,
                ':id_est'   => $reg['id_estimacion'],
                ':id_cot'   => $reg['id_cotizacion'],
                ':perfil'   => $reg['perfil_sello'],
                ':comp'     => $reg['componente'],
                ':mat'      => $reg['material'],
                ':clave'    => $reg['clave'],
                ':lote'     => $reg['lote_pedimento'],
                ':medida'   => $reg['medida'],
                ':pz_t'     => $reg['pz_teoricas'],
                ':di'       => $reg['di_sello'],
                ':de'       => $reg['de_sello'],
                ':alt'      => $reg['altura_pz'],
                ':h_comp'   => $reg['h_componente']
            ]);
        }
    }
    // ============================================================
    // PROCESAMIENTO
    // ============================================================

    // 1. Obtener cotizaciones ligadas a la requisición
    $stmtReq = $conn->prepare("SELECT cotizaciones FROM requisiciones WHERE id_requisicion = ? LIMIT 1");
    $stmtReq->execute([$id_requisicion]);
    $requisicion = $stmtReq->fetch(PDO::FETCH_ASSOC);

    if (!$requisicion || empty($requisicion['cotizaciones'])) {
        echo json_encode(['success' => true, 'billets' => [], 'message' => 'Sin cotizaciones']);
        exit;
    }

    $cotizacion_ids = explode(', ', $requisicion['cotizaciones']);
    $placeholders = implode(',', array_fill(0, count($cotizacion_ids), '?'));
    $stmtCot = $conn->prepare("SELECT * FROM cotizacion_materiales WHERE id_cotizacion IN ($placeholders)");
    $stmtCot->execute($cotizacion_ids);
    $cotizacionesData = $stmtCot->fetchAll(PDO::FETCH_ASSOC);

// 2. Determinar qué registros DEBEN existir (Esperados)
$esperadosRaw = procesarCotizacionesARegistros($cotizacionesData);
$registros_unicos_esperados = [];

// IMPORTANTE: Mapear los esperados con la misma llave compuesta que los existentes
foreach ($esperadosRaw as $reg) {
    $key = $reg['id_cotizacion'] . '_' . $reg['lote_pedimento'];
    $registros_unicos_esperados[$key] = $reg;
}

// 3. Obtener registros EXISTENTES en control_almacen
// (Mantenemos tu lógica de filtrado para proteger extras)
$stmtActual = $conn->prepare("SELECT * FROM control_almacen WHERE id_requisicion = :id_req AND NOT (es_eliminacion = 1 AND es_eliminacion_auth = 1)");
$stmtActual->bindParam(':id_req', $id_requisicion, PDO::PARAM_INT);
$stmtActual->execute();
$actualesEnDB = $stmtActual->fetchAll(PDO::FETCH_ASSOC);

$existentesMapping = [];
$barrasProtegidas = 0;

foreach ($actualesEnDB as $row) {
    if ($row['es_extra'] == 1 || $row['es_remplazo'] == 1 || $row['es_merma'] == 1) {
        $barrasProtegidas++;
        continue;
    }
    // Misma lógica de llave: id_cotizacion + lote
    $key = $row['id_cotizacion'] . '_' . $row['lote_pedimento'];
    $existentesMapping[$key] = $row;
}

// 4. Lógica de Discrepancia (Ahora las llaves coinciden)
$hayDiscrepancia = false;

foreach ($registros_unicos_esperados as $key => $esperado) {
    if (!isset($existentesMapping[$key])) {
        $hayDiscrepancia = true;
        break;
    }
}

if (!$hayDiscrepancia) {
    foreach ($existentesMapping as $key => $actual) {
        if (!isset($registros_unicos_esperados[$key])) {
            $hayDiscrepancia = true;
            break;
        }
    }
}

    // 5. Sincronización Incremental (Si hay discrepancia)
    if ($hayDiscrepancia) {
        $conn->beginTransaction();
        try {
            // A. Insertar solo lo que falta de la cotización
            foreach ($registros_unicos_esperados as $key => $reg) {
                if (!isset($existentesMapping[$key])) {
                    insertarRegistrosControlAlmacen($conn, $id_requisicion, [$reg]);
                    $agregados++;
                }
            }

            // B. Eliminar solo lo que ya no existe en la cotización Y NO ES EXTRA
            foreach ($existentesMapping as $key => $row) {
                if (!isset($registros_unicos_esperados[$key])) {
                    $stmtDel = $conn->prepare("DELETE FROM control_almacen WHERE id_control = ?");
                    $stmtDel->execute([$row['id_control']]);
                    if ($stmtDel->rowCount() > 0) {
                        $eliminados++;
                    }
                }
            }
            $conn->commit();
        } catch (Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            throw $e;
        }
    }

    // 6. Obtener lista final para el frontend
    $stmtFinal = $conn->prepare("SELECT * FROM control_almacen WHERE id_requisicion = ? AND NOT (es_eliminacion = 1 AND es_eliminacion_auth = 1) ORDER BY id_control ASC");
    $stmtFinal->execute([$id_requisicion]);
    $registrosFinales = $stmtFinal->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'billets' => $registrosFinales,
        'sincronizacion' => [
            'nuevos_agregados' => $agregados,
            'obsoletos_eliminados' => $eliminados,
            'extras_protegidos' => $barrasProtegidas
        ]
    ]);

} catch (Throwable $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>