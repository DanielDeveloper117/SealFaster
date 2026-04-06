<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    // Campos requeridos
    $required_fields = [
        'id_requisicion', 'cantidad_barras', 'clave',
        'lote_pedimento', 'mm_entrega'
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
    $material         = trim($_POST['material']);
    $clave            = trim($_POST['clave']);
    $lote_pedimento   = trim($_POST['lote_pedimento']);
    $medida           = trim($_POST['medida']);
    $es_extra         = isset($_POST['es_extra']) ? trim($_POST['es_extra']) : 0;
    $mm_entrega       = trim($_POST['mm_entrega']);

    // Validar cantidad de barras
    if (!ctype_digit($cantidad_barras) || intval($cantidad_barras) <= 0) {
        echo json_encode(['success' => false, 'message' => 'Cantidad de barras debe ser un entero positivo.']);
        exit;
    }

    // Validar decimales con hasta 2 cifras
    function esDecimalValido($valor) {
        return preg_match('/^\d+(\.\d{1,2})?$/', $valor);
    }

    if (!esDecimalValido($mm_entrega)) {
        echo json_encode(['success' => false, 'message' => "El campo 'mm_entrega' debe ser un número decimal válido (hasta 2 decimales)."]);
        exit;
    }

    // Validar caracteres válidos para 'clave' y 'lote_pedimento'
    $regex = '/^[A-Za-z0-9\.\-_]+$/';

    if (!preg_match($regex, $clave)) {
        echo json_encode(['success' => false, 'message' => 'La clave contiene caracteres inválidos.']);
        exit;
    }

    if (!preg_match($regex, $lote_pedimento)) {
        echo json_encode(['success' => false, 'message' => 'El lote/pedimento contiene caracteres inválidos.']);
        exit;
    }

    // VALIDACIÓN DE DUPLICADO (id_requisicion + clave + lote_pedimento)
    $verificar = $conn->prepare("
        SELECT COUNT(*) AS total 
        FROM control_almacen 
        WHERE id_requisicion = :id_requisicion 
        AND clave = :clave
        AND lote_pedimento = :lote_pedimento
    ");
    $verificar->bindParam(':id_requisicion', $id_requisicion);
    $verificar->bindParam(':clave', $clave);
    $verificar->bindParam(':lote_pedimento', $lote_pedimento);
    $verificar->execute();

    $existe = $verificar->fetch(PDO::FETCH_ASSOC)['total'];

    if ($existe > 0) {
        echo json_encode([
            'success' => false,
            'message' => "Ya existe un registro con la clave '$clave' y lote/pedimento '$lote_pedimento' para esta requisición."
        ]);
        exit;
    }

    // INSERTAR NUEVO REGISTRO
    $stmt = $conn->prepare("
        INSERT INTO control_almacen (
            id_requisicion, cantidad_barras, material, clave, lote_pedimento, medida, es_extra, mm_entrega
        ) VALUES (
            :id_requisicion, :cantidad_barras, :material, :clave, :lote_pedimento, :medida, :es_extra, :mm_entrega
        )
    ");

    $stmt->bindParam(':id_requisicion', $id_requisicion);
    $stmt->bindParam(':cantidad_barras', $cantidad_barras, PDO::PARAM_INT);
    $stmt->bindParam(':material', $material);
    $stmt->bindParam(':clave', $clave);
    $stmt->bindParam(':lote_pedimento', $lote_pedimento);
    $stmt->bindParam(':medida', $medida);
    $stmt->bindParam(':es_extra', $es_extra, PDO::PARAM_INT);
    $stmt->bindParam(':mm_entrega', $mm_entrega);

    $stmt->execute();

    // Preparar y ejecutar update una vez
    $sqlEstatusLP = "UPDATE inventario_cnc 
                    SET estatus = 'Maquinado en curso'
                    WHERE lote_pedimento = :lote_pedimento";
    $stmtEstatusLP = $conn->prepare($sqlEstatusLP);
    $stmtEstatusLP->bindParam(':lote_pedimento', $lote_pedimento);
    $stmtEstatusLP->execute();

    echo json_encode(['success' => true, 'message' => 'Registro insertado correctamente. Barra deshabilitada para cotizar.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en base de datos: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>
