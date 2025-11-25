<?php?>
<style>
.status-chain {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    flex-wrap: nowrap;
    gap: 1rem;
    font-size: 2.5rem;
    color: #ccc;
}

.status-chain .icon {
    color: #ccc;
    transition: color 0.3s ease;
}

.status-chain .icon.active {
    color: #55AD9B;
}

.status-chain .label {
    position: absolute;
    top: 60px;
    font-size: 13px;
    font-weight: 500;
    color: #000;
    white-space: nowrap;
}


</style>
<!-- /////////////////////////////MODAL DETALLES DEL ESTATUS DE REQUISICION //////////////////////////////// -->
<div class="modal fade" id="modalEstatusInfo" tabindex="-1" aria-labelledby="modalEstatusLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEstatusLabel">Historial de estatus</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- contenedor oculto de visibilidad y tabla informativa -->
        <div id="contenedorDetalles" class="overflow-hidden" style="">
            <!-- tabla informativa -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle tabla-billets">
                    <thead class="table-light">
                        <tr>
                            <th>Estatus</th>
                            <th>Detalles</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="trPendiente" class="">
                            <td>Pendiente</td>
                            <td>Ingreso de requisición al sistema.</td>
                            <td id="fechaInsercion"></td>
                        </tr>
                        <tr id="trAutorizada" class="d-none">
                            <td>Autorizada</td>
                            <td id="infoAutorizo"></td>
                            <td id="fechaAutorizacion"></td>
                        </tr>
                        <tr id="trProduccion" class="d-none">
                            <td>Producción</td>
                            <td>El maquinado del sello está en proceso.</td>
                            <td id="fechaInicioMaquinado"></td>
                        </tr>
                        <tr id="trFinalizada" class="d-none">
                            <td>Finalizada</td>
                            <td>El proceso de maquinado ha concluido con éxito.</td>
                            <td id="fechaFinMaquinado"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Al hacer click en el botón principal del modal de estatus
    $(document).on('click', '.btn-estatus', function() {
        // Obtener datos del botón clickeado
        const $boton = $(this);
        // Obtener ID de requisición
        let idRequisicionActual = $boton.data('id-requisicion');
        // Mostrar el modal
        $('#modalEstatusInfo').modal('show');
        // Cargar la información de los estatus
        cargarEstatusRequisicion(idRequisicionActual);
    });
});  
function cargarEstatusRequisicion(idRequisicion) {
    $("#fechaInsercion").text('Cargando...');
    $("#infoAutorizo").text('Cargando...');
    $("#fechaAutorizacion").text('Cargando...');
    $("#fechaInicioMaquinado").text('Cargando...');
    $("#fechaFinMaquinado").text('Cargando...');
    // Realizar una solicitud AJAX para obtener los datos de estatus
    $.ajax({
        url: '../ajax/get_requisicion.php',
        method: 'GET',
        data: { id_requisicion: idRequisicion },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $("#fechaInsercion").text(response.data.fecha_insercion || 'No disponible');
                $("#trAutorizada,#trProduccion,#trFinalizada").removeClass('d-none');
                switch(response.data.estatus) {
                    case 'Pendiente':
                        $("#trAutorizada,#trProduccion,#trFinalizada").addClass('d-none');
                        break;
                    case 'Autorizada':
                        $("#trProduccion,#trFinalizada").addClass('d-none');
                        break;
                    case 'Producción' || 'En Producción':
                        $("#trFinalizada").addClass('d-none');
                        break;
                    case 'Finalizada' || 'Completada':
                        $("#trAutorizada,#trProduccion,#trFinalizada").removeClass('d-none');
                        break;
                    default:
                        // Si el estatus no coincide con ninguno, ocultar todas las filas excepto Pendiente
                        $("#trPendiente").removeClass('d-none');
                        $("#trAutorizada").addClass('d-none');
                        $("#trProduccion").addClass('d-none');
                        $("#trFinalizada").addClass('d-none');
                    break;
                }
           
                $("#infoAutorizo").text(response.data.autorizo ? 'Autorizado por ' + response.data.autorizo : 'No disponible');
                $("#fechaAutorizacion").text(response.data.fecha_autorizacion || 'No disponible');
            
                $("#fechaInicioMaquinado").text(response.data.fecha_entrega_barras || 'No disponible');
        
                $("#fechaFinMaquinado").text(response.data.fin_maquinado || 'No disponible');
                
            } else {
                $("#fechaInsercion").text('No disponible');
                $("#infoAutorizo").text('No disponible');
                $("#fechaAutorizacion").text('No disponible');
                $("#fechaInicioMaquinado").text('No disponible');
                console.error('Error al obtener estatus:', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en la solicitud AJAX:', error);
        }
    });
}
</script>