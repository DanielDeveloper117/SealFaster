<?php
function showSweetAlertSuccess($c){
    echo "Swal.fire({
            title: 'Proceso exitoso',
            text: 'Parametros actualizados correctamente.',
            icon: 'success',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#55AD9B',
            showCloseButton: true,
            allowOutsideClick: false, 
            allowEscapeKey: false     
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
                // window.location.href = 'parametros_cotizador.php?c=".$c."';
            }
        });";
}

function showSweetAlertError($c){
    echo "Swal.fire({
            title: 'Ocurrió un problema',
            text: 'Hubo un error al guardar alguno de los parametros.',
            icon: 'error',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#55AD9B',
            showCloseButton: true
        }).then((result) => {
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
                window.location.href = 'parametros_cotizador.php?c=".$c."';
            }
        });";
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formulario = $_POST['formulario'];
    echo "<script>$(document).ready(function() {";
    switch ($formulario) {
        case "coH-ECOPUR":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'coH-ECOPUR'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("coH-ECOPUR");

            }else{
                showSweetAlertError("coH-ECOPUR");
            }
        break;
        case "coECOTAL":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'coECOTAL'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("coECOTAL");

            }else{
                showSweetAlertError("coECOTAL");
            }
        break;
        case "coECOSIL":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'coECOSIL'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("coECOSIL");

            }else{
                showSweetAlertError("coECOSIL");
            }
        break;
        case "coECORUBBER1":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'coECORUBBER1'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("coECORUBBER1");

            }else{
                showSweetAlertError("coECORUBBER1");
            }
        break;
        case "coECORUBBER2":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'coECORUBBER2'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("coECORUBBER2");

            }else{
                showSweetAlertError("coECORUBBER2");
            }
        break;
        case "coECORUBBER3":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'coECORUBBER3'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("coECORUBBER3");

            }else{
                showSweetAlertError("coECORUBBER3");
            }
        break;
        case "coECOPUR":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'coECOPUR'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("coECOPUR");

            }else{
                showSweetAlertError("coECOPUR");
            }
        break;
        case "coECOMID":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'coECOMID'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("coECOMID");

            }else{
                showSweetAlertError("coECOMID");
            }
        break;
        case "coECOFLON1":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'coECOFLON1'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("coECOFLON1");

            }else{
                showSweetAlertError("coECOFLON1");
            }
        break;
        case "coECOFLON2":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'coECOFLON2'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("coECOFLON2");

            }else{
                showSweetAlertError("coECOFLON2");
            }
        break;
        case "coECOFLON3":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'coECOFLON3'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("coECOFLON3");

            }else{
                showSweetAlertError("coECOFLON3");
            }
        break;
        case "muH-ECOPUR":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'muH-ECOPUR'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("muH-ECOPUR");

            }else{
                showSweetAlertError("muH-ECOPUR");
            }
        break;
        case "muECOTAL":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'muECOTAL'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("muECOTAL");

            }else{
                showSweetAlertError("muECOTAL");
            }
        break;
        case "muECOSIL":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'muECOSIL'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("muECOSIL");

            }else{
                showSweetAlertError("muECOSIL");
            }
        break;
        case "muECORUBBER1":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'muECORUBBER 1'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("muECORUBBER1");

            }else{
                showSweetAlertError("muECORUBBER1");
            }
        break;
        case "muECORUBBER2":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'muECORUBBER 2'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("muECORUBBER2");

            }else{
                showSweetAlertError("muECORUBBER2");
            }
        break;
        case "muECORUBBER3":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'muECORUBBER 3'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("muECORUBBER3");

            }else{
                showSweetAlertError("muECORUBBER3");
            }
        break;
        case "muECOPUR":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'muECOPUR'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("muECOPUR");

            }else{
                showSweetAlertError("muECOPUR");
            }
        break;
        case "muECOMID":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'muECOMID'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("muECOMID");

            }else{
                showSweetAlertError("muECOMID");
            }
        break;
        case "muECOFLON1":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'muECOFLON 1'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("muECOFLON1");

            }else{
                showSweetAlertError("muECOFLON1");
            }
        break;
        case "muECOFLON2":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'muECOFLON 2'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("muECOFLON2");

            }else{
                showSweetAlertError("muECOFLON2");
            }
        break;
        case "muECOFLON3":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'muECOFLON 3'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("muECOFLON3");

            }else{
                showSweetAlertError("muECOFLON3");
            }
        break;
        case "ch":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'ch'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("ch");

            }else{
                showSweetAlertError("ch");
            }
        break;
        case "cpdib":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'cpdib'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("cpdib");
            }else{
                showSweetAlertError("cpdib");
            }
        break; 
        case "dc":
            if (isset($_POST['descuento'])) {
                foreach ($_POST['descuento'] as $clasificacion => $descuento) {
                    $sql = "UPDATE clientes SET descuento = :descuento WHERE clasificacion = :clasificacion";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':descuento', $descuento);
                    $stmt->bindParam(':clasificacion', $clasificacion);
                    $stmt->execute();
                }
                showSweetAlertSuccess("dc");
            }else{
                showSweetAlertError("dc");
            }
        break; 
        case "drc":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'rc'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("drc");
            }else{
                showSweetAlertError("drc");
            }
        break;
        case "dm":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'dm'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("dm");
            }else{
                showSweetAlertError("dm");
            }
        break;
        case "cmu":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'cmu'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("cmu");
            }else{
                showSweetAlertError("cmu");
            }
        break;
        case "multiploResorteMetalico":
            if (isset($_POST['valor'])) {
                foreach ($_POST['valor'] as $id => $valor) {
                    $sql = "UPDATE parametros2 SET valor = :valor WHERE id = :id AND caso = 'mrm'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':valor', $valor);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }
                showSweetAlertSuccess("mrm");
            }else{
                showSweetAlertError("mrm");
            }
        break;
        default:
            echo "Swal.fire({
                    title: 'Ocurrió un problema',
                    text: 'El formulario no es valido.',
                    icon: 'error',
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#55AD9B',
                    showCloseButton: true
                }).then((result) => {
                    if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
                        window.location.href = window.location.href;
                    }
                });";
        break;
    }
    echo "});</script>";
}
if(isset($_GET["c"])){
    $caso = $_GET["c"];
    $aQuienTrigger=[];
    switch ($caso) {
        case "coH-ECOPUR":
            $aQuienTrigger = [
                "#btnTabCostosOperacion",
                "#btnTabCostosOperacionHECOPUR"
            ];
        break;
        case "coECOTAL":
            $aQuienTrigger = [
                "#btnTabCostosOperacion",
                "#btnTabCostosOperacionECOTAL"
            ];
        break;
        case "coECOSIL":
            $aQuienTrigger = [
                "#btnTabCostosOperacion",
                "#btnTabCostosOperacionECOSIL"
            ];
        break;
        case "coECORUBBER1":
            $aQuienTrigger = [
                "#btnTabCostosOperacion",
                "#btnTabCostosOperacionECORUBBER1"
            ];
        break;
        case "coECORUBBER2":
            $aQuienTrigger = [
                "#btnTabCostosOperacion",
                "#btnTabCostosOperacionECORUBBER2"
            ];
        break;
        case "coECORUBBER3":
            $aQuienTrigger = [
                "#btnTabCostosOperacion",
                "#btnTabCostosOperacionECORUBBER3"
            ];
        break;
        case "coECOPUR":
            $aQuienTrigger = [
                "#btnTabCostosOperacion",
                "#btnTabCostosOperacionECOPUR"
            ];
        break;
        case "coECOMID":
            $aQuienTrigger = [
                "#btnTabCostosOperacion",
                "#btnTabCostosOperacionECOMID"
            ];
        break;
        case "coECOFLON1":
            $aQuienTrigger = [
                "#btnTabCostosOperacion",
                "#btnTabCostosOperacionECOFLON1"
            ];
        break;
        case "coECOFLON2":
            $aQuienTrigger = [
                "#btnTabCostosOperacion",
                "#btnTabCostosOperacionECOFLON2"
            ];
        break;
        case "coECOFLON3":
            $aQuienTrigger = [
                "#btnTabCostosOperacion",
                "#btnTabCostosOperacionECOFLON3"
            ];
        break;
        case "muH-ECOPUR":
            $aQuienTrigger = [
                "#btnTabMU",
                "#btnTabMultiplosUtilidadHECOPUR"
            ];
        break;
        case "muECOTAL":
            $aQuienTrigger = [
                "#btnTabMU",
                "#btnTabMultiplosUtilidadECOTAL"
            ];
        break;
        case "muECOSIL":
            $aQuienTrigger = [
                "#btnTabMU",
                "#btnTabMultiplosUtilidadECOSIL"
            ];
        break;
        case "muECORUBBER1":
            $aQuienTrigger = [
                "#btnTabMU",
                "#btnTabMultiplosUtilidadECORUBBER1"
            ];
        break;
        case "muECORUBBER2":
            $aQuienTrigger = [
                "#btnTabMU",
                "#btnTabMultiplosUtilidadECORUBBER2"
            ];
        break;
        case "muECORUBBER3":
            $aQuienTrigger = [
                "#btnTabMU",
                "#btnTabMultiplosUtilidadECORUBBER3"
            ];
        break;
        case "muECOPUR":
            $aQuienTrigger = [
                "#btnTabMU",
                "#btnTabMultiplosUtilidadECOPUR"
            ];
        break;
        case "muECOMID":
            $aQuienTrigger = [
                "#btnTabMU",
                "#btnTabMultiplosUtilidadECOMID"
            ];
        break;
        case "muECOFLON1":
            $aQuienTrigger = [
                "#btnTabMU",
                "#btnTabMultiplosUtilidadECOFLON1"
            ];
        break;
        case "muECOFLON2":
            $aQuienTrigger = [
                "#btnTabMU",
                "#btnTabMultiplosUtilidadECOFLON2"
            ];
        break;
        case "muECOFLON3":
            $aQuienTrigger = [
                "#btnTabMU",
                "#btnTabMultiplosUtilidadECOFLON3"
            ];
        break;
        case "ch":
            $aQuienTrigger= ["#btnTabCostoHerramienta"];
        break;
        case "cpdib":
            $aQuienTrigger= ["#btnTabPreparacionBarraDI"];
        break; 
        case "dc":
            $aQuienTrigger= ["#btnTabDescuentoCliente"];
        break; 
        case "drc":
            $aQuienTrigger= ["#btnTabDescuentoRelacionCantidad"];
        break;
        case "dm":
            $aQuienTrigger= ["#btnTabDescuentoMayoreo"];
        break;
        case "cmu":
            $aQuienTrigger= ["#btnTabCostoMinimoUnidad"];
        break;
        case "mrm":
            $aQuienTrigger= ["#btnTabResorteMetalico"];
        break;
        default:
            $aQuienTrigger= ["#btnTabCostosOperacion"];
        break;
    }
    echo '<script>$(document).ready(function() {';
        $counter = 500;
        foreach ($aQuienTrigger as $selector) {
          echo  '
            document.getElementById("title").scrollIntoView({ behavior: "smooth" });
            setTimeout(() => {
                    $("'.$selector.'").trigger("click");
            }, '.$counter.');';
            $counter += 500;
        }
    echo '});</script>';
}
// Obtener los costos de operacion H-ECOPUR
$sqlCostosOperacionHECOPUR = "SELECT * FROM parametros2 WHERE caso = 'coH-ECOPUR' ORDER BY limite_superior ASC";
$stmtCostosOperacionHECOPUR = $conn->prepare($sqlCostosOperacionHECOPUR);
$stmtCostosOperacionHECOPUR->execute();
$arregloCostosOperacionHECOPUR = $stmtCostosOperacionHECOPUR->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de operacion ECOTAL
$sqlCostosOperacionECOTAL = "SELECT * FROM parametros2 WHERE caso = 'coECOTAL' ORDER BY limite_superior ASC";
$stmtCostosOperacionECOTAL = $conn->prepare($sqlCostosOperacionECOTAL);
$stmtCostosOperacionECOTAL->execute();
$arregloCostosOperacionECOTAL = $stmtCostosOperacionECOTAL->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de operacion ECOSIL
$sqlCostosOperacionECOSIL = "SELECT * FROM parametros2 WHERE caso = 'coECOSIL' ORDER BY limite_superior ASC";
$stmtCostosOperacionECOSIL = $conn->prepare($sqlCostosOperacionECOSIL);
$stmtCostosOperacionECOSIL->execute();
$arregloCostosOperacionECOSIL = $stmtCostosOperacionECOSIL->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de operacion ECORUBBER1
$sqlCostosOperacionECORUBBER1 = "SELECT * FROM parametros2 WHERE caso = 'coECORUBBER1' ORDER BY limite_superior ASC";
$stmtCostosOperacionECORUBBER1 = $conn->prepare($sqlCostosOperacionECORUBBER1);
$stmtCostosOperacionECORUBBER1->execute();
$arregloCostosOperacionECORUBBER1 = $stmtCostosOperacionECORUBBER1->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de operacion ECORUBBER2
$sqlCostosOperacionECORUBBER2 = "SELECT * FROM parametros2 WHERE caso = 'coECORUBBER2' ORDER BY limite_superior ASC";
$stmtCostosOperacionECORUBBER2 = $conn->prepare($sqlCostosOperacionECORUBBER2);
$stmtCostosOperacionECORUBBER2->execute();
$arregloCostosOperacionECORUBBER2 = $stmtCostosOperacionECORUBBER2->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de operacion ECORUBBER3
$sqlCostosOperacionECORUBBER3 = "SELECT * FROM parametros2 WHERE caso = 'coECORUBBER3' ORDER BY limite_superior ASC";
$stmtCostosOperacionECORUBBER3 = $conn->prepare($sqlCostosOperacionECORUBBER3);
$stmtCostosOperacionECORUBBER3->execute();
$arregloCostosOperacionECORUBBER3 = $stmtCostosOperacionECORUBBER3->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de operacion ECOPUR
$sqlCostosOperacionECOPUR = "SELECT * FROM parametros2 WHERE caso = 'coECOPUR' ORDER BY limite_superior ASC";
$stmtCostosOperacionECOPUR = $conn->prepare($sqlCostosOperacionECOPUR);
$stmtCostosOperacionECOPUR->execute();
$arregloCostosOperacionECOPUR = $stmtCostosOperacionECOPUR->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de operacion ECOMID
$sqlCostosOperacionECOMID = "SELECT * FROM parametros2 WHERE caso = 'coECOMID' ORDER BY limite_superior ASC";
$stmtCostosOperacionECOMID = $conn->prepare($sqlCostosOperacionECOMID);
$stmtCostosOperacionECOMID->execute();
$arregloCostosOperacionECOMID = $stmtCostosOperacionECOMID->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de operacion ECOFLON1
$sqlCostosOperacionECOFLON1 = "SELECT * FROM parametros2 WHERE caso = 'coECOFLON1' ORDER BY limite_superior ASC";
$stmtCostosOperacionECOFLON1 = $conn->prepare($sqlCostosOperacionECOFLON1);
$stmtCostosOperacionECOFLON1->execute();
$arregloCostosOperacionECOFLON1 = $stmtCostosOperacionECOFLON1->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de operacion ECOFLON2
$sqlCostosOperacionECOFLON2 = "SELECT * FROM parametros2 WHERE caso = 'coECOFLON2' ORDER BY limite_superior ASC";
$stmtCostosOperacionECOFLON2 = $conn->prepare($sqlCostosOperacionECOFLON2);
$stmtCostosOperacionECOFLON2->execute();
$arregloCostosOperacionECOFLON2 = $stmtCostosOperacionECOFLON2->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de operacion ECOFLON3
$sqlCostosOperacionECOFLON3 = "SELECT * FROM parametros2 WHERE caso = 'coECOFLON3' ORDER BY limite_superior ASC";
$stmtCostosOperacionECOFLON3 = $conn->prepare($sqlCostosOperacionECOFLON3);
$stmtCostosOperacionECOFLON3->execute();
$arregloCostosOperacionECOFLON3 = $stmtCostosOperacionECOFLON3->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de utilidad H-ECOPUR
$sqlMultiplosUtilidadHECOPUR = "SELECT * FROM parametros2 WHERE caso = 'muH-ECOPUR' ORDER BY limite_superior ASC";
$stmtMultiplosUtilidadHECOPUR = $conn->prepare($sqlMultiplosUtilidadHECOPUR);
$stmtMultiplosUtilidadHECOPUR->execute();
$arregloMultiplosUtilidadHECOPUR = $stmtMultiplosUtilidadHECOPUR->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de utilidad ECOTAL
$sqlMultiplosUtilidadECOTAL = "SELECT * FROM parametros2 WHERE caso = 'muECOTAL' ORDER BY limite_superior ASC";
$stmtMultiplosUtilidadECOTAL = $conn->prepare($sqlMultiplosUtilidadECOTAL);
$stmtMultiplosUtilidadECOTAL->execute();
$arregloMultiplosUtilidadECOTAL = $stmtMultiplosUtilidadECOTAL->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de utilidad ECOSIL
$sqlMultiplosUtilidadECOSIL = "SELECT * FROM parametros2 WHERE caso = 'muECOSIL' ORDER BY limite_superior ASC";
$stmtMultiplosUtilidadECOSIL = $conn->prepare($sqlMultiplosUtilidadECOSIL);
$stmtMultiplosUtilidadECOSIL->execute();
$arregloMultiplosUtilidadECOSIL = $stmtMultiplosUtilidadECOSIL->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de utilidad ECORUBBER1
$sqlMultiplosUtilidadECORUBBER1 = "SELECT * FROM parametros2 WHERE caso = 'muECORUBBER 1' ORDER BY limite_superior ASC";
$stmtMultiplosUtilidadECORUBBER1 = $conn->prepare($sqlMultiplosUtilidadECORUBBER1);
$stmtMultiplosUtilidadECORUBBER1->execute();
$arregloMultiplosUtilidadECORUBBER1 = $stmtMultiplosUtilidadECORUBBER1->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de utilidad ECORUBBER2
$sqlMultiplosUtilidadECORUBBER2 = "SELECT * FROM parametros2 WHERE caso = 'muECORUBBER 2' ORDER BY limite_superior ASC";
$stmtMultiplosUtilidadECORUBBER2 = $conn->prepare($sqlMultiplosUtilidadECORUBBER2);
$stmtMultiplosUtilidadECORUBBER2->execute();
$arregloMultiplosUtilidadECORUBBER2 = $stmtMultiplosUtilidadECORUBBER2->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de utilidad ECORUBBER3
$sqlMultiplosUtilidadECORUBBER3 = "SELECT * FROM parametros2 WHERE caso = 'muECORUBBER 3' ORDER BY limite_superior ASC";
$stmtMultiplosUtilidadECORUBBER3 = $conn->prepare($sqlMultiplosUtilidadECORUBBER3);
$stmtMultiplosUtilidadECORUBBER3->execute();
$arregloMultiplosUtilidadECORUBBER3 = $stmtMultiplosUtilidadECORUBBER3->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de utilidad ECOPUR
$sqlMultiplosUtilidadECOPUR = "SELECT * FROM parametros2 WHERE caso = 'muECOPUR' ORDER BY limite_superior ASC";
$stmtMultiplosUtilidadECOPUR = $conn->prepare($sqlMultiplosUtilidadECOPUR);
$stmtMultiplosUtilidadECOPUR->execute();
$arregloMultiplosUtilidadECOPUR = $stmtMultiplosUtilidadECOPUR->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de utilidad ECOMID
$sqlMultiplosUtilidadECOMID = "SELECT * FROM parametros2 WHERE caso = 'muECOMID' ORDER BY limite_superior ASC";
$stmtMultiplosUtilidadECOMID = $conn->prepare($sqlMultiplosUtilidadECOMID);
$stmtMultiplosUtilidadECOMID->execute();
$arregloMultiplosUtilidadECOMID = $stmtMultiplosUtilidadECOMID->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de utilidad ECOFLON1
$sqlMultiplosUtilidadECOFLON1 = "SELECT * FROM parametros2 WHERE caso = 'muECOFLON 1' ORDER BY limite_superior ASC";
$stmtMultiplosUtilidadECOFLON1 = $conn->prepare($sqlMultiplosUtilidadECOFLON1);
$stmtMultiplosUtilidadECOFLON1->execute();
$arregloMultiplosUtilidadECOFLON1 = $stmtMultiplosUtilidadECOFLON1->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de utilidad ECOFLON2
$sqlMultiplosUtilidadECOFLON2 = "SELECT * FROM parametros2 WHERE caso = 'muECOFLON 2' ORDER BY limite_superior ASC";
$stmtMultiplosUtilidadECOFLON2 = $conn->prepare($sqlMultiplosUtilidadECOFLON2);
$stmtMultiplosUtilidadECOFLON2->execute();
$arregloMultiplosUtilidadECOFLON2 = $stmtMultiplosUtilidadECOFLON2->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de utilidad ECOFLON3
$sqlMultiplosUtilidadECOFLON3 = "SELECT * FROM parametros2 WHERE caso = 'muECOFLON 3' ORDER BY limite_superior ASC";
$stmtMultiplosUtilidadECOFLON3 = $conn->prepare($sqlMultiplosUtilidadECOFLON3);
$stmtMultiplosUtilidadECOFLON3->execute();
$arregloMultiplosUtilidadECOFLON3 = $stmtMultiplosUtilidadECOFLON3->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de herramienta
$sqlCostosHerramienta = "SELECT * FROM parametros2 WHERE caso = 'ch' ORDER BY limite_superior ASC";
$stmtCostosHerramienta = $conn->prepare($sqlCostosHerramienta);
$stmtCostosHerramienta->execute();
$arregloCostosHerramienta = $stmtCostosHerramienta->fetchAll(PDO::FETCH_ASSOC);
// Obtener los costos de preparacion de DI barra
$sqlCostoPreparacionBarraDI = "SELECT * FROM parametros2 WHERE caso = 'cpdib' ORDER BY limite_superior ASC";
$stmtCostoPreparacionBarraDI = $conn->prepare($sqlCostoPreparacionBarraDI);
$stmtCostoPreparacionBarraDI->execute();
$arregloPreparacionBarraDI = $stmtCostoPreparacionBarraDI->fetchAll(PDO::FETCH_ASSOC);
// Obtener costo minimo de unidad
$sqlCostoMinimoUnidad = "SELECT * FROM parametros2 WHERE caso = 'cmu' ORDER BY limite_superior ASC";
$stmtCostoMinimoUnidad = $conn->prepare($sqlCostoMinimoUnidad);
$stmtCostoMinimoUnidad->execute();
$arregloCostoMinimoUnidad = $stmtCostoMinimoUnidad->fetchAll(PDO::FETCH_ASSOC);
// Obtener los descuentos de cliente
$sqlDescuentosCliente = "SELECT DISTINCT clasificacion, descuento FROM clientes";
$stmtDescuentosCliente = $conn->prepare($sqlDescuentosCliente);
$stmtDescuentosCliente->execute();
$arregloDescuentosCliente = $stmtDescuentosCliente->fetchAll(PDO::FETCH_ASSOC);
// Obtener los descuentos por cantidad
$sqlDescuentosRelacionCantidad = "SELECT * FROM parametros2 WHERE caso = 'rc' ORDER BY limite_superior ASC";
$stmtDescuentosRelacionCantidad = $conn->prepare($sqlDescuentosRelacionCantidad);
$stmtDescuentosRelacionCantidad->execute();
$arregloDescuentosRelacionCantidad = $stmtDescuentosRelacionCantidad->fetchAll(PDO::FETCH_ASSOC);
// Obtener los descuentos por mayoreo
$sqlDescuentosMayoreo = "SELECT * FROM parametros2 WHERE caso = 'dm' ORDER BY valor ASC";
$stmtDescuentosMayoreo = $conn->prepare($sqlDescuentosMayoreo);
$stmtDescuentosMayoreo->execute();
$arregloDescuentosMayoreo = $stmtDescuentosMayoreo->fetchAll(PDO::FETCH_ASSOC);
// Obtener los multiplos de costo de resorte metalico
$sqlResorteMetalico = "SELECT * FROM parametros2 WHERE caso = 'mrm'";
$stmtResorteMetalico = $conn->prepare($sqlResorteMetalico);
$stmtResorteMetalico->execute();
$arregloResorteMetalico = $stmtResorteMetalico->fetchAll(PDO::FETCH_ASSOC);
?>