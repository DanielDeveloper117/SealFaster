<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}
?>
<?php
include(ROOT_PATH . 'scripts/actualizar_proveedores_correctos.php');
//include(ROOT_PATH . 'scripts/actualizar_claves_inventario.php');
?>