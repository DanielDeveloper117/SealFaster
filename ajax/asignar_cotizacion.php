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

    // --------- VALIDACIONES BÁSICAS ----------
    if (empty($_POST['id_cotizacion']) || !preg_match('/^\d+$/', $_POST['id_cotizacion'])) {
        throw new Exception("Id de cotización no valido.");
    }

    if (empty($_POST['id_vendedor'])) {
        throw new Exception("Debe especificar un vendedor");
    }

    $id_cotizacion = trim($_POST['id_cotizacion']);
    $id_vendedor = trim($_POST['id_vendedor']);

    // --------- ACTUALIZAR REQUISICIÓN ----------
    $sqlUpdate = "
        UPDATE cotizacion_materiales
        SET
            id_usuario = :id_vendedor,
            fecha_actualizacion = NOW()
        WHERE id_cotizacion = :id_cotizacion
    ";

    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bindValue(':id_vendedor', $id_vendedor);
    $stmtUpdate->bindValue(':id_cotizacion', $id_cotizacion);
    $stmtUpdate->execute();

    echo json_encode([
        'success' => true,
        'message' => "Ahora el vendedor verá la cotización en sus cotizaciones"
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
