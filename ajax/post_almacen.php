<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido");
    }

    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';

    set_error_handler(function($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    $acciones_validas = ['insert', 'update'];
    if (!in_array($action, $acciones_validas)) {
        throw new Exception("Acción no válida.");
    }

    // Limpiar espacios automáticamente sin notificar al usuario
    $id          = $_POST['id'] ?? '';
    $almacen     = $_POST['almacen'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    $errores = [];
    if ($action === '' || $action === null) $errores[] = "Falta la acción";
    if (($id === '' || $id === null) && $action === 'update') $errores[] = "Falta el id";
    if ($almacen === '' || $almacen === null) $errores[] = "Falta el nombre de almacen";
    if ($descripcion === '' || $descripcion === null) $errores[] = "Falta la descripción";

    if (!empty($errores)) {
        echo json_encode([
            'success' => false,
            'message' => $errores[0]
        ]);
        exit;
    }

    if ($action === 'insert') {
        $sql = "INSERT INTO almacenes 
                (almacen, descripcion)
                VALUES 
                (:almacen, :descripcion)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':almacen', $almacen);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->execute();
        echo json_encode([
            'success' => true,
            'message' => "Registro agregado correctamente", 
        ]);
        exit;
    }elseif ($action === 'update') {

        if (empty($id)) throw new Exception("ID requerido para actualizar.");
        $sql = "UPDATE almacenes SET 
                    almacen = :almacen, 
                    descripcion = :descripcion, 
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':almacen', $almacen);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->execute();
        echo json_encode([
            'success' => true,
            'message' => "Registro actualizado correctamente", 
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