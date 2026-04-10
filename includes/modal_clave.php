<!-- ============================================================
     modal_clave.php
     Modal reutilizable para insertar y editar registros de la
     tabla parametros (claves SRS).
     Incluir desde claves.php con:
         include(ROOT_PATH . 'includes/modal_clave.php');
     Requiere que alerts_sweet_alert.js ya este cargado en la pagina.
     ============================================================ -->
<div class="modal fade" id="modalClave" tabindex="-1" aria-hidden="true"
     aria-labelledby="titleModalClave"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="titleModalClave" class="modal-title">Agregar registro</h5>
                <button id="btnCerrarModalClave" type="button"
                        class="btn-close btnCerrar"
                        data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <form id="formClave" autocomplete="off">
                    <input type="hidden" id="inputIdClave"     name="id"     value="0">
                    <input type="hidden" id="inputActionClave" name="action" value="insert">

                    <!-- Clave -->
                    <div class="mb-3">
                        <label for="inputClaveSRS" class="lbl-general">
                            Clave <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="inputClaveSRS" class="input-text"
                               name="clave" placeholder="Ingrese la clave SRS" required>
                        <p id="pClaveExiste"   class="d-none p-invalida  mt-1"></p>
                        <p id="pClaveLibre"    class="d-none p-valida    mt-1"></p>
                    </div>

                    <!-- Clave Alterna (opcional) -->
                    <div class="mb-3">
                        <label for="inputClaveAlternaClave" class="lbl-general">
                            Clave Alterna <span class="text-muted" style="font-size:0.85rem;">(opcional)</span>
                        </label>
                        <input type="text" id="inputClaveAlternaClave" class="input-text"
                               name="clave_alterna" placeholder="Ingrese una clave alterna (si aplica)">
                        <p id="pClaveAlternaExiste"   class="d-none p-invalida  mt-1"></p>
                        <p id="pClaveAlternaLibre"    class="d-none p-valida    mt-1"></p>
                    </div>

                    <!-- Material / Proveedor -->
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:48%;">
                            <label for="inputMaterialClave" class="lbl-general">
                                Material <span class="text-danger">*</span>
                            </label>
                            <select id="inputMaterialClave" class="selector selectorMaterialesParametros" name="material" required>
                                <option value="" disabled selected>Seleccionar</option>
                            </select>
                        </div>
                        <div style="width:48%;">
                            <label for="inputProveedorClave" class="lbl-general">
                                Proveedor <span class="text-danger">*</span>
                            </label>
                            <select id="inputProveedorClave" class="selector selectorProveedoresParametros" name="proveedor" required>
                                <option value="" disabled selected>Seleccionar</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tipo -->
                    <div class="mb-3">
                        <label for="inputTipoClave" class="lbl-general">
                            Tipo <span class="text-danger">*</span>
                        </label>
                        <select id="inputTipoClave" class="selector" name="tipo" required>
                            <option value="" disabled selected>Seleccionar</option>
                        </select>
                    </div>

                    <!-- Interior / Exterior -->
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:48%;">
                            <label for="inputInteriorClave" class="lbl-general">
                                Medida interior <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="inputInteriorClave" class="input-text"
                                   name="interior" min="0" step="1" required>
                        </div>
                        <div style="width:48%;">
                            <label for="inputExteriorClave" class="lbl-general">
                                Medida exterior <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="inputExteriorClave" class="input-text"
                                   name="exterior" min="0" step="1" required>
                        </div>
                    </div>

                    <!-- Max. Length / Precio -->
                    <div class="d-flex justify-content-between mb-3">
                        <div style="width:48%;">
                            <label for="inputMaxUsableClave" class="lbl-general">
                                Max. Length <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="inputMaxUsableClave" class="input-text"
                                   name="max_usable" min="0" required>
                        </div>
                        <div style="width:48%;">
                            <label for="inputPrecioClave" class="lbl-general">
                                Precio <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="inputPrecioClave" class="input-text"
                                   name="precio" min="0" step="0.01" required>
                        </div>
                    </div>

                    <button id="btnGuardarClave" type="button" class="btn-general">
                        <i class="bi bi-floppy me-1"></i> Guardar
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="<?= controlCache('../assets/js/dynamic_selectors.js'); ?>"></script>

<script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    var _claveIdEnEdicion  = 0;       // id del registro en modo update (0 = insert)
    var _claveOriginal     = '';      // clave antes de editar, para no re-validar duplicado contra si misma
    var _claveAlternaOriginal = '';   // clave_alterna antes de editar
    var _tiposDisponibles  = [];      // cache de tipos cargados desde el servidor


    // ============================================================
    //              ******** FUNCIONES ********
    // ============================================================
    /**
     * Carga los tipos de material disponibles desde el servidor
     * y los inserta en el selector de tipo del modal.
     */
    function cargarTiposModalClave() {
        if (_tiposDisponibles.length > 0) {
            // Ya fueron cargados previamente, solo repoblar el selector
            poblarSelectorTipos();
            return;
        }
        $.ajax({
            url: '../ajax/ajax_tiposmateriales_parametros.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                _tiposDisponibles = data || [];
                poblarSelectorTipos();
            },
            error: function () {
                console.error('Error al cargar tipos de material para modal clave.');
            }
        });
    }
    /**
     * Puebla el selector de tipo con los valores en cache.
     */
    function poblarSelectorTipos() {
        var $sel = $('#inputTipoClave');
        $sel.find('option:not([disabled])').remove();
        $.each(_tiposDisponibles, function (i, item) {
            $sel.append('<option value="' + item.tipo + '">' + item.tipo + '</option>');
        });
    }
    /**
     * Limpia el formulario y sus estados visuales.
     * @param {boolean} limpiarClave  Si true resetea tambien el campo clave y sus mensajes.
     */
    function limpiarFormModalClave(limpiarClave) {
        $('#formClave')[0].reset();
        $('#inputIdClave').val('0');
        $('#inputActionClave').val('insert');
        _claveIdEnEdicion = 0;
        _claveOriginal    = '';
        _claveAlternaOriginal = '';

        // Mensajes de validacion
        $('#pClaveExiste, #pClaveLibre, #pClaveAlternaExiste, #pClaveAlternaLibre').addClass('d-none').text('');
    }
    /**
     * Verifica en tiempo real si la clave ya existe en la base de datos.
     * En modo update ignora la clave original del registro en edicion.
     */
    function verificarDuplicadoClave() {
        var valor = $('#inputClaveSRS').val().replace(/\s+/g, '').trim();

        if (valor === '') {
            $('#pClaveExiste, #pClaveLibre').addClass('d-none').text('');
            return;
        }

        // En modo update, si el valor no cambio respecto a la clave original, no consultar
        if (_claveIdEnEdicion > 0 && valor === _claveOriginal) {
            $('#pClaveExiste').addClass('d-none').text('');
            $('#pClaveLibre').removeClass('d-none').text('Clave sin cambios.');
            return;
        }

        $.ajax({
            url: '../ajax/post_clave.php',
            type: 'GET',
            data: {
                action:    'verificar',
                clave:     valor,
                excluir_id: _claveIdEnEdicion
            },
            dataType: 'json',
            success: function (resp) {
                if (resp.existe) {
                    $('#pClaveLibre').addClass('d-none').text('');
                    $('#pClaveExiste').removeClass('d-none').text('Esta clave ya existe en el catalogo.');
                } else {
                    $('#pClaveExiste').addClass('d-none').text('');
                    $('#pClaveLibre').removeClass('d-none').text('Clave disponible.');
                }
            },
            error: function () {
                console.error('Error al verificar duplicado de clave.');
            }
        });
    }
    /**
     * Envia el formulario de clave al backend via AJAX.
     * En caso de exito actualiza la fila en la tabla DOM sin recargar la pagina
     * (modo update) o inserta una nueva fila al inicio de tbody (modo insert).
     */
    function ajaxGuardarClave() {
        var id            = parseInt($('#inputIdClave').val())    || 0;
        var action        = $('#inputActionClave').val();
        var clave         = $('#inputClaveSRS').val().replace(/\s+/g, '').trim();
        var clave_alterna = $('#inputClaveAlternaClave').val().replace(/\s+/g, '').trim();
        var material      = $('#inputMaterialClave').val()        || '';
        var proveedor     = $('#inputProveedorClave').val()       || '';
        var tipo          = $('#inputTipoClave').val()            || '';
        var interior      = $('#inputInteriorClave').val().replace(/\s+/g, '').trim();
        var exterior      = $('#inputExteriorClave').val().replace(/\s+/g, '').trim();
        var max_usable    = $('#inputMaxUsableClave').val().replace(/\s+/g, '').trim();
        var precio        = $('#inputPrecioClave').val().replace(/\s+/g, '').trim();

        // Validacion de campos requeridos
        if (!clave || !material || !proveedor || !tipo ||
            interior === '' || exterior === '' || max_usable === '' || precio === '') {
            sweetAlertResponse('warning', 'Campos incompletos', 'Todos los campos son obligatorios.', 'none');
            return;
        }

        // Bloquear boton para evitar envios duplicados
        var $btn = $('#btnGuardarClave');
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Guardando...');

        $.ajax({
            url: '../ajax/post_clave.php',
            type: 'POST',
            data: {
                action:         action,
                id:             id,
                clave:          clave,
                clave_alterna:  clave_alterna,
                material:       material,
                proveedor:      proveedor,
                tipo:           tipo,
                interior:       interior,
                exterior:       exterior,
                max_usable:     max_usable,
                precio:         precio
            },
            dataType: 'json',
            success: function (resp) {
                $btn.prop('disabled', false).html('<i class="bi bi-floppy me-1"></i> Guardar');

                if (resp.success) {
                    // Función auxiliar para refrescar DOM
                    var executeDomRefresh = function() {
                        $('#modalClave').modal('hide');
                        limpiarFormModalClave(true);
                        if (action === 'update') {
                            actualizarFilaTabla(id, clave, clave_alterna, proveedor, tipo, material, interior, exterior, max_usable, precio);
                        } else {
                            // insert: agregar fila nueva al inicio del tbody
                            insertarFilaTabla(resp.id, clave, clave_alterna, proveedor, tipo, material, interior, exterior, max_usable, precio);
                        }
                    };

                    // Lógica interactiva si se hallaron match con inventario_cnc
                    if (resp.requires_sync) {
                        Swal.fire({
                            title: '¿Actualizar Inventario CNC?',
                            html: `Detectamos <strong>${resp.sync_count}</strong> barras coincidentes en inventario para esta clave / clave alterna.<br><br>¿Deseas sincronizar y sobreescribir los datos de las barras del inventario respecto a los datos de la clave?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, sincronizar datos de barras',
                            cancelButtonText: 'No, no actualizar datos de inventario',
                            confirmButtonColor: '#28a745',
                            cancelButtonColor: '#6c757d',
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Enviar confirmación al backend
                                var syncId = (action === 'update') ? id : resp.id;
                                $.post('../ajax/post_clave.php', { action: 'sync_inventario', id: syncId }, function(rSync) {
                                    executeDomRefresh();
                                    sweetAlertResponse('success', 'Completado', 'Parámetros guardados y barras de inventario sincronizadas con éxito.', 'self');
                                }, 'json').fail(function() {
                                    executeDomRefresh();
                                    sweetAlertResponse('error', 'Completado con advertencias', 'Los parámetros se guardaron pero la sincronización de inventario falló.', 'none');
                                });
                            } else {
                                executeDomRefresh();
                                sweetAlertResponse('success', 'Completado', 'Parámetros guardados. El inventario_cnc no fue modificado.', 'none');
                            }
                        });
                    } else {
                        executeDomRefresh();
                        sweetAlertResponse('success', 'Completado', resp.message, 'none');
                    }
                    
                    $.ajax({
                        url: "../ajax/ajax_notificacion.php",
                        type: "POST",
                        data: { mensaje: "Accion en alguna clave"},
                        success: function(response) {
                            console.log("Notificacion enviada: ", response);
                        },
                        error: function(error) {
                            console.error("Error al enviar la notificacion: ", error);
                        }
                    });
                } else {
                    sweetAlertResponse('warning', 'Hubo un problema', resp.message, 'none');
                }
            },
            error: function (xhr, status, error) {
                $btn.prop('disabled', false).html('<i class="bi bi-floppy me-1"></i> Guardar');
                console.error('Error AJAX guardar clave:', error);
                sweetAlertResponse('error', 'Error', 'Error al guardar el registro. ' + error, 'none');
            }
        });
    }
    /**
     * Actualiza una fila existente en la tabla #clavesTable sin recargar la pagina.
     */
    function actualizarFilaTabla(id, clave, clave_alterna, proveedor, tipo, material, interior, exterior, max_usable, precio) {
        var $fila     = $('#tr_clave_' + id);
        var $celdas   = $fila.find('td');

        if ($fila.length === 0) {
            // Si por alguna razon la fila no esta en el DOM, hacer reload suave
            window.location.reload();
            return;
        }

        // Actualizar celdas de datos (indices segun el orden de columnas en clavesTable)
        $fila.find('.td-clave').text(clave);
        $fila.find('.td-clave_alterna').text(clave_alterna);
        $fila.find('.td-proveedor').text(proveedor);
        $fila.find('.td-tipo').text(tipo);
        $fila.find('.td-material').text(material);
        $fila.find('.td-interior').text(interior);
        $fila.find('.td-exterior').text(exterior);
        $fila.find('.td-max_usable').text(max_usable);
        $fila.find('.td-precio').text(precio);

        // Actualizar data-attributes del boton editar para reflejar nuevos valores
        var $btnEdit = $fila.find('.edit-btn');
        $btnEdit.data('clave',          clave);
        $btnEdit.data('clave_alterna',  clave_alterna);
        $btnEdit.data('proveedor',      proveedor);
        $btnEdit.data('tipo',           tipo);
        $btnEdit.data('material',       material);
        $btnEdit.data('interior',       interior);
        $btnEdit.data('exterior',       exterior);
        $btnEdit.data('max_usable',     max_usable);
        $btnEdit.data('precio',         precio);

        // Efecto visual de fila actualizada
        $fila.addClass('bg-row-updated');
        $celdas.addClass('bg-row-updated');
        setTimeout(function () {
            $fila.removeClass('bg-row-updated');
            $celdas.removeClass('bg-row-updated');
        }, 1400);
    }
    /**
     * Inserta una fila nueva al inicio del tbody de #clavesTable.
     * Si la tabla aun no tiene resultados, reemplaza el mensaje vacio.
     * @param {number} id  Id del registro recien insertado, devuelto por el backend.
     */
    function insertarFilaTabla(id, clave, clave_alterna, proveedor, tipo, material, interior, exterior, max_usable, precio) {
        var $tbody = $('#clavesTable tbody');

        // Si existia el mensaje de "sin resultados", removerlo
        $tbody.find('tr.fila-vacia').remove();

        var filaHtml =
            '<tr id="tr_clave_' + id + '">' +
                '<td class="td-first-actions">' +
                    '<div class="d-flex gap-2 container-actions">' +
                        '<button class="btn-general edit-btn" title="Editar registro"' +
                            ' data-id="'             + id                         + '"' +
                            ' data-clave="'          + _escaparAttr(clave)              + '"' +
                            ' data-clave_alterna="'  + _escaparAttr(clave_alterna)     + '"' +
                            ' data-proveedor="'      + _escaparAttr(proveedor)         + '"' +
                            ' data-tipo="'           + _escaparAttr(tipo)              + '"' +
                            ' data-material="'       + _escaparAttr(material)          + '"' +
                            ' data-interior="'       + _escaparAttr(interior)          + '"' +
                            ' data-exterior="'       + _escaparAttr(exterior)          + '"' +
                            ' data-max_usable="'     + _escaparAttr(max_usable)        + '"' +
                            ' data-precio="'         + _escaparAttr(precio)            + '">' +
                            '<i class="bi bi-pencil-square"></i>' +
                        '</button>' +
                        '<button class="btn-cancel delete-btn" title="Eliminar registro"' +
                            ' data-id="'    + id                       + '"' +
                            ' data-clave="' + _escaparAttr(clave) + '">' +
                            '<i class="bi bi-trash"></i>' +
                        '</button>' +
                    '</div>' +
                '</td>' +
                '<td class="td-clave">'           + _escaparTexto(clave)           + '</td>' +
                '<td class="td-clave_alterna">'   + _escaparTexto(clave_alterna)   + '</td>' +
                '<td class="td-proveedor">'       + _escaparTexto(proveedor)       + '</td>' +
                '<td class="td-tipo">'       + _escaparTexto(tipo)       + '</td>' +
                '<td class="td-material">'   + _escaparTexto(material)   + '</td>' +
                '<td class="td-interior">'   + _escaparTexto(interior)   + '</td>' +
                '<td class="td-exterior">'   + _escaparTexto(exterior)   + '</td>' +
                '<td class="td-max_usable">' + _escaparTexto(max_usable) + '</td>' +
                '<td class="td-precio">'     + _escaparTexto(precio)     + '</td>' +
            '</tr>';

        $tbody.prepend(filaHtml);

        // Efecto visual de fila nueva
        var $filaNew  = $('#tr_clave_' + id);
        var $celdasNw = $filaNew.find('td');
        $filaNew.addClass('bg-row-updated');
        $celdasNw.addClass('bg-row-updated');
        setTimeout(function () {
            $filaNew.removeClass('bg-row-updated');
            $celdasNw.removeClass('bg-row-updated');
        }, 1400);

        // Actualizar el contador de resultados si existe
        var $contador = $('#contadorResultados');
        if ($contador.length) {
            var actual = parseInt($contador.text()) || 0;
            $contador.text(actual + 1);
        }
    }
    /**
     * Escapa texto para insertarlo como contenido de celda HTML.
     */
    function _escaparTexto(str) {
        return $('<span>').text(String(str)).html();
    }
    /**
     * Escapa texto para usarlo dentro de un atributo HTML.
     */
    function _escaparAttr(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }
    /**
     * Verifica en tiempo real si la clave alterna ya existe en la base de datos.
     * En modo update ignora la clave_alterna original del registro en edicion.
     */
    function verificarDuplicadoClaveAlterna() {
        var valor = $('#inputClaveAlternaClave').val().replace(/\s+/g, '').trim();

        if (valor === '') {
            $('#pClaveAlternaExiste, #pClaveAlternaLibre').addClass('d-none').text('');
            return;
        }

        // En modo update, si el valor no cambio respecto a la clave_alterna original, no consultar
        if (_claveIdEnEdicion > 0 && valor === _claveAlternaOriginal) {
            $('#pClaveAlternaExiste').addClass('d-none').text('');
            $('#pClaveAlternaLibre').removeClass('d-none').text('Sin cambios.');
            return;
        }

        $.ajax({
            url: '../ajax/post_clave.php',
            type: 'GET',
            data: {
                action:             'verificar_clave_alterna',
                clave_alterna:      valor,
                excluir_id:         _claveIdEnEdicion
            },
            dataType: 'json',
            success: function (resp) {
                if (resp.existe) {
                    $('#pClaveAlternaLibre').addClass('d-none').text('');
                    $('#pClaveAlternaExiste').removeClass('d-none').text('Esta clave alterna ya existe.');
                } else {
                    $('#pClaveAlternaExiste').addClass('d-none').text('');
                    $('#pClaveAlternaLibre').removeClass('d-none').text('Disponible.');
                }
            },
            error: function () {
                console.error('Error al verificar clave_alterna.');
            }
        });
    }


    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        // =================================
        //  ****** INICIALIZACIONES ****** 
        // Cargar tipos al iniciar para tenerlos en cache
        cargarTiposModalClave();
        // =================================

        // ---- Cerrar modal: limpiar formulario ----
        $('#btnCerrarModalClave').on('click', function () {
            limpiarFormModalClave(true);
        });
        // Si se cierra con la X de Bootstrap tambien limpiar
        $('#modalClave').on('hidden.bs.modal', function () {
            limpiarFormModalClave(true);
        });
        // ---- Input clave: verificar duplicado en tiempo real ----
        $('#inputClaveSRS').on('input', function () {
            verificarDuplicadoClave();
        });
        // ---- Input clave_alterna: verificar duplicado en tiempo real ----
        $('#inputClaveAlternaClave').on('input', function () {
            verificarDuplicadoClaveAlterna();
        });
        // ---- Boton abrir modal en modo AGREGAR ----
        // El boton disparador puede estar en claves.php con id="btnAbrirAgregar"
        $(document).on('click', '#btnAbrirAgregar', function () {
            limpiarFormModalClave(true);
            $('#titleModalClave').text('Agregar registro');
            $('#inputActionClave').val('insert');
        });
        // ---- Click EDITAR en la tabla ----
        $(document).on('click', '#clavesTable .edit-btn', function () {
            limpiarFormModalClave(true);

            var id            = $(this).data('id');
            var clave         = $(this).data('clave')            || '';
            var clave_alterna = $(this).data('clave_alterna')    || '';
            var proveedor     = $(this).data('proveedor')        || '';
            var tipo          = $(this).data('tipo')             || '';
            var material      = $(this).data('material')         || '';
            var interior      = $(this).data('interior')         ;
            var exterior      = $(this).data('exterior')         || '';
            var maxUsable     = $(this).data('max_usable')       || '';
            var precio        = $(this).data('precio')           || '';

            _claveIdEnEdicion     = parseInt(id) || 0;
            _claveOriginal        = clave;
            _claveAlternaOriginal = clave_alterna;

            $('#inputIdClave').val(id);
            $('#inputActionClave').val('update');
            $('#inputClaveSRS').val(clave);
            $('#inputClaveAlternaClave').val(clave_alterna);
            $('#inputMaterialClave').val(material);
            $('#inputProveedorClave').val(proveedor);
            $('#inputInteriorClave').val(interior);
            $('#inputExteriorClave').val(exterior);
            $('#inputMaxUsableClave').val(maxUsable);
            $('#inputPrecioClave').val(precio);

            // El selector de tipo se llena de forma asincrona;
            // esperar a que poblarSelectorTipos termine antes de asignar el valor
            cargarTiposModalClave();
            setTimeout(function () {
                $('#inputTipoClave').val(tipo);
            }, 150);

            $('#titleModalClave').text('Editar registro: ' + clave);
            $('#modalClave').modal('show');
        });
        // ---- Click ELIMINAR en la tabla ----
        $(document).on('click', '#clavesTable .delete-btn', function () {
            var id    = $(this).data('id');
            var clave = $(this).data('clave');
            var $fila = $('#tr_clave_' + id);

            // --- PRE-CHECK DE DEPENDENCIA ---
            $.ajax({
                url: '../ajax/post_clave.php',
                type: 'POST',
                data: { action: 'check_delete', id: id },
                dataType: 'json',
                success: function (chk) {
                    if (!chk.success) {
                        sweetAlertResponse('error', 'Fallo al verificar registro', chk.message, 'none');
                        return;
                    }

                    var count = parseInt(chk.orphan_count) || 0;
                    var htmlContent = 'Confirma eliminar la clave maestra <strong>' + _escaparTexto(clave) + '</strong>?<br><small>Esta acción no se puede deshacer.</small>';
                    
                    if (count > 0) {
                        htmlContent += '<div class="alert alert-warning text-start mt-3 mb-0" style="color: #856404; background-color: #fff3cd; border-color: #ffeeba;">' +
                                    '<i class="bi bi-exclamation-triangle-fill"></i> ¡Atención!<br>' +
                                    'Existen <strong>' + count + '</strong> barra(s) registradas en existencias (Inventario CNC) vinculadas a esta clave.<br><br>' +
                                    'Al continuar y eliminar la clave, los datos de las barras se mantendran igual e instantáneamente el estatus será: <strong>"Clave nueva pendiente"</strong>.' +
                                    '</div>';
                    }

                    Swal.fire({
                        title: 'Eliminar registro',
                        html:  htmlContent,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText:  'Cancelar',
                        confirmButtonColor: '#ca4747',
                        cancelButtonColor:  '#6c757d',
                        allowOutsideClick: false
                    }).then(function (result) {
                        if (!result.isConfirmed) { return; }

                        $.ajax({
                            url: '../ajax/post_clave.php',
                            type: 'POST',
                            data: { action: 'delete', id: id },
                            dataType: 'json',
                            success: function (resp) {
                                if (resp.success) {
                                    // Eliminar la fila del DOM con animacion
                                    $fila.find('td').addClass('bg-row-deleted');
                                    setTimeout(function () {
                                        $fila.fadeOut(400, function () {
                                            $(this).remove();

                                            // Actualizar contador si existe
                                            var $contador = $('#contadorResultados');
                                            if ($contador.length) {
                                                var actual = parseInt($contador.text()) || 1;
                                                $contador.text(Math.max(0, actual - 1));
                                            }

                                            // Si el tbody quedo vacio, mostrar mensaje
                                            if ($('#clavesTable tbody tr').length === 0) {
                                                $('#clavesTable tbody').append(
                                                    '<tr class="fila-vacia">' +
                                                        '<td colspan="10" class="text-center text-muted py-4">No quedan registros con los filtros actuales.</td>' +
                                                    '</tr>'
                                                );
                                            }
                                        });
                                    }, 300);

                                    sweetAlertResponse('success', 'Eliminado', resp.message, 'none');
                                } else {
                                    sweetAlertResponse('warning', 'Aviso', resp.message, 'none');
                                }
                            },
                            error: function (xhr, status, error) {
                                console.error("AJAX Error (delete):", status, error);
                                sweetAlertResponse('error', 'Error del servidor', 'No se pudo eliminar el registro.', 'none');
                            }
                        });
                    });
                },
                error: function() {
                    sweetAlertResponse('error', 'Error de red', 'No pudimos conectarnos para auditar dependencias en el servidor.', 'none');
                }
            });
        });
        // ---- Boton guardar del modal ----
        $('#btnGuardarClave').on('click', function () {
            // Verificar si ya se detecto duplicado antes de enviar
            if ($('#pClaveExiste').is(':visible')) {
                sweetAlertResponse('warning', 'Clave duplicada', 'Corrija la clave antes de guardar.', 'none');
                return;
            }
            ajaxGuardarClave();
        });

    });
</script>