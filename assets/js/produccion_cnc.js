$(document).ready(function(){
    // Funciones auxiliares de validación
    function esEnteroValido(valor) {
        return /^\d+$/.test(valor);
    }

    function esDecimalValido(valor) {
        return /^\d+(\.\d{1,2})?$/.test(valor);
    }

    function formatearDecimal(valor) {
        return parseFloat(valor).toFixed(2);
    }
    // Cuando CNC desea editar las medidas cuando por ejemplo eran medidas muestra
    function ajaxTraerCotizaciones(idRequisicion){
        $.ajax({
            url: '../ajax/traer_medidas_cotizaciones.php', 
            type: 'get',
            data: { 
                id_requisicion: idRequisicion
            },
            dataType: 'json',
            success: function(data) {
                $('#modalEditarMedidas .modal-body').empty(); // Corrige selector

                if (data.length > 0) {
                    $.each(data, function(index, item) {
                        $('#modalEditarMedidas .modal-body').append(`
                            <div style="width:100%; margin-bottom:20px;">
                                <h5 class="modal-title">Id cotización: <span>${item.id_cotizacion}</span></h5>
                                <table class="tabla-medidas table table-bordered border border-2 tabla-billets" data-id_cotizacion="${item.id_cotizacion}">
                                    <thead>
                                        <tr>
                                            <th>DI MM</th>
                                            <th>DI INCH</th>
                                            <th>DE MM</th>
                                            <th>DE INCH</th>
                                            <th>A MM</th>
                                            <th>A INCH</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="number" class="input-text di_sello" name="di_sello" value="${item.di_sello || ''}" step="0.01" min="0"></td>
                                            <td><input type="number" class="input-text di_sello_inch" name="di_sello_inch" value="${item.di_sello_inch || ''}" step="0.0001" min="0"></td>
                                            <td><input type="number" class="input-text de_sello" name="de_sello" value="${item.de_sello || ''}" step="0.01" min="0"></td>
                                            <td><input type="number" class="input-text de_sello_inch" name="de_sello_inch" value="${item.de_sello_inch || ''}" step="0.0001" min="0"></td>
                                            <td><input type="number" class="input-text a_sello" name="a_sello" value="${item.a_sello || ''}" step="0.01" min="0"></td>
                                            <td><input type="number" class="input-text a_sello_inch" name="a_sello_inch" value="${item.a_sello_inch || ''}" step="0.0001" min="0"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        `);
                    });
                } else {
                    $('#modalEditarMedidas .modal-body').append('<p>No hay cotizaciones disponibles para esta requisición.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                $('#modalEditarMedidas .modal-body').append('<h5>Error en ajax</h5>');
                sweetAlertResponse("error", "Error", "Error al consultar cotizaciones: " + error, "none");
            }
        });
    }
    // Traer las barras de la requisicion para entregar
    function cargarTablaEntregarBarras(idRequisicion, estatusRequisicion, maquinaAsignada){
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
            } else if (estatusRequisicion === 'Producción') {
                // Para Producción, mostrar guardar progreso siempre
                $('#btnGuardarProgresoEntregaBarras').removeClass('d-none');
                // Entregar barras solo si tiene máquina asignada
                if (tieneMaquina) {
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
            success: function(data) {
                $('#tableEntregarBarras tbody').empty();

                if (data.success && data.billets.length > 0) {
                    $.each(data.billets, function(index, billet) {
                        // Si hay múltiples cotizaciones, podrías agregar lógica para seleccionar la correcta
                        let material = billet.material || '';
                        let desbasteMaterial = calcularDesbaste(material);
                        // Calcular mmTeoricos de forma segura: si falta pz_teoricas o altura_pz usar 0
                        let rawPz = parseFloat(billet.pz_teoricas);
                        let rawAltura = parseFloat(billet.altura_pz);
                        let mmTeoricosVal = rawPz * ((isNaN(rawAltura) ? 0 : rawAltura) + desbasteMaterial);
                        if (!isFinite(mmTeoricosVal) || isNaN(mmTeoricosVal)) mmTeoricosVal = 0;
                        let mmTeoricos = parseFloat(mmTeoricosVal).toFixed(2);
                        console.log(rawPz, "*","(",rawAltura,"+",desbasteMaterial,") = ",mmTeoricos);

                        // <button type="button" class="btn-eliminar btn-eliminar-barra"
                        //             data-id-requisicion="${data.id_requisicion}"
                        //             data-id-control="${billet.id_control}"
                        //             title="Eliminar barra de este folio">
                        //     <i class="bi bi-trash"></i>
                        // </button>

                        $('#tableEntregarBarras tbody').append(`
                            <tr class="data-row" data-lote="${billet.lote_pedimento}">
                                <input type="hidden" tabindex="-1" name="id_control" class="id_control" value="${billet.id_control ? billet.id_control : ''}">
                                <input type="hidden" tabindex="-1" name="id_cotizacion" class="id_cotizacion" value="${billet.id_cotizacion ? billet.id_cotizacion : ''}">
                                <input type="hidden" tabindex="-1" name="id_estimacion" class="id_estimacion" value="${billet.id_estimacion ? billet.id_estimacion : ''}">

                                <td>
                                    ${billet.es_remplazo == 0 && billet.es_remplazo_auth == 0 && billet.es_extra == 0 && billet.es_extra_auth == 0
                                        ? `<div class="d-flex gap-2 container-actions">
                                            <button type="button" class="btn-thunder btn-remplazar-barra"
                                                        data-id-requisicion="${data.id_requisicion}"
                                                        data-id-control="${billet.id_control}"
                                                        data-clave="${billet.clave}"
                                                        data-lp="${billet.lote_pedimento}"
                                                        data-medida="${billet.medida}"
                                                        title="Remplazar barra por otra">
                                                <i class="bi bi-repeat"></i>
                                            </button>

                                        </div>`
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
                                                    data-id-requisicion="${data.id_requisicion}"
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
                                </td>


                                <td>
                                    ${billet.perfil_sello ? 
                                        `<input type="text" class="input-disabled perfil_sello" name="perfil_sello" value="${billet.perfil_sello}" required>` : 
                                        `<input type="text" class="input-text perfil_sello" name="perfil_sello" value="${billet.perfil_sello || ''}" placeholder="Ingrese perfil sello" required>`
                                    }
                                </td>
                                <td>
                                    ${billet.material ? 
                                        `<input type="text" class="input-disabled material" name="material" value="${billet.material}" required>` : 
                                        `<input type="text" class="input-text material" name="material" value="${billet.material || ''}" placeholder="Ingrese material" required>`
                                    }
                                </td>
                                <td>
                                ${billet.clave_remplazo && billet.clave_remplazo.trim() !== ''
                                    ? `<div class="d-flex flex-column gap-2">
                                        <input type="text" class="input-disabled clave" name="clave" 
                                                value="${billet.clave || ''}" required>
                                        <span class="span-remplazar-por">Clave remplazo:</span>
                                        <input type="text" class="input-disabled clave_remplazo" name="clave_remplazo" 
                                                value="${billet.clave_remplazo}" required>
                                    </div>`
                                    : `<input type="text" class="input-disabled clave" name="clave" 
                                            value="${billet.clave || ''}" placeholder="Ingrese clave" required>`
                                }
                                </td>

                                <td>
                                    ${billet.lp_remplazo
                                        ? `<div class="d-flex flex-column gap-2">
                                                <input type="text" class="input-disabled lote_pedimento" name="lote_pedimento" 
                                                    value="${billet.lote_pedimento || ''}" required>
                                                <span class="span-remplazar-por">Remplazar por:</span>
                                                <input type="text" class="input-disabled lp_remplazo" name="lp_remplazo" 
                                                    value="${billet.lp_remplazo}" placeholder="Ingrese lote pedimento" required>
                                            </div>`
                                        : `<input type="text" class="input-disabled lote_pedimento" name="lote_pedimento" 
                                                    value="${billet.lote_pedimento || ''}" placeholder="Ingrese lote pedimento" required>`
                                    }

                                </td>
                                <td>
                                ${billet.medida_remplazo && billet.medida_remplazo.trim() !== ''
                                    ? `<div class="d-flex flex-column gap-2">
                                        <input type="text" class="input-disabled medida" name="medida" 
                                                value="${billet.medida || ''}" required>
                                        <span class="span-remplazar-por">Medida remplazo:</span>
                                        <input type="text" class="input-disabled medida_remplazo" name="medida_remplazo" 
                                                value="${billet.medida_remplazo}" required>
                                    </div>`
                                    : `<input type="text" class="input-disabled medida" name="medida" 
                                            value="${billet.medida || ''}" placeholder="Ingrese medida (di/de)" required>`
                                }
                                </td>

                                <td>
                                    ${billet.pz_teoricas ? 
                                        `<input type="text" class="input-text pz_teoricas" name="pz_teoricas" value="${billet.pz_teoricas}" required>` : 
                                        `<input type="text" class="input-text pz_teoricas" name="pz_teoricas" value="${billet.pz_teoricas || ''}" placeholder="Ingrese pz a maquinar" required>`
                                    }
                                </td>
                                <td>
                                ${billet.altura_pz ? 
                                    `<input type="text" class="input-disabled altura_pz" name="altura_pz" value="${billet.altura_pz}" required>` : 
                                    `<input type="text" class="input-text altura_pz" name="altura_pz" value="${billet.altura_pz || ''}" placeholder="Ingrese altura del sello" required>`
                                }
                                </td>                                
                                <td><input type="text" class="input-disabled mm_teoricos" value="${mmTeoricos}" readonly></td>
                                <td><input type="number" class="input-text mm_entrega" name="mm_entrega" value="${(billet.mm_entrega !== undefined && billet.mm_entrega !== null && billet.mm_entrega !== '' && isFinite(Number(billet.mm_entrega))) ? Number(billet.mm_entrega).toFixed(2) : ''}" step="0.01" min="0" required></td>

                                                           
                            </tr>
                            <tr class="row-justificar-remplazo ${billet.justificacion_remplazo && billet.justificacion_remplazo.trim() !== '' ? '' : 'd-none'}">
                                <td colspan="10">
                                    <div class="d-flex flex-column justify-content-start align-items-start">
                                    <label class="mb-2 text-secondary">
                                        Justificación de remplazo de la barra <strong>${billet.lote_pedimento || ''}:</strong>
                                    </label> 
                                    <input type="text" class="input-disabled justificacion_remplazo" name="justificacion_remplazo" 
                                            value="${billet.justificacion_remplazo || ''}">
                                    </div>
                                </td>
                            </tr>

                        `);

                        // Adjuntar listeners a los inputs relevantes para recalcular mmTeoricos cuando el usuario escriba
                        // Hacemos esto dentro del scope de la iteración para que 'material' y 'desbasteMaterial' estén disponibles
                        (function(materialLocal, desbasteLocal){
                            const $thisRow = $('#tableEntregarBarras tbody tr.data-row').last();

                            // Si material o medida faltan, intentar autocompletar desde inventario via AJAX usando el lote pedimento
                            try {
                                const currentMaterial = ($thisRow.find('.material').val() || '').toString().trim();
                                const currentMedida = ($thisRow.find('.medida').val() || '').toString().trim();
                                if (!currentMaterial || !currentMedida) {
                                    $.ajax({
                                        url: '../ajax/info_lote_pedimento.php',
                                        type: 'POST',
                                        data: { billet: billet.lote_pedimento },
                                        dataType: 'json',
                                        success: function(resp){
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
                                                const alturaNow = parseFloat($thisRow.find('.altura_pz').val()) || 0;
                                                const desbNow = calcularDesbaste(info.material || materialLocal || $thisRow.find('.material').val() || '');
                                                let mmNow = pzNow * (alturaNow + desbNow);
                                                if (!isFinite(mmNow) || isNaN(mmNow)) mmNow = 0;
                                                $thisRow.find('.mm_teoricos').val(parseFloat(mmNow).toFixed(2));
                                            } else {
                                                // no existe el lote en inventario: dejar los inputs editables (ya lo son por defecto)
                                            }
                                        },
                                        error: function(err){
                                            console.warn('No se pudo obtener info del lote pedimento:', err);
                                        }
                                    });
                                }
                            } catch (e) {
                                console.warn('Error al intentar autocompletar material/medida:', e);
                            }

                            $thisRow.find('.pz_teoricas, .altura_pz').on('input change', function(){
                                const pz = parseFloat($thisRow.find('.pz_teoricas').val()) || 0;
                                const altura = parseFloat($thisRow.find('.altura_pz').val()) || 0;
                                // recalcular desbaste en caso de que material cambie o no esté definido
                                const desb = calcularDesbaste(materialLocal || $thisRow.find('.material').val() || '');
                                let mm = pz * (altura + desb);
                                if (!isFinite(mm) || isNaN(mm)) mm = 0;
                                $thisRow.find('.mm_teoricos').val(parseFloat(mm).toFixed(2));
                            });
                        })(material, desbasteMaterial);
                    });
                    console.log(data.fuente);

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
                        }
                    } catch (e) {
                        console.warn('Error aplicando modo solo lectura en tablaEntregarBarras:', e);
                    }
                } else {
                    $('#tableEntregarBarras tbody').append('<tr><td colspan="14" class="text-center">No hay barras disponibles para esta requisición.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al consultar los datos de las barras: " + error, "none");
            }
        });
    }    
    // Traer los lotes pedimento de la requisicion para que CNC llene los campos para finalizar la requisicion
    function ajaxTraerClavesControlAlmacen(idRequisicion){
        $.ajax({
            url: '../ajax/barras_para_finalizar.php',
            type: 'get',
            data: { 
                id_requisicion: idRequisicion
            },
            dataType: 'json',
            success: function(data) {
                $('#modalFinalizar tbody').empty();

                if (data.success && data.billets.length > 0) {
                    $.each(data.billets, function(index, billet) {
                        // Calcular desbaste según material
                        let material = billet.material || '';
                        let desbasteMaterial = calcularDesbaste(material);
                        
                        // Calcular campos iniciales
                        let pzTeoricas = billet.pz_teoricas ? (billet.pz_teoricas || 0) : 0;
                        let alturaPz = billet.altura_pz ? (parseFloat(billet.altura_pz) || 0) : 0;
                        let mmTeoricos = (pzTeoricas * (alturaPz + desbasteMaterial)).toFixed(2);
                        let longTSellos = (pzTeoricas * alturaPz).toFixed(2);

                        $('#modalFinalizar tbody').append(`
                            <tr class="data-row" data-id-control="${billet.id_control}" data-lote="${billet.lote_pedimento}" data-desbaste="${desbasteMaterial}">
                                <input type="hidden" tabindex="-1" name="id_control" value="${billet.id_control || ''}">
                                <input type="hidden" tabindex="-1" name="es_merma" class="es_merma" value="${billet.es_merma || '0'}">
                                <input type="hidden" tabindex="-1" name="mm_teoricos" class="mm_teoricos" value="${billet.mm_teoricos || mmTeoricos}">
                                <input type="hidden" tabindex="-1" name="mm_merma_real" class="mm_merma_real" value="${billet.mm_merma_real || ''}">
                                <input type="hidden" tabindex="-1" name="id_cotizacion" class="id_cotizacion" value="${billet.id_cotizacion ? billet.id_cotizacion : ''}">
                                <input type="hidden" tabindex="-1" name="id_estimacion" class="id_estimacion" value="${billet.id_cotizacion ? billet.id_estimacion : ''}">

                                <!-- Perfil Sello: Input editable si no hay cotización -->
                                <td>
                                    ${billet.perfil_sello ? 
                                        `<input type="text" class="input-disabled perfil_sello" name="perfil_sello" value="${billet.perfil_sello}" placeholder="Ingrese perfil sello" required>` : 
                                        `<input type="text" class="input-text perfil_sello" name="perfil_sello" value="${billet.perfil_sello || ''}" placeholder="Ingrese perfil sello" required>`
                                    }
                                </td>
                                
                                <td><p class="input-disabled material">${billet.material || 'No se encontró material'}</p></td>
                                <td>
                                    <div>
                                        <p class="input-disabled lote_pedimento mb-0"> ${billet.lote_pedimento || ''}</p>
                                        ${billet.pendiente_autorizar && parseInt(billet.pendiente_autorizar) == 1 ? '<small class="text-warning">Pendiente por autorizar</small>' : ''}
                                    </div>
                                </td>
                                <td><p class="input-disabled medida">${billet.medida || '?/?'}</p></td>
                                <td><p class="input-disabled mm_entrega">${billet.mm_entrega || '0'}</p></td>
                                
                                <!-- Piezas Teóricas: Input editable si no hay cotización -->
                                <td>
                                    ${billet.pz_teoricas ? 
                                        `<input type="number" class="input-disabled pz_teoricas" name="pz_teoricas" value="${pzTeoricas}" step="1" min="0" placeholder="Pz teóricas" required>` : 
                                        `<input type="number" class="input-text pz_teoricas" name="pz_teoricas" value="${billet.pz_teoricas || ''}" step="1" min="0" placeholder="Pz teóricas" required>`
                                    }
                                </td>
                                
                                <td><input type="number" class="input-text pz_maquinadas" name="pz_maquinadas" value="${billet.pz_maquinadas || ''}" step="1" min="0" required></td>
                                <td>
                                    <input type="number" 
                                        ${billet.altura_pz ? '' : ''}
                                        class="${billet.altura_pz ? 'input-text' : 'input-text'} altura_pz" 
                                        name="altura_pz" 
                                        value="${billet.altura_pz || alturaPz}" 
                                        step="0.01" 
                                        min="0.01" 
                                        ${billet.altura_pz ? '' : 'required'}>
                                </td>                            
                                <td><input type="number" class="input-text mm_usados" name="mm_usados" value="${billet.mm_usados || ''}" step="0.01" min="0.01" required></td>
                                <td><input type="number" tabindex="-1" class="input-disabled long_t_sellos" name="total_sellos" value="${billet.total_sellos || longTSellos}" step="0.01" min="0" required></td>
                                <td><input type="number" tabindex="-1" class="input-disabled merma_corte" name="merma_corte" value="${billet.merma_corte || ''}" step="0.01" min="0" required></td>
                                <td><input type="number" tabindex="-1" class="input-disabled scrap_pz" name="scrap_pz" value="${billet.scrap_pz || ''}" step="1" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled scrap_mm" name="scrap_mm" value="${billet.scrap_mm || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled mm_total_usados" name="mm_total_usados" value="${billet.mm_total_usados || ''}" step="0.01" min="0"></td>
                            </tr>
                            <tr class="row-justificar ${billet.causa_merma ? '' : 'd-none'}">
                                <td colspan="14">
                                    <div class="d-flex flex-column justify-content-start align-items-start">
                                        <label class="mb-2 text-danger">Causa y justificación de merma requerida para <strong>${billet.lote_pedimento || ''}</strong>:</label> 
                                        <div class="d-flex flex-row gap-3 align-items-start w-100">
                                            <div class="flex-shrink-0" style="width: 300px;">
                                                <select class="form-select causa_merma" name="causa_merma" required>
                                                    <option value="" disabled ${!billet.causa_merma ? 'selected' : ''}>Seleccione causa...</option>
                                                    <option value="Error humano" ${billet.causa_merma === 'Error humano' ? 'selected' : ''}>Error humano</option>
                                                    <option value="Filo de herramienta gastada" ${billet.causa_merma === 'Filo de herramienta gastada' ? 'selected' : ''}>Filo de herramienta gastada</option>
                                                    <option value="Daño en la materia prima" ${billet.causa_merma === 'Daño en la materia prima' ? 'selected' : ''}>Daño en la materia prima</option>
                                                    <option value="Sellos especiales" ${billet.causa_merma === 'Sellos especiales' ? 'selected' : ''}>Sellos especiales</option>
                                                    <option value="Vibración de la barra" ${billet.causa_merma === 'Vibración de la barra' ? 'selected' : ''}>Vibración de la barra</option>
                                                </select>
                                                <small class="text-muted">Causa principal</small>
                                            </div>
                                            <div class="flex-grow-1">
                                                <input type="text" class="form-control justificacion_merma" name="justificacion_merma" value="${billet.justificacion_merma || ''}" placeholder="" required>
                                                <small class="text-muted text-muted text-merma-real"></small>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        `);

                        // Agregar event listeners para los cálculos
                        agregarEventListenersCalculos(billet.id_control);
                    });
                } else {
                    $('#modalFinalizar tbody').append('<tr><td colspan="14" class="text-center">No hay barras disponibles para esta requisición.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al consultar los datos de las barras: " + error, "none");
            }
        });
    }
    // Función para calcular desbaste según material
    function calcularDesbaste(material) {
        console.log(material);
        const materialesBlandos = [
            'H-ECOPUR', 'ECOSIL', 'ECORUBBER 1', 'ECORUBBER 2', 
            'ECORUBBER 3', 'ECOPUR'
        ];
        
        const materialesDuros = [
            'ECOTAL', 'ECOMID', 'ECOFLON 1', 'ECOFLON 2', 'ECOFLON 3'
        ];

        if (materialesBlandos.includes(material.toUpperCase())) {
            return 2.00;
        } else if (materialesDuros.includes(material.toUpperCase())) {
            return 2.50;
        } else {
            return 2.50; // Por defecto
        }
    }
    // Función para agregar event listeners a los campos de cálculo
    function agregarEventListenersCalculos(idControl) {
        const row = $(`tr[data-id-control="${idControl}"]`);
        const lote = row.data('lote') || 'Desconocido';
        
        console.log(`Agregando listeners para fila ID: ${idControl}, Lote: ${lote}`);
        
        // Eventos para pz_maquinadas y mm_usados
        row.find('.pz_maquinadas, .altura_pz, .mm_usados').on('input', function() {
            calcularFila(row, lote);
        });
        setTimeout(() => {
            row.find('.mm_usados').trigger("input");
        }, 500);
    }
    // Función principal de cálculos para una fila
    function calcularFila(row, lote) {
        console.log('=== INICIANDO CÁLCULO DE FILA ===');
        
        // Obtener valores con logs
        const pzMaquinadas = parseFloat(row.find('.pz_maquinadas').val()) || 0;
        const mmUsados = parseFloat(row.find('.mm_usados').val()) || 0;
        const alturaPz = parseFloat(row.find('.altura_pz').val()) || 0;
        const pzTeoricas = parseFloat(row.find('.pz_teoricas').val()) || 0;
        const desbasteMaterial = parseFloat(row.data('desbaste')) || 2.50;
        const mmEntrega = parseFloat(row.find('.mm_entrega').text()) || 0;
        
        // 1. mm_usados = pz_maquinadas * (altura_pz + desbaste_material)
        const mmUsadosCalculado = pzMaquinadas * (alturaPz + desbasteMaterial);
        console.log(`${pzMaquinadas} * (${alturaPz} + ${desbasteMaterial}) = ${mmUsadosCalculado}`);
        
        // 2. mm_teoricos = pz_teoricas * (altura_pz + desbaste_material)
        const mmTeoricos = pzTeoricas * (alturaPz + desbasteMaterial);
        console.log(`${pzTeoricas} * (${alturaPz} + ${desbasteMaterial}) = ${mmTeoricos}`);
        
        // 3. long_t_sellos = pz_teoricas * altura_pz
        const longTSellos = pzTeoricas * alturaPz;
        console.log(`${pzTeoricas} * ${alturaPz} = ${longTSellos}`);
        
        // 4. merma_corte = pz_maquinadas * desbaste_material
        const mermaCorte = pzMaquinadas * desbasteMaterial;
        console.log(`${pzMaquinadas} * ${desbasteMaterial} = ${mermaCorte}`);
        
        // 5. scrap_pz = pz_maquinadas - pz_teoricas
        let scrapPz = pzMaquinadas - pzTeoricas;
        console.log(`${pzMaquinadas} - ${pzTeoricas} = ${scrapPz}`);
        
        // 6. scrap_mm = scrap_pz * altura_pz
        let scrapMm = scrapPz * alturaPz;
        console.log(`${scrapPz} * ${alturaPz} = ${scrapMm}`);
        
        // Calcular los total usados
        const mmTotalUsados = (alturaPz * pzMaquinadas) + mermaCorte; 
        let mmMermaReal = 0.00;
        
        // 8. mm_merma_real = mm_usados - mm_teoricos
        if(mmUsados > mmTotalUsados){
            mmMermaReal = mmUsados - mmTeoricos;
        }else{
            mmMermaReal = mmTotalUsados - mmTeoricos;
        }

        console.log(`${mmUsados} - ${mmTeoricos} = ${mmMermaReal}`);

        // 9. Validar mm_usados no puede ser menor a mmMinimos (altura_pz*pz_maquinadas)
        const mmMinimos = alturaPz * pzMaquinadas;
        if (mmUsados < mmMinimos && mmUsados > 0) {
            row.find('.mm_usados').addClass('error');
        } else {
            console.log('OK: mm_usados es válido');
            row.find('.mm_usados').removeClass('error');
        }

        // 12. Validar mm_usados no puede ser menor a mm_entrega
        if (mmUsados > mmEntrega && mmEntrega > 0) {
            row.find('.mm_usados').addClass('error');
        } else {
            row.find('.mm_usados').removeClass('error');
        }
        
        // 10. Si mm_merma_real > mm_teoricos mostrar input de justificacion_merma
        const justificarRow = row.next('.row-justificar');
        if (mmMermaReal > 0 || scrapPz > 0) {
        //if (mmMermaReal > 0 && mmUsados > mmTeoricos) {
            justificarRow.removeClass('d-none');
            justificarRow.find('.text-merma-real').text(`Debe justificar por que hay una merma de ${mmMermaReal.toFixed(2)}mm. Lote pedimento: ${lote}`);
        } else {
            justificarRow.addClass('d-none');
            justificarRow.find('.justificacion_merma').val('');
        }
        
        // 11. Si mm_usados = mm_entrega, es_merma = 1
        //const esMerma = (mmUsados === mmEntrega) ? 1 : 0;
        const esMerma = 0;
        console.log(`es_merma = ${esMerma}`);
        
        if(scrapPz<0){
            scrapPz=0.00;
            scrapMm=0.00;
        }
        // Actualizar campos en la fila
        console.log('ACTUALIZANDO CAMPOS:');
        console.log('long_t_sellos:', longTSellos.toFixed(2));
        console.log('merma_corte:', mermaCorte.toFixed(2));
        console.log('scrap_pz:', scrapPz);
        console.log('scrap_mm:', scrapMm.toFixed(2));
        console.log('mm_merma_real:', mmMermaReal.toFixed(2));
        console.log('es_merma:', esMerma);
        console.log('mm_teoricos:', mmTeoricos.toFixed(2));
        
        //row.find('.mm_usados').val(mmUsadosCalculado.toFixed(2));
        row.find('.long_t_sellos').val(longTSellos.toFixed(2));
        row.find('.merma_corte').val(mermaCorte.toFixed(2));
        row.find('.scrap_pz').val(scrapPz);
        row.find('.scrap_mm').val(scrapMm.toFixed(2));
        row.find('.mm_merma_real').val(mmMermaReal.toFixed(2));
        row.find('.es_merma').val(esMerma);
        row.find('.mm_total_usados').val(mmTotalUsados.toFixed(2));
        row.find('.mm_teoricos').val(mmTeoricos.toFixed(2));
        
        console.log('=== FINALIZADO CÁLCULO DE FILA ===\n');
    }
    // Función para cargar los resultados del maquinado
    function cargarResultadosMaquinado(idRequisicion, rol) {
        $.ajax({
            url: '../ajax/obtener_resultados_maquinado.php',
            type: 'get',
            data: { 
                id_requisicion: idRequisicion
            },
            dataType: 'json',
            success: function(data) {
                $('#tbodyResultadosMaquinado').empty();

                if (data.success && data.barras.length > 0) {
                    $.each(data.barras, function(index, barra) {
                        // Determinar si mostrar la justificación
                        const mostrarJustificacion = barra.justificacion_merma && barra.justificacion_merma.trim() !== '';
                        
                        // Determinar clase para merma real (rojo si es mayor a 0)
                        const estiloMermaReal = (parseFloat(barra.mm_merma_real) > 0) ? 'color:#ff1100 !important;' : '';
                        
                        $('#tbodyResultadosMaquinado').append(`
                            <tr>
                                <td class="text-center">${barra.es_merma == 1 ? 'Si' : 'No'}</td>
                                <td>${barra.perfil_sello || ''}</td>
                                <td>${barra.material || ''}</td>
                                <td>${barra.lote_pedimento || ''}</td>
                                <td>${barra.medida || ''}</td>
                                <td class="text-end">${parseFloat(barra.mm_entrega || 0).toFixed(2)}</td>
                                <td class="text-end">${parseInt(barra.pz_teoricas || 0)}</td>
                                <td class="text-end">${parseInt(barra.pz_maquinadas || 0)}</td>
                                <td class="text-end">${parseFloat(barra.altura_pz || 0).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(barra.mm_usados || 0).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(barra.total_sellos || 0).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(barra.merma_corte || 0).toFixed(2)}</td>
                                <td class="text-end">${parseInt(barra.scrap_pz || 0)}</td>
                                <td class="text-end">${parseFloat(barra.scrap_mm || 0).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(barra.mm_total_usados || 0).toFixed(2)}</td>
                                <td class="text-end fw-bold" style="${estiloMermaReal}">${parseFloat(barra.mm_merma_real || 0).toFixed(2)}</td>
                            </tr>
                            ${mostrarJustificacion ? `
                            <tr class="table-warning">
                                <td colspan="16">
                                    <div class="d-flex flex-column">
                                        <strong class="text-danger">Causa y justificación de merma:</strong>
                                        <span>${barra.causa_merma} - ${barra.justificacion_merma}</span>
                                    </div>
                                </td>
                            </tr>
                            ` : ''}
                        `);
                    });
                } else {
                    $('#tbodyResultadosMaquinado').append('<tr><td colspan="16" class="text-center">No hay registros de maquinado para esta requisición.</td></tr>');
                }

                // Mostrar información de revisión si existe
                
                // Mostrar/ocultar sección de observaciones según el rol y estado
                const yaRevisada = data.requisicion.fecha_revision_maquinado !== null; // true si tiene fecha
                console.log(yaRevisada);
                if (rol === 'Gerente') {
                    if (yaRevisada) {
                        $('#badgeRevisionContainer').html(`
                            <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Esta requisición ya fue revisada</strong>
                            </div>
                            `);
                        mostrarInformacionRevision(data.requisicion);
                        $('#seccionObservaciones').addClass('d-none');
                        $('#terminarRevision').addClass('d-none');
                    }else{
                        $('#badgeRevisionContainer').html(`
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Pendiente de revisión</strong> - 
                                El maquinado está finalizado pero aún no ha hecho la revisión de los resultados.
                            </div>
                        `);
                        $('#infoRevisionContainer').html("");
                        $('#seccionObservaciones').removeClass('d-none');
                        $('#terminarRevision').removeClass('d-none');
                        $('#observacionesGerente').val(''); // Limpiar textarea

                    }
                } else {
                    if (yaRevisada) {
                        $('#badgeRevisionContainer').html(`
                            <div class="alert alert-info mt-3">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Esta requisición ya fue revisada</strong>
                            </div>
                            `);
                        mostrarInformacionRevision(data.requisicion);
                    }else{
                        $('#badgeRevisionContainer').html(`
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Pendiente de revisión</strong> - 
                                El maquinado está finalizado pero aún no ha hecho la revisión de los resultados.
                            </div>
                        `);
                        $('#infoRevisionContainer').html("");
                    }
                    $('#seccionObservaciones').addClass('d-none');
                    $('#terminarRevision').addClass('d-none');
                }

            },
            error: function(xhr, status, error) {
                console.error('Error al cargar resultados:', error);
                $('#tbodyResultadosMaquinado').html('<tr><td colspan="16" class="text-center text-danger">Error al cargar los datos</td></tr>');
            }
        });
    }
    // Función para mostrar información de revisión
    function mostrarInformacionRevision(requisicion) {
        $('#infoRevisionContainer').html("");
        // Crear o actualizar la sección de información
        const fechaRevision = new Date(requisicion.fecha_revision_maquinado).toLocaleString();
        let infoHTML = `
            <div class="alert alert-success mt-3">
                <h6><i class="bi bi-check-circle"></i> Información de Revisión</h6>
                <div class="row mt-2">
                    <div class="col-md-6">
                        <strong>Fecha de revisión:</strong> ${fechaRevision}
                    </div>

                </div>
        `;
        infoHTML += `
            <div class="row mt-2">
                <div class="col-12">
                    <strong>Observaciones:</strong><br>
                    <div class="mt-1 p-2 bg-light rounded">${requisicion.observacion_maquinado || '<small class="fst-italic text-secondary-emphasis">No hay observaciones</small>'}</div>
                </div>
            </div>
        `;
        infoHTML += `</div>`;
        // Insertar antes de la sección de observaciones
        $('#infoRevisionContainer').html(infoHTML);
    }
    // Traer las claves para que inventario marque nuevo stock en MM de retorno la requisicion
    function ajaxClavesRetorno(idRequisicion){
        $.ajax({
            url: '../ajax/traer_claves_control_almacen.php', 
            type: 'get',
            data: { 
                id_requisicion: idRequisicion
            },
            dataType: 'json',
            success: function(data) {
                $('#modalRetorno tbody').empty();

                if (data.success && data.data.length > 0) {
                    $.each(data.data, function(index, item) {
                        let esExtra = "";
                        let esMerma = "";
                        let esReemplazo = "";
                        if(item.es_extra == 1){
                            esExtra = " (Barra extra)*";
                        }
                        if(item.es_merma == 1){
                            esMerma = " (Barra mermada)*";
                        }
                        if(item.es_remplazo == 1){
                            esReemplazo = " (Reemplazo de la barra: "+item.lote_pedimento+")*";
                        }
                        $('#modalRetorno tbody').append(`
                            <tr>
                                <input type="hidden" tabindex="-1" name="id_requisicion" value="${idRequisicion || ''}">
                                <input type="hidden" tabindex="-1" name="id_control" value="${item.id_control || ''}">
                                <input type="hidden" tabindex="-1" name="es_remplazo" value="${item.es_remplazo || ''}">
                                <td><input type="text" tabindex="-1" class="input-disabled material" value="${item.material || ''}"></td>
                                <td><input type="text" tabindex="-1" class="input-disabled clave" value="${item.clave || ''}"></td>
                                <td><input type="text" tabindex="-1" class="input-disabled lote_pedimento d-flex flex-column" value="${
                                    item.es_remplazo == 1 ?
                                    item.lp_remplazo :
                                    item.lote_pedimento
                                }">
                                    <span style="color:#ffc107;">${esExtra}</span>
                                    <span style="color:#B71C1C;">${esMerma}</span>
                                    <span style="color:#ffc107;">${esReemplazo}</span>
                                </td>
                                <td><input type="text" tabindex="-1" class="input-disabled medida" value="${item.medida || ''}"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled mm_entrega" name="mm_entrega" value="${item.mm_entrega || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled mm_usados" name="mm_usados" value="${item.mm_total_usados || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-text mm_retorno" name="mm_retorno" value="" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled long_t_sellos" name="total_sellos" value="${item.total_sellos || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled merma_corte" name="merma_corte" value="${item.scrap_mm || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled scrap_pz" name="scrap_pz" value="${item.scrap_pz || ''}" step="1" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled scrap_mm" name="scrap_mm" value="${item.scrap_mm || ''}" step="0.01" min="0"></td>
                            </tr>
                        `);
                    });
                } else {
                    $('#modalFinalizar tbody').append('<tr><td colspan="10" class="text-center">No hay claves disponibles para esta requisición.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                $('#modalFinalizar tbody').append('<h5>Error en ajax</h5>');
                sweetAlertResponse("error", "Error", "Error al consultar cotizaciones: " + error, "none");
            }
        });
    }




    //---------------------------------------- EVENTOS DEL DOM ------------------------------------
    // ESCUCHAR INPUTS DE MEDIDAS EN MODAL EDITAR MEDIDAS
    $('#modalEditarMedidas').on('input', 'input[type="number"]', function () {
        const $input = $(this);
        const clase = $input.attr('class').split(' ').find(c => c !== 'input-text'); // Evita 'input-text' genérica
        const valueRaw = $input.val();
        const value = parseFloat(valueRaw);

        if (!clase || isNaN(value)) {
            return;
        }

        let claseRelacionada = '';
        let valorConvertido = 0;

        if (clase.includes('_inch')) {
            claseRelacionada = clase.replace('_inch', '');
            valorConvertido = value * 25.4;
        } else {
            claseRelacionada = clase + '_inch';
            valorConvertido = value / 25.4;
        }

        // Buscar input relacionado dentro de la misma tabla, por clase exacta
        const $tabla = $input.closest('table');
        const $inputRelacionado = $tabla.find(`input.${claseRelacionada}`);

        if ($inputRelacionado.length == 0) {
            return;
        }

        const decimales = claseRelacionada.includes('_inch') ? 4 : 2;
        $inputRelacionado.val(valorConvertido.toFixed(decimales));
    });
    // CLICK EDITAR MEDIDAS
    $('#productionTable').on('click', '.btn-editar-medidas', function() {
        $dataIdRequisicion = $(this).data('id-requisicion');
        
        ajaxTraerCotizaciones($dataIdRequisicion);
    });  
    // BOTON GUARDAR LAS MEDIDAS ACTUALIZADAS
    $('#btnGuardarMedidas').on('click', function () {
        const promesas = [];

        $('.tabla-medidas').each(function () {
            const $tabla = $(this);
            const id_cotizacion = $tabla.data('id_cotizacion');
            if (!id_cotizacion) return;

            const datos = {
                id_cotizacion: id_cotizacion
            };

            $tabla.find('input').each(function () {
                const name = $(this).attr('name');
                const value = $(this).val();
                datos[name] = value;
            });

            // Convertir AJAX a promesa y agregarla al array
            const promesa = new Promise((resolve, reject) => {
                $.ajax({
                    url: '../ajax/actualizar_medidas.php',
                    method: 'POST',
                    data: datos,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            console.log(`Cotización ${id_cotizacion} actualizada correctamente`);
                            resolve(id_cotizacion);
                        } else {
                            console.warn(`Error en ${id_cotizacion}: ${response.message}`);
                            reject(`Error en ${id_cotizacion}: ${response.message}`);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(`Error AJAX en cotización ${id_cotizacion}:`, error);
                        reject(`Error AJAX en cotización ${id_cotizacion}`);
                    }
                });
            });

            promesas.push(promesa);
        });

        // Ejecutar todas las promesas y manejar el resultado global
        Promise.allSettled(promesas).then(resultados => {
            const fallos = resultados.filter(r => r.status === 'rejected');

            if (fallos.length == 0) {
                Swal.fire({
                    title: 'Proceso exitoso',
                    text: 'Medidas guardadas correctamente.',
                    icon: 'success',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#55AD9B',
                });
            } else {
                const errores = fallos.map(f => f.reason).join('\n');
                Swal.fire({
                    title: 'Ocurrió un problema',
                    text: 'Hubo un error al actualizar alguna cotización. ' + errores + 'Si el problema persiste, contacte el área de sistemas.',
                    icon: 'error',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#55AD9B',
                });
            }
        });

    });
    // VER LA TABLA DE BARRAS DE CONTROL DE ALMACEN PARA ENTREGAR
    $(document).on('click', '.btn-entregar-barras', function(){
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
    $(document).on('click', '.btn-remplazar-barra', function(){
        $('#modalTableControlAlmacenEntrega').modal('show');
        $('#modalSolicitarRemplazoBarra').modal('show');
        
        let idRequisicionRemplazo = $(this).data("id-requisicion");
        let dataIdControl = $(this).data("id-control");
        let dataClave=$(this).data("clave");
        let dataLotePedimento = $(this).data("lp");
        let dataMedida = $(this).data("medida");
        $("#inputIdRequisicionRemplazo").val(idRequisicionRemplazo);
        $("#inputIdControl").val(dataIdControl);
        $("#inputClaveRemplazoA").val(dataClave);
        $("#inputLoteRemplazoA").val(dataLotePedimento);
        $("#inputMedidaRemplazoA").val(dataMedida);
    });
    // ENVIAR LA SOLICITUD DE REMPLAZO DE BARRA
    $("#btnSolicitarRemplazoBarra").on("click", function(){
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
                success: function(data) {
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
                error: function(xhr, status, error) {
                    console.error('Error al realizar la petición AJAX:', error);
                    console.error('Respuesta del servidor:', xhr.responseText); // Muestra el error enviado por PHP
                    $("#pValidacionSolicitarRemplazo").removeClass("d-none");
                    $('#pValidacionSolicitarRemplazo').text('Error en ajax solicitar remplazo de barra.');
                    $("#btnSolicitarRemplazoBarra").removeClass("d-none");
                }
            });
        } else {
            sweetAlertResponse("warning", "Advertencia", "Debe digitar el lote pedimento de la barra de remplazo", "none");
        }
    });
    // Mostrar modal para agregar barra extra desde el modal de entrega
    $('#addExtraBillet').on('click', function(){
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
    $('#enviarAddBillet').on('click', function(e){
        e.preventDefault();

        var id_requisicion = $('#idRequisicionExtra').val();
        var lote_pedimento = $('#lotePedimentoExtra').val().trim();
        var perfil = $('#perfilExtra').val().trim();
        var pz_teoricas = $('#pzTeoricasExtra').val();
        var altura_pz = $('#alturaPzExtra').val();
        var mm_entrega = $('#mmEntregaExtra').val();
        var justificacion = $('#justificacionExtra').val().trim();

        // Validación básica (ahora incluye mm_entrega)
        if (!lote_pedimento || !perfil || pz_teoricas === '' || altura_pz === '' || mm_entrega === '' || !justificacion) {
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
                mm_entrega: mm_entrega,
                justificacion_extra: justificacion
            },
            dataType: 'json',
            success: function(resp) {
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
            error: function(xhr, status, error) {
                console.error('Error AJAX al agregar barra extra:', xhr.responseText || error);
                sweetAlertResponse('error', 'Error', 'Ocurrió un error al agregar la barra extra.', 'self');
                // Rehabilitar botón pero NO resetear formulario para que el usuario pueda corregir
                $btn.prop('disabled', false).text('Agregar');
            }
        });
    });
    // DAR SALIDA A LOS BILLETS QUE AGREGO INVENTARIOS
    $("#btnEntregarBarras").on('click', function(){
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
    $("#btnGuardarProgresoEntregaBarras").on('click', function(){
        let $btn = $(this);
        let idRequisicion = $('#modalTableControlAlmacenEntrega .title-form form input').val();
        
        // Recopilar datos de la tabla
        const registros = [];
        $('#tableEntregarBarras tbody tr.data-row').each(function(){
            const $row = $(this);
            const idControl = $row.find('.id_control').val();
            const pzTeoricas = $row.find('.pz_teoricas').val();
            const mmEntrega = $row.find('.mm_entrega').val();
            
            if (idControl) {
                registros.push({
                    id_control: idControl,
                    pz_teoricas: pzTeoricas || 0,
                    mm_entrega: mmEntrega || 0
                });
            }
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
            success: function(data) {
                if (data.success) {
                    sweetAlertResponse("success", "Éxito", data.message || "Progreso guardado correctamente", "none");
                    // Recargar la tabla
                    cargarTablaEntregarBarras(idRequisicion, $('#modalTableControlAlmacenEntrega').data('estatus-requi'), $('#modalTableControlAlmacenEntrega').data('maquina-asignada'));
                } else {
                    sweetAlertResponse("error", "Error", data.error || "Error al guardar el progreso", "none");
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al guardar progreso:', error);
                sweetAlertResponse("error", "Error", "Error al guardar el progreso: " + error, "none");
            },
            complete: function() {
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
            success: function(data) {
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
    // BOTON DE ABRIR EL MODAL DE INICIAR MAQUINADO CNC, TRAER MAQUINAS
    $("#productionTable").on('click', ".btn-iniciar-maquinado", function () {
        let idRequisicion = $(this).data('id-requisicion');

        $.ajax({
            url: '../ajax/maquinas.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data) {
                   
                    $('#inputMaquina').html(`<option value="" selected disabled>Seleccione máquina</option>`);
                    data.forEach(element => {
                        $(`#inputMaquina`).append(
                            `<option value="${element.rol}">${element.rol}</option>`
                        );
                    });
                    
                } else {
                    sweetAlertResponse("warning", "Hubo un problema", data.message, "self");
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al actualizar registro. " + error, "self");
            }
        });

        $('#modalGuardarOperador').modal('show');
        $("#inputIdRequisicionOperador").val(idRequisicion);
    });
    // CLICK SUBMIT A GUARDAR EL OPERADOR CNC
    $("#btnGuardarOperador").on('click', function () {
        let inputMaquina = $("#inputMaquina").val();
        let inputOperadorCNC = $("#inputOperadorCNC").val();
        let inputIdRequisicionOperador = $("#inputIdRequisicionOperador").val();

        if(!inputMaquina){
            sweetAlertResponse("warning", "Faltan datos", "Seleccione una máquina CNC", "none");
            return;
        }

        $(this).addClass("d-none");
        $.ajax({
            url: '../ajax/guardar_operadorcnc.php',
            type: 'POST',
            data: { 
                maquina: inputMaquina,
                operador_cnc: inputOperadorCNC,
                id_requisicion: inputIdRequisicionOperador
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                    $('#modalGuardarOperador').modal('hide');
                    //$("#ContainerQR").css("filter", "blur(0px)");
                } else {
                    sweetAlertResponse("warning", "Hubo un problema", data.message, "self");
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al actualizar registro. " + error, "self");
            }
        });
    });
    //CLICK FINALIZAR TAL REQUISICION DESDE LA TABLA
    $("#productionTable").on('click', ".btn-finalizar", function(){
        $dataIdRequisicion=$(this).data('id-requisicion');
        $("#modalFinalizar h5 span").text($dataIdRequisicion);
        ajaxTraerClavesControlAlmacen($dataIdRequisicion);
    });
    // GUARDAR PROGRESO DE MAQUINADO
    $("#saveChangesFinalizar").on('click', function () {
        // Recolectar datos sin validación estricta
        let datos = [];
        let tieneDatos = true;

        $('#modalFinalizar tbody .data-row').each(function () {
            let id_control = $(this).find('input[name="id_control"]').val();

            if (!id_control) return;

            // Verificar si hay al menos un campo lleno
            const pzMaquinadas = $(this).find('.pz_maquinadas').val();
            const mmUsados = $(this).find('.mm_usados').val();
            
            if (pzMaquinadas || mmUsados) {
                tieneDatos = true;
            }

            let fila = {
                id_control: id_control,
                es_merma: $(this).find('.es_merma').val() || 0,
                perfil_sello: $(this).find('.perfil_sello').val() || '',
                pz_maquinadas: $(this).find('.pz_maquinadas').val() || 0,
                altura_pz: $(this).find('.altura_pz').val() || 0,
                mm_usados: $(this).find('.mm_usados').val() || 0,
                total_sellos: $(this).find('.long_t_sellos').val() || 0,
                merma_corte: $(this).find('.merma_corte').val() || 0,
                scrap_pz: $(this).find('.scrap_pz').val() || 0,
                scrap_mm: $(this).find('.scrap_mm').val() || 0,
                mm_total_usados: $(this).find('.mm_total_usados').val() || 0,
                // Campos calculados y ocultos
                mm_teoricos: $(this).find('.mm_teoricos').val() || 0,
                mm_merma_real: $(this).find('.mm_merma_real').val() || 0,
                // Información de cotización
                id_cotizacion: $(this).find('.id_cotizacion').val() || '',
                id_estimacion: $(this).find('.id_estimacion').val() || '',
                pz_teoricas: $(this).find('.pz_teoricas').val() || 0,
                // Justificación (si aplica)
                causa_merma: $(this).next('.row-justificar').find('.causa_merma').val() || '',
                justificacion_merma: $(this).next('.row-justificar').find('.justificacion_merma').val() || ''
            };

            datos.push(fila);
        });

        // Validar que haya al menos algún dato para guardar
        if (!tieneDatos) {
            Swal.fire({
                title: 'Sin datos',
                text: 'No hay datos nuevos para guardar. Complete al menos algunos campos.',
                icon: 'info',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Mostrar loading en el botón
        const $btn = $(this);
        const originalText = $btn.html();
        $btn.html('<i class="bi bi-hourglass-split"></i> Guardando...');
        $btn.prop('disabled', true);

        // Enviar al servidor para guardar progreso
        $.ajax({
            url: '../ajax/guardar_progreso_maquinado.php',
            type: 'post',
            data: { registros: JSON.stringify(datos) },
            success: function (resp) {
                // Restaurar botón
                $btn.html(originalText);
                $btn.prop('disabled', false);

                if (resp.success) {
                    Swal.fire({
                        title: 'Progreso guardado',
                        text: resp.message,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        //timer: 2000,
                        showConfirmButton: true
                    });
                    
                    // Opcional: Marcar campos como guardados visualmente
                    $('#modalFinalizar .data-row').each(function() {
                        $(this).find('input, textarea').addClass('is-valid');
                        setTimeout(() => {
                            $(this).find('input, textarea').removeClass('is-valid');
                        }, 3000);
                    });
                } else {
                    Swal.fire({
                        title: 'Error al guardar',
                        text: resp.error,
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            },
            error: function (xhr, status, error) {
                // Restaurar botón
                $btn.html(originalText);
                $btn.prop('disabled', false);
                
                Swal.fire({
                    title: 'Error de conexión',
                    text: 'No se pudo guardar el progreso: ' + error,
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        });
    });
    // ENVIAR EL FORMULARIO DE FINALIZAR LA REQUISICION
    $("#finalizarRequisicion").on('click', function () {
        let valido = true;
        let problemas = [];

        // Validar campos editables obligatorios
        $('#modalFinalizar tbody .data-row').each(function () {
            const $fila = $(this);
            const lote = $fila.data('lote') || 'Lote desconocido';
            
            const perfil = $fila.find('.perfil_sello').val();
            // Obtener valores numéricos
            const mmEntrega = parseFloat($fila.find('.mm_entrega').text()) || 0;
            const pzTeoricas = parseFloat($fila.find('.pz_teoricas').val()) || 0;
            const pzMaquinadas = parseFloat($fila.find('.pz_maquinadas').val()) || 0;
            const alturaPz = parseFloat($fila.find('.altura_pz').val()) || 0;
            const mmUsados = parseFloat($fila.find('.mm_usados').val()) || 0;
            const mermaCorte = parseFloat($fila.find('.merma_corte').val()) || 0;
            const desbaste = parseFloat($fila.data('desbaste')) || 2.50;
            
            if(!perfil || perfil.trim() === ""){
                valido = false;
                problemas.push(`Ingrese perfil. Lote pedimento: ${lote}`);
                $fila.find('.perfil_sello').addClass('is-invalid');
            }else{
                $fila.find('.perfil_sello').removeClass('is-invalid');
            }
            // Validar pz_teoricas (required)
            if ($fila.find('.pz_teoricas').val().trim() === "" || isNaN(pzTeoricas) || pzTeoricas <= 0) {
                valido = false;
                problemas.push(`Debe digitar las piezas teóricas mayor a 0. Lote pedimento: ${lote}`);
                $fila.find('.pz_teoricas').addClass('is-invalid');
            } else {
                $fila.find('.pz_teoricas').removeClass('is-invalid');
            }
            // Validar pz_maquinadas (required)
            if ($fila.find('.pz_maquinadas').val().trim() === "" || isNaN(pzMaquinadas) || pzMaquinadas <= 0) {
                valido = false;
                problemas.push(`Debe digitar las piezas maquinadas mayor a 0. Lote pedimento: ${lote}`);
                $fila.find('.pz_maquinadas').addClass('is-invalid');
            } else {
                $fila.find('.pz_maquinadas').removeClass('is-invalid');
            }

            // Validar altura_pz cuando es editable (required si no hay cotización)
            const $alturaPz = $fila.find('.altura_pz');
            if ($alturaPz.hasClass('input-text')) { // Solo validar si es editable
                const alturaPzVal = $alturaPz.val().trim();
                if (alturaPzVal === "" || isNaN(alturaPz) || alturaPz <= 0) {
                    valido = false;
                    problemas.push(`Debe digitar la altura por pieza. Lote pedimento: ${lote}`);
                    $alturaPz.addClass('is-invalid');
                } else {
                    $alturaPz.removeClass('is-invalid');
                }
            }

            // Validar mm_usados (required)
            if ($fila.find('.mm_usados').val().trim() === "" || isNaN(mmUsados) || mmUsados < 0) {
                valido = false;
                problemas.push(`Debe digitar los mm usados. Lote pedimento: ${lote}`);
                $fila.find('.mm_usados').addClass('is-invalid');
            } else {
                $fila.find('.mm_usados').removeClass('is-invalid');
            }

            // Validar scrap_pz (no required pero debe ser número válido si tiene valor)
            const scrapPz = $fila.find('.scrap_pz').val().trim();
            if (scrapPz !== "" && (isNaN(scrapPz) || parseFloat(scrapPz) < 0)) {
                valido = false;
                problemas.push(`Falta scrap de piezas. Lote pedimento: ${lote}`);
                $fila.find('.scrap_pz').addClass('is-invalid');
            } else {
                $fila.find('.scrap_pz').removeClass('is-invalid');
            }

            // Validar que mm_usados no sea menor a los mm minimos usados en teoria
            const minimoAlturaPz = alturaPz * pzMaquinadas;
            if (mmUsados < minimoAlturaPz) {
                valido = false;
                problemas.push(`Los mm usados (${mmUsados}mm) no pueden ser menores al mínimo requerido (altura/pz ${alturaPz}mm × ${pzMaquinadas} pz = ${minimoAlturaPz.toFixed(2)}). Lote pedimento: ${lote}`);
                $fila.find('.mm_usados').addClass('is-invalid');
            }

            // Validar mm_usados no puede ser mayor a mm_entrega
            if (mmUsados > mmEntrega) {
                valido = false;
                problemas.push(`Los mm usados (${mmUsados}mm) no pueden ser mayores a los mm entregados (${mmEntrega.toFixed(2)}mm). Lote pedimento: ${lote}`);
                $fila.find('.mm_usados').addClass('is-invalid');
            } 
        });

        // Validar justificaciones requeridas
        $('#modalFinalizar tbody .row-justificar:not(.d-none)').each(function() {
            const $justificarRow = $(this);
            const lote = $justificarRow.prev('.data-row').data('lote') || 'Lote desconocido';
            const justificacion = $justificarRow.find('.justificacion_merma').val().trim();
            const causaMerma = $justificarRow.find('.causa_merma').val();
            
            // Validar causa de merma (obligatorio)
            if (!causaMerma) {
                valido = false;
                problemas.push(`Debe seleccionar la causa de merma. Lote pedimento: ${lote}`);
                $justificarRow.find('.causa_merma').addClass('is-invalid');
            } else {
                $justificarRow.find('.causa_merma').removeClass('is-invalid');
            }
            
            // Validar justificación (obligatorio y longitud mínima)
            if (justificacion === '') {
                valido = false;
                problemas.push(`Debe justificar la merma. Lote pedimento: ${lote}`);
                $justificarRow.find('.justificacion_merma').addClass('is-invalid');
            } else if (justificacion.length < 10) {
                valido = false;
                problemas.push(`La justificación de merma debe tener al menos 10 caracteres. Lote pedimento: ${lote}`);
                $justificarRow.find('.justificacion_merma').addClass('is-invalid');
            } else {
                $justificarRow.find('.justificacion_merma').removeClass('is-invalid');
            }
        });

        if (!valido) {
            let mensaje = "<div style='text-align: left;'>";
            mensaje += "<h5 style='margin-bottom: 15px; color: #856404;'>Se encontraron los siguientes problemas:</h5>";
            
            if (problemas.length > 0) {
                mensaje += "<ul style='padding-left: 20px; margin-bottom: 0;'>";
                problemas.forEach(problema => {
                    mensaje += `<li style='margin-bottom: 8px; color: #721c24;'>${problema}</li>`;
                });
                mensaje += "</ul>";
            }
            
            mensaje += "<hr style='margin: 15px 0;'>";
            mensaje += "<small style='color: #6c757d;'>Por favor, corrija los datos antes de continuar.</small>";
            mensaje += "</div>";

            Swal.fire({
                title: 'Verificar datos',
                html: mensaje,
                icon: 'warning',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#ffc107',
                width: '600px'
            });
            return;
        }

        // Recolectar datos: solo id_control + campos editables
        let datos = [];
        $('#modalFinalizar tbody .data-row').each(function () {
            let id_control = $(this).find('input[name="id_control"]').val();

            // Saltar fila si id_control no existe o está vacío
            if (!id_control) return;
            let fila = {
                id_control: id_control,
                es_merma: $(this).find('.es_merma').val() || 0,
                perfil_sello: $(this).find('.perfil_sello').val() || '',
                pz_maquinadas: $(this).find('.pz_maquinadas').val() || 0,
                altura_pz: $(this).find('.altura_pz').val() || 0,
                mm_usados: $(this).find('.mm_usados').val() || 0,
                total_sellos: $(this).find('.long_t_sellos').val() || 0,
                merma_corte: $(this).find('.merma_corte').val() || 0,
                scrap_pz: $(this).find('.scrap_pz').val() || 0,
                scrap_mm: $(this).find('.scrap_mm').val() || 0,
                mm_total_usados: $(this).find('.mm_total_usados').val() || 0,
                // Campos calculados y ocultos
                mm_teoricos: $(this).find('.mm_teoricos').val() || 0,
                mm_merma_real: $(this).find('.mm_merma_real').val() || 0,
                // Información de cotización
                id_cotizacion: $(this).find('.id_cotizacion').val() || '',
                id_estimacion: $(this).find('.id_estimacion').val() || '',
                pz_teoricas: $(this).find('.pz_teoricas').val() || 0,
                // Justificación (si aplica)
                causa_merma: $(this).next('.row-justificar').find('.causa_merma').val() || '',
                justificacion_merma: $(this).next('.row-justificar').find('.justificacion_merma').val() || ''
            };

            datos.push(fila);
        });

        // Validar que haya registros reales
        if (datos.length == 0) {
            sweetAlertResponse("warning", "Sin registros", "No hay registros por enviar. Se necesita mínimo un registro.", "none");
            return;
        }

        // Mostrar confirmación final antes de enviar
        Swal.fire({
            title: '¿Finalizar requisición?',
            text: `Se enviarán ${datos.length} registro(s) a revisión de mermas y retorno de barras`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Finalizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).addClass("d-none");

                // Enviar al servidor
                $.ajax({
                    url: '../ajax/finalizar_requisicion.php',
                    type: 'post',
                    data: { registros: JSON.stringify(datos) },
                    success: function (resp) {
                        if (resp.success) {
                            sweetAlertResponse("success", "Éxito", resp.message, "self");
                            $('#modalFinalizar').modal('hide');
                        } else {
                            sweetAlertResponse("warning", "Advertencia", resp.error || "Error desconocido.", "self");
                            $("#finalizarRequisicion").removeClass("d-none");
                        }
                    },
                    error: function (xhr, status, error) {
                        sweetAlertResponse("error", "Error", "No se pudo finalizar: " + error, "self");
                        $("#finalizarRequisicion").removeClass("d-none");
                    }
                });
            }
        });
    });
    // CLICK A REVISAR RESULTADOS DE MAQUINADO Y MERMAS
    $(document).on('click', '.btn-tabla-maquinado-mermas', function(){
        const idRequisicion = $(this).data('id-requisicion');
        const rol = $(this).data('rol');
        
        $('#folioRequisicion').text(idRequisicion);
        $("#inputIdRequisicionResultadosMaquinado").val(idRequisicion);
        cargarResultadosMaquinado(idRequisicion, rol);
    });
    // Función para terminar revisión (solo Gerente)
    $('#terminarRevision').on('click', function() {
        const observaciones = $('#observacionesGerente').val();
        const idRequisicion = $("#inputIdRequisicionResultadosMaquinado").val();
        
        if (!idRequisicion) {
            sweetAlertResponse("error", "Error", "No se encontró el ID de la requisición.", "none");
            return;
        }

        // Mostrar confirmación
        Swal.fire({
            title: '¿Terminar revisión?',
            html: `¿Está seguro de que desea terminar la revisión del maquinado de la requisición <strong>${idRequisicion}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, terminar revisión',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return $.ajax({
                    url: '../ajax/guardar_revision_maquinado.php',
                    type: 'POST',
                    data: {
                        id_requisicion: idRequisicion,
                        observaciones: observaciones
                    },
                    dataType: 'json'
                }).then(response => {
                    if (!response.success) {
                        throw new Error(response.error);
                    }
                    return response;
                }).catch(error => {
                    Swal.showValidationMessage(`Error: ${error.statusText || error.responseText || error}`);
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const response = result.value;
                
                Swal.fire({
                    title: '¡Revisión completada!',
                    html: `
                        <div class="text-start">
                            <p>${response.message}</p>
                            <div class="mt-3 p-2 bg-light rounded">
                                <strong>Folio:</strong> ${response.id_requisicion}<br>
                                <strong>Fecha revisión:</strong> ${new Date(response.fecha_revision).toLocaleString()}<br>
                                ${response.observaciones ? `<strong>Observaciones:</strong> ${response.observaciones}` : ''}
                            </div>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    // Cerrar modal y opcionalmente recargar la tabla principal
                    $('#modalTablaMaquinadoMermas').modal('hide');
                    
                    // Opcional: Recargar la tabla principal para reflejar el cambio
                    if (typeof recargarTablaPrincipal === 'function') {
                        recargarTablaPrincipal();
                    }
                });
            }
        });
    });
    //CLICK RETORNAR BARRAS TAL REQUISICION DESDE LA TABLA
    $("#productionTable").on('click', ".btn-claves-retorno", function(){
        $dataIdRequisicion=$(this).data('id-requisicion');
        $("#modalRetorno h5 span").text($dataIdRequisicion);
        ajaxClavesRetorno($dataIdRequisicion);
    });

    // ENVIAR EL NUEVO STOCK COMO RETORNO DE LA BARRAS
    $("#retornoFinalizado").on('click', function () {
        let valido = true;

        // Validar solo inputs que el usuario puede editar (excluimos .input-disabled)
        $('#modalRetorno tbody input:not(.input-disabled)').each(function () {
            let valor = $(this).val().trim();
            if (valor === "" || valor === null) {
                valido = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!valido) {
            sweetAlertResponse("warning", "Campos incompletos", "Debes llenar todos los campos editables antes de finalizar.", "none");
            return;
        }

        // Recolectar datos: solo id_control + campos editables
        let datos = [];
        $('#modalRetorno tbody tr').each(function () {
            let id_requisicion = $(this).find('input[name="id_requisicion"]').val();
            let id_control = $(this).find('input[name="id_control"]').val();
            let lote_pedimento = $(this).find('.lote_pedimento').val();

            // Saltar fila si id_control no existe o está vacío
            if (!id_requisicion || !id_control || !lote_pedimento) return;

            let fila = {
                id_requisicion: id_requisicion,
                id_control: id_control,
                mm_retorno: $(this).find('.mm_retorno').val() || 0,
                lote_pedimento: $(this).find('.lote_pedimento').val() || "",
            };

            datos.push(fila);
        });

        // Validar que haya registros reales
        if (datos.length == 0) {
            sweetAlertResponse("warning", "Sin registros", "No hay registros por enviar. Se necesita mínimo un registro con Lote Pedimento.", "none");
            return;
        }
        // Obtener observaciones del textarea
        const observaciones_inv = $('#observacionesInventario').val().trim();

        $(this).addClass("d-none");

        // Enviar al servidor
        $.ajax({
            url: '../ajax/retornar_barras.php',
            type: 'post',
            data: { registros: JSON.stringify(datos),
                    observaciones_inv: observaciones_inv 
            },
            success: function (resp) {
                if (resp.success) {
                    sweetAlertResponse("success", "Éxito", resp.message, "self");
                    $('#modalRetorno').modal('hide');
                } else {
                    sweetAlertResponse("error", "Error", resp.error || "Error desconocido.", "self");
                }
            },
            error: function (xhr, status, error) {
                sweetAlertResponse("error", "Error", "No se pudo finalizar: " + error, "self");
            }
        });
    });

});
