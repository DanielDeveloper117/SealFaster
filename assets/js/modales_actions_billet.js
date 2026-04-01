// ============================================================
//          ******** VARIABLES GLOBALES ********
// ============================================================   
var noVerificarEsteLP = "";
window.CLAVE_VALIDA = false;
window.LP_VALIDO = false;
let almacenesDisponiblesBilletForm = [];
// ============================================================
//              ******** FUNCIONES ********
// ============================================================
/**
 * Carga los almacenes disponibles desde el backend
 */
function cargarAlmacenesBilletForm() {
    $.ajax({
        url: '../ajax/ajax_almacenes.php',
        type: 'GET',
        data: {
            excluir: "0"
        },
        dataType: 'json',
        success: function(data) {
            if (data.success && data.almacenes) {
                almacenesDisponiblesBilletForm = data.almacenes;
                llenarSelectorAlmacenesBilletForm();
            } else {
                console.error('Error cargando almacenes:', data.message);
                sweetAlertResponse("warning", "Ocurrió un problema", data.message, "none");
            }
        },
        error: function(xhr, status, error) {
            // Aquí 'data' NO existe. Debes usar los argumentos de esta función.
            console.error('Error de red/servidor:', error);
            
            // Intentamos obtener un mensaje del servidor si es que mandó algo
            let mensajeError = "No se pudo conectar con el servidor.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensajeError = xhr.responseJSON.message;
            }
            sweetAlertResponse("warning", "Ocurrió un problema" , mensajeError, "none");
        }
    });
}
/**
 * Llena el selector de almacen con los almacenes disponibles
 */
function llenarSelectorAlmacenesBilletForm() {
    const selector = $('#inputAlmacenIdBilletForm');      
    selector.html("");  
    selector.append(`<option value="" disabled selected>Seleccionar almacén</option>`);
    if (almacenesDisponiblesBilletForm.length > 0) {
        almacenesDisponiblesBilletForm.forEach(almacen => {
            selector.append(`<option value="${almacen.id}">${almacen.almacen} - ${almacen.descripcion}</option>`);
        });
    }
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

                // ── ENCONTRADO (array con un elemento) ──────────────────────
                if (Array.isArray(data) && data.length > 0) {
                    let rec    = data[0];
                    let medida = rec.interior + "/" + rec.exterior;

                    window.CLAVE_VALIDA = true;
                    $("#pWarning").addClass("d-none");
                    $("#pValida").removeClass("d-none")
                        .text(`Clave válida. Material: ${rec.material_corregido}. Proveedor: ${rec.proveedor}`);

                    // Autorellenar campos bloqueados
                    $("#inputMaterial").removeClass("selector").addClass("input-disabled").val(rec.material_corregido);
                    $("#inputProveedor").removeClass("selector").addClass("input-disabled").val(rec.proveedor);
                    $("#inputMedida").val(medida).removeClass("input-text").addClass("input-disabled");
                    $("#inputMaxUsable").val(rec.max_usable).removeClass("input-text").addClass("input-disabled");

                    // Estatus
                    let estatusActual = $("#inputEstatus").val();
                    if (estatusActual !== "En uso" && estatusActual !== "Maquinado en curso") {
                        $("#inputEstatus").val("Disponible para cotizar");
                    }

                    // Si el usuario digitó la clave ALTERNA → mostrar aviso y corregir el campo
                    if (rec.es_alterna == 1) {
                        $("#inputClavePost").val(rec.clave_srs_utilizada); // poner la clave principal
                        $("#pAlterna").removeClass("d-none").text(rec.mensaje);
                    } else {
                        // Digitó la clave principal → mostrar clave alterna si existe
                        if (rec.clave_alterna !== "") {
                            $("#pAlterna").removeClass("d-none").text(rec.mensaje);
                        } else {
                            $("#pAlterna").addClass("d-none");
                        }
                    }

                    $("#pAlterna").removeClass("p-warning").addClass("p-valida");
                }
                // ── NO ENCONTRADO ────────────────────────────────────────────
                else {
                    let msg = (data && data.mensaje)
                        ? data.mensaje
                        : 'No se encontró ninguna clave. Favor de comunicarse con el área de sistemas para alta de clave.';

                    window.CLAVE_VALIDA = false;
                    $("#pWarning").removeClass("d-none").text(msg);
                    $("#pValida, #pAlterna").addClass("d-none");

                    // Resetear campos editables
                    $("#inputMaterial").removeClass("input-disabled").addClass("selector");
                    $("#inputProveedor").removeClass("input-disabled").addClass("selector");
                    $("#inputMedida").removeClass("input-disabled").addClass("input-text");
                    $("#inputMaxUsable").removeClass("input-disabled").addClass("input-text");
                    $("#inputEstatus").val("Clave nueva pendiente");
                    $("#inputClaveAlterna").val(claveValue);
                    $("#pAlterna").removeClass("p-valida").addClass("p-warning");
                }
            },
            error: function() {
                console.error('Error al realizar la peticion AJAX');
                $('#pWarning').removeClass("d-none").text('Error al verificar la clave. Intente nuevamente.');
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
    var inputAlmacenId = $('#inputAlmacenIdBilletForm').val() || "";
    var inputClave=$('#inputClavePost').val() || "";
    var inputMaterial=$('#inputMaterial').val() || "";
    var inputProveedor=$('#inputProveedor').val() || "";
    var inputMedida=$('#inputMedida').val() || "";
    var inputMaxUsable=$('#inputMaxUsable').val() || "";
    var inputStock=$('#inputStock').val() || "";
    var inputLotePedimento=$('#inputLotePedimento').val() || "";
    var inputEstatus=$('#inputEstatus').val() || "";
    var inputJustificacion=$("#inputJustificacionSolicitarArchivar").val() || "";
    var inputClaveAlterna=$('#inputClaveAlterna').val();

    var actionForm=accion;
    let actionAfter = "none";
    if(actionForm == "delete" || actionForm == "autorizar_archivado"){
        actionAfter = "self";
    }

    const fila = $(`#tr_${dataId}`);
    const filaAfectada = $(`#tr_${dataId} td`);
    
    // Crear FormData para enviar archivos
    var formData = new FormData();
    formData.append('id', dataId);
    formData.append('almacen_id', inputAlmacenId);
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
                window.LP_VALIDO = true;
                $("#modalInventario #btnCloseModal").trigger("click");
                
                if(actionForm == "update"){
                    // Código para actualizar la fila en la tabla...
                    const claveLimpia = inputClave.replace(/\s+/g, "").trim();
                    fila.find(".td-almacen").text(almacenesDisponiblesBilletForm.find(a => a.id == inputAlmacenId)?.almacen || "N/A");
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
                    btn.attr('data-almacen_id', inputAlmacenId);
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
                sweetAlertResponse("success", "Proceso exitoso", data.message, actionAfter);
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

// ============================================================
//          ******** EVENTOS DEL DOM ********
// ============================================================  
$(document).ready(function(){
    // =================================
    //  ****** INICIALIZACIONES ****** 
    cargarAlmacenesBilletForm();
    // =================================

    // Agregar esta función para mostrar preview de la imagen
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
        $("#pAlterna, #pValida, #pWarning, #pInvalida3").addClass("d-none");
        $("#inputMaterial").removeClass("input-disabled").addClass("selector");
        $("#inputProveedor").removeClass("input-disabled").addClass("selector");
        $("#inputMedida").removeClass("input-disabled").addClass("input-text");
        $("#inputMaxUsable").removeClass("input-disabled").addClass("input-text");
    });
    // CAMBIAR A add AL CLICK AGREGAR REGISTRO
    $("#btnAgregar").on("click", function(){
        $("#formInventario")[0].reset();
        $("#pAlterna, #pValida, #pWarning, #pInvalida3").addClass("d-none");
        $("#inputAlmacenIdBilletForm").removeClass("input-disabled").attr("disabled", false);
        $('#inputAction').val('insert');
        $("#titleModal").text("Agregar registro");
        $("#formInventario").removeAttr("target");
        noVerificarEsteLP = "";
    });
    // CAMBIAR A add AL CLICK AGREGAR REGISTRO
    $("#btnAgregar2").on("click", function(){
        $("#formInventario")[0].reset();
        $("#pAlterna, #pValida, #pWarning, #pInvalida3").addClass("d-none");
        $('#inputAction').val('insert2');
        $("#titleModal").text("Agregar registro");
        $("#formInventario").attr("target", "_blank");
        noVerificarEsteLP = "";
    });
    // CLICK A EDITAR UN REGISTRO
    $('#inventarioTable').on('click', '.edit-btn', function() {
        // Limpiar formulario
        $('#formInventario')[0].reset();

        $("#inputAlmacenIdBilletForm").addClass("input-disabled").attr("disabled", true);

        // Variables de edición
        window.CLAVE_VALIDA = true;
        window.LP_VALIDO = true;
        var dataId = $(this).data('id');
        var dataAlmacenId = $(this).attr('data-almacen_id');
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
        $('#inputAlmacenIdBilletForm').val(dataAlmacenId);
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
        //sweetAlertResponse("info", "Información", "Función en desarrollo", "none");
        //return;
        
        // resetear formulario
        $('#formSolicitarArchivar')[0].reset();
        $('#previewFotoArchivar').empty(); // Limpiar el preview de la imagen
        
        var dataId = $(this).data('id');
        var dataLP = $(this).data('lp');
        $("#modalSolicitarArchivar").modal("show");

        $("#inputIdBarra").val(dataId);
        $('#inputLotePedimento').val(dataLP);
        $("#modalSolicitarArchivar p strong").text(dataLP);
    });
    // Limpiar formulario cuando se cierra el modal
    $('#modalSolicitarArchivar').on('hidden.bs.modal', function () {
        $('#formSolicitarArchivar')[0].reset();
        $('#previewFotoArchivar').empty();
    });
    // Limpiar formulario cuando se hace clic en la X
    $('#modalSolicitarArchivar .btn-close').on('click', function() {
        $('#formSolicitarArchivar')[0].reset();
        $('#previewFotoArchivar').empty();
    });
    // ENVIAR SOLICITUD PARA ARCHIVAR BARRA
    $("#btnContinuarSolicitarArchivar").on("click", function(){
        let idBarra = $("#inputIdBarra").val();
        let justificacion = $("#inputJustificacionSolicitarArchivar").val().trim();
        $('#inputLotePedimento').val();

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
    // CLICK A DESARCHIVAR/ACTIVAR BARRA, PROCESO INVERSO A ARCHIVAR
    $('#inventarioTable').on('click', '.btn-autorizar-archivado', function() {
        //sweetAlertResponse("info", "Información", "Función en desarrollo", "none");
        var dataId = $(this).data('id');
        var dataLP = $(this).data('lp');
        $("#modalAutorizarBarraArchivada").modal("show");
        $("#inputIdBarraArchivada").val(dataId);
        $('#inputLotePedimento').val(dataLP);
    });
    // AUTORIZAR ARCHIVAR LA BARRA
    $("#btnConfirmAutorizarBarraArchivada").on("click", function(){
        let idBarra = $("#inputIdBarraArchivada").val();
        
        if(!idBarra || idBarra == null || idBarra == ""){
            sweetAlertResponse("warning", "Advertencia", "Falta el id de la barra", "none");
            return;
        } 
        ajaxBackend(idBarra, 'autorizar_archivado');
    });
    // CLICK A VER LA JUSTIFICACION Y FOTO PARA ARCHIVAR (versión SweetAlert con imagen clickeable)
    $('#inventarioTable').on('click', '.btn-ver-justificacion', function() {
        var dataJus = $(this).data('jus');
        var dataRuta = $(this).data('ruta');
        var dataLote = $(this).data('lote');
        var dataFecha = $(this).data('fecha');
        
        // Crear contenido HTML para el sweetalert
        var contentHtml = '<div class="text-start">';
        contentHtml += '<h6>Justificación:</h6>';
        contentHtml += '<div class="border rounded p-3 mb-3" style="min-height: 100px; max-height: 200px; overflow-y: auto; background-color: #f8f9fa;">';
        contentHtml += dataJus || 'Sin justificación proporcionada.';
        contentHtml += '</div>';
        
        if (dataRuta && dataRuta.trim() !== '') {
            contentHtml += '<h6>Fotografía:</h6>';
            contentHtml += '<div class="text-center">';
            contentHtml += '<a href="' + dataRuta + '" target="_blank" title="Haz clic para ver la imagen completa">';
            contentHtml += '<img src="' + dataRuta + '" alt="Foto de la barra" class="img-fluid rounded border" style="max-height: 200px; cursor: pointer;" ';
            contentHtml += 'onerror="this.style.display=\'none\'; this.parentElement.innerHTML=\'<p class=\'text-muted\'>Error al cargar la imagen</p>\';" ';
            contentHtml += 'onclick="window.open(\'' + dataRuta + '\', \'_blank\');">';
            contentHtml += '</a>';
            contentHtml += '<p class="small text-muted mt-1">Lote: ' + (dataLote || 'N/A') + ' | Fecha: ' + (dataFecha || 'N/A') + '</p>';
            contentHtml += '<p class="small text-muted m-0">Haz clic en la imagen para verla en tamaño completo</p>';
            contentHtml += '</div>';
        } else {
            contentHtml += '<div class="text-center text-muted">';
            contentHtml += '<i class="bi bi-image" style="font-size: 3rem;"></i>';
            contentHtml += '<p>No hay fotografía disponible</p>';
            contentHtml += '</div>';
        }
        contentHtml += '</div>';
        
        Swal.fire({
            title: 'Solicitud de archivado',
            html: contentHtml,
            width: '700px',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Cerrar',
            confirmButtonColor: '#55AD9B'
        });
    });
});