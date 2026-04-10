<!-- ============================================================
     modal_perfiles_grupo.php
     Gestión de perfiles vinculados a un grupo de herramienta.
     Muestra los perfiles ya asignados como badges removibles
     y permite buscar y asignar perfiles sin grupo o reasignar
     los que ya tienen otro grupo asignado.
     ============================================================ -->
<div class="modal fade" id="modalPerfilesGrupo" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <div>
                    <h5 id="titleModalPerfiles" class="modal-title mb-0">
                        Perfiles asignados al grupo
                    </h5>
                    <small id="subtitleModalPerfiles" class="text-muted">Grupo: —</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- SECCION: Perfiles actuales del grupo -->
                <div class="mghp-section mb-3">
                    <div class="mghp-section-header" >
                        <i class="bi bi-diagram-3-fill me-2"></i>
                        Perfiles actualmente en este grupo
                        <span class="ms-2 mghp-tab-badge" id="badge-perfiles-asignados">0</span>
                    </div>
                    <div class="mghp-section-body">
                        <div id="containerPerfilesAsignados" class="d-flex flex-wrap gap-2 min-height-badges">
                            <span class="text-muted fst-italic" id="msgSinAsignados">
                                Ningún perfil asignado a este grupo.
                            </span>
                        </div>
                    </div>
                </div>

                <!-- SECCION: Buscador para agregar perfiles -->
                <div class="mghp-section">
                    <div class="mghp-section-header">
                        <i class="bi bi-search me-2"></i>
                        Buscar y asignar perfiles
                    </div>
                    <div class="mghp-section-body">
                        <div class="d-flex gap-2 mb-3">
                            <input type="text" id="inputBuscarPerfil"
                                   class="form-control"
                                   placeholder="Buscar perfil por nombre (ej: K04, R13, S22)...">
                            <button type="button" class="btn-general w-auto px-4"
                                    id="btnBuscarPerfil" style="white-space:nowrap;">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                        </div>

                        <!-- Resultados de busqueda -->
                        <div id="resultadosBusqueda" style="display:none;">
                            <div class="d-flex flex-wrap gap-2" id="containerResultados">
                            </div>
                        </div>
                        <div id="msgBusquedaVacia" class="text-muted fst-italic" style="display:none;">
                            No se encontraron resultados.
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel w-auto px-4"
                        data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>

<script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    var pgGrupoId  = null;
    var pgGrupoNom = "";


    // ============================================================
    //              ******** FUNCIONES ********
    // ============================================================
    // FUNCION GLOBAL: abrir modal de perfiles del grupo
    window.abrirModalPerfiles = function(grupoId, grupoNombre, totalPerfiles) {
        pgGrupoId  = grupoId;
        pgGrupoNom = grupoNombre;

        $("#titleModalPerfiles").text("Perfiles del grupo: " + grupoNombre);
        $("#subtitleModalPerfiles").text("Grupo ID: " + grupoId);
        $("#inputBuscarPerfil").val("");
        $("#resultadosBusqueda").hide();
        $("#msgBusquedaVacia").hide();
        $("#containerResultados").html("");

        cargarPerfilesAsignados();
        $("#modalPerfilesGrupo").modal("show");
        
        $.ajax({
            url: "../ajax/ajax_notificacion.php",
            type: "POST",
            data: { mensaje: "Se vio modal de perfiles del grupo"},
            success: function(response) {
                console.log("Notificacion enviada: ", response);
            },
            error: function(error) {
                console.error("Error al enviar la notificacion: ", error);
            }
        });
    };
    // CARGAR PERFILES ASIGNADOS AL GRUPO ACTUAL
    function cargarPerfilesAsignados() {
        $("#containerPerfilesAsignados").html(
            '<span class="text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Cargando...</span>'
        );

        $.get("../ajax/get_perfiles_grupo.php",
            { grupo_id: pgGrupoId, tipo: "asignados" },
            function (data) {
                $("#badge-perfiles-asignados").text(data.length);
                if (data.length === 0) {
                    $("#containerPerfilesAsignados").html(
                        '<span class="text-muted fst-italic">Ningún perfil asignado a este grupo.</span>'
                    );
                    return;
                }
                var html = "";
                $.each(data, function (i, p) {
                    html += '<span class="badge-perfil badge-perfil-asignado" title="' + (p.familia || '') + '">' +
                                '<i class="bi bi-grid-3x3-gap-fill me-1"></i>' + p.nombre +
                                '<button class="btn-desvincular" data-id="' + p.id + '" data-nombre="' + p.nombre + '" title="Desvincular perfil">' +
                                    '<i class="bi bi-x-circle-fill"></i>' +
                                '</button>' +
                            '</span>';
                });
                $("#containerPerfilesAsignados").html(html);
            }, "json"
        ).fail(function () {
            $("#containerPerfilesAsignados").html('<span class="text-danger">Error al cargar perfiles.</span>');
        });
    }
    // BUSCAR PERFILES (todos, con info de su grupo actual)
    function buscarPerfiles() {
        var query = $("#inputBuscarPerfil").val().trim();
        // if (query.length < 1) {
        //     sweetAlertResponse("info", "Escriba un nombre", "Ingrese al menos 1 caracter para buscar.", "none");
        //     return;
        // }

        $("#containerResultados").html(
            '<span class="text-muted"><div class="spinner-border spinner-border-sm me-2"></div>Buscando...</span>'
        );
        $("#resultadosBusqueda").show();
        $("#msgBusquedaVacia").hide();

        $.get("../ajax/get_perfiles_grupo.php",
            { query: query, grupo_id: pgGrupoId, tipo: "buscar" },
            function (data) {
                if (data.length === 0) {
                    $("#containerResultados").html("");
                    $("#resultadosBusqueda").hide();
                    $("#msgBusquedaVacia").show();
                    return;
                }
                var html = "";
                $.each(data, function (i, p) {
                    var esPropio   = p.grupo_herramienta_id == pgGrupoId;
                    var tieneOtro  = p.grupo_herramienta_id && !esPropio;
                    var claseExtra = esPropio   ? "badge-resultado-propio"
                                : tieneOtro ? "badge-resultado-ocupado"
                                : "badge-resultado-libre";
                    var iconoExtra = esPropio   ? '<i class="bi bi-check-circle-fill"></i>'
                                : tieneOtro ? '<i class="bi bi-arrow-left-right"></i>'
                                : '<i class="bi bi-plus-circle"></i>';
                    var tooltip = esPropio   ? "Ya pertenece a este grupo"
                                //: tieneOtro ? "Reasignar desde: " + (p.grupo_nombre || "otro grupo")
                                : tieneOtro ? "Reasignar a este grupo (grupo actual: " + (p.grupo_nombre || "otro grupo") + ")"
                                : "Sin grupo - clic para asignar";

                    html += '<span class="badge-resultado ' + claseExtra + '" ' +
                                'title="' + tooltip + '" ' +
                                'data-id="' + p.id + '" ' +
                                'data-nombre="' + p.nombre + '" ' +
                                'data-grupo-actual="' + (p.grupo_herramienta_id || '') + '" ' +
                                'data-grupo-actual-nombre="' + (p.grupo_nombre || '') + '">' +
                                    p.nombre +
                                    // (p.familia ? ' <small style="opacity:0.7;font-weight:400;">(' + p.familia + ')</small>' : '') +
                                    iconoExtra +
                            '</span>';
                });
                $("#containerResultados").html(html);
                $("#msgBusquedaVacia").hide();
            }, "json"
        ).fail(function () {
            $("#containerResultados").html('<span class="text-danger">Error al buscar perfiles.</span>');
        });
    }
    // ASIGNAR PERFIL AL GRUPO ACTUAL
    function asignarPerfil(perfilId, perfilNom) {
        $.post("../ajax/post_grupo_limitante.php", {
            action:    "asignar_perfil",
            perfil_id: perfilId,
            grupo_id:  pgGrupoId
        }, function (data) {
            if (data.success) {
                cargarPerfilesAsignados();
                sweetAlertResponse("success", "Perfil asignado", '"' + perfilNom + '" ha sido asignado al grupo correctamente.', "none");
                buscarPerfiles(); // refrescar resultados con estado actualizado
            
            } else {
                sweetAlertResponse("warning", "No se pudo asignar", data.message, "none");
            }
        }, "json");
    }
    // DESVINCULAR PERFIL DEL GRUPO
    function desvincularPerfil(perfilId, perfilNom) {
        Swal.fire({
            title: "Desvincular perfil",
            html: "¿Quitar el perfil <strong>" + perfilNom + "</strong> de este grupo?<br>" +
                "<small>El perfil quedará sin grupo de herramienta asignado.</small>",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, desvincular",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#ca4747",
            cancelButtonColor: "#6c757d"
        }).then(function (result) {
            if (result.isConfirmed) {
                $.post("../ajax/post_grupo_limitante.php", {
                    action:    "desvincular_perfil",
                    perfil_id: perfilId
                }, function (data) {
                    if (data.success) {
                        cargarPerfilesAsignados();
                        sweetAlertResponse("success", "Desvinculado", '"' + perfilNom + '" desvinculado del grupo correctamente.', "none");
                        buscarPerfiles(); // refrescar resultados con estado actualizado
                    } else {
                        sweetAlertResponse("warning", "No se pudo desvincular", data.message, "none");
                    }
                }, "json");
            }
        });
    }


    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        // =================================
        //  ****** INICIALIZACIONES ****** 
        // Buscar perfiles
        $("#btnBuscarPerfil").on("click", buscarPerfiles);
        $("#inputBuscarPerfil").on("keydown", function (e) {
            if (e.key === "Enter") buscarPerfiles();
        });
        // =================================
        // GESTIONAR PERFILES DEL GRUPO
        $("#gruposTable").on("click", ".btn-gestionar-perfiles", function () {
            var id       = $(this).data("id");
            var nombre   = $(this).data("nombre");
            var perfiles = $(this).data("perfiles");
            abrirModalPerfiles(id, nombre, perfiles);
        });

        // ELIMINAR GRUPO
        $("#gruposTable").on("click", ".btn-eliminar-grupo", function () {
            var id          = $(this).data("id");
            var nombre      = $(this).data("nombre");
            var limitantes  = parseInt($(this).data("limitantes"));

            var textoExtra = limitantes > 0
                ? "<br><small>También se eliminarán sus " + limitantes + " limitante(s) asociada(s).</small>"
                : "";

            Swal.fire({
                title: "Eliminar grupo",
                html: "¿Confirma eliminar el grupo <strong>" + nombre + "</strong>?" + textoExtra,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar",
                confirmButtonColor: "#ca4747",
                cancelButtonColor: "#6c757d",
                allowOutsideClick: false
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "../ajax/post_grupo_limitante.php",
                        type: "POST",
                        data: { id: id, action: "delete_grupo" },
                        dataType: "json",
                        success: function (data) {
                            if (data.success) {
                                sweetAlertResponse("success", "Grupo eliminado", data.message, "self");
                            } else {
                                sweetAlertResponse("warning", "No se pudo eliminar", data.message, "none");
                            }
                        },
                        error: function (xhr, status, error) {
                            sweetAlertResponse("error", "Error", "Error al procesar la solicitud. " + error, "none");
                        }
                    });
                }
            });
        });
        // Desvincular perfil (delegado)
        $(document).on("click", ".btn-desvincular", function () {
            var perfilId   = $(this).data("id");
            var perfilNom  = $(this).data("nombre");
            desvincularPerfil(perfilId, perfilNom);
        });
        // Asignar perfil desde resultados de busqueda (delegado)
        $(document).on("click", ".badge-resultado-libre, .badge-resultado-ocupado", function () {
            var perfilId      = $(this).data("id");
            var perfilNom     = $(this).data("nombre");
            var grupoActual   = $(this).data("grupo-actual") || null;
            var grupoActualNom = $(this).data("grupo-actual-nombre") || "";

            if (grupoActual && grupoActual != pgGrupoId) {
                // Perfil ya tiene otro grupo: pedir confirmacion de reasignacion
                Swal.fire({
                    title: "Reasignar perfil",
                    html: "El perfil <strong>" + perfilNom + "</strong> ya pertenece al grupo " +
                        "<strong>" + grupoActualNom + "</strong>.<br>" +
                        "¿Desea reasignarlo al grupo actual <strong>" + pgGrupoNom + "</strong>?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, reasignar",
                    cancelButtonText: "Cancelar",
                    confirmButtonColor: "#55AD9B",
                    cancelButtonColor: "#6c757d"
                }).then(function (result) {
                    if (result.isConfirmed) {
                        asignarPerfil(perfilId, perfilNom);
                    }
                });
            } else {
                asignarPerfil(perfilId, perfilNom);
            }
        });

    });
</script>

<style>
    /* Badge de perfil asignado — con boton de desvincular */
    .badge-perfil {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: default;
        border: 1px solid transparent;
        transition: all 0.2s;
    }
    .badge-perfil-asignado {
        background: #55ad9b33;
    }
    .badge-perfil-asignado .btn-desvincular {
        background: none; border: none; padding: 0;
        color: #55ad9b; cursor: pointer; line-height: 1;
        font-size: 16px; border-radius: 50%;
        transition: color 0.15s, background 0.15s;
    }
    .badge-perfil-asignado .btn-desvincular:hover { color: #ca4747; }

    /* Badge de resultado de busqueda */
    .badge-resultado {
        padding: 8px 14px;
        border-radius: 20px;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        cursor: pointer;
        border: 1.5px solid transparent;
        transition: all 0.2s;
        user-select: none;
    }
    .badge-resultado-libre {
        background: #e8f5e9;
        border-color: #a5d6a7;
    }
    .badge-resultado-libre:hover { background: #c8e6c9; transform: translateY(-2px); }
    .badge-resultado-libre i { color: #2e7d32; font-size: 16px;}

    .badge-resultado-ocupado {
        background: #fff8e1;
        border-color: #ffe082;
    }
    .badge-resultado-ocupado:hover { background: #ffecb3; transform: translateY(-2px); }
    .badge-resultado-ocupado i { color: #f9a825; font-size: 16px;}

    .badge-resultado-propio {
        background: #55ad9b33;
        color: #55ad9b;
        border-color: #55ad9b;
        cursor: default;
        opacity: 0.7;
    }
    .badge-resultado-propio i { color: #55ad9b; }
    .min-height-badges { min-height: 40px; }
</style>