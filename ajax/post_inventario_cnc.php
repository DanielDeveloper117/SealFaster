<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }

    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? null;

    set_error_handler(function($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    $acciones_validas = ['insert', 'update', 'delete', 'insert2'];
    if (!in_array($action, $acciones_validas)) {
        throw new Exception("Acción no válida.");
    }

    $clave          = trim($_POST['clave'] ?? '');
    $medida         = trim($_POST['medida'] ?? '');
    $proveedor      = trim($_POST['proveedor'] ?? '');
    $material       = trim($_POST['material'] ?? ''); 
    $max_usable     = trim($_POST['max_usable'] ?? '');
    $stock          = trim($_POST['stock'] ?? '');
    $lote_pedimento = trim($_POST['lote_pedimento'] ?? '');
    $estatus        = trim($_POST['estatus'] ?? '');

    if ($action !== 'delete') {
        foreach ([
            'clave' => $clave,
            'medida' => $medida,
            'proveedor' => $proveedor,
            'max_usable' => $max_usable,
            'stock' => $stock,
            'lote_pedimento' => $lote_pedimento
        ] as $campo => $valor) {
            if (preg_match('/\s/', $valor)) {
                echo json_encode([
                    'success' => false,
                    'message' => "El campo '$campo' contiene espacios en blanco. Los registros no deben llevar espacios."
                ]);
                exit;
            }
        }

        $errores = [];
        if ($action === '') $errores[] = "Falta el action";
        if ($id === null) $errores[] = "Falta el id";
        if ($estatus === '') $errores[] = "Falta el estatus";
        if ($clave === '') $errores[] = "Falta la clave";
        if ($material === '') $errores[] = "Falta el material";
        if ($proveedor === '') $errores[] = "Falta el proveedor";
        if ($medida === '') $errores[] = "Falta la medida";
        if ($max_usable === '') $errores[] = "Falta el maximo usable";
        if ($stock === '') $errores[] = "Falta el stock";
        if ($lote_pedimento === '') $errores[] = "Falta el lote/pedimento";

        if (!empty($errores)) {
            echo json_encode([
                'success' => false,
                'message' => $errores[0]
            ]);
            exit;
        }
    }

    if (in_array($action, ['insert', 'insert2', 'update'])) {

        if (!preg_match('/^\d+\/\d+$/', $medida)) {
            throw new Exception("Formato de medida inválido. Usa formato interior/exterior.");
        }

        list($interior, $exterior) = explode('/', $medida);
        $interior = (int)$interior;
        $exterior = (int)$exterior;
    }

    if ($action === 'insert' || $action === 'insert2') {
        $sql = "INSERT INTO inventario_cnc 
                (clave, medida, interior, exterior, proveedor, material, max_usable, pre_stock, stock, lote_pedimento, estatus, updated_at)
                VALUES 
                (:clave, :medida, :interior, :exterior, :proveedor, :material, :max_usable, :pre_stock, :stock, :lote_pedimento, :estatus, NOW())";
        $stmt = $conn->prepare($sql);
    }

    if ($action === 'update') {
        if (empty($id)) throw new Exception("ID requerido para actualizar.");
        $sql = "UPDATE inventario_cnc SET 
                    clave = :clave, 
                    medida = :medida, 
                    interior = :interior, 
                    exterior = :exterior, 
                    proveedor = :proveedor, 
                    material = :material, 
                    max_usable = :max_usable, 
                    pre_stock = :pre_stock,
                    stock = :stock, 
                    lote_pedimento = :lote_pedimento,
                    estatus = :estatus,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
    }

    if ($action === 'insert' || $action === 'insert2' || $action === 'update') {
        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':medida', $medida);
        $stmt->bindParam(':interior', $interior);
        $stmt->bindParam(':exterior', $exterior);
        $stmt->bindParam(':proveedor', $proveedor);
        $stmt->bindParam(':material', $material);
        $stmt->bindParam(':max_usable', $max_usable);
        $stmt->bindParam(':pre_stock', $stock);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':lote_pedimento', $lote_pedimento);
        $stmt->bindParam(':estatus', $estatus);
        $stmt->execute();

        if (($action === 'insert' || $action === 'insert2') && $estatus === 'Falta precio o máx. usable') {
            try {
                $verificar_sql = "SELECT COUNT(*) FROM parametros WHERE Clave = :clave";
                $stmt_verificar = $conn->prepare($verificar_sql);
                $stmt_verificar->bindParam(':clave', $clave);
                $stmt_verificar->execute();
                $existe = $stmt_verificar->fetchColumn();
                // cuando el resultado es 0 registros coincidentes agrega la nueva clave sin max usable y sin precio
                if ($existe == 0) {
                    $sql_parametros = "INSERT INTO parametros (Clave, material, proveedor, interior, exterior)
                                    VALUES (:clave, :material, :proveedor, :interior, :exterior)";
                    $stmt_parametros = $conn->prepare($sql_parametros);
                    $stmt_parametros->bindParam(':clave', $clave);
                    $stmt_parametros->bindParam(':material', $material);
                    $stmt_parametros->bindParam(':proveedor', $proveedor);
                    $stmt_parametros->bindParam(':interior', $interior);
                    $stmt_parametros->bindParam(':exterior', $exterior);
                    $stmt_parametros->execute();
                }
            } catch (Exception $e) {
                // No hacer nada si ocurre error
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => $action === 'update' ? 'Registro actualizado correctamente.' : 'Registro agregado correctamente.'
        ]);
        exit;
    }

    if ($action === 'delete') {
        if (empty($id)) throw new Exception("ID requerido para eliminar.");

        $stmt = $conn->prepare("UPDATE inventario_cnc SET estatus = 'Eliminado', deleted_at = NOW() WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => "Registro eliminado correctamente."
        ]);
        exit;
    }

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    restore_error_handler();
    $conn = null;
}
