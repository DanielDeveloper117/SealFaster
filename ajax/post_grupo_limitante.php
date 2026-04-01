<?php
// ============================================================
// post_grupo_limitante.php
// Maneja todas las operaciones de escritura del módulo:
//   delete_grupo, insert_limitante, update_limitante,
//   delete_limitante, asignar_perfil, desvincular_perfil
// ============================================================
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success'=>false,'message'=>'Sesion no valida.']); exit;
}
include(ROOT_PATH . 'includes/backend_info_user.php');
// Verificar permiso: solo CNC Gerente, Administrador o Sistemas
$tienePermiso = ($tipo_usuario === 'Administrador')
             || ($tipo_usuario === 'Sistemas')
             || ($tipo_usuario === 'CNC' && $rol_usuario === 'Gerente');
if (!($tienePermiso)) {
    echo json_encode(['success'=>false,'message'=>'Sin permisos para esta accion.']); exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        // ---- GRUPOS ----------------------------------------

        case 'insert':
            $nombre = trim($_POST['nombre'] ?? '');
            $desc   = trim($_POST['descripcion'] ?? '');
            if (!$nombre) { echo json_encode(['success'=>false,'message'=>'El nombre es obligatorio.']); exit; }
            $chk = $conn->prepare("SELECT id FROM grupos_herramienta WHERE nombre=:n LIMIT 1");
            $chk->execute([':n'=>$nombre]);
            if ($chk->fetch()) { echo json_encode(['success'=>false,'message'=>'Ya existe un grupo con ese nombre.']); exit; }
            $conn->prepare("INSERT INTO grupos_herramienta (nombre,descripcion,created_at,updated_at) VALUES(:n,:d,NOW(),NOW())")
                 ->execute([':n'=>$nombre,':d'=>$desc]);
            echo json_encode(['success'=>true,'message'=>'Grupo "'.$nombre.'" creado correctamente.']);
            break;

        case 'update':
            $id     = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $desc   = trim($_POST['descripcion'] ?? '');
            if ($id <= 0 || !$nombre) { echo json_encode(['success'=>false,'message'=>'Datos incompletos.']); exit; }
            $chk = $conn->prepare("SELECT id FROM grupos_herramienta WHERE nombre=:n AND id!=:id LIMIT 1");
            $chk->execute([':n'=>$nombre,':id'=>$id]);
            if ($chk->fetch()) { echo json_encode(['success'=>false,'message'=>'Ya existe otro grupo con ese nombre.']); exit; }
            $conn->prepare("UPDATE grupos_herramienta SET nombre=:n, descripcion=:d, updated_at=NOW() WHERE id=:id")
                 ->execute([':n'=>$nombre,':d'=>$desc,':id'=>$id]);
            echo json_encode(['success'=>true,'message'=>'Grupo actualizado correctamente.']);
            break;

        case 'delete_grupo':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'ID no válido.']); exit; }
            // Segunda capa: verificar perfiles asociados
            $cntP = $conn->prepare("SELECT COUNT(*) FROM perfiles2 WHERE grupo_herramienta_id=:id");
            $cntP->execute([':id'=>$id]);
            if ((int)$cntP->fetchColumn() > 0) {
                echo json_encode(['success'=>false,'message'=>'No se puede eliminar: el grupo tiene perfiles asignados. Desvinculelos primero.']); exit;
            }
            // Eliminar limitantes del grupo y luego el grupo
            $conn->prepare("DELETE FROM limitantes_herramienta WHERE grupo_herramienta_id=:id")->execute([':id'=>$id]);
            $conn->prepare("DELETE FROM grupos_herramienta WHERE id=:id")->execute([':id'=>$id]);
            echo json_encode(['success'=>true,'message'=>'Grupo eliminado correctamente.']);
            break;

        // ---- LIMITANTES ------------------------------------

        case 'insert_limitante':
            $grupoId = (int)($_POST['grupo_id']      ?? 0);
            $herraId = (int)($_POST['herramienta_id']?? 0);
            $dureza  = $_POST['dureza'] ?? '';
            if ($grupoId <= 0 || $herraId <= 0 || !in_array($dureza, ['blandos','duros'])) {
                echo json_encode(['success'=>false,'message'=>'Datos incompletos para agregar limitante.']); exit;
            }
            // Verificar unicidad: grupo + herramienta + dureza
            $chk = $conn->prepare("SELECT id FROM limitantes_herramienta WHERE grupo_herramienta_id=:g AND herramienta_id=:h AND dureza=:d LIMIT 1");
            $chk->execute([':g'=>$grupoId,':h'=>$herraId,':d'=>$dureza]);
            if ($chk->fetch()) {
                echo json_encode(['success'=>false,'message'=>'Esta herramienta ya tiene limitante registrada para "'.$dureza.'" en este grupo.']); exit;
            }
            $conn->prepare("INSERT INTO limitantes_herramienta
                (grupo_herramienta_id,herramienta_id,dureza,di_min,di_max,de_min,de_max,seccion_min,seccion_max,h_min,h_max,created_at,updated_at)
                VALUES(:g,:h,:d,:di_min,:di_max,:de_min,:de_max,:sm,:sx,:hm,:hx,NOW(),NOW())")
            ->execute([
                ':g'=>$grupoId,':h'=>$herraId,':d'=>$dureza,
                ':di_min'=>(float)$_POST['di_min'], ':di_max'=>(float)$_POST['di_max'],
                ':de_min'=>(float)$_POST['de_min'], ':de_max'=>(float)$_POST['de_max'],
                ':sm'=>(float)$_POST['seccion_min'], ':sx'=>(float)$_POST['seccion_max'],
                ':hm'=>(float)$_POST['h_min'],      ':hx'=>(float)$_POST['h_max'],
            ]);
            echo json_encode(['success'=>true,'message'=>'Limitante agregada correctamente.']);
            break;

        case 'update_limitante':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'ID no válido.']); exit; }
            $conn->prepare("UPDATE limitantes_herramienta SET
                di_min=:di_min, di_max=:di_max, de_min=:de_min, de_max=:de_max,
                seccion_min=:sm, seccion_max=:sx, h_min=:hm, h_max=:hx, updated_at=NOW()
                WHERE id=:id")
            ->execute([
                ':di_min'=>(float)$_POST['di_min'], ':di_max'=>(float)$_POST['di_max'],
                ':de_min'=>(float)$_POST['de_min'], ':de_max'=>(float)$_POST['de_max'],
                ':sm'=>(float)$_POST['seccion_min'], ':sx'=>(float)$_POST['seccion_max'],
                ':hm'=>(float)$_POST['h_min'],      ':hx'=>(float)$_POST['h_max'],
                ':id'=>$id
            ]);
            echo json_encode(['success'=>true,'message'=>'Limitante actualizada correctamente.']);
            break;

        case 'delete_limitante':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'ID no válido.']); exit; }
            $conn->prepare("DELETE FROM limitantes_herramienta WHERE id=:id")->execute([':id'=>$id]);
            echo json_encode(['success'=>true,'message'=>'Limitante eliminada correctamente.']);
            break;

        // ---- PERFILES --------------------------------------

        case 'asignar_perfil':
            $perfilId = (int)($_POST['perfil_id'] ?? 0);
            $grupoId  = (int)($_POST['grupo_id']  ?? 0);
            if ($perfilId <= 0 || $grupoId <= 0) {
                echo json_encode(['success'=>false,'message'=>'Datos incompletos.']); exit;
            }
            // Verificar que el grupo tiene al menos una limitante antes de asignar
            $cntLim = $conn->prepare("SELECT COUNT(*) FROM limitantes_herramienta WHERE grupo_herramienta_id=:g");
            $cntLim->execute([':g'=>$grupoId]);
            if ((int)$cntLim->fetchColumn() === 0) {
                echo json_encode(['success'=>false,'message'=>'No se puede asignar el perfil a un grupo sin limitantes.']); exit;
            }
            $conn->prepare("UPDATE perfiles2 SET grupo_herramienta_id=:g, updated_at=NOW() WHERE id=:p")
                 ->execute([':g'=>$grupoId,':p'=>$perfilId]);
            echo json_encode(['success'=>true,'message'=>'Perfil asignado al grupo correctamente.']);
            break;

        case 'desvincular_perfil':
            $perfilId = (int)($_POST['perfil_id'] ?? 0);
            if ($perfilId <= 0) { echo json_encode(['success'=>false,'message'=>'ID de perfil no válido.']); exit; }
            $conn->prepare("UPDATE perfiles2 SET grupo_herramienta_id=NULL, updated_at=NOW() WHERE id=:p")
                 ->execute([':p'=>$perfilId]);
            echo json_encode(['success'=>true,'message'=>'Perfil desvinculado del grupo.']);
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