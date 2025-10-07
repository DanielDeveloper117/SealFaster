<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    // VALIDACIÓN: campos requeridos
    if (!isset($_POST['proveedor'], $_POST['material'], $_POST['multiplo'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Faltan parámetros requeridos.'
        ]);
        exit;
    }

    $proveedor = trim($_POST['proveedor']); // puede estar vacío
    $material = trim($_POST['material']);   // puede estar vacío
    $condicionDI = isset($_POST['condicion']) ? trim($_POST['condicion']) : "";
    $valorDI = isset($_POST['di']) ? trim($_POST['di']) : "";
    $multiplo = trim($_POST['multiplo']);

    // VALIDAR múltiplo: positivo con máximo dos decimales
    if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $multiplo) || (float)$multiplo <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El múltiplo debe ser un número positivo con máximo dos decimales.'
        ]);
        exit;
    }

    // Construir el parámetro personalizado "caso"
    $partesCaso = [];

    if (!empty($proveedor)) {
        $partesCaso[] = $proveedor;
    }

    if (!empty($material)) {
        $partesCaso[] = $material;
    }

    if (!empty($condicionDI) && !empty($valorDI)) {
        $partesCaso[] = "(di{$condicionDI}{$valorDI})";
    }

    // VALIDACIÓN: Deben existir al menos 2 componentes en el parámetro personalizado
    if (count($partesCaso) < 2) {
        echo json_encode([
            'success' => false,
            'message' => 'El parámetro personalizado debe tener al menos dos componentes: proveedor+material, proveedor+condicionalDI o material+condicionalDI.'
        ]);
        exit;
    }

    $casoPersonalizado = implode("+", $partesCaso);

    $descripcion = "MultiplicadorUtilidadPersonalizado";

    // VALIDAR SI YA EXISTE EL REGISTRO
    $stmtCheck = $conn->prepare("SELECT COUNT(*) as total FROM parametros2 WHERE caso = :caso AND descripcion = :descripcion");
    $stmtCheck->bindValue(':caso', $casoPersonalizado, PDO::PARAM_STR);
    $stmtCheck->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($resultCheck['total'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => "El parámetro personalizado '{$casoPersonalizado}' ya existe en la base de datos."
        ]);
        exit;
    }

    // INSERTAR en la tabla
    $stmtInsert = $conn->prepare("INSERT INTO parametros2 (caso, descripcion, valor) 
                                  VALUES (:caso, :descripcion, :valor)");

    $stmtInsert->bindValue(':caso', $casoPersonalizado, PDO::PARAM_STR);
    $stmtInsert->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
    $stmtInsert->bindValue(':valor', $multiplo, PDO::PARAM_STR);

    $stmtInsert->execute();

    echo json_encode([
        'success' => true,
        'message' => "Múltiplo agregado correctamente. Caso guardado: {$casoPersonalizado}"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
