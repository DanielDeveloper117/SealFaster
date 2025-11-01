<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
try{
    header('Content-Type: application/json');

    $id_usuario = $_SESSION['id'];
    
    // Preparar la consulta - SIN CÁLCULO EN SQL
    $sqlCotizaciones = "SELECT * FROM cotizacion_materiales 
                        WHERE id_usuario = :id  
                        AND archivada = 0 
                        GROUP BY id_cotizacion 
                        ORDER BY fecha DESC, hora DESC";
    
    $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
    $stmtCotizaciones->bindParam(':id', $id_usuario);
    $stmtCotizaciones->execute();
    $cotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);

    // Calcular vencimiento en PHP con misma lógica
    $fecha_actual = new DateTime('now', new DateTimeZone('America/Mexico_City'));
    
    // Agregar campo de vencimiento a cada cotización
    foreach ($cotizaciones as &$cotizacion) {
        if (isset($cotizacion['fecha_vencimiento'])) {
            $fecha_vencimiento = new DateTime($cotizacion['fecha_vencimiento'], new DateTimeZone('America/Mexico_City'));
            $cotizacion['esta_vencida'] = ($fecha_vencimiento < $fecha_actual) ? 1 : 0;
            
            // Calcular horas restantes
            $diferencia = $fecha_vencimiento->getTimestamp() - $fecha_actual->getTimestamp();
            $cotizacion['horas_restantes'] = floor($diferencia / 3600);
        } else {
            // Si no tiene fecha_vencimiento, considerar como no vencida
            $cotizacion['esta_vencida'] = 0;
            $cotizacion['horas_restantes'] = 999; // Valor alto para indicar no vence
        }
    }

    // Devolver los resultados en formato JSON
    echo json_encode($cotizaciones);

} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>