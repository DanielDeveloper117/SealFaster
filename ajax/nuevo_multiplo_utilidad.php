<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    // Validar que los datos existan
    if (!isset($_POST['proveedor'], $_POST['material'], $_POST['multiplo'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Faltan parametros requeridos.'
        ]);
        exit;
    }

    $proveedor = trim($_POST['proveedor']);
    $material = trim($_POST['material']); 
    $multiplo = trim($_POST['multiplo']);

    // Validar que multiplo sea float positivo con maximo 2 decimales
    if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $multiplo) || (float)$multiplo <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El multiplo debe ser un numero positivo con maximo dos decimales.'
        ]);
        exit;
    }

    // Armar el campo "caso" = proveedor+material
    $caso = $proveedor . "+" . $material;
    $descripcion = "MultiplicadorUtilidadPersonalizado";

    // Insertar en la tabla
    $stmt = $conn->prepare("INSERT INTO parametros2 (caso, descripcion, valor) 
                            VALUES (:caso, :descripcion, :valor)");

    $stmt->bindValue(':caso', $caso, PDO::PARAM_STR);
    $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
    $stmt->bindValue(':valor', $multiplo, PDO::PARAM_STR);

    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Multiplo agregado correctamente.'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
