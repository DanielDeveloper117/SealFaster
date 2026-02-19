<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_GET['id_requisicion'], $_GET['autoriza'])) {
    echo 'false';
    exit;
}

$id = $_GET['id_requisicion'];
$autoriza = $_GET['autoriza'];
if ($autoriza === 'g') {
    $autoriza = "gerente";
} elseif ($autoriza === 'a') {
    $autoriza = "admin";
} elseif ($autoriza === 'cnc') {
    $autoriza = "cnc";
} else {
    echo 'false';
    exit;
}
try {
    $stmt = $conn->prepare("SELECT estatus FROM requisiciones WHERE id_requisicion = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $estatus = $stmt->fetchColumn();

    $autorizado = false;

    if (($autoriza === 'gerente' || $autoriza === 'admin') && $estatus === 'Autorizada') {
        $autorizado = true;
    } elseif ($autoriza === 'admin' && $estatus === 'Producción') {
        $autorizado = true;
    } elseif ($autoriza === 'cnc' && $estatus === 'En producción') {
        $autorizado = true;
    }

    // Retorna un objeto JSON real
    echo json_encode(['autorizado' => $autorizado]);

} catch (Throwable $e) {
    echo json_encode(['autorizado' => false, 'error' => $e->getMessage()]);
}
