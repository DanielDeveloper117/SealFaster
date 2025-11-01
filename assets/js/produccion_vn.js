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
        
        $("#selectorEstatus").on("change", function(){
            $("#dt-search-0").val("");

            let valueSelector = $(this).val();
            switch(valueSelector){
                case 'Pendiente':
                    $("#dt-search-0").val("Gerencia debe autorizar");
                break;
                case 'Autorizar1':
                    $("#dt-search-0").val("Pendiente");
                    break;
                case 'Autorizar2':
                    $("#dt-search-0").val("Autorizar maquinado");
                break;
                case 'Autorizada1':
                    $("#dt-search-0").val("Dirección debe autorizar");
                break;
                case 'Produccion':
                    $("#dt-search-0").val("En producción");
                break;
                case 'Finalizada':
                    $("#dt-search-0").val("Finalizada");
                break;
                case 'Todo':
                    $("#dt-search-0").val("");
                break;
            }
            // Disparar el evento de búsqueda manualmente
            $("#dt-search-0").trigger("keyup");
        });
        // NOTIFICACION AL GUARDAR
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