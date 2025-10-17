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
    // Función que realiza la validación de las claves de finalizacion por CNC y retorna los datos si son válidos
    function obtenerDatosValidados() {
        const inputIdRequisicion = $("#inputIdRequisicion").val().trim();
        const inputCantidadBarras = $("#inputCantidadBarras").val().trim();
        const inputClave = $("#inputClave").val().trim();
        const inputLotePedimento = $("#inputLotePedimento").val().trim();
        const esExtra = $("#inputExtra").val();
        const inputEntrada = $("#inputEntrada").val().trim();
        // const inputSalida = $("#inputSalida").val().trim();
        // const inputTotalSellos = $("#inputTotalSellos").val().trim();
        // const inputMermaCorte = $("#inputMermaCorte").val().trim();
        // const inputScrapPz = $("#inputScrapPz").val().trim();
        // const inputScrapMm = $("#inputScrapMm").val().trim();

        // Validar campos vacíos
        // if (!inputIdRequisicion || !inputCantidadBarras || !inputClave ||
        //     !inputEntrada || !inputSalida || !inputTotalSellos ||
        //     !inputMermaCorte || !inputScrapPz || !inputScrapMm) {
        //     sweetAlertResponse("warning", "Advertencia", "Debe llenar todos los campos.", "none");
        //     return null;
        // }
        if (!inputIdRequisicion || !inputCantidadBarras || !inputClave || !inputLotePedimento || !inputEntrada) {
            sweetAlertResponse("warning", "Advertencia", "Debe llenar todos los campos.", "none");
            return null;
        }

        // Validar cantidad de barras
        if (!esEnteroValido(inputCantidadBarras)) {
            sweetAlertResponse("warning", "Advertencia", "Cantidad de barras debe ser un entero válido.", "none");
            return null;
        }

        // Validar decimales
        // const camposDecimales = [
        //     { nombre: "Entrada", valor: inputEntrada },
        //     { nombre: "Salida", valor: inputSalida },
        //     { nombre: "Total de sellos", valor: inputTotalSellos },
        //     { nombre: "Merma corte", valor: inputMermaCorte },
        //     { nombre: "Scrap piezas", valor: inputScrapPz },
        //     { nombre: "Scrap mm", valor: inputScrapMm },
        // ];

        const camposDecimales = [
            { nombre: "Entrada", valor: inputEntrada }
        ];

        for (const campo of camposDecimales) {
            if (!esDecimalValido(campo.valor)) {
                sweetAlertResponse("warning", "Advertencia", `El campo "${campo.nombre}" debe ser un número decimal válido (hasta 2 decimales).`, "none");
                return null;
            }
        }

        // Retornar objeto con datos formateados
        // return {
        //     id_requisicion: inputIdRequisicion,
        //     cantidad_barras: parseInt(inputCantidadBarras),
        //     clave: inputClave,
        //     mm_entrega: formatearDecimal(inputEntrada),
        //     mm_usados: formatearDecimal(inputSalida),
        //     total_sellos: formatearDecimal(inputTotalSellos),
        //     merma_corte: formatearDecimal(inputMermaCorte),
        //     scrap_pz: formatearDecimal(inputScrapPz),
        //     scrap_mm: formatearDecimal(inputScrapMm)
        // };
        return {
            id_requisicion: inputIdRequisicion,
            cantidad_barras: parseInt(inputCantidadBarras),
            clave: inputClave,
            lote_pedimento: inputLotePedimento,
            es_extra: esExtra,
            mm_entrega: formatearDecimal(inputEntrada)
        };
    }
    // Traer las claves agregadas de la requisicion solo para verlas, algnas se pueden eliminar
    function ajaxTablaControlAlmacenInventario(eststus){
        const inputIdRequisicion = $("#inputIdRequisicion").val();
        //$dataEstatus = $(this).data('estatus');
        const dataEstatus = eststus;
        $.ajax({
            url: '../ajax/ver_control_almacen.php', 
            type: 'get',
            data: { 
                id_requisicion: inputIdRequisicion
            },
            dataType: 'json',
            success: function(data) {
                $('#miniTableBarrasInventario tbody').empty(); // evita duplicados y posibles desbordes
                // Verifica que la respuesta tenga datos
                if (data.length > 0) {
                    $.each(data, function(index, item) {
                        let esExtra = "";
                        let dNone = "";
                        console.log(dataEstatus);
                        if(item.es_extra === 1){
                            esExtra = " (Barra extra)*";
                        }
                        if(dataEstatus != "Autorizada" && item.es_extra === 0){
                            dNone = "d-none";
                        }
                        $('#miniTableBarrasInventario tbody').append(`
                            <tr>
                                <td>
                                    <button data-id_control="${item.id_control}"  data-es_extra="${item.es_extra}" 
                                        type="button" class="btn btn-danger btn-sm btnEliminarFila ${dNone}">
                                        X
                                    </button>
                                </td>
                                <td>${item.cantidad_barras}</td>
                                <td>${item.clave}<span style="color:#ffc107;">${esExtra}</span></td>
                                <td>${item.lote_pedimento}</td>
                                <td>${item.mm_entrega}</td>
                            </tr>
                        `);
                    });
                } else {
                     $(`#miniTableBarrasInventario tbody`).append('<tr><td colspan="5">No hay barras agregadas aún</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                $(`#miniTableBarrasInventario tbody`).append('<tr><td colspan="5">Error en ajax</td></tr>');
                sweetAlertResponse("error", "Error", "Error al consultar barras: " + error, "none");
            }
    
        });
    }

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
                        sweetAlertResponse("success", "Firma confirmada", `La requisición ha sido firmada correctamente.`, "self");
                    }
                },
                error: function () {
                    console.error("Error al consultar el estatus de requisicion.");
                }
            });
            console.log("Se ha enviado la solicitud para verificar firma con QR.");
        }, 4000);
    }    

    function cancelarVerificacionQR() {
        if (intervaloQR) {
            clearInterval(intervaloQR);
            intervaloQR = null;
            console.log("Verificación QR cancelada.");
        }
    }   

    function verificarClave() {
        let claveValue = $("#inputClave").val();

        if (claveValue !== "") {
            console.log("El usuario ingreso un valor en el inputClave.");
            $.ajax({
                url: '../ajax/ajax_parametros.php',
                type: 'POST',
                data: { clave: claveValue },
                dataType: 'json',
                success: function(data) {
                    if (data.length > 0) {
                        window.CLAVE_VALIDA = true;
                        $("#pInvalida, #pInvalida2").addClass("d-none");
                        $("#pValida").removeClass("d-none");
                        $("#pValida").text(`Clave valida.`);
                    } else {
                        window.CLAVE_VALIDA = false;
                        $("#pInvalida, #pInvalida2").removeClass("d-none");
                        $("#pValida").addClass("d-none");
                    }
                    verificarBtnAgregarBarra();
                },
                error: function() {
                    console.error('Error al realizar la peticion AJAX');
                    $('#pInvalida2').text('Error en ajax validar clave.');
                }
            });
        } else {
            console.log("El usuario dejo el inputClave vacio.");
        }
    }

    function verificarBilletControlAlmacen() {
        let billetValue = $("#inputLotePedimento").val();

        if (billetValue !== "") {
            console.log("El usuario ingreso un valor en el inputLotePedimento.");
            $.ajax({
                url: '../ajax/ajax_existe_billet.php',
                type: 'POST',
                data: { billet: billetValue },
                dataType: 'json',
                success: function(data) {
                    if (data.existe) { 
                        window.LP_VALIDO = true;
                        $("#pLotePedimento").addClass("d-none");
                    } else {
                        window.LP_VALIDO = false;
                        $("#pLotePedimento").removeClass("d-none");
                        $('#pLotePedimento').text('Ese Lote pedimento no existe.');
                    }
                    verificarBtnAgregarBarra();
                },
                error: function(xhr, status, error) {
                    console.error('Error al realizar la petición AJAX:', error);
                    console.error('Respuesta del servidor:', xhr.responseText); // Muestra el error enviado por PHP
                    $("#pLotePedimento").removeClass("d-none");
                    $('#pLotePedimento').text('Error en ajax validar lote pedimento.');
                }
            });
        } else {
            console.log("El usuario dejo el inputClavePost vacio.");
        }
    }
    // Habilitar/deshabilitar el boton de agregar barra si la clave y el LP son validados
    function verificarBtnAgregarBarra(){
        if(window.CLAVE_VALIDA == true && window.LP_VALIDO == true){
            $("#btnAgregarBarra").removeClass("btn-disabled").addClass("btn-general");
        }else{
            $("#btnAgregarBarra").removeClass("btn-general").addClass("btn-disabled");
        }
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
                        // $('#modalEditarMedidas .modal-body').append(`
                        //     <div style="width:100%; margin-bottom:20px;">
                        //         <h5 class="modal-title">Id cotización: <span>${item.id_cotizacion}</span></h5>
                        //         <table class="tabla-medidas table table-bordered border border-2 tabla-billets" data-id_cotizacion="${item.id_cotizacion}">
                        //             <thead>
                        //                 <tr>
                        //                     <th>Tipo de medida</th>
                        //                     <th>DI MM</th>
                        //                     <th>DI INCH</th>
                        //                     <th>DE MM</th>
                        //                     <th>DE INCH</th>
                        //                     <th>A MM</th>
                        //                     <th>A INCH</th>
                        //                 </tr>
                        //             </thead>
                        //             <tbody>
                        //                 <tr>
                        //                     <td>Sello</td>
                        //                     <td><input type="number" class="input-text di_sello" name="di_sello" value="${item.di_sello || ''}" step="0.01" min="0"></td>
                        //                     <td><input type="number" class="input-text di_sello_inch" name="di_sello_inch" value="${item.di_sello_inch || ''}" step="0.0001" min="0"></td>
                        //                     <td><input type="number" class="input-text de_sello" name="de_sello" value="${item.de_sello || ''}" step="0.01" min="0"></td>
                        //                     <td><input type="number" class="input-text de_sello_inch" name="de_sello_inch" value="${item.de_sello_inch || ''}" step="0.0001" min="0"></td>
                        //                     <td><input type="number" class="input-text a_sello" name="a_sello" value="${item.a_sello || ''}" step="0.01" min="0"></td>
                        //                     <td><input type="number" class="input-text a_sello_inch" name="a_sello_inch" value="${item.a_sello_inch || ''}" step="0.0001" min="0"></td>
                        //                 </tr>
                        //                 <tr>
                        //                     <td>Metal</td>
                        //                     <td><input type="number" class="input-text di_sello2" name="di_sello2" value="${item.di_sello2 || ''}" step="0.01" min="0"></td>
                        //                     <td><input type="number" class="input-text di_sello2_inch" name="di_sello_inch2" value="${item.di_sello_inch2 || ''}" step="0.0001" min="0"></td>
                        //                     <td><input type="number" class="input-text de_sello2" name="de_sello2" value="${item.de_sello2 || ''}" step="0.01" min="0"></td>
                        //                     <td><input type="number" class="input-text de_sello2_inch" name="de_sello_inch2" value="${item.de_sello_inch2 || ''}" step="0.0001" min="0"></td>
                        //                     <td><input type="number" class="input-text a_sello2" name="a_sello2" value="${item.a_sello2 || ''}" step="0.01" min="0"></td>
                        //                     <td><input type="number" class="input-text a_sello2_inch" name="a_sello_inch2" value="${item.a_sello_inch2 || ''}" step="0.0001" min="0"></td>
                        //                 </tr>
                        //             </tbody>
                        //         </table>
                        //     </div>
                        // `);
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
                        $('#modalFinalizar tbody').append(`
                            <tr>
                                <input type="hidden" tabindex="-1" name="id_control" value="${item.id_control || ''}">
                                <td>
                                    <input type="checkbox" tabindex="-1" 
                                        name="es_merma"
                                        class="es_merma"  
                                        value="0"
                                        onclick="this.value = this.checked ? 1 : 0"
                                        style="transform: scale(1.5); margin-left: 10px;"
                                    >
                                </td>
                                <td><input type="number" tabindex="-1" class="input-disabled cantidad_barras" value="${item.cantidad_barras || ''}" step="1" min="0"></td>
                                <td><input type="text" tabindex="-1" class="input-disabled clave" value="${item.clave || ''}"></td>
                                <td><input type="text" tabindex="-1" class="input-disabled lote_pedimento" value="${item.lote_pedimento || ''}"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled mm_entrega" name="mm_entrega" value="${item.mm_entrega || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-text mm_usados" name="mm_usados" value="${item.mm_usados || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-text long_t_sellos" name="total_sellos" value="${item.total_sellos || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-text merma_corte" name="merma_corte" value="${item.scrap_mm || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-text scrap_pz" name="scrap_pz" value="${item.scrap_pz || ''}" step="1" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-text scrap_mm" name="scrap_mm" value="${item.scrap_mm || ''}" step="0.01" min="0"></td>
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
                        if(item.es_extra === 1){
                            esExtra = " (Barra extra)*";
                        }
                        if(item.es_merma === 1){
                            esMerma = " (Barra mermada)*";
                        }
                        $('#modalRetorno tbody').append(`
                            <tr>
                                <input type="hidden" tabindex="-1" name="id_requisicion" value="${idRequisicion || ''}">
                                <input type="hidden" tabindex="-1" name="id_control" value="${item.id_control || ''}">
                                <td><input type="number" tabindex="-1" class="input-disabled cantidad_barras" value="${item.cantidad_barras || ''}" step="1" min="0"></td>
                                <td><input type="text" tabindex="-1" class="input-disabled clave" value="${item.clave || ''}"></td>
                                <td><input type="text" tabindex="-1" class="input-disabled lote_pedimento d-flex flex-column" value="${item.lote_pedimento || ''}"><span style="color:#ffc107;">${esExtra}</span><span style="color:#B71C1C;">${esMerma}</span></td>
                                <td><input type="number" tabindex="-1" class="input-disabled mm_entrega" name="mm_entrega" value="${item.mm_entrega || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled mm_usados" name="mm_usados" value="${item.mm_usados || ''}" step="0.01" min="0"></td>
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
    // Escuchamos todos los inputs dentro del modal
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

        if ($inputRelacionado.length === 0) {
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

            if (fallos.length === 0) {
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
    // CLICK AGREGAR CLAVE A CONTROL ALMACEN
    $('#productionTable').on('click', '.btn-control-almacen', function() {
        $dataIdRequisicion = $(this).data('id_requisicion');
        $dataExtra = $(this).data('es_extra');

        if ($dataExtra == "1") {
            $("#inputExtra").prop("checked", true).prop("disabled", true);
            $("#inputExtra").css("cursor", "not-allowed");
            $("#lblInputExtra").text("Barra extra (Esta barra es extra obligatoriamente)");
        } else {
            $("#inputExtra").prop("checked", false).prop("disabled", false);
            $("#inputExtra").css("cursor", "pointer");
            $("#lblInputExtra").text("Barra extra");
        }
        $('#inputIdRequisicion').val($dataIdRequisicion);
    });    
    // CLICK SUBMIT A AGREGAR BARRA
    $("#btnAgregarBarra").on('click', function () {
        const datos = obtenerDatosValidados();

        if (!datos) {
            return; // Salir si hay errores de validación
        }

        // Enviar por AJAX si todo es válido
        $.ajax({
            url: '../ajax/agregar_control_almacen_inv.php',
            type: 'POST',
            data: datos,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "none");
                } else {
                    sweetAlertResponse("warning", "Advertencia", data.message, "none");
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                $('#miniTableBarrasInventario tbody').append('<tr><td colspan="4">Error en ajax</td></tr>');
                sweetAlertResponse("error", "Error", "Error al agregar registro. " + error, "none");
            }
        });
    });
    // CLICK AGREGAR CONTROL ALMACEN DESDE LA TABLA
    $("#productionTable .btn-control-almacen").on("click", function(){
        $dataExtra = $(this).data('es_extra');
        $dataEstatus = $(this).data('estatus');
        console.log($dataExtra);
        console.log($dataEstatus);
        $("#btnTablaControlAlmacenInventario").data('estatus-requi', $dataEstatus);

        if($dataExtra == "1"){
            $("#inputExtra").val("1");
        }
    });
    // CLICK CERRAR MODAL AGREGAR
    $("#modalControlAlmacenInventario .btn-close").on("click", function(){
        $("#formControlAlmacenInventario")[0].reset();
        $("#pInvalida").addClass("d-none");
        $("#pInvalida2").addClass("d-none");
        $("#pInvalida3").addClass("d-none");
        $("#pValida").addClass("d-none");
        window.CLAVE_VALIDA = false;
        window.LP_VALIDO = false;
        verificarBtnAgregarBarra();
    });
    // CLICK VER TABLA DE BARRAS DESDE EL MODAL
    $("#btnTablaControlAlmacenInventario").on("click", function(){
        $('#modalTableControAlmacenInventario').modal('show');
        // AJAX para llenar la tabla de barras
        let eststusRequi = $("#btnTablaControlAlmacenInventario").data("estatus-requi");
        ajaxTablaControlAlmacenInventario(eststusRequi);
    });
    // Delegamos el evento por si las filas se agregan dinámicamente
    $(document).on('click', '.btnEliminarFila', function () {
        const id_control = $(this).data('id_control');

        if (!id_control) {
            sweetAlertResponse("warning", "Advertencia", "ID de barra inválido.", "none");
            return;
        }

        $.ajax({
            url: '../ajax/eliminar_barra_ca.php',
            type: 'POST',
            data: { id_control },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    sweetAlertResponse("success", "Eliminado", response.message, "none");
                    ajaxTablaControlAlmacenInventario(); // Recargar la tabla
                } else {
                    sweetAlertResponse("warning", "Atención", response.message || "No se pudo eliminar.", "none");
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al eliminar la barra:', error);
                sweetAlertResponse("error", "Error", "Error en la solicitud: " + error, "none");
            }
        });
    });
    // CLICK CERRAR MODAL TABLA
    $("#modalTableControAlmacen .btn-close").on("click", function(){
        $('#miniTableBarrasInventario tbody').empty();
    });
    // CLICK A Generar QR para autorizar
    $("#productionTable").on('click', ".btn-cnc-firma", function () {
        let idRequisicion = $(this).data('id-requisicion');
        //let autoriza = $(this).data('autoriza');
        //let qrSrc = `../includes/functions/generar_qr.php?id_requisicion=${encodeURIComponent(idRequisicion)}&t=${encodeURIComponent(autoriza)}`;
        // Mostrar imagen QR en el contenedor del modal
        //$("#ContainerQR").html(`<img src="${qrSrc}" width="250" height="250">`);
        //$("#ContainerQR").css("filter", "blur(3px)");
        // Iniciar la verificación periódica
        //verificarAutorizacionQR(idRequisicion, autoriza);
        $('#modalGuardarOperador').modal('show');
        $("#inputIdRequisicionOperador").val(idRequisicion);
    });
    // CLICK SUBMIT A GUARDAR EL OPERADOR CNC
    $("#btnGuardarOperador").on('click', function () {
        let inputOperadorCNC = $("#inputOperadorCNC").val();
        let inputIdRequisicionOperador = $("#inputIdRequisicionOperador").val();
        $(this).addClass("d-none");
        $.ajax({
            url: '../ajax/guardar_operadorcnc.php',
            type: 'POST',
            data: { 
                operador_cnc: inputOperadorCNC,
                id_requisicion: inputIdRequisicionOperador
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                    $('#modalGuardarOperador').modal('hide');
                    $("#ContainerQR").css("filter", "blur(0px)");
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
    // QUITAR LOS MODALES SI QUITA EL DE GUARDAR OPERADOR
    // $('#modalGuardarOperador').on('hidden.bs.modal', function () {
    //    $('#modalCncFirma').modal('hide');
    // });
    $('#btn-closeOperador').on('click', function () {
       $('#modalCncFirma').modal('hide');
    });
    $('#modalCncFirma').on('hidden.bs.modal', function () {
        cancelarVerificacionQR();
    });
    //CLICK FINALIZAR TAL REQUISICION DESDE LA TABLA
    $("#productionTable").on('click', ".btn-finalizar", function(){
        $dataIdRequisicion=$(this).data('id-requisicion');
        $("#modalFinalizar h5 span").text($dataIdRequisicion);
        ajaxTraerClavesControlAlmacen($dataIdRequisicion);
    });
    //CLICK RETORNAR BARRAS TAL REQUISICION DESDE LA TABLA
    $("#productionTable").on('click', ".btn-claves-retorno", function(){
        $dataIdRequisicion=$(this).data('id-requisicion');
        $("#modalRetorno h5 span").text($dataIdRequisicion);
        ajaxClavesRetorno($dataIdRequisicion);
    });
    // VALIDAR QUE LA CLAVE EXISTA
    $("#inputClave").on("input change", function(){
        verificarClave();
        verificarBtnAgregarBarra();
    });
    // VALIDAR QUE el lote pedimento exista
    $("#inputLotePedimento").on("input change", function(){
        verificarBilletControlAlmacen();
        verificarBtnAgregarBarra();
    });
    // ENVIAR EL FORMULARIO DE FINALIZAR LA REQUISICION
    $("#finalizarRequisicion").on('click', function () {
        let valido = true;

        // Validar solo inputs que el usuario puede editar (excluimos .input-disabled)
        $('#modalFinalizar tbody input:not(.input-disabled)').each(function () {
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
        $('#modalFinalizar tbody tr').each(function () {
            let id_control = $(this).find('input[name="id_control"]').val();

            // Saltar fila si id_control no existe o está vacío
            if (!id_control) return;

            let fila = {
                id_control: id_control,
                es_merma: $(this).find('.es_merma').val() || 0,
                mm_usados: $(this).find('.mm_usados').val() || 0,
                total_sellos: $(this).find('.long_t_sellos').val() || 0,
                merma_corte: $(this).find('.merma_corte').val() || 0,
                scrap_pz: $(this).find('.scrap_pz').val() || 0,
                scrap_mm: $(this).find('.scrap_mm').val() || 0
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
                    //sweetAlertResponse("success", "Éxito", "La requisición fue finalizada correctamente.", "self");
                    sweetAlertResponse("success", "Éxito", resp.message, "self");
                    $('#modalFinalizar').modal('hide');
                } else {
                    sweetAlertResponse("error", "Error", resp.error || "Error desconocido.", "self");
                }
            },
            error: function (xhr, status, error) {
                sweetAlertResponse("error", "Error", "No se pudo finalizar: " + error, "self");
            }
        });
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
        if (datos.length === 0) {
            sweetAlertResponse("warning", "Sin registros", "No hay registros por enviar. Se necesita mínimo un registro con Lote Pedimento.", "none");
            return;
        }

        $(this).addClass("d-none");

        // Enviar al servidor
        $.ajax({
            url: '../ajax/retornar_barras.php',
            type: 'post',
            data: { registros: JSON.stringify(datos) },
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
    // NOTIFICACIOIN AL GUARDAR CLAVE
    $("#formControlAlmacenInventario").on("submit", function(){
        let inputIdRequisicion = $("#inputIdRequisicion").val();
        $.ajax({
            url: "../ajax/ajax_notificacion.php",
            type: "POST",
            data: { mensaje: "Inventarios ha agregado una barra a "+inputIdRequisicion },
            success: function(response) {
                console.log("Notificación enviada: ", response);
            },
            error: function(error) {
                console.error("Error al enviar la notificación: ", error);
            }
        });
    });
    // DAR SALIDA A LOS BILLETS QUE AGREGO INVENTARIOS
    $("#productionTable").on('click', ".btn-salida-barras", function(){
        $dataIdRequisicionSalida=$(this).data('id-requisicion');
        //$("#modalFinalizar h5 span").text($dataIdRequisicion);
        $("#inputRequisicionDarSalida").val($dataIdRequisicionSalida);
    });
    // ACCION DE ENTREGAR LAS BARRAS A CNC PARA QUE COMIENCE EL MAQUINADO
    $("#btnDarSalidaBillets").on('click', function () {
        let inputIdRequisicionSalida = $("#inputRequisicionDarSalida").val();
        $(this).addClass("d-none");
        // Enviar al servidor
        $.ajax({
            url: '../ajax/entregar_barras.php',
            type: 'POST',
            data: { 
                id_requisicion: inputIdRequisicionSalida
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
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


    $("#selectorEstatus").on("change", function(){
        $("#dt-search-0").val("");

        let valueSelector = $(this).val();
        switch(valueSelector){
            case 'Pendiente':
                $("#dt-search-0").val("Comenzar maquinado");
            break;
            case 'Pendiente2':
                $("#dt-search-0").val("En revisión");
            break;
            case 'Maquinando':
                $("#dt-search-0").val("Finalizar");
            break;
            case 'Maquinando2':
                $("#dt-search-0").val("finalizarla");
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
});