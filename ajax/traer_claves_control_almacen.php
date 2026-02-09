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
    if (!$id_requisicion ) {
        throw new Exception("No se encontró requisición con id: $id_requisicion.");
    }
    try {
        // 1. Obtener la fecha de retorno de la tabla requisiciones
        $sqlReq = "SELECT observaciones_inv, fecha_retorno_barras FROM requisiciones WHERE id_requisicion = :id LIMIT 1";
        $stmtReq = $conn->prepare($sqlReq);
        $stmtReq->execute([':id' => $id_requisicion]);
        $resReq = $stmtReq->fetch(PDO::FETCH_ASSOC);
        $fechaRetorno = $resReq['fecha_retorno_barras'] ?? null;
        $observaciones_inv = $resReq['observaciones_inv'] ?? null;

        // 2. Tu consulta de claves existente
        $sqlClaves = "SELECT * FROM control_almacen 
                    WHERE id_requisicion = :id_requisicion 
                    AND clave IS NOT NULL 
                    AND clave <> ''";
        $stmtClaves = $conn->prepare($sqlClaves);
        $stmtClaves->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        $stmtClaves->execute();
        $allClaves = $stmtClaves->fetchAll(PDO::FETCH_ASSOC);

        if (!$allClaves) {
            throw new Exception("No se encontró requisición con id: $id_requisicion.");
        }

        // Enviamos la fecha de retorno en la raíz de la respuesta JSON
        echo json_encode([
            'success' => true,
            'data' => $allClaves,
            'fecha_retorno_barras' => $fechaRetorno,
            'observaciones_inv' => $observaciones_inv
        ]);

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
