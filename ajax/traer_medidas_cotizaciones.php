<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['id_requisicion']) || empty(trim($_GET['id_requisicion']))) {
        throw new Exception("Parámetro 'id_requisicion' faltante o vacío.");
    }

    $id_requisicion = trim($_GET['id_requisicion']);

    if (!preg_match('/^\d+$/', $id_requisicion)) {
        throw new Exception("Parámetro 'id_requisicion' no es un número válido.");
    }

    try {
        $sqlCotizaciones = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
        $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
        $stmtCotizaciones->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        $stmtCotizaciones->execute();
        $result = $stmtCotizaciones->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new Exception("No se encontró requisición con id: $id_requisicion.");
        }

        if (empty($result['cotizaciones'])) {
            throw new Exception("La requisición $id_requisicion no tiene cotizaciones asociadas.");
        }

        $cotizacion_ids = explode(', ', $result['cotizaciones']);
        $allCotizaciones = [];

        $sql = "SELECT * FROM cotizacion_materiales WHERE id_cotizacion = :id_cotizacion";
        $stmt = $conn->prepare($sql);

        foreach ($cotizacion_ids as $id_cotizacion) {
            $stmt->bindValue(':id_cotizacion', $id_cotizacion, PDO::PARAM_INT);
            $stmt->execute();
            $cotizacion = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cotizacion) {
                $allCotizaciones[] = $cotizacion;
            } else {
                // Opcional: lanzar excepción si una cotización no existe
                // throw new Exception("No se encontró información para la cotización ID $id_cotizacion.");
            }
        }

        echo json_encode($allCotizaciones);
    } catch (PDOException $e) {
        throw new Exception("Error al consultar la base de datos: " . $e->getMessage());
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>
