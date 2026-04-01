// Traer las claves para que CNC llene los campos de las claves para finalizar la requisicion
function ajaxTraerClavesControlAlmacen(idRequisicion){
    $.ajax({
        url: '../ajax/traer_claves_control_almacen.php', 
        type: 'get',
        data: { 
            id_requisicion: idRequisicion
        },
        dataType: 'json',
        success: function(data) {
            $('#modalFinalizar tbody').empty();

            if (data.success && data.data.length > 0) {
                $.each(data.data, function(index, item) {
                    $.ajax({
                        url: '../ajax/info_lote_pedimento.php',
                        type: 'POST',
                        data: { billet: item.lote_pedimento },
                        dataType: 'json',
                        success: function(data) {
                            if (data.success) { 
                                $('#modalFinalizar tbody').append(`
                                    <tr class="data-row">
                                        <input type="hidden" tabindex="-1" name="id_control" value="${item.id_control || ''}">
                                        <input type="hidden" tabindex="-1" name="es_merma" class="es_merma" value="0">
                                        <input type="hidden" tabindex="-1" name="mm_teoricos" class="mm_teoricos" value="">
                                        <input type="hidden" tabindex="-1" name="mm_merma_teorica" class="mm_merma_teorica" value="">
                                        <input type="hidden" tabindex="-1" name="mm_sumatoria_sellos" class="mm_sumatoria_sellos" value="">
                                        <input type="hidden" tabindex="-1" name="mm_merma_real" class="mm_merma_real" value="">
                                        <td><p class="input-disabled material">${data.billetResult["material"] || ''}</p></td>
                                        <td><p class="input-disabled lote_pedimento">${data.billetResult["lote_pedimento"] || ''}</p></td>
                                        <td><p class="input-disabled medida">${data.billetResult["Medida"] || ''}</p></td>
                                        <td><p class="input-disabled mm_entrega">${item.mm_entrega || ''}</p></td>
                                        <td><input type="number" class="input-text pz_maquinadas" name="pz_maquinadas" value="" step="1" min="0" required></td>
                                        <td><input type="number" class="input-text altura_pz" name="altura_pz" value="" step="0.01" min="0" required></td>
                                        <td><input type="number" class="input-text mm_usados" name="mm_usados" value="" step="0.01" min="0" required></td>
                                        <td><input type="number" class="input-text long_t_sellos" name="total_sellos" value="" step="0.01" min="0" required></td>
                                        <td><input type="number" class="input-text merma_corte" name="merma_corte" value="" step="0.01" min="0" required></td>
                                        <td><input type="number" class="input-text scrap_pz" name="scrap_pz" value="" step="1" min="0"></td>
                                        <td><input type="number" class="input-text scrap_mm" name="scrap_mm" value="" step="0.01" min="0"></td>
                                    </tr>
                                    <tr class="row-justificar d-none">
                                        <td colspan="11">
                                            <div class="d-flex flex-column justify-content-start align-items-start">
                                                <label class="mb-2 text-danger">Justificación de merma requerida para <strong>${data.billetResult["lote_pedimento"] || ''}</strong>:</label> 
                                                <input type="text" class="input-text justificacion_merma" name="justificacion_merma" value="" required>
                                                <small class="text-muted">La merma real supera la merma teórica. Justifique el exceso.</small>
                                            </div>
                                        </td>
                                    </tr>
                                `);

                                // Configurar event listeners para los cálculos
                                configurarCalculosMerma($('#modalFinalizar tbody tr.data-row:last'));
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al realizar la petición AJAX:', error);
                        }
                    });
                });
            } else {
                $('#modalFinalizar tbody').append('<tr><td colspan="11" class="text-center">No hay claves disponibles para esta requisición.</td></tr>');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al realizar la petición AJAX:', error);
            sweetAlertResponse("error", "Error", "Error al consultar cotizaciones: " + error, "none");
        }
    });
}

// Configurar event listeners para cálculos de merma
function configurarCalculosMerma(fila) {
    fila.find('.pz_maquinadas, .altura_pz, .mm_usados').on('input', function() {
        calcularYValidarMerma(fila);
    });
}

// Función principal de cálculo y validación
function calcularYValidarMerma(fila) {
    const pzMaquinadas = parseFloat(fila.find('.pz_maquinadas').val()) || 0;
    const alturaPz = parseFloat(fila.find('.altura_pz').val()) || 0;
    const mmUsados = parseFloat(fila.find('.mm_usados').val()) || 0;
    const material = fila.find('.material').text().trim();
    
    // Validar que tengamos los datos necesarios
    if (pzMaquinadas <= 0 || alturaPz <= 0 || mmUsados <= 0) {
        return;
    }

    // Determinar desbaste según material
    const desbaste = determinarDesbaste(material);
    
    // CÁLCULOS PRINCIPALES
    const mmTeoricos = (alturaPz + desbaste) * pzMaquinadas;
    const mmMermaTeorica = mmUsados - mmTeoricos;
    const mmSumatoriaSellos = alturaPz * pzMaquinadas;
    const mmMermaReal = mmUsados - mmSumatoriaSellos;

    // Guardar cálculos en campos hidden
    fila.find('.mm_teoricos').val(mmTeoricos.toFixed(2));
    fila.find('.mm_merma_teorica').val(mmMermaTeorica.toFixed(2));
    fila.find('.mm_sumatoria_sellos').val(mmSumatoriaSellos.toFixed(2));
    fila.find('.mm_merma_real').val(mmMermaReal.toFixed(2));

    // Llenar campos visibles automáticamente
    fila.find('.long_t_sellos').val(mmSumatoriaSellos.toFixed(2));
    fila.find('.merma_corte').val(mmMermaReal.toFixed(2));

    // VALIDACIÓN: Mostrar/ocultar justificación
    const justificacionRow = fila.next('.row-justificar');
    const justificacionInput = justificacionRow.find('.justificacion_merma');
    
    if (mmMermaReal > mmMermaTeorica) {
        // Merma real > merma teórica - MOSTRAR justificación
        justificacionRow.removeClass('d-none');
        justificacionInput.prop('required', true);
        justificacionInput.css('border', '2px solid #dc3545');
        
        // Resaltar la fila de datos
        fila.css('background-color', '#fff3cd');
    } else {
        // Merma real <= merma teórica - OCULTAR justificación
        justificacionRow.addClass('d-none');
        justificacionInput.prop('required', false);
        justificacionInput.css('border', '1px solid #ced4da');
        
        // Quitar resaltado
        fila.css('background-color', '');
    }

    // Mostrar información de cálculo en consola (para debugging)
    console.log(`Lote: ${fila.find('.lote_pedimento').text()}`);
    console.log(`- MM Teóricos: ${mmTeoricos.toFixed(2)}`);
    console.log(`- Merma Teórica: ${mmMermaTeorica.toFixed(2)}`);
    console.log(`- Sumatoria Sellos: ${mmSumatoriaSellos.toFixed(2)}`);
    console.log(`- Merma Real: ${mmMermaReal.toFixed(2)}`);
    console.log(`- Requiere justificación: ${mmMermaReal > mmMermaTeorica}`);
}

// Determinar desbaste según material
function determinarDesbaste(material) {
    const materialesBlandos = ['H-ECOPUR', 'ECOSIL', 'ECORUBBER 1', 'ECORUBBER 2', 'ECORUBBER 3', 'ECOPUR'];
    const materialesDuros = ['ECOTAL', 'ECOMID', 'ECOFLON 1', 'ECOFLON 2', 'ECOFLON 3'];
    
    if (materialesBlandos.some(m => material.includes(m))) {
        return 2.00;
    } else if (materialesDuros.some(m => material.includes(m))) {
        return 2.50;
    } else {
        return 2.50; // Valor por defecto
    }
}

// ENVIAR EL FORMULARIO DE FINALIZAR LA REQUISICION
$("#finalizarRequisicion").on('click', function () {
    let valido = true;
    let problemas = [];

    // Validar campos editables obligatorios
    $('#modalFinalizar tbody .data-row input:not(.input-disabled)').each(function () {
        let valor = $(this).val().trim();
        if (valor === "" || valor === null) {
            valido = false;
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Validar justificaciones requeridas
    $('#modalFinalizar tbody .row-justificar:not(.d-none)').each(function() {
        const justificacion = $(this).find('.justificacion_merma').val().trim();
        const lote = $(this).find('strong').text();
        
        if (justificacion === '') {
            valido = false;
            problemas.push(`Debe justificar la merma alta para el lote: ${lote}`);
            $(this).find('.justificacion_merma').addClass('is-invalid');
        } else {
            $(this).find('.justificacion_merma').removeClass('is-invalid');
        }
    });

    if (!valido) {
        let mensaje = "Debes llenar todos los campos obligatorios.";
        if (problemas.length > 0) {
            mensaje += "\n\n" + problemas.join('\n');
        }
        sweetAlertResponse("warning", "Validación", mensaje, "none");
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
            pz_maquinadas: $(this).find('.pz_maquinadas').val() || 0,
            altura_pz: $(this).find('.altura_pz').val() || 0,
            mm_usados: $(this).find('.mm_usados').val() || 0,
            total_sellos: $(this).find('.long_t_sellos').val() || 0,
            merma_corte: $(this).find('.merma_corte').val() || 0,
            scrap_pz: $(this).find('.scrap_pz').val() || 0,
            scrap_mm: $(this).find('.scrap_mm').val() || 0,
            // Campos calculados
            mm_teoricos: $(this).find('.mm_teoricos').val() || 0,
            mm_merma_teorica: $(this).find('.mm_merma_teorica').val() || 0,
            mm_sumatoria_sellos: $(this).find('.mm_sumatoria_sellos').val() || 0,
            mm_merma_real: $(this).find('.mm_merma_real').val() || 0,
            // Justificación (si aplica)
            justificacion_merma: $(this).next('.row-justificar').find('.justificacion_merma').val() || ''
        };

        datos.push(fila);
    });

    // Validar que haya registros reales
    if (datos.length === 0) {
        sweetAlertResponse("warning", "Sin registros", "No hay registros por enviar. Se necesita mínimo un registro.", "none");
        return;
    }

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
            }
        },
        error: function (xhr, status, error) {
            sweetAlertResponse("error", "Error", "No se pudo finalizar: " + error, "self");
        }
    });
});