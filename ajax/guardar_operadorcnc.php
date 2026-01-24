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

    if (empty($_POST['maquina'])) {
        throw new Exception("Debe especificar una máquina CNC.");
    }

    $id_requisicion = (int) $_POST['id_requisicion'];
    $maquinaSolicitada = trim($_POST['maquina']);
    $operador_cnc = $_POST['operador_cnc'] ?? null;

    // --------- VALIDACIÓN DE PERMISOS ----------
    if ($tipo_usuario === "CNC" && $rol_usuario !== "Gerente") {
        // Operador CNC solo puede asignarse a sí mismo
        if ($maquinaSolicitada !== $rolUser) {
            throw new Exception("No tiene permiso para asignar otra máquina distinta a la suya.");
        }
    }

    // --------- CONSULTAR ESTADO ACTUAL DE LA REQUISICIÓN ----------
    $sqlCheck = "
        SELECT 
            maquina,
            operador_cnc,
            estatus,
            inicio_maquinado,
            fecha_entrega_barras
        FROM requisiciones
        WHERE id_requisicion = :id
        FOR UPDATE
    ";

    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bindValue(':id', $id_requisicion, PDO::PARAM_INT);
    $stmtCheck->execute();
    $req = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$req) {
        throw new Exception("La requisición no existe.");
    }

    // --------- VALIDAR SI YA TIENE MÁQUINA ----------
    if (!empty($req['maquina'])) {
        $fechaInicio = $req['inicio_maquinado']
            ? date('d/m/Y H:i', strtotime($req['inicio_maquinado']))
            : 'No registrada';

        throw new Exception(
            "Esta requisición ya fue asignada.\n" .
            "Máquina: {$req['maquina']}\n" .
            "Operador: {$req['operador_cnc']}\n" .
            "Inicio de maquinado: {$fechaInicio}"
        );
    }

    // --------- DETERMINAR NUEVO ESTATUS ----------
    $nuevoEstatus = $req['estatus'];

    if ($req['estatus'] === "Producción" && !empty($req['fecha_entrega_barras'])) {
        $nuevoEstatus = "En producción";
    }else{
        $nuevoEstatus = "Producción";
    }

    // --------- ACTUALIZAR REQUISICIÓN ----------
    $sqlUpdate = "
        UPDATE requisiciones
        SET
            maquina = :maquina,
            operador_cnc = :operador,
            estatus = :estatus,
            inicio_maquinado = NOW()
        WHERE id_requisicion = :id
    ";

    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bindValue(':maquina', $maquinaSolicitada);
    $stmtUpdate->bindValue(':operador', $operador_cnc);
    $stmtUpdate->bindValue(':estatus', $nuevoEstatus);
    $stmtUpdate->bindValue(':id', $id_requisicion, PDO::PARAM_INT);
    $stmtUpdate->execute();

    echo json_encode([
        'success' => true,
        'message' => $nuevoEstatus === "En producción"
            ? "Máquina asignada correctamente. El folio ya se encuentra En producción con barras entregadas."
            : "Máquina asignada correctamente. Entrega de barras pendiente."
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
