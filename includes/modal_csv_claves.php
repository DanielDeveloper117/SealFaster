<!-- modal_csv_claves.php -->
<div class="modal fade" id="modalCsvClaves" tabindex="-1" aria-hidden="true"
     aria-labelledby="titleModalCsvClaves"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="titleModalCsvClaves" class="modal-title">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Carga masiva de claves CSV
                </h5>
                <button id="btnCerrarModalCsvClaves" type="button"
                        class="btn-close" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">

                <div class="text-center mb-3">
                    <p class="mb-1 fw-semibold">Formato requerido del archivo CSV</p>
                    <img src="../assets/img/general/formato_csv2.jpg"
                         class="img-fluid rounded border" alt="Formato CSV esperado"
                         style="max-height:160px;">
                </div>

                <form id="formCsvClaves" autocomplete="off" novalidate>
                    <div class="mb-3">
                        <label for="inputCsvArchivo" class="lbl-general">Seleccionar archivo <span class="text-danger">*</span></label>
                        <input type="file" id="inputCsvArchivo" class="input-file" name="csv_precios" accept=".csv" required>
                        <p id="pCsvError" class="d-none p-invalida mt-1 text-danger"></p>
                    </div>

                    <div class="mb-3 p-3 rounded" style="background:#f8f9fa; border:1px solid #dee2e6;">
                        <label class="lbl-general mb-2 d-block border-bottom pb-1">Seleccione cómo actualizar el Inventario (inventario_cnc)</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="sync_mode" id="syncClave" value="sync_clave" checked>
                            <label class="form-check-label" for="syncClave">
                                <strong>Mediante clave principal</strong><br>
                                <span class="text-muted" style="font-size:0.8rem;">Actualiza registros en inventario usando únicamente la columna 'clave'.</span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="sync_mode" id="syncAlterna" value="sync_alterna">
                            <label class="form-check-label" for="syncAlterna">
                                <strong>Mediante clave alterna</strong><br>
                                <span class="text-muted" style="font-size:0.8rem;">Actualiza registros en inventario usando únicamente la columna 'clave_alterna'.</span>
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="sync_mode" id="syncAmbas" value="sync_ambas">
                            <label class="form-check-label" for="syncAmbas">
                                <strong>Mediante ambas</strong><br>
                                <span class="text-muted" style="font-size:0.8rem;">Escanea el inventario buscando coincidencias tanto en clave principal como en clave alterna.</span>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sync_mode" id="syncNada" value="sync_nada">
                            <label class="form-check-label" for="syncNada">
                                <strong>No actualizar el Inventario CNC</strong><br>
                                <span class="text-muted" style="font-size:0.8rem;">Omitirá la actualización del inventario CNC por completo. El proceso solo actualizará la tabla de claves.</span>
                            </label>
                        </div>
                    </div>

                    <div id="containerInfoCsv" class="d-none mb-3 p-2 rounded" style="background:#f0faf8; border:1px solid #a5d6a7; font-size:0.87rem;">
                        <i class="bi bi-file-earmark-text me-1 text-success"></i> <span id="spanNombreCsv"></span>
                    </div>

                    <div id="containerProgresoCsv" class="d-none mb-3">
                        <label class="lbl-general mb-1" id="lblProcesando">Subiendo y procesando por lotes...</label>
                        <div class="progress mb-2" style="height:20px; border-radius:8px;">
                            <div id="barraProgresoCsv" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%; background-color:#55AD9B;">0%</div>
                        </div>
                        <button id="btnCancelarCsv" type="button" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-x-circle me-1"></i>Cancelar Proceso</button>
                    </div>

                    <button id="btnEnviarCsv" type="button" class="btn-general">
                        <i class="bi bi-upload me-1"></i> Iniciar Procesamiento
                    </button>

                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    var isCancelled = false;


    // ============================================================
    //              ******** FUNCIONES ********
    // ============================================================  
    function _procesarChunks(upload_id, total_lines, sync_mode) {
        var chunk_size = 500;
        var start_line = 1; // 1 = linea 2 del doc (sin header)
        var totalInsertados = 0;
        var totalActualizados = 0;
        var clavesRecopiladas = [];

        function processNext() {
            if (isCancelled) {
                $.post('../ajax/post_csv_claves.php', {action: 'cancel', upload_id: upload_id});
                _mostrarError('El proceso fue cancelado por el usuario. Los registros procesados hasta ahora se guardaron, pero no se sincronizó el inventario.', 'Proceso Cancelado', 'warning');
                return;
            }

            if (start_line >= total_lines || total_lines === 0) {
                _finalizar(upload_id, sync_mode, clavesRecopiladas, totalInsertados, totalActualizados);
                return;
            }

            var pct = Math.round((start_line / total_lines) * 85); // 85% visual por procesado iterativo
            _actualizarBarra(pct, 'Procesando lote ' + (Math.round(start_line/chunk_size)+1) + '...');

            $.ajax({
                url: '../ajax/post_csv_claves.php',
                type: 'POST',
                data: {action: 'process_chunk', upload_id: upload_id, start_line: start_line, chunk_size: chunk_size},
                dataType: 'json',
                success: function(r) {
                    if (r.success) {
                        totalInsertados += r.insertados;
                        totalActualizados += r.actualizados;
                        if (r.claves_procesadas && Array.isArray(r.claves_procesadas)) {
                            clavesRecopiladas = clavesRecopiladas.concat(r.claves_procesadas);
                        }
                        start_line += chunk_size;
                        processNext();
                    } else {
                        isCancelled = true;
                        $.post('../ajax/post_csv_claves.php', {action: 'cancel', upload_id: upload_id});
                        _mostrarErrorConDetalles('Se detectaron errores de validación. El proceso fue detenido.', r);
                    }
                },
                error: function(xhr) {
                    isCancelled = true;
                    $.post('../ajax/post_csv_claves.php', {action: 'cancel', upload_id: upload_id});
                    _mostrarError('Error al procesar parte del archivo. Detenido.');
                }
            });
        }

        processNext();
    }

    function _finalizar(upload_id, sync_mode, clavesRecopiladas, ins, upd) {
        _actualizarBarra(90, 'Sincronizando inventario (finalizando)...');

        $.ajax({
            url: '../ajax/post_csv_claves.php',
            type: 'POST',
            data: {
                action: 'finish', 
                upload_id: upload_id, 
                sync_mode: sync_mode,
                claves_procesadas: JSON.stringify(clavesRecopiladas)
            },
            dataType: 'json',
            success: function(r) {
                if (r.success) {
                    _actualizarBarra(100, '¡Completado!');
                    $('#modalCsvClaves').modal('hide');
                    _limpiarModalCsv();
                    var resTxt = `Nuevas claves detectadas: ${ins} <br>Total de claves detectadas: ${upd} <br>Barras de inventario CNC actualizadas: ${r.inventario_actualizados}`;
                    Swal.fire({icon: 'success', title: 'Carga completada', html: resTxt, confirmButtonText: 'Entendido'});
                } else {
                    _mostrarError(r.message);
                }
            },
            error: function() { _mostrarError('Error al finalizar sincronización.'); }
        });
    }

    function _actualizarBarra(pct, text) {
        $('#barraProgresoCsv').css('width', pct + '%').text(pct + '%');
        if(text) $('#lblProcesando').text(text);
    }

    function _mostrarError(msg, title='Error en el CSV', icon='error') {
        _limpiarModalCsv();
        Swal.fire({icon: icon, title: title, html: '<p>' + msg + '</p>', confirmButtonText: 'Cerrar'});
    }

    function _mostrarErrorConDetalles(msg, r) {
        _limpiarModalCsv();
        var htmlError = '<p>' + msg + '</p>';
        if (r.error_detail) htmlError += '<p class="text-danger" style="font-size:0.8rem;">' + r.error_detail + '</p>';
        if (r.errores && r.errores.length) {
            htmlError += '<ul class="text-start" style="max-height:200px;overflow-y:auto;font-size:0.8rem;">';
            $.each(r.errores, function(i, e) { htmlError += '<li>' + e + '</li>'; });
            htmlError += '</ul>';
        }
        Swal.fire({icon: 'error', title: 'Error de Validación', html: htmlError, confirmButtonText: 'Entendido'});
    }

    function _limpiarModalCsv() {
        $('#formCsvClaves')[0].reset();
        $('#containerInfoCsv, #containerProgresoCsv').addClass('d-none');
        $('#btnEnviarCsv').prop('disabled', false).show();
        _actualizarBarra(0, '');
    }

    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================  
    $(document).ready(function () {
        
        $('#inputCsvArchivo').on('change', function () {
            var file = this.files[0];
            $('#pCsvError').addClass('d-none').text('');
            $('#containerInfoCsv').addClass('d-none');
            if (!file) return;
            $('#spanNombreCsv').text(file.name);
            $('#containerInfoCsv').removeClass('d-none');
        });

        $('#btnCerrarModalCsvClaves').on('click', function () {
            if ($('#containerProgresoCsv').hasClass('d-none')) {
                $('#modalCsvClaves').modal('hide');
            } else {
                Swal.fire({
                    title: '¿Cancelar proceso en curso?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar'
                }).then((r) => { if (r.isConfirmed) $('#btnCancelarCsv').click(); });
            }
        });

        $('#btnCancelarCsv').on('click', function() {
            isCancelled = true;
            $(this).prop('disabled', true).text('Cancelando...');
        });

        $('#btnEnviarCsv').on('click', function () {
            var input = $('#inputCsvArchivo')[0];
            $('#pCsvError').addClass('d-none').text('');

            if (!input.files || input.files.length === 0) {
                $('#pCsvError').removeClass('d-none').text('Seleccione un archivo CSV.');
                return;
            }

            var file = input.files[0];
            var sync_mode = $('input[name="sync_mode"]:checked').val();
            
            isCancelled = false;
            $('#btnEnviarCsv').prop('disabled', true).hide();
            $('#containerProgresoCsv').removeClass('d-none');
            $('#btnCancelarCsv').prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i>Cancelar Proceso');
            _actualizarBarra(0, 'Subiendo archivo inicial...');

            var fd = new FormData();
            fd.append('action', 'upload');
            fd.append('csv_precios', file);

            $.ajax({
                url: '../ajax/post_csv_claves.php',
                type: 'POST',
                data: fd,
                processData: false, contentType: false, dataType: 'json',
                success: function(resp) {
                    if (resp.success) {
                        _procesarChunks(resp.upload_id, resp.total_lines, sync_mode);
                    } else {
                        _mostrarError(resp.message);
                    }
                },
                error: function(xhr) { _mostrarError('Error de servidor al subir archivo.'); }
            });
        });

    });
</script>