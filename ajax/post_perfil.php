<?php
// ============================================================
// post_perfil.php
// Acciones:
//   update_perfil     — edita detalles, flags y grupo
//   update_porcentaje — actualiza un registro de porcentajes_perfil
//   update_tolerancia — actualiza un registro de tolerancias_perfil
// ============================================================
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success'=>false,'message'=>'Sesion no valida.']); exit;
}
include(ROOT_PATH . 'includes/backend_info_user.php');
$tienePermiso = ($tipo_usuario === 'Administrador')
             || ($tipo_usuario === 'Sistemas')
             || ($tipo_usuario === 'CNC' && $rol_usuario === 'Gerente');
if (!$tienePermiso) {
    echo json_encode(['success'=>false,'message'=>'Sin permisos para esta accion.']); exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        // ---- UPDATE PERFIL ---------------------------------
        case 'update_perfil':
            $id       = (int)($_POST['id'] ?? 0);
            $detalles = trim($_POST['detalles'] ?? '');
            $grupoId  = !empty($_POST['grupo_herramienta_id']) ? (int)$_POST['grupo_herramienta_id'] : null;

            if ($id <= 0) {
                echo json_encode(['success'=>false,'message'=>'ID de perfil no valido.']); exit;
            }
            if (strlen($detalles) > 600) {
                echo json_encode(['success'=>false,'message'=>'Los detalles no pueden superar 600 caracteres.']); exit;
            }

            // Si se envia nuevo grupo, verificar que ese grupo tenga limitantes
            if ($grupoId !== null) {
                $cntLim = $conn->prepare("SELECT COUNT(*) FROM limitantes_herramienta WHERE grupo_herramienta_id=:g");
                $cntLim->execute([':g'=>$grupoId]);
                if ((int)$cntLim->fetchColumn() === 0) {
                    echo json_encode(['success'=>false,'message'=>'No se puede asignar un grupo sin limitantes de herramienta.']); exit;
                }
            }

            $params = [':d'=>$detalles, ':g'=>$grupoId, ':id'=>$id];
            $sql = "UPDATE perfiles2 SET detalles=:d, grupo_herramienta_id=:g, updated_at=NOW()";

            // Flags: solo Administrador/Sistemas pueden editarlos
            if ($tipo_usuario === 'Administrador' || $tipo_usuario === 'Sistemas') {
                $flagCampos = [
                    'con_resorte_en'       => (int)($_POST['con_resorte_en']       ?? 0),
                    'es_wiper_en'          => (int)($_POST['es_wiper_en']          ?? 0),
                    'con_escalon_en'       => (int)($_POST['con_escalon_en']       ?? 0),
                    'es_wiper_especial_en' => (int)($_POST['es_wiper_especial_en'] ?? 0),
                ];
                foreach ($flagCampos as $col => $val) {
                    $sql .= ", {$col}=:{$col}";
                    $params[":{$col}"] = $val;
                }
            }

            $sql .= " WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            echo json_encode(['success'=>true,'message'=>'Perfil actualizado correctamente.']);
            break;

        // ---- UPDATE PORCENTAJE -----------------------------
        case 'update_porcentaje':
            $id = (int)($_POST['id'] ?? 0);
            $di = isset($_POST['di']) ? (float)$_POST['di'] : null;
            $de = isset($_POST['de']) ? (float)$_POST['de'] : null;
            $h  = isset($_POST['h'])  ? (float)$_POST['h']  : null;

            if ($id <= 0 || $di === null || $de === null || $h === null) {
                echo json_encode(['success'=>false,'message'=>'Datos incompletos.']); exit;
            }
            // Validaciones de rango
            if ($di > 1.000 || $de > 1.000 || $h > 1.000) {
                echo json_encode(['success'=>false,'message'=>'Ningun porcentaje puede superar 1.000.']); exit;
            }
            if ($di < 0 || $de < 0) {
                echo json_encode(['success'=>false,'message'=>'Los porcentajes no pueden ser negativos.']); exit;
            }
            if ($h <= 0) {
                echo json_encode(['success'=>false,'message'=>'El porcentaje H no puede ser 0.']); exit;
            }
            // Redondear a 3 decimales para coherencia con DECIMAL(6,3)
            $diR = round($di, 3);
            $deR = round($de, 3);
            $hR  = round($h,  3);
            // Los tres tipos (DI, DE, H) se guardan en registros separados
            // Buscar los ids de cada tipo para este porcentaje
            $stmtGetIds = $conn->prepare("
                SELECT tipo, id FROM porcentajes_perfil
                WHERE perfil_id = (SELECT perfil_id FROM porcentajes_perfil WHERE id=:id LIMIT 1)
                  AND componente = (SELECT componente FROM porcentajes_perfil WHERE id=:id2 LIMIT 1)
            ");
            $stmtGetIds->execute([':id'=>$id, ':id2'=>$id]);
            $registros = $stmtGetIds->fetchAll(PDO::FETCH_ASSOC);
            foreach ($registros as $reg) {
                $val = ($reg['tipo'] === 'DI') ? $diR : (($reg['tipo'] === 'DE') ? $deR : $hR);
                $conn->prepare("UPDATE porcentajes_perfil SET porcentaje=:p, updated_at=NOW() WHERE id=:rid")
                     ->execute([':p'=>$val, ':rid'=>$reg['id']]);
            }
            echo json_encode(['success'=>true,'message'=>'Porcentajes actualizados correctamente.']);
            break;

        // ---- UPDATE TOLERANCIA -----------------------------
        case 'update_tolerancia':
            $id = (int)($_POST['id'] ?? 0);
            $di = isset($_POST['di']) ? (float)$_POST['di'] : null;
            $de = isset($_POST['de']) ? (float)$_POST['de'] : null;

            if ($id <= 0 || $di === null || $de === null) {
                echo json_encode(['success'=>false,'message'=>'Datos incompletos.']); exit;
            }
            if ($di < 0 || $de < 0) {
                echo json_encode(['success'=>false,'message'=>'Las tolerancias deben ser mayores o iguales a 0.']); exit;
            }
            $diR = round($di, 2);
            $deR = round($de, 2);
            // DI y DE se guardan en registros separados
            $stmtGetIds = $conn->prepare("
                SELECT tipo, id FROM tolerancias_perfil
                WHERE perfil_id = (SELECT perfil_id FROM tolerancias_perfil WHERE id=:id LIMIT 1)
                  AND componente = (SELECT componente FROM tolerancias_perfil WHERE id=:id2 LIMIT 1)
            ");
            $stmtGetIds->execute([':id'=>$id, ':id2'=>$id]);
            $registros = $stmtGetIds->fetchAll(PDO::FETCH_ASSOC);
            foreach ($registros as $reg) {
                $val = ($reg['tipo'] === 'DI') ? $diR : $deR;
                $conn->prepare("UPDATE tolerancias_perfil SET tolerancia=:t, updated_at=NOW() WHERE id=:rid")
                     ->execute([':t'=>$val, ':rid'=>$reg['id']]);
            }
            echo json_encode(['success'=>true,'message'=>'Tolerancias actualizadas correctamente.']);
            break;

        default:
            echo json_encode(['success'=>false,'message'=>'Accion no reconocida.']);
            break;
    }

} catch (PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Error de base de datos: '.$e->getMessage()]);
} finally {
    $conn = null;
}
?>