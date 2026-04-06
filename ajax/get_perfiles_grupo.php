<?php
// ============================================================
// get_perfiles_grupo.php
// Devuelve:
//   tipo=asignados  → perfiles cuyo grupo_herramienta_id = grupo_id
//   tipo=buscar     → perfiles que coincidan con ?query,
//                     incluyendo su grupo actual para mostrar estado
// ============================================================
// NOTA: este archivo debe guardarse como get_perfiles_grupo.php
//       en la carpeta ajax/. Se incluye aquí por conveniencia
//       de entrega pero debe separarse en producción.
// ============================================================
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

$tipo    = $_GET['tipo']     ?? 'asignados';
$grupoId = (int)($_GET['grupo_id'] ?? 0);
$query   = trim($_GET['query'] ?? '');

try {
    if ($tipo === 'asignados') {
        // Perfiles ya asignados a este grupo
        $stmt = $conn->prepare("
            SELECT p.id, p.nombre, f.nombre AS familia
            FROM perfiles2 p
            LEFT JOIN familias f ON f.id = p.familia_id
            WHERE p.grupo_herramienta_id = :g
            ORDER BY p.nombre ASC
        ");
        $stmt->execute([':g'=>$grupoId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

    } elseif ($tipo === 'buscar') {
        // Buscar perfiles por nombre, incluyendo estado de grupo actual
        if (strlen($query) < 1) { echo json_encode([]); exit; }
        $like = '%' . $query . '%';
        $stmt = $conn->prepare("
            SELECT
                p.id,
                p.nombre,
                p.grupo_herramienta_id,
                f.nombre       AS familia,
                gh.nombre      AS grupo_nombre
            FROM perfiles2 p
            LEFT JOIN familias f           ON f.id  = p.familia_id
            LEFT JOIN grupos_herramienta gh ON gh.id = p.grupo_herramienta_id
            WHERE p.nombre LIKE :q
            ORDER BY p.nombre ASC
            LIMIT 40
        ");
        $stmt->execute([':q'=>$like]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    } else {
        echo json_encode([]);
    }
} catch (PDOException $e) {
    echo json_encode([]);
} finally { $conn = null; }
?>