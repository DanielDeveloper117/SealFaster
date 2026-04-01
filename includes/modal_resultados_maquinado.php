<!-- ////////////////////////////// VER REGISTROS DEL MAQUINADO RESULTANTE Y MERMAS PARA REVISION //////////////////////// -->
<div class="modal fade" id="modalTablaMaquinadoMermas" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Resultados de maquinado y mermas</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="width:100%; margin-bottom:20px;">
                    <h5 class="modal-title">Barras de requisición con folio: <span id="folioRequisicion"></span></h5>
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
                            <tbody id="tbodyResultadosMaquinado">
                                <!-- Aquí van tus registros -->
                            </tbody>
                        </table>
                    </div>

                    <div id="badgeRevisionContainer">
                    </div>
                    <div id="infoRevisionContainer">
                    </div>
                    <!-- Sección de observaciones (solo para Gerente) -->
                    <div id="seccionObservaciones" class="mt-4 d-none">
                        <hr>
                        <h6>Revisión de resultados de maquinado</h6>
                        <textarea id="observacionesGerente" class="form-control" rows="3" placeholder="Ingrese observaciones de los resultados del maquinado (opcional)..."></textarea>
                        <input id="inputIdRequisicionResultadosMaquinado" type="hidden">
                    </div>
                    <!-- Sección de observaciones de inventarios (solo lectura) -->
                    <div id="seccionObservacionesInventario" class="mt-4 d-none">
                        <hr>
                        <h6>Observaciones de inventarios</h6>
                        <div id="infoObservacionesInventario" class="mt-1 p-2 bg-light rounded"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="terminarRevision" type="button" class="btn-general d-none">Terminar revisión</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    // Función para cargar los resultados del maquinado
    function cargarResultadosMaquinado(idRequisicion, rol) {
        $.ajax({
            url: '../ajax/obtener_resultados_maquinado.php',
            type: 'get',
            data: {
                id_requisicion: idRequisicion
            },
            dataType: 'json',
            success: function (data) {
                $('#tbodyResultadosMaquinado').empty();

                if (data.success && data.billets.length > 0) {
                    $.each(data.billets, function (index, billet) {
                        // Determinar si mostrar la justificación
                        const mostrarJustificacion = billet.justificacion_merma && billet.justificacion_merma.trim() !== '';

                        // Determinar clase para merma real (rojo si es mayor a 0)
                        const estiloMermaReal = (parseFloat(billet.mm_merma_real) > 0) ? 'color:#ff1100 !important;' : '';

                        $('#tbodyResultadosMaquinado').append(`
                            <tr>
                                <td>${billet.perfil_sello || ''}</td>
                                <td class="text-center">
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
                                <td>${billet.material || ''}</td>
                                <td>${billet.lote_pedimento || ''}</td>
                                <td>${billet.medida || ''}</td>
                                <td class="text-end">${parseFloat(billet.mm_entrega || 0).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(billet.altura_pz || 0).toFixed(2)}</td>
                                <td class="text-end">${parseInt(billet.pz_teoricas || 0)}</td>
                                <td class="text-end">${parseFloat(billet.total_sellos || 0).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(billet.h_componente || 0).toFixed(2)}</td>
                                <td class="text-end">${parseInt(billet.pz_maquinadas || 0)}</td>
                                <td class="text-end">${parseFloat(billet.mm_usados || 0).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(billet.merma_corte || 0).toFixed(2)}</td>
                                <td class="text-end">${parseInt(billet.scrap_pz || 0)}</td>
                                <td class="text-end">${parseFloat(billet.scrap_mm || 0).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(billet.mm_total_usados || 0).toFixed(2)}</td>
                                <td class="text-end fw-bold" style="${estiloMermaReal}">${parseFloat(billet.mm_merma_real || 0).toFixed(2)}</td>
                            </tr>
                            ${mostrarJustificacion ? `
                            <tr class="table-warning">
                                <td colspan="17">
                                    <div class="d-flex flex-column">
                                        <strong class="text-danger">Causa y justificación de merma:</strong>
                                        <span>${billet.causa_merma} - ${billet.justificacion_merma}</span>
                                    </div>
                                </td>
                            </tr>
                            ` : ''}
                        `);
                    });
                } else {
                    $('#tbodyResultadosMaquinado').append('<tr><td colspan="17" class="text-center">No hay registros de maquinado para esta requisición.</td></tr>');
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
                    } else {
                        $('#badgeRevisionContainer').html(`
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Pendiente de revisión</strong> - 
                                El maquinado está finalizado pero aún no se ha realizado la revisión de los resultados.
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
                    } else {
                        $('#badgeRevisionContainer').html(`
                            <div class="alert alert-warning mt-3">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Pendiente de revisión</strong> - 
                                El maquinado está finalizado pero aún no se ha realizado la revisión de los resultados.
                            </div>
                        `);
                        $('#infoRevisionContainer').html("");
                    }
                    $('#seccionObservaciones').addClass('d-none');
                    $('#terminarRevision').addClass('d-none');
                }

                // Mostrar observaciones de inventarios si existen
                console.log(data);
                if (data.requisicion.observaciones_inv && data.requisicion.observaciones_inv.trim() !== '') {
                    $('#seccionObservacionesInventario').removeClass('d-none');
                    $('#infoObservacionesInventario').text(data.requisicion.observaciones_inv);
                } else {
                    $('#seccionObservacionesInventario').addClass('d-none');
                }

            },
            error: function (xhr, status, error) {
                console.error('Error al cargar resultados:', error);
                $('#tbodyResultadosMaquinado').html('<tr><td colspan="17" class="text-center text-danger">Error al cargar los datos</td></tr>');
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
    // Función para actualizar la fila de la tabla principal sin recargar
    function recargarTablaPrincipal(idRequisicion) {
        const $row = $(`#productionTable tr[data-id-requisicion="${idRequisicion}"]`);
        if ($row.length > 0) {
            const $btn = $row.find('.btn-tabla-maquinado-mermas');
            if ($btn.length > 0) {
                // Cambiar de btn-amber a btn-auth
                $btn.removeClass('btn-amber').addClass('btn-auth');
                // Asegurarse de que el icono sea el correcto (bi-clipboard-check)
                $btn.html('<i class="bi bi-clipboard-check"></i>');
                // Actualizar el título
                $btn.attr('title', 'Ver registros de maquinado y merma');
            }
        }
    }


    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        // CLICK A REVISAR RESULTADOS DE MAQUINADO Y MERMAS
        $(document).on('click', '.btn-tabla-maquinado-mermas', function () {
            const idRequisicion = $(this).data('id-requisicion');
            const rol = $(this).data('rol');

            $('#folioRequisicion').text(idRequisicion);
            $("#inputIdRequisicionResultadosMaquinado").val(idRequisicion);
            cargarResultadosMaquinado(idRequisicion, rol);
        });
        // Función para terminar revisión (solo Gerente)
        $('#terminarRevision').on('click', function () {
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

                        // Recargar la tabla principal o actualizar la fila específica
                        if (typeof recargarTablaPrincipal === 'function') {
                            recargarTablaPrincipal(response.id_requisicion);
                        }
                    });
                }
            });
        });
    }); 
</script>