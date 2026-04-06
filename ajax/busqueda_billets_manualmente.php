<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
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
                AND exterior >= :exterior
                AND estatus != 'Eliminado'";
        
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

        // --- VERIFICACIÓN DE COTIZACIONES VIGENTES (MISMA LÓGICA QUE ajax_billets.php) ---
        if (!empty($arregloSelectBillets)) {
            // Obtener todos los lotes únicos de los billets encontrados
            $lotes_inventario = array_column($arregloSelectBillets, 'lote_pedimento');
            
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
            foreach ($arregloSelectBillets as &$billet) {
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