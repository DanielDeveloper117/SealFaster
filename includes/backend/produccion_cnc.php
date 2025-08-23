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
    if ($tipo_usuario == "CNC" || $tipo_usuario == "Administrador") {
        $sqlRequisiciones = "SELECT * FROM requisiciones WHERE estatus != 'Pendiente' ORDER BY fecha_insercion DESC";
    } else{
        $sqlRequisiciones = "SELECT * FROM requisiciones WHERE estatus = 'Autorizada' ORDER BY fecha_insercion DESC";
    }
    $stmtRequisiciones = $conn->prepare($sqlRequisiciones);
 
    $stmtRequisiciones->execute();
    $arregloSelectRequisiciones = $stmtRequisiciones->fetchAll(PDO::FETCH_ASSOC);

    // function btnManager($estatus){
    //     switch ($estatus){
    //         case 'Producción':
    //         break;
    //         case 'En producción':
                
    //         break;
    //         case 'Finalizada':
    //         break;
    //         default:
    //         break;
    //     }

    // }
?>