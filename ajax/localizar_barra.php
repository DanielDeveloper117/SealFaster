<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();

header('Content-Type: application/json');

// Validar sesión
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        'error' => true,
        'mensaje' => 'No autorizado. Por favor inicie sesión.'
    ]);
    exit;
}

try {
    $lote_pedimento = isset($_POST['lote_pedimento']) ? trim($_POST['lote_pedimento']) : '';
    
    if (empty($lote_pedimento)) {
        throw new Exception('Lote/Pedimento no proporcionado');
    }

    $resultado = [
        'encontrada' => false,
        'requisiciones' => [],
        'mensaje' => 'La barra no se encontró en ningún folio. Parece estar disponible.'
    ];

    // FORMA 2: Buscar primero en control_almacen (producción)
    $sqlControlAlmacen = "SELECT id_requisicion, lote_pedimento 
                          FROM control_almacen 
                          WHERE lote_pedimento LIKE :lote_pedimento";
    $stmtControlAlmacen = $conn->prepare($sqlControlAlmacen);
    $stmtControlAlmacen->bindValue(':lote_pedimento', '%' . $lote_pedimento . '%', PDO::PARAM_STR);
    $stmtControlAlmacen->execute();
    $registrosControlAlmacen = $stmtControlAlmacen->fetchAll(PDO::FETCH_ASSOC);

    $requisicionesEncontradas = [];

    // Si se encuentra en control_almacen, obtener los id_requisicion
    if (!empty($registrosControlAlmacen)) {
        foreach ($registrosControlAlmacen as $registro) {
            $requisicionesEncontradas[] = $registro['id_requisicion'];
        }
    } else {
        // FORMA 1: Si no está en control_almacen, buscar en cotizacion_materiales
        $sqlCotizacion = "SELECT id_cotizacion, billets, estatus_completado 
                          FROM cotizacion_materiales 
                          WHERE billets LIKE :lote_pedimento 
                          AND estatus_completado = 'Autorizada'";
        $stmtCotizacion = $conn->prepare($sqlCotizacion);
        $stmtCotizacion->bindValue(':lote_pedimento', '%' . $lote_pedimento . '%', PDO::PARAM_STR);
        $stmtCotizacion->execute();
        $registrosCotizacion = $stmtCotizacion->fetchAll(PDO::FETCH_ASSOC);

        // Si se encuentran cotizaciones, buscar las requisiciones
        if (!empty($registrosCotizacion)) {
            // Construir la consulta con los id_cotizacion
            $idsCotizacion = implode(',', array_map(function($r) { return $r['id_cotizacion']; }, $registrosCotizacion));
            
            $sqlRequisiciones = "SELECT id_requisicion, cotizaciones, estatus 
                                FROM requisiciones 
                                WHERE (" . implode(' OR ', array_fill(0, count($registrosCotizacion), 'cotizaciones LIKE ?')) . ") 
                                AND estatus IN ('Autorizada', 'Producción', 'En producción', 'Finalizada')";
            
            $stmtRequisiciones = $conn->prepare($sqlRequisiciones);
            $paramIndex = 1;
            foreach ($registrosCotizacion as $cotizacion) {
                $stmtRequisiciones->bindValue($paramIndex++, '%' . $cotizacion['id_cotizacion'] . '%', PDO::PARAM_STR);
            }
            $stmtRequisiciones->execute();
            $requisicionesTemp = $stmtRequisiciones->fetchAll(PDO::FETCH_ASSOC);

            foreach ($requisicionesTemp as $req) {
                $requisicionesEncontradas[] = $req['id_requisicion'];
            }
        }
    }

    // Eliminar duplicados y obtener datos de las requisiciones
    $requisicionesEncontradas = array_unique($requisicionesEncontradas);

if (!empty($requisicionesEncontradas)) {
    $placeholders = implode(',', array_fill(0, count($requisicionesEncontradas), '?'));
    
    // AGREGAMOS EL FILTRO DE ESTATUS AQUÍ TAMBIÉN
    $sqlDetalleRequisicion = "SELECT 
                                    id_requisicion, folio, estatus, fecha_insercion,
                                    nombre_vendedor, comentario, autorizo,
                                    fecha_autorizacion, maquina, operador_cnc,
                                    inicio_maquinado, fecha_entrega_barras
                                FROM requisiciones 
                                WHERE id_requisicion IN ($placeholders)
                                AND estatus IN ('Autorizada', 'Producción', 'En producción', 'Finalizada')";
    
    $stmtDetalleRequisicion = $conn->prepare($sqlDetalleRequisicion);
    foreach ($requisicionesEncontradas as $key => $idReq) {
        $stmtDetalleRequisicion->bindValue(($key + 1), $idReq, PDO::PARAM_INT);
    }
    $stmtDetalleRequisicion->execute();
    $rows = $stmtDetalleRequisicion->fetchAll(PDO::FETCH_ASSOC);

    // Solo marcar como encontrada si el SELECT final devolvió registros con los estatus permitidos
    if (!empty($rows)) {
        $resultado['encontrada'] = true;
        $resultado['requisiciones'] = $rows;
        $resultado['mensaje'] = 'Barra localizada en folios activos.';
    } else {
        $resultado['encontrada'] = false;
        $resultado['mensaje'] = 'La barra existe, pero no está en un estatus activo (Autorizada/Producción).';
    }
} else {
    $resultado['encontrada'] = false;
}

echo json_encode($resultado);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ]);
}
