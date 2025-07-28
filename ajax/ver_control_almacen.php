<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['id_requisicion']) || empty(trim($_GET['id_requisicion']))) {
        echo json_encode([]);
        exit;
    }

    $id_requisicion = trim($_GET['id_requisicion']);

    // Validar que sea un número o una cadena segura según tu base de datos
    if (!preg_match('/^\d+$/', $id_requisicion)) {
        echo json_encode([]);
        exit;
    }

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare("
        SELECT 
            id_control,
            cantidad_barras,
            clave,
            mm_entrada,
            mm_salida,
            total_sellos,
            merma_corte,
            scrap_pz,
            scrap_mm
        FROM control_almacen
        WHERE id_requisicion = :id_requisicion
        ORDER BY id_control ASC
    ");
    $stmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);

} catch (PDOException $e) {
    // En caso de error, devolver un JSON vacío o un mensaje de error
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar la base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>
