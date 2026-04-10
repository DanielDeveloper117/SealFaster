<!-- //////////////////////////////////////////////////////////////////////// -->
<!-- Modal para agregar/editar registro -->
<div class="modal fade" id="modalAgregarEditar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="titleModalAddEdit" class="modal-title" id="modalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formCrearRequisicion" class="form-post" action="" method="POST">                        
                    <input type="hidden" id="inputAction" name="action">
                    <input type="hidden" id="inputIdRequisicion" name="id_requisicion" >
                    <input type="hidden" id="inputId" value="<?= $_SESSION['id'] ?>" name="id_vendedor" >
                    <input type="hidden" id="estatusPendiente" value="Pendiente" name="estatus">
                    <input id="inputCotizaciones" type="hidden" name="cotizaciones">
                    <input id="inputVendedor" type="hidden" name="nombre_vendedor" value="<?= $nombreUser ?>" readonly tabindex="-1">

                    <div class="d-flex justify-content-between ">
                        <div style="width:48%;">
                            <label for="inputSucursal" class="lbl-general">Sucursal/origen *</label>
                            <select id="inputSucursal" class="selector" name="sucursal" required >
                                <option value="" selected disabled>Seleccionar</option>
                                <option value="Ventas Nacionales">Ventas Nacionales</option>
                                <option value="Ventas Internacionales">Ventas Internacionales</option>
                                <option value="Ventas Industriales">Ventas Industriales</option>
                                <option value="Sucursal Industrias">Sucursal Industrias</option>
                                <option value="Sucursal Monterrey">Sucursal Monterrey</option>
                                <option value="Sucursal Queretaro">Sucursal Queretaro</option>
                                <option value="Sucursal Saltillo">Sucursal Saltillo</option>
                                <option value="Sucursal Toluca">Sucursal Toluca</option>
                                <option value="Sucursal Veracruz">Sucursal Veracruz</option>
                                <option value="Taller">Taller</option>
                            </select>
                        </div>
                        <div style="width:48%;">
                            <label for="inputCliente" class="lbl-general">Cliente *</label>
                            <input id="inputCliente" type="text" class="input-text" name="cliente" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between ">
                        <div style="width:48%;">
                            <label for="inputPedido" class="lbl-general">Num. Pedido *</label>
                            <input id="inputPedido" type="text" class="input-text" name="num_pedido" required>
                        </div>
                        <div style="width:48%;">
                            <label for="inputPaqueteria" class="lbl-general">Paqueteria *</label>
                            <select id="inputPaqueteria" class="selector" name="paqueteria" required >
                                <option value="" selected disabled>Seleccionar</option>
                                <option value="INBOX">INBOX</option>
                                <option value="PAQUETE EXPRESS">PAQUETE EXPRESS</option>
                                <option value="PRIMERA PLUS">PRIMERA PLUS</option>
                                <option value="DHL">DHL</option>
                                <option value="ESTRELLA BLANCA">ESTRELLA BLANCA</option>
                                <option value="VENCEDOR">VENCEDOR</option>
                                <option value="TRES GUERRAS">TRES GUERRAS</option>
                                <option value="FEDEX">FEDEX</option>
                                <option value="ODM">ODM</option>
                                <option value="ESTAFETA">ESTAFETA</option>
                                <option value="CASTORES">CASTORES</option>
                                <option value="FUTURA">FUTURA</option>
                                <option value="JR">JR</option>
                                <option value="POTOSINOS">POTOSINOS</option>
                            </select>
                        </div>                        
                    </div>
                    <div class="d-flex justify-content-between ">
                        <div style="width:48%;">
                            <label for="inputFactura" class="lbl-general text-break">Factura/remision/nota <?php if($tipo_usuario == "Vendedor" && $areaUser != "Ventas Nacionales"){ echo "*"; } ?></label>
                            <input id="inputFactura" type="text" class="input-text" name="factura" <?php if($tipo_usuario == "Vendedor" && $areaUser != "Ventas Nacionales"){ echo "required"; } ?>>
                        </div>
                        <div style="width:48%;">
                            <label for="inputComentario" class="lbl-general">Comentario (opcional)</label>
                            <input id="inputComentario" type="text" maxlength="50" class="input-text" name="comentario" placeholder="Solo comentarios generales...">
                            <small id="contadorComentario" style="display:block; text-align:right; font-size:12px; color:#555;">0 / 50 caracteres</small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                        <div style="width:100%;">
                            <div id="alertaAdjunto" class="mt-2 p-2" style="display:none; background-color: #fff3cd; border: 1px solid #ffe69c; border-radius: 5px; font-size: 16px; color: #856404;">
                                <i class="bi bi-exclamation-triangle-fill"></i> 
                                <strong>¿Vas a adjuntar algo?</strong> Parece que mencionas adjuntar algun archivo, recuerda subirlo después de crear la requisición haciendo clic en el icono <i class="bi bi-chat-left-text"></i>.
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:100%;">
                            <label for="buscadorCotizaciones" class="lbl-general">Agregar cotizaciones *</label>
                            <select id="buscadorCotizaciones">
                                <option value="" selected disabled>Seleccione una cotización</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between ">
                        <div class="mb-3" style="width:100%;overflow-x:auto;">
                            <table id="miniTableCotizaciones" class="table table-bordered border border-2 tabla-billets">
                                <thead>
                                    <tr>
                                        <th scope="col">Remover</th>
                                        <th scope="col">Id cotizacion</th>
                                        <th scope="col">Perfil</th>
                                        <!-- <th scope="col">Tipo medida</th> -->
                                        <th scope="col">Medidas</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <button id="btnGuardar" type="submit" class="btn-general">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////MODAL GERENTE DEBE AUTORIZAR /////////////////////// -->
<div class="modal fade" id="modalGerenteAutoriza" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Siga las instrucciónes para autorizar</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Escanea el código QR con tu dispositivo movil o haz click en el enlace, luego en el recuadro dibuja tu firma para autorizar. Caducará en 5 minutos.</p>
                <div class="d-flex flex-column flex-md-row justify-content-evenly justify-content-md-center align-items-center">
                    <!-- CONTENEDOR QR -->
                    <div id="containerQRLink" 
                        class="d-flex flex-column align-items-center text-center p-2"
                        >
                        
                        <div id="ContainerQR" 
                            class="d-none d-md-flex justify-content-center align-items-center"
                            >
                        </div>

                        <div id="qrLinkContainer" 
                            class="d-flex d-md-flex justify-content-center text-break mb-md-3"
                            style="word-break: break-all;">
                        </div>
                    </div>
                    <?php 
                        $id_usuario = $_SESSION['id'];
                        $nombreArchivo = $id_usuario . ".png";
                        $carpeta = '../files/signatures/';
                        $rutaCompleta = $carpeta . $nombreArchivo;
                        if(file_exists($rutaCompleta)){
                            echo '
                                <div class="d-flex flex-column justify-content-center">
                                    <h5 class="text-center text-md-start">¿Autorizar con firma predeterminada?</h5>
                                    <img src="'.$rutaCompleta.'?v='.time().'" width="150" height="100" class="align-self-center mb-3">
                                    <button type="button" class="btnFirmaPredeterminada btn-auth d-none" 
                                    data-id-requisicion="" data-autoriza="">Aceptar</button>
                                </div>                            
                            ';
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
<!-- //////////////////////////MODAL ADMIN DEBE AUTORIZAR /////////////////////// -->
<div class="modal fade" id="modalAdminAutoriza" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">Siga las instrucciónes para autorizar maquinado</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Escanea el código QR con tu dispositivo movil o haz click en el enlace, luego en el recuadro dibuja tu firma para autorizar. Caducará en 5 minutos.</p>
                <div class="d-flex flex-column flex-md-row justify-content-evenly justify-content-md-center align-items-center">
                    <!-- CONTENEDOR QR -->
                    <div id="containerQRLink" 
                        class="d-flex flex-column align-items-center text-center p-2"
                        >
                        
                        <div id="ContainerQR2" 
                            class="d-none d-md-flex justify-content-center align-items-center"
                            >
                        </div>

                        <div id="qrLinkContainer2" 
                            class="d-flex d-md-flex justify-content-center text-break mb-md-3"
                            style="word-break: break-all;">
                        </div>
                    </div>
                    <?php 
                        $id_usuario = $_SESSION['id'];
                        $nombreArchivo = $id_usuario . ".png";
                        $carpeta = '../files/signatures/';
                        $rutaCompleta = $carpeta . $nombreArchivo;
                        if(file_exists($rutaCompleta)){
                            echo '
                                <div class="d-flex flex-column justify-content-center">
                                    <h5 class="text-center text-md-start">¿Autorizar con firma predeterminada?</h5>
                                    <img src="'.$rutaCompleta.'?v='.time().'" width="150" height="100" class="align-self-center mb-3">
                                    <button type="button" class="btnFirmaPredeterminada btn-auth d-none" 
                                    data-id-requisicion="" data-autoriza="">Aceptar</button>
                                </div>                            
                            ';
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
<!-- //////////////////////////MODAL: ENVIAR ESTAS SEGURO DE CANCELAR? /////////////////////// -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">¿Desea continuar?</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta acción cancelará la requisición con Folio: <strong></strong></p>
                <form action="" method="POST">
                    <input id="inputRequisicionCancelar" type="hidden" name="id_requisicion">
                    <button id="btnContinuarCancelar" type="button" class="btn-cancel">Continuar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- ////////////////////////// ARCHIVAR LA REQUISICION, ES IRREVERSIBLE /////////////////////// -->
<div class="modal fade" id="modalArchivar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <span class="title-form">¿Desea continuar?</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Esta acción cambiará el estatus de la requisición a "Archivada", esta acción es irreversible y el folio solo se mostrará filtrando por requisiciones archivadas.</p>
                <div class="my-3">
                    <hr>
                    <h6>Justificación *</h6>
                    <textarea id="justificacionArchivar" class="form-control" rows="3" placeholder="Ingrese justificación para archivar..." required></textarea>
                </div>
                <div>
                    <input id="inputRequisicionArchivar" type="hidden" name="id_requisicion" >
                    <button id="btnConfirmarArchivar" type="button" class="btn-general">Continuar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- //////////////////////////////////////////////////////////////////////// -->
 <script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    if (typeof cotizacionesSeleccionadas === 'undefined') {
        var cotizacionesSeleccionadas = [];
    }
    let intervaloQR = null;
    // ID de requisición actual en edición (0 si es nueva)
    let idRequisicionEditando = 0;
    // ============================================================
    //              ******** FUNCIONES ********
    // ============================================================
    // -------------- AGREGAR/EDITAR ------------------------------
    /**
     * Carga las opciones del Chosen con cotizaciones del servidor.
     * @param {number} excludeReq - ID de requisición a excluir (para edición)
     * @param {function} callback - Función a ejecutar después de cargar
     */
    function cargarCotizacionesChosen(excludeReq, callback) {
        let url = '../ajax/ajax_cotizaciones_chosen.php';
        if (excludeReq && excludeReq > 0) {
            url += '?exclude_req=' + excludeReq;
        }
        // Limpiar opciones previas (excepto la primera placeholder)
        $('#buscadorCotizaciones option:not(:first)').remove();

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data && data.length > 0) {
                    $.each(data, function(index, item) {
                        if (item.di_sello == "0.00") item.di_sello = item.di_sello2;
                        if (item.de_sello == "0.00") item.de_sello = item.de_sello2;
                        if (item.a_sello == "0.00") item.a_sello = item.a_sello2;

                        const estaVencida = item.esta_vencida == 1 || item.horas_restantes < 0;
                        const enRequisicion = item.en_requisicion == 1;

                        let textoOpcion = `${item.id_cotizacion} - ${item.perfil_sello} - ${item.di_sello}/${item.de_sello}/${item.a_sello}`;

                        // Determinar estado de la opción
                        let esDeshabilitada = false;
                        let estiloInline = '';

                        if (enRequisicion) {
                            textoOpcion += ` (Ya existe en el folio #${item.id_requisicion_asignada})`;
                            esDeshabilitada = true;
                            estiloInline = 'color: #856404; font-style: italic; background-color: #fff3cd;';
                        } else if (estaVencida && item.simulacion == 0) {
                            textoOpcion += ` - Vencida internamente`;
                            esDeshabilitada = true;
                            estiloInline = 'color: #ff1100; font-style: italic; background-color: #f8f9fa;';
                        } else if (item.simulacion == 1) {
                            textoOpcion += ` - Stock no sujeto a inventario CNC`;
                            esDeshabilitada = true;
                            estiloInline = 'color: #6c757d; font-style: italic; background-color: #f8f9fa;';
                        } else {
                            //estiloInline = 'color: #000; background-color: #95D2B3;';
                            estiloInline = 'color: #000; background-color: #fff;';
                        }

                        $("#buscadorCotizaciones").append(
                            `<option id="c_${item.id_cotizacion}" 
                                    value="${item.id_cotizacion}"
                                    data-id="${item.id_cotizacion}"
                                    data-perfil="${item.perfil_sello}"
                                    data-tipomedida="${item.tipo_medida}"
                                    data-di="${item.di_sello || item.di_sello2}"
                                    data-de="${item.de_sello}"
                                    data-a="${item.a_sello}"
                                    data-en-requisicion="${item.en_requisicion}"
                                    ${esDeshabilitada ? 'disabled' : ''}
                                    ${estiloInline ? 'style="' + estiloInline + '"' : ''}
                            >${textoOpcion}</option>`
                        );
                    });
                } else {
                    console.log('No se encontraron cotizaciones');
                }
                $('#buscadorCotizaciones').trigger("chosen:updated");
                if (typeof callback === 'function') callback();
            },
            error: function() {
                console.error('Error al realizar la petición AJAX de cotizaciones');
                if (typeof callback === 'function') callback();
            }
        });
    }

    // Actualizar la mini tabla de cotizaciones y ocultar las ya seleccionadas en el Chosen
    function actualizarTablaYSeleccion(cotizaciones) {
        $('#miniTableCotizaciones tbody').empty();
        // Reset: mostrar todas las opciones
        $('#buscadorCotizaciones option').removeClass('d-none');
        
        cotizaciones.forEach(function(cot) {
            // Ocultar del selector las que ya están seleccionadas
            $(`#c_${cot.id_cotizacion}`).addClass('d-none');
            
            // Normalizar medidas
            let di = cot.di_sello;
            let de = cot.de_sello;
            let a = cot.a_sello;
            if (di == "0.00" && cot.di_sello2) di = cot.di_sello2;
            if (de == "0.00" && cot.de_sello2) de = cot.de_sello2;
            if (a == "0.00" && cot.a_sello2) a = cot.a_sello2;

            // Agregar fila a la tabla
            $('#miniTableCotizaciones tbody').append(`
                <tr data-id="${cot.id_cotizacion}">
                    <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila">X</button></td>
                    <td>${cot.id_cotizacion}</td>
                    <td>${cot.perfil_sello}</td>
                    <td>${di}/${de}/${a}</td>
                </tr>
            `);
        });
        $('#buscadorCotizaciones').trigger("chosen:updated");
    }

    // Sincronizar el input oculto con el arreglo global
    function sincronizarInputCotizaciones() {
        const valor = cotizacionesSeleccionadas.length > 0 ? cotizacionesSeleccionadas.join(', ') : '';
        $('#inputCotizaciones').val(valor);
    }

    // --------------- AUTORIZACION ------------------------------
    // middleware para saber si ya se autorizo la requisicion 
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
                    if (respuesta.autorizado === true || respuesta.autorizado == 'true') {
                        cancelarVerificacionQR();
                        sweetAlertResponse("success", "Autorización confirmada", `La requisición ha sido autorizada correctamente.`, "self");
                    }
                },
                error: function () {
                    console.error("Error al consultar el estatus de autorización.");
                }
            });
            console.log("Se ha enviado la solicitud para verificar autorizacion QR.");
        }, 4000);
    }
    // cancelar el middleware de verificacion 
    function cancelarVerificacionQR() {
        if (intervaloQR) {
            clearInterval(intervaloQR);
            intervaloQR = null;
            console.log("Verificación QR cancelada.");
        }
    }
    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        // --------------- INICIALIZACIONES -----------------------------
        // Iniciar el buscador Chosen de cotizaciones
        $('#buscadorCotizaciones').chosen({
            placeholder_text_single: "Seleccione una cotización",
            no_results_text: "No se encontró",
            width: "100%"
        });
        const inputComentario = document.getElementById('inputComentario');
        const contador = document.getElementById('contadorComentario');
        const alertaAdjunto = document.getElementById('alertaAdjunto');

        // Cargar las cotizaciones en el Chosen al inicio (sin excluir ninguna req)
        cargarCotizacionesChosen(0);

        // -------------------------------------------------------------
        // -------------- AGREGAR/EDITAR ------------------------------
        // Abrir modal para nueva requisición
        $("#btnAgregar").on("click", function(){
            idRequisicionEditando = 0;
            $('#inputAction').val('insert');
            $("#titleModalAddEdit").text("Crear nueva requisición");
            // Limpiar tabla, input y arreglo
            $('#miniTableCotizaciones tbody').empty();
            $('#inputCotizaciones').val('');
            cotizacionesSeleccionadas = [];
            // Recargar Chosen sin excluir ninguna requisición
            cargarCotizacionesChosen(0);
        });

        // Abrir modal para editar requisición existente
        $('#productionTable').on('click', '.edit-btn', function() {
            const idReq = $(this).data('id_requisicion');
            $("#overlay").removeClass("d-none");

            $.ajax({
                url: '../ajax/get_requisicion_vn.php',
                type: 'GET',
                data: { id: idReq },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $("#overlay").addClass("d-none");
                        const r = response.requisicion;
                        idRequisicionEditando = r.id_requisicion;
                        $('#inputAction').val('update');
                        // Llenar campos simples
                        $('#inputIdRequisicion').val(r.id_requisicion);
                        $('#inputVendedor').val(r.nombre_vendedor);
                        $('#inputSucursal').val(r.sucursal);
                        $('#inputCliente').val(r.cliente);
                        $('#inputPedido').val(r.num_pedido);
                        $('#inputFactura').val(r.factura);
                        $('#inputPaqueteria').val(r.paqueteria);
                        $('#inputComentario').val(r.comentario);

                        // Reiniciar y llenar el arreglo global
                        const idsString = r.cotizaciones || "";
                        cotizacionesSeleccionadas = idsString.split(',')
                                                    .map(s => s.trim())
                                                    .filter(s => s !== "");
                        sincronizarInputCotizaciones();

                        // Recargar Chosen excluyendo esta requisición (para que sus propias cotizaciones no aparezcan como "en uso")
                        cargarCotizacionesChosen(r.id_requisicion, function() {
                            // Después de cargar, asegurarnos que las cotizaciones de esta req existan en el select
                            response.cotizaciones_detalles.forEach(function(cot) {
                                if ($(`#buscadorCotizaciones option[value="${cot.id_cotizacion}"]`).length === 0) {
                                    let di = cot.di_sello;
                                    let de = cot.de_sello;
                                    let a = cot.a_sello;
                                    if (di == "0.00" && cot.di_sello2) di = cot.di_sello2;
                                    if (de == "0.00" && cot.de_sello2) de = cot.de_sello2;
                                    if (a == "0.00" && cot.a_sello2) a = cot.a_sello2;
                                    let nuevaOpcion = new Option(`${cot.id_cotizacion} - ${cot.perfil_sello} - ${di}/${de}/${a}`, cot.id_cotizacion, false, false);
                                    $(nuevaOpcion).attr({
                                        'data-perfil': cot.perfil_sello,
                                        'data-di': di,
                                        'data-de': de,
                                        'data-a': a
                                    });
                                    $('#buscadorCotizaciones').append(nuevaOpcion);
                                }
                            });
                            // Actualizar tabla visual y ocultar las seleccionadas
                            actualizarTablaYSeleccion(response.cotizaciones_detalles);
                        });

                        $('#modalAgregarEditar').modal('show');
                        $("#titleModalAddEdit").text("Editar registro");
                    } else {
                        $("#overlay").addClass("d-none");
                        sweetAlertResponse("error", "Ocurrió un problema en el servidor", response.error_detail || "Error desconocido", "none");
                    }
                },
                error: function (response) {
                    $("#overlay").addClass("d-none");
                    console.error("Error al consultar requisición.");
                    sweetAlertResponse("error", "Ocurrió un problema en el servidor", "Error desconocido", "none");
                }
            });
        });

        // ===========================================================
        // ENVIAR FORMULARIO DE REQUISICION VIA AJAX (INSERT/UPDATE)
        // ===========================================================
        $("#formCrearRequisicion").on("submit", function(e){
            e.preventDefault(); // Evitar el POST síncrono
            
            const btnGuardar = $("#btnGuardar");
            btnGuardar.prop('disabled', true).css("pointer-events", "none").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');

            // Sincronizar el input oculto antes de enviar
            sincronizarInputCotizaciones();

            const formData = $(this).serialize();

            $.ajax({
                url: '../ajax/guardar_requisicion_vn.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Enviar notificación en paralelo (fire-and-forget)
                        $.ajax({
                            url: "../ajax/ajax_notificacion.php",
                            type: "POST",
                            data: { mensaje: "Se ha generado una requisicion" }
                        });
                        sweetAlertResponse("success", "Proceso exitoso", response.message, "self");
                    } else {
                        sweetAlertResponse("warning", "Advertencia", response.message, "none");
                        btnGuardar.prop('disabled', false).css("pointer-events", "auto").html('Guardar');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX al guardar requisición:', error);
                    sweetAlertResponse("error", "Error", "Error al procesar la solicitud. Intente nuevamente.", "none");
                    btnGuardar.prop('disabled', false).css("pointer-events", "auto").html('Guardar');
                }
            });
        });

        // Seleccionar una cotización del Chosen
        $("#buscadorCotizaciones").on("change", function () {
            const selectedId = $(this).val();
            if (!selectedId) return; 
            const $option = $(this).find("option:selected");
            const id = String($option.val()); 
            const perfil = $option.data("perfil") || "N/A";
            const di = $option.data("di") || "0";
            const de = $option.data("de") || "0";
            const a  = $option.data("a")  || "0";

            // Verificar si la cotización ya está en una requisición (doble seguridad frontend)
            if ($option.data('en-requisicion') == 1) {
                sweetAlertResponse("warning", "No disponible", "Esta cotización ya se encuentra en otra requisición.", "none");
                $(this).val('').trigger("chosen:updated");
                return;
            }

            // Ocultar la opción en el selector
            $option.addClass('d-none');

            // Check de duplicados
            if (cotizacionesSeleccionadas.map(String).includes(id)) {
                sweetAlertResponse("warning", "Duplicada", "Esta cotización ya fue agregada a la lista.", "none");
            } else {
                // Agregar al arreglo global
                cotizacionesSeleccionadas.push(id);
                // Inyectar en la tabla visual
                $('#miniTableCotizaciones tbody').append(`
                    <tr data-id="${id}">
                        <td><button type="button" class="btn btn-danger btn-sm btnEliminarFila">X</button></td>
                        <td>${id}</td>
                        <td>${perfil}</td>
                        <td>${di}/${de}/${a}</td>
                    </tr>
                `);
                // Sincronizar input oculto
                sincronizarInputCotizaciones();
            }
            // Reset del Chosen
            $(this).val('').trigger("chosen:updated");
        });

        // Eliminar una cotización seleccionada
        $(document).on('click', '.btnEliminarFila', function () {
            let fila = $(this).closest('tr');
            let idAEliminar = fila.find('td:eq(1)').text().trim(); 
            // 1. Mostrar de nuevo en el selector
            $("#buscadorCotizaciones option[value='" + idAEliminar + "']").removeClass('d-none');
            // 2. Filtrar el arreglo global
            cotizacionesSeleccionadas = cotizacionesSeleccionadas.filter(id => String(id) !== String(idAEliminar));
            // 3. Sincronizar input oculto
            sincronizarInputCotizaciones();
            console.log("IDs restantes:", $('#inputCotizaciones').val());
            // 4. Limpiar UI
            fila.remove();
            $('#buscadorCotizaciones').trigger("chosen:updated");
        });

        // Lógica del Contador en comentario y Alerta
        if (inputComentario && contador) {
            const keywords = [/adjunto/i, /imagen/i, /dibujo/i, /plano/i, /foto/i, /archivo/i];

            inputComentario.addEventListener('input', function() {
                const valor = this.value;
                contador.textContent = `${valor.length} / 50 caracteres`;

                const tieneKeyword = keywords.some(regex => regex.test(valor));
                if (tieneKeyword) {
                    alertaAdjunto.style.display = 'block';
                    setTimeout(() => {
                        $("#inputComentario").val("");
                        $("#inputComentario").attr("placeholder","Archivos adjuntos van por cotización individual..");
                    }, 500);
                }
            });
        }
        // cerrar modal y resetear form
        $(".btn-close").on("click", function(){
            $(".form-post")[0].reset();
        });
        // -------------------------------------------------------------
        // --------------- AUTORIZACION ------------------------------
        // Generar QR para autorizar
        $("#productionTable").on('click', ".btn-gerente-autoriza, .btn-admin-autoriza", function () {
            let idRequisicion = $(this).data('id-requisicion');
            let autoriza = $(this).data('autoriza');

            // Ocultar el botón de firma mientras se genera el QR
            $(".btnFirmaPredeterminada").addClass("d-none");

            // Llamar al script PHP que devuelve JSON con el QR
            $.ajax({
                url: `../includes/functions/generar_qr.php?id_requisicion=${encodeURIComponent(idRequisicion)}&t=${encodeURIComponent(autoriza)}`,
                method: "GET",
                dataType: "json",
                success: function (resp) {
                    if (resp.success) {
                        // Mostrar QR volátil
                        let imgTag = `<img src="data:image/png;base64,${resp.qrBase64}" width="200" height="200">`;
                        $("#ContainerQR, #ContainerQR2").html(imgTag);

                        // Mostrar la URL debajo del QR
                        let linkTag = `
                            <a href="${resp.url}" target="_blank" class="mt-2 fs-3">
                                Ir a firmar
                            </a>`;
                        $("#qrLinkContainer, #qrLinkContainer2").html(linkTag);

                        // Mostrar el botón de firma solo después de que el QR y el link estén disponibles
                        $(".btnFirmaPredeterminada")
                            .removeClass("d-none")
                            .data("id-requisicion", idRequisicion)
                            .data("autoriza", autoriza);

                        // Iniciar verificación periódica
                        verificarAutorizacionQR(idRequisicion, autoriza);
                    } else {
                        sweetAlertResponse("error", "Error al generar QR", resp.error || "Error desconocido.", "self");
                    }
                },
                error: function (xhr, status, error) {
                    sweetAlertResponse("error", "Error AJAX", "No se pudo generar el QR: " + error, "self");
                }
            });

        });
        // se cierra el modal de autorizar y se cancela la verificacion middleware
        $('#modalGerenteAutoriza, #modalAdminAutoriza').on('hidden.bs.modal', function () {
            cancelarVerificacionQR();
        });
        // autorizar con firma predeterminada
        $(".btnFirmaPredeterminada").on("click", function(){
            const idRequisicionX = $(this).data('id-requisicion');
            const autorizaX = $(this).data('autoriza');
            cancelarVerificacionQR();
            $(".btnFirmaPredeterminada").addClass("d-none");
            $(this).addClass("d-none");
            $.ajax({
                url: '../ajax/autorizar_firma_predeterminada.php',
                method: 'POST',
                data: {
                    id_requisicion: idRequisicionX,
                    t: autorizaX
                },
                success: function(data) {
                    if (data.success) {
                        sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                    } else {
                        sweetAlertResponse("warning", "Advertencia", data.error, "self");
                    }
                },
                error: function () {
                    sweetAlertResponse("error", "Error", "Ocurrio algo inesperado al autorizar", "self");
                    console.error("Error al consultar el estatus de autorización.");
                }
            });        
        });
        // -------------------------------------------------------------
        // --------------- CANCELACION ------------------------------
        // abrir modal de cancelar autorizacion de la requisicion
        $("#productionTable").on('click', ".btn-cancelar", function () {
            let dataIdRequisicionCancelar = $(this).data('id-requisicion');
            $('#inputRequisicionCancelar').val(dataIdRequisicionCancelar);
            $("#modalCancelar .modal-body strong").text(dataIdRequisicionCancelar);
        });
        // continuar con la cancelacion
        $("#btnContinuarCancelar").on('click', function () {
            let idRequisicionCancelar = $('#inputRequisicionCancelar').val();
            $(this).addClass("d-none");
            $.ajax({
                url: '../ajax/cancelar_requisicion.php',
                method: 'POST',
                data: {
                    id_requisicion: idRequisicionCancelar
                },
                success: function(data) {
                    if (data.success) {
                        sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                    } else {
                        sweetAlertResponse("warning", "Advertencia", data.error, "self");
                    }
                },
                error: function () {
                    sweetAlertResponse("error", "Error", "Ocurrio algo inesperado al autorizar", "self");
                    console.error("Error al consultar el estatus de autorización.");
                }
            });
        });
        // -------------------------------------------------------------
        // ----------------- ARCHIVAR ------------------------------
        // abrir formulario de archivar requisicion
        $('#productionTable').on('click', '.btn-archivar-requisicion', function() {
            $dataIdRequisicion = $(this).data('id-requisicion');
            
            $("#inputRequisicionArchivar").val($dataIdRequisicion);
        });  
        // enviar formulario de archivar
        $("#btnConfirmarArchivar").on('click', function () {
            let justificacionArchivar = $("#justificacionArchivar").val();
            let inputRequisicionArchivar = $("#inputRequisicionArchivar").val();

            if(!justificacionArchivar || justificacionArchivar.length < 10){
                sweetAlertResponse("warning", "Faltan datos", "Ingrese una justificación de mínimo 10 caracteres", "none");
                return;
            }
            if(!inputRequisicionArchivar){
                sweetAlertResponse("warning", "Faltan datos", "Falta el id de requisición. Contactar a sistemas.", "none");
                return;
            }
            $(this).addClass("d-none");
            $.ajax({
                url: '../ajax/archivar_requisicion.php',
                type: 'POST',
                data: { 
                    justificacion:justificacionArchivar,
                    id_requisicion: inputRequisicionArchivar
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
    });
</script>