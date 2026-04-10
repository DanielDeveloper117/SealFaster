<!-- ///////////////////////MODAL TABLA DE BARRAS PENDIENTES POR AUTORIZAR /////////////////////// -->
<div class="modal fade" id="modalTableBarrasPendientes" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 85% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form d-flex gap-2 align-items-center"><span>Barras pendientes por autorizar. Folio de requisición: </span>                    
                    <form action="../includes/functions/generar_requisicion.php" method="GET" target="_blank">
                        <input id="hiddenIdRequisicionBarrasPendientes" type="hidden" name="id_requisicion">
                        <button type="submit" class="btn btn-link p-0 border-0 text-decoration-underline fs-5"></button>
                    </form>
                </span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="overflow-x: auto; width: 100%; max-height:300px !important; overflow-y:auto;">
                    <table id="tableBarrasPendientes" class="table table-bordered border border-2 tabla-billets mb-3" style="table-layout: fixed; width: max-content;">
                        <thead>
                            <tr>
                                <th style="width: 50px;">ACCIONES</th>
                                <th style="width: 130px;">PERFIL</th>
                                <th style="width: 160px;">MATERIAL</th>
                                <th style="width: 280px;">CLAVE</th>
                                <th style="width: 220px;">LOTE PEDIMENTO</th>
                                <th style="width: 130px;">MEDIDA</th>
                                <th style="width: 80px;">PZ TEÓRICAS</th>
                                <th style="width: 100px;">ALTURA DE PZ</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>
<!-- ///////////////////////MODAL CONFIRMAR AUTORIZAR BARRA /////////////////////// -->
<div class="modal fade" id="modalAutorizarBarra" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-autorizar-barra" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="label-modal-autorizar-barra">Confirmar autorización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p></p>
                <form id="formAutorizarBarra">
                    <input type="hidden" name="id_requisicion" id="autorizarIdRequisicion" value="">
                    <input type="hidden" name="id_control" id="autorizarIdControl" value="">
                    <input type="hidden" name="accion" id="autorizarAccion" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnConfirmAutorizarBarra" class="btn-auth">Si, continuar</button>
                <button type="button" id="btnCancelAutorizarBarra" class="btn-cancel" data-bs-dismiss="modal">No, cancelar</button>
            </div>
        </div>
    </div>
</div>
<!-- ///////////////////////MODAL CONFIRMAR RECHAZAR BARRA /////////////////////// -->
<div class="modal fade" id="modalRechazarBarra" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-rechazar-barra" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="label-modal-rechazar-barra">Rechazar barra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formRechazarBarra">
                    <input type="hidden" id="idRequisicionRechazo" name="id_requisicion" value="">
                    <input type="hidden" id="inputControlRechazo" name="id_control" value="">
                    <input type="hidden" id="inputAccionRechazo" name="accion" value="">

                    <div class="mb-3">
                        <label for="inputRazonRechazo" class="form-label">Razón del rechazo</label>
                        <textarea id="inputRazonRechazo" name="razon" class="form-control" rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnEnviarRechazo" class="btn-general">Enviar</button>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
 <script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    // ============================================================
    //              ******** FUNCIONES ********
    // ============================================================
    // Traer las barras pendientes por autorizar
    function cargarTablaBarrasPendientes(idRequisicion) {
        $.ajax({
            url: '../ajax/barras_pendientes_autorizar.php',
            type: 'get',
            data: { 
                id_requisicion: idRequisicion
            },
            dataType: 'json',
            success: function(data) {
                $('#tableBarrasPendientes tbody').empty();

                if (data.success && data.billets && data.billets.length > 0) {
                    $.each(data.billets, function(index, billet) {
                        const tieneRemplazo = billet.situacion === "remplazo";
                        const tieneEliminacion = billet.situacion === "eliminacion";
                        const tieneJustificacionRemplazo = billet.justificacion_remplazo && billet.justificacion_remplazo.trim() !== '';
                        const tieneJustificacionExtra = billet.justificacion_extra && billet.justificacion_extra.trim() !== '';
                        const tieneJustificacionEliminacion = billet.justificacion_eliminacion && billet.justificacion_eliminacion.trim() !== '';

                        // Decidir cuál justificación mostrar según la situación solicitada.
                        let mostrarJustificacionTipo = null; // 'extra' | 'remplazo' | 'eliminacion' | null
                        let mostrarJustificacionTexto = '';
                        
                        if (billet.situacion === 'remplazo') {
                            if (tieneJustificacionRemplazo) {
                                mostrarJustificacionTipo = 'remplazo';
                                mostrarJustificacionTexto = billet.justificacion_remplazo;
                            }
                        } else if (billet.situacion === 'eliminacion') {
                            if (tieneJustificacionEliminacion) {
                                mostrarJustificacionTipo = 'eliminacion';
                                mostrarJustificacionTexto = billet.justificacion_eliminacion;
                            }
                        } else if (billet.situacion === 'extra') {
                            if (tieneJustificacionExtra) {
                                mostrarJustificacionTipo = 'extra';
                                mostrarJustificacionTexto = billet.justificacion_extra;
                            }
                        }
                        
                        // Determinar texto del botón y tooltip
                        let textoBoton = "Autorizar acción";
                        let textoBotonRechazo = "Rechazar acción";
                        let textoSmall = "Acción pendiente";
                        let iconoClase = "bi-check-circle";

                        if (tieneRemplazo) {
                            textoBoton = "Autorizar reemplazo de barra";
                            textoBotonRechazo = "Rechazar reemplazo de barra";
                            textoSmall = "Remplazo de barra";
                            iconoClase = "bi-arrow-left-right";
                        } else if (tieneEliminacion) {
                            textoBoton = "Autorizar eliminación de barra";
                            textoBotonRechazo = "Rechazar eliminación de barra";
                            textoSmall = "Eliminación de barra";
                            iconoClase = "bi-trash";
                        } else {
                            // Extra
                            textoBoton = "Autorizar barra extra";
                            textoBotonRechazo = "Rechazar barra extra";
                            textoSmall = "Barra extra";
                            iconoClase = "bi-plus-circle";
                        }
                        
                        $('#tableBarrasPendientes tbody').append(`
                            <tr class="data-row" data-id-control="${billet.id_control}">
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn-auth btn-sm btn-autorizar-barra"
                                                data-id-requisicion="${data.id_requisicion}"
                                                data-id-control="${billet.id_control}"
                                                data-accion="${billet.situacion}"
                                                title="${textoBoton}">
                                            <i class="bi ${iconoClase}"></i> Autorizar
                                        </button>
                                        <button type="button" class="btn-cancel btn-sm btn-rechazar-barra"
                                                data-id-requisicion="${data.id_requisicion}"
                                                data-id-control="${billet.id_control}"
                                                data-accion="${billet.situacion}"
                                                title="${textoBotonRechazo}">
                                            <i class="bi bi-x-octagon"></i> Rechazar
                                        </button>
                                        <small>${textoSmall}</small>
                                    </div>
                                </td>

                                <td>
                                    <input type="text" class="form-control form-control-sm input-disabled perfil_sello" 
                                        value="${billet.perfil_sello || ''}" readonly>
                                </td>
                                
                                <td>
                                    <input type="text" class="form-control form-control-sm input-disabled material" 
                                        value="${billet.material || ''}" readonly>
                                </td>
                                
                                <td>
                                    ${billet.clave_remplazo && billet.clave_remplazo.trim() !== ''
                                        ? `<div class="d-flex flex-column gap-1">
                                            <input type="text" class="form-control form-control-sm input-disabled clave" 
                                                value="${billet.clave || ''}" readonly>
                                            <small class="text-muted">Reemplazar por:</small>
                                            <input type="text" class="form-control form-control-sm input-disabled clave_remplazo" 
                                                value="${billet.clave_remplazo}" readonly>
                                        </div>`
                                        : `<input type="text" class="form-control form-control-sm input-disabled clave" 
                                                value="${billet.clave || ''}" readonly>`
                                    }
                                </td>

                                <td>
                                    ${billet.lp_remplazo && billet.lp_remplazo.trim() !== ''
                                        ? `<div class="d-flex flex-column gap-1">
                                            <input type="text" class="form-control form-control-sm input-disabled lote_pedimento" 
                                                value="${billet.lote_pedimento || ''}" readonly>
                                            <small class="text-muted">Reemplazar por:</small>
                                            <input type="text" class="form-control form-control-sm input-disabled lp_remplazo" 
                                                value="${billet.lp_remplazo}" readonly>
                                        </div>`
                                        : `<input type="text" class="form-control form-control-sm input-disabled lote_pedimento" 
                                                value="${billet.lote_pedimento || ''}" readonly>`
                                    }
                                </td>

                                <td>
                                    ${billet.medida_remplazo && billet.medida_remplazo.trim() !== ''
                                        ? `<div class="d-flex flex-column gap-1">
                                            <input type="text" class="form-control form-control-sm input-disabled medida" 
                                                value="${billet.medida || ''}" readonly>
                                            <small class="text-muted">Reemplazar por:</small>
                                            <input type="text" class="form-control form-control-sm input-disabled medida_remplazo" 
                                                value="${billet.medida_remplazo}" readonly>
                                        </div>`
                                        : `<input type="text" class="form-control form-control-sm input-disabled medida" 
                                                value="${billet.medida || ''}" readonly>`
                                    }
                                </td>

                                <td>
                                    <input type="text" class="form-control form-control-sm input-disabled pz_teoricas" 
                                        value="${billet.pz_teoricas || ''}" readonly>
                                </td>
                                
                                <td>
                                    <input type="text" class="form-control form-control-sm input-disabled altura_pz" 
                                        value="${billet.altura_pz || ''}" readonly>
                                </td>
                            </tr>
                            
                            ${mostrarJustificacionTipo ? `
                            <tr class="row-justificacion">
                                <td colspan="8">
                                    <div class="p-2">
                                        <small class="text-muted d-block mb-1">
                                            ${mostrarJustificacionTipo === 'extra' ? 'Justificación de barra extra para' : 'Justificación para'} <strong>${billet.lote_pedimento || ''}:</strong>
                                        </small>
                                        <input type="text" class="form-control form-control-sm input-disabled ${mostrarJustificacionTipo === 'extra' ? 'justificacion_extra' : 'justificacion_remplazo'}" 
                                            value="${mostrarJustificacionTexto}" readonly>
                                    </div>
                                </td>
                            </tr>
                            ` : ''}
                        `);
                    });
                    
                } else {
                    $('#tableBarrasPendientes tbody').append(`
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                No hay barras pendientes de autorización para esta requisición.
                            </td>
                        </tr>
                    `);
                }
                
                console.log('Fuente de datos:', data.fuente);
            },
            error: function(xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                sweetAlertResponse("error", "Error", "Error al consultar las barras pendientes: " + error, "none");
            }
        });
    }
    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        // VER LA TABLA DE BARRAS DE CONTROL DE ALMACEN PARA ENTREGAR
        $(document).on('click', '.btn-barras-pendientes', function(){
            const idRequisicionEntrega = $(this).data('id_requisicion');
            
            $('#modalTableBarrasPendientes .title-form form input').val(idRequisicionEntrega);
            $('#modalTableBarrasPendientes .title-form form button').text(idRequisicionEntrega);
            cargarTablaBarrasPendientes(idRequisicionEntrega);
        });
        // CLICK para mostrar modal de confirmar autorización de barra
        $(document).on('click', '.btn-autorizar-barra', function(){
            const idRequisicion = $(this).data('id-requisicion');
            const idControl = $(this).data('id-control');
            const accion = $(this).data('accion');

            console.log('Autorizar barra', { idRequisicion, idControl, accion });
            if (accion === 'remplazo') {
                $("#modalAutorizarBarra .modal-body p").text("¿Está seguro de autorizar el remplazo de la barra?");
            } else if (accion === 'eliminacion') {
                $("#modalAutorizarBarra .modal-body p").text("¿Está seguro de autorizar la eliminación de la barra?");
            } else {
                $("#modalAutorizarBarra .modal-body p").text("¿Está seguro de autorizar la barra extra?");
            }
            // Pasar valores a los inputs ocultos del modal
            $('#autorizarIdRequisicion').val(idRequisicion);
            $('#autorizarIdControl').val(idControl);
            $('#autorizarAccion').val(accion);

            // Mostrar modal
            $('#modalAutorizarBarra').modal('show');
        });
        // SI AUTORIZAR EL REMPLAZO DE BARRA O LA BARRA EXTRA
        $("#btnConfirmAutorizarBarra").on("click", function(){
            let autorizarIdRequisicion = $("#autorizarIdRequisicion").val();
            let autorizarIdControl = $("#autorizarIdControl").val();
            let autorizarAccion = $("#autorizarAccion").val();

            $("#btnConfirmAutorizarBarra").addClass("d-none");
        
            $.ajax({
                url: '../ajax/autorizar_accion_barra.php',
                type: 'POST',
                data: { 
                    id_requisicion: autorizarIdRequisicion,
                    id_control: autorizarIdControl,
                    accion: autorizarAccion
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        // Mostrar mensaje de éxito pero mantener el modal abierto
                        sweetAlertResponse("success", "Proceso exitoso", data.message, "none");
                        $("#formAutorizarBarra")[0].reset();
                        // Ocultar el renglón correspondiente en la tabla de barras pendientes
                        try {
                            var selector = '#tableBarrasPendientes tbody tr.data-row[data-id-control="' + autorizarIdControl + '"]';
                            var $row = $(selector);
                            if ($row.length) {
                                $row.hide();
                                // Si existe una fila de justificación justo después, ocultarla también
                                var $next = $row.next('tr.row-justificacion');
                                if ($next.length) {
                                    $next.hide();
                                }
                            }
                        } catch (err) {
                            console.error('Error ocultando renglón de barra pendiente:', err);
                        }
                        if(data.no_hay_pendientes){
                            $('#tableBarrasPendientes tbody').append(`
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        No hay barras pendientes de autorización para esta requisición.
                                    </td>
                                </tr>
                            `);
                        }
                    } else {
                        sweetAlertResponse("warning", "Advertencia", data.message, "none");
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al realizar la petición AJAX:', error);
                    console.error('Respuesta del servidor:', xhr.responseText); // Muestra el error enviado por PHP
                    sweetAlertResponse("error", "Error", "Ocurrió un error al autorizar la barra", "none");
                },
                complete: function(){
                    // Re-habilitar el botón siempre
                    $("#btnConfirmAutorizarBarra").removeClass("d-none");
                    $('#modalAutorizarBarra').modal('hide');
                    
                }
            });

        });
        // CLICK para mostrar modal de rechazar barra
        $(document).on('click', '.btn-rechazar-barra', function(){
            const idRequisicion = $(this).data('id-requisicion');
            const idControl = $(this).data('id-control');
            const accion = $(this).data('accion');

            // Pasar valores a los inputs ocultos del modal de rechazo
            $('#idRequisicionRechazo').val(idRequisicion);
            $('#inputControlRechazo').val(idControl);
            $('#inputAccionRechazo').val(accion);
            // Limpiar textarea previa
            $('#inputRazonRechazo').val('');

            // Mostrar modal
            $('#modalRechazarBarra').modal('show');
        });
        // Enviar rechazo de barra
        $('#btnEnviarRechazo').on('click', function(e){
            e.preventDefault();

            var id_requisicion = $('#idRequisicionRechazo').val();
            var id_control = $('#inputControlRechazo').val();
            var accion = $('#inputAccionRechazo').val();
            var razon = $('#inputRazonRechazo').val().trim();

            if (!razon) {
                sweetAlertResponse('warning', 'Campo requerido', 'Por favor ingresa la razón del rechazo.', 'none');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).text('Enviando...');

            $.ajax({
                url: '../ajax/rechazar_barra.php',
                method: 'POST',
                data: {
                    id_requisicion: id_requisicion,
                    id_control: id_control,
                    accion: accion,
                    razon: razon
                },
                dataType: 'json',
                success: function(resp) {
                    if (resp && resp.success) {
                        sweetAlertResponse('success', 'Rechazo enviado', resp.message || 'La barra fue rechazada correctamente.', 'none');
                        // Ocultar renglón correspondiente en la tabla de pendientes
                        try {
                            var selector = '#tableBarrasPendientes tbody tr.data-row[data-id-control="' + id_control + '"]';
                            var $row = $(selector);
                            if ($row.length) {
                                $row.hide();
                                var $next = $row.next('tr.row-justificacion');
                                if ($next.length) $next.hide();
                            }
                        } catch (err) {
                            console.error('Error ocultando renglón tras rechazo:', err);
                        }
                        if(resp.no_hay_pendientes){
                            $('#tableBarrasPendientes tbody').append(`
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        No hay barras pendientes de autorización para esta requisición.
                                    </td>
                                </tr>
                            `);
                        }
                        // reset form
                        $('#formRechazarBarra')[0].reset();
                        // ocultar modal de rechazo (mantener modal de pendientes abierto)
                        $('#modalRechazarBarra').modal('hide');
                    } else {
                        sweetAlertResponse('warning', 'Advertencia', resp.message || 'No se pudo procesar el rechazo.', 'none');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX al rechazar barra:', xhr.responseText || error);
                    sweetAlertResponse('error', 'Error', 'Ocurrió un error al enviar el rechazo.', 'self');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Enviar');
                }
            });
        });
    });
</script>