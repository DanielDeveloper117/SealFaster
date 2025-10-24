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
                    <input type="hidden" id="inputEstatus" name="estatus" value="">

                    <div class="mb-3">
                        <label for="inputClavePost" class="lbl-general">Clave</label>
                        <input type="text" class="input-text" id="inputClavePost" name="clave" placeholder="Ingrese una clave" required>
                        <p id="pInvalida2" class="d-none p-warning">No se encontró la clave o el precio está pendiente. No será posible cotizar con este billet.</p>
                        <p id="pValida" class="d-none p-valida"></p>
                        <a href="../files/CNC_CLAVES.xlsx" download="CNC_CLAVES.xlsx" class="btn btn-success d-none">
                            Descargar Excel de claves validas
                            <i class ="bi bi-download"></i>
                        </a>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputMaterial" class="lbl-general">Material</label>
                            <select id="inputMaterial" class="selector" name="material" required >
                                <option disabled selected>Seleccionar</option>
                            </select>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputProveedor" class="lbl-general">Proveedor</label>
                            <select id="inputProveedor" class="selector" name="proveedor" required>
                                <option selected disabled>Seleccionar</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputMedida" class="lbl-general">Medida (interior/exterior)</label>
                            <input id="inputMedida" type="text" class="input-text"  name="medida" placeholder="Ej. 27/50" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputMaxUsable" class="lbl-general">Max. Usable</label>
                            <input id="inputMaxUsable" type="number" class="input-text"  name="max_usable" min="0" placeholder="Ej. 144" required>
                        </div>                        
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputStock" class="lbl-general">Stock</label>
                            <input id="inputStock" type="number" class="input-text"  min="0" step="0.01" name="stock" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputLotePedimento" class="lbl-general">Lote pedimento</label>
                            <input id="inputLotePedimento" type="text" class="input-text"  name="lote_pedimento" required>
                            <p id="pInvalida3" class="d-none p-invalida">Ese Lote pedimento ya existe.</p>
                        </div>                        
                    </div>

                    <!-- <div id="containerSelectorDureza" class="mb-4">
                        <label for="selectorDureza" class="lbl-general">Dureza del material</label>
                        <select id="selectorDureza" class="form-select" name="dureza" required disabled>
                            <option value="" disabled selected>Seleccionar</option>
                            <option value="duro">Polimero (material duro)</option>
                            <option value="suave">Elastomero (material suave)</option>
                        </select>
                    </div> -->

                    <button id="btnGuardar" type="button" class="btn-general">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>