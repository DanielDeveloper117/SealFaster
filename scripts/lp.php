<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    $limit = 10000;
    $actualizados = 0;
    $omitidos = 0;
    $errores = 0;
    $detalles_errores = [];

    // Obtener los primeros 100 registros sin billets_lotes
    $query = "SELECT id_estimacion, billets, billets_string 
              FROM cotizacion_materiales 
              WHERE billets_lotes IS NULL OR TRIM(billets_lotes) = ''
              ORDER BY id_estimacion
              LIMIT $limit";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo json_encode(['success' => false, 'message' => 'No hay registros pendientes.']);
        exit;
    }

    $update = $conn->prepare("UPDATE cotizacion_materiales SET billets_lotes = :billets_lotes WHERE id_estimacion = :id_estimacion");

    foreach ($rows as $row) {
        $id_estimacion = $row['id_estimacion'];
        $billets = trim($row['billets'] ?? '');
        $billets_string = trim($row['billets_string'] ?? '');

        // Validar datos
        if ($billets === '' || $billets_string === '') {
            $omitidos++;
            continue;
        }

        // Separar por coma
        $arrBillets = array_map('trim', explode(',', $billets));
        $arrStrings = array_map('trim', explode(',', $billets_string));

        $lenA = count($arrBillets);
        $lenB = count($arrStrings);

        if ($lenA === 0 || $lenB === 0) {
            $omitidos++;
            continue;
        }

        // Si longitudes difieren, se usara el minimo
        if ($lenA !== $lenB) {
            $detalles_errores[] = "ID $id_estimacion: mismatch billets=$lenA billets_string=$lenB";
        }

        $min = min($lenA, $lenB);
        $combined = [];

        for ($i = 0; $i < $min; $i++) {
            $billet = $arrBillets[$i];
            $string = $arrStrings[$i];

            // Extraer parte entre parentesis si existe
            $parte = '';
            $pos = strpos($string, '(');
            if ($pos !== false) {
                $parte = trim(substr($string, $pos)); // ejemplo: (47/72) 6 pz
            } else {
                // fallback: tomar lo que haya despues del primer espacio
                $parts = preg_split('/\s+/', $string, 2);
                if (isset($parts[1])) {
                    $parte = trim($parts[1]);
                }
            }

            $combined[] = $billet . ' ' . $parte;
        }

        $billets_lotes = implode(',', $combined);

        try {
            $update->execute([':billets_lotes' => $billets_lotes, ':id_estimacion' => $id_estimacion]);
            $actualizados++;
        } catch (Exception $e) {
            $errores++;
            $detalles_errores[] = "id_estimacion $id_estimacion: " . $e->getMessage();
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Proceso completado de prueba (100 registros max).',
        'actualizados' => $actualizados,
        'omitidos' => $omitidos,
        'errores' => $errores,
        'detalles_errores' => $detalles_errores
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>
