<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    $di = $_GET['di'];
    $material = "mu" . $_GET['material'];
    $proveedor = $_GET['proveedor'];
    $personalizadoPr4 =  $_GET['proveedor']."+".$_GET['material'];
    $personalizadoPr3 = $_GET['material']."+".$_GET['di'];
    $personalizadoPr2 = $_GET['proveedor']."+".$_GET['di'];
    $personalizadoPr1 = $_GET['proveedor']."+".$_GET['material']."+".$_GET['di'];

    // Consulta 1: multiplicador por material/precio de lista
    $stmt = $conn->prepare("SELECT valor 
                            FROM parametros2 
                            WHERE caso = :caso 
                            AND limite_inferior <= :di 
                            AND :di <= limite_superior");
    $stmt->bindParam(':caso', $material);
    $stmt->bindParam(':di', $di);
    $stmt->execute();
    $arregloSelectMultiploUtilidadMaterial = $stmt->fetch(PDO::FETCH_ASSOC);

    // Consulta 2: multiplicador por proveedor
    $stmt = $conn->prepare("SELECT valor 
                            FROM parametros2 
                            WHERE caso = :caso 
                            AND descripcion = 'MultiplicadorUtilidadProveedor'");
    $stmt->bindParam(':caso', $proveedor);
    $stmt->execute();
    $arregloSelectMultiploUtilidadProveedor = $stmt->fetch(PDO::FETCH_ASSOC);

    // Consulta 3: multiplicadores personalizados
    $stmt = $conn->prepare("SELECT valor 
                            FROM parametros2 
                            WHERE descripcion = 'MultiplicadorUtilidadPersonalizado' 
                            AND caso = :caso");
    $stmt->bindParam(':caso', $personalizado);
    $stmt->execute();
    $arregloSelectMultiploUtilidadPersonalizado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Extraer valores como float
    $valorMaterial = isset($arregloSelectMultiploUtilidadMaterial['valor']) 
        ? (float)$arregloSelectMultiploUtilidadMaterial['valor'] 
        : null;

    $valorProveedor = isset($arregloSelectMultiploUtilidadProveedor['valor']) 
        ? (float)$arregloSelectMultiploUtilidadProveedor['valor'] 
        : null;

    $valorPersonalizado = isset($arregloSelectMultiploUtilidadPersonalizado['valor']) 
        ? (float)$arregloSelectMultiploUtilidadPersonalizado['valor'] 
        : null;

    // *DESICION DE PRIORIDAD*
    // CASO PRIORIDAD 5: mu MATERIAL y mu PROVEEDOR. = PRIORIDAD EL MENOR DE LOS DOS
    // PRIORIDAD EN PARAMETROS PERSONALIZADOS (mayor prioridad)
    // CASO PRIORIDAD 4: PROVEEDOR + MATERIAL
    // CASO PRIORIDAD 3: MATERIAL + DI 
    // CASO PRIORIDAD 2: PROVEEDOR + DI 
    // CASO PRIORIDAD 1: PROVEEDOR + MATERIAL + DI 

    // primero determinar si existe un multiplo de utilidad personalizado coincidente
    if ($valorPersonalizado !== null) {
        $multiploUtilidad = $valorPersonalizado;

    }else{
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
        $multiploUtilidad = $menor;
    }

    // Retornar en formato JSON
    $arregloSelectMultiploUtilidad = ['valor' => $multiploUtilidad];
    echo json_encode($arregloSelectMultiploUtilidad);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexion
}
?>
