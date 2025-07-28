<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');
    if (isset($_POST['material'])) {
        $material = $_POST['material'];
        
        $altura_mm = $_POST['altura_mm'];
        $diametro_interior_mm = $_POST['diametro_interior_mm'];
        $diametro_exterior_mm = $_POST['diametro_exterior_mm'];
        // Verificar si 'arreglo_excluir' existe y es un array
        $arreglo_excluir = isset($_POST['arreglo_excluir']) ? $_POST['arreglo_excluir'] : [];
        if (!is_array($arreglo_excluir)) {
            $arreglo_excluir = [];
        }

        // Base de la consulta
        $sql = "
            SELECT * FROM inventario_cnc 
            WHERE material = :material 
            AND stock >= :stock 
            AND interior <= :interior 
            AND exterior >= :exterior 
            AND estatus = 'Habilitado'
        ";

        // Solo agregar condiciones de exclusión si el arreglo no está vacío
        if (!empty($arreglo_excluir)) {
            $excluir_params = [];
            foreach ($arreglo_excluir as $index => $valor) {
                $param_name = ":excluir_$index"; 
                $sql .= " AND lote_pedimento != $param_name";
                $excluir_params[$param_name] = $valor;
            }
        }

        // Agregar ordenamiento
        $sql .= " ORDER BY 
            ABS(interior - :interior) ASC,
            ABS(exterior - :exterior) ASC
        ";

        $stmt = $conn->prepare($sql);

        // Asignar valores a los parámetros
        $stmt->bindParam(':material', $material);
        $stmt->bindParam(':stock', $altura_mm);
        $stmt->bindParam(':interior', $diametro_interior_mm);
        $stmt->bindParam(':exterior', $diametro_exterior_mm);

        // Si hay valores para excluir, los asignamos
        if (!empty($arreglo_excluir)) {
            foreach ($excluir_params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
        }

        $stmt->execute();

        // Obtener resultados
        $billets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Devolver los resultados en formato JSON
        echo json_encode($billets);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>