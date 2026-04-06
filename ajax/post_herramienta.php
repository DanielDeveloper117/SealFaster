<?php
// ============================================================
// post_herramienta.php
// Maneja las operaciones CRUD de la tabla herramientas.
// Acciones: insert | update | delete
// ============================================================
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
include(ROOT_PATH . 'includes/backend_info_user.php');

header('Content-Type: application/json');
// Verificar permiso: solo CNC Gerente, Administrador o Sistemas
$tienePermiso = ($tipo_usuario === 'Administrador')
             || ($tipo_usuario === 'Sistemas')
             || ($tipo_usuario === 'CNC' && $rol_usuario === 'Gerente');

if (!$tienePermiso) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos para realizar esta accion.']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {

        // --------------------------------------------------------
        // INSERT: nueva herramienta
        // --------------------------------------------------------
        case 'insert':
            $numero      = trim($_POST['numero']      ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            if ($numero === '') {
                echo json_encode(['success' => false, 'message' => 'El numero de herramienta es obligatorio.']);
                exit;
            }

            // Verificar que el numero no este duplicado
            $stmtCheck = $conn->prepare("SELECT id FROM herramientas WHERE numero = :numero LIMIT 1");
            $stmtCheck->bindParam(':numero', $numero);
            $stmtCheck->execute();
            if ($stmtCheck->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Ya existe una herramienta con el numero "' . $numero . '".']);
                exit;
            }

            $stmt = $conn->prepare("
                INSERT INTO herramientas (numero, descripcion, created_at, updated_at)
                VALUES (:numero, :descripcion, NOW(), NOW())
            ");
            $stmt->bindParam(':numero',      $numero);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Herramienta "' . $numero . '" agregada correctamente.']);
            break;

        // --------------------------------------------------------
        // UPDATE: editar herramienta existente
        // --------------------------------------------------------
        case 'update':
            $id          = (int)($_POST['id']          ?? 0);
            $numero      = trim($_POST['numero']       ?? '');
            $descripcion = trim($_POST['descripcion']  ?? '');

            if ($id <= 0 || $numero === '') {
                echo json_encode(['success' => false, 'message' => 'Datos incompletos para editar.']);
                exit;
            }

            // Verificar que el numero no este en uso por OTRA herramienta
            $stmtCheck = $conn->prepare("
                SELECT id FROM herramientas
                WHERE numero = :numero AND id != :id
                LIMIT 1
            ");
            $stmtCheck->bindParam(':numero', $numero);
            $stmtCheck->bindParam(':id',     $id);
            $stmtCheck->execute();
            if ($stmtCheck->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El numero "' . $numero . '" ya pertenece a otra herramienta.']);
                exit;
            }

            $stmt = $conn->prepare("
                UPDATE herramientas
                SET numero = :numero, descripcion = :descripcion, updated_at = NOW()
                WHERE id = :id
            ");
            $stmt->bindParam(':numero',      $numero);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':id',          $id);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Herramienta actualizada correctamente.']);
            break;

        // --------------------------------------------------------
        // DELETE: eliminar herramienta
        // Segunda capa de seguridad: verificar relaciones en BD
        // aunque el boton ya este deshabilitado en el frontend.
        // --------------------------------------------------------
        case 'delete':
            $id = (int)($_POST['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Id de herramienta no valido.']);
                exit;
            }

            // Verificar si tiene relaciones en limitantes_herramienta
            $stmtRel = $conn->prepare("
                SELECT COUNT(*) AS total FROM limitantes_herramienta WHERE herramienta_id = :id
            ");
            $stmtRel->bindParam(':id', $id);
            $stmtRel->execute();
            $totalRel = (int)$stmtRel->fetchColumn();

            if ($totalRel > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No es posible eliminar esta herramienta porque esta asignada a '
                               . $totalRel . ' grupo(s) de limitantes. '
                               . 'Primero desasiganela de todos los grupos.'
                ]);
                exit;
            }

            $stmt = $conn->prepare("DELETE FROM herramientas WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Herramienta eliminada correctamente.']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Accion no reconocida.']);
            break;
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>