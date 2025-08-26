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

        // Validar si al menos un registro tiene clave
        $tieneClave = false;
        foreach ($allClaves as $claveRow) {
            if (!empty($claveRow['clave'])) {
                $tieneClave = true;
                break;
            }
        }

        if (!$tieneClave) {
            throw new Exception("La requisición $id_requisicion no tiene claves asociadas.");
        }

        echo json_encode([
            'success' => true,
            'data' => $allClaves
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
