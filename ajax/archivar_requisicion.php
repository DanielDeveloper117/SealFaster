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
    if (empty($_POST['id_requisicion']) || !preg_match('/^\d+$/', $_POST['id_requisicion'])) {
        throw new Exception("Id de requisición inválido.");
    }
    if (empty($_POST['justificacion']) || strlen($_POST['justificacion']) < 10) {
        throw new Exception("Ingrese una justificación de mínimo 10 caracteres");
    }
    $id_requisicion = (int) $_POST['id_requisicion'];
    $razon = $_POST['razon'] ?? null;
    $justificacion = $_POST['justificacion'] ?? null;

    // --------- ACTUALIZAR REQUISICIÓN ----------
    $sqlUpdate = "
        UPDATE requisiciones
        SET
            justificacion_archivada = :justificacion,
            estatus = 'Archivada',
            fecha_archivada = NOW()
        WHERE id_requisicion = :id
    ";

    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bindValue(':justificacion', $justificacion);
    $stmtUpdate->bindValue(':id', $id_requisicion, PDO::PARAM_INT);
    $stmtUpdate->execute();

    echo json_encode([
        'success' => true,
        'message' => "Folio archivado correctamente para el folio: " . $id_requisicion
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
