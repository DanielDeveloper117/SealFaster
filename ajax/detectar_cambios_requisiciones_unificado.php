<?php
/**
 * Middleware Unificado de Detección de Cambios en Requisiciones
 * 
 * Este archivo funciona para AMBOS módulos: VN y CNC
 * 
 * Por qué es unificado:
 * - El frontend envía los IDs específicos que está viendo
 * - El backend SOLO consulta esos IDs exactos
 * - No hay lógica de roles/filtros en el backend
 * - La lógica de filtrado ya está en produccion_vn.php y produccion_cnc.php
 * - El usuario solo verá en la tabla lo que tiene permiso de ver
 * 
 * Input: JSON con array de IDs y estatuses que el frontend está viendo
 * Output: JSON con cambios detectados o null si no hay cambios
 */

require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();

if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json');

try {
    // Obtener el estado actual desde POST (IDs y estatuses del frontend)
    $estadoActual = isset($_POST['estadoActual']) ? json_decode($_POST['estadoActual'], true) : [];
    
    if (empty($estadoActual)) {
        echo json_encode([
            'tipo_cambio' => null,
            'mensaje' => '',
            'estado_actual' => []
        ]);
        exit;
    }

    // Extraer IDs del estado actual
    $idsRequisiciones = [];
    $estatusFrontend = [];
    foreach ($estadoActual as $req) {
        if (isset($req['id_requisicion'])) {
            $idsRequisiciones[] = $req['id_requisicion'];
            $estatusFrontend[$req['id_requisicion']] = $req['estatus'];
        }
    }

    if (empty($idsRequisiciones)) {
        echo json_encode([
            'tipo_cambio' => null,
            'mensaje' => '',
            'estado_actual' => []
        ]);
        exit;
    }

    // Consultar SOLO los IDs que el frontend envía (no filtros por rol)
    // La lógica de filtrado ya está en el frontend (produccion_vn.php o produccion_cnc.php)
    // El usuario solo ve lo que tiene permiso de ver
    $placeholders = implode(',', array_fill(0, count($idsRequisiciones), '?'));
    $sql = "
        SELECT 
            id_requisicion,
            estatus
        FROM requisiciones
        WHERE id_requisicion IN ($placeholders)
        ORDER BY id_requisicion DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute($idsRequisiciones);
    $requisicionesActuales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Construir estado actual indexado por ID
    $estatusBackend = [];
    foreach ($requisicionesActuales as $req) {
        $estatusBackend[$req['id_requisicion']] = $req['estatus'];
    }

    // Comparar: frontend vs backend
    $cambios = [
        'tipo_cambio' => null,
        'mensaje' => '',
        'estado_actual' => $requisicionesActuales
    ];

    // Verificar si hubo cambio de estatus
    foreach ($estatusFrontend as $id_req => $estatus_frontend) {
        if (isset($estatusBackend[$id_req])) {
            $estatus_bd = $estatusBackend[$id_req];
            if ($estatus_bd !== $estatus_frontend) {
                // Se detectó cambio de estatus
                $cambios['tipo_cambio'] = 'cambio_estatus';
                $cambios['mensaje'] = 'Se detectó cambio de estatus en una requisición. ¿Desea actualizar la tabla?';
                break;
            }
        }
    }

    echo json_encode($cambios);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error al consultar la base de datos',
        'message' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>
