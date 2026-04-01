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

<script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================    
    // ============================================================
    //              ******** FUNCIONES ********
    // ============================================================

    /**
     * Elimina un traspaso del sistema
     * @param {number} traspasoId - ID del traspaso a eliminar
     */
    function eliminarTraspaso(traspasoId) {
        $.ajax({
            url: '../ajax/eliminar_traspaso.php',
            method: 'POST',
            data: { id: traspasoId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Cerrar modal si está abierto
                    const modalDetalles = bootstrap.Modal.getInstance(
                        document.getElementById('modalDetallesTraspaso')
                    );
                    if (modalDetalles) {
                        modalDetalles.hide();
                    }
                    
                    // Eliminar la fila de la tabla
                    $(`#tr_${traspasoId}`).fadeOut(300, function() {
                        $(this).remove();
                    });
                    
                    // Mostrar mensaje de éxito
                    Swal.fire(
                        'Eliminado',
                        'El traspaso ha sido eliminado correctamente.',
                        'success'
                    );
                    
                    // Recargar la tabla si usa DataTable
                    if ($.fn.DataTable.isDataTable('#traspasosTable')) {
                        $('#traspasosTable').DataTable().ajax.reload();
                    }
                } else {
                    Swal.fire('Error', response.error || 'Error al eliminar el traspaso', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al eliminar:', error);
                Swal.fire('Error', 'Error al eliminar el traspaso', 'error');
            }
        });
    } 
    /**
     * Muestra preview de las imágenes de recepción
     * @param {HTMLElement} inputElement - Input file element
     * @param {string} previewSelector - Selector del contenedor de preview
     */
    function mostrarPreviewRecepcion(inputElement, previewSelector) {
        const file = inputElement.files[0];
        const preview = $(previewSelector);
        
        if (file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.html(`<img src="${e.target.result}" alt="Preview">`);
            };
            
            reader.readAsDataURL(file);
        } else {
            preview.html('');
        }
    }
    /**
     * Valida que se haya seleccionado una imagen
     */
    function validarImagenes() {
        const archivoPaquete = $('#inputFotoRecepcionPaquete')[0].files[0];
        const inputElementPaquete = $('#inputFotoRecepcionPaquete');
        const archivo = $('#inputFotoRecepcionBarras')[0].files[0];
        const inputElement = $('#inputFotoRecepcionBarras');

        if (!archivoPaquete) {
            inputElementPaquete.addClass('is-invalid');
            return false;
        }
        
        if (archivoPaquete.size > 5242880) { // 5MB
            inputElementPaquete.addClass('is-invalid');
            Swal.fire({
                icon: 'warning',
                title: 'Archivo muy grande',
                text: 'La imagen del paquete no debe exceder 5MB'
            });
            return false;
        }      
          
        if (!archivo) {
            inputElement.addClass('is-invalid');
            return false;
        }
        
        if (archivo.size > 5242880) { // 5MB
            inputElement.addClass('is-invalid');
            Swal.fire({
                icon: 'warning',
                title: 'Archivo muy grande',
                text: 'La imagen de barras no debe exceder 5MB'
            });
            return false;
        }



        inputElement.removeClass('is-invalid').addClass('is-valid');
        inputElementPaquete.removeClass('is-invalid').addClass('is-valid');
        return true;
    }
    /**
     * Valida el formulario de recepción
     * @returns {boolean} true si el formulario es válido
     */
    function validarFormularioRecepcion() {
        // Validar imagenes
        if (!validarImagenes()) {
            Swal.fire({
                icon: 'warning',
                title: 'Falta información',
                text: 'Debe cargar fotografía de barras y del paquete'
            });
            return false;
        }
        return true;
    }
    /**
     * Limpia el formulario de recepción
     */
    function limpiarFormularioRecepcion() {
        $('#formRecepcionTraspaso')[0].reset();
        $('#previewFotoRecepcionPaquete').html('');
        $('#previewFotoRecepcionBarras').html('');
    }
    /**
     * Confirma la recepción del traspaso
     * @param {number} traspasoId - ID del traspaso a recibir
     */
    function confirmarRecepcionTraspaso(traspasoId) {
        const formData = new FormData();
        
        formData.append('id', traspasoId);
        formData.append('img_recepcion_paquete', $('#inputFotoRecepcionPaquete')[0].files[0]);
        formData.append('img_recepcion_barras', $('#inputFotoRecepcionBarras')[0].files[0]);
        
        // Mostrar loading
        const btnConfirmar = $('#btnConfirmarRecepcion');
        const textoOriginal = btnConfirmar.html();
        btnConfirmar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Procesando...');
        
        $.ajax({
            url: '../ajax/recibir_traspaso.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Cerrar modal
                    const modalRecepcion = bootstrap.Modal.getInstance(
                        document.getElementById('modalRecepcion')
                    );
                    if (modalRecepcion) {
                        modalRecepcion.hide();
                    }
                    
                    // Actualizar estatus en la tabla
                    const fila = $(`#tr_${traspasoId}`);
                    const celEstatus = fila.find('.td-estatus');
                    const celFechaRecibido = fila.find('.td-fecha_recibido');
                    
                    celEstatus.html('<div class="d-flex align-items-center gap-1">Recibido</div>');
                    celFechaRecibido.html(new Date().toLocaleDateString() + ' ' + new Date().toLocaleTimeString());
                    
                    // Ocultar botón delete-btn y btn-recibir
                    fila.find('.delete-btn').fadeOut();
                    fila.find('.btn-recibir').fadeOut();
                    
                    // Mostrar mensaje de éxito
                    Swal.fire(
                        '¡Éxito!',
                        'El traspaso ha sido recibido correctamente. Se han actualizado ' + response.barras_actualizadas + ' barra(s).',
                        'success'
                    );
                } else {
                    Swal.fire('Error', response.error || 'Error al procesar la recepción', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                let mensajeError = 'Error al procesar la recepción';
                
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    mensajeError = xhr.responseJSON.error;
                }
                
                Swal.fire('Error', mensajeError, 'error');
            },
            complete: function() {
                // Restaurar botón
                btnConfirmar.prop('disabled', false).html(textoOriginal);
            }
        });
    }

    
    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {       
        /**
         * Evento: Click en delete-btn para eliminar un traspaso
         */
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            
            const traspasoId = $(this).data('id');
            
            if (!traspasoId) {
                Swal.fire('Error', 'ID de traspaso no encontrado', 'error');
                return;
            }
            
            // Pedir confirmación al usuario
            Swal.fire({
                title: '¿Eliminar Traspaso?',
                text: 'Esta acción no se puede deshacer. Se eliminará el detalle del traspaso y se revertirán los estatus de las barras a "Disponible para cotizar".',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminarTraspaso(traspasoId);
                }
            });
        });
        /**
         * Evento: Click en btn-ver-inventario desde el modal de detalles
         */
        $(document).on('click', '#btn-ver-inventario', function(e) {
            e.preventDefault();
            
            const traspasoId = $('#modalDetallesTraspaso').data('traspaso-id');
            
            if (traspasoId) {
                window.open(`inventario.php?traspaso=${traspasoId}&oper=0`, '_blank');
            }
        });
        /**
         * Evento: Cuando se abre el modal de recepción
         */
        $('#modalRecepcion').on('show.bs.modal', function(e) {
            const traspasoId = $(e.relatedTarget).data('id');
            
            if (traspasoId) {
                $('#inputTraspaso_id').val(traspasoId);
                limpiarFormularioRecepcion();
                
                // Mostrar información del usuario receptor
                const ahora = new Date();
                const fechaFormato = ahora.toLocaleDateString('es-ES', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                
                //$('#infoFechaRecepcion').text(fechaFormato);
            }
        });
        /**
         * Evento: Preview de imágenes de recepción
         */
        $(document).on('change', '#inputFotoRecepcionPaquete', function() {
            mostrarPreviewRecepcion(this, '#previewFotoRecepcionPaquete');
        });
        
        $(document).on('change', '#inputFotoRecepcionBarras', function() {
            mostrarPreviewRecepcion(this, '#previewFotoRecepcionBarras');
        });
        /**
         * Evento: Click en btnConfirmarRecepcion
         */
        $(document).on('click', '#btnConfirmarRecepcion', function(e) {
            e.preventDefault();
            
            const traspasoId = $('#inputTraspaso_id').val();
            
            if (!traspasoId) {
                Swal.fire('Error', 'ID de traspaso no encontrado', 'error');
                return;
            }
            
            if (!validarFormularioRecepcion()) {
            return;
        }
            confirmarRecepcionTraspaso(traspasoId);
        });
    });
</script>
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
