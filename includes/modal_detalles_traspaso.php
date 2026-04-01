<!-- Modal para detalles del traspaso -->
<div class="modal fade" id="modalDetallesTraspaso" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-detalles-traspaso" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl mdt-dialog">
        <div class="modal-content mdt-content">

            <!-- HEADER -->
            <div class="modal-header">
                <h5 id="label-modal-operacion" class="modal-title">Detalles del traspaso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body mdt-body">

                <!-- TABS NAV -->
                <ul class="nav mdt-tabs" id="tabsDetallesTraspaso" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="mdt-tab-btn active" id="tab-detalles-traspaso"
                            data-bs-toggle="tab" data-bs-target="#content-detalles-traspaso"
                            type="button" role="tab" aria-controls="content-detalles-traspaso" aria-selected="true">
                            Detalles
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="mdt-tab-btn" id="tab-barras-traspaso"
                            data-bs-toggle="tab" data-bs-target="#content-barras-traspaso"
                            type="button" role="tab" aria-controls="content-barras-traspaso" aria-selected="false">
                            Barras &nbsp;<span class="mdt-tab-badge" id="cantidad-barras">0</span>
                        </button>
                    </li>
                </ul>

                <!-- TAB CONTENT -->
                <div class="tab-content mdt-tab-content" id="contentDetallesTraspaso">

                    <!-- PESTAÑA: DETALLES -->
                    <div class="tab-pane fade show active" id="content-detalles-traspaso" role="tabpanel" aria-labelledby="tab-detalles-traspaso">

                        <!-- BLOQUE: Información General -->
                        <div class="mdt-section">
                            <div class="mdt-section-header mdt-section-header--blue">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                    <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                                </svg>
                                Información General
                            </div>
                            <div class="mdt-section-body">
                                <div class="mdt-grid">
                                    <div class="mdt-field">
                                        <span class="mdt-field-label">ID del Traspaso</span>
                                        <p id="info-id" class="mdt-field-value">-</p>
                                    </div>
                                    <div class="mdt-field">
                                        <span class="mdt-field-label">Usuario Creador</span>
                                        <p id="info-usuario-creador" class="mdt-field-value">-</p>
                                    </div>
                                    <div class="mdt-field">
                                        <span class="mdt-field-label">Almacén de Origen</span>
                                        <p id="info-almacen-origen" class="mdt-field-value">-</p>
                                    </div>
                                    <div class="mdt-field">
                                        <span class="mdt-field-label">Almacén de Destino</span>
                                        <p id="info-almacen-destino" class="mdt-field-value">-</p>
                                    </div>
                                    <div class="mdt-field mdt-field--full">
                                        <span class="mdt-field-label">Justificación</span>
                                        <p id="info-justificacion" class="mdt-field-value">-</p>
                                    </div>
                                    <div class="mdt-field">
                                        <span class="mdt-field-label">Estado de Recepción</span>
                                        <p id="info-recibido" class="">-</p>
                                    </div>
                                    <div class="mdt-field">
                                        <span class="mdt-field-label">Fecha de Creación</span>
                                        <p id="info-fecha-creacion" class="mdt-field-value">-</p>
                                    </div>
                                    <div class="mdt-field" id="section-usuario-receptor" style="display:none;">
                                        <span class="mdt-field-label">Usuario Receptor</span>
                                        <p id="info-usuario-receptor" class="mdt-field-value">-</p>
                                    </div>
                                    <div class="mdt-field" id="section-fecha-recepcion" style="display:none;">
                                        <span class="mdt-field-label">Fecha de Recepción</span>
                                        <p id="info-fecha-recepcion" class="mdt-field-value">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BLOQUE: Evidencias de Envío -->
                        <div class="mdt-section">
                            <div class="mdt-section-header mdt-section-header--cyan">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>
                                </svg>
                                Evidencias de Envío
                            </div>
                            <div class="mdt-section-body">
                                <div class="mdt-images-grid">
                                    <div class="mdt-image-block">
                                        <span class="mdt-field-label">Fotografía de Barras Enviadas</span>
                                        <div id="img-envio-barras" class="mdt-img-viewer">
                                            <span class="mdt-img-placeholder">
                                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                                    <polyline points="21 15 16 10 5 21"/>
                                                </svg>
                                                Sin imagen
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mdt-image-block">
                                        <span class="mdt-field-label">Fotografía del Paquete Enviado</span>
                                        <div id="img-envio-paquete" class="mdt-img-viewer">
                                            <span class="mdt-img-placeholder">
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

                        <!-- BLOQUE: Evidencias de Recepción -->
                        <div class="mdt-section mdt-section--success" id="section-evidencias-recepcion" style="display:none;">
                            <div class="mdt-section-header mdt-section-header--green">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                                Evidencias de Recepción
                            </div>
                            <div class="mdt-section-body">
                                <div class="mdt-images-grid">
                                    <div class="mdt-image-block">
                                        <span class="mdt-field-label">Fotografía de Paquete Recibido</span>
                                        <div id="img-recepcion-paquete" class="mdt-img-viewer">
                                            <span class="mdt-img-placeholder">
                                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>
                                                    <polyline points="21 15 16 10 5 21"/>
                                                </svg>
                                                Sin imagen
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mdt-image-block">
                                        <span class="mdt-field-label">Fotografía de Barras Recibidas</span>
                                        <div id="img-recepcion-barras" class="mdt-img-viewer">
                                            <span class="mdt-img-placeholder">
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
                    <div class="tab-pane fade" id="content-barras-traspaso" role="tabpanel" aria-labelledby="tab-barras-traspaso">
                        <div class="mdt-barras-toolbar mb-1 justify-content-end mdt-section pt-2 pb-2">
                            <button id="btn-ver-inventario" type="button" class="btn-general w-auto">
                                Ver en Inventario CNC
                            </button>
                        </div>
                        <div class="mdt-table-wrapper mdt-section">
                            <table class=" table-striped table-bordered table-sm mdt-table" id="tabla-barras-traspaso">
                                <thead>
                                    <tr>
                                        <th>Clave</th>
                                        <th>Lote Pedimento</th>
                                        <th>Medida</th>
                                        <th>Material</th>
                                        <th>Proveedor</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-barras-traspaso">
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
         * Evento: Click en btn-ver-inventario desde el modal de detalles
         */
        $(document).on('click', '#btn-ver-inventario', function(e) {
            e.preventDefault();
            
            const traspasoId = $('#modalDetallesTraspaso').data('traspaso-id');
            
            if (traspasoId) {
                window.open(`inventario.php?traspaso=${traspasoId}&oper=0`, '_blank');
            }
        });
    });
</script>
<style>
    /* ================================================================
    MODAL DETALLES TRASPASO — Estilo refinado industrial
    ================================================================ */

    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap');

    /* Variables */
    :root {
        --mdt-bg:           #f0f8f0;
        --mdt-surface:      #fff;
        --mdt-surface-2:    #e6f2e6;
        --mdt-surface-3:    #d1e7d1;
        --mdt-border:       rgba(255,255,255,0.07);
        --mdt-border-hover: rgba(255,255,255,0.14);
        --mdt-text-primary: #e8eaf0;
        --mdt-text-muted:   #5a6070;
        --mdt-text-label:   #7c8494;
        --mdt-accent-blue:  #3b82f6;
        --mdt-accent-cyan:  #06b6d4;
        --mdt-accent-green: #22c55e;
        --mdt-accent-amber: #f59e0b;
        --mdt-radius:       10px;
        --mdt-radius-sm:    6px;
        --mdt-font:         'DM Sans', sans-serif;
        --mdt-mono:         'JetBrains Mono', monospace;
        --mdt-shadow:       0 24px 64px rgba(0,0,0,0.6);
    }

    /* Dialog */
    .mdt-dialog {
        max-width: 960px;
    }

    /* Content */
    .mdt-content {
        font-family: var(--mdt-font);
        background: var(--mdt-surface);
        border: 1px solid var(--mdt-border);
        border-radius: 14px !important;
        box-shadow: var(--mdt-shadow);
        overflow: hidden;
    }


    /* ---- BODY ---- */
    .mdt-body {
        background: var(--mdt-surface);
        padding: 1.5rem;
    }

    /* ---- TABS ---- */
    .mdt-tabs {
        display: flex;
        gap: 4px;
        padding-bottom: 0;
        list-style: none;
        padding-left: 0;
    }

    .mdt-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0.55rem 1rem;
        font-size: 16px;
        font-weight: 500;
        font-family: var(--mdt-font);
        color: #0a0f0a;
        background: transparent;
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: var(--mdt-radius-sm) var(--mdt-radius-sm) 0 0;
        cursor: pointer;
        transition: color 0.15s, background 0.15s, border-color 0.15s;
        position: relative;
        bottom: -1px;
        white-space: nowrap;
    }

    .mdt-tab-btn:hover {
        color: #55ad9b;
        background: var(--mdt-surface-2);
    }

    .mdt-tab-btn.active {
        color: #55ad9b;
        font-weight: 700;
        background: var(--mdt-surface);
        border-color: var(--mdt-border);
        border-bottom-color: var(--mdt-surface);
    }

    .mdt-tab-badge {
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
    .mdt-section {
        background: #fff;
        border: 1px solid var(--mdt-border);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    .mdt-section:last-child {
        margin-bottom: 0;
    }

    .mdt-section-header {
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

    .mdt-section-body {
        padding: 1.25rem 1.1rem;
    }

    /* ---- GRID DE CAMPOS ---- */
    .mdt-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
    }

    .mdt-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .mdt-field--full {
        grid-column: 1 / -1;
    }

    .mdt-field-label {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #333;
    }

    .mdt-field-value {
        font-size: 14px;
        font-weight: 500;
        color: #555;
        margin: 0;
        padding: 0.45rem 0.65rem;
        background: var(--mdt-surface-2);
        border: 1px solid var(--mdt-border);
        border-radius: var(--mdt-radius-sm);
        line-height: 1.4;
        min-height: 34px;
        display: flex;
        align-items: center;
    }


    /* ---- IMÁGENES ---- */
    .mdt-images-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .mdt-image-block {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .mdt-img-viewer {
        min-height: 220px;
        background: var(--mdt-surface-2);
        border: 1px dashed var(--mdt-border-hover);
        border-radius: var(--mdt-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        transition: border-color 0.2s;
    }

    .mdt-img-viewer:hover {
        border-color: rgba(59,130,246,0.3);
    }

    .mdt-img-viewer img {
        max-width: 100%;
        max-height: 280px;
        object-fit: contain;
        border-radius: var(--mdt-radius-sm);
    }

    .mdt-img-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        font-size: 0.78rem;
        color: var(--mdt-text-muted);
        font-family: var(--mdt-font);
        letter-spacing: 0.02em;
    }

    /* ---- TABLA BARRAS ---- */
    .mdt-barras-toolbar {
        display: flex;
        align-items: center;
    }

    .mdt-table-wrapper {
        max-height: 520px;
        overflow-y: auto;
        overflow-x: auto;
        border: 1px solid var(--mdt-border);
        scrollbar-width: thin;
        scrollbar-color: #2a2e3a transparent;
    }

    .mdt-table-wrapper::-webkit-scrollbar { width: 6px; }
    .mdt-table-wrapper::-webkit-scrollbar-track { background: transparent; }
    .mdt-table-wrapper::-webkit-scrollbar-thumb { background: #2a2e3a; border-radius: 4px; }

    .mdt-table {
        width: 100%;
        margin: 0 !important;
        font-family: var(--mdt-font);
        background: var(--mdt-bg) !important;
    }

    .mdt-table thead tr th {
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

    .mdt-table tbody tr {
        background: #e8f5e8 !important;
        transition: background 0.12s;
    }

    .mdt-table tbody td {
        font-size: 14px;
        font-weight: 400;
        color: #0a0f0a !important;
        padding: 0.55rem 0.85rem;
        vertical-align: middle;
    }

    /* Badges del JS — sobrescribir colores para tema oscuro */
    .mdt-table .badge.bg-info {
        background: rgba(6,182,212,0.15) !important;
        color: #67e8f9 !important;
        border: 1px solid rgba(6,182,212,0.25);
        font-size: 0.68rem;
        font-family: var(--mdt-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdt-table .badge.bg-warning {
        background: rgba(245,158,11,0.15) !important;
        color: #fcd34d !important;
        border: 1px solid rgba(245,158,11,0.25);
        font-size: 0.68rem;
        font-family: var(--mdt-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdt-table .badge.bg-secondary {
        background: rgba(100,116,139,0.2) !important;
        color: #94a3b8 !important;
        border: 1px solid rgba(100,116,139,0.25);
        font-size: 0.68rem;
        font-family: var(--mdt-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdt-table .badge.bg-danger {
        background: rgba(239,68,68,0.12) !important;
        color: #fca5a5 !important;
        border: 1px solid rgba(239,68,68,0.22);
        font-size: 0.68rem;
        font-family: var(--mdt-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdt-table .badge.bg-light {
        background: rgba(255,255,255,0.05) !important;
        color: #9ca3af !important;
        border: 1px solid rgba(255,255,255,0.1);
        font-size: 0.68rem;
        font-family: var(--mdt-font);
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
        .mdt-grid {
            grid-template-columns: 1fr;
        }
        .mdt-field--full {
            grid-column: 1;
        }
        .mdt-images-grid {
            grid-template-columns: 1fr;
        }
        .mdt-body {
            padding: 1rem;
        }
    }
</style>