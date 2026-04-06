<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');

try {

    $vendedores = []; // Este será nuestro único arreglo de objetos
    $sqlusuariosVendedores = "
        SELECT id, nombre 
        FROM login 
        WHERE lider = 3
    ";
    $stmt = $conn->prepare($sqlusuariosVendedores);
    $stmt->execute();
    $usuariosVendedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($usuariosVendedores as $vendedor){
        // Desencriptamos el nombre
        $nombreUncript = openssl_decrypt($vendedor['nombre'], 'AES-128-ECB', $PASS_UNCRIPT);
        // Creamos un objeto (asociativo) con ambos datos juntos
        $vendedores[] = [
            'id'     => $vendedor['id'],
            'nombre' => $nombreUncript
        ];
    }

    echo json_encode([
        'success'    => true,
        'vendedores' => $vendedores
    ]);
   

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al consultar la base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>
