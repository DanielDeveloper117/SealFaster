<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');
    
    // Verificar que todos los parámetros requeridos estén presentes
    if (isset($_GET['diametro_interior'], $_GET['diametro_exterior'], $_GET['material'], $_GET['stock'])) {
        $diametro_interior = $_GET['diametro_interior'];
        $diametro_exterior = $_GET['diametro_exterior'];
        $material = $_GET['material'];
        $stock = $_GET['stock'];
        $excluir_billets = isset($_GET['excluir_billets']) ? $_GET['excluir_billets'] : '';
        
        // Construir la consulta base
        $sql = "SELECT * FROM inventario_cnc 
                WHERE material = :material 
                AND pre_stock >= :stock 
                AND interior <= :interior 
                AND exterior >= :exterior";
        
        // Agregar condición para excluir billets si se proporcionan
        if (!empty($excluir_billets)) {
            $excluir_array = explode(',', $excluir_billets);
            $excluir_placeholders = [];
            
            foreach ($excluir_array as $index => $lote) {
                $param_name = ":excluir_" . $index;
                $excluir_placeholders[] = $param_name;
            }
            
            $sql .= " AND lote_pedimento NOT IN (" . implode(',', $excluir_placeholders) . ")";
        }
        
        // Preparar la consulta
        $stmt = $conn->prepare($sql);
        
        // Bind de parámetros principales
        $stmt->bindParam(':material', $material);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':interior', $diametro_interior);
        $stmt->bindParam(':exterior', $diametro_exterior);
        
        // Bind de parámetros para excluir billets si existen
        if (!empty($excluir_billets)) {
            $excluir_array = explode(',', $excluir_billets);
            foreach ($excluir_array as $index => $lote) {
                $param_name = ":excluir_" . $index;
                $stmt->bindValue($param_name, $lote);
            }
        }
        
        $stmt->execute();
        
        // Obtener resultados
        $arregloSelectBillets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Devolver los resultados en formato JSON
        echo json_encode($arregloSelectBillets);
    } else {
        // Devolver error si faltan parámetros requeridos
        echo json_encode(['error' => 'Parámetros incompletos. Se requieren: diametro_interior, diametro_exterior, material, stock']);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>