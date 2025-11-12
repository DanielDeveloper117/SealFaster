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

    // 1. PRIMERO consultar control_almacen
    $stmtControlAlmacen = $conn->prepare("
        SELECT * FROM control_almacen 
        WHERE id_requisicion = :id_requisicion
    ");
    $stmtControlAlmacen->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtControlAlmacen->execute();
    $registrosControlAlmacen = $stmtControlAlmacen->fetchAll(PDO::FETCH_ASSOC);

    // 2. Si HAY registros en control_almacen, retornarlos directamente
    if ($registrosControlAlmacen && count($registrosControlAlmacen) > 0) {
        echo json_encode([
            'success' => true,
            'id_requisicion' => $id_requisicion,
            'total_registros' => count($registrosControlAlmacen),
            'billets' => $registrosControlAlmacen,
            'fuente' => 'control_almacen'
        ]);
        exit;
    }

    // 3. Si NO HAY registros en control_almacen, proceder con cotizaciones
    $stmtRequisicion = $conn->prepare("SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion LIMIT 1");
    $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtRequisicion->execute();
    $requisicion = $stmtRequisicion->fetch(PDO::FETCH_ASSOC);

    if (!$requisicion || empty($requisicion['cotizaciones'])) {
        echo json_encode([
            'success' => true,
            'registros' => [],
            'message' => 'No se encontraron cotizaciones para esta requisición'
        ]);
        exit;
    }

    // 4. Convertir IDs de cotizaciones a array y obtener datos completos
    $cotizacion_ids = explode(', ', $requisicion['cotizaciones']);
    $placeholders = str_repeat('?,', count($cotizacion_ids) - 1) . '?';
    $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE id_cotizacion IN ($placeholders)";
    $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
    $stmtCotizaciones->execute($cotizacion_ids);
    $cotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);

    // 5. Procesar cada cotización y separar los billets_claves_lotes en registros únicos
    $registros_unicos = [];

    foreach ($cotizaciones as $cotizacion) {
        if (empty($cotizacion['billets_claves_lotes'])) {
            continue;
        }

        // Separar los billets_claves_lotes por comas
        $billets = explode(',', $cotizacion['billets_claves_lotes']);
        
        foreach ($billets as $billetItem) {
            $billetItem = trim($billetItem);
            
            if (empty($billetItem)) {
                continue;
            }

            // Extraer información del formato: "lote-pedimento clave (di/de) N pz"
            $lote_pedimento = '';
            $clave = '';
            $medida = '';
            $pz_teoricas = null;
            $di_sello = null;
            $de_sello = null;

            if (preg_match('/^([A-Z0-9\-\.]+)\s+([A-Z0-9\-\.]+)\s+\(([^)]+)\)\s+(\d+)\s+pz$/i', $billetItem, $matches)) {
                $lote_pedimento = trim($matches[1]);
                $clave = trim($matches[2]);
                $medida = trim($matches[3]);
                $pz_teoricas = intval($matches[4]);
                
                // Si la medida está en formato "di/de", extraer los valores individuales
                if (preg_match('/(\d+)\s*\/\s*(\d+)/', $medida, $medida_matches)) {
                    $di_sello = floatval($medida_matches[1]);
                    $de_sello = floatval($medida_matches[2]);
                }
            }
            // Patrón alternativo más flexible
            elseif (preg_match('/^([^\s]+)\s+([^\s]+)\s+\(([^)]+)\)\s+(\d+)\s+pz$/i', $billetItem, $matches)) {
                $lote_pedimento = trim($matches[1]);
                $clave = trim($matches[2]);
                $medida = trim($matches[3]);
                $pz_teoricas = intval($matches[4]);
                
                // Intentar extraer di/de de la medida
                if (preg_match('/(\d+)\s*\/\s*(\d+)/', $medida, $medida_matches)) {
                    $di_sello = floatval($medida_matches[1]);
                    $de_sello = floatval($medida_matches[2]);
                }
            }
            // Último intento: patrón muy flexible
            else {
                // Dividir por espacios y analizar los componentes
                $parts = preg_split('/\s+/', $billetItem);
                if (count($parts) >= 5) {
                    $lote_pedimento = $parts[0];
                    $clave = $parts[1];
                    
                    // Buscar el patrón (X/Y) en el string
                    if (preg_match('/\(([^)]+)\)/', $billetItem, $parentesis_matches)) {
                        $medida = $parentesis_matches[1];
                        if (preg_match('/(\d+)\s*\/\s*(\d+)/', $medida, $medida_matches)) {
                            $di_sello = floatval($medida_matches[1]);
                            $de_sello = floatval($medida_matches[2]);
                        }
                    }
                    
                    // Buscar el número de piezas
                    foreach ($parts as $part) {
                        if (is_numeric($part)) {
                            $pz_teoricas = intval($part);
                            break;
                        }
                    }
                }
            }

            // Usar valores de la cotización si no se extrajeron del billet_item
            if ($di_sello === null) $di_sello = $cotizacion['di_sello'];
            if ($de_sello === null) $de_sello = $cotizacion['de_sello'];

            // Crear registro único
            $registro = [
                'id_estimacion' => $cotizacion['id_estimacion'],
                'id_cotizacion' => $cotizacion['id_cotizacion'],
                'perfil_sello' => $cotizacion['perfil_sello'],
                'material' => $cotizacion['material'],
                'clave' => $clave,
                'lote_pedimento' => $lote_pedimento,
                'medida' => $medida,
                'pz_teoricas' => $pz_teoricas,
                'di_sello' => $di_sello,
                'de_sello' => $de_sello,
                'a_sello' => $cotizacion['a_sello'],
                'billet_item_original' => $billetItem
            ];

            $registros_unicos[] = $registro;
        }
    }

    // 6. INSERTAR registros únicos en control_almacen (evitando duplicados)
    if (count($registros_unicos) > 0) {
        $conn->beginTransaction();
        
        try {
            $insertStmt = $conn->prepare("
                INSERT INTO control_almacen (
                    id_requisicion, id_estimacion, id_cotizacion, perfil_sello, material, 
                    clave, lote_pedimento, medida, pz_teoricas, di_sello, de_sello, altura_pz,
                    fecha_registro
                ) VALUES (
                    :id_requisicion, :id_estimacion, :id_cotizacion, :perfil_sello, :material,
                    :clave, :lote_pedimento, :medida, :pz_teoricas, :di_sello, :de_sello, :altura_pz,
                    NOW()
                )
            ");

            foreach ($registros_unicos as $registro) {
                // Verificar si ya existe el registro para evitar duplicados
                $checkStmt = $conn->prepare("
                    SELECT id_control FROM control_almacen 
                    WHERE id_requisicion = :id_requisicion 
                    AND lote_pedimento = :lote_pedimento 
                    LIMIT 1
                ");
                $checkStmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
                $checkStmt->bindParam(':lote_pedimento', $registro['lote_pedimento']);
                $checkStmt->execute();
                
                $existe = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$existe) {
                    // Insertar solo si no existe
                    $insertStmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
                    $insertStmt->bindParam(':id_estimacion', $registro['id_estimacion']);
                    $insertStmt->bindParam(':id_cotizacion', $registro['id_cotizacion']);
                    $insertStmt->bindParam(':perfil_sello', $registro['perfil_sello']);
                    $insertStmt->bindParam(':material', $registro['material']);
                    $insertStmt->bindParam(':clave', $registro['clave']);
                    $insertStmt->bindParam(':lote_pedimento', $registro['lote_pedimento']);
                    $insertStmt->bindParam(':medida', $registro['medida']);
                    $insertStmt->bindParam(':pz_teoricas', $registro['pz_teoricas'], PDO::PARAM_INT);
                    $insertStmt->bindParam(':di_sello', $registro['di_sello']);
                    $insertStmt->bindParam(':de_sello', $registro['de_sello']);
                    $insertStmt->bindParam(':altura_pz', $registro['a_sello']);
                    $insertStmt->execute();
                }
            }
            
            $conn->commit();
            
        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }

        // 7. FINALMENTE, consultar los registros recién insertados en control_almacen
        $stmtFinal = $conn->prepare("
            SELECT * FROM control_almacen 
            WHERE id_requisicion = :id_requisicion
        ");
        $stmtFinal->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        $stmtFinal->execute();
        $registrosFinales = $stmtFinal->fetchAll(PDO::FETCH_ASSOC);

        $response = [
            'success' => true,
            'id_requisicion' => $id_requisicion,
            'total_registros' => count($registrosFinales),
            'billets' => $registrosFinales,
            'fuente' => 'control_almacen_insertado'
        ];
    } else {
        $response = [
            'success' => true,
            'id_requisicion' => $id_requisicion,
            'total_registros' => 0,
            'billets' => [],
            'message' => 'No se encontraron registros en las cotizaciones'
        ];
    }

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Error en barras_para_entregar: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor: ' . $e->getMessage()
    ]);
    http_response_code(500);
} finally {
    $conn = null;
}
?>