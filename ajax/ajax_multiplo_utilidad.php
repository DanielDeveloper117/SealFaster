<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    // ---------- 1. Parámetros recibidos ----------
    $diametroInterior = isset($_GET['di']) ? (float)$_GET['di'] : null;
    $nombreMaterial = isset($_GET['material']) ? trim($_GET['material']) : '';
    $nombreProveedor = isset($_GET['proveedor']) ? trim($_GET['proveedor']) : '';
    $nombreMaterialPrefijo = "mu" . $nombreMaterial;

    if ($diametroInterior === null || $nombreMaterial === '' || $nombreProveedor === '') {
        echo json_encode(['error' => 'Faltan parametros GET requeridos: proveedor, material, di.']);
        exit;
    }

    // ---------- 2. Multiplicador por MATERIAL ----------
    $stmt = $conn->prepare("
        SELECT valor 
        FROM parametros2 
        WHERE caso = :caso 
        AND limite_inferior <= :di1 
        AND :di2 <= limite_superior
    ");
    $stmt->bindValue(':caso', $nombreMaterialPrefijo, PDO::PARAM_STR);
    $stmt->bindValue(':di1', $diametroInterior);
    $stmt->bindValue(':di2', $diametroInterior);
    $stmt->execute();
    $registroMaterial = $stmt->fetch(PDO::FETCH_ASSOC);

    // ---------- 3. Multiplicador por PROVEEDOR ----------
    $stmt = $conn->prepare("
        SELECT valor 
        FROM parametros2 
        WHERE caso = :caso 
        AND descripcion = 'MultiplicadorUtilidadProveedor'
    ");
    $stmt->bindValue(':caso', $nombreProveedor, PDO::PARAM_STR);
    $stmt->execute();
    $registroProveedor = $stmt->fetch(PDO::FETCH_ASSOC);

    // ---------- 4. Multiplicadores PERSONALIZADOS (todos) ----------
    $stmt = $conn->prepare("
        SELECT caso, valor 
        FROM parametros2 
        WHERE descripcion = 'MultiplicadorUtilidadPersonalizado'
    ");
    $stmt->execute();
    $parametrosPersonalizados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ---------- 5. Evaluar parámetros personalizados ----------
    $valorSeleccionado = null;
    $casoSeleccionado = null;
    $prioridadSeleccionada = null;
    $menorDiferencia = PHP_FLOAT_MAX;
    $coincidencias = [];

    foreach ($parametrosPersonalizados as $registro) {
        $casoRaw = trim($registro['caso']);    // texto tal cual en BD
        $valorRegistro = (float)$registro['valor'];

        // Detectar si contiene condicion DI (ej: (di>=400))
        $hasDI = false;
        $operador = null;
        $valorCondDI = null;
        if (preg_match('/\(di\s*(>=|<=|>|<)\s*(\d+(\.\d+)?)\)/i', $casoRaw, $m)) {
            $hasDI = true;
            $operador = $m[1];
            $valorCondDI = (float)$m[2];
        }

        // Antes de cualquier otra cosa: evaluar condicion DI si existe
        if ($hasDI) {
            $cumpleDI = false;
            switch ($operador) {
                case '>':  $cumpleDI = ($diametroInterior > $valorCondDI); break;
                case '<':  $cumpleDI = ($diametroInterior < $valorCondDI); break;
                case '>=': $cumpleDI = ($diametroInterior >= $valorCondDI); break;
                case '<=': $cumpleDI = ($diametroInterior <= $valorCondDI); break;
            }
            if (!$cumpleDI) {
                // si tiene DI pero la condicion no se cumple -> ignorar
                continue;
            }
        }

        // Construir lista de subcomponentes **incluyendo** la parte DI como elemento si existe
        // Para decidir la forma exacta (2 o 3 subparametros) consideramos la estructura real:
        $parts = array_values(array_filter(array_map('trim', explode('+', $casoRaw))));
        // Normalizamos partes (sin paréntesis en la condicion ya que viene como "(di>=400)")
        // parts puede contener: ['SLM', 'ECOMID', '(di>=400)'] o ['SLM', 'ECOMID']
        $numParts = count($parts);

        // Determinar si este registro corresponde (respecto al input) y cuál es su prioridad
        // Reglas permitidas: (siempre 2 o 3 subparametros)
        // prioridad 1 -> proveedor + material + di
        // prioridad 2 -> proveedor + material
        // prioridad 3 -> material + di
        // prioridad 4 -> proveedor + di

        $priority = null;
        $matchesProvider = false;
        $matchesMaterial = false;

        // Check presence of provider/material tokens inside parts (case-insensitive)
        foreach ($parts as $p) {
            if (strcasecmp($p, $nombreProveedor) === 0) $matchesProvider = true;
            if (strcasecmp($p, $nombreMaterial) === 0) $matchesMaterial = true;
        }

        // Decide priority only si la combinacion cumple con una de las formas validas **relativas al input**
        if ($numParts === 3) {
            // debe contener provider + material + (di...); ambos provider y material deben coincidir
            if ($matchesProvider && $matchesMaterial && $hasDI) {
                $priority = 1;
            } else {
                // 3 partes que no contienen ambos provider y material => ignorar
                continue;
            }
        } elseif ($numParts === 2) {
            // Puede ser:
            // - provider + material  (ambos tokens presentes)
            // - provider + (di...)   (provider + DI token)
            // - material + (di...)   (material + DI token)
            if ($matchesProvider && $matchesMaterial && !$hasDI) {
                $priority = 2; // provider + material (sin DI)
            } elseif ($hasDI && $matchesMaterial && !$matchesProvider) {
                // e.g. "ECOPUR+(di<400)" -> material + di
                $priority = 3;
            } elseif ($hasDI && $matchesProvider && !$matchesMaterial) {
                // e.g. "SLM+(di>=500)" -> provider + di
                $priority = 4;
            } else {
                // si no encaja en ninguna forma válida para este input -> ignorar
                continue;
            }
        } else {
            // no es 2 ni 3 partes -> ignorar (según regla de negocio)
            continue;
        }

        // Guardamos coincidencia legible
        $coincidencias[] = [
            'caso' => $casoRaw,
            'valor' => $valorRegistro,
            'hasDI' => $hasDI,
            'condicionDI' => $hasDI ? ($operador . $valorCondDI) : null,
            'prioridad' => $priority
        ];

        // Evaluar candidato según prioridad y proximidad (si aplica DI)
        $diferencia = $hasDI ? abs($diametroInterior - $valorCondDI) : 0.0;

        if (
            $prioridadSeleccionada === null ||
            $priority < $prioridadSeleccionada ||
            ($priority === $prioridadSeleccionada && $diferencia < $menorDiferencia)
        ) {
            $valorSeleccionado = $valorRegistro;
            $casoSeleccionado = $casoRaw;
            $prioridadSeleccionada = $priority;
            $menorDiferencia = $diferencia;
        }
    } // end foreach personalizados

    // ---------- 6. Determinar multiplicador final ----------
    $valorPorMaterial = isset($registroMaterial['valor']) ? (float)$registroMaterial['valor'] : null;
    $valorPorProveedor = isset($registroProveedor['valor']) ? (float)$registroProveedor['valor'] : null;

    $mensaje = "";
    if ($valorSeleccionado !== null) {
        $multiploFinal = $valorSeleccionado;
        $mensaje = "Multiplo personalizado aplicado: {$multiploFinal}, caso: {$casoSeleccionado}.";
    } else {
        if ($valorPorMaterial !== null && $valorPorProveedor !== null) {
            $multiploFinal = min($valorPorMaterial, $valorPorProveedor);
            $mensaje = "Multiplo aplicado: menor entre proveedor ({$valorPorProveedor}) y material ({$valorPorMaterial}).";
            $prioridadSeleccionada = 5;
            $casoSeleccionado = 'Proveedor/Material';
        } elseif ($valorPorMaterial !== null) {
            $multiploFinal = $valorPorMaterial;
            $mensaje = "Multiplo aplicado por material ({$valorPorMaterial}).";
            $prioridadSeleccionada = 5;
            $casoSeleccionado = $nombreMaterial;
        } elseif ($valorPorProveedor !== null) {
            $multiploFinal = $valorPorProveedor;
            $mensaje = "Multiplo aplicado por proveedor ({$valorPorProveedor}).";
            $prioridadSeleccionada = 5;
            $casoSeleccionado = $nombreProveedor;
        } else {
            echo json_encode(['error' => 'No se encontraron valores validos para proveedor ni material.']);
            exit;
        }
    }

    // ---------- 7. Respuesta JSON ----------
    echo json_encode([
        'valor' => $multiploFinal,
        'detalle' => [
            'proveedor' => $nombreProveedor,
            'material' => $nombreMaterial,
            'diametro_recibido' => $diametroInterior,
            'caso_aplicado' => $casoSeleccionado,
            'prioridad' => $prioridadSeleccionada
        ],
        'coincidencias' => $coincidencias,
        'mensaje' => $mensaje
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>
