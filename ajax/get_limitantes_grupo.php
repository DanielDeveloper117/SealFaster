<?php
// ============================================================
// get_limitantes_grupo.php
// Devuelve las limitantes de herramienta de un grupo filtradas
// por dureza. Usado por el modal de limitantes.
// GET params: grupo_id, dureza (blandos|duros)
// ============================================================
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id'])) { echo json_encode([]); exit; }

$grupoId = (int)($_GET['grupo_id'] ?? 0);
$dureza  = in_array($_GET['dureza'] ?? '', ['blandos','duros']) ? $_GET['dureza'] : 'blandos';

if ($grupoId <= 0) { echo json_encode([]); exit; }

try {
    $stmt = $conn->prepare("
        SELECT
            lh.id,
            h.numero  AS herramienta_numero,
            h.id      AS herramienta_id,
            lh.dureza,
            lh.di_min,     lh.di_max,
            lh.de_min,     lh.de_max,
            lh.seccion_min, lh.seccion_max,
            lh.h_min,      lh.h_max
        FROM limitantes_herramienta lh
        JOIN herramientas h ON h.id = lh.herramienta_id
        WHERE lh.grupo_herramienta_id = :g
          AND lh.dureza = :d
        ORDER BY h.numero ASC
    ");
    $stmt->execute([':g'=>$grupoId, ':d'=>$dureza]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode([]);
} finally { $conn = null; }
?>