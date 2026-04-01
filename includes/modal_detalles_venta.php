<!-- Modal para detalles del venta -->
<div class="modal fade" id="modalDetallesVenta" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-detalles-venta" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl mdv-dialog">
        <div class="modal-content mdv-content">

            <!-- HEADER -->
            <div class="modal-header">
                <h5 id="label-modal-operacion" class="modal-title">Detalles de venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body mdv-body">

                <!-- TABS NAV -->
                <ul class="nav mdv-tabs" id="tabsDetallesVenta" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="mdv-tab-btn active" id="tab-detalles-venta"
                            data-bs-toggle="tab" data-bs-target="#content-detalles-venta"
                            type="button" role="tab" aria-controls="content-detalles-venta" aria-selected="true">
                            Detalles
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="mdv-tab-btn" id="tab-barras-venta"
                            data-bs-toggle="tab" data-bs-target="#content-barras-venta"
                            type="button" role="tab" aria-controls="content-barras-venta" aria-selected="false">
                            Barras &nbsp;<span class="mdv-tab-badge" id="cantidad-barras">0</span>
                        </button>
                    </li>
                </ul>

                <!-- TAB CONTENT -->
                <div class="tab-content mdv-tab-content" id="contentDetallesVenta">

                    <!-- PESTAÑA: DETALLES -->
                    <div class="tab-pane fade show active" id="content-detalles-venta" role="tabpanel" aria-labelledby="tab-detalles-venta">

                        <!-- BLOQUE: Información General -->
                        <div class="mdv-section">
                            <div class="mdv-section-header mdv-section-header--blue">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                                </svg>
                                Información General
                            </div>
                            <div class="mdv-section-body">
                                <div class="mdv-grid">
                                    <div class="mdv-field">
                                        <span class="mdv-field-label">ID del venta</span>
                                        <p id="info-id" class="mdv-field-value">-</p>
                                    </div>
                                    <div class="mdv-field">
                                        <span class="mdv-field-label">Usuario Creador</span>
                                        <p id="info-usuario-creador" class="mdv-field-value">-</p>
                                    </div>
                                    <div class="mdv-field">
                                        <span class="mdv-field-label">Almacén de Origen</span>
                                        <p id="info-almacen-origen" class="mdv-field-value">-</p>
                                    </div>
                                    <div class="mdv-field">
                                        <span class="mdv-field-label">Fecha de Creación</span>
                                        <p id="info-fecha-creacion" class="mdv-field-value">-</p>
                                    </div>
                                    <div class="mdv-field mdv-field--full">
                                        <span class="mdv-field-label">Justificación</span>
                                        <p id="info-justificacion" class="mdv-field-value">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BLOQUE: Evidencias -->
                        <div class="mdv-section">
                            <div class="mdv-section-header mdv-section-header--cyan">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>
                                </svg>
                                Evidencias
                            </div>
                            <div class="mdv-section-body">
                                <div class="mdv-images-grid">
                                    <div class="mdv-image-block">
                                        <span class="mdv-field-label">Fotografía de Barras</span>
                                        <div id="img-envio-barras" class="mdv-img-viewer">
                                            <span class="mdv-img-placeholder">
                                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                                    <polyline points="21 15 16 10 5 21"/>
                                                </svg>
                                                Sin imagen
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mdv-image-block">
                                        <span class="mdv-field-label">Fotografía del Paquete</span>
                                        <div id="img-envio-paquete" class="mdv-img-viewer">
                                            <span class="mdv-img-placeholder">
                                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                                    <polyline points="21 15 16 10 5 21"/>
                                                </svg>
                                                Sin imagen
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /PESTAÑA DETALLES -->

                    <!-- PESTAÑA: BARRAS -->
                    <div class="tab-pane fade" id="content-barras-venta" role="tabpanel" aria-labelledby="tab-barras-venta">
                        <div class="mdv-barras-toolbar mb-1 justify-content-end mdv-section pt-2 pb-2">
                            <button id="btn-ver-inventario" type="button" class="btn-general w-auto">
                                Ver en Inventario CNC
                            </button>
                        </div>
                        <div class="mdv-table-wrapper mdv-section">
                            <table class=" table-striped table-bordered table-sm mdv-table" id="tabla-barras-venta">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Lote</th>
                                        <th>Medida</th>
                                        <th>Material</th>
                                        <th>Proveedor</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-barras-venta">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Cargando barras...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div><!-- /PESTAÑA BARRAS -->

                </div><!-- /tab-content -->
            </div><!-- /modal-body -->

            <div class="modal-footer border-top pt-3">
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                   
                </div>
            </div>

        </div><!-- /modal-content -->
    </div><!-- /modal-dialog -->
</div>

<script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    // ============================================================
    //              ******** FUNCIONES ********
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
    

    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
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
    });
</script>

<style>
    /* ================================================================
        Estilo refinado industrial
    ================================================================ */


    /* Variables */
    :root {
        --mdv-bg:           #f0f8f0;
        --mdv-surface:      #fff;
        --mdv-surface-2:    #e6f2e6;
        --mdv-surface-3:    #d1e7d1;
        --mdv-border:       rgba(255,255,255,0.07);
        --mdv-border-hover: rgba(255,255,255,0.14);
        --mdv-text-primary: #e8eaf0;
        --mdv-text-muted:   #5a6070;
        --mdv-text-label:   #7c8494;
        --mdv-accent-blue:  #3b82f6;
        --mdv-accent-cyan:  #06b6d4;
        --mdv-accent-green: #22c55e;
        --mdv-accent-amber: #f59e0b;
        --mdv-radius:       10px;
        --mdv-radius-sm:    6px;
        --mdv-shadow:       0 24px 64px rgba(0,0,0,0.6);
    }

    /* Dialog */
    .mdv-dialog {
        max-width: 960px;
    }

    /* Content */
    .mdv-content {
        background: var(--mdv-surface);
        border: 1px solid var(--mdv-border);
        border-radius: 14px !important;
        box-shadow: var(--mdv-shadow);
        overflow: hidden;
    }


    /* ---- BODY ---- */
    .mdv-body {
        background: var(--mdv-surface);
        padding: 1.5rem;
    }

    /* ---- TABS ---- */
    .mdv-tabs {
        display: flex;
        gap: 4px;
        padding-bottom: 0;
        list-style: none;
        padding-left: 0;
    }

    .mdv-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0.55rem 1rem;
        font-size: 16px;
        font-weight: 500;
        color: #0a0f0a;
        background: transparent;
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: var(--mdv-radius-sm) var(--mdv-radius-sm) 0 0;
        cursor: pointer;
        transition: color 0.15s, background 0.15s, border-color 0.15s;
        position: relative;
        bottom: -1px;
        white-space: nowrap;
    }

    .mdv-tab-btn:hover {
        color: #55ad9b;
        background: var(--mdv-surface-2);
    }

    .mdv-tab-btn.active {
        color: #55ad9b;
        font-weight: 700;
        background: var(--mdv-surface);
        border-color: var(--mdv-border);
        border-bottom-color: var(--mdv-surface);
    }

    .mdv-tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        font-size: 14px;
        font-weight: 600;
        background: rgba(85, 173, 155, 0.1);
        color: #55ad9b;
        border-radius: 20px;
        border: 1px solid #55ad9b;
    }

    /* ---- SECCIONES ---- */
    .mdv-section {
        background: #fff;
        border: 1px solid var(--mdv-border);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    .mdv-section:last-child {
        margin-bottom: 0;
    }

    .mdv-section-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.7rem 1.1rem;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        background-color: #607d8b;
        border-bottom: 1px solid rgba(59,130,246,0.15);
        color: #fff;
    }

    .mdv-section-body {
        padding: 1.25rem 1.1rem;
    }

    /* ---- GRID DE CAMPOS ---- */
    .mdv-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
    }

    .mdv-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .mdv-field--full {
        grid-column: 1 / -1;
    }

    .mdv-field-label {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #333;
    }

    .mdv-field-value {
        font-size: 14px;
        font-weight: 500;
        color: #555;
        margin: 0;
        padding: 0.45rem 0.65rem;
        background: var(--mdv-surface-2);
        border: 1px solid var(--mdv-border);
        border-radius: var(--mdv-radius-sm);
        line-height: 1.4;
        min-height: 34px;
        display: flex;
        align-items: center;
    }


    /* ---- IMÁGENES ---- */
    .mdv-images-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .mdv-image-block {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .mdv-img-viewer {
        min-height: 220px;
        background: var(--mdv-surface-2);
        border: 1px dashed var(--mdv-border-hover);
        border-radius: var(--mdv-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        transition: border-color 0.2s;
    }

    .mdv-img-viewer:hover {
        border-color: rgba(59,130,246,0.3);
    }

    .mdv-img-viewer img {
        max-width: 100%;
        max-height: 280px;
        object-fit: contain;
        border-radius: var(--mdv-radius-sm);
    }

    .mdv-img-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        font-size: 0.78rem;
        color: var(--mdv-text-muted);
        letter-spacing: 0.02em;
    }

    /* ---- TABLA BARRAS ---- */
    .mdv-barras-toolbar {
        display: flex;
        align-items: center;
    }

    .mdv-table-wrapper {
        max-height: 520px;
        overflow-y: auto;
        overflow-x: auto;
        border: 1px solid var(--mdv-border);
        scrollbar-width: thin;
        scrollbar-color: #2a2e3a transparent;
    }

    .mdv-table-wrapper::-webkit-scrollbar { width: 6px; }
    .mdv-table-wrapper::-webkit-scrollbar-track { background: transparent; }
    .mdv-table-wrapper::-webkit-scrollbar-thumb { background: #2a2e3a; border-radius: 4px; }

    .mdv-table {
        width: 100%;
        margin: 0 !important;
        background: var(--mdv-bg) !important;
    }

    .mdv-table thead tr th {
        background: #b8d4b8 !important;
        color: #0a0f0a !important;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 0.65rem 0.85rem;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .mdv-table tbody tr {
        background: #e8f5e8 !important;
        transition: background 0.12s;
    }

    .mdv-table tbody td {
        font-size: 14px;
        font-weight: 400;
        color: #0a0f0a !important;
        padding: 0.55rem 0.85rem;
        vertical-align: middle;
    }

    /* Badges del JS — sobrescribir colores para tema oscuro */
    .mdv-table .badge.bg-info {
        background: rgba(6,182,212,0.15) !important;
        color: #67e8f9 !important;
        border: 1px solid rgba(6,182,212,0.25);
        font-size: 0.68rem;
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdv-table .badge.bg-warning {
        background: rgba(245,158,11,0.15) !important;
        color: #fcd34d !important;
        border: 1px solid rgba(245,158,11,0.25);
        font-size: 0.68rem;
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdv-table .badge.bg-secondary {
        background: rgba(100,116,139,0.2) !important;
        color: #94a3b8 !important;
        border: 1px solid rgba(100,116,139,0.25);
        font-size: 0.68rem;
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdv-table .badge.bg-danger {
        background: rgba(239,68,68,0.12) !important;
        color: #fca5a5 !important;
        border: 1px solid rgba(239,68,68,0.22);
        font-size: 0.68rem;
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdv-table .badge.bg-light {
        background: rgba(255,255,255,0.05) !important;
        color: #9ca3af !important;
        border: 1px solid rgba(255,255,255,0.1);
        font-size: 0.68rem;
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    /* Badges fuera de la tabla (estado de recepción) */
    .badge.bg-success {
        background: rgba(34,197,94,0.12) !important;
        /* #86efac */
        color: #00876cde !important;
        border: 1px solid rgba(34,197,94,0.22);
        font-size: 14px;
        font-weight: 700;
        padding: 10px 0.65em;
        border-radius: 4px;
    }

    .badge.bg-warning {
        background: rgba(245, 158, 11, 0.12) !important;
        color: #ffbf00 !important;
        border: 1px solid rgba(245, 158, 11, 0.22);
        font-size: 14px;
        font-weight: 700;
        padding: 10px 0.65em;
        border-radius: 4px;
    }

    /* ---- RESPONSIVO ---- */
    @media (max-width: 768px) {
        .mdv-grid {
            grid-template-columns: 1fr;
        }
        .mdv-field--full {
            grid-column: 1;
        }
        .mdv-images-grid {
            grid-template-columns: 1fr;
        }
        .mdv-body {
            padding: 1rem;
        }
    }
</style>