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
                            <option value="" disabled selected>Seleccionar un almacén...</option>
                        </select>
                    </div>    
                    <div class="mb-3">
                        <label for="inputClavePost" class="lbl-general">Clave <span class="text-danger">*</span></label>
                        <input type="text" class="input-text" id="inputClavePost" name="clave" placeholder="Ingrese una clave" required>
                        <p id="pAlterna" class="d-none my-1"></p>
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
                            <select id="inputMaterial" class="selector selectorMaterialesInventario" name="material" required>
                                <option value="" disabled selected>Seleccionar...</option>
                            </select>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputProveedor" class="lbl-general">Proveedor <span class="text-danger">*</span></label>
                            <select id="inputProveedor" class="selector selectorProveedoresInventario" name="proveedor" required>
                                <option value="" selected disabled>Seleccionar...</option>
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
<!-- //////////////////////////MODAL: FORMULARIO SOLICITAR ARCHIVAR BARRA /////////////////////// -->
<div class="modal fade" id="modalSolicitarArchivar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Solicitar archivar barra a dirección</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Describa la razón por la cual desea archivar la barra: <strong></strong></p>
                <form id="formSolicitarArchivar" enctype="multipart/form-data">
                    <input id="inputIdBarra" type="hidden">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:100%;">
                            <label for="inputJustificacionSolicitarArchivar" class="lbl-general">Justificación *</label>
                            <textarea id="inputJustificacionSolicitarArchivar" class="form-control" rows="3" placeholder="Ingrese la justificación..." required></textarea>
                        </div>  
                    </div>
                    <div class="mb-3">
                        <label for="inputFotoArchivar" class="lbl-general">Fotografía de la barra *</label>
                        <input type="file" id="inputFotoArchivar" class="form-control" accept="image/*" capture="environment" required>
                        <small class="form-text text-muted">Suba una foto que muestre el estado actual de la barra (máx. 5MB)</small>
                        <div id="previewFotoArchivar" class="mt-2"></div>
                    </div>
                    <button id="btnContinuarSolicitarArchivar" type="button" class="btn-general">Continuar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal para ver justificación y foto -->
<div class="modal fade" id="modalVerJustificacionFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Solicitud de archivado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Justificación:</h6>
                        <div id="justificacionTexto" class="border rounded p-3 mb-3" style="min-height: 150px; max-height: 300px; overflow-y: auto;">
                            <!-- La justificación se insertará aquí -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>Fotografía de la barra:</h6>
                        <div id="fotoContenedor" class="text-center">
                            <div id="sinFoto" class="d-none">
                                <i class="bi bi-image text-muted" style="font-size: 5rem;"></i>
                                <p class="text-muted mt-2">No hay fotografía disponible</p>
                            </div>
                            <img id="fotoBarra" src="" alt="Foto de la barra" 
                                 class="img-fluid rounded border" 
                                 style="max-height: 300px; display: none;">
                        </div>
                        <div id="infoFoto" class="mt-2 small text-muted">
                            <!-- Información de la foto se insertará aquí -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- ///////////////////////MODAL CONFIRMAR AUTORIZAR ARCHIVADO BARRA /////////////////////// -->
<div class="modal fade" id="modalAutorizarBarraArchivada" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-autorizar-barra-archivada" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="label-modal-autorizar-barra-archivada">Confirmar autorización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Desea continuar con la autorización para archivar esta barra?</p>
                <form id="formAutorizarBarraArchivada">
                    <input id="inputIdBarraArchivada" type="hidden" name="id"  value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnConfirmAutorizarBarraArchivada" class="btn-auth">Si, continuar</button>
                <button type="button" id="btnCancelAutorizarBarraArchivada" class="btn-cancel" data-bs-dismiss="modal">No, cancelar</button>
            </div>
        </div>
    </div>
</div>
<script src="<?= controlCache('../assets/js/dynamic_selectors.js'); ?>"></script>