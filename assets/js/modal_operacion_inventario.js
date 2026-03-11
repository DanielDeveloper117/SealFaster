/**
 * Módulo Modal Operación de Inventario
 * Gestiona la lógica completa del modal de operaciones (traspasos y ventas)
 */

// ============================================================
// VARIABLES GLOBALES
// ============================================================
let almacenesDisponibles = [];
let operacionEnProceso = false;


var urlParams = new URLSearchParams(window.location.search);

// Cargar valores en el formulario
window.ALMACEN_ORIGEN = urlParams.get('origen') || '';
console.log(window.ALMACEN_ORIGEN);

// ============================================================
// FUNCIONES AUXILIARES
// ============================================================

/**
 * Carga los almacenes disponibles desde el backend
 */
function cargarAlmacenes() {
    $.ajax({
        url: '../ajax/ajax_almacenes.php',
        type: 'GET',
        data: {
            excluir: window.ALMACEN_ORIGEN
        },
        dataType: 'json',
        success: function(data) {
            if (data.success && data.almacenes) {
                almacenesDisponibles = data.almacenes;
            } else {
                console.error('Error cargando almacenes:', data.message);
                sweetAlertResponse("warning", "Ocurrió un problema", data.message, "none");
            }
        },
        error: function(xhr, status, error) {
            // Aquí 'data' NO existe. Debes usar los argumentos de esta función.
            console.error('Error de red/servidor:', error);
            
            // Intentamos obtener un mensaje del servidor si es que mandó algo
            let mensajeError = "No se pudo conectar con el servidor.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensajeError = xhr.responseJSON.message;
            }
            sweetAlertResponse("warning", "Ocurrió un problema" , mensajeError, "none");
        }
    });
}

/**
 * Obtiene los datos de una barra del DOM basándose en el ID
 * Funciona incluso con DataTables activo
 */
function obtenerDatosBarra(id) {
    // Obtener el checkbox
    const checkbox = $(`input.btn-check-cute[val="${id}"]`);
    if (checkbox.length === 0) return null;
    
    // Obtener la fila
    const fila = checkbox.closest('tr');
    if (fila.length === 0) return null;
    
    // Extraer datos de los data attributes del checkbox
    const lpDelAtributo = checkbox.attr('data-lp') || '';
    
    // Extraer datos de las celdas de la tabla
    // Estructura: | selector | Clave | Lote | Medida | Estatus | Material | Proveedor | Max Usable | Stock | ...
    const celdas = fila.find('td');
    
    try {
        const clave = celdas.eq(1).text().trim();        // Índice 1: Clave
        const lote = celdas.eq(2).text().trim();         // Índice 2: Lote/Pedimento
        const medida = celdas.eq(3).text().trim();       // Índice 3: Medida
        // Índice 4: Estatus (lo saltamos)
        const material = celdas.eq(5).text().trim();     // Índice 5: Material
        const proveedor = celdas.eq(6).text().trim();    // Índice 6: Proveedor
        
        return {
            id: id,
            clave: clave || 'S/D',
            medida: medida || 'S/D',
            material: material || 'S/D',
            proveedor: proveedor || 'S/D',
            lote_pedimento: lpDelAtributo || lote || 'S/D'
        };
    } catch (e) {
        console.error('Error obteniendo datos de barra:', e);
        return null;
    }
}

/**
 * Renderiza los registros seleccionados en el contenedor
 */
function renderizarBarrasSeleccionadas() {
    const selectedIds = JSON.parse(sessionStorage.getItem('inventarioOperacionIds') || '[]');
    const container = $('#containerBarrasSeleccionadas');
    
    if (selectedIds.length === 0) {
        container.html('<ul><li class="text-muted">No hay barras seleccionadas</li></ul>');
        return;
    }
    
    let html = '<ul>';
    selectedIds.forEach(id => {
        const datos = obtenerDatosBarra(id);
        if (datos) {
            html += `<li class="barra-seleccionada">
                <small><strong>${datos.clave}</strong> ${datos.lote_pedimento} ${datos.medida} ${datos.material} ${datos.proveedor}</small>
            </li>`;
        }
    });
    html += '</ul>';
    
    container.html(html);
}

/**
 * Actualiza la visibilidad del selector de almacén de destino
 */
function actualizarVisibilidadAlmacenDestino() {
    const tipoOperacion = $('#inputTipoOperacion').val();
    const seccionDestino = $('#seccionDestinoId');
    
    if (tipoOperacion === 'Venta') {
        seccionDestino.addClass('d-none');
        $('#inputDestinoId').prop('required', false).val('');
    } else if (tipoOperacion === 'Traspaso') {
        seccionDestino.removeClass('d-none');
        $('#inputDestinoId').prop('required', true);
    }
}

/**
 * Llena el selector de almacen destino con los almacenes disponibles
 */
function llenarSelectorAlmacenes() {
    const selector = $('#inputDestinoId');      
    selector.html("");  
    selector.append(`<option value="" disabled selected>Seleccionar almacén de destino</option>`);
    if (almacenesDisponibles.length > 0) {
        almacenesDisponibles.forEach(almacen => {
            selector.append(`<option value="${almacen.id}">${almacen.almacen} - ${almacen.descripcion}</option>`);
        });
    }
}

/**
 * Limpia el formulario del modal
 */
function limpiarFormularioModal() {
    $('#formOperacionInventario')[0].reset();
    $('#inputTipoOperacion').val('').trigger('change');
    $('#inputDestinoId').val('');
    $('#inputJustificacionOperacion').val('');
    $('#inputFotoEnvioBarras').val('');
    $('#inputFotoEnvioPaquete').val('');
    $('#previewFotoEnvioBarras').html('');
    $('#previewFotoEnvioPaquete').html('');
    $('#containerBarrasSeleccionadas').html('<ul><li class="text-muted">No hay barras seleccionadas</li></ul>');
    operacionEnProceso = false;
}

/**
 * Valida que la justificación tenga al menos 10 caracteres
 */
function validarJustificacion() {
    const justificacion = $('#inputJustificacionOperacion').val().trim();
    const inputElement = $('#inputJustificacionOperacion');
    
    if (justificacion.length < 10) {
        inputElement.addClass('is-invalid');
        return false;
    } else {
        inputElement.removeClass('is-invalid').addClass('is-valid');
        return true;
    }
}

/**
 * Valida que se haya seleccionado una imagen
 */
function validarImagenes() {
    const archivo = $('#inputFotoEnvioBarras')[0].files[0];
    const inputElement = $('#inputFotoEnvioBarras');
    const archivoPaquete = $('#inputFotoEnvioPaquete')[0].files[0];
    const inputElementPaquete = $('#inputFotoEnvioPaquete');
    
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

    inputElement.removeClass('is-invalid').addClass('is-valid');
    inputElementPaquete.removeClass('is-invalid').addClass('is-valid');
    return true;
}

/**
 * Valida el formulario completo antes de enviar
 */
function validarFormulario() {
    let esValido = true;
    
    // Validar tipo de operación
    if (!$('#inputTipoOperacion').val()) {
        Swal.fire({
            icon: 'warning',
            title: 'Falta información',
            text: 'Debe seleccionar un tipo de operación'
        });
        return false;
    }
    
    // Validar almacén de destino si es traspaso
    if ($('#inputTipoOperacion').val() === 'Traspaso') {
        if (!$('#inputDestinoId').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Falta información',
                text: 'Debe seleccionar un almacén de destino para traspasos'
            });
            return false;
        }
    }
    
    // Validar justificación
    if (!validarJustificacion()) {
        Swal.fire({
            icon: 'warning',
            title: 'Aviso',
            text: 'La justificación debe tener al menos 10 caracteres'
        });
        return false;
    }
    
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
 * Muestra preview de la imagen seleccionada
 */
function mostrarPreviewImagen() {
    const fileBarras = $('#inputFotoEnvioBarras')[0].files[0];
    const previewBarras = $('#previewFotoEnvioBarras');
    const filePaquete = $('#inputFotoEnvioPaquete')[0].files[0];
    const previewPaquete = $('#previewFotoEnvioPaquete');

    // Manejo individual para Barras
    if (fileBarras) {
        const readerBarras = new FileReader();
        readerBarras.onload = (e) => {
            previewBarras.html(`<img src="${e.target.result}" class="img-fluid rounded mt-2" style="max-height: 200px;">`);
        };
        readerBarras.readAsDataURL(fileBarras);
    } else {
        previewBarras.html('');
    }

    // Manejo individual para Paquete
    if (filePaquete) {
        const readerPaquete = new FileReader();
        readerPaquete.onload = (e) => {
            previewPaquete.html(`<img src="${e.target.result}" class="img-fluid rounded mt-2" style="max-height: 200px;">`);
        };
        readerPaquete.readAsDataURL(filePaquete);
    } else {
        previewPaquete.html('');
    }
}

/**
 * Envía la operación al servidor
 */
function enviarOperacion() {
    if (!validarFormulario()) {
        return;
    }
    
    if (operacionEnProceso) {
        Swal.fire({
            icon: 'info',
            title: 'Procesando',
            text: 'La operación se está procesando. Por favor, espere.'
        });
        return;
    }
    
    operacionEnProceso = true;
    
    const selectedIds = JSON.parse(sessionStorage.getItem('inventarioOperacionIds') || '[]');
    const formData = new FormData();
    
    formData.append('tipo', $('#inputTipoOperacion').val());
    formData.append('almacen_origen_id', $('#inputOrigenId').val());
    if ($('#inputTipoOperacion').val() === 'Traspaso') {
        formData.append('almacen_destino_id', $('#inputDestinoId').val());
    } else {
        formData.append('almacen_destino_id', '0');
    }
    formData.append('justificacion', $('#inputJustificacionOperacion').val());
    formData.append('img_envio_barras', $('#inputFotoEnvioBarras')[0].files[0]);
    formData.append('img_envio_paquete', $('#inputFotoEnvioPaquete')[0].files[0]);
    
    // Agregar IDs como array
    selectedIds.forEach(id => {
        formData.append('ids[]', id);
    });
    
    $.ajax({
        url: '../ajax/guardar_operacion_inventario.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: `${response.message}\n\n ${response.tipo},\n ${response.cantidad_barras} barra(s) procesada(s).`,
                    confirmButtonText: 'Entendido'
                }).then(() => {
                    // Cerrar modal
                    bootstrap.Modal.getInstance(document.getElementById('modalOperacionInventario')).hide();
                    // Recargar tabla
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Aviso',
                    text: response.message
                });
            }
            operacionEnProceso = false;
        },
        error: function(xhr) {
            let mensaje = 'Error al procesar la operación.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: mensaje
            });
            operacionEnProceso = false;
        }
    });
}
    
// ============================================================
// EVENTOS DEL DOM
// ============================================================
$(document).ready(function() {  
    /**
     * Cuando se abre el modal, cargar datos iniciales
     */
    $('#modalOperacionInventario').on('show.bs.modal', function(e) {
        // Cargar datos del sessionStorage
        const selectedIds = JSON.parse(sessionStorage.getItem('inventarioOperacionIds') || '[]');
        
        if (selectedIds.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Sin selección',
                text: 'No hay barras seleccionadas.'
            });
            return;
        }
        
        // Renderizar barras seleccionadas
        renderizarBarrasSeleccionadas();
        
        // Cargar almacenes dinámicamente si aún no están cargados
        if (almacenesDisponibles.length === 0) {
            cargarAlmacenes();
            // Esperar a que se carguen
            setTimeout(() => {
                llenarSelectorAlmacenes();
            }, 500);
        } else {
            llenarSelectorAlmacenes();
        }
    });
    
    /**
     * Cambio en tipo de operación
     */
    $('#inputTipoOperacion').on('change', function() {
        actualizarVisibilidadAlmacenDestino();
    });
    
    /**
     * Preview de imagen
     */
    $('#inputFotoEnvioBarras, #inputFotoEnvioPaquete').on('change', function() {
        mostrarPreviewImagen();
        validarImagenes();
    });
    
    /**
     * Validación en tiempo real de justificación
     */
    $('#inputJustificacionOperacion').on('keyup change input', function() {
        const longitud = $(this).val().trim().length;
        
        // Buscar el contador en la estructura del DOM
        const contador = $(this).next('small.texto-contador');
        if (contador.length > 0) {
            contador.find('span.fw-bold').text(longitud);
        }
        
        // Aplicar estilos de validación
        if (longitud >= 10) {
            $(this).removeClass('is-invalid').addClass('is-valid');
        } else {
            $(this).removeClass('is-valid');
            if (longitud > 0) {
                $(this).addClass('is-invalid');
            }
        }
    });
    
    /**
     * Click en botón enviar operación
     */
    $('#btnEnviarOperacion').on('click', function(e) {
        e.preventDefault();
        enviarOperacion();
    });
    
    /**
     * Cuando se cierra el modal sin confirmar
     */
    $('#modalOperacionInventario').on('hide.bs.modal', function() {
        // No resetear aquí ya que el usuario podría necesitar volver a entrar
    });
    
    /**
     * Cargar almacenes al inicializar el DOM
     */
    cargarAlmacenes();
});
