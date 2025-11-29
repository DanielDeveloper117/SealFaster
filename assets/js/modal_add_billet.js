$(document).ready(function(){
    var noVerificarEsteLP = "";
    window.CLAVE_VALIDA = false;
    window.LP_VALIDO = false;

    // CONSULTA AJAX PARA MATERIALES DESDE PARAMETROS2
    // $.ajax({
    //     url: '../ajax/ajax_materiales_parametros2.php', 
    //     type: 'GET',
    //     dataType: 'json',
    //     success: function(data) {
    //         if (data.length > 0) {
    //             $.each(data, function(index, item) {
    //                 $("#inputMaterial").append(
    //                     `
    //                     <option value="${item.caso}">${item.caso}</option>
    //                     `
    //                 );
    //             });
    //         } else {
    //         }
    //     },
    //     error: function() {
    //         console.error('Error al realizar la peticion AJAX');
    //     }
    // });

    //---------------------------------------- @ FUNCIONES ------------------------------------
    function formatTimestamp12h(timestamp) {
        const date = new Date(timestamp.replace(' ', 'T'));
        return date.toLocaleString('es-MX', {
            hour12: true,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }
    // Genera un timestamp con formato MySQL (ejemplo: 2025-10-21 16:45:32)
    function getTimestampNow() {
        const now = new Date();

        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }


    function verificarClave() {
        let claveValue = $("#inputClavePost").val(); 
        $("#inputClaveAlterna").val(""); 

        if (claveValue !== "") {
            console.log("El usuario ingreso un valor en el inputClavePost.");

            $.ajax({
                url: '../ajax/verificar_clave.php',
                type: 'GET',
                data: { clave: claveValue },
                dataType: 'json',
                success: function(data) {
                    // CASO 1: Clave encontrada en parametros (array con datos)
                    if (Array.isArray(data) && data.length > 0) {
                        let medida = data[0].interior + "/" + data[0].exterior;
                        window.CLAVE_VALIDA = true;
                        $("#pWarning").addClass("d-none");
                        $("#pValida").removeClass("d-none");
                        $("#pValida").text(`Clave valida encontrada. Material: ${data[0].material_corregido}. Proveedor: ${data[0].proveedor}`);
                        
                        $("#inputMaterial").removeClass("selector").addClass("input-disabled").val(data[0].material_corregido);
                        $("#inputProveedor").removeClass("selector").addClass("input-disabled").val(data[0].proveedor);
                        $("#inputMedida").val(medida).removeClass("input-text").addClass("input-disabled");
                        $("#inputMaxUsable").val(data[0].max_usable).removeClass("input-text").addClass("input-disabled");
                        
                        let inputEstatusActual = $("#inputEstatus").val();
                        if(inputEstatusActual == "En uso"){
                            $("#inputEstatus").val("En uso");
                        }else if(inputEstatusActual == "Maquinado en curso"){
                            $("#inputEstatus").val("Maquinado en curso");
                        }else{
                            $("#inputEstatus").val("Disponible para cotizar");    
                        }

                        if(data[0].es_alterna == 1){
                            $("#inputClavePost").val(data[0].clave_srs_utilizada);
                            $("#pAlterna").removeClass("d-none");
                            $("#pAlterna").text(`Clave alterna sustituida: ${data[0].clave_alterna} → ${data[0].clave_srs_utilizada}`);
                        }else{
                            $("#pAlterna").addClass("d-none");
                        }
                        
                    } 
                    // CASO 2: Clave alterna sin relación (clave_srs es null)
                    else if (data.sin_relacion) {
                        window.CLAVE_VALIDA = false;
                        $("#pWarning").removeClass("d-none");
                        $("#pWarning").text(`Clave alterna encontrada (${data.clave_alterna}) pero no tiene relación con clave SRS. Se enviara correo para relacionar la clave`);
                        $("#pValida, #pAlterna").addClass("d-none");
                        
                        // Resetear campos
                        $("#inputMaterial").removeClass("input-disabled").addClass("selector");
                        $("#inputProveedor").removeClass("input-disabled").addClass("selector");
                        $("#inputMedida").removeClass("input-disabled").addClass("input-text");
                        $("#inputMaxUsable").removeClass("input-disabled").addClass("input-text");
                        $("#inputEstatus").val("Relación pendiente");
                        $("#inputClaveAlterna").val(data.clave_alterna); 
                        
                    }
                    // CASO 3: No existe en claves_alternas ni en parametros
                    else if (data.no_encontrada) {
                        window.CLAVE_VALIDA = false;
                        $("#pWarning").removeClass("d-none");
                        $("#pWarning").text(`No se encontró clave SRS, no se encontró clave alterna. Se enviará correo para validar.`);
                        $("#pValida, #pAlterna").addClass("d-none");
                        
                        // Resetear campos
                        $("#inputMaterial").removeClass("input-disabled").addClass("selector");
                        $("#inputProveedor").removeClass("input-disabled").addClass("selector");
                        $("#inputMedida").removeClass("input-disabled").addClass("input-text");
                        $("#inputMaxUsable").removeClass("input-disabled").addClass("input-text");
                        $("#inputEstatus").val("Clave nueva pendiente");
                        $("#inputClaveAlterna").val(claveValue); 
                        
                    }
                    // CASO 4: Clave alterna con relación pero no existe en parametros
                    else if (data.no_en_parametros) {
                        window.CLAVE_VALIDA = false;
                        $("#pWarning").removeClass("d-none");
                        $("#pWarning").text(`Clave alterna encontrada (${data.clave_alterna}) pero no existe Clave SRS. Se enviará correo.`);
                        $("#pValida, #pAlterna").addClass("d-none");
                        
                        // Resetear campos
                        $("#inputMaterial").removeClass("input-disabled").addClass("selector");
                        $("#inputProveedor").removeClass("input-disabled").addClass("selector");
                        $("#inputMedida").removeClass("input-disabled").addClass("input-text");
                        $("#inputMaxUsable").removeClass("input-disabled").addClass("input-text");
                        $("#inputEstatus").val("Clave SRS inexistente");
                        $("#inputClaveAlterna").val(data.clave_alterna); 
                    }
                    //verificarBtnGuardar();
                },
                error: function() {
                    console.error('Error al realizar la peticion AJAX');
                    $('#pWarning').removeClass("d-none").text('Error en ajax validar clave.');
                }
            });
        } else {
            console.log("El usuario dejo el inputClavePost vacio.");
        }
    }

    function verificarBillet() {
        let actionValue = $("#inputAction").val();
        let billetValue = $("#inputLotePedimento").val();

        if (billetValue !== "") {
            console.log("El usuario ingreso un valor en el inputLotePedimento.");

            if(actionValue == "insert" || actionValue == "insert2" || actionValue == "update"){
                if(billetValue !== noVerificarEsteLP){
                    $.ajax({
                        url: '../ajax/ajax_existe_billet.php',
                        type: 'POST',
                        data: { billet: billetValue },
                        dataType: 'json',
                        success: function(data) {
                            if (data.existe) { 
                                window.LP_VALIDO = false;
                                $("#pInvalida3").removeClass("d-none");
                            } else {
                                window.LP_VALIDO = true;
                                $("#pInvalida3").addClass("d-none");
                            }
                            verificarBtnGuardar();
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al realizar la petición AJAX:', error);
                            console.error('Respuesta del servidor:', xhr.responseText); // Muestra el error enviado por PHP
                            $("#pInvalida3").removeClass("d-none");
                            $('#pInvalida3').text('Error en ajax validar lote pedimento.');
                        }
                    });
                }
            }

        } else {
            console.log("El usuario dejo el inputClavePost vacio.");
        }
    }

    function verificarBtnGuardar(){
        // if(window.CLAVE_VALIDA == true && window.LP_VALIDO == true){
        //     $("#btnGuardar").removeClass("btn-disabled").addClass("btn-general");
        // }else{
        //     $("#btnGuardar").removeClass("btn-general").addClass("btn-disabled");
        // }
        if(window.LP_VALIDO == true){
            $("#btnGuardar").removeClass("btn-disabled").addClass("btn-general");
        }else{
            $("#btnGuardar").removeClass("btn-general").addClass("btn-disabled");
        }
    }

    function ajaxBackend(idBillet, accion){
        var dataId = idBillet;
        var inputClave=$('#inputClavePost').val();
        var inputMaterial=$('#inputMaterial').val();
        var inputProveedor=$('#inputProveedor').val();
        var inputMedida=$('#inputMedida').val();
        var inputMaxUsable=$('#inputMaxUsable').val();
        var inputStock=$('#inputStock').val();
        var inputLotePedimento=$('#inputLotePedimento').val();
        var inputEstatus=$('#inputEstatus').val();
        var inputClaveAlterna=$('#inputClaveAlterna').val();

        var actionForm=accion;
        let actionAfter = "none";

        const fila = $(`#tr_${dataId}`);
        const filaAfectada = $(`#tr_${dataId} td`);
        
        if(actionForm == "delete"){  
            sweetAlertResponse("success", "Proceso exitoso", "Registro afectado correctamente", "none");
            fila.addClass("bg-row-deleted");  
            setTimeout(() => {
                fila.removeClass("bg-row-deleted");
                $(`#tr_${dataId}`).addClass("d-none");
            }, 800);
            return;
        }

        $.ajax({
            url: '../ajax/post_inventario_cnc.php',
            type: 'POST',
            data: { 
                id: dataId,
                clave: inputClave,
                material: inputMaterial,
                proveedor: inputProveedor,
                medida: inputMedida,
                max_usable: inputMaxUsable,
                stock: inputStock,
                lote_pedimento: inputLotePedimento,
                action: actionForm,
                estatus: inputEstatus,
                inputClaveAlterna: inputClaveAlterna
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    sweetAlertResponse("success", "Proceso exitoso", data.message, actionAfter);
                    window.LP_VALIDO = true;
                    $("#modalInventario #btnCloseModal").trigger("click");
                    
                    if(actionForm == "update"){
                        // Código para actualizar la fila en la tabla...
                        const claveLimpia = inputClave.replace(/\s+/g, "").trim();
                        fila.find(".td-clave").text(claveLimpia);
                        fila.find(".td-lote").text(inputLotePedimento);
                        fila.find(".td-material").text(inputMaterial);
                        fila.find(".td-proveedor").text(inputProveedor);
                        fila.find(".td-medida").text(inputMedida);
                        fila.find(".td-max_usable").text(inputMaxUsable);
                        fila.find(".td-stock").text(inputStock);
                        fila.find(".td-updated").text(formatTimestamp12h(getTimestampNow()));

                        // Barra de stock
                        const width = inputMaxUsable > 0 ? (inputStock / inputMaxUsable) * 100 : 0;
                        let barClass = "bar-bajo";
                        if (inputStock >= inputMaxUsable * 0.75) barClass = "bar-alto";
                        else if (inputStock >= inputMaxUsable * 0.25) barClass = "bar-medio";

                        const barra = fila.find(".td-barra .bar");
                        barra.css("width", width + "%").removeClass("bar-alto bar-medio bar-bajo").addClass(barClass);

                        // Usable / No usable
                        if (inputStock < 15) {
                            fila.attr("style", "background-color: #ff00002e !important;");
                        } else {
                            fila.removeAttr("style");
                        }

                        // Estatus
                        fila.find(".td-estatus").text(inputEstatus);

                        // Resaltar la fila actualizada
                        fila.addClass("bg-row-updated");
                        filaAfectada.addClass("bg-row-updated");
                        setTimeout(() => {
                            fila.removeClass("bg-row-updated");
                            filaAfectada.removeClass("bg-row-updated");
                        }, 1200);

                        const btn = fila.find('.edit-btn');
                        btn.attr('data-clave', inputClave);
                        btn.attr('data-lote_pedimento', inputLotePedimento);
                        btn.attr('data-material', inputMaterial);
                        btn.attr('data-proveedor', inputProveedor);
                        btn.attr('data-medida', inputMedida);
                        btn.attr('data-max_usable', inputMaxUsable);
                        btn.attr('data-stock', inputStock);

                    }else if(actionForm == "delete"){  
                        fila.addClass("bg-row-deleted");  
                        setTimeout(() => {
                            fila.removeClass("bg-row-deleted");
                            $(`#tr_${dataId}`).addClass("d-none");
                        }, 800);
                    }
                    
                    $("#formInventario")[0].reset();
                } else {
                    sweetAlertResponse("warning", "Hubo un problema", data.message, "none");
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al actualizar registro. " + error, "none");
            }
        });
    }
    //---------------------------------------- @ EVENTOS DEL DOM ------------------------------------
    // EVENTO AL CAMBIAR TIPO DE MATERIAL, CONSULTAR PROVEEDOR
    // $("#selectorMaterial, #inputMaterial").on("change", function() { 
    //     $("#selectorProveedor").html('<option value="all" selected>Todos</option>');
    //     $("#inputProveedor").html('<option selected disabled>Seleccionar</option>');
    //     var materialSeleccionado = $(this).val();
    //     $.ajax({
    //         url: '../ajax/ajax_proveedores.php', 
    //         type: 'POST',
    //         data: { material: materialSeleccionado },
    //         dataType: 'json',
    //         success: function(data) {
    //             if (data.length > 0) {
    //                 $.each(data, function(index, item) {
    //                     $("#selectorProveedor").append(
    //                         `
    //                         <option value="${item.proveedor}">${item.proveedor}</option>
    //                         `
    //                     );
    //                     $("#inputProveedor").append(
    //                         `
    //                         <option value="${item.proveedor}">${item.proveedor}</option>
    //                         `
    //                     );
    //                 });
    //             } else {
    //             }
    //         },
    //         error: function() {
    //             console.error('Error al realizar la petición AJAX');
    //         }
    //     });

    // });
    
    // $("#inputMaterial, #inputProveedor").on("change", function(){
    //     let material = $("#inputMaterial").val();
    //     let proveedor = $("#inputProveedor").val();
    //     let maxUsable = 0; 

    //     if(material !== "" && proveedor !== ""){
    //         switch (proveedor) {
    //             case "SKF":
    //                 switch (material) {
    //                     case "ECORUBBER 1":
    //                     case "ECORUBBER 2":
    //                     case "ECORUBBER 3":
    //                     case "ECOSIL":
    //                         maxUsable = 122.00;
    //                         break;
    //                     case "ECOFLON 1":
    //                         maxUsable = 146.00;
    //                         break;
    //                     case "ECOFLON 2":
    //                     case "ECOFLON 3":
    //                         maxUsable = 140.00;
    //                         break;
    //                     case "ECOTAL":
    //                         maxUsable = 138.00;
    //                         break;
    //                     case "ECOMID":
    //                         maxUsable = 155.00;
    //                         break;
    //                     case "ECOPUR":
    //                         maxUsable = 146.00;
    //                         break;
    //                     case "H-ECOPUR":
    //                         maxUsable = 145.00;
    //                         break;
    //                 }
    //                 break;

    //             case "TRYGONAL":
    //                 switch (material) {
    //                     case "ECORUBBER 1":
    //                         maxUsable = 147.00;
    //                         break;
    //                     case "ECORUBBER 2":
    //                         maxUsable = 144.00;
    //                         break;
    //                     case "ECORUBBER 3":
    //                         maxUsable = 146.00;
    //                         break;
    //                     case "ECOFLON 1":
    //                     case "ECOFLON 2":
    //                     case "ECOFLON 3":
    //                     case "ECOMID":
    //                         maxUsable = 0.00;
    //                         break;
    //                     case "ECOSIL":
    //                         maxUsable = 146.00;
    //                         break;
    //                     case "ECOTAL":
    //                         maxUsable = 141.00;
    //                         break;
    //                     case "ECOPUR":
    //                     case "H-ECOPUR":
    //                         maxUsable = 147.00;
    //                         break;
    //                 }
    //                 break;

    //             case "SLM":
    //                 switch (material) {
    //                     case "ECORUBBER 1":
    //                     case "ECORUBBER 2":
    //                     case "ECORUBBER 3":
    //                         maxUsable = 120.00;
    //                         break;
    //                     case "ECOFLON 1":
    //                     case "ECOFLON 2":
    //                     case "ECOFLON 3":
    //                     case "ECOSIL":
    //                         maxUsable = 0.00;
    //                         break;
    //                     case "ECOTAL":
    //                         maxUsable = 147.00;
    //                         break;
    //                     case "ECOMID":
    //                         maxUsable = 140.00;
    //                         break;
    //                     case "ECOPUR":
    //                         maxUsable = 149.00;
    //                         break;
    //                     case "H-ECOPUR":
    //                         maxUsable = 153.00;
    //                         break;
    //                 }
    //                 break;

    //             case "CARVIFLON":
    //                 switch (material) {
    //                     case "ECORUBBER 1":
    //                     case "ECORUBBER 2":
    //                     case "ECORUBBER 3":
    //                     case "ECOSIL":
    //                     case "ECOTAL":
    //                     case "ECOMID":
    //                     case "ECOPUR":
    //                     case "H-ECOPUR":
    //                         maxUsable = 0.00;
    //                         break;
    //                     case "ECOFLON 1":
    //                         maxUsable = 143.00;
    //                         break;
    //                     case "ECOFLON 2":
    //                         maxUsable = 147.00;
    //                         break;
    //                     case "ECOFLON 3":
    //                         maxUsable = 145.00;
    //                         break;
    //                 }
    //                 break;

    //             default:
    //                 maxUsable = 0.00;
    //                 break;
    //         }
    //         if(maxUsable == 0.00){
    //             $("#inputMaxUsable").val("");
    //             $("#inputMaxUsable").attr("placeholder", "Ej. 144");

    //         }else{
    //             $("#inputMaxUsable").val(maxUsable);
    //             $("#inputMaxUsable").attr("placeholder", "Ej. 144");

    //         }
    //     }
    // });

    $("#inputClavePost").on("input change", function(){
        verificarClave();
        //verificarBtnGuardar();
    });

    $("#inputLotePedimento").on("input change", function(){
        verificarBillet();
        verificarBtnGuardar();
    });
    // RESETEAR EL FORMULARIO AL CERRAR
    $("#btnCloseModal").on("click", function(){
        $("#formInventario")[0].reset();
        $("#pValida, #pWarning, #pInvalida3").addClass("d-none");
        $("#inputMaterial").removeClass("input-disabled").addClass("selector");
        $("#inputProveedor").removeClass("input-disabled").addClass("selector");
        $("#inputMedida").removeClass("input-disabled").addClass("input-text");
        $("#inputMaxUsable").removeClass("input-disabled").addClass("input-text");
    });
    // CAMBIAR A add AL CLICK AGREGAR REGISTRO
    $("#btnAgregar").on("click", function(){
        $("#formInventario")[0].reset();
        $('#inputAction').val('insert');
        $("#titleModal").text("Agregar registro");
        $("#formInventario").removeAttr("target");
        noVerificarEsteLP = "";
    });
    // CAMBIAR A add AL CLICK AGREGAR REGISTRO
    $("#btnAgregar2").on("click", function(){
        $("#formInventario")[0].reset();
        $('#inputAction').val('insert2');
        $("#titleModal").text("Agregar registro");
        $("#formInventario").attr("target", "_blank");
        noVerificarEsteLP = "";
    });
    // CLICK A EDITAR UN REGISTRO
    $('#inventarioTable').on('click', '.edit-btn', function() {
        // Limpiar formulario
        $('#formInventario')[0].reset();

        // Variables de edición
        window.CLAVE_VALIDA = true;
        window.LP_VALIDO = true;
        var dataId = $(this).data('id');
        var dataClave = $(this).attr('data-clave');
        var dataMedida = $(this).attr('data-medida');
        var dataProveedor = $(this).attr('data-proveedor');
        var dataMaterial = $(this).attr('data-material');
        var dataMaxUsable = $(this).attr('data-max_usable');
        var dataStock = $(this).attr('data-stock');
        var dataLotePedimento = $(this).attr('data-lote_pedimento');
        var dataEstatus = $(this).attr('data-estatus');

        // Llenar solo los campos que corresponden
        $('#inputId').val(dataId);
        $('#inputClavePost').val(dataClave);
        $('#inputMedida').val(dataMedida);
        $('#inputMaterial').val(dataMaterial);
        $('#inputMaterial').trigger("change");
        $('#inputProveedor').val(dataProveedor);
        $('#inputMaxUsable').val(dataMaxUsable);
        $('#inputStock').val(dataStock);
        $('#inputLotePedimento').val(dataLotePedimento);
        $('#inputEstatus').val(dataEstatus);
        $('#inputAction').val('update');
        $('#modalInventario').modal('show');
        $("#titleModal").text("Editar registro");

        noVerificarEsteLP = dataLotePedimento;
        verificarClave();
        verificarBillet();
        verificarBtnGuardar();
    });

    // ENVIAR FORMULARIO
    $("#btnGuardar").on("click", function(){
        var inputId = $('#inputId').val();
        var actionForm=$('#inputAction').val();
        ajaxBackend(inputId, actionForm);
    });        
    // CLICK A ELIMINAR REGISTRO
    $('#inventarioTable').on('click', '.delete-btn', function() {
        sweetAlertResponse("info", "Información", "Función en desarrollo", "none");
        return;
        var dataId = $(this).data('id');
        var dataLP = $(this).data('lp');
        $("#modalSolicitarArchivar").modal("show");

        $("#inputIdBarra").val(dataId);
        $("#modalSolicitarArchivar p strong").text(dataLP);
    });
    // ENVIAR SOLICITUD PARA ARCHIVAR BARRA
    $("#btnContinuarSolicitarArchivar").on("click", function(){
        let idBarra = $("#inputIdBarra").val();
        let justificacion = $("#inputJustificacionSolicitarArchivar").val().trim();

        if(!idBarra || idBarra == null || idBarra == ""){
            sweetAlertResponse("warning", "Advertencia", "Falta el id de la barra", "none");
            return;
        }        
        if(!justificacion){
            sweetAlertResponse("warning", "Advertencia", "La justificación es obligatoria", "none");
            return;
        }
        if(justificacion.length < 10){
            sweetAlertResponse("warning", "Advertencia", "La justificación debe tener al menos 10 caracteres", "none");
            return;
        }
        ajaxBackend(idBarra, 'delete');
    });
    // CLICK A DESARCHIVAR/ACTIVAR BARRA
    $('#inventarioTable').on('click', '.btn-activar-barra', function() {
        sweetAlertResponse("info", "Información", "Función en desarrollo", "none");
        return;
    });
    $("#overlay").addClass("d-none");
    $("body").removeClass("scroll-disablado");



});