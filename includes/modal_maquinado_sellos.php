<!-- 
MODAL MAQUINADO DE SELLOS
Propósito: Mostrar detalles de cotizaciones de maquinado para una requisición
Consulta: cotizacion_materiales basada en id_requisicion
-->
<div class="modal fade" id="modalMaquinadoSellos" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-maquinado-sellos" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl mms-dialog">
        <div class="modal-content mms-content">

            <!-- ENCABEZADO DEL MODAL -->
            <div class="modal-header">
                <h5 class="modal-title">Maquinado de sellos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="modal-body mms-body">
                <!-- Las cotizaciones se cargarán dinámicamente aquí -->
            </div>

            <!-- PIE DEL MODAL -->
            <div class="modal-footer border-top pt-3">
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
     * Carga la información de maquinado de sellos para una requisición
     * @param {number} idRequisicion - ID de la requisición
     */
    function cargarMaquinadoSellos(idRequisicion) {
        console.log("INICIANDO cargarMaquinadoSellos con ID:", idRequisicion);
        
        var modalBody = $("#modalMaquinadoSellos .modal-body");
        
        // Mostrar mensaje de carga
        modalBody.html('<div class="text-center text-muted py-5"><div class="spinner-border spinner-border-sm"></div> Cargando información...</div>');
        
        // Petición AJAX al backend
        $.ajax({
            type: "GET",
            url: "../ajax/get_maquinado_sellos.php",
            data: { id_requisicion: idRequisicion },
            dataType: "json",
            success: function(data) {
                console.log("✓ AJAX SUCCESS - Datos retornados del backend:", data);
                console.log("  Datos.success:", data.success);
                console.log("  Datos.cotizaciones:", data.cotizaciones);
                console.log("  Cantidad de cotizaciones:", data.cotizaciones ? data.cotizaciones.length : 0);
                
                if (data.error) {
                    console.error("Error en respuesta:", data.error);
                    modalBody.html('<div class="alert alert-danger">' + data.error + '</div>');
                    return;
                }
                
                if (data.success && data.cotizaciones && data.cotizaciones.length > 0) {
                    console.log("Procediendo a renderizar cotizaciones...");
                    // Limpiar modal
                    modalBody.html('');
                    
                    // Renderizar cada cotización
                    $.each(data.cotizaciones, function(indexCot, cotizacion) {
                        console.log("Procesando cotización #" + indexCot + ":", cotizacion);
                        var esUnicoComponente = cotizacion.cantidad_componentes === 1;
                        console.log("  ¿Es único componente?:", esUnicoComponente);
                        var htmlCotizacion = construirSeccionCotizacion(cotizacion, esUnicoComponente);
                        modalBody.append(htmlCotizacion);
                    });
                } else {
                    console.warn("No hay cotizaciones o respuesta inválida");
                    modalBody.html('<div class="alert alert-info">No hay cotizaciones disponibles</div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('✗ AJAX ERROR');
                console.error('  Status:', status);
                console.error('  Error:', error);
                console.error('  Response Text:', xhr.responseText);
                console.error('  HTTP Status Code:', xhr.status);
                modalBody.html('<div class="alert alert-danger">Error al cargar la información. Verifique la consola del navegador.</div>');
            }
        });
    }
    /**
     * Construye la sección HTML para una cotización
     * @param {object} cotizacion - Datos de la cotización
     * @param {boolean} esUnico - Si es un único componente
     * @returns {string} HTML de la sección
     */
    function construirSeccionCotizacion(cotizacion, esUnico) {
        console.log("construirSeccionCotizacion:");
        console.log("  ID Cotización:", cotizacion.id_cotizacion);
        console.log("  Perfil:", cotizacion.perfil_sello);
        console.log("  Es único componente:", esUnico);
        
        var html = '<div class="mms-section-quote">' +
            '<!-- Encabezado de cotización -->' +
            '<div class="d-flex justify-content-between align-items-center mms-section-header">' +
            '<span>ID Cotización: ' + cotizacion.id_cotizacion + '</span>' +
            '<span>Perfil: ' + cotizacion.perfil_sello + ' (' + cotizacion.familia_perfil + ')</span>' +
            '</div>' +
            '<div class="mms-section-body">';
        
        if (esUnico) {
            // Caso: UN SOLO COMPONENTE
            console.log("  → Renderizando como ÚNICO componente");
            html += construirCasoUnicoComponente(cotizacion);
        } else {
            // Caso: MÚLTIPLES COMPONENTES
            console.log("  → Renderizando como MÚLTIPLES componentes");
            html += construirCasoMultiplesComponentes(cotizacion);
        }
        
        if (cotizacion.comentarios && cotizacion.comentarios.length > 0) {
            html += construirSeccionComentarios(cotizacion.comentarios);
        }
        
        html += '</div></div>';
        
        return html;
    }
    /**
     * Construye el contenido para caso de un único componente
     * @param {object} cotizacion
     * @returns {string} HTML
     */
    function construirCasoUnicoComponente(cotizacion) {
        console.log("construirCasoUnicoComponente:");
        console.log("  Componentes disponibles:", cotizacion.componentes);
        
        var componente = cotizacion.componentes[0];
        console.log("  Procesando componente:", componente);
        console.log("  Barras en componente:", componente.barras);
        
        var html = '<div class="mms-grid mb-2">' +
            '<div class="mms-field">' +
            '<span class="mms-field-label">Cantidad:</span>' +
            '<p class="mms-field-value">' + cotizacion.cantidad + '</p>' +
            '</div>' +
            '<div class="mms-field">' +
            '<span class="mms-field-label">Material:</span>' +
            '<p class="mms-field-value">' + cotizacion.material + '</p>' +
            '</div>' +
            '</div>' +
            '<div class="mms-card-component d-flex flex-column p-3">' +
            '<div class="mms-component-section1 d-flex mb-2 gap-2">' +
            '<div class="mms-imag-container w-auto">' +
            '<div class="mms-img-viewer">' +
            '<img src="' + (componente.img || '../assets/img/general/blanco.jpg') + '" ' +
            'alt="Foto del maquinado de sello" ' +
            'onerror="this.src=\'../assets/img/general/blanco.jpg\'">' +
            '</div>' +
            '</div>' +
            '<div class="mms-container-nominals w-100">' +
            '<div>'  +
            '<span class="mms-title-container m-2">Nominales</span>' +
            '</div>' +
            '<div class="mms-container-table-nominals">' +
            construirTablaNominales(componente.nominales, false, true) +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="mms-component-section2">' +
            '<div>' +
            '<span class="mms-title-container m-2">Barras</span>' +
            '</div>' +
            '<div class="mms-table-wrapper">' +
            construirTablaBarras(componente.barras) +
            '</div>' +
            '</div>' +
            '</div>';
        
        return html;
    }
    /**
     * Construye el contenido para caso de múltiples componentes
     * @param {object} cotizacion
     * @returns {string} HTML
     */
    function construirCasoMultiplesComponentes(cotizacion) {
        console.log("Procesando múltiples componentes - Cotización:", cotizacion);
        console.log("Componentes disponibles:", cotizacion.componentes);
        
        var html = '<div class="mms-container-nominals w-100 mb-2">' +
            '<div>' +
                '<span class="mms-title-container m-2">Nominales</span>' +
            '</div>' +
            '<div class="mms-container-table-nominals">' +
            construirTablaNominales(cotizacion.nominales_generales, true, false) +
            '</div>' +
            '</div>';
        
        // Iterar en cada componente
        $.each(cotizacion.componentes, function(indexComp, componente) {
            console.log("Procesando componente #" + indexComp + ":", componente);
            console.log("Barras para este componente:", componente.barras);
            
            var imgPath = componente.img || '../assets/img/general/blanco.jpg';
            console.log("Ruta de imagen dinámica:", imgPath);
            
            html += '<div class="mms-card-component d-flex flex-column p-3">' +
                '<div class="mms-component-section1 d-flex mb-2 gap-2">' +
                '<div class="mms-imag-container w-auto">' +
                '<div class="mms-img-viewer">' +
                '<img src="' + imgPath + '" ' +
                'alt="Foto del componente ' + (indexComp + 1) + '" ' +
                'onerror="this.src=\'../assets/img/general/blanco.jpg\'; console.error(\'Error cargando imagen: ' + imgPath + '\');">' +
                '</div>' +
                '</div>' +
                '<div class="mms-container-nominals w-100">' +
                '<div>' +
                '<span class="mms-title-container mb-2">Componente: ' + componente.cantidad_material + '</span>' +
                '</div>' +
                '<div class="mms-grid mb-2">' +
                '<div class="mms-field">' +
                '<span class="mms-field-label">Cantidad:</span>' +
                '<p class="mms-field-value">' + cotizacion.cantidad + '</p>' +
                '</div>' +
                '<div class="mms-field">' +
                '<span class="mms-field-label">Material:</span>' +
                '<p class="mms-field-value">' + cotizacion.material + '</p>' +
                '</div>' +
                '</div>' +
                '<div>' +
                '<span class="mms-title-container mb-2">Nominales Teóricas (Aproximaciónes)</span>' +
                '</div>' +
                '<div class="mms-container-table-nominals">' +
                construirTablaNominales(componente.nominales, false, false) +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="mms-component-section2">' +
                '<div>' +
                '<span class="mms-title-container m-2">Barras</span>' +
                '</div>' +
                '<div class="mms-table-wrapper">' +
                construirTablaBarras(componente.barras) +
                '</div>' +
                '</div>' +
                '</div>';
        });
        
        return html;
    }
    /**
     * Construye la tabla HTML de nominales
     * @param {object} nominales - Objeto con valores de nominales
     * @param {boolean} esGeneral - Si es la sección general
     * @param {boolean} esCasoUnico - Si es el caso de un único componente
     * @returns {string} HTML de tabla
     */
    function construirTablaNominales(nominales, esGeneral, esCasoUnico) {
        var headerAltura = (esGeneral || esCasoUnico) ? 'Altura(s)' : 'Altura componente';
        var labelTotal = (esGeneral || esCasoUnico) ? 'Total: ' : '';

        var html = '<table class="table-striped table-bordered table-sm mms-table">' +
                        '<thead>' +
                            '<tr>' +
                                '<th>DI</th>' +
                                '<th>DE</th>' +
                                '<th>' + headerAltura + '</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>' +
                            '<tr>' +
                                '<td>' +
                                    '<div class="d-flex flex-column">' +
                                        '<span class="fw-bold">(' + nominales.tipo_medida_di + ')</span>' +
                                        '<span>' + nominales.di_sello + '</span>' +
                                    '</div>' +
                                '</td>' +
                                '<td>' +
                                    '<div class="d-flex flex-column">' +
                                        '<span class="fw-bold">(' + nominales.tipo_medida_de + ')</span>' +
                                        '<span>' + nominales.de_sello + '</span>' +
                                    '</div>' +
                                '</td>' +
                                '<td>' +
                                    '<div class="d-flex flex-column">' +
                                        '<span class="fw-bold">(' + nominales.tipo_medida_h + ')</span>' +
                                        '<span>' + labelTotal + nominales.a_sello + '</span>';
        
        // Agregar alturas adicionales si existen (Solo en general o caso único)
        if (esGeneral || esCasoUnico) {
            if (nominales.altura_escalon && typeof nominales.altura_escalon === 'object' && nominales.altura_escalon.valor) {
                html += '<span>Caja + Escalón: ' + nominales.altura_escalon.valor + '</span>';
            }
            if (nominales.altura_caja && typeof nominales.altura_caja === 'object' && nominales.altura_caja.valor) {
                html += '<span>Caja: ' + nominales.altura_caja.valor + '</span>';
            }
            if (nominales.altura_h2 && typeof nominales.altura_h2 === 'object' && nominales.altura_h2.valor) {
                html += '<span>H2: ' + nominales.altura_h2.valor + '</span>';
            }
            if (nominales.altura_h3 && typeof nominales.altura_h3 === 'object' && nominales.altura_h3.valor) {
                html += '<span>H3: ' + nominales.altura_h3.valor + '</span>';
            }
        }
        
                    html += '</div>' +
                        '</td>' +
                    '</tr>' +
                '</tbody>' +
            '</table>';
        
        return html;
    }
    /**
     * Construye la tabla HTML de barras
     * @param {array} barras - Array de objetos barras
     * @returns {string} HTML de tabla
     */
    function construirTablaBarras(barras) {
        console.log("Construyendo tabla barras con datos:", barras);
        
        if (!barras || barras.length === 0) {
            console.warn("No hay barras disponibles");
            return '<div class="text-center text-muted py-3">No hay barras registradas</div>';
        }
        
        var html = '<table class="table-striped table-bordered table-sm mms-table">' +
            '<thead>' +
                '<tr>' +
                    '<th>Barra (Lote/Clave/Medida/Pz Teóricas)</th>' +
                    '<th>Tipo de selección</th>' +
                '</tr>' +
            '</thead>' +
            '<tbody>';
        
        $.each(barras, function(index, barra) {
            console.log("Renderizando barra:", barra);
            var badge = (barra.tipo === 'manual') 
                ? '<span class="badge badge-manual">Selección manual de ventas</span>' 
                : '<span class="badge badge-sistema">Sugerencia del sistema</span>';
            
            html += '<tr>' +
                        '<td>' + barra.barra + '</td>' +
                        '<td>' + badge + '</td>' +
                    '</tr>';
        });
        
        html += '</tbody></table>';
        
        return html;
    }
    /**
     * Construye la sección HTML de comentarios y adjuntos
     * @param {array} comentarios - Array de objetos con comentarios
     * @returns {string} HTML de sección
     */
    function construirSeccionComentarios(comentarios) {
        console.log("Construyendo sección de comentarios con:", comentarios);
        
        var html = '<div class="mms-comments-section mt-3">' +
            '<div class="mb-1">' +
            '<span class="mms-title-container m-1">Comentarios y adjuntos</span>' +
            '</div>' +
            '<div class="mms-comments-list">';
        
        $.each(comentarios, function(index, reg) {
            var archivoHTML = reg.ruta_adjunto ? 
                '<div class="mt-1">' +
                    '<a href="../' + reg.ruta_adjunto + '" class="mms-comment-attachment" target="_blank">' +
                        '<i class="bi bi-paperclip"></i> ' + reg.ruta_adjunto.split('/').pop() +
                    '</a>' +
                '</div>' : '';
                
            html += '<div class="mms-comment-item p-2 mb-2">' +
                        '<p class="mb-1 text-break" style="font-size: 14px;">' + (reg.comentario || '') + '</p>' +
                        archivoHTML +
                        //'<div class="text-end">' +
                           // '<small class="text-muted" style="font-size: 11px;">' + (reg.fecha_creacion || '') + '</small>' +
                        //'</div>' +
                    '</div>';
        });
        
        html += '</div></div>';
        return html;
    }

    
    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ===========================================================  
    $(document).ready(function() {
        // Evento cuando se abre el modal
        $("#modalMaquinadoSellos").on("show.bs.modal", function(event) {
            // Obtener id_requisicion del botón que dispara el modal
            var idRequisicion = $(event.relatedTarget).data("id-requisicion");
            
            if (!idRequisicion) {
                idRequisicion = window.idRequisicionActual;
            }
            
            if (idRequisicion) {
                cargarMaquinadoSellos(idRequisicion);
            }
        });
    });
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap');
    /* Variables de color y tipografía */
    :root {
        --mms-bg:           #f0f8f0;
        --mms-surface:      #fff;
        --mms-surface-2:    #e6f2e6;
        --mms-surface-3:    #d1e7d1;
        --mms-border:       #aaa;
        --mms-border-hover: #7c8494;
        --mms-text-primary: #e8eaf0;
        --mms-text-muted:   #5a6070;
        --mms-text-label:   #7c8494;
        --mms-accent-blue:  #3b82f6;
        --mms-accent-cyan:  #06b6d4;
        --mms-accent-green: #22c55e;
        --mms-accent-amber: #f59e0b;
        --mms-radius:       10px;
        --mms-radius-sm:    6px;
        --mms-shadow:       0 24px 64px rgba(0,0,0,0.6);
    }

    /* Contenedor del diálogo */
    .mms-dialog {
        max-width: 960px;
    }

    /* Contenedor principal del modal */
    .mms-content {
        background: var(--mms-surface);
        border: 1px solid var(--mms-border);
        border-radius: 14px !important;
        box-shadow: var(--mms-shadow);
        overflow: hidden;
    }

    /* Cuerpo del modal */
    .mms-body {
        background: var(--mms-surface);
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #2a2e3a transparent;
    }

    .mms-body::-webkit-scrollbar {
        width: 6px;
    }

    .mms-body::-webkit-scrollbar-track {
        background: transparent;
    }

    .mms-body::-webkit-scrollbar-thumb {
        background: #2a2e3a;
        border-radius: 4px;
    }

    /* ========================================
    SECCIONES DE COTIZACIÓN
    ======================================== */
    .mms-section-quote {
        background: #fff;
        border: 1px solid var(--mms-border);
        margin-bottom: 3rem;
        border-radius: 8px;
        overflow: hidden;
    }

    .mms-section-quote:last-child {
        margin-bottom: 0;
    }

    /* Encabezado de sección de cotización */
    .mms-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.7rem 1.1rem;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.04em;
        background-color: #607d8b;
        border-bottom: 1px solid rgba(59,130,246,0.15);
        color: #fff;
    }

    /* Cuerpo de sección de cotización */
    .mms-section-body {
        padding: 1.25rem 1.1rem;
    }

    /* ========================================
    CONTENEDOR DE COMPONENTES
    ======================================== */
    .mms-card-component {
        background-color: #fff;
        border: 1px solid var(--mms-border);
        border-radius: 14px;
        padding: 1.5rem;
        margin-bottom: 0.2rem;
        box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
    }

    .mms-card-component:last-child {
        margin-bottom: 0;
    }

    /* Título de contenedor/componente */
    .mms-title-container {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.06em;
        color: #333;
    }

    /* ========================================
    GRID DE CAMPOS
    ======================================== */
    .mms-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
        margin-bottom: 1rem;
    }

    /* Campo individual */
    .mms-field {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 0.5rem;
    }

    .mms-field-label {
        font-size: 13px;
        font-weight: 600;
        letter-spacing: 0.06em;
        color: #333;
        min-width: fit-content;
    }

    .mms-field-value {
        font-size: 14px;
        font-weight: 500;
        color: #555;
        padding: 0.45rem 0.65rem;
        background: var(--mms-surface-2);
        border: 1px solid var(--mms-border);
        border-radius: var(--mms-radius-sm);
        flex-grow: 1;
        display: flex;
        align-items: center;
        margin-bottom: 0px;
    }

    /* ========================================
    SECCIÓN DE IMAGEN
    ======================================== */
    .mms-component-section1 {
        display: flex;
        margin-bottom: 1.5rem;
        gap: 1.5rem;
    }

    .mms-imag-container {
        flex-shrink: 0;
    }

    .mms-img-viewer {
        min-height: 220px;
        min-width: 220px;
        border: 1px dashed var(--mms-border-hover);
        border-radius: var(--mms-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: var(--mms-surface-2);
        transition: border-color 0.2s;
    }

    .mms-img-viewer:hover {
        border-color: rgba(59,130,246,0.3);
    }

    .mms-img-viewer img {
        max-width: 100%;
        max-height: 280px;
        object-fit: contain;
        border-radius: var(--mms-radius-sm);
    }

    /* Contenedor de nominales */
    .mms-container-nominals {
        flex-grow: 1;
    }

    /* ========================================
    TABLA DE NOMINALES
    ======================================== */
    .mms-container-table-nominals {
        max-height: 400px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #2a2e3a transparent;
    }

    .mms-container-table-nominals::-webkit-scrollbar {
        width: 6px;
    }

    .mms-container-table-nominals::-webkit-scrollbar-track {
        background: transparent;
    }

    .mms-container-table-nominals::-webkit-scrollbar-thumb {
        background: #2a2e3a;
        border-radius: 4px;
    }

    .mms-table {
        width: 100%;
        margin: 0 !important;
        background: var(--mms-bg) !important;
    }

    .mms-table thead tr th {
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

    .mms-table tbody tr {
        background: #e8f5e8 !important;
        transition: background 0.12s;
    }

    .mms-table tbody tr:hover {
        background: #d1e7d1 !important;
    }

    .mms-table tbody td {
        font-size: 13px;
        font-weight: 400;
        color: #0a0f0a !important;
        padding: 0.55rem 0.85rem;
        vertical-align: middle;
    }

    /* ========================================
    SECCIÓN DE BARRAS
    ======================================== */
    .mms-component-section2 {
        margin-top: 0.2rem;
    }

    /* Contenedor de tabla de barras */
    .mms-table-wrapper {
        max-height: 350px;
        overflow-y: auto;
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #2a2e3a transparent;
    }

    .mms-table-wrapper::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .mms-table-wrapper::-webkit-scrollbar-track {
        background: transparent;
    }

    .mms-table-wrapper::-webkit-scrollbar-thumb {
        background: #2a2e3a;
        border-radius: 4px;
    }

    /* Badges de estado */
    .badge.badge-manual {
        background: rgba(245, 158, 11, 0.15) !important;
        color: #ffbf00 !important;
        border: 1px solid rgba(245, 158, 11, 0.25);
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.35em 0.6em;
        border-radius: 3px;
    }

    .badge.badge-sistema {
        background: rgba(34, 197, 94, 0.15) !important;
        color: #22c55e !important;
        border: 1px solid rgba(34, 197, 94, 0.25);
        font-size: 0.8rem;
        font-weight: 600;
        padding: 0.35em 0.6em;
        border-radius: 3px;
    }

    /* ========================================
    SECCIÓN DE COMENTARIOS
    ======================================== */
    .mms-comments-section {
        border-top: 1px dashed var(--mms-border);
        padding-top: 0.3rem;
    }

    .mms-comment-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        transition: transform 0.2s;
    }

    .mms-comment-item:hover {
        transform: translateY(-2px);
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .mms-comment-attachment {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.3rem 0.6rem;
        color: #2563eb;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.2s;
    }

    .mms-comment-attachment:hover {
        background: #dbeafe;
        color: #1d4ed8;
        text-decoration: none;
    }

    /* ========================================
    RESPONSIVO
    ======================================== */
    @media (max-width: 768px) {
        .mms-grid {
            grid-template-columns: 1fr;
        }

        .mms-component-section1 {
            flex-direction: column;
            gap: 1rem;
        }

        .mms-img-viewer {
            min-width: 100%;
        }

        .mms-body {
            padding: 1rem;
        }

        .mms-section-quote {
            margin-bottom: 2rem;
        }
    }
</style>