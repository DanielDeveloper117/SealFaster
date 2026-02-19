<?php
require_once(ROOT_PATH . 'vendor/autoload.php');
include(ROOT_PATH . 'includes/backend_info_user.php');

$arregloSelectRequisiciones = [];

try {
    $preferencias = $_SESSION['filtros_requisiciones_cnc'] ?? [
        'estatus' => '',
        'sucursal' => '',
        'fecha_inicio' => '',
        'fecha_fin' => '',
        'default' => 2,
        'orden' => 'desc'
    ];

    // --------- LECTURA DE GET ----------
    $estatus = isset($_GET['estatus']) ? trim($_GET['estatus']) : $preferencias["estatus"];
    $sucursal = isset($_GET['sucursal']) ? trim($_GET['sucursal']) : $preferencias["sucursal"];
    $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : null;
    $fecha_fin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
    $default = isset($_GET['default']) ? (int)$_GET['default'] : $preferencias['default'];
    $orden = (isset($_GET['orden']) && $_GET['orden'] === 'asc') ? 'ASC' : 'DESC';

    $params = [];

    // --------- CONDICIÓN BASE ----------
    $condicionBase = "
        SELECT 
            r.*,
            (
                SELECT COUNT(ca.id)
                FROM comentarios_adjuntos ca
                WHERE CONCAT(',', REPLACE(r.cotizaciones, ', ', ','), ',') 
                      LIKE CONCAT('%,', ca.id_cotizacion, ',%')
            ) AS total_comentarios
        FROM requisiciones r
        WHERE estatus != 'Pendiente' AND estatus != 'Archivada' 
    ";

    // --------- VISIBILIDAD POR ROL ----------
    if (
        ($tipo_usuario === "CNC" && in_array($rol_usuario, ["Gerente", "Auxiliar"])) ||
        $tipo_usuario === "Administrador"
    ) {
        // Ve todo excepto pendientes
        $sqlRequisiciones = "$condicionBase";

    } elseif ($tipo_usuario === "CNC") {
        // Operador CNC
        $sqlRequisiciones = "
            $condicionBase
              AND (
                    (estatus = 'Autorizada' AND (maquina IS NULL OR maquina = ''))
                 OR (estatus != 'Autorizada' AND maquina = :maquina)
              )
        ";
        $params[':maquina'] = $rolUser;

    } elseif ($tipo_usuario === "Inventarios") {
        // Inventarios ve todo excepto Autorizada
        $sqlRequisiciones = "$condicionBase ";

    } else {
        $sqlRequisiciones = "$condicionBase";
    }

    // --------- FILTRO DE ESTATUS (SOLO FILTRA, NO CONTROLA VISIBILIDAD) ----------
    if ($estatus) {
        switch ($estatus) {
            case 'autorizada':
                $sqlRequisiciones .= " AND estatus = 'Autorizada'";
                break;

            case 'produccion':
                $sqlRequisiciones .= " AND estatus IN ('Producción', 'En producción')";
                break;

            case 'finalizada':
                $sqlRequisiciones .= " AND estatus = 'Finalizada'";
                break;
            case 'completada':
                $sqlRequisiciones .= " AND estatus = 'Completada'";
                break;
            case 'detenida':
                $sqlRequisiciones .= " AND estatus = 'Detenida'";
                break;
        }
    }

    if ($sucursal) {
        $sqlRequisiciones .= " AND sucursal = :sucursal";
        $params[':sucursal'] = $sucursal;
    }

    // --------- FILTROS DE FECHA ----------
    if ($fecha_inicio && $fecha_fin) {
        $sqlRequisiciones .= " AND DATE(fecha_insercion) BETWEEN :fecha_inicio AND :fecha_fin";
        $params[':fecha_inicio'] = $fecha_inicio;
        $params[':fecha_fin'] = $fecha_fin;
    } elseif ($fecha_inicio) {
        $sqlRequisiciones .= " AND DATE(fecha_insercion) >= :fecha_inicio";
        $params[':fecha_inicio'] = $fecha_inicio;
    } elseif ($fecha_fin) {
        $sqlRequisiciones .= " AND DATE(fecha_insercion) <= :fecha_fin";
        $params[':fecha_fin'] = $fecha_fin;
    } elseif ($default > 0) {
        switch ($default) {
            case 1:
                $sqlRequisiciones .= " AND DATE(fecha_insercion) = CURDATE()";
                break;
            case 2:
                $sqlRequisiciones .= " AND YEARWEEK(fecha_insercion, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 3:
                $sqlRequisiciones .= " AND YEAR(fecha_insercion) = YEAR(CURDATE())
                                       AND MONTH(fecha_insercion) = MONTH(CURDATE())";
                break;
        }
    }

    // --------- ORDEN ----------
    $sqlRequisiciones .= " ORDER BY id_requisicion $orden";

    // --------- EJECUCIÓN ----------
    $stmt = $conn->prepare($sqlRequisiciones);

    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    $stmt->execute();
    $arregloSelectRequisiciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // --------- GUARDAR PREFERENCIAS ----------
    $_SESSION['filtros_requisiciones_cnc'] = [
        'estatus' => $estatus,
        'sucursal' => $sucursal,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'default' => $default,
        'orden' => strtolower($orden)
    ];

} catch (Throwable $e) {
    // En error NO se consulta nada
    $arregloSelectRequisiciones = [];
    error_log("Error backend requisiciones CNC: " . $e->getMessage());
}

// --------- PREFERENCIAS PARA UI ----------
$preferencias = $_SESSION['filtros_requisiciones_cnc'] ?? [
    'estatus' => '',
    'sucursal' => '',
    'fecha_inicio' => '',
    'fecha_fin' => '',
    'default' => 2,
    'orden' => 'des'
];
?>
