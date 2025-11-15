<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    // Materiales a buscar
    $materiales = [
        "ECORUBBER 1", "ECORUBBER 2", "ECORUBBER 3", 
        "ECOPUR", "H-ECOPUR", 
        "ECOFLON 1", "ECOFLON 2", "ECOFLON 3","ECOTAL"
    ];

    // Crear placeholders para la consulta
    $placeholders = str_repeat('?,', count($materiales) - 1) . '?';
    
    // Consulta para obtener los registros que cumplen con los criterios
    $stmt = $conn->prepare("
        SELECT id, Clave, proveedor, material 
        FROM inventario_cnc 
        WHERE proveedor = 'TRYGONAL' 
        AND material IN ($placeholders)
    ");
    
    $stmt->execute($materiales);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $registrosActualizados = 0;
    $errores = [];
    
    // Recorrer cada registro y procesar la clave
    foreach ($registros as $registro) {
        $claveOriginal = $registro['Clave'];
        $id = $registro['id'];
        
        // Verificar si la clave tiene la estructura correcta (números + letras)
        if (preg_match('/^(\d+)([A-Z].*)$/', $claveOriginal, $matches)) {
            $parteNumerica = $matches[1];
            $parteAlfabetica = $matches[2];
            
            // Verificar que la parte numérica tenga al menos 3 dígitos para eliminar
            if (strlen($parteNumerica) >= 3) {
                // Eliminar los últimos 3 dígitos
                $nuevaParteNumerica = substr($parteNumerica, 0, -3);
                
                // Construir la nueva clave
                $nuevaClave = $nuevaParteNumerica . $parteAlfabetica;
                
                // Actualizar el registro
                $updateStmt = $conn->prepare("UPDATE inventario_cnc SET Clave = :nueva_clave WHERE id = :id");
                $updateStmt->execute([
                    ':nueva_clave' => $nuevaClave,
                    ':id' => $id
                ]);
                
                if ($updateStmt->rowCount() > 0) {
                    $registrosActualizados++;
                } else {
                    $errores[] = "No se pudo actualizar el registro ID: $id, Clave: $claveOriginal";
                }
            } else {
                $errores[] = "Clave con parte numérica muy corta - ID: $id, Clave: $claveOriginal";
            }
        } else {
            // La clave no cumple con el patrón números+letras, se omite
            $errores[] = "Clave no cumple patrón - ID: $id, Clave: $claveOriginal";
        }
    }

    // Preparar respuesta
    $response = [
        'success' => true,
        'message' => "Proceso completado. Registros actualizados: $registrosActualizados",
        'registros_actualizados' => $registrosActualizados,
        'total_registros_procesados' => count($registros)
    ];
    
    // Agregar errores si existen
    if (!empty($errores)) {
        $response['errores'] = $errores;
        $response['total_errores'] = count($errores);
    }

    echo json_encode($response);

} catch (PDOException $e) {
    error_log("Error en actualizar_claves_trygonal: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("Error en actualizar_claves_trygonal: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>