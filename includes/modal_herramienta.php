<!-- ============================================================
     modal_herramienta.php
     Modal reutilizable para insertar y editar herramientas CNC.
     Incluido desde herramientas.php.
     ============================================================ -->
<div class="modal fade" id="modalHerramienta" tabindex="-1" aria-hidden="true"
     aria-labelledby="titleModalHerramienta"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 id="titleModalHerramienta" class="modal-title">Nueva herramienta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">

                <!-- Alerta de impacto: solo visible al editar una herramienta con relaciones -->
                <div id="alertaImpacto" class="alert alert-warning d-none mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Atención:</strong> Esta herramienta está asignada a
                    <strong id="spanGruposAfectados">0</strong> grupo(s) de limitantes.
                    Cambiar el número actualizará su identificador en todo el sistema.
                </div>

                <form id="formHerramienta" novalidate>
                    <input type="hidden" id="inputIdHerramienta"     name="id">
                    <input type="hidden" id="inputActionHerramienta" name="action" value="insert">

                    <!-- Numero de herramienta -->
                    <div class="mb-3">
                        <label for="inputNumeroHerramienta" class="lbl-general">
                            Número de herramienta <span class="text-danger">*</span>
                        </label>
                        <input id="inputNumeroHerramienta"
                               type="text"
                               class="input-text form-control"
                               name="numero"
                               maxlength="40"
                               placeholder="Ej: 112, 023, h024"
                               required>
                        <small class="text-muted">
                            Identificador único. Puede ser numérico o alfanumérico (ej: 112, 023, h024).
                        </small>
                    </div>

                    <!-- Descripcion -->
                    <div class="mb-3">
                        <label for="inputDescripcionHerramienta" class="lbl-general">
                            Descripción <span class="text-muted">(opcional)</span>
                        </label>
                        <textarea id="inputDescripcionHerramienta"
                                  name="descripcion"
                                  class="form-control"
                                  rows="3"
                                  maxlength="255"
                                  placeholder="Descripción o notas sobre la herramienta"></textarea>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="button"
                                class="btn-general"
                                id="btnGuardarHerramienta">
                            <i class="bi bi-floppy me-1"></i> Guardar
                        </button>
                        <button type="button"
                                class="btn-cancel"
                                data-bs-dismiss="modal">
                            Cancelar
                        </button>
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
    function enviarFormularioHerramienta(id, numero, descripcion, action) {
        var formData = new FormData();
        formData.append("id",          id);
        formData.append("numero",      numero);
        formData.append("descripcion", descripcion);
        formData.append("action",      action);

        $.ajax({
            url: "../ajax/post_herramienta.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: "json",
            success: function (data) {
                if (data.success) {
                    $("#modalHerramienta").modal("hide");
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                } else {
                    sweetAlertResponse("warning", "Hubo un problema", data.message, "none");
                }
            },
            error: function (xhr, status, error) {
                sweetAlertResponse("error", "Error", "Error al enviar los datos. " + error, "none");
            }
        });
    }


    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        // GUARDAR (insert o update)
        $("#btnGuardarHerramienta").on("click", function () {

            var numero = $("#inputNumeroHerramienta").val().trim();

            if (numero === "") {
                sweetAlertResponse("warning", "Campo requerido", "El número de herramienta es obligatorio.", "none");
                return;
            }

            var id          = $("#inputIdHerramienta").val();
            var descripcion = $("#inputDescripcionHerramienta").val().trim();
            var action      = $("#inputActionHerramienta").val();
            var relaciones  = parseInt($("#spanGruposAfectados").text()) || 0;

            // Si es edicion de una herramienta relacionada, pedir confirmacion extra
            if (action === "update" && relaciones > 0) {
                Swal.fire({
                    title: "Confirmar cambio con impacto",
                    html: "Esta herramienta está asignada a <strong>" + relaciones + "</strong> grupo(s).<br>"
                        + "¿Confirma que desea guardar los cambios?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, guardar",
                    cancelButtonText: "Cancelar",
                    confirmButtonColor: "#55AD9B",
                    cancelButtonColor: "#6c757d",
                    allowOutsideClick: false
                }).then(function (result) {
                    if (result.isConfirmed) {
                        enviarFormularioHerramienta(id, numero, descripcion, action);
                    }
                });
            } else {
                enviarFormularioHerramienta(id, numero, descripcion, action);
            }
        });
    });
</script>