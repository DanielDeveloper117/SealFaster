<!-- Modal para ver detalles del maquinado de sellos respecto a las cotizaciones de la requisicion -->
<div class="modal fade" id="modalMaquinadoSellos" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-maquinado-sellos" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl mms-dialog">
        <div class="modal-content mms-content">

            <!-- HEADER -->
            <div class="modal-header">
                <h5 id="" class="modal-title">Maquinado de sellos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body mms-body">


                <!-- CASO: UNICO COMPONENTE -->
                <!-- CARD DE COTIZACION -->
                <div class="mms-section-quote">
                    <div class="d-flex justify-content-between align-items-center mms-section-header">
                        <span class="">ID Cotización: cm.id_cotizacion</span>
                        <span class="">Perfil: cm.perfil_sello (cm.familia_perfil)</span>
                    </div>
                    <div class="mms-section-body">
                        <div class="mms-grid mb-2">
                            <div class="mms-field">
                                <span class="mms-field-label">Cantidad:</span>
                                <p id="" class="mms-field-value">cm.cantidad</p>
                            </div>
                            <div class="mms-field">
                                <span class="mms-field-label">Material:</span>
                                <p id="" class="mms-field-value">cm.material</p>
                            </div>
                        </div>
                        
                        <div class="mms-card-component d-flex flex-column p-3">
                            <div class="mms-component-section1 d-flex mb-2 gap-2">
                                <div class="mms-imag-container w-auto">
                                    <div class="mms-img-viewer">
                                        <img id="img-maquinado-sello" src="../assets/img/piston/K01-P/K01-P_0.jpg" alt="Foto del maquinado de sello">
                                    </div>
                                </div>
                                <div class="mms-container-nominals w-100">
                                    <div class="">
                                        <span class="mms-title-container m-2">Nominales</span>
                                    </div>
                                    <div class="mms-container-table-nominals">
                                        <table class="table-striped table-bordered table-sm mms-table">
                                            <thead>
                                                <tr>
                                                    <th>DI</th>
                                                    <th>DE</th>
                                                    <th>Altura(s)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody-nominals-maquinado-sello ">
                                                <tr>
                                                    <td>
                                                        <span class="fw-bold">(cm.tipo_medida_di)</span>
                                                        <span>cm.di_sello</span> 
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold">(cm.tipo_medida_de)</span>
                                                        <span>cm.de_sello</span> 
                                                    </td>
                                                    <td>
                                                        <div class="mms-container-alturas d-flex flex-column">
                                                            <span class="fw-bold">(cm.tipo_medida_h)</span>
                                                            <span>Total: cm.a_sello</span>
                                                            <span>Caja + Escalón: cm.altura_escalon (mostrar si es > 0.00)</span>
                                                            <span>Caja: cm.altura_caja (mostrar si es > 0.00)</span>
                                                            <span>H2: cm.altura_h2 (mostrar si es > 0.00)</span>
                                                            <span>H3: cm.altura_h3 (mostrar si es > 0.00)</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
    
                                </div>
                            </div>
                            <div class="mms-component-section2">
                                <div class="">
                                    <span class="mms-title-container m-2">Barras</span>
                                </div>
                                <!-- TABLA DE BARRAS -->
                                <div class="mms-table-wrapper">
                                    <table id="" class=" table-striped table-bordered table-sm mms-table" >
                                        <thead>
                                            <tr>
                                                <th>Clave</th>
                                                <th>Lote</th>
                                                <th>Medida</th>
                                                <th>Pz Teóricas</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-barras-venta">
                                            <tr>
                                                <td>ABCDEFGHIJKL</td>
                                                <td>ABC-1</td>
                                                <td>50/60</td>
                                                <td>2 pz</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Cargando barras...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        
                            
                        </div>

                    </div>
                </div>

                   
                <!-- CASO: MAS DE UN COMPONENTE -->
                <!-- CARD DE COTIZACION -->
                <div class="mms-section-quote">
                    <div class="d-flex justify-content-between align-items-center mms-section-header">
                        <span class="">ID Cotización: cm.id_cotizacion</span>
                        <span class="">Perfil: cm.perfil_sello (cm.familia_perfil)</span>
                    </div>
                    <div class="mms-section-body">
                        <div class="mms-container-nominals w-100 mb-3">
                            <div class="">
                                <span class="mms-title-container m-2">Nominales</span>
                            </div>
                            <div class="mms-container-table-nominals">
                                <table class="table-striped table-bordered table-sm mms-table">
                                    <thead>
                                        <tr>
                                            <th>DI</th>
                                            <th>DE</th>
                                            <th>Altura(s)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-nominals-maquinado-sello ">
                                        <tr>
                                            <td>
                                                <span class="fw-bold">(cm.tipo_medida_di)</span>
                                                <span>cm.di_sello</span> 
                                            </td>
                                            <td>
                                                <span class="fw-bold">(cm.tipo_medida_de)</span>
                                                <span>cm.de_sello</span> 
                                            </td>
                                            <td>
                                                <div class="mms-container-alturas d-flex flex-column">
                                                    <span class="fw-bold">(cm.tipo_medida_h)</span>
                                                    <span>Total: cm.a_sello</span>
                                                    <span>Caja + Escalón: cm.altura_escalon (mostrar si es > 0.00)</span>
                                                    <span>Caja: cm.altura_caja (mostrar si es > 0.00)</span>
                                                    <span>H2: cm.altura_h2 (mostrar si es > 0.00)</span>
                                                    <span>H3: cm.altura_h3 (mostrar si es > 0.00)</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                
                        
                        <div class="mms-card-component d-flex flex-column p-3">
                            <div class="mms-component-section1 d-flex mb-2 gap-2">
                                <div class="mms-imag-container w-auto">
                                    <div class="mms-img-viewer">
                                        <img id="img-maquinado-sello" src="../assets/img/piston/K02-P/K02-P_1.jpg" alt="Foto del componente">
                                    </div>
                                </div>
                                <div class="mms-container-nominals w-100">
                                    <div class="">
                                        <span class="mms-title-container mb-2">Componente: cm.cantidad_material</span>
                                    </div>
                                    <div class="mms-grid mb-2">
                                        <div class="mms-field">
                                            <span class="mms-field-label">Cantidad:</span>
                                            <p id="" class="mms-field-value">cm.cantidad</p>
                                        </div>
                                        <div class="mms-field">
                                            <span class="mms-field-label">Material:</span>
                                            <p id="" class="mms-field-value">cm.material</p>
                                        </div>
                                    </div>
                                    <div class="">
                                        <span class="mms-title-container mb-2">Nominales Teóricas (Aproximaciónes)</span>
                                    </div>
                                    <div class="mms-container-table-nominals">
                                        <table class="table-striped table-bordered table-sm mms-table">
                                            <thead>
                                                <tr>
                                                    <th>DI</th>
                                                    <th>DE</th>
                                                    <th>Altura(s)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody-nominals-maquinado-sello ">
                                                <tr>
                                                    <td>
                                                        <span>cm.diametro_int</span> 
                                                    </td>
                                                    <td>
                                                        <span>cm.diametro_ext</span> 
                                                    </td>
                                                    <td>
                                                        <span>cm.altura</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
    
                                </div>
                            </div>
                            <div class="mms-component-section2">
                                <div class="">
                                    <span class="mms-title-container m-2">Barras</span>
                                </div>
                                <!-- TABLA DE BARRAS -->
                                <div class="mms-table-wrapper">
                                    <table id="" class=" table-striped table-bordered table-sm mms-table" >
                                        <thead>
                                            <tr>
                                                <th>Clave</th>
                                                <th>Lote</th>
                                                <th>Medida</th>
                                                <th>Pz Teóricas</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-barras-venta">
                                            <tr>
                                                <td>ABCDEFGHIJKL</td>
                                                <td>ABC-1</td>
                                                <td>50/60</td>
                                                <td>2 pz</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Cargando barras...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        
                            
                        </div>
                        <div class="mms-card-component d-flex flex-column p-3">
                            <div class="mms-component-section1 d-flex mb-2 gap-2">
                                <div class="mms-imag-container w-auto">
                                    <div class="mms-img-viewer">
                                        <img id="img-maquinado-sello" src="../assets/img/piston/K02-P/K02-P_2.jpg" alt="Foto del componente">
                                    </div>
                                </div>
                                <div class="mms-container-nominals w-100">
                                    <div class="">
                                        <span class="mms-title-container mb-2">Componente: cm.cantidad_material</span>
                                    </div>
                                    <div class="mms-grid mb-2">
                                        <div class="mms-field">
                                            <span class="mms-field-label">Cantidad:</span>
                                            <p id="" class="mms-field-value">cm.cantidad</p>
                                        </div>
                                        <div class="mms-field">
                                            <span class="mms-field-label">Material:</span>
                                            <p id="" class="mms-field-value">cm.material</p>
                                        </div>
                                    </div>
                                    <div class="">
                                        <span class="mms-title-container mb-2">Nominales Teóricas (Aproximaciónes)</span>
                                    </div>
                                    <div class="mms-container-table-nominals">
                                        <table class="table-striped table-bordered table-sm mms-table">
                                            <thead>
                                                <tr>
                                                    <th>DI</th>
                                                    <th>DE</th>
                                                    <th>Altura(s)</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody-nominals-maquinado-sello ">
                                                <tr>
                                                    <td>
                                                        <span>cm.diametro_int</span> 
                                                    </td>
                                                    <td>
                                                        <span>cm.diametro_ext</span> 
                                                    </td>
                                                    <td>
                                                        <span>cm.altura</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
    
                                </div>
                            </div>
                            <div class="mms-component-section2">
                                <div class="">
                                    <span class="mms-title-container m-2">Barras</span>
                                </div>
                                <!-- TABLA DE BARRAS -->
                                <div class="mms-table-wrapper">
                                    <table id="" class=" table-striped table-bordered table-sm mms-table" >
                                        <thead>
                                            <tr>
                                                <th>Clave</th>
                                                <th>Lote</th>
                                                <th>Medida</th>
                                                <th>Pz Teóricas</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbody-barras-venta">
                                            <tr>
                                                <td>ABCDEFGHIJKL</td>
                                                <td>ABC-1</td>
                                                <td>50/60</td>
                                                <td>2 pz</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Cargando barras...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        
                            
                        </div>
                    </div>
                </div>

               
            </div><!-- /modal-body -->

            <div class="modal-footer border-top pt-3">
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                   
                </div>
            </div>

        </div><!-- /modal-content -->
    </div><!-- /modal-dialog -->
</div>

<style>
    /* ================================================================
    MODAL DETALLES venta — Estilo refinado industrial
    ================================================================ */

    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap');

    /* Variables */
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
        --mms-font:         'DM Sans', sans-serif;
        --mms-mono:         'JetBrains Mono', monospace;
        --mms-shadow:       0 24px 64px rgba(0,0,0,0.6);
    }

    /* Dialog */
    .mms-dialog {
        max-width: 960px;
    }

    /* Content */
    .mms-content {
        font-family: var(--mms-font);
        background: var(--mms-surface);
        border: 1px solid var(--mms-border);
        border-radius: 14px !important;
        box-shadow: var(--mms-shadow);
        overflow: hidden;
    }


    /* ---- BODY ---- */
    .mms-body {
        background: var(--mms-surface);
        padding: 1.5rem;
    }

    /* ---- TABS ---- */
    .mms-tabs {
        display: flex;
        gap: 4px;
        padding-bottom: 0;
        list-style: none;
        padding-left: 0;
    }

    .mms-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0.55rem 1rem;
        font-size: 16px;
        font-weight: 500;
        font-family: var(--mms-font);
        color: #0a0f0a;
        background: transparent;
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: var(--mms-radius-sm) var(--mms-radius-sm) 0 0;
        cursor: pointer;
        transition: color 0.15s, background 0.15s, border-color 0.15s;
        position: relative;
        bottom: -1px;
        white-space: nowrap;
    }

    .mms-tab-btn:hover {
        color: #55ad9b;
        background: var(--mms-surface-2);
    }

    .mms-tab-btn.active {
        color: #55ad9b;
        font-weight: 700;
        background: var(--mms-surface);
        border-color: var(--mms-border);
        border-bottom-color: var(--mms-surface);
    }

    .mms-tab-badge {
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
    .mms-section-quote {
        background: #fff;
        border: 1px solid var(--mms-border);
        margin-bottom: 3rem;
        overflow: hidden;
    }

    .mms-section:last-child {
        margin-bottom: 0;
    }

    .mms-section-header {
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

    .mms-section-body {
        padding: 1.25rem 1.1rem;
    }
    .mms-card-component{
        background-color: #fff;
        height: auto;
        position: relative;
        border: 1px solid var(--mms-border);
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
        flex-shrink: 0;
    }
    .mms-title-container{
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.06em;
        /* text-transform: uppercase; */
        color: #333;
    }

    /* ---- GRID DE CAMPOS ---- */
    .mms-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
    }

    .mms-field {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 4px;
    }

    .mms-field--full {
        grid-column: 1 / -1;
    }

    .mms-field-label {
        font-size: 14px;
        font-weight: 500;
        letter-spacing: 0.06em;
        /* text-transform: uppercase; */
        color: #333;
    }

    .mms-field-value {
        font-size: 14px;
        font-weight: 500;
        color: #555;
        margin: 0;
        padding: 0.45rem 0.65rem;
        background: var(--mms-surface-2);
        border: 1px solid var(--mms-border);
        border-radius: var(--mms-radius-sm);
        line-height: 1.4;
        min-height: 34px;
        display: flex;
        align-items: center;
    }


    /* ---- IMÁGENES ---- */
    .mms-images-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .mms-image-block {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .mms-img-viewer {
        min-height: 220px;
        border: 1px dashed var(--mms-border-hover);
        border-radius: var(--mms-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
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

    .mms-img-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        font-size: 0.78rem;
        color: var(--mms-text-muted);
        font-family: var(--mms-font);
        letter-spacing: 0.02em;
    }

    /* ---- TABLA BARRAS ---- */
    .mms-barras-toolbar {
        display: flex;
        align-items: center;
    }

    .mms-table-wrapper {
        max-height: 520px;
        overflow-y: auto;
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: #2a2e3a transparent;
    }

    .mms-table-wrapper::-webkit-scrollbar { width: 6px; }
    .mms-table-wrapper::-webkit-scrollbar-track { background: transparent; }
    .mms-table-wrapper::-webkit-scrollbar-thumb { background: #2a2e3a; border-radius: 4px; }

    .mms-table {
        width: 100%;
        margin: 0 !important;
        font-family: var(--mms-font);
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

    .mms-table tbody td {
        font-size: 14px;
        font-weight: 400;
        color: #0a0f0a !important;
        padding: 0.55rem 0.85rem;
        vertical-align: middle;
    }

    /* Badges del JS — sobrescribir colores para tema oscuro */
    .mms-table .badge.bg-info {
        background: rgba(6,182,212,0.15) !important;
        color: #67e8f9 !important;
        border: 1px solid rgba(6,182,212,0.25);
        font-size: 0.68rem;
        font-family: var(--mms-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mms-table .badge.bg-warning {
        background: rgba(245,158,11,0.15) !important;
        color: #fcd34d !important;
        border: 1px solid rgba(245,158,11,0.25);
        font-size: 0.68rem;
        font-family: var(--mms-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mms-table .badge.bg-secondary {
        background: rgba(100,116,139,0.2) !important;
        color: #94a3b8 !important;
        border: 1px solid rgba(100,116,139,0.25);
        font-size: 0.68rem;
        font-family: var(--mms-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mms-table .badge.bg-danger {
        background: rgba(239,68,68,0.12) !important;
        color: #fca5a5 !important;
        border: 1px solid rgba(239,68,68,0.22);
        font-size: 0.68rem;
        font-family: var(--mms-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mms-table .badge.bg-light {
        background: rgba(255,255,255,0.05) !important;
        color: #9ca3af !important;
        border: 1px solid rgba(255,255,255,0.1);
        font-size: 0.68rem;
        font-family: var(--mms-font);
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
        .mms-grid {
            grid-template-columns: 1fr;
        }
        .mms-field--full {
            grid-column: 1;
        }
        .mms-images-grid {
            grid-template-columns: 1fr;
        }
        .mms-body {
            padding: 1rem;
        }
    }
</style>