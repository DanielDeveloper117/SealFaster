<!-- ============================================================
     modal_grupo.php — Crear / editar grupo de herramienta
     ============================================================ -->
<div class="modal fade" id="modalGrupo" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="titleModalGrupo" class="modal-title">Nuevo grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formGrupo" novalidate>
                    <input type="hidden" id="inputIdGrupo"     name="id">
                    <input type="hidden" id="inputActionGrupo" name="action" value="insert">
                    <div class="mb-3">
                        <label for="inputNombreGrupo" class="lbl-general">
                            Nombre del grupo <span class="text-danger">*</span>
                        </label>
                        <input id="inputNombreGrupo" type="text" class="form-control"
                               name="nombre" maxlength="60"
                               placeholder="Ej: limitantesHerramientas1" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputDescripcionGrupo" class="lbl-general">Descripción</label>
                        <textarea id="inputDescripcionGrupo" name="descripcion"
                                  class="form-control" rows="3" maxlength="255"></textarea>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn-general" id="btnGuardarGrupo">
                            <i class="bi bi-floppy me-1"></i> Crear
                        </button>
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
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



    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================  
    $(document).ready(function () {
        // NUEVO GRUPO
        $("#btnNuevoGrupo").on("click", function () {
            $("#formGrupo")[0].reset();
            $("#inputIdGrupo").val("");
            $("#inputActionGrupo").val("insert");
            $("#titleModalGrupo").text("Nuevo grupo de herramienta");
            $("#modalGrupo").modal("show");
        });
        // EDITAR GRUPO
        $("#gruposTable").on("click", ".btn-editar-grupo", function () {
            $("#formGrupo")[0].reset();
            $("#inputIdGrupo").val($(this).data("id"));
            $("#inputNombreGrupo").val($(this).data("nombre"));
            $("#inputDescripcionGrupo").val($(this).data("descripcion"));
            $("#inputActionGrupo").val("update");
            $("#titleModalGrupo").text("Editar grupo: " + $(this).data("nombre"));
            $("#modalGrupo").modal("show");
        });
        // GUARDAR GRUPO (insert / update)
        // --- Guardar grupo ---
        $("#btnGuardarGrupo").on("click", function () {
            var nombre = $("#inputNombreGrupo").val().trim();
            if (!nombre) {
                sweetAlertResponse("warning", "Campo requerido", "El nombre del grupo es obligatorio.", "none");
                return;
            }
            var formData = new FormData();
            formData.append("id",          $("#inputIdGrupo").val());
            formData.append("nombre",      nombre);
            formData.append("descripcion", $("#inputDescripcionGrupo").val().trim());
            formData.append("action",      $("#inputActionGrupo").val());

            $.ajax({
                url: "../ajax/post_grupo_limitante.php",
                type: "POST", data: formData,
                processData: false, contentType: false, dataType: "json",
                success: function (data) {
                    if (data.success) {
                        $("#modalGrupo").modal("hide");
                        sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                    } else {
                        sweetAlertResponse("warning", "Hubo un problema", data.message, "none");
                    }
                },
                error: function (xhr, status, error) {
                    sweetAlertResponse("error", "Error", "Error al enviar datos. " + error, "none");
                }
            });
        });
    });
</script>