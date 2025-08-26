<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    if (!isset($_POST['operador_cnc']) || empty(trim($_POST['operador_cnc']))) {
        throw new Exception("Falta el nombre del operador CNC");
    }

    if (!isset($_POST['id_requisicion']) || empty(trim($_POST['id_requisicion']))) {
        throw new Exception("Id de requisicion faltante o vacío");
    }


    $id_requisicion = trim($_POST['id_requisicion']);
    $operador_cnc = $_POST['operador_cnc'];

    if (!preg_match('/^\d+$/', $id_requisicion)) {
        throw new Exception("Parámetro 'id_requisicion' no es un número válido.");
    }

    $sql = "UPDATE requisiciones SET operador_cnc = :operador_cnc, 
            estatus = 'En producción',
            inicio_maquinado = NOW() WHERE id_requisicion = :id_requisicion";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':operador_cnc', $operador_cnc);
    $stmt->bindValue(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => "Operador CNC agregado correctamente. El estatus ha cambiado a Maquinado de sellos."
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
