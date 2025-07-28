<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    if (!isset($_POST['id_cotizacion']) || empty(trim($_POST['id_cotizacion']))) {
        throw new Exception("Parámetro 'id_cotizacion' faltante o vacío.");
    }

    $id_cotizacion = trim($_POST['id_cotizacion']);

    if (!preg_match('/^\d+$/', $id_cotizacion)) {
        throw new Exception("Parámetro 'id_cotizacion' no es un número válido.");
    }

    // Lista de campos a actualizar
    $campos = [
        'di_sello', 'di_sello_inch', 'de_sello', 'de_sello_inch', 'a_sello', 'a_sello_inch',
        'di_sello2', 'di_sello_inch2', 'de_sello2', 'de_sello_inch2', 'a_sello2', 'a_sello_inch2'
    ];

    $datos = [];

    foreach ($campos as $campo) {
        $valor = isset($_POST[$campo]) ? trim($_POST[$campo]) : null;
        $datos[$campo] = $valor !== '' ? $valor : null;
    }

    $sql = "UPDATE cotizacion_materiales SET 
        di_sello = :di_sello,
        di_sello_inch = :di_sello_inch,
        de_sello = :de_sello,
        de_sello_inch = :de_sello_inch,
        a_sello = :a_sello,
        a_sello_inch = :a_sello_inch,
        di_sello2 = :di_sello2,
        di_sello_inch2 = :di_sello_inch2,
        de_sello2 = :de_sello2,
        de_sello_inch2 = :de_sello_inch2,
        a_sello2 = :a_sello2,
        a_sello_inch2 = :a_sello_inch2
        WHERE id_cotizacion = :id_cotizacion";

    $stmt = $conn->prepare($sql);

    foreach ($datos as $campo => $valor) {
        $stmt->bindValue(":$campo", $valor);
    }

    $stmt->bindValue(':id_cotizacion', $id_cotizacion, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => "Medidas actualizadas correctamente."
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
