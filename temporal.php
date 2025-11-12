<!-- contenedor del body -->
<div class="modal-body d-flex col-12 flex-column flex-md-row justify-content-between" style="min-height:400px; overflow-y:auto;">
    <div id="containerBodyModalBillets_m<?= $i ?>" 
            style="overflow-y:auto; width:<?php if ($tipoUsuario == 3 || $tipoUsuario == 4 || $tipoUsuario == 5) {echo '100';}else{echo '62';}?>%;">
        
        <!-- Contenedor Simulador Mejorado -->
        <div id="containerSimulador__m<?= $i ?>" class="d-flex flex-column mb-4 d-none">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-calculator me-2 text-primary fs-5"></i>
                <h5 class="mb-0 fw-semibold">Simular cotización</h5>
            </div>
            
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="inputSimulacionDI_m<?= $i ?>" class="form-label fw-medium">
                        <i class="bi bi-circle me-1 text-info"></i>Diámetro Interior (mm)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-arrow-left-right text-secondary"></i>
                        </span>
                        <input id="inputSimulacionDI_m<?= $i ?>" 
                               type="number" 
                               class="form-control border-start-0" 
                               placeholder="Ej: 45"
                               min="0"
                               step="1">
                    </div>
                </div>
                
                <div class="col-md-5">
                    <label for="inputSimulacionDE_m<?= $i ?>" class="form-label fw-medium">
                        <i class="bi bi-record-circle me-1 text-warning"></i>Diámetro Exterior (mm)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-arrow-left-right text-secondary"></i>
                        </span>
                        <input id="inputSimulacionDE_m<?= $i ?>" 
                               type="number" 
                               class="form-control border-start-0" 
                               placeholder="Ej: 65"
                               min="0"
                               step="1">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <button type="button" id="btnBuscarClave_m<?= $i ?>" class="btn btn-primary w-100 h-100">
                        <i class="bi bi-calculator me-1"></i>Calcular
                    </button>
                </div>
            </div>
            
            <div class="mt-2">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>Ingrese los diámetros en milímetros para simular la cotización
                </small>
            </div>
        </div>
        
        <table id="tablaBillets_m<?= $i ?>" class="table table-bordered border border-2 tabla-billets">
            <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col">Clave</th>
                    <th scope="col">Aprovechamiento</th>
                    <th scope="col">Stock MM</th>
                    <th scope="col">Estatus</th>
                    <th scope="col">Piezas</th>
                    <th scope="col">Medida</th>
                    <th scope="col">Lote/pedimento</th>
                </tr>
            </thead>
            <tbody>
    
            </tbody>
        </table>
    </div>
    
    <div id="container38_m<?= $i ?>" 
            class="d-flex flex-column <?php if ($tipoUsuario == 4 || $tipoUsuario == 5 ) {echo 'd-none';}?>" 
            style="<?php if ($tipoUsuario != 3 && $tipoUsuario != 4 && $tipoUsuario != 5) {echo 'width:38%';} ?>;">
        <button id="btnQuitarCircle_m<?= $i ?>" class="btn-close align-self-end d-none" style="padding-right:5%;"></button>
        
        <table class="table table-bordered text-secondary table-dimensiones-necesarias <?php if ($tipoUsuario == 3 || $tipoUsuario == 4 || $tipoUsuario == 5) {echo 'd-none';}?>" 
                style="font-size:12px !important; margin-bottom:5px;">
            <thead class="table-dark text-white fw-bold" >
                <tr>
                    <th style="background-color:#000;">Dimensión</th>
                    <th style="background-color:#3657c4;">Medida aproximada</th>
                    <th style="background-color:#000;">Necesario para CNC</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Altura</td>
                    <td><span id="spanAlturaCliente_m<?= $i ?>">X</span> mm</td>
                    <td><span id="spanAlturaNecesario_m<?= $i ?>">X</span> mm</td>
                </tr>
                <tr>
                    <td>Diametro Interior</td>
                    <td><span id="spanDiCliente_m<?= $i ?>">X</span> mm</td>
                    <td><span id="spanDiNecesario_m<?= $i ?>">X</span> mm</td>
                </tr>
                <tr>
                    <td>Diametro Exterior</td>
                    <td><span id="spanDeCliente_m<?= $i ?>">X</span> mm</td>
                    <td><span id="spanDeNecesario_m<?= $i ?>">X</span> mm</td>
                </tr>
            </tbody>
        </table>

        <div id="containerCircleBillet_m<?= $i ?>" 
            class="d-flex flex-column justify-content-center align-items-center <?php if ($tipoUsuario == 3 || $tipoUsuario == 4) {echo 'd-none';}?>">
            <div class="d-flex justify-content-center align-items-start">
            <svg 
                id="circuloSvg_m<?= $i ?>"
                width="<?= ($tipoUsuario == 3 || $tipoUsuario == 4) ? '330' : '260' ?>"
                height="<?= ($tipoUsuario == 3 || $tipoUsuario == 4) ? '330' : '260' ?>"
                viewBox="0 0 100 100">
            </svg>
            </div>
            <div class="d-flex justify-content-center align-items-start" style="font-size:18px; font-weight:700;">
                <p>Porcentaje de aprovechamiento: <span id="spanPorcentAprov_m<?= $i ?>">0.00</span>%</p>
            </div>
        </div>
    </div>
</div>