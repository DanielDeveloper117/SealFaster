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
    // Función que realiza la validación de campos de agregar barra
    function obtenerDatosValidados() {
        const inputIdRequisicion = $("#inputIdRequisicion").val().trim();
        const inputCantidadBarras = $("#inputCantidadBarras").val().trim();
        const inputMaterial = $("#inputMaterial").val().trim();
        const inputClave = $("#inputClave").val().trim();
        const inputLotePedimento = $("#inputLotePedimento").val().trim();
        const inputMedida = $("#inputMedida").val();
        const esExtra = $("#inputExtra").val();
        const inputEntrada = $("#inputEntrada").val().trim();

        if (!inputIdRequisicion || !inputCantidadBarras || !inputClave || !inputLotePedimento || !inputEntrada) {
            sweetAlertResponse("warning", "Advertencia", "Debe llenar todos los campos.", "none");
            return null;
        }

        // Validar cantidad de barras
        if (!esEnteroValido(inputCantidadBarras)) {
            sweetAlertResponse("warning", "Advertencia", "Cantidad de barras debe ser un entero válido.", "none");
            return null;
        }

        const camposDecimales = [
            { nombre: "Entrada", valor: inputEntrada }
        ];

        for (const campo of camposDecimales) {
            if (!esDecimalValido(campo.valor)) {
                sweetAlertResponse("warning", "Advertencia", `El campo "${campo.nombre}" debe ser un número decimal válido (hasta 2 decimales).`, "none");
                return null;
            }
        }

        return {
            id_requisicion: inputIdRequisicion,
            cantidad_barras: parseInt(inputCantidadBarras),
            material: inputMaterial,
            clave: inputClave,
            lote_pedimento: inputLotePedimento,
            medida: inputMedida,
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
                                <td>${item.material}</td>
                                <td>${item.lote_pedimento}</td>
                                <td>${item.clave}<span style="color:#ffc107;">${esExtra}</span></td>
                                <td>${item.medida}</td>
                                <td>${item.mm_entrega}</td>
                            </tr>
                        `);
                    });
                } else {
                     $(`#miniTableBarrasInventario tbody`).append('<tr><td colspan="6">No hay barras agregadas aún</td></tr>');
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
                url: '../ajax/info_lote_pedimento.php',
                type: 'POST',
                data: { billet: billetValue },
                dataType: 'json',
                success: function(data) {
                    if (data.success) { 
                        window.LP_VALIDO = true;
                        $("#pLotePedimento").removeClass("d-none");
                        $("#pLotePedimento").removeClass("p-invalida");
                        $("#pLotePedimento").addClass("p-valida");
                        $('#pLotePedimento').text(`${data.billetResult.material} - ${data.billetResult.Clave} (${data.billetResult.Medida})`);
                        $("#inputClave").val(data.billetResult.Clave);
                        $("#inputMaterial").val(data.billetResult.material);
                        $("#inputMedida").val(data.billetResult.Medida);
                    } else {
                        window.LP_VALIDO = false;
                        $("#pLotePedimento").removeClass("d-none");
                        $("#pLotePedimento").removeClass("p-valida");
                        $("#pLotePedimento").addClass("p-invalida");
                        $('#pLotePedimento').text('Lote pedimento no encontrado.');
                        $("#inputClave").val("");
                        $("#inputMaterial").val("");
                        $("#inputMedida").val("");
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
        //if(window.CLAVE_VALIDA == true && window.LP_VALIDO == true){
        if(window.LP_VALIDO == true){
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
                                
                                <td><input type="text" tabindex="-1" class="input-disabled material" value="${item.material || ''}"></td>
                                <td><input type="text" tabindex="-1" class="input-disabled clave" value="${item.clave || ''}"></td>
                                <td><input type="text" tabindex="-1" class="input-disabled lote_pedimento d-flex flex-column" value="${item.lote_pedimento || ''}"><span style="color:#ffc107;">${esExtra}</span><span style="color:#B71C1C;">${esMerma}</span></td>
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

    // Traer los lotes pedimento de la requisicion para que CNC llene los campos para finalizar la requisicion
    function ajaxTraerClavesControlAlmacen(idRequisicion){
        $.ajax({
            url: '../ajax/union_registros_billet.php',
            type: 'get',
            data: { 
                id_requisicion: idRequisicion
            },
            dataType: 'json',
            success: function(data) {
                $('#modalFinalizar tbody').empty();

                if (data.success && data.barras.length > 0) {
                    $.each(data.barras, function(index, barra) {
                        // Determinar qué cotización usar (primera por defecto, o lógica específica)
                        let cotizacionSeleccionada = barra.cotizaciones.length > 0 ? barra.cotizaciones[0] : null;
                        
                        // Si hay múltiples cotizaciones, podrías agregar lógica para seleccionar la correcta
                        let tieneMultiplesCotizaciones = barra.cotizaciones.length > 1;
                        
                        // Calcular desbaste según material
                        let material = barra.material || '';
                        let desbasteMaterial = calcularDesbaste(material);
                        
                        // Calcular campos iniciales
                        let pzTeoricas = cotizacionSeleccionada ? (cotizacionSeleccionada.pz_teoricas || 0) : 0;
                        let alturaPz = cotizacionSeleccionada ? (parseFloat(cotizacionSeleccionada.a_sello) || 0) : 0;
                        let mmTeoricos = (pzTeoricas * (alturaPz + desbasteMaterial)).toFixed(2);
                        let longTSellos = (pzTeoricas * alturaPz).toFixed(2);

                        $('#modalFinalizar tbody').append(`
                            <tr class="data-row" data-id-control="${barra.id_control}" data-lote="${barra.lote_pedimento}" data-desbaste="${desbasteMaterial}">
                                <input type="hidden" tabindex="-1" name="id_control" value="${barra.id_control || ''}">
                                <input type="hidden" tabindex="-1" name="es_merma" class="es_merma" value="${barra.es_merma || '0'}">
                                <input type="hidden" tabindex="-1" name="mm_teoricos" class="mm_teoricos" value="${barra.mm_teoricos || mmTeoricos}">
                                <input type="hidden" tabindex="-1" name="mm_merma_real" class="mm_merma_real" value="${barra.mm_merma_real || ''}">
                                <input type="hidden" tabindex="-1" name="id_cotizacion" class="id_cotizacion" value="${cotizacionSeleccionada ? cotizacionSeleccionada.id_cotizacion : ''}">
                                <input type="hidden" tabindex="-1" name="id_estimacion" class="id_estimacion" value="${cotizacionSeleccionada ? cotizacionSeleccionada.id_estimacion : ''}">

                                <!-- Perfil Sello: Input editable si no hay cotización -->
                                <td>
                                    ${cotizacionSeleccionada ? 
                                        `<input type="text" class="input-disabled perfil_sello" name="perfil_sello" value="${cotizacionSeleccionada.perfil_sello}" placeholder="Ingrese perfil sello" required>` : 
                                        `<input type="text" class="input-text perfil_sello" name="perfil_sello" value="${barra.perfil_sello || ''}" placeholder="Ingrese perfil sello" required>`
                                    }
                                </td>
                                
                                <td><p class="input-disabled material">${barra.material || 'No se encontró material'}</p></td>
                                <td>
                                    <p class="input-disabled lote_pedimento">
                                        ${barra.lote_pedimento || ''}
                                        ${tieneMultiplesCotizaciones ? ' <span class="badge bg-warning" title="En múltiples cotizaciones">⚠</span>' : ''}
                                    </p>
                                </td>
                                <td><p class="input-disabled medida">${barra.medida || '?/?'}</p></td>
                                <td><p class="input-disabled mm_entrega">${barra.mm_entrega || '0'}</p></td>
                                
                                <!-- Piezas Teóricas: Input editable si no hay cotización -->
                                <td>
                                    ${cotizacionSeleccionada ? 
                                        `<input type="number" class="input-disabled pz_teoricas" name="pz_teoricas" value="${pzTeoricas}" step="1" min="0" placeholder="Pz teóricas" required>` : 
                                        `<input type="number" class="input-text pz_teoricas" name="pz_teoricas" value="${barra.pz_teoricas || ''}" step="1" min="0" placeholder="Pz teóricas" required>`
                                    }
                                </td>
                                
                                <td><input type="number" class="input-text pz_maquinadas" name="pz_maquinadas" value="${barra.pz_maquinadas || ''}" step="1" min="0" required></td>
                                <td>
                                    <input type="number" 
                                        ${cotizacionSeleccionada ? 'tabindex="-1"' : ''}
                                        class="${cotizacionSeleccionada ? 'input-disabled' : 'input-text'} altura_pz" 
                                        name="altura_pz" 
                                        value="${barra.altura_pz || alturaPz}" 
                                        step="0.01" 
                                        min="0" 
                                        ${cotizacionSeleccionada ? '' : 'required'}>
                                </td>                            
                                <td><input type="number" class="input-text mm_usados" name="mm_usados" value="${barra.mm_usados || ''}" step="0.01" min="0" required></td>
                                <td><input type="number" tabindex="-1" class="input-disabled long_t_sellos" name="total_sellos" value="${barra.total_sellos || longTSellos}" step="0.01" min="0" required></td>
                                <td><input type="number" tabindex="-1" class="input-disabled merma_corte" name="merma_corte" value="${barra.merma_corte || ''}" step="0.01" min="0" required></td>
                                <td><input type="number" tabindex="-1" class="input-disabled scrap_pz" name="scrap_pz" value="${barra.scrap_pz || ''}" step="1" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled scrap_mm" name="scrap_mm" value="${barra.scrap_mm || ''}" step="0.01" min="0"></td>
                                <td><input type="number" tabindex="-1" class="input-disabled mm_total_usados" name="mm_total_usados" value="${barra.mm_total_usados || ''}" step="0.01" min="0"></td>
                            </tr>
                            <tr class="row-justificar ${barra.justificacion_merma ? '' : 'd-none'}">
                                <td colspan="14">
                                    <div class="d-flex flex-column justify-content-start align-items-start">
                                        <label class="mb-2 text-danger">Justificación de merma requerida para <strong>${barra.lote_pedimento || ''}</strong>:</label> 
                                        <input type="text" class="input-text justificacion_merma" name="justificacion_merma" value="${barra.justificacion_merma || ''}" required>
                                        <small class="text-muted">La merma real supera la merma teórica. Justifique el exceso.</small>
                                    </div>
                                </td>
                            </tr>
                        `);

                        // Agregar event listeners para los cálculos
                        agregarEventListenersCalculos(barra.id_control);
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
        row.find('.pz_maquinadas, .mm_usados').on('input', function() {
            calcularFila(row, lote);
        });
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
        if (mmMermaReal > 0 && mmUsados > mmTeoricos) {
            justificarRow.removeClass('d-none');
            justificarRow.find('.text-muted').text(`Debe justificar por que hay una merma de ${mmMermaReal.toFixed(2)}mm. Lote pedimento: ${lote}`);
        } else {
            justificarRow.addClass('d-none');
            justificarRow.find('.justificacion_merma').val('');
        }
        
        // 11. Si mm_usados = mm_entrega, es_merma = 1
        const esMerma = (mmUsados === mmEntrega) ? 1 : 0;
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
                                        <strong class="text-danger">Justificación de merma:</strong>
                                        <span>${barra.justificacion_merma}</span>
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
        $("#pLotePedimento").addClass("d-none");

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
        $("#pLotePedimento").addClass("d-none");
        console.log($dataExtra);
        console.log($dataEstatus);
        $("#btnTablaControlAlmacenInventario").data('estatus-requi', $dataEstatus);

        if($dataExtra == "1"){
            $("#inputExtra").prop("checked", true).prop("disabled", true);
            $("#inputExtra").val("1");
        }else{
            $("#inputExtra").prop("checked", false).prop("disabled", false);
            $("#inputExtra").val("0");
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
                    let eststusRequi = $("#btnTablaControlAlmacenInventario").data("estatus-requi");
                    ajaxTablaControlAlmacenInventario(eststusRequi);// Recargar la tabla
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
    $("#productionTable").on('click', ".btn-iniciar-maquinado", function () {
        let idRequisicion = $(this).data('id-requisicion');
        //let autoriza = $(this).data('autoriza');
        //let qrSrc = `../includes/functions/generar_qr.php?id_requisicion=${encodeURIComponent(idRequisicion)}&t=${encodeURIComponent(autoriza)}`;
        // Mostrar imagen QR en el contenedor del modal
        //$("#ContainerQR").html(`<img src="${qrSrc}" width="250" height="250">`);
        //$("#ContainerQR").css("filter", "blur(3px)");
        // Iniciar la verificación periódica
        //verificarAutorizacionQR(idRequisicion, autoriza);
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

        let maquinaOperador;
        if(!inputMaquina){
            sweetAlertResponse("warning", "Faltan datos", "Seleccione una máquina CNC", "none");
            return;
        }
        // if(inputOperadorCNC){
        //     maquinaOperador = inputMaquina + ' - ' + inputOperadorCNC;
        // }else{
        //     maquinaOperador = inputMaquina;
        // }
        //maquinaOperador = inputMaquina;
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

    // También actualizar la función de cálculos para guardar automáticamente después de cierto tiempo
    // let autoSaveTimeout;
    // function programarAutoSave() {
    //     clearTimeout(autoSaveTimeout);
    //     autoSaveTimeout = setTimeout(() => {
    //         if ($('#modalFinalizar').is(':visible')) {
    //             $('#saveChangesFinalizar').click();
    //         }
    //     }, 30000); // Auto-guardar después de 30 segundos de inactividad
    // }

    // // Agregar event listeners para el auto-guardado
    // function agregarEventListenersCalculos(idControl) {
    //     const row = $(`tr[data-id-control="${idControl}"]`);
        
    //     row.find('input, textarea').on('input', function() {
    //         programarAutoSave();
    //         calcularFila(row);
    //     });
    // }
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
            
            if (justificacion === '') {
                valido = false;
                problemas.push(`Debe justificar la merma. Lote pedimento: ${lote}`);
                $justificarRow.find('.justificacion_merma').addClass('is-invalid');
            } else if (justificacion.length < 10) {
                valido = false;
                problemas.push(`La justificación de merma debe tener al menos 10 caracteres. Lote pedimento: ${lote} `);
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
                justificacion_merma: $(this).next('.row-justificar').find('.justificacion_merma').val() || ''
            };

            datos.push(fila);
        });

        // Validar que haya registros reales
        if (datos.length === 0) {
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