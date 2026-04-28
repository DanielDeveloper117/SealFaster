<!-- ///////////////////////MODAL TABLA CONTROL ALMACEN INVENTARIO ENTREGAR BARRAS/////////////////////// -->
<div class="modal fade" id="modalTableControlAlmacenEntrega" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form d-flex gap-2 align-items-center"><span>Barras para entrega. Folio de requisición: </span>                    
                    <form action="../includes/functions/generar_requisicion.php" method="GET" target="_blank">
                        <input type="hidden" name="id_requisicion">
                        <button type="submit" class="btn btn-link p-0 border-0 text-decoration-underline fs-5"></button>
                    </form>
                </span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex col-12 gap-3 mb-2">
                    <div class="col-3">
                        <button id="addExtraBillet" type="button" class="btn-general">
                            <i class="bi bi-plus-circle"></i> Agregar barra extra
                        </button>
                    </div>
                </div>
                <div style="overflow-x: auto; width: 100%; max-height:300px !important; overflow-y:auto;">
                    <table id="tableEntregarBarras" class="table table-bordered border border-2 tabla-billets mb-3" style="table-layout: fixed; width: max-content;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">ACCIONES</th>
                                <th style="width: 130px;">PERFIL</th>
                                <th style="width: 80px;">COMPONENTE</th>
                                <th style="width: 160px;">MATERIAL</th>
                                <th style="width: 280px;">CLAVE</th>
                                <th style="width: 220px;">LOTE</th>
                                <th style="width: 130px;">MEDIDA BARRA</th>
                                <th style="width: 80px;">PZ TEÓRICAS</th>
                                <th style="width: 100px;">H. TOTAL PERFIL</th>
                                <th style="width: 100px;">H. TEÓRICA COMPONENTE</th>
                                <th style="width: 100px;">TOTAL MM TEÓRICOS</th>
                                <th style="width: 120px;">MM ENTREGA</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 justify-content-center gap-3">
                    <button id="btnGuardarProgresoEntregaBarras" type="button" class="btn-general">
                        <i class="bi bi-floppy"></i> Guardar progreso
                    </button>
                    <button id="btnEntregarBarras" type="button" class="btn-general">
                        <i class="bi bi-database-fill-check"></i> Entregar barras
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /////////////////////// MODAL: AGREGAR BARRA EXTRA /////////////////////// -->
<div class="modal fade" id="modalAddExtra" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-add-extra" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="label-modal-add-extra">Solicitar autorización de barra extra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAddExtraBillet">
                    <input type="hidden" id="idRequisicionExtra" name="id_requisicion">

                    <div class="mb-3">
                        <label for="lotePedimentoExtra" class="lbl-general">Lote *</label>
                        <input id="lotePedimentoExtra" name="lote_pedimento" type="text" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="perfilExtra" class="lbl-general">Perfil *</label>
                        <input id="perfilExtra" name="perfil" type="text" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-6">
                            <label for="pzTeoricasExtra" class="lbl-general">Piezas teóricas *</label>
                            <input id="pzTeoricasExtra" name="pz_teoricas" type="number" min="1" step="1" class="form-control" required>
                        </div>
                        <div class="mb-3 col-6">
                            <label for="alturaPzExtra" class="lbl-general">Altura de pieza *</label>
                            <input id="alturaPzExtra" name="altura_pz" type="number" min="0.01" step="0.01" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-6">
                            <label for="componenteExtra" class="lbl-general">Componente *</label>
                            <input id="componenteExtra" name="componente" type="number" min="1" step="1" class="form-control" value="1" required>
                        </div>
                        <div class="mb-3 col-6">
                            <label for="hComponenteExtra" class="lbl-general">H. Componente *</label>
                            <input id="hComponenteExtra" name="h_componente" type="number" min="0.01" step="0.01" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-6">
                            <label for="mmEntregaExtra" class="lbl-general">MM Entrega *</label>
                            <input id="mmEntregaExtra" name="mm_entrega" type="number" min="0" step="0.01" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="justificacionExtra" class="lbl-general">Justificación *</label>
                        <textarea id="justificacionExtra" name="justificacion" class="form-control" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="enviarAddBillet" class="btn-general">Agregar</button>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////MODAL SOLICITAR REMPLAZO DE BARRA A DIRECCION/////////////////////// -->
<div class="modal fade" id="modalSolicitarRemplazoBarra" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex justify-content-between" style="width:90%;">
                    <h5 class="modal-title">Solicitar autorización de remplazo de barra</h5>
                </div>
                <button id="btnCloseModal" type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formSolicitarRemplazo">                        
                    <input id="inputIdRequisicionRemplazo" type="hidden" >
                    <input id="inputIdControl" type="hidden">
                    <!-- <input id="inputLoteRemplazoA" type="hidden"> -->
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:100%;">
                            <label for="inputLoteRemplazoA" class="lbl-general">Barra a remplazar:</label>
                            <input id="inputLoteRemplazoA" type="text" class="input-disabled">
                        </div>  
                    </div>                     
                    <div class="d-flex justify-content-between ">
                        <div class="" style="width:100%;">
                            <label for="inputLoteRemplazoB" class="lbl-general">Nuevo lote *</label>
                            <input id="inputLoteRemplazoB" type="text" class="input-text" placeholder="Ingrese la barra de remplazo" required>
                        </div>  
                    </div> 
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:100%;">
                            <p id="pValidacionSolicitarRemplazo" class="d-none p-invalida"></p>
                        </div>  
                    </div>  
                    <div class="d-flex justify-content-between">
                        <div class="" style="width:100%;">
                            <label for="inputJustificacionRemplazo" class="lbl-general">Justificación de remplazo *</label>
                            <textarea id="inputJustificacionRemplazo" class="form-control" rows="3" placeholder="Ingrese la justificación del remplazo de barra..."></textarea>
                        </div>  
                    </div>                  

                    <div class="d-flex justify-content-between mt-3">
                        <button id="btnSolicitarRemplazoBarra" type="button" class="btn-general" tabindex="-1">Enviar</button>
                    </div> 
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Solicitar Eliminación de Barra -->
<div class="modal fade" id="modalSolicitarEliminacionBarra" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-eliminacion" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Solicitar eliminación de barra</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea solicitar la eliminación de la barra <strong id="loteEliminarSpan"></strong>?</p>
                <p>Debe proporcionar una justificación para proceder (mínimo 10 caracteres).</p>
                <div class="my-3">
                    <textarea id="inputJustificacionEliminacion" class="form-control" rows="4" placeholder="Ingrese la justificación aquí..." required></textarea>
                </div>
                <div>
                    <input id="inputIdRequisicionEliminar" type="hidden">
                    <input id="inputIdControlEliminar" type="hidden">
                    <button id="btnConfirmarEliminacionBarra" type="button" class="btn-general">Confirmar Solicitud</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ////////////////////////// MARCAR COMO BARRAS ENTREGADAS A CNC DE LA REQUISICION /////////////////////// -->
<div class="modal fade" id="modalDarSalida" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">¿Desea continuar?</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta acción notificará a Sellos Maquinados para comenzar el maquinado. Asegurese de entregar las barras correctas.</p>
                <div>
                    <input id="inputRequisicionDarSalida" type="hidden" name="id_requisicion" >
                    <button id="btnConfirmarDarSalidaBillets" type="button" class="btn-general">Continuar</button>
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
    // Traer las barras de la requisicion para entregar
    function cargarTablaEntregarBarras(idRequisicion, estatusRequisicion, maquinaAsignada) {
        // Mostrar u ocultar los botones según el estatus y máquina asignada:
        // - Autorizada: NO mostrar ningún botón de acción
        // - Producción + máquina asignada: mostrar guardar progreso Y entregar barras
        // - Producción sin máquina: mostrar guardar progreso, NO mostrar entregar barras
        // - Otro: no mostrar ningun botón
        try {
            const tieneMaquina = maquinaAsignada && maquinaAsignada.trim() !== '';

            if (estatusRequisicion === 'Autorizada') {
                // Para Autorizada, no mostrar botones de acción
                $('#btnGuardarProgresoEntregaBarras').addClass('d-none');
                $('#btnEntregarBarras').addClass('d-none');
            } else if (estatusRequisicion === 'Producción' || estatusRequisicion === 'En producción') {
                // Para Producción, mostrar guardar progreso siempre
                $('#btnGuardarProgresoEntregaBarras').removeClass('d-none');
                // Entregar barras solo si tiene máquina asignada
                if (estatusRequisicion === 'Producción' && tieneMaquina) {
                    $('#btnEntregarBarras').removeClass('d-none');
                } else {
                    $('#btnEntregarBarras').addClass('d-none');
                }
            } else {
                // Para otros estatus, no mostrar ningún botón
                $('#btnGuardarProgresoEntregaBarras').addClass('d-none');
                $('#btnEntregarBarras').addClass('d-none');
            }
        } catch (e) {
            // Silently ignore DOM errors (button might not be present yet)
            console.warn('Error toggling button visibility:', e);
        }
        $.ajax({
            url: '../ajax/barras_para_entregar.php',
            type: 'get',
            data: {
                id_requisicion: idRequisicion
            },
            dataType: 'json',
            success: function (data) {
                $('#tableEntregarBarras tbody').empty();

                if (data.success && data.billets.length > 0) {
                    // Pre-calcular el mapa de conteos por grupo (ID Cotizacion, Componente, Clave)
                    // Solo contamos barras activas (no eliminadas y autorizadas si son reemplazo/extra)
                    let conteosClaves = {};
                    $.each(data.billets, function (i, b) {
                        let esActiva = (b.es_eliminacion == 0) && (
                            (b.es_remplazo == 0 && b.es_extra == 0) || 
                            (b.es_remplazo == 1 && b.es_remplazo_auth == 1) || 
                            (b.es_extra == 1 && b.es_extra_auth == 1)
                        );
                        if (esActiva) {
                            let key = `${b.id_cotizacion || ''}_${b.componente || 1}_${b.clave || ''}`;
                            conteosClaves[key] = (conteosClaves[key] || 0) + 1;
                        }
                    });

                    $.each(data.billets, function (index, billet) {
                            // Si hay múltiples cotizaciones, podrías agregar lógica para seleccionar la correcta
                            let material = billet.material || '';
                            let desbasteMaterial = calcularDesbaste(material);
                            // Calcular mmTeoricos de forma segura: si falta pz_teoricas o altura_pz usar 0
                            let rawPz = parseFloat(billet.pz_teoricas);
                            let rawAltura = parseFloat(billet.h_componente);
                            let mmTeoricosVal = rawPz * ((isNaN(rawAltura) ? 0 : rawAltura) + desbasteMaterial);
                            if (!isFinite(mmTeoricosVal) || isNaN(mmTeoricosVal)) mmTeoricosVal = 0;
                            let mmTeoricos = parseFloat(mmTeoricosVal).toFixed(2);
                            console.log(rawPz, "*", "(", rawAltura, "+", desbasteMaterial, ") = ", mmTeoricos);
                            
                            let groupKey = `${billet.id_cotizacion || ''}_${billet.componente || 1}_${billet.clave || ''}`;
                            let hayMasDeUnaActiva = (conteosClaves[groupKey] || 0) > 1;

                            $('#tableEntregarBarras tbody').append(`
                                <tr class="data-row" data-lote="${billet.lote_pedimento}" data-es-eliminacion="${billet.es_eliminacion || 0}">
                                    <input type="hidden" tabindex="-1" name="id_control" class="id_control" value="${billet.id_control ? billet.id_control : ''}">
                                    <input type="hidden" tabindex="-1" name="id_cotizacion" class="id_cotizacion" value="${billet.id_cotizacion ? billet.id_cotizacion : ''}">
                                    <input type="hidden" tabindex="-1" name="id_estimacion" class="id_estimacion" value="${billet.id_estimacion ? billet.id_estimacion : ''}">

                                    <td>
                                        <div class="d-flex gap-2 container-actions">
                                        ${billet.es_remplazo == 0 && billet.es_remplazo_auth == 0 && billet.es_extra == 0 && billet.es_extra_auth == 0 && billet.es_eliminacion == 0
                                            ? `
                                                    <button type="button" class="btn-thunder btn-remplazar-barra"
                                                                data-id-requisicion="${billet.id_requisicion}"
                                                                data-id-control="${billet.id_control}"
                                                                data-clave="${billet.clave}"
                                                                data-lp="${billet.lote_pedimento}"
                                                                data-medida="${billet.medida}"
                                                                title="Remplazar barra por otra">
                                                        <i class="bi bi-repeat"></i>
                                                    </button>
                                                `
                                            : ``
                                        }
                                        ${billet.es_remplazo == 0 && billet.es_remplazo_auth == 0 && billet.es_extra == 0 && billet.es_extra_auth == 0 && billet.es_eliminacion == 0 && hayMasDeUnaActiva
                                            ? `
                                                    <button type="button" class="btn-eliminar btn-solicitar-eliminacion-barra"
                                                                data-id-requisicion="${billet.id_requisicion}"
                                                                data-id-control="${billet.id_control}"
                                                                data-lp="${billet.lote_pedimento}"
                                                                title="Solicitar eliminación de barra">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                `
                                            : ``
                                        }                                    
                                    ${billet.es_remplazo == 1 && billet.es_remplazo_auth == 0
                                        ? `<small class="text-warning fw-semibold">Autorización pendiente</small>`
                                        : ``
                                    }
                                    ${billet.es_remplazo == 1 && billet.es_remplazo_auth == 1 && billet.es_extra == 0 && billet.es_extra_auth == 0
                                        ? `<small class="text-success fw-semibold">Remplazo autorizado</small>`
                                        : ``
                                    }
                                    ${billet.es_remplazo == 1 && billet.es_remplazo_auth == 1 && billet.es_extra == 1 && billet.es_extra_auth == 1
                                        ? `<small class="text-success fw-semibold">Remplazo autorizado</small>`
                                        : ``
                                    }
                                    ${billet.es_remplazo == 0 && billet.es_remplazo_auth == 0 && billet.es_extra == 1 && billet.es_extra_auth == 1
                                        ? `<button type="button" class="btn-thunder btn-remplazar-barra"
                                                    data-id-requisicion="${billet.id_requisicion}"
                                                    data-id-control="${billet.id_control}"
                                                    data-clave="${billet.clave}"
                                                    data-lp="${billet.lote_pedimento}"
                                                    data-medida="${billet.medida}"
                                                    title="Remplazar barra por otra">
                                            <i class="bi bi-repeat"></i>
                                        </button>`
                                        : ``
                                    }
                                    ${billet.es_remplazo == 0 && billet.es_remplazo_auth == 0 && billet.es_extra == 1 && billet.es_extra_auth == 0
                                        ? `<small class="text-warning fw-semibold">Autorización pendiente</small>`
                                        : ``
                                    }
                                    ${billet.es_eliminacion == 1 && billet.es_eliminacion_auth == 0
                                        ? `<small class="text-warning fw-semibold">Eliminación en espera</small>`
                                        : ``
                                    }
                                    </div>
                                </td>


                                <td>
                                ${billet.perfil_sello ?
                                    `<input type="text" class="input-disabled perfil_sello" name="perfil_sello" value="${billet.perfil_sello}" required tabindex="-1">` :
                                    `<input type="text" class="input-text perfil_sello" name="perfil_sello" value="${billet.perfil_sello || ''}" placeholder="Ingrese perfil sello" required>`
                                }
                                </td>
                                <td>
                                    <div class="d-flex align-items-center container-actions gap-2">
                                        <input type="number" tabindex="-1" class="input-disabled componente" name="componente" value="${billet.componente || 1}" readonly>
                                        <button type="button" class="btn-general btn-ver-componente" 
                                            data-id-cotizacion="${billet.id_cotizacion || ''}" 
                                            data-componente="${billet.componente || 1}"
                                            data-perfil="${billet.perfil_sello || ''}"
                                            title="Ver componente">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                ${billet.material ?
                                    `<input type="text" tabindex="-1" class="input-disabled material" name="material" value="${billet.material}" required>` :
                                    `<input type="text" tabindex="-1" class="input-text material" name="material" value="${billet.material || ''}" placeholder="Ingrese material" required>`
                                }
                                </td>
                                <td>
                                ${billet.clave_remplazo && billet.clave_remplazo.trim() !== ''
                                    ? `<div class="d-flex flex-column gap-2">
                                            <input type="text" class="input-disabled clave" name="clave" tabindex="-1"
                                                    value="${billet.clave || ''}" required>
                                            <span class="span-remplazar-por">Clave remplazo:</span>
                                            <input type="text" class="input-disabled clave_remplazo" name="clave_remplazo" tabindex="-1"
                                                    value="${billet.clave_remplazo}" required>
                                        </div>`
                                    : `<input type="text" class="input-disabled clave" name="clave" tabindex="-1"
                                                value="${billet.clave || ''}" placeholder="Ingrese clave" required>`
                                }
                                </td>
                                <td>
                                ${billet.lp_remplazo
                                    ? `<div class="d-flex flex-column gap-2">
                                                    <input type="text" class="input-disabled lote_pedimento" name="lote_pedimento" tabindex="-1"
                                                        value="${billet.lote_pedimento || ''}" required>
                                                    <span class="span-remplazar-por">Remplazar por:</span>
                                                    <input type="text" class="input-disabled lp_remplazo" name="lp_remplazo" tabindex="-1"
                                                        value="${billet.lp_remplazo}" placeholder="Ingrese lote" required>
                                                </div>`
                                    : `<input type="text" class="input-disabled lote_pedimento" name="lote_pedimento" tabindex="-1"
                                                        value="${billet.lote_pedimento || ''}" placeholder="Ingrese lote" required>`
                                }
                                </td>
                                <td>
                                ${billet.medida_remplazo && billet.medida_remplazo.trim() !== ''
                                    ? `<div class="d-flex flex-column gap-2">
                                            <input type="text" class="input-disabled medida" name="medida" 
                                                    value="${billet.medida || ''}" required tabindex="-1">
                                            <span class="span-remplazar-por">Medida remplazo:</span>
                                            <input type="text" class="input-disabled medida_remplazo" name="medida_remplazo" tabindex="-1"
                                                    value="${billet.medida_remplazo}" required>
                                        </div>`
                                    : `<input type="text" class="input-disabled medida" name="medida" 
                                                value="${billet.medida || ''}" placeholder="Ingrese medida (di/de)" required tabindex="-1">`
                                }
                                </td>
                                <td>
                                ${billet.pz_teoricas ?
                                    `<input type="number" class="input-text pz_teoricas" name="pz_teoricas" min="0" step="1" value="${billet.pz_teoricas}" required >` :
                                    `<input type="number" class="input-text pz_teoricas" name="pz_teoricas" min="0" step="1" value="${billet.pz_teoricas || ''}" placeholder="Ingrese pz a maquinar" required>`
                                }
                                </td>
                                <td>
                                ${billet.altura_pz ?
                                    `<input type="text" class="input-disabled altura_pz" name="altura_pz" value="${billet.altura_pz}" required tabindex="-1">` :
                                    `<input type="text" class="input-text altura_pz" name="altura_pz" value="${billet.altura_pz || ''}" placeholder="Ingrese altura del sello" required>`
                                }
                                </td>
                                <td>
                                    <input type="number" class="input-disabled h_componente" name="h_componente" value="${billet.h_componente || ''}" readonly tabindex="-1">
                                </td>
                                <td><input type="text" class="input-disabled mm_teoricos" value="${mmTeoricos}" readonly tabindex="-1"></td>
                                <td><input type="number" class="input-text mm_entrega" name="mm_entrega" value="${billet.mm_entrega != undefined  ? billet.mm_entrega : ''}" step="0.01" min="0" required></td>                        
                            </tr>
                            <tr class="row-justificar-remplazo ${billet.justificacion_remplazo && billet.justificacion_remplazo.trim() !== '' ? '' : 'd-none'}">
                                <td colspan="12">
                                    <div class="d-flex flex-column justify-content-start align-items-start">
                                    <label class="mb-2 text-secondary">
                                        Justificación de remplazo de la barra <strong>${billet.lote_pedimento || ''}:</strong>
                                    </label> 
                                    <input type="text" class="input-disabled justificacion_remplazo" name="justificacion_remplazo" 
                                            value="${billet.justificacion_remplazo || ''}" readonly tabindex="-1">
                                    </div>
                                </td>
                            </tr>

                        `);

                        // Adjuntar listeners a los inputs relevantes para recalcular mmTeoricos cuando el usuario escriba
                        // Hacemos esto dentro del scope de la iteración para que 'material' y 'desbasteMaterial' estén disponibles
                        (function (materialLocal, desbasteLocal) {
                            const $thisRow = $('#tableEntregarBarras tbody tr.data-row').last();

                            // Si material o medida faltan, intentar autocompletar desde inventario via AJAX usando el lote
                            try {
                                const currentMaterial = ($thisRow.find('.material').val() || '').toString().trim();
                                const currentMedida = ($thisRow.find('.medida').val() || '').toString().trim();
                                if (!currentMaterial || !currentMedida) {
                                    $.ajax({
                                        url: '../ajax/info_lote_pedimento.php',
                                        type: 'POST',
                                        data: { billet: billet.lote_pedimento },
                                        dataType: 'json',
                                        success: function (resp) {
                                            if (resp && resp.success && resp.billetResult) {
                                                const info = resp.billetResult;
                                                // Rellenar material si no existe
                                                if (!currentMaterial && info.material) {
                                                    $thisRow.find('.material').val(info.material)
                                                        .removeClass('input-text').addClass('input-disabled').prop('readonly', true);
                                                }
                                                // Rellenar medida (di/de) si no existe
                                                if (!currentMedida && info.medida) {
                                                    $thisRow.find('.medida').val(info.medida)
                                                        .removeClass('input-text').addClass('input-disabled').prop('readonly', true);
                                                }
                                                // Recalcular mmTeoricos inmediatamente si hay valores
                                                const pzNow = parseFloat($thisRow.find('.pz_teoricas').val()) || 0;
                                                const alturaNow = parseFloat($thisRow.find('.h_componente').val()) || 0;
                                                const desbNow = calcularDesbaste(info.material || materialLocal || $thisRow.find('.material').val() || '');
                                                let mmNow = pzNow * (alturaNow + desbNow);
                                                if (!isFinite(mmNow) || isNaN(mmNow)) mmNow = 0;
                                                $thisRow.find('.mm_teoricos').val(parseFloat(mmNow).toFixed(2));
                                            } else {
                                                // no existe el lote en inventario: dejar los inputs editables (ya lo son por defecto)
                                            }
                                        },
                                        error: function (err) {
                                            console.warn('No se pudo obtener info del lote:', err);
                                        }
                                    });
                                }
                            } catch (e) {
                                console.warn('Error al intentar autocompletar material/medida:', e);
                            }

                            $thisRow.find('.pz_teoricas, .h_componente').on('input change', function () {
                                const pz = parseFloat($thisRow.find('.pz_teoricas').val()) || 0;
                                const altura = parseFloat($thisRow.find('.h_componente').val()) || 0;
                                // recalcular desbaste en caso de que material cambie o no esté definido
                                const desb = calcularDesbaste(materialLocal || $thisRow.find('.material').val() || '');
                                let mm = pz * (altura + desb);
                                if (!isFinite(mm) || isNaN(mm)) mm = 0;
                                $thisRow.find('.mm_teoricos').val(parseFloat(mm).toFixed(2));
                            });
                        })(material, desbasteMaterial);
                    });

                    // Si la requisición NO es Producción, convertir TODOS los inputs de la tabla a solo lectura
                    // y quitar el atributo required para que no bloqueen validaciones posteriores.
                    try {
                        if (typeof estatusRequisicion !== 'undefined' && estatusRequisicion !== 'Producción') {
                            const $allInputs = $('#tableEntregarBarras tbody').find('input, textarea, select');
                            $allInputs.each(function () {
                                const $el = $(this);
                                // No convertir botones (aunque suelen ser <button>), y evitar tocar inputs ocultos si es necesario
                                if ($el.is(':button') || $el.is(':submit')) return;
                                // Marcar readonly (mantiene el valor enviado en formularios si fuese necesario)
                                try { $el.prop('readonly', true); } catch (e) { /* algunos elementos podrían no soportar readonly */ }
                                // Quitar required para que las validaciones de cliente no impidan acciones
                                $el.prop('required', false);
                                // Cambiar clases visuales para indicar que son no-editables
                                $el.removeClass('input-text').addClass('input-disabled');
                            });
                            if(estatusRequisicion == 'Producción' || estatusRequisicion == 'En producción'){
                                $('#tableEntregarBarras tbody .pz_teoricas').each(function () {
                                    const $row = $(this);
                                    $row.removeClass('input-disabled').addClass('input-text').prop('readonly', false);
                                });
                            }
                        }
                    } catch (e) {
                        console.warn('Error aplicando modo solo lectura en tablaEntregarBarras:', e);
                    }
                } else {
                    $('#tableEntregarBarras tbody').append('<tr><td colspan="14" class="text-center">No hay barras disponibles para esta requisición.</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al consultar los datos de las barras: " + error, "none");
            }
        });
    }


    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        // VER LA TABLA DE BARRAS DE CONTROL DE ALMACEN PARA ENTREGAR
        $(document).on('click', '.btn-entregar-barras', function () {
            const idRequisicionEntrega = $(this).data('id_requisicion');
            const estatusRequisicionEntrega = $(this).data('estatus');
            const maquinaAsignada = $(this).data('maquina');

            $('#modalTableControlAlmacenEntrega .title-form form input').val(idRequisicionEntrega);
            $('#modalTableControlAlmacenEntrega .title-form form button').text(idRequisicionEntrega);
            // Guardar el estatus en el modal para poder reutilizarlo al recargar la tabla
            $('#modalTableControlAlmacenEntrega').data('estatus-requi', estatusRequisicionEntrega);
            // Guardar la máquina asignada en el modal
            $('#modalTableControlAlmacenEntrega').data('maquina-asignada', maquinaAsignada);

            cargarTablaEntregarBarras(idRequisicionEntrega, estatusRequisicionEntrega, maquinaAsignada);
        });
        // CLICK VER TABLA DE BARRAS DESDE EL MODAL
        $(document).on('click', '.btn-remplazar-barra', function () {
            $('#modalTableControlAlmacenEntrega').modal('show');
            $('#modalSolicitarRemplazoBarra').modal('show');

            let idRequisicionRemplazo = $(this).data("id-requisicion");
            let dataIdControl = $(this).data("id-control");
            let dataClave = $(this).data("clave");
            let dataLotePedimento = $(this).data("lp");
            let dataMedida = $(this).data("medida");
            $("#inputIdRequisicionRemplazo").val(idRequisicionRemplazo);
            $("#inputIdControl").val(dataIdControl);
            $("#inputClaveRemplazoA").val(dataClave);
            $("#inputLoteRemplazoA").val(dataLotePedimento);
            $("#inputMedidaRemplazoA").val(dataMedida);
        });
        // ENVIAR LA SOLICITUD DE REMPLAZO DE BARRA
        $("#btnSolicitarRemplazoBarra").on("click", function () {
            let idRequisicionRemplazo = $("#inputIdRequisicionRemplazo").val();
            let idControl = $("#inputIdControl").val();
            let barraRemplazoA = $("#inputLoteRemplazoA").val();
            let barraRemplazoB = $("#inputLoteRemplazoB").val();
            let justificacionRemplazo = $("#inputJustificacionRemplazo").val();

            if (barraRemplazoB !== "") {
                $("#btnSolicitarRemplazoBarra").addClass("d-none");

                $.ajax({
                    url: '../ajax/solicitar_remplazo_barra.php',
                    type: 'POST',
                    data: {
                        id_requisicion: idRequisicionRemplazo,
                        id_control: idControl,
                        barra_a: barraRemplazoA,
                        barra_b: barraRemplazoB,
                        justificacion_remplazo: justificacionRemplazo
                    },
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            $('#modalTableControlAlmacenEntrega').modal('hide');
                            $('#modalSolicitarRemplazoBarra').modal('hide');
                            sweetAlertResponse("success", "Proceso exitoso", data.message, "none");
                            $("#pValidacionSolicitarRemplazo").addClass("d-none");
                            $("#btnSolicitarRemplazoBarra").removeClass("d-none");
                            $("#formSolicitarRemplazo")[0].reset();
                        } else {
                            sweetAlertResponse("warning", "Advertencia", data.message, "none");
                            $("#pValidacionSolicitarRemplazo").removeClass("d-none");
                            $('#pValidacionSolicitarRemplazo').text(data.message);
                            $("#btnSolicitarRemplazoBarra").removeClass("d-none");
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error al realizar la petición AJAX:', error);
                        console.error('Respuesta del servidor:', xhr.responseText); // Muestra el error enviado por PHP
                        $("#pValidacionSolicitarRemplazo").removeClass("d-none");
                        $('#pValidacionSolicitarRemplazo').text('Error en ajax solicitar remplazo de barra.');
                        $("#btnSolicitarRemplazoBarra").removeClass("d-none");
                    }
                });
            } else {
                sweetAlertResponse("warning", "Advertencia", "Debe digitar el lote de la barra de remplazo", "none");
            }
        });

        // SOLICITAR ELIMINACIÓN DE BARRA
        $(document).on('click', '.btn-solicitar-eliminacion-barra', function () {
            const idRequisicion = $(this).data('id-requisicion');
            const idControl = $(this).data('id-control');
            const lotePedimento = $(this).data('lp');

            $('#inputIdRequisicionEliminar').val(idRequisicion);
            $('#inputIdControlEliminar').val(idControl);
            $('#loteEliminarSpan').text(lotePedimento);
            $('#inputJustificacionEliminacion').val('');
            
            $('#modalSolicitarEliminacionBarra').modal('show');
        });

        // CONFIRMAR ELIMINACIÓN DE BARRA (Desde el nuevo modal Bootstrap)
        $(document).on('click', '#btnConfirmarEliminacionBarra', function () {
            const idRequisicion = $('#inputIdRequisicionEliminar').val();
            const idControl = $('#inputIdControlEliminar').val();
            const justificacion = $('#inputJustificacionEliminacion').val().trim();

            if (!justificacion || justificacion.length < 10) {
                sweetAlertResponse('warning', 'Justificación insuficiente', 'La justificación debe tener al menos 10 caracteres.', 'none');
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).text('Procesando...');

            $.ajax({
                url: '../ajax/solicitar_eliminacion_barra.php',
                type: 'POST',
                data: {
                    id_requisicion: idRequisicion,
                    id_control: idControl,
                    justificacion_eliminacion: justificacion
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#modalSolicitarEliminacionBarra').modal('hide');
                        sweetAlertResponse('success', 'Solicitud enviada', response.message, 'none');
                        
                        // Recargar la tabla si es posible
                        const estReq = $('#modalTableControlAlmacenEntrega').data('estatus-requi') || '';
                        const maqReq = $('#modalTableControlAlmacenEntrega').data('maquina-asignada') || '';
                        cargarTablaEntregarBarras(idRequisicion, estReq, maqReq);
                    } else {
                        sweetAlertResponse('error', 'Error', response.message || 'Error al procesar la solicitud', 'none');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error al solicitar eliminación:', error);
                    sweetAlertResponse('error', 'Error', 'Ocurrió un error en la comunicación con el servidor.', 'none');
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Confirmar Solicitud');
                }
            });
        });
        // Mostrar modal para agregar barra extra desde el modal de entrega
        $('#addExtraBillet').on('click', function () {
            $('#modalAddExtra').modal('show');
            // Intentar obtener el id de requisición desde el modalTableControlAlmacenEntrega
            var idRequi = '';
            try {
                // El formulario dentro del modalTableControlAlmacenEntrega contiene un input hidden (si existe)
                idRequi = $('#modalTableControlAlmacenEntrega .title-form form input[name="id_requisicion"]').val() || $('#inputIdRequisicion').val() || '';
            } catch (e) {
                console.warn('No se pudo obtener id de requisición desde el modalTableControlAlmacenEntrega:', e);
            }

            $('#idRequisicionExtra').val(idRequi);
        });
        // Handler submit para agregar barra extra
        $('#enviarAddBillet').on('click', function (e) {
            e.preventDefault();

            var id_requisicion = $('#idRequisicionExtra').val();
            var lote_pedimento = $('#lotePedimentoExtra').val().trim();
            var perfil = $('#perfilExtra').val().trim();
            var pz_teoricas = $('#pzTeoricasExtra').val();
            var altura_pz = $('#alturaPzExtra').val();
            var componente = $('#componenteExtra').val();
            var h_componente = $('#hComponenteExtra').val();
            var mm_entrega = $('#mmEntregaExtra').val();
            var justificacion = $('#justificacionExtra').val().trim();

            // Validación básica (ahora incluye mm_entrega, componente y h_componente)
            if (!lote_pedimento || !perfil || pz_teoricas === '' || altura_pz === '' || componente === '' || h_componente === '' || mm_entrega === '' || !justificacion) {
                sweetAlertResponse('warning', 'Campos incompletos', 'Todos los campos son obligatorios.', 'none');
                return;
            }

            // Validar mm_entrega numérico y >= 0
            if (isNaN(parseFloat(mm_entrega)) || parseFloat(mm_entrega) < 0) {
                sweetAlertResponse('warning', 'Valor inválido', 'MM Entrega debe ser un número mayor o igual a 0.', 'none');
                return;
            }

            // Deshabilitar botón para evitar envíos duplicados
            var $btn = $(this);
            $btn.prop('disabled', true).text('Enviando...');

            // Enviar a endpoint que procesa la solicitud de barra extra
            $.ajax({
                url: '../ajax/solicitar_extra_billet.php',
                method: 'POST',
                data: {
                    id_requisicion: id_requisicion,
                    lote_pedimento: lote_pedimento,
                    perfil: perfil,
                    pz_teoricas: pz_teoricas,
                    altura_pz: altura_pz,
                    componente: componente,
                    h_componente: h_componente,
                    mm_entrega: mm_entrega,
                    justificacion_extra: justificacion
                },
                dataType: 'json',
                success: function (resp) {
                    if (resp && resp.success) {
                        sweetAlertResponse('success', 'Barra agregada', resp.message || 'La barra extra se agregó correctamente.', 'none');
                        // Cerrar modal y refrescar tabla de entrega (si existe la función)
                        $('#modalAddExtra').modal('hide');
                        try {
                            var idReq = id_requisicion || $('#modalTableControlAlmacenEntrega .title-form form input[name="id_requisicion"]').val();
                            var estReq = $('#modalTableControlAlmacenEntrega').data('estatus-requi') || '';
                            var maqReq = $('#modalTableControlAlmacenEntrega').data('maquina-asignada') || '';
                            if (typeof cargarTablaEntregarBarras === 'function' && idReq) {
                                cargarTablaEntregarBarras(idReq, estReq, maqReq);
                            }
                        } catch (e) { console.warn(e); }

                        // Resetear formulario SOLO en caso de éxito
                        $("#formAddExtraBillet")[0].reset();
                        $btn.prop('disabled', false).text('Agregar');
                    } else {
                        sweetAlertResponse('warning', 'Advertencia', (resp && resp.message) ? resp.message : 'Ocurrió un problema al agregar la barra extra.', 'none');
                        // No resetear el formulario: mantener los valores para corrección
                        $btn.prop('disabled', false).text('Agregar');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error AJAX al agregar barra extra:', xhr.responseText || error);
                    sweetAlertResponse('error', 'Error', 'Ocurrió un error al agregar la barra extra.', 'self');
                    // Rehabilitar botón pero NO resetear formulario para que el usuario pueda corregir
                    $btn.prop('disabled', false).text('Agregar');
                }
            });
        });
        // DAR SALIDA A LOS BILLETS QUE AGREGO INVENTARIOS
        $("#btnEntregarBarras").on('click', function () {
            $('#modalDarSalida').modal('show');
            // (se establece cuando se abre la tabla de entrega). Si no existe, usar
            // cualquier data attribute disponible como fallback.
            let idRequisicionSalida = $('#modalTableControlAlmacenEntrega .title-form form input').val();
            if (!idRequisicionSalida) {
                // intentar por nombre de input dentro del modal (por si cambia el selector)
                idRequisicionSalida = $('#modalTableControlAlmacenEntrega input[name="id_requisicion"]').val();
            }
            if (!idRequisicionSalida) {
                // último recurso: usar data attribute del botón (si existe)
                idRequisicionSalida = $(this).data('id-requisicion') || $(this).data('id_requisicion');
            }

            $("#inputRequisicionDarSalida").val(idRequisicionSalida);
        });
        // GUARDAR PROGRESO DE ENTREGA DE BARRAS (pz_teoricas y mm_entrega)
        $("#btnGuardarProgresoEntregaBarras").on('click', function () {
            let $btn = $(this);
            let idRequisicion = $('#modalTableControlAlmacenEntrega .title-form form input').val();

            // Recopilar datos de la tabla
            const registros = [];
            $('#tableEntregarBarras tbody tr.data-row').each(function () {
                const $row = $(this);
                registros.push({
                    id_control: $row.find('.id_control').val(),
                    lote_pedimento: $row.find('.lote_pedimento').val(),
                    pz_teoricas: $row.find('.pz_teoricas').val(),
                    mm_entrega: $row.find('.mm_entrega').val()
                });
            });

            if (registros.length === 0) {
                sweetAlertResponse("warning", "Sin datos", "No hay registros para guardar el progreso.", "none");
                return;
            }

            // Deshabilitar botón mientras se procesa
            $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Guardando...');

            // Enviar al servidor
            $.ajax({
                url: '../ajax/guardar_progreso_entrega_barras.php',
                type: 'POST',
                data: {
                    registros: JSON.stringify(registros)
                },
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        sweetAlertResponse("success", "Éxito", data.message || "Progreso guardado correctamente", "none");
                        // Recargar la tabla
                        cargarTablaEntregarBarras(idRequisicion, $('#modalTableControlAlmacenEntrega').data('estatus-requi'), $('#modalTableControlAlmacenEntrega').data('maquina-asignada'));
                    } else {
                        sweetAlertResponse("error", "Error", data.error || "Error al guardar el progreso", "none");
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error al guardar progreso:', error);
                    sweetAlertResponse("error", "Error", "Error al guardar el progreso: " + error, "none");
                },
                complete: function () {
                    // Rehabilitar botón
                    $btn.prop('disabled', false).html('<i class="bi bi-floppy"></i> Guardar progreso');
                }
            });
        });
        // ACCION DE ENTREGAR LAS BARRAS A CNC PARA QUE COMIENCE EL MAQUINADO
        $("#btnConfirmarDarSalidaBillets").on('click', function () {
            let $btnConfirm = $(this);
            let inputIdRequisicionSalida = $("#inputRequisicionDarSalida").val();

            // Validar que todos los inputs requeridos de la tabla hayan sido llenados
            const filas = $('#tableEntregarBarras tbody tr.data-row');
            let errores = [];

            filas.each(function () {
                const $fila = $(this);
                // Si la barra ha sido solicitada para eliminación, saltar validación de campos
                if ($fila.data('es-eliminacion') == 1) return;

                const lote = ($fila.data('lote') || '').toString();
                // Seleccionar inputs que tengan el atributo required
                $fila.find('input[required]').each(function () {
                    const $inp = $(this);
                    // Los inputs tipo number pueden tener value === '' o NaN
                    const val = ($inp.val() || '').toString().trim();
                    if (val === '') {
                        const nombre = $inp.attr('name') || $inp.attr('class') || 'campo';
                        errores.push(`Lote ${lote}: ${nombre} vacío`);
                    }
                    if (val == 0) {
                        const nombre = $inp.attr('name') || $inp.attr('class') || 'campo';
                        errores.push(`Lote ${lote}: ${nombre} no debe ser 0`);
                    }
                });
            });

            if (errores.length > 0) {
                // Mostrar advertencia y no proceder
                const mensaje = 'Complete los campos obligatorios antes de entregar:\n' + errores.slice(0, 10).join('\n') + (errores.length > 10 ? `\n...y ${errores.length - 10} más` : '');
                sweetAlertResponse('warning', 'Faltan campos', mensaje, 'none');
                $('#modalDarSalida').modal('hide');
                return;
            }

            // Opcional: validar que haya al menos un valor numérico positivo en mm_entrega
            let anyMmEntregaInvalid = false;
            filas.each(function () {
                const $fila = $(this);
                // Si la barra ha sido solicitada para eliminación, saltar validación de mm_entrega
                if ($fila.data('es-eliminacion') == 1) return;

                const mmEntregaVal = $fila.find('input.mm_entrega').val();
                if (mmEntregaVal === '' || isNaN(parseFloat(mmEntregaVal)) || parseFloat(mmEntregaVal) < 0) {
                    anyMmEntregaInvalid = true;
                }
            });
            if (anyMmEntregaInvalid) {
                sweetAlertResponse('warning', 'Valores inválidos', 'Revise que las cantidades en "mm_entrega" sean números válidos y mayores o iguales a 0.', 'none');
                return;
            }

            // Todo correcto: deshabilitar botón para evitar envíos duplicados y proceder
            $btnConfirm.addClass('d-none');

            // Construir el array de registros a enviar (uno por fila)
            let registros = [];
            filas.each(function () {
                const $fila = $(this);
                const registro = {
                    id_control: $fila.find('.id_control').val() || null,
                    perfil_sello: ($fila.find('.perfil_sello').val() || '').toString().trim(),
                    material: ($fila.find('.material').val() || '').toString().trim(),
                    clave: ($fila.find('.clave').val() || '').toString().trim(),
                    clave_remplazo: ($fila.find('.clave_remplazo').val() || '').toString().trim(),
                    lote_pedimento: ($fila.find('.lote_pedimento').val() || '').toString().trim(),
                    lp_remplazo: ($fila.find('.lp_remplazo').val() || '').toString().trim(),
                    medida: ($fila.find('.medida').val() || '').toString().trim(),
                    medida_remplazo: ($fila.find('.medida_remplazo').val() || '').toString().trim(),
                    pz_teoricas: parseFloat($fila.find('.pz_teoricas').val()) || 0,
                    altura_pz: parseFloat($fila.find('.altura_pz').val()) || 0,
                    mm_entrega: parseFloat($fila.find('.mm_entrega').val()) || 0,
                    mm_teoricos: parseFloat($fila.find('.mm_teoricos').val()) || 0,
                    lote_original: ($fila.data('lote') || '').toString()
                };
                registros.push(registro);
            });

            // Enviar al servidor (id_requisicion + registros JSON)
            $.ajax({
                url: '../ajax/entregar_barras.php',
                type: 'POST',
                data: {
                    id_requisicion: inputIdRequisicionSalida,
                    registros: JSON.stringify(registros)
                },
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                    } else {
                        sweetAlertResponse("warning", "Advertencia", data.message, "none");
                        $("#btnConfirmarDarSalidaBillets").removeClass("d-none");
                        $('#modalDarSalida').modal('hide');
                        $('#modalTableControlAlmacenEntrega').modal('hide');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error al realizar la petición AJAX:', error);
                    sweetAlertResponse("error", "Error", "Error al actualizar registro. " + error, "self");
                    // habilitar botón nuevamente para reintento
                    $("#btnConfirmarDarSalidaBillets").removeClass("d-none");
                }
            });
        });
    });
</script>