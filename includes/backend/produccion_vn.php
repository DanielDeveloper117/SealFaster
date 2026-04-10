<?php
    require_once(__DIR__ . '/../../config/rutes.php');
    require_once(ROOT_PATH . 'auth/session_manager.php');
    require_once(ROOT_PATH . 'vendor/autoload.php');
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'];
        set_error_handler(function($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        // NOTA: Las acciones 'insert' y 'update' se migraron al endpoint AJAX:
        // ajax/guardar_requisicion_vn.php

        if($action=="autorizada"){
            try{
                $id_requisicion = $_POST['id_requisicion'];

                // Iniciar transacción para consistencia
                $conn->beginTransaction();

                // 1. Actualizar estado de la requisición
                $queryUpdateRequisicion = "UPDATE requisiciones SET estatus = 'Autorizada' WHERE id_requisicion = :id_requisicion";
                $stmtUpdateRequisicion = $conn->prepare($queryUpdateRequisicion);
                $stmtUpdateRequisicion->bindParam(':id_requisicion', $id_requisicion);
                $stmtUpdateRequisicion->execute();

                // 2. Obtener cotizaciones asociadas
                $sqlRequisicion = "SELECT cotizaciones FROM requisiciones WHERE id_requisicion = :id_requisicion";
                $stmtRequisicion = $conn->prepare($sqlRequisicion);
                $stmtRequisicion->bindParam(':id_requisicion', $id_requisicion);
                $stmtRequisicion->execute();
                $result = $stmtRequisicion->fetch(PDO::FETCH_ASSOC);

                $cotizacion_ids = explode(', ', $result['cotizaciones']);

                // 3. Actualizar estado de cada cotización
                $sqlUpdateCotizacion = "UPDATE cotizacion_materiales SET estatus_completado = 'Autorizada', fecha_actualizacion = NOW() WHERE id_cotizacion = :id_cotizacion";
                $stmtUpdateCotizacion = $conn->prepare($sqlUpdateCotizacion);

                // 4. Preparar consulta para actualizar inventario
                $sqlUpdatePreStock = "UPDATE inventario_cnc SET pre_stock = pre_stock - :consumo_total, updated_at = NOW() WHERE lote_pedimento = :lote_pedimento";
                $stmtUpdatePreStock = $conn->prepare($sqlUpdatePreStock);

                // 5. Array para acumular consumo por lote_pedimento
                $consumoPorLote = [];

                foreach ($cotizacion_ids as $id_cotizacion) {
                    try {
                        // Actualizar estado de la cotización
                        $stmtUpdateCotizacion->bindValue(':id_cotizacion', $id_cotizacion);
                        $stmtUpdateCotizacion->execute();

                        // Obtener todas las estimaciones de esta cotización
                        $sqlEstimaciones = "SELECT * FROM cotizacion_materiales WHERE id_cotizacion = :id_cotizacion";
                        $stmtEstimaciones = $conn->prepare($sqlEstimaciones);
                        $stmtEstimaciones->bindValue(':id_cotizacion', $id_cotizacion);
                        $stmtEstimaciones->execute();
                        $estimaciones = $stmtEstimaciones->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($estimaciones as $estimacion) {
                            $a_sello = floatval($estimacion['a_sello']);
                            $material = $estimacion['material'];
                            $billets_lotes = $estimacion['billets_claves_lotes'];

                            // Determinar desbaste por material
                            $desbaste = 2.50; // Valor por defecto (material duro)
                            
                            $materialesBlandos = ['H-ECOPUR', 'ECOSIL', 'ECORUBBER 1', 'ECORUBBER 2', 'ECORUBBER 3', 'ECOPUR'];
                            $materialesDuros = ['ECOTAL', 'ECOMID', 'ECOFLON 1', 'ECOFLON 2', 'ECOFLON 3'];
                            
                            if (in_array($material, $materialesBlandos)) {
                                $desbaste = 2.00;
                            } elseif (in_array($material, $materialesDuros)) {
                                $desbaste = 2.50;
                            }

                            // Procesar cada billet/lote
                            $billets = array_map('trim', explode(',', $billets_lotes));
                            
                            foreach ($billets as $billet) {
                                // Extraer lote_pedimento y cantidad de piezas
                                // Formato: "R2T047062-1 (47/62) 1 pz"
                                if (preg_match('/^([^\s]+)\s+\([^)]+\)\s+(\d+)\s+pz$/i', $billet, $matches)) {
                                    $lote_pedimento = trim($matches[1]);
                                    $cantidad_piezas = intval($matches[2]);
                                    
                                    // Calcular consumo para este billet
                                    $altura_por_pieza = $a_sello + $desbaste;
                                    $consumo_total = $altura_por_pieza * $cantidad_piezas;
                                    
                                    // Acumular consumo por lote_pedimento
                                    if (!isset($consumoPorLote[$lote_pedimento])) {
                                        $consumoPorLote[$lote_pedimento] = 0;
                                    }
                                    $consumoPorLote[$lote_pedimento] += $consumo_total;
                                    
                                    // Log para debugging
                                    error_log("Lote: $lote_pedimento, Material: $material, Desbaste: $desbaste, Altura sello: $a_sello, Piezas: $cantidad_piezas, Consumo: $consumo_total");
                                }
                            }
                        }

                    } catch (Throwable $e) {
                        throw new Exception("Error al procesar cotización ID $id_cotizacion: " . $e->getMessage());
                    }
                }

                // 6. Actualizar pre_stock en inventario_cnc para cada lote
                foreach ($consumoPorLote as $lote_pedimento => $consumo_total) {
                    try {
                        $stmtUpdatePreStock->bindValue(':consumo_total', $consumo_total);
                        $stmtUpdatePreStock->bindValue(':lote_pedimento', $lote_pedimento);
                        $stmtUpdatePreStock->execute();
                        
                        // Verificar si se actualizó correctamente
                        if ($stmtUpdatePreStock->rowCount() === 0) {
                            error_log("Advertencia: Lote pedimento no encontrado: $lote_pedimento");
                        }
                        
                    } catch (Throwable $e) {
                        throw new Exception("Error al actualizar inventario para lote $lote_pedimento: " . $e->getMessage());
                    }
                }

                // Confirmar transacción
                $conn->commit();

                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("success", "Éxito", "Requisición autorizada y inventario actualizado correctamente", "self");
                });</script>';

            } catch (Throwable $e) {
                // Revertir transacción en caso de error
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("error", "Error", "Error al intentar actualizar estatus de cotización: ' . addslashes($e->getMessage()) . '", "self");
                });</script>';
                exit;
            }

            ////////////////////////////PHP MAILER -> cotizador a Inventarios ////////////////
            try {
                //require_once(ROOT_PATH . 'includes/PHPMailer.php');
                //$mail = getMailer($conn);
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = $HOST;
                $mail->SMTPAuth = true;
                $mail->Username = $USER;
                $mail->Password = $PASS; 
                $mail->SMTPSecure = $SECURE;
                $mail->Port = $PORT;
                $mail->setFrom($FROM, $DOMAIN_NAME);
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';
                $id_requisicion = $_POST['id_requisicion'];
                //$sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6 AND rol = 'Gerente'";
                $sqlCorreoInventarios = "SELECT usuario FROM login WHERE lider = 6";
                $stmt = $conn->prepare($sqlCorreoInventarios);
                $stmt->execute();
                $correosInventarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!$correosInventarios || count($correosInventarios) === 0) {
                    throw new Exception("No se encontro ningun correo de inventarios.");
                }

                $clave_encriptacion = $PASS_UNCRIPT ?? '';
                $contadorCorreos = 0;

                foreach ($correosInventarios as $fila) {
                    if (!empty($fila['usuario'])) {
                        $correo = openssl_decrypt($fila['usuario'], 'AES-128-ECB', $clave_encriptacion);
                        if ($correo) {
                            if($DEV_MODE === false){
                                $mail->addAddress($correo);
                            }
                            $contadorCorreos++;
                        }
                    }
                }

                if ($contadorCorreos === 0) {
                    throw new Exception("No se pudo agregar ningún destinatario valido para inventarios.");
                }
                if($DEV_MODE === true){
                    $mail->addAddress($DEV_EMAIL);
                }
                $mail->isHTML(true);
                $mail->Subject = 'Nueva requisición pendiente. Folio: '.$id_requisicion;
                $mail->Body = "Se ha autorizado el maquinado de sello de una nueva requisición.<br>
                            Se necesita su ingreso al sistema para agregar las barras correspondientes al control de almacen.<br>
                            Folio de requisición: <b>".$id_requisicion."</b>";

                if($SEND_MAIL === true){
                    // enviar correo
                    if (!$mail->send()) {
                        throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
                    }
    
                    echo '<script>document.addEventListener("DOMContentLoaded", function () {
                        sweetAlertResponse("success", "Proceso exitoso", "Correo enviado exitosamente a Inventarios para continuar con el siguiente proceso.", "self");
                    });</script>';

                }else{
                    echo '<script>document.addEventListener("DOMContentLoaded", function () {
                        sweetAlertResponse("success", "Proceso exitoso", "Inventarios podrá continuar con el siguiente proceso. Envío de correos no disponible.", "self");
                    });</script>';
                }

            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("error", "Error", "Error al enviar correo'. addslashes($e->getMessage()).'", "self");
                });</script>';
                exit;        
            }
            ////////////////////////////////////////////////////////////////////////
            
        }
    }

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