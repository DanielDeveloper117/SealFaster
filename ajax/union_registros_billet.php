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

    // 2. Obtener las cotizaciones de la requisición
    $stmtRequisicion = $conn->prepare("SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion LIMIT 1");
    $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtRequisicion->execute();
    $requisicion = $stmtRequisicion->fetch(PDO::FETCH_ASSOC);

    $cotizaciones = [];
    if ($requisicion && !empty($requisicion['cotizaciones'])) {
        // 3. Convertir IDs de cotizaciones a array y obtener datos completos
        $cotizacion_ids = explode(', ', $requisicion['cotizaciones']);
        $placeholders = str_repeat('?,', count($cotizacion_ids) - 1) . '?';
        $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE id_cotizacion IN ($placeholders)";
        $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
        $stmtCotizaciones->execute($cotizacion_ids);
        $cotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Para cada barra, buscar información solo en cotizaciones
    $barrasCompletas = [];

    foreach ($barrasControlAlmacen as $barra) {
        $lotePedimento = $barra['lote_pedimento'];
        
        // Buscar en TODAS las cotizaciones donde aparezca este lote_pedimento
        $cotizacionesEncontradas = [];
        
        foreach ($cotizaciones as $cotizacion) {
            if (!empty($cotizacion['billets_lotes'])) {
                $billets = explode(',', $cotizacion['billets_lotes']);
                
                foreach ($billets as $billetItem) {
                    $billetItem = trim($billetItem);
                    
                    // Verificar si este billetItem contiene nuestro lote_pedimento
                    if (strpos($billetItem, $lotePedimento) !== false) {
                        // Extraer cantidad de piezas del formato: "R2T3042136-15 (27/46) 1 pz"
                        $pzTeoricas = null;
                        if (preg_match('/\([^)]+\)\s+(\d+)\s+pz/i', $billetItem, $matches)) {
                            $pzTeoricas = intval($matches[1]);
                        }
                        
                        $cotizacionesEncontradas[] = [
                            'id_estimacion' => $cotizacion['id_estimacion'],
                            'id_cotizacion' => $cotizacion['id_cotizacion'],
                            'perfil_sello' => $cotizacion['perfil_sello'],
                            'material' => $cotizacion['material'],
                            'a_sello' => $cotizacion['a_sello'],
                            'di_sello' => $cotizacion['di_sello'],
                            'de_sello' => $cotizacion['de_sello'],
                            'pz_teoricas' => $pzTeoricas,
                            'billet_item' => $billetItem
                        ];
                        break; // Salir del loop interno de billets (siguiente cotización)
                    }
                }
            }
        }

      // Construir objeto completo de la barra usando todos los datos de control_almacen
        $barraCompleta = [
            'id_control' => $barra['id_control'],
            'lote_pedimento' => $lotePedimento,
            'mm_entrega' => $barra['mm_entrega'],
            'fecha_registro' => $barra['fecha_registro'],
            // 'fecha_actualizacion' => $barra['fecha_actualizacion'],
            'material' => $barra['material'],
            'medida' => $barra['medida'],
            // Campos de maquinado (pueden estar vacíos si no se han guardado)
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
            'justificacion_merma' => $barra['justificacion_merma'],
            // Información de cotización
            'id_cotizacion' => $barra['id_cotizacion'],
            'id_estimacion' => $barra['id_estimacion'],
            // Cotizaciones encontradas
            'cotizaciones' => $cotizacionesEncontradas
        ];

        $barrasCompletas[] = $barraCompleta;
    }

    // 5. Preparar respuesta final
    $response = [
        'success' => true,
        'id_requisicion' => $id_requisicion,
        'total_barras' => count($barrasCompletas),
        'barras' => $barrasCompletas
    ];

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Error en union_registros_billet: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor: ' . $e->getMessage()
    ]);
    http_response_code(500);
} finally {
    $conn = null;
}
?>