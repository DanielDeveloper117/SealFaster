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

    if (!preg_match('/^\d+$/', $id_requisicion)) {
        echo json_encode([]);
        exit;
    }

    // Consulta principal: control_almacen
    $stmt = $conn->prepare("
        SELECT * 
        FROM control_almacen
        WHERE id_requisicion = :id_requisicion
        ORDER BY id_control ASC
    ");
    $stmt->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmt->execute();
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $fusionados = [];

    foreach ($resultados as $r) {
        // Buscar el registro correspondiente en inventario_cnc
        $stmtBillet = $conn->prepare("
            SELECT material, Clave, Medida, estatus 
            FROM inventario_cnc 
            WHERE lote_pedimento = :lote_pedimento
            LIMIT 1
        ");
        $stmtBillet->bindParam(':lote_pedimento', $r["lote_pedimento"]);
        $stmtBillet->execute();
        $billetData = $stmtBillet->fetch(PDO::FETCH_ASSOC);

        // Combinar ambos resultados (control_almacen + inventario_cnc)
        $fusionados[] = array_merge(
            $r,
            $billetData ? $billetData : [
                'material' => null,
                'Clave' => null,
                'Medida' => null,
                'estatus' => null
            ]
        );
    }

    echo json_encode($fusionados);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar la base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>
