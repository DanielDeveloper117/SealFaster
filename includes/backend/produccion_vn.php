<?php
    require_once(__DIR__ . '/../../config/rutes.php');
    require_once(ROOT_PATH . 'auth/session_manager.php');
    require_once(ROOT_PATH . 'vendor/autoload.php');
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    include(ROOT_PATH . 'includes/backend_info_user.php');
    // --------- CARGAR PREFERENCIAS GUARDADAS PARA EL FORMULARIO ----------
    $arregloSelectRequisiciones = [];
    $default = 2;
    try {
        $preferencias = $_SESSION['filtros_requisiciones'] ?? $preferencias = [
            'estatus' => '',
            'fecha_inicio' => '',
            'fecha_fin' => '',
            'default' => 2, // 1: las de hoy, 2:las de la semana, 3: las del mes
            'orden' => 'des'
        ];
    } catch (Throwable $e) {
        
        $preferencias = [
            'estatus' => '',
            'fecha_inicio' => '',
            'fecha_fin' => '',
            'default' => 2, // 1: las de hoy, 2:las de la semana, 3: las del mes
            'orden' => 'des'
        ];
    }

    try {
        $preferencias = $_SESSION['filtros_requisiciones'] ?? [
            'estatus' => '',
            'fecha_inicio' => '',
            'fecha_fin' => '',
            'default' => 2,
            'orden' => 'desc'
        ];
        // --------- LECTURA DE GET ----------
        $estatus = isset($_GET['estatus']) ? trim($_GET['estatus']) : $preferencias["estatus"];
        $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : null;
        $fecha_fin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
        $default = isset($_GET['default']) ? $_GET['default'] : $preferencias["default"]; 
        $orden = isset($_GET['orden']) && $_GET['orden'] === 'asc' ? 'ASC' : 'DESC';
        $params = [];
        $conditions = [];

        $sqlBase = "
            SELECT 
                r.*,
                (
                    SELECT COUNT(ca.id)
                    FROM comentarios_adjuntos ca
                    WHERE CONCAT(',', REPLACE(r.cotizaciones, ', ', ','), ',') 
                          LIKE CONCAT('%,', ca.id_cotizacion, ',%')
                ) AS total_comentarios
            FROM requisiciones r
            WHERE 1=1
        ";

        // --------- BASE QUERY POR TIPO DE USUARIO ----------
        if ($tipo_usuario === "Administrador") {
            $sqlRequisiciones = $sqlBase;

        } elseif ($tipo_usuario === "Vendedor" && $rol_usuario === "Gerente") {
            $sqlRequisiciones = $sqlBase . " AND (r.sucursal = :area OR r.id_vendedor = :id)";
            $params[':area'] = $areaUser;
            $params[':id'] = $_SESSION['id'];

        } else {
            // vendedor normal
            $sqlRequisiciones = $sqlBase . " AND r.id_vendedor = :id";
            $params[':id'] = $_SESSION['id'];
        }


        // --------- APLICAR FILTROS ----------

        // Filtro por estatus
        if ($estatus) {
            switch($estatus) {
                case 'pendiente':
                    $sqlRequisiciones .= " AND estatus = 'Pendiente'";
                    break;
                case 'autorizada':
                    $sqlRequisiciones .= " AND estatus = 'Autorizada'";
                    break;
                case 'produccion':
                    $sqlRequisiciones .= " AND estatus IN ('Producción', 'En producción')";
                    break;
                case 'finalizada':
                    $sqlRequisiciones .= " AND estatus = 'Finalizada'";
                    break;
                case 'detenida':
                    $sqlRequisiciones .= " AND estatus = 'Detenida'";
                    break;
                case 'archivada':
                    $sqlRequisiciones .= " AND estatus = 'Archivada'";
                    break;
                default:
                    $estatus .= " AND estatus != 'Archivada'";
                    break;

            }
        }else{
            $sqlRequisiciones .= " AND estatus != 'Archivada'";
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
                case 1: // hoy (DEFAULT)
                    $sqlRequisiciones .= " AND DATE(fecha_insercion) = CURDATE()";
                    break;
                case 2: // esta semana
                    $sqlRequisiciones .= " AND YEARWEEK(fecha_insercion, 1) = YEARWEEK(CURDATE(), 1)";
                    break;
                case 3: // este mes
                    $sqlRequisiciones .= " AND YEAR(fecha_insercion) = YEAR(CURDATE()) 
                                        AND MONTH(fecha_insercion) = MONTH(CURDATE())";
                    break;
                // case 0: "Todas" - no se agrega condición
            }
        }

        // --------- ORDEN Y LIMITE ----------
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
        $_SESSION['filtros_requisiciones'] = [
            'estatus' => $estatus,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'default' => $default,
            'orden' => $orden
        ];
        // Sobreescribir con valores actuales de GET si existen
        if (isset($_GET['estatus'])) $preferencias['estatus'] = $_GET['estatus'];
        if (isset($_GET['fecha_inicio'])) $preferencias['fecha_inicio'] = $_GET['fecha_inicio'];
        if (isset($_GET['fecha_fin'])) $preferencias['fecha_fin'] = $_GET['fecha_fin'];
        if (isset($_GET['default'])) $preferencias['default'] = $_GET['default'];
        if (isset($_GET['orden'])) $preferencias['orden'] = $_GET['orden'];

    } catch (Throwable $e) {
        // En error NO se consulta nada
        $arregloSelectRequisiciones = [];
        error_log("Error backend requisiciones CNC: " . $e->getMessage());
        // Sobreescribir con valores actuales de GET si existen
        if (isset($_GET['estatus'])) $preferencias['estatus'] = $_GET['estatus'];
        if (isset($_GET['fecha_inicio'])) $preferencias['fecha_inicio'] = $_GET['fecha_inicio'];
        if (isset($_GET['fecha_fin'])) $preferencias['fecha_fin'] = $_GET['fecha_fin'];
        if (isset($_GET['default'])) $preferencias['default'] = $_GET['default'];
        if (isset($_GET['orden'])) $preferencias['orden'] = $_GET['orden'];
    }

?>