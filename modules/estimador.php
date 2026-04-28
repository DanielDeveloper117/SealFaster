<?php
    require_once(__DIR__ . '/../config/rutes.php');
    require_once(ROOT_PATH . 'auth/session_manager.php');
    require_once(ROOT_PATH . 'includes/functions/control_cache.php');
    require_once(ROOT_PATH . 'config/config.php');

    $cantidadMateriales = 1;
    $ultimoCaracter = "";
    $perfilOriginal = "";
    try {
        $perfilOriginal = $_GET['perfil'];
        $ultimoCaracter = substr($perfilOriginal, -1);
        $perfilImg = substr($perfilOriginal, 0, -2);
        $stmtPerfilSello = "SELECT * FROM perfiles WHERE perfil = :perfil";
        $stmtPerfilSello = $conn->prepare($stmtPerfilSello);
        $stmtPerfilSello->bindParam(':perfil', $perfilOriginal, PDO::PARAM_STR);
        $stmtPerfilSello->execute();
        $arregloPerfilSello = $stmtPerfilSello->fetch(PDO::FETCH_ASSOC);

        $cantidadMateriales = $arregloPerfilSello["cantidad_materiales"];

        if (!isset($arregloPerfilSello["perfil"]) || $arregloPerfilSello["perfil"] != $perfilOriginal) {
            $arregloPerfilSello["perfil"] = "No existe ese perfil.";
        }
    } catch (PDOException $e) {
        echo 'Error: ' . $e->getMessage();
    }

    if (isset($_GET['fam']) && isset($_GET['perfil'])) {
        $familia = $_GET['fam'];
        $perfilOriginal = $_GET['perfil'];
        $baseDir = '../assets/img/';
        $imageDir = '';

        if (in_array($perfilOriginal, ['z1', 'z2', 'z3', 'z4', 'z5', 'z6', 'z7'])) {
            header('Location: select_familia.php');
            exit();
        }

        switch ($familia) {
            case 'rotary':
                $imageDir = $baseDir . 'rotary/';
                $familiaEcho = "Rotativo";
                $familiaButton = $familia;
                $imageDirFamilyDI = $baseDir . 'family/rotary/di_rotary.jpg';
                $imageDirFamilyDE = $baseDir . 'family/rotary/de_rotary.jpg';
                $imageDirFamilyH = $baseDir . 'family/rotary/h_rotary.jpg';
                break;
            case 'piston':
                $imageDir = $baseDir . 'piston/';
                $familiaEcho = "Pistón";
                $familiaButton = $familia;
                $imageDirFamilyDI = $baseDir . 'family/piston/di_piston.jpg';
                $imageDirFamilyDE = $baseDir . 'family/piston/de_piston.jpg';
                $imageDirFamilyH = $baseDir . 'family/piston/h_piston.jpg';
                break;

            case 'backup':
                $imageDir = $baseDir . 'backup/';
                $familiaEcho = "Respaldo";
                $familiaButton = $familia;
                $imageDirFamilyDI = $baseDir . 'family/backup/di_backup.jpg';
                $imageDirFamilyDE = $baseDir . 'family/backup/de_backup.jpg';
                $imageDirFamilyH = $baseDir . 'family/backup/h_backup.jpg';
            break;
            case 'guide':
                $imageDir = $baseDir . 'guide/';
                $familiaEcho = "Guía";
                $familiaButton = $familia;
                $imageDirFamilyDI = $baseDir . 'family/guide/di_guide.jpg';
                $imageDirFamilyDE = $baseDir . 'family/guide/de_guide.jpg';
                $imageDirFamilyH = $baseDir . 'family/guide/h_guide.jpg';
            break;
            case 'wipers':
                $imageDir = $baseDir . 'wiper/';
                $familiaEcho = "Limpiadores";
                $familiaButton = $familia;
                $imageDirFamilyDI = $baseDir . 'family/wiper/di_wiper.jpg';
                $imageDirFamilyDE = $baseDir . 'family/wiper/de_wiper.jpg';
                $imageDirFamilyH = $baseDir . 'family/wiper/h_wiper.jpg';
            break;
            case 'rod':
                $imageDir = $baseDir . 'rod/';
                $familiaEcho = "Vástago";
                $familiaButton = $familia;
                $imageDirFamilyDI = $baseDir . 'family/rod/di_rod.jpg';
                $imageDirFamilyDE = $baseDir . 'family/rod/de_rod.jpg';
                $imageDirFamilyH = $baseDir . 'family/rod/h_rod.jpg';
                break;            
            default:
                header('Location: select_familia.php');
                exit();
        }

        $imagePath = $imageDir.$perfilOriginal;
       
        // extraer el ultimo caracter del nombre de la imagen
        echo '<script>window.PERFIL_SELLO = "'.$perfilOriginal.'";</script>';

    } else {
        header('Location: select_familia.php');
        exit();
    }

?> 
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=yes">
    <link rel="icon" type="image/svg+xml" href="../assets/img/general/favicon.ico?v=2" />
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/bootstrap-icons.min.css'); ?>">
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/datatables.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/datatables.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/dependencies/html2canvas.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/chosen.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/chosen.jquery.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/dependencies/d3.v7.min.js'); ?>"></script>

    <link href="<?= controlCache('../assets/dependencies/select2.min.css'); ?>" rel="stylesheet" />
    <script src="<?= controlCache('../assets/dependencies/select2.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>

    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-estimador2.css'); ?>">
    <link rel="stylesheet" href="<?= controlCache('../assets/css/select2-selector.css'); ?>">

    <title><?= $perfilOriginal ?></title>
    
</head>
<body>
    
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>
<div id="overlay">
    <div class="loading-message">
        <span>Cargando datos del perfil, por favor, espere...</span>    
    </div>
</div>
<section id="containerSections" class="d-flex flex-column">

    <div class="controles d-flex flex-column justify-content-evenly flex-md-row col-12 mb-3 gap-3">
        <div class="d-flex w-100 flex-column justify-content-evenly align-items-center">
            <h4><?= $perfilOriginal ?> (<?= $familiaEcho ?>), Componentes: <?=  $cantidadMateriales ?></h4>
            <?php
                if (file_exists($imagePath)) {
                    $imgMaterial=$imagePath . '/' . $perfilOriginal . '_0.jpg';
                    echo '<a class="align-self-center align-self-md-center img-sello" href="select_perfil.php?fam=' . $familia . '" title="Click para cambiar el perfil">';
                    echo '<img  src="'. $imgMaterial . '" alt="' . htmlspecialchars($perfilOriginal) . '" class="border-gray img-fluid">';
                    echo '</a>';
                } else {
                    echo "<h2>Imagen no encontrada</h2>";
                }
            ?>
            <div class="text-center w-100 mt-3 py-2 px-3 px-md-5 border border-dark-subtitle bg-white bg-opacity-10 font-monospace rounded-2">
                <span id="descripcionPerfil"></span>
            </div>
        </div>
        <?php if ($tipoUsuario == 5): ?>
            <input id="isFive" type="hidden" value="1">
            <div class="selector-estimador-container">
                <select id="selectorCliente" class="d-none selector-estimador">
                    <option selected value="CLIENTE PUBLICO GENERAL" data-clasificacion="PUBLICO" data-descuento="0.00" data-codigo="09-0003" data-correo="sistemas3@sellosyretenes.com">
                        CLIENTE PUBLICO GENERAL - PUBLICO
                    </option>
                </select>
            </div>
        <?php else: ?>
            <input id="isFive" type="hidden" value="0">
            <section id="sectionSelectorCliente" class="section-container d-flex flex-column col-12 col-md-6">
                <div class="mb-3 d-flex flex-column  col-12 col-md-12">
                    <h5>Cliente *</h5>
                    <select id="selectorCliente" class="" style="z-index:999;">
                        <option value="" disabled selected>Seleccione un cliente</option>
                    </select>
                </div> 
                <div class="d-flex col-12 col-md-12 flex-column mb-3">
                    <h5 class="mb-3">Tipo de inventario *</h5>
                    <select id="selectorTipoInventario" class="" name="tipo_inventario" required>
                        <option value="" disabled selected>Seleccione una opción</option>
                        <option value="fisico">Stock físico (Inventario CNC)</option>
                        <option value="simulacion">Material no sujeto a stock (simulación de costos)</option>
                    </select>
                    <span id="spanSimulacion" class="d-none text-truncate fst-italic">La cotización no podrá ser usada para requisiciones</span>
                </div>
                <div id="sectionDureza" class="">
                    <div class="d-flex col-12 flex-column">
                        <div class="d-flex col-12 flex-row mb-3">
                            <div class="d-flex col-12 col-md-12 flex-column">
                                <h5 class="mb-3">Dureza de materiales *</h5>
                                <div class="d-flex align-items-center gap-1">
                                    <select id="selectorDurezaMateriales" class="" name="material" required>
                                        <option value="" disabled selected>Seleccione una opción</option>
                                        <option value="blandos">Limitantes para materiales Blandos</option>
                                        <option value="duros">Limitantes para materiales Duros</option>
                                        <option id="todosMaterialesOption" value="todos">Todos los materiales</option>
                                    </select>
                                    <div class="align-self-end">
                                        <i id="btnQuestionMaterials" class="bi bi-question-circle-fill" data-bs-toggle="modal" data-bs-target="#modalQuestionMaterials" style="padding-left:5px;font-size:30px;"></i>
                                    </div>                                
                                </div>
                            </div>

                        </div>
                        <div class="d-flex col-11 col-md-12 flex-column gap-2">
                            <span class="text-truncate fst-italic">Materiales Blandos: H-ECOPUR, ECOSIL, ECORUBBER 1/2/3, ECOPUR</span>
                            <span class="text-truncate fst-italic">Materiales Duros: ECOTAL, ECOMID, ECOFLON 1/2/3</span>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
        
    </div>



    <?php
    $archivoEventos = "eventos_materiales.js";
    $archivoPrevisualizacion = "tabla_previsualizacion.php";
    include('../includes/dimensiones_resultantes.php');
    if ($tipoUsuario == "Vendedor" || $tipoUsuario == "Cliente Externo") {
        include('../includes/materiales2.php');
        $archivoEventos = "eventos_materiales2.js";
        $archivoPrevisualizacion = "tabla_previsualizacion2.php";
    }else{
        include('../includes/materiales.php');
        $archivoEventos = "eventos_materiales.js";
        $archivoPrevisualizacion = "tabla_previsualizacion.php";
    }
    ?>
    <section id="sectionCotizar" class="d-none section-container">
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
        <div class="mb-3 d-flex col-12 gap-2 flex-column flex-md-row justify-content-center justify-content-md-between <?= ($tipoUsuario == 5) ? 'd-none' : '' ?>">
            <div class="d-flex col-12 col-md-5 flex-column mt-3">
                <?php include(ROOT_PATH . 'includes/backend_info_user.php'); ?>
                <label for="inputVendedor" class="label-estimador">Nombre del vendedor</label>
                <input id="inputVendedor" type="text" class="input-estimador" value="<?= $nombreUser ?>" required readonly tabindex="-1">
            </div>
            <div class="d-flex col-12 col-md-3 flex-column justify-content-end">
                <button type="button" id="btnModalComentariosAdjuntos" class="btn-general mt-4 btn-modal-comentarios-adjuntos" 
                        data-origen="coti"
                        data-es-mia="1">
                        Comentarios y adjuntos
                        <i class='bi bi-chat-left-text' style='color:#fff; margin-left: 5px;'></i>
                </button>
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
            <div class="d-flex flex-column col-md-7">
                <div class="d-flex">
                    <img id="imagenMaterialTabla_m1" class="col-4 img-sello-tabla d-none" src="../assets/img/general/blanco.jpg" alt="">
                    <img id="imagenMaterialTabla_m2" class="col-4 img-sello-tabla d-none" src="../assets/img/general/blanco.jpg" alt="">
                    <img id="imagenMaterialTabla_m3" class="col-4 img-sello-tabla d-none" src="../assets/img/general/blanco.jpg" alt="">
                </div>
                <div class="d-flex">
                    <img id="imagenMaterialTabla_m4" class="col-4 img-sello-tabla d-none" src="../assets/img/general/blanco.jpg" alt="">
                    <img id="imagenMaterialTabla_m5" class="col-4 img-sello-tabla d-none" src="../assets/img/general/blanco.jpg" alt="">
                </div>
            </div>
            <div class="d-flex flex-column col-md-5 ps-3">
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
            <button type="button" id="btnGuardarCotizacion" class="btn-general">Guardar cotización</button>
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
                    <li>H-ECOPUR (PU ROJO)</li>
                    <li>ECOSIL (RUBBER SILICON)</li>
                    <li>ECORUBBER 1 (RUBBER NITRILO)</li>
                    <li>ECORUBBER 2 (RUBBER VITON)</li>
                    <li>ECORUBBER 3 (RUBBER EPDM)</li>
                    <li>ECOPUR (PU VERDE)</li>
                </ul>
            </div>
            <div class="d-flex flex-column col-5">
                <label for="">Materiales duros:</label>
                <ul>
                    <li>ECOTAL (PLASTIC ECOTAL)</li>
                    <li>ECOMID (PLASTIC ECOMID)</li>
                    <li>ECOFLON 1 (PTFE TEFLON VIRGEN)</li>
                    <li>ECOFLON 2 (PTFE	NIKEL/MOLLY)</li>
                    <li>ECOFLON 3 (PTFE BRONCE)</li>
                </ul>
            </div>

        </div>
      </div>
    </div>
  </div>
</div>

<?php include(ROOT_PATH . 'includes/modal_comentarios_adjuntos.php'); ?>
<div style="height:500px;"></div>
<?php include(ROOT_PATH . 'includes/footer.php'); ?>

<script>
    const cantidadMateriales = <?= (int)$cantidadMateriales ?>;
    window.CANTIDAD_MATERIALES = cantidadMateriales;
</script>


<script src="<?= controlCache('../assets/js/estimador.js'); ?>"></script>
<script src="<?= controlCache('../assets/js/'.$archivoEventos.''); ?>"></script>

</body>
</html>