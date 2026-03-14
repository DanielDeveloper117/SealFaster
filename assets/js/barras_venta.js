/**
 * Módulo de Ventas - Gestión de acciones
 * Maneja las funcionalidades de eliminar y ver detalles de ventas
 */

$(document).ready(function() {
    
    // ============================================================
    // EVENT LISTENERS PARA BOTONES DE ACCIONES
    // ============================================================
    
    /**
     * Evento: Click en btn-detalles para mostrar detalles de venta
     */
    $(document).on('click', '.btn-detalles', function(e) {
        e.preventDefault();
        
        const ventaId = $(this).data('id');
        
        if (!ventaId) {
            Swal.fire('Error', 'ID de venta no encontrado', 'error');
            return;
        }
        
        // Mostrar modal de detalles
        const modalDetalles = new bootstrap.Modal(
            document.getElementById('modalDetallesVenta'),
            { backdrop: 'static', keyboard: false }
        );
        modalDetalles.show();
        
        // Cargar datos de venta
        cargarDetallesVenta(ventaId);
    });
    
    /**
     * Evento: Click en delete-btn para eliminar una venta
     */
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        
        const ventaId = $(this).data('id');
        
        if (!ventaId) {
            Swal.fire('Error', 'ID de venta no encontrado', 'error');
            return;
        }
        
        // Pedir confirmación al usuario
        Swal.fire({
            title: '¿Eliminar venta?',
            text: 'Esta acción no se puede deshacer. Se eliminará el detalle del venta y se revertirán los estatus de las barras a "Disponible para cotizar".',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarVenta(ventaId);
            }
        });
    });
    
    /**
     * Evento: Click en btn-ver-inventario desde el modal de detalles
     */
    $(document).on('click', '#btn-ver-inventario', function(e) {
        e.preventDefault();
        
        const ventaId = $('#modalDetallesVenta').data('venta-id');
        
        if (ventaId) {
            window.open(`inventario.php?venta=${ventaId}&oper=0`, '_blank');
        }
    });
    
    // ============================================================
    // FUNCIONES PRINCIPALES
    // ============================================================
    
    /**
     * Carga los detalles del venta desde el servidor
     * @param {number} ventaId - ID de venta
     */
    function cargarDetallesVenta(ventaId) {
        $.ajax({
            url: '../ajax/detalles_barras_venta.php',
            method: 'GET',
            data: { id: ventaId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    llenarDetallesVenta(response.operacion, response.barras);
                    // Guardar el ID en el modal para usarlo en el botón de inventario
                    $('#modalDetallesVenta').data('venta-id', ventaId);
                } else {
                    Swal.fire('Error', response.error || 'Error al cargar los detalles', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar detalles:', error);
                Swal.fire('Error', 'Error al cargar los detalles de venta', 'error');
            }
        });
    }
    
    /**
     * Llena el modal con los detalles de venta
     * @param {object} operacion - Datos de la operación
     * @param {array} barras - Listado de barras
     */
    function llenarDetallesVenta(operacion, barras) {
        // Llenar información general
        $('#info-id').text(operacion.id || '-');
        $('#info-usuario-creador').text(operacion.usuario_creador || '-');
        $('#info-almacen-origen').text(operacion.almacen_origen || '-');
        $('#info-justificacion').text(operacion.justificacion || '-');
        $('#info-fecha-creacion').text(formatearFecha(operacion.created_at) || '-');
        
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
     * Llena la tabla de barras con los datos de venta
     * @param {array} barras - Listado de barras
     */
    function llenarTablaBarras(barras) {
        const tbody = $('#tbody-barras-venta');
        tbody.empty();
        
        if (barras.length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center text-muted">No hay barras asociadas a esta venta</td></tr>');
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
     * Elimina un venta del sistema
     * @param {number} ventaId - ID del venta a eliminar
     */
    function eliminarVenta(ventaId) {
        $.ajax({
            url: '../ajax/eliminar_venta_barras.php',
            method: 'POST',
            data: { id: ventaId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Cerrar modal si está abierto
                    const modalDetalles = bootstrap.Modal.getInstance(
                        document.getElementById('modalDetallesVenta')
                    );
                    if (modalDetalles) {
                        modalDetalles.hide();
                    }
                    
                    // Eliminar la fila de la tabla
                    $(`#tr_${ventaId}`).fadeOut(300, function() {
                        $(this).remove();
                    });
                    
                    // Mostrar mensaje de éxito
                    Swal.fire(
                        'Eliminado',
                        'La venta ha sido eliminada correctamente.',
                        'success'
                    );
                    
                    // Recargar la tabla si usa DataTable
                    if ($.fn.DataTable.isDataTable('#ventasTable')) {
                        $('#ventasTable').DataTable().ajax.reload();
                    }
                } else {
                    Swal.fire('Error', response.error || 'Error al eliminar la venta', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al eliminar:', error);
                Swal.fire('Error', 'Error al eliminar la venta', 'error');
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


});