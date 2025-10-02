<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    // Verificar que todos los campos requeridos están presentes
    $required_fields = [
        'id_requisicion', 'cantidad_barras', 'clave',
        'mm_entrega', 'mm_usados', 'total_sellos',
        'merma_corte', 'scrap_pz', 'scrap_mm'
    ];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            echo json_encode(['success' => false, 'message' => "Falta el campo: $field"]);
            exit;
        }
    }

    // Obtener y sanitizar los valores
    $id_requisicion   = trim($_POST['id_requisicion']);
    $cantidad_barras  = trim($_POST['cantidad_barras']);
    $clave            = trim($_POST['clave']);
    $mm_entrega       = trim($_POST['mm_entrega']);
    $mm_usados        = trim($_POST['mm_usados']);
    $total_sellos     = trim($_POST['total_sellos']);
    $merma_corte      = trim($_POST['merma_corte']);
    $scrap_pz         = trim($_POST['scrap_pz']);
    $scrap_mm         = trim($_POST['scrap_mm']);

    // Validar tipo de datos
    if (!ctype_digit($cantidad_barras) || intval($cantidad_barras) <= 0) {
        echo json_encode(['success' => false, 'message' => 'Cantidad de barras debe ser un entero positivo.']);
        exit;
    }

    // Función para validar decimales con hasta 2 cifras
    function esDecimalValido($valor) {
        return preg_match('/^\d+(\.\d{1,2})?$/', $valor);
    }

    $camposDecimales = [
        'mm_entrega' => $mm_entrega,
        'mm_usados' => $mm_usados,
        'total_sellos' => $total_sellos,
        'merma_corte' => $merma_corte,
        'scrap_pz' => $scrap_pz,
        'scrap_mm' => $scrap_mm
    ];

    foreach ($camposDecimales as $nombre => $valor) {
        if (!esDecimalValido($valor)) {
            echo json_encode(['success' => false, 'message' => "El campo '$nombre' debe ser un número decimal con hasta 2 decimales."]);
            exit;
        }
    }

    // Verificar que la clave no tenga caracteres especiales
    if (!preg_match('/^[A-Za-z0-9\.\-_]+$/', $clave)) {
        echo json_encode(['success' => false, 'message' => 'La clave contiene caracteres inválidos.']);
        exit;
    }

    // Preparar la consulta SQL
    $stmt = $conn->prepare("
        INSERT INTO control_almacen (
            id_requisicion, cantidad_barras, clave,
            mm_entrega, mm_usados, total_sellos,
            merma_corte, scrap_pz, scrap_mm
        ) VALUES (
            :id_requisicion, :cantidad_barras, :clave,
            :mm_entrega, :mm_usados, :total_sellos,
            :merma_corte, :scrap_pz, :scrap_mm
        )
    ");

    // Vincular parámetros
    $stmt->bindParam(':id_requisicion', $id_requisicion);
    $stmt->bindParam(':cantidad_barras', $cantidad_barras, PDO::PARAM_INT);
    $stmt->bindParam(':clave', $clave);
    $stmt->bindParam(':mm_entrega', $mm_entrega);
    $stmt->bindParam(':mm_usados', $mm_usados);
    $stmt->bindParam(':total_sellos', $total_sellos);
    $stmt->bindParam(':merma_corte', $merma_corte);
    $stmt->bindParam(':scrap_pz', $scrap_pz);
    $stmt->bindParam(':scrap_mm', $scrap_mm);

    // Ejecutar
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Registro insertado correctamente.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en base de datos: ' . $e->getMessage()]);
} finally {
    $conn = null; // Cerrar conexión
}
?>
