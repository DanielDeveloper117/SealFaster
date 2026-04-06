<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    if (!isset($_GET['clave'])) {
        echo json_encode(['no_encontrada' => true, 'mensaje' => 'Parámetro clave no proporcionado.']);
        exit;
    }

    // Limpiar espacios de la clave ingresada
    $clave_input = preg_replace('/\s+/', '', trim($_GET['clave']));

    if ($clave_input === '') {
        echo json_encode(['no_encontrada' => true, 'mensaje' => 'Clave vacía.']);
        exit;
    }

    // ── Buscar en parametros: coincidencia por clave principal O por clave_alterna ──────────────
    $stmt = $conn->prepare(
        "SELECT * FROM parametros
          WHERE (REPLACE(clave, CHAR(0), '') = :clave1
             OR  REPLACE(clave_alterna, CHAR(0), '') = :clave2)
          AND max_usable != 0
         LIMIT 1"
    );
    $stmt->bindValue(':clave1', $clave_input);
    $stmt->bindValue(':clave2', $clave_input);
    $stmt->execute();
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    // ── No encontrado ────────────────────────────────────────────────────────────────────────────
    if (!$registro) {
        echo json_encode([
            'no_encontrada' => true,
            'mensaje'       => 'No se encontró ninguna clave. Favor de comunicarse con el área de sistemas para alta de clave.'
        ]);
        exit;
    }

    // ── Determinar si el usuario digitó la clave principal o la alterna ──────────────────────────
    $clave_principal = trim(str_replace("\0", '', $registro['clave']         ?? ''));
    $clave_alterna   = trim(str_replace("\0", '', $registro['clave_alterna'] ?? ''));

    $es_alterna = ($clave_alterna !== '' && strtoupper($clave_input) === strtoupper($clave_alterna));

    // ── Mapeo de material ────────────────────────────────────────────────────────────────────────
    $mapeoMateriales = [
        // Prioridad 1: Coincidencias compuestas/específicas
        "PU ROJO"      => "H-ECOPUR",
        "PU VERDE"     => "ECOPUR",
        "PTFE VIRGEN"  => "ECOFLON 1",
        "PTFE NIKEL"   => "ECOFLON 2",
        "PTFE MOLLY"   => "ECOFLON 2",
        "PTFE BRONCE"  => "ECOFLON 3",
        
        // Prioridad 2: Materiales base
        "NITRILO"      => "ECORUBBER 1",
        "VITON"        => "ECORUBBER 2",
        "EPDM"         => "ECORUBBER 3",
        "SILICON"      => "ECOSIL",
        "ECOTAL"       => "ECOTAL",
        "ECOMID"       => "ECOMID",
        "NIKEL"   => "ECOFLON 2",
        "MOLLY"   => "ECOFLON 2",
        "PA"       => "ECOMID", // Mapeo de poliamida
        
        // Prioridad 3: Nombres comerciales directos
        "H-ECOPUR"     => "H-ECOPUR",
        "ECOPUR"       => "ECOPUR",
        "ECOSIL"       => "ECOSIL",
        "ECORUBBER 1"  => "ECORUBBER 1",
        "ECORUBBER 2"  => "ECORUBBER 2",
        "ECORUBBER 3"  => "ECORUBBER 3",
    ];

    $material_raw     = trim(str_replace("\0", '', $registro['material'] ?? ''));
    $material_mapeado = $material_raw;
    foreach ($mapeoMateriales as $patron => $mapeado) {
        if (stripos($material_raw, $patron) !== false) {
            $material_mapeado = $mapeado;
            break;
        }
    }

    // ── Respuesta unificada ──────────────────────────────────────────────────────────────────────
    if ($es_alterna) {
        $mensaje = "Clave alterna encontrada. Clave principal: $clave_principal";
    } else {
        $mensaje = "Clave principal encontrada.";
        if ($clave_alterna !== '') {
            $mensaje .= " Clave alterna: $clave_alterna";
        }
    }

    echo json_encode([[
        // Datos del registro
        'id'                    => $registro['id'],
        'clave'                 => $clave_principal,
        'clave_alterna'         => $clave_alterna,
        'material'              => $material_raw,
        'material_corregido'    => $material_mapeado,
        'proveedor'             => trim(str_replace("\0", '', $registro['proveedor'] ?? '')),
        'interior'              => $registro['interior'] ?? 0,
        'exterior'              => $registro['exterior'] ?? 0,
        'max_usable'            => $registro['max_usable'] ?? 0,
        // Info de coincidencia
        'es_alterna'            => $es_alterna ? 1 : 0,
        'clave_original_buscada'=> $clave_input,
        'clave_srs_utilizada'   => $clave_principal,
        // Mensaje legible
        'mensaje'               => $mensaje,
    ]]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>