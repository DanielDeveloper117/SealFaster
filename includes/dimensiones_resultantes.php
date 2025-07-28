<form method="post"> 
    <!-- PRIMER FORMULARIO DE DIMENSIONES DEL CLIENTE -->
    <section id="sectionDimensionesSello" class="section-container">
        <h2 class="pt-2">Ingrese dimensiones del sello deseadas por el cliente</h2>
        
        <div class="d-flex col-12 flex-column flex-md-row justify-content-between align-items-md-start align-items-center">
            <div class="controles d-flex col-12 flex-column border-gray">
                <h4>Familia: <?php echo $tipoEcho ?>, Perfil: <?php echo $selloOriginal ?></h4>

                <div class="d-flex flex-column">
                    <div class="d-flex col-12 flex-column justify-content-between mt-4" >
                        <div class=" mb-3">
                            <label for="selectorTipoMedida" class="form-label">Tipo de medida indicada por el cliente</label>
                            <select id="selectorTipoMedida" class="form-select" required disabled>
                                <option value="" disabled selected>Seleccione el tipo de medida</option>
                                <option value="Sello">Sello</option>
                                <option value="Metal">Metal</option>
                            </select>
                            <div class=" mt-3">
                                <h5 id="lblMedidaPrimaria" class="form-label">Medida primaria</h5>
                            </div>  
                        </div>                        
                        <div class="col-12 col-md-12 d-flex flex-column flex-md-row gap-4 justify-content-between">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="img-dimensions" style="height:auto;">
                                    <img src="<?= $imageDirFamilyDI ?>" class="img-fluid" alt="">
                                </div>
                                <div class="d-flex flex-column col-8">
                                    <label for="diametro_interior_mm_cliente" class="form-label">Diámetro Interior (mm) *</label>
                                    <input type="number" id="diametro_interior_mm_cliente" class="input-readonly" step="0.01" min="0"  name="diametro_interior_mm"  required disabled placeholder="Seleccione tipo de medida">
                                    <label for="diametro_interior_inch_cliente" class="form-label mt-2">Diámetro Interior (inches) *</label>
                                    <input type="number" id="diametro_interior_inch_cliente" class="input-readonly" step="0.0001" min="0"  name="diametro_interior_inch" required disabled placeholder="Seleccione tipo de medida">
                                </div>
                            </div>                            
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="img-dimensions" style="height:auto;">
                                    <img src="<?= $imageDirFamilyDE ?>" class="img-fluid" alt="">
                                </div>
                                <div class="d-flex flex-column col-8">
                                    <label for="diametro_exterior_mm_cliente" class="form-label">Diámetro Exterior (mm) *</label>
                                    <input type="number" id="diametro_exterior_mm_cliente"step="0.01" min="0" class="input-readonly" name="diametro_exterior_mm" required disabled placeholder="Seleccione tipo de medida">
                                    <label for="diametro_exterior_inch_cliente" class="form-label mt-2">Diámetro Exterior (inches) *</label>
                                    <input type="number" id="diametro_exterior_inch_cliente" step="0.0001" min="0" class="input-readonly" name="diametro_exterior_inch" required disabled placeholder="Seleccione tipo de medida">
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="img-dimensions" style="height:auto;">
                                    <img src="<?= $imageDirFamilyH ?>" class="img-fluid" alt="">
                                </div>
                                <div class="d-flex flex-column col-8">
                                    <label for="altura_mm_cliente" id="labelAlturaMM_cliente" class="form-label">Altura (mm) *</label>
                                    <input type="number" id="altura_mm_cliente" class="input-readonly" step="0.01" min="0" name="altura_mm" required disabled placeholder="Seleccione tipo de medida">
                                    <label for="altura_inch_cliente" id="labelAlturaInch_cliente" class="form-label mt-2">Altura (inches) *</label>
                                    <input type="number" id="altura_inch_cliente" class="input-readonly" step="0.0001" min="0"  name="altura_inch" required disabled placeholder="Seleccione tipo de medida">
                                </div>
                                
                            </div>
                        </div>
                    </div>
                    <div class="d-flex col-12 flex-column justify-content-between mt-4" >
                        <h5 id="otrasAlturasTitle" class="mt-3 d-none">Otras alturas para este perfil<i id="questionIconSpecialWiper" class="bi bi-question-circle-fill d-none" style="padding-left:5px;"></i></h5>
                        <div class="col-12 col-md-12 d-flex flex-column flex-md-row gap-4 justify-content-center">
                            <div id="divAlturaCaja" class="d-flex flex-column col-md-3 d-none">
                                <label for="inputAlturaCaja" id="labelAlturaCajaMM" class="form-label mt-3">Altura de caja (mm)</label>
                                <input type="number" id="inputAlturaCaja" class="input-readonly" step="0.01" min="0" required placeholder="Seleccione tipo de medida">
                                <label for="inputAlturaCajaInch" id="labelAlturaCajaInch" class="form-label mt-2">Altura de caja (inches)</label>
                                <input type="number" id="inputAlturaCajaInch" class="input-readonly" step="0.0001" min="0" required placeholder="Seleccione tipo de medida">
                            </div>
                            <div id="divAlturaEscalon" class="d-flex flex-column col-md-3 d-none">
                                <label for="inputAlturaEscalon" id="labelAlturaEscalonMM" class="form-label mt-3">Altura escalon (mm)</label>
                                <input type="number" id="inputAlturaEscalon" class="input-readonly" step="0.01" min="0" required placeholder="Seleccione tipo de medida">
                                <label for="inputAlturaEscalonInch" id="labelAlturaEscalonInch" class="form-label mt-2">Altura escalon (inches)</label>
                                <input type="number" id="inputAlturaEscalonInch" class="input-readonly" step="0.0001" min="0" required placeholder="Seleccione tipo de medida">
                            </div>
                            <div id="divAlturaH2" class="d-flex flex-column col-md-3 d-none">
                                <label for="inputAlturaH2" id="labelAlturaH2MM" class="form-label mt-3">Altura H2 (mm)</label>
                                <input type="number" id="inputAlturaH2" class="input-readonly" step="0.01" min="0" required placeholder="Seleccione tipo de medida">
                                <label for="inputAlturaH2Inch" id="labelAlturaH2Inch" class="form-label mt-2">Altura H2 (inches)</label>
                                <input type="number" id="inputAlturaH2Inch" class="input-readonly" step="0.0001" min="0" required placeholder="Seleccione tipo de medida">
                            </div>
                            <div id="divAlturaH3" class="d-flex flex-column col-md-3 d-none">
                                <label for="inputAlturaH3" id="labelAlturaH3MM" class="form-label mt-3">Altura H3 (mm)</label>
                                <input type="number" id="inputAlturaH3" class="input-readonly" step="0.01" min="0" required placeholder="Seleccione tipo de medida">
                                <label for="inputAlturaH3Inch" id="labelAlturaH3Inch" class="form-label mt-2">Altura H3 (inches)</label>
                                <input type="number" id="inputAlturaH3Inch" class="input-readonly" step="0.0001" min="0" required placeholder="Seleccione tipo de medida">
                            </div>

                        </div>
                    </div> 
                    <div id="containerErrorDimensiones_cliente" class="text-faltan mt-3">
                        <span></span>
                    </div>   

                    <div class="d-flex col-12 flex-column justify-content-between mt-1" >
                        <div class=" mb-3">
                            <h5 id="lblMedidaSecundaria" class="form-label">Medida secundaria</h5>
                        </div>                        
                        <div class="col-12 col-md-12 d-flex flex-column flex-md-row  gap-4 justify-content-between">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="img-dimensions2">
                                    <img src="<?= $imageDirFamilyDI ?>" class="img-fluid" alt="">
                                </div>
                                <div class="d-flex flex-column col-8">
                                    <label for="diametro_interior_mm_cliente2" class="form-label">Diámetro Interior (mm)</label>
                                    <input type="number" id="diametro_interior_mm_cliente2" class="input-readonly" step="0.01" min="0"  name="diametro_interior_mm"  required disabled placeholder="Seleccione tipo de medida">
                                    <label for="diametro_interior_inch_cliente2" class="form-label mt-2">Diámetro Interior (inches)</label>
                                    <input type="number" id="diametro_interior_inch_cliente2" class="input-readonly" step="0.0001" min="0"  name="diametro_interior_inch" required disabled placeholder="Seleccione tipo de medida">
                                </div>
                            </div>                            
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="img-dimensions2">
                                    <img src="<?= $imageDirFamilyDE ?>" class="img-fluid" alt="">
                                </div>
                                <div class="d-flex flex-column col-8">
                                    <label for="diametro_exterior_mm_cliente2" class="form-label">Diámetro Exterior (mm)</label>
                                    <input type="number" id="diametro_exterior_mm_cliente2"step="0.01" min="0" class="input-readonly" name="diametro_exterior_mm" required disabled placeholder="Seleccione tipo de medida">
                                    <label for="diametro_exterior_inch_cliente2" class="form-label mt-2">Diámetro Exterior (inches)</label>
                                    <input type="number" id="diametro_exterior_inch_cliente2" step="0.0001" min="0" class="input-readonly" name="diametro_exterior_inch" required disabled placeholder="Seleccione tipo de medida">
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="img-dimensions2">
                                    <img src="<?= $imageDirFamilyH ?>" class="img-fluid" alt="">
                                </div>
                                <div class="d-flex flex-column col-8">
                                    <label for="altura_mm_cliente2" id="labelAlturaMM_cliente2" class="form-label">Altura (mm)</label>
                                    <input type="number" id="altura_mm_cliente2" class="input-readonly" step="0.01" min="0" name="altura_mm" required disabled placeholder="Seleccione tipo de medida">
                                    <label for="altura_inch_cliente2" id="labelAlturaInch_cliente2" class="form-label mt-2">Altura (inches)</label>
                                    <input type="number" id="altura_inch_cliente2" class="input-readonly" step="0.0001" min="0"  name="altura_inch" required disabled placeholder="Seleccione tipo de medida">
                                </div>
                                
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </section>

</form>

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

