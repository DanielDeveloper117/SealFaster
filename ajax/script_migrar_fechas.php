<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    echo "=== INICIANDO MIGRACIÓN DE FECHAS ===\n";
    
    // 1. Contar registros totales
    $sqlCount = "SELECT COUNT(*) as total FROM cotizacion_materiales";
    $stmtCount = $conn->query($sqlCount);
    $totalRegistros = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "Registros totales a procesar: " . $totalRegistros . "\n";
    
    // 2. Obtener registros que necesitan actualización
    $sqlSelect = "SELECT id_estimacion, fecha, hora FROM cotizacion_materiales 
                  WHERE (fecha IS NOT NULL AND fecha != '0000-00-00') 
                  AND (hora IS NOT NULL AND hora != '00:00:00')
                  AND fecha_insercion IS NULL";
    
    $stmtSelect = $conn->query($sqlSelect);
    $registros = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Registros a actualizar: " . count($registros) . "\n\n";
    
    // 3. Preparar consulta de actualización
    $sqlUpdate = "UPDATE cotizacion_materiales 
                  SET fecha_insercion = :fecha_insercion 
                  WHERE id_estimacion = :id_estimacion";
    
    $stmtUpdate = $conn->prepare($sqlUpdate);
    
    $actualizados = 0;
    $errores = 0;
    
    // 4. Procesar cada registro
    foreach ($registros as $registro) {
        $id = $registro['id_estimacion'];
        $fecha = $registro['fecha'];
        $hora = $registro['hora'];
        
        // Combinar fecha y hora en formato datetime
        $fecha_insercion = $fecha . ' ' . $hora;
        
        // Validar que sea una fecha válida
        if (strtotime($fecha_insercion) === false) {
            echo "ERROR: Fecha/hora inválida para ID $id: $fecha $hora\n";
            $errores++;
            continue;
        }
        
        try {
            // Actualizar el registro
            $stmtUpdate->bindParam(':fecha_insercion', $fecha_insercion);
            $stmtUpdate->bindParam(':id_estimacion', $id);
            $stmtUpdate->execute();
            
            $actualizados++;
            
            // Mostrar progreso cada 100 registros
            if ($actualizados % 100 === 0) {
                echo "Procesados: $actualizados registros...\n";
            }
            
        } catch (PDOException $e) {
            echo "ERROR al actualizar ID $id: " . $e->getMessage() . "\n";
            $errores++;
        }
    }
    
    // 5. Mostrar resumen
    echo "\n=== RESUMEN DE MIGRACIÓN ===\n";
    echo "Registros totales en tabla: $totalRegistros\n";
    echo "Registros actualizados exitosamente: $actualizados\n";
    echo "Errores: $errores\n";
    echo "Registros sin cambios: " . ($totalRegistros - $actualizados - $errores) . "\n";
    
    // 6. Verificar resultados
    echo "\n=== VERIFICACIÓN FINAL ===\n";
    $sqlVerificacion = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN fecha_insercion IS NOT NULL THEN 1 ELSE 0 END) as con_fecha_insercion,
                        SUM(CASE WHEN fecha_insercion IS NULL THEN 1 ELSE 0 END) as sin_fecha_insercion
                        FROM cotizacion_materiales";
    
    $stmtVerificacion = $conn->query($sqlVerificacion);
    $verificacion = $stmtVerificacion->fetch(PDO::FETCH_ASSOC);
    
    echo "Total registros: " . $verificacion['total'] . "\n";
    echo "Con fecha_insercion: " . $verificacion['con_fecha_insercion'] . "\n";
    echo "Sin fecha_insercion: " . $verificacion['sin_fecha_insercion'] . "\n";
    
    if ($verificacion['sin_fecha_insercion'] > 0) {
        echo "\n⚠️  Hay registros sin fecha_insercion. Posibles causas:\n";
        echo "   - Fecha/hora inválidas\n";
        echo "   - Registros con fecha/hora nulas\n";
    } else {
        echo "\n✅ ¡Migración completada exitosamente!\n";
    }
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    $conn = null;
    echo "\nConexión cerrada.\n";
}
?>