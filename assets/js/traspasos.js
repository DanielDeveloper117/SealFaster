/**
 * Módulo de Traspasos - Gestión de acciones
 * Maneja las funcionalidades de eliminar y ver detalles de traspasos
 */

$(document).ready(function() {
    
    // ============================================================
    // EVENT LISTENERS PARA BOTONES DE ACCIONES
    // ============================================================
    
    /**
     * Evento: Click en btn-detalles para mostrar detalles del traspaso
     */
    $(document).on('click', '.btn-detalles', function(e) {
        e.preventDefault();
        
        const traspasoId = $(this).data('id');
        
        if (!traspasoId) {
            Swal.fire('Error', 'ID de traspaso no encontrado', 'error');
            return;
        }
        
        // Mostrar modal de detalles
        const modalDetalles = new bootstrap.Modal(
            document.getElementById('modalDetallesTraspaso'),
            { backdrop: 'static', keyboard: false }
        );
        modalDetalles.show();
        
        // Cargar datos del traspaso
        cargarDetallesTraspaso(traspasoId);
    });
    
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
    
    // ============================================================
    // FUNCIONES PRINCIPALES
    // ============================================================
    
    /**
     * Carga los detalles del traspaso desde el servidor
     * @param {number} traspasoId - ID del traspaso
     */
    function cargarDetallesTraspaso(traspasoId) {
        $.ajax({
            url: '../ajax/detalles_traspaso.php',
            method: 'GET',
            data: { id: traspasoId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    llenarDetallesTraspaso(response.operacion, response.barras);
                    // Guardar el ID en el modal para usarlo en el botón de inventario
                    $('#modalDetallesTraspaso').data('traspaso-id', traspasoId);
                } else {
                    Swal.fire('Error', response.error || 'Error al cargar los detalles', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar detalles:', error);
                Swal.fire('Error', 'Error al cargar los detalles del traspaso', 'error');
            }
        });
    }
    
    /**
     * Llena el modal con los detalles del traspaso
     * @param {object} operacion - Datos de la operación
     * @param {array} barras - Listado de barras
     */
    function llenarDetallesTraspaso(operacion, barras) {
        // Llenar información general
        $('#info-id').text(operacion.id || '-');
        $('#info-usuario-creador').text(operacion.usuario_creador || '-');
        $('#info-almacen-origen').text(operacion.almacen_origen || '-');
        $('#info-almacen-destino').text(operacion.almacen_destino || '-');
        $('#info-justificacion').text(operacion.justificacion || '-');
        $('#info-fecha-creacion').text(formatearFecha(operacion.created_at) || '-');
        
        // Mostrar información de recepción si recibido = 1
        if (operacion.recibido == 1) {
            $('#section-usuario-receptor').show();
            $('#section-fecha-recepcion').show();
            $('#section-evidencias-recepcion').show();
            
            $('#info-recibido').html('<span class="badge bg-success">El envío ha sido recibido</span>');
            $('#info-usuario-receptor').text(operacion.usuario_receptor || '-');
            $('#info-fecha-recepcion').text(formatearFecha(operacion.fecha_recibido) || '-');
            
            // Cargar imágenes de recepción
            if (operacion.img_recepcion_paquete) {
                $('#img-recepcion-paquete').html(`<img src="../${operacion.img_recepcion_paquete}" alt="Paquete Recibido">`);
            }
            if (operacion.img_recepcion_barras) {
                $('#img-recepcion-barras').html(`<img src="../${operacion.img_recepcion_barras}" alt="Barras Recibidas">`);
            }
        } else {
            $('#section-usuario-receptor').hide();
            $('#section-fecha-recepcion').hide();
            $('#section-evidencias-recepcion').hide();
            $('#info-recibido').html('<span class="badge bg-warning">No recibido aún por el almacén de destino</span>');
        }
        
        // Cargar imágenes de envío
        if (operacion.img_envio_barras) {
            $('#img-envio-barras').html(`<img src="../${operacion.img_envio_barras}" alt="Barras Enviadas">`);
        } else {
            $('#img-envio-barras').html('<span class="text-muted">Sin imagen</span>');
        }
        
        if (operacion.img_envio_paquete) {
            $('#img-envio-paquete').html(`<img src="../${operacion.img_envio_paquete}" alt="Paquete Enviado">`);
        } else {
            $('#img-envio-paquete').html('<span class="text-muted">Sin imagen</span>');
        }
        
        // Llenar tabla de barras
        llenarTablaBarras(barras);
        
        // Actualizar cantidad en la pestaña
        $('#cantidad-barras').text(barras.length);
    }
    
    /**
     * Llena la tabla de barras con los datos del traspaso
     * @param {array} barras - Listado de barras
     */
    function llenarTablaBarras(barras) {
        const tbody = $('#tbody-barras-traspaso');
        tbody.empty();
        
        if (barras.length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center text-muted">No hay barras asociadas a este traspaso</td></tr>');
            return;
        }
        
        barras.forEach(barra => {
            const fila = `
                <tr>
                    <td>${barra.Clave || '-'}</td>
                    <td>${barra.lote_pedimento || '-'}</td>
                    <td>${barra.Medida || '-'}</td>
                    <td>${barra.material || '-'}</td>
                    <td>${barra.proveedor || '-'}</td>
                    <td>${barra.stock || '-'}</td>
                </tr>
            `;
            tbody.append(fila);
        });
    }
    
    /**
     * Obtiene el color de badge según el estatus de la barra
     * @param {string} estatus - Estatus de la barra
     * @returns {string} Clase de color para the badge
     */
    function obtenerColorEstatus(estatus) {
        const colores = {
            'Disponible para cotizar': 'bg-info',
            'Traspaso': 'bg-warning',
            'Venta': 'bg-secondary',
            'Archivado': 'bg-danger',
            'default': 'bg-light text-dark'
        };
        return colores[estatus] || colores['default'];
    }
    
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
     * Formatea una fecha al formato DD/MM/YYYY HH:MM:SS
     * @param {string} fecha - Fecha en formato ISO o MySQL
     * @returns {string} Fecha formateada
     */
    function formatearFecha(fecha) {
        if (!fecha) return '-';
        
        const date = new Date(fecha);
        if (isNaN(date.getTime())) return fecha;
        
        const dia = String(date.getDate()).padStart(2, '0');
        const mes = String(date.getMonth() + 1).padStart(2, '0');
        const año = date.getFullYear();
        const horas = String(date.getHours()).padStart(2, '0');
        const minutos = String(date.getMinutes()).padStart(2, '0');
        const segundos = String(date.getSeconds()).padStart(2, '0');
        
        return `${dia}/${mes}/${año} ${horas}:${minutos}:${segundos}`;
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
});