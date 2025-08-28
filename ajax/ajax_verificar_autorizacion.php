<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
header('Content-Type: text/html; charset=UTF-8');

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

    if (($autoriza === 'gerente' || $autoriza === 'admin') && $estatus === 'Autorizada') {
        echo 'true';
    } elseif ($autoriza === 'admin' && $estatus === 'Producción') {
        echo 'true';
    } elseif ($autoriza === 'cnc' && $estatus === 'En producción') {
        echo 'true';
    } else {
        echo 'false';
    }

} catch (Throwable $e) {
    error_log("[QR VERIFICACIÓN ERROR] " . date('Y-m-d H:i:s') . " - " . $e->getMessage());
    echo 'false';
}
