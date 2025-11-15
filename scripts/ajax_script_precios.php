<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    $totalActualizados = 0;
    $lote = 500; // Tamaño del lote
    do {
        // Ejecutar actualización limitada
        $stmt = $conn->prepare("
            UPDATE inventario_cnc 
            INNER JOIN parametros ON inventario_cnc.Clave = parametros.clave
            SET inventario_cnc.precio = parametros.precio
            LIMIT $lote
        ");
        $stmt->execute();

        // Obtener el número de filas afectadas
        $registrosTotales = $stmt->rowCount();
        $totalActualizados += $registrosTotales;

        // Si no quedan filas, salir del bucle
    } while ($registrosTotales > 0);

    echo json_encode([
        'success' => true,
        'message' => "Se actualizaron $totalActualizados registros correctamente."
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>
