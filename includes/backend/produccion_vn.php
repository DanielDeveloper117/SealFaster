<?php
    require_once(ROOT_PATH . 'vendor/autoload.php');

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
                $folio = $_POST['folio'];
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

                $sql = "INSERT INTO requisiciones (id_vendedor, estatus, cotizaciones, nombre_vendedor, sucursal, cliente, fechahora, folio, num_pedido, factura, paqueteria, comentario) 
                                        VALUES (:id_vendedor, :estatus, :cotizaciones, :nombre_vendedor, :sucursal, :cliente, :fechahora, :folio , :num_pedido, :factura , :paqueteria , :comentario)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_vendedor', $id_vendedor);
                $stmt->bindParam(':estatus', $estatus);
                $stmt->bindParam(':cotizaciones', $cotizaciones);
                $stmt->bindParam(':nombre_vendedor', $nombre_vendedor);
                $stmt->bindParam(':sucursal', $sucursal);
                $stmt->bindParam(':cliente', $cliente);
                $stmt->bindParam(':fechahora', $fechahora);
                $stmt->bindParam(':folio', $folio);
                $stmt->bindParam(':num_pedido', $num_pedido);
                $stmt->bindParam(':factura', $factura);
                $stmt->bindParam(':paqueteria', $paqueteria);
                $stmt->bindParam(':comentario', $comentario);
                $stmt->execute();

            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("error", "Error", "Error al intentar agregar registro. '. addslashes($e->getMessage()).'", "self");
                });</script>';
                exit;
            }
            ////////////////////////////PHP MAILER -> cotizador a gerente VN ////////////////
            try {
                require_once(ROOT_PATH . 'includes/PHPMailer.php');
                $mail = getMailer($conn);

                $sqlCorreoVentasGerencia = "SELECT usuario FROM login WHERE lider = 3 AND rol = 'Gerente'";
                $stmt = $conn->prepare($sqlCorreoVentasGerencia);
                $stmt->execute();
                $arregloCorreoVentasGerencia = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$arregloCorreoVentasGerencia || empty($arregloCorreoVentasGerencia['usuario'])) {
                    throw new Exception("No se encontró correo de gerencia.");
                }
                $clave_encriptacion = 'SRS2024#tides';
                //$correoVentasGerencia = openssl_decrypt($arregloCorreoVentasGerencia['usuario'], 'AES-128-ECB', $clave_encriptacion);
                $correoVentasGerencia = "desarrollo2.sistemas@sellosyretenes.com";

                $mail->addAddress($correoVentasGerencia);
                $mail->Subject = 'Nueva requisición por autorizar.';
                $mail->Body = "$nombre_vendedor ha generado una requisición para el maquinado de sello. Vaya a la sección de producción para autorizarla con su firma.";

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
                $folio = $_POST['folio'];
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
                            folio = :folio,
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
                $stmt->bindParam(':folio', $folio);
                $stmt->bindParam(':num_pedido', $num_pedido);
                $stmt->bindParam(':factura', $factura);
                $stmt->bindParam(':paqueteria', $paqueteria);
                $stmt->bindParam(':comentario', $comentario);
                $stmt->execute();

                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("success", "Proceso exitoso", "Registro actualizado correctamete.", "self");
                });</script>';

            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("error", "Error", "Error al intentar actualizar registro. '. addslashes($e->getMessage()).'", "self");
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
                    sweetAlertResponse("error", "Error", "Ocurrió un error al eliminar la requisición. ' . addslashes($e->getMessage()) . '", "self");
                });</script>';
                exit;
            }
        }elseif($action=="autorizada"){
            try{
                $id_requisicion = $_POST['id_requisicion'];

                $queryUpdateRequisicion = "UPDATE requisiciones SET estatus = 'Producción' WHERE id_requisicion = :id_requisicion";
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
                $sql = "UPDATE cotizacion_materiales SET estatus_completado = 'Producción', fecha_actualizacion = NOW() WHERE id_cotizacion = :id_cotizacion";
                $stmt = $conn->prepare($sql);

                // Ejecutar para cada id
                foreach ($cotizacion_ids as $id_cotizacion) {
                    try {
                        $stmt->bindValue(':id_cotizacion', $id_cotizacion);
                        $stmt->execute();
                    } catch (Throwable $e) {
                        echo '<script>document.addEventListener("DOMContentLoaded", function () {
                            sweetAlertResponse("error", "Error", "Error al actualizar la cotización con ID: ' . $id_cotizacion . '. Detalles: ' . addslashes($e->getMessage()) . '", "self");
                        });</script>';
                        exit;
                    }
                }


            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("error", "Error", "Error al intentar actualizar estatus de cotización. '. addslashes($e->getMessage()).'", "self");
                });</script>';
                exit;
            }
            ////////////////////////////PHP MAILER -> cotizador a CNC ////////////////
            try {
                require_once(ROOT_PATH . 'includes/PHPMailer.php');
                $mail = getMailer($conn);
                $id_requisicion = $_POST['id_requisicion'];

                $sqlCorreoProduccion = "SELECT usuario FROM login WHERE rol = 'CORREO_PRODUCCION'";
                $stmt = $conn->prepare($sqlCorreoProduccion);
                $stmt->execute();
                $arregloCorreoProduccion = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$arregloCorreoProduccion || empty($arregloCorreoProduccion['usuario'])) {
                    throw new Exception("No se encontró correo de gerencia.");
                }
                $clave_encriptacion = 'SRS2024#tides';
                //$correoProduccion = openssl_decrypt($arregloCorreoProduccion['usuario'], 'AES-128-ECB', $clave_encriptacion);
                $correoProduccion = "desarrollo2.sistemas@sellosyretenes.com";
                $mail->addAddress($correoProduccion);
                $mail->isHTML(true);
                $mail->Subject = 'Nueva requisicion para produccion.';
                $mail->Body = "Se ha autorizado el maquinado de sello de una nueva requisicion, la cual ya se encuentra disponible en el modulo de Produccion.<br>Id de requisicion: ".$id_requisicion;
                $mail->send();

                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("success", "Proceso exitoso", "Correo enviado exitosamente a CNC. Estatus de requisición cambiado a Producción.", "self");
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
    if($tipo_usuario == "Administrador" || ($tipo_usuario == "Vendedor" && $rol_usuario=="Gerente")){
        $sqlRequisiciones = "SELECT * FROM requisiciones ORDER BY id_requisicion DESC";
        $stmtRequisiciones = $conn->prepare($sqlRequisiciones);
    }else{
        $sqlRequisiciones = "SELECT * FROM requisiciones WHERE id_vendedor = :id ORDER BY fecha_insercion DESC";
        $stmtRequisiciones = $conn->prepare($sqlRequisiciones);
        $stmtRequisiciones->bindParam(':id', $_SESSION['id']);
    }
    $stmtRequisiciones->execute();
    $arregloSelectRequisiciones = $stmtRequisiciones->fetchAll(PDO::FETCH_ASSOC);
?>