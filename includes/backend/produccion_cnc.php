<?php
    require_once(ROOT_PATH . 'vendor/autoload.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'];
        set_error_handler(function($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
        if($action=="finalizar"){
            try{
                $id_requisicion = $_POST['id_requisicion'];

                $queryUpdateRequisicion = "UPDATE requisiciones SET estatus = 'Finalizada', fin_maquinado = NOW() WHERE id_requisicion = :id_requisicion";
                $stmtUpdateRequisicion = $conn->prepare($queryUpdateRequisicion);
                $stmtUpdateRequisicion->bindParam(':id_requisicion', $id_requisicion);
                $stmtUpdateRequisicion->execute();

                $sqlRequisicion = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
                $stmtRequisicion = $conn->prepare($sqlRequisicion);
                $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion);
                $stmtRequisicion->execute();
                $result = $stmtRequisicion->fetch(PDO::FETCH_ASSOC);

                // Dividir los IDs
                $cotizacion_ids = explode(', ', $result['cotizaciones']);

                // Preparar la consulta
                $sql = "UPDATE cotizacion_materiales SET estatus_completado = 'Finalizada', fecha_actualizacion = NOW() WHERE id_cotizacion = :id_cotizacion";
                $stmt = $conn->prepare($sql);

                // Ejecutar para cada id
                foreach ($cotizacion_ids as $id_cotizacion) {
                    try {
                        $stmt->bindValue(':id_cotizacion', $id_cotizacion);
                        $stmt->execute();
                    } catch (Throwable $e) {
                        echo '<p>Error al actualizar la cotización con ID: ' . $id_cotizacion . '. Detalles: ' . addslashes($e->getMessage()) . '"</p>';
                    }
                }


            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("error", "Error", "Error al intentar finalizar la requisición. '. addslashes($e->getMessage()).'", "self");
                });</script>';
                exit;
            }
            ////////////////////////////PHP MAILER -> cotizador a  ////////////////

            try {
                $id_requisicion = $_POST['id_requisicion'];

                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("success", "Proceso exitoso", "Estatus de requisición cambiado a Finalizada.", "self");
                });</script>';

            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("error", "Error", "Error al enviar correo. '. addslashes($e->getMessage()).' - '.$mail->ErrorInfo .'", "self");
                });</script>';
                exit;        
            }
            ////////////////////////////////////////////////////////////////////////
        }
    }

include(ROOT_PATH . 'includes/backend_info_user.php');

try {
    $preferencias = $_SESSION['filtros_requisiciones_cnc'] ?? [
        'estatus' => '',
        'fecha_inicio' => '',
        'fecha_fin' => '',
        'default' => 2,
        'orden' => 'des'
    ];
    // --------- LECTURA DE GET ----------
    $estatus = isset($_GET['estatus']) && $_GET['estatus'] !== '' ? trim($_GET['estatus']) : null;
    $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : null;
    $fecha_fin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
    $default = isset($_GET['default']) ? (int)$_GET['default'] : $preferencias["default"]; // Default: 2 = Esta semana
    $orden = isset($_GET['orden']) && $_GET['orden'] === 'asc' ? 'ASC' : 'DESC';

    $params = [];
    
    // --------- BASE QUERY POR TIPO Y ROL DE USUARIO (NUNCA mostrar "Pendiente") ----------
    $condicionBase = "estatus != 'Pendiente'";
    
    if (($tipo_usuario == "CNC" && ($rol_usuario == "Gerente" || $rol_usuario == "Auxiliar")) || $tipo_usuario == "Administrador") {
        // Gerente/Auxiliar CNC o Administrador - ve todas las requisiciones NO PENDIENTES
        $sqlRequisiciones = "SELECT * FROM requisiciones WHERE $condicionBase";
    } else if ($tipo_usuario == "CNC" && ($rol_usuario != "Gerente" && $rol_usuario != "Auxiliar")) {
        // Operador CNC - ve solo las de su máquina NO PENDIENTES
        $sqlRequisiciones = "SELECT * FROM requisiciones WHERE $condicionBase AND maquina = :maquina";
        $params[':maquina'] = $rolUser;
    } else if ($tipo_usuario == "Vendedor" && $rol_usuario == "Gerente") {
        // Gerente de ventas - ve las de su área/sucursal NO PENDIENTES
        $sqlRequisiciones = "SELECT * FROM requisiciones WHERE $condicionBase AND sucursal = :area";
        $params[':area'] = $areaUser;
    } else if ($tipo_usuario == "Vendedor") {
        // Vendedor regular - ve solo sus propias requisiciones NO PENDIENTES
        $sqlRequisiciones = "SELECT * FROM requisiciones WHERE $condicionBase AND id_vendedor = :id";
        $params[':id'] = $_SESSION['id'];
    } else {
        // Otros tipos de usuario (Inventarios, etc.) - ve todas NO PENDIENTES
        $sqlRequisiciones = "SELECT * FROM requisiciones WHERE $condicionBase";
    }

    // --------- APLICAR FILTROS ADICIONALES (EXCLUYENDO "Pendiente") ----------

    // Filtro por estatus (SOLO estatus no pendientes)
    if ($estatus) {
        switch($estatus) {
            case 'autorizada':
                $sqlRequisiciones .= " AND estatus = 'Autorizada'";
                break;
            case 'produccion':
                $sqlRequisiciones .= " AND (estatus = 'Producción' OR estatus = 'En producción')";
                break;
            case 'finalizada':
                $sqlRequisiciones .= " AND estatus = 'Finalizada'";
                break;
            // NO INCLUIR 'pendiente' como opción
        }
    }

    // Filtros de Fechas
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
        // Solo se ejecuta si NO hay fecha específica seleccionada
        switch ($default) {
            case 1: // hoy
                $sqlRequisiciones .= " AND DATE(fecha_insercion) = CURDATE()";
                break;
            case 2: // esta semana (DEFAULT)
                $sqlRequisiciones .= " AND YEARWEEK(fecha_insercion, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 3: // este mes
                $sqlRequisiciones .= " AND YEAR(fecha_insercion) = YEAR(CURDATE()) 
                                    AND MONTH(fecha_insercion) = MONTH(CURDATE())";
                break;
            // case 0: "Todas" - no se agrega condición
        }
    }

    // --------- ORDEN ----------
    $sqlRequisiciones .= " ORDER BY id_requisicion $orden";

    // --------- EJECUCIÓN ----------
    $stmtRequisiciones = $conn->prepare($sqlRequisiciones);
    
    foreach ($params as $k => $v) {
        $type = is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmtRequisiciones->bindValue($k, $v, $type);
    }
    
    $stmtRequisiciones->execute();
    $arregloSelectRequisiciones = $stmtRequisiciones->fetchAll(PDO::FETCH_ASSOC);

    // --------- GUARDAR PREFERENCIAS EN SESIÓN ----------
    $_SESSION['filtros_requisiciones_cnc'] = [
        'estatus' => $estatus,
        'fecha_inicio' => $fecha_inicio,
        'fecha_fin' => $fecha_fin,
        'default' => $default,
        'orden' => $orden
    ];

} catch (Throwable $e) {
    // Fallback robusto en caso de error (TAMBIÉN EXCLUYE "Pendiente")
    try {
        $condicionFallback = "estatus != 'Pendiente'";
        
        if (($tipo_usuario == "CNC" && ($rol_usuario == "Gerente" || $rol_usuario == "Auxiliar")) || $tipo_usuario == "Administrador") {
            $sqlFallback = "SELECT * FROM requisiciones WHERE $condicionFallback ORDER BY id_requisicion DESC";
            $stmtRequisiciones = $conn->prepare($sqlFallback);
        } else if ($tipo_usuario == "CNC" && ($rol_usuario != "Gerente" && $rol_usuario != "Auxiliar")) {
            $sqlFallback = "SELECT * FROM requisiciones WHERE $condicionFallback AND maquina = :maquina ORDER BY id_requisicion DESC";
            $stmtRequisiciones = $conn->prepare($sqlFallback);
            $stmtRequisiciones->bindParam(':maquina', $rolUser);
        } else if ($tipo_usuario == "Vendedor" && $rol_usuario == "Gerente") {
            $sqlFallback = "SELECT * FROM requisiciones WHERE $condicionFallback AND sucursal = :area ORDER BY id_requisicion DESC";
            $stmtRequisiciones = $conn->prepare($sqlFallback);
            $stmtRequisiciones->bindParam(':area', $areaUser);
        } else if ($tipo_usuario == "Vendedor") {
            $sqlFallback = "SELECT * FROM requisiciones WHERE $condicionFallback AND id_vendedor = :id ORDER BY id_requisicion DESC";
            $stmtRequisiciones = $conn->prepare($sqlFallback);
            $stmtRequisiciones->bindParam(':id', $_SESSION['id']);
        } else {
            $sqlFallback = "SELECT * FROM requisiciones WHERE $condicionFallback ORDER BY id_requisicion DESC";
            $stmtRequisiciones = $conn->prepare($sqlFallback);
        }
        
        $stmtRequisiciones->execute();
        $arregloSelectRequisiciones = $stmtRequisiciones->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Error en filtros de requisiciones (CNC): " . $e->getMessage());

    } catch (Throwable $e2) {
        // Si también falla el fallback
        $arregloSelectRequisiciones = [];
        error_log("Error crítico en filtros de requisiciones (CNC): " . $e2->getMessage());
    }
}

// --------- CARGAR PREFERENCIAS GUARDADAS PARA EL FORMULARIO ----------
$preferencias = $_SESSION['filtros_requisiciones_cnc'] ?? [
    'estatus' => '',
    'fecha_inicio' => '',
    'fecha_fin' => '',
    'default' => 2, // Default: 2 = Esta semana
    'orden' => 'des'
];

// Sobreescribir con valores actuales de GET si existen
if (isset($_GET['estatus'])) $preferencias['estatus'] = $_GET['estatus'];
if (isset($_GET['fecha_inicio'])) $preferencias['fecha_inicio'] = $_GET['fecha_inicio'];
if (isset($_GET['fecha_fin'])) $preferencias['fecha_fin'] = $_GET['fecha_fin'];
if (isset($_GET['default'])) $preferencias['default'] = $_GET['default'];
if (isset($_GET['orden'])) $preferencias['orden'] = $_GET['orden'];

?>