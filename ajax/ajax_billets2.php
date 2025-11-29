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
        
        $arreglo_excluir = isset($_POST['arreglo_excluir']) ? $_POST['arreglo_excluir'] : [];
        if (!is_array($arreglo_excluir)) {
            $arreglo_excluir = [];
        }

        // Consulta base para inventario_cnc
        $sql = "
            SELECT * FROM inventario_cnc 
            WHERE material = :material 
         
            AND pre_stock >= :pre_stock 
            AND interior <= :interior 
            AND exterior >= :exterior 
            AND estatus != 'Eliminado'
        ";

        if (!empty($arreglo_excluir)) {
            $excluir_params = [];
            foreach ($arreglo_excluir as $index => $valor) {
                $param_name = ":excluir_$index"; 
                $sql .= " AND lote_pedimento != $param_name";
                $excluir_params[$param_name] = $valor;
            }
        }

        $sql .= " ORDER BY 
            ABS(interior - :interior) ASC,
            ABS(exterior - :exterior) ASC
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':material', $material);
    
        $stmt->bindParam(':pre_stock', $altura_mm);
        $stmt->bindParam(':interior', $diametro_interior_mm);
        $stmt->bindParam(':exterior', $diametro_exterior_mm);

        if (!empty($arreglo_excluir)) {
            foreach ($excluir_params as $param => $value) {
                $stmt->bindValue($param, $value);
            }
        }

        $stmt->execute();
        $billets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // --- VERIFICACIÓN DE COTIZACIONES VIGENTES ---
        if (!empty($billets)) {
            // Obtener todos los lotes únicos de los billets encontrados
            $lotes_inventario = array_column($billets, 'lote_pedimento');
            
            // Consultar cotizaciones vigentes no archivadas del mismo material
            $sql_cotizaciones = "
                SELECT id_cotizacion, billets, vendedor, fecha_vencimiento 
                FROM cotizacion_materiales 
                WHERE archivada = 0 
                AND fecha_vencimiento > NOW()
                AND material = :material
            ";
            
            $stmt_cotizaciones = $conn->prepare($sql_cotizaciones);
            $stmt_cotizaciones->bindParam(':material', $material);
            $stmt_cotizaciones->execute();
            $cotizaciones_vigentes = $stmt_cotizaciones->fetchAll(PDO::FETCH_ASSOC);

            // Crear un mapa de lotes que están en cotización
            $lotes_en_cotizacion = [];
            
            foreach ($cotizaciones_vigentes as $cotizacion) {
                // Dividir la cadena de billets en lotes individuales
                $billets_cotizacion = explode(', ', $cotizacion['billets']);
                
                foreach ($billets_cotizacion as $lote_cotizacion) {
                    $lote_cotizacion = trim($lote_cotizacion);
                    
                    // Verificar si este lote está en nuestro inventario
                    if (in_array($lote_cotizacion, $lotes_inventario)) {
                        // Solo marcar como en cotización si no está ya registrado
                        // (tomamos la primera coincidencia como mencionaste)
                        if (!isset($lotes_en_cotizacion[$lote_cotizacion])) {
                            $lotes_en_cotizacion[$lote_cotizacion] = [
                                'id_cotizacion' => $cotizacion['id_cotizacion'],
                                'vendedor' => $cotizacion['vendedor'],
                                'fecha_vencimiento' => $cotizacion['fecha_vencimiento']
                            ];
                        }
                    }
                }
            }

            // Actualizar el estatus de los billets que están en cotización
            // SOLO si su estatus original era "Disponible para cotizar"
            foreach ($billets as &$billet) {
                $lote = $billet['lote_pedimento'];
                
                if (isset($lotes_en_cotizacion[$lote]) && $billet['estatus'] === 'Disponible para cotizar') {
                    $billet['estatus'] = 'En cotización';
                    $billet['id_cotizacion'] = $lotes_en_cotizacion[$lote]['id_cotizacion'];
                    $billet['vendedor'] = $lotes_en_cotizacion[$lote]['vendedor'];
                    $billet['fecha_vencimiento'] = $lotes_en_cotizacion[$lote]['fecha_vencimiento'];
                }
            }
            unset($billet); // Romper la referencia
        }

        // Devolver los resultados en formato JSON
        echo json_encode($billets);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>