document.addEventListener("DOMContentLoaded", function () {    
    console.log("Cantidad de materiales:", cantidadMateriales);

    for (let iLocal = 1; iLocal <= cantidadMateriales; iLocal++) {
        console.log("material N = ", iLocal );
        (function(i) {
            $(document).ready(function () {
                console.log(`Document ready listo para material ${i}`);
                // estado inicial de variables globales del material
                window[`DIMENSIONES_VALIDAS_m${i}`] = false;
                window[`DESBASTE_DUREZA_m${i}`] = 0.00;
                window[`SELLOS_RESTANTES_m${i}`] = 0;
                window[`MILIMETROS_RESTANTES_m${i}`] = 0;
                window[`INICIO_SELECCION_BILLETS_m${i}`] = false;
                window[`PRECIO_BARRAS_m${i}`] = 0.00;
                window[`CANTIDAD_PIEZAS_TEMPORAL_m${i}`] = 0;
                window[`BILLETS_SELECCIONADOS_OCUPA_m${i}`] = [];
                window[`BILLETS_SELECCIONADOS_LOTES_m${i}`] = [];
                window[`BILLETS_SELECCIONADOS_STRING_m${i}`] = [];
                window[`BILLETS_SELECCIONADOS_MANUALMENTE_m${i}`] = [];
                let precioBarra = 0.00;



// /////////////////////////////////////////// @FUNCIONES JAVASCRIPT
                // habilitar el boton con clases y atributos
                const habilitarBoton = function(elemento) {
                    $(elemento).attr(`disabled`, false).removeClass(`btn-disabled`).addClass(`btn-general`);
                };
                // deshabilitar el boton con clases y atributos
                const disablarBoton = function(elemento) {
                    $(elemento).attr(`disabled`, true).removeClass(`btn-general`).addClass(`btn-disabled`);
                };
                // reiniciar arreglos textarea de billets
                const resetTextareasBillets = function() {
                    $(`#inputClaves_m${i}`).val(``);
                    $(`#inputBillets_m${i}`).val(``);
                    $(`#inputBilletsLotes_m${i}`).val(``);
                    $(`#inputBilletsString_m${i}`).val(``);
                    window[`BILLETS_SELECCIONADOS_OCUPA_m${i}`] = [];

                };
                // reiniciar arreglos, milimetros/sellos necesarios, precio de barras, seleccion de billets y limpiar UI
                const reiniciarMilimetrosNecesarios = function() {
                    window[`INICIO_SELECCION_BILLETS_m${i}`] = false;
                    window[`SELLOS_RESTANTES_m${i}`] = 0;
                    window[`MILIMETROS_RESTANTES_m${i}`] = 0;
                    window[`PRECIO_BARRAS_m${i}`] = 0.00;
                    window[`BILLETS_SELECCIONADOS_OCUPA_m${i}`] = [];

                    // Limpiar UI
                    $(`#spanMilimetrosSobrantes_m${i}`).text(`0`);
                    $(`#spanMilimetrosNecesarios_m${i}`).text(`0`);
                    $(`#spanSellosRestantes_m${i}`).text(`0`);

                    $(`#containerBarraSeleccionadaSimulacion_m${i} span`).text(``);

                    disablarBoton(`#btnBillets_m${i}`);
                    disablarBoton(`#btnBilletsSimulacion_m${i}`);
                    disablarBoton(`#btnSiguiente_m${i}`);
                    disablarBoton(`#btnLimpiarSeleccion_m${i}`);
                    // Vuelve a calcular desde cero
                    setMilimetrosNecesarios();
                    // Cambiar colores
                    $(`#containerFaltanSiNo_m${i}`).removeClass(`text-no-faltan`).addClass(`text-faltan`);
                    console.log({
                        CANTIDAD_PIEZAS_TEMPORAL_m: window[`CANTIDAD_PIEZAS_TEMPORAL_m${i}`],
                        SELLOS_RESTANTES_m: window[`SELLOS_RESTANTES_m${i}`],
                        MILIMETROS_RESTANTES_m: window[`MILIMETROS_RESTANTES_m${i}`],
                        BILLETS_SELECCIONADOS_OCUPA_m: window[`BILLETS_SELECCIONADOS_OCUPA_m${i}`]
                    });
                };                
                // calcular total de milimetros y sellos necesarios inicialmente y mostrarlos en el span
                const setMilimetrosNecesarios = function() {
                    let valueCantidad = parseInt($(`#inputCantidad_m${i}`).val()) || 0;
                    let alturaSello = parseFloat($(`#altura_mm_m${i}`).val()) || 0;

                    if (isNaN(valueCantidad) || valueCantidad <= 0 || !Number.isInteger(Number(valueCantidad)) || isNaN(alturaSello) || alturaSello <= 0){
                        $(`#spanMilimetrosNecesarios_m${i}`).text(`0`);
                        $(`#spanSellosRestantes_m${i}`).text(`0`);
                        window[`SELLOS_RESTANTES_m${i}`] = 0;
                        window[`MILIMETROS_RESTANTES_m${i}`] = 0;
                        disablarBoton(`#btnBillets_m${i}`);
                        disablarBoton(`#btnBilletsSimulacion_m${i}`);
                        disablarBoton(`#btnSiguiente_m${i}`);
                        disablarBoton(`#btnLimpiarSeleccion_m${i}`);
                        return 0;
                    }else{
                        window[`CANTIDAD_PIEZAS_TEMPORAL_m${i}`] = valueCantidad;
                    }

                    let alturaPorSello = alturaSello + window[`DESBASTE_DUREZA_m${i}`];
                    let milimetrosNecesarios = (alturaPorSello * valueCantidad) + window.MEDIDA_AGARRE_MAQUINA;

                    // inicializar globales solo la primera vez
                    if (!window[`INICIO_SELECCION_BILLETS_m${i}`]) {
                        window[`SELLOS_RESTANTES_m${i}`] = valueCantidad;
                        window[`MILIMETROS_RESTANTES_m${i}`] = milimetrosNecesarios;
                        window[`INICIO_SELECCION_BILLETS_m${i}`] = true;
                    }

                    $(`#spanMilimetrosNecesarios_m${i}`).text(window[`MILIMETROS_RESTANTES_m${i}`].toFixed(2));
                    $(`#spanSellosRestantes_m${i}`).text(window[`SELLOS_RESTANTES_m${i}`]);
                    $(`#containerFaltanSiNo_m${i}`).removeClass(`text-no-faltan`).addClass(`text-faltan`);
                    console.log({
                        CANTIDAD_PIEZAS_TEMPORAL_m: window[`CANTIDAD_PIEZAS_TEMPORAL_m${i}`],
                        SELLOS_RESTANTES_m: window[`SELLOS_RESTANTES_m${i}`],
                        MILIMETROS_RESTANTES_m: window[`MILIMETROS_RESTANTES_m${i}`],
                        valueCantidad,
                        alturaSello,
                        milimetrosNecesarios,
                        alturaPorSello
                    });
                    return window[`MILIMETROS_RESTANTES_m${i}`];
                };
                // calcular cuantos sellos y milimetros restan y mostrarlos en el span
                const setNecesariosQuedanSobran = function(alturaBillet, lp) {
                    alturaBillet = parseFloat(alturaBillet);
                    if (isNaN(alturaBillet)) {
                        console.error(`Altura de billet inválida`);
                        return;
                    }
                    
                    // Obtener valores actuales
                    let milimetrosNecesarios = parseFloat($(`#spanMilimetrosNecesarios_m${i}`).text()) || 0;
                    let sellosRestantes = parseInt($(`#spanSellosRestantes_m${i}`).text(), 10);
                    let sellosRestantesAntes = sellosRestantes; // Guardamos valor original ANTES de modificarlo
                    let alturaSello = parseFloat($(`#altura_mm_m${i}`).val()) || 0;
                    let alturaPorSello = alturaSello + window[`DESBASTE_DUREZA_m${i}`];
                    
                    // Calcular cuántos sellos caben en este billet
                    let caben = Math.floor((alturaBillet - window.MEDIDA_AGARRE_MAQUINA) / alturaPorSello);
                    let milimetrosUsados = caben * alturaPorSello;

                    // Restar lo que se puede usar del billet actual
                    let nuevosMilimetrosNecesarios = milimetrosNecesarios - milimetrosUsados;
                    let nuevosSellosRestantes = sellosRestantes - caben;

                    // Validación: si ya no se necesitan más sellos
                    if (nuevosSellosRestantes <= 0) {
                        nuevosMilimetrosNecesarios = 0; // Fuerza mostrar 0 aunque sobren milímetros
                        habilitarBoton(`#btnSiguiente_m${i}`);
                        disablarBoton(`#btnBillets_m${i}`);
                        disablarBoton(`#btnBilletsSimulacion_m${i}`);
                        $(`#containerFaltanSiNo_m${i}`).removeClass(`text-faltan`).addClass(`text-no-faltan`);
                    }

                    // Mostrar resultados
                    $(`#spanMilimetrosNecesarios_m${i}`).text(nuevosMilimetrosNecesarios.toFixed(2));
                    $(`#spanSellosRestantes_m${i}`).text(Math.max(nuevosSellosRestantes, 0)); // Nunca negativos

                    // Mostrar sobrantes
                    let sobrantes = alturaBillet - (milimetrosUsados + window.MEDIDA_AGARRE_MAQUINA);
                    $(`#spanMilimetrosSobrantes_m${i}`).text(sobrantes.toFixed(2));

                    // Cerrar modal
                    $(`#btnCerrarModalBillets_m${i}`).click();
                    $(`#btnCerrarModalBilletsSimulacion_m${i}`).click();

                    let ocupa = Math.min(caben, sellosRestantesAntes);
                    window[`BILLETS_SELECCIONADOS_OCUPA_m${i}`].push({
                        lote_pedimento: lp,
                        ocupa: ocupa
                    });
                    console.log({
                        CANTIDAD_PIEZAS_TEMPORAL_m: window[`CANTIDAD_PIEZAS_TEMPORAL_m${i}`],
                        SELLOS_RESTANTES_m: window[`SELLOS_RESTANTES_m${i}`],
                        MILIMETROS_RESTANTES_m: window[`MILIMETROS_RESTANTES_m${i}`],
                        alturaBillet,
                        sellosRestantes,
                        sellosRestantesAntes,
                        alturaSello,
                        alturaPorSello,
                        caben,
                        milimetrosUsados,
                        nuevosMilimetrosNecesarios,
                        nuevosSellosRestantes,
                        sobrantes,
                        BILLETS_SELECCIONADOS_OCUPA_m: window[`BILLETS_SELECCIONADOS_OCUPA_m${i}`]
                    });
                    return caben;
                };
                // para mostrar cuantos le caben al billet en la tabla de billets
                const setLeCaben = function(alturaBillet) {
                    alturaBillet = parseFloat(alturaBillet);
                    if (isNaN(alturaBillet)) {
                        console.error(`Altura de billet inválida`);
                        return;
                    }

                    let alturaSello = parseFloat($(`#altura_mm_m${i}`).val()) || 0;
                    let alturaPorSello = alturaSello + window[`DESBASTE_DUREZA_m${i}`];
                    let cabenPz = Math.floor((alturaBillet - window.MEDIDA_AGARRE_MAQUINA) / alturaPorSello);

                    console.log({
                        CANTIDAD_PIEZAS_TEMPORAL_m: window[`CANTIDAD_PIEZAS_TEMPORAL_m${i}`],
                        SELLOS_RESTANTES_m: window[`SELLOS_RESTANTES_m${i}`],
                        MILIMETROS_RESTANTES_m: window[`MILIMETROS_RESTANTES_m${i}`],
                        alturaBillet,
                        alturaSello,
                        alturaPorSello,
                        cabenPz,
                        BILLETS_SELECCIONADOS_OCUPA_m: window[`BILLETS_SELECCIONADOS_OCUPA_m${i}`]
                    });
                    return cabenPz;
                };
                // validar que se encuentren todas las claves en minitabla de precios de barra
                const sonClavesPrecioValidas = function(){
                    let textoMiniTabla = $(`#miniTableCostoBarra_m${i} tbody`).text();
                    let textoInvalido = "no fue encontrada";
                    if(textoMiniTabla.includes(textoInvalido)){
                        disablarBoton(`#btnListo_m${i}`);
                        console.log(`Alguna de las claves es invalida`);
                    }else{
                        habilitarBoton(`#btnListo_m${i}`);
                        console.log(`Todas las claves son correctas`);
                    }
                };
                // iniciar mm de dureza respecto al material
                const setDesbasteDureza = function(materialDesbaste) {
                    switch (materialDesbaste) {
                        case 'H-ECOPUR':
                        case 'ECOSIL':
                        case 'ECORUBBER 1':
                        case 'ECORUBBER 2':
                        case 'ECORUBBER 3':
                        case 'ECOPUR':
                            window[`DESBASTE_DUREZA_m${i}`] = 2.00; 
                            console.log(`Desbaste añadido por corte (blando) = `, window[`DESBASTE_DUREZA_m${i}`]);
                            break;
                        case 'ECOTAL':
                        case 'ECOMID':
                        case 'ECOFLON 1':
                        case 'ECOFLON 2':
                        case 'ECOFLON 3':
                            window[`DESBASTE_DUREZA_m${i}`] = 2.50;  
                            console.log(`Desbaste añadido por corte (duro) = `, window[`DESBASTE_DUREZA_m${i}`]);
                            break;
                        default:
                            window[`DESBASTE_DUREZA_m${i}`] = 2.50;
                            console.log(`Material no encontrado, desbaste por defecto = `, window[`DESBASTE_DUREZA_m${i}`]);
                    }
                };
                // VALIDAR LAS DIMENSIONES
                const validarCampos = function() {
                    let valores = [
                        $(`#altura_mm_m${i}`).val(),
                        $(`#altura_inch_m${i}`).val(),
                        $(`#diametro_interior_mm_m${i}`).val(),
                        $(`#diametro_interior_inch_m${i}`).val(),
                        $(`#diametro_exterior_mm_m${i}`).val(),
                        $(`#diametro_exterior_inch_m${i}`).val(),
                        
                    ];
                
                    if (parseFloat(valores[0]) == 0 || parseFloat(valores[0]) < 0 || parseFloat(valores[4]) == 0 || parseFloat(valores[4]) < 0) {
                        // console.log(`La altura y el DE no puede ser 0.`);
                        $(`#containerErrorDimensiones_m${i} span`).text('La altura y el DE no puede ser 0');
                        disablarBoton(`#btnBillets_m${i}`);
                        disablarBoton(`#btnBilletsSimulacion_m${i}`);
                        disablarBoton(`#btnSiguiente_m${i}`);
                        disablarBoton(`#btnLimpiarSeleccion_m${i}`);
                        window[`DIMENSIONES_VALIDAS_m${i}`] = false;
                        return false;
                    }       
                    if (parseFloat(valores[2]) >= parseFloat(valores[4]) || parseFloat(valores[3]) >= parseFloat(valores[5])) {
                        // console.log(`El DI no puede ser mayor o igual al DE.`);
                        $(`#containerErrorDimensiones_m${i} span`).text('El DI no puede ser mayor o igual al DE');
                        disablarBoton(`#btnBillets_m${i}`);
                        disablarBoton(`#btnBilletsSimulacion_m${i}`);
                        disablarBoton(`#btnSiguiente_m${i}`);
                        disablarBoton(`#btnLimpiarSeleccion_m${i}`);
                        window[`DIMENSIONES_VALIDAS_m${i}`] = false;
                        return false; 
                    }

                    // Verificar si alguno de los valores es inválido
                    let hayValoresInvalidos = valores.some(function(valor) {
                        return valor === null || valor === "" || isNaN(valor) || valor === ".";
                    });

                    if (hayValoresInvalidos) {
                        // console.log(`Uno de los campos tiene un valor no válido.`);
                        $(`#containerErrorDimensiones_m${i} span`).text('Ingrese las dimensiones correctamente');
                        window[`DIMENSIONES_VALIDAS_m${i}`] = false;
                        return false; 
                    }
                

                    window[`DIMENSIONES_VALIDAS_m${i}`] = true;
                    return true; 
                };
                // funcion para validar antes de mostrar btn billets
                const mostrarBtnBillets = function(validarMaterial, validarProveedor, validarCantidad, dimensionesValidas){
                    if (isNaN(validarCantidad) || validarCantidad <= 0 || !Number.isInteger(Number(validarCantidad)) || !validarMaterial || !validarProveedor || validarMaterial.trim() === "" || validarProveedor.trim() === "" || dimensionesValidas == false || window.DIMENSIONES_VALIDAS == false){
                        $(`#inputCostoOperacion_m${i}, #inputCostoHerramienta_m${i}, #inputCostoMaterial_m${i}, #precioBarra_m${i}`).val(``);
                        $(`#inputDescuentoRC_m${i}, #inputPorcentDescuentoRC_m${i}, #inputDescuentoMayoreo_m${i}, #inputPorcentDescuentoMayoreo_m${i}`).val(``);
                        $(`#totalInput_m${i}, #colPrecio_m${i}, #colMaxUsable_m${i}, #inputTotalUnitarios_m${i}, #inputTotalDescuentos_m${i}, #inputTotalMaterial_m${i}`).val(``);

                        $(`#miniTableBillets_m${i} tbody`).empty();
                        $(`#miniTableCostoBarra_m${i} tbody`).empty();
                
                        $(`#inputClaves_m${i}`).val(``);
                        $(`#inputBillets_m${i}`).val(``);

                        disablarBoton(`#btnBillets_m${i}`);
                        disablarBoton(`#btnBilletsSimulacion_m${i}`); 
                        disablarBoton(`#btnSiguiente_m${i}`);
                        disablarBoton(`#btnLimpiarSeleccion_m${i}`);

                    } else {
                        habilitarBoton(`#btnBillets_m${i}`);
                        habilitarBoton(`#btnBilletsSimulacion_m${i}`);
                        disablarBoton(`#btnLimpiarSeleccion_m${i}`);
                        disablarBoton(`#btnSiguiente_m${i}`);
                    }
                };
                // limpiar un lote pedimento para uso de id
                const cleanAttrId = function(str) {
                    // 1. Convertir a cadena
                    str = String(str);

                    // 2. Si la cadena resulta vacía, generar un ID aleatorio como fallback
                    if (!str) {
                        str = makeRandomId(8);
                    }

                    // 3. Limpiar la cadena usando replaces
                    let idLimpio = str
                        .replace(/[^\w\-]/g, "")  // elimina caracteres no permitidos
                        .replace(/\s+/g, "_");    // reemplaza espacios por guiones bajos

                    // 4. Si comienza con número, prefix "x"
                    if (/^\d/.test(idLimpio)) {
                        idLimpio = "x" + idLimpio.slice(1);
                    }

                    return idLimpio;
                };
                // Función para generar string aleatorio alfanumérico
                const makeRandomId = function(length) {
                    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
                    let result = '';
                    for (let i = 0; i < length; i++) {
                        result += chars.charAt(Math.floor(Math.random() * chars.length));
                    }
                    return result;
                };
                // limpiar/escapar caracteres no permitidos en inputs numericos 
                const escaparCaracteresNumericos = function() {
                    let valorFiltrado = $(this).val();

                    $(`#miniTableBillets_m${i} tbody`).empty();
                    $(`#miniTableCostoBarra_m${i} tbody`).empty();

                    resetTextareasBillets();

                    window[`billetsSeleccionados_m${i}`] = [];
                    window[`BILLETS_SELECCIONADOS_LOTES_m${i}`] = [];
                    window[`BILLETS_SELECCIONADOS_STRING_m${i}`] = [];
                    window[`BILLETS_SELECCIONADOS_MANUALMENTE_m${i}`] = [];
                    window[`PRECIO_BARRAS_m${i}`] = 0.00;
                    // console.log(`Limpiando billets seleccionados_m${i}. `, window[`billetsSeleccionados_m${i}`]);
                
                    reiniciarMilimetrosNecesarios();
                
                    // Validar si el valor es un número dentro del rango permitido
                    if (isNaN(parseFloat(valorFiltrado)) || parseFloat(valorFiltrado) > 9999.99 || parseFloat(valorFiltrado) < 0 || valorFiltrado.trim() == "") {
                        if (valorFiltrado.trim() == "") {
                            window[`DIMENSIONES_VALIDAS_m${i}`] = false;
                        } else {
                            $(this).val('0');
                        }
                    }
                };



// //////////////////////////////////////// @LLAMADAS AJAX INICIALES/ INICIALIZACION DE VARIABLES
                $(`#selectorMaterial_m${i}`).select2({
                    width: "100%",
                    minimumResultsForSearch: Infinity  // Esto desactiva completamente la búsqueda
                });
                $(`#selectorProveedor_m${i}`).select2({
                    width: "100%",
                    minimumResultsForSearch: Infinity  // Esto desactiva completamente la búsqueda
                });
                // Realizar la solicitud AJAX para mostrar todos los materiales
                // $.ajax({
                //     url: '../ajax/ajax_materiales_parametros2.php',
                //     type: 'GET',
                //     dataType: 'json',
                //     success: function(data) {
                //         if (data.length > 0) {
                //             $.each(data, function(index, item) {
                //                 $(`#selectorMaterial_m${i}`).append(
                //                     `<option value="${item.caso}">${item.caso}</option>`
                //                 );
                //             });
                //         }
                //     },
                //     error: function() {
                //         console.error('Error al realizar la petición AJAX');
                //     }
                // });



// ////////////////////////////////////////////////// @LISTENING DE EVENTOS EN EL DOM
                // Ckeckear el checkbox de omitir elemento
                $(`#checkboxOmitirElemento_m${i}`).on('change', function() {
                    if ($(this).is(':checked')) {
                        $(`#seraEnviado_m${i}`).val("no");
                        $(`#containerPags_m${i}`).addClass("d-none");
                        $(`#containerAltPags_m${i}`).removeClass("d-none");

                        $(`#imagenMaterialTabla_m${i}`).addClass("d-none");
                        $(`#rowM${i}`).addClass("d-none");

                        $(`#inputTotalMaterial_m${i}`).val(0);
                    } else {
                        $(`#seraEnviado_m${i}`).val("si");
                        $(`#containerPags_m${i}`).removeClass("d-none");
                        $(`#containerAltPags_m${i}`).addClass("d-none");

                        $(`#imagenMaterialTabla_m${i}`).removeClass("d-none");
                        $(`#rowM${i}`).removeClass("d-none");
                        
                    }
                });
                // llamar a validar que las dimensiones sean correctas
                $(`#altura_mm_m${i}, #altura_inch_m${i}, #diametro_interior_mm_m${i}, #diametro_interior_inch_m${i}, #diametro_exterior_mm_m${i}, #diametro_exterior_inch_m${i}, #inputAlturaCaja_m${i}, #inputAlturaCajaInch_m${i}, #inputAlturaEscalon_m${i}, #inputAlturaEscalonInch_m${i}`).on('input', function() {
                    // Antes de proceder con cualquier acción, validar todos los campos
                    if (!validarCampos()) {
                        return; 
                    }
                    // Si todas las validaciones pasan, proceder con la habilitación y la solicitud AJAX
                    // console.log(`Todos los campos tienen valores válidos.`);
                    $(`#containerErrorDimensiones_m${i} span`).text('');
                    $(`#containerErrorDimensiones_cliente span`).text('');
                    setTimeout(() => {
                        $(`#inputCantidad_m${i}`).trigger(`change`);
                    }, 300);
                }); 
                // EVENTO AL CAMBIAR TIPO DE MATERIAL llamar dureza, obtener sus proveedores y reiniciar los milimetros necesarios
                $(`#selectorMaterial_m${i}`).on(`change`, function() { 
                    let materialSeleccionado = $(this).val();
                    setDesbasteDureza(materialSeleccionado);

                    setTimeout(() => {
                        $(`#selectorProveedor_m${i}`).attr(`disabled`, false);
                    }, 500);
                    $(`#selectorProveedor_m${i}`).empty().append('<option value="" disabled>Seleccione un proveedor</option>');
                    $(`#selectorProveedor_m${i}`).val(``);
                    
                    reiniciarMilimetrosNecesarios();

                    // Realizar la llamada AJAX para obtener los PROVEEDORES de ese material
                    $.ajax({
                        url: '../ajax/ajax_proveedores.php', 
                        type: 'POST',
                        data: { material: materialSeleccionado },
                        dataType: 'json',
                        success: function(data) {
                            // Verifica que la respuesta tenga datos
                            if (data.length > 0) {
                                $.each(data, function(index, item) {
                                    $(`#selectorProveedor_m${i}`).append(
                                        `
                                        <option value="${item.proveedor}">${item.proveedor}</option>
                                        `
                                    );
                                });
                            } else {
                                $(`#selectorProveedor_m${i}`).append('<option value="" disabled>No hay proveedores disponibles</option>');
                            }
                        },
                        error: function() {
                            console.error('Error al realizar la petición AJAX');
                        }
                    });
                });
                // EVENTO AL CAMBIAR proveedor reiniciar los milimetros necesarios
                $(`#selectorProveedor_m${i}`).on(`change`, function() {
                    reiniciarMilimetrosNecesarios();
                });
                // CUANDO DIGITA CANTIDAD llamar reiniciar los milimetros necesarios
                $(`#inputCantidad_m${i}`).on(`input`, function() {
                    reiniciarMilimetrosNecesarios();
                });
                // EVENTOS ESCUCHAR material, proveedor y cantidad para reiniciar arreglos, selecciones y minitablas
                $(`#selectorMaterial_m${i}, #selectorProveedor_m${i}, #inputCantidad_m${i}`).on(`input change`, function() { 

                    let elMaterial = $(`#selectorMaterial_m${i}`).val();
                    let elProveedor = $(`#selectorProveedor_m${i}`).val();
                    let laCantidad = $(`#inputCantidad_m${i}`).val();
                    
                    $(`#miniTableBillets_m${i} tbody`).empty();
                    $(`#miniTableCostoBarra_m${i} tbody`).empty();

                    $(`#precioBarra_m${i}`).val(``);

                    resetTextareasBillets();

                    window[`billetsSeleccionados_m${i}`] = [];
                    window[`BILLETS_SELECCIONADOS_LOTES_m${i}`] = [];
                    window[`BILLETS_SELECCIONADOS_STRING_m${i}`] = [];
                    window[`BILLETS_SELECCIONADOS_MANUALMENTE_m${i}`] = [];
                    window[`PRECIO_BARRAS_m${i}`] = 0.00;
                    // console.log(`Limpiando billets seleccionados_m${i}. `, window[`billetsSeleccionados_m${i}`]);
                    let tipoDurezaMateriales = $("#selectorDurezaMateriales").val();

                    if(window.perfilSello.includes("R16") && tipoDurezaMateriales == "duros"){
                        sweetAlertResponse("warning", "Falta información","No es posible maquinar este sello con materiales duros", "none");
                        return;
                    }
                    mostrarBtnBillets(elMaterial, elProveedor, laCantidad, window[`DIMENSIONES_VALIDAS_m${i}`]);
                });
                // **** MODAL DE BILLETS DE INVENTATIO CNC
                // EVENTO VER MODAL DE BILLETS, AJAX traer billets coincidentes
                $(`#btnBillets_m${i}`).on(`click`, function() {
                    let materialSeleccionado = $(`#selectorMaterial_m${i}`).val();
                    let proveedorSeleccionado = $(`#selectorProveedor_m${i}`).val();
                    let alturaSeleccionada = parseFloat($(`#altura_mm_m${i}`).val());
                    alturaSeleccionada = parseFloat(alturaSeleccionada);
                    let dInteriorSeleccionado = parseFloat($(`#diametro_interior_mm_m${i}`).val());
                    let dExteriorSeleccionado = parseFloat($(`#diametro_exterior_mm_m${i}`).val());
                    // sumatoria de altura total 
                    let alturaNecesario = Math.abs(parseFloat(alturaSeleccionada + window[`DESBASTE_DUREZA_m${i}`] + window.MEDIDA_AGARRE_MAQUINA)).toFixed(2);
                    let dInteriorNecesario = 0.00;
                    let dExteriorNecesario = 0.00;
                    let materialConLabioDI = "_m"+window.CON_LABIO_DI;
                    let materialConLabioDE = "_m"+window.CON_LABIO_DE;
                    let tipoDurezaMateriales = $("#selectorDurezaMateriales").val();
                    if(!materialSeleccionado || materialSeleccionado === "" || materialSeleccionado==null){
                        $(`#selectorMaterial_m${i}`).val("");
                        $(`#btnCerrarModalBillets_m${i}`).trigger("click");
                        $(`#tablaBillets_m${i} tbody`).empty();
                        sweetAlertResponse("warning", "Falta información","Seleccione un material", "none");
                        return;
                    }
                    if(!proveedorSeleccionado || proveedorSeleccionado === "" || proveedorSeleccionado==null){
                        $(`#selectorProveedor_m${i}`).val("");
                        sweetAlertResponse("warning", "Falta información","Seleccione un proveedor", "none");
                        return;
                    }
                    console.log(`Aplicando calculos al Material _m${i}`);
                    // if(window.FAMILIA_PERFIL === "backup" || window.FAMILIA_PERFIL === "guide"){
                    //     window.DI_TOLERANCIA_DEFAULT = 1.00;
                    //     window.DE_TOLERANCIA_DEFAULT = 1.00;
                    // }else{
                    //     window.DI_TOLERANCIA_DEFAULT = 3.00;
                    //     window.DE_TOLERANCIA_DEFAULT = 1.00;
                    // }
                    console.log(`Desperdicio default DI = `, window.DI_TOLERANCIA_DEFAULT);
                    console.log(`Desperdicio default DE = `, window.DE_TOLERANCIA_DEFAULT);    

                    dInteriorNecesario = Math.abs(parseFloat((dInteriorSeleccionado  - window.DI_TOLERANCIA_DEFAULT))).toFixed(2);
                    
                    if(dInteriorNecesario >= dInteriorSeleccionado){
                        dInteriorNecesario = 0.00;
                    }

                    dExteriorNecesario = Math.abs(parseFloat((dExteriorSeleccionado + window.DE_TOLERANCIA_DEFAULT))).toFixed(2);

                    $(`#spanAlturaCliente_m${i}`).text(alturaSeleccionada);
                    $(`#spanDiCliente_m${i}`).text(dInteriorSeleccionado);
                    $(`#spanDeCliente_m${i}`).text(dExteriorSeleccionado);
                    $(`#spanAlturaNecesario_m${i}`).text(alturaNecesario);
                    console.log(`Altura con desbaste dureza y desperdicio por agarre cnc: `, alturaNecesario);
                    
                    $(`#spanDiNecesario_m${i}`).text(dInteriorNecesario);
                    $(`#spanDeNecesario_m${i}`).text(dExteriorNecesario);

                    console.log(`DI necesario calculado = `, dInteriorNecesario);
                    console.log(`DE necesario calculado = `, dExteriorNecesario);

                    $(`#spanPorcentAprov_m${i}`).text(`0.00`);

                    window.unirBilletsSeleccionados();

                    console.log(`Excluir billets en la consulta: `, window.billetsSeleccionados);

                    if (materialSeleccionado) {
                        // Realizar la llamada AJAX para obtener los billets filtrados
                        // ajax_billets consulta con proveedor y ajax_billets2 no
                        $.ajax({
                            url: '../ajax/ajax_billets.php', 
                            type: 'POST',
                            data: { 
                                material: materialSeleccionado,
                                proveedor: proveedorSeleccionado,
                                altura_mm: alturaNecesario,
                                diametro_interior_mm: dInteriorNecesario,
                                diametro_exterior_mm: dExteriorNecesario,
                                arreglo_excluir: window.billetsSeleccionados
                            },
                            dataType: 'json',
                            success: function(data) {
                                $(`#tablaBillets_m${i} tbody`).empty();

                                // Verifica que la respuesta tenga datos
                                if (data.length > 0) {

                                    $(`#titleClavesCoincidentes_m${i}`).text(`Claves coincidentes en inventario CNC (${data.length} resultados)`);

                                    let clavesUnicas = {}; // Almacena las claves ya insertadas
                                    let contadorClaves = {}; // Contador de repeticiones de Clave

                                    // Primera pasada: contar las repeticiones
                                    $.each(data, function(index, item) {
                                        if (contadorClaves[item.Clave]) {
                                            contadorClaves[item.Clave]++; // Incrementa si ya existe
                                        } else {
                                            contadorClaves[item.Clave] = 1; // Inicializa en 1 si es la primera vez
                                        }
                                    });

                                    // Primero calculamos y agregamos el porcentaje de aprovechamiento a cada billet
                                    data.forEach(function(item) {
                                        let dataDEBillet = parseFloat(item.exterior);
                                        let dataDEResultante = parseFloat(dExteriorSeleccionado);
                                        let dataDIResultante = parseFloat(dInteriorSeleccionado);
                                        let dataDIBillet = parseFloat(item.interior);

                                        function calcularRadio(valor) {
                                            return (valor / dataDEBillet) * 50;
                                        }

                                        let dimensiones = {
                                            exterior: 50,
                                            azul: calcularRadio(dataDEResultante),
                                            gris: calcularRadio(dataDIResultante),
                                            blanco: calcularRadio(dataDIBillet)
                                        };

                                        let desperdicioPorcentaje = ((Math.pow(dimensiones.exterior, 2) - Math.pow(dimensiones.azul, 2)) +
                                                                    (Math.pow(dimensiones.gris, 2) - Math.pow(dimensiones.blanco, 2))) /
                                                                    Math.pow(dimensiones.exterior, 2) * 100;

                                        let porcentajeAprovechamiento = 100 - desperdicioPorcentaje;
                                        item.porcentajeAprovechamiento = porcentajeAprovechamiento;
                                    });

                                    // Ordenamos los billets por mayor porcentaje de aprovechamiento
                                    data.sort(function(a, b) {
                                        return b.porcentajeAprovechamiento - a.porcentajeAprovechamiento;
                                    });

                                    // Segunda pasada: agregar claves únicas y renglón extra si es necesario
                                    $.each(data, function(index, item) {
                                        let dataStockBillet = item.pre_stock;
                                        let cabenEnBillet = setLeCaben(item.pre_stock);
                                        let dataDEBillet = parseFloat(item.exterior);
                                        let dataDEResultante = parseFloat(dExteriorSeleccionado);
                                        let dataDIResultante = parseFloat(dInteriorSeleccionado);
                                        let dataDIBillet = parseFloat(item.interior);

                                        function calcularRadio(valor) {
                                            return (valor / dataDEBillet) * 50;
                                        }

                                        let dimensiones = {
                                            exterior: 50,
                                            azul: calcularRadio(dataDEResultante),
                                            gris: calcularRadio(dataDIResultante),
                                            blanco: calcularRadio(dataDIBillet)
                                        };

                                        // Recuperamos el porcentaje calculado previamente
                                        let porcentajeAprovechamiento = item.porcentajeAprovechamiento;

                                        // Determinar si mostrar u ocultar el botón de selección
                                        let mostrarBoton = ['Disponible para cotizar', 'En cotización'].includes(item.estatus);

                                        let claseBoton = mostrarBoton ? '' : 'd-none';
                                        let claseEstatus = mostrarBoton ? 'text-success fw-bold' : 'text-danger fw-bold';
                                        let textoAdicionalEstatus = "";

                                        switch(item.estatus){
                                            case "Eliminado": 
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='Preguntar disponibilidad de la barra con stock a inventarios'>
                                                    </i>
                                                `;
                                            break;
                                            case "En cotización":
                                                claseEstatus = 'text-warning fw-bold';
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='La barra se encuentra en una cotización vigente no archivada. Se libera el ${item.fecha_vencimiento} automáticamente o al archivarla.'>
                                                    </i>
                                                `;
                                            break;
                                            case "En uso":
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='La barra se encuentra en una requisición autorizada pendiente de maquinar'>
                                                    </i>
                                                `;
                                            break;
                                            case "Maquinado en curso":
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='La barra se liberará al finalizar el maquinado con el nuevo stock disponible'>
                                                    </i>
                                                `;
                                            break;
                                            case "Clave incorrecta":
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='Comuniquese con inventarios o soporte para validación y cambio de clave'>
                                                    </i>
                                                `;
                                            break;
                                            case "Relación pendiente":
                                            case "Clave nueva pendiente":
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='Comuniquese con soporte para verificar la relación de claves de esta barra'>
                                                    </i>
                                                `;
                                            break;
                                            default:
                                                textoAdicionalEstatus = "";
                                            break;
                                        }
                                        // Si la clave no ha sido insertada aún, la agrega
                                        if (!clavesUnicas[item.Clave]) {
                                            $(`#tablaBillets_m${i} tbody`).append(
                                                `<tr id="fila_${cleanAttrId(item.Clave)}_m${i}" style="border-top:3px solid #95D2B3 !important;">
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn-general btn-seleccionar-billet_m${i} ${claseBoton}" 
                                                                title="Seleccionar este billet"
                                                                data-clave="${item.Clave}"
                                                                data-altura="${item.pre_stock}"
                                                                data-interior="${item.interior}"
                                                                data-exterior="${item.exterior}"
                                                                data-lote="${item.lote_pedimento}"
                                                                data-proveedor="${item.proveedor}"
                                                                data-manualmente="0"
                                                            ><i class="icon-item bi bi-check2-square"></i></button>

                                                            <button type="button" class="btn-general btn-circulo-billet_m${i}" 
                                                                title="Ver representacion de porcentaje de aprovechamiento"
                                                                data-altura-resultante="${alturaSeleccionada}"
                                                                data-di-resultante="${dInteriorSeleccionado}"
                                                                data-de-resultante="${dExteriorSeleccionado}"

                                                                data-altura-necesario="${alturaNecesario}"
                                                                data-di-necesario="${dInteriorNecesario}"
                                                                data-de-necesario="${dExteriorNecesario}"

                                                                data-di-billet="${item.interior}"
                                                                data-de-billet="${item.exterior}"
                                                            ><div class="d-flex justify-content-center align-items-center"><i class="icon-item bi bi-vinyl"></i><i class="bi bi-percent"></i></div></button>
                                                        </div>
                                                    </td>
                                                    <td>${item.Clave}</td>
                                                    <td>${porcentajeAprovechamiento.toFixed(2)}%</td>
                                                    <td >${dataStockBillet}</td>
                                                    <td class="${claseEstatus}">${item.estatus=="Eliminado"?"Barra archivada":item.estatus} ${textoAdicionalEstatus}</td>
                                                    <td >${cabenEnBillet}</td>
                                                    <td id="td_interior_${cleanAttrId(item.lote_pedimento)}_m${i}">${item.interior}/${item.exterior}</td>
                                                    <td id="td_lote_${cleanAttrId(item.lote_pedimento)}_m${i}">${item.lote_pedimento}</td>
                                                </tr>`
                                            );

                                            // Marcar esta clave como insertada
                                            clavesUnicas[item.Clave] = true;
                                        } else {
                                            // Si la clave está repetida, la agrega oculta con d-none
                                            $(`#tablaBillets_m${i} tbody`).append(
                                                `<tr class="fila-repetida" data-clave="${item.Clave}" style="display:none;">
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn-general btn-seleccionar-billet_m${i} ${claseBoton}" 
                                                                title="Seleccionar este billet"
                                                                data-clave="${item.Clave}"
                                                                data-altura="${item.pre_stock}"
                                                                data-interior="${item.interior}"
                                                                data-exterior="${item.exterior}"
                                                                data-lote="${item.lote_pedimento}"
                                                                data-proveedor="${item.proveedor}"
                                                                data-manualmente="0"
                                                            ><i class="icon-item bi bi-check2-square"></i></button>

                                                            <button type="button" class="btn-general btn-circulo-billet_m${i}" 
                                                                title="Ver representacion de porcentaje de desperdicio"
                                                                data-altura-resultante="${alturaSeleccionada}"
                                                                data-di-resultante="${dInteriorSeleccionado}"
                                                                data-de-resultante="${dExteriorSeleccionado}"

                                                                data-altura-necesario="${alturaNecesario}"
                                                                data-di-necesario="${dInteriorNecesario}"
                                                                data-de-necesario="${dExteriorNecesario}"

                                                                data-di-billet="${item.interior}"
                                                                data-de-billet="${item.exterior}"
                                                            ><div class="d-flex justify-content-center align-items-center"><i class="icon-item bi bi-vinyl"></i><i class="bi bi-percent"></i></div></button>
                                                        </div>
                                                    </td>
                                                    <td>${item.Clave}</td>
                                                    <td>${porcentajeAprovechamiento.toFixed(2)}%</td>
                                                    <td >${dataStockBillet}</td>
                                                    <td class="${claseEstatus}">${item.estatus=="Eliminado"?"Barra archivada":item.estatus} ${textoAdicionalEstatus}</td>
                                                    <td >${cabenEnBillet}</td>
                                                    <td id="td_interior_${cleanAttrId(item.lote_pedimento)}_m${i}" >${item.interior}/${item.exterior}</td>
                                                    <td id="td_lote_${cleanAttrId(item.lote_pedimento)}_m${i}" >${item.lote_pedimento}</td>
                                                </tr>`
                                            );
                                        }

                                        // Si es el último elemento o si la clave cambia, agrega el botón después de las repetidas
                                        if (
                                            index === data.length - 1 || // Último elemento
                                            (data[index + 1] && data[index + 1].Clave !== item.Clave) // Cambia de clave
                                        ) {
                                            // Si la clave tiene más de 1 repetición, agrega el botón después
                                            if (contadorClaves[item.Clave] > 1) {
                                                
                                                $(`#tablaBillets_m${i} tbody`).append(
                                                    `<tr id="row_${cleanAttrId(item.Clave)}" class="row-ver-mas">
                                                        <td colspan="8" class="p-0">
                                                            <button id="btn_${cleanAttrId(item.Clave)}_m${i}" type="button" class="btn-ver-mas" data-clave="${item.Clave}">
                                                                Mas billets de ${item.Clave} (${contadorClaves[item.Clave]-1})
                                                            </button>
                                                        </td>
                                                    </tr>`
                                                );
                                            }
                                        }
                                    });

                                    // .slideDown .slideUp
                                    // Evento para "Ver más / Ver menos"
                                    $(`#tablaBillets_m${i} tbody`).on('click', '.btn-ver-mas', function() {
                                        let clave = $(this).data('clave'); // Obtener clave
                                        let filasRepetidas = $(`.fila-repetida[data-clave="${clave}"]`);
                                        
                                        if (filasRepetidas.css('display') === 'none') {
                                            filasRepetidas.each(function(index) {
                                                setTimeout(() => {
                                                    $(this).fadeIn(400); // Aplicar fadeIn de manera secuencial
                                                }, 50 * index); // Retraso basado en el índice
                                            });

                                            $(this).text(`Ver menos billets de ${clave}`);
                                        } else {
                                            // Invertir el orden para aplicar el fadeOut de la última a la primera
                                            filasRepetidas.get().reverse().forEach(function(fila, index) {
                                                setTimeout(() => {
                                                    $(fila).fadeOut(400); // Aplicar fadeOut de manera secuencial
                                                }, 50 * index); // Retraso basado en el índice
                                            });

                                            $(this).text(`Mas billets de ${clave} (${contadorClaves[clave]-1})`);
                                        }
                                    });
                                    $(`#containerBusquedaManual__m${i}`).addClass(`d-none`);

                                } else {
                                    $(`#titleClavesCoincidentes_m${i}`).text(`Claves coincidentes en inventario CNC (0 resultados)`);
                                    $(`#tablaBillets_m${i} tbody`).append(
                                        '<tr><td colspan="6">No se encontraron billets coincidentes</td></tr>'
                                    );
                                    $(`#containerBusquedaManual__m${i}`).removeClass(`d-none`);
                                }
                                
                                $(`#circuloSvg_m${i}`).addClass(`d-none`);
                                $(`#spanPorcentDesp_m${i}`).text(`0.00`);
                            },
                            error: function() {
                                console.error('Error al realizar la petición AJAX');
                                $(`#tablaBillets_m${i} tbody`).append('<tr><td colspan="4">Error en ajax</td></tr>');
                            }
                        });
                    }
                });
                // CUANDO EL USUARIO BUSCA MANUALMENTE LAS BARRAS
                $(`#btnBuscarManualmente_m${i}`).on(`click`, function() {
                    let materialSeleccionado = $(`#selectorMaterial_m${i}`).val();
                    let inputDI = $(`#inputBusquedaManualDI_m${i}`).val();
                    let inputDE = $(`#inputBusquedaManualDE_m${i}`).val();
                    let alturaSeleccionada = parseFloat($(`#altura_mm_m${i}`).val());
                    alturaSeleccionada = parseFloat(alturaSeleccionada);
                    let dInteriorSeleccionado = parseFloat(inputDI); // Usar los valores exactos de los inputs
                    let dExteriorSeleccionado = parseFloat(inputDE); // Usar los valores exactos de los inputs
                    let alturaNecesario = Math.abs(parseFloat(alturaSeleccionada + window[`DESBASTE_DUREZA_m${i}`] + window.MEDIDA_AGARRE_MAQUINA)).toFixed(2);

                    console.log(`Aplicando calculos al Material _m${i}`);
                    
                    // Mostrar las medidas exactas ingresadas por el usuario
                    $(`#spanAlturaCliente_m${i}`).text(alturaSeleccionada);
                    $(`#spanDiCliente_m${i}`).text(dInteriorSeleccionado);
                    $(`#spanDeCliente_m${i}`).text(dExteriorSeleccionado);
                    $(`#spanAlturaNecesario_m${i}`).text(alturaNecesario);
                    console.log(`Altura con desbaste dureza y desperdicio por agarre cnc: `, alturaNecesario);
                    
                    // Para la búsqueda, usar las medidas exactas (sin tolerancias)
                    $(`#spanDiNecesario_m${i}`).text(dInteriorSeleccionado);
                    $(`#spanDeNecesario_m${i}`).text(dExteriorSeleccionado);

                    console.log(`DI exacto para búsqueda = `, dInteriorSeleccionado);
                    console.log(`DE exacto para búsqueda = `, dExteriorSeleccionado);

                    $(`#spanPorcentAprov_m${i}`).text(`0.00`);

                    window.unirBilletsSeleccionados();

                    console.log(`Excluir billets en la consulta: `, window.billetsSeleccionados);

                    // Validar que los campos requeridos estén completos
                    if (inputDI && inputDE && materialSeleccionado) {
                        // Realizar la llamada AJAX para obtener los billets filtrados
                        $.ajax({
                            url: '../ajax/busqueda_billets_manualmente.php', 
                            type: 'GET',
                            data: { 
                                diametro_interior: dInteriorSeleccionado, // Enviar valores exactos
                                diametro_exterior: dExteriorSeleccionado, // Enviar valores exactos
                                material: materialSeleccionado,
                                stock: alturaNecesario,
                                excluir_billets: window.billetsSeleccionados.join(',')
                            },
                            dataType: 'json',
                            success: function(data) {
                                $(`#tablaBillets_m${i} tbody`).empty();

                                // Verifica que la respuesta tenga datos
                                if (data.length > 0) {

                                    $(`#titleClavesCoincidentes_m${i}`).text(`Billets coincidentes en inventario CNC (${data.length} resultados)`);

                                    let clavesUnicas = {}; // Almacena las claves ya insertadas
                                    let contadorClaves = {}; // Contador de repeticiones de Clave

                                    // Primera pasada: contar las repeticiones
                                    $.each(data, function(index, item) {
                                        if (contadorClaves[item.Clave]) {
                                            contadorClaves[item.Clave]++; // Incrementa si ya existe
                                        } else {
                                            contadorClaves[item.Clave] = 1; // Inicializa en 1 si es la primera vez
                                        }
                                    });

                                    // Primero calculamos y agregamos el porcentaje de aprovechamiento a cada billet
                                    data.forEach(function(item) {
                                        let dataDEBillet = parseFloat(item.exterior);
                                        let dataDEResultante = parseFloat(dExteriorSeleccionado);
                                        let dataDIResultante = parseFloat(dInteriorSeleccionado);
                                        let dataDIBillet = parseFloat(item.interior);

                                        function calcularRadio(valor) {
                                            return (valor / dataDEBillet) * 50;
                                        }

                                        let dimensiones = {
                                            exterior: 50,
                                            azul: calcularRadio(dataDEResultante),
                                            gris: calcularRadio(dataDIResultante),
                                            blanco: calcularRadio(dataDIBillet)
                                        };

                                        let desperdicioPorcentaje = ((Math.pow(dimensiones.exterior, 2) - Math.pow(dimensiones.azul, 2)) +
                                                                    (Math.pow(dimensiones.gris, 2) - Math.pow(dimensiones.blanco, 2))) /
                                                                    Math.pow(dimensiones.exterior, 2) * 100;

                                        let porcentajeAprovechamiento = 100 - desperdicioPorcentaje;
                                        item.porcentajeAprovechamiento = porcentajeAprovechamiento;
                                    });

                                    // Ordenamos los billets por mayor porcentaje de aprovechamiento
                                    data.sort(function(a, b) {
                                        return b.porcentajeAprovechamiento - a.porcentajeAprovechamiento;
                                    });

                                    // Segunda pasada: agregar claves únicas y renglón extra si es necesario
                                    $.each(data, function(index, item) {
                                        let dataStockBillet = item.pre_stock;
                                        let cabenEnBillet = setLeCaben(item.pre_stock);
                                        let dataDEBillet = parseFloat(item.exterior);
                                        let dataDEResultante = parseFloat(dExteriorSeleccionado);
                                        let dataDIResultante = parseFloat(dInteriorSeleccionado);
                                        let dataDIBillet = parseFloat(item.interior);
                                        
                                        // Determinar si mostrar u ocultar el botón de selección
                                        let mostrarBoton = ['Disponible para cotizar', 'En cotización'].includes(item.estatus);
                                        let claseBoton = mostrarBoton ? '' : 'd-none';
                                        let claseEstatus = mostrarBoton ? 'text-success fw-bold' : 'text-danger fw-bold';
                                        let textoAdicionalEstatus = "";

                                        switch(item.estatus){
                                            case "Eliminado": 
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='Preguntar disponibilidad de la barra con stock a inventarios'>
                                                    </i>
                                                `;
                                            break;
                                            case "En cotización":
                                                claseEstatus = 'text-warning fw-bold';
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='La barra se encuentra en una cotización vigente no archivada. Se libera el ${item.fecha_vencimiento} automáticamente o al archivarla.'>
                                                    </i>
                                                `;
                                            break;
                                            case "En uso":
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='La barra se encuentra en una requisición autorizada pendiente de maquinar'>
                                                    </i>
                                                `;
                                            break;
                                            case "Maquinado en curso":
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='La barra se liberará al finalizar el maquinado con el nuevo stock disponible'>
                                                    </i>
                                                `;
                                            break;
                                            case "Clave incorrecta":
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='Comuniquese con inventarios o soporte para validación y cambio de clave'>
                                                    </i>
                                                `;
                                            break;
                                            case "Relación pendiente":
                                            case "Clave nueva pendiente":
                                                textoAdicionalEstatus = `
                                                    <i class='text-secondary bi bi-question-circle-fill btn-detalle-estatus'
                                                        style='padding-left:5px;font-size:20px;'
                                                        data-detalle='Comuniquese con soporte para verificar la relación de claves de esta barra'>
                                                    </i>
                                                `;
                                            break;
                                            default:
                                                textoAdicionalEstatus = "";
                                            break;
                                        }

                                        // Si la clave no ha sido insertada aún, la agrega
                                        if (!clavesUnicas[item.Clave]) {
                                            $(`#tablaBillets_m${i} tbody`).append(
                                                `<tr id="fila_${cleanAttrId(item.Clave)}_m${i}" style="border-top:3px solid #95D2B3 !important;">
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn-general btn-seleccionar-billet_m${i} ${claseBoton}" 
                                                                title="Seleccionar este billet"
                                                                data-clave="${item.Clave}"
                                                                data-altura="${item.pre_stock}"
                                                                data-interior="${item.interior}"
                                                                data-exterior="${item.exterior}"
                                                                data-lote="${item.lote_pedimento}"
                                                                data-proveedor="${item.proveedor}"
                                                                data-manualmente="1"
                                                            ><i class="icon-item bi bi-check2-square"></i></button>
                                                        </div>
                                                    </td>
                                                    <td>${item.Clave}</td>
                                                    <td>${item.porcentajeAprovechamiento.toFixed(2)}%</td>
                                                    <td>${dataStockBillet}</td>
                                                    <td class="${claseEstatus}">${item.estatus=="Eliminado"?"Barra archivada":item.estatus} ${textoAdicionalEstatus}</td>
                                                    <td>${cabenEnBillet}</td>
                                                    <td id="td_interior_${cleanAttrId(item.lote_pedimento)}_m${i}">${item.interior}/${item.exterior}</td>
                                                    <td id="td_lote_${cleanAttrId(item.lote_pedimento)}_m${i}">${item.lote_pedimento}</td>
                                                </tr>`
                                            );

                                            // Marcar esta clave como insertada
                                            clavesUnicas[item.Clave] = true;
                                        } else {
                                            // Si la clave está repetida, la agrega oculta con d-none
                                            $(`#tablaBillets_m${i} tbody`).append(
                                                `<tr class="fila-repetida" data-clave="${item.Clave}" style="display:none;">
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn-general btn-seleccionar-billet_m${i} ${claseBoton}" 
                                                                title="Seleccionar este billet"
                                                                data-clave="${item.Clave}"
                                                                data-altura="${item.pre_stock}"
                                                                data-interior="${item.interior}"
                                                                data-exterior="${item.exterior}"
                                                                data-lote="${item.lote_pedimento}"
                                                                data-proveedor="${item.proveedor}"
                                                                data-manualmente="1"
                                                            ><i class="icon-item bi bi-check2-square"></i></button>
                                                        </div>
                                                    </td>
                                                    <td>${item.Clave}</td>
                                                    <td>${item.porcentajeAprovechamiento.toFixed(2)}%</td>
                                                    <td>${dataStockBillet}</td>
                                                    <td class="${claseEstatus}">${item.estatus=="Eliminado"?"Barra archivada":item.estatus} ${textoAdicionalEstatus}</td>
                                                    <td>${cabenEnBillet}</td>
                                                    <td id="td_interior_${cleanAttrId(item.lote_pedimento)}_m${i}" >${item.interior}/${item.exterior}</td>
                                                    <td id="td_lote_${cleanAttrId(item.lote_pedimento)}_m${i}" >${item.lote_pedimento}</td>
                                                </tr>`
                                            );
                                        }

                                        // Si es el último elemento o si la clave cambia, agrega el botón después de las repetidas
                                        if (
                                            index === data.length - 1 || // Último elemento
                                            (data[index + 1] && data[index + 1].Clave !== item.Clave) // Cambia de clave
                                        ) {
                                            // Si la clave tiene más de 1 repetición, agrega el botón después
                                            if (contadorClaves[item.Clave] > 1) {
                                                
                                                $(`#tablaBillets_m${i} tbody`).append(
                                                    `<tr id="row_${cleanAttrId(item.Clave)}" class="row-ver-mas">
                                                        <td colspan="8" class="p-0">
                                                            <button id="btn_${cleanAttrId(item.Clave)}_m${i}" type="button" class="btn-ver-mas" data-clave="${item.Clave}">
                                                                Mas billets de ${item.Clave} (${contadorClaves[item.Clave]-1})
                                                            </button>
                                                        </td>
                                                    </tr>`
                                                );
                                            }
                                        }
                                    });

                                    // .slideDown .slideUp
                                    // Evento para "Ver más / Ver menos"
                                    $(`#tablaBillets_m${i} tbody`).on('click', '.btn-ver-mas', function() {
                                        let clave = $(this).data('clave'); // Obtener clave
                                        let filasRepetidas = $(`.fila-repetida[data-clave="${clave}"]`);
                                        
                                        if (filasRepetidas.css('display') === 'none') {
                                            filasRepetidas.each(function(index) {
                                                setTimeout(() => {
                                                    $(this).fadeIn(400); // Aplicar fadeIn de manera secuencial
                                                }, 50 * index); // Retraso basado en el índice
                                            });

                                            $(this).text(`Ver menos billets de ${clave}`);
                                        } else {
                                            // Invertir el orden para aplicar el fadeOut de la última a la primera
                                            filasRepetidas.get().reverse().forEach(function(fila, index) {
                                                setTimeout(() => {
                                                    $(fila).fadeOut(400); // Aplicar fadeOut de manera secuencial
                                                }, 50 * index); // Retraso basado en el índice
                                            });

                                            $(this).text(`Mas billets de ${clave} (${contadorClaves[clave]-1})`);
                                        }
                                    });

                                } else {
                                    $(`#titleClavesCoincidentes_m${i}`).text(`Billets coincidentes en inventario CNC (0 resultados)`);
                                    $(`#tablaBillets_m${i} tbody`).append(
                                        '<tr><td colspan="8">No se encontraron billets coincidentes</td></tr>'
                                    );
                                }

                                $(`#circuloSvg_m${i}`).addClass(`d-none`);
                                $(`#spanPorcentDesp_m${i}`).text(`0.00`);
                            },
                            error: function() {
                                console.error('Error al realizar la petición AJAX');
                                $(`#tablaBillets_m${i} tbody`).append('<tr><td colspan="8">Error en la consulta de billets</td></tr>');
                            }
                        });
                    } else {
                        // Mostrar mensaje de error si faltan campos
                        Swal.fire({
                            icon: 'warning',
                            title: 'Campos incompletos',
                            text: 'Por favor, complete todos los campos requeridos para la busqueda manual de billets.',
                            confirmButtonColor: '#55AD9B'
                        });
                    }
                });              
                // SELECCIONAR BILLET DE TABLA BILLETS, CALCULO PB y OBTENER MULTIPLO DE UTILIDAD
                $(`#tablaBillets_m${i}`).on('click', `.btn-seleccionar-billet_m${i}`, function() {
                    let cabenEnBillet;
                    // Obtiene la clave seleccionada
                    let claveSeleccionada = $(this).attr(`data-clave`);
                    let dataInterior = $(this).attr(`data-interior`);
                    let dataExterior = $(this).attr(`data-exterior`);
                    let lotePedimentoSeleccionado = $(this).attr(`data-lote`);
                    let proveedorBillet = $(this).attr(`data-proveedor`);
                    let esBilletSeleccionadoManualmente = $(this).attr(`data-manualmente`) || "0";

                    let clavesAnterior = $(`#inputClaves_m${i}`).val() || "";
                    let billetsAnterior = $(`#inputBillets_m${i}`).val() || ""; 

                    let nuevasClaves = clavesAnterior ? clavesAnterior + ", " + claveSeleccionada : claveSeleccionada;
                    let nuevosBillets = billetsAnterior ? billetsAnterior + ", " + lotePedimentoSeleccionado : lotePedimentoSeleccionado;
                    $(`#inputClaves_m${i}`).val(nuevasClaves);
                    $(`#inputBillets_m${i}`).val(nuevosBillets);

                    console.log(`Los billets son: `, nuevosBillets);

                    let alturaBillet = $(this).attr(`data-altura`);
                    cabenEnBillet = setNecesariosQuedanSobran(alturaBillet, lotePedimentoSeleccionado);
                    $(`#miniTableBillets_m${i} tbody`).append(
                        `<tr>
                            <td>${claveSeleccionada}</td>
                            <td>${lotePedimentoSeleccionado}</td>
                            <td>${alturaBillet}</td>
                            <td>${dataInterior}/${dataExterior}</td>
                            <td>${cabenEnBillet}</td>
                        </tr>`
                    );
                    // Realizar la llamada AJAX para obtener registro con la clave a parametros
                    let materialValue = $(`#selectorMaterial_m${i}`).val();
                    let multiploUtilidad = 0.00;
                    let precio = 0.00;
                    let max_usable = 0.00;
                    let altura = 0.00;
                    let diametroInteriorValue = $(`#diametro_interior_mm_m${i}`).val() || 0;

                    $.when(
                        // Realizar la llamada AJAX para obtener el PRECIO DE BARRA EN FASTSEAL
                        $.ajax({
                            url: '../ajax/ajax_parametros.php', 
                            type: 'POST',
                            data: { 
                                clave: claveSeleccionada
                            },
                            dataType: 'json',
                            success: function(data) {
                                // Verifica que la respuesta tenga datos
                                if (data.length > 0) {
                                    // calcular precio de la barra
                                    precioBarra = 0.00;
                                    precio = parseFloat(data[0].precio);
                                    max_usable = parseFloat(data[0].max_usable);
                                    altura = parseFloat($(`#altura_mm_m${i}`).val());

                                    console.log(`Precio = `, precio);
                                    console.log(`Max Usable = `, max_usable);
                                    console.log(`Altura = `, altura);
                                    console.log(`Desbaste por Dureza = `, window[`DESBASTE_DUREZA_m${i}`]);
                                    console.log(`Desbaste por Agarre de la Maquina = `, window.MEDIDA_AGARRE_MAQUINA);
                                    if (isNaN(precio) || isNaN(max_usable) || isNaN(altura)) {
                                        console.error('Uno o más valores no son números válidos.');
                                        $(`#miniTableCostoBarra_m${i} tbody`).append('<tr><td colspan="4">Uno o más valores no son números válidos.</td></tr>');
                                    } else {
                                        console.log(`((precio/max_usable)(altura + desbasteDureza))`);
                                        console.log("(",precio,"/",max_usable,")(",altura," + ",window[`DESBASTE_DUREZA_m${i}`],"))");
                                        console.log("(",(precio/max_usable),")(",altura + window[`DESBASTE_DUREZA_m${i}`],"))");
                                        // PRECIO DE LA BARRA
                                        precioBarra = ((precio / max_usable) * (altura + window[`DESBASTE_DUREZA_m${i}`]));
                                        console.log("precio de barra = ",precioBarra,"");
                                        console.log(`------------------------`);
                                    }
                                } else {
                                    $(`#miniTableCostoBarra_m${i} tbody`).append(`<tr><td colspan="4" style="color:#dc3545;">La clave ${claveSeleccionada} no fue encontrada para calcular el precio. CNC debe corregir la clave.</td></tr>`);
                                }
                            },
                            error: function() {
                                console.error('Error al realizar la petición AJAX');
                                $(`#miniTableCostoBarra_m${i} tbody`).append('<tr><td colspan="4">Error en ajax</td></tr>');
                            }
                        }),
                        // Realizar la llamada AJAX para obtener el MULTIPLO DE UTILIDAD
                        $.ajax({
                            url: '../ajax/ajax_multiplo_utilidad.php', 
                            type: 'GET',
                            data: { 
                                di: diametroInteriorValue,
                                material: materialValue,
                                proveedor: proveedorBillet
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data && data.valor !== undefined) {
                                    multiploUtilidad = parseFloat(data.valor);
                                    console.log(`Multiplo Utilidad = `, multiploUtilidad);
                                } else {
                                    $(`#miniTableCostoBarra_m${i} tbody`).append(
                                        `<tr><td colspan="4" style="color:#dc3545;">La clave ${claveSeleccionada} no fue encontrada para calcular el precio.</td></tr>`
                                    );
                                }
                            },
                            error: function() {
                                console.error('Error al realizar la petición AJAX');
                                $(`#miniTableCostoBarra_m${i} tbody`).append('<tr><td colspan="4">Error en ajax</td></tr>');
                            }
                        })
                    ).done(function () {
                        let precioBarraUtilidad = 0.00;
                        precioBarraUtilidad = (precioBarra) * (multiploUtilidad);
                        console.log(`Precio Final de la Barra = ((precioBarra)(multiploUtilidad)) = `, precioBarraUtilidad);
                        
                        let obj = window[`BILLETS_SELECCIONADOS_OCUPA_m${i}`].find(item => item.lote_pedimento === lotePedimentoSeleccionado);
                        let ocupaEnBillet = 0;
                        if (obj) {
                            ocupaEnBillet = obj.ocupa; 
                        } else {
                            console.warn(`Lote no encontrado`);
                        }

                        let totalPrecioBarraUtilidad = precioBarraUtilidad * ocupaEnBillet;
                        
                        $(`#miniTableCostoBarra_m${i} tbody`).append(
                            `<tr>
                            <td>${claveSeleccionada}</td>
                            <td>${lotePedimentoSeleccionado}</td>
                                <td>${alturaBillet}</td>
                                <td>${dataInterior}/${dataExterior}</td>
                                <td>${ocupaEnBillet}</td>
                                <td>${precioBarraUtilidad.toFixed(2)}</td> 
                                <td>${totalPrecioBarraUtilidad.toFixed(2)}</td> 
                            </tr>`
                        );

                        let billetInfoStringLote = lotePedimentoSeleccionado + " (" + dataInterior + "/" + dataExterior + ") " + ocupaEnBillet + " pz";
                        let billetInfoString = claveSeleccionada + " (" + dataInterior + "/" + dataExterior + ") " + ocupaEnBillet + " pz";
                        window[`PRECIO_BARRAS_m${i}`] += totalPrecioBarraUtilidad;
                        window[`billetsSeleccionados_m${i}`].push(lotePedimentoSeleccionado);
                        window[`BILLETS_SELECCIONADOS_LOTES_m${i}`].push(billetInfoStringLote);
                        window[`BILLETS_SELECCIONADOS_STRING_m${i}`].push(billetInfoString);
                        if(esBilletSeleccionadoManualmente === "1"){
                            window[`BILLETS_SELECCIONADOS_MANUALMENTE_m${i}`].push(lotePedimentoSeleccionado);
                        }

                        $(`#precioBarra_m${i}`).val(window[`PRECIO_BARRAS_m${i}`].toFixed(2));

                        console.log(`Billets seleccionados_m${i} son: `, window[`billetsSeleccionados_m${i}`]);
                        console.log(`Billets Lotes string m${i} son: `, window[`BILLETS_SELECCIONADOS_LOTES_m${i}`]);
                        console.log(`Billets string m${i} son: `, window[`BILLETS_SELECCIONADOS_STRING_m${i}`]);
                        console.log(`Billets manualmente m${i} son: `, window[`BILLETS_SELECCIONADOS_MANUALMENTE_m${i}`]);

                        console.log({
                            // Globales
                            CANTIDAD_PIEZAS_TEMPORAL: window[`CANTIDAD_PIEZAS_TEMPORAL_m${i}`],
                            SELLOS_RESTANTES: window[`SELLOS_RESTANTES_m${i}`],
                            MILIMETROS_RESTANTES: window[`MILIMETROS_RESTANTES_m${i}`],
                            BILLETS_SELECCIONADOS_OCUPA: window[`BILLETS_SELECCIONADOS_OCUPA_m${i}`],
    
                            // Locales
                            claveSeleccionada,
                            dataInterior,
                            dataExterior,
                            lotePedimentoSeleccionado,
                            clavesAnterior,
                            billetsAnterior,
                            nuevasClaves,
                            nuevosBillets,
                            alturaBillet,
                            cabenEnBillet,
                            materialValue,
                            multiploUtilidad,
                            precio,
                            max_usable,
                            altura,
                            diametroInteriorValue,
                            precioBarra,
                            billetInfoString,
                        });
                    }).fail(function () {
                        alert(`Hubo un error al obtener los datos.`);
                        console.error(`Error en una de las solicitudes AJAX.`);
                    });

                    habilitarBoton(`#btnLimpiarSeleccion_m${i}`);
                });                
                // dibujar el circulo del billet, representacion de aprovechamiento
                $(`#tablaBillets_m${i}`).on('click', `.btn-circulo-billet_m${i}`, function () {
                    // Obtener los valores desde los atributos del botón y convertirlos a número
                    let dataDEBillet = parseFloat($(this).attr(`data-de-billet`));
                    let dataDEResultante = parseFloat($(this).attr(`data-de-resultante`));
                    let dataDIResultante = parseFloat($(this).attr(`data-di-resultante`));
                    let dataDIBillet = parseFloat($(this).attr(`data-di-billet`));
                
                    $(`#circuloSvg_m${i}`).removeClass(`d-none`);

                    // Función para calcular el radio en porcentaje basado en el diámetro exterior (100%)
                    const calcularRadio = function(valor) {
                        return (valor / dataDEBillet) * 50; // Dividimos por 2 para obtener el radio
                    };
                
                    // Definir los radios de los círculos en porcentaje
                    let dimensiones = {
                        exterior: 50, // Radio máximo = 50% (diámetro del 100%)
                        azul: calcularRadio(dataDEResultante),
                        gris: calcularRadio(dataDIResultante),
                        blanco: calcularRadio(dataDIBillet)
                    };
                
                    const dibujarCirculo = function() {
                        let svg = d3.select(`#circuloSvg_m${i}`);
                        svg.selectAll(`*`).remove(); // Limpiar antes de redibujar
                
                        // Círculo exterior (gris)
                        svg.append(`circle`)
                            .attr(`cx`, 50).attr(`cy`, 50)
                            .attr(`r`, dimensiones.exterior)
                            .attr(`fill`, "#8c9095");
                
                        // Círculo del anillo azul
                        svg.append(`circle`)
                            .attr(`cx`, 50).attr(`cy`, 50)
                            .attr(`r`, dimensiones.azul)
                            .attr(`fill`, "#3657c4");
                
                        // Círculo interior (gris)
                        svg.append(`circle`)
                            .attr(`cx`, 50).attr(`cy`, 50)
                            .attr(`r`, dimensiones.gris)
                            .attr(`fill`, "#8c9095");
                
                        // Círculo blanco (centro)
                        if (dimensiones.blanco > 0) {
                            svg.append(`circle`)
                                .attr(`cx`, 50).attr(`cy`, 50)
                                .attr(`r`, dimensiones.blanco)
                                .attr(`fill`, "#fff");
                        }

                        // Calcular porcentaje de desperdicio
                        let desperdicio = ((Math.pow(dimensiones.exterior, 2) - Math.pow(dimensiones.azul, 2)) +
                        (Math.pow(dimensiones.gris, 2) - Math.pow(dimensiones.blanco, 2))) /
                        Math.pow(dimensiones.exterior, 2) * 100;

                        let aprovechamiento = 100 - desperdicio;
                        // Actualizar el span con el porcentaje de desperdicio
                        $(`#spanPorcentAprov_m${i}`).text(aprovechamiento.toFixed(2));
                    };
                
                    // Dibujar el círculo con los valores obtenidos
                    dibujarCirculo();
                });
                //  ************** MODAL DE CLAVES DE BARRAS SIMULACION
                // EVENTO VER MODAL DE BARRAS DESDE PARAMETROS
                $(`#btnBilletsSimulacion_m${i}`).on(`click`, function() {
                    let materialSeleccionado = $(`#selectorMaterial_m${i}`).val();
                    let proveedorSeleccionado = $(`#selectorProveedor_m${i}`).val();
                    let alturaSeleccionada = parseFloat($(`#altura_mm_m${i}`).val());
                    let cantidadDigitada = $(`#inputCantidad_m${i}`).val();
                    alturaSeleccionada = parseFloat(alturaSeleccionada);
                    let dInteriorSeleccionado = parseFloat($(`#diametro_interior_mm_m${i}`).val());
                    let dExteriorSeleccionado = parseFloat($(`#diametro_exterior_mm_m${i}`).val());
                    if(!materialSeleccionado || materialSeleccionado === "" || materialSeleccionado==null){
                        $(`#selectorMaterial_m${i}`).val("");
                        $(`#btnCerrarModalBilletsSimulacion_m${i}`).trigger("click");
                        $(`#tablaBilletsSimulacion_m${i} tbody`).empty();
                        sweetAlertResponse("warning", "Falta información","Seleccione un material", "none");
                        return;
                    }
                    if(!proveedorSeleccionado || proveedorSeleccionado === "" || proveedorSeleccionado==null){
                        $(`#selectorProveedor_m${i}`).val("");
                        sweetAlertResponse("warning", "Falta información","Seleccione un proveedor", "none");
                        return;
                    }
                    console.log(`Aplicando calculos al Material _m${i}`);
                    // if(window.FAMILIA_PERFIL === "backup" || window.FAMILIA_PERFIL === "guide"){
                    //     window.DI_TOLERANCIA_DEFAULT = 1.00;
                    //     window.DE_TOLERANCIA_DEFAULT = 1.00;
                    // }else{
                    //     window.DI_TOLERANCIA_DEFAULT = 3.00;
                    //     window.DE_TOLERANCIA_DEFAULT = 1.00;
                    // }
                    console.log(`Desperdicio default DI = `, window.DI_TOLERANCIA_DEFAULT);
                    console.log(`Desperdicio default DE = `, window.DE_TOLERANCIA_DEFAULT);                        


                    $(`#spanAlturaCliente_m${i}`).text(alturaSeleccionada);
                    $(`#spanDiCliente_m${i}`).text(dInteriorSeleccionado);
                    $(`#spanDeCliente_m${i}`).text(dExteriorSeleccionado);
                    
                    $(`#spanPorcentAprov_m${i}`).text(`0.00`);

                    window.unirBilletsSeleccionados();

                    console.log(`Excluir billets en la consulta: `, window.billetsSeleccionados);

                    if (materialSeleccionado) {
                        // Realizar la llamada AJAX para obtener los billets filtrados
                        // claves_simulacion consulta con proveedor y claves_simulacion2 no
                        $.ajax({
                            url: '../ajax/claves_simulacion.php', 
                            type: 'GET',
                            data: { 
                                material: materialSeleccionado,
                                proveedor: proveedorSeleccionado,
                                diametro_interior_mm: dInteriorSeleccionado,
                                diametro_exterior_mm: dExteriorSeleccionado,
                                u: "a"
                            },
                            dataType: 'json',
                            success: function(data) {
                                $(`#tablaBilletsSimulacion_m${i} tbody`).empty();
                                data = data.claves;
                                // Verifica que la respuesta tenga datos
                                if (data.length > 0) {

                                    $(`#titleClavesCoincidentesSimulacion_m${i}`).text(`Claves de barras coincidentes (${data.length} resultados)`);

                                    let clavesUnicas = {}; // Almacena las claves ya insertadas
                                    let contadorClaves = {}; // Contador de repeticiones de Clave

                                    // Primera pasada: contar las repeticiones
                                    $.each(data, function(index, item) {
                                        if (contadorClaves[item.clave]) {
                                            contadorClaves[item.clave]++; // Incrementa si ya existe
                                        } else {
                                            contadorClaves[item.clave] = 1; // Inicializa en 1 si es la primera vez
                                        }
                                    });

                                    // Primero calculamos y agregamos el porcentaje de aprovechamiento a cada billet
                                    data.forEach(function(item) {
                                        let dataDEBillet = parseFloat(item.exterior);
                                        let dataDEResultante = parseFloat(dExteriorSeleccionado);
                                        let dataDIResultante = parseFloat(dInteriorSeleccionado);
                                        let dataDIBillet = parseFloat(item.interior);

                                        function calcularRadio(valor) {
                                            return (valor / dataDEBillet) * 50;
                                        }

                                        let dimensiones = {
                                            exterior: 50,
                                            azul: calcularRadio(dataDEResultante),
                                            gris: calcularRadio(dataDIResultante),
                                            blanco: calcularRadio(dataDIBillet)
                                        };

                                        let desperdicioPorcentaje = ((Math.pow(dimensiones.exterior, 2) - Math.pow(dimensiones.azul, 2)) +
                                                                    (Math.pow(dimensiones.gris, 2) - Math.pow(dimensiones.blanco, 2))) /
                                                                    Math.pow(dimensiones.exterior, 2) * 100;

                                        let porcentajeAprovechamiento = 100 - desperdicioPorcentaje;
                                        item.porcentajeAprovechamiento = porcentajeAprovechamiento;
                                    });

                                    // Ordenamos los billets por mayor porcentaje de aprovechamiento
                                    data.sort(function(a, b) {
                                        return b.porcentajeAprovechamiento - a.porcentajeAprovechamiento;
                                    });

                                    // Segunda pasada: agregar claves únicas y renglón extra si es necesario
                                    $.each(data, function(index, item) {
                                        
                                        
                                        let dataDEBillet = parseFloat(item.exterior);
                                        let dataDEResultante = parseFloat(dExteriorSeleccionado);
                                        let dataDIResultante = parseFloat(dInteriorSeleccionado);
                                        let dataDIBillet = parseFloat(item.interior);

                                        function calcularRadio(valor) {
                                            return (valor / dataDEBillet) * 50;
                                        }

                                        let dimensiones = {
                                            exterior: 50,
                                            azul: calcularRadio(dataDEResultante),
                                            gris: calcularRadio(dataDIResultante),
                                            blanco: calcularRadio(dataDIBillet)
                                        };

                                        // Recuperamos el porcentaje calculado previamente
                                        let porcentajeAprovechamiento = item.porcentajeAprovechamiento;
                                        let sumatoriaAlturaMM = (alturaSeleccionada + window[`DESBASTE_DUREZA_m${i}`]+ window.MEDIDA_AGARRE_MAQUINA)*(cantidadDigitada);
                                        // Si la clave no ha sido insertada aún, la agrega
                                        if (!clavesUnicas[item.clave]) {
                                            $(`#tablaBilletsSimulacion_m${i} tbody`).append(
                                                `<tr id="fila_${cleanAttrId(item.clave)}_m${i}" style="border-top:3px solid #95D2B3 !important;">
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn-general btn-seleccionar-billet_m${i} " 
                                                                title="Seleccionar este billet"
                                                                data-clave="${item.clave}"
                                                                data-altura="${sumatoriaAlturaMM}"
                                                                data-interior="${item.interior}"
                                                                data-exterior="${item.exterior}"
                                                                
                                                                data-proveedor="${item.proveedor}"
                                                                data-manualmente="0"
                                                            ><i class="icon-item bi bi-check2-square"></i></button>

                                                            <button type="button" class="btn-general btn-circulo-billet_m${i}" 
                                                                title="Ver representacion de porcentaje de aprovechamiento"
                                                                data-altura-resultante="${alturaSeleccionada}"
                                                                data-di-resultante="${dInteriorSeleccionado}"
                                                                data-de-resultante="${dExteriorSeleccionado}"

                                                                data-di-billet="${item.interior}"
                                                                data-de-billet="${item.exterior}"
                                                            ><div class="d-flex justify-content-center align-items-center"><i class="icon-item bi bi-vinyl"></i><i class="bi bi-percent"></i></div></button>
                                                        </div>
                                                    </td>
                                                    <td>${item.clave}</td>
                                                    <td>${porcentajeAprovechamiento.toFixed(2)}%</td>
                                                    <td>${item.interior}/${item.exterior}</td>
                                                    <td>${item.max_usable}</td>
                                                    <td>${item.material}</td>
                                                </tr>`
                                            );

                                            // Marcar esta clave como insertada
                                            clavesUnicas[item.clave] = true;
                                        } else {
                                            // Si la clave está repetida, la agrega oculta con d-none
                                            $(`#tablaBilletsSimulacion_m${i} tbody`).append(
                                                `<tr class="fila-repetida" data-clave="${item.clave}" style="display:none;">
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button type="button" class="btn-general btn-seleccionar-billet_m${i} " 
                                                                title="Seleccionar este billet"
                                                                data-clave="${item.clave}"
                                                                
                                                                data-interior="${item.interior}"
                                                                data-exterior="${item.exterior}"
                                                               
                                                                data-proveedor="${item.proveedor}"
                                                                data-manualmente="0"
                                                            ><i class="icon-item bi bi-check2-square"></i></button>

                                                            <button type="button" class="btn-general btn-circulo-billet_m${i}" 
                                                                title="Ver representacion de porcentaje de desperdicio"
                                                                data-altura-resultante="${alturaSeleccionada}"
                                                                data-di-resultante="${dInteriorSeleccionado}"
                                                                data-de-resultante="${dExteriorSeleccionado}"

                                                                data-di-billet="${item.interior}"
                                                                data-de-billet="${item.exterior}"
                                                            ><div class="d-flex justify-content-center align-items-center"><i class="icon-item bi bi-vinyl"></i><i class="bi bi-percent"></i></div></button>
                                                        </div>
                                                    </td>
                                                    <td>${item.clave}</td>
                                                    <td>${porcentajeAprovechamiento.toFixed(2)}%</td>
                                                    <td>${item.interior}/${item.exterior}</td>
                                                    <td>${item.max_usable}</td>
                                                    <td>${item.material}</td>
                                                </tr>`
                                            );
                                        }

                                        // Si es el último elemento o si la clave cambia, agrega el botón después de las repetidas
                                        if (
                                            index === data.length - 1 || // Último elemento
                                            (data[index + 1] && data[index + 1].Clave !== item.clave) // Cambia de clave
                                        ) {
                                            // Si la clave tiene más de 1 repetición, agrega el botón después
                                            if (contadorClaves[item.clave] > 1) {
                                                
                                                $(`#tablaBilletsSimulacion_m${i} tbody`).append(
                                                    `<tr id="row_${cleanAttrId(item.clave)}" class="row-ver-mas">
                                                        <td colspan="8" class="p-0">
                                                            <button id="btn_${cleanAttrId(item.clave)}_m${i}" type="button" class="btn-ver-mas" data-clave="${item.Clave}">
                                                                Mas billets de ${item.clave} (${contadorClaves[item.clave]-1})
                                                            </button>
                                                        </td>
                                                    </tr>`
                                                );
                                            }
                                        }
                                    });

                                    // .slideDown .slideUp
                                    // Evento para "Ver más / Ver menos"
                                    $(`#tablaBilletsSimulacion_m${i} tbody`).on('click', '.btn-ver-mas', function() {
                                        let clave = $(this).data('clave'); // Obtener clave
                                        let filasRepetidas = $(`.fila-repetida[data-clave="${clave}"]`);
                                        
                                        if (filasRepetidas.css('display') === 'none') {
                                            filasRepetidas.each(function(index) {
                                                setTimeout(() => {
                                                    $(this).fadeIn(400); // Aplicar fadeIn de manera secuencial
                                                }, 50 * index); // Retraso basado en el índice
                                            });

                                            $(this).text(`Ver menos billets de ${clave}`);
                                        } else {
                                            // Invertir el orden para aplicar el fadeOut de la última a la primera
                                            filasRepetidas.get().reverse().forEach(function(fila, index) {
                                                setTimeout(() => {
                                                    $(fila).fadeOut(400); // Aplicar fadeOut de manera secuencial
                                                }, 50 * index); // Retraso basado en el índice
                                            });

                                            $(this).text(`Mas billets de ${clave} (${contadorClaves[clave]-1})`);
                                        }
                                    });
                                    $(`#containerBusquedaManual__m${i}`).addClass(`d-none`);

                                } else {
                                    $(`#titleClavesCoincidentes_m${i}`).text(`Claves coincidentes en inventario CNC (0 resultados)`);
                                    $(`#tablaBilletsSimulacion_m${i} tbody`).append(
                                        '<tr><td colspan="6">No se encontraron billets coincidentes</td></tr>'
                                    );
                                    $(`#containerBusquedaManual__m${i}`).removeClass(`d-none`);
                                }
                                
                                $(`#circuloSvg_m${i}`).addClass(`d-none`);
                                $(`#spanPorcentDesp_m${i}`).text(`0.00`);
                            },
                            error: function() {
                                console.error('Error al realizar la petición AJAX');
                                $(`#tablaBilletsSimulacion_m${i} tbody`).append('<tr><td colspan="4">Error en ajax</td></tr>');
                            }
                        });
                    }
                });      
                // SELECCIONAR BILLET DE TABLA BILLETS, CALCULO PB y OBTENER MULTIPLO DE UTILIDAD
                $(`#tablaBilletsSimulacion_m${i}`).on('click', `.btn-seleccionar-billet_m${i}`, function() {
                    let cabenEnBillet;
                    // Obtiene la clave seleccionada
                    let claveSeleccionada = $(this).attr(`data-clave`);
                    let dataInterior = $(this).attr(`data-interior`);
                    let dataExterior = $(this).attr(`data-exterior`);
                    let lotePedimentoSeleccionado = $(this).attr(`data-clave`);
                    let proveedorBillet = $(this).attr(`data-proveedor`);
                    let esBilletSeleccionadoManualmente = $(this).attr(`data-manualmente`) || "0";

                    let clavesAnterior = $(`#inputClaves_m${i}`).val() || "";
                    let billetsAnterior = $(`#inputBillets_m${i}`).val() || ""; 

                    let nuevasClaves = clavesAnterior ? clavesAnterior + ", " + claveSeleccionada : claveSeleccionada;
                    let nuevosBillets = billetsAnterior ? billetsAnterior + ", " + lotePedimentoSeleccionado : lotePedimentoSeleccionado;
                    $(`#inputClaves_m${i}`).val(nuevasClaves);
                    $(`#inputBillets_m${i}`).val(nuevosBillets);

                    console.log(`Los billets son: `, nuevosBillets);

                    let alturaBillet = $(this).attr(`data-altura`);
                    cabenEnBillet = setNecesariosQuedanSobran(alturaBillet, lotePedimentoSeleccionado);
                    $(`#containerBarraSeleccionadaSimulacion_m${i} span`).text(`Barra: ${claveSeleccionada} (${dataInterior}/${dataExterior}) seleccionada`);
                    // Realizar la llamada AJAX para obtener registro con la clave a parametros
                    let materialValue = $(`#selectorMaterial_m${i}`).val();
                    let multiploUtilidad = 0.00;
                    let precio = 0.00;
                    let max_usable = 0.00;
                    let altura = 0.00;
                    let diametroInteriorValue = $(`#diametro_interior_mm_m${i}`).val() || 0;

                    $.when(
                        // Realizar la llamada AJAX para obtener el PRECIO DE BARRA EN FASTSEAL
                        $.ajax({
                            url: '../ajax/ajax_parametros.php', 
                            type: 'POST',
                            data: { 
                                clave: claveSeleccionada
                            },
                            dataType: 'json',
                            success: function(data) {
                                // Verifica que la respuesta tenga datos
                                if (data.length > 0) {
                                    // calcular precio de la barra
                                    precioBarra = 0.00;
                                    precio = parseFloat(data[0].precio);
                                    max_usable = parseFloat(data[0].max_usable);
                                    altura = parseFloat($(`#altura_mm_m${i}`).val());

                                    console.log(`Precio = `, precio);
                                    console.log(`Max Usable = `, max_usable);
                                    console.log(`Altura = `, altura);
                                    console.log(`Desbaste por Dureza = `, window[`DESBASTE_DUREZA_m${i}`]);
                                    console.log(`Desbaste por Agarre de la Maquina = `, window.MEDIDA_AGARRE_MAQUINA);
                                    if (isNaN(precio) || isNaN(max_usable) || isNaN(altura)) {
                                        console.error('Uno o más valores no son números válidos.');
                                        $(`#miniTableCostoBarra_m${i} tbody`).append('<tr><td colspan="4">Uno o más valores no son números válidos.</td></tr>');
                                    } else {
                                        console.log(`((precio/max_usable)(altura + desbasteDureza))`);
                                        console.log("(",precio,"/",max_usable,")(",altura," + ",window[`DESBASTE_DUREZA_m${i}`],"))");
                                        console.log("(",(precio/max_usable),")(",altura + window[`DESBASTE_DUREZA_m${i}`],"))");
                                        // PRECIO DE LA BARRA
                                        precioBarra = ((precio / max_usable) * (altura + window[`DESBASTE_DUREZA_m${i}`]));
                                        console.log("precio de barra = ",precioBarra,"");
                                        console.log(`------------------------`);
                                    }
                                } else {
                                    $(`#miniTableCostoBarra_m${i} tbody`).append(`<tr><td colspan="4" style="color:#dc3545;">La clave ${claveSeleccionada} no fue encontrada para calcular el precio. CNC debe corregir la clave.</td></tr>`);
                                }
                            },
                            error: function() {
                                console.error('Error al realizar la petición AJAX');
                                $(`#miniTableCostoBarra_m${i} tbody`).append('<tr><td colspan="4">Error en ajax</td></tr>');
                            }
                        }),
                        // Realizar la llamada AJAX para obtener el MULTIPLO DE UTILIDAD
                        $.ajax({
                            url: '../ajax/ajax_multiplo_utilidad.php', 
                            type: 'GET',
                            data: { 
                                di: diametroInteriorValue,
                                material: materialValue,
                                proveedor: proveedorBillet
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data && data.valor !== undefined) {
                                    multiploUtilidad = parseFloat(data.valor);
                                    console.log(`Multiplo Utilidad = `, multiploUtilidad);
                                } else {
                                    $(`#miniTableCostoBarra_m${i} tbody`).append(
                                        `<tr><td colspan="4" style="color:#dc3545;">La clave ${claveSeleccionada} no fue encontrada para calcular el precio.</td></tr>`
                                    );
                                }
                            },
                            error: function() {
                                console.error('Error al realizar la petición AJAX');
                                $(`#miniTableCostoBarra_m${i} tbody`).append('<tr><td colspan="4">Error en ajax</td></tr>');
                            }
                        })
                    ).done(function () {
                        let precioBarraUtilidad = 0.00;
                        precioBarraUtilidad = (precioBarra) * (multiploUtilidad);
                        console.log(`Precio Final de la Barra = ((precioBarra)(multiploUtilidad)) = `, precioBarraUtilidad);
                        
                        let obj = window[`BILLETS_SELECCIONADOS_OCUPA_m${i}`].find(item => item.lote_pedimento === lotePedimentoSeleccionado);
                        let ocupaEnBillet = 0;
                        if (obj) {
                            ocupaEnBillet = obj.ocupa; 
                        } else {
                            console.warn(`Lote no encontrado`);
                        }

                        let totalPrecioBarraUtilidad = precioBarraUtilidad * ocupaEnBillet;
                        
                        $(`#miniTableCostoBarra_m${i} tbody`).append(
                            `<tr>
                                <td>${claveSeleccionada}</td>
                                <td>${"NA"}</td>
                                <td>${"NA"}</td>
                                <td>${dataInterior}/${dataExterior}</td>
                                <td>${ocupaEnBillet}</td>
                                <td>${precioBarraUtilidad.toFixed(2)}</td> 
                                <td>${totalPrecioBarraUtilidad.toFixed(2)}</td> 
                            </tr>`
                        );

                        let billetInfoStringLote = lotePedimentoSeleccionado + " (" + dataInterior + "/" + dataExterior + ") " + ocupaEnBillet + " pz";
                        let billetInfoString = claveSeleccionada + " (" + dataInterior + "/" + dataExterior + ") " + ocupaEnBillet + " pz";
                        window[`PRECIO_BARRAS_m${i}`] += totalPrecioBarraUtilidad;
                        window[`billetsSeleccionados_m${i}`].push(lotePedimentoSeleccionado);
                        window[`BILLETS_SELECCIONADOS_LOTES_m${i}`].push(billetInfoStringLote);
                        window[`BILLETS_SELECCIONADOS_STRING_m${i}`].push(billetInfoString);
                        if(esBilletSeleccionadoManualmente === "1"){
                            window[`BILLETS_SELECCIONADOS_MANUALMENTE_m${i}`].push(lotePedimentoSeleccionado);
                        }

                        $(`#precioBarra_m${i}`).val(window[`PRECIO_BARRAS_m${i}`].toFixed(2));

                        console.log(`Billets seleccionados_m${i} son: `, window[`billetsSeleccionados_m${i}`]);
                        console.log(`Billets Lotes string m${i} son: `, window[`BILLETS_SELECCIONADOS_LOTES_m${i}`]);
                        console.log(`Billets string m${i} son: `, window[`BILLETS_SELECCIONADOS_STRING_m${i}`]);
                        console.log(`Billets manualmente m${i} son: `, window[`BILLETS_SELECCIONADOS_MANUALMENTE_m${i}`]);

                        console.log({
                            // Globales
                            CANTIDAD_PIEZAS_TEMPORAL: window[`CANTIDAD_PIEZAS_TEMPORAL_m${i}`],
                            SELLOS_RESTANTES: window[`SELLOS_RESTANTES_m${i}`],
                            MILIMETROS_RESTANTES: window[`MILIMETROS_RESTANTES_m${i}`],
                            BILLETS_SELECCIONADOS_OCUPA: window[`BILLETS_SELECCIONADOS_OCUPA_m${i}`],
    
                            // Locales
                            claveSeleccionada,
                            dataInterior,
                            dataExterior,
                            lotePedimentoSeleccionado,
                            clavesAnterior,
                            billetsAnterior,
                            nuevasClaves,
                            nuevosBillets,
                            alturaBillet,
                            cabenEnBillet,
                            materialValue,
                            multiploUtilidad,
                            precio,
                            max_usable,
                            altura,
                            diametroInteriorValue,
                            precioBarra,
                            billetInfoString,
                        });
                    }).fail(function () {
                        alert(`Hubo un error al obtener los datos.`);
                        console.error(`Error en una de las solicitudes AJAX.`);
                    });

                    habilitarBoton(`#btnLimpiarSeleccion_m${i}`);
                    setTimeout(() => {
                        $(`#btnSiguiente_m${i}`).click();
                    }, 400);
                });                
                // dibujar el circulo del billet, representacion de aprovechamiento
                $(`#tablaBilletsSimulacion_m${i}`).on('click', `.btn-circulo-billet_m${i}`, function () {
                    // Obtener los valores desde los atributos del botón y convertirlos a número
                    let dataDEBillet = parseFloat($(this).attr(`data-de-billet`));
                    let dataDEResultante = parseFloat($(this).attr(`data-de-resultante`));
                    let dataDIResultante = parseFloat($(this).attr(`data-di-resultante`));
                    let dataDIBillet = parseFloat($(this).attr(`data-di-billet`));

                    $(`#containerBodyModalBillets_m${i}`).css("width", "62%");
                    $(`#container38Simulacion_m${i}`).removeClass(`d-none`).css("width", "38%");
                    $(`#containerCircleBilletSimulacion_m${i}`).removeClass(`d-none`);
                    $(`#circuloSvgSimulacion_m${i}`).removeClass(`d-none`);
                    $(`#btnQuitarCircleSimulacion_m${i}`).removeClass(`d-none`);
                    // Función para calcular el radio en porcentaje basado en el diámetro exterior (100%)
                    const calcularRadio = function(valor) {
                        return (valor / dataDEBillet) * 50; // Dividimos por 2 para obtener el radio
                    };
                
                    // Definir los radios de los círculos en porcentaje
                    let dimensiones = {
                        exterior: 50, // Radio máximo = 50% (diámetro del 100%)
                        azul: calcularRadio(dataDEResultante),
                        gris: calcularRadio(dataDIResultante),
                        blanco: calcularRadio(dataDIBillet)
                    };
                
                    const dibujarCirculo = function() {
                        let svg = d3.select(`#circuloSvgSimulacion_m${i}`);
                        svg.selectAll(`*`).remove(); // Limpiar antes de redibujar
                
                        // Círculo exterior (gris)
                        svg.append(`circle`)
                            .attr(`cx`, 50).attr(`cy`, 50)
                            .attr(`r`, dimensiones.exterior)
                            .attr(`fill`, "#8c9095");
                
                        // Círculo del anillo azul
                        svg.append(`circle`)
                            .attr(`cx`, 50).attr(`cy`, 50)
                            .attr(`r`, dimensiones.azul)
                            .attr(`fill`, "#3657c4");
                
                        // Círculo interior (gris)
                        svg.append(`circle`)
                            .attr(`cx`, 50).attr(`cy`, 50)
                            .attr(`r`, dimensiones.gris)
                            .attr(`fill`, "#8c9095");
                
                        // Círculo blanco (centro)
                        if (dimensiones.blanco > 0) {
                            svg.append(`circle`)
                                .attr(`cx`, 50).attr(`cy`, 50)
                                .attr(`r`, dimensiones.blanco)
                                .attr(`fill`, "#fff");
                        }

                        // Calcular porcentaje de desperdicio
                        let desperdicio = ((Math.pow(dimensiones.exterior, 2) - Math.pow(dimensiones.azul, 2)) +
                        (Math.pow(dimensiones.gris, 2) - Math.pow(dimensiones.blanco, 2))) /
                        Math.pow(dimensiones.exterior, 2) * 100;

                        let aprovechamiento = 100 - desperdicio;
                        // Actualizar el span con el porcentaje de desperdicio
                        $(`#spanPorcentAprovSimulacion_m${i}`).text(aprovechamiento.toFixed(2));
                    };
                
                    // Dibujar el círculo con los valores obtenidos
                    dibujarCirculo();
                });
                // quitar la representacion del % de aprovechamiento
                $(`#btnCerrarModalBilletsSimulacion_m${i}, #btnQuitarCircleSimulacion_m${i}`).on('click', function(){
                    $(`#containerBodyModalBilletsSimulacion_m${i}`).css("width", "100%");
                    $(`#container38Simulacion_m${i}`).addClass(`d-none`).css("width", "0%");
                    $(`#containerCircleBilletSimulacion_m${i}`).addClass(`d-none`);
                    $(`#circuloSvgSimulacion_m${i}`).addClass(`d-none`);
                    $(`#btnQuitarCircleSimulacion_m${i}`).addClass(`d-none`);
                });
                // *******************************************
                // RESETEAR LAS MINI TABLAS DE BILLETS/CLAVES, REINICIAR MM NECESARIOS
                $(`#btnLimpiarSeleccion_m${i}`).on(`click`, function(){
                    const confirmarLimpieza = confirm("¿Está seguro de que desea limpiar la selección de barras?");

                    if (!confirmarLimpieza) {
                        return;
                    }
                    $(`#miniTableBillets_m${i} tbody`).empty();
                    $(`#miniTableCostoBarra_m${i} tbody`).empty();
                    $(`#precioBarra_m${i}`).val(``);

                    resetTextareasBillets();

                    $(`#spanMilimetrosNecesarios_m${i}`).text(`0`);
                    $(`#spanSellosRestantes_m${i}`).text(`0`);
                    $(`#spanMilimetrosSobrantes_m${i}`).text(`0`);

                    disablarBoton(this);
                    disablarBoton(`#btnSiguiente_m${i}`);
                    setTimeout(() => {
                        $(`#inputCantidad_m${i}`).trigger(`change`);
                    }, 300);
                    reiniciarMilimetrosNecesarios();

                    window[`billetsSeleccionados_m${i}`] = [];
                    window[`BILLETS_SELECCIONADOS_LOTES_m${i}`] = [];
                    window[`BILLETS_SELECCIONADOS_STRING_m${i}`] = [];
                    window[`BILLETS_SELECCIONADOS_MANUALMENTE_m${i}`] = [];
                    window[`PRECIO_BARRAS_m${i}`] = 0.00;
                    console.log(`Limpiando billets seleccionados_m${i}. `, window[`billetsSeleccionados_m${i}`]);

                });
                // CUANDO SE LE DA A SIGUIENTE, CALCULOS: SUMATORIA PB, (CO, CH, CPDI, CRM)(CANTIDAD) DESCUENTOS Y TOTAL
                $(`#btnSiguiente_m${i}`).on(`click`, function(){
                    $(`#pag1_m${i}`).removeClass(`d-flex`);
                    $(`#pag1_m${i}`).addClass(`d-none`);
                    $(`#pag2_m${i}`).removeClass(`d-none`);
                    $(`#pag2_m${i}`).addClass(`d-flex`);
                    // $(`html, body`).animate({
                    //     scrollTop: $(`#sectionContainerMaterial_m${i}`).offset().top
                    // }, 100); 

                    $(`#containerOmitirElemento_m${i}`).addClass(`d-none`);

                    let diametroInteriorValue = $(`#diametro_interior_mm_m${i}`).val() || 0;
                    let cantidadValue = $(`#inputCantidad_m${i}`).val();
                    let materialValue = $(`#selectorMaterial_m${i}`).val().replace(/\s+/g, '');

                    let costoMinimoUnidad = 0.00;
                    let costoOperacion = 0.00;
                    let costoHerramienta = 0.00;
                    let costoPreparacionDIbarra = 0.00;

                    if (diametroInteriorValue) {
                        // Realizar las llamadas AJAX en paralelo y esperar a que todas terminen
                        $.when(
                            // Realizar la llamada AJAX para obtener el COSTO MINIMO DE UNIDAD
                            $.ajax({
                                url: '../ajax/ajax_costo_minimo_unidad.php', 
                                type: 'POST',
                                data: { di: diametroInteriorValue },
                                dataType: 'json',
                                success: function(data) {
                                    // Verifica que la respuesta tenga datos
                                    if (data.length > 0) {
                                        costoMinimoUnidad = parseFloat(data[0].valor) || 0;
                                        console.log(`caso: `, data[0].caso);
                                        console.log(`limite_inferior = `, data[0].limite_inferior);
                                        console.log(`diametro interior = `, diametroInteriorValue);
                                        console.log(`limite_superior = `, data[0].limite_superior);
                                        console.log(`Costo minimo de unidad = `, data[0].valor);
                                        console.log(`------------------------------------------`);
                                    } else {
                                    }
                                },
                                error: function() {
                                    console.error('Error al realizar la petición AJAX');
                                }
                            }),
                            // Realizar la llamada AJAX para obtener el COSTO DE OPERACION
                            $.ajax({
                                url: '../ajax/ajax_costo_operacion.php', 
                                type: 'POST',
                                data: { di: diametroInteriorValue,
                                        material: materialValue
                                    },
                                dataType: 'json',
                                success: function(data) {
                                    // Verifica que la respuesta tenga datos
                                    if (data.length > 0) {
                                        costoOperacion = parseFloat(data[0].valor) || 0;
                                        console.log(`caso: `, data[0].caso);
                                        console.log(`limite_inferior = `, data[0].limite_inferior);
                                        console.log(`diametro interior = `, diametroInteriorValue);
                                        console.log(`limite_superior = `, data[0].limite_superior);
                                        console.log(`${data[0].limite_inferior} < ${diametroInteriorValue} <= ${data[0].limite_superior}`);
                                        console.log(`costo de operacion (valor) = `, (data[0].valor));
                                        console.log(`----------------------------------------------`);
                                    } else {
                                        console.log(`No hay resultado costo de operacion `);
                                    }
                                },
                                error: function() {
                                    console.error('Error al realizar la petición AJAX costo de operacion');
                                }
                            }),
                            // Realizar la llamada AJAX para obtener el COSTO DE HERRAMIENTA
                            $.ajax({
                                url: '../ajax/ajax_costo_herramienta.php', 
                                type: 'POST',
                                data: { di: diametroInteriorValue },
                                dataType: 'json',
                                success: function(data) {
                                    // Verifica que la respuesta tenga datos
                                    if (data.length > 0) {
                                        costoHerramienta = parseFloat(data[0].valor) || 0;
                                        console.log(`caso: `, data[0].caso);
                                        console.log(`limite_inferior = `, data[0].limite_inferior);
                                        console.log(`diametro interior = `, diametroInteriorValue);
                                        console.log(`limite_superior = `, data[0].limite_superior);
                                        console.log(`${data[0].limite_inferior} < ${diametroInteriorValue} <= ${data[0].limite_superior}`);
                                        console.log(`costo de herramienta (valor) = `, (data[0].valor));
                                        console.log(`-------------------------------------`);
                                    } else {
                                        console.log(`No hay resultado costo de herramienta `);
                                    }
                                },
                                error: function() {
                                    console.error('Error al realizar la petición AJAX costo de herramienta');
                                }
                            }),  
                            // Realizar la llamada AJAX para obtener el COSTO DE PREPARACION DI
                            $.ajax({
                                url: '../ajax/ajax_costo_preparacion_di.php', 
                                type: 'POST',
                                data: { di: diametroInteriorValue },
                                dataType: 'json',
                                success: function(data) {
                                    // Verifica que la respuesta tenga datos
                                    if (data.length > 0) {
                                        costoPreparacionDIbarra = parseFloat(data[0].valor) || 0;
                                        console.log(`caso: `, data[0].caso);
                                        console.log(`limite_inferior = `, data[0].limite_inferior);
                                        console.log(`diametro interior = `, diametroInteriorValue);
                                        console.log(`limite_superior = `, data[0].limite_superior);
                                        console.log(`${data[0].limite_inferior} < ${diametroInteriorValue} <= ${data[0].limite_superior}`);
                                        console.log(`Costo de preparacion DI barra = `, (data[0].valor));
                                        console.log(`-------------------------------------`);
                                    } else {
                                        console.log(`No hay resultado Costo de preparacion DI barra`);
                                    }
                                },
                                error: function() {
                                    console.error('Error al realizar la petición AJAX Costo de preparacion DI barra');
                                }
                            }),
                            // Realizar la llamada AJAX para obtener el DESCUENTO RELACION CANTIDAD
                            $.ajax({
                                url: '../ajax/ajax_relacion_cantidad.php', 
                                type: 'POST',
                                data: { q: cantidadValue },
                                dataType: 'json',
                                success: function(data) {
                                    // Verifica que la respuesta tenga datos
                                    if (data.length > 0) {
                                        $(`#inputPorcentDescuentoRC_m${i}`).val(data[0].valor);

                                        console.log(`caso: `, data[0].caso);
                                        console.log(`limite_inferior = `, data[0].limite_inferior);
                                        console.log(`cantidad = `, cantidadValue);
                                        console.log(`limite_superior = `, data[0].limite_superior);
                                        console.log(`${data[0].limite_inferior} < ${cantidadValue} <= ${data[0].limite_superior}`);
                                        console.log(`descuento relacion cantidad (valor) = `, data[0].valor);
                                        console.log(`------------------------------------------`);
                                    } else {
                                    }
                                },
                                error: function() {
                                    console.error('Error al realizar la petición AJAX');
                                }
                            }),
                            // Realizar la llamada AJAX para obtener el DESCUENTO MAYOREO
                            $.ajax({
                                url: '../ajax/ajax_descuento_mayoreo.php', 
                                type: 'POST',
                                data: { q: cantidadValue },
                                dataType: 'json',
                                success: function(data) {
                                    // Verifica que la respuesta tenga datos
                                    if (data.length > 0) {
                                        $(`#inputPorcentDescuentoMayoreo_m${i}`).val(data[0].valor);

                                        console.log(`caso: `, data[0].caso);
                                        console.log(`limite_inferior = `, data[0].limite_inferior);
                                        console.log(`cantidad = `, cantidadValue);
                                        console.log(`limite_superior = `, data[0].limite_superior);
                                        console.log(`${data[0].limite_inferior} < ${cantidadValue} <= ${data[0].limite_superior}`);
                                        console.log(`descuento mayoreo (valor) = `, data[0].valor);
                                        console.log(`------------------------------------------`);
                                    } else {
                                    }
                                },
                                error: function() {
                                    console.error('Error al realizar la petición AJAX');
                                }
                            }),
                            // Realizar la llamada AJAX para obtener el COSTO MINIMO DE UNIDAD
                            $.ajax({
                                url: '../ajax/ajax_resorte_metalico.php', 
                                type: 'GET',
                                dataType: 'json',
                                success: function(data) {
                                    // Verifica que la respuesta tenga datos
                                    if (data.length > 0) {
                                        if(window.tieneResorte === "1"){
                                            window.multiploResorte = data[0].valor;
                                        }else{
                                            window.multiploResorte = 1.00;
                                        }
                                        console.log(`caso: `, data[0].caso);
                                        console.log(`Multiplo por resorte es = `, window.multiploResorte);
                                        console.log(`------------------------------------------`);

                                    } else {
                                    }
                                },
                                error: function() {
                                    console.error('Error al realizar la petición AJAX');
                                }
                            })
                        ).done(function () {
                            // Obtener los valores de los inputs y convertirlos a números decimales
                            // let precioBarraValue = parseFloat($(`#precioBarra_m${i}`).val()) || 0;
                            $(`#precioBarra_m${i}`).val(window[`PRECIO_BARRAS_m${i}`].toFixed(2));
                            let porcentDescuentoCliente = parseInt($(`#inputPorcentDescuentoCliente_m${i}`).val()) || 0;
                            let porcentDescuentoRC = parseInt($(`#inputPorcentDescuentoRC_m${i}`).val()) || 0;
                            let porcentDescuentoMayoreo = parseInt($(`#inputPorcentDescuentoMayoreo_m${i}`).val()) || 0;
                            let inputCantidadValue = parseInt($(`#inputCantidad_m${i}`).val()) || 0;

                            costoMinimoUnidad = costoMinimoUnidad * inputCantidadValue;
                            $(`#inputCostoMinimoUnidad_m${i}`).val(costoMinimoUnidad.toFixed(2));
                            costoOperacion = costoOperacion * inputCantidadValue;
                            $(`#inputCostoOperacion_m${i}`).val(costoOperacion.toFixed(2));
                            costoHerramienta = costoHerramienta * inputCantidadValue;
                            $(`#inputCostoHerramienta_m${i}`).val(costoHerramienta.toFixed(2));
                            costoPreparacionDIbarra = costoPreparacionDIbarra * inputCantidadValue;
                            $(`#inputCostoPreparacionDI_m${i}`).val(costoPreparacionDIbarra.toFixed(2));

                            let costoResorte = 0.00;
                            // Mostrar total de precios de barra
                            // Cálculos del total aplicando la formula secreta
                            let subTotalSinDescuentos = window[`PRECIO_BARRAS_m${i}`] + costoMinimoUnidad + costoOperacion + costoHerramienta + costoPreparacionDIbarra;
                            console.log(`Sub total antes de descuentos = `, subTotalSinDescuentos);
                            // sumar el costo del resorte SI EXISTE
                            if(window.tieneResorte === "1"){
                                costoResorte = subTotalSinDescuentos * window.multiploResorte;
                                $(`#inputCostoResorte_m${i}`).val(costoResorte.toFixed(2));
                                console.log(`Costo por resorte metalico = `, costoResorte);
                                subTotalSinDescuentos = subTotalSinDescuentos + costoResorte;
                                console.log(`Sub total con resorte metalico = `, subTotalSinDescuentos);
                            }else{
                                $(`#inputCostoResorte_m${i}`).val(``);
                                console.log(`Costo por resorte metalico = 0`);
                            }
                            // Aplicar descuentos al subtotal escaladamente
                            let descuentoClienteValue = (subTotalSinDescuentos * porcentDescuentoCliente) / 100; // descuento de cliente
                            let subTotalCotizacion1 = subTotalSinDescuentos - descuentoClienteValue; // sub total con descuento de cliente

                            let descuentoRCValue = (subTotalCotizacion1 * porcentDescuentoRC) / 100; // descuento relacion cantidad
                            let subTotalCotizacion2 = subTotalCotizacion1 - descuentoRCValue; // sub total con descuento de cliente y relacion cantidad

                            let descuentoMayoreoValue = (subTotalCotizacion2 * porcentDescuentoMayoreo) / 100; // descuento de mayoreo
                            let subTotalCotizacion3 = subTotalCotizacion2 - descuentoMayoreoValue;// sub total con descuento de cliente, con relacion cantidad y de mayoreo
                            let totalDescuentos = descuentoClienteValue + descuentoRCValue + descuentoMayoreoValue;
                            let total = (subTotalCotizacion3);
                            // Mostrar el resultado en el elemento #inputSubTotal
                            $(`#inputTotalUnitarios_m${i}`).val(subTotalSinDescuentos.toFixed(2)); 
                            $(`#inputDescuentoCliente_m${i}`).val(descuentoClienteValue.toFixed(2));
                            $(`#inputDescuentoRC_m${i}`).val(descuentoRCValue.toFixed(2));
                            $(`#inputDescuentoMayoreo_m${i}`).val(descuentoMayoreoValue.toFixed(2));
                            $(`#inputTotalDescuentos_m${i}`).val(totalDescuentos.toFixed(2)); 
                            $(`#inputTotalMaterial_m${i}`).val(total.toFixed(2)); 
                            $(`#spanCantidad_m${i}`).text(`Cantidad: `+inputCantidadValue+` pieza(s)`);
                            sonClavesPrecioValidas();
                            //debug en consola
                            console.log(`Total del material = `, total);

                        }).fail(function () {
                            alert(`Hubo un error al obtener los datos.`);
                            console.error(`Error en una de las solicitudes AJAX.`);
                        });
                    }
                }); 
                // CUANDO SE LE DA ATRAS Mostrar pagina anterior
                $(`#btnAtras_m${i}`).on(`click`, function(){
                    $(`#pag2_m${i}`).removeClass(`d-flex`);
                    $(`#pag2_m${i}`).addClass(`d-none`);
                    $(`#pag1_m${i}`).removeClass(`d-none`);
                    $(`#pag1_m${i}`).addClass(`d-flex`);
                    $(`#containerOmitirElemento_m${i}`).removeClass(`d-none`);
                });
                // CUANDO SE MARCA COMO LISTO/COMPLETADO
                $(`#btnListo_m${i}`).on(`click`, function(){
                    $(`#btnAtras_m${i}`).removeClass(`d-flex`);
                    $(`#btnAtras_m${i}`).addClass(`d-none`);
                    $(this).removeClass(`btn-general`);
                    $(this).addClass(`btn-disabled`);
                    $(this).text(`Completado`);
                    $(this).append(`<i class='bi bi-check-circle-fill' style='color:#4caf50b5; margin-left: 5px;'></i>`);
                    $(`#btnNoListo_m${i}`).removeClass(`d-none`);
                    $(`#btnNoListo_m${i}`).addClass(`d-block`);
                    $(`#inputBilletsLotes_m${i}`).val(window[`BILLETS_SELECCIONADOS_LOTES_m${i}`]);
                    $(`#inputBilletsString_m${i}`).val(window[`BILLETS_SELECCIONADOS_STRING_m${i}`]);
                    $(`#inputBilletsManualmente_m${i}`).val(window[`BILLETS_SELECCIONADOS_MANUALMENTE_m${i}`]);
                    console.log("Los Lotes strings del material son: ", window[`BILLETS_SELECCIONADOS_LOTES_m${i}`]);
                });
                // CUANDO SE HABILITA LA EDICION
                $(`#btnNoListo_m${i}`).on(`click`, function(){
                    $(`#btnAtras_m${i}`).removeClass(`d-none`);
                    $(`#btnAtras_m${i}`).addClass(`d-block`);
                    $(`#btnListo_m${i}`).removeClass(`btn-disabled`);
                    $(`#btnListo_m${i}`).addClass(`btn-general`);
                    $(`#btnListo_m${i}`).text(`Marcar como completado`);
                    $(`#btnNoListo_m${i}`).removeClass(`d-block`);
                    $(`#btnNoListo_m${i}`).addClass(`d-none`);
                    $(`#btnAtras_m${i}`).removeClass(`d-none`);
                    $(`#btnAtras_m${i}`).addClass(`d-block`);
                    $(`#btnListo_m${i}`).append(`<i class='bi bi-check2-circle' style='color:#fff; margin-left: 5px;'></i>`);
                });

                // EVENTOS EN LAS DIMENSIONES DEL SELLO
                //////////// SCRIPTS para convertir automaticamente mm a inches, inches a mm de CADA input
                // -----------------------------ALTURA--------------------------------------------------------------------
                // ---------------  MM a INCH  ---------------------
                $(`#altura_mm_m${i}`).on('input', function(){
                    escaparCaracteresNumericos.call(this);
                    let valorAlturaMm = parseFloat($(this).val());
                    if (isNaN(valorAlturaMm)) {
                        valorAlturaMm = 0;
                    }
                    let alturaMmToInch = valorAlturaMm / 25.4;
                    $(`#altura_inch_m${i}`).val(alturaMmToInch.toFixed(4));
                });
                // ----------  INCH a MM ------------------------------------
                $(`#altura_inch_m${i}`).on('input', function(){
                    escaparCaracteresNumericos.call(this);
                    let valorAlturaInch = parseFloat($(this).val());
                    if (isNaN(valorAlturaInch)) {
                        valorAlturaInch = 0;
                    }
                    let alturaInchToMm = valorAlturaInch * 25.4;
                    $(`#altura_mm_m${i}`).val(alturaInchToMm.toFixed(2));
                });

                // ---------------------------DIAMETRO INTERIOR--------------------------------------------------------------------
                // ---------------  MM a INCH  ---------------------
                $(`#diametro_interior_mm_m${i}`).on('input', function(){
                    escaparCaracteresNumericos.call(this);
                    let valorDiaInMm = parseFloat($(this).val());
                    if (isNaN(valorDiaInMm)) {
                        valorDiaInMm = 0;
                    }
                    let DiaInMmToInch = valorDiaInMm / 25.4;
                    $(`#diametro_interior_inch_m${i}`).val(DiaInMmToInch.toFixed(4));
                });
                // ----------  INCH a MM ------------------------------------
                $(`#diametro_interior_inch_m${i}`).on('input', function(){
                    escaparCaracteresNumericos.call(this);
                    let valorDiaInInch = parseFloat($(this).val());
                    if (isNaN(valorDiaInInch)) {
                        valorDiaInInch = 0;
                    }
                    let DiaInInchToMm = valorDiaInInch * 25.4;
                    $(`#diametro_interior_mm_m${i}`).val(DiaInInchToMm.toFixed(2));
                });

                // ---------------------------DIAMETRO EXTERIOR--------------------------------------------------------------------
                // ---------------  MM a INCH  ---------------------
                $(`#diametro_exterior_mm_m${i}`).on('input', function(){
                    escaparCaracteresNumericos.call(this);
                    let valorDiaExMm = parseFloat($(this).val());
                    if (isNaN(valorDiaExMm)) {
                        valorDiaExMm = 0;
                    }
                    let DiaExMmToInch = valorDiaExMm / 25.4;
                    $(`#diametro_exterior_inch_m${i}`).val(DiaExMmToInch.toFixed(4));
                });
                // ----------  INCH a MM ------------------------------------
                $(`#diametro_exterior_inch_m${i}`).on('input', function(){
                    escaparCaracteresNumericos.call(this);
                    let valorDiaExInch = parseFloat($(this).val());
                    if (isNaN(valorDiaExInch)) {
                        valorDiaExInch = 0;
                    }
                    let DiaExInchToMm = valorDiaExInch * 25.4;
                    $(`#diametro_exterior_mm_m${i}`).val(DiaExInchToMm.toFixed(2));
                });
            });
        })(iLocal);
    }
});