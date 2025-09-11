<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}

    $cantidadMateriales = 1;
    $ultimoCaracter = "";
    $selloOriginal = "";
    try {
        $sello = $_GET['sello'];
        $ultimoCaracter = substr($sello, -1);
        $selloOriginal = substr($sello, 0, -2);
        $stmtPerfilSello = "SELECT * FROM perfiles WHERE perfil = :perfil";
        $stmtPerfilSello = $conn->prepare($stmtPerfilSello);
        $stmtPerfilSello->bindParam(':perfil', $selloOriginal, PDO::PARAM_STR);
        $stmtPerfilSello->execute();
        $arregloPerfilSello = $stmtPerfilSello->fetch(PDO::FETCH_ASSOC);

        $cantidadMateriales = $arregloPerfilSello["cantidad_materiales"];

        if (!isset($arregloPerfilSello["perfil"]) || $arregloPerfilSello["perfil"] != $sello) {
            $arregloPerfilSello["perfil"] = "No existe ese perfil.";
        }
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }

    if (isset($_GET['tipo']) && isset($_GET['sello'])) {
        $tipo = $_GET['tipo'];
        $sello = $_GET['sello'];
        $baseDir = '../assets/img/';
        $imageDir = '';

        if (in_array($sello, ['z1', 'z2', 'z3', 'z4', 'z5', 'z6', 'z7'])) {
            header('Location: selectTipoSello.php');
            exit();
        }

        switch ($tipo) {
            case 'rotary':
                $imageDir = $baseDir . 'rotary/';
                $tipoEcho = "Rotativo";
                $tipoButton = $tipo;
                $imageDirFamilyDI = $baseDir . 'family/rotary/di_rotary.jpg';
                $imageDirFamilyDE = $baseDir . 'family/rotary/de_rotary.jpg';
                $imageDirFamilyH = $baseDir . 'family/rotary/h_rotary.jpg';
                break;
            case 'piston':
                $imageDir = $baseDir . 'piston/';
                $tipoEcho = "Pistón";
                $tipoButton = $tipo;
                $imageDirFamilyDI = $baseDir . 'family/piston/di_piston.jpg';
                $imageDirFamilyDE = $baseDir . 'family/piston/de_piston.jpg';
                $imageDirFamilyH = $baseDir . 'family/piston/h_piston.jpg';
                break;

            case 'backup':
                $imageDir = $baseDir . 'backup/';
                $tipoEcho = "Respaldo";
                $tipoButton = $tipo;
                $imageDirFamilyDI = $baseDir . 'family/backup/di_backup.jpg';
                $imageDirFamilyDE = $baseDir . 'family/backup/de_backup.jpg';
                $imageDirFamilyH = $baseDir . 'family/backup/h_backup.jpg';
            break;
            case 'guide':
                $imageDir = $baseDir . 'guide/';
                $tipoEcho = "Guía";
                $tipoButton = $tipo;
                $imageDirFamilyDI = $baseDir . 'family/guide/di_guide.jpg';
                $imageDirFamilyDE = $baseDir . 'family/guide/de_guide.jpg';
                $imageDirFamilyH = $baseDir . 'family/guide/h_guide.jpg';
            break;
            case 'wipers':
                $imageDir = $baseDir . 'wipers/';
                $tipoEcho = "Limpiadores";
                $tipoButton = $tipo;
                $imageDirFamilyDI = $baseDir . 'family/wiper/di_wiper.jpg';
                $imageDirFamilyDE = $baseDir . 'family/wiper/de_wiper.jpg';
                $imageDirFamilyH = $baseDir . 'family/wiper/h_wiper.jpg';
            break;
            case 'rod':
                $imageDir = $baseDir . 'rod/';
                $tipoEcho = "Vástago";
                $tipoButton = $tipo;
                $imageDirFamilyDI = $baseDir . 'family/rod/di_rod.jpg';
                $imageDirFamilyDE = $baseDir . 'family/rod/de_rod.jpg';
                $imageDirFamilyH = $baseDir . 'family/rod/h_rod.jpg';
                break;            
            default:
                header('Location: selectTipoSello.php');
                exit();
        }

        $imagePath = $imageDir . $sello . '.jpg';
        // extraer el ultimo caracter del nombre de la imagen
        echo '<script>window.perfilSello = "'.$selloOriginal.'";</script>';

    } else {
        header('Location: selectTipoSello.php');
        exit();
    }

?> 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-estimador.css'); ?>">
    <script src="<?= controlCache('../assets/js/estimador.js'); ?>"></script>
    <!-- <script src="<?= controlCache('../assets/js/scripts_ajax.js'); ?>"></script> -->
    <title><?= $selloOriginal ?></title>
</head>
<body>
    
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<section id="containerSections" class="d-flex flex-column">
    <section id="sectionSelectorCM" class="section-container d-none">
        <div class="mb-3">
            <h4 for="selectorCantidadMateriales" class="form-label">Cantidad de materiales</h4>
            <select id="selectorCantidadMateriales" class="form-select" name="cantidad_materiales" style="font-size: 18px;">
                <option value="" disabled selected>Seleccione una cantidad</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div> 
    </section>
    <div class="d-flex flex-column flex-md-row col-12 mb-3 gap-3">

        <?php if ($tipoUsuario == 5): ?>
            <input id="isFive" type="hidden" value="1">
            <select id="selectorCliente" class="d-none">
                <option selected value="CLIENTE PUBLICO GENERAL" data-clasificacion="PUBLICO" data-descuento="0.00" data-codigo="09-0003" data-correo="sistemas3@sellosyretenes.com">
                    CLIENTE PUBLICO GENERAL - PUBLICO
                </option>
            </select>
        <?php else: ?>
            <input id="isFive" type="hidden" value="0">
            <section id="sectionSelectorCliente" class="section-container">
                <div class="mb-3 d-flex flex-column  col-12 col-md-12">
                    <h4>Cliente</h4>
                    <select id="selectorCliente">
                        <option value="" disabled selected>Seleccione un cliente</option>
                    </select>
                </div> 
            </section>
        <?php endif; ?>
        <div id="sectionDureza" class="section-container">
            <div class="d-flex col-12">
                <div class="col-11 col-md-11 flex-column">

                    <h5 class="mb-3">Dureza de materiales</h5>
                    <select id="selectorDurezaMateriales" class="form-select" name="material" required>
                        <option value="" disabled selected>Seleccione una opcion</option>
                        <option value="blandos">Materiales blandos</option>
                        <option value="duros">Materiales duros</option>
                        <option id="todosMaterialesOption" value="todos">Todos los materiales</option>
                    </select>
                </div>
                <div class="align-self-end">
                    <i id="btnQuestionMaterials" class="bi bi-question-circle-fill" data-bs-toggle="modal" data-bs-target="#modalQuestionMaterials" style="padding-left:5px;font-size:20px;"></i>
                </div>
            </div>
        </div>
    </div>
    <?php
    $archivoEventos = "eventos_materiales.js";
    $archivoPrevisualizacion = "tabla_previsualizacion.php";
    include('../includes/dimensiones_resultantes.php');
    if ($tipoUsuario == 2 || $tipoUsuario == 3 || $tipoUsuario == 4 || $tipoUsuario == 5) {
        include('../includes/materiales2.php');
        $archivoEventos = "eventos_materiales2.js";
        $archivoPrevisualizacion = "tabla_previsualizacion2.php";
    }else{
        include('../includes/materiales.php');
        $archivoEventos = "eventos_materiales.js";
        $archivoPrevisualizacion = "tabla_previsualizacion.php";
    }
    ?>
    <section id="sectionCotizar" class="section-container">
        <div class="col-12 col-md-3">
            <button type="button" id="btnCotizar" class="btn-disabled mt-1 mb-0">Cotizar</button>
        </div> 
    </section>
    <section id="sectionTotalFinal" class="section-container d-none">
        <div class="mb-3">
            <div class="d-flex col-12 justify-content-start align-items-baseline">
                <h4 class="h4-iva">IVA (16%): $</h4>
                <input type="number" id="inputIVA1" class="input-span-iva" placeholder="Pendiente">
            </div>  
           <div class="d-flex align-items-center justify-content-start">
                <h2 class="h2-total-final">Total final: $</h2>
                <input type="number" id="inputTotalCotizacion" class="input-span-total-final" value="" name="total_cotizacion" step="0.01" min="0" placeholder="Pendiente">
      
            </div>  
            <button type="button" id="btnPrevisualizar" class="btn-general d-none mt-4">Previsualizar</button>

        </div> 
    </section>

    
    <section id="sectionPrevisualizar" class="section-container d-none flex-column justify-content-center align-items-center">
        <div class="mb-3 d-flex col-12 justify-content-between">
            <div>
                <h1>Previsualizar cotización</h1>
                <div class="d-flex judtify-content-center align-items-baseline">
                    <h3>Id: </h3>
                    <form id="formGenerarPdf" action="fpdf/generar_pdf2.php" method="GET" target="_blank">
                        <input type="number" id="inputIdCotizacion" class="input-span2" placeholder="00000000" name="id_cotizacion" style="font-size:28px;" required readonly tabindex="-1">
                    </form>
                </div>
            </div>
            <div>
                <div class="mb-3 d-flex col-12 justify-content-between">
                    <button type="button" id="btnContinuarEditando" class="btn-general mt-4">Continuar editando<i class='bi bi-pencil' style='color:#fff; margin-left: 5px;'></i></button>
                </div>
            </div>
        </div> 
        <div class="mb-3 d-flex col-12 justify-content-start <?= ($tipoUsuario == 5) ? 'd-none' : '' ?>">
            <div class="d-flex col-4 flex-column mt-3">
                <?php include(ROOT_PATH . 'includes/backend_info_user.php'); ?>
                <label for="inputVendedor" class="form-label">Nombre del vendedor</label>
                <input id="inputVendedor" type="text" class="input-readonly" value="<?= $nombreUser ?>" required readonly tabindex="-1">
            </div>
        </div>
        <div class="mb-3 d-flex col-12 flex-column justify-content-start">
            <div class="<?= ($tipoUsuario == 5) ? 'd-none' : '' ?>">
                <span style="font-weight:600;">Cliente: </span><span id="spanCliente"></span>
            </div>
            <!-- <div>
                <span style="font-weight:600;">Tipo de medida: </span><span id="spanTipoMedida"></span>
            </div> -->
            <div>
                <span style="font-weight:600;">Medidas del sello (DI/DE/H): </span><span id="spanDimensiones"></span>
            </div>
            <div>
                <span id="spanDimensiones2"></span>
            </div>
        </div>
        <div id="containerTablePrev" class="mb-3 d-md-flex justify-content-md-center">
           
            <?php include(ROOT_PATH . 'includes/'.$archivoPrevisualizacion.''); ?>
            
        </div> 
        <div class="mb-3 d-flex col-12 flex-column flex-md-row justify-content-between">
            <div class="d-flex flex-column col-md-8">
                <div class="d-flex">
                    <img id="imagenMaterialTabla_m1" class="col-4 img-sello-tabla" src="../assets/img/general/blanco.jpg" alt="">
                    <img id="imagenMaterialTabla_m2" class="col-4 img-sello-tabla" src="../assets/img/general/blanco.jpg" alt="">
                    <img id="imagenMaterialTabla_m3" class="col-4 img-sello-tabla" src="../assets/img/general/blanco.jpg" alt="">
                </div>
                <div class="d-flex">
                    <img id="imagenMaterialTabla_m4" class="col-4 img-sello-tabla" src="../assets/img/general/blanco.jpg" alt="">
                    <img id="imagenMaterialTabla_m5" class="col-4 img-sello-tabla" src="../assets/img/general/blanco.jpg" alt="">
                </div>
            </div>
            <div class="d-flex flex-column col-md-4 ps-3">
                <div class="d-flex col-12 justify-content-start align-items-baseline">
                    <h4 class="h4-iva">IVA (16%): $</h4>
                    <input type="number" id="inputIVA2" class="input-span-iva" placeholder="Pendiente" readonly tabindex="-1">
                </div>   
                <div class="d-flex col-12 justify-content-start align-items-baseline">
                    <h2 class="h2-total-final">Total final: $</h2>
                    <input type="number" id="inputTotalCotizacion2" class="input-span-total-final" placeholder="Pendiente" readonly tabindex="-1">
                </div>  
            </div>
        </div>
        <div class="mb-3 d-flex col-12 justify-content-center">
            <button type="button" id="btnGuardarCotizacion" class="btn-general">Guardar cotización y generar PDF</button>
        </div>
    </section>
</section>
<!-- Modal Question Wiper Especial-->
<div class="modal fade" id="modalQuestionMaterials" tabindex="-1" aria-labelledby="modalQuestionMaterialsLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md"> <!-- puedes usar modal-md o modal-lg -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Información de los materiales</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <div class="d-flex col-12 justify-content-evenly">
            <div class="d-flex flex-column col-5">
                <label for="">Materiales blandos:</label>
                <ul>
                    <li>H-ECOPUR</li>
                    <li>ECOSIL</li>
                    <li>ECORUBBER 1</li>
                    <li>ECORUBBER 2</li>
                    <li>ECORUBBER 3</li>
                    <li>ECOPUR</li>
                </ul>
            </div>
            <div class="d-flex flex-column col-5">
                <label for="">Materiales duros:</label>
                <ul>
                    <li>ECOTAL</li>
                    <li>ECOMID</li>
                    <li>ECOFLON 1</li>
                    <li>ECOFLON 2</li>
                    <li>ECOFLON 3</li>
                </ul>
            </div>

        </div>
      </div>
    </div>
  </div>
</div>
<?php include(ROOT_PATH . 'includes/footer.php'); ?>

<script>
    const cantidadMateriales = <?= (int)$cantidadMateriales ?>;
</script>
<?php?>
<script src="<?= controlCache('../assets/js/'.$archivoEventos.''); ?>"></script>

<script>
$(document).ready(function(){
    // $.ajax({
    //     url: "../ajax/ajax_notificacion.php",
    //     type: "POST",
    //     data: { mensaje: "El usuario ha cargado estimador" },
    //     success: function(response) {
    //         console.log("Notificación enviada: ", response);
    //     },
    //     error: function(error) {
    //         console.error("Error al enviar la notificación: ", error);
    //     }
    // });
});
</script>
</body>
</html>