    $(document).ready(function(){
        var noVerificarEsteLP = "";
        window.CLAVE_VALIDA = false;
        window.LP_VALIDO = false;

        // CONSULTA AJAX PARA MATERIALES DESDE PARAMETROS2
        $.ajax({
            url: '../ajax/ajax_materiales_parametros2.php', 
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.length > 0) {
                    $.each(data, function(index, item) {
                        $("#inputMaterial").append(
                            `
                            <option value="${item.caso}">${item.caso}</option>
                            `
                        );
                    });
                } else {
                }
            },
            error: function() {
                console.error('Error al realizar la peticion AJAX');
            }
        });

        //---------------------------------------- @ FUNCIONES ------------------------------------
        function verificarClave() {
            let claveValue = $("#inputClavePost").val(); 

            if (claveValue !== "") {
                console.log("El usuario ingreso un valor en el inputClavePost.");

                $.ajax({
                    url: '../ajax/ajax_parametros.php',
                    type: 'POST',
                    data: { clave: claveValue },
                    dataType: 'json',
                    success: function(data) {
                        if (data.length > 0) {
                            let medida = data[0].interior + "/" + data[0].exterior;
                            window.CLAVE_VALIDA = true;
                            $("#pInvalida, #pInvalida2").addClass("d-none");
                            $("#pValida").removeClass("d-none");
                            $("#pValida").text(`Clave valida encontrada. Material: ${data[0].material}. Proveedor: ${data[0].proveedor}`);
                            
                            //$("#inputProveedor").val(data[0].proveedor);
                            $("#inputMedida").val(medida);
                            $("#inputEstatus").val("Habilitado");
                        } else {
                            window.CLAVE_VALIDA = false;
                            $("#pInvalida, #pInvalida2").removeClass("d-none");
                            $("#pValida").addClass("d-none");
                            //$("#inputProveedor").val("");
                            //$("#inputProveedor").html('<option selected disabled>Seleccionar</option>');
                            //$("#inputMedida").val("");
                            $("#inputEstatus").val("Deshabilitado");
                        }
                        //verificarBtnGuardar();
                    },
                    error: function() {
                        console.error('Error al realizar la peticion AJAX');
                        $('#pInvalida2').text('Error en ajax validar clave.');
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
            var dataClave=$('#inputClavePost').val();
            var dataMaterial=$('#inputMaterial').val();
            var dataProveedor=$('#inputProveedor').val();
            var dataMedida=$('#inputMedida').val();
            var dataMaxUsable=$('#inputMaxUsable').val();
            var dataStock=$('#inputStock').val();
            var dataLotePedimento=$('#inputLotePedimento').val();
            var dataEstatus=$('#inputEstatus').val();

            var actionForm=accion;
            let actionAfter = "";
            if(actionForm == "delete" || actionForm == "update"){
                actionAfter = "self";
            }else{
                actionAfter = "none";
            } 

            $.ajax({
                url: '../ajax/post_inventario_cnc.php',
                type: 'POST',
                data: { 
                    id: dataId,
                    clave: dataClave,
                    material: dataMaterial,
                    proveedor: dataProveedor,
                    medida: dataMedida,
                    max_usable: dataMaxUsable,
                    stock: dataStock,
                    lote_pedimento: dataLotePedimento,
                    action: actionForm,
                    estatus: dataEstatus
                },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        sweetAlertResponse("success", "Proceso exitoso", data.message, actionAfter);
                        window.LP_VALIDO = true;
                        $("#modalInventario #btnCloseModal").trigger("click");
                        // $.ajax({
                        //     url: "../ajax/ajax_notificacion.php",
                        //     type: "POST",
                        //     data: { mensaje: "CNC ha "+actionForm+" un billet: "+dataClave },
                        //     success: function(response) {
                        //         console.log("Notificación enviada: ", response);
                        //     },
                        //     error: function(error) {
                        //         console.error("Error al enviar la notificación: ", error);
                        //     }
                        // });
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
        $("#selectorMaterial, #inputMaterial").on("change", function() { 
            $("#selectorProveedor").html('<option value="all" selected>Todos</option>');
            $("#inputProveedor").html('<option selected disabled>Seleccionar</option>');
            var materialSeleccionado = $(this).val();
            $.ajax({
                url: '../ajax/ajax_proveedores.php', 
                type: 'POST',
                data: { material: materialSeleccionado },
                dataType: 'json',
                success: function(data) {
                    if (data.length > 0) {
                        $.each(data, function(index, item) {
                            $("#selectorProveedor").append(
                                `
                                <option value="${item.proveedor}">${item.proveedor}</option>
                                `
                            );
                            $("#inputProveedor").append(
                                `
                                <option value="${item.proveedor}">${item.proveedor}</option>
                                `
                            );
                        });
                    } else {
                    }
                },
                error: function() {
                    console.error('Error al realizar la petición AJAX');
                }
            });

        });
        
        $("#inputMaterial, #inputProveedor").on("change", function(){
            let material = $("#inputMaterial").val();
            let proveedor = $("#inputProveedor").val();
            let maxUsable = 0; 

            if(material !== "" && proveedor !== ""){
                switch (proveedor) {
                    case "SKF":
                        switch (material) {
                            case "ECORUBBER 1":
                            case "ECORUBBER 2":
                            case "ECORUBBER 3":
                            case "ECOSIL":
                                maxUsable = 122.00;
                                break;
                            case "ECOFLON 1":
                                maxUsable = 146.00;
                                break;
                            case "ECOFLON 2":
                            case "ECOFLON 3":
                                maxUsable = 140.00;
                                break;
                            case "ECOTAL":
                                maxUsable = 138.00;
                                break;
                            case "ECOMID":
                                maxUsable = 155.00;
                                break;
                            case "ECOPUR":
                                maxUsable = 146.00;
                                break;
                            case "H-ECOPUR":
                                maxUsable = 145.00;
                                break;
                        }
                        break;

                    case "TRYGONAL":
                        switch (material) {
                            case "ECORUBBER 1":
                                maxUsable = 147.00;
                                break;
                            case "ECORUBBER 2":
                                maxUsable = 144.00;
                                break;
                            case "ECORUBBER 3":
                                maxUsable = 146.00;
                                break;
                            case "ECOFLON 1":
                            case "ECOFLON 2":
                            case "ECOFLON 3":
                            case "ECOMID":
                                maxUsable = 0.00;
                                break;
                            case "ECOSIL":
                                maxUsable = 146.00;
                                break;
                            case "ECOTAL":
                                maxUsable = 141.00;
                                break;
                            case "ECOPUR":
                            case "H-ECOPUR":
                                maxUsable = 147.00;
                                break;
                        }
                        break;

                    case "SLM":
                        switch (material) {
                            case "ECORUBBER 1":
                            case "ECORUBBER 2":
                            case "ECORUBBER 3":
                                maxUsable = 120.00;
                                break;
                            case "ECOFLON 1":
                            case "ECOFLON 2":
                            case "ECOFLON 3":
                            case "ECOSIL":
                                maxUsable = 0.00;
                                break;
                            case "ECOTAL":
                                maxUsable = 147.00;
                                break;
                            case "ECOMID":
                                maxUsable = 140.00;
                                break;
                            case "ECOPUR":
                                maxUsable = 149.00;
                                break;
                            case "H-ECOPUR":
                                maxUsable = 153.00;
                                break;
                        }
                        break;

                    case "CARVIFLON":
                        switch (material) {
                            case "ECORUBBER 1":
                            case "ECORUBBER 2":
                            case "ECORUBBER 3":
                            case "ECOSIL":
                            case "ECOTAL":
                            case "ECOMID":
                            case "ECOPUR":
                            case "H-ECOPUR":
                                maxUsable = 0.00;
                                break;
                            case "ECOFLON 1":
                                maxUsable = 143.00;
                                break;
                            case "ECOFLON 2":
                                maxUsable = 147.00;
                                break;
                            case "ECOFLON 3":
                                maxUsable = 145.00;
                                break;
                        }
                        break;

                    default:
                        maxUsable = 0.00;
                        break;
                }
                if(maxUsable == 0.00){
                    $("#inputMaxUsable").val("");
                    $("#inputMaxUsable").attr("placeholder", "Ej. 144");

                }else{
                    $("#inputMaxUsable").val(maxUsable);
                    $("#inputMaxUsable").attr("placeholder", "Ej. 144");

                }
            }
        });

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
            $("#pValida, #pInvalida, #pInvalida2, #pInvalida3").addClass("d-none");
        });
        // CAMBIAR A add AL CLICK AGREGAR REGISTRO
        $("#btnAgregar").on("click", function(){
            $('#inputAction').val('insert');
            $("#titleModal").text("Agregar registro");
            $("#formInventario").removeAttr("target");
            noVerificarEsteLP = "";
        });
        // CAMBIAR A add AL CLICK AGREGAR REGISTRO
        $("#btnAgregar2").on("click", function(){
            $('#inputAction').val('insert2');
            $("#titleModal").text("Agregar registro");
            $("#formInventario").attr("target", "_blank");
            noVerificarEsteLP = "";
        });
        // CLICK A EDITAR UN REGISTRO
        $('#inventarioTable').on('click', '.edit-btn', function() {
            window.CLAVE_VALIDA = true;
            window.LP_VALIDO = true;
            var dataId = $(this).data('id');
            $dataClave=$(this).attr('data-clave');
            $dataMedida=$(this).attr('data-medida');
            $dataProveedor=$(this).attr('data-proveedor');
            $dataMaterial=$(this).attr('data-material');
            $dataMaxUsable=$(this).attr('data-max_usable');
            $dataStock=$(this).attr('data-stock');
            $dataLotePedimento=$(this).attr('data-lote_pedimento');

            $('#inputId').val(dataId);
            $('#inputClavePost').val($dataClave);
            $('#inputMedida').val($dataMedida);
            $('#inputMaterial').val($dataMaterial);
            $('#inputMaterial').trigger("change");
            $('#inputProveedor').val($dataProveedor);
            $('#inputMaxUsable').val($dataMaxUsable);
            $('#inputStock').val($dataStock);
            $('#inputLotePedimento').val($dataLotePedimento);
            $('#inputAction').val('update');
            $('#modalInventario').modal('show');
            $("#titleModal").text("Editar registro");

            noVerificarEsteLP = $dataLotePedimento;
            verificarClave();
            verificarBillet();
            verificarBtnGuardar();
            //console.log( window.CLAVE_VALIDA);
            console.log( window.LP_VALIDO);
        });
        // ENVIAR FORMULARIO
        $("#btnGuardar").on("click", function(){
            var inputId = $('#inputId').val();
            var actionForm=$('#inputAction').val();
            ajaxBackend(inputId, actionForm);
        });        
        // CLICK A ELIMINAR REFGISTRO
        $('#inventarioTable').on('click', '.delete-btn', function() {
            var dataId = $(this).data('id');
            ajaxBackend(dataId, 'delete');
        });
        $("#overlay").addClass("d-none");
        $("body").removeClass("scroll-disablado");



    });