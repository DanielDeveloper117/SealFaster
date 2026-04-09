// ============================================================
// VARIABLES GLOBALES
// ============================================================
console.log("CANTIDAD DE MATERIALES: ", window.CANTIDAD_MATERIALES);
window.FAMILIA_PERFIL = "";
window.CON_RESORTE = "0";
window.WIPER_EN = "0";
window.ESCALON_EN = "0";
window.WIPER_ESPECIAL_EN = "0";

window.DI_TOLERANCIA_DEFAULT = 3.00;
window.DE_TOLERANCIA_DEFAULT = 3.00;

window.MULTIPLO_RESORTE = 1.00;
window.BILLETS_SELECCIONADOS = [];

window.DI_CLIENTE = 0.00;
window.DE_CLIENTE = 0.00;

window.PORCENTAJE_H_ESCALON = 1.00;
window.PORCENTAJE_H_CAJA = 1.00;
window.PORCENTAJE_H_H2 = 1.00;
window.PORCENTAJE_H_H3 = 1.00;

window.PORCENTAJE_H_m1 = 1.00;
window.PORCENTAJE_H_m2 = 1.00;
window.PORCENTAJE_H_m3 = 1.00;
window.PORCENTAJE_H_m4 = 1.00;
window.PORCENTAJE_H_m5 = 1.00;

window.PORCENTAJE_DI_m1 = 1.00;
window.PORCENTAJE_DI_m2 = 1.00;
window.PORCENTAJE_DI_m3 = 1.00;
window.PORCENTAJE_DI_m4 = 1.00;
window.PORCENTAJE_DI_m5 = 1.00;

window.PORCENTAJE_DE_m1 = 1.00;
window.PORCENTAJE_DE_m2 = 1.00;
window.PORCENTAJE_DE_m3 = 1.00;
window.PORCENTAJE_DE_m4 = 1.00;
window.PORCENTAJE_DE_m5 = 1.00;

window.CLIENTE_SELECCIONADO = false;
window.TIPO_INVENTARIO = false;
window.DUREZA_SELECCIONADA = false;

window.TIPO_MEDIDA_DI_SELECCIONADA = false;
window.TIPO_MEDIDA_DE_SELECCIONADA = false;
window.TIPO_MEDIDA_H_SELECCIONADA = false;

window.DI_CLIENTE_DIGITADO = false;
window.DE_CLIENTE_DIGITADO = false;
window.H_CLIENTE_DIGITADO = false;

window.TIPO_INVENTARIO_STRING = "simulacion";

window.TIPO_MEDIDA_DI = "Metal";
window.TIPO_MEDIDA_DE = "Metal";
window.TIPO_MEDIDA_H = "Metal";

window.DIMENSIONES_VALIDAS = false;

window.MATERIALES_COMPLETADOS = 0;



// ============================================================
// FUNCIONES
// ============================================================
function habilitarBoton(elemento) {
    $(elemento).attr("disabled", false).removeClass("btn-disabled").addClass("btn-general");
}
function disablarBoton(elemento) {
    $(elemento).attr("disabled", true).removeClass("btn-general").addClass("btn-disabled");
}
function habilitarInput(elemento) {
    $(elemento).attr("disabled", false).removeClass("input-readonly").addClass("input-estimador").attr("placeholder", "");
}
function disablarInput(elemento) {
    $(elemento).attr("disabled", true).removeClass("form-control").addClass("input-readonly");
} 
// funcion para asignar el id de la cotizacion    
function idRandom(){
    const numeroAleatorio = Math.floor(10000000 + Math.random() * 90000000);
    return numeroAleatorio;
}

/**
 * Unites selected billets from all material sources dynamically.
 * Une los billets seleccionados de todas las fuentes de materiales dinámicamente.
 */
window.unirBilletsSeleccionados = function () {
    let result = [];
    // Iterate based on the global constant
    for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
        const key = `BILLETS_SELECCIONADOS_m${i}`;
        if (window[key]) {
            result.push(...window[key]);
        }
    }
    window.BILLETS_SELECCIONADOS = result;
};

/**
 * Unites selected billet lots from all material sources.
 * Une los lotes de billets seleccionados de todas las fuentes de materiales.
 */
window.unirStringBilletsLotes = function () {
    let result = [];
    for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
        const key = `BILLETS_SELECCIONADOS_LOTES_m${i}`;
        if (window[key]) {
            result.push(...window[key]);
        }
    }
    window.BILLETS_SELECCIONADOS_LOTES = result;
    console.log("STRING DE BILLETS LOTES: ", window.BILLETS_SELECCIONADOS_LOTES);
};

/**
 * Unites selected billet strings from all material sources.
 * Une los strings de billets seleccionados de todas las fuentes de materiales.
 */
window.unirStringBillets = function () {
    let result = [];
    for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
        const key = `BILLETS_SELECCIONADOS_STRING_m${i}`;
        if (window[key]) {
            result.push(...window[key]);
        }
    }
    window.BILLETS_SELECCIONADOS_STRING = result;
    console.log("STRING DE BILLETS: ", window.BILLETS_SELECCIONADOS_STRING);
};

function resetear_materiales_completados(){
    window.MATERIALES_COMPLETADOS=0;
    for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
        if($(`#checkboxOmitirElemento_m${i}`).is(':checked')){
            window.MATERIALES_COMPLETADOS += 1;
        }else{
            window.MATERIALES_COMPLETADOS += 1;
            $(`#btnNoListo_m${i}`).trigger("click");
            $(`#btnAtras_m${i}`).trigger("click");
        }
    }
    console.log("Materiales completados reseteados: ", window.MATERIALES_COMPLETADOS, "/", window.CANTIDAD_MATERIALES);
    habilitarCotizacion();
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
// FUNCION DE CALCULO DE DIMENSIONES DE MATERIALES PARA SELECCION DE BILLETS
/**
 * Calcula y asigna las dimensiones (DI, DE, H) de cada material del perfil
 * a partir de las dimensiones del cliente.
 *
 * Notas:
 * - Los porcentajes de DI/DE por material vienen de window.PORCENTAJE_DI_m{i} y
 *   window.PORCENTAJE_DE_m{i}, definidos en la inicializacion o desde el AJAX del perfil.
 * - Los calculos de altura de caja, escalon, H2 y H3 estan preparados pero
 *   sus asignaciones al DOM estan comentadas: el usuario los ingresa manualmente.
 *   No eliminar los comentarios para conservar la logica de calculo, que se puede reactivar facilmente en el futuro.   
 * @param {number} clienteDI - Diametro interior del sello en mm (ingresado por el cliente)
 * @param {number} clienteDE - Diametro exterior del sello en mm
 * @param {number} clienteH  - Altura del sello en mm
 */
function autoCalculoDimensiones(clienteDI, clienteDE, clienteH) {

    const PERFILES_DI_INVERSO = ['K22', 'K32', 'S25', 'S32'];
    const PERFILES_DE_INVERSO = ['S22', 'K32', 'S25', 'S32'];
    const anchoSello = clienteDE - clienteDI;

    // ************************************************************
    // PRECALCULO SOLO PARA ALTURA (logica de redistribucion valida)
    let sumaPorcentajesActivosH  = 0.0;
    let sumaPorcentajesOmitidosH = 0.0;
    let cantidadActivosH         = 0;
    let hayMaterialEnvelopeH     = false; // algun activo tiene porcentajeH >= 1.00

    for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
        const pH       = parseFloat(window[`PORCENTAJE_H_m${i}`]) || 0.0;
        const omitidoH = $(`#checkboxOmitirElemento_m${i}`).is(':checked');

        if (omitidoH) {
            sumaPorcentajesOmitidosH += pH;
        } else {
            sumaPorcentajesActivosH += pH;
            cantidadActivosH++;
            if (pH >= 1.0) {
                hayMaterialEnvelopeH = true;
            }
        }
    }

    if (sumaPorcentajesActivosH === 0) {
        sumaPorcentajesActivosH = 1.0;
    }

    // Consecutivos: suma de activos <= 1.00 → normalizar (suma H = clienteH)
    // Solapados:    suma de activos >  1.00 → distribuir porcentaje omitido en partes iguales
    const sonConsecutivosH = sumaPorcentajesActivosH <= 1.0;

    // Extra a sumar a cada material activo en caso de solapamiento sin envelope:
    //   suma_omitidos / cantidad_activos
    // Si hay envelope (algun activo tiene p >= 1.00) el extra es 0 y se
    // respetan los porcentajes originales porque ese material ya representa
    // la altura total del cliente y no puede crecer mas.
    const extraPorMaterialH = (!sonConsecutivosH && !hayMaterialEnvelopeH && cantidadActivosH > 0)
        ? sumaPorcentajesOmitidosH / cantidadActivosH
        : 0.0;

    // ============================================================
    // PRECALCULO DIAMETROS: herencia de porcentaje 1.0

    /**
     * Calcula que materiales activos heredan el envelope (p=1.0)
     * para un eje dado cuando el componente que lo tenia fue omitido.
     *
     * @param {string} eje - "DI" o "DE"
     * @returns {Set<number>} indices de materiales activos que deben
     *                        usar el diametro del cliente para ese eje
     */
    function calcularHerenciaEnvelope(eje) {
        const clave = `PORCENTAJE_${eje}_m`;

        const activos  = [];
        const omitidos = [];
        for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
            const p       = parseFloat(window[`${clave}${i}`]) ?? 1.0;
            const omitido = $(`#checkboxOmitirElemento_m${i}`).is(':checked');
            if (omitido) {
                omitidos.push({ i, p });
            } else {
                activos.push({ i, p });
            }
        }

        const envelopeSet = new Set();

        // Si ya hay un activo con p=1.0, el envelope sigue presente
        // — marcarlo y no necesitar herencia
        const activosConEnvelope = activos.filter(a => a.p === 1.0);
        if (activosConEnvelope.length > 0) {
            activosConEnvelope.forEach(a => envelopeSet.add(a.i));
            return envelopeSet;
        }

        // Sin activo con envelope: verificar si algun omitido tenia p=1.0
        const omitidoTeniaEnvelope = omitidos.some(o => o.p === 1.0);
        if (!omitidoTeniaEnvelope || activos.length === 0) {
            // Nadie tenia envelope o no hay activos: sin herencia,
            // cada activo usara su porcentaje original
            return envelopeSet;
        }

        // El envelope fue omitido → heredar al activo con MENOR porcentaje
        // (el mas alejado del borde perdido, geometricamente el nuevo extremo)
        let minP = Infinity;
        activos.forEach(a => { if (a.p < minP) minP = a.p; });

        // Todos los activos que comparten ese minimo heredan
        // (pueden ser varios si tienen el mismo porcentaje)
        activos.filter(a => a.p === minP).forEach(a => envelopeSet.add(a.i));
        return envelopeSet;
    }

    const herenciaDI = calcularHerenciaEnvelope('DI');
    const herenciaDE = calcularHerenciaEnvelope('DE');

    // ************************************************************
    // ---- CALCULO POR COMPONENTE ----
    for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {

        const omitido = $(`#checkboxOmitirElemento_m${i}`).is(':checked');

        if (omitido) {
            $(`#diametro_interior_mm_m${i}`).val('0.00').trigger('input');
            $(`#diametro_exterior_mm_m${i}`).val('0.00').trigger('input');
            $(`#altura_mm_m${i}`).val('0.00').trigger('input');
            continue;
        }

        // ---- ALTURA ----
        // Caso 1 - Consecutivos (suma_activos <= 1.00):
        //   normalizar → H_i = clienteH * (p_i / suma_activos)
        //   garantiza que la suma de alturas activas = clienteH
        //
        // Caso 2 - Solapados sin envelope (suma_activos > 1.00, ningun p >= 1.00):
        //   H_i = clienteH * (p_i + extra)
        //   donde extra = suma_omitidos / cantidad_activos
        //   el porcentaje omitido se reparte en partes iguales entre los activos
        //
        // Caso 3 - Solapados con envelope (suma_activos > 1.00, algun p >= 1.00):
        //   extra = 0 → H_i = clienteH * p_i (porcentajes originales intactos)
        //   el material envelope siempre representa la altura total del cliente
        const porcentajeH = parseFloat(window[`PORCENTAJE_H_m${i}`]) || 1.0;
        let autoH;

        if (sonConsecutivosH) {
            autoH = clienteH * (porcentajeH / sumaPorcentajesActivosH);
        } else {
            autoH = clienteH * (porcentajeH + extraPorMaterialH);
        }

        // ---- DIAMETRO INTERIOR ----
        let autoDI;
        if (herenciaDI.has(i)) {
            // Hereda (o ya tenia) el borde interior → DI del cliente exacto
            autoDI = clienteDI;
        } else {
            const porcentajeDI = parseFloat(window[`PORCENTAJE_DI_m${i}`]) ?? 1.0;
            const pmmDI        = anchoSello * porcentajeDI;
            //const esInverso    = PERFILES_DI_INVERSO.some(p => window.PERFIL_SELLO.includes(p));
            //autoDI = esInverso ? clienteDI - pmmDI : clienteDI + pmmDI;
            autoDI = clienteDI + pmmDI;
        }

        // ---- DIAMETRO EXTERIOR ----
        let autoDE;
        if (herenciaDE.has(i)) {
            // Hereda (o ya tenia) el borde exterior → DE del cliente exacto
            autoDE = clienteDE;
        } else {
            const porcentajeDE = parseFloat(window[`PORCENTAJE_DE_m${i}`]) ?? 1.0;
            const pmmDE        = anchoSello * porcentajeDE;
            //const esInverso    = PERFILES_DE_INVERSO.some(p => window.PERFIL_SELLO.includes(p));
            //autoDE = esInverso ? clienteDE + pmmDE : clienteDE - pmmDE;
            autoDE = clienteDE - pmmDE;
        }

        // ---- CASO ESPECIAL R13 y R16----
        if (window.PERFIL_SELLO.includes('R13') || window.PERFIL_SELLO.includes('R16')) {
            const deOring = clienteDI + (clienteH * 2);
            $('#diametro_exterior_mm_cliente').val(deOring.toFixed(2));
            $('#diametro_exterior_inch_cliente').val((deOring / 25.4).toFixed(4));
            autoDE = deOring;
        }

        // ---- ASIGNACION AL DOM ----
        $(`#diametro_interior_mm_m${i}`).val(autoDI.toFixed(2)).trigger('input');
        $(`#diametro_exterior_mm_m${i}`).val(autoDE.toFixed(2)).trigger('input');
        $(`#altura_mm_m${i}`).val(autoH.toFixed(2)).trigger('input');
        $(`#calculoTeoricoDI_m${i}`).text(autoDI.toFixed(2));
        $(`#calculoTeoricoDE_m${i}`).text(autoDE.toFixed(2));
        $(`#calculoTeoricoH_m${i}`).text(autoH.toFixed(2));
        console.log("material ", i, ": % DI =", parseFloat(window[`PORCENTAJE_DI_m${i}`]), ", DI =", autoDI.toFixed(2), herenciaDI.has(i) ? "(heredado)" : "");
        console.log("material ", i, ": % DE =", parseFloat(window[`PORCENTAJE_DE_m${i}`]), ", DE =", autoDE.toFixed(2), herenciaDE.has(i) ? "(heredado)" : "");
        //console.log("material ", i, ": % = ", porcentajeH, ", H = ", autoH.toFixed(2));
    }

    // ---- ALTURAS COMPLEMENTARIAS (wipers) ----
    const esA03oA06 = window.PERFIL_SELLO.includes('A03') || window.PERFIL_SELLO.includes('A06');
    const idxWiper  = window.WIPER_EN;
    const alturaBaseWiper = esA03oA06
        ? clienteH
        : parseFloat($(`#altura_mm_m${idxWiper}`).val()) || 0.0;

    if (idxWiper != '0') {
        // const autoAlturaCaja     = alturaBaseWiper * window.PORCENTAJE_H_CAJA;
        // const autoAlturaCajaInch = autoAlturaCaja / 25.4;
        // $(`#inputAlturaCaja_m${idxWiper}`).val(autoAlturaCaja.toFixed(2));
        // $(`#inputAlturaCajaInch_m${idxWiper}`).val(autoAlturaCajaInch.toFixed(4));
        // $('#inputAlturaCaja').val(autoAlturaCaja.toFixed(2));
        // $('#inputAlturaCajaInch').val(autoAlturaCajaInch.toFixed(4));
    } else {
        $('#inputAlturaCaja').val('0.00');
        $('#inputAlturaCajaInch').val('0.0000');
    }

    if (window.ESCALON_EN != '0' && window.WIPER_ESPECIAL_EN == '0') {
        // const autoAlturaEscalon     = alturaBaseWiper * window.PORCENTAJE_H_ESCALON;
        // const autoAlturaEscalonInch = autoAlturaEscalon / 25.4;
        // $(`#inputAlturaEscalon_m${window.ESCALON_EN}`).val(autoAlturaEscalon.toFixed(2));
        // $(`#inputAlturaEscalonInch_m${window.ESCALON_EN}`).val(autoAlturaEscalonInch.toFixed(4));
        // $('#inputAlturaEscalon').val(autoAlturaEscalon.toFixed(2));
        // $('#inputAlturaEscalonInch').val(autoAlturaEscalonInch.toFixed(4));
    } else {
        $('#inputAlturaEscalon').val('0.00');
        $('#inputAlturaEscalonInch').val('0.0000');
    }

    if (window.WIPER_ESPECIAL_EN != '0') {
        // const autoH2     = alturaBaseWiper * window.PORCENTAJE_H_H2;
        // const autoH3     = alturaBaseWiper * window.PORCENTAJE_H_H3;
        // const autoH2Inch = autoH2 / 25.4;
        // const autoH3Inch = autoH3 / 25.4;
        // $(`#inputAlturaH2_m${window.WIPER_ESPECIAL_EN}`).val(autoH2.toFixed(2));
        // $(`#inputAlturaH2Inch_m${window.WIPER_ESPECIAL_EN}`).val(autoH2Inch.toFixed(4));
        // $(`#inputAlturaH3_m${window.WIPER_ESPECIAL_EN}`).val(autoH3.toFixed(2));
        // $(`#inputAlturaH3Inch_m${window.WIPER_ESPECIAL_EN}`).val(autoH3Inch.toFixed(4));
        // $('#inputAlturaH2').val(autoH2.toFixed(2));
        // $('#inputAlturaH2Inch').val(autoH2Inch.toFixed(4));
        // $('#inputAlturaH3').val(autoH3.toFixed(2));
        // $('#inputAlturaH3Inch').val(autoH3Inch.toFixed(4));
    } else {
        $('#inputAlturaH2').val('0.00');
        $('#inputAlturaH2Inch').val('0.0000');
        $('#inputAlturaH3').val('0.00');
        $('#inputAlturaH3Inch').val('0.0000');
    }
}

function obtenerHerramientaSegunDimensiones(limitantesHerramientas, dureza, DI_R, DE_R, ALTURA_R, SECCION) {
    const herramientas = limitantesHerramientas[dureza];
    for (const numHerramienta in herramientas) {
        const lim = herramientas[numHerramienta];
        if (DI_R >= lim.DI_MIN && DI_R <= lim.DI_MAX &&
            DE_R >= lim.DE_MIN && DE_R <= lim.DE_MAX &&
            SECCION >= lim.SECCION_MIN && SECCION <= lim.SECCION_MAX &&
            ALTURA_R >= lim.H_MIN && ALTURA_R <= lim.H_MAX) {
            return { numHerramienta, limitante: lim };
        }
    }
    return null;
}
// VALIDAR LAS DIMENSIONES
function validarCamposDimensiones() {

    let valores = [
        $("#altura_mm_cliente").val(),
        $("#diametro_interior_mm_cliente").val(),
        $("#diametro_exterior_mm_cliente").val(),
        $("#inputAlturaCaja").val() || "0.00",
        $("#inputAlturaEscalon").val() || "0.00",
        $("#inputAlturaH2").val() || "0.00",
        $("#inputAlturaH3").val() || "0.00",
    ];

    let tipoDurezaMateriales = $("#selectorDurezaMateriales").val();
    let DI_R     = valores[1] || 0.00;
    let DE_R     = valores[2] || 0.00;
    let ALTURA_R = valores[0] || 0.00;
    let SECCION  = (parseFloat(DE_R) - parseFloat(DI_R)) / 2;

    // ---- VALIDACIONES BASICAS PRIMERO ----
    if (parseFloat(ALTURA_R) == 0 || parseFloat(ALTURA_R) < 0 || parseFloat(DE_R) == 0 || parseFloat(DE_R) < 0) {
        $("#containerErrorDimensiones_cliente span").css("color", "#ff0400de");
        $("#containerErrorDimensiones_cliente span").text('La altura y el DE no puede ser 0');
        return false;
    }
    if (parseFloat(DI_R) >= parseFloat(DE_R)) {
        $("#containerErrorDimensiones_cliente span").css("color", "#ff0400de");
        $("#containerErrorDimensiones_cliente span").text('El DI no puede ser mayor o igual al DE');
        return false;
    }
    let hayValoresInvalidos = valores.some(function(valor) {
        console.log("Valor a validar: ", valor);
        return valor === null || valor === "" || isNaN(valor) || valor === ".";
    });
    if (hayValoresInvalidos) {
        $("#containerErrorDimensiones_cliente span").css("color", "#ff0400de");
        $("#containerErrorDimensiones_cliente span").text('Ingrese las dimensiones solicitadas correctamente');
        if (window.WIPER_EN != "0") {
            $(`#containerErrorDimensiones_m${window.WIPER_EN} span`).text('Ingrese las dimensiones solicitadas correctamente');
            window[`DIMENSIONES_VALIDAS_m${window.WIPER_EN}`] = false;
        }
        window.DIMENSIONES_VALIDAS = false;
        return false;
    }

    // ---- VALIDACIONES DE WIPER ----
    if (window.WIPER_EN != "0") {
        if (parseFloat(valores[3]) > parseFloat(ALTURA_R)) {
            $("#containerErrorDimensiones_cliente span").text('Altura de caja no debe ser mayor a la total');
            $(`#containerErrorDimensiones_m${window.WIPER_EN} span`).text('Altura de caja no debe ser mayor a la total');
            window[`DIMENSIONES_VALIDAS_m${window.WIPER_EN}`] = false;
            return false;
        }
        if (parseFloat(valores[3]) <= 0 || isNaN(parseFloat(valores[3]))) {
            $("#containerErrorDimensiones_cliente span").text('Medida de altura de caja no valida');
            $(`#containerErrorDimensiones_m${window.WIPER_EN} span`).text('Medida de altura de caja no valida');
            window[`DIMENSIONES_VALIDAS_m${window.WIPER_EN}`] = false;
            return false;
        }
    }

    // ---- VALIDACIONES DE ESCALON ----
    if (window.ESCALON_EN != "0" && window.WIPER_ESPECIAL_EN == "0") {
        if ((parseFloat(valores[3]) > parseFloat(valores[4])) || (parseFloat(valores[4]) > parseFloat(ALTURA_R))) {
            $("#containerErrorDimensiones_cliente span").css("color", "#ff0400de");
            $("#containerErrorDimensiones_cliente span").text('Altura caja + escalón no puede ser menor a la altura de caja o mayor a la total');
            $(`#containerErrorDimensiones_m${window.ESCALON_EN} span`).text('Altura escalón no puede ser menor a la altura de caja o mayor a la total');
            window[`DIMENSIONES_VALIDAS_m${window.ESCALON_EN}`] = false;
            return false;
        }
        if (parseFloat(valores[4]) <= 0 || isNaN(parseFloat(valores[4]))) {
            $("#containerErrorDimensiones_cliente span").css("color", "#ff0400de");
            $("#containerErrorDimensiones_cliente span").text('Medida de altura de escalón no valida');
            $(`#containerErrorDimensiones_m${window.WIPER_EN} span`).text('Medida de altura de escalón no valida');
            window[`DIMENSIONES_VALIDAS_m${window.WIPER_EN}`] = false;
            return false;
        }
    }

    // ---- VALIDACIONES DE WIPER ESPECIAL H2 Y H3 ----
    if (window.WIPER_ESPECIAL_EN != "0") {
        let H2MasH3 = parseFloat(valores[5]) + parseFloat(valores[6]);
        if ((parseFloat(valores[5]) > parseFloat(valores[3])) || (parseFloat(valores[5]) > parseFloat(ALTURA_R)) ||
            (parseFloat(valores[6]) > parseFloat(valores[3])) || (parseFloat(valores[6]) > parseFloat(ALTURA_R))) {
            $("#containerErrorDimensiones_cliente span").css("color", "#ff0400de");
            $("#containerErrorDimensiones_cliente span").text('Altura H2 y H3 no puede ser mayor a la altura de caja o mayor a la total');
            $(`#containerErrorDimensiones_m${window.WIPER_ESPECIAL_EN} span`).text('Altura H2 y H3 no puede ser mayor a la altura de caja o mayor a la total');
            window[`DIMENSIONES_VALIDAS_m${window.WIPER_ESPECIAL_EN}`] = false;
            return false;
        }
        if (H2MasH3 > parseFloat(valores[3]) || H2MasH3 > parseFloat(ALTURA_R)) {
            $("#containerErrorDimensiones_cliente span").css("color", "#ff0400de");
            $("#containerErrorDimensiones_cliente span").text('La suma de H2 y H3 no puede ser mayor a la altura de caja o mayor a la total');
            $(`#containerErrorDimensiones_m${window.WIPER_ESPECIAL_EN} span`).text('La suma de H2 y H3 no puede ser mayor a la altura de caja o mayor a la total');
            window[`DIMENSIONES_VALIDAS_m${window.WIPER_ESPECIAL_EN}`] = false;
            return false;
        }
        if (parseFloat(valores[5]) <= 0 || isNaN(parseFloat(valores[5]))) {
            $("#containerErrorDimensiones_cliente span").css("color", "#ff0400de");
            $("#containerErrorDimensiones_cliente span").text('Medida de altura H2 no valida');
            $(`#containerErrorDimensiones_m${window.WIPER_EN} span`).text('Medida de altura H2 no valida');
            window[`DIMENSIONES_VALIDAS_m${window.WIPER_EN}`] = false;
            return false;
        }
        if (parseFloat(valores[6]) <= 0 || isNaN(parseFloat(valores[6]))) {
            $("#containerErrorDimensiones_cliente span").css("color", "#ff0400de");
            $("#containerErrorDimensiones_cliente span").text('Medida de altura H3 no valida');
            $(`#containerErrorDimensiones_m${window.WIPER_EN} span`).text('Medida de altura H3 no valida');
            window[`DIMENSIONES_VALIDAS_m${window.WIPER_EN}`] = false;
            return false;
        }
    }

    // ---- VALIDACION DE HERRAMIENTA (al final, con dimensiones ya validadas) ----
    if (tipoDurezaMateriales == "duros" && window.PERFIL_SELLO == "R16") {
        $("#containerErrorDimensiones_cliente span").css("color", "#ff0400de");
        $("#containerErrorDimensiones_cliente span").text('No es posible maquinar con materiales duros.');
        return false;
    }

    if (window.LIMITANTES_PERFIL && (tipoDurezaMateriales == "blandos" || tipoDurezaMateriales == "duros")) {

        const resultado = obtenerHerramientaSegunDimensiones(
            window.LIMITANTES_PERFIL, tipoDurezaMateriales,
            DI_R, DE_R, ALTURA_R, SECCION
        );

        if (!resultado) {
            let mensajeTecnico = "No se encontró herramienta para maquinar tales dimensiones.<br>";
            mensajeTecnico += `Material: ${tipoDurezaMateriales}<br>`;
            mensajeTecnico += `Dimensiones dadas: DI=${DI_R}, DE=${DE_R}, Seccion=${SECCION}, H=${ALTURA_R}<br><br>`;
            mensajeTecnico += "Rangos de herramientas disponibles:<br>";

            const herramientas = window.LIMITANTES_PERFIL[tipoDurezaMateriales];
            for (const numHerramienta in herramientas) {
                const lim = herramientas[numHerramienta];
                mensajeTecnico += `Herramienta ${numHerramienta}: `;
                mensajeTecnico += `DI [${lim.DI_MIN}-${lim.DI_MAX}], `;
                mensajeTecnico += `DE [${lim.DE_MIN}-${lim.DE_MAX}], `;
                mensajeTecnico += `Seccion [${lim.SECCION_MIN}-${lim.SECCION_MAX}], `;
                mensajeTecnico += `H [${lim.H_MIN}-${lim.H_MAX}]<br>`;
            }
            $("#containerErrorDimensiones_cliente span").css("color", "#ff0400de");
            $("#containerErrorDimensiones_cliente span").html(mensajeTecnico);
            window.DIMENSIONES_VALIDAS = false;
            return false;
        }

        const { numHerramienta, limitante } = resultado;
        let mensajeTecnicoValidadas = `Dimensiones validas.<br>`;
        mensajeTecnicoValidadas += `Herramienta a usar: ${numHerramienta}<br>`;
        mensajeTecnicoValidadas += `Rango de dimensiones permitido por esta herramienta:<br>`;
        mensajeTecnicoValidadas += `DI [${limitante.DI_MIN}-${limitante.DI_MAX}], `;
        mensajeTecnicoValidadas += `DE [${limitante.DE_MIN}-${limitante.DE_MAX}], `;
        mensajeTecnicoValidadas += `H [${limitante.H_MIN}-${limitante.H_MAX}], `;
        mensajeTecnicoValidadas += `Seccion [${limitante.SECCION_MIN}-${limitante.SECCION_MAX}]`;
        $("#containerErrorDimensiones_cliente span").css("color", "#28a745");
        $("#containerErrorDimensiones_cliente span").html(mensajeTecnicoValidadas);
    }

    // ---- LIMPIAR ERRORES DE CONTENEDORES SECUNDARIOS ----
    $(`#containerErrorDimensiones_m${window.WIPER_EN} span`).text('');
    $(`#containerErrorDimensiones_m${window.ESCALON_EN} span`).text('');
    $(`#containerErrorDimensiones_m${window.WIPER_ESPECIAL_EN} span`).text('');

    // ---- MENSAJE FINAL SI NO TIENE LIMITANTES DE HERRAMIENTA ----
    if (!window.TIENE_LIMITANTES) {
        $("#containerErrorDimensiones_cliente span").css("color", "#28a745");
        $("#containerErrorDimensiones_cliente span").text('Dimensiones validas');
    }

    window.DIMENSIONES_VALIDAS = true;
    return true;
}
// Funcion para saber si ya todos los materiales han sido completados
function habilitarCotizacion(){
    if(window.MATERIALES_COMPLETADOS == window.CANTIDAD_MATERIALES){
        habilitarBoton("#btnCotizar");
        $("#btnCotizar").text("Cotizar");
        console.log("Todos los materiales han sido completados");
    }else{
        disablarBoton("#btnCotizar");
        //$("#btnCotizar").text("Complete todos los materiales para cotizar");
        // console.log("Aun no estan completados todos los materiales", window.CANTIDAD_MATERIALES);
        //console.log("Materiales completados: ", window.MATERIALES_COMPLETADOS, "/",window.CANTIDAD_MATERIALES);  
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

        if(!tipoMedidaDE || tipoMedidaDE == null || tipoMedidaDE == ""){
            tipoMedidaDE = tipoMedidaDI;
        }

        let esSimulacion = 0;
        if(window.TIPO_INVENTARIO_STRING == "fisico"){
            esSimulacion = 0;
        }else{
            esSimulacion = 1;
        }
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
                '&de_sello_inch2=' + encodeURIComponent(deClienteSelloInch2) +
                '&simulacion=' + encodeURIComponent(esSimulacion),
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


// ============================================================
// EVENTOS DEL DOM
// ============================================================
$(document).ready(function() {

    // ============================================================
    // INICIALIZACION DE VARIABLES Y DEPENDIENTES DEL DOM
    // ============================================================
    // llamada ajax para obtener la informacion del PERFIL
    $.ajax({
        url: '../ajax/ajax_perfil.php',
        type: 'POST',
        data: { perfil: window.PERFIL_SELLO },
        dataType: 'json',
        success: function (data) {
            // ****** INFORMACION GENERAL ******
            if (data == false) {
                console.log("Perfil no encontrado. Respuesta: ", data.perfil);
            }else{
                console.log("Familia existe. Respuesta: ", data.familia_nombre);
                console.log("Perfil existe. Respuesta: ", data.perfil);
            }
            window.FAMILIA_PERFIL = data.familia_nombre + " ("+data.familia_nombre2+")";
            console.log(window.FAMILIA_PERFIL);
            window.DESCRIPCION_PERFIL = data.detalles;
            window.CON_RESORTE_EN = data.con_resorte_en;
            $(`#descripcionPerfil`).text(window.DESCRIPCION_PERFIL || "Sin detalles para este perfil");
            $(".familia-perfil").val(window.FAMILIA_PERFIL);
            if(window.CON_RESORTE_EN != "0"){
                console.log("Si tiene resorte");
            }else{
                console.log("No tiene resorte");
            }
            // decidir si quitar la opcion de todos los materiales
            $("#todosMaterialesOption").remove();

            if(window.PERFIL_SELLO.includes("R13") || window.PERFIL_SELLO.includes('R16')){
                $("#labelAlturaMM_cliente").text("Altura (H)/Sección radial");
            }    
            if(window.CON_RESORTE_EN != "0"){
                console.log("Si tiene resorte");
            }else{
                console.log("No tiene resorte");
            }

            window.TIENE_LIMITANTES  = data.tiene_limitantes;
            window.LIMITANTES_PERFIL = data.limitantes;

            if(window.TIENE_LIMITANTES){
                console.log("El perfil tiene limitantes de herramienta.", window.LIMITANTES_PERFIL);
            }else{
                console.log("El perfil no tiene limitantes de herramienta. ", window.LIMITANTES_PERFIL);
            }
            if(window.LIMITANTES_PERFIL){
                console.log("Limitantes del perfil: ", window.LIMITANTES_PERFIL);
            }else{
                console.log(window.LIMITANTES_PERFIL);
            }

            // ****** INFORMACION ESPECIAL PARA WIPERS ******
            window.WIPER_EN = data.es_wiper_en;
            window.ESCALON_EN = data.con_escalon_en;
            window.WIPER_ESPECIAL_EN = data.es_wiper_especial_en;
            if(window.WIPER_EN != "0"){
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

            if(window.ESCALON_EN != "0" && window.WIPER_ESPECIAL_EN == "0"){
                $(`#divAlturaEscalon, #containerWiperEscalon`).removeClass("d-none");
                console.log("Tiene escalon");
            }else{
                console.log("No tiene escalon");
            }

            if(window.WIPER_ESPECIAL_EN != "0"){
                //$(`#divAlturaH2, #divAlturaH3, #questionIconSpecialWiper`).removeClass("d-none");
                $(`#divAlturaH2, #divAlturaH3, #containerWiperEspecial`).removeClass("d-none");
                console.log("Es wisper especial");
            }else{
                console.log("No es wisper especial");
            }


            // ****** PORCENTAJES DE MATERIALES ******
            for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
                window[`PORCENTAJE_H_m${i}`]  = data.porcentajes.H[i]  ?? 1.0;
                window[`PORCENTAJE_DI_m${i}`] = data.porcentajes.DI[i] ?? 1.0;
                window[`PORCENTAJE_DE_m${i}`] = data.porcentajes.DE[i] ?? 1.0;
                console.log(`Porcentajes material ${i} - H: ${window[`PORCENTAJE_H_m${i}`]}, DI: ${window[`PORCENTAJE_DI_m${i}`]}, DE: ${window[`PORCENTAJE_DE_m${i}`]}`);
            }


            // ****** TOLERANCIAS DE DIAMETROS EN LA BARRA ******
            for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
                const tolDI = data.tolerancias.DI[i] ?? 4.00;
                const tolDE = data.tolerancias.DE[i] ?? 4.00;
                window[`DI_TOLERANCIA_m${i}`] = tolDI;
                window[`DE_TOLERANCIA_m${i}`] = tolDE;
                $(`#toleranciaBarraDI_m${i}`).text(tolDI);
                $(`#toleranciaBarraDE_m${i}`).text(tolDE);
                console.log(`Tolerancias material ${i} - DI: ${tolDI}, DE: ${tolDE}`);
            }


            // ****** ITERACIONES DE MATERIALES ******
            for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
                let imagen = $(`#imagenMaterial_m${i}`);
                let imagenUrl = imagen.attr('src');
                let img = new Image();
                img.src = imagenUrl;
            
                img.onload = function () {
                    $(`#imagenMaterialTabla_m${i}`).removeClass("d-none");
                    $(`#imagenMaterialTabla_m${i}`).attr("src", imagenUrl);
                    $(`#seraEnviado_m${i}`).val("si");
                };
                
            }  
            // ---- LISTENER DE CHECKBOXES DE OMISION DE MATERIALES ----
            // Se registra de forma dinamica al terminar de cargar los datos del perfil,
            // momento en que window.CANTIDAD_MATERIALES ya tiene el valor correcto.
            // Se coloca dentro del callback success del AJAX de ajax_perfil.php,
            // al final de ese bloque, despues de todas las asignaciones.

            for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
                $(`#checkboxOmitirElemento_m${i}`).on('change', function() {
                    const di = parseFloat($('#diametro_interior_mm_cliente').val()) || 0;
                    const de = parseFloat($('#diametro_exterior_mm_cliente').val()) || 0;
                    const h  = parseFloat($('#altura_mm_cliente').val()) || 0;
                    autoCalculoDimensiones(di, de, h);
                    resetear_materiales_completados();
                });
            }

            for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
                window[`BILLETS_SELECCIONADOS_m${i}`] = [];
                window[`BILLETS_SELECCIONADOS_LOTES_m${i}`] = [];
                window[`BILLETS_SELECCIONADOS_STRING_m${i}`] = [];
            }
            $("#overlay").addClass("d-none");
        },
        error: function (xhr, status, error) {
            $("#overlay").addClass("d-none");
            sweetAlertResponse('error', 'Error', 'Ocurrió un error al cargar datos del perfil. (' + error + ')', 'self');
            console.error('Error al realizar la petición AJAX', status, error);
        }
    });

    const idCotizacion = idRandom();

    $("#selectorDurezaMateriales").select2({
        width: "100%",
        minimumResultsForSearch: Infinity  // Esto desactiva completamente la búsqueda
    });
    $("#selectorTipoInventario").select2({
        width: "100%",
        minimumResultsForSearch: Infinity  // Esto desactiva completamente la búsqueda
    });
    $("#selectorTipoMedidaDI").select2({
        width: "100%",
        minimumResultsForSearch: Infinity  // Esto desactiva completamente la búsqueda
    });
    $("#selectorTipoMedidaDE").select2({
        width: "100%",
        minimumResultsForSearch: Infinity  // Esto desactiva completamente la búsqueda
    });
    $("#selectorTipoMedidaH").select2({
        width: "100%",
        minimumResultsForSearch: Infinity  // Esto desactiva completamente la búsqueda
    });

    const isUserFive = $("#isFive").val();
    if(isUserFive == "0"){
        // Inicializar Select2
        const $selector = $('#selectorCliente');
        const $select2Container = $selector.next('.select2-container');
        const $selectionElement = $select2Container.find('.select2-selection');

        // Agregar clase de loading
        $selectionElement.addClass('select2-loading');
        $("#selectorCliente").select2({
            placeholder: "Seleccione un cliente",
            allowClear: false,
            width: "100%",
            language: {
                noResults: function() {
                    return "No se encontraron coincidencias";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
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

                    //$chosen.removeClass('chosen-loading');
                    $selector.trigger('change.select2');
                    $selectionElement.removeClass('select2-loading');
                } else {
                }
            },
            error: function() {
                console.error('Error al realizar la petición AJAX');
            }
        });
    }else{
        setTimeout(() => {
            $selector.trigger('change.select2');
            $selectionElement.removeClass('select2-loading');
            console.log("se disparo el trigger");
        }, 1000);
    }
    $(`label`).css('pointer-events', 'none');
    // ============================================================
    // FIN DE INICIALIZACION DE VARIABLES Y DEPENDIENTES DEL DOM
    // ============================================================


    // EVENTO SELECCIONAR CLIENTE, PONE EL MISMO CLIENTE A TODOS LOS FORMULARIOS
    $("#selectorCliente").on("change", function(){
        const selectedText = $(this).find('option:selected').text();
        if (selectedText && selectedText != 'Seleccione un cliente') {
            // Efecto visual de confirmación
            const $chosen = $(this).next('.chosen-container');
            $chosen.addClass('selection-confirmed');
            
            setTimeout(() => {
                $chosen.removeClass('selection-confirmed');
            }, 600);

        } 

        if(window.CLIENTE_SELECCIONADO==false){
            window.CLIENTE_SELECCIONADO=true;
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
    // EVENTO CAMBIAR TIPO DE INVENTARIO, REINICIAR TODO
    $("#selectorTipoInventario").on("change", function(){
        if(window.TIPO_INVENTARIO==false){
            window.TIPO_INVENTARIO=true;
        }else{
            resetear_materiales_completados();
        }
        validarCamposDimensiones();
        
        window.TIPO_INVENTARIO_STRING = $(this).val();

        for(let i=1; i<=window.CANTIDAD_MATERIALES; i++){
            $(`#inputCantidad_m${i}`).trigger("input");
            
            if(window.TIPO_INVENTARIO_STRING == "fisico"){
                $(`#btnBilletsSimulacion_m${i}`).addClass("d-none");
                $(`#containerBarraSeleccionadaSimulacion_m${i}`).addClass("d-none");
                $(`#spanSimulacion`).addClass("d-none");
                $(`#btnBillets_m${i}`).removeClass("d-none");
                $(`#miniTableBillets_m${i}`).removeClass("d-none");
                $(`#containerFaltanSiNo_m${i}`).removeClass("d-none");
            }else{
                $(`#btnBilletsSimulacion_m${i}`).removeClass("d-none");
                $(`#containerBarraSeleccionadaSimulacion_m${i}`).removeClass("d-none");
                $(`#spanSimulacion`).removeClass("d-none");
                $(`#btnBillets_m${i}`).addClass("d-none");
                $(`#miniTableBillets_m${i}`).addClass("d-none");
                $(`#containerFaltanSiNo_m${i}`).addClass("d-none");
            }
        }
    }); 
    // EVENTO CAMBIAR DUREZA DE MATERIALES, REINICIAR TODO
    $("#selectorDurezaMateriales").on("change", function(){
        if(window.DUREZA_SELECCIONADA==false){
            window.DUREZA_SELECCIONADA=true;
        }else{
            resetear_materiales_completados();
        }
        validarCamposDimensiones();
        let durezaMateriales = $(this).val();
        const blandos = ["H-ECOPUR","ECOSIL","ECORUBBER 1","ECORUBBER 2","ECORUBBER 3","ECOPUR"];
        const duros = ["ECOTAL","ECOMID","ECOFLON 1","ECOFLON 2","ECOFLON 3"];
        const todosMateriales = [...blandos, ...duros];
        for(i=1; i<=window.CANTIDAD_MATERIALES; i++){
            $(`#inputCantidad_m${i}`).trigger("input");

            todosMateriales.forEach(element => {
                $(`#selectorMaterial_m${i}`).html(
                    `<option value="" disabled selected>Seleccione una opcion</option>`
                );
            });
            todosMateriales.forEach(element => {
                $(`#selectorMaterial_m${i}`).append(
                    `<option value="${element}">${element}</option>`
                );
            });
        }
    });  
    // AL SELECCIONAR LAS 3 COSAS MUESTRA LA SECCION DE DIMENSIONES
    $("#selectorCliente, #selectorTipoInventario, #selectorDurezaMateriales").on("change", function(){
        if(window.CLIENTE_SELECCIONADO===true && window.TIPO_INVENTARIO===true && window.DUREZA_SELECCIONADA===true){
            $(`#sectionDimensionesSello`).removeClass("d-none");
            
        }
    });
    // EVENTO CAMBIAR DE TIPO DE MEDIDA DEL DIAMETRO INTERIOR
    $("#selectorTipoMedidaDI").on("change", function(){
        let tipoMedidaDI = $(this).val();
        window.TIPO_MEDIDA_DI = tipoMedidaDI;
        console.log("tipo medida DI cambio a: ", window.TIPO_MEDIDA_DI);
        if(window.TIPO_MEDIDA_DI_SELECCIONADA==false){
            window.TIPO_MEDIDA_DI_SELECCIONADA=true;
            habilitarInput(`#diametro_interior_mm_cliente`);
            habilitarInput(`#diametro_interior_inch_cliente`);
            habilitarInput(`#diametro_interior_mm_cliente2`);
            habilitarInput(`#diametro_interior_inch_cliente2`);
            if(!window.PERFIL_SELLO.includes("R13") && !window.PERFIL_SELLO.includes("R16")){

            }
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
        if(window.PERFIL_SELLO.includes("R13") || window.PERFIL_SELLO.includes('R16')){
            window.TIPO_MEDIDA_DE_SELECCIONADA=true;
            window.DE_CLIENTE_DIGITADO = true;
            console.log("r13 detectado");
        }
        if(window.TIPO_MEDIDA_DE_SELECCIONADA===false){
            window.TIPO_MEDIDA_DE_SELECCIONADA=true;
            if(!window.PERFIL_SELLO.includes("R13") || window.PERFIL_SELLO.includes('R16')){
                habilitarInput(`#diametro_exterior_mm_cliente`);
                habilitarInput(`#diametro_exterior_inch_cliente`);
                habilitarInput(`#diametro_exterior_mm_cliente2`);
                habilitarInput(`#diametro_exterior_inch_cliente2`);
            }
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
        if(window.TIPO_MEDIDA_H_SELECCIONADA==false){
            window.TIPO_MEDIDA_H_SELECCIONADA=true;
            habilitarInput(`#altura_mm_cliente`);
            habilitarInput(`#altura_inch_cliente`);
            habilitarInput(`#altura_mm_cliente2`);
            habilitarInput(`#altura_inch_cliente2`);

            // para wispers
            habilitarBoton("#btnOtrasAlturas");
        }else{
            $("#altura_mm_cliente").trigger("input");
            resetear_materiales_completados();
        }
    });
    // AL SELECCIONAR LOS 3 TIPOS DE MEDIDA SE MUESTRAN LAS SECCIONES DE ELEMENTOS DEL SELLO
    $("#selectorTipoMedidaDI, #selectorTipoMedidaDE, #selectorTipoMedidaH, #altura_mm_cliente, #diametro_interior_mm_cliente, #diametro_exterior_mm_cliente, #altura_inch_cliente, #diametro_interior_inch_cliente, #diametro_exterior_inch_cliente").on("input change", function(){
        if(window.PERFIL_SELLO.includes("R13") || window.PERFIL_SELLO.includes('R16')){
            window.DE_CLIENTE_DIGITADO = true;
            window.TIPO_MEDIDA_DE_SELECCIONADA=true;
            console.log("r13 o r16 detectado");
        }
        setTimeout(() => {
            if(window.TIPO_MEDIDA_DI_SELECCIONADA===true 
                && window.TIPO_MEDIDA_DE_SELECCIONADA===true 
                && window.TIPO_MEDIDA_H_SELECCIONADA===true
                && window.DI_CLIENTE_DIGITADO===true
                && window.DE_CLIENTE_DIGITADO===true
                && window.H_CLIENTE_DIGITADO===true ){
                for(let i=1; i<=window.CANTIDAD_MATERIALES; i++){
                    $(`#sectionContainerMaterial_m${i}`).removeClass("d-none");
                }
                $(`#sectionCotizar`).removeClass("d-none");
            }
        }, 800);
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
        window.H_CLIENTE_DIGITADO=true;
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
        window.H_CLIENTE_DIGITADO=true;
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
        window.DI_CLIENTE_DIGITADO=true;
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
        window.DI_CLIENTE_DIGITADO=true;
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
        window.DE_CLIENTE_DIGITADO=true;
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
        window.DE_CLIENTE_DIGITADO=true;
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
        
        $(`#inputAlturaCaja_m${window.WIPER_EN}`).val($("#inputAlturaCaja").val());
        $(`#inputAlturaEscalon_m${window.ESCALON_EN}`).val($("#inputAlturaEscalon").val());
        $(`#inputAlturaH2_m${window.WIPER_ESPECIAL_EN}`).val($("#inputAlturaH2").val());
        $(`#inputAlturaH3_m${window.WIPER_ESPECIAL_EN}`).val($("#inputAlturaH3").val());
        let alturaCliente = parseFloat($('#altura_mm_cliente').val()) || 0.00;     
        
        autoCalculoDimensiones(window.DI_CLIENTE, window.DE_CLIENTE, alturaCliente);
        resetear_materiales_completados();
        if (!validarCamposDimensiones() || validarCamposDimensiones() == false || window["DIMENSIONES_VALIDAS_m" + window.WIPER_EN] == false) {
            disablarBoton(`#btnSiguiente_m${window.WIPER_EN}`);
            return; 
        }
        habilitarBoton(`#btnSiguiente_m${window.WIPER_EN}`);
        window["DIMENSIONES_VALIDAS_m" + window.WIPER_EN] = true;        

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
        $(`#inputAlturaCaja_m${window.WIPER_EN}`).on("input", function(){
            if(window.PERFIL_SELLO.includes("A03") || window.PERFIL_SELLO.includes("A06")) {
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
    // ESCUCHAR EL CHECK DE OMITIR ELEMENTO
    // for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
    // $(`#checkboxOmitirElemento_m${i}`).on('change', function() {

    // }                    
    // $("#checkboxOmitirElemento_m1, #checkboxOmitirElemento_m2, #checkboxOmitirElemento_m3, #checkboxOmitirElemento_m4, #checkboxOmitirElemento_m5").on("change", function(){
    //         if ($(this).is(':checked')) {
    //             window.MATERIALES_COMPLETADOS += 1;
    //             habilitarCotizacion();
    //         } else {
    //             window.MATERIALES_COMPLETADOS -= 1;
    //             habilitarCotizacion();
    //         }
    //     });
    // }
    // ESCUCHAR EL CLICK DE COMPLETAR UN MATERIAL
    $("#btnListo_m1, #btnListo_m2, #btnListo_m3, #btnListo_m4, #btnListo_m5").on("click", function(){
        window.MATERIALES_COMPLETADOS += 1;
        habilitarCotizacion();
    });

    // ESCUCHAR window.MATERIALES_COMPLETADOS DE HABILITAR EDICION - resetear total total
    $("#btnNoListo_m1, #btnNoListo_m2, #btnNoListo_m3, #btnNoListo_m4, #btnNoListo_m5").on("click", function(){
        window.MATERIALES_COMPLETADOS-=1;
        habilitarCotizacion();
        $("#inputTotalCotizacion, #inputIVA1").val("");
        $("#inputTotalCotizacion, #inputIVA1").attr("placeholder", "Pendiente");
        $("#inputTotalCotizacion, #inputIVA1").removeClass("glow-effect");
        $("#btnCotizar").removeClass("d-none");
        $("#btnPrevisualizar").addClass("d-none");
    });
    
    
    // ESCUCHAR CLICK EN COTIZAR PARA SUMAR LOS TOTALES
    $("#btnCotizar").on("click", function(){
        let MaterialesOmitidos = 0;
        for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
            if($(`#checkboxOmitirElemento_m${i}`).is(':checked')){
                MaterialesOmitidos += 1;
            }
        }

        if(window.MATERIALES_COMPLETADOS <= 0){
            sweetAlertResponse("warning", "Faltan datos","Debe completar al menos un elemento de este perfil.", "none");
            return;
        }
        if(MaterialesOmitidos == window.MATERIALES_COMPLETADOS){
            sweetAlertResponse("warning", "Faltan datos","No es posible omitir todos los elementos. Debe completar al menos uno.", "none");
            return;
        }
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

        //$("#btnPrevisualizar").removeClass("d-none");

        // Mostrar total con animación
        $("#inputTotalCotizacion, #inputTotalCotizacion2").addClass("glow-effect");
        setTimeout(() => {
            $("#btnPrevisualizar").trigger("click");
        }, 200);
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
        for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
            $(`#sectionContainerMaterial_m${i}`).fadeOut(500);
        }
        $('#sectionDureza').fadeOut(500);
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
        if(window.WIPER_EN != "0" && window.ESCALON_EN == "0"){
            $("#spanDimensiones2").text(`H caja: ${aCajaResultante}`);
        }
        if(window.ESCALON_EN != "0"){
            $("#spanDimensiones2").text(`H caja: ${aCajaResultante}, H escalon: ${aEscalon}`);
        }
        if(window.WIPER_ESPECIAL_EN != "0"){
             $("#spanDimensiones2").text(`H caja: ${aCajaResultante}, H2: ${aH2}, H3: ${aH3}`);
        }
        window.unirStringBillets();
    });
    
    // ESCUCHAR EL BOTON PREVISUALIZAR
    $("#btnContinuarEditando").on("click", function () {
        // Revertir animación: mostrar secciones
        $('#sectionSelectorCliente, #sectionDimensionesSello').fadeIn(1000);
        for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
            $(`#sectionContainerMaterial_m${i}`).fadeIn(1000);
        }
        $('#sectionDureza').fadeIn(1000);
        $('#sectionCotizar').fadeIn(1000);
        $('#sectionTotalFinal').fadeIn(1000);

        // Ocultar la previsualización
        $("#sectionPrevisualizar").removeClass("d-flex").addClass("d-none");

    });

    // INPUT NOMBRE DEL VENDEDOR, MISMO VALOR A TODOS
    $("#inputVendedor").on("input", function(){
        let elVendedor = $(this).val();
        $(".vendedor-input").val(elVendedor);
        if (elVendedor.trim() != "") {
            habilitarBoton("#btnGuardarCotizacion");
            console.log("El nombre del vendedor es:", elVendedor);
        } else {
            disablarBoton("#btnGuardarCotizacion");
        }
    });

    $(document).on('click', '.btn-detalle-estatus', function(){
        let detalle = $(this).data("detalle");
        Swal.fire({
            title: "Detalle del estatus",
            text: detalle,
            icon: "info",
            showConfirmButton: true,
            confirmButtonText: "Entendido",
            timer: null,
            toast: true,
            width: '500px',
            position: "bottom"
        });
        $.ajax({
            url: "../ajax/ajax_notificacion.php",
            type: "POST",
            data: { mensaje: "Se vio el detalle de una barra: "+detalle },
            success: function(response) {
                console.log("Notificacion enviada: ", response);
            },
            error: function(error) {
                console.error("Error al enviar la notificacion: ", error);
            }
        });
    });
    // GUARDAR LA COTIZACION DESIDIENDO CUALES FORMULARIOS VA A ENVIAR
    $("#btnGuardarCotizacion").on("click", function () {
        $(this).addClass("d-none");
        let promises = [];

        for (let i = 1; i <= window.CANTIDAD_MATERIALES; i++) {
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
                $(this).removeClass("d-none");
            } else {
                Swal.fire({
                    title: 'Proceso exitoso',
                    text: 'Cotización guardada exitosamente. Vencerá en 72 horas.',
                    icon: 'success',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#55AD9B',
                }).then((result) => {
                    if (result.isConfirmed) {

                        //window.open("../includes/functions/generar_pdf.php?id_cotizacion=" + idCotizacion, "_blank");
                        const savedDefault = localStorage.getItem('filtroDefault') || '1';
                        window.location.href = `cotizaciones.php?cot=u&default=${savedDefault}`;

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

    // Animación “pop” al (des)chequear y hook listo para tu lógica
    document.addEventListener('change', (e) => {
        if (!e.target.classList.contains('btn-check-cute')) return;

        // animación breve
        e.target.classList.add('pop');
        setTimeout(() => e.target.classList.remove('pop'), 220);
    });

});