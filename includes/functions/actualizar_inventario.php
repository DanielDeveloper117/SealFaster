<?php
require_once(__DIR__ . '/../../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
/**
 * functions/actualizar_inventario.php
 * 
 * Funciones para actualización automática de inventario_cnc
 * cuando se inserta o actualiza un registro de parametros (claves SRS).
 */

/**
 * Mapea un nombre de material a su equivalente en inventario_cnc
 */
function mapearMaterial($materialParametros) {
    $mapeoMateriales = [
        "PU ROJO"    => "H-ECOPUR",
        "H-ECOPUR"   => "H-ECOPUR",
        "SILICON"    => "ECOSIL",
        "ECOSIL"     => "ECOSIL",
        "NITRILO"    => "ECORUBBER 1",
        "ECORUBBER 1"=> "ECORUBBER 1",
        "VITON"      => "ECORUBBER 2",
        "ECORUBBER 2"=> "ECORUBBER 2",
        "EPDM"       => "ECORUBBER 3",
        "ECORUBBER 3"=> "ECORUBBER 3",
        "PU VERDE"   => "ECOPUR",
        "ECOPUR"     => "ECOPUR",
        "ECOTAL"     => "ECOTAL",
        "ECOMID"     => "ECOMID",
        "VIRGEN"     => "ECOFLON 1",
        "ECOFLON 1"  => "ECOFLON 1",
        "NIKEL"      => "ECOFLON 2",
        "MOLLY"      => "ECOFLON 2",
        "BRONCE"     => "ECOFLON 3",
        "ECOFLON 3"  => "ECOFLON 3"
    ];

    $materialUpper = strtoupper($materialParametros);
    foreach ($mapeoMateriales as $patron => $materialMapeado) {
        if (stripos($materialUpper, $patron) !== false) {
            return $materialMapeado;
        }
    }

    return $materialParametros;
}

function logInventario($msg) {
    if (!defined('ROOT_PATH')) {
        $path = __DIR__ . '/../../debug';
    } else {
        $path = ROOT_PATH . 'debug';
    }
    if (!is_dir($path)) mkdir($path, 0755, true);
    error_log(date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, 3, $path . '/actualizar_inventario.log');
}

/**
 * Actualiza automáticamente los registros de inventario_cnc basándose en
 * un registro de parametros (clave o clave_alterna).
 *
 * Estatus protegidos (NO se actualizan): En uso, Maquinado en curso, Venta, Traspaso, Eliminado
 *
 * @param PDO   $conn           Conexión a base de datos
 * @param int   $parametro_id  ID del registro en parametros
 * @param array $parametro_data (clave, clave_alterna, material, proveedor, interior, exterior, max_usable)
 * @return array ['success' => bool, 'actualizados' => int, 'mensaje' => string]
 */
function actualizarInventarioCnc($conn, $parametro_id, $parametro_data) {
    $ctx = "[actualizarInventarioCnc|id=$parametro_id]";

    try {
        // ── Extraer y sanitizar datos de entrada ─────────────────────────────
        $clave         = trim(str_replace("\0", '', $parametro_data['clave']         ?? ''));
        $clave_alterna = trim(str_replace("\0", '', $parametro_data['clave_alterna'] ?? ''));
        $material      = trim(str_replace("\0", '', $parametro_data['material']      ?? ''));
        $proveedor     = trim(str_replace("\0", '', $parametro_data['proveedor']     ?? ''));
        $interior      = (int)($parametro_data['interior']   ?? 0);
        $exterior      = (int)($parametro_data['exterior']   ?? 0);
        $max_usable    = $parametro_data['max_usable']    ?? 0;

        $material_mapeado = mapearMaterial($material);

        logInventario("$ctx clave='$clave' clave_alterna='$clave_alterna' material='$material' → mapeado='$material_mapeado' interior=$interior exterior=$exterior");

        // ── Estatus protegidos ────────────────────────────────────────────────
        $estatus_protegidos   = ['En uso', 'Maquinado en curso', 'Venta', 'Traspaso', 'Eliminado'];
        $placeholders_estatus = implode(',', array_fill(0, count($estatus_protegidos), '?'));

        // ── Construir WHERE ───────────────────────────────────────────────────
        $where_parts  = [];
        $where_params = [];

        if ($clave !== '') {
            $where_parts[]  = 'Clave = ?';
            $where_params[] = $clave;
        }
        if ($clave_alterna !== '') {
            $where_parts[]  = 'Clave = ?';
            $where_params[] = $clave_alterna;
        }

        if (empty($where_parts)) {
            logInventario("$ctx SKIP: no hay clave ni clave_alterna.");
            return ['success' => false, 'actualizados' => 0, 'mensaje' => 'No hay clave ni clave_alterna para actualizar inventario.'];
        }

        $where_sql = count($where_parts) > 1
            ? '(' . implode(' OR ', $where_parts) . ')'
            : $where_parts[0];

        // ── SELECT: cuántos registros son candidatos ──────────────────────────
        $sql_select = "SELECT id, Clave, estatus FROM inventario_cnc
                        WHERE $where_sql
                          AND estatus NOT IN ($placeholders_estatus)";

        logInventario("$ctx SELECT SQL: $sql_select | params claves=[" . implode(', ', $where_params) . "] | estatus_excluidos=[" . implode(', ', $estatus_protegidos) . "]");

        $stmt_select = $conn->prepare($sql_select);
        $param_index = 1;
        foreach ($where_params as $p) {
            $stmt_select->bindValue($param_index++, $p);
        }
        foreach ($estatus_protegidos as $e) {
            $stmt_select->bindValue($param_index++, $e);
        }
        $stmt_select->execute();
        $registros_a_actualizar = $stmt_select->fetchAll(PDO::FETCH_ASSOC);

        $n_encontrados = count($registros_a_actualizar);
        logInventario("$ctx SELECT resultado: $n_encontrados registro(s) candidatos.");

        if ($n_encontrados > 0) {
            $ids_encontrados = array_column($registros_a_actualizar, 'id');
            $estatus_encontrados = array_column($registros_a_actualizar, 'estatus');
            logInventario("$ctx IDs encontrados: [" . implode(', ', $ids_encontrados) . "] | estatus: [" . implode(', ', $estatus_encontrados) . "]");
        }

        // ── Verificar también cuántos tienen estatus protegido (para diagnóstico) ──
        $sql_protegidos = "SELECT COUNT(*) FROM inventario_cnc
                            WHERE $where_sql
                              AND estatus IN ($placeholders_estatus)";
        $stmt_prot = $conn->prepare($sql_protegidos);
        $pi = 1;
        foreach ($where_params as $p) { $stmt_prot->bindValue($pi++, $p); }
        foreach ($estatus_protegidos as $e) { $stmt_prot->bindValue($pi++, $e); }
        $stmt_prot->execute();
        $n_protegidos = (int)$stmt_prot->fetchColumn();
        if ($n_protegidos > 0) {
            logInventario("$ctx INFO: $n_protegidos registro(s) con estatus protegido fueron omitidos.");
        }

        if (empty($registros_a_actualizar)) {
            $sql_any = "SELECT COUNT(*) FROM inventario_cnc WHERE $where_sql";
            $stmt_any = $conn->prepare($sql_any);
            $pi2 = 1;
            foreach ($where_params as $p) { $stmt_any->bindValue($pi2++, $p); }
            $stmt_any->execute();
            $n_any = (int)$stmt_any->fetchColumn();
            logInventario("$ctx RESULTADO: 0 candidatos. Registros totales con esa clave en inventario_cnc (sin filtro de estatus): $n_any.");

            $mensaje = count($where_params) > 1
                ? "No hay registros en inventario_cnc con claves (" . implode(', ', $where_params) . ") que no estén protegidos."
                : "No hay registros en inventario_cnc con clave {$where_params[0]} que no estén protegidos.";

            return ['success' => true, 'actualizados' => 0, 'mensaje' => $mensaje];
        }

        // ── UPDATE ────────────────────────────────────────────────────────────
        $sql_update = "UPDATE inventario_cnc SET
                       material   = ?,
                       proveedor  = ?,
                       interior   = ?,
                       exterior   = ?,
                       max_usable = ?,
                       Medida     = CONCAT(?, '/', ?),
                       estatus    = 'Disponible para cotizar',
                       updated_at = NOW()
                       WHERE $where_sql
                         AND estatus NOT IN ($placeholders_estatus)";

        logInventario("$ctx UPDATE SQL: $sql_update");

        $stmt_update = $conn->prepare($sql_update);
        $param_index = 1;
        $stmt_update->bindValue($param_index++, $material_mapeado);
        $stmt_update->bindValue($param_index++, $proveedor);
        $stmt_update->bindValue($param_index++, $interior, PDO::PARAM_INT);
        $stmt_update->bindValue($param_index++, $exterior, PDO::PARAM_INT);
        $stmt_update->bindValue($param_index++, $max_usable);
        $stmt_update->bindValue($param_index++, $interior, PDO::PARAM_INT); // Medida parte 1
        $stmt_update->bindValue($param_index++, $exterior, PDO::PARAM_INT); // Medida parte 2
        foreach ($where_params as $p) {
            $stmt_update->bindValue($param_index++, $p);
        }
        foreach ($estatus_protegidos as $e) {
            $stmt_update->bindValue($param_index++, $e);
        }

        $stmt_update->execute();
        $actualizados = $stmt_update->rowCount();

        logInventario("$ctx UPDATE rowCount=$actualizados.");

        return [
            'success'             => true,
            'actualizados'        => $actualizados,
            'mensaje'             => "Se actualizaron $actualizados registro(s) en inventario_cnc.",
            'registros_afectados' => $registros_a_actualizar
        ];

    } catch (Exception $e) {
        logInventario("[actualizarInventarioCnc|id=$parametro_id] EXCEPTION: " . $e->getMessage());
        return [
            'success'    => false,
            'actualizados' => 0,
            'mensaje'    => 'Error al actualizar inventario: ' . $e->getMessage()
        ];
    }
}

/**
 * Sincroniza inventario_cnc con TODOS los registros activos de parametros
 * en una sola operación SQL (UPDATE con JOIN). Mucho más eficiente que
 * iterar fila a fila.
 *
 * @param PDO $conn
 * @return array ['total_procesados' => int, 'total_actualizados' => int]
 */
function sincronizarInventarioCompleto($conn) {
    logInventario("[sincronizarInventarioCompleto] Iniciando sincronización masiva (JOIN UPDATE)...");

    // ── Contar registros de parametros (para el reporte) ─────────────────────
    $stmtCount = $conn->prepare("SELECT COUNT(*) FROM parametros");
    $stmtCount->execute();
    $total_procesados = (int)$stmtCount->fetchColumn();

    // ── Mapeo de material replicado como CASE SQL ─────────────────────────────
    // El orden de los WHEN importa (misma precedencia que la función PHP mapearMaterial).
    // H-ECOPUR va antes que ECOPUR porque 'H-ECOPUR' contiene la cadena 'ECOPUR'.
    $material_case = "
        CASE
            WHEN UPPER(p.material) LIKE '%PU ROJO%'    THEN 'H-ECOPUR'
            WHEN UPPER(p.material) LIKE '%PU VERDE%'   THEN 'ECOPUR'
            WHEN UPPER(p.material) LIKE '%PTFE VIRGEN%' THEN 'ECOFLON 1'
            WHEN UPPER(p.material) LIKE '%PTFE NIKEL%' THEN 'ECOFLON 2'
            WHEN UPPER(p.material) LIKE '%PTFE MOLLY%' THEN 'ECOFLON 2'
            WHEN UPPER(p.material) LIKE '%PTFE BRONCE%' THEN 'ECOFLON 3'
            WHEN UPPER(p.material) LIKE '%NITRILO%'    THEN 'ECORUBBER 1'
            WHEN UPPER(p.material) LIKE '%VITON%'      THEN 'ECORUBBER 2'
            WHEN UPPER(p.material) LIKE '%EPDM%'       THEN 'ECORUBBER 3'
            WHEN UPPER(p.material) LIKE '%SILICON%'    THEN 'ECOSIL'
            WHEN UPPER(p.material) LIKE '%ECOTAL%'     THEN 'ECOTAL'
            WHEN UPPER(p.material) LIKE '%ECOMID%'     THEN 'ECOMID'
            WHEN UPPER(p.material) LIKE '%NIKEL%'      THEN 'ECOFLON 2'
            WHEN UPPER(p.material) LIKE '%MOLLY%'      THEN 'ECOFLON 2'
            WHEN UPPER(p.material) LIKE '%PA%'         THEN 'ECOMID'
            WHEN UPPER(p.material) LIKE '%H-ECOPUR%'   THEN 'H-ECOPUR'
            WHEN UPPER(p.material) LIKE '%ECOPUR%'     THEN 'ECOPUR'
            WHEN UPPER(p.material) LIKE '%ECOSIL%'     THEN 'ECOSIL'
            WHEN UPPER(p.material) LIKE '%ECORUBBER 1%' THEN 'ECORUBBER 1'
            WHEN UPPER(p.material) LIKE '%ECORUBBER 2%' THEN 'ECORUBBER 2'
            WHEN UPPER(p.material) LIKE '%ECORUBBER 3%' THEN 'ECORUBBER 3'
            ELSE p.material
        END
    ";

    // ── Estatus protegidos ────────────────────────────────────────────────────
    $estatus_protegidos   = ['En uso', 'Maquinado en curso', 'Venta', 'Traspaso', 'Eliminado'];
    $placeholders_estatus = implode(',', array_fill(0, count($estatus_protegidos), '?'));

    // ── JOIN: inventario_cnc.Clave coincide con parametros.clave O parametros.clave_alterna ──
    $sql = "
        UPDATE inventario_cnc ic
        INNER JOIN parametros p
            ON ic.Clave = p.clave
            OR (
                p.clave_alterna IS NOT NULL
                AND p.clave_alterna != ''
                AND ic.Clave = p.clave_alterna
            )
        SET
            ic.material   = $material_case,
            ic.proveedor  = p.proveedor,
            ic.interior   = p.interior,
            ic.exterior   = p.exterior,
            ic.max_usable = p.max_usable,
            ic.Medida     = CONCAT(p.interior, '/', p.exterior),
            ic.estatus    = 'Disponible para cotizar',
            ic.updated_at = NOW()
        WHERE ic.estatus NOT IN ($placeholders_estatus)
    ";

    $stmt = $conn->prepare($sql);
    $param_index = 1;
    foreach ($estatus_protegidos as $e) {
        $stmt->bindValue($param_index++, $e);
    }
    $stmt->execute();
    $total_actualizados = $stmt->rowCount();

    logInventario("[sincronizarInventarioCompleto] Fin: parametros=$total_procesados, inventario_cnc actualizados=$total_actualizados.");

    return [
        'total_procesados'   => $total_procesados,
        'total_actualizados' => $total_actualizados,
    ];
}

/**
 * Sincroniza inventario_cnc SOLO para las claves que fueron provistas.
 * Construye condiciones dinámicas según la elección del usuario ($modo).
 */
function sincronizarInventarioPorClaves($conn, $datos_claves, $modo) {
    if (empty($datos_claves)) {
        return ['total_procesados' => 0, 'total_actualizados' => 0];
    }

    if ($modo === 'sync_ambas') {
        // Ejecución secuencial para evitar OR en los JOINS que causan lock de MySQL (Cartesian/Table Scan)
        $res1 = sincronizarInventarioPorClaves($conn, $datos_claves, 'sync_clave');
        $res2 = sincronizarInventarioPorClaves($conn, $datos_claves, 'sync_alterna');
        return [
             'total_procesados'   => max($res1['total_procesados'], $res2['total_procesados']),
             'total_actualizados' => $res1['total_actualizados'] + $res2['total_actualizados']
        ];
    }

    $claves = [];
    $alternas = [];
    foreach ($datos_claves as $d) {
        if (!empty($d['clave'])) $claves[$d['clave']] = true;
        if (!empty($d['clave_alterna'])) $alternas[$d['clave_alterna']] = true;
    }
    $claves = array_keys($claves);
    $alternas = array_keys($alternas);

    logInventario("[sincronizarInventarioPorClaves] Iniciando sync restrictivo ($modo) con " . count($claves) . " claves principales...");

    $estatus_protegidos   = ['En uso', 'Maquinado en curso', 'Venta', 'Traspaso', 'Eliminado'];
    $placeholders_estatus = implode(',', array_fill(0, count($estatus_protegidos), '?'));

    $join_cond = "";
    $where_cond = "";
    $params_to_bind = [];

    // ¡TRUCO DE ÍNDICES!: Para evitar un Full Table Scan masivo que congele el servidor,
    // SIEMPRE filtramos la tabla parametros a través de `p.clave IN (...)` puesto que
    // `clave` tiene índice principal y `clave_alterna` no.
    if ($modo === 'sync_clave') {
        if (empty($claves)) return ['total_procesados' => 0, 'total_actualizados' => 0];
        $join_cond = "ON ic.Clave = p.clave";
        $p_c = implode(',', array_fill(0, count($claves), '?'));
        $where_cond = "AND p.clave IN ($p_c)";
        $params_to_bind = $claves;
    } else if ($modo === 'sync_alterna') {
        // En lugar de filtrar WHERE p.clave_alterna IN (...) [LENTO], filtramos por p.clave IN (...) [INSTANTÁNEO] 
        // lo que aisla el grupo pequeño de filas, y el JOIN se encarga del match cruzado exacto.
        if (empty($claves) || empty($alternas)) return ['total_procesados' => 0, 'total_actualizados' => 0];
        $join_cond = "ON p.clave_alterna != '' AND ic.Clave = p.clave_alterna";
        $p_c = implode(',', array_fill(0, count($claves), '?'));
        $where_cond = "AND p.clave IN ($p_c)";
        $params_to_bind = $claves;
    } else {
        return ['total_procesados' => 0, 'total_actualizados' => 0]; // Desconocido/Nada
    }

    $material_case = "
        CASE
            WHEN UPPER(p.material) LIKE '%H-ECOPUR%' OR UPPER(p.material) LIKE '%PU ROJO%'   THEN 'H-ECOPUR'
            WHEN UPPER(p.material) LIKE '%SILICON%'  OR UPPER(p.material) LIKE '%ECOSIL%'     THEN 'ECOSIL'
            WHEN UPPER(p.material) LIKE '%NITRILO%'  OR UPPER(p.material) LIKE '%ECORUBBER 1%' THEN 'ECORUBBER 1'
            WHEN UPPER(p.material) LIKE '%VITON%'    OR UPPER(p.material) LIKE '%ECORUBBER 2%' THEN 'ECORUBBER 2'
            WHEN UPPER(p.material) LIKE '%EPDM%'     OR UPPER(p.material) LIKE '%ECORUBBER 3%' THEN 'ECORUBBER 3'
            WHEN UPPER(p.material) LIKE '%ECOTAL%'                                             THEN 'ECOTAL'
            WHEN UPPER(p.material) LIKE '%ECOMID%'                                             THEN 'ECOMID'
            WHEN UPPER(p.material) LIKE '%PU VERDE%' OR UPPER(p.material) LIKE '%ECOPUR%'     THEN 'ECOPUR'
            WHEN UPPER(p.material) LIKE '%VIRGEN%'   OR UPPER(p.material) LIKE '%ECOFLON 1%'  THEN 'ECOFLON 1'
            WHEN UPPER(p.material) LIKE '%NIKEL%'    OR UPPER(p.material) LIKE '%MOLLY%'
              OR UPPER(p.material) LIKE '%ECOFLON 2%'                                          THEN 'ECOFLON 2'
            WHEN UPPER(p.material) LIKE '%BRONCE%'   OR UPPER(p.material) LIKE '%ECOFLON 3%'  THEN 'ECOFLON 3'
            ELSE p.material
        END
    ";

    $sql = "
        UPDATE inventario_cnc ic
        INNER JOIN parametros p $join_cond
        SET
            ic.material   = $material_case,
            ic.proveedor  = p.proveedor,
            ic.interior   = p.interior,
            ic.exterior   = p.exterior,
            ic.max_usable = p.max_usable,
            ic.Medida     = CONCAT(p.interior, '/', p.exterior),
            ic.estatus    = 'Disponible para cotizar',
            ic.updated_at = NOW()
        WHERE ic.estatus NOT IN ($placeholders_estatus)
        $where_cond
    ";

    $stmt = $conn->prepare($sql);
    $param_index = 1;
    foreach ($estatus_protegidos as $e) { $stmt->bindValue($param_index++, $e); }
    foreach ($params_to_bind as $val) { $stmt->bindValue($param_index++, $val); }
    
    $stmt->execute();
    $total_actualizados = $stmt->rowCount();

    logInventario("[sincronizarInventarioPorClaves] Fin: " . count($claves) . " items procesados, inventario_cnc actualizados=$total_actualizados.");

    return [
        'total_procesados'   => count($claves),
        'total_actualizados' => $total_actualizados,
    ];
}

/**
 * @deprecated Usar sincronizarInventarioCompleto para sync masivo.
 */
function actualizarInventarioCncMasivo($conn, $parametros) {
    $total_actualizados = 0;
    $detalles = [];

    foreach ($parametros as $param) {
        $resultado           = actualizarInventarioCnc($conn, $param['id'] ?? 0, $param);
        $total_actualizados += $resultado['actualizados'] ?? 0;
        $detalles[] = [
            'clave'         => $param['clave']         ?? '',
            'clave_alterna' => $param['clave_alterna'] ?? '',
            'actualizados'  => $resultado['actualizados'] ?? 0,
            'mensaje'       => $resultado['mensaje']    ?? ''
        ];
    }

    return [
        'success'            => true,
        'total_actualizados' => $total_actualizados,
        'detalles'           => $detalles
    ];
}
?>
