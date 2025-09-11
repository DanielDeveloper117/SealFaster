<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    $di = $_GET['di'];
    $materialValue = "mu" . $_GET['materialValue'];
    $proveedorBillet = $_GET['proveedor'];

    // Consulta 1: multiplicador por material
    $stmt = $conn->prepare("SELECT valor 
                            FROM parametros2 
                            WHERE caso = :caso 
                            AND limite_inferior <= :di 
                            AND :di <= limite_superior");
    $stmt->bindParam(':caso', $materialValue);
    $stmt->bindParam(':di', $di);
    $stmt->execute();
    $arregloSelectMultiploUtilidadMaterial = $stmt->fetch(PDO::FETCH_ASSOC);

    // Consulta 2: multiplicador por proveedor
    $stmt = $conn->prepare("SELECT valor 
                            FROM parametros2 
                            WHERE caso = :caso 
                            AND descripcion = 'MultiplicadorUtilidadProveedor'");
    $stmt->bindParam(':caso', $proveedorBillet);
    $stmt->execute();
    $arregloSelectMultiploUtilidadProveedor = $stmt->fetch(PDO::FETCH_ASSOC);

    // Extraer valores como float
    $valorMaterial = isset($arregloSelectMultiploUtilidadMaterial['valor']) 
        ? (float)$arregloSelectMultiploUtilidadMaterial['valor'] 
        : null;

    $valorProveedor = isset($arregloSelectMultiploUtilidadProveedor['valor']) 
        ? (float)$arregloSelectMultiploUtilidadProveedor['valor'] 
        : null;

    // Comparar y decidir el menor
    if ($valorMaterial !== null && $valorProveedor !== null) {
        $menor = min($valorMaterial, $valorProveedor);
    } elseif ($valorMaterial !== null) {
        $menor = $valorMaterial;
    } elseif ($valorProveedor !== null) {
        $menor = $valorProveedor;
    } else {
        echo json_encode(['error' => 'No se encontraron valores validos']);
        exit;
    }

    // Retornar en formato JSON
    $arregloSelectMultiploUtilidad = ['valor' => $menor];
    echo json_encode($arregloSelectMultiploUtilidad);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexion
}
?>
