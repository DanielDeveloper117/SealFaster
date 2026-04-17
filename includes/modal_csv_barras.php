<!-- modal_csv_barras.php -->
<div class="modal fade" id="modalCsvBarras" tabindex="-1" aria-hidden="true"
     aria-labelledby="titlemodalCsvBarras"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="titlemodalCsvBarras" class="modal-title">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Carga masiva de barras a partir del excel
                </h5>
                <button id="btnCerrarmodalCsvBarras" type="button"
                        class="btn-close" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">

                <div class="text-center mb-3">
                    <p class="mb-1 fw-semibold">Formato requerido del archivo CSV</p>
                    <img src="../assets/img/general/formato_csv_barras.jpg"
                         class="img-fluid rounded border mb-1" alt="Formato CSV esperado"
                         style="max-height:160px;">
                </div>

                <form id="formCsvClaves" autocomplete="off" novalidate>
                    <div class="mb-3">
                        <label for="inputAlmacenId3" class="form-label fw-bold">Almacén <span class="text-danger">*</span></label>
                        <select id="inputAlmacenId3" class="inputAlmacenIdClass selector" name="almacen_id" required>
                            <option value="" disabled selected>Seleccionar un almacén</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="inputCsvArchivo" class="lbl-general">Seleccionar archivo <span class="text-danger">*</span></label>
                        <input type="file" id="inputCsvArchivo" class="input-file" name="csv_precios" accept=".csv" required>
                        <p id="pCsvError" class="d-none p-invalida mt-1 text-danger"></p>
                    </div>

                    <div class="mb-3 p-3 rounded d-flex flex-column" style="background: #f8f9fa; border:1px solid #dee2e6;">
                        <span class="mb-2" style="font-size:1rem;"><i class="bi bi-info-circle me-1"></i>Consideraciones importantes:</span>                
                        <ul class="text-muted" style="font-size:0.8rem;">
                            <li>El proceso puede tardar dependiendo del volumen de datos.</li>
                            <li>Los registros que no cumplan con el formato requerido serán ignorados.</li>
                            <li>Si el dato lote ya existe en el inventario CNC, será ignorado. Debe ser único.</li>
                            <li>Las barras cuya clave no exista o no tengan precio serán ingresadas con estatus Clave nueva pendiente.</li>
                            <li>Las barras cuya clave exista y tenga precio serán ingresadas con estatus Disponible para cotizar.</li>
                        </ul>
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
    //    CARGA MASIVA DE BARRAS CSV → inventario_cnc
    //    Selectores scoped dentro de #modalCsvBarras para evitar
    //    conflictos de IDs con modal_csv_claves.php
    // ============================================================
    $(document).ready(function () {

        var $modalBarras = $('#modalCsvBarras');
        var _xhrBarras   = null;
        var _progressTimerBarras = null;

        // Helper: buscar elementos DENTRO del modal (evita colisión de IDs)
        function $b(sel) { return $modalBarras.find(sel); }

        // ============================================================
        //              ******** FUNCIONES ********
        // ============================================================

        function _actualizarBarraBarras(pct, text) {
            $b('#barraProgresoCsv').css('width', pct + '%').text(pct + '%');
            if (text) $b('#lblProcesando').text(text);
        }

        function _limpiarModalBarras() {
            if (_progressTimerBarras) { clearInterval(_progressTimerBarras); _progressTimerBarras = null; }
            if (_xhrBarras) { _xhrBarras = null; }
            $b('#formCsvClaves')[0].reset();
            $b('#inputAlmacenId3').val(null).trigger('change');
            $b('#containerInfoCsv').addClass('d-none');
            $b('#containerProgresoCsv').addClass('d-none');
            $b('#btnEnviarCsv').prop('disabled', false).show();
            $b('#btnCancelarCsv').prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i>Cancelar Proceso');
            _actualizarBarraBarras(0, '');
        }

        /**
         * Genera reporte Excel (.xlsx) con hojas condicionales
         * usando SheetJS (xlsx.full.min.js) ya incluido en el proyecto.
         */
        function _generarReporteExcel(data) {
            var wb = XLSX.utils.book_new();
            var headers = ['Clave', 'Lote', 'Proveedor', 'Material', 'Medida', 'Estatus', 'Comentario'];

            function crearHoja(items) {
                var wsData = [headers];
                for (var i = 0; i < items.length; i++) {
                    wsData.push([
                        items[i].clave      || '',
                        items[i].lote       || '',
                        items[i].proveedor  || '',
                        items[i].material   || '',
                        items[i].medida     || '',
                        items[i].estatus    || '',
                        items[i].comentario || ''
                    ]);
                }
                var ws = XLSX.utils.aoa_to_sheet(wsData);
                // Auto-ancho de columnas
                ws['!cols'] = headers.map(function(h, idx) {
                    var maxLen = h.length;
                    for (var j = 0; j < items.length; j++) {
                        var val = (wsData[j + 1][idx] || '').toString();
                        if (val.length > maxLen) maxLen = val.length;
                    }
                    return { wch: Math.min(maxLen + 3, 55) };
                });
                return ws;
            }

            var hasSheets = false;

            if (data.ISSUES_COLLECTOR && data.ISSUES_COLLECTOR.length > 0) {
                XLSX.utils.book_append_sheet(wb, crearHoja(data.ISSUES_COLLECTOR), 'Discrepancias');
                hasSheets = true;
            }
            if (data.DUPLICATE_COLLECTOR && data.DUPLICATE_COLLECTOR.length > 0) {
                XLSX.utils.book_append_sheet(wb, crearHoja(data.DUPLICATE_COLLECTOR), 'Duplicados');
                hasSheets = true;
            }
            if (data.NEW_CLAVE_COLLECTOR && data.NEW_CLAVE_COLLECTOR.length > 0) {
                XLSX.utils.book_append_sheet(wb, crearHoja(data.NEW_CLAVE_COLLECTOR), 'Claves pendientes');
                hasSheets = true;
            }
            if (data.SUCCESS_COLLECTOR && data.SUCCESS_COLLECTOR.length > 0) {
                XLSX.utils.book_append_sheet(wb, crearHoja(data.SUCCESS_COLLECTOR), 'Registro exitoso');
                hasSheets = true;
            }
            if (data.ERROR_COLLECTOR && data.ERROR_COLLECTOR.length > 0) {
                XLSX.utils.book_append_sheet(wb, crearHoja(data.ERROR_COLLECTOR), 'Errores');
                hasSheets = true;
            }

            if (!hasSheets) {
                XLSX.utils.book_append_sheet(wb, XLSX.utils.aoa_to_sheet([['Sin datos para reportar']]), 'Sin datos');
            }

            const now = new Date();

            const fecha =
            now.getFullYear().toString() +
            String(now.getMonth() + 1).padStart(2, '0') +
            String(now.getDate()).padStart(2, '0') +
            String(now.getHours()).padStart(2, '0') +
            String(now.getMinutes()).padStart(2, '0') +
            String(now.getSeconds()).padStart(2, '0');
            XLSX.writeFile(wb, 'reporte_carga_barras_' + fecha + '.xlsx');
        }

        /**
         * Muestra modal de resultados con SweetAlert2.
         * Resumen de los 5 recolectores + botón descargar Excel.
         */
        function _mostrarResultadosBarras(data) {
            var issues     = (data.ISSUES_COLLECTOR     || []).length;
            var duplicates = (data.DUPLICATE_COLLECTOR   || []).length;
            var newClaves  = (data.NEW_CLAVE_COLLECTOR   || []).length;
            var success    = (data.SUCCESS_COLLECTOR     || []).length;
            var errors     = (data.ERROR_COLLECTOR       || []).length;
            var total      = issues + duplicates + newClaves + success + errors;

            var html = '<div class="text-start" style="font-size:0.93rem;">';
            html += '<p class="text-muted mb-3" style="font-size:0.84rem;">Total de registros procesados: <strong>' + total + '</strong></p>';

            // Exitosos
            html += '<div class="mb-2 p-2 rounded d-flex align-items-center" style="background:#e8f5e9;border:1px solid #a5d6a7;">';
            html += '<i class="bi bi-check-circle-fill text-success me-2" style="font-size:1.15rem;"></i>';
            html += '<div><strong>Registros exitosos:</strong> ' + success + '</div></div>';

            // Claves pendientes
            html += '<div class="mb-2 p-2 rounded d-flex align-items-center" style="background:#fff8e1;border:1px solid #ffe082;">';
            html += '<i class="bi bi-exclamation-triangle-fill text-warning me-2" style="font-size:1.15rem;"></i>';
            html += '<div><strong>Claves nuevas pendientes:</strong> ' + newClaves + '</div></div>';

            // Discrepancias
            html += '<div class="mb-2 p-2 rounded d-flex align-items-center" style="background:#fce4ec;border:1px solid #ef9a9a;">';
            html += '<i class="bi bi-x-octagon-fill text-danger me-2" style="font-size:1.15rem;"></i>';
            html += '<div><strong>Barras con discrepancias:</strong> ' + issues + '</div></div>';

            // Duplicados
            html += '<div class="mb-2 p-2 rounded d-flex align-items-center" style="background:#e3f2fd;border:1px solid #90caf9;">';
            html += '<i class="bi bi-files text-primary me-2" style="font-size:1.15rem;"></i>';
            html += '<div><strong>Duplicados ignorados:</strong> ' + duplicates + '</div></div>';

            // Errores internos (solo si existen)
            if (errors > 0) {
                html += '<div class="mb-2 p-2 rounded d-flex align-items-center" style="background:#fbe9e7;border:1px solid #ffab91;">';
                html += '<i class="bi bi-bug-fill text-danger me-2" style="font-size:1.15rem;"></i>';
                html += '<div><strong>Errores internos:</strong> ' + errors + '</div></div>';
            }

            html += '</div>';

            // Guardar datos para posible re-descarga
            window._lastBarrasResultData = data;

            Swal.fire({
                icon: 'success',
                title: 'Resultados de la Carga Masiva',
                html: html,
                width: '520px',
                showConfirmButton: true,
                confirmButtonText: '<i class="bi bi-file-earmark-excel me-1"></i> Descargar Reporte',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonText: 'Cerrar',
                confirmButtonColor: '#198754',
            }).then(function(result) {
                if (result.isConfirmed) {
                    _generarReporteExcel(data);
                }
            });
        }

        // ============================================================
        //          ******** EVENTOS DEL DOM ********
        // ============================================================

        // Mostrar nombre del archivo seleccionado
        $b('#inputCsvArchivo').on('change', function () {
            var file = this.files[0];
            $b('#pCsvError').addClass('d-none').text('');
            $b('#containerInfoCsv').addClass('d-none');
            if (!file) return;
            $b('#spanNombreCsv').text(file.name);
            $b('#containerInfoCsv').removeClass('d-none');
        });

        // Cerrar modal (con confirmación si hay proceso activo)
        $b('#btnCerrarmodalCsvBarras').on('click', function () {
            if ($b('#containerProgresoCsv').hasClass('d-none')) {
                $modalBarras.modal('hide');
            } else {
                Swal.fire({
                    title: '¿Cancelar proceso en curso?',
                    text: 'Se cancelará la carga del archivo.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'Continuar'
                }).then(function(r) {
                    if (r.isConfirmed) {
                        if (_xhrBarras) _xhrBarras.abort();
                        _limpiarModalBarras();
                        $modalBarras.modal('hide');
                    }
                });
            }
        });

        // Cancelar proceso (abortar XHR)
        $b('#btnCancelarCsv').on('click', function () {
            if (_xhrBarras) _xhrBarras.abort();
            $(this).prop('disabled', true).text('Cancelando...');
            setTimeout(function() {
                _limpiarModalBarras();
                Swal.fire({
                    icon: 'warning',
                    title: 'Proceso Cancelado',
                    text: 'La carga del archivo fue cancelada por el usuario.',
                    confirmButtonText: 'Entendido'
                });
            }, 300);
        });

        // ── ENVIAR CSV ───────────────────────────────────────────
        $b('#btnEnviarCsv').on('click', function () {
            var $pError = $b('#pCsvError');
            $pError.addClass('d-none').text('');

            // Validar almacén obligatorio
            var almacen_id = $b('#inputAlmacenId3').val();
            if (!almacen_id || almacen_id === '') {
                $pError.removeClass('d-none').text('Debe seleccionar un almacén.');
                return;
            }

            // Validar archivo obligatorio
            var input = $b('#inputCsvArchivo')[0];
            if (!input.files || input.files.length === 0) {
                $pError.removeClass('d-none').text('Seleccione un archivo CSV.');
                return;
            }

            var file = input.files[0];

            // UI: deshabilitar botón, mostrar progreso
            $b('#btnEnviarCsv').prop('disabled', true).hide();
            $b('#containerProgresoCsv').removeClass('d-none');
            $b('#btnCancelarCsv').prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i>Cancelar Proceso');
            _actualizarBarraBarras(5, 'Preparando archivo...');

            var fd = new FormData();
            fd.append('csv_barras', file);
            fd.append('almacen_id', almacen_id);

            _xhrBarras = $.ajax({
                url: '../ajax/post_csv_barras.php',
                type: 'POST',
                data: fd,
                processData: false,
                contentType: false,
                dataType: 'json',
                timeout: 120000, // 2 min timeout
                xhr: function () {
                    var xhr = new window.XMLHttpRequest();
                    // Progreso real de subida: 5% → 45%
                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            var pct = Math.round((e.loaded / e.total) * 40) + 5;
                            _actualizarBarraBarras(pct, 'Subiendo archivo...');
                        }
                    });
                    // Subida completa → iniciar simulación de procesamiento
                    xhr.upload.addEventListener('load', function () {
                        _actualizarBarraBarras(50, 'Procesando datos en el servidor...');
                        var simPct = 50;
                        _progressTimerBarras = setInterval(function () {
                            if (simPct < 90) {
                                simPct++;
                                _actualizarBarraBarras(simPct, 'Procesando datos en el servidor...');
                            }
                        }, 600);
                    });
                    return xhr;
                },
                success: function (resp) {
                    if (_progressTimerBarras) { clearInterval(_progressTimerBarras); _progressTimerBarras = null; }
                    _actualizarBarraBarras(100, '¡Procesamiento completado!');

                    if (resp.success) {
                        setTimeout(function () {
                            $modalBarras.modal('hide');
                            _limpiarModalBarras();
                            _mostrarResultadosBarras(resp);
                        }, 600);
                    } else {
                        _limpiarModalBarras();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en el procesamiento',
                            html: '<p>' + (resp.message || 'Error desconocido.') + '</p>',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                },
                error: function (xhr, status) {
                    if (_progressTimerBarras) { clearInterval(_progressTimerBarras); _progressTimerBarras = null; }
                    _limpiarModalBarras();
                    if (status === 'abort') return; // Cancelado por el usuario
                    Swal.fire({
                        icon: 'error',
                        title: 'Error del servidor',
                        html: '<p>Error al procesar el archivo CSV. Intente nuevamente.</p>',
                        confirmButtonText: 'Cerrar'
                    });
                }
            });
        });

        // Limpiar al cerrar el modal
        $modalBarras.on('hidden.bs.modal', function () {
            _limpiarModalBarras();
        });

    });
</script>