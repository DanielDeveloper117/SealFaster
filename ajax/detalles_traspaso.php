<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();

try {
    header('Content-Type: application/json');
    
    // Verificar que sea una petición GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        exit;
    }
    
    // Verificar que el usuario esté autenticado
    if (!isset($_SESSION['id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Sesión expirada']);
        exit;
    }
    
    // Verificar que se recibió el ID del traspaso
    if (!isset($_GET['id']) || empty($_GET['id']) || !preg_match('/^\d+$/', $_GET['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID de traspaso inválido']);
        exit;
    }
    
    $operacion_id = (int)$_GET['id'];
    
    // =============== OBTENER DATOS DEL TRASPASO ===============
    $sqlOperacion = "
        SELECT 
            o.*,
            a_origen.almacen AS almacen_origen,
            a_destino.almacen AS almacen_destino
        FROM operaciones_inv AS o
        LEFT JOIN almacenes AS a_origen ON o.almacen_origen_id = a_origen.id
        LEFT JOIN almacenes AS a_destino ON o.almacen_destino_id = a_destino.id
        WHERE o.id = :id AND o.tipo = 'Traspaso'";
    
    $stmtOperacion = $conn->prepare($sqlOperacion);
    $stmtOperacion->bindValue(':id', $operacion_id, PDO::PARAM_INT);
    $stmtOperacion->execute();
    
    $operacion = $stmtOperacion->fetch(PDO::FETCH_ASSOC);
    
    if (!$operacion) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'El traspaso no existe']);
        exit;
    }
    
    // =============== OBTENER USUARIO CREADOR ===============
    $clave_encriptacion = $PASS_UNCRIPT;
    $sqlUsuarioCreador = "SELECT nombre FROM login WHERE id = :id";
    $stmtUsuarioCreador = $conn->prepare($sqlUsuarioCreador);
    $stmtUsuarioCreador->bindValue(':id', $operacion['usuario_id'], PDO::PARAM_INT);
    $stmtUsuarioCreador->execute();
    $usuarioCreador = $stmtUsuarioCreador->fetch(PDO::FETCH_ASSOC);
    
    $nombreCreador = 'N/A';
    if ($usuarioCreador && !empty($usuarioCreador['nombre'])) {
        $nombreCreador = openssl_decrypt($usuarioCreador['nombre'], 'AES-128-ECB', $clave_encriptacion);
    }
    
    // =============== OBTENER USUARIO RECEPTOR (si existe) ===============
    $nombreReceptor = 'N/A';
    if ($operacion['recibido'] == 1 && !empty($operacion['recibio_id'])) {
        $sqlUsuarioReceptor = "SELECT nombre FROM login WHERE id = :id";
        $stmtUsuarioReceptor = $conn->prepare($sqlUsuarioReceptor);
        $stmtUsuarioReceptor->bindValue(':id', $operacion['recibio_id'], PDO::PARAM_INT);
        $stmtUsuarioReceptor->execute();
        $usuarioReceptor = $stmtUsuarioReceptor->fetch(PDO::FETCH_ASSOC);
        
        if ($usuarioReceptor && !empty($usuarioReceptor['nombre'])) {
            $nombreReceptor = openssl_decrypt($usuarioReceptor['nombre'], 'AES-128-ECB', $clave_encriptacion);
        }
    }
    
    // =============== OBTENER BARRAS ASOCIADAS ===============
    $sqlBarras = "
        SELECT 
            id,
            Clave,
            Medida,
            lote_pedimento,
            material,
            proveedor,
            stock,
            estatus
        FROM inventario_cnc
        WHERE operacion_id = :operacion_id
        ORDER BY id ASC";
    
    $stmtBarras = $conn->prepare($sqlBarras);
    $stmtBarras->bindValue(':operacion_id', $operacion_id, PDO::PARAM_INT);
    $stmtBarras->execute();
    
    $barras = $stmtBarras->fetchAll(PDO::FETCH_ASSOC);
    
    // =============== CONSTRUIR RESPUESTA ===============

    $respuesta = [
        'success' => true,
        'operacion' => [
            'id' => $operacion['id'],
            'usuario_creador' => $nombreCreador,
            'almacen_origen' => $operacion['almacen_origen'] ?? 'N/A',
            'almacen_destino' => $operacion['almacen_destino'] ?? 'N/A',
            'justificacion' => $operacion['justificacion'],
            'recibido' => $operacion['recibido'],
            'usuario_receptor' => $nombreReceptor,
            'fecha_recibido' => $operacion['fecha_recibido'],
            'created_at' => $operacion['created_at'],
            'img_envio_barras' => $operacion['img_envio_barras'],
            'img_envio_paquete' => $operacion['img_envio_paquete'],
            'img_recepcion_barras' => $operacion['img_recepcion_barras'],
            'img_recepcion_paquete' => $operacion['img_recepcion_paquete']
        ],
        'barras' => $barras,
        'cantidad_barras' => count($barras)
    ];
    
    echo json_encode($respuesta);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?>
