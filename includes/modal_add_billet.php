<!-- Modal para agregar/editar registro -->
<div class="modal fade" id="modalInventario" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="titleModal" class="modal-title" >Agregar registro</h5>
                <button id="btnCloseModal" type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formInventario">                        
                    <input type="hidden" id="inputId" name="id">
                    <input type="hidden" id="inputAction" name="action" value="insert">
                    <input type="hidden" id="inputEstatus" name="estatus" value="Disponible para cotizar">
                    <input type="hidden" id="inputClaveAlterna" name="clave_alterna" value="">
                    <div class="mb-3">
                        <label for="inputAlmacenIdBilletForm" class="form-label fw-bold">Almacén <span class="text-danger">*</span></label>
                        <select id="inputAlmacenIdBilletForm" class="selector" name="almacen_id" required>
                            <option value="" disabled selected>Seleccionar un almacén</option>
                        </select>
                    </div>    
                    <div class="mb-3">
                        <label for="inputClavePost" class="lbl-general">Clave <span class="text-danger">*</span></label>
                        <input type="text" class="input-text" id="inputClavePost" name="clave" placeholder="Ingrese una clave" required>
                        <p id="pAlterna" class="d-none p-warning my-1"></p>
                        <p id="pWarning" class="d-none p-warning"></p>
                        <p id="pValida" class="d-none p-valida"></p>
                        <a href="../files/CNC_CLAVES.xlsx" download="CNC_CLAVES.xlsx" class="btn btn-success d-none">
                            Descargar Excel de claves validas
                            <i class ="bi bi-download"></i>
                        </a>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputMaterial" class="lbl-general">Material <span class="text-danger">*</span></label>
                            <select id="inputMaterial" class="selector" name="material" required>
                                <option value="" disabled selected>Seleccionar</option>
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
                        <div class="" style="width:48%;">
                            <label for="inputProveedor" class="lbl-general">Proveedor <span class="text-danger">*</span></label>
                            <select id="inputProveedor" class="selector" name="proveedor" required>
                                <option value="" selected disabled>Seleccionar</option>
                                <option value="TRYGONAL">TRYGONAL</option>
                                <option value="CARVIFLON">CARVIFLON</option>
                                <option value="SKF">SKF</option>
                                <option value="SLM">SLM</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputMedida" class="lbl-general">Medida (interior/exterior) <span class="text-danger">*</span></label>
                            <input id="inputMedida" type="text" class="input-text"  name="medida" placeholder="Ej. 27/50" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputMaxUsable" class="lbl-general">Max. Usable <span class="text-danger">*</span></label>
                            <input id="inputMaxUsable" type="number" class="input-text"  name="max_usable" min="0" placeholder="Ej. 144" required>
                        </div>                        
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputStock" class="lbl-general">Stock <span class="text-danger">*</span></label>
                            <input id="inputStock" type="number" class="input-text"  min="0" step="0.01" name="stock" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputLotePedimento" class="lbl-general">Lote <span class="text-danger">*</span></label>
                            <input id="inputLotePedimento" type="text" class="input-text"  name="lote_pedimento" required>
                            <p id="pInvalida3" class="d-none p-invalida">Ese Lote ya existe.</p>
                        </div>                        
                    </div>

                    <button id="btnGuardar" type="button" class="btn-general">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>