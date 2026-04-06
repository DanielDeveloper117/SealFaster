<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');
    
    // Verificar que se recibió el parámetro origen
    if (!isset($_GET['origen'])) {
        echo json_encode(['success' => false, 'error' => 'Parámetro origen no proporcionado']);
        exit;
    }
    
    $origen = $_GET['origen'];
    $registros = [];
    
    if ($origen === 'coti') {
        // ORIGEN: Cotización individual
        if (!isset($_GET['id_cotizacion']) || empty($_GET['id_cotizacion'])) {
            echo json_encode(['success' => false, 'error' => 'ID de cotización no proporcionado']);
            exit;
        }
        
        $id_cotizacion = $_GET['id_cotizacion'];
        
        // Consultar comentarios para una cotización específica
        $sql = "SELECT id, id_cotizacion, comentario, ruta_adjunto, fecha_creacion 
                FROM comentarios_adjuntos 
                WHERE id_cotizacion = :id_cotizacion 
                ORDER BY id ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id_cotizacion', $id_cotizacion);
        $stmt->execute();
        
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } elseif ($origen === 'requi') {
        // ORIGEN: Requisición (múltiples cotizaciones)
        if (!isset($_GET['id_requisicion']) || empty($_GET['id_requisicion'])) {
            echo json_encode(['success' => false, 'error' => 'ID de requisición no proporcionado']);
            exit;
        }
        
        $id_requisicion = $_GET['id_requisicion'];
        
        // 1. Primero obtener las cotizaciones de la requisición
        $sql_requisicion = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
        $stmt_requisicion = $conn->prepare($sql_requisicion);
        $stmt_requisicion->bindParam(':id_requisicion', $id_requisicion);
        $stmt_requisicion->execute();
        
        $requisicion = $stmt_requisicion->fetch(PDO::FETCH_ASSOC);
        
        if (!$requisicion || empty($requisicion['cotizaciones'])) {
            echo json_encode([
                'success' => true,
                'registros' => [],
                'message' => 'No se encontraron cotizaciones en la requisición'
            ]);
            exit;
        }
        
        // 2. Descomponer las cotizaciones (formato: "13467985,89985464")
        $ids_cotizaciones = array_filter(
            array_map('trim', explode(',', $requisicion['cotizaciones'])),
            function($id) {
                return !empty($id) && is_numeric($id);
            }
        );
        if (empty($ids_cotizaciones)) {
            echo json_encode([
                'success' => true,
                'registros' => [],
                'message' => 'No hay IDs de cotización válidos en la requisición'
            ]);
            exit;
        }
        // Devolver resultados, solo cotizaciones si se solicita
        if (isset($_GET['solo_cotizaciones']) && $_GET['solo_cotizaciones'] == 'true') {
            echo json_encode([
                'success' => true,
                'registros' => $ids_cotizaciones,
                'origen' => $origen,
                'total_registros' => count($ids_cotizaciones)
            ]);
            exit;
        }
        
        // 3. Consultar comentarios para todas las cotizaciones de la requisición
        $placeholders = str_repeat('?,', count($ids_cotizaciones) - 1) . '?';
        $sql = "SELECT id, id_cotizacion, comentario, ruta_adjunto, fecha_creacion 
                FROM comentarios_adjuntos 
                WHERE id_cotizacion IN ($placeholders) 
                ORDER BY id ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($ids_cotizaciones);
        
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Origen no válido. Use "coti" o "requi"']);
        exit;
    }
    
    // Devolver resultados
    echo json_encode([
        'success' => true,
        'registros' => $registros,
        'origen' => $origen,
        'total_registros' => count($registros)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>