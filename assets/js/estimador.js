$(document).ready(function() {
    $(`label`).css('pointer-events', 'none');
    //alert("GET: " + window.perfilSello);
// //////////////////////////////////// @DECLARACION DE VARIABLES
    window.MEDIDA_AGARRE_MAQUINA = 6.00;
    window.FAMILIA_PERFIL = "";
    window.CANTIDAD_MATERIALES=0;
    window.CON_LABIO_DI = 0;
    window.CON_LABIO_DE = 0;
    window.tieneResorte = "0";
    window.esWiper = "0";
    window.conEscalon = "0";
    window.esWisperEspecial = "0";

    window.DI_DESPERDICIO_DEFAULT = 3.00;
    window.DE_DESPERDICIO_DEFAULT = 3.00;

    window.multiploResorte = 1.00;
    window.billetsSeleccionados = [];

    for (let i = 1; i <= 5; i++) {
        window[`billetsSeleccionados_m${i}`] = [];
        window[`BILLETS_SELECCIONADOS_STRING_m${i}`] = [];
    }

    window.P_LABIO_DI = 0.000;
    window.P_LABIO_DE = 0.000;

    window.MEDIDA_LABIO_DI = 0.00;
    window.MEDIDA_LABIO_DE = 0.00;

    window.DI_CLIENTE = 0.00;
    window.DE_CLIENTE = 0.00;

    window.porcentajeA_escalon = 1.00;
    window.porcentajeA_caja = 1.00;
    window.porcentajeA_h3 = 1.00;

    window.porcentajeA_m1 = 1.00;
    window.porcentajeA_m2 = 1.00;
    window.porcentajeA_m3 = 1.00;
    window.porcentajeA_m4 = 1.00;
    window.porcentajeA_m5 = 1.00;

    window.porcentajeDI_m1 = 1.00;
    window.porcentajeDI_m2 = 1.00;
    window.porcentajeDI_m3 = 1.00;
    window.porcentajeDI_m4 = 1.00;
    window.porcentajeDI_m5 = 1.00;

    window.porcentajeDE_m1 = 1.00;
    window.porcentajeDE_m2 = 1.00;
    window.porcentajeDE_m3 = 1.00;
    window.porcentajeDE_m4 = 1.00;
    window.porcentajeDE_m5 = 1.00;

    let boolClienteSeleccionado = false;
    let boolMedidaSeleccionadaDI = false;
    let boolMedidaSeleccionadaDE = false;
    let boolMedidaSeleccionadaH = false;

    window.A_CAJA = 0.00;

    window.TIPO_MEDIDA_SELLO = "Metal";

    window.TIPO_MEDIDA_DI = "Metal";
    window.TIPO_MEDIDA_DE = "Metal";
    window.TIPO_MEDIDA_H = "Metal";

    let MaterialesCompletados=0;
    window.CANTIDAD_MATERIALES=0;

    window.DIMENSIONES_VALIDAS = false;
// /////////////////////////////////////////// @FUNCIONES JAVASCRIPT
    function habilitarBoton(elemento) {
        $(elemento).attr("disabled", false).removeClass("btn-disabled").addClass("btn-general");
    }
    function disablarBoton(elemento) {
        $(elemento).attr("disabled", true).removeClass("btn-general").addClass("btn-disabled");
    }
    function habilitarInput(elemento) {
        $(elemento).attr("disabled", false).removeClass("input-readonly").addClass("form-control").attr("placeholder", "");
    }
    function disablarInput(elemento) {
        $(elemento).attr("disabled", true).removeClass("form-control").addClass("input-readonly");
    } 

    // funcion para asignar el id de la cotizacion    
    function idRandom(){
        const numeroAleatorio = Math.floor(10000000 + Math.random() * 90000000);
        return numeroAleatorio;
    }

    window.unirBilletsSeleccionados = function () {
        window.billetsSeleccionados = [
            ...window.billetsSeleccionados_m1,
            ...window.billetsSeleccionados_m2,
            ...window.billetsSeleccionados_m3,
            ...window.billetsSeleccionados_m4,
            ...window.billetsSeleccionados_m5
        ];
    };

    window.unirStringBillets = function () {
        window.BILLETS_SELECCIONADOS_STRING = [
            ...window.BILLETS_SELECCIONADOS_STRING_m1,
            ...window.BILLETS_SELECCIONADOS_STRING_m2,
            ...window.BILLETS_SELECCIONADOS_STRING_m3,
            ...window.BILLETS_SELECCIONADOS_STRING_m4,
            ...window.BILLETS_SELECCIONADOS_STRING_m5
        ];
        console.log("STRING DE BILLETS: ", window.BILLETS_SELECCIONADOS_STRING);
    };

    function resetear_materiales_completados(){
        for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
            $(`#btnNoListo_m${i}`).trigger("click");
            $(`#btnAtras_m${i}`).trigger("click");
        }
        MaterialesCompletados=0;
        habilitarCotizacion(0);
    }

    function escaparCaracteresNumericos_cliente() {
        let valorFiltrado = $(this).val();
        // Validar si el valor es un número dentro del rango permitido
        if (isNaN(parseFloat(valorFiltrado)) || parseFloat(valorFiltrado) > 9999.99 || parseFloat(valorFiltrado) < 0 || valorFiltrado.trim() == "") {
            if (valorFiltrado.trim() == "") {
            } else {
                $(this).val('0');
            }
        }
    }

    // FUNCION DE CALCULO DE DIMENSIONES DE MATERIALES PARA 
    function autoCalculoDimensiones(ClienteDI, ClienteDE, ClienteA){

        let anchoSello = ClienteDE - ClienteDI;
        console.log("ancho = ", anchoSello);
        //console.log("Tipo de medida: ", window.TIPO_MEDIDA_SELLO);
        console.log("Porcentaje labio DI = ", window.P_LABIO_DI);
        console.log("Porcentaje labio DE = ", window.P_LABIO_DE); 

        for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
            // AUTO CALCULO DI - DIAMETRO INTERIOR
            let porcentajeDI = window["porcentajeDI_m" + i];
            let pmmDI = anchoSello * porcentajeDI;
            let autoDI = 0.00;
            console.log("-- MATERIAL ", i);
        
            console.log(`porcentaje en mm DI = `, pmmDI);
            //porcentaje es 1.00 no cambia su valor, es decir es tiene el 100% del DI
            if (porcentajeDI == 1.000) {
                autoDI = ClienteDI;
            } else {
                // caso especial cuando el Diametro del material sobresale de la medida sello
                if(window.perfilSello.includes("K22") || window.perfilSello.includes("K32") || window.perfilSello.includes("S25") || window.perfilSello.includes("S32")){
                //if(window.perfilSello.includes("K22") || window.perfilSello.includes("S25") || window.perfilSello.includes("S32")){
                    autoDI = ClienteDI - pmmDI;
                }else{
                    autoDI = ClienteDI + pmmDI;
                }
                if(window.perfilSello.includes("A25") || window.perfilSello.includes("A27")){
                    if(window.TIPO_MEDIDA_SELLO ==="Sello" && window.CON_LABIO_DI != i){
                        autoDI = ClienteDI + pmmDI;
                    }
                    if(window.perfilSello.includes("A26") && window.TIPO_MEDIDA_SELLO ==="Metal" && window.CON_LABIO_DI != i){
                        autoDI = ClienteDI - pmmDI;
                    }
                } 
                if(window.perfilSello.includes("A26")){
                    if(window.TIPO_MEDIDA_SELLO ==="Metal" && window.CON_LABIO_DI != i){
                        autoDI = autoDI - pmmDI;
                    }
                }
                if(window.perfilSello.includes("R01-FP")){
                    if(window.TIPO_MEDIDA_SELLO ==="Metal" && window.CON_LABIO_DI != i){
                        autoDI = ClienteDI + pmmDI;
                        console.log("auto es ", autoDI);
                    }
                }

            }
            // los que no tienen labio % es 0.000, es sumar 0
            window.MEDIDA_LABIO_DI = (anchoSello)*window.P_LABIO_DI;
            if(window.perfilSello.includes("R01-FP")){
                if(window.TIPO_MEDIDA_SELLO ==="Metal" && window.CON_LABIO_DI != i){
                    window.MEDIDA_LABIO_DI = 0.00;
                }
            }
            // si el tipo de medida del perfil es Sello y no es el material con labio
            if(window.TIPO_MEDIDA_SELLO ==="Sello" && window.CON_LABIO_DI != i){
                window.MEDIDA_LABIO_DI = 0.00;
                autoDI = autoDI - window.MEDIDA_LABIO_DI;
                console.log("No se le agrego labio DI al material ", i);
            }
            if(window.TIPO_MEDIDA_SELLO ==="Metal" && window.CON_LABIO_DI != i){
                autoDI = autoDI + window.MEDIDA_LABIO_DI;
                console.log("Se le quito la medida del labio DI al material ", i);
            }
            if(window.CON_LABIO_DI == i && window.TIPO_MEDIDA_SELLO ==="Sello"){
                autoDI = autoDI - window.MEDIDA_LABIO_DI;
                console.log("Se le agrego la medida del labio DI al material ", i);
            }
            if(window.CON_LABIO_DI == i && window.TIPO_MEDIDA_SELLO ==="Metal"){
                window.MEDIDA_LABIO_DI = 0.00;
                autoDI = autoDI + window.MEDIDA_LABIO_DI;
                console.log("No se le agrego labio DI al material ", i);
            }
            // PROBLEMATICOS, donde ya mejor es la misma medida del cliente
            // if (window.perfilSello.includes("K32")) {
            //     autoDI = ClienteDI;
            // }
            console.log("Medida labio DI = ", window.MEDIDA_LABIO_DI);
            $(`#diametro_interior_mm_m${i}`).val(autoDI.toFixed(2)).trigger("input");
            console.log(`Auto calculo DI M${i} = `, autoDI);
        
            // AUTO CALCULO DE - DIAMETRO EXTERIOR 
            let porcentajeDE = window["porcentajeDE_m" + i];
            let pmmDE = anchoSello * porcentajeDE;
            let autoDE = 0.00;
            console.log(`porcentaje en mm DE = `, pmmDE);

            if (porcentajeDE == 1.000) {
                autoDE = ClienteDE;
            } else {
                if(window.perfilSello.includes("S22") || window.perfilSello.includes("K32") || window.perfilSello.includes("S25") || window.perfilSello.includes("S32")){
                    autoDE = ClienteDE + pmmDE;
                }else{
                    autoDE = ClienteDE - pmmDE;
                }
                if(window.perfilSello.includes("A25") || window.perfilSello.includes("A27") || window.perfilSello.includes("R01-FP")){
                    if(window.TIPO_MEDIDA_SELLO ==="Sello" && window.CON_LABIO_DI != i){
                        autoDE = ClienteDE + pmmDE;
                    }
                    if(window.TIPO_MEDIDA_SELLO ==="Sello" && window.CON_LABIO_DI == i){
                        autoDE = ClienteDE;
                    }
                    if(window.TIPO_MEDIDA_SELLO ==="Metal" && window.CON_LABIO_DI != i){
                        autoDE = ClienteDE;
                    }
                    if(window.TIPO_MEDIDA_SELLO ==="Metal" && window.CON_LABIO_DI == i){
                        autoDE = ClienteDE - pmmDE;
                    }
                } 
                if(window.perfilSello.includes("A26")){
                    if(window.TIPO_MEDIDA_SELLO ==="Sello" && window.CON_LABIO_DI != i){
                        autoDE = ClienteDE + pmmDE;
                    }
                    if(window.TIPO_MEDIDA_SELLO ==="Sello" && window.CON_LABIO_DI == i){
                        autoDE = ClienteDE;
                    }
                    if(window.TIPO_MEDIDA_SELLO ==="Metal" && window.CON_LABIO_DI != i){
                        autoDE = ClienteDE;
                    }
                    if(window.TIPO_MEDIDA_SELLO ==="Metal" && window.CON_LABIO_DI == i){
                        autoDE = ClienteDE - pmmDE;
                    }
                } 
            }
            window.MEDIDA_LABIO_DE = (anchoSello)*window.P_LABIO_DE;
            // si el tipo de medida del perfil es Sello y no es el material con labio
            if(window.TIPO_MEDIDA_SELLO ==="Sello" && window.CON_LABIO_DE != i){
                window.MEDIDA_LABIO_DE = 0.00;
                autoDE = autoDE + window.MEDIDA_LABIO_DE;
                console.log("No se le agrego labio DE al material ", i);
                console.log("auto de", autoDE);
            }
            if(window.TIPO_MEDIDA_SELLO ==="Metal" && window.CON_LABIO_DE != i){
                autoDE = autoDE - window.MEDIDA_LABIO_DE;
                console.log("Se le quito la medida del labio DE al material ", i);
            }
            if(window.CON_LABIO_DE == i && window.TIPO_MEDIDA_SELLO ==="Sello"){
                autoDE = autoDE + window.MEDIDA_LABIO_DE;
                console.log("Se le agrego la medida del labio DE al material ", i);
            }
            if(window.CON_LABIO_DE == i && window.TIPO_MEDIDA_SELLO ==="Metal"){
                window.MEDIDA_LABIO_DE = 0.00;
                autoDE = autoDE - window.MEDIDA_LABIO_DE;
                console.log("No se le agrego labio DE al material ", i);
            }
            // PROBLEMATICOS, donde ya mejor es la misma medida del cliente
            // if (window.perfilSello.includes("K32")) {
            //     autoDE = ClienteDE;
            // }
            
            console.log("Medida labio DE = ", window.MEDIDA_LABIO_DE);  
            $(`#diametro_exterior_mm_m${i}`).val(autoDE.toFixed(2)).trigger("input");
            console.log(`Auto calculo DE ${i} = `, autoDE);
        
            // CALCULO ALTURA
            let autoAltura = ClienteA * window["porcentajeA_m" + i];
        
            $(`#altura_mm_m${i}`).val(autoAltura.toFixed(2)).trigger("input");
            console.log(`Auto calculo H m${i} = `, autoAltura);
        }
        // CALCULO ALTURA CAJA
        let alturaMaterialWisper = parseFloat($(`#altura_mm_m${window.esWiper}`).val()) || 0.00; 
        if(window.perfilSello.includes("A03") || window.perfilSello.includes("A06")){
            alturaMaterialWisper = parseFloat($(`#altura_mm_cliente`).val()) || 0.00; 
        }
        if(window.esWiper !== "0"){
            let autoAlturaCaja = alturaMaterialWisper * window.porcentajeA_caja;
            //$(`#inputAlturaCaja_m${window.esWiper}`).val(autoAlturaCaja.toFixed(2));

            let alturaCajaMmToInch = autoAlturaCaja / 25.4;
            //$(`#inputAlturaCajaInch_m${window.esWiper}`).val(alturaCajaMmToInch.toFixed(4));

            //$(`#inputAlturaCaja`).val(autoAlturaCaja.toFixed(2));
            //$(`#inputAlturaCajaInch`).val(alturaCajaMmToInch.toFixed(4));
        }else{
            $(`#inputAlturaCaja`).val(0.00);
            $(`#inputAlturaCajaInch`).val(0.0000);
        }
        // CALCULO ALTURA ESCALON
        if(window.conEscalon !== "0" && window.esWisperEspecial === "0"){
            let autoAlturaEscalon = alturaMaterialWisper * window.porcentajeA_escalon;
            //$(`#inputAlturaEscalon_m${window.conEscalon}`).val(autoAlturaEscalon.toFixed(2));

            let alturaEscalonMmToInch = autoAlturaEscalon / 25.4;
            //$(`#inputAlturaEscalonInch_m${window.conEscalon}`).val(alturaEscalonMmToInch.toFixed(4));

            //$(`#inputAlturaEscalon`).val(autoAlturaEscalon.toFixed(2));
            //$(`#inputAlturaEscalonInch`).val(alturaEscalonMmToInch.toFixed(4));
        }else{
            $(`#inputAlturaEscalon`).val(0.00);
            $(`#inputAlturaEscalonInch`).val(0.0000);
        }
        // CALCULO ALTURA H2 Y H3 DE WISPER ESPECIAL A12
        if(window.esWisperEspecial !== "0"){
            let autoAlturaH2 = alturaMaterialWisper * window.porcentajeA_h2;
            //$(`#inputAlturaH2_m${window.esWisperEspecial}`).val(autoAlturaH2.toFixed(2));
    
            let alturaH2MmToInch = autoAlturaH2 / 25.4;
            //$(`#inputAlturaH2Inch_m${window.esWisperEspecial}`).val(alturaH2MmToInch.toFixed(4));

            let autoAlturaH3 = alturaMaterialWisper * window.porcentajeA_h3;
            //$(`#inputAlturaH3_m${window.esWisperEspecial}`).val(autoAlturaH3.toFixed(2));
    
            let alturaH3MmToInch = autoAlturaH3 / 25.4;
            //$(`#inputAlturaH3Inch_m${window.esWisperEspecial}`).val(alturaH3MmToInch.toFixed(4));

            //$(`#inputAlturaH2`).val(autoAlturaH2.toFixed(2));
            //$(`#inputAlturaH2Inch`).val(alturaH2MmToInch.toFixed(4));
            //$(`#inputAlturaH3`).val(autoAlturaH3.toFixed(2));
           // $(`#inputAlturaH3Inch`).val(alturaH3MmToInch.toFixed(4));
        }else{
            $(`#inputAlturaH2`).val(0.00);
            $(`#inputAlturaH2Inch`).val(0.0000);
            $(`#inputAlturaH3`).val(0.00);
            $(`#inputAlturaH3Inch`).val(0.0000);
        }
    }

    // VALIDAR LAS DIMENSIONES
    function validarCamposDimensiones() {
        let valores = [
            $("#altura_mm_cliente").val(),
            $("#diametro_interior_mm_cliente").val(),
            $("#diametro_exterior_mm_cliente").val(),
            $("#inputAlturaCaja").val(),
            $("#inputAlturaEscalon").val(),
            $("#inputAlturaH2").val(),
            $("#inputAlturaH3").val(),
        ];

        let familiaSello = window.FAMILIA_PERFIL_SIMPLE;
        let perfilSello = window.perfilSello;
        let selectorTipoMedidaDI = $("#selectorTipoMedidaDI").val();
        let selectorTipoMedidaDE = $("#selectorTipoMedidaDE").val();
        let selectorTipoMedidaH = $("#selectorTipoMedidaH").val();
        let DI_R = valores[1] || 0.00;
        let DE_R = valores[2] || 0.00;
        let ALTURA_R = valores[0] || 0.00;

        let SECCION = 0.00;
        // AQUI VA EL CODIGO DONDE SE MANDA A LLAMAR LA VALIDACION DE MEDIDAS MINIMAS Y MAXIMAS DEL MAQUINADO
        let esAdvertencia = false;
        // Calcular seccion radial
        SECCION = (parseFloat(DE_R) - parseFloat(DI_R)) / 2;

        // Validaciones CNC
        if (parseFloat(DI_R) < 5.00) {
            $("#containerErrorDimensiones_cliente span").text('El DI debe ser mayor a 5.00 mm. Favor de consultar si es posible el maquinado.');
            esAdvertencia = true;
            //window.DIMENSIONES_VALIDAS = false;
            //return false;
        }

        if (parseFloat(DE_R) > 850.00) {
            $("#containerErrorDimensiones_cliente span").text('El DE debe ser menor a 850.00 mm. Favor de consultar si es posible el maquinado.');
            esAdvertencia = true;
            //window.DIMENSIONES_VALIDAS = false;
            //return false;
        }
        // Seccion radial minima
        if (SECCION < 2.25) {
            $("#containerErrorDimensiones_cliente span").text('La seccion radial no puede ser menor a 2.25 mm. Favor de consultar si es posible el maquinado.');
            esAdvertencia = true;
            //window.DIMENSIONES_VALIDAS = false;
            //return false;
        }
        // Seccion radial maxima
        if (SECCION > 45.00) {
            $("#containerErrorDimensiones_cliente span").text('La seccion radial no puede ser mayor a 45 mm. Favor de consultar si es posible el maquinado.');
            esAdvertencia = true;
            //window.DIMENSIONES_VALIDAS = false;
            //return false;
        }

        // Para H < 50.8 el DI debe ser > 20, condicional de rechazo
        if (parseFloat(ALTURA_R) < 50.8 && parseFloat(DI_R) <= 20.00) {
            $("#containerErrorDimensiones_cliente span").text('Para alturas menores a 50.8 mm, el DI debe ser mayor a 20 mm. Favor de consultar si es posible el maquinado.');
            esAdvertencia = true;
            //window.DIMENSIONES_VALIDAS = false;
            //return false;
        }

        // Para H < 9 el DI debe ser > 5, condicional de rechazo
        if (parseFloat(ALTURA_R) < 9.00 && parseFloat(DI_R) <= 5.00) {
            $("#containerErrorDimensiones_cliente span").text('Para alturas menores a 9 mm, el DI debe ser mayor a 5 mm. Favor de consultar si es posible el maquinado.');
            esAdvertencia = true;
            //window.DIMENSIONES_VALIDAS = false;
            //return false;
        }


        /////////////////////////////////////////////////////////////////
    
        if (parseFloat(ALTURA_R) == 0 || parseFloat(ALTURA_R) < 0 || parseFloat(DE_R) == 0 || parseFloat(DE_R) < 0) {
            $("#containerErrorDimensiones_cliente span").text('La altura y el DE no puede ser 0');
            return false;
        }       
        if (parseFloat(DI_R) >= parseFloat(DE_R)) {
            $("#containerErrorDimensiones_cliente span").text('El DI no puede ser mayor o igual al DE');
            return false; 
        }
        // Verificar si alguno de los valores es inválido
        let hayValoresInvalidos = valores.some(function(valor) {
            return valor === null || valor === "" || isNaN(valor) || valor === ".";
        });
        if (hayValoresInvalidos) {
            $("#containerErrorDimensiones_cliente span").text('Ingrese las dimensiones solicitadas correctamente');
            if(window.esWiper !== "0"){
                $("#containerErrorDimensiones_cliente span").text('Ingrese las dimensiones solicitadas correctamente');
                $(`#containerErrorDimensiones_m${window.esWiper} span`).text('Ingrese las dimensiones solicitadas correctamente');
                window["DIMENSIONES_VALIDAS_m" + window.esWiper] = false;  
            }
            window.DIMENSIONES_VALIDAS = false;
            return false; 
        }
        // es un wiper
        if(window.esWiper !== "0"){
            if (parseFloat(valores[3]) > parseFloat(ALTURA_R)) {
                // console.log("La altura de caja no puede ser mayor a la altura total.");
                $("#containerErrorDimensiones_cliente span").text('Altura de caja no debe ser mayor a la total');
                $(`#containerErrorDimensiones_m${window.esWiper} span`).text('Altura de caja no debe ser mayor a la total');
                console.log("altura caja: ", parseFloat(valores[3]));
                console.log("altura cliente: ", parseFloat(ALTURA_R));
                window["DIMENSIONES_VALIDAS_m" + window.esWiper] = false;
                return false;
            }
            if(parseFloat(valores[3]) == 0 || parseFloat(valores[3]) <= 0 || isNaN(parseFloat(valores[3])) || parseFloat(valores[3]) == null){
                $("#containerErrorDimensiones_cliente span").text('Medida de altura de caja no valida');
                $(`#containerErrorDimensiones_m${window.esWiper} span`).text('Medida de altura de caja no valida');
                console.log("altura caja: ", parseFloat(valores[3]));
                console.log("altura cliente: ", parseFloat(ALTURA_R));
                window["DIMENSIONES_VALIDAS_m" + window.esWiper] = false;
                return false;
            }
        }
       // tiene escalon pero no es un especial
        if(window.conEscalon !== "0" && window.esWisperEspecial === "0"){
            if ((parseFloat(valores[4]) > parseFloat(valores[3])) || (parseFloat(valores[4]) > parseFloat(ALTURA_R))) {
                // console.log("La altura escalon no puede ser mayor a la altura caja.");
                $("#containerErrorDimensiones_cliente span").text('Altura escalon no puede ser mayor a la altura de caja o mayor a la total');
                $(`#containerErrorDimensiones_m${window.conEscalon} span`).text('Altura escalon no puede ser mayor a la altura de caja o mayor a la total');
                window["DIMENSIONES_VALIDAS_m" + window.conEscalon] = false;
                return false;
            }
            if(parseFloat(valores[4]) == 0 || parseFloat(valores[4]) <= 0 || isNaN(parseFloat(valores[4])) || parseFloat(valores[4]) == null){
                $("#containerErrorDimensiones_cliente span").text('Medida de altura de escalón no valida');
                $(`#containerErrorDimensiones_m${window.esWiper} span`).text('Medida de altura de escalón no valida');
                console.log("altura escalón: ", parseFloat(valores[4]));
                console.log("altura cliente: ", parseFloat(ALTURA_R));
                window["DIMENSIONES_VALIDAS_m" + window.esWiper] = false;
                return false;
            }
        }
        let H2MasH3 = 0.00;
        // es un especial con h1 y h2        
        if(window.esWisperEspecial !== "0"){
            H2MasH3 = parseFloat(valores[5]) + parseFloat(valores[6]);
            if ((parseFloat(valores[5]) > parseFloat(valores[3])) || (parseFloat(valores[5]) > parseFloat(ALTURA_R)) || (parseFloat(valores[6]) > parseFloat(valores[3])) || (parseFloat(valores[6]) > parseFloat(ALTURA_R))) {
                $("#containerErrorDimensiones_cliente span").text('Altura H2 y H3 no puede ser mayor a la altura de caja o mayor a la total');
                $(`#containerErrorDimensiones_m${window.esWisperEspecial} span`).text('Altura H2 y H3 no puede ser mayor a la altura de caja o mayor a la total');
                window["DIMENSIONES_VALIDAS_m" + window.esWisperEspecial] = false;
                return false;
            }
            if((H2MasH3 > parseFloat(valores[3])) || (H2MasH3 > parseFloat(ALTURA_R))){
                $("#containerErrorDimensiones_cliente span").text('La suma de H2 y H3 no puede ser mayor a la altura de caja o mayor a la total');
                $(`#containerErrorDimensiones_m${window.esWisperEspecial} span`).text('La suma de H2 y H3 no puede ser mayor a la altura de caja o mayor a la total');
                window["DIMENSIONES_VALIDAS_m" + window.esWisperEspecial] = false;
                return false;
            }
            if(parseFloat(valores[5]) == 0 || parseFloat(valores[5]) <= 0 || isNaN(parseFloat(valores[5])) || parseFloat(valores[5]) == null){
                $("#containerErrorDimensiones_cliente span").text('Medida de altura H2 no valida');
                $(`#containerErrorDimensiones_m${window.esWiper} span`).text('Medida de altura H2 no valida');
                console.log("altura H2: ", parseFloat(valores[5]));
                console.log("altura cliente: ", parseFloat(ALTURA_R));
                window["DIMENSIONES_VALIDAS_m" + window.esWiper] = false;
                return false;
            }
            if(parseFloat(valores[6]) == 0 || parseFloat(valores[6]) <= 0 || isNaN(parseFloat(valores[6])) || parseFloat(valores[6]) == null){
                $("#containerErrorDimensiones_cliente span").text('Medida de altura H3 no valida');
                $(`#containerErrorDimensiones_m${window.esWiper} span`).text('Medida de altura H3 no valida');
                console.log("altura H3: ", parseFloat(valores[6]));
                console.log("altura cliente: ", parseFloat(ALTURA_R));
                window["DIMENSIONES_VALIDAS_m" + window.esWiper] = false;
                return false;
            }
            console.log("suma", H2MasH3);
        }

        if (parseFloat(DI_R) == 0.00){
            window.DI_DESPERDICIO_DEFAULT = 0.00;
            window.DE_DESPERDICIO_DEFAULT = 0.00;
        }else{
            window.DI_DESPERDICIO_DEFAULT = 3.00;
            window.DE_DESPERDICIO_DEFAULT = 3.00;
        }
        if(esAdvertencia = false){
            $("#containerErrorDimensiones_cliente span").text('');
        }else{

        }
        $(`#containerErrorDimensiones_m${window.esWisper} span`).text('');
        $(`#containerErrorDimensiones_m${window.conEscalon} span`).text('');
        $(`#containerErrorDimensiones_m${window.esWisperEspecial} span`).text('');

        // si todo es valido, retornar verdadero
        window.DIMENSIONES_VALIDAS = true;
        return true; 
    }

    // Funcion para saber si ya todos los materiales han sido completados
    function habilitarCotizacion(MaterialesCompletados){
        if(MaterialesCompletados == window.CANTIDAD_MATERIALES){
            habilitarBoton("#btnCotizar");
            $("#btnCotizar").text("Cotizar");
            console.log("Todos los materiales han sido completados");
        }else{
            disablarBoton("#btnCotizar");
            $("#btnCotizar").text("Complete todos los materiales para cotizar");
            // console.log("Aun no estan completados todos los materiales", window.CANTIDAD_MATERIALES);
            console.log("Materiales completados: ", MaterialesCompletados, "/",window.CANTIDAD_MATERIALES);  
        }
    }

    // PUSH A PROMISES
    function pushPromise(numeroMaterial) {
        return new Promise((resolve) => {
            let diClienteSello = 0.00;
            let deClienteSello = 0.00;
            let alturaClienteSello = 0.00; 
            let diClienteSelloInch = 0.00;
            let deClienteSelloInch = 0.00;
            let alturaClienteSelloInch = 0.00;
            let diClienteSello2 = 0.00;
            let deClienteSello2 = 0.00;
            let alturaClienteSello2 = 0.00;
            let diClienteSelloInch2 = 0.00;
            let deClienteSelloInch2 = 0.00;
            let alturaClienteSelloInch2 = 0.00;

            diClienteSello = parseFloat($('#diametro_interior_mm_cliente').val()) || 0.00;
            deClienteSello = parseFloat($('#diametro_exterior_mm_cliente').val()) || 0.00;
            alturaClienteSello = parseFloat($('#altura_mm_cliente').val()) || 0.00; 
            diClienteSelloInch = parseFloat($('#diametro_interior_inch_cliente').val()) || 0.00;
            deClienteSelloInch = parseFloat($('#diametro_exterior_inch_cliente').val()) || 0.00;
            alturaClienteSelloInch = parseFloat($('#altura_inch_cliente').val()) || 0.00;
            diClienteSello2 = parseFloat($('#diametro_interior_mm_cliente2').val()) || 0.00;
            deClienteSello2 = parseFloat($('#diametro_exterior_mm_cliente2').val()) || 0.00;
            alturaClienteSello2 = parseFloat($('#altura_mm_cliente2').val()) || 0.00;
            diClienteSelloInch2 = parseFloat($('#diametro_interior_inch_cliente2').val()) || 0.00;
            deClienteSelloInch2 = parseFloat($('#diametro_exterior_inch_cliente2').val()) || 0.00;
            alturaClienteSelloInch2 = parseFloat($('#altura_inch_cliente2').val()) || 0.00;

            let tipoMedidaDI = $('#selectorTipoMedidaDI').val();
            let tipoMedidaDE = $('#selectorTipoMedidaDE').val();
            let tipoMedidaH = $('#selectorTipoMedidaH').val();

            // if(window.TIPO_MEDIDA_SELLO == "Sello"){
            //     diClienteSello = parseFloat($('#diametro_interior_mm_cliente').val()) || 0.00;
            //     deClienteSello = parseFloat($('#diametro_exterior_mm_cliente').val()) || 0.00;
            //     alturaClienteSello = parseFloat($('#altura_mm_cliente').val()) || 0.00; 
            //     diClienteSelloInch = parseFloat($('#diametro_interior_inch_cliente').val()) || 0.00;
            //     deClienteSelloInch = parseFloat($('#diametro_exterior_inch_cliente').val()) || 0.00;
            //     alturaClienteSelloInch = parseFloat($('#altura_inch_cliente').val()) || 0.00;
            //     diClienteSello2 = parseFloat($('#diametro_interior_mm_cliente2').val()) || 0.00;
            //     deClienteSello2 = parseFloat($('#diametro_exterior_mm_cliente2').val()) || 0.00;
            //     alturaClienteSello2 = parseFloat($('#altura_mm_cliente2').val()) || 0.00;
            //     diClienteSelloInch2 = parseFloat($('#diametro_interior_inch_cliente2').val()) || 0.00;
            //     deClienteSelloInch2 = parseFloat($('#diametro_exterior_inch_cliente2').val()) || 0.00;
            //     alturaClienteSelloInch2 = parseFloat($('#altura_inch_cliente2').val()) || 0.00;
            // }else{
            //     diClienteSello = parseFloat($('#diametro_interior_mm_cliente2').val()) || 0.00;
            //     deClienteSello = parseFloat($('#diametro_exterior_mm_cliente2').val()) || 0.00;
            //     alturaClienteSello = parseFloat($('#altura_mm_cliente2').val()) || 0.00; 
            //     diClienteSelloInch = parseFloat($('#diametro_interior_inch_cliente2').val()) || 0.00;
            //     deClienteSelloInch = parseFloat($('#diametro_exterior_inch_cliente2').val()) || 0.00;
            //     alturaClienteSelloInch = parseFloat($('#altura_inch_cliente2').val()) || 0.00;
            //     diClienteSello2 = parseFloat($('#diametro_interior_mm_cliente').val()) || 0.00;
            //     deClienteSello2 = parseFloat($('#diametro_exterior_mm_cliente').val()) || 0.00;
            //     alturaClienteSello2 = parseFloat($('#altura_mm_cliente').val()) || 0.00;
            //     diClienteSelloInch2 = parseFloat($('#diametro_interior_inch_cliente').val()) || 0.00;
            //     deClienteSelloInch2 = parseFloat($('#diametro_exterior_inch_cliente').val()) || 0.00;
            //     alturaClienteSelloInch2 = parseFloat($('#altura_inch_cliente').val()) || 0.00;
            // }

            $.ajax({
                url: '../ajax/ajax_guardar_cotizacion.php',
                type: 'POST',
                data: $(`#estimateForm_m${numeroMaterial}`).serialize() +
                    '&a_sello=' + encodeURIComponent(alturaClienteSello) +
                    '&di_sello=' + encodeURIComponent(diClienteSello) +
                    '&de_sello=' + encodeURIComponent(deClienteSello) +
                    '&a_sello_inch=' + encodeURIComponent(alturaClienteSelloInch) +
                    '&di_sello_inch=' + encodeURIComponent(diClienteSelloInch) +
                    '&de_sello_inch=' + encodeURIComponent(deClienteSelloInch) +
                    '&tipo_medida_di=' + encodeURIComponent(tipoMedidaDI) +
                    '&tipo_medida_de=' + encodeURIComponent(tipoMedidaDE) +
                    '&tipo_medida_h=' + encodeURIComponent(tipoMedidaH) +
                    '&a_sello2=' + encodeURIComponent(alturaClienteSello2) +
                    '&di_sello2=' + encodeURIComponent(diClienteSello2) +
                    '&de_sello2=' + encodeURIComponent(deClienteSello2) +
                    '&a_sello_inch2=' + encodeURIComponent(alturaClienteSelloInch2) +
                    '&di_sello_inch2=' + encodeURIComponent(diClienteSelloInch2) +
                    '&de_sello_inch2=' + encodeURIComponent(deClienteSelloInch2),
                dataType: 'json',
                success: function (data) {
                    console.log("Respuesta AJAX material", numeroMaterial, data);
                    if (data.success) {
                        resolve({ ok: true, numeroMaterial });
                    } else {
                        console.error("Error en material " + numeroMaterial + ": " + (data.message || "Error desconocido"));
                        resolve({ ok: false, numeroMaterial });
                    }
                },
                error: function (xhr, status, error) {
                    console.error(`Fallo AJAX en material ${numeroMaterial}:`, status, error);
                    resolve({ ok: false, numeroMaterial });
                }
            });
        });
    }


// //////////////////////////////////////// @LLAMADAS AJAX INICIALES
    // llamada ajax para obtener la informacion del PERFIL
    $.ajax({
        url: '../ajax/ajax_perfil.php',
        type: 'POST',
        data: { perfil: window.perfilSello },
        dataType: 'json',
        success: function (data) {
            if (data == false) {
                console.log("Perfil no encontrado. Respuesta: ", data.perfil);
            }else{
                console.log("Familia existe. Respuesta: ", data.tipo);
                console.log("Perfil existe. Respuesta: ", data.perfil);
            }
            window.FAMILIA_PERFIL = data.tipo;
            window.FAMILIA_PERFIL_SIMPLE = data.tipo;
            window.CANTIDAD_MATERIALES = data.cantidad_materiales;
            window.CON_LABIO_DI = data.con_labio_di;
            window.CON_LABIO_DE = data.con_labio_de;
            window.P_LABIO_DI = data.p_labio_di;
            window.P_LABIO_DE = data.p_labio_de;
            //window.TIPO_MEDIDA_SELLO = data.tipo_medida;
            window.tieneResorte = data.con_resorte;
            window.esWiper = data.es_wiper;
            window.conEscalon = data.con_escalon;
            window.esWisperEspecial = data.es_wisper_especial;
            window.porcentajeA_escalon = data.p_a_escalon;
            window.porcentajeA_caja = data.p_a_caja;
            window.porcentajeA_h2 = data.p_a_h2;
            window.porcentajeA_h3 = data.p_a_h3;

            window.porcentajeA_m1 = data.p_a_m1;
            window.porcentajeA_m2 = data.p_a_m2;
            window.porcentajeA_m3 = data.p_a_m3;
            window.porcentajeA_m4 = data.p_a_m4;
            window.porcentajeA_m5 = data.p_a_m5;

            // window.porcentajeDI_m1 = data.p_di_m1;
            // window.porcentajeDI_m2 = data.p_di_m2;
            // window.porcentajeDI_m3 = data.p_di_m3;
            // window.porcentajeDI_m4 = data.p_di_m4;
            // window.porcentajeDI_m5 = data.p_di_m5;

            // window.porcentajeDE_m1 = data.p_de_m1;
            // window.porcentajeDE_m2 = data.p_de_m2;
            // window.porcentajeDE_m3 = data.p_de_m3;
            // window.porcentajeDE_m4 = data.p_de_m4;
            // window.porcentajeDE_m5 = data.p_de_m5;

            switch (window.FAMILIA_PERFIL) {
                case "rotary":
                    window.FAMILIA_PERFIL = "Rotary (Rotativo)";
                    break;
                case "piston":
                    window.FAMILIA_PERFIL = "Piston (Pistón)";
                    break;
                case "backup":
                    window.FAMILIA_PERFIL = "Backup (Respaldo)";
                    break;
                case "guide":
                    window.FAMILIA_PERFIL = "Guide (Guía)";
                    break;
                case "wipers":
                    window.FAMILIA_PERFIL = "Wiper (Limpiador)";
                    break;
                case "rod":
                    window.FAMILIA_PERFIL = "Rod (Vástago)";
                    break;
                default:
                    window.FAMILIA_PERFIL = "";
                    break;
            }

            $(".familia-perfil").val(window.FAMILIA_PERFIL);

            console.log("Desperdicio DI: ", window.DI_DESPERDICIO_DEFAULT);
            console.log("Desperdicio DE: ", window.DE_DESPERDICIO_DEFAULT);

            if(window.CON_LABIO_DI !== "0"){
                console.log("Si tiene labio en el DI en el material: ", window.CON_LABIO_DI);
            }else{
                console.log("No tiene labio en el DI");
            }
            if(window.CON_LABIO_DE !== "0"){
                console.log("Si tiene labio en el DE en el material: ", window.CON_LABIO_DE);
            }else{
                console.log("No tiene labio en el DE");
            }
            // if(window.CON_LABIO_DI === "0" && window.CON_LABIO_DE === "0"){
            //     $("#selectorTipoMedida").append(`
            //         <option value="Sello">Sello</option>
            //         <option value="Metal">Metal</option>`);
            // }else{
            //     $("#selectorTipoMedida").append(`<option value="Sello">Sello</option>
            //                                         <option value="Metal">Metal</option>`);
            // }

            if(window.tieneResorte === "1"){
                console.log("Si tiene resorte");
            }else{
                console.log("No tiene resorte");
            }

            console.log("Tipo de medida: ", window.TIPO_MEDIDA_SELLO);

            if(window.esWiper !== "0"){
                $(`#labelAlturaMM_cliente`).text("Altura Total (H)");
                $(`#labelAlturaMM_cliente, #labelAlturaInch_cliente`).css("font-size", "22px");
                $(`#labelAlturaMM_cliente, #labelAlturaInch_cliente`).css("color", "#00bb8f");
                $(`#divAlturaCaja`).removeClass("d-none");
                $("#btnOtrasAlturas").removeClass("d-none");
                console.log("Si es wiper");

            }else{
                $(`#containerbtnOtrasAlturas`).remove();
                console.log("No es wiper");
            }
            console.log("wiper = ", window.esWiper);

            if(window.conEscalon !== "0" && window.esWisperEspecial === "0"){
                $(`#divAlturaEscalon`).removeClass("d-none");
                console.log("Tiene escalon");
            }else{
                console.log("No tiene escalon");
            }

            if(window.esWisperEspecial !== "0"){
                $(`#divAlturaH2, #divAlturaH3, #questionIconSpecialWiper`).removeClass("d-none");
                console.log("Es wisper especial");
            }else{
                console.log("No es wisper especial");
            }
            for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
                let imagen = $(`#imagenMaterial_m${i}`);
                let imagenUrl = imagen.attr('src');
                let img = new Image();
                img.src = imagenUrl;
            
                img.onload = function () {
                    $(`#sectionContainerMaterial_m${i}`).removeClass("d-none");
                    $(`#imagenMaterialTabla_m${i}`).attr("src", imagenUrl);
                    $(`#seraEnviado_m${i}`).val("si");
                };
                
            }

            console.log("Materiales del perfil: ", window.CANTIDAD_MATERIALES);  
        },
        error: function (xhr, status, error) {
            console.error('Error al realizar la petición AJAX', status, error);
        }
    });

    const isUserFive = $("#isFive").val();
    if(isUserFive == "0"){
        $('#selectorCliente').chosen({
            no_results_text: "No se encontraron resultados",
            width: "100%"
        });
        // Realizar la llamada AJAX para obtener los CLIENTES
        $.ajax({
            url: '../ajax/ajax_clientes.php', 
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Verifica que la respuesta tenga datos
                if (data.length > 0) {
                    $.each(data, function(index, item) {
                        $("#selectorCliente").append(
                            `
                            <option value="${item.nombre}"
                                    data-clasificacion="${item.clasificacion}"
                                    data-descuento="${item.descuento}"
                                    data-codigo="${item.codigo}"
                                    data-correo="${item.correo}"
                            >${item.nombre} - ${item.clasificacion}</option>
                            `
                        );
                        $("#selectorCliente").trigger("chosen:updated");
                    });
                } else {
                }
            },
            error: function() {
                console.error('Error al realizar la petición AJAX');
            }
        });
    }else{
        setTimeout(() => {
            $("#selectorCliente").trigger("change");
            console.log("se disparo el trigger");
            
        }, 1000);
    }




// ///////////////////////////////////////// @OTRAS INICIALIZACIONES

    const idCotizacion = idRandom();
    $("#btnCotizar").removeClass("d-none");
    $("#sectionTotalFinal").removeClass("d-none");
    $("#sectionCotizar").removeClass("d-none");



// ////////////////////////////////////////////////// @LISTENING DE EVENTOS EN EL DOM
    // EVENTO SELECCIONAR CLIENTE, PONE EL MISMO CLIENTE A TODOS LOS FORMULARIOS
    $("#selectorCliente").on("change", function(){
        
        if(boolClienteSeleccionado==false){
            boolClienteSeleccionado=true;
        }else{
            resetear_materiales_completados();
        }

        let nombreCliente = $("#selectorCliente").val();
        let selectedOption = $("#selectorCliente option:selected");
        
        let clasificacionCliente = $(selectedOption).data("clasificacion");
        let descuentoCliente = $(selectedOption).data("descuento");
        let codigoCliente = $(selectedOption).data("codigo");
        let correoCliente = $(selectedOption).data("correo");
        
        $(".cliente-nombre").val(nombreCliente);
        $(".cliente-tipo").val(clasificacionCliente);
        $(".porcent-descuento-cliente").val(descuentoCliente);
        $(".cliente-codigo").val(codigoCliente);
        $(".cliente-correo").val(correoCliente);

        console.log(nombreCliente);
        console.log(clasificacionCliente);
        console.log(descuentoCliente);
        console.log(codigoCliente);
        console.log(correoCliente);

        // $("#selectorCliente_chosen").css("pointer-events", "none");
        // $("#selectorCliente").css("cursor", "not-allowed");
        // $("#selectorCliente_chosen").css("cursor", "not-allowed");
        $("#selectorTipoMedidaDI").attr("disabled", false);
        $("#selectorTipoMedidaDE").attr("disabled", false);
        $("#selectorTipoMedidaH").attr("disabled", false);

    });
    
    // EVENTO CAMBIAR DE TIPO DE MEDIDA DEL DIAMETRO INTERIOR
    $("#selectorTipoMedidaDI").on("change", function(){
        let tipoMedidaDI = $(this).val();
        window.TIPO_MEDIDA_DI = tipoMedidaDI;
        console.log("tipo medida DI cambio a: ", window.TIPO_MEDIDA_DI);
        if(boolMedidaSeleccionadaDI==false){
            boolMedidaSeleccionadaDI=true;
            habilitarInput(`#diametro_interior_mm_cliente`);
            habilitarInput(`#diametro_interior_inch_cliente`);
            habilitarInput(`#diametro_interior_mm_cliente2`);
            habilitarInput(`#diametro_interior_inch_cliente2`);
        }else{
            $("#altura_mm_cliente").trigger("input");
            resetear_materiales_completados();
        }
    });
    // EVENTO CAMBIAR DE TIPO DE MEDIDA DEL DIAMETRO EXTERIOR
    $("#selectorTipoMedidaDE").on("change", function(){
        let tipoMedidaDE = $(this).val();
        window.TIPO_MEDIDA_DE = tipoMedidaDE;
        console.log("tipo medida DE cambio a: ", window.TIPO_MEDIDA_DE);
        if(boolMedidaSeleccionadaDE==false){
            boolMedidaSeleccionadaDE=true;
            habilitarInput(`#diametro_exterior_mm_cliente`);
            habilitarInput(`#diametro_exterior_inch_cliente`);
            habilitarInput(`#diametro_exterior_mm_cliente2`);
            habilitarInput(`#diametro_exterior_inch_cliente2`);
        }else{
            $("#altura_mm_cliente").trigger("input");
            resetear_materiales_completados();
        }
    });
    // EVENTO CAMBIAR DE TIPO DE MEDIDA ALTURA
    $("#selectorTipoMedidaH").on("change", function(){
        let tipoMedidaH = $(this).val();
        window.TIPO_MEDIDA_H = tipoMedidaH;
        console.log("tipo medida H cambio a: ", window.TIPO_MEDIDA_H);
        if(boolMedidaSeleccionadaH==false){
            boolMedidaSeleccionadaH=true;
            habilitarInput(`#altura_mm_cliente`);
            habilitarInput(`#altura_inch_cliente`);
            habilitarInput(`#altura_mm_cliente2`);
            habilitarInput(`#altura_inch_cliente2`);

            // para wispers
            habilitarInput("#inputAlturaCaja");
            habilitarInput("#inputAlturaCajaInch");
            habilitarInput("#inputAlturaEscalon");
            habilitarInput("#inputAlturaEscalonInch");
            habilitarInput("#inputAlturaH2");
            habilitarInput("#inputAlturaH2Inch");
            habilitarInput("#inputAlturaH3");
            habilitarInput("#inputAlturaH3Inch");
        }else{
            $("#altura_mm_cliente").trigger("input");
            resetear_materiales_completados();
        }
        // $(".tipo-medida").val(window.TIPO_MEDIDA_SELLO);
        // $("#spanTipoMedida").text(window.TIPO_MEDIDA_SELLO);

        // if(window.TIPO_MEDIDA_SELLO == "Sello"){
        //     $("#lblMedidaPrimaria").text("Digitar medida Sello");
        //     $("#lblMedidaSecundaria").text("Digitar medida Metal (opcional)");
        // }else{
        //     $("#lblMedidaPrimaria").text("Digitar medida Metal");
        //     $("#lblMedidaSecundaria").text("Digitar medida Sello (opcional)");
        // }
        
    });

    // -----------------------------ALTURA--------------------------------------------------------------------
    // ---------------  MM a INCH  ---------------------
    $('#altura_mm_cliente').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaMm = parseFloat($(this).val());
        if (isNaN(valorAlturaMm)) {
            valorAlturaMm = 0;
        }
        let alturaMmToInch = valorAlturaMm / 25.4;
        $('#altura_inch_cliente').val(alturaMmToInch.toFixed(4));
    });
    // ----------  INCH a MM ------------------------------------
    $('#altura_inch_cliente').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaInch = parseFloat($(this).val());
        if (isNaN(valorAlturaInch)) {
            valorAlturaInch = 0;
        }
        let alturaInchToMm = valorAlturaInch * 25.4;
        $('#altura_mm_cliente').val(alturaInchToMm.toFixed(2));
    });

    // -----------------------------ALTURA CAJA--------------------------------------------------------------------
    // ---------------  MM a INCH  ---------------------
    $('#inputAlturaCaja').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaCajaMm = parseFloat($(this).val());
        if (isNaN(valorAlturaCajaMm)) {
            valorAlturaCajaMm = 0;
        }
        let alturaCajaMmToInch = valorAlturaCajaMm / 25.4;
        $('#inputAlturaCajaInch').val(alturaCajaMmToInch.toFixed(4));
        $('#inputAlturaCajaInch_m1').val(alturaCajaMmToInch.toFixed(4));
    });
    // ----------  INCH a MM ------------------------------------
    $('#inputAlturaCajaInch').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaCajaInch = parseFloat($(this).val());
        if (isNaN(valorAlturaCajaInch)) {
            valorAlturaCajaInch = 0;
        }
        let alturaCajaInchToMm = valorAlturaCajaInch * 25.4;
        $('#inputAlturaCaja').val(alturaCajaInchToMm.toFixed(2));
    });

    // -----------------------------ALTURA ESCALON--------------------------------------------------------------------
    // ---------------  MM a INCH  ---------------------
    $('#inputAlturaEscalon').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaEscalonMm = parseFloat($(this).val());
        if (isNaN(valorAlturaEscalonMm)) {
            valorAlturaEscalonMm = 0;
        }
        let alturaEscalonMmToInch = valorAlturaEscalonMm / 25.4;
        $('#inputAlturaEscalonInch').val(alturaEscalonMmToInch.toFixed(4));
        $('#inputAlturaEscalonInch_m1').val(alturaEscalonMmToInch.toFixed(4));
    });
    // ----------  INCH a MM ------------------------------------
    $('#inputAlturaEscalonInch').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaEscalonInch = parseFloat($(this).val());
        if (isNaN(valorAlturaEscalonInch)) {
            valorAlturaEscalonInch = 0;
        }
        let alturaEscalonInchToMm = valorAlturaEscalonInch * 25.4;
        $('#inputAlturaEscalon').val(alturaEscalonInchToMm.toFixed(2));
    });

    // -----------------------------ALTURA H2--------------------------------------------------------------------
    // ---------------  MM a INCH  ---------------------
    $('#inputAlturaH2').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaH2Mm = parseFloat($(this).val());
        if (isNaN(valorAlturaH2Mm)) {
            valorAlturaH2Mm = 0;
        }
        let alturaH2MmToInch = valorAlturaH2Mm / 25.4;
        $('#inputAlturaH2Inch').val(alturaH2MmToInch.toFixed(4));
    });
    // ----------  INCH a MM ------------------------------------
    $('#inputAlturaH2Inch').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaH2Inch = parseFloat($(this).val());
        if (isNaN(valorAlturaH2Inch)) {
            valorAlturaH2Inch = 0;
        }
        let alturaH2InchToMm = valorAlturaH2Inch * 25.4;
        $('#inputAlturaH2').val(alturaH2InchToMm.toFixed(2));
    });

    // -----------------------------ALTURA H3--------------------------------------------------------------------
    // ---------------  MM a INCH  ---------------------
    $('#inputAlturaH3').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaH3Mm = parseFloat($(this).val());
        if (isNaN(valorAlturaH3Mm)) {
            valorAlturaH3Mm = 0;
        }
        let alturaH3MmToInch = valorAlturaH3Mm / 25.4;
        $('#inputAlturaH3Inch').val(alturaH3MmToInch.toFixed(4));
    });
    // ----------  INCH a MM ------------------------------------
    $('#inputAlturaH3Inch').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaH3Inch = parseFloat($(this).val());
        if (isNaN(valorAlturaH3Inch)) {
            valorAlturaH3Inch = 0;
        }
        let alturaH3InchToMm = valorAlturaH3Inch * 25.4;
        $('#inputAlturaH3').val(alturaH3InchToMm.toFixed(2));
    });


    // ---------------------------DIAMETRO INTERIOR--------------------------------------------------------------------
    // ---------------  MM a INCH  ---------------------
    $('#diametro_interior_mm_cliente').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorDiaInMm = parseFloat($(this).val());
        if (isNaN(valorDiaInMm)) {
            valorDiaInMm = 0;
        }
        let DiaInMmToInch = valorDiaInMm / 25.4;
        $('#diametro_interior_inch_cliente').val(DiaInMmToInch.toFixed(4));
        
    });
    // ----------  INCH a MM ------------------------------------
    $('#diametro_interior_inch_cliente').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorDiaInInch = parseFloat($(this).val());
        if (isNaN(valorDiaInInch)) {
            valorDiaInInch = 0;
        }
        let DiaInInchToMm = valorDiaInInch * 25.4;
        $('#diametro_interior_mm_cliente').val(DiaInInchToMm.toFixed(2));
    });


    // ---------------------------DIAMETRO EXTERIOR--------------------------------------------------------------------
    // ---------------  MM a INCH  ---------------------
    $('#diametro_exterior_mm_cliente').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorDiaExMm = parseFloat($(this).val());
        if (isNaN(valorDiaExMm)) {
            valorDiaExMm = 0;
        }
        let DiaExMmToInch = valorDiaExMm / 25.4;
        $('#diametro_exterior_inch_cliente').val(DiaExMmToInch.toFixed(4));
        
    });
    // ----------  INCH a MM ------------------------------------
    $('#diametro_exterior_inch_cliente').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorDiaExInch = parseFloat($(this).val());
        if (isNaN(valorDiaExInch)) {
            valorDiaExInch = 0;
        }
        let DiaExInchToMm = valorDiaExInch * 25.4;
        $('#diametro_exterior_mm_cliente').val(DiaExInchToMm.toFixed(2));
    });


    // -----------------------------ALTURA SECUNDARIA--------------------------------------------------------------------
    // ---------------  MM a INCH  ---------------------
    $('#altura_mm_cliente2').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaMm = parseFloat($(this).val());
        if (isNaN(valorAlturaMm)) {
            valorAlturaMm = 0;
        }
        let alturaMmToInch = valorAlturaMm / 25.4;
        $('#altura_inch_cliente2').val(alturaMmToInch.toFixed(4));
    });
    // ----------  INCH a MM ------------------------------------
    $('#altura_inch_cliente2').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorAlturaInch = parseFloat($(this).val());
        if (isNaN(valorAlturaInch)) {
            valorAlturaInch = 0;
        }
        let alturaInchToMm = valorAlturaInch * 25.4;
        $('#altura_mm_cliente2').val(alturaInchToMm.toFixed(2));
    });


    // ---------------------------DIAMETRO INTERIOR--------------------------------------------------------------------
    // ---------------  MM a INCH  ---------------------
    $('#diametro_interior_mm_cliente2').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorDiaInMm = parseFloat($(this).val());
        if (isNaN(valorDiaInMm)) {
            valorDiaInMm = 0;
        }
        let DiaInMmToInch = valorDiaInMm / 25.4;
        $('#diametro_interior_inch_cliente2').val(DiaInMmToInch.toFixed(4));
        
    });
    // ----------  INCH a MM ------------------------------------
    $('#diametro_interior_inch_cliente2').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorDiaInInch = parseFloat($(this).val());
        if (isNaN(valorDiaInInch)) {
            valorDiaInInch = 0;
        }
        let DiaInInchToMm = valorDiaInInch * 25.4;
        $('#diametro_interior_mm_cliente2').val(DiaInInchToMm.toFixed(2));
    });


    // ---------------------------DIAMETRO EXTERIOR--------------------------------------------------------------------
    // ---------------  MM a INCH  ---------------------
    $('#diametro_exterior_mm_cliente2').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorDiaExMm = parseFloat($(this).val());
        if (isNaN(valorDiaExMm)) {
            valorDiaExMm = 0;
        }
        let DiaExMmToInch = valorDiaExMm / 25.4;
        $('#diametro_exterior_inch_cliente2').val(DiaExMmToInch.toFixed(4));
        
    });
    // ----------  INCH a MM ------------------------------------
    $('#diametro_exterior_inch_cliente2').on('input', function(){
        escaparCaracteresNumericos_cliente.call(this);
        let valorDiaExInch = parseFloat($(this).val());
        if (isNaN(valorDiaExInch)) {
            valorDiaExInch = 0;
        }
        let DiaExInchToMm = valorDiaExInch * 25.4;
        $('#diametro_exterior_mm_cliente2').val(DiaExInchToMm.toFixed(2));
    });

    // EVENTO INPUT DE LOS DIAMETROS RESULTANTES
    $('#diametro_interior_mm_cliente, #diametro_exterior_mm_cliente, #altura_mm_cliente, #diametro_interior_inch_cliente, #diametro_exterior_inch_cliente, #altura_inch_cliente').on("input", function(){
        window.DI_CLIENTE = parseFloat($('#diametro_interior_mm_cliente').val()) || 0.00;
        window.DE_CLIENTE = parseFloat($('#diametro_exterior_mm_cliente').val()) || 0.00;
        let alturaCliente = parseFloat($('#altura_mm_cliente').val()) || 0.00;     
        
        autoCalculoDimensiones(window.DI_CLIENTE, window.DE_CLIENTE, alturaCliente);
        resetear_materiales_completados();
        validarCamposDimensiones();
    
    });

    // EVENTO INPUT DE LAS ALTURAS DE WISPERS
    $('#inputAlturaCaja, #inputAlturaCajaInch, #inputAlturaEscalon, #inputAlturaEscalonInch, #inputAlturaH2, #inputAlturaH2Inch, #inputAlturaH3, #inputAlturaH3Inch').on("input change", function(){
        
        $(`#inputAlturaCaja_m${window.esWiper}`).val($("#inputAlturaCaja").val());
        $(`#inputAlturaEscalon_m${window.conEscalon}`).val($("#inputAlturaEscalon").val());
        $(`#inputAlturaH2_m${window.esWisperEspecial}`).val($("#inputAlturaH2").val());
        $(`#inputAlturaH3_m${window.esWisperEspecial}`).val($("#inputAlturaH3").val());
        let alturaCliente = parseFloat($('#altura_mm_cliente').val()) || 0.00;     
        
        autoCalculoDimensiones(window.DI_CLIENTE, window.DE_CLIENTE, alturaCliente);
        resetear_materiales_completados();
        if (!validarCamposDimensiones() || validarCamposDimensiones() == false || window["DIMENSIONES_VALIDAS_m" + window.esWiper] == false) {
            disablarBoton(`#btnSiguiente_m${window.esWiper}`);
            return; 
        }
        habilitarBoton(`#btnSiguiente_m${window.esWiper}`);
        window["DIMENSIONES_VALIDAS_m" + window.esWiper] = true;        

    });
    //VERIFICAR MEDIDAS OTRA VEZ
    $("#btnOtrasAlturasClose").on("click", function(){
        validarCamposDimensiones();
    });

    $('#diametro_interior_mm_cliente, #diametro_interior_inch_cliente').on("blur", function() {
        let di = $(this).val().trim();
        if (di === "" || isNaN(di)) {
            $(this).val("0.00").trigger("input");
        }
    });
    
    $('#diametro_exterior_mm_cliente, #diametro_exterior_inch_cliente').on("blur", function() {
        let de = $(this).val().trim();
        if (de === "" || isNaN(de)) {
            $(this).val("0.00").trigger("input");
        }
    });
    
    $('#altura_mm_cliente, #altura_inch_cliente').on("blur", function() {
        let a = $(this).val().trim();
        if (a === "" || isNaN(a)) {
            $(this).val("0.00").trigger("input");
        }
    });

    // EVENTO CUANDO EL USUARIO CAMBIA LA ALTURA DE CAJA
    setTimeout(() => {
        $(`#inputAlturaCaja_m${window.esWiper}`).on("input", function(){
            if(window.perfilSello.includes("A03") || window.perfilSello.includes("A06")) {
                console.log("es wiper en el dos, y es un a03 o a06");
                // esto esta bien porque la altura de caja es la misma que la altura siempre en el material 1
                $(`#altura_mm_m1`).val($("#altura_mm_cliente").val());
            }
        });
    }, 1000);

    // WIPER ESPECIAL QUESTION ICON MODAL
    $("#questionIconSpecialWiper").on("click", function(){
        const modal = new bootstrap.Modal(document.getElementById('modalSpecialWiper'));
        modal.show();
    });

    // ESCUCHAR EL CLICK DE COMPLETAR UN MATERIAL
    $("#btnListo_m1, #btnListo_m2, #btnListo_m3, #btnListo_m4, #btnListo_m5").on("click", function(){
        MaterialesCompletados += 1;
        habilitarCotizacion(MaterialesCompletados);
    });

    // ESCUCHAR MaterialesCompletados DE HABILITAR EDICION - resetear total total
    $("#btnNoListo_m1, #btnNoListo_m2, #btnNoListo_m3, #btnNoListo_m4, #btnNoListo_m5").on("click", function(){
        MaterialesCompletados-=1;
        habilitarCotizacion(MaterialesCompletados);
        $("#inputTotalCotizacion, #inputIVA1").val("");
        $("#inputTotalCotizacion, #inputIVA1").attr("placeholder", "Pendiente");
        $("#inputTotalCotizacion, #inputIVA1").removeClass("glow-effect");
        $("#btnCotizar").removeClass("d-none");
        $("#btnPrevisualizar").addClass("d-none");
    });
    
    // ESCUCHAR CLICK EN COTIZAR PARA SUMAR LOS TOTALES
    $("#btnCotizar").on("click", function(){
        let totalM1 = parseFloat($("#inputTotalMaterial_m1").val()) || 0;  // Convierte a número, y usa 0 si es NaN
        let totalM2 = parseFloat($("#inputTotalMaterial_m2").val()) || 0;
        let totalM3 = parseFloat($("#inputTotalMaterial_m3").val()) || 0; 
        let totalM4 = parseFloat($("#inputTotalMaterial_m4").val()) || 0; 
        let totalM5 = parseFloat($("#inputTotalMaterial_m5").val()) || 0; 
 
        console.log("Total material 1 = ", totalM1);
        console.log("Total material 2 = ", totalM2);
        console.log("Total material 3 = ", totalM3);
        console.log("Total material 4 = ", totalM4);
        console.log("Total material 5 = ", totalM5);

        let totalFinal = totalM1 + totalM2 + totalM3 + totalM4 + totalM5;
        console.log("Total de totales sin iva = ", totalFinal.toFixed(2));
        // Calcular el 16% de IVA
        let iva = totalFinal * 0.16;
        // Mostrar el IVA en los inputs #inputIVA1 y #inputIVA2
        $("#inputIVA1, #inputIVA2").val(iva.toFixed(2));
        console.log("IVA = ", iva.toFixed(2));
        // Sumar el IVA al total
        let totalConIva = totalFinal + iva;
        // Actualizar los campos con el total con IVA
        $("#inputTotalCotizacion, #inputTotalCotizacion2").val(totalConIva.toFixed(2));
        console.log("Total con iva = ", totalConIva.toFixed(2));

        $("#btnPrevisualizar").removeClass("d-none");

        // Mostrar total con animación
        $("#inputTotalCotizacion").addClass("glow-effect");
    });

    // ESCUCHAR EL BOTON PREVISUALIZAR
    $("#btnPrevisualizar").on("click", function () {
        for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
            // Obtener valores
            let material = $(`#selectorMaterial_m${i}`).val();
            let proveedor = $(`#selectorProveedor_m${i}`).val();
            let cantidad = $(`#inputCantidad_m${i}`).val();
            let billets = $(`#inputBilletsString_m${i}`).val();
            let descuentoCliente = $(`#inputDescuentoCliente_m${i}`).val();
            let descuentoRC = $(`#inputDescuentoRC_m${i}`).val();
            let descuentoMayoreo = $(`#inputDescuentoMayoreo_m${i}`).val();
            let totalUnitarios = $(`#inputTotalUnitarios_m${i}`).val();
            let totalDescuentos = $(`#inputTotalDescuentos_m${i}`).val();
            let totalMaterial = $(`#inputTotalMaterial_m${i}`).val();
    
            let billetsFormateados = billets.replace(/,/g, '\n');

            // Asignar valores
            $(`#inputTablaMaterialM${i}`).val(material);
            $(`#inputTablaProveedorM${i}`).val(proveedor);
            $(`#inputTablaCantidadM${i}`).val(cantidad);
            $(`#inputTablaClaveM${i}`).val(billetsFormateados);
            
            $(`#inputTablaDescuentoClienteM${i}`).text(descuentoCliente);
            $(`#inputTablaDescuentoCantidadM${i}`).text(descuentoRC);
            $(`#inputTablaDescuentoMayoreoM${i}`).text(descuentoMayoreo);
            
            $(`#inputTablaTotalUnitariosM${i}`).val(totalUnitarios);
            $(`#inputTablaTotalDescuentosM${i}`).val(totalDescuentos);
            $(`#inputTablaTotalM${i}`).val(totalMaterial);
        }
    
        // animación para ocultar formularios        
        $('#sectionSelectorCliente, #sectionDimensionesSello').fadeOut(500);
        for (let i = 1; i <= 5; i++) {
            $(`#sectionContainerMaterial_m${i}`).fadeOut(500);
        }
        $('#sectionCotizar').fadeOut(500);
        $('#sectionTotalFinal').fadeOut(500);
   
        setTimeout(function () {
            $("#sectionPrevisualizar").removeClass("d-none").addClass("d-flex");
            // $("#inputVendedor").focus();
            $("#inputVendedor").trigger("input");
        }, 600);                
        setTimeout(() => {
            $("html, body").animate({
                scrollTop: $("#sectionPrevisualizar").offset().top
            }, 100);        
        }, 800);            

        $("#inputIdCotizacion").val(idCotizacion);
        $(".id-cotizacion").val(idCotizacion);

        let valueCliente = $("#selectorCliente").val();
        $("#spanCliente").text(valueCliente);

        let diResultante = parseFloat($('#diametro_interior_mm_cliente').val()).toFixed(2);
        let deResultante = parseFloat($('#diametro_exterior_mm_cliente').val()).toFixed(2);
        let aResultante = parseFloat($('#altura_mm_cliente').val()).toFixed(2);  
        let aCajaResultante = parseFloat($(`#inputAlturaCaja`).val()).toFixed(2);
        let aEscalon = parseFloat($(`#inputAlturaEscalon`).val()).toFixed(2);
        let aH2 = parseFloat($(`#inputAlturaH2`).val()).toFixed(2);
        let aH3 = parseFloat($(`#inputAlturaH3`).val()).toFixed(2);

        $("#spanDimensiones").text(`${diResultante}/${deResultante}/${aResultante}`);
        if(window.esWiper !== "0" && window.conEscalon === "0"){
            $("#spanDimensiones2").text(`H caja: ${aCajaResultante}`);
        }
        if(window.conEscalon !== "0"){
            $("#spanDimensiones2").text(`H caja: ${aCajaResultante}, H escalon: ${aEscalon}`);
        }
        if(window.esWisperEspecial !== "0"){
             $("#spanDimensiones2").text(`H caja: ${aCajaResultante}, H2: ${aH2}, H3: ${aH3}`);
        }
        window.unirStringBillets();
    });
    
    // ESCUCHAR EL BOTON PREVISUALIZAR
    $("#btnContinuarEditando").on("click", function () {
        // Revertir animación: mostrar secciones
        $('#sectionSelectorCliente, #sectionDimensionesSello').fadeIn(1000);
        for (let i = 1; i <= 5; i++) {
            $(`#sectionContainerMaterial_m${i}`).fadeIn(1000);
        }
        $('#sectionCotizar').fadeIn(1000);
        $('#sectionTotalFinal').fadeIn(1000);

        // Ocultar la previsualización
        $("#sectionPrevisualizar").removeClass("d-flex").addClass("d-none");

    });

    // INPUT NOMBRE DEL VENDEDOR, MISMO VALOR A TODOS
    $("#inputVendedor").on("input", function(){
        let elVendedor = $(this).val();
        $(".vendedor-input").val(elVendedor);
        if (elVendedor.trim() !== "") {
            habilitarBoton("#btnGuardarCotizacion");
            console.log("El nombre del vendedor es:", elVendedor);
        } else {
            disablarBoton("#btnGuardarCotizacion");
        }
    });

    // GUARDAR LA COTIZACION DESIDIENDO CUALES FORMULARIOS VA A ENVIAR
    $("#btnGuardarCotizacion").on("click", function () {
        let promises = [];

        for (let i = 1; i <= 5; i++) {
            if ($(`#seraEnviado_m${i}`).val() === "si") {
                promises.push(pushPromise(i)); 
            }
        }

        Promise.all(promises).then((resultados) => {
        const hayErrores = resultados.some(r => !r.ok);

            if (hayErrores) {
                Swal.fire({
                    title: 'Ocurrió un problema',
                    text: 'Hubo un error al guardar alguno de los materiales. Si el problema persiste, contacte el área de sistemas.',
                    icon: 'error',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#55AD9B',
                });
            } else {
                Swal.fire({
                    title: 'Proceso exitoso',
                    text: 'Cotización guardada exitosamente.',
                    icon: 'success',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#55AD9B',
                }).then((result) => {
                    if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
                        //window.open("../includes/functions/generar_pdf.php?id_cotizacion=" + idCotizacion, "_blank");
                        window.location.href = "cotizaciones.php";

                        $.post("../ajax/ajax_notificacion.php", {
                            mensaje: "Se ha generado una cotización: " + idCotizacion
                        }).done(function (response) {
                            console.log("Notificación enviada:", response);
                        }).fail(function (error) {
                            console.error("Error al enviar notificación:", error);
                        });
                    }
                });
            }
        });
    });

    Swal.fire({
      title: 'Informacion importante',
      text: 'Actualmente se estan realizando pruebas acerca de la validación de tolerancias y limitantes de dimensiones. No se asegura la precison de las mismas.',
      icon: 'info',
      confirmButtonText: 'Entendido',
      width: '400px',  // Tamaño pequeño del modal
      padding: '10px',  // Relleno para que se vea agradable
      position: 'bottom-end', // Coloca el modal en la esquina superior derecha (puedes cambiarlo)
      toast: true, // Mostrar como un "toast", que es una notificación pequeña
      timer: 5300, // El modal desaparece automáticamente después de 5 segundos (opcional)
      showConfirmButton: true // Mostrar el botón de confirmación
  });
});