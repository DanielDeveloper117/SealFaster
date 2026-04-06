<?php
// ============================================================
// post_clave.php
// Endpoint AJAX para operaciones CRUD sobre la tabla parametros
// (claves SRS) y para verificar si una clave ya existe.
//
// Acciones GET : verificar | verificar_clave_alterna
// Acciones POST: insert | update | delete
// ============================================================
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'includes/functions/actualizar_inventario.php');

function clone_val($v) { return $v; } // helper local

header('Content-Type: application/json');

// Verificar sesión activa
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
    exit;
}

include(ROOT_PATH . 'includes/backend_info_user.php');

// Solo Administrador y Sistemas pueden operar claves SRS
$tienePermiso = ($tipo_usuario === 'Administrador' || $tipo_usuario === 'Sistemas');

if (!$tienePermiso) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos para realizar esta acción.']);
    exit;
}

try {

    // --------------------------------------------------------
    // GET: verificar si una clave ya existe
    // Parámetros: clave, excluir_id (opcional, para update)
    // --------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = trim($_GET['action'] ?? '');

        // Verificar clave
        if ($action === 'verificar') {
            $clave      = preg_replace('/\s+/', '', trim($_GET['clave'] ?? ''));
            $excluir_id = (int)($_GET['excluir_id'] ?? 0);

            if ($clave === '') {
                echo json_encode(['existe' => false]);
                exit;
            }

            if ($excluir_id > 0) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM parametros WHERE clave = :clave AND id != :id");
                $stmt->bindParam(':clave', $clave);
                $stmt->bindParam(':id',    $excluir_id, PDO::PARAM_INT);
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM parametros WHERE clave = :clave");
                $stmt->bindParam(':clave', $clave);
            }

            $stmt->execute();
            $total = (int)$stmt->fetchColumn();

            echo json_encode(['existe' => $total > 0]);
            exit;
        }

        // Verificar clave_alterna
        if ($action === 'verificar_clave_alterna') {
            $clave_alterna = preg_replace('/\s+/', '', trim($_GET['clave_alterna'] ?? ''));
            $excluir_id    = (int)($_GET['excluir_id'] ?? 0);

            if ($clave_alterna === '') {
                echo json_encode(['existe' => false]);
                exit;
            }

            if ($excluir_id > 0) {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM parametros WHERE clave_alterna = :clave_alterna AND id != :id");
                $stmt->bindParam(':clave_alterna', $clave_alterna);
                $stmt->bindParam(':id',    $excluir_id, PDO::PARAM_INT);
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) FROM parametros WHERE clave_alterna = :clave_alterna");
                $stmt->bindParam(':clave_alterna', $clave_alterna);
            }

            $stmt->execute();
            $total = (int)$stmt->fetchColumn();

            echo json_encode(['existe' => $total > 0]);
            exit;
        }

        // Acción no reconocida
        echo json_encode(['success' => false, 'message' => 'Acción GET no reconocida.']);
        exit;
    }

    // --------------------------------------------------------
    // POST: insert | update | delete
    // --------------------------------------------------------
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
        exit;
    }

    $action = trim($_POST['action'] ?? '');

    switch ($action) {

        // ----------------------------------------------------
        // INSERT
        // ----------------------------------------------------
        case 'insert':
            $clave          = preg_replace('/\s+/', '', trim($_POST['clave']          ?? ''));
            $clave_alterna  = preg_replace('/\s+/', '', trim($_POST['clave_alterna']  ?? ''));
            $material       = trim($_POST['material']   ?? '');
            $proveedor      = trim($_POST['proveedor']  ?? '');
            $tipo           = trim($_POST['tipo']       ?? '');
            $interior       = preg_replace('/\s+/', '', trim($_POST['interior']   ?? ''));
            $exterior       = preg_replace('/\s+/', '', trim($_POST['exterior']   ?? ''));
            $max_usable     = preg_replace('/\s+/', '', trim($_POST['max_usable'] ?? ''));
            $precio         = preg_replace('/\s+/', '', trim($_POST['precio']     ?? ''));

            // Validar campos requeridos
            if ($clave === '' || $material === '' || $proveedor === '' || $tipo === '' ||
                $interior === '' || $exterior === '' || $max_usable === '' || $precio === '') {
                echo json_encode(['success' => false, 'message' => 'Campos requeridos faltantes.']);
                exit;
            }

            // Validar que clave no sea duplicada
            $stmtChk = $conn->prepare("SELECT COUNT(*) FROM parametros WHERE clave = :clave");
            $stmtChk->bindParam(':clave', $clave);
            $stmtChk->execute();
            if ((int)$stmtChk->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'La clave ya existe en el catálogo.']);
                exit;
            }

            // Validar que clave_alterna no sea duplicada (si se proporciona)
            if ($clave_alterna !== '') {
                $stmtChk = $conn->prepare("SELECT COUNT(*) FROM parametros WHERE clave_alterna = :clave_alterna");
                $stmtChk->bindParam(':clave_alterna', $clave_alterna);
                $stmtChk->execute();
                if ((int)$stmtChk->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'La clave alterna ya existe en el catálogo.']);
                    exit;
                }
            }

            // Convertir a tipos correctos
            $interior       = (int)$interior;
            $exterior       = (int)$exterior;
            $max_usable     = (float)$max_usable;
            $precio         = (float)$precio;

            // INSERT
            $stmt = $conn->prepare(
                "INSERT INTO parametros 
                (clave, clave_alterna, material, proveedor, tipo, interior, exterior, max_usable, precio, usuario_id, created_at, updated_at)
                VALUES (:clave, :clave_alterna, :material, :proveedor, :tipo, :interior, :exterior, :max_usable, :precio, :usuario_id, NOW(), NOW())"
            );
            $stmt->bindParam(':clave',          $clave);
            $stmt->bindParam(':clave_alterna',  $clave_alterna);
            $stmt->bindParam(':material',       $material);
            $stmt->bindParam(':proveedor',      $proveedor);
            $stmt->bindParam(':tipo',           $tipo);
            $stmt->bindParam(':interior',       $interior,       PDO::PARAM_INT);
            $stmt->bindParam(':exterior',       $exterior,       PDO::PARAM_INT);
            $stmt->bindParam(':max_usable',     $max_usable);
            $stmt->bindParam(':precio',         $precio);
            $stmt->bindParam(':usuario_id',     $_SESSION['id'], PDO::PARAM_INT);
            $stmt->execute();

            $nuevoId = (int)$conn->lastInsertId();

            // Contar cuántos registros coinciden en inventario_cnc
            $estatus_protegidos = ['En uso', 'Maquinado en curso', 'Venta', 'Traspaso', 'Eliminado'];
            $placeholders = implode(',', array_fill(0, count($estatus_protegidos), '?'));
            $sqlCount = "SELECT COUNT(*) FROM inventario_cnc WHERE estatus NOT IN ($placeholders) AND (Clave = ? OR ( ? != '' AND Clave = ? ))";
            $stmtC = $conn->prepare($sqlCount);
            $paramsC = $estatus_protegidos;
            $paramsC[] = $clave;
            $paramsC[] = $clave_alterna;
            $paramsC[] = $clave_alterna;
            $stmtC->execute($paramsC);
            $matching_inventory = (int)$stmtC->fetchColumn();

            echo json_encode([
                'success'       => true,
                'message'       => "Clave insertada correctamente en parámetros.",
                'id'            => $nuevoId,
                'requires_sync' => ($matching_inventory > 0),
                'sync_count'    => $matching_inventory
            ]);
            break;

        // ----------------------------------------------------
        // UPDATE
        // ----------------------------------------------------
        case 'update':
            $id             = (int)($_POST['id'] ?? 0);
            $clave          = preg_replace('/\s+/', '', trim($_POST['clave']          ?? ''));
            $clave_alterna  = preg_replace('/\s+/', '', trim($_POST['clave_alterna']  ?? ''));
            $material       = trim($_POST['material']   ?? '');
            $proveedor      = trim($_POST['proveedor']  ?? '');
            $tipo           = trim($_POST['tipo']       ?? '');
            $interior       = preg_replace('/\s+/', '', trim($_POST['interior']   ?? ''));
            $exterior       = preg_replace('/\s+/', '', trim($_POST['exterior']   ?? ''));
            $max_usable     = preg_replace('/\s+/', '', trim($_POST['max_usable'] ?? ''));
            $precio         = preg_replace('/\s+/', '', trim($_POST['precio']     ?? ''));

            // Validar ID
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de registro inválido.']);
                exit;
            }

            // Validar campos requeridos
            if ($clave === '' || $material === '' || $proveedor === '' || $tipo === '' ||
                $interior === '' || $exterior === '' || $max_usable === '' || $precio === '') {
                echo json_encode(['success' => false, 'message' => 'Campos requeridos faltantes.']);
                exit;
            }

            // Obtener datos actuales para validar cambios
            $stmtActual = $conn->prepare("SELECT clave, clave_alterna FROM parametros WHERE id = :id");
            $stmtActual->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtActual->execute();
            $actual = $stmtActual->fetch();

            if (!$actual) {
                echo json_encode(['success' => false, 'message' => 'El registro no existe.']);
                exit;
            }

            // Validar que la clave no pertenezca a otro registro
            if ($clave !== $actual['clave']) {
                $stmtChk = $conn->prepare("SELECT COUNT(*) FROM parametros WHERE clave = :clave AND id != :id");
                $stmtChk->bindParam(':clave', $clave);
                $stmtChk->bindParam(':id',    $id, PDO::PARAM_INT);
                $stmtChk->execute();
                if ((int)$stmtChk->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'La clave ya existe en el catálogo.']);
                    exit;
                }
            }

            // Validar que la clave_alterna no pertenezca a otro registro (si se proporciona)
            if ($clave_alterna !== '' && $clave_alterna !== $actual['clave_alterna']) {
                $stmtChk = $conn->prepare("SELECT COUNT(*) FROM parametros WHERE clave_alterna = :clave_alterna AND id != :id");
                $stmtChk->bindParam(':clave_alterna', $clave_alterna);
                $stmtChk->bindParam(':id',    $id, PDO::PARAM_INT);
                $stmtChk->execute();
                if ((int)$stmtChk->fetchColumn() > 0) {
                    echo json_encode(['success' => false, 'message' => 'La clave alterna ya existe en el catálogo.']);
                    exit;
                }
            }

            // Convertir a tipos correctos
            $interior       = (int)$interior;
            $exterior       = (int)$exterior;
            $max_usable     = (float)$max_usable;
            $precio         = (float)$precio;

            // UPDATE
            $stmt = $conn->prepare(
                "UPDATE parametros SET 
                clave = :clave,
                clave_alterna = :clave_alterna,
                material = :material,
                proveedor = :proveedor,
                tipo = :tipo,
                interior = :interior,
                exterior = :exterior,
                max_usable = :max_usable,
                precio = :precio,
                usuario_id = :usuario_id,
                updated_at = NOW()
                WHERE id = :id"
            );
            $stmt->bindParam(':id',             $id,             PDO::PARAM_INT);
            $stmt->bindParam(':clave',          $clave);
            $stmt->bindParam(':clave_alterna',  $clave_alterna);
            $stmt->bindParam(':material',       $material);
            $stmt->bindParam(':proveedor',      $proveedor);
            $stmt->bindParam(':tipo',           $tipo);
            $stmt->bindParam(':interior',       $interior,       PDO::PARAM_INT);
            $stmt->bindParam(':exterior',       $exterior,       PDO::PARAM_INT);
            $stmt->bindParam(':max_usable',     $max_usable);
            $stmt->bindParam(':precio',         $precio);
            $stmt->bindParam(':usuario_id',     $_SESSION['id'], PDO::PARAM_INT);
            $stmt->execute();

            // Contar cuántos registros coinciden en inventario_cnc
            $estatus_protegidos = ['En uso', 'Maquinado en curso', 'Venta', 'Traspaso', 'Eliminado'];
            $placeholders = implode(',', array_fill(0, count($estatus_protegidos), '?'));
            $sqlCount = "SELECT COUNT(*) FROM inventario_cnc WHERE estatus NOT IN ($placeholders) AND (Clave = ? OR ( ? != '' AND Clave = ? ))";
            $stmtC = $conn->prepare($sqlCount);
            $paramsC = $estatus_protegidos;
            $paramsC[] = $clave;
            $paramsC[] = $clave_alterna;
            $paramsC[] = $clave_alterna;
            $stmtC->execute($paramsC);
            $matching_inventory = (int)$stmtC->fetchColumn();

            echo json_encode([
                'success'       => true,
                'message'       => "Clave actualizada correctamente en parámetros.",
                'requires_sync' => ($matching_inventory > 0),
                'sync_count'    => $matching_inventory
            ]);
            break;

        // ----------------------------------------------------
        // PRE-CHECK: Dependencias (check_delete)
        // ----------------------------------------------------
        case 'check_delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de registro inválido.']);
                exit;
            }
            $stmtCheck = $conn->prepare("SELECT clave, clave_alterna FROM parametros WHERE id = :id");
            $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtCheck->execute();
            $param = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$param) {
                echo json_encode(['success' => false, 'message' => 'El registro a verificar no existe.']);
                exit;
            }
            $hasAlterna = ($param['clave_alterna'] ?? '') !== '';
            $sqlOrphans = "SELECT COUNT(*) FROM inventario_cnc WHERE Clave = :c" . ($hasAlterna ? " OR Clave = :ca" : "");
            $stmtO = $conn->prepare($sqlOrphans);
            $stmtO->bindValue(':c', $param['clave']);
            if ($hasAlterna) {
                $stmtO->bindValue(':ca', $param['clave_alterna']);
            }
            $stmtO->execute();
            $orphan_count = (int)$stmtO->fetchColumn();

            echo json_encode(['success' => true, 'orphan_count' => $orphan_count]);
            break;

        // ----------------------------------------------------
        // DELETE
        // ----------------------------------------------------
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de registro inválido.']);
                exit;
            }

            // Verificar que el registro existe
            $stmtCheck = $conn->prepare("SELECT id, clave, clave_alterna FROM parametros WHERE id = :id");
            $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtCheck->execute();
            $param = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$param) {
                echo json_encode(['success' => false, 'message' => "El registro no existe. (ID: $id)"]);
                exit;
            }
            $clave         = $param['clave'];
            $clave_alterna = $param['clave_alterna'];

            // Desvincular barras huérfanas antes de eliminar
            $hasAlterna = ($clave_alterna ?? '') !== '';
            $sqlOrphans = "SELECT COUNT(*) FROM inventario_cnc WHERE Clave = :c" . ($hasAlterna ? " OR Clave = :ca" : "");
            $stmtO = $conn->prepare($sqlOrphans);
            $stmtO->bindValue(':c', $clave);
            if ($hasAlterna) {
                $stmtO->bindValue(':ca', $clave_alterna);
            }
            $stmtO->execute();
            $orphan_count = (int)$stmtO->fetchColumn();

            if ($orphan_count > 0) {
                $sqlUpd = "UPDATE inventario_cnc SET estatus = 'Clave nueva pendiente', updated_at = NOW() WHERE Clave = :c" . ($hasAlterna ? " OR Clave = :ca" : "");
                $stmtU = $conn->prepare($sqlUpd);
                $stmtU->bindValue(':c', $clave);
                if ($hasAlterna) {
                    $stmtU->bindValue(':ca', $clave_alterna);
                }
                $stmtU->execute();
            }

            // DELETE
            $stmt = $conn->prepare("DELETE FROM parametros WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode([
                'success' => true,
                'message' => "Clave eliminada correctamente." . ($orphan_count > 0 ? " ($orphan_count barras desvinculadas)" : "")
            ]);
            break;

        // ----------------------------------------------------
        // SYNC INVENTARIO (Llamada opcional posterior a update/insert)
        // ----------------------------------------------------
        case 'sync_inventario':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de registro inválido.']);
                exit;
            }
            
            // Comprobar existencia
            $stmtP = $conn->prepare("SELECT id FROM parametros WHERE id = :id");
            $stmtP->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtP->execute();
            if (!$stmtP->fetchColumn()) {
                echo json_encode(['success' => false, 'message' => 'El parámetro original no fue encontrado.']);
                exit;
            }
            
            $material_case = "
                CASE
                    WHEN UPPER(p.material) LIKE '%H-ECOPUR%' OR UPPER(p.material) LIKE '%PU ROJO%' THEN 'H-ECOPUR'
                    WHEN UPPER(p.material) LIKE '%ECOPUR%'   OR UPPER(p.material) LIKE '%PU VERDE%' THEN 'ECOPUR'
                    WHEN UPPER(p.material) LIKE '%NITRILO%'  OR UPPER(p.material) LIKE '%ECORUBBER 1%' THEN 'ECORUBBER 1'
                    WHEN UPPER(p.material) LIKE '%VITON%'    OR UPPER(p.material) LIKE '%ECORUBBER 2%' THEN 'ECORUBBER 2'
                    WHEN UPPER(p.material) LIKE '%EPDM%'     OR UPPER(p.material) LIKE '%ECORUBBER 3%' THEN 'ECORUBBER 3'
                    WHEN UPPER(p.material) LIKE '%SILICON%'  OR UPPER(p.material) LIKE '%ECOSIL%'      THEN 'ECOSIL'
                    WHEN UPPER(p.material) LIKE '%ECOTAL%' THEN 'ECOTAL'
                    WHEN UPPER(p.material) LIKE '%ECOMID%' OR UPPER(p.material) LIKE '%PA%' THEN 'ECOMID'
                    WHEN UPPER(p.material) LIKE '%VIRGEN%' OR UPPER(p.material) LIKE '%ECOFLON 1%' THEN 'ECOFLON 1'
                    WHEN UPPER(p.material) LIKE '%NIKEL%'  OR UPPER(p.material) LIKE '%MOLLY%'    OR UPPER(p.material) LIKE '%ECOFLON 2%' THEN 'ECOFLON 2'
                    WHEN UPPER(p.material) LIKE '%BRONCE%' OR UPPER(p.material) LIKE '%ECOFLON 3%' THEN 'ECOFLON 3'
                    
                    ELSE p.material
                END
            ";

            $estatus_protegidos   = ['En uso', 'Maquinado en curso', 'Venta', 'Traspaso', 'Eliminado'];
            $placeholders_estatus = implode(',', array_fill(0, count($estatus_protegidos), '?'));

            $sql_sync = "
                UPDATE inventario_cnc ic
                INNER JOIN parametros p ON p.id = ? AND (ic.Clave = p.clave OR (p.clave_alterna != '' AND ic.Clave = p.clave_alterna))
                SET
                    ic.material   = $material_case,
                    ic.proveedor  = p.proveedor,
                    ic.interior   = p.interior,
                    ic.exterior   = p.exterior,
                    ic.max_usable = p.max_usable,
                    ic.Medida     = CONCAT(p.interior, '/', p.exterior),
                    ic.estatus    = 'Disponible para cotizar',
                    ic.updated_at = NOW()
                WHERE ic.estatus NOT IN ($placeholders_estatus)
            ";

            $stmt_sync = $conn->prepare($sql_sync);
            $params_s = [$id];
            foreach ($estatus_protegidos as $e) { $params_s[] = $e; }
            $stmt_sync->execute($params_s);
            $actualizados = $stmt_sync->rowCount();
            
            echo json_encode([
                'success' => true, 
                'message' => "Inventario CNC actualizado exitosamente ($actualizados afectados).",
                'inventario' => ['actualizados' => $actualizados, 'mensaje' => "Registros afectados: $actualizados"]
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción POST no reconocida.']);
            exit;
    }

} catch (PDOException $e) {
    error_log('post_clave.php PDOException: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error de BD: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>