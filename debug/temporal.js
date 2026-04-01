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
    var inputJustificacion=$("#inputJustificacionSolicitarArchivar").val() || "";
    var inputClaveAlterna=$('#inputClaveAlterna').val();

    var actionForm=accion;
    let actionAfter = "none";
    if(actionForm == "delete"){
        actionAfter = "self";
    }

    const fila = $(`#tr_${dataId}`);
    const filaAfectada = $(`#tr_${dataId} td`);
    
    // Crear FormData para enviar archivos
    var formData = new FormData();
    formData.append('id', dataId);
    formData.append('clave', inputClave);
    formData.append('material', inputMaterial);
    formData.append('proveedor', inputProveedor);
    formData.append('medida', inputMedida);
    formData.append('max_usable', inputMaxUsable);
    formData.append('stock', inputStock);
    formData.append('lote_pedimento', inputLotePedimento);
    formData.append('action', actionForm);
    formData.append('estatus', inputEstatus);
    formData.append('justificacion_archivado', inputJustificacion);
    formData.append('inputClaveAlterna', inputClaveAlterna);
    
    // Agregar archivo solo para la acción "delete"
    if (actionForm == "delete") {
        var fotoArchivar = $('#inputFotoArchivar')[0].files[0];
        if (!fotoArchivar) {
            sweetAlertResponse("warning", "Archivo requerido", "Debe subir una fotografía de la barra.", "none");
            return;
        }
        formData.append('foto_archivar', fotoArchivar);
    }

    $.ajax({
        url: '../ajax/post_inventario_cnc.php',
        type: 'POST',
        data: formData,
        processData: false,  // Importante para FormData
        contentType: false,  // Importante para FormData
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
                    $("#modalSolicitarArchivar .btn-close").trigger("click");
                    
                    // Limpiar el campo de archivo y preview
                    $('#inputFotoArchivar').val('');
                    $('#previewFotoArchivar').empty();

                    fila.find(".acciones .edit-btn").remove();
                    fila.find(".acciones .form-delete .delete-btn").remove();
                    // Estatus
                    fila.find(".td-estatus").text("Solicitado para archivar");
                    // Buscar el <p>
                    let p = fila.find(".acciones .form-delete p");

                    // Si no existe, crearlo e insertarlo
                    if (p.length === 0) {
                        fila.find(".acciones .form-delete").append("<p></p>");
                        p = fila.find(".acciones .form-delete p");
                    }

                    // Asignar el texto
                    p.text("Solicitud enviada para archivar");

                    // Resaltar la fila actualizada
                    fila.addClass("bg-row-updated");
                    filaAfectada.addClass("bg-row-updated");

                    setTimeout(() => {
                        fila.removeClass("bg-row-updated");
                        filaAfectada.removeClass("bg-row-updated");
                    }, 1200);

                    // Quitar estilos inline del tr y de sus td (version jQuery)
                    fila.attr("style", "");
                    fila.attr("style", "background-color:#ffeb3b2e !important;");
                    fila.find("td").attr("style", "");
                    fila.find("td").attr("style", "background-color:#ffeb3b2e !important;");

                    $("#modalSolicitarArchivar").modal("hide");

                }else if(actionForm == "autorizar_archivado"){  
                    $("#modalAutorizarBarraArchivada .btn-close").trigger("click");

                    fila.find(".acciones .edit-btn").remove();
                    fila.find(".acciones .form-delete .btn-autorizar-archivado").remove();
                    // Estatus
                    fila.find(".td-estatus").text("Archivado");
                    // Buscar el <p>
                    let p = fila.find(".acciones .form-delete p");

                    // Si no existe, crearlo e insertarlo
                    if (p.length === 0) {
                        fila.find(".acciones .form-delete").append("<p></p>");
                        p = fila.find(".acciones .form-delete p");
                    }

                    // Asignar el texto
                    p.text("Autorizado para archivar");
                    p.append("<i class='bi bi-archive-fill px-2'></i>");

                    // Resaltar la fila actualizada
                    fila.addClass("bg-row-updated");
                    filaAfectada.addClass("bg-row-updated");

                    setTimeout(() => {
                        fila.removeClass("bg-row-updated");
                        filaAfectada.removeClass("bg-row-updated");
                    }, 1200);

                    // Quitar estilos inline del tr y de sus td (version jQuery)
                    fila.attr("style", "");
                    fila.attr("style", "background-color:#9e9e9e90 !important;");
                    fila.find("td").attr("style", "");
                    fila.find("td").attr("style", "background-color:#9e9e9e90 !important;")

                    $("#modalAutorizarBarraArchivada").modal("hide");
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

// Agregar esta función para mostrar preview de la imagen
$(document).ready(function() {
    $('#inputFotoArchivar').on('change', function(e) {
        var file = e.target.files[0];
        var preview = $('#previewFotoArchivar');
        
        if (file) {
            // Validar tamaño (máx. 5MB)
            if (file.size > 5 * 1024 * 1024) {
                sweetAlertResponse("warning", "Archivo muy grande", "La imagen no debe superar los 5MB.", "none");
                $(this).val('');
                preview.empty();
                return;
            }
            
            // Validar tipo de archivo
            if (!file.type.match('image.*')) {
                sweetAlertResponse("warning", "Tipo de archivo inválido", "Solo se permiten archivos de imagen.", "none");
                $(this).val('');
                preview.empty();
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.html('<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">');
            }
            reader.readAsDataURL(file);
        } else {
            preview.empty();
        }
    });
});