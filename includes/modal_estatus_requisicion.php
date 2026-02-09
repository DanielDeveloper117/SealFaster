<style>
    /* Estilos de la cadena de estatus */
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

    /* Estilo para filas que aparecen con suavidad */
    .fila-estatus {
        transition: all 0.3s ease;
    }
</style>

<div class="modal fade" id="modalEstatusInfo" tabindex="-1" aria-labelledby="modalEstatusLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEstatusLabel">Historial de estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="contenedorDetalles" class="overflow-hidden">
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle tabla-billets">
                            <thead class="table-light">
                                <tr>
                                    <th>Estatus</th>
                                    <th>Detalles</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyEstatus">
                                <tr id="trPendiente" class="fila-estatus">
                                    <td><span class="span-status-yellow">Pendiente</span></td>
                                    <td>Ingreso de requisición al sistema.</td>
                                    <td class="dato-fecha"></td>
                                </tr>
                                <tr id="trAutorizada" class="fila-estatus d-none">
                                    <td><span class="span-status">Autorizada</span></td>
                                    <td class="dato-extra"></td>
                                    <td class="dato-fecha"></td>
                                </tr>
                                <tr id="trProduccion" class="fila-estatus d-none">
                                    <td><span class="span-status">Producción</span></td>
                                    <td>Se asignó la máquina, entrega de barras pendiente.</td>
                                    <td class="dato-fecha"></td>
                                </tr>
                                <tr id="trMaquinado" class="fila-estatus d-none">
                                    <td><span class="span-status">En maquinado</span></td>
                                    <td>Barras entregadas, el maquinado del sello está en proceso.</td>
                                    <td class="dato-fecha"></td>
                                </tr>
                                <tr id="trFinalizada" class="fila-estatus d-none">
                                    <td><span class="span-status">Finalizada</span></td>
                                    <td>El proceso de maquinado ha concluido con éxito.</td>
                                    <td class="dato-fecha"></td>
                                </tr>
                                <tr id="trCompletada" class="fila-estatus d-none">
                                    <td><span class="span-status">Completada</span></td>
                                    <td>El nuevo stock de las barras ha sido actualizado.</td>
                                    <td class="dato-fecha"></td>
                                </tr>
                                <tr id="trDetenida" class="fila-estatus d-none">
                                    <td><span class="span-status-red">Detenida</span></td>
                                    <td>La producción se ha detenido/cancelado. <br><strong>Justificación:</strong> <span class="dato-extra"></span></td>
                                    <td class="dato-fecha"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Escuchar click en botones de estatus
    $(document).on('click', '.btn-estatus', function() {
        const idRequisicionActual = $(this).data('id-requisicion');
        $('#modalEstatusInfo').modal('show');
        cargarEstatusRequisicion(idRequisicionActual);
    });
});

function cargarEstatusRequisicion(idRequisicion) {
    // Reset visual: ocultamos todo excepto la primera fila y ponemos carga
    $(".fila-estatus").not("#trPendiente").addClass('d-none');
    $(".dato-fecha").text('Cargando...');
    $(".dato-extra").text('');

    $.ajax({
        url: '../ajax/get_requisicion.php',
        method: 'GET',
        data: { id_requisicion: idRequisicion },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const d = response.data;

                /**
                 * MAPEO DE DATOS (Data Mapping)
                 * Definimos un array de objetos con la lógica de cada fila.
                 * Si la 'fecha' existe, la fila se mostrará automáticamente.
                 */
                const esquemaEstatus = [
                    { id: "#trPendiente",  fecha: d.fecha_insercion },
                    { id: "#trAutorizada", fecha: d.fecha_autorizacion, extra: d.autorizo ? 'Autorizado por ' + d.autorizo : 'Información de autorizador no disponible' },
                    { id: "#trProduccion", fecha: d.inicio_maquinado },
                    { id: "#trMaquinado",  fecha: d.fecha_entrega_barras },
                    { id: "#trFinalizada", fecha: d.fin_maquinado },
                    { id: "#trCompletada", fecha: d.fin_maquinado },
                    { id: "#trDetenida",   fecha: d.fecha_detencion,    extra: d.justificacion_detencion }
                ];

                // Iteramos el esquema para aplicar los cambios al DOM
                esquemaEstatus.forEach(item => {
                    const $fila = $(item.id);
                    
                    // Condición simplificada: si hay fecha válida, mostrar
                    if (item.fecha && item.fecha !== '0000-00-00 00:00:00') {
                        $fila.removeClass('d-none');
                        $fila.find(".dato-fecha").text(item.fecha);
                        
                        // Si hay información extra (autorizador o justificación)
                        if (item.extra) {
                            $fila.find(".dato-extra").text(item.extra);
                        }
                    } else {
                        // Si no hay fecha y no es el pendiente, asegurar que esté oculto
                        if(item.id !== "#trPendiente") $fila.addClass('d-none');
                        else $fila.find(".dato-fecha").text('No disponible');
                    }
                });

            } else {
                alert("Error al obtener los datos: " + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            $(".dato-fecha").text('Error al cargar');
        }
    });
}
</script>