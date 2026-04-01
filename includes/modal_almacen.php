<!-- Modal para agregar/editar registro -->
<div class="modal fade" id="modalAlmacen" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="titleModalAlmacen" class="modal-title" >Agregar registro</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAlmacen">                        
                    <input type="hidden" id="inputIdAlmacen" name="id">
                    <input type="hidden" id="inputActionAlmacen" name="action" value="insert">
   
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:100%;">
                            <label for="inputNombreAlmacen" class="lbl-general">Nombre de almacen <span class="text-danger">*</span></label>
                            <input id="inputNombreAlmacen" type="text" class="input-text"  name="almacen" required>
                        </div> 
                    </div>
                    <div class="d-flex justify-content-between mb-3">

                        <div class="" style="width:100%;">
                            <label for="inputDescripcionAlmacen" class="lbl-general">Descripción del almacen <span class="text-danger">*</span></label>
                            <textarea id="inputDescripcionAlmacen" name="descripcion" class="form-control" rows="3" required></textarea>
                        
                        </div>                        
                    </div>

                    <button id="btnGuardarAlmacen" type="button" class="btn-general">Guardar</button>
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
    function ajaxBackendAlmacen(idAlmacen, accion){
        var actionForm=accion;    
        var dataId = idAlmacen;
        var inputNombre=$('#inputNombreAlmacen').val() || "";
        var inputDescripcion=$('#inputDescripcionAlmacen').val() || "";
        
        // Crear FormData para enviar archivos
        var formData = new FormData();
        formData.append('id', dataId);
        formData.append('almacen', inputNombre);
        formData.append('descripcion', inputDescripcion);
        formData.append('action', actionForm);


        $.ajax({
            url: '../ajax/post_almacen.php',
            type: 'POST',
            data: formData,
            processData: false,  // Importante para FormData
            contentType: false,  // Importante para FormData
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
                sweetAlertResponse("error", "Error", "Error al enviar datos. " + error, "self");
            }
        });
    }



    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================  
    $(document).ready(function(){
        // CAMBIAR A add AL CLICK AGREGAR REGISTRO
        $(".btnAgregarAlmacen").on("click", function(){
            $("#formAlmacen")[0].reset();
            $('#inputActionAlmacen').val('insert');
            $("#titleModalAlmacen").text("Agregar registro");
            $("#formAlmacen").removeAttr("target");
        });
        // CLICK A EDITAR UN REGISTRO
        $('#almacenesTable').on('click', '.edit-btn', function() {
            // Limpiar formulario
            $('#formAlmacen')[0].reset();

            var dataId = $(this).data('id');
            var dataAlmacen = $(this).attr('data-almacen');
            var dataDescripcion = $(this).attr('data-descripcion');

            // Llenar solo los campos que corresponden
            $('#inputIdAlmacen').val(dataId);
            $('#inputNombreAlmacen').val(dataAlmacen);
            $('#inputDescripcionAlmacen').val(dataDescripcion);

            $('#inputActionAlmacen').val('update');
            $('#modalAlmacen').modal('show');
            $("#titleModalAlmacen").text("Editar registro");

        });
        // ENVIAR FORMULARIO
        $("#btnGuardarAlmacen").on("click", function(){
            var inputIdAlmacen = $('#inputIdAlmacen').val();
            var actionForm=$('#inputActionAlmacen').val();
            ajaxBackendAlmacen(inputIdAlmacen, actionForm);
        });        

    });
</script>