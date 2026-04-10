<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');

    $id_usuario = $_SESSION['id'];
    // Parámetro opcional: excluir una requisición específica (para edición)
    $exclude_req = isset($_GET['exclude_req']) ? intval($_GET['exclude_req']) : 0;
    
    $sqlCotizaciones = "SELECT 
                            cm.id_cotizacion, 
                            MAX(cm.perfil_sello) as perfil_sello, 
                            MAX(cm.di_sello) as di_sello, 
                            MAX(cm.di_sello2) as di_sello2, 
                            MAX(cm.de_sello) as de_sello, 
                            MAX(cm.de_sello2) as de_sello2, 
                            MAX(cm.a_sello) as a_sello, 
                            MAX(cm.a_sello2) as a_sello2, 
                            MAX(cm.tipo_medida) as tipo_medida,
                            MAX(cm.simulacion) as simulacion, 
                            MAX(cm.fecha_vencimiento) as fecha_vencimiento,
                            MAX(cm.fecha) as fecha, 
                            MAX(cm.hora) as hora
                        FROM cotizacion_materiales cm
                        WHERE cm.id_usuario = :id  
                        AND cm.archivada = 0 
                        GROUP BY cm.id_cotizacion 
                        ORDER BY fecha DESC, hora DESC 
                        LIMIT 50";

    $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
    $stmtCotizaciones->bindParam(':id', $id_usuario);
    $stmtCotizaciones->execute();
    $cotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todas las cotizaciones que ya están asignadas a requisiciones activas
    // El campo 'cotizaciones' en requisiciones es un CSV como "101, 102, 103"
    $sqlReqActivas = "SELECT id_requisicion, cotizaciones FROM requisiciones WHERE estatus != 'Archivada'";
    if ($exclude_req > 0) {
        $sqlReqActivas .= " AND id_requisicion != :exclude_req";
    }
    $stmtReq = $conn->prepare($sqlReqActivas);
    if ($exclude_req > 0) {
        $stmtReq->bindParam(':exclude_req', $exclude_req, PDO::PARAM_INT);
    }
    $stmtReq->execute();
    $requisicionesActivas = $stmtReq->fetchAll(PDO::FETCH_ASSOC);

    // Construir un mapa de id_cotizacion => id_requisicion
    $cotizacionesEnUso = [];
    foreach ($requisicionesActivas as $req) {
        $ids = array_map('trim', explode(',', $req['cotizaciones']));
        foreach ($ids as $idCot) {
            if ($idCot !== '') {
                $cotizacionesEnUso[$idCot] = $req['id_requisicion'];
            }
        }
    }

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

        // Marcar si la cotización ya está en uso en otra requisición
        $idCot = $cotizacion['id_cotizacion'];
        if (isset($cotizacionesEnUso[$idCot])) {
            $cotizacion['en_requisicion'] = 1;
            $cotizacion['id_requisicion_asignada'] = $cotizacionesEnUso[$idCot];
        } else {
            $cotizacion['en_requisicion'] = 0;
            $cotizacion['id_requisicion_asignada'] = null;
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