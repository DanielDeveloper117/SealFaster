
<?php
// ============================================================
// get_herramientas.php
// Devuelve el catalogo completo de herramientas para el
// selector del formulario de agregar limitante.
// ============================================================
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
header('Content-Type: application/json');

try {
    $stmt = $conn->prepare("SELECT id, numero, descripcion FROM herramientas ORDER BY numero ASC");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode([]);
} finally { $conn = null; }
?>