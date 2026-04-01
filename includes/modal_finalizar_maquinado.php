<!-- ////////////////////////////// CNC DEBE LLENAR LOS CAMPOS DE CONTROL DE ALMACEN PARA FINALIZAR //////////////////////// -->
<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Para finalizar llene los campos solicitados</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="width:100%; margin-bottom:20px;">
                    <h5 class="modal-title">Barras de requisición con folio: <span></span></h5>
                    <div style="overflow-x: auto; width: 100%; max-height:300px !important; overflow-y:auto;">
                        <table class="table table-bordered border border-2 tabla-billets" style="table-layout: fixed; width: max-content;">
                            <thead>
                                <tr>
                                    <th style="width: 150px;">PERFIL</th>
                                    <th style="width: 80px;">COMPONENTE</th>
                                    <th style="width: 200px;">MATERIAL</th>
                                    <th style="width: 250px;">LOTE</th>
                                    <th style="width: 100px;">MEDIDA BARRA</th>
                                    <th style="width: 120px;">MM ENTREGADOS</th>
                                    <th style="width: 120px;">H. TOTAL PERFIL</th>
                                    <th style="width: 100px;">PZ TEÓRICAS</th>
                                    <th style="width: 120px;">LONG. TOTAL SELLOS</th>
                                    <th style="width: 120px;">H. COMPONENTE</th>
                                    <th style="width: 100px;">PZ MAQUINADAS</th>
                                    <th style="width: 120px;">MM USADOS OPERADOR</th>
                                    <th style="width: 120px;">MERMA POR CORTE</th>
                                    <th style="width: 120px;">SCRAP PZ</th>
                                    <th style="width: 120px;">SCRAP MM</th>
                                    <th style="width: 120px;">MM TOTAL TEÓRICOS</th>
                                    <th style="width: 120px;">MM TOTAL MERMA</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Aquí van tus registros -->
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex col-12 gap-3">
                    <div class="col-3">
                        <button id="saveChangesFinalizar" type="button" class="btn-general">
                            <i class="bi bi-floppy"></i> Guardar progreso
                        </button>
                    </div>
                    <small id="smallText" class="text-muted d-none mt-1">Las barras ya fueron retornadas, no es posible editar resultados de maquinado</small>

                    <button id="finalizarRequisicion" type="button" class="btn-general btn-success">
                        <i class="bi bi-check-circle"></i> Finalizar maquinado
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
    // Traer los lotes pedimento de la requisicion para que CNC llene los campos para finalizar la requisicion
    function ajaxBarrasFinalizarMaquinado(idRequisicion, estatusRequisicion) {
        $.ajax({
            url: '../ajax/barras_para_finalizar.php',
            type: 'get',
            data: {
                id_requisicion: idRequisicion
            },
            dataType: 'json',
            success: function (data) {
                $('#modalFinalizar tbody').empty();
                if (estatusRequisicion === 'Detenida') {
                    $('#finalizarRequisicion').addClass('d-none');
                } else {
                    $('#finalizarRequisicion').removeClass('d-none');
                }
                if (data.success && data.billets.length > 0) {
                    $.each(data.billets, function (index, billet) {
                        // Calcular desbaste según material
                        let material = billet.material || '';
                        let desbasteMaterial = calcularDesbaste(material);

                        // Calcular campos iniciales
                        let pzTeoricas = billet.pz_teoricas ? (billet.pz_teoricas || 0) : 0;
                        let hComponente = billet.h_componente ? (parseFloat(billet.h_componente) || 0) : 0;
                        let alturaPz = billet.altura_pz ? (parseFloat(billet.altura_pz) || 0) : 0;
                        let mmTeoricos = (pzTeoricas * (hComponente + desbasteMaterial)).toFixed(2);
                        let longTSellos = (pzTeoricas * hComponente).toFixed(2);

                        // LÓGICA DE BLOQUEO POR FECHA O ESTATUS
                        const tieneFechaRetorno = (data.fecha_retorno_barras !== null && data.fecha_retorno_barras !== "");
                        const esSoloLectura = tieneFechaRetorno;

                        // Manejo del botón Guardar/Finalizar
                        if (esSoloLectura) {
                            $('#saveChangesFinalizar,#smallText').addClass('d-none');
                            $('#smallText').removeClass('d-none');
                        } else {
                            $('#saveChangesFinalizar,#smallText').removeClass('d-none');
                            $('#smallText').addClass('d-none');
                        }

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
                                
                                <td><p class="input-disabled material">${billet.material || 'No se encontró material'}</p></td>
                                <td>
                                    <div>
                                        <p class="input-disabled lote_pedimento mb-0"> ${billet.lote_pedimento || ''}</p>
                                        ${billet.pendiente_autorizar && parseInt(billet.pendiente_autorizar) == 1 ? '<small class="text-warning">Pendiente por autorizar</small>' : ''}
                                        ${billet.es_eliminacion == 1 && billet.es_eliminacion_auth == 0 ? '<br><small class="text-warning fw-semibold">Eliminación en espera</small>' : ''}
                                    </div>
                                </td>
                                <td><p class="input-disabled medida">${billet.medida || '?/?'}</p></td>
                                <td><p class="input-disabled mm_entrega">${billet.mm_entrega || '0'}</p></td>
                                <td>
                                    <input type="number" tabindex="-1" class="input-disabled altura_pz" name="altura_pz" value="${billet.altura_pz || alturaPz}" readonly>
                                </td>
                                
                                <!-- Piezas Teóricas: Input editable si no hay cotización -->
                                <td>
                                    ${billet.pz_teoricas ?
                                        `<input type="number" tabindex="-1" class="input-disabled pz_teoricas" name="pz_teoricas" value="${pzTeoricas}" readonly>` :
                                        `<input type="number" class="input-text pz_teoricas" name="pz_teoricas" value="${billet.pz_teoricas || ''}" step="1" min="0" placeholder="Pz teóricas" required>`
                                    }
                                </td>
                                <td><input type="number" tabindex="-1" class="input-disabled long_t_sellos" name="total_sellos" value="${billet.total_sellos || longTSellos}" step="0.01" min="0" required></td>
                                <td>
                                    <input type="number" 
                                        class="${esSoloLectura ? 'input-disabled' : 'input-text'} h_componente"
                                        name="h_componente" 
                                        value="${billet.h_componente || hComponente}" 
                                        step="0.01" 
                                        min="0.01" 
                                        required
                                    >
                                </td>
                                
                                <td>
                                    ${billet.pz_maquinadas == "0" || billet.pz_maquinadas === 0 ?
                                        `<input type="number" class="${esSoloLectura ? 'input-disabled' : 'input-text'} pz_maquinadas" name="pz_maquinadas" value="${0}" step="1" min="0" required>` :
                                        `<input type="number" class="${esSoloLectura ? 'input-disabled' : 'input-text'} pz_maquinadas" name="pz_maquinadas" value="${billet.pz_maquinadas || ''}" step="1" min="0" required>`
                                    }
                                </td>
                                <td><input type="number" class="${esSoloLectura ? 'input-disabled' : 'input-text'} mm_usados" name="mm_usados" value="${billet.mm_usados || ''}" step="0.01" min="0.01" required></td>
                                <td><input type="number" tabindex="-1" class="input-disabled merma_corte" name="merma_corte" value="${billet.merma_corte || ''}" step="0.01" min="0" required></td>
                                <td><input type="number" tabindex="-1" class="input-disabled scrap_pz" name="scrap_pz" value="${billet.scrap_pz || ''}" step="1" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled scrap_mm" name="scrap_mm" value="${billet.scrap_mm || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled mm_total_usados" name="mm_total_usados" value="${billet.mm_total_usados || ''}" step="0.01" min="0"></td>
                                <td>
                                    <input type="number" tabindex="-1" class="input-disabled mm_total_merma" name="mm_total_merma" value="0.00" readonly>
                                </td>
                            </tr>
                            <tr class="row-justificar ${billet.causa_merma ? '' : 'd-none'}">
                                <td colspan="17">
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
                    $('#modalFinalizar tbody').append('<tr><td colspan="17" class="text-center">No hay barras disponibles para esta requisición.</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al consultar los datos de las barras: " + error, "none");
            }
        });
    }
    // Función para agregar event listeners a los campos de cálculo
    function agregarEventListenersCalculos(idControl) {
        const row = $(`tr[data-id-control="${idControl}"]`);
        const lote = row.data('lote') || 'Desconocido';

        console.log(`Agregando listeners para fila ID: ${idControl}, Lote: ${lote}`);

        // Eventos para pz_maquinadas y mm_usados
        row.find('.pz_maquinadas, .h_componente, .mm_usados').on('input', function () {
            calcularFila(row, lote);
        });
        setTimeout(() => {
            row.find('.mm_usados').trigger("input");
        }, 500);
    }
    // Función principal de cálculos para una fila
    function calcularFila(row, lote) {
        console.log('=== INICIANDO CÁLCULO DE FILA (Format Optimized) ===');

        // Función auxiliar para redondear a 2 decimales reales
        const round2 = (num) => Math.round((num + Number.EPSILON) * 100) / 100;
        // Función auxiliar para forzar enteros
        const toInt = (num) => parseInt(num) || 0;

        // 1. Obtención y Saneamiento de Entradas
        const pzMaquinadas = toInt(row.find('.pz_maquinadas').val());
        const pzTeoricas = toInt(row.find('.pz_teoricas').val());
        const mmUsados = round2(parseFloat(row.find('.mm_usados').val()) || 0);
        const hComponente = round2(parseFloat(row.find('.h_componente').val()) || 0);
        const desbasteMat = round2(parseFloat(row.data('desbaste')) || 2.50);
        const mmEntrega = round2(parseFloat(row.find('.mm_entrega').text()) || 0);

        // 2. Cálculos de Producción
        // mm_usados_calc = pz * (alt + desbaste)
        const mmUsadosCalculado = round2(pzMaquinadas * (hComponente + desbasteMat));

        // mm_teoricos = pz_teoricas * (alt + desbaste)
        const mmTeoricos = round2(pzTeoricas * (hComponente + desbasteMat));

        // long_t_sellos = pz_teoricas * alt
        const longTSellos = round2(pzTeoricas * hComponente);

        // merma_corte = pz_maquinadas * desbaste
        const mermaCorte = round2(pzMaquinadas * desbasteMat);

        // 3. Cálculos de Scrap y Merma
        let scrapPz = pzMaquinadas - pzTeoricas;
        if (scrapPz < 0) scrapPz = 0; // Evitar scrap negativo

        let scrapMm = round2(scrapPz * hComponente);

        const mmTotalUsados = round2((hComponente * pzMaquinadas) + mermaCorte);

        // Cálculo de Merma Real con corrección de precisión
        let mmMermaReal = 0.00;
        if (mmUsados > mmTotalUsados) {
            mmMermaReal = round2(mmUsados - mmTeoricos);
        } else {
            mmMermaReal = round2(mmTotalUsados - mmTeoricos);
        }

        // 4. Lógica de Interfaz (UI)
        const justificarRow = row.next('.row-justificar');

        // Ahora la comparación es exacta contra 0
        if (mmMermaReal > 0 || scrapPz > 0) {
            justificarRow.removeClass('d-none');
            justificarRow.find('.text-merma-real').text(
                `Debe justificar por que hay una merma de ${mmMermaReal.toFixed(2)} mm. Lote: ${lote}`
            );
            row.find('.mm_total_merma').val(mmMermaReal.toFixed(2));
        } else {
            justificarRow.addClass('d-none');
            justificarRow.find('.justificacion_merma').val('');
            row.find('.mm_total_merma').val(0.00);
        }

        // 5. Actualización de Campos con Formato Estricto
        row.find('.long_t_sellos').val(longTSellos.toFixed(2));
        row.find('.merma_corte').val(mermaCorte.toFixed(2));
        row.find('.scrap_pz').val(toInt(scrapPz)); // Entero
        row.find('.scrap_mm').val(scrapMm.toFixed(2));
        row.find('.mm_merma_real').val(mmMermaReal.toFixed(2));
        row.find('.mm_total_usados').val(mmTotalUsados.toFixed(2));
        row.find('.mm_teoricos').val(mmTeoricos.toFixed(2));

        // Debug final para validar tipos
        // --- BLOQUE DE DEBUG PARA CONSOLA ---
        console.group(`%c DEBUG BARRA: ${lote} `, 'background: #222; color: #bada55; font-weight: bold;');

        console.table({
            "Piezas (Int)": { Maquinadas: pzMaquinadas, Teoricas: pzTeoricas, Scrap: scrapPz }
        });
        console.table({
            "Medidas (mm)": { Altura: hComponente, Desbaste: desbasteMat, Entrega: mmEntrega }
        });
        console.log(`%c > Cálculos de Merma:`, 'color: #007bff; font-weight: bold;');
        console.log(`  [Total Usados]: ${mmTotalUsados.toFixed(2)}mm`);
        console.log(`  [Teóricos]:     ${mmTeoricos.toFixed(2)}mm`);
        console.log(`  [Merma Real]:   %c${mmMermaReal.toFixed(2)}mm`, mmMermaReal > 0 ? 'color: red;' : 'color: green;');

        console.log(`%c > Estado de Interfaz:`, 'color: #007bff; font-weight: bold;');
        console.log(`  [Justificar]:   ${(mmMermaReal > 0 || scrapPz > 0) ? 'SÍ (Visible)' : 'NO (Oculto)'}`);
        console.log(`  [Scrap MM]:     ${scrapMm.toFixed(2)}mm`);

        console.groupEnd();
        // --- FIN DEL BLOQUE DE DEBUG ---
        console.log(`Final mmMermaReal: ${mmMermaReal} (Type: ${typeof mmMermaReal})`);
        console.log('=== FINALIZADO CÁLCULO DE FILA ===\n');
    }

    
    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        //CLICK FINALIZAR TAL REQUISICION DESDE LA TABLA
        $("#productionTable").on('click', ".btn-finalizar", function () {
            $dataIdRequisicion = $(this).data('id-requisicion');
            const estatusRequisicionF = $(this).data('estatus');
            $("#modalFinalizar h5 span").text($dataIdRequisicion);
            ajaxBarrasFinalizarMaquinado($dataIdRequisicion, estatusRequisicionF);
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
                    h_componente: $(this).find('.h_componente').val() || 0,
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
                    componente: $(this).find('.componente').val() || 1,
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
                        $('#modalFinalizar .data-row').each(function () {
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

                
                // Obtener valores numéricos
                const mmEntrega = parseFloat($fila.find('.mm_entrega').text()) || 0;
                
                
                const alturaPz = parseFloat($fila.find('.altura_pz').val()) || 0;
                
                const mermaCorte = parseFloat($fila.find('.merma_corte').val()) || 0;
                const desbaste = parseFloat($fila.data('desbaste')) || 2.50;

                const perfil = $fila.find('.perfil_sello').val();
                if (!perfil || perfil.trim() === "") {
                    valido = false;
                    problemas.push(`Ingrese perfil. Lote: ${lote}`);
                    $fila.find('.perfil_sello').addClass('is-invalid');
                } else {
                    $fila.find('.perfil_sello').removeClass('is-invalid');
                }
                // Validar pz_teoricas (required)
                const pzTeoricas = parseFloat($fila.find('.pz_teoricas').val()) || 0;
                if ($fila.find('.pz_teoricas').val().trim() === "" || isNaN(pzTeoricas) || pzTeoricas <= 0) {
                    valido = false;
                    problemas.push(`Debe digitar las piezas teóricas mayor a 0. Lote: ${lote}`);
                    $fila.find('.pz_teoricas').addClass('is-invalid');
                } else {
                    $fila.find('.pz_teoricas').removeClass('is-invalid');
                }
                // Validar pz_maquinadas (required)
                const pzMaquinadas = parseFloat($fila.find('.pz_maquinadas').val()) || 0;
                if ($fila.find('.pz_maquinadas').val().trim() === "" || isNaN(pzMaquinadas) || pzMaquinadas <= 0) {
                    valido = false;
                    problemas.push(`Debe digitar las piezas maquinadas mayor a 0. Lote: ${lote}`);
                    $fila.find('.pz_maquinadas').addClass('is-invalid');
                } else {
                    $fila.find('.pz_maquinadas').removeClass('is-invalid');
                }

                // Validar h_componente (required)
                const hComponente = $fila.find('.h_componente');
                const hComponenteVal = parseFloat(hComponente.val().trim()) || 0;
                if (hComponenteVal === "" || isNaN(hComponenteVal) || hComponenteVal <= 0) {
                    valido = false;
                    problemas.push(`Debe digitar la H. Componente. Lote: ${lote}`);
                    hComponente.addClass('is-invalid');
                } else {
                    hComponente.removeClass('is-invalid');
                }

                // Validar mm_usados (required)
                const mmUsados = parseFloat($fila.find('.mm_usados').val()) || 0;
                if ($fila.find('.mm_usados').val().trim() === "" || isNaN(mmUsados) || mmUsados < 0) {
                    valido = false;
                    problemas.push(`Debe digitar los mm usados. Lote: ${lote}`);
                    $fila.find('.mm_usados').addClass('is-invalid');
                } else {
                    $fila.find('.mm_usados').removeClass('is-invalid');
                }

                // Validar scrap_pz (no required pero debe ser número válido si tiene valor)
                const scrapPz = $fila.find('.scrap_pz').val().trim();
                if (scrapPz !== "" && (isNaN(scrapPz) || parseFloat(scrapPz) < 0)) {
                    valido = false;
                    problemas.push(`Falta scrap de piezas. Lote: ${lote}`);
                    $fila.find('.scrap_pz').addClass('is-invalid');
                } else {
                    $fila.find('.scrap_pz').removeClass('is-invalid');
                }

                // Validar que mm_usados no sea menor a los mm minimos usados en teoria
                const minimoHComp = hComponente * pzMaquinadas;
                if (mmUsados < minimoHComp) {
                    valido = false;
                    problemas.push(`Los mm usados (${mmUsados}mm) no pueden ser menores al mínimo requerido (H. Comp ${hComponente}mm × ${pzMaquinadas} pz = ${minimoHComp.toFixed(2)}). Lote: ${lote}`);
                    $fila.find('.mm_usados').addClass('is-invalid');
                }

                // Validar mm_usados no puede ser mayor a mm_entrega
                if (mmUsados > mmEntrega) {
                    valido = false;
                    problemas.push(`Los mm usados (${mmUsados}mm) no pueden ser mayores a los mm entregados (${mmEntrega.toFixed(2)}mm). Lote: ${lote}`);
                    $fila.find('.mm_usados').addClass('is-invalid');
                }
            });

            // Validar justificaciones requeridas
            $('#modalFinalizar tbody .row-justificar:not(.d-none)').each(function () {
                const $justificarRow = $(this);
                const lote = $justificarRow.prev('.data-row').data('lote') || 'Lote desconocido';
                const justificacion = $justificarRow.find('.justificacion_merma').val().trim();
                const causaMerma = $justificarRow.find('.causa_merma').val();

                // Validar causa de merma (obligatorio)
                if (!causaMerma) {
                    valido = false;
                    problemas.push(`Debe seleccionar la causa de merma. Lote: ${lote}`);
                    $justificarRow.find('.causa_merma').addClass('is-invalid');
                } else {
                    $justificarRow.find('.causa_merma').removeClass('is-invalid');
                }

                // Validar justificación (obligatorio y longitud mínima)
                if (justificacion === '') {
                    valido = false;
                    problemas.push(`Debe justificar la merma. Lote: ${lote}`);
                    $justificarRow.find('.justificacion_merma').addClass('is-invalid');
                } else if (justificacion.length < 10) {
                    valido = false;
                    problemas.push(`La justificación de merma debe tener al menos 10 caracteres. Lote: ${lote}`);
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
                    h_componente: $(this).find('.h_componente').val() || 0,
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
                    componente: $(this).find('.componente').val() || 1,
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
    });
</script>