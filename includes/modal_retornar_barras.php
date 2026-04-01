<!-- //////////////////////// INVENTARIOS DEBE COMPLETAR MM RETORNO DE CONTROL DE ALMACEN PARA COMPLETAR //////////////////////// -->
<div class="modal fade" id="modalRetorno" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span id="spanTitleModalRetorno" class="title-form"></span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="width:100%; margin-bottom:20px;">
                    <h5 class="modal-title">Claves de requisición con folio: <span></span></h5>
                    <div style="overflow-x: auto; width: 100%;">
                        <table class="table table-bordered border border-2 tabla-billets" style="table-layout: fixed; width: max-content;">
                            <thead>
                                <tr>
                                    <th style="width: 130px;">PERFIL</th>
                                    <th style="width: 80px;">COMPONENTE</th>
                                    <th style="width: 150px;">MATERIAL</th>
                                    <th style="width: 260px;">CLAVE</th>
                                    <th style="width: 220px;">LOTE</th>
                                    <th style="width: 120px;">MEDIDA</th>
                                    <th style="width: 80px;">PZ. TEÓRICAS</th>
                                    <th style="width: 120px;">H. TOTAL PERFIL</th>
                                    <th style="width: 120px;">H. COMPONENTE</th>
                                    <th style="width: 120px;">MM ENTREGADOS</th>
                                    <th style="width: 120px;">MM USADOS OPERADOR</th>
                                    <th style="width: 120px;">MM Retorno (nuevo stock)</th>
                                    <th style="width: 120px;">LONG. TOTAL DE SELLOS</th>
                                    <th style="width: 120px;">MERMA POR CORTE</th>
                                    <th style="width: 120px;">SCRAP PZ</th>
                                    <th style="width: 120px;">SCRAP MM</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Aquí van tus registros -->
                            </tbody>
                        </table>


                    </div>
                   
                    <!-- Sección de revisión de maquinado (solo lectura) -->
                    <div id="seccionRevisionMaquinado" class="mt-4 d-none">
                        <hr>
                        <h6>Revisión de resultados de maquinado</h6>
                        <div id="infoRevisionMaquinado" class="mt-1 p-2 bg-light rounded text-dark"></div>
                    </div>
                    <!-- Sección de observaciones de inventarios -->
                    <div class="mt-4">
                        <hr>
                        <h6>Observaciones de inventarios</h6>
                        <textarea id="observacionesInventario" class="form-control" rows="3" placeholder="Ingrese observaciones generales (opcional)"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="retornoFinalizado" type="button" class="btn-general">Listo</button>
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
    // Traer las claves para que inventario marque nuevo stock en MM de retorno la requisicion
    function ajaxClavesRetorno(idRequisicion) {
        $.ajax({
            url: '../ajax/traer_claves_control_almacen.php',
            type: 'get',
            data: {
                id_requisicion: idRequisicion
            },
            dataType: 'json',
            success: function (data) {
                $('#modalRetorno tbody').empty();
                $("#observacionesInventario").val("");

                if (data.success && data.billets.length > 0) {
                    $.each(data.billets, function (index, billet) {
                        let esExtra = "";
                        let esMerma = "";
                        let esReemplazo = "";
                        if (billet.es_extra == 1) {
                            esExtra = " (Barra extra)*";
                        }
                        if (billet.es_merma == 1) {
                            esMerma = " (Barra mermada)*";
                        }
                        if (billet.es_remplazo == 1) {
                            esReemplazo = " (Reemplazo de la barra: " + billet.lote_pedimento + ")*";
                        }
                        const esSoloLectura = (data.fecha_retorno_barras !== null && data.fecha_retorno_barras !== "");
                        if (esSoloLectura) {
                            $("#retornoFinalizado").hide(); // Quitar botón
                            $("#observacionesInventario").prop('readonly', true); // Observaciones solo lectura
                            $("#spanTitleModalRetorno").text("Resultados de las barras");
                            if(data.observaciones_inv){
                                $("#observacionesInventario").val(data.observaciones_inv);
                            }else{
                                $("#observacionesInventario").prop('placeholder', 'No hay observaciones...');
                            }
                        } else {
                            $("#retornoFinalizado").show();
                            $("#observacionesInventario").prop('readonly', false);
                            $("#spanTitleModalRetorno").text("Indique el nuevo stock en el campo MM de Retorno");
                        }

                        // Mostrar revisión de maquinado si existe
                        if (data.observacion_maquinado && data.observacion_maquinado.trim() !== '') {
                            $('#seccionRevisionMaquinado').removeClass('d-none');
                            $('#infoRevisionMaquinado').text(data.observacion_maquinado);
                        } else {
                            $('#seccionRevisionMaquinado').addClass('d-none');
                        }
                        // Clase dinámica para inputs
                        // Si ya existe fecha de retorno, TODO es input-disabled
                        let claseInput = esSoloLectura ? "input-disabled" : "input-text mm_retorno";
                        let readonlyAttr = esSoloLectura ? "readonly tabindex='-1'" : "";
                        $('#modalRetorno tbody').append(`
                            <tr>
                                <input type="hidden" tabindex="-1" name="id_requisicion" value="${idRequisicion || ''}">
                                <input type="hidden" tabindex="-1" name="id_control" value="${billet.id_control || ''}">
                                <input type="hidden" tabindex="-1" name="es_remplazo" value="${billet.es_remplazo || ''}">
                                <td><input type="text" tabindex="-1" class="input-disabled perfil_sello" value="${billet.perfil_sello || ''}"></td>
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
                                <td><input type="text" tabindex="-1" class="input-disabled material" value="${billet.material || ''}"></td>
                                <td><input type="text" tabindex="-1" class="input-disabled clave" value="${billet.clave || ''}"></td>
                                <td>
                                    <input type="text" tabindex="-1" class="input-disabled lote_pedimento" value="${billet.es_remplazo == 1 ? billet.lp_remplazo : billet.lote_pedimento}">
                                    <div class="d-flex flex-column">
                                        <span style="color:#ffc107; font-size: 0.8rem;">${esExtra}</span>
                                        <span style="color:#B71C1C; font-size: 0.8rem;">${esMerma}</span>
                                        <span style="color:#ffc107; font-size: 0.8rem;">${esReemplazo}</span>
                                    </div>
                                </td>
                                <td><input type="text" tabindex="-1" class="input-disabled medida" value="${billet.medida || ''}"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled pz_teoricas" value="${billet.pz_teoricas || 0}"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled altura_pz" value="${billet.altura_pz || 0}"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled h_componente" value="${billet.h_componente || 0}"></td>

                                <td><input type="number" readonly tabindex='-1' class="input-disabled mm_entrega" value="${billet.mm_entrega ?? ''}"></td>                                
                                <td><input type="number" tabindex="-1" class="input-disabled mm_usados" name="mm_usados" value="${billet.mm_total_usados || ''}" step="0.01" min="0"></td>
                                <td><input type="number" ${readonlyAttr} class="${claseInput}" name="mm_retorno" value="${billet.mm_retorno ?? ''}" step="0.01"></td>                                
                                <td><input type="number" tabindex="-1" class="input-disabled total_sellos" name="total_sellos" value="${billet.total_sellos || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled merma_corte" name="merma_corte" value="${billet.merma_corte || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled scrap_pz" name="scrap_pz" value="${billet.scrap_pz || ''}" step="1" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled scrap_mm" name="scrap_mm" value="${billet.scrap_mm || ''}" step="0.01" min="0"></td>
                            </tr>
                        `);
                    });
                } else {
                    $('#modalRetorno tbody').append('<tr><td colspan="16" class="text-center">No hay claves disponibles para esta requisición.</td></tr>');
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                $('#modalRetorno tbody').append('<h5>Error en ajax</h5>');
                sweetAlertResponse("error", "Error", "Error al consultar cotizaciones: " + error, "none");
            }
        });
    }


    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        //CLICK RETORNAR BARRAS TAL REQUISICION DESDE LA TABLA
        $("#productionTable").on('click', ".btn-claves-retorno", function () {
            $dataIdRequisicion = $(this).data('id-requisicion');
            $("#modalRetorno h5 span").text($dataIdRequisicion);
            ajaxClavesRetorno($dataIdRequisicion);
        });

        // ENVIAR EL NUEVO STOCK COMO RETORNO DE LA BARRAS
        $("#retornoFinalizado").on('click', function () {
            let valido = true;

            // Validar solo inputs que el usuario puede editar (excluimos .input-disabled)
            $('#modalRetorno tbody input:not(.input-disabled):not([type="hidden"])').each(function () {
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
                sweetAlertResponse("warning", "Sin registros", "No hay registros por enviar. Se necesita mínimo un registro con LOTE.", "none");
                return;
            }
            // Obtener observaciones del textarea
            const observaciones_inv = $('#observacionesInventario').val().trim();

            $(this).addClass("d-none");

            // Enviar al servidor
            $.ajax({
                url: '../ajax/retornar_barras.php',
                type: 'post',
                data: {
                    registros: JSON.stringify(datos),
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
</script>