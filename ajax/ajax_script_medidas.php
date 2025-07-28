<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    // Preparar la consulta para obtener las medidas
    $stmt = $conn->prepare("SELECT id, Medida FROM inventario_cnc");  // Asumo que existe una columna 'id' como clave primaria
    $stmt->execute();
    
    // Obtener los resultados
    $billets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recorremos cada fila y actualizamos 'interior' y 'exterior' basándonos en la columna 'Medida'
    foreach ($billets as $row) {
        // Dividir el valor de 'Medida' en dos partes usando la barra '/'
        list($interior, $exterior) = explode('/', $row['Medida']);
        
        // Preparar la consulta de actualización
        $updateStmt = $conn->prepare("UPDATE inventario_cnc SET interior = :interior, exterior = :exterior WHERE id = :id");
        
        // Ejecutar la actualización con los valores obtenidos
        $updateStmt->execute([
            ':interior' => (int)$interior,  // Convertir a entero
            ':exterior' => (int)$exterior,  // Convertir a entero
            ':id' => $row['id']
        ]);
    }

    // Devolver una respuesta en formato JSON
    echo json_encode(['success' => true, 'message' => 'Se han actualizado las medidas correctamente']);

} catch (PDOException $e) {
    // En caso de error, devolver un mensaje de error
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>
