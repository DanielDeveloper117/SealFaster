<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    echo "=== ACTUALIZACIÓN MASIVA DE FECHAS VENCIMIENTO ===\n";
    
    // Configurar timezone
    date_default_timezone_set('America/Mexico_City');
    
    // 1. Contar registros
    $sqlCount = "SELECT COUNT(*) as total FROM cotizacion_materiales";
    $stmtCount = $conn->query($sqlCount);
    $totalRegistros = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "Registros totales: " . $totalRegistros . "\n";
    
    // 2. Obtener TODOS los registros con fecha_insercion (no solo los null)
    $sqlSelect = "SELECT id_estimacion, fecha_insercion 
                  FROM cotizacion_materiales 
                  WHERE fecha_insercion IS NOT NULL 
                  AND fecha_insercion != '0000-00-00 00:00:00'";
    
    $stmtSelect = $conn->query($sqlSelect);
    $registros = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Registros con fecha_insercion válida: " . count($registros) . "\n\n";
    
    if (count($registros) === 0) {
        echo "❌ No hay registros con fecha_insercion válida.\n";
        exit;
    }
    
    // 3. Preparar consulta de actualización
    $sqlUpdate = "UPDATE cotizacion_materiales 
                  SET fecha_vencimiento = :fecha_vencimiento 
                  WHERE id_estimacion = :id_estimacion";
    
    $stmtUpdate = $conn->prepare($sqlUpdate);
    
    $actualizados = 0;
    $errores = 0;
    
    // 4. Procesar cada registro
    echo "Iniciando actualización masiva...\n";
    
    foreach ($registros as $registro) {
        $id_estimacion = $registro['id_estimacion'];
        $fecha_insercion = $registro['fecha_insercion'];
        
        try {
            // Calcular fecha_vencimiento = fecha_insercion + 72 horas
            $fecha_insercion_obj = new DateTime($fecha_insercion);
            $fecha_vencimiento_obj = clone $fecha_insercion_obj;
            $fecha_vencimiento_obj->modify('+72 hours');
            $fecha_vencimiento = $fecha_vencimiento_obj->format('Y-m-d H:i:s');
            
            // Actualizar el registro
            $stmtUpdate->bindParam(':fecha_vencimiento', $fecha_vencimiento);
            $stmtUpdate->bindParam(':id_estimacion', $id_estimacion);
            $stmtUpdate->execute();
            
            $actualizados++;
            
            // Mostrar progreso cada 50 registros
            if ($actualizados % 50 === 0) {
                echo "Procesados: $actualizados registros...\n";
            }
            
        } catch (Exception $e) {
            echo "ERROR en ID $id_estimacion: " . $e->getMessage() . "\n";
            $errores++;
        }
    }
    
    // 5. Mostrar resumen
    echo "\n=== RESUMEN FINAL ===\n";
    echo "Total registros en tabla: $totalRegistros\n";
    echo "Registros procesados: " . count($registros) . "\n";
    echo "Registros actualizados exitosamente: $actualizados\n";
    echo "Errores: $errores\n";
    
    // 6. Verificación inmediata
    echo "\n=== VERIFICACIÓN INMEDIATA ===\n";
    $sqlVerificacion = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN fecha_vencimiento IS NOT NULL AND fecha_vencimiento != '0000-00-00 00:00:00' THEN 1 ELSE 0 END) as con_fecha_vencimiento,
                        SUM(CASE WHEN fecha_vencimiento IS NULL OR fecha_vencimiento = '0000-00-00 00:00:00' THEN 1 ELSE 0 END) as sin_fecha_vencimiento
                        FROM cotizacion_materiales";
    
    $stmtVerificacion = $conn->query($sqlVerificacion);
    $verificacion = $stmtVerificacion->fetch(PDO::FETCH_ASSOC);
    
    echo "Total: " . $verificacion['total'] . "\n";
    echo "Con fecha_vencimiento: " . $verificacion['con_fecha_vencimiento'] . "\n";
    echo "Sin fecha_vencimiento: " . $verificacion['sin_fecha_vencimiento'] . "\n";
    
    if ($verificacion['sin_fecha_vencimiento'] > 0) {
        echo "\n🔍 Analizando registros sin fecha_vencimiento...\n";
        
        $sqlSinFecha = "SELECT COUNT(*) as sin_fecha_insercion 
                        FROM cotizacion_materiales 
                        WHERE fecha_insercion IS NULL OR fecha_insercion = '0000-00-00 00:00:00'";
        
        $stmtSinFecha = $conn->query($sqlSinFecha);
        $sinFechaInsercion = $stmtSinFecha->fetch(PDO::FETCH_ASSOC)['sin_fecha_insercion'];
        
        echo "Registros sin fecha_insercion válida: $sinFechaInsercion\n";
    }
    
    echo "\n✅ ¡Actualización masiva completada!\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    $conn = null;
    echo "Conexión cerrada.\n";
}
?>