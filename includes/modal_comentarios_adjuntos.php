<style>
    /* Estilos para el modal */
.border-dashed {
    border: 2px dashed #dee2e6;
    transition: all 0.3s ease;
}

.border-dashed:hover {
    border-color: #55AD9B;
    background-color: #f8f9fa;
}

.border-solid {
    border: 1px solid #dee2e6;
}

.cursor-pointer {
    cursor: pointer;
}

/* Estilos para los registros existentes */
.registro-comentario {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 1rem;
    background-color: #f8f9fa;
}

/* Loading state para el botón */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Mejorar la visualización de archivos */
.archivo-adjunto {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem;
    color: #0d6efd;
    background-color: #e9ecef;
    border-radius: 0.375rem;
    text-decoration: none;
    border: 1px solid #dee2e6;
}

.archivo-adjunto:hover {
    text-decoration: underline;
    background-color: #dee2e6;
}

.input-file-simple {
    width: 100%;
    text-align: center;
    background-color: #fff;
    padding: 12px;
    border: none;
    border-radius: 5px;
    color: #000;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 0px 1px 1px #55AD9B;
}
.input-file-simple:hover {
    background-color: #95D2B3; 
    color: #fff;
}
#modalComentariosAdjuntos .modal-body textarea, #modalComentariosAdjuntos .modal-body select  {
    border: solid 1px #55AD9B;
}
#archivoSeleccionado{
    border: solid 1px #55AD9B;
    border-radius: 5px;
    background-color: #e9f7f0;
}
/* Estilos para el contador de caracteres */
.text-warning {
    color: #ffc107 !important;
    font-weight: bold;
}

.text-danger {
    color: #dc3545 !important;
    font-weight: bold;
}

.form-text {
    font-size: 0.875em;
    margin-top: 0.25rem;
}

/* Estilo cuando está cerca del límite */
#inputComentario.warning {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

/* Estilo cuando excede el límite */
#inputComentario.danger {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}
.comentarios-wrapper {
    position: relative;
    display: inline-block;
}

.badge-comentarios {
    position: absolute;
    top: -6px;
    right: -6px;
    background-color: #0d6efd; /* azul bootstrap */
    color: #fff;
    font-size: 11px;
    font-weight: 600;
    min-width: 18px;
    height: 18px;
    line-height: 18px;
    text-align: center;
    border-radius: 50%;
    padding: 0 4px;
    pointer-events: none;
}

</style>
<!-- Modal para comentarios y adjuntos -->
<div class="modal fade" id="modalComentariosAdjuntos" tabindex="-1" aria-labelledby="modalComentariosAdjuntosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalComentariosAdjuntosLabel">
                    Comentarios y archivos adjuntos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Contenedor principal -->
                <div id="contenedorComentariosAdjuntos">
                    <!-- Los registros existentes se cargarán aquí -->
                </div>
                <div id="containerNoHay" class="text-center mb-2">
                    <small class="fst-italic text-secondary">No hay comentarios adjuntos</small>
                </div>
                <!-- Formulario para agregar nuevo (inicialmente oculto) -->
                <?php if($tipo_usuario != "CNC" && $tipo_usuario != "Inventarios"): ?>
                <div id="formularioNuevo" class="d-none">
                    <div class="card border-secondary-subtle border-2 mb-3">
                        <div class="card-body">
                            <form id="formComentarioAdjunto">
                                <input type="hidden" id="inputIdCotizacionAdicion" name="id_cotizacion">
                                <!-- Selector para origen "requi" (inicialmente oculto) -->
                                <div id="selectorCotizacionContainer" class="mb-3 d-none">
                                    <label for="selectCotizacionRequisicion" class="form-label fw-bold">Seleccionar cotización *</label>
                                    <select class="form-select" id="selectCotizacionRequisicion" name="id_cotizacion_requi" required>
                                        <option value="" selected disabled>Selecciona una cotización...</option>
                                        <!-- Las opciones se llenarán dinámicamente -->
                                    </select>
                                    <div class="form-text">Selecciona a qué cotización de la requisición pertenece este comentario</div>
                                </div>
                                <div class="mb-3">
                                    <label for="inputComentario" class="form-label fw-bold">Comentario *</label>
                                    <textarea class="form-control" id="inputComentario" name="comentario" rows="2" 
                                            maxlength="98" required></textarea>
                                    <div class="form-text text-end">
                                        <span id="contadorCaracteres">0/98</span> caracteres
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Archivo adjunto</label>
                                    <div id="areaAdjunto">
                                        <button type="button" class="input-file-simple w-100" id="btnSeleccionarArchivo">
                                            <i class="bi bi-paperclip"></i> Añadir adjunto
                                        </button>
                                        
                                        <input type="file" class="d-none" id="inputAdjunto" name="nombre_archivo" required>
                                        <div id="archivoSeleccionado" class="d-none mt-2 p-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span id="nombreArchivo" class="text-secondary"></span>
                                                <button type="button" class="btn btn-sm btn-outline-danger" id="btnQuitarArchivo">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn-eliminar" id="btnCancelarFormulario">Cancelar</button>
                                    <button type="button" class="btn-general" id="btnAgregarRegistro">Agregar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Botón para agregar nuevo comentario -->
                <div id="botonAgregarInicial" class="text-center">
                    <div class="border-dashed p-4 rounded cursor-pointer" id="btnAgregarComentario">
                        <i class="bi bi-plus-lg fs-1 text-muted"></i>
                        <div class="text-muted">Agregar</div>
                    </div>
                </div>
                <?php endif;?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-general" data-bs-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>