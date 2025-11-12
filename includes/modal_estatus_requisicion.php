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
#contenedorDetalles {
    transition: max-height 0.6s ease-in-out;
}

</style>
<!-- /////////////////////////////MODAL DETALLES DEL ESTATUS DE REQUISICION //////////////////////////////// -->
<div class="modal fade" id="modalEstatusInfo" tabindex="-1" aria-labelledby="modalEstatusLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header">
        <h5 class="modal-title" id="modalEstatusLabel">Detalles de los estatus de requisiciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <!-- boton mostrar/ocultar detalles -->
        <div class="text-start my-3">
            <button id="toggleDetalles" class="btn btn-outline-secondary btn-sm">
                Ver detalles del estatus de requisiciones
            </button>
        </div>
        <!-- contenedor oculto de visibilidad y tabla informativa -->
        <div id="contenedorDetalles" class="overflow-hidden" style="max-height: 0; transition: max-height 0.6s ease;">
            <!-- visibilidad -->
            <div class="mb-4">
                <h6 class="fw-bold">Visibilidad de Requisiciones</h6>
                <ul>
                    <li><strong>Gerencia y dirección:</strong> pueden ver <em>todas</em> las requisiciones.</li>
                    <li><strong>CNC:</strong> solo verán las requisiciones cuyo estatus sea a partir de autorizada.</li>
                    <li><strong>Vendedor:</strong> solo ve requisiciones que ha creado con su usuario.</li>
                </ul>
            </div>
            <!-- tabla informativa -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle tabla-billets">
                    <thead class="table-light">
                        <tr>
                            <th>Estatus</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Pendiente</td>
                            <td>Ventas gerencia o dirección deben autorizar la requisición.</td>
                        </tr>
                        <tr>
                            <td>Autorizada</td>
                            <td>Requisición autorizada. Inventarios debe dar salida a billets.</td>
                        </tr>
                        <tr>
                            <td>Producción</td>
                            <td>El maquinado del sello está pendiente de comenzar.</td>
                        </tr>
                        <tr>
                            <td>Maquinado CNC</td>
                            <td>El sello está siendo maquinado actualmente.</td>
                        </tr>
                        <tr>
                            <td>Finalizada</td>
                            <td>El proceso de maquinado ha concluido con éxito.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- progreso de estatus -->
        <div class="text-center mt-4 mb-4" style="font-size: 12px !important;">
            <div id="cadenaEstatusModal" class="status-chain" style="overflow-x:auto;min-height:120px;">
                <!-- Estatus: Pendiente -->
                <div class="d-flex flex-column align-items-center position-relative">
                    <i class="bi bi-check-circle-fill icon" data-step="1"></i>
                    <span class="label">Pendiente</span>
                </div>
                <i class="bi bi-dash icon" data-step="1-2"></i>

                <!-- Estatus: Autorizada -->
                <div class="d-flex flex-column align-items-center position-relative">
                    <i class="bi bi-check-circle-fill icon" data-step="2"></i>
                    <span class="label">Autorizada</span>
                </div>
                <i class="bi bi-dash icon" data-step="2-3"></i>

                <!-- Estatus: Produccion -->
                <div class="d-flex flex-column align-items-center position-relative">
                    <i class="bi bi-check-circle-fill icon" data-step="3"></i>
                    <span class="label">Producción</span>
                </div>
                <i class="bi bi-dash icon" data-step="3-4"></i>

                <!-- Estatus: En producción -->
                <div class="d-flex flex-column align-items-center position-relative">
                    <i class="bi bi-check-circle-fill icon" data-step="4"></i>
                    <span class="label">Maquinado CNC</span>
                </div>
                <i class="bi bi-dash icon" data-step="4-5"></i>

                <!-- Estatus: Finalizada -->
                <div class="d-flex flex-column align-items-center position-relative">
                    <i class="bi bi-check-circle-fill icon" data-step="5"></i>
                    <span class="label">Finalizada</span>
                </div>
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
    const toggleBtn = document.getElementById('toggleDetalles');
    const contenedor = document.getElementById('contenedorDetalles');

    let abierto = false;

    toggleBtn.addEventListener('click', function () {
        if (!abierto) {
            contenedor.style.maxHeight = contenedor.scrollHeight + "px";
            toggleBtn.textContent = "Ver menos detalles";
            abierto = true;
        } else {
            contenedor.style.maxHeight = "0";
            toggleBtn.textContent = "Ver detalles de los estatus de requisiciones";
            abierto = false;
        }
    });

});  
function pintarCadenaEstatus(estatusActual) {
    const orden = ['Creada', 'Pendiente', 'Autorizada', 'Producción', 'En producción', 'Finalizada'];
    const index = orden.findIndex(e => e.toLowerCase() === estatusActual.toLowerCase());

    const icons = document.querySelectorAll('#cadenaEstatusModal .icon');
    icons.forEach((icon) => {
        const step = icon.dataset.step;
        if (step !== undefined) {
            const isCircle = !step.includes('-');
            const pos = isCircle ? parseInt(step) : parseInt(step.split('-')[0]);
            if (pos <= index) {
                icon.classList.add('item-chain-active');
            } else {
                icon.classList.remove('item-chain-active');
            }
        }
    });
}
</script>