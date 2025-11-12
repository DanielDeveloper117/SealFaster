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
                                    ${billet.pendiente_autorizar && parseInt(billet.pendiente_autorizar) === 1 ? '<small class="text-warning">Pendiente por autorizar</small>' : ''}
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
                                    ${billet.altura_pz ? 'tabindex="-1"' : ''}
                                    class="${billet.altura_pz ? 'input-disabled' : 'input-text'} altura_pz" 
                                    name="altura_pz" 
                                    value="${billet.altura_pz || alturaPz}" 
                                    step="0.01" 
                                    min="0" 
                                    ${billet.altura_pz ? '' : 'required'}>
                            </td>                            
                            <td><input type="number" class="input-text mm_usados" name="mm_usados" value="${billet.mm_usados || ''}" step="0.01" min="0" required></td>
                            <td><input type="number" tabindex="-1" class="input-disabled long_t_sellos" name="total_sellos" value="${billet.total_sellos || longTSellos}" step="0.01" min="0" required></td>
                            <td><input type="number" tabindex="-1" class="input-disabled merma_corte" name="merma_corte" value="${billet.merma_corte || ''}" step="0.01" min="0" required></td>
                            <td><input type="number" tabindex="-1" class="input-disabled scrap_pz" name="scrap_pz" value="${billet.scrap_pz || ''}" step="1" min="0"></td>
                            <td><input type="number" tabindex="-1" class="input-disabled scrap_mm" name="scrap_mm" value="${billet.scrap_mm || ''}" step="0.01" min="0"></td>
                            <td><input type="number" tabindex="-1" class="input-disabled mm_total_usados" name="mm_total_usados" value="${billet.mm_total_usados || ''}" step="0.01" min="0"></td>
                        </tr>
                        <tr class="row-justificar ${billet.justificacion_merma ? '' : 'd-none'}">
                            <td colspan="14">
                                <div class="d-flex flex-column justify-content-start align-items-start">
                                    <label class="mb-2 text-danger">Justificación de merma requerida para <strong>${billet.lote_pedimento || ''}</strong>:</label> 
                                    <div class="d-flex flex-row gap-3 align-items-start w-100">
                                        <div class="flex-shrink-0" style="width: 200px;">
                                            <select class="form-select causa_merma" name="causa_merma" required>
                                                <option value="">Seleccione causa...</option>
                                                <option value="Error humano" ${billet.causa_merma === 'Error humano' ? 'selected' : ''}>Error humano</option>
                                                <option value="Filo de herramienta gastada" ${billet.causa_merma === 'Filo de herramienta gastada' ? 'selected' : ''}>Filo de herramienta gastada</option>
                                                <option value="Daño en la materia prima" ${billet.causa_merma === 'Daño en la materia prima' ? 'selected' : ''}>Daño en la materia prima</option>
                                                <option value="Sellos especiales" ${billet.causa_merma === 'Sellos especiales' ? 'selected' : ''}>Sellos especiales</option>
                                            </select>
                                            <small class="text-muted">Causa principal</small>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="text" class="form-control justificacion_merma" name="justificacion_merma" value="${billet.justificacion_merma || ''}" placeholder="Detalle adicional de la justificación..." required>
                                            <small class="text-muted">Detalle adicional de la justificación</small>
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