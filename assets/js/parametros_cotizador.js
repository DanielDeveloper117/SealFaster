$(document).ready(function() {
    const btnTabCostosOperacion = document.querySelector("#btnTabCostosOperacion");
    const containerMaterialsCO = document.querySelector("#containerMaterialsCO");
    const iconArrowRight = document.querySelector("#iconArrowRight");
    btnTabCostosOperacion.addEventListener("click", function(){
        containerMaterialsCO.classList.toggle("d-none");
        iconArrowRight.classList.toggle("bi-caret-right");
        iconArrowRight.classList.toggle("bi-caret-down");
    });
    const btnTabMU = document.querySelector("#btnTabMU");
    const containerMaterialsMU = document.querySelector("#containerMaterialsMU");
    const iconArrowRight2 = document.querySelector("#iconArrowRight2");
        btnTabMU.addEventListener("click", function(){
        containerMaterialsMU.classList.toggle("d-none");
        iconArrowRight2.classList.toggle("bi-caret-right");
        iconArrowRight2.classList.toggle("bi-caret-down");
    });

    function actualizarParametro(valor) {
        const url = new URL(window.location);
        url.searchParams.set("c", valor);
        history.replaceState(null, "", url);
    }

    // Función que maneja la visualización de los formularios
    function mostrarFormulario(mostrarEste) {
        // Ocultamos todos los formularios
        const containers = [
            "#containerCostoOperacionHECOPUR",
            "#containerCostoOperacionECOTAL",
            "#containerCostoOperacionECOSIL",
            "#containerCostoOperacionECORUBBER1",
            "#containerCostoOperacionECORUBBER2",
            "#containerCostoOperacionECORUBBER3",
            "#containerCostoOperacionECOPUR",
            "#containerCostoOperacionECOMID",
            "#containerCostoOperacionECOFLON1",
            "#containerCostoOperacionECOFLON2",
            "#containerCostoOperacionECOFLON3",
            "#containerMultiploUtilidadCustom",
            "#containerMultiploUtilidadProveedores",
            "#containerMultiploUtilidadHECOPUR",
            "#containerMultiploUtilidadECOTAL",
            "#containerMultiploUtilidadECOSIL",
            "#containerMultiploUtilidadECORUBBER1",
            "#containerMultiploUtilidadECORUBBER2",
            "#containerMultiploUtilidadECORUBBER3",
            "#containerMultiploUtilidadECOPUR",
            "#containerMultiploUtilidadECOMID",
            "#containerMultiploUtilidadECOFLON1",
            "#containerMultiploUtilidadECOFLON2",
            "#containerMultiploUtilidadECOFLON3",
            "#containerCostoHerramienta",
            "#containerCostoPreparacionBarraDI",
            "#containerDescuentoCliente",
            "#containerDescuentoRelacionCantidad",
            "#containerDescuentoMayoreo",
            "#containerCostoMinimoUnidad",
            "#containerResorteMetalico"
        ];

        $(containers.join(', ')).addClass("d-none");

        // Mostrar el formulario correspondiente según el tipoFormulario
        switch(mostrarEste) {
            // Costos de operación
            case "coH-ECOPUR": 
                $("#containerCostoOperacionHECOPUR").removeClass("d-none");
                actualizarParametro("coH-ECOPUR");
                break;
            case "coECOTAL": 
                $("#containerCostoOperacionECOTAL").removeClass("d-none");
                actualizarParametro("coECOTAL");
                break;
            case "coECOSIL": 
                $("#containerCostoOperacionECOSIL").removeClass("d-none");
                actualizarParametro("coECOSIL");
                break;
            case "coECORUBBER1": 
                $("#containerCostoOperacionECORUBBER1").removeClass("d-none");
                actualizarParametro("coECORUBBER1");
                break;
            case "coECORUBBER2": 
                $("#containerCostoOperacionECORUBBER2").removeClass("d-none");
                actualizarParametro("coECORUBBER2");
                break;
            case "coECORUBBER3": 
                $("#containerCostoOperacionECORUBBER3").removeClass("d-none");
                actualizarParametro("coECORUBBER3");
                break;
            case "coECOPUR": 
                $("#containerCostoOperacionECOPUR").removeClass("d-none");
                actualizarParametro("coECOPUR");
                break;
            case "coECOMID": 
                $("#containerCostoOperacionECOMID").removeClass("d-none");
                actualizarParametro("coECOMID");
                break;
            case "coECOFLON1": 
                $("#containerCostoOperacionECOFLON1").removeClass("d-none");
                actualizarParametro("coECOFLON1");
                break;
            case "coECOFLON2": 
                $("#containerCostoOperacionECOFLON2").removeClass("d-none");
                actualizarParametro("coECOFLON2");
                break;
            case "coECOFLON3": 
                $("#containerCostoOperacionECOFLON3").removeClass("d-none");
                actualizarParametro("coECOFLON3");
                break;
            // Multiplos de Utilidad
            case "muc": 
                $("#containerMultiploUtilidadCustom").removeClass("d-none");
                actualizarParametro("muc");
                break;
            case "mup": 
                $("#containerMultiploUtilidadProveedores").removeClass("d-none");
                actualizarParametro("mup");
                break;
            case "muH-ECOPUR": 
                $("#containerMultiploUtilidadHECOPUR").removeClass("d-none");
                actualizarParametro("muH-ECOPUR");
                break;
            case "muECOTAL": 
                $("#containerMultiploUtilidadECOTAL").removeClass("d-none");
                actualizarParametro("muECOTAL");
                break;
            case "muECOSIL": 
                $("#containerMultiploUtilidadECOSIL").removeClass("d-none");
                actualizarParametro("muECOSIL");
                break;
            case "muECORUBBER1": 
                $("#containerMultiploUtilidadECORUBBER1").removeClass("d-none");
                actualizarParametro("muECORUBBER1");
                break;
            case "muECORUBBER2": 
                $("#containerMultiploUtilidadECORUBBER2").removeClass("d-none");
                actualizarParametro("muECORUBBER2");
                break;
            case "muECORUBBER3": 
                $("#containerMultiploUtilidadECORUBBER3").removeClass("d-none");
                actualizarParametro("muECORUBBER3");
                break;
            case "muECOPUR": 
                $("#containerMultiploUtilidadECOPUR").removeClass("d-none");
                actualizarParametro("muECOPUR");
                break;
            case "muECOMID": 
                $("#containerMultiploUtilidadECOMID").removeClass("d-none");
                actualizarParametro("muECOMID");
                break;
            case "muECOFLON1": 
                $("#containerMultiploUtilidadECOFLON1").removeClass("d-none");
                actualizarParametro("muECOFLON1");
                break;
            case "muECOFLON2": 
                $("#containerMultiploUtilidadECOFLON2").removeClass("d-none");
                actualizarParametro("muECOFLON2");
                break;
            case "muECOFLON3": 
                $("#containerMultiploUtilidadECOFLON3").removeClass("d-none");
                actualizarParametro("muECOFLON3");
                break;
            case "ch": 
                $("#containerCostoHerramienta").removeClass("d-none");
                actualizarParametro("ch");
                break;
            case "cpdib": 
                $("#containerCostoPreparacionBarraDI").removeClass("d-none");
                actualizarParametro("cpdib");
                break;
            case "dc": 
                $("#containerDescuentoCliente").removeClass("d-none");
                actualizarParametro("dc");
                break;
            case "drc": 
                $("#containerDescuentoRelacionCantidad").removeClass("d-none");
                actualizarParametro("drc");
                break;
            case "dm": 
                $("#containerDescuentoMayoreo").removeClass("d-none");
                actualizarParametro("dm");
                break;
            case "cmu": 
                $("#containerCostoMinimoUnidad").removeClass("d-none");
                actualizarParametro("cmu");
                break;
            case "mrm": 
                $("#containerResorteMetalico").removeClass("d-none");
                actualizarParametro("mrm");
                break;
            default:
                console.log("Formulario no encontrado");
        }

    }
    //Evento click a boton de que parametros
    $(".btn-tab, .btn-tab-material").on("click", function() {
        $(".btn-tab-selected").removeClass("btn-tab-selected").addClass("btn-tab");
        $(".btn-tab-material-selected").removeClass("btn-tab-material-selected").addClass("btn-tab-material");
        if ($(this).hasClass("btn-tab")) {
            $(this).addClass("btn-tab-selected").removeClass("btn-tab");
        } else if ($(this).hasClass("btn-tab-material")) {
            $(this).addClass("btn-tab-material-selected").removeClass("btn-tab-material");
        }
        $("#containerInitial").addClass("d-none");
        let mostrarEste = $(this).data("mostrar");
        mostrarFormulario(mostrarEste);
    });
    // GUARDAR EL NUEVO PARAMETRO DE MULTIPLO DE UTILIDAD PERSONALIZADO
    $("#btnGuardarNuevoParametro").on("click", function() {
        let proveedor = $("#inputProveedor").val();
        let material = $("#inputMaterial").val();
        let condicionDI = $("#inputCondicion").val();
        let valorDI = $("#inputDI").val();
        let multiplo = $("#inputMultiplo").val();

        // VALIDACIÓN 1: Debe seleccionar proveedor o material o ambos
        if (!proveedor && !material) {
            sweetAlertResponse("warning", "Advertencia", "Debe seleccionar al menos proveedor o material.", "none");
            return;
        }

        // VALIDACIÓN 2 y 3: Si solo selecciona proveedor o material, debe tener condicional DI y valor DI
        if ((proveedor && !material) || (!proveedor && material)) {
            if (!condicionDI || !valorDI) {
                sweetAlertResponse("warning", "Advertencia", "Debe seleccionar condición del DI y digitar el valor DI.", "none");
                return;
            }
        }

        // VALIDACIÓN 4: Condicional DI y valor DI siempre van juntos
        if ((condicionDI && !valorDI) || (!condicionDI && valorDI)) {
            sweetAlertResponse("warning", "Advertencia", "Condicional del DI y valor DI deben ir juntos.", "none");
            return;
        }

        // VALIDACIÓN 5: Multiplo siempre requerido, positivo, máximo 2 decimales
        let regexMultiplo = /^[0-9]+(\.[0-9]{1,2})?$/;
        if (!multiplo || !regexMultiplo.test(multiplo) || parseFloat(multiplo) <= 0) {
            sweetAlertResponse("warning", "Advertencia", "El multiplo debe ser un número positivo con máximo dos decimales.", "none");
            return;
        }

        // VALIDACIÓN PASÓ: Ocultar botón para evitar doble envío
        $(this).addClass("d-none");

        // Preparar datos para enviar
        let datos = {
            proveedor: proveedor || "",
            material: material || "",
            condicion: condicionDI || "",
            di: valorDI || "",
            multiplo: multiplo
        };

        // AJAX para guardar
        $.ajax({
            url: '../ajax/nuevo_multiplo_utilidad.php',
            type: 'POST',
            data: datos,
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                } else {
                    sweetAlertResponse("warning", "Hubo un problema", data.message, "self");
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al agregar el registro. " + error, "self");
            }
        });
    });
    // TESTEAR VALORES PARA OBTENER RESULTADO DE MULTIPLO DE UTILIDAD
    $("#btnEnviarPrueba").on("click", function() {
        let proveedor = $("#inputProveedor2").val();
        let material = $("#inputMaterial2").val();
        let valorDI = $("#inputDI2").val();

        // VALIDACIÓN: Proveedor siempre requerido
        if (!proveedor) {
            sweetAlertResponse("warning", "Advertencia", "Debe seleccionar un proveedor.", "none");
            return;
        }

        // VALIDACIÓN: Material siempre requerido
        if (!material) {
            sweetAlertResponse("warning", "Advertencia", "Debe seleccionar un material.", "none");
            return;
        }

        // VALIDACIÓN: Valor DI siempre requerido y positivo
        if (!valorDI || parseFloat(valorDI) <= 0) {
            sweetAlertResponse("warning", "Advertencia", "Debe ingresar un valor válido para el diámetro interior.", "none");
            return;
        }

        // Ocultar botón para evitar doble envío
        //$(this).addClass("d-none");

        // Preparar datos para enviar
        let datos = {
            proveedor: proveedor,
            material: material,
            di: valorDI
        };
        $("#resultadoPruebas").html("");
        // AJAX para consultar múltiplo de utilidad
        $.ajax({
            url: '../ajax/ajax_multiplo_utilidad.php',
            type: 'GET',
            data: datos,
            dataType: 'json',
            success: function(response) {

                if (response.valor !== undefined) {
                    $("#containerPruebas").removeClass("d-none").addClass("d-flex");

                    let mensajeFinal = "";
                    mensajeFinal += `<p><b>Múltiplo de utilidad resultante:</b> ${response.valor}</p>`;
                    mensajeFinal += `<p><b>Proveedor:</b> ${response.detalle.proveedor}</p>`;
                    mensajeFinal += `<p><b>Material:</b> ${response.detalle.material}</p>`;
                    mensajeFinal += `<p><b>Diámetro Interior:</b> ${response.detalle.diametro_recibido}</p>`;
                    mensajeFinal += `<p><b>Caso aplicado:</b> ${response.detalle.caso_aplicado}</p>`;
                    mensajeFinal += `<p><b>Prioridad:</b> ${response.detalle.prioridad}</p>`;

                    response.coincidencias.forEach((item, index) => {
                        mensajeFinal += `${index + 1}. Caso: ${item.caso}, Valor: ${item.valor}, Prioridad: ${item.prioridad}`;
                        if (item.condicionDI) {
                            mensajeFinal += `, Condición DI: ${item.condicionDI}`;
                        }
                        mensajeFinal += "<br>";
                    });


                    if (response.mensaje) {
                        mensajeFinal += `<p><b>Nota:</b> ${response.mensaje}</p>`;
                    }

                    $("#resultadoPruebas").html(mensajeFinal);

                    // Cerrar modal (Bootstrap ejemplo)
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modalPrueba'));
                    if (modal) modal.hide();
                }else if (response.error) {
                    sweetAlertResponse("warning", "Advertencia", response.error, "none");
                } else {
                    sweetAlertResponse("warning", "Advertencia", "No se obtuvo un resultado válido.", "none");
                }

                $("#btnEnviarPrueba").removeClass("d-none");
            },
            error: function(xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al consultar el múltiplo de utilidad. " + error, "self");
                $("#btnEnviarPrueba").removeClass("d-none");
            }
        });
    });
    // ELIMINAR PARAMETRO DE MULTIPLO DE UTILIDAD PERSONALIZADO
    $(".eliminar-parametro").on("click", function(){
        let parametro = $(this).data("eliminar");

        $(this).addClass("d-none");
        $.ajax({
            url: '../ajax/eliminar_multiplo_utilidad.php',
            type: 'POST',
            data: { 
                id: parametro
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
                sweetAlertResponse("error", "Error", "Error al eliminar el registro. " + error, "self");
            }
        });
    });
});