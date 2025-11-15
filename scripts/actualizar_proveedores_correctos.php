<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    // Consulta para obtener todos los registros
    $stmt = $conn->prepare("SELECT id, Clave, proveedor FROM inventario_cnc");
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $estadisticas = [
        'total_registros' => count($registros),
        'trygonal_numeros' => 0,
        'trygonal_numeros_letras' => 0,
        'carviflon' => 0,
        'skf' => 0,
        'slm' => 0,
        'sin_cambios' => 0, // Cambiado de 'pendiente' a 'sin_cambios'
        'errores' => 0
    ];
    
    $errores = [];
    
    // Recorrer cada registro y determinar el proveedor
    foreach ($registros as $registro) {
        $id = $registro['id'];
        $clave = trim($registro['Clave']);
        $nuevoProveedor = '';
        $actualizarProveedor = false;
        
        try {
            // REGLA 1: Clave que comienza con "TEF"
            if (strtoupper(substr($clave, 0, 3)) === 'TEF') {
                $nuevoProveedor = 'CARVIFLON';
                $actualizarProveedor = true;
                $estadisticas['carviflon']++;
            }
            // REGLA 2: Clave que comienza con "TU"
            elseif (strtoupper(substr($clave, 0, 2)) === 'TU') {
                $nuevoProveedor = 'SKF';
                $actualizarProveedor = true;
                $estadisticas['skf']++;
            }
            // REGLA 3: Clave que comienza con "RM"
            elseif (strtoupper(substr($clave, 0, 2)) === 'RM') {
                $nuevoProveedor = 'SLM';
                $actualizarProveedor = true;
                $estadisticas['slm']++;
            }
            // REGLA 4: Clave con solo números
            elseif (preg_match('/^\d+$/', $clave)) {
                $nuevoProveedor = 'TRYGONAL';
                $actualizarProveedor = true;
                $estadisticas['trygonal_numeros']++;
            }
            // REGLA 5: Clave con números + letras (parte izquierda números, derecha letras)
            elseif (preg_match('/^\d+[A-Za-z].*$/', $clave)) {
                $nuevoProveedor = 'TRYGONAL';
                $actualizarProveedor = true;
                $estadisticas['trygonal_numeros_letras']++;
            }
            // REGLA 6: No cumple ninguna condición anterior - NO CAMBIAR LA CLAVE
            else {
                // No se actualiza ni el proveedor ni la clave
                $estadisticas['sin_cambios']++;
                continue; // Saltar a la siguiente iteración
            }
            
            // Solo actualizar si se determinó un nuevo proveedor
            if ($actualizarProveedor) {
                $updateStmt = $conn->prepare("UPDATE inventario_cnc SET proveedor = :proveedor WHERE id = :id");
                $updateStmt->execute([
                    ':proveedor' => $nuevoProveedor,
                    ':id' => $id
                ]);
                
                if ($updateStmt->rowCount() === 0) {
                    throw new Exception("No se pudo actualizar el registro ID: $id");
                }
            }
            
        } catch (Exception $e) {
            $estadisticas['errores']++;
            $errores[] = $e->getMessage();
        }
    }

    // Preparar respuesta
    $response = [
        'success' => true,
        'message' => "Proceso de actualización de proveedores completado",
        'estadisticas' => $estadisticas,
        'resumen' => [
            'Registros procesados' => $estadisticas['total_registros'],
            'TRYGONAL (solo números)' => $estadisticas['trygonal_numeros'],
            'TRYGONAL (números + letras)' => $estadisticas['trygonal_numeros_letras'],
            'CARVIFLON (TEF)' => $estadisticas['carviflon'],
            'SKF (TU)' => $estadisticas['skf'],
            'SLM (RM)' => $estadisticas['slm'],
            'Registros sin cambios' => $estadisticas['sin_cambios'], // Cambiado de 'PENDIENTE'
            'Errores' => $estadisticas['errores']
        ]
    ];
    
    // Agregar errores si existen
    if (!empty($errores)) {
        $response['errores_detallados'] = array_slice($errores, 0, 10); // Mostrar solo primeros 10 errores
    }

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Error en actualizar_proveedores_claves: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error en actualizar_proveedores_claves: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>