<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    $limit = 10000;
    $actualizados = 0;
    $errores = 0;
    $detalles_errores = [];

    // Obtener hasta 10000 registros (sin filtrar)
    $query = "SELECT id_estimacion, billets, billets_string 
              FROM cotizacion_materiales
              ORDER BY id_estimacion
              LIMIT $limit";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo json_encode(['success' => false, 'message' => 'No se encontraron registros.']);
        exit;
    }

    $update = $conn->prepare("UPDATE cotizacion_materiales 
                              SET billets_claves_lotes = :billets_claves_lotes 
                              WHERE id_estimacion = :id_estimacion");

    foreach ($rows as $row) {
        $id_estimacion = $row['id_estimacion'];
        $billets = trim($row['billets'] ?? '');
        $billets_string = trim($row['billets_string'] ?? '');

        // Si no hay datos en billets o billets_string, aun asi actualizar con cadena vacia
        if ($billets === '' || $billets_string === '') {
            $nuevo_valor = '';
        } else {
            // Convertir a arreglos
            $arrBillets = array_map('trim', explode(',', $billets));
            $arrStrings = array_map('trim', explode(',', $billets_string));

            $lenBillets = count($arrBillets);
            $lenStrings = count($arrStrings);

            // Tomar el menor tamaño para evitar desbordes
            $min = min($lenBillets, $lenStrings);
            $combined = [];

            for ($i = 0; $i < $min; $i++) {
                $combined[] = $arrBillets[$i] . ' ' . $arrStrings[$i];
            }

            $nuevo_valor = implode(',', $combined);
        }

        try {
            $update->execute([
                ':billets_claves_lotes' => $nuevo_valor,
                ':id_estimacion' => $id_estimacion
            ]);
            $actualizados++;
        } catch (Exception $e) {
            $errores++;
            $detalles_errores[] = "ID $id_estimacion: " . $e->getMessage();
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Proceso completado (actualizados todos los registros).',

        'actualizados' => $actualizados,
        'errores' => $errores,
        'detalles_errores' => $detalles_errores
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    $conn = null;
}
?>
