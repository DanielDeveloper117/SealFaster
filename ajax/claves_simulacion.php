<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    if (isset($_GET['material'])) {

        $material = $_GET['material'];
        $diametro_interior_mm = $_GET['diametro_interior_mm'];
        $diametro_exterior_mm = $_GET['diametro_exterior_mm'];
        
        // Parámetro "u" para determinar si incluir o excluir proveedor
        $modo = isset($_GET['u']) ? $_GET['u'] : 'a'; // Por defecto 'a' (con proveedor)
        
        // Si u='a', se requiere proveedor; si u='v', no se requiere
        if ($modo === 'a') {
            if (!isset($_GET['proveedor'])) {
                echo json_encode([
                    'error' => 'Parámetro proveedor no proporcionado (modo u="a" requiere proveedor)',
                    'parametros_requeridos' => ['material', 'proveedor', 'diametro_interior_mm', 'diametro_exterior_mm', 'u']
                ]);
                exit;
            }
            $proveedor = $_GET['proveedor'];
        }

        // Mapeo de materiales de inventario_cnc a patrones de parametros (igual que en actualizar_claves_inventario.php)
        $mapeoMateriales = [
            "H-ECOPUR" => "PU ROJO",
            "ECOSIL" => "SILICON",
            "ECORUBBER 1" => "NITRILO",
            "ECORUBBER 2" => "VITON", 
            "ECORUBBER 3" => "EPDM",
            "ECOPUR" => "PU VERDE",
            "ECOTAL" => "ECOTAL",
            "ECOMID" => "ECOMID",
            "ECOFLON 1" => "VIRGEN",
            "ECOFLON 2" => ["NIKEL", "MOLLY"], // Múltiples opciones para ECOFLON 2
            "ECOFLON 3" => "BRONCE"
        ];

        // Verificar si el material está en el mapeo
        if (!isset($mapeoMateriales[$material])) {
            echo json_encode([
                'error' => 'Material no encontrado en el mapeo',
                'material_solicitado' => $material,
                'materiales_mapeados_disponibles' => array_keys($mapeoMateriales)
            ]);
            exit;
        }

        $materialParametros = $mapeoMateriales[$material];

        // ------------------ CONSTRUIR CONSULTA BASE CON MAPEO ------------------
        if (is_array($materialParametros)) {
            // Para materiales con múltiples opciones (ECOFLON 2)
            $sql = "
                SELECT *
                FROM parametros
                WHERE interior <= :interior
                  AND exterior >= :exterior";
            
            // Agregar condición de proveedor solo si u='a'
            if ($modo === 'a') {
                $sql .= " AND proveedor = :proveedor";
            }
            
            $sql .= " AND (";
            
            // Construir condiciones OR para cada material
            $orConditions = [];
            foreach ($materialParametros as $index => $mat) {
                $orConditions[] = "material LIKE CONCAT('%', :material_" . $index . ", '%')";
            }
            $sql .= implode(" OR ", $orConditions) . ")";
            
        } else {
            // Para materiales con una sola opción
            $sql = "
                SELECT *
                FROM parametros
                WHERE interior <= :interior
                  AND exterior >= :exterior";
            
            // Agregar condición de proveedor solo si u='a'
            if ($modo === 'a') {
                $sql .= " AND proveedor = :proveedor";
            }
            
            $sql .= " AND material LIKE CONCAT('%', :material, '%')";
        }

        // ------------------ ORDENAMIENTO ------------------
        $sql .= "
            ORDER BY
                ABS(interior - :interior_order) ASC,
                ABS(exterior - :exterior_order) ASC
        ";

        // ------------------ PREPARE ------------------
        $stmt = $conn->prepare($sql);

        // Bind de parámetros comunes
        $stmt->bindValue(':interior', $diametro_interior_mm);
        $stmt->bindValue(':exterior', $diametro_exterior_mm);
        $stmt->bindValue(':interior_order', $diametro_interior_mm);
        $stmt->bindValue(':exterior_order', $diametro_exterior_mm);

        // Bind del proveedor solo si u='a'
        if ($modo === 'a') {
            $stmt->bindValue(':proveedor', $proveedor);
        }

        // Bind del material según el tipo
        if (is_array($materialParametros)) {
            foreach ($materialParametros as $index => $mat) {
                $stmt->bindValue(':material_' . $index, $mat);
            }
        } else {
            $stmt->bindValue(':material', $materialParametros);
        }

        $stmt->execute();
        $billets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Agregar información del mapeo aplicado a la respuesta
        $response = [
            'claves' => $billets,
            'mapeo_aplicado' => [
                'material_solicitado' => $material,
                'material_buscado_en_parametros' => $materialParametros,
                'modo_busqueda' => $modo === 'a' ? 'con proveedor' : 'sin proveedor',
                'diametro_interior_mm' => $diametro_interior_mm,
                'diametro_exterior_mm' => $diametro_exterior_mm
            ]
        ];
        
        // Agregar proveedor a la respuesta solo si se usó
        if ($modo === 'a') {
            $response['mapeo_aplicado']['proveedor'] = $proveedor;
        }
        
        $response['total_resultados'] = count($billets);

        echo json_encode($response);
    } else {
        echo json_encode([
            'error' => 'Parámetro material no proporcionado',
            'parametros_requeridos' => ['material', 'diametro_interior_mm', 'diametro_exterior_mm', 'u'],
            'nota' => 'Parámetro "proveedor" es obligatorio solo cuando u="a"'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error en la base de datos: ' . $e->getMessage(),
        'mapeo_materiales_disponibles' => array_keys($mapeoMateriales ?? [])
    ]);
} finally {
    $conn = null;
}