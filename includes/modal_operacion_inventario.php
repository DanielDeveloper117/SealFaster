<!-- Modal para operaciones de almacen -->
<div class="modal fade" id="modalOperacionInventario" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-operacion" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="label-modal-operacion" class="modal-title">Formulario de Operación de Almacén</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formOperacionInventario" class="d-flex flex-column flex-md-row col-12 gap-3">
                    <input id="inputOrigenId" type="hidden" name="almacen_origen_id">
                    <div class="col-12 col-md-6">
                        <!-- Sección de Barras Seleccionadas -->
                        <div class="card mb-4 border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Barras Seleccionadas</h6>
                            </div>
                            <div class="card-body">
                                <div id="containerBarrasSeleccionadas" class="overflow-auto" >
                                    <ul>
                                        <li class="text-muted">No hay barras seleccionadas</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-100">
                        <!-- Sección de Tipo de Operación -->
                        <div class="mb-2">
                            <label for="inputTipoOperacion" class="form-label fw-bold">Tipo de Operación <span class="text-danger">*</span></label>
                            <select id="inputTipoOperacion" class="selector" name="tipo" required>
                                <option value="" disabled selected>Seleccionar tipo de operación</option>
                                <option value="Traspaso">Traspaso entre almacenes</option>
                                <option value="Venta">Venta</option>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un tipo de operación.</div>
                        </div>
    
                        <!-- Sección de Almacén de Destino (solo para Traspasos) -->
                        <div class="d-none mb-2" id="seccionDestinoId">
                            <label for="inputDestinoId" class="form-label fw-bold">Almacén de Destino <span class="text-danger">*</span></label>
                            <select id="inputDestinoId" class="selector" name="almacen_destino_id">
                                <option value="" disabled selected>Seleccionar almacén de destino</option>
                            </select>
                            <div class="invalid-feedback">Debe seleccionar un almacén de destino para traspasos.</div>
                        </div>
    
                        <!-- Sección de Justificación -->
                        <div class="mb-2">
                            <label for="inputJustificacionOperacion" class="form-label fw-bold">Justificación <span class="text-danger">*</span> <span class="text-secondary">(mínimo 10 caracteres)</span></label>
                            <textarea id="inputJustificacionOperacion" class="input-text" rows="4" name="justificacion" placeholder="Describa el motivo de la operación..." required minlength="10"></textarea>
                            <small class="form-text text-muted d-block mt-1 texto-contador">Caracteres: <span class="fw-bold">0</span>/10 mínimo</small>
                            <div class="invalid-feedback">La justificación debe tener al menos 10 caracteres.</div>
                        </div>
    
                        <!-- Sección de Imagen de Barras -->
                        <div class="mb-2">
                            <label for="inputFotoEnvioBarras" class="form-label fw-bold">Fotografía de Barras <span class="text-danger">*</span></label>
                            <input type="file" id="inputFotoEnvioBarras" class="input-file" accept="image/*" name="img_envio_barras" required>
                            <small class="form-text text-muted d-block mt-1">
                                Formatos permitidos: JPEG, PNG, GIF, WebP | Tamaño máximo: 5MB
                            </small>
                            <div id="previewFotoEnvioBarras" class=""></div>
                        </div>
                        
                            <!-- Sección de Imagen de Paquete -->
                        <div class="mb-2">
                            <label for="inputFotoEnvioPaquete" class="form-label fw-bold">Fotografía del Paquete <span class="text-danger">*</span></label>
                            <input type="file" id="inputFotoEnvioPaquete" class="input-file" accept="image/*" name="img_envio_paquete" required>
                            <small class="form-text text-muted d-block mt-1">
                                Formatos permitidos: JPEG, PNG, GIF, WebP | Tamaño máximo: 5MB
                            </small>
                            <div id="previewFotoEnvioPaquete" class=""></div>
                            <!-- <div class="invalid-feedback">Debe cargar una fotografía válida.</div> -->
                        </div>
              
                    </div>

                </form>
            </div>
            <div class="modal-footer border-top pt-3">
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button id="btnEnviarOperacion" type="button" class="btn-general">
                        <i class="bi bi-check-circle"></i> Realizar Operación
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #containerBarrasSeleccionadas {
        max-height: 300px;
    }

    #containerBarrasSeleccionadas ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    #containerBarrasSeleccionadas li {
        padding: 8px 12px;
        background-color: #f8f9fa;
        border:1px solid #13673f;
        border-left: 3px solid #13673f;
        margin-bottom: 8px;
        border-radius: 4px;
        font-size: 0.95rem;
    }
    
    #containerBarrasSeleccionadas li.text-muted {
        border-left-color: #ccc;
        color: #999;
    }
    
    #previewFotoEnvioBarras img, #previewFotoEnvioPaquete img {
        border: 2px solid #dee2e6;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
</style>