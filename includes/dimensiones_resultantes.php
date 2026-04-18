<form method="post"> 
    <!-- PRIMER FORMULARIO DE DIMENSIONES DEL CLIENTE -->
    <section id="sectionDimensionesSello" class="d-none section-container" style="backdrop-filter:none !important;">
        <h2 class="pt-2">Ingrese dimensiones del sello deseadas por el cliente</h2>
        
        <div class="d-flex col-12 flex-column flex-md-row justify-content-between align-items-md-start align-items-center">
            <div class="controles d-flex col-12 flex-column border-gray" style="backdrop-filter:none !important;">
                <div class="d-flex flex-column">
                    <div class="d-flex col-12 flex-column justify-content-between mt-4" >
                        
                            <!-- <label for="selectorTipoMedida" class="label-estimador">Tipo de medida * indicada por el cliente</label>
                            <select id="selectorTipoMedida" class="form-select" required disabled>
                                <option value="" disabled selected>Seleccione el tipo de medida *</option>
                                <option value="Sello">Sello</option>
                                <option value="Metal">Metal</option>
                            </select> -->
                                              
                        <div class="col-12 col-md-12 d-flex flex-column flex-md-row gap-5 justify-content-evenly">

                            <div class="d-flex gap-2 flex-column col-12 col-md-3 justify-content-between align-items-center">
                                <div class="d-flex flex-column col-12 align-items-center">
                                    <h5 class="label-estimador">Diámetro Interior (DI)</h5>
                                    <div class="img-dimensions" style="height:auto;width:30%;">
                                        <img src="<?= $imageDirFamilyDI ?>" class="img-fluid" alt="">
                                    </div>
                                </div>
                                <div class="d-flex flex-column col-12 align-items-center">
                                    <div class="d-flex flex-column col-12 mb-4">
                                        <label id="lblMedidaPrimariaDI" for="selectorTipoMedidaDI" class="label-estimador">Tipo de medida *</label>
                                        <select id="selectorTipoMedidaDI" class="" required disabled>
                                            <option value="" disabled selected>Seleccione una opción</option>
                                            <option value="Sello">Sello</option>
                                            <option value="Metal">Metal</option>
                                            <option value="Muestra">Muestra</option>
                                            <option value="Plano">Plano</option>
                                        </select>
                                     
                                    </div>
                                    <div class="d-flex flex-column col-12">
                                        <label for="diametro_interior_mm_cliente" class="label-estimador">Milimetros (mm) *</label>
                                        <input type="number" id="diametro_interior_mm_cliente" class="input-estimador" step="0.01" min="0"  name="diametro_interior_mm"  required disabled placeholder="">
                                        <label for="diametro_interior_inch_cliente" class="label-estimador mt-2">Pulgadas (inches) *</label>
                                        <input type="number" id="diametro_interior_inch_cliente" class="input-estimador" step="0.0001" min="0"  name="diametro_interior_inch" required disabled placeholder="">
                                    </div>
                                </div>
                            </div>
                                                    
                            <div id="containerDE" class="d-flex gap-2 flex-column col-12 col-md-3 justify-content-between align-items-center">
                                <div class="d-flex flex-column col-12 align-items-center">
                                    <h5 class="label-estimador">Diámetro Exterior (DE)</h5>
                                    <div class="img-dimensions" style="height:auto;width:30%;">
                                        <img src="<?= $imageDirFamilyDE ?>" class="img-fluid" alt="">
                                    </div>
                                </div>
                                <div class="d-flex flex-column col-12 align-items-center">
                                    <div class="d-flex flex-column col-12 mb-4">
                                        <label id="lblMedidaPrimariaDE" for="selectorTipoMedidaDE" class="label-estimador">Tipo de medida *</label>
                                        <select id="selectorTipoMedidaDE" class="form-select" required disabled>
                                            <option value="" disabled selected>Seleccione una opción</option>
                                            <option value="Sello">Sello</option>
                                            <option value="Metal">Metal</option>
                                            <option value="Muestra">Muestra</option>
                                            <option value="Plano">Plano</option>
                                        </select>
                                    </div>
                                    <div class="d-flex flex-column col-12">
                                        <label for="diametro_exterior_mm_cliente" class="label-estimador">Milimetros (mm) *</label>
                                        <input type="number" id="diametro_exterior_mm_cliente"step="0.01" min="0" class="input-estimador" name="diametro_exterior_mm" required disabled placeholder="">
                                        <label for="diametro_exterior_inch_cliente" class="label-estimador mt-2">Pulgadas (inches) *</label>
                                        <input type="number" id="diametro_exterior_inch_cliente" step="0.0001" min="0" class="input-estimador" name="diametro_exterior_inch" required disabled placeholder="">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-column col-12 col-md-3 justify-content-between align-items-center">
                                <div class="d-flex flex-column col-12 align-items-center">
                                    <h5 class="label-estimador" id="labelAlturaMM_cliente">Altura (H)</h5>
                                    <div class="img-dimensions" style="height:auto;width:30%;">
                                        <img src="<?= $imageDirFamilyH ?>" class="img-fluid" alt="">
                                    </div>
                                </div>
                                <div class="d-flex flex-column col-12 align-items-center">    
                                    <div class="d-flex flex-column col-12 mb-4">
                                        <label id="lblMedidaPrimariaH" for="selectorTipoMedidaH" class="label-estimador">Tipo de medida *</label>
                                        <select id="selectorTipoMedidaH" class="form-select" required disabled>
                                            <option value="" disabled selected>Seleccione una opción</option>
                                            <option value="Sello">Sello</option>
                                            <option value="Metal">Metal</option>
                                            <option value="Muestra">Muestra</option>
                                            <option value="Plano">Plano</option>
                                        </select>
                                    </div>
                                    <div class="d-flex flex-column col-12">
                                        <label for="altura_mm_cliente"  class="label-estimador">Milimetros (mm) *</label>
                                        <input type="number" id="altura_mm_cliente" class="input-estimador" step="0.01" min="0" name="altura_mm" required disabled placeholder="">
                                        <label for="altura_inch_cliente" class="label-estimador mt-2">Pulgadas (inches) *</label>
                                        <input type="number" id="altura_inch_cliente" class="input-estimador" step="0.0001" min="0"  name="altura_inch" required disabled placeholder="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    <div class="d-flex col-12 col-md-3 align-self-end flex-column justify-content-between mt-3" >
                        <div id="containerbtnOtrasAlturas" class="d-flex flex-column col-12 align-items-center">
                            <button id="btnOtrasAlturas" type="button" class="btn-disabled d-none" data-bs-toggle="modal" data-bs-target="#modalOtrasAlturas">Otras alturas</button>
                        </div>
                    </div>
                    <div id="containerErrorDimensiones_cliente" class="text-faltan mt-3 py-2 px-3 px-md-5 border border-dark-subtitle bg-white bg-opacity-10 font-monospace rounded-2">
                        <span></span>
                    </div>   


                    <div class="d-none col-12 flex-column justify-content-between mt-1" >
                        <div class=" mb-3">
                            <h5 id="lblMedidaSecundaria" class="label-estimador">Medida secundaria</h5>
                        </div>                        
                        <div class="col-12 col-md-12 d-flex flex-column flex-md-row  gap-4 justify-content-between">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="img-dimensions2">
                                    <img src="<?= $imageDirFamilyDI ?>" class="img-fluid" alt="">
                                </div>
                                <div class="d-flex flex-column col-8">
                                    <label for="diametro_interior_mm_cliente2" class="label-estimador">Diámetro Interior (mm)</label>
                                    <input type="number" id="diametro_interior_mm_cliente2" class="input-estimador" step="0.01" min="0"  name="diametro_interior_mm"  required disabled placeholder="">
                                    <label for="diametro_interior_inch_cliente2" class="label-estimador mt-2">Diámetro Interior (inches)</label>
                                    <input type="number" id="diametro_interior_inch_cliente2" class="input-estimador" step="0.0001" min="0"  name="diametro_interior_inch" required disabled placeholder="">
                                </div>
                            </div>                            
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="img-dimensions2">
                                    <img src="<?= $imageDirFamilyDE ?>" class="img-fluid" alt="">
                                </div>
                                <div class="d-flex flex-column col-8">
                                    <label for="diametro_exterior_mm_cliente2" class="label-estimador">Diámetro Exterior (mm)</label>
                                    <input type="number" id="diametro_exterior_mm_cliente2"step="0.01" min="0" class="input-estimador" name="diametro_exterior_mm" required disabled placeholder="">
                                    <label for="diametro_exterior_inch_cliente2" class="label-estimador mt-2">Diámetro Exterior (inches)</label>
                                    <input type="number" id="diametro_exterior_inch_cliente2" step="0.0001" min="0" class="input-estimador" name="diametro_exterior_inch" required disabled placeholder="">
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="img-dimensions2">
                                    <img src="<?= $imageDirFamilyH ?>" class="img-fluid" alt="">
                                </div>
                                <div class="d-flex flex-column col-8">
                                    <label for="altura_mm_cliente2" id="labelAlturaMM_cliente2" class="label-estimador">Altura (mm)</label>
                                    <input type="number" id="altura_mm_cliente2" class="input-estimador" step="0.01" min="0" name="altura_mm" required disabled placeholder="">
                                    <label for="altura_inch_cliente2" id="labelAlturaInch_cliente2" class="label-estimador mt-2">Altura (inches)</label>
                                    <input type="number" id="altura_inch_cliente2" class="input-estimador" step="0.0001" min="0"  name="altura_inch" required disabled placeholder="">
                                </div>
                                
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </section>

</form>
<!-- Modal Otras medidas Wipers-->
<div class="modal fade" id="modalOtrasAlturas" 
     tabindex="-1" 
     aria-labelledby="modalOtrasAlturasLabel" 
     aria-hidden="true"
     data-bs-backdrop="static" 
     data-bs-keyboard="false"> <!-- evita cerrar con click fuera o ESC -->

    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="otrasAlturasTitle">
                    Otras alturas para este perfil
                    <!-- <i id="questionIconSpecialWiper" class="bi bi-question-circle-fill d-none" style="padding-left:5px;"></i> -->
                </h5>
            </div>
            <div class="modal-body text-center">
                <div class="d-flex col-12 flex-column justify-content-between" >
                    <div id="containerWiperEscalon" class="d-none d-flex col-12 flex-column justify-content-between align-items-center">
                        <img src="../assets/img/general/wiper_escalon.jpg" class="img-fluid col-8 col-md-4" alt="Wiper con Escalon">
                    </div>
                    <div id="containerWiperEspecial" class="d-none d-flex col-12 flex-column justify-content-between align-items-center">
                        <img src="../assets/img/general/wisper_especial3.jpg" class="img-fluid col-8 col-md-4" alt="Wiper Especial">
                    </div>
                    <div class="col-12 col-md-12 d-flex flex-column flex-md-row gap-4 justify-content-center">
                        <div id="divAlturaCaja" class="d-flex flex-column d-none">
                            <label for="inputAlturaCaja" id="labelAlturaCajaMM" class="lbl-general mt-3">Altura de caja (mm)</label>
                            <input type="number" id="inputAlturaCaja" class="input-text" step="0.01" min="0" required placeholder="">
                            <label for="inputAlturaCajaInch" id="labelAlturaCajaInch" class="lbl-general mt-2">Altura de caja (inches)</label>
                            <input type="number" id="inputAlturaCajaInch" class="input-text" step="0.0001" min="0" required placeholder="">
                        </div>
                        <div id="divAlturaEscalon" class="d-flex flex-column d-none">
                            <label for="inputAlturaEscalon" id="labelAlturaEscalonMM" class="lbl-general mt-3">Altura caja + escalón (mm)</label>
                            <input type="number" id="inputAlturaEscalon" class="input-text" step="0.01" min="0" required placeholder="">
                            <label for="inputAlturaEscalonInch" id="labelAlturaEscalonInch" class="lbl-general mt-2">Altura caja + escalón (inches)</label>
                            <input type="number" id="inputAlturaEscalonInch" class="input-text" step="0.0001" min="0" required placeholder="">
                        </div>
                        <div id="divAlturaH2" class="d-flex flex-column d-none">
                            <label for="inputAlturaH2" id="labelAlturaH2MM" class="lbl-general mt-3">Altura H2 (mm)</label>
                            <input type="number" id="inputAlturaH2" class="input-text" step="0.01" min="0" required placeholder="">
                            <label for="inputAlturaH2Inch" id="labelAlturaH2Inch" class="lbl-general mt-2">Altura H2 (inches)</label>
                            <input type="number" id="inputAlturaH2Inch" class="input-text" step="0.0001" min="0" required placeholder="">
                        </div>
                        <div id="divAlturaH3" class="d-flex flex-column d-none">
                            <label for="inputAlturaH3" id="labelAlturaH3MM" class="lbl-general mt-3">Altura H3 (mm)</label>
                            <input type="number" id="inputAlturaH3" class="input-text" step="0.01" min="0" required placeholder="">
                            <label for="inputAlturaH3Inch" id="labelAlturaH3Inch" class="lbl-general mt-2">Altura H3 (inches)</label>
                            <input type="number" id="inputAlturaH3Inch" class="input-text" step="0.0001" min="0" required placeholder="">
                        </div>
                    </div>
                </div> 
            </div>
        <div class="modal-footer col-3 align-self-center">
            <button id="btnOtrasAlturasClose" type="button" class="btn-general" data-bs-dismiss="modal">Ok</button>
        </div>
    </div>
  </div>
</div>

<!-- Modal Question Wiper Especial-->
<div class="modal fade" id="modalSpecialWiper" tabindex="-1" aria-labelledby="modalSpecialWiperLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md"> <!-- puedes usar modal-md o modal-lg -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalSpecialWiperLabel">Alturas de los Wipers especiales</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <img src="../assets/img/general/wisper_especial3.jpg" class="img-fluid" alt="Wiper Especial">
      </div>
    </div>
  </div>
</div>

<!-- Modal para descripcion del estatus del billet-->
<div class="modal fade" id="modalEstatusBillet" tabindex="-1" aria-labelledby="modalEstatusBilletLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md"> <!-- puedes usar modal-md o modal-lg -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" >Detalle del estatus de barra</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <p id="textoDetalleEstatus" class="fs-6 fw-bold"></p>
      </div>
    </div>
  </div>
</div>
