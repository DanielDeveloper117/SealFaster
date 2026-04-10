<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json'); // Asegurar respuesta JSON

    $id = $_GET['id'] ?? null;

    if ($id) {
        // 1. Obtener la requisición
        $stmt = $conn->prepare("SELECT * FROM requisiciones WHERE id_requisicion = ?");
        $stmt->execute([$id]);
        $requisicion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$requisicion) {
            echo json_encode([
                'success' => false,
                'message' => 'Requisición no encontrada'
            ]);
            exit;
        }

        // 2. Obtener los detalles de las cotizaciones asociadas
        // Convertimos la cadena "101, 102" en un array para la consulta
        $ids = array_map('trim', explode(',', $requisicion['cotizaciones']));
        $ids = array_filter($ids, function($v) { return $v !== ''; });
        
        if (count($ids) > 0) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            
            // AGRUPAMOS por id_cotizacion para que no se repitan
            // Una cotización puede tener múltiples estimaciones (componentes),
            // pero en el selector solo necesitamos ver 1 entrada por id_cotizacion
            $stmtCot = $conn->prepare("
                SELECT 
                    id_cotizacion,
                    MAX(perfil_sello) as perfil_sello,
                    MAX(di_sello) as di_sello,
                    MAX(di_sello2) as di_sello2,
                    MAX(de_sello) as de_sello,
                    MAX(de_sello2) as de_sello2,
                    MAX(a_sello) as a_sello,
                    MAX(a_sello2) as a_sello2,
                    MAX(tipo_medida) as tipo_medida
                FROM cotizacion_materiales 
                WHERE id_cotizacion IN ($placeholders)
                GROUP BY id_cotizacion
            ");
            $stmtCot->execute(array_values($ids));
            $detallesCot = $stmtCot->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $detallesCot = [];
        }

        echo json_encode([
            'success' => true,
            'requisicion' => $requisicion,
            'cotizaciones_detalles' => $detallesCot
        ]);
    }else{
        echo json_encode([
            'success' => false,
            'message' => 'Falta el parámetro id'
        ]);
        exit;

    }


} catch (PDOException $e) {
    error_log("Error en la consulta: " . $e->getMessage());
    // Devolver un mensaje más descriptivo para depuración sin exponer detalles sensibles
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor',
        'error_detail' => $e->getMessage()
    ]);
    http_response_code(500);
} finally {
    $conn = null; // Cerrar la conexión
}

?>