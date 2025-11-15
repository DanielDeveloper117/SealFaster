<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try{
    header('Content-Type: application/json');
    if (isset($_GET['clave'])) {
        // Eliminar todos los espacios en blanco de la clave antes de consultar
        $clave = preg_replace('/\s+/', '', trim($_GET['clave']));
        $claveFinal = $clave;
        $esAlterna = 0;
        $claveAlterna = $clave;

        // PRIMERO: Consultar en claves_alternas
        $stmtAlterna = $conn->prepare("SELECT clave_srs, clave_alterna FROM claves_alternas WHERE clave_alterna = :clave");
        $stmtAlterna->bindParam(':clave', $clave);
        $stmtAlterna->execute();
        $claveAlternaData = $stmtAlterna->fetch(PDO::FETCH_ASSOC);

        if ($claveAlternaData) {
            // Si existe en claves_alternas, usar clave_srs para consultar parametros
            $claveFinal = $claveAlternaData['clave_srs'];
            $esAlterna = 1;
            $claveAlterna = $claveAlternaData['clave_alterna'];
        }

        // SEGUNDO: Consultar en parametros con la clave final
        $stmt = $conn->prepare("SELECT * FROM parametros WHERE clave = :clave AND max_usable != 0 AND precio != 0.00");
        $stmt->bindParam(':clave', $claveFinal);
        $stmt->execute();
        
        // Obtener resultados
        $billet = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mapeo inverso: de material de parametros a material de inventario_cnc
        $mapeoInversoMateriales = [
            "PU ROJO" => "H-ECOPUR",
            "SILICON" => "ECOSIL",
            "NITRILO" => "ECORUBBER 1",
            "VITON" => "ECORUBBER 2", 
            "EPDM" => "ECORUBBER 3",
            "PU VERDE" => "ECOPUR",
            "ECOTAL" => "ECOTAL",
            "ECOMID" => "ECOMID",
            "VIRGEN" => "ECOFLON 1",
            "NIKEL" => "ECOFLON 2",
            "MOLLY" => "ECOFLON 2",
            "BRONCE" => "ECOFLON 3"
        ];

        // Procesar cada resultado para convertir el material y agregar info de clave alterna
        foreach ($billet as &$registro) {
            if (isset($registro['material'])) {
                $materialParametros = $registro['material'];
                $materialInventario = null;
                
                // Buscar coincidencia en el mapeo inverso
                foreach ($mapeoInversoMateriales as $patron => $materialMapeado) {
                    if (stripos($materialParametros, $patron) !== false) {
                        $materialInventario = $materialMapeado;
                        break;
                    }
                }
                
                // Si no se encuentra coincidencia, mantener el material original
                $registro['material_corregido'] = $materialInventario ?: $materialParametros;
            }

            // Agregar información de clave alterna
            $registro['es_alterna'] = $esAlterna;
            $registro['clave_alterna'] = $claveAlterna;
            $registro['clave_original_buscada'] = $clave;
            $registro['clave_srs_utilizada'] = $claveFinal;
        }

        // CASO 1: No existe en claves_alternas ni en parametros
        if (empty($billet) && !$claveAlternaData) {
            echo json_encode([
                'es_alterna' => 0,
                'no_encontrada' => true,
                'mensaje' => 'No se encontró clave SRS, no se encontró clave alterna'
            ]);
        }
        // CASO 2: Existe en claves_alternas pero clave_srs es null (sin relación)
        else if (empty($billet) && $claveAlternaData && $claveAlternaData['clave_srs'] === null) {
            echo json_encode([
                'es_alterna' => 1,
                'sin_relacion' => true,
                'clave_alterna' => $claveAlternaData['clave_alterna'],
                'clave_srs' => null,
                'mensaje' => 'Clave alterna encontrada pero no tiene relación con clave SRS'
            ]);
        }
        // CASO 3: Existe en claves_alternas y tiene relación (clave_srs no es null) pero no existe en parametros
        else if (empty($billet) && $claveAlternaData && $claveAlternaData['clave_srs'] !== null) {
            echo json_encode([
                'es_alterna' => 1,
                'no_en_parametros' => true,
                'clave_alterna' => $claveAlternaData['clave_alterna'],
                'clave_srs' => $claveAlternaData['clave_srs'],
                'mensaje' => 'Clave alterna encontrada pero no existe en parametros'
            ]);
        }
        // CASO 4: Existe en parametros (con o sin ser clave alterna)
        else {
            echo json_encode($billet);
        }
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>