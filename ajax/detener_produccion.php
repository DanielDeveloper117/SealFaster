<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
include(ROOT_PATH . 'includes/backend_info_user.php');

header('Content-Type: application/json');

try {

    // --------- VALIDACIONES BÁSICAS ----------
    if (empty($_POST['id_requisicion']) || !preg_match('/^\d+$/', $_POST['id_requisicion'])) {
        throw new Exception("Id de requisición inválido.");
    }

    if (empty($_POST['razon'])) {
        throw new Exception("Seleccione una razón del selector");
    }
    if (empty($_POST['justificacion']) || strlen($_POST['justificacion']) < 10) {
        throw new Exception("Ingrese una justificación de mínimo 10 caracteres");
    }
    $id_requisicion = (int) $_POST['id_requisicion'];
    $razon = $_POST['razon'] ?? null;
    $justificacion = $_POST['justificacion'] ?? null;

    // --------- VALIDACIÓN DE PERMISOS ----------
    if ($tipo_usuario === "CNC" && $rol_usuario !== "Gerente") {
        throw new Exception("No tiene permiso para detener la producción de un folio.");
    }

    // --------- ACTUALIZAR REQUISICIÓN ----------
    $sqlUpdate = "
        UPDATE requisiciones
        SET
            razon_detencion = :razon,
            justificacion_detencion = :justificacion,
            estatus = 'Detenida',
            fecha_detencion = NOW()
        WHERE id_requisicion = :id
    ";

    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bindValue(':razon', $razon);
    $stmtUpdate->bindValue(':justificacion', $justificacion);
    $stmtUpdate->bindValue(':id', $id_requisicion, PDO::PARAM_INT);
    $stmtUpdate->execute();

    echo json_encode([
        'success' => true,
        'message' => "Producción detenida correctamente para el folio: " . $id_requisicion
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
