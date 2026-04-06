<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'vendor/autoload.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    header('Content-Type: application/json');

    // Validar que todos los campos POST estén presentes
    $camposRequeridos = ['id_requisicion', 'id_control', 'justificacion_eliminacion'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
            echo json_encode([
                'success' => false,
                'message' => "Campo requerido faltante: $campo"
            ]);
            exit;
        }
    }

    $id_requisicion = trim($_POST['id_requisicion']);
    $id_control = trim($_POST['id_control']);
    $justificacion_eliminacion = trim($_POST['justificacion_eliminacion']);

    // Validar longitud de justificación
    if (strlen($justificacion_eliminacion) < 10) {
        echo json_encode([
            'success' => false,
            'message' => "La justificación debe tener al menos 10 caracteres"
        ]);
        exit;
    }

    // Iniciar transacción
    $conn->beginTransaction();

    try {
        // 1. Verificar que exista el registro en control_almacen
        $stmtCheck = $conn->prepare("
            SELECT * FROM control_almacen 
            WHERE id_control = :id_control 
            AND id_requisicion = :id_requisicion
            LIMIT 1
        ");
        $stmtCheck->bindParam(':id_control', $id_control, PDO::PARAM_INT);
        $stmtCheck->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        $stmtCheck->execute();
        $registro = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$registro) {
            throw new Exception("No se encontró el registro con id_control: $id_control e id_requisicion: $id_requisicion");
        }

        // VALIDACIÓN: No permitir eliminar si es la única barra activa del grupo (misma cotización, componente y clave)
        // Se consideran barras activas aquellas que no están eliminadas Y (son originales O son reemplazos/extras autorizados)
        $id_cot = $registro['id_cotizacion'];
        $comp = $registro['componente'];
        $clave = $registro['clave'];

        $stmtCount = $conn->prepare("
            SELECT COUNT(*) FROM control_almacen 
            WHERE id_requisicion = :id_requisicion
            AND id_cotizacion = :id_cotizacion
            AND componente = :componente
            AND clave = :clave
            AND es_eliminacion = 0
            AND (
                (es_remplazo = 0 AND es_extra = 0)
                OR (es_remplazo = 1 AND es_remplazo_auth = 1)
                OR (es_extra = 1 AND es_extra_auth = 1)
            )
        ");
        $stmtCount->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);
        $stmtCount->bindParam(':id_cotizacion', $id_cot);
        $stmtCount->bindParam(':componente', $comp);
        $stmtCount->bindParam(':clave', $clave);
        $stmtCount->execute();
        $activasCount = $stmtCount->fetchColumn();

        if ($activasCount <= 1) {
            throw new Exception("No se puede solicitar la eliminación de la única barra activa para este componente (" . ($clave ?: 'sin clave') . "). Debe solicitar un reemplazo en su lugar.");
        }

        // 2. Actualizar control_almacen para solicitar eliminación
        $stmtUpdate = $conn->prepare("
            UPDATE control_almacen 
            SET es_eliminacion = 1,
                justificacion_eliminacion = :justificacion_eliminacion,
                updated_at = NOW()
            WHERE id_control = :id_control
        ");
        $stmtUpdate->bindParam(':justificacion_eliminacion', $justificacion_eliminacion);
        $stmtUpdate->bindParam(':id_control', $id_control, PDO::PARAM_INT);

        if (!$stmtUpdate->execute()) {
            throw new Exception("Error al actualizar control_almacen para eliminar");
        }

        // 3. Marcar en requisiciones que hay una barra pendiente
        $stmtUpdateRequisicion = $conn->prepare("
            UPDATE requisiciones 
            SET barra_pendiente = 1
            WHERE id_requisicion = :id_requisicion
        ");
        $stmtUpdateRequisicion->bindParam(':id_requisicion', $id_requisicion, PDO::PARAM_INT);

        if (!$stmtUpdateRequisicion->execute()) {
            throw new Exception("Error al actualizar el campo barra_pendiente en requisiciones");
        }

        // Confirmar transacción
        $conn->commit();

        // 4. Preparar datos para el correo (opcional, siguiendo el patrón de otros archivos)
        // Por ahora omitimos el envío de correo para simplificar, pero el patrón está ahí si se requiere.
        
        echo json_encode([
            'success' => true,
            'message' => "Solicitud de eliminación de barra registrada correctamente. Pendiente de autorización."
        ]);

    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }

} catch (PDOException $e) {
    error_log("Error en solicitar_eliminacion_barra: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
    http_response_code(500);
} catch (Exception $e) {
    error_log("Error en solicitar_eliminacion_barra: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    http_response_code(400);
} finally {
    $conn = null;
}
?>
