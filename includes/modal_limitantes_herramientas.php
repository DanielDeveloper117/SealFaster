<?php
function renderTabLimitantes(string $dureza): string {
    $tabId  = "tabla-lim-{$dureza}";
    $label  = $dureza === 'blandos' ? 'Blandos' : 'Duros';
    return <<<HTML
        <div class="mghp-table-wrapper mghp-section">
            <table id="{$tabId}" class=" table-striped table-bordered table-sm mghp-table">
                <thead>
                    <tr>
                        <th style="width:50px;"></th>
                        <th>Herramienta</th>
                        <th>DI min</th><th>DI max</th>
                        <th>DE min</th><th>DE max</th>
                        <th>Sec min</th><th>Sec max</th>
                        <th>H min</th><th>H max</th>
                    </tr>
                </thead>
                <tbody id="tbody-lim-{$dureza}">
                    <tr id="row-empty-{$dureza}">
                        <td colspan="10" class="text-center text-muted py-3">
                            <i class="bi bi-inbox me-2"></i>Sin limitantes para {$label}.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    HTML;
}
?>
<!-- ============================================================
     modal_limitantes_grupo.php — Gestión de limitantes por dureza
     ============================================================ -->
<div class="modal fade" id="modalLimitantes" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 id="titleModalLimitantes" class="modal-title mb-0">Limitantes de herramienta</h5>
                    <small id="subtitleModalLimitantes" class="text-muted">Grupo: —</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" style="padding:1.25rem;">

                <!-- TABS: Blandos / Duros -->
                <ul class="nav mghp-tabs mb-0" id="tabsLimitantes" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="mghp-tab-btn active" id="tab-blandos"
                                data-bs-toggle="tab" data-bs-target="#content-blandos"
                                type="button" role="tab">
                            <i class="bi bi-droplet-half me-1"></i> Blandos
                            <span class="mghp-tab-badge" id="badge-blandos">0</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="mghp-tab-btn" id="tab-duros"
                                data-bs-toggle="tab" data-bs-target="#content-duros"
                                type="button" role="tab">
                            <i class="bi bi-gem me-1"></i> Duros
                            <span class="mghp-tab-badge" id="badge-duros">0</span>
                        </button>
                    </li>
                </ul>

                <div class="tab-content mghp-tab-content" id="contentLimitantes">

                    <!-- ---- TAB BLANDOS ---- -->
                    <div class="tab-pane fade show active" id="content-blandos" role="tabpanel">
                        <?php echo renderTabLimitantes('blandos'); ?>
                    </div>

                    <!-- ---- TAB DUROS ---- -->
                    <div class="tab-pane fade" id="content-duros" role="tabpanel">
                        <div class="alert alert-info d-flex align-items-center gap-2 mb-3" id="alertSinDuros" style="display:none!important;">
                            <i class="bi bi-info-circle-fill"></i>
                            Este grupo no tiene limitantes para materiales duros.
                            Puede agregarlas con el botón inferior.
                        </div>
                        <?php echo renderTabLimitantes('duros'); ?>
                    </div>

                </div>

                <!-- Formulario inline para agregar nueva limitante -->
                <div id="panelAgregarLimitante" class="mt-3 p-3"
                     style="background:#f0faf8; border:1px solid #a5d6a7; border-radius:8px; display:none;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-plus-circle-fill text-success"></i>
                        <strong id="labelAgregarLimitante">Agregar limitante — Blandos</strong>
                    </div>
                    <div class="d-flex flex-column" style="overflow-x:auto;">
                        <div class="d-flex flex-column flex-lg-row gap-3 py-3 ">
                            <div class="col-12 col-md-2">
                                <label class="form-label form-label-sm mb-1">Herramienta <span class="text-danger">*</span></label>
                                <select id="selectHerramientaNueva" class="form-select form-select-sm">
                                    <option value="">Seleccione...</option>
                                </select>
                            </div>
                            <div class="">
                                <label class="form-label form-label-sm mb-1">DI min</label>
                                <input type="number" id="inp_di_min" class="form-control form-control-sm" step="0.01" min="0" placeholder="0">
                            </div>
                            <div class="">
                                <label class="form-label form-label-sm mb-1">DI max</label>
                                <input type="number" id="inp_di_max" class="form-control form-control-sm" step="0.01" min="0" placeholder="850">
                            </div>
                            <div class="">
                                <label class="form-label form-label-sm mb-1">DE min</label>
                                <input type="number" id="inp_de_min" class="form-control form-control-sm" step="0.01" min="0" placeholder="0">
                            </div>
                            <div class="">
                                <label class="form-label form-label-sm mb-1">DE max</label>
                                <input type="number" id="inp_de_max" class="form-control form-control-sm" step="0.01" min="0" placeholder="850">
                            </div>
                            <div class="">
                                <label class="form-label form-label-sm mb-1">Sec min</label>
                                <input type="number" id="inp_sec_min" class="form-control form-control-sm" step="0.01" min="0" placeholder="0">
                            </div>
                            <div class="">
                                <label class="form-label form-label-sm mb-1">Sec max</label>
                                <input type="number" id="inp_sec_max" class="form-control form-control-sm" step="0.01" min="0" placeholder="45">
                            </div>
                            <div class="">
                                <label class="form-label form-label-sm mb-1">H min</label>
                                <input type="number" id="inp_h_min" class="form-control form-control-sm" step="0.01" min="0" placeholder="0">
                            </div>
                            <div class="">
                                <label class="form-label form-label-sm mb-1">H max</label>
                                <input type="number" id="inp_h_max" class="form-control form-control-sm" step="0.01" min="0" placeholder="55">
                            </div>                            
                        </div>
                        <div class="d-flex flex-column flex-lg-row gap-4 justify-content-center p-2 align-items-center">
                            <div class="">
                                <button type="button" class="btn-general btn-sm w-auto px-3" id="btnConfirmarAgregar"
                                        style="padding:6px 12px; font-size:13px;">
                                    <i class="bi bi-check-lg"></i> Agregar
                                </button>
                                <button type="button" class="btn-cancel w-auto px-2" id="btnCancelarAgregar"
                                        style="padding:6px 10px; font-size:13px;">
                                    <i class="bi bi-x-lg"></i>Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones inferiores del modal -->
                <div class="d-flex col-12 col-lg-4 gap-2 mt-3">
                    <button type="button" class="btn-general btn-accion-lim" id="btnAgregarLimitante"
                            style="padding:8px 16px; font-size:14px;">
                        <i class="bi bi-plus-circle"></i> Agregar limitante
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>



<script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    var limGrupoId   = null;
    var limGrupoNom  = "";
    var limDurezaActiva = "blandos";
    var limDatosCache = { blandos: [], duros: [] };


    // ============================================================
    //              ******** FUNCIONES ********
    // ============================================================
    // FUNCION GLOBAL: abrir modal de limitantes
    window.abrirModalLimitantes = function(grupoId, grupoNombre) {
        limGrupoId  = grupoId;
        limGrupoNom = grupoNombre;
        $("#titleModalLimitantes").text("Limitantes — " + grupoNombre);
        $("#subtitleModalLimitantes").text("Grupo ID: " + grupoId);
        $("#panelAgregarLimitante").hide();

        // Cargar herramientas disponibles en el selector
        cargarHerramientasSelector();

        // Cargar limitantes de ambas dureza
        cargarLimitantes("blandos");
        cargarLimitantes("duros");

        // Activar tab blandos por defecto
        $("#tab-blandos").tab("show");
        limDurezaActiva = "blandos";

        $("#modalLimitantes").modal("show");
    };
    // CARGAR LIMITANTES DE UN GRUPO POR DUREZA (AJAX)
    function cargarLimitantes(dureza) {
        var tbodyId = "#tbody-lim-" + dureza;
        var badgeId = "#badge-" + dureza;

        $(tbodyId).html('<tr><td colspan="10" class="text-center py-3"><div class="spinner-border spinner-border-sm text-success"></div> Cargando...</td></tr>');

        $.ajax({
            url: "../ajax/get_limitantes_grupo.php",
            type: "GET",
            data: { grupo_id: limGrupoId, dureza: dureza },
            dataType: "json",
            success: function (data) {
                limDatosCache[dureza] = data;
                $(badgeId).text(data.length);
                if (data.length === 0) {
                    $(tbodyId).html('<tr id="row-empty-' + dureza + '"><td colspan="10" class="text-center text-muted py-3"><i class="bi bi-inbox me-2"></i>Sin limitantes para ' + dureza + '.</td></tr>');
                    return;
                }
                var html = "";
                $.each(data, function (i, lim) {
                    html += renderFilaLimitante(lim, dureza);
                });
                $(tbodyId).html(html);
            },
            error: function () {
                $(tbodyId).html('<tr><td colspan="10" class="text-center text-danger">Error al cargar datos.</td></tr>');
            }
        });
    }

    function renderFilaLimitante(lim, dureza) {
        return '<tr id="fila-lim-' + lim.id + '">' +
            '<td><div class="d-flex gap-1 container-actions">' +
                '<button class="btn-general btn-accion-edit btn-lim-editar" data-id="' + lim.id + '" title="Editar"><i class="bi bi-pencil-square"></i></button>' +
                '<button class="btn-cancel btn-accion-del btn-lim-eliminar" data-id="' + lim.id + '" data-herramienta="' + lim.herramienta_numero + '" data-dureza="' + dureza + '" title="Eliminar"><i class="bi bi-trash"></i></button>' +
            '</div></td>' +
            '<td><strong>' + lim.herramienta_numero + '</strong></td>' +
            '<td>' + lim.di_min  + '</td><td>' + lim.di_max  + '</td>' +
            '<td>' + lim.de_min  + '</td><td>' + lim.de_max  + '</td>' +
            '<td>' + lim.seccion_min + '</td><td>' + lim.seccion_max + '</td>' +
            '<td>' + lim.h_min   + '</td><td>' + lim.h_max   + '</td>' +
        '</tr>';
    }
    // EDICION INLINE
    function activarEdicionInline(limId) {
        var dureza = "";
        // Buscar en cache
        $.each(["blandos", "duros"], function (i, d) {
            $.each(limDatosCache[d], function (j, lim) {
                if (lim.id == limId) { dureza = d; return false; }
            });
            if (dureza) return false;
        });

        var lim = limDatosCache[dureza].find(function (l) { return l.id == limId; });
        if (!lim) return;

        var fila = $("#fila-lim-" + limId);
        fila.addClass("lim-row-editing");
        fila.html(
            '<td><div class="d-flex gap-1">' +
                '<button class="btn-auth btn-accion-edit btn-lim-guardar" data-id="' + limId + '" title="Guardar"><i class="bi bi-floppy"></i></button>' +
                '<button class="btn-cancel btn-accion-del btn-lim-cancelar"  data-id="' + limId + '" data-dureza="' + dureza + '" title="Cancelar"><i class="bi bi-x-lg"></i></button>' +
            '</div></td>' +
            '<td><strong>' + lim.herramienta_numero + '</strong></td>' +
            td_inp("di_min_e",  lim.di_min)  + td_inp("di_max_e",  lim.di_max) +
            td_inp("de_min_e",  lim.de_min)  + td_inp("de_max_e",  lim.de_max) +
            td_inp("sec_min_e", lim.seccion_min) + td_inp("sec_max_e", lim.seccion_max) +
            td_inp("h_min_e",   lim.h_min)   + td_inp("h_max_e",   lim.h_max)
        );
    }

    function td_inp(id, val) {
        return '<td><input type="number" id="' + id + '" class="lim-edit-input" value="' + val + '" step="0.01" min="0"></td>';
    }

    function guardarEdicionInline(limId) {
        var dureza = "";
        $.each(["blandos","duros"], function(i, d) {
            $.each(limDatosCache[d], function(j, lim) {
                if (lim.id == limId) { dureza = d; return false; }
            });
            if (dureza) return false;
        });

        var di_min  = parseFloat($("#di_min_e").val());
        var di_max  = parseFloat($("#di_max_e").val());
        var de_min  = parseFloat($("#de_min_e").val());
        var de_max  = parseFloat($("#de_max_e").val());
        var sec_min = parseFloat($("#sec_min_e").val());
        var sec_max = parseFloat($("#sec_max_e").val());
        var h_min   = parseFloat($("#h_min_e").val());
        var h_max   = parseFloat($("#h_max_e").val());

        if (di_min >= di_max || de_min >= de_max || sec_min >= sec_max || h_min >= h_max) {
            sweetAlertResponse("warning", "Rangos inválidos", "Cada valor mínimo debe ser menor que su máximo correspondiente.", "none");
            return;
        }

        $.post("../ajax/post_grupo_limitante.php", {
            action: "update_limitante", id: limId,
            di_min, di_max, de_min, de_max,
            seccion_min: sec_min, seccion_max: sec_max,
            h_min, h_max
        }, function (data) {
            if (data.success) {
                cargarLimitantes(dureza);
                sweetAlertResponse("success", "Limitante actualizada", data.message, "none");
                
                $.ajax({
                    url: "../ajax/ajax_notificacion.php",
                    type: "POST",
                    data: { mensaje: "Limitante actualizada"},
                    success: function(response) {
                        console.log("Notificacion enviada: ", response);
                    },
                    error: function(error) {
                        console.error("Error al enviar la notificacion: ", error);
                    }
                });
            } else {
                sweetAlertResponse("warning", "No se pudo guardar", data.message, "none");
            }
        }, "json");
    }
    // CARGAR HERRAMIENTAS EN EL SELECTOR
    function cargarHerramientasSelector() {
        $.get("../ajax/get_herramientas.php", function (data) {
            var options = '<option value="" selected disabled>Seleccionar...</option>';
            $.each(data, function (i, h) {
                options += '<option value="' + h.id + '">' + h.numero + (h.descripcion ? ' — ' + h.descripcion : '') + '</option>';
            });
            $("#selectHerramientaNueva").html(options);
        }, "json");
    }

    function limpiarFormAgregar() {
        $("#selectHerramientaNueva").val("");
        $("#inp_di_min,#inp_di_max,#inp_de_min,#inp_de_max,#inp_sec_min,#inp_sec_max,#inp_h_min,#inp_h_max").val("");
    }


    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        // --- Tab activo cambia la dureza activa ---
        $("#tab-blandos").on("shown.bs.tab", function () { limDurezaActiva = "blandos"; });
        $("#tab-duros").on("shown.bs.tab",   function () { limDurezaActiva = "duros"; });

        // --- Mostrar panel de agregar limitante ---
        $("#btnAgregarLimitante").on("click", function () {
            $("#btnAgregarLimitante").addClass("d-none");
            limpiarFormAgregar();
            $("#labelAgregarLimitante").text("Agregar limitante — " + (limDurezaActiva === "blandos" ? "Blandos" : "Duros"));
            $("#panelAgregarLimitante").slideDown(200);
        });

        $("#btnCancelarAgregar").on("click", function () {
            $("#btnAgregarLimitante").removeClass("d-none");
            $("#panelAgregarLimitante").slideUp(200);
            limpiarFormAgregar();
        });
        // --- Confirmar agregar limitante ---
        $("#btnConfirmarAgregar").on("click", function () {
            var herramientaId = $("#selectHerramientaNueva").val();
            var di_min  = $("#inp_di_min").val();
            var di_max  = $("#inp_di_max").val();
            var de_min  = $("#inp_de_min").val();
            var de_max  = $("#inp_de_max").val();
            var sec_min = $("#inp_sec_min").val();
            var sec_max = $("#inp_sec_max").val();
            var h_min   = $("#inp_h_min").val();
            var h_max   = $("#inp_h_max").val();

            // Validacion frontend
            if (!herramientaId) {
                sweetAlertResponse("warning", "Campo requerido", "Seleccione una herramienta para agregar la limitante.", "none");
                return;
            }
            if (parseFloat(di_min) >= parseFloat(di_max)) {
                sweetAlertResponse("warning", "Rango inválido", "DI min debe ser menor que DI max.", "none");
                return;
            }
            if (parseFloat(de_min) >= parseFloat(de_max)) {
                sweetAlertResponse("warning", "Rango inválido", "DE min debe ser menor que DE max.", "none");
                return;
            }
            if (parseFloat(sec_min) >= parseFloat(sec_max)) {
                sweetAlertResponse("warning", "Rango inválido", "Sección min debe ser menor que Sección max.", "none");
                return;
            }
            if (parseFloat(h_min) >= parseFloat(h_max)) {
                sweetAlertResponse("warning", "Rango inválido", "H min debe ser menor que H max.", "none");
                return;
            }

            $.ajax({
                url: "../ajax/post_grupo_limitante.php",
                type: "POST",
                data: {
                    action: "insert_limitante",
                    grupo_id:      limGrupoId,
                    herramienta_id: herramientaId,
                    dureza:         limDurezaActiva,
                    di_min, di_max, de_min, de_max,
                    seccion_min: sec_min, seccion_max: sec_max,
                    h_min, h_max
                },
                dataType: "json",
                success: function (data) {
                    if (data.success) {
                        $("#panelAgregarLimitante").slideUp(200);
                        limpiarFormAgregar();
                        cargarLimitantes(limDurezaActiva);
                        sweetAlertResponse("success", "Limitante agregada", data.message, "none");
                        
                        $.ajax({
                            url: "../ajax/ajax_notificacion.php",
                            type: "POST",
                            data: { mensaje: "Nueva limitante de herramienta"},
                            success: function(response) {
                                console.log("Notificacion enviada: ", response);
                            },
                            error: function(error) {
                                console.error("Error al enviar la notificacion: ", error);
                            }
                        });
                    } else {
                        sweetAlertResponse("warning", "No se pudo agregar", data.message, "none");
                    }
                },
                error: function (xhr, status, error) {
                    sweetAlertResponse("error", "Error", "Error al enviar datos. " + error, "none");
                }
            });
        });
        
        // VER / GESTIONAR LIMITANTES
        $("#gruposTable").on("click", ".btn-ver-limitantes", function () {
            var id     = $(this).data("id");
            var nombre = $(this).data("nombre");
            abrirModalLimitantes(id, nombre);
        });
        // --- Delegar edición inline en tablas de limitantes ---
        $(document).on("click", ".btn-lim-editar", function () {
            var limId = $(this).data("id");
            activarEdicionInline(limId);
        });
        // --- Guardar edicion inline ---
        $(document).on("click", ".btn-lim-guardar", function () {
            var limId = $(this).data("id");
            guardarEdicionInline(limId);
        });
        // --- Cancelar edicion inline ---
        $(document).on("click", ".btn-lim-cancelar", function () {
            var dureza = $(this).data("dureza");
            cargarLimitantes(dureza);
        });
        // --- Eliminar limitante ---
        $(document).on("click", ".btn-lim-eliminar", function () {
            var limId  = $(this).data("id");
            var herNum = $(this).data("herramienta");
            var dureza = $(this).data("dureza");
            Swal.fire({
                title: "Eliminar limitante",
                html: "¿Eliminar la limitante de herramienta <strong>" + herNum + "</strong> (" + dureza + ")?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#ca4747",
                cancelButtonColor: "#6c757d"
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.post("../ajax/post_grupo_limitante.php",
                        { action: "delete_limitante", id: limId },
                        function (data) {
                            if (data.success) {
                                cargarLimitantes(dureza);
                                sweetAlertResponse("success", "Limitante eliminada", data.message, "none");
                                $.ajax({
                                    url: "../ajax/ajax_notificacion.php",
                                    type: "POST",
                                    data: { mensaje: "Limitante eliminada"},
                                    success: function(response) {
                                        console.log("Notificacion enviada: ", response);
                                    },
                                    error: function(error) {
                                        console.error("Error al enviar la notificacion: ", error);
                                    }
                                });
                            } else {
                                sweetAlertResponse("warning", "No se pudo eliminar", data.message, "none");
                            }
                        }, "json"
                    );
                }
            });
        });
    });
</script>

<style>
    /* Fila de edicion inline en la tabla de limitantes */
    .lim-row-editing td { background: #55ad9b66 !important; }
    .lim-edit-input {
        width: 70px; font-size: 12px; padding: 2px 4px;
        border: 1px solid #55AD9B; border-radius: 4px;
    }
</style>