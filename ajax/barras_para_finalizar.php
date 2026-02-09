<?php
require_once(__DIR__ . '/../config/rutes.php');
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

    // 0. Obtener la fecha de retorno de la tabla requisiciones para bloqueo de edición
    $stmtFecha = $conn->prepare("SELECT fecha_retorno_barras FROM requisiciones WHERE id_requisicion = :id LIMIT 1");
    $stmtFecha->execute([':id' => $id_requisicion]);
    $fechaRetorno = $stmtFecha->fetchColumn();

    // 1. Obtener todas las barras del control_almacen para esta requisición
    $stmtControlAlmacen = $conn->prepare("
        SELECT *
        FROM control_almacen 
        WHERE id_requisicion = :id_requisicion
    ");
    $stmtControlAlmacen->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtControlAlmacen->execute();
    $barrasControlAlmacen = $stmtControlAlmacen->fetchAll(PDO::FETCH_ASSOC);

    if (!$barrasControlAlmacen) {
        echo json_encode([
            'success' => true,
            'barras' => [],
            'message' => 'No se encontraron barras en control_almacen para esta requisición'
        ]);
        exit;
    }

    // 2. Construir respuesta con datos completos de control_almacen
    $barrasCompletas = [];

    foreach ($barrasControlAlmacen as $barra) {
        // Construir objeto completo de la barra usando todos los datos de control_almacen
        $barraCompleta = [
            'id_control' => $barra['id_control'],
            'lote_pedimento' => $barra['lote_pedimento'],
            'mm_entrega' => $barra['mm_entrega'],
            'fecha_registro' => $barra['fecha_registro'],
            'material' => $barra['material'],
            'medida' => $barra['medida'],
            // Campos de maquinado
            'perfil_sello' => $barra['perfil_sello'],
            'pz_teoricas' => $barra['pz_teoricas'],
            'pz_maquinadas' => $barra['pz_maquinadas'],
            'altura_pz' => $barra['altura_pz'],
            'mm_usados' => $barra['mm_usados'],
            'total_sellos' => $barra['total_sellos'],
            'merma_corte' => $barra['merma_corte'],
            'scrap_pz' => $barra['scrap_pz'],
            'scrap_mm' => $barra['scrap_mm'],
            'mm_total_usados' => $barra['mm_total_usados'],
            // Campos calculados
            'mm_teoricos' => $barra['mm_teoricos'],
            'mm_merma_real' => $barra['mm_merma_real'],
            // Estado y justificaciones
            'es_merma' => $barra['es_merma'],
            'causa_merma' => $barra['causa_merma'],
            'justificacion_merma' => $barra['justificacion_merma'],
            // Información de cotización
            'id_cotizacion' => $barra['id_cotizacion'],
            'id_estimacion' => $barra['id_estimacion'],
            // Campos de reemplazo
            'clave_remplazo' => $barra['clave_remplazo'],
            'lp_remplazo' => $barra['lp_remplazo'],
            'medida_remplazo' => $barra['medida_remplazo'],
            'justificacion_remplazo' => $barra['justificacion_remplazo'],
            'es_remplazo' => $barra['es_remplazo'],
            'es_remplazo_auth' => $barra['es_remplazo_auth'],
            // Campos de extra
            'justificacion_extra' => $barra['justificacion_extra'],
            'es_extra' => $barra['es_extra'],
            'es_extra_auth' => $barra['es_extra_auth'],
            // Otros campos
            'clave' => $barra['clave'],
            'di_sello' => $barra['di_sello'],
            'de_sello' => $barra['de_sello'],
            'altura_pz' => $barra['altura_pz']
        ];

        // Determinar si esta barra tiene una autorización pendiente (extra o reemplazo)
        $pendiente = 0;
        if ((isset($barra['es_extra']) && intval($barra['es_extra']) === 1 && (!isset($barra['es_extra_auth']) || intval($barra['es_extra_auth']) === 0))
            || (isset($barra['es_remplazo']) && intval($barra['es_remplazo']) === 1 && (!isset($barra['es_remplazo_auth']) || intval($barra['es_remplazo_auth']) === 0))) {
            $pendiente = 1;
        }

        $barraCompleta['pendiente_autorizar'] = $pendiente;

        $barrasCompletas[] = $barraCompleta;
    }

    // 3. Preparar respuesta final
    $response = [
        'success' => true,
        'id_requisicion' => $id_requisicion,
        'total_barras' => count($barrasCompletas),
        'billets' => $barrasCompletas,
        'fecha_retorno_barras' => $fechaRetorno,
        'fuente' => 'control_almacen'
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Error en barras_para_finalizar: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor: ' . $e->getMessage()
    ]);
    http_response_code(500);
} finally {
    $conn = null;
}
?>