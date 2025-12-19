<?php
    require_once(ROOT_PATH . 'vendor/autoload.php');
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $action = $_POST['action'];
        set_error_handler(function($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        if ($action === 'insert') {
            try{
                $id_vendedor = $_POST['id_vendedor'];
                $estatus = $_POST['estatus'];
                $cotizaciones = $_POST['cotizaciones'];
                $nombre_vendedor = $_POST['nombre_vendedor'];
                $sucursal = $_POST['sucursal'];
                $cliente = $_POST['cliente'];
                $fechahora = $_POST['fechahora'];
                //$folio = $_POST['folio'];
                $num_pedido = $_POST['num_pedido'];
                $factura = $_POST['factura'];
                $paqueteria = $_POST['paqueteria'];
                $comentario = mb_substr($_POST['comentario'], 0, 75, 'UTF-8');

                if(empty($cotizaciones)){
                    echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("warning", "Advertencia", "La requisición debe tener al menos una cotizacion, intente nuevamente.", "self");
                    });</script>';
                    exit;
                }else{

                }

                $sql = "INSERT INTO requisiciones (id_vendedor, estatus, cotizaciones, nombre_vendedor, sucursal, cliente, fechahora, num_pedido, factura, paqueteria, comentario) 
                                        VALUES (:id_vendedor, :estatus, :cotizaciones, :nombre_vendedor, :sucursal, :cliente, :fechahora, :num_pedido, :factura , :paqueteria , :comentario)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_vendedor', $id_vendedor);
                $stmt->bindParam(':estatus', $estatus);
                $stmt->bindParam(':cotizaciones', $cotizaciones);
                $stmt->bindParam(':nombre_vendedor', $nombre_vendedor);
                $stmt->bindParam(':sucursal', $sucursal);
                $stmt->bindParam(':cliente', $cliente);
                $stmt->bindParam(':fechahora', $fechahora);
                //$stmt->bindParam(':folio', $folio);
                $stmt->bindParam(':num_pedido', $num_pedido);
                $stmt->bindParam(':factura', $factura);
                $stmt->bindParam(':paqueteria', $paqueteria);
                $stmt->bindParam(':comentario', $comentario);
                $stmt->execute();

                // ACTUALIZAR QUE EL FOLIO SEA IGUAL A LA ID REQUISICION
                $id_requisicion = $conn->lastInsertId();
                $update = $conn->prepare("UPDATE requisiciones SET folio = :folio WHERE id_requisicion = :id");
                $update->execute([
                    'folio' => $id_requisicion,
                    'id' => $id_requisicion
                ]);

            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("error", "Error", "Error al intentar agregar registro'. addslashes($e->getMessage()).'", "self");
                });</script>';
                exit;
            }
            ////////////////////////////PHP MAILER -> cotizador a gerente VN ////////////////
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

                // Clave de encriptacionn
                $clave_encriptacion = $PASS_UNCRIPT ?? '';

                $area_desencriptada = $sucursal;
                $areaGerenteEncriptado = openssl_encrypt($area_desencriptada, 'AES-128-ECB', $clave_encriptacion);

                if($sucursal == "Ventas Nacionales"){
                    $sqlCorreoVentasGerencia = "SELECT usuario FROM login WHERE lider = 3 AND (rol = 'Gerente' AND area = :area) OR rol = 'CORREO_DIRECCION'";
                    $stmt = $conn->prepare($sqlCorreoVentasGerencia);
                    $stmt->bindParam(':area', $areaGerenteEncriptado);   
                }else{
                    $sqlCorreoVentasGerencia = "SELECT usuario FROM login WHERE lider = 3 AND area = :area AND rol = 'Gerente'";
                    $stmt = $conn->prepare($sqlCorreoVentasGerencia);
                    $stmt->bindParam(':area', $areaGerenteEncriptado);                    
                }
                $stmt->execute();
                $correosGerencia = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!$correosGerencia || count($correosGerencia) === 0) {
                    throw new Exception("No se encontro ningun correo de gerencia.");
                }

                $contadorCorreos = 0;

                foreach ($correosGerencia as $fila) {
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
                    throw new Exception("No se pudo agregar ningún destinatario valido.");
                }
                $mail->addAddress($DEV_EMAIL);
                
                $mail->Subject = 'Nueva requisición por autorizar. Folio: '.$id_requisicion;
                $mail->Body = "$nombre_vendedor ha generado una requisición para el maquinado de sello. Vaya a la sección de <b>Requisiciones</b> para autorizarla con su firma.<br>Folio de requisición: <b>".$id_requisicion."</b>";
                // enviar correo
                if (!$mail->send()) {
                    throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
                }

                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("success", "Proceso exitoso", "Registro agregado correctamente. Correo enviado a gerencia.", "self");
                });</script>';

            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("warning", "Aviso", "Registro agregado exitosamente. No se pudo enviar correo a gerencia. '. addslashes($e->getMessage()) .'", "self");
                });</script>';
                exit;
            }
            ////////////////////////////////////////////////////////////////////////

        } elseif ($action === 'update') {
            try {
                $id_requisicion = $_POST['id_requisicion'];
                $id_vendedor = $_POST['id_vendedor'];
                $estatus = $_POST['estatus'];
                $cotizaciones = $_POST['cotizaciones'];
                $nombre_vendedor = $_POST['nombre_vendedor'];
                $sucursal = $_POST['sucursal'];
                $cliente = $_POST['cliente'];
                $fechahora = $_POST['fechahora'];
                // $folio = $_POST['folio'];
                $num_pedido = $_POST['num_pedido'];
                $factura = $_POST['factura'];
                $paqueteria = $_POST['paqueteria'];
                $comentario = mb_substr($_POST['comentario'], 0, 75, 'UTF-8');

                if(empty($cotizaciones)){
                    echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("warning", "Advertencia", "La requisición debe tener al menos una cotizacion, intente nuevamente.", "self");
                    });</script>';
                    exit;
                }

                $sql = "UPDATE requisiciones SET 
                            id_vendedor = :id_vendedor,
                            estatus = :estatus,
                            cotizaciones = :cotizaciones,
                            nombre_vendedor = :nombre_vendedor,
                            sucursal = :sucursal,
                            cliente = :cliente,
                            fechahora = :fechahora,
                            
                            num_pedido = :num_pedido,
                            factura = :factura,
                            paqueteria = :paqueteria,
                            comentario = :comentario
                        WHERE id_requisicion = :id_requisicion";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_requisicion', $id_requisicion);
                $stmt->bindParam(':id_vendedor', $id_vendedor);
                $stmt->bindParam(':estatus', $estatus);
                $stmt->bindParam(':cotizaciones', $cotizaciones);
                $stmt->bindParam(':nombre_vendedor', $nombre_vendedor);
                $stmt->bindParam(':sucursal', $sucursal);
                $stmt->bindParam(':cliente', $cliente);
                $stmt->bindParam(':fechahora', $fechahora);
                //$stmt->bindParam(':folio', $folio);
                $stmt->bindParam(':num_pedido', $num_pedido);
                $stmt->bindParam(':factura', $factura);
                $stmt->bindParam(':paqueteria', $paqueteria);
                $stmt->bindParam(':comentario', $comentario);
                $stmt->execute();

                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("success", "Proceso exitoso", "Registro actualizado correctamete.", "none");
                });</script>';

            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("error", "Error", "Error al intentar actualizar registro'. addslashes($e->getMessage()).'", "self");
                });</script>';
                exit;
            }
        }elseif ($action === 'delete') {
            try {
                $id_requisicion = $_POST['id_requisicion'];

                $sqlDelete = "DELETE FROM requisiciones WHERE id_requisicion = :id_requisicion";
                $stmtDelete = $conn->prepare($sqlDelete);
                $stmtDelete->bindParam(':id_requisicion', $id_requisicion);
                $stmtDelete->execute();

                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("success", "Requisición eliminada", "La requisición ha sido eliminada exitosamente.", "self");
                });</script>';

            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("error", "Error", "Ocurrió un error al eliminar la requisición' . addslashes($e->getMessage()) . '", "self");
                });</script>';
                exit;
            }
        }elseif($action=="autorizada"){
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
                $mail->addAddress($DEV_EMAIL);
                
                $mail->isHTML(true);
                $mail->Subject = 'Nueva requisición pendiente. Folio: '.$id_requisicion;
                $mail->Body = "Se ha autorizado el maquinado de sello de una nueva requisición.<br>
                            Se necesita su ingreso al sistema para agregar las barras correspondientes al control de almacen.<br>
                            Folio de requisición: <b>".$id_requisicion."</b>";
                // enviar correo
                if (!$mail->send()) {
                    throw new Exception("No se pudo enviar el correo: " . $mail->ErrorInfo);
                }

                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("success", "Proceso exitoso", "Correo enviado exitosamente a Inventarios para continuar con el siguiente proceso.", "self");
                });</script>';

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
    $default = "";
    if($_SESSION['filtros_requisiciones']){
        $preferencias = $_SESSION['filtros_requisiciones'];
    }else{
        $preferencias = [
            'estatus' => '',
            'fecha_inicio' => '',
            'fecha_fin' => '',
            'default' => 2, // 1: las de hoy, 2:las de la semana, 3: las del mes
            'orden' => 'des'
        ];
    }
    try {

        // --------- LECTURA DE GET ----------
        $estatus = isset($_GET['estatus']) && $_GET['estatus'] !== '' ? trim($_GET['estatus']) : $preferencias["estatus"];
        $fecha_inicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? trim($_GET['fecha_inicio']) : null;
        $fecha_fin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? trim($_GET['fecha_fin']) : null;
        $default = isset($_GET['default']) ? $_GET['default'] : $preferencias["default"]; 
        $orden = isset($_GET['orden']) && $_GET['orden'] === 'asc' ? 'ASC' : 'DESC';
        $params = [];
        $conditions = [];

        // --------- BASE QUERY POR TIPO DE USUARIO ----------
        if ($tipo_usuario == "Administrador") {
            $sqlRequisiciones = "SELECT * FROM requisiciones WHERE 1=1 ";
        } else if ($tipo_usuario == "Vendedor" && $rol_usuario == "Gerente") {
            $sqlRequisiciones = "SELECT * FROM requisiciones WHERE sucursal = :area";
            $params[':area'] = $areaUser;
        } else {
            $sqlRequisiciones = "SELECT * FROM requisiciones WHERE id_vendedor = :id";
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
                    $sqlRequisiciones .= " AND estatus = 'Producción' OR estatus = 'En producción'";
                    break;
                case 'finalizada':
                    $sqlRequisiciones .= " AND estatus = 'Finalizada'";
                    break;
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
        // Fallback robusto en caso de error
        try {
            $default = 2;
            if ($tipo_usuario == "Administrador") {
                $sqlFallback = "SELECT * FROM requisiciones ORDER BY id_requisicion DESC";
                $stmtRequisiciones = $conn->prepare($sqlFallback);
            } else if ($tipo_usuario == "Vendedor" && $rol_usuario == "Gerente") {
                $sqlFallback = "SELECT * FROM requisiciones WHERE sucursal = :area ORDER BY id_requisicion DESC";
                $stmtRequisiciones = $conn->prepare($sqlFallback);
                $stmtRequisiciones->bindParam(':area', $areaUser);
            } else {
                $sqlFallback = "SELECT * FROM requisiciones WHERE id_vendedor = :id ORDER BY id_requisicion DESC";
                $stmtRequisiciones = $conn->prepare($sqlFallback);
                $stmtRequisiciones->bindParam(':id', $_SESSION['id']);
            }
            
            $stmtRequisiciones->execute();
            $arregloSelectRequisiciones = $stmtRequisiciones->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Error en filtros de requisiciones: " . $e->getMessage());

        } catch (Throwable $e2) {
            // Si también falla el fallback
            $arregloSelectRequisiciones = [];
            error_log("Error crítico en filtros de requisiciones: " . $e2->getMessage());
        }
        // Sobreescribir con valores actuales de GET si existen
        if (isset($_GET['estatus'])) $preferencias['estatus'] = $_GET['estatus'];
        if (isset($_GET['fecha_inicio'])) $preferencias['fecha_inicio'] = $_GET['fecha_inicio'];
        if (isset($_GET['fecha_fin'])) $preferencias['fecha_fin'] = $_GET['fecha_fin'];
        if (isset($_GET['default'])) $preferencias['default'] = $_GET['default'];
        if (isset($_GET['orden'])) $preferencias['orden'] = $_GET['orden'];
    }

?>