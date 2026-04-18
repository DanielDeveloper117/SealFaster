<?php
/**
 * ajax_constructor_consulta.php
 * 
 * Endpoint dinámico para el constructor de consultas del inventario CNC.
 * Recibe un "step" (1-6) y los filtros acumulados hasta ese paso.
 * Retorna:
 *   - count:           total de registros con esos filtros
 *   - count_con_stock: total excluyendo stock <= 0 (solo en step 5+)
 *   - distinct_next:   opciones para el siguiente selector
 */
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    // ── Parámetros ────────────────────────────────────────────────
    $step        = isset($_GET['step'])            ? (int)$_GET['step']       : 0;
    $almacen_id  = isset($_GET['almacen_id'])       ? trim($_GET['almacen_id']): '';
    $material    = isset($_GET['material'])          ? trim($_GET['material'])  : '';
    $proveedor   = isset($_GET['proveedor'])         ? trim($_GET['proveedor']) : '';
    $estatus     = isset($_GET['estatus'])            ? trim($_GET['estatus'])   : '';
    $medida      = isset($_GET['medida'])             ? trim($_GET['medida'])    : '';
    $omitirSinStock = isset($_GET['omitir_sin_stock']) && $_GET['omitir_sin_stock'] === '1';

    if ($step < 1 || $step > 6 || $almacen_id === '') {
        echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
        exit;
    }

    // ── Construcción dinámica de WHERE ────────────────────────────
    // Base: siempre filtramos por almacen y excluimos archivados
    $where   = "i.almacen_id = :almacen_id AND NOT (i.estatus = 'Eliminado' AND i.solicita_archivado = 1)";
    $params  = [':almacen_id' => $almacen_id];

    // Step 2+: material
    if ($step >= 2 && $material !== '' && $material !== 'all') {
        $where .= " AND i.material = :material";
        $params[':material'] = $material;
    }

    // Step 3+: proveedor
    if ($step >= 3 && $proveedor !== '' && $proveedor !== 'all') {
        $where .= " AND i.proveedor = :proveedor";
        $params[':proveedor'] = $proveedor;
    }

    // Step 4+: estatus
    if ($step >= 4 && $estatus !== '' && $estatus !== 'all') {
        $where .= " AND i.estatus = :estatus";
        $params[':estatus'] = $estatus;
    }

    // Step 5+: medida
    if ($step >= 5 && $medida !== '' && $medida !== 'all') {
        $where .= " AND i.Medida = :medida";
        $params[':medida'] = $medida;
    }

    // Desde el Step 1: omitir sin stock si se indicó (para contar cuántos quedarían con stock > 0)
    $whereConStock = $where;
    if ($step >= 1 && $omitirSinStock) {
        $where .= " AND i.stock > 0";
    }

    // ── COUNT total ──────────────────────────────────────────────
    $sqlCount = "SELECT COUNT(*) AS total FROM inventario_cnc AS i WHERE $where";
    $stmtCount = $conn->prepare($sqlCount);
    foreach ($params as $k => $v) {
        $stmtCount->bindValue($k, $v);
    }

    $stmtCount->execute();
    $count = (int)$stmtCount->fetchColumn();

    // ── COUNT con stock (desde el step 1, calcular cuantos tienen stock) ──
    $countConStock = null;
    $countSinStock = null;
    if ($step >= 1) {
        $sqlConStock = "SELECT COUNT(*) AS total FROM inventario_cnc AS i WHERE $whereConStock AND i.stock > 0";
        $stmtConStock = $conn->prepare($sqlConStock);
        foreach ($params as $k => $v) {
            $stmtConStock->bindValue($k, $v);
        }
        $stmtConStock->execute();
        $countConStock = (int)$stmtConStock->fetchColumn();

        // Total sin el filtro de stock (todos los registros con los filtros previos)
        $sqlTotalSinFiltroStock = "SELECT COUNT(*) AS total FROM inventario_cnc AS i WHERE $whereConStock";
        $stmtTotalSin = $conn->prepare($sqlTotalSinFiltroStock);
        foreach ($params as $k => $v) {
            $stmtTotalSin->bindValue($k, $v);
        }
        $stmtTotalSin->execute();
        $totalSinFiltroStock = (int)$stmtTotalSin->fetchColumn();
        $countSinStock = $totalSinFiltroStock - $countConStock;
    }

    // ── DISTINCT para el siguiente selector ──────────────────────
    $distinctNext = [];
    $distinctColumn = '';

    switch ($step) {
        case 1:
            $distinctColumn = 'material';
            break;
        case 2:
            $distinctColumn = 'proveedor';
            break;
        case 3:
            $distinctColumn = 'estatus';
            break;
        case 4:
            $distinctColumn = 'Medida';
            break;
        // Steps 5 y 6 no requieren distinct para un siguiente selector
    }

    if ($distinctColumn !== '') {
        $sqlDistinct = "SELECT DISTINCT i.$distinctColumn AS valor 
                        FROM inventario_cnc AS i 
                        WHERE $whereConStock 
                          AND i.$distinctColumn IS NOT NULL 
                          AND i.$distinctColumn != ''
                        ORDER BY i.$distinctColumn ASC";
        $stmtDistinct = $conn->prepare($sqlDistinct);
        foreach ($params as $k => $v) {
            $stmtDistinct->bindValue($k, $v);
        }
        $stmtDistinct->execute();
        $distinctNext = $stmtDistinct->fetchAll(PDO::FETCH_COLUMN);

        // Limpiar bytes nulos
        $distinctNext = array_map(function($val) {
            return trim(str_replace("\0", '', $val));
        }, $distinctNext);
        $distinctNext = array_values(array_filter($distinctNext, function($val) {
            return $val !== '';
        }));
    }

    // ── Respuesta ────────────────────────────────────────────────
    $response = [
        'success'          => true,
        'count'            => $count,
        'distinct_next'    => $distinctNext,
    ];

    if ($countConStock !== null) {
        $response['count_con_stock'] = $countConStock;
        $response['count_sin_stock'] = $countSinStock;
    }

    echo json_encode($response);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado: ' . $e->getMessage()
    ]);
}
?>
