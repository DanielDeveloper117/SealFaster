    $(document).ready(function(){
        $('#buscadorCotizaciones').chosen({
            placeholder_text_single: "Seleccione una cotización",
            no_results_text: "No se encontró",
            width: "100%"
        });

        // Realizar la llamada AJAX para obtener las cotizaciones
        $.ajax({
            url: '../ajax/ajax_cotizaciones_chosen.php', 
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Verifica que la respuesta tenga datos
                if (data.length > 0) {
                    $.each(data, function(index, item) {
                        if(item.di_sello == "0.00"){
                            item.di_sello = item.di_sello2;
                        }
                        if(item.de_sello == "0.00"){
                            item.de_sello = item.de_sello2;
                        }
                        if(item.a_sello == "0.00"){
                            item.a_sello = item.a_sello2;
                        }

                        // Determinar si está vencida
                        const estaVencida = item.esta_vencida == 1 || item.horas_restantes < 0;
                        
                        // Texto base de la opción
                        let textoOpcion = `${item.id_cotizacion} - ${item.perfil_sello} - ${item.di_sello}/${item.de_sello}/${item.a_sello}`;
                        
                        // Si está vencida, agregar leyenda
                        if (estaVencida) {
                            textoOpcion += ` - <small style="color: #dc3545;">Vencida internamente</small>`;
                        }

                        $("#buscadorCotizaciones").append(
                            `
                            <option id="c_${item.id_cotizacion}" 
                                    value="${item.id_cotizacion}"
                                    data-id="${item.id_cotizacion}"
                                    data-perfil="${item.perfil_sello}"
                                    data-tipomedida="${item.tipo_medida}"
                                    data-di="${item.di_sello || item.di_sello2}"
                                    data-de="${item.de_sello}"
                                    data-a="${item.a_sello}"
                                    ${estaVencida ? 'disabled style="color: #6c757d; font-style: italic; background-color: #f8f9fa;"' : ''}
                            >${textoOpcion}</option>
                            `
                        );
                        $("#buscadorCotizaciones").trigger("chosen:updated");
                    });
                } else {
                    console.log('No se encontraron cotizaciones');
                }
            },
            error: function() {
                console.error('Error al realizar la petición AJAX');
            }
        });

        function actualizarContadorComentario() {
            $("#contadorComentario").text(`${$("#inputComentario").val().length} / 50 caracteres`);
        }
        $("#inputComentario").on("input", function () {
            actualizarContadorComentario();
        });

        // cuando el usuario selecciona una cotizacion del selector chosen
        let cotizacionesSeleccionadas = [];

        $("#buscadorCotizaciones").on("change", function () {
            const selectedId = $(this).val();

            if (!selectedId) return; // si no seleccionó nada

            const $option = $(this).find("option:selected");

            const id = $option.data("id");
            const perfil = $option.data("perfil");
            const tipoMedida = $option.data("tipomedida");
            const di = $option.data("di");
            const de = $option.data("de");
            const a = $option.data("a");

            $option.addClass('d-none');

            // evitar duplicados
            if (cotizacionesSeleccionadas.includes(id)) {
                alert("Esta cotización ya fue agregada.");
            } else {
                cotizacionesSeleccionadas.push(id);

                $('#miniTableCotizaciones tbody').append(`
                    <tr>
                        <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila">X</button></td>
                        <td>${id}</td>
                        <td>${perfil}</td>
                        <td>${di}/${de}/${a}</td>
                    </tr>
                `);
                        
                        // <td>${tipoMedida}</td>
                // Actualizar input oculto con los IDs separados por coma
                $('#inputCotizaciones').val(cotizacionesSeleccionadas.join(', '));
            }

            // Limpiar selección de Chosen
            $(this).val('').trigger("chosen:updated");
        });
        // Delegamos el evento por si las filas se agregan dinámicamente
        $(document).on('click', '.btnEliminarFila', function () {
            let fila = $(this).closest('tr');
            let idAEliminar = fila.find('td:eq(1)').text().trim(); // Asegura eliminar espacios
            let idCotizacionOption = "#c_" + idAEliminar;
            $(idCotizacionOption).removeClass('d-none');
            $('#buscadorCotizaciones').trigger("chosen:updated");
            // Eliminar del arreglo (usa == para tolerar cadena vs número)
            cotizacionesSeleccionadas = cotizacionesSeleccionadas.filter(id => id != idAEliminar);
            // Actualizar el input oculto
            $('#inputCotizaciones').val(cotizacionesSeleccionadas.join(','));
            // Eliminar la fila visualmente
            fila.remove();
        });

        function establecerFechaHoraLegible(idInput) {
            const now = new Date();

            const fechaFormateada = now.toLocaleString('es-MX', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            const input = document.getElementById(idInput);
            if (input) {
                input.value = fechaFormateada;
            }
        }

        // CAMBIAR A add AL CLICK AGREGAR REGISTRO
        $("#btnAgregar").on("click", function(){
            establecerFechaHoraLegible('inputFecha');
            $('#modalAgregarEditar').modal('show');
            $('#inputAction').val('insert');
            $("#titleModalAddEdit").text("Crear nueva requisicion");

            // Restaurar todas las opciones visibles
            $('#buscadorCotizaciones option').removeClass('d-none');
            $('#buscadorCotizaciones').trigger("chosen:updated");

            // Limpiar tabla, input y arreglo
            $('#miniTableCotizaciones tbody').empty();
            $('#inputCotizaciones').val('');
            cotizacionesSeleccionadas = [];
        });
        // CLICK A EDITAR UN REGISTRO
        $('#productionTable').on('click', '.edit-btn', function() {
            establecerFechaHoraLegible('inputFecha');
            var dataIdRequisicion = $(this).data('id_requisicion');
            $dataNombreVendedor=$(this).attr('data-nombre_vendedor');
            $dataSucursal=$(this).attr('data-sucursal');
            $dataCliente=$(this).attr('data-cliente');
            $dataFolio=$(this).attr('data-folio');
            $dataNumPedido=$(this).attr('data-num_pedido');
            $dataFactura=$(this).attr('data-factura');
            $dataPaqueteria=$(this).attr('data-paqueteria');
            $dataComentario=$(this).attr('data-comentario')
            $dataCotizaciones=$(this).attr('data-cotizaciones');

            $('#inputIdRequisicion').val(dataIdRequisicion);
            $('#inputVendedor').val($dataNombreVendedor);
            $('#inputSucursal').val($dataSucursal);
            $('#inputCliente').val($dataCliente);
            $('#inputFolio').val($dataFolio);
            $('#inputPedido').val($dataNumPedido);
            $('#inputFactura').val($dataFactura);
            $('#inputPaqueteria').val($dataPaqueteria);
            $('#inputComentario').val($dataComentario);
            $('#inputCotizaciones').val($dataCotizaciones);

            $('#inputAction').val('update');
            $('#modalAgregarEditar').modal('show');
            $("#titleModalAddEdit").text("Editar registro");

            // Restaurar todas las opciones visibles
            $('#buscadorCotizaciones option').removeClass('d-none');

            // Limpiar selección actual
            cotizacionesSeleccionadas = [];

            let cotizacionesStr = $dataCotizaciones || "";
            let cotizacionesArray = cotizacionesStr.split(',').map(id => id.trim()).filter(id => id !== '');

            cotizacionesSeleccionadas = cotizacionesArray;

            // Ocultar en el select las cotizaciones ya seleccionadas
            cotizacionesArray.forEach(function(id) {
                let optionId = "#c_" + id;
                $(optionId).addClass('d-none');
            });

            $('#buscadorCotizaciones').trigger("chosen:updated");

            // Mostrar cotizaciones en la tabla
            $('#miniTableCotizaciones tbody').empty();
            cotizacionesArray.forEach(function(id) {
                const option = $("#c_" + id);
                const perfil = option.data("perfil");
                const tipoMedida = option.data("tipomedida");
                const di = option.data("di");
                const de = option.data("de");
                const a = option.data("a");

                $('#miniTableCotizaciones tbody').append(`
                    <tr>
                        <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila">X</button></td>
                        <td>${id}</td>
                        <td>${perfil}</td>
                        
                        <td>${di}/${de}/${a}</td>
                    </tr>
                `);
                // <td>${tipoMedida}</td>
            });

            actualizarContadorComentario();
        });

        let intervaloQR = null;
        function verificarAutorizacionQR(idRequisicion, autoriza) {
            cancelarVerificacionQR(); // siempre cancelamos anterior antes de empezar nuevo
            intervaloQR = setInterval(() => {
                $.ajax({
                    url: '../ajax/ajax_verificar_autorizacion.php',
                    method: 'GET',
                    data: {
                        id_requisicion: idRequisicion,
                        autoriza: autoriza
                    },
                    success: function (respuesta) {
                        if (respuesta === 'true') {
                            cancelarVerificacionQR();
                            sweetAlertResponse("success", "Autorización confirmada", `La requisición ha sido autorizada correctamente.`, "self");
                        }
                    },
                    error: function () {
                        console.error("Error al consultar el estatus de autorización.");
                    }
                });
                console.log("Se ha enviado la solicitud para verificar autorizacion QR.");
            }, 4000);
        }

        // CLICK A Generar QR para autorizar
        $("#productionTable").on('click', ".btn-gerente-autoriza, .btn-admin-autoriza", function () {
            let idRequisicion = $(this).data('id-requisicion');
            let autoriza = $(this).data('autoriza');

            // Llamar al script PHP que devuelve JSON con el QR
            $.ajax({
                url: `../includes/functions/generar_qr.php?id_requisicion=${encodeURIComponent(idRequisicion)}&t=${encodeURIComponent(autoriza)}`,
                method: "GET",
                dataType: "json",
                success: function (resp) {
                    if (resp.success) {
                        // Mostrar QR volátil
                        let imgTag = `<img src="data:image/png;base64,${resp.qrBase64}" width="200" height="200">`;
                        $("#ContainerQR, #ContainerQR2").html(imgTag);

                        // Mostrar la URL debajo del QR
                        let linkTag = `
                            <a href="${resp.url}" target="_blank" class="mt-2 fs-3">
                                Ir a firmar
                            </a>`;
                        $("#qrLinkContainer, #qrLinkContainer2").html(linkTag);

                        $(".btnFirmaPredeterminada").data("id-requisicion", idRequisicion);
                        $(".btnFirmaPredeterminada").data("autoriza", autoriza);

                        // Iniciar verificación periódica
                        verificarAutorizacionQR(idRequisicion, autoriza);
                    } else {
                        sweetAlertResponse("error", "Error al generar QR", resp.error || "Error desconocido.", "self");
                    }
                },
                error: function (xhr, status, error) {
                    sweetAlertResponse("error", "Error AJAX", "No se pudo generar el QR: " + error, "self");
                }
            });

        });

        // FIRMAR CON LA FIRMA PREDETERMINADA
        $(".btnFirmaPredeterminada").on("click", function(){
            const idRequisicionX = $(this).data('id-requisicion');
            const autorizaX = $(this).data('autoriza');
            cancelarVerificacionQR();
            $(".btnFirmaPredeterminada").addClass("d-none");
            $(this).addClass("d-none");
            $.ajax({
                url: '../ajax/autorizar_firma_predeterminada.php',
                method: 'POST',
                data: {
                    id_requisicion: idRequisicionX,
                    t: autorizaX
                },
                success: function(data) {
                    if (data.success) {
                        sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                    } else {
                        sweetAlertResponse("warning", "Advertencia", data.error, "self");
                    }
                },
                error: function () {
                    sweetAlertResponse("error", "Error", "Ocurrio algo inesperado al autorizar", "self");
                    console.error("Error al consultar el estatus de autorización.");
                }
            });        

        });

        function cancelarVerificacionQR() {
            if (intervaloQR) {
                clearInterval(intervaloQR);
                intervaloQR = null;
                console.log("Verificación QR cancelada.");
            }
        }

        $('#modalGerenteAutoriza, #modalAdminAutoriza').on('hidden.bs.modal', function () {
            cancelarVerificacionQR();
        });
        // CLICK CERRAR MODAL 
        $(".btn-close").on("click", function(){
            $(".form-post")[0].reset();
        });
        // CLICK CANCELAR REQUISICION DESDE LA TABLA
        $("#productionTable").on('click', ".btn-cancelar", function () {
            let dataIdRequisicionCancelar = $(this).data('id-requisicion');
            $('#inputRequisicionCancelar').val(dataIdRequisicionCancelar);
            $("#modalCancelar .modal-body strong").text(dataIdRequisicionCancelar);
        });
        // BOTON DE CANCELAR LA REQUISICION, AJAX A CANCELAR
        $("#btnContinuarCancelar").on('click', function () {
            let idRequisicionCancelar = $('#inputRequisicionCancelar').val();
            $(this).addClass("d-none");
            $.ajax({
                url: '../ajax/cancelar_requisicion.php',
                method: 'POST',
                data: {
                    id_requisicion: idRequisicionCancelar
                },
                success: function(data) {
                    if (data.success) {
                        sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                    } else {
                        sweetAlertResponse("warning", "Advertencia", data.error, "self");
                    }
                },
                error: function () {
                    sweetAlertResponse("error", "Error", "Ocurrio algo inesperado al autorizar", "self");
                    console.error("Error al consultar el estatus de autorización.");
                }
            });
        });






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
                            const tieneJustificacionRemplazo = billet.justificacion_remplazo && billet.justificacion_remplazo.trim() !== '';
                            const tieneJustificacionExtra = billet.justificacion_extra && billet.justificacion_extra.trim() !== '';

                            // Decidir cuál justificación mostrar según la situación solicitada.
                            // Priorizar la justificación que corresponda a la acción actual (situacion).
                            let mostrarJustificacionTipo = null; // 'extra' | 'remplazo' | null
                            let mostrarJustificacionTexto = '';
                            if (billet.situacion === 'remplazo') {
                                if (tieneJustificacionRemplazo) {
                                    mostrarJustificacionTipo = 'remplazo';
                                    mostrarJustificacionTexto = billet.justificacion_remplazo;
                                } else if (tieneJustificacionExtra) {
                                    // Si no hay justificacion de reemplazo, mostrar la de extra (si existe)
                                    mostrarJustificacionTipo = 'extra';
                                    mostrarJustificacionTexto = billet.justificacion_extra;
                                }
                            } else {
                                // situacion != 'remplazo' (normalmente 'extra') -> preferir justificacion_extra
                                if (tieneJustificacionExtra) {
                                    mostrarJustificacionTipo = 'extra';
                                    mostrarJustificacionTexto = billet.justificacion_extra;
                                } else if (tieneJustificacionRemplazo) {
                                    // fallback
                                    mostrarJustificacionTipo = 'remplazo';
                                    mostrarJustificacionTexto = billet.justificacion_remplazo;
                                }
                            }
                            
                            // Determinar texto del botón y tooltip
                            const textoBoton = tieneRemplazo ? "Autorizar reemplazo de barra" : "Autorizar barra extra";
                            const textoBotonRechazo = tieneRemplazo ? "Rechazar reemplazo de barra" : "Rechazar barra extra";
                            const textoSmall = tieneRemplazo ? "Remplazo de barra" : "Barra extra";
                            const iconoClase = tieneRemplazo ? "bi-arrow-left-right" : "bi-plus-circle";
                            
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
            }else{
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


        // NOTIFICACION AL GUARDAR LA REQUISICION
        $("#btnGuardar").on("click", function(){
            $.ajax({
                url: "../ajax/ajax_notificacion.php",
                type: "POST",
                data: { mensaje: "Se ha generado una requisicion"},
                success: function(response) {
                    console.log("Notificacion enviada: ", response);
                },
                error: function(error) {
                    console.error("Error al enviar la notificacion: ", error);
                }
            });
        });
    });