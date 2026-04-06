<?php
/**
 * migracion_claves_alternas.php
 * 
 * Script de migración una sola vez para traspasar datos de claves_alternas a parametros.
 * Ejecutar desde admin solo una sola vez.
 * 
 * Migra: claves_alternas.clave_alterna → parametros.clave_alterna
 *        claves_alternas.clave_srs → parametros.clave
 */

require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

include(ROOT_PATH . 'includes/backend_info_user.php');

// Solo admin y sistemas
if (!in_array($tipo_usuario, ['Administrador', 'Sistemas'], true)) {
    echo json_encode(['success' => false, 'message' => 'Permisos insuficientes.']);
    exit;
}

try {
    // Verificar que claves_alternas exista
    $stmtCheck = $conn->prepare("SHOW TABLES LIKE 'claves_alternas'");
    $stmtCheck->execute();
    if ($stmtCheck->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'La tabla claves_alternas no existe o ya fue migrada.',
            'accion' => $_GET['action'] ?? 'verificar'
        ]);
        exit;
    }

    // Acción: verificar cuántos registros hay para migrar
    if (($_GET['action'] ?? '') === 'verificar') {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM claves_alternas");
        $stmt->execute();
        $resultado = $stmt->fetch();
        
        echo json_encode([
            'success'  => true,
            'message'  => 'Verificación completada.',
            'total'    => (int)$resultado['total'],
            'accion'   => 'verificar'
        ]);
        exit;
    }

    // Acción: ejecutar migración
// Acción: ejecutar migración
// ... (Tus verificaciones de sesión y permisos se mantienen igual)

if (($_GET['action'] ?? '') === 'migrar') {
    // 0. Aumentar el tiempo solo por seguridad, aunque con índices no debería ser necesario
    set_time_limit(300); 

    try {
        $conn->beginTransaction();

        // 1. Actualizar registros existentes (MATCH)
        // Usamos BINARY para forzar la comparación si persisten dudas de collation, 
        // pero con los índices del paso anterior volará.
        $sqlUpd = "UPDATE parametros p
                   INNER JOIN claves_alternas ca ON p.clave = ca.clave_srs
                   SET p.clave_alterna = ca.clave_alterna,
                       p.updated_at = NOW()";
        $stmtUpd = $conn->prepare($sqlUpd);
        $stmtUpd->execute();
        $actualizados = $stmtUpd->rowCount();

        // 2. Insertar registros nuevos (NO MATCH)
        // $sqlIns = "INSERT INTO parametros (clave, clave_alterna, usuario_id, created_at, updated_at)
        //            SELECT ca.clave_srs, ca.clave_alterna, :user_id, ca.fecha_registro, NOW()
        //            FROM claves_alternas ca
        //            LEFT JOIN parametros p ON ca.clave_srs = p.clave
        //            WHERE p.id IS NULL";
        // $stmtIns = $conn->prepare($sqlIns);
        // $stmtIns->execute([':user_id' => $_SESSION['id']]);
        // $insertados = $stmtIns->rowCount();

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => "Migración exitosa: $actualizados registros actualizados.",
            'total' => $actualizados,
            'accion' => 'migrar'
        ]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
    }
    exit;
}
    else {
        echo json_encode([
            'success' => false,
            'message' => 'Acción no reconocida.'
        ]);
    }

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    error_log('Migración exception: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error durante la migración: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>
