<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/bootstrap-icons.min.css'); ?>">
    <link href="<?= controlCache('../assets/dependencies/datatables.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/datatables.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/parametros_cotizador.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-formulario.css'); ?>">
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>

    <?php include(ROOT_PATH . 'includes/user_control.php'); 
          include(ROOT_PATH . 'includes/backend/parametros_cotizador.php'); 
    ?>
    <title>Parametros cotizador</title>
</head>
<body>
    

<div id="title" class="titulo mt-3">
    <h1>Editar parametros del cotizador</h1>
</div>


<section id="sectionParams" class="d-flex flex-row col-12 justify-content-center align-items-center mt-3">
    
    <div class="d-flex col-10 flex-row form-general">
        <div class="container-tab d-flex flex-column col-3">
            <div id="btnTabCostosOperacion" class="div-btn-arrow d-flex justify-content-between align-items-center">
                <div class="">Costos de operación</div>
                <i id="iconArrowRight" class="bi bi-caret-right align-content-center"></i>
            </div>
            <div id="containerMaterialsCO" class="container-materials d-none">
                <button type="button" id="btnTabCostosOperacionHECOPUR" class="btn-tab-material" data-mostrar="coH-ECOPUR">H-ECOPUR</button>
                <button type="button" id="btnTabCostosOperacionECOTAL" class="btn-tab-material" data-mostrar="coECOTAL">ECOTAL</button>
                <button type="button" id="btnTabCostosOperacionECOSIL" class="btn-tab-material" data-mostrar="coECOSIL">ECOSIL</button>
                <button type="button" id="btnTabCostosOperacionECORUBBER1" class="btn-tab-material" data-mostrar="coECORUBBER1">ECORUBBER 1</button>
                <button type="button" id="btnTabCostosOperacionECORUBBER2" class="btn-tab-material" data-mostrar="coECORUBBER2">ECORUBBER 2</button>
                <button type="button" id="btnTabCostosOperacionECORUBBER3" class="btn-tab-material" data-mostrar="coECORUBBER3">ECORUBBER 3</button>
                <button type="button" id="btnTabCostosOperacionECOPUR" class="btn-tab-material" data-mostrar="coECOPUR">ECOPUR</button>
                <button type="button" id="btnTabCostosOperacionECOMID" class="btn-tab-material" data-mostrar="coECOMID">ECOMID</button>
                <button type="button" id="btnTabCostosOperacionECOFLON1" class="btn-tab-material" data-mostrar="coECOFLON1">ECOFLON 1</button>
                <button type="button" id="btnTabCostosOperacionECOFLON2" class="btn-tab-material" data-mostrar="coECOFLON2">ECOFLON 2</button>
                <button type="button" id="btnTabCostosOperacionECOFLON3" class="btn-tab-material" data-mostrar="coECOFLON3">ECOFLON 3</button>
            </div>
            <div id="btnTabMU" class="div-btn-arrow d-flex justify-content-between align-items-center">
                <div class="">Multiplos de utilidad</div>
                <i id="iconArrowRight2" class="bi bi-caret-right align-content-center"></i>
            </div>
            <div id="containerMaterialsMU" class="container-materials d-none">
                <button type="button" id="btnTabMultiplosUtilidadCustom" class="btn-tab-material" data-mostrar="muc">Personalizado</button>
                <button type="button" id="btnTabMultiplosUtilidadProveedores" class="btn-tab-material" data-mostrar="mup">Proveedores</button>
                <button type="button" id="btnTabMultiplosUtilidadHECOPUR" class="btn-tab-material" data-mostrar="muH-ECOPUR">H-ECOPUR</button>
                <button type="button" id="btnTabMultiplosUtilidadECOTAL" class="btn-tab-material" data-mostrar="muECOTAL">ECOTAL</button>
                <button type="button" id="btnTabMultiplosUtilidadECOSIL" class="btn-tab-material" data-mostrar="muECOSIL">ECOSIL</button>
                <button type="button" id="btnTabMultiplosUtilidadECORUBBER1" class="btn-tab-material" data-mostrar="muECORUBBER1">ECORUBBER 1</button>
                <button type="button" id="btnTabMultiplosUtilidadECORUBBER2" class="btn-tab-material" data-mostrar="muECORUBBER2">ECORUBBER 2</button>
                <button type="button" id="btnTabMultiplosUtilidadECORUBBER3" class="btn-tab-material" data-mostrar="muECORUBBER3">ECORUBBER 3</button>
                <button type="button" id="btnTabMultiplosUtilidadECOPUR" class="btn-tab-material" data-mostrar="muECOPUR">ECOPUR</button>
                <button type="button" id="btnTabMultiplosUtilidadECOMID" class="btn-tab-material" data-mostrar="muECOMID">ECOMID</button>
                <button type="button" id="btnTabMultiplosUtilidadECOFLON1" class="btn-tab-material" data-mostrar="muECOFLON1">ECOFLON 1</button>
                <button type="button" id="btnTabMultiplosUtilidadECOFLON2" class="btn-tab-material" data-mostrar="muECOFLON2">ECOFLON 2</button>
                <button type="button" id="btnTabMultiplosUtilidadECOFLON3" class="btn-tab-material" data-mostrar="muECOFLON3">ECOFLON 3</button>
            </div>
            <button type="button" id="btnTabCostoHerramienta" class="btn-tab" data-mostrar="ch">Costos de herramienta</button>
            <button type="button" id="btnTabPreparacionBarraDI" class="btn-tab" data-mostrar="cpdib">Costo de preparacion DI de barra</button>
            <button type="button" id="btnTabCostoMinimoUnidad" class="btn-tab" data-mostrar="cmu">Costo mínimo de unidad</button>
            <button type="button" id="btnTabDescuentoCliente" class="btn-tab" data-mostrar="dc">Descuentos de cliente</button>
            <button type="button" id="btnTabDescuentoRelacionCantidad" class="btn-tab" data-mostrar="drc">Descuentos relacion cantidad</button>
            <button type="button" id="btnTabDescuentoMayoreo" class="btn-tab" data-mostrar="dm">Descuentos de mayoreo</button>
            <button type="button" id="btnTabResorteMetalico" class="btn-tab" data-mostrar="mrm">Resorte metalico</button>
        </div>
        
        <div id="containerFormsParametros" class="w-100 d-flex justify-content-start flex-column">
            <div id="containerInitial" class="d-flex flex-column w-100 h-100 justify-content-center align-items-center">
                <div class="mb-4">
                    <h4>Seleccione una categoría de parametros en el menú</h4>
                </div>
                <div>
                    <i class="bi bi-sliders2" style="font-size:150px;"></i>
                </div>
            </div>
            <div id="containerCostoOperacionHECOPUR" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de operación H-ECOPUR</h4>
                </div>
                <form id="formCostoOperacionHECOPUR" action="" method="POST">
                    <input type="hidden" name="formulario" value="coH-ECOPUR">
                    <?php foreach ($arregloCostosOperacionHECOPUR as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoOperacionECOTAL" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de operación ECOTAL</h4>
                </div>
                <form id="formCostoOperacionECOTAL" action="" method="POST">
                    <input type="hidden" name="formulario" value="coECOTAL">
                    <?php foreach ($arregloCostosOperacionECOTAL as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoOperacionECOSIL" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de operación ECOSIL</h4>
                </div>
                <form id="formCostoOperacionECOSIL" action="" method="POST">
                    <input type="hidden" name="formulario" value="coECOSIL">
                    <?php foreach ($arregloCostosOperacionECOSIL as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoOperacionECORUBBER1" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de operación ECORUBBER 1</h4>
                </div>
                <form id="formCostoOperacionECORUBBER1" action="" method="POST">
                    <input type="hidden" name="formulario" value="coECORUBBER1">
                    <?php foreach ($arregloCostosOperacionECORUBBER1 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoOperacionECORUBBER2" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de operación ECORUBBER 2</h4>
                </div>
                <form id="formCostoOperacionECORUBBER2" action="" method="POST">
                    <input type="hidden" name="formulario" value="coECORUBBER2">
                    <?php foreach ($arregloCostosOperacionECORUBBER2 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoOperacionECORUBBER3" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de operación ECORUBBER 3</h4>
                </div>
                <form id="formCostoOperacionECORUBBER3" action="" method="POST">
                    <input type="hidden" name="formulario" value="coECORUBBER3">
                    <?php foreach ($arregloCostosOperacionECORUBBER3 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoOperacionECOPUR" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de operación ECOPUR</h4>
                </div>
                <form id="formCostoOperacionECOPUR" action="" method="POST">
                    <input type="hidden" name="formulario" value="coECOPUR">
                    <?php foreach ($arregloCostosOperacionECOPUR as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoOperacionECOMID" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de operación ECOMID</h4>
                </div>
                <form id="formCostoOperacionECOMID" action="" method="POST">
                    <input type="hidden" name="formulario" value="coECOMID">
                    <?php foreach ($arregloCostosOperacionECOMID as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoOperacionECOFLON1" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de operación ECOFLON 1</h4>
                </div>
                <form id="formCostoOperacionECOFLON1" action="" method="POST">
                    <input type="hidden" name="formulario" value="coECOFLON1">
                    <?php foreach ($arregloCostosOperacionECOFLON1 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoOperacionECOFLON2" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de operación ECOFLON 2</h4>
                </div>
                <form id="formCostoOperacionECOFLON2" action="" method="POST">
                    <input type="hidden" name="formulario" value="coECOFLON2">
                    <?php foreach ($arregloCostosOperacionECOFLON2 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoOperacionECOFLON3" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de operación ECOFLON 3</h4>
                </div>
                <form id="formCostoOperacionECOFLON3" action="" method="POST">
                    <input type="hidden" name="formulario" value="coECOFLON3">
                    <?php foreach ($arregloCostosOperacionECOFLON3 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadCustom" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad con Proveedor y Material</h4>
                    <h5>Multiplo prioritario al cotizar</h5>
                </div>
                <form id="formMultiplosUtilidadCustom" action="" method="POST">
                    <input type="hidden" name="formulario" value="muc">
                    <?php foreach ($arregloMultiplosUtilidadCustom as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-7">
                                <button type="button" class="btn btn-danger eliminar-parametro mx-2" data-eliminar="<?= $registro['id']; ?>">
                                    <i class="bi bi-trash3"></i></button>
                                <label class="lbl-general">
                                    <?= $registro['caso']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div id="containerPruebas" class="d-none col-12 justify-content-between p-2 mt-4" style="border:1px solid #aaa;">
                        <p id="resultadoPruebas"></p>
                    </div>
                    <div class="d-flex col-12 justify-content-between mt-4">
                        <div class="col-3 d-flex justify-content-between align-items-center" >
                            <button id="btnNuevoParam" type="button" class="btn-general d-flex gap-2 justify-content-center align-items-center" 
                            data-bs-toggle="modal" data-bs-target="#modalAgregarParam"><i class="bi bi-file-plus" style="font-size:24px;"></i>Nuevo</button>
                        </div>
                        <div class="col-3 d-flex justify-content-between align-items-center" >
                            <button id="btnPrueba" type="button" class="btn-general d-flex gap-2 justify-content-center align-items-center" 
                            data-bs-toggle="modal" data-bs-target="#modalPrueba"><i class="bi bi-file-plus" style="font-size:24px;"></i>Prueba</button>
                        </div>
                        <div class="col-3 d-flex justify-content-between" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>            
            <div id="containerMultiploUtilidadProveedores" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad por proveedor</h4>
                </div>
                <form id="formMultiplosUtilidadProveedores" action="" method="POST">
                    <input type="hidden" name="formulario" value="mup">
                    <?php foreach ($arregloMultiplosUtilidadProveedores as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['caso']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadHECOPUR" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad H-ECOPUR</h4>
                </div>
                <form id="formMultiplosUtilidadECOTAL" action="" method="POST">
                    <input type="hidden" name="formulario" value="muH-ECOPUR">
                    <?php foreach ($arregloMultiplosUtilidadHECOPUR as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadECOTAL" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad ECOTAL</h4>
                </div>
                <form id="formMultiplosUtilidadECOTAL" action="" method="POST">
                    <input type="hidden" name="formulario" value="muECOTAL">
                    <?php foreach ($arregloMultiplosUtilidadECOTAL as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadECOSIL" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad ECOSIL</h4>
                </div>
                <form id="formMultiplosUtilidadECOSIL" action="" method="POST">
                    <input type="hidden" name="formulario" value="muECOSIL">
                    <?php foreach ($arregloMultiplosUtilidadECOSIL as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadECORUBBER1" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad ECORUBBER 1</h4>
                </div>
                <form id="formMultiplosUtilidadECORUBBER1" action="" method="POST">
                    <input type="hidden" name="formulario" value="muECORUBBER1">
                    <?php foreach ($arregloMultiplosUtilidadECORUBBER1 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadECORUBBER2" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad ECORUBBER 2</h4>
                </div>
                <form id="formMultiplosUtilidadECORUBBER2" action="" method="POST">
                    <input type="hidden" name="formulario" value="muECORUBBER2">
                    <?php foreach ($arregloMultiplosUtilidadECORUBBER2 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadECORUBBER3" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad ECORUBBER 3</h4>
                </div>
                <form id="formMultiplosUtilidadECORUBBER3" action="" method="POST">
                    <input type="hidden" name="formulario" value="muECORUBBER3">
                    <?php foreach ($arregloMultiplosUtilidadECORUBBER3 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadECOPUR" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad ECOPUR</h4>
                </div>
                <form id="formMultiplosUtilidadECOPUR" action="" method="POST">
                    <input type="hidden" name="formulario" value="muECOPUR">
                    <?php foreach ($arregloMultiplosUtilidadECOPUR as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadECOMID" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad ECOMID</h4>
                </div>
                <form id="formMultiplosUtilidadECOMID" action="" method="POST">
                    <input type="hidden" name="formulario" value="muECOMID">
                    <?php foreach ($arregloMultiplosUtilidadECOMID as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadECOFLON1" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad ECOFLON 1</h4>
                </div>
                <form id="formMultiplosUtilidadECOFLON1" action="" method="POST">
                    <input type="hidden" name="formulario" value="muECOFLON1">
                    <?php foreach ($arregloMultiplosUtilidadECOFLON1 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadECOFLON2" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad ECOFLON 2</h4>
                </div>
                <form id="formMultiplosUtilidadECOFLON2" action="" method="POST">
                    <input type="hidden" name="formulario" value="muECOFLON2">
                    <?php foreach ($arregloMultiplosUtilidadECOFLON2 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerMultiploUtilidadECOFLON3" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplos de utilidad ECOFLON 3</h4>
                </div>
                <form id="formMultiplosUtilidadECOFLON3" action="" method="POST">
                    <input type="hidden" name="formulario" value="muECOFLON3">
                    <?php foreach ($arregloMultiplosUtilidadECOFLON3 as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoHerramienta" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costos de herramienta</h4>
                </div>
                <form id="formCostoHerramienta" action="" method="POST">
                    <input type="hidden" name="formulario" value="ch">
                    <?php foreach ($arregloCostosHerramienta as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoPreparacionBarraDI" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costo de preparacion DI de la barra</h4>
                </div>
                <form id="formPreparacionBarraDI" action="" method="POST">
                    <input type="hidden" name="formulario" value="cpdib">
                    <?php foreach ($arregloPreparacionBarraDI as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerCostoMinimoUnidad" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Costo mínimo de unidad</h4>
                </div>
                <form id="formCostoMinimoUnidad" action="" method="POST">
                    <input type="hidden" name="formulario" value="cmu">
                    <?php foreach ($arregloCostoMinimoUnidad as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> < DI <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="col-2">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>            
            <div id="containerDescuentoCliente" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Descuentos de cliente</h4>
                </div>
                <form id="formDescuentoCliente" action="" method="POST">
                    <input type="hidden" name="formulario" value="dc">
                    <?php foreach ($arregloDescuentosCliente as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['clasificacion']; ?>
                                </label>
                            </div>
                            <div class="d-flex col-2 align-items-center">
                                <input class="input-text" type="number" min="0" step="0.01"
                                        name="descuento[<?= $registro['clasificacion']; ?>]"  
                                        value="<?= $registro['descuento']; ?>" required><span>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerDescuentoRelacionCantidad" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Descuento relación cantidad</h4>
                </div>
                <form id="formDescuentoCantidad" action="" method="POST">
                    <input type="hidden" name="formulario" value="drc">
                    <?php foreach ($arregloDescuentosRelacionCantidad as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> <= Q <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="d-flex col-2 align-items-center">
                                <input class="input-text" type="number" min="0" step="0.01"
                                        name="valor[<?= $registro['id']; ?>]" 
                                        value="<?= $registro['valor']; ?>" required><span>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerDescuentoMayoreo" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Descuento por mayoreo</h4>
                </div>
                <form id="formDescuentoMayoreo" action="" method="POST">
                    <input type="hidden" name="formulario" value="dm">
                    <?php foreach ($arregloDescuentosMayoreo as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    <?= $registro['limite_inferior']; ?> <= Q <= <?= $registro['limite_superior']; ?>
                                </label>
                            </div>
                            <div class="d-flex col-2 align-items-center">
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]"
                                        value="<?= $registro['valor']; ?>" required><span>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            <div id="containerResorteMetalico" class="mb-5 px-5 d-none">
                <div class="mb-4">
                    <h4>Multiplo de costo por resorte/inserto/muelle metalico</h4>
                </div>
                <form id="formResorteMetalico" action="" method="POST">
                    <input type="hidden" name="formulario" value="multiploResorteMetalico">
                    <?php foreach ($arregloResorteMetalico as $registro): ?>
                        <div class="d-flex flex-row justify-content-evenly align-items-center">
                            <div class="col-5">
                                <label class="lbl-general">
                                    Multiplo de porcentaje aplicado
                                </label>
                            </div>
                            <div class="d-flex col-2 align-items-center">
                                <span>*</span>
                                <input class="input-text" type="number" min="0" step="0.01" 
                                        name="valor[<?= $registro['id']; ?>]"
                                        value="<?= $registro['valor']; ?>" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex col-12 justify-content-end mt-4">
                        <div class="col-3 d-flex justify-content-end" >
                            <button type="submit" class="btn-general" data-target="guardar" >Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Modal para agregar nuevo parametro -->
<div class="modal fade" id="modalAgregarParam" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Agregar nuevo múltiplo de utilidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">                       
                    <div class="d-flex justify-content-between ">
                        <div class="" style="width:48%;">
                            <label for="inputProveedor" class="lbl-general">Proveedor</label>
                            <select id="inputProveedor" class="selector" name="proveedor" >
                                <option selected disabled>Seleccionar</option>
                                <option value="TRYGONAL">TRYGONAL</option>        
                                <option value="SKF">SKF</option>
                                <option value="CARVIFLON">CARVIFLON</option>
                                <option value="SLM">SLM</option>    
                            </select>
                        </div>                      
                        <div class="" style="width:48%;">
                            <label for="inputMaterial" class="lbl-general">Material</label>
                            <select id="inputMaterial" class="selector" name="material"  >
                                <option disabled selected>Seleccionar</option>
                                <option value="H-ECOPUR">H-ECOPUR</option>
                                <option value="ECOSIL">ECOSIL</option>
                                <option value="ECORUBBER 1">ECORUBBER 1</option>
                                <option value="ECORUBBER 2">ECORUBBER 2</option>
                                <option value="ECORUBBER 3">ECORUBBER 3</option>
                                <option value="ECOPUR">ECOPUR</option>
                                <option value="ECOTAL">ECOTAL</option>
                                <option value="ECOMID">ECOMID</option>
                                <option value="ECOFLON 1">ECOFLON 1</option>
                                <option value="ECOFLON 2">ECOFLON 2</option>
                                <option value="ECOFLON 3">ECOFLON 3</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between ">
                        <div style="width:48%;">
                            <label for="inputCondicion" class="lbl-general">Condicional del DI</label>
                            <select id="inputCondicion" class="selector" name="condicion" >
                                <option selected disabled>Seleccionar</option>
                                <option value="<">DI < (Menor a)</option>        
                                <option value="<=">DI <= (Menor o igual a)</option>
                                <option value=">">DI > (Mayor a)</option>
                                <option value=">=">DI >= (Mayor o igual a)</option>    
                            </select>
                        </div>
                        <div style="width:48%;">
                            <label for="inputDI" class="lbl-general">Diametro Interior</label>
                            <input id="inputDI" class="input-text" type="number" min="0" step="0.01" name="di">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between ">
                        <div style="width:48%;">
                            <label for="inputMultiplo" class="lbl-general">Valor del multiplo*</label>
                            <input id="inputMultiplo" class="input-text" type="number" min="0" step="0.01" name="valor" required>
                        </div>
                        <div style="width:48%;">
                        </div>
                    </div>

                    <button id="btnGuardarNuevoParametro" type="button" class="btn-general">Guardar</button>
            
            </div>
        </div>
    </div>
</div>


<!-- Modal para testear parametro de multiplo de utilidad resultante -->
<div class="modal fade" id="modalPrueba" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Prueba resultado de multiplo de utilidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">                       
                <div class="d-flex justify-content-between ">
                    <div class="" style="width:48%;">
                        <label for="inputProveedor2" class="lbl-general">Proveedor*</label>
                        <select id="inputProveedor2" class="selector" name="proveedor" >
                            <option selected disabled>Seleccionar</option>
                            <option value="TRYGONAL">TRYGONAL</option>        
                            <option value="SKF">SKF</option>
                            <option value="CARVIFLON">CARVIFLON</option>
                            <option value="SLM">SLM</option>    
                        </select>
                    </div>                      
                    <div class="" style="width:48%;">
                        <label for="inputMaterial2" class="lbl-general">Material*</label>
                        <select id="inputMaterial2" class="selector" name="material"  >
                            <option disabled selected>Seleccionar</option>
                            <option value="H-ECOPUR">H-ECOPUR</option>
                            <option value="ECOSIL">ECOSIL</option>
                            <option value="ECORUBBER 1">ECORUBBER 1</option>
                            <option value="ECORUBBER 2">ECORUBBER 2</option>
                            <option value="ECORUBBER 3">ECORUBBER 3</option>
                            <option value="ECOPUR">ECOPUR</option>
                            <option value="ECOTAL">ECOTAL</option>
                            <option value="ECOMID">ECOMID</option>
                            <option value="ECOFLON 1">ECOFLON 1</option>
                            <option value="ECOFLON 2">ECOFLON 2</option>
                            <option value="ECOFLON 3">ECOFLON 3</option>
                        </select>
                    </div>
                </div>
                <div class="d-flex justify-content-between ">
                    <div style="width:48%;">
                        <label for="inputDI2" class="lbl-general">Diametro Interior*</label>
                        <input id="inputDI2" class="input-text" type="number" min="0" step="0.01" name="di">
                    </div>
                    <div style="width:48%;">
                    </div>
                </div>

                <button id="btnEnviarPrueba" type="button" class="btn-general">Enviar</button>
            
            </div>
        </div>
    </div>
</div>
</body>
</html>