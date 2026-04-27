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

                $lote_pedimento = ''; $clave = ''; $medida = '';
                $pz_teoricas = 0; $di_sello = null; $de_sello = null;

                // Regex mejorada para capturar lote, clave, medida y piezas
                if (preg_match('/^([A-Z0-9\-\.]+)\s+([A-Z0-9\-\.]+)\s+\(([^)]+)\)\s+(\d+)\s+pz$/i', $billetItem, $matches)) {
                    $lote_pedimento = trim($matches[1]);
                    $clave = trim($matches[2]);
                    $medida = trim($matches[3]);
                    $pz_teoricas = intval($matches[4]);
                    if (preg_match('/(\d+)\s*\/\s*(\d+)/', $medida, $m)) {
                        $di_sello = floatval($m[1]); $de_sello = floatval($m[2]);
                    }
                }

                if ($di_sello === null) $di_sello = $cotizacion['di_sello'];
                if ($de_sello === null) $de_sello = $cotizacion['de_sello'];

                // Creamos una llave única por barra para comparar
                $unique_key = $cotizacion['id_cotizacion'] . '_' . $lote_pedimento;

                $registros[$unique_key] = [
                    'id_estimacion' => $cotizacion['id_estimacion'],
                    'id_cotizacion' => $cotizacion['id_cotizacion'],
                    'perfil_sello' => $cotizacion['perfil_sello'],
                    'componente' => $cotizacion['cantidad_material'],
                    'material' => $cotizacion['material'],
                    'clave' => $clave,
                    'lote_pedimento' => $lote_pedimento,
                    'medida' => $medida,
                    'pz_teoricas' => $pz_teoricas,
                    'di_sello' => $di_sello,
                    'de_sello' => $de_sello,
                    'a_sello' => $cotizacion['a_sello'],
                    'h_componente' => $cotizacion['altura']
                ];
            }
        }
        return $registros;
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
    $esperados = procesarCotizacionesARegistros($cotizacionesData);

    // 3. Obtener qué registros EXISTEN actualmente en control_almacen
    $stmtActual = $conn->prepare("SELECT * FROM control_almacen WHERE id_requisicion = ? AND NOT (es_eliminacion = 1 AND es_eliminacion_auth = 1)");
    $stmtActual->execute([$id_requisicion]);
    $actualesEnDB = $stmtActual->fetchAll(PDO::FETCH_ASSOC);

    $existentesMapping = [];
    foreach ($actualesEnDB as $row) {
        // Llave: id_cotizacion + lote_pedimento
        $key = $row['id_cotizacion'] . '_' . $row['lote_pedimento'];
        $existentesMapping[$key] = $row;
    }

    // 4. Lógica de Sincronización Incremental (SIN DELETE BRUTO)
    $conn->beginTransaction();
    $agregados = 0; $eliminados = 0;

    // A. INSERTAR lo que falta
    $sqlInsert = "INSERT INTO control_almacen (id_requisicion, id_estimacion, id_cotizacion, perfil_sello, componente, material, clave, lote_pedimento, medida, pz_teoricas, di_sello, de_sello, altura_pz, h_componente, fecha_registro) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmtIns = $conn->prepare($sqlInsert);

    foreach ($esperados as $key => $reg) {
        if (!isset($existentesMapping[$key])) {
            $stmtIns->execute([
                $id_requisicion, $reg['id_estimacion'], $reg['id_cotizacion'], $reg['perfil_sello'], 
                $reg['componente'], $reg['material'], $reg['clave'], $reg['lote_pedimento'], 
                $reg['medida'], $reg['pz_teoricas'], $reg['di_sello'], $reg['de_sello'], 
                $reg['a_sello'], $reg['h_componente']
            ]);
            $agregados++;
        }
    }

    // B. ELIMINAR lo que ya no está en la requisición (pero solo lo que sobra)
    foreach ($existentesMapping as $key => $row) {
        if (!isset($esperados[$key])) {
            $stmtDel = $conn->prepare("DELETE FROM control_almacen WHERE id_control = ?");
            $stmtDel->execute([$row['id_control']]);
            $eliminados++;
        }
    }

    $conn->commit();

    // 5. Retornar los datos finales (ya sincronizados)
    $stmtFinal = $conn->prepare("SELECT * FROM control_almacen WHERE id_requisicion = ? AND NOT (es_eliminacion = 1 AND es_eliminacion_auth = 1) ORDER BY id_control ASC");
    $stmtFinal->execute([$id_requisicion]);
    $resultadoFinal = $stmtFinal->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'id_requisicion' => $id_requisicion,
        'total_registros' => count($resultadoFinal),
        'billets' => $resultadoFinal,
        'sincronizacion' => [
            'nuevos_agregados' => $agregados,
            'obsoletos_eliminados' => $eliminados,
            'preservados' => count($resultadoFinal) - $agregados
        ]
    ]);

} catch (Exception $e) {
    if (isset($conn)) $conn->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}