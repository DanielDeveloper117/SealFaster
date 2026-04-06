<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception("Método no permitido");
    }

    set_error_handler(function($severity, $message, $file, $line) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    $proveedor = trim($_GET['proveedor'] ?? '');
    $interior = trim($_GET['interior'] ?? '');
    $exterior = trim($_GET['exterior'] ?? '');

    $errores = [];

    if (!isset($proveedor) || trim($proveedor) === '') {
        $errores[] = "Falta el proveedor";
    }
    if (!isset($interior) || trim($interior) === '') {
        $errores[] = "Falta la medida interior";
    }
    if (!isset($exterior) || trim($exterior) === '') {
        $errores[] = "Falta la medida exterior";
    }   
    // Si hay errores, regresar solo el primero
    if (!empty($errores)) {
        echo json_encode([
            'status' => 'error',
            'message' => $errores[0]
        ]);
        exit;
    }

    // Preparar la consulta
    $stmt = $conn->prepare("SELECT * FROM parametros WHERE proveedor = :proveedor AND interior = :interior AND exterior = :exterior");
    $stmt->bindParam(':proveedor', $proveedor);
    $stmt->bindParam(':interior', $interior);
    $stmt->bindParam(':exterior', $exterior);
    $stmt->execute();
    
    // Obtener resultados
    $arregloClavesValidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los resultados en formato JSON
    echo json_encode($arregloClavesValidas);

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    restore_error_handler();
    $conn = null;
}
