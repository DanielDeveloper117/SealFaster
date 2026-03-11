<!-- Modal para recibir traspasos -->
<div class="modal fade" id="modalRecepcion" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-recepcion" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="label-modal-recepcion" class="modal-title">Recibir Traspaso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formRecepcionTraspaso" enctype="multipart/form-data">
                    <input type="hidden" id="inputTraspaso_id" name="id">
                    
                    <!-- Sección de información -->
       
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="alert alert-info" role="alert">
                                        <i class="bi bi-exclamation-circle"></i>
                                        Por favor, cargue las fotografías de evidencia de recepción del paquete y las barras.
                                    </div>
                                </div>
                               
                               
                            </div>
                        </div>
                  
                    
                    <!-- Sección de fotografía del paquete recibido -->
                    <div class="mb-3">
                        <label for="inputFotoRecepcionPaquete" class="form-label fw-bold">
                            Fotografía del Paquete Recibido <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               id="inputFotoRecepcionPaquete" 
                               class="input-file" 
                               accept="image/*" 
                               name="img_recepcion_paquete" 
                               required>
                        <small class="form-text text-muted d-block mt-1">
                            Formatos permitidos: JPEG, PNG, GIF, WebP | Tamaño máximo: 5MB
                        </small>
                        <div id="previewFotoRecepcionPaquete" class=""></div>
                    </div>
                    
                    <!-- Sección de fotografía de barras recibidas -->
                    <div class="mb-3">
                        <label for="inputFotoRecepcionBarras" class="form-label fw-bold">
                            Fotografía de las Barras Recibidas <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               id="inputFotoRecepcionBarras" 
                               class="input-file" 
                               accept="image/*" 
                               name="img_recepcion_barras" 
                               required>
                        <small class="form-text text-muted d-block mt-1">
                            Formatos permitidos: JPEG, PNG, GIF, WebP | Tamaño máximo: 5MB
                        </small>
                        <div id="previewFotoRecepcionBarras" class=""></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top pt-3">
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button id="btnConfirmarRecepcion" type="button" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Confirmar Recepción
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #previewFotoRecepcionPaquete img, 
    #previewFotoRecepcionBarras img {
        max-width: 100%;
        max-height: 300px;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-top: 10px;
    }
</style>
