<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    if (!isset($_GET['id_cotizacion']) || !isset($_GET['componente'])) {
        throw new Exception("Faltan parámetros requeridos (id_cotizacion, componente).");
    }

    $id_cotizacion = trim($_GET['id_cotizacion']);
    $componente = trim($_GET['componente']);

    if (empty($id_cotizacion)) {
         throw new Exception("El ID de cotización no puede estar vacío.");
    }

    // Consulta para obtener la imagen y el nombre del perfil
    // El usuario indica que:
    // cotizacion_materiales.id_cotizacion = control_almacen.id_cotizacion
    // cotizacion_materiales.cantidad_material = control_almacen.componente
    // cotizacion_materiales.img = ruta de la imagen
    
    $sql = "SELECT img, perfil_sello, familia_perfil 
            FROM cotizacion_materiales 
            WHERE id_cotizacion = :id_cotizacion 
            AND cantidad_material = :componente 
            LIMIT 1";
            
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id_cotizacion', $id_cotizacion);
    $stmt->bindValue(':componente', $componente);
    $stmt->execute();
    
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        echo json_encode([
            'success' => true,
            'img' => $resultado['img'],
            'perfil' => $resultado['perfil_sello'],
            'familia' => $resultado['familia_perfil']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'No se encontró información del componente para esta cotización.'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
