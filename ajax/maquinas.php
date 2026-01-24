<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}
include(ROOT_PATH . 'includes/backend_info_user.php');
header('Content-Type: application/json');

try {
    if ($rol_usuario !== "Gerente") {

        $sqlusuariosMaquinaCNC = "
            SELECT rol
            FROM login
            WHERE lider = 2
            AND rol = :rol
        ";

        $stmt = $conn->prepare($sqlusuariosMaquinaCNC);
        $stmt->bindParam(':rol', $rol_usuario, PDO::PARAM_STR);

    } else {

        $sqlusuariosMaquinaCNC = "
            SELECT rol
            FROM login
            WHERE lider = 2
            AND rol LIKE 'Máquina%'
        ";

        $stmt = $conn->prepare($sqlusuariosMaquinaCNC);
    }

    $stmt->execute();
    $usuariosMaquinaCNC = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($usuariosMaquinaCNC);


} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar la base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>
