<!-- Modal Constructor de Consulta Dinámica -->
<div class="modal fade" id="modalConsultar" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-consultar" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-funnel me-2"></i>Constructor de consultas</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-row gap-3">

                <!-- ═══ Formulario de filtros ═══ -->
                <form action="<?php
                        if($tipoUsuario == "CNC" || $tipoUsuario == "Vendedor"){
                            echo 'inventario_vn.php';
                        }else{
                            echo 'inventario.php';
                        }
                    ?>" method="GET" target="_blank" id="formConstructorConsulta"> 

                    <input type="hidden" name="cc" value="1">

                    <!-- STEP 1: Almacén (siempre primero) -->
                    <div class="mb-3">
                        <label for="ccAlmacen" class="form-label fw-bold">
                            <span class="cc-step-badge">1</span> Almacén <span class="text-danger">*</span>
                        </label>
                        <select id="ccAlmacen" class="inputAlmacenIdClass selector" name="origen" required>
                            <option value="" disabled selected>Seleccionar un almacén</option>
                        </select>
                    </div>

                    <!-- STEP 2: Material -->
                    <div class="mb-3">
                        <label for="ccMaterial" class="form-label fw-bold">
                            <span class="cc-step-badge">2</span> Material 
                        </label>
                        <select id="ccMaterial" class="selector" name="material" disabled>
                            <option value="" disabled selected>Primero seleccione un almacén</option>
                        </select>
                    </div>

                    <!-- STEP 3: Proveedor -->
                    <div class="mb-3">
                        <label for="ccProveedor" class="form-label fw-bold">
                            <span class="cc-step-badge">3</span> Proveedor
                        </label>
                        <select id="ccProveedor" class="selector" name="proveedor" disabled>
                            <option value="" disabled selected>Primero seleccione un material</option>
                        </select>
                    </div>

                    <!-- STEP 4: Estatus -->
                    <div class="mb-3">
                        <label for="ccEstatus" class="form-label fw-bold">
                            <span class="cc-step-badge">4</span> Estatus
                        </label>
                        <select id="ccEstatus" class="selector" name="estatus" disabled>
                            <option value="" disabled selected>Primero seleccione un proveedor</option>
                        </select>
                    </div>

                    <!-- STEP 5: Medida -->
                    <div class="mb-3">
                        <label for="ccMedida" class="form-label fw-bold">
                            <span class="cc-step-badge">5</span> Medida
                        </label>
                        <select id="ccMedida" class="selector" name="medida" disabled>
                            <option value="" disabled selected>Primero seleccione un estatus</option>
                        </select>
                    </div>

                    <!-- STEP 6: Omitir sin stock -->
                    <div class="mb-3" id="ccStockWrapper" style="display:none;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ccOmitirSinStock" name="omitir_sin_stock" value="1">
                            <label class="form-check-label fw-bold" for="ccOmitirSinStock">
                                <span class="cc-step-badge">6</span> Omitir barras sin stock
                                <span id="ccStockOmitidos" class="badge bg-secondary ms-2"></span>
                            </label>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- Ordenamiento -->
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="ccOrdenCampo" class="form-label fw-bold">
                                <i class="bi bi-sort-down me-1"></i> Ordenar por
                            </label>
                            <select id="ccOrdenCampo" class="selector" name="orden_campo">
                                <option value="created_at" selected>Fecha de registro</option>
                                <option value="updated_at">Fecha de actualización</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="ccOrdenDir" class="form-label fw-bold">Orden</label>
                            <select id="ccOrdenDir" class="selector" name="orden_dir">
                                <option value="DESC" selected>Descendente (recientes primero)</option>
                                <option value="ASC">Ascendente (antiguos primero)</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" id="btnConsultarConstructor" class="btn-general">
                        Consultar <i class="bi bi-arrow-up-right mx-2"></i>
                    </button>
                </form>

                <!-- ═══ Contador predictivo ═══ -->
                <div id="ccContadorWrapper" class="cc-contador-wrapper ">
                    <div class="cc-contador-inner">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-database" style="font-size:1.2rem;"></i>
                            <span class="cc-contador-label">Cantidad de registros:</span>
                            <span id="ccContadorNumero" class="cc-contador-numero">0</span>
                            <span id="ccContadorSpinner" class="spinner-border spinner-border-sm text-secondary d-none" role="status"></span>
                        </div>
                        <div id="ccContadorTiempo" class="cc-contador-tiempo"></div>
                    </div>
                </div>      

            </div>
        </div>
    </div>
</div>

<!-- ═══ JAVASCRIPT ═══ -->
<script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    const ccEndpoint = '../ajax/ajax_constructor_consulta.php';
    let ccCurrentCount = 0;

    // ============================================================
    //              ******** FUNCIONES ********
    // ============================================================

    /**
     * Obtiene los filtros acumulados según el step actual
     */
    function ccGetFilters(step) {
        const filters = {
            step: step,
            almacen_id: $('#ccAlmacen').val() || ''
        };
        if (step >= 2) filters.material    = $('#ccMaterial').val()   || '';
        if (step >= 3) filters.proveedor   = $('#ccProveedor').val()  || '';
        if (step >= 4) filters.estatus     = $('#ccEstatus').val()    || '';
        if (step >= 5) filters.medida      = $('#ccMedida').val()     || '';
        if (step >= 6) filters.omitir_sin_stock = $('#ccOmitirSinStock').is(':checked') ? '1' : '0';
        return filters;
    }

    /**
     * Realiza la consulta al endpoint y actualiza el contador + siguiente selector
     */
    function ccFetchStep(step, nextSelectorId, placeholderText) {
        const filters = ccGetFilters(step);
        
        // Mostrar spinner
        $('#ccContadorSpinner').removeClass('d-none');
        $('#ccContadorWrapper').removeClass('d-none');

        $.ajax({
            url: ccEndpoint,
            type: 'GET',
            dataType: 'json',
            data: filters,
            success: function(data) {
                $('#ccContadorSpinner').addClass('d-none');
                
                if (!data.success) {
                    console.error('Error en constructor:', data.message);
                    return;
                }

                // Actualizar contador
                ccCurrentCount = data.count;
                ccUpdateCounter(data.count);

                // Llenar siguiente selector si aplica
                if (nextSelectorId && data.distinct_next && data.distinct_next.length > 0) {
                    ccFillSelector(nextSelectorId, data.distinct_next, placeholderText);
                }

                // Step 5: manejar info de stock
                if (step >= 1 && data.count_sin_stock !== undefined) {
                    $('#ccStockWrapper').slideDown(200);
                    if (data.count_sin_stock > 0) {
                        $('#ccStockOmitidos').text('Se omitirían ' + data.count_sin_stock + ' registros sin stock').removeClass('d-none');
                    } else {
                        $('#ccStockOmitidos').text('Todos los registros tienen stock').removeClass('d-none');
                    }
                }

                // Habilitar botón de consultar si hay al menos almacen
                if (step >= 1) {
                    $('#btnConsultarConstructor').prop('disabled', false);
                }
            },
            error: function(xhr, status, err) {
                $('#ccContadorSpinner').addClass('d-none');
                console.error('Error AJAX constructor:', err);
            }
        });
    }

    /**
     * Llena un selector con las opciones recibidas
     */
    function ccFillSelector(selectorId, options, placeholderText) {
        const $sel = $(selectorId);
        let html = '<option value="" disabled selected>' + (placeholderText || 'Seleccionar...') + '</option>';
        html += '<option value="all">Todos</option>';
        
        options.forEach(function(val) {
            html += '<option value="' + val + '">' + val + '</option>';
        });
        
        $sel.html(html);
        $sel.prop('disabled', false);
    }

    /**
     * Actualiza el contador visual y el indicador de tiempo de carga
     */
    function ccUpdateCounter(count) {
        const $numero = $('#ccContadorNumero');
        const $tiempo = $('#ccContadorTiempo');
        const $wrapper = $('#ccContadorWrapper');
        
        // Animar el número
        $numero.text(count.toLocaleString('es-MX'));
        $numero.addClass('cc-pulse');
        setTimeout(() => $numero.removeClass('cc-pulse'), 350);

        // Limpiar clases de nivel
        $wrapper.removeClass('cc-nivel-rojo cc-nivel-naranja cc-nivel-amarillo cc-nivel-verde');

        // Determinar nivel según el conteo
        let texto = '';
        let color = '';
        let nivelClass = '';

        if (count > 2000) {
            texto = 'Tiempo de carga muy alto (se recomienda aplicar más filtros)';
            color = '#dc3545';
            nivelClass = 'cc-nivel-rojo';
        } else if (count >= 1001) {
            texto = 'Tiempo de carga alto (se recomienda aplicar más filtros)';
            color = '#dc3545';
            nivelClass = 'cc-nivel-rojo';
        } else if (count >= 500) {
            texto = 'Tiempo de carga medio (se recomienda aplicar más filtros)';
            color = '#fd7e14';
            nivelClass = 'cc-nivel-naranja';
        } else if (count >= 200) {
            texto = 'Tiempo de carga moderado';
            color = '#ffc107';
            nivelClass = 'cc-nivel-amarillo';
        } else if (count >= 80) {
            texto = 'Tiempo de carga rápido';
            color = '#28a745';
            nivelClass = 'cc-nivel-verde';
        } else if (count >= 1) {
            texto = 'Tiempo de carga muy rápido';
            color = '#28a745';
            nivelClass = 'cc-nivel-verde';
        } else {
            texto = 'No se encontraron registros con estos filtros';
            color = '#6c757d';
        }

        $tiempo.text(texto).css('color', color);
        $numero.css('color', color);
        $wrapper.addClass(nivelClass);
    }

    /**
     * Resetea los selectores a partir de un step dado (inclusive)
     */
    function ccResetFrom(fromStep) {
        const steps = [
            { step: 2, id: '#ccMaterial',   placeholder: 'Primero seleccione un almacén' },
            { step: 3, id: '#ccProveedor',  placeholder: 'Primero seleccione un material' },
            { step: 4, id: '#ccEstatus',    placeholder: 'Primero seleccione un proveedor' },
            { step: 5, id: '#ccMedida',     placeholder: 'Primero seleccione un estatus' }
        ];

        steps.forEach(function(s) {
            if (s.step >= fromStep) {
                $(s.id).html('<option value="" disabled selected>' + s.placeholder + '</option>');
                $(s.id).prop('disabled', true);
            }
        });

        $('#ccOmitirSinStock').prop('checked', false);
        if (fromStep <= 1) {
            $('#ccStockWrapper').slideUp(200);
            $('#ccStockOmitidos').text('').addClass('d-none');
        }

        // Si reseteamos desde step 1, ocultar contador y deshabilitar botón
        if (fromStep <= 1) {
            //$('#btnConsultarConstructor').prop('disabled', true);
        }
    }

    /**
     * Valida el submit del formulario con alertas según el conteo
     */
    function ccValidateSubmit() {
        return new Promise(function(resolve) {
            if (ccCurrentCount > 2000) {
                Swal.fire({
                    icon: 'warning',
                    title: '¿Está seguro?',
                    text: 'El tiempo de carga de datos es muy alto (' + ccCurrentCount.toLocaleString('es-MX') + ' registros), se recomienda aplicar más filtros.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, consultar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#55AD9B',
                    cancelButtonColor: '#6c757d'
                }).then(result => resolve(result.isConfirmed));
            } else if (ccCurrentCount >= 1001) {
                Swal.fire({
                    icon: 'warning',
                    title: '¿Está seguro?',
                    text: 'El tiempo de carga de datos es alto (' + ccCurrentCount.toLocaleString('es-MX') + ' registros), se recomienda aplicar más filtros.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, consultar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#55AD9B',
                    cancelButtonColor: '#6c757d'
                }).then(result => resolve(result.isConfirmed));
            } else if (ccCurrentCount >= 500) {
                Swal.fire({
                    icon: 'info',
                    title: '¿Está seguro?',
                    text: 'El tiempo de carga de datos puede tardar (' + ccCurrentCount.toLocaleString('es-MX') + ' registros), se recomienda aplicar más filtros.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, consultar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#55AD9B',
                    cancelButtonColor: '#6c757d'
                }).then(result => resolve(result.isConfirmed));
            } else {
                resolve(true);
            }
        });
    }

    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================
    $(document).ready(function() {

        // ── STEP 1: Almacén seleccionado ─────────────────────────
        $(document).on('change', '#ccAlmacen', function() {
            ccResetFrom(2);
            if ($(this).val()) {
                ccFetchStep(1, '#ccMaterial', 'Seleccionar material...');
            }
        });

        // ── STEP 2: Material seleccionado ────────────────────────
        $(document).on('change', '#ccMaterial', function() {
            ccResetFrom(3);
            if ($(this).val()) {
                ccFetchStep(2, '#ccProveedor', 'Seleccionar proveedor...');
            }
        });

        // ── STEP 3: Proveedor seleccionado ───────────────────────
        $(document).on('change', '#ccProveedor', function() {
            ccResetFrom(4);
            if ($(this).val()) {
                ccFetchStep(3, '#ccEstatus', 'Seleccionar estatus...');
            }
        });

        // ── STEP 4: Estatus seleccionado ─────────────────────────
        $(document).on('change', '#ccEstatus', function() {
            ccResetFrom(5);
            if ($(this).val()) {
                ccFetchStep(4, '#ccMedida', 'Seleccionar medida...');
            }
        });

        // ── STEP 5: Medida seleccionada ──────────────────────────
        $(document).on('change', '#ccMedida', function() {
            // Resetear solo el checkbox de stock
            $('#ccOmitirSinStock').prop('checked', false);
            if ($(this).val()) {
                ccFetchStep(5, null, null);
            }
        });

        // ── STEP 6: Checkbox de stock ────────────────────────────
        $(document).on('change', '#ccOmitirSinStock', function() {
            ccFetchStep(6, null, null);
        });

        // ── Submit con validación ────────────────────────────────
        $(document).on('submit', '#formConstructorConsulta', function(e) {
            e.preventDefault();
            const form = this;

            if(form.origen.value === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Faltan filtros obligatorios',
                    text: 'Por favor seleccione al menos un almacén para consultar.',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#55AD9B'
                });
                return;
            }

            ccValidateSubmit().then(function(proceed) {
                if (proceed) {
                    // Submit nativo del formulario
                    form.submit();
                }
            });
        });

        // ── Reset al cerrar modal ────────────────────────────────
        $('#modalConsultar').on('hidden.bs.modal', function() {
            ccResetFrom(1);
            $('#ccAlmacen').val('');
            ccCurrentCount = 0;
            ccUpdateCounter(ccCurrentCount);
        });
    });
</script>

<!-- ═══ ESTILOS ═══ -->
<style>
    /* ── Contador predictivo ─────────────────────────────────── */
    .cc-contador-wrapper {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
        border-radius: 12px;
        padding: 14px 18px;
        margin-bottom: 20px;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
        height: fit-content;
    }
    .cc-contador-wrapper::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 12px 12px 0 0;
        transition: background 0.4s ease;
    }
    .cc-contador-wrapper.cc-nivel-rojo::before    { background: linear-gradient(90deg, #dc3545, #e74c3c); }
    .cc-contador-wrapper.cc-nivel-naranja::before  { background: linear-gradient(90deg, #fd7e14, #e67e22); }
    .cc-contador-wrapper.cc-nivel-amarillo::before { background: linear-gradient(90deg, #ffc107, #f39c12); }
    .cc-contador-wrapper.cc-nivel-verde::before    { background: linear-gradient(90deg, #28a745, #2ecc71); }

    .cc-contador-inner {
        display: flex;
        flex-direction: column;
    }
    .cc-contador-label {
        font-weight: 600;
        color: #495057;
        font-size: 0.9rem;
    }
    .cc-contador-numero {
        font-size: 1.6rem;
        font-weight: 800;
        color: #212529;
        transition: color 0.4s ease;
        font-variant-numeric: tabular-nums;
    }
    .cc-contador-tiempo {
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 2px;
        transition: color 0.4s ease;
        min-height: 1.3em;
    }

    /* ── Step badges ─────────────────────────────────────────── */
    .cc-step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background-color: #55AD9B;
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        margin-right: 4px;
    }

    /* ── Animación para el número ─────────────────────────────── */
    @keyframes ccPulse {
        0%   { transform: scale(1); }
        50%  { transform: scale(1.08); }
        100% { transform: scale(1); }
    }
    .cc-contador-numero.cc-pulse {
        animation: ccPulse 0.35s ease;
    }

    /* ── Selector deshabilitado ───────────────────────────────── */
    #modalConsultar .selector:disabled {
        background-color: #e9ecef;
        color: #adb5bd;
        cursor: not-allowed;
        opacity: 0.7;
    }
</style>