<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    // Mapeo de materiales de inventario_cnc a patrones de parametros
    $mapeoMateriales = [
        "H-ECOPUR" => "PU ROJO",
        "ECOSIL" => "SILICON",
        "ECORUBBER 1" => "NITRILO",
        "ECORUBBER 2" => "VITON", 
        "ECORUBBER 3" => "EPDM",
        "ECOPUR" => "PU VERDE",
        "ECOTAL" => "ECOTAL",
        "ECOMID" => "ECOMID",
        "ECOFLON 1" => "VIRGEN",
        "ECOFLON 2" => ["NIKEL", "MOLLY"], // Múltiples opciones para ECOFLON 2
        "ECOFLON 3" => "BRONCE"
    ];

    // Estatus que se deben mantener si la clave es válida
    $estatusMantener = ['En uso', 'Maquinado en curso', 'Eliminado', 'Disponible para cotizar'];

    // Consulta para obtener todos los registros de inventario_cnc
    $stmtInventario = $conn->prepare("
        SELECT id, material, proveedor, interior, exterior, Clave, max_usable, estatus 
        FROM inventario_cnc
    ");
    $stmtInventario->execute();
    $registrosInventario = $stmtInventario->fetchAll(PDO::FETCH_ASSOC);
    
    $estadisticas = [
        'total_registros' => count($registrosInventario),
        'actualizados_clave_valida' => 0,
        'actualizados_clave_invalida' => 0,
        'material_no_mapeado' => 0,
        'errores' => 0,
        'estatus_mantenidos' => 0,
        'estatus_cambiados' => 0
    ];
    
    $errores = [];
    $registrosPendientes = [];
    
    // Recorrer cada registro de inventario_cnc
    foreach ($registrosInventario as $registro) {
        $id = $registro['id'];
        $materialInventario = $registro['material'];
        $proveedor = $registro['proveedor'];
        $interior = $registro['interior'];
        $exterior = $registro['exterior'];
        $estatusActual = $registro['estatus'];
        
        try {
            // Verificar si el material está en el mapeo
            if (!isset($mapeoMateriales[$materialInventario])) {
                $estadisticas['material_no_mapeado']++;
                $registrosPendientes[] = [
                    'id' => $id,
                    'material' => $materialInventario,
                    'razon' => 'Material no encontrado en mapeo'
                ];
                continue;
            }
            
            $materialParametros = $mapeoMateriales[$materialInventario];
            
            // Construir la consulta según el tipo de material (simple o múltiple)
            if (is_array($materialParametros)) {
                // Para materiales con múltiples opciones (ECOFLON 2)
                $placeholders = str_repeat('?,', count($materialParametros) - 1) . '?';
                $sqlParametros = "
                    SELECT clave, max_usable 
                    FROM parametros 
                    WHERE interior = ? 
                    AND exterior = ? 
                    AND proveedor = ? 
                    AND (material LIKE CONCAT('%', ?, '%') OR material LIKE CONCAT('%', ?, '%'))
                ";
                
                $params = [$interior, $exterior, $proveedor];
                foreach ($materialParametros as $material) {
                    $params[] = $material;
                }
                
                $stmtParametros = $conn->prepare($sqlParametros);
                $stmtParametros->execute($params);
                
            } else {
                // Para materiales con una sola opción
                $stmtParametros = $conn->prepare("
                    SELECT clave, max_usable 
                    FROM parametros 
                    WHERE interior = :interior 
                    AND exterior = :exterior 
                    AND proveedor = :proveedor 
                    AND material LIKE CONCAT('%', :material, '%')
                ");
                
                $stmtParametros->execute([
                    ':interior' => $interior,
                    ':exterior' => $exterior,
                    ':proveedor' => $proveedor,
                    ':material' => $materialParametros
                ]);
            }
            
            $coincidencias = $stmtParametros->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($coincidencias) > 0) {
                // CLAVE VÁLIDA - Se encontró coincidencia en parámetros
                $parametro = $coincidencias[0];
                $nuevaClave = $parametro['clave'];
                $nuevoMaxUsable = $parametro['max_usable'];
                
                // Determinar el nuevo estatus según las condiciones
                if (in_array($estatusActual, $estatusMantener)) {
                    // Mantener el estatus actual
                    $nuevoEstatus = $estatusActual;
                    $estadisticas['estatus_mantenidos']++;
                } else {
                    // Cambiar a "Disponible para cotizar"
                    $nuevoEstatus = 'Disponible para cotizar';
                    $estadisticas['estatus_cambiados']++;
                }
                
                // Actualizar inventario_cnc con clave válida
                $updateStmt = $conn->prepare("
                    UPDATE inventario_cnc 
                    SET Clave = :clave, 
                        max_usable = :max_usable, 
                        estatus = :estatus 
                    WHERE id = :id
                ");
                
                $updateStmt->execute([
                    ':clave' => $nuevaClave,
                    ':max_usable' => $nuevoMaxUsable,
                    ':estatus' => $nuevoEstatus,
                    ':id' => $id
                ]);
                
                if ($updateStmt->rowCount() > 0) {
                    $estadisticas['actualizados_clave_valida']++;
                } else {
                    throw new Exception("No se pudo actualizar registro ID: $id");
                }
                
            } else {
                // CLAVE NO VÁLIDA - No se encontró coincidencia en parámetros
                // NO cambiar la clave, solo actualizar el estatus a "Clave incorrecta"
                
                $updateStmt = $conn->prepare("
                    UPDATE inventario_cnc 
                    SET estatus = 'Clave incorrecta' 
                    WHERE id = :id
                ");
                
                $updateStmt->execute([':id' => $id]);
                
                $estadisticas['actualizados_clave_invalida']++;
                
                // Determinar el patrón de búsqueda para el reporte
                $patronBusqueda = is_array($materialParametros) ? 
                    implode(' o ', $materialParametros) : $materialParametros;
                
                $registrosPendientes[] = [
                    'id' => $id,
                    'material_inventario' => $materialInventario,
                    'proveedor' => $proveedor,
                    'interior' => $interior,
                    'exterior' => $exterior,
                    'material_buscado' => $patronBusqueda,
                    'estatus_anterior' => $estatusActual,
                    'estatus_nuevo' => 'Clave incorrecta',
                    'razon' => 'No se encontró coincidencia en parametros'
                ];
            }
            
        } catch (Exception $e) {
            $estadisticas['errores']++;
            $errores[] = "ID: $id - " . $e->getMessage();
        }
    }

    // Preparar respuesta
    $response = [
        'success' => true,
        'message' => "Proceso de actualización de claves completado",
        'estadisticas' => $estadisticas,
        'resumen' => [
            'Total registros procesados' => $estadisticas['total_registros'],
            'Registros con clave válida actualizados' => $estadisticas['actualizados_clave_valida'],
            'Registros con clave inválida (estatus cambiado)' => $estadisticas['actualizados_clave_invalida'],
            'Materiales no mapeados' => $estadisticas['material_no_mapeado'],
            'Estatus mantenidos' => $estadisticas['estatus_mantenidos'],
            'Estatus cambiados a "Disponible para cotizar"' => $estadisticas['estatus_cambiados'],
            'Errores' => $estadisticas['errores']
        ],
        'estatus_que_se_mantienen' => $estatusMantener,
        'mapeo_materiales_aplicado' => $mapeoMateriales
    ];
    
    // Agregar detalles si existen
    if (!empty($registrosPendientes)) {
        $response['registros_con_clave_invalida'] = array_slice($registrosPendientes, 0, 20); // Mostrar primeros 20
        $response['total_registros_clave_invalida'] = count($registrosPendientes);
    }
    
    if (!empty($errores)) {
        $response['errores_detallados'] = array_slice($errores, 0, 10); // Mostrar primeros 10 errores
    }

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Error en actualizar_claves_desde_parametros: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error en actualizar_claves_desde_parametros: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>