<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    if (!isset($_GET['id_requisicion'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Falta parámetro: id_requisicion es requerido'
        ]);
        exit;
    }

    $id_requisicion = $_GET['id_requisicion'];

    // 1. Consultar todos los registros de control_almacen para esta requisición
    $stmtControlAlmacen = $conn->prepare("
        SELECT * 
        FROM control_almacen 
        WHERE id_requisicion = :id_requisicion
    ");
    $stmtControlAlmacen->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtControlAlmacen->execute();
    $registrosControlAlmacen = $stmtControlAlmacen->fetchAll(PDO::FETCH_ASSOC);

    // 2. Filtrar registros por situación (remplazo o extra)
    $billetsPendientes = [];

    foreach ($registrosControlAlmacen as $registro) {
        $situacion = '';
        
        // Determinar la situación
        if ((isset($registro['es_remplazo']) && $registro['es_remplazo'] == 1) && (isset($registro['es_remplazo_auth']) && $registro['es_remplazo_auth'] == 0)) {
            $situacion = 'remplazo';
        } elseif ((isset($registro['es_extra']) && $registro['es_extra'] == 1) && (isset($registro['es_extra_auth']) && $registro['es_extra_auth'] == 0)) {
            $situacion = 'extra';
        } elseif ((isset($registro['es_eliminacion']) && $registro['es_eliminacion'] == 1) && (isset($registro['es_eliminacion_auth']) && $registro['es_eliminacion_auth'] == 0)) {
            $situacion = 'eliminacion';
        }
        
        // Solo incluir registros que tengan situación definida
        if (!empty($situacion)) {
            $billet = [
                'id_control' => $registro['id_control'],
                'id_requisicion' => $registro['id_requisicion'],
                'id_estimacion' => $registro['id_estimacion'],
                'id_cotizacion' => $registro['id_cotizacion'],
                'perfil_sello' => $registro['perfil_sello'],
                'material' => $registro['material'],
                'clave' => $registro['clave'],
                'lote_pedimento' => $registro['lote_pedimento'],
                'medida' => $registro['medida'],
                'pz_teoricas' => $registro['pz_teoricas'],
                'altura_pz' => $registro['altura_pz'],
                'di_sello' => $registro['di_sello'],
                'de_sello' => $registro['de_sello'],
                'altura_pz' => $registro['altura_pz'],
                'situacion' => $situacion,
                // Campos de remplazo
                'clave_remplazo' => $registro['clave_remplazo'],
                'lp_remplazo' => $registro['lp_remplazo'],
                'medida_remplazo' => $registro['medida_remplazo'],
                'justificacion_remplazo' => $registro['justificacion_remplazo'],
                // Justificación cuando se solicitó una barra extra
                'justificacion_extra' => isset($registro['justificacion_extra']) ? $registro['justificacion_extra'] : null,
                // Justificación cuando se solicitó eliminar una barra
                'justificacion_eliminacion' => isset($registro['justificacion_eliminacion']) ? $registro['justificacion_eliminacion'] : null,
                // Campo extra
                'es_extra' => $registro['es_extra'],
                'es_eliminacion' => $registro['es_eliminacion']
            ];
            
            $billetsPendientes[] = $billet;
        }
    }

    // 3. Preparar respuesta final
    $response = [
        'success' => true,
        'id_requisicion' => $id_requisicion,
        'total_registros' => count($billetsPendientes),
        'billets' => $billetsPendientes,
        'fuente' => 'control_almacen_pendientes'
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Error en barras_pendientes_autorizar: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor: ' . $e->getMessage()
    ]);
    http_response_code(500);
} finally {
    $conn = null;
}
?>