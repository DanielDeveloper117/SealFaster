<!-- ============================================================
     modal_detalle_perfil.php
     Muestra la informacion completa del perfil en modo lectura.
     ============================================================ -->
<div class="modal fade" id="modalDetallePerfil" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="titleModalDetallePerfil" class="modal-title">Detalle del perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Imagen principal del perfil -->
                <div class="d-flex justify-content-center mb-4">
                    <div style="width:180px; height:180px; border:1px solid #e0e0e0; border-radius:10px; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#f5f5f5;">
                        <img id="imgDetallePerfil" src="" alt="" style="max-width:100%; max-height:100%; object-fit:contain;"
                             onerror="this.src='../assets/img/general/blanco.jpg'">
                    </div>
                </div>

                <!-- Datos verticales -->
                <div class="d-flex flex-column gap-3">

                    <div class="mdt-field">
                        <span class="mdt-field-label">Nombre</span>
                        <p id="detalle-nombre" class="mdt-field-value">—</p>
                    </div>

                    <div class="mdt-field">
                        <span class="mdt-field-label">Familia</span>
                        <p id="detalle-familia" class="mdt-field-value">—</p>
                    </div>

                    <div class="mdt-field">
                        <span class="mdt-field-label">Detalles / Notas</span>
                        <p id="detalle-detalles" class="mdt-field-value">—</p>
                    </div>

                    <div class="mdt-field">
                        <span class="mdt-field-label">Cantidad de componentes</span>
                        <p id="detalle-componentes" class="mdt-field-value">—</p>
                    </div>

                    <!-- Flags -->
                    <div>
                        <span class="mdt-field-label d-block mb-2">Flags de componentes</span>
                        <div class="d-flex flex-column gap-2">
                            <div id="flag-resorte"></div>
                            <div id="flag-wiper"></div>
                            <div id="flag-escalon"></div>
                            <div id="flag-wiper-especial"></div>
                        </div>
                    </div>

                    <div class="mdt-field">
                        <span class="mdt-field-label">Grupo de herramienta</span>
                        <p id="detalle-grupo" class="mdt-field-value">—</p>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <span class="mdt-field-label">Última actualización</span>
                            <p id="detalle-updated" class="mdt-field-value">—</p>
                        </div>
                        <div class="col-6">
                            <span class="mdt-field-label">Fecha de creación</span>
                            <p id="detalle-created" class="mdt-field-value">—</p>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel w-auto px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     modal_editar_perfil.php
     Formulario de edicion parcial del perfil.
     ============================================================ -->
<div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="titleModalEditarPerfil" class="modal-title">Editar perfil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="inputIdEditarPerfil">

                <!-- Detalles / notas -->
                <div class="mb-3">
                    <label for="inputDetallesEditarPerfil" class="lbl-general">Detalles / Notas</label>
                    <textarea id="inputDetallesEditarPerfil" class="form-control" rows="3" maxlength="600"
                              placeholder="Descripcion adicional del perfil (max 600 caracteres)..."></textarea>
                    <small class="text-muted">Máximo 600 caracteres.</small>
                </div>

                <!-- Selector de grupo de herramienta -->
                <div class="mb-3">
                    <label for="selectGrupoEditarPerfil" class="lbl-general">Grupo de herramienta</label>
                    <select id="selectGrupoEditarPerfil" class="form-select">
                        <option value="">Seleccione un grupo de herramientas...</option>
                    </select>
                </div>

                <!-- Seccion de flags — solo visible para Administrador/Sistemas -->
                <div id="seccionFlagsEditar" class="d-none">
                    <hr>
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-flag-fill text-warning"></i>
                        <strong>Flags de componentes</strong>
                        <span class="badge bg-warning text-dark ms-1" style="font-size:0.65rem;">Solo Administrador</span>
                    </div>
                    <div id="containerFlagsEditar" class="d-flex flex-column gap-2">
                        <!-- Generado dinamicamente por JS -->
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <button type="button" class="btn-general" id="btnGuardarEditarPerfil">
                        <i class="bi bi-floppy me-1"></i> Guardar cambios
                    </button>
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     modal_params_perfil.php
     Gestion de porcentajes y tolerancias del perfil.
     Tabs: Porcentajes | Tolerancias.
     Edicion inline, sin agregar ni eliminar registros.
     ============================================================ -->
<div class="modal fade" id="modalParamsPerfil" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 id="titleModalParams" class="modal-title mb-0">Parámetros del perfil</h5>
                    <small id="subtitleModalParams" class="text-muted">—</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding:1.25rem;">

                <!-- TABS -->
                <ul class="nav mdt-tabs mb-0" id="tabsParams" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="mdt-tab-btn active" id="tab-porcentajes"
                                data-bs-toggle="tab" data-bs-target="#content-porcentajes"
                                type="button" role="tab">
                            <i class="bi bi-percent me-1"></i> Porcentajes
                            <span class="mdt-tab-badge" id="badge-porcentajes">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="mdt-tab-btn" id="tab-tolerancias"
                                data-bs-toggle="tab" data-bs-target="#content-tolerancias"
                                type="button" role="tab">
                            <i class="bi bi-arrows-collapse me-1"></i> Tolerancias
                            <span class="mdt-tab-badge" id="badge-tolerancias">0</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content mdt-tab-content" id="contentParams">

                    <!-- ---- TAB PORCENTAJES ---- -->
                    <div class="tab-pane fade show active" id="content-porcentajes" role="tabpanel">
                        <div class="alert alert-info d-flex gap-2 align-items-start mb-3" style="font-size:13px;">
                            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                            <span>Los porcentajes representan la fracción que se aplica a cada componente para calcular las dimensiones teóricas y mejorar la seleccion de barras.
                                Al DI se le suma el % en milimetros y al DE se le resta el % en milimetros.
                            El porcentaje H indica la fraccion de la altura total del sello (Si no se omiten los componentes al cotizar). 
                            <strong>Ninguno puede superar 1.000 y H no puede ser 0.</strong></span>
                        </div>
                        <div class="mdt-table-wrapper mdt-section">
                            <table class="table-striped table-bordered  mdt-table">
                                <thead>
                                    <tr>
                                        <th class="column-image">Imagen</th>
                                        <th>Acciones</th>
                                        <th># Componente</th>
                                        <th>% DI</th>
                                        <th>% DE</th>
                                        <th>% H</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-porcentajes">
                                    <tr><td colspan="6" class="text-center text-muted py-3">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ---- TAB TOLERANCIAS ---- -->
                    <div class="tab-pane fade" id="content-tolerancias" role="tabpanel">
                        <div class="alert alert-info d-flex gap-2 align-items-start mb-3" style="font-size:13px;">
                            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                            <span>Indica cuántos mm se restan/agregan al diámetro teórico para la tolerancia de barra.
                            <strong>Deben ser mayores o iguales a 0. Si no existe registro se usa el valor por defecto 4.00 mm.</strong></span>
                        </div>
                        <div class="mdt-table-wrapper mdt-section">
                            <table class=" table-striped table-bordered  mdt-table">
                                <thead>
                                    <tr>
                                        <th class="column-image">Imagen</th>
                                        <th>Acciones</th>
                                        <th># Componente</th>
                                        <th>Tolerancia DI (-N mm)</th>
                                        <th>Tolerancia DE (+N mm)</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-tolerancias">
                                    <tr><td colspan="5" class="text-center text-muted py-3">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel w-auto px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>

<script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    // ============================================================
    //              ******** FUNCIONES ********
    // ============================================================
    // CARGAR PORCENTAJES DEL PERFIL
    function cargarPorcentajes() {
        var tbody  = "#tbody-porcentajes";
        var badge  = "#badge-porcentajes";
        $(tbody).html('<tr><td colspan="6" class="text-center py-3"><div class="spinner-border spinner-border-sm text-success"></div> Cargando...</td></tr>');

        $.ajax({
            url: "../ajax/get_params_perfil.php",
            type: "GET",
            data: { perfil_id: window.paramPerfilId, tipo: "porcentajes" },
            dataType: "json",
            success: function (data) {
                $(badge).text(data.length);
                window.cachePct = data;
                if (data.length === 0) {
                    $(tbody).html('<tr><td colspan="6" class="text-center text-muted py-3"><i class="bi bi-inbox me-2"></i>Sin porcentajes registrados.</td></tr>');
                    return;
                }
                var html = "";
                $.each(data, function (i, row) {
                    html += renderFilaPorcentaje(data.length, row);
                });
                $(tbody).html(html);
            },
            error: function () { $(tbody).html('<tr><td colspan="6" class="text-danger text-center">Error al cargar.</td></tr>'); }
        });
    }

    function renderFilaPorcentaje(n_componentes, row) {
        var componenteImg = row.componente;
        if(n_componentes == 1) {
            componenteImg = 0;
        }
        var rutaImg = "../assets/img/" + window.paramPerfilFamilia + "/" + window.paramPerfilNombre + "/" + window.paramPerfilNombre + "_" + componenteImg + ".jpg";
        return '<tr id="fila-pct-' + row.id + '">' +
            '<td class="column-image" ><img src="' + rutaImg + '" alt="comp ' + row.componente + '"  onerror="this.src=\'../assets/img/general/blanco.jpg\'"></td>' +
            '<td><div class="d-flex gap-1 container-actions">' +
                '<button class="btn-general btn-param-editar" data-id="' + row.id + '" data-tipo="pct" data-componentes="' + n_componentes + '" title="Editar"><i class="bi bi-pencil-square"></i></button>' +
            '</div></td>' +
            '<td class="text-center fw-bold">' + row.componente + '</td>' +
            '<td class="text-center">' + parseFloat(row.di).toFixed(3) + '</td>' +
            '<td class="text-center">' + parseFloat(row.de).toFixed(3) + '</td>' +
            '<td class="text-center">' + parseFloat(row.h).toFixed(3) + '</td>' +
        '</tr>';
    }
    // CARGAR TOLERANCIAS DEL PERFIL
    function cargarTolerancias() {
        var tbody = "#tbody-tolerancias";
        var badge = "#badge-tolerancias";
        $(tbody).html('<tr><td colspan="5" class="text-center py-3"><div class="spinner-border spinner-border-sm text-success"></div> Cargando...</td></tr>');

        $.ajax({
            url: "../ajax/get_params_perfil.php",
            type: "GET",
            data: { perfil_id: window.paramPerfilId, tipo: "tolerancias" },
            dataType: "json",
            success: function (data) {
                $(badge).text(data.length);
                window.cacheTol = data;
                if (data.length === 0) {
                    $(tbody).html('<tr><td colspan="5" class="text-center text-muted py-3"><i class="bi bi-inbox me-2"></i>Sin tolerancias registradas.</td></tr>');
                    return;
                }
                var html = "";
                $.each(data, function (i, row) {
                    html += renderFilaTolerancias(data.length, row);
                });
                $(tbody).html(html);
            },
            error: function () { $(tbody).html('<tr><td colspan="5" class="text-danger text-center">Error al cargar.</td></tr>'); }
        });
    }

    function renderFilaTolerancias(n_componentes, row) {
        var componenteImg = row.componente;
        if(n_componentes == 1) {
            componenteImg = 0;
        }
        var rutaImg = "../assets/img/" + window.paramPerfilFamilia + "/" + window.paramPerfilNombre + "/" + window.paramPerfilNombre + "_" + componenteImg + ".jpg";
        return '<tr id="fila-tol-' + row.id + '">' +
            '<td class="column-image" ><img src="' + rutaImg + '" alt="comp ' + row.componente + '"  onerror="this.src=\'../assets/img/general/blanco.jpg\'"></td>' +
            '<td><div class="d-flex gap-1 container-actions">' +
                '<button class="btn-general btn-param-editar" data-id="' + row.id + '" data-tipo="tol" title="Editar"><i class="bi bi-pencil-square"></i></button>' +
            '</div></td>' +
            '<td class="text-center fw-bold">' + row.componente + '</td>' +
            '<td class="text-center">' + parseFloat(row.di).toFixed(2) + '</td>' +
            '<td class="text-center">' + parseFloat(row.de).toFixed(2) + '</td>' +
        '</tr>';
    }
    // EDICION INLINE PARAMETROS
    function activarEdicionParam(n_componentes, rowId, tipo) {
        var cache  = (tipo === "pct") ? window.cachePct : window.cacheTol;
        var prefix = (tipo === "pct") ? "fila-pct-" : "fila-tol-";
        var fila   = $("#" + prefix + rowId);
        var row    = cache.find(function (r) { return r.id == rowId; });
        if (!row) return;

        var componenteImg = row.componente;
        if(n_componentes == 1) {
            componenteImg = 0;
        }
    
        var rutaImg = "../assets/img/" + window.paramPerfilFamilia + "/" + window.paramPerfilNombre + "/" + window.paramPerfilNombre + "_" + componenteImg + ".jpg";
        var imgCell = '<td class="column-image" ><img src="' + rutaImg + '" alt="comp ' + row.componente + '"  onerror="this.src=\'../assets/img/general/blanco.jpg\'"></td>';
        var actsCell = '<td><div class="d-flex gap-1 container-actions">' +
            '<button class="btn-auth btn-param-guardar" data-id="' + rowId + '" data-tipo="' + tipo + '" title="Guardar"><i class="bi bi-floppy"></i></button>' +
            '<button class="btn-cancel btn-param-cancelar" data-id="' + rowId + '" data-tipo="' + tipo + '" title="Cancelar"><i class="bi bi-x-lg"></i></button>' +
            '</div></td>';
        var compCell = '<td class="text-center fw-bold">' + row.componente + '</td>';

        fila.addClass("param-row-editing");

        if (tipo === "pct") {
            fila.html(imgCell + actsCell + compCell +
                paramInp("pct_di_" + rowId, parseFloat(row.di).toFixed(3), "0.001", "DI") +
                paramInp("pct_de_" + rowId, parseFloat(row.de).toFixed(3), "0.001", "DE") +
                paramInp("pct_h_"  + rowId, parseFloat(row.h).toFixed(3),  "0.001", "H")
            );
        } else {
            fila.html(imgCell + actsCell + compCell +
                paramInp("tol_di_" + rowId, parseFloat(row.di).toFixed(2), "0.01", "DI") +
                paramInp("tol_de_" + rowId, parseFloat(row.de).toFixed(2), "0.01", "DE")
            );
        }
    }

    function paramInp(id, val, step, label) {
        return '<td><input type="number" id="' + id + '" class="param-edit-input" value="' + val + '" step="' + step + '" min="0" placeholder="' + label + '"></td>';
    }

    function guardarEdicionParam(rowId, tipo) {
        var url  = "../ajax/post_perfil.php";
        var data = { id: rowId };

        if (tipo === "pct") {
            var di = parseFloat($("#pct_di_" + rowId).val());
            var de = parseFloat($("#pct_de_" + rowId).val());
            var h  = parseFloat($("#pct_h_"  + rowId).val());

            if (isNaN(di) || isNaN(de) || isNaN(h)) {
                sweetAlertResponse("warning", "Valores inválidos", "Ingrese valores numéricos válidos.", "none"); return;
            }
            if (di > 1.000 || de > 1.000 || h > 1.000) {
                sweetAlertResponse("warning", "Valor fuera de rango", "Ningún porcentaje puede superar 1.000.", "none"); return;
            }
            if (h <= 0) {
                sweetAlertResponse("warning", "Valor inválido", "El porcentaje H no puede ser 0.", "none"); return;
            }
            data.action = "update_porcentaje";
            data.di     = di.toFixed(3);
            data.de     = de.toFixed(3);
            data.h      = h.toFixed(3);

        } else {
            var di = parseFloat($("#tol_di_" + rowId).val());
            var de = parseFloat($("#tol_de_" + rowId).val());

            if (isNaN(di) || isNaN(de)) {
                sweetAlertResponse("warning", "Valores inválidos", "Ingrese valores numéricos válidos.", "none"); return;
            }
            if (di < 0 || de < 0) {
                sweetAlertResponse("warning", "Valor inválido", "Las tolerancias deben ser mayores o iguales a 0.", "none"); return;
            }
            data.action = "update_tolerancia";
            data.di     = di.toFixed(2);
            data.de     = de.toFixed(2);
        }

        $.post(url, data, function (resp) {
            if (resp.success) {
                if (tipo === "pct") cargarPorcentajes();
                else cargarTolerancias();
                sweetAlertResponse("success", "Guardado", resp.message, "none");
            } else {
                sweetAlertResponse("warning", "No se pudo guardar", resp.message, "none");
            }
        }, "json");
    }

    function renderFlagDetalle(selector, valor, etiqueta) {
        var $el = $(selector);
        if (valor === "0" || !valor) {
            $el.html('<span class="flag-badge flag-inactivo"><i class="bi bi-x-circle me-1"></i>' + etiqueta + ': No aplica</span>');
        } else if (valor === "1") {
            $el.html('<span class="flag-badge flag-activo"><i class="bi bi-check-circle-fill me-1"></i>' + etiqueta + ': Sí (componente 1)</span>');
        } else {
            $el.html('<span class="flag-badge flag-activo"><i class="bi bi-check-circle-fill me-1"></i>' + etiqueta + ': Sí, en el componente ' + valor + '</span>');
        }
    }

    function generarSelectoresFlags(nComp, resorte, wiper, escalon, wiperEsp) {
        var campos = [
            { id: "selectFlagResorte",      label: "Componente con resorte",         val: resorte },
            { id: "selectFlagWiper",        label: "Componente wiper",               val: wiper },
            { id: "selectFlagEscalon",      label: "Componente con caja + escalón",  val: escalon },
            { id: "selectFlagWiperEsp",     label: "Componente con H1 y H2",         val: wiperEsp }
        ];
        var html = "";
        $.each(campos, function (i, c) {
            html += '<div class="mb-2">';
            html += '<label class="lbl-general">' + c.label + '</label>';
            html += '<select id="' + c.id + '" class="form-select">';
            html += '<option value="0"' + (c.val === "0" ? " selected" : "") + '>0 — No aplicar bandera</option>';
            for (var n = 1; n <= nComp; n++) {
                var sel = (c.val == n) ? " selected" : "";
                html += '<option value="' + n + '"' + sel + '>' + n + ' — Aplicar al componente ' + n + '</option>';
            }
            html += '</select></div>';
        });
        $("#containerFlagsEditar").html(html);
    }

    
    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        // VER DETALLES DEL PERFIL
        $("#perfilesTable").on("click", ".btn-ver-detalle", function () {
            var d = $(this).data();

            // Titulo del modal con id
            $("#titleModalDetallePerfil").text("ID: " + d.id);

            // Imagen principal (_0.jpg)
            var familiaLower = d.familia.toLowerCase();
            var rutaImg = "../assets/img/" + familiaLower + "/" + d.nombre + "/" + d.nombre + "_0.jpg";
            $("#imgDetallePerfil").attr("src", rutaImg).attr("alt", d.nombre);

            // Datos basicos
            $("#detalle-nombre").text(d.nombre);
            $("#detalle-familia").text(d.familia + (d.familia2 ? " / " + d.familia2 : ""));
            $("#detalle-componentes").text(d.componentes);
            $("#detalle-grupo").text(d.grupoNombre || "No asignado aún");

            if (d.detalles && d.detalles.trim() !== "") {
                $("#detalle-detalles").text(d.detalles).removeClass("fst-italic text-muted").addClass("");
            } else {
                $("#detalle-detalles").text("No hay detalles").removeClass("").addClass("fst-italic text-muted");
            }

            // Fechas
            $("#detalle-updated").text(d.updated || "—");
            $("#detalle-created").text(d.created || "—");

            // Flags
            renderFlagDetalle("#flag-resorte",       d.resorte,      "Resorte");
            renderFlagDetalle("#flag-wiper",          d.wiper,        "Wiper");
            renderFlagDetalle("#flag-escalon",        d.escalon,      "Caja + Escalón");
            renderFlagDetalle("#flag-wiper-especial", d.wiperEspecial,"Altura H1 y H2");

            $("#modalDetallePerfil").modal("show");
        });
        // ABRIR MODAL EDITAR PERFIL
        $("#perfilesTable").on("click", ".btn-editar-perfil", function () {
            var d = $(this).data();

            $("#titleModalEditarPerfil").text("ID: " + d.id + " - Perfil: " + d.nombre);
            $("#inputIdEditarPerfil").val(d.id);
            $("#inputDetallesEditarPerfil").val(d.detalles || "");

            // Selector de grupo
            var $sel = $("#selectGrupoEditarPerfil");
            $sel.html('<option value="">Seleccione un grupo de herramientas...</option>');
            $.each(window.GRUPOS_HERRAMIENTA, function (i, g) {
                var selected = (g.id == d.grupoId) ? " selected" : "";
                $sel.append('<option value="' + g.id + '"' + selected + '>' + g.nombre + '</option>');
            });

            // Flags — solo visibles y editables para Administrador/Sistemas
            if (window.ES_ADMINISTRADOR) {
                $("#seccionFlagsEditar").removeClass("d-none");
                generarSelectoresFlags(d.componentes, d.resorte, d.wiper, d.escalon, d.wiperEspecial);
            } else {
                $("#seccionFlagsEditar").addClass("d-none");
            }

            $("#modalEditarPerfil").modal("show");
        });
        // Guardar edicion de perfil
        $("#btnGuardarEditarPerfil").on("click", function () {
            var id       = $("#inputIdEditarPerfil").val();
            var detalles = $("#inputDetallesEditarPerfil").val().trim();
            var grupoId  = $("#selectGrupoEditarPerfil").val();

            var data = { action:"update_perfil", id:id, detalles:detalles, grupo_herramienta_id: grupoId || "" };

            if (window.ES_ADMINISTRADOR) {
                data.con_resorte_en      = $("#selectFlagResorte").val()   || "0";
                data.es_wiper_en         = $("#selectFlagWiper").val()     || "0";
                data.con_escalon_en      = $("#selectFlagEscalon").val()   || "0";
                data.es_wiper_especial_en= $("#selectFlagWiperEsp").val()  || "0";
            }

            $.ajax({
                url: "../ajax/post_perfil.php",
                type: "POST", data: data, dataType: "json",
                success: function (resp) {
                    if (resp.success) {
                        $("#modalEditarPerfil").modal("hide");
                        sweetAlertResponse("success", "Perfil actualizado", resp.message, "self");
                    } else {
                        sweetAlertResponse("warning", "Hubo un problema", resp.message, "none");
                    }
                },
                error: function (xhr, status, error) {
                    sweetAlertResponse("error", "Error", "Error al enviar datos. " + error, "none");
                }
            });
        });
        // ABRIR MODAL GESTIONAR PARAMETROS
        $("#perfilesTable").on("click", ".btn-gestionar-params", function () {
            var id         = $(this).data("id");
            var nombre     = $(this).data("nombre");
            var familia    = $(this).data("familia");
            var componentes= $(this).data("componentes");

            $("#titleModalParams").text("Parámetros del perfil: " + nombre);
            $("#subtitleModalParams").text("Perfil ID: " + id + " - Componentes: " + componentes);

            window.paramPerfilId      = id;
            window.paramPerfilNombre  = nombre;
            window.paramPerfilFamilia = familia;
            window.paramNComp         = componentes;

            // Activar tab porcentajes por defecto
            $("#tab-porcentajes").tab("show");
            cargarPorcentajes();
            cargarTolerancias();

            $("#modalParamsPerfil").modal("show");
        });
        // Cambio de tab
        $("#tab-porcentajes").on("shown.bs.tab", function () { cargarPorcentajes(); });
        $("#tab-tolerancias").on("shown.bs.tab", function () { cargarTolerancias(); });
        // DELEGACION DE EDICION INLINE EN MODAL PARAMS
        $(document).on("click", ".btn-param-editar", function () {
            var rowId = $(this).data("id");
            var tipo  = $(this).data("tipo"); // "pct" o "tol"
            var nComp  = $(this).data("componentes") || window.paramNComp || 1;
            activarEdicionParam(nComp, rowId, tipo);
        });

        $(document).on("click", ".btn-param-guardar", function () {
            var rowId = $(this).data("id");
            var tipo  = $(this).data("tipo");
            guardarEdicionParam(rowId, tipo);
        });

        $(document).on("click", ".btn-param-cancelar", function () {
            var tipo = $(this).data("tipo");
            if (tipo === "pct") cargarPorcentajes();
            else cargarTolerancias();
        });
    });
</script>

<style>
    /* ================================================================
    MODAL DETALLES venta — Estilo refinado industrial
    ================================================================ */

    /* Variables */
    :root {
        --mdt-bg:           #f0f8f0;
        --mdt-surface:      #fff;
        --mdt-surface-2:    #e6f2e6;
        --mdt-surface-3:    #d1e7d1;
        --mdt-border:       rgba(255,255,255,0.07);
        --mdt-border-hover: rgba(255,255,255,0.14);
        --mdt-text-primary: #e8eaf0;
        --mdt-text-muted:   #5a6070;
        --mdt-text-label:   #7c8494;
        --mdt-accent-blue:  #3b82f6;
        --mdt-accent-cyan:  #06b6d4;
        --mdt-accent-green: #22c55e;
        --mdt-accent-amber: #f59e0b;
        --mdt-radius:       10px;
        --mdt-radius-sm:    6px;
        --mdt-font:         'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        --mdt-mono:         'JetBrains Mono', monospace;
        --mdt-shadow:       0 24px 64px rgba(0,0,0,0.6);
    }

    /* Dialog */
    .mdt-dialog {
        max-width: 960px;
    }

    /* Content */
    .mdt-content {
        font-family: var(--mdt-font);
        background: var(--mdt-surface);
        border: 1px solid var(--mdt-border);
        border-radius: 14px !important;
        box-shadow: var(--mdt-shadow);
        overflow: hidden;
    }


    /* ---- BODY ---- */
    .mdt-body {
        background: var(--mdt-surface);
        padding: 1.5rem;
    }

    /* ---- TABS ---- */
    .mdt-tabs {
        display: flex;
        gap: 4px;
        padding-bottom: 0;
        list-style: none;
        padding-left: 0;
    }

    .mdt-tab-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 0.55rem 1rem;
        font-size: 16px;
        font-weight: 500;
        font-family: var(--mdt-font);
        color: #0a0f0a;
        background: transparent;
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: var(--mdt-radius-sm) var(--mdt-radius-sm) 0 0;
        cursor: pointer;
        transition: color 0.15s, background 0.15s, border-color 0.15s;
        position: relative;
        bottom: -1px;
        white-space: nowrap;
    }

    .mdt-tab-btn:hover {
        color: #55ad9b;
        background: var(--mdt-surface-2);
    }

    .mdt-tab-btn.active {
        color: #55ad9b;
        font-weight: 700;
        background: var(--mdt-surface);
        border-color: var(--mdt-border);
        border-bottom-color: var(--mdt-surface);
    }

    .mdt-tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        font-size: 14px;
        font-weight: 600;
        background: rgba(85, 173, 155, 0.1);
        color: #55ad9b;
        border-radius: 20px;
        border: 1px solid #55ad9b;
    }

    /* ---- SECCIONES ---- */
    .mdt-section {
        background: #fff;
        border: 1px solid var(--mdt-border);
        margin-bottom: 1.25rem;
        overflow: hidden;
    }

    .mdt-section:last-child {
        margin-bottom: 0;
    }

    .mdt-section-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 0.7rem 1.1rem;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        background-color: #607d8b;
        border-bottom: 1px solid rgba(59,130,246,0.15);
        color: #fff;
    }

    .mdt-section-body {
        padding: 1.25rem 1.1rem;
    }

    /* ---- GRID DE CAMPOS ---- */
    .mdt-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
    }

    .mdt-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .mdt-field--full {
        grid-column: 1 / -1;
    }

    .mdt-field-label {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #333;
    }

    .mdt-field-value {
        font-size: 14px;
        font-weight: 500;
        color: #555;
        margin: 0;
        padding: 0.45rem 0.65rem;
        background: var(--mdt-surface-2);
        border: 1px solid var(--mdt-border);
        border-radius: var(--mdt-radius-sm);
        line-height: 1.4;
        min-height: 34px;
        display: flex;
        align-items: center;
    }


    /* ---- IMÁGENES ---- */
    .mdt-images-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .mdt-image-block {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .mdt-img-viewer {
        min-height: 220px;
        background: var(--mdt-surface-2);
        border: 1px dashed var(--mdt-border-hover);
        border-radius: var(--mdt-radius);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        transition: border-color 0.2s;
    }

    .mdt-img-viewer:hover {
        border-color: rgba(59,130,246,0.3);
    }

    .mdt-img-viewer img {
        max-width: 100%;
        max-height: 280px;
        object-fit: contain;
        border-radius: var(--mdt-radius-sm);
    }

    .mdt-img-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        font-size: 0.78rem;
        color: var(--mdt-text-muted);
        font-family: var(--mdt-font);
        letter-spacing: 0.02em;
    }

    /* ---- TABLA BARRAS ---- */
    .mdt-barras-toolbar {
        display: flex;
        align-items: center;
    }

    .mdt-table-wrapper {
        max-height: 300px;
        overflow-y: auto;
        overflow-x: auto;
        border: 1px solid var(--mdt-border);
        scrollbar-width: thin;
        scrollbar-color: #2a2e3a transparent;
    }

    .mdt-table-wrapper::-webkit-scrollbar { width: 6px; }
    .mdt-table-wrapper::-webkit-scrollbar-track { background: transparent; }
    .mdt-table-wrapper::-webkit-scrollbar-thumb { background: #2a2e3a; border-radius: 4px; }

    .mdt-table {
        width: 100%;
        margin: 0 !important;
        font-family: var(--mdt-font);
        background: var(--mdt-bg) !important;
    }

    .mdt-table thead tr th {
        background: #b8d4b8 !important;
        color: #0a0f0a !important;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 0.65rem 0.85rem;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .mdt-table tbody tr {
        background: #e8f5e8 !important;
        transition: background 0.12s;
    }

    .mdt-table tbody td {
        font-size: 14px;
        font-weight: 400;
        color: #0a0f0a !important;
        padding: 0.55rem 0.85rem;
        vertical-align: middle;
    }

    /* Badges del JS — sobrescribir colores para tema oscuro */
    .mdt-table .badge.bg-info {
        background: rgba(6,182,212,0.15) !important;
        color: #67e8f9 !important;
        border: 1px solid rgba(6,182,212,0.25);
        font-size: 0.68rem;
        font-family: var(--mdt-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdt-table .badge.bg-warning {
        background: rgba(245,158,11,0.15) !important;
        color: #fcd34d !important;
        border: 1px solid rgba(245,158,11,0.25);
        font-size: 0.68rem;
        font-family: var(--mdt-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdt-table .badge.bg-secondary {
        background: rgba(100,116,139,0.2) !important;
        color: #94a3b8 !important;
        border: 1px solid rgba(100,116,139,0.25);
        font-size: 0.68rem;
        font-family: var(--mdt-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdt-table .badge.bg-danger {
        background: rgba(239,68,68,0.12) !important;
        color: #fca5a5 !important;
        border: 1px solid rgba(239,68,68,0.22);
        font-size: 0.68rem;
        font-family: var(--mdt-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    .mdt-table .badge.bg-light {
        background: rgba(255,255,255,0.05) !important;
        color: #9ca3af !important;
        border: 1px solid rgba(255,255,255,0.1);
        font-size: 0.68rem;
        font-family: var(--mdt-font);
        font-weight: 500;
        padding: 0.28em 0.55em;
    }

    /* Badges fuera de la tabla (estado de recepción) */
    .badge.bg-success {
        background: rgba(34,197,94,0.12) !important;
        /* #86efac */
        color: #00876cde !important;
        border: 1px solid rgba(34,197,94,0.22);
        font-size: 14px;
        font-weight: 700;
        padding: 10px 0.65em;
        border-radius: 4px;
    }

    .badge.bg-warning {
        background: rgba(245, 158, 11, 0.12) !important;
        color: #ffbf00 !important;
        border: 1px solid rgba(245, 158, 11, 0.22);
        font-size: 14px;
        font-weight: 700;
        padding: 10px 0.65em;
        border-radius: 4px;
    }

    /* ---- RESPONSIVO ---- */
    @media (max-width: 768px) {
        .mdt-grid {
            grid-template-columns: 1fr;
        }
        .mdt-field--full {
            grid-column: 1;
        }
        .mdt-images-grid {
            grid-template-columns: 1fr;
        }
        .mdt-body {
            padding: 1rem;
        }
    }
</style>
<style>
    /* Fila de edicion inline en la tabla de limitantes */
    .param-row-editing td { background: #55ad9b66 !important; }
    .param-edit-input {
        font-size: 14px; padding: 4px 4px;
        border: 1px solid #55AD9B; border-radius: 4px;
    }
    .column-image{
        width: 250px !important;
    }
    .column-image img  {
        width: 200px !important; 
        border: 1px solid #e0e0e0; 
        border-radius: 6px;
    }
</style>