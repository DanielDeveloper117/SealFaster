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

    // ============================================================
    // FUNCIONES AUXILIARES
    // ============================================================

    /**
     * Procesa un array de cotizaciones y extrae los registros únicos
     */
    function procesarCotizacionesARegistros($cotizaciones) {
        $registros = [];
        
        foreach ($cotizaciones as $cotizacion) {
            if (empty($cotizacion['billets_claves_lotes'])) {
                continue;
            }

            $billets = explode(',', $cotizacion['billets_claves_lotes']);
            
            foreach ($billets as $billetItem) {
                $billetItem = trim($billetItem);
                
                if (empty($billetItem)) {
                    continue;
                }

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
                    
                    if (preg_match('/(\d+)\s*\/\s*(\d+)/', $medida, $medida_matches)) {
                        $di_sello = floatval($medida_matches[1]);
                        $de_sello = floatval($medida_matches[2]);
                    }
                }
                elseif (preg_match('/^([^\s]+)\s+([^\s]+)\s+\(([^)]+)\)\s+(\d+)\s+pz$/i', $billetItem, $matches)) {
                    $lote_pedimento = trim($matches[1]);
                    $clave = trim($matches[2]);
                    $medida = trim($matches[3]);
                    $pz_teoricas = intval($matches[4]);
                    
                    if (preg_match('/(\d+)\s*\/\s*(\d+)/', $medida, $medida_matches)) {
                        $di_sello = floatval($medida_matches[1]);
                        $de_sello = floatval($medida_matches[2]);
                    }
                }
                else {
                    $parts = preg_split('/\s+/', $billetItem);
                    if (count($parts) >= 5) {
                        $lote_pedimento = $parts[0];
                        $clave = $parts[1];
                        
                        if (preg_match('/\(([^)]+)\)/', $billetItem, $parentesis_matches)) {
                            $medida = $parentesis_matches[1];
                            if (preg_match('/(\d+)\s*\/\s*(\d+)/', $medida, $medida_matches)) {
                                $di_sello = floatval($medida_matches[1]);
                                $de_sello = floatval($medida_matches[2]);
                            }
                        }
                        
                        foreach ($parts as $part) {
                            if (is_numeric($part)) {
                                $pz_teoricas = intval($part);
                                break;
                            }
                        }
                    }
                }

                if ($di_sello === null) $di_sello = $cotizacion['di_sello'];
                if ($de_sello === null) $de_sello = $cotizacion['de_sello'];

                $registro = [
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

                $registros[] = $registro;
            }
        }
        
        return $registros;
    }

    /**
     * Compara registros esperados con los actuales para detectar cambios
     * Retorna true si hay discrepancia, false si son iguales
     */
    function verificarDiscrepancia($registrosEsperados, $registrosActuales) {
        // Si la cantidad es diferente, hay discrepancia
        if (count($registrosEsperados) !== count($registrosActuales)) {
            return true;
        }

        // Comparar cada registro por lote_pedimento (el identificador único)
        $lotesEsperados = [];
        foreach ($registrosEsperados as $reg) {
            $lotesEsperados[$reg['lote_pedimento']] = [
                'clave' => $reg['clave'],
                'pz_teoricas' => $reg['pz_teoricas'],
                'di_sello' => $reg['di_sello'],
                'de_sello' => $reg['de_sello']
            ];
        }

        $lotesActuales = [];
        foreach ($registrosActuales as $reg) {
            $lotesActuales[$reg['lote_pedimento']] = [
                'clave' => $reg['clave'],
                'pz_teoricas' => $reg['pz_teoricas'],
                'di_sello' => $reg['di_sello'],
                'de_sello' => $reg['de_sello']
            ];
        }

        // Comparar si las claves son iguales
        if (array_keys($lotesEsperados) !== array_keys($lotesActuales)) {
            return true;
        }

        // Comparar los datos de cada lote
        foreach ($lotesEsperados as $lote => $datos) {
            if (!isset($lotesActuales[$lote])) {
                return true;
            }
            if ($datos !== $lotesActuales[$lote]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Inserta registros en control_almacen
     */
    function insertarRegistrosControlAlmacen($conn, $id_requisicion, $registros) {
        $insertStmt = $conn->prepare("
            INSERT INTO control_almacen (
                id_requisicion, id_estimacion, id_cotizacion, perfil_sello, componente, material, 
                clave, lote_pedimento, medida, pz_teoricas, di_sello, de_sello, altura_pz, h_componente,
                fecha_registro
            ) VALUES (
                :id_requisicion, :id_estimacion, :id_cotizacion, :perfil_sello, :componente, :material,
                :clave, :lote_pedimento, :medida, :pz_teoricas, :di_sello, :de_sello, :altura_pz, :h_componente,
                NOW()
            )
        ");

        foreach ($registros as $registro) {
            $insertStmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
            $insertStmt->bindParam(':id_estimacion', $registro['id_estimacion']);
            $insertStmt->bindParam(':id_cotizacion', $registro['id_cotizacion']);
            $insertStmt->bindParam(':perfil_sello', $registro['perfil_sello']);
            $insertStmt->bindParam(':componente', $registro['componente']);
            $insertStmt->bindParam(':material', $registro['material']);
            $insertStmt->bindParam(':clave', $registro['clave']);
            $insertStmt->bindParam(':lote_pedimento', $registro['lote_pedimento']);
            $insertStmt->bindParam(':medida', $registro['medida']);
            $insertStmt->bindParam(':pz_teoricas', $registro['pz_teoricas'], PDO::PARAM_INT);
            $insertStmt->bindParam(':di_sello', $registro['di_sello']);
            $insertStmt->bindParam(':de_sello', $registro['de_sello']);
            $insertStmt->bindParam(':altura_pz', $registro['a_sello']);
            $insertStmt->bindParam(':h_componente', $registro['h_componente']);
            $insertStmt->execute();
        }
    }

    // ============================================================
    // FIN FUNCIONES AUXILIARES
    // ============================================================

    $id_requisicion = $_GET['id_requisicion'];

    // 1. Obtener cotizaciones actuales de la requisición
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

    // 2. Convertir IDs de cotizaciones a array y obtener datos completos
    $cotizacion_ids = explode(', ', $requisicion['cotizaciones']);
    $placeholders = str_repeat('?,', count($cotizacion_ids) - 1) . '?';
    $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE id_cotizacion IN ($placeholders)";
    $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
    $stmtCotizaciones->execute($cotizacion_ids);
    $cotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);

    // 3. Procesar las cotizaciones actuales
    $registros_unicos_esperados = procesarCotizacionesARegistros($cotizaciones);

    // 4. Consultar registros actuales en control_almacen
    $stmtControlAlmacen = $conn->prepare("
        SELECT * FROM control_almacen 
        WHERE id_requisicion = :id_requisicion
        AND NOT (es_eliminacion = 1 AND es_eliminacion_auth = 1)
        ORDER BY id_control ASC
    ");
    $stmtControlAlmacen->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmtControlAlmacen->execute();
    $registrosControlAlmacen = $stmtControlAlmacen->fetchAll(PDO::FETCH_ASSOC);

    // 5. Comparar si hay cambios entre lo esperado y lo actual
    $hayDiscrepancia = verificarDiscrepancia($registros_unicos_esperados, $registrosControlAlmacen);

    // 6. Si hay discrepancia, eliminar los registros antiguos y reinsertar
    if ($hayDiscrepancia && count($registrosControlAlmacen) > 0) {
        $conn->beginTransaction();
        
        try {
            // Eliminar registros anteriores para esta requisición
            $stmtDelete = $conn->prepare("
                DELETE FROM control_almacen 
                WHERE id_requisicion = :id_requisicion
                AND NOT (es_eliminacion = 1 AND es_eliminacion_auth = 1)
            ");
            $stmtDelete->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
            $stmtDelete->execute();

            // Insertar los nuevos registros
            if (count($registros_unicos_esperados) > 0) {
                insertarRegistrosControlAlmacen($conn, $id_requisicion, $registros_unicos_esperados);
            }

            $conn->commit();
            
            // Actualizar referencia de registros para retornar
            $stmtControlAlmacen = $conn->prepare("
                SELECT * FROM control_almacen 
                WHERE id_requisicion = :id_requisicion
                AND NOT (es_eliminacion = 1 AND es_eliminacion_auth = 1)
            ");
            $stmtControlAlmacen->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
            $stmtControlAlmacen->execute();
            $registrosControlAlmacen = $stmtControlAlmacen->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    } 
    // 7. Si NO hay registros en control_almacen, insertar los nuevos
    elseif (count($registrosControlAlmacen) === 0 && count($registros_unicos_esperados) > 0) {
        $conn->beginTransaction();
        
        try {
            insertarRegistrosControlAlmacen($conn, $id_requisicion, $registros_unicos_esperados);
            $conn->commit();

            // Actualizar referencia de registros para retornar
            $stmtControlAlmacen = $conn->prepare("
                SELECT * FROM control_almacen 
                WHERE id_requisicion = :id_requisicion
                AND NOT (es_eliminacion = 1 AND es_eliminacion_auth = 1)
            ");
            $stmtControlAlmacen->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
            $stmtControlAlmacen->execute();
            $registrosControlAlmacen = $stmtControlAlmacen->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }

    // 8. Retornar los registros finales
    $response = [
        'success' => true,
        'id_requisicion' => $id_requisicion,
        'total_registros' => count($registrosControlAlmacen),
        'billets' => $registrosControlAlmacen,
        'discrepancia_detectada' => $hayDiscrepancia,
        'fuente' => count($registrosControlAlmacen) > 0 ? 'control_almacen' : 'vacio'
    ];

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