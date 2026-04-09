<?php for ($i = 1; $i <= $cantidadMateriales; $i++): ?>
    <form id="estimateForm_m<?= $i ?>" method="post"> 
        <section id="sectionContainerMaterial_m<?= $i ?>" class="section-container d-none">
            <?php
                $numeroMaterial="";
                if($cantidadMateriales == 1){
                        $numeroMaterial="0";
                }else{
                    if($i == 1){
                        $numeroMaterial="1";
                        echo '<h2>Componente 1</h2>';
    
                    }
                    if($i > 1){
                        $numeroMaterial = $i;
                        echo '<h2>Componente '.$i.'</h2>';
                    }
                }
            ?>
            <div class="d-flex col-12 flex-column gap-1 flex-md-row justify-content-between align-items-md-start align-items-center">
                <?php
                    if (file_exists($imagePath)) {
                        $imgMaterial=$imagePath . '/' . $perfilOriginal . '_'.$numeroMaterial.'.jpg';
                        echo '<div class="d-flex col-8 col-md-3 mb-3 justify-content-center">';
                        echo '<a class="img-sello" href="select_perfil.php?fam=' . $familia . '" title="Click para cambiar el perfil">';
                        echo '<img id="imagenMaterial_m'. $i.'" src="'. $imgMaterial . '" alt="' . htmlspecialchars($perfilOriginal) . '" class="border-gray img-fluid">';
                        echo '</a></div>';
                    } else {
                        echo "<h2>Imagen no encontrada</h2>";
                    }
                ?>
                <div class="controles pb-5 d-flex col-12 col-md-9 flex-column border-gray">
                    <div class="d-flex justify-content-between">
                        <h4>Selección de billets para el maquinado</h4>
                        <?php if ($cantidadMateriales != 1):  ?>
                        <div id="containerOmitirElemento_m<?= $i ?>" class="d-flex align-items-center gap-2">
                            <input
                                id="checkboxOmitirElemento_m<?= $i ?>"
                                type="checkbox"
                                class="form-check-input"
                                val="<?= $i ?>"
                                aria-label="Omitir el elemento del perfil"
                                style="width: 25px; height: 25px;"
                            />
                            <label for="checkboxOmitirElemento_m<?= $i ?>" class="mb-0">
                                Omitir elemento
                            </label>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div id="containerPags_m<?= $i ?>" class="d-flex col-12 justify-content-center">
                        <div id="pag1_m<?= $i ?>" class="d-flex col-12 flex-column flex-md-row justify-content-between mt-2" >
                      
                            <input type="hidden" id="diametro_interior_mm_m<?= $i ?>" class="input-estimador" step="0.01" min="0"  name="diametro_interior_mm"  required  placeholder="Ingrese las dimensiones">
                            <input type="hidden" id="diametro_interior_inch_m<?= $i ?>" class="input-estimador" step="0.0001" min="0"  name="diametro_interior_inch" required  placeholder="Ingrese las dimensiones">
                        
                            <input type="hidden" id="diametro_exterior_mm_m<?= $i ?>"step="0.01" min="0" class="input-estimador" name="diametro_exterior_mm" required  placeholder="Ingrese las dimensiones">
                            <input type="hidden" id="diametro_exterior_inch_m<?= $i ?>" step="0.0001" min="0" class="input-estimador" name="diametro_exterior_inch" required  placeholder="Ingrese las dimensiones">
                        
                            <input type="hidden" id="altura_mm_m<?= $i ?>" class="input-estimador" step="0.01" min="0" name="altura_mm" required placeholder="Ingrese las dimensiones">
                            <input type="hidden" id="altura_inch_m<?= $i ?>" class="input-estimador" step="0.0001" min="0"  name="altura_inch" required placeholder="Ingrese las dimensiones">
                    
                
                            <div class="col-12 col-md-5 d-flex flex-column">
                                
                                <div class="mb-2">
                                    <label for="selectorMaterial_m<?= $i ?>" class="label-estimador">Material</label>
                                    <select id="selectorMaterial_m<?= $i ?>" class="" name="material" required>
                                        <option value="" disabled selected>Seleccione un material</option>
                                    </select>
                                </div>

                                <div class="mb-2 d-none">
                                    <label for="selectorProveedor_m<?= $i ?>" class="label-estimador">Proveedor</label>
                                    <select id="selectorProveedor_m<?= $i ?>" class="" name="proveedor" required disabled>
                                        <option value="" disabled selected>Seleccione un proveedor</option>
                                    </select>
                                </div>
                                
                                <div class="d-flex flex-column mb-4">
                                    <label for="inputCantidad_m<?= $i ?>" class="label-estimador">Cantidad</label>
                                    <input id="inputCantidad_m<?= $i ?>" type="number" class="input-estimador" value="" name="cantidad" step="1" min="1" oninput="this.value = this.value.replace(/\D+/g, '')" placeholder="Cantidad de piezas">
                                </div>

                                <div class="d-flex flex-column mb-1 p-2 font-monospace border border-dark-subtitle bg-white bg-opacity-10 rounded-2">
                                    <label for="ulDiametrosTeoricos_m<?= $i ?>" class="label-estimador">Diámetros Teóricos</label>
                                    <ul id="ulDiametrosTeoricos_m<?= $i ?>">
                                        <li>DE + Tolerancia: 
                                            <span id="calculoTeoricoDE_m<?= $i ?>" class="fw-bold">0</span> +
                                            <span id="toleranciaBarraDE_m<?= $i ?>" class="fw-bold">0</span> mm
                                        </li>
                                        <li>DI - Tolerancia: 
                                            <span id="calculoTeoricoDI_m<?= $i ?>" class="fw-bold">0</span> -
                                            <span id="toleranciaBarraDI_m<?= $i ?>" class="fw-bold">0</span> mm
                                        </li>
                                    </ul>
                                    <label for="ulAlturaTeorica_m<?= $i ?>" class="label-estimador">Altura Teórica/pz</label>
                                    <ul id="ulAlturaTeorica_m<?= $i ?>">
                                        <li>H + Desbaste: 
                                            <span id="calculoTeoricoH_m<?= $i ?>" class="fw-bold">0</span> +
                                            <span id="toleranciaHpzMaterial_m<?= $i ?>" class="fw-bold">0</span> mm
                                        </li>
                                    </ul>
                                </div>

                                <div id="containerErrorDimensiones_m<?= $i ?>" class="text-faltan">
                                    <span></span>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 d-flex flex-column">
                                <div class="d-flex flex-column align-items-start" style="height:100%;">
                                    <label for="btnBillets_m<?= $i ?>" class="label-estimador">Seleccionar claves de Billet</label>
                                    <button id="btnBillets_m<?= $i ?>" type="button" class="btn-disabled mb-2" data-bs-toggle="modal" data-bs-target="#modalBillets_m<?= $i ?>" disabled>Ver billets disponibles</button>
                                    <button id="btnBilletsSimulacion_m<?= $i ?>" type="button" class="d-none btn-disabled mb-2" data-bs-toggle="modal" data-bs-target="#modalBilletsSimulacion_m<?= $i ?>" disabled>Ver barras coincidentes</button>
                                    <!-- <span>Desbaste añadido por pieza: 2.5 mm</span> -->
                                    <div class="d-flex flex-column font-monospace">
                                        <div id="containerFaltanSiNo_m<?= $i ?>" class="text-faltan">
                                            <span>Milímetros necesarios: </span>
                                            <span id="spanMilimetrosNecesarios_m<?= $i ?>">0</span>
                                            <span>| Faltan: </span>
                                            <span id="spanSellosRestantes_m<?= $i ?>">0</span>
                                            <span> pz</span>
                                        </div>
                                        <div id="containerMilimetrosSobrantes_m<?= $i ?>" class="d-none mt-1">
                                            <span>Sobran: </span>
                                            <span id="spanMilimetrosSobrantes_m<?= $i ?>">0</span>
                                            <span> milímetros</span>
                                        </div>
                                        <div id="containerBarraSeleccionadaSimulacion_m<?= $i ?>" class="d-none text-no-faltan">
                                            <span></span>
                                        </div>
                                    </div>
                                    <div class="mb-3" style="width:100%; overflow-x:auto;">
                                        <table id="miniTableBillets_m<?= $i ?>" class="table table-bordered border border-2 tabla-billets">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Clave</th>
                                                    <th scope="col">Lote</th>
                                                    <th scope="col">Stock MM</th>
                                                    <th scope="col">Medida</th>
                                                    <th scope="col">Piezas</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                        
                                    </div>
                                    <button id="btnLimpiarSeleccion_m<?= $i ?>" type="button" class="btn-disabled mt-0" disabled style="width:40%;">Limpiar billets</button>
                                </div>
                
                                <div class="d-flex align-items-end mt-4">
                                    <button id="btnSiguiente_m<?= $i ?>" type="button" class="btn-disabled" disabled>Siguiente >></button>
                                </div>
                            </div>
                        </div>

                        <div id="pag2_m<?= $i ?>" class="d-none col-12 flex-column justify-content-center align-items-center mt-4">
                            <div class="col-11 d-flex flex-column">
                                <div class="mb-2" style="overflow-y: auto;
                                                        width: 100%;
                                                        overflow-x: auto;
                                                        height: 100px !important;">
                                    <table id="miniTableCostoBarra_m<?= $i ?>" class="table table-bordered border border-2 tabla-billets">
                                        <thead>
                                            <tr>
                                                <th scope="col">Clave</th>
                                                <th scope="col">Lote</th>
                                                <th scope="col">Stock MM</th>
                                                <th scope="col">Medida</th>
                                                <th scope="col">Piezas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                </div> 
                                
                                <div class="d-flex dis-none align-items-center">
                                    <h5>Total unitarios: $</h5>
                                    <input type="number" id="inputTotalUnitarios_m<?= $i ?>" class="h5 input-span" value="0.00" name="total_unitarios" placeholder="0" style="margin-right:5px;font-size:1.25rem;">
                                </div>

                                <div class="d-flex justify-content-start align-items-center mb-2">
                                    <span id="spanCantidad_m<?= $i ?>" style="font-size: 20px;">0</span>
                                </div>

                                <div class="d-flex align-items-center">
                                    <h4>Descuentos: $</h4>
                                    <input type="number" id="inputTotalDescuentos_m<?= $i ?>" class="h5 input-span" value="0.00" name="total_descuentos" placeholder="0" style="margin-right:5px;font-size:1.25rem;">
                                </div>

                                <div class="mt-4">
                                    <div class="d-flex justify-content-start align-items-baseline">
                                        <h3 style="margin:0px;">Total: $</h3>
                                        <input type="number" id="inputTotalMaterial_m<?= $i ?>" class="input-span-total-material" value="" name="total_material" step="0.01" min="0" placeholder="Cargando..." style="">
                                    </div>
                                </div>
                               
                                <input type="hidden" id="precioBarra_m<?= $i ?>" class="input-estimador" value="" name="precio_barra" step="0.01" min="0" placeholder="Calculo de costos de barra pendiente">
                                <input type="hidden" id="inputCostoMinimoUnidad_m<?= $i ?>" class="input-estimador" value="" name="costo_minimo_unidad">
                                <input type="hidden" id="inputCostoOperacion_m<?= $i ?>" class="input-estimador" value="" name="costo_operacion" step="0.01" min="0" placeholder="Intente con otra clave">
                                <input type="hidden" id="inputCostoHerramienta_m<?= $i ?>" class="input-estimador" value="" name="costo_herramienta" step="0.01" min="0" placeholder="Intente con otra clave">
                                <input type="hidden" id="inputCostoPreparacionDI_m<?= $i ?>" class="input-estimador" value="" name="costo_preparacion_di_barra" step="0.01" min="0" placeholder="Intente con otra clave">
                                <input type="hidden" id="inputCostoResorte_m<?= $i ?>" class="input-estimador" value="" name="costo_resorte" step="0.01" min="0" placeholder="No aplica">

                                <div class="d-flex flex-column flex-md-row gap-5 justify-content-between align-items-end" >
                                    <button id="btnAtras_m<?= $i ?>" type="button" class="btn-general mt-4"><< Atras</button>
                                    <button id="btnNoListo_m<?= $i ?>" type="button" class="btn-general d-none mt-4">Habilitar edición<i class='bi bi-pencil' style='color:#fff; margin-left: 5px;'></i></button>
                                    <button id="btnListo_m<?= $i ?>" type="button" class="btn-disabled mt-4" disabled>Marcar como completado<i class='bi bi-check2-circle' style='color:#fff; margin-left: 5px;'></i></button>
                                </div>
                            </div>
                            
                            <input type="hidden" id="inputDescuentoCliente_m<?= $i ?>" class="input-span descuento-cliente" value="0.0" name="desc_cliente" placeholder="0" style="margin-right:5px;">
                            <input type="hidden" id="inputPorcentDescuentoCliente_m<?= $i ?>" class="input-span-porcent porcent-descuento-cliente" value="" name="descuento_cliente_porcent" placeholder="Cargando...">
                            <input type="hidden" id="inputDescuentoRC_m<?= $i ?>" class="input-span" value="" name="desc_cantidad" placeholder="Cargando..." style="margin-right:5px;">
                            <input type="hidden" id="inputPorcentDescuentoRC_m<?= $i ?>" class="input-span-porcent" value="" name="descuento_rc_porcent" placeholder="Cargando...">
                            <input type="hidden" id="inputDescuentoMayoreo_m<?= $i ?>" class="input-span" value="0.0" name="desc_mayoreo" placeholder="0" style="margin-right:5px;">
                            <input type="hidden" id="inputPorcentDescuentoMayoreo_m<?= $i ?>" class="input-span-porcent" name="descuento_mayoreo_porcent" placeholder="0">
                          
                        </div>
                    </div>
                    <div id="containerAltPags_m<?= $i ?>" class="d-none">
                        <span class="text-truncate fst-italic">Este elemento fue omitido para cotizar</span>
                    </div>
                </div>
            </div>
        </section>
        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['id']; ?>">
        <input type="hidden" id="totalInput_m<?= $i ?>" name="total">
        <input type="hidden" id="colPrecio_m<?= $i ?>" name="col_precio">
        <input type="hidden" id="colMaxUsable_m<?= $i ?>" name="col_max_usable">
        <textarea id="inputClaves_m<?= $i ?>" class="d-none" name="claves" readonly></textarea>
        <textarea id="inputBillets_m<?= $i ?>" class="d-none" name="billets" readonly></textarea>
        <textarea id="inputBilletsLotes_m<?= $i ?>" class="d-none" name="billets_lotes" readonly></textarea>
        <textarea id="inputBilletsString_m<?= $i ?>" class="d-none" name="billets_string" readonly></textarea>
        <textarea id="inputBilletsManualmente_m<?= $i ?>" class="d-none" name="billets_manualmente" readonly></textarea>
        <input type="hidden" value="<?php echo $selloOriginal; ?>" name="perfil_sello">
        <input type="hidden" id="seraEnviado_m<?= $i ?>" value="no">
        <input type="hidden" class="id-cotizacion" name="id_cotizacion">
        <input id="cantidadMaterial_m<?= $i ?>" type="hidden" value="<?= $i ?>" name="cantidad_material">
        <input type="hidden" value="Cotización" name="estatus_completado">
        <input type="hidden" class="vendedor-input" name="vendedor">
        <input type="hidden" name="img" value="<?php echo $imgMaterial; ?>">
        <input type="hidden" class="cliente-nombre" name="cliente">
        <input type="hidden" class="cliente-tipo" name="tipo_cliente">
        <input type="hidden" class="cliente-codigo" name="codigo_cliente">
        <input type="hidden" class="cliente-correo" name="correo_cliente">
        <input type="hidden" class="tipo-medida" name="tipo_medida">
        <input type="hidden" class="familia-perfil" name="familia_perfil">

        <input type="hidden" id="inputAlturaCaja_m<?= $i ?>" step="0.01" min="0" value="0.00" name="altura_caja_mm" required>
        <input type="hidden" id="inputAlturaEscalon_m<?= $i ?>" step="0.01" min="0" value="0.00" name="altura_escalon_mm" required>
        <input type="hidden" id="inputAlturaH2_m<?= $i ?>" step="0.01" min="0" value="0.00" name="altura_h2_mm" required>
        <input type="hidden" id="inputAlturaH3_m<?= $i ?>" step="0.01" min="0" value="0.00" name="altura_h3_mm" required>

    </form>

    <!-- MODAL DE SELECCIONAR BILLETS -->
    <div class="modal fade" id="modalBillets_m<?= $i ?>" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" >
        <!-- Contenedor del header, body y footer del modal -->
        <div class="modal-dialog" style="max-width: 95% !important;">
            <div class="modal-content" style="height:95%;">
                <!-- contenedor del titulo -->
                <div class="modal-header justify-content-between">
                    <h5 id="titleClavesCoincidentes_m<?= $i ?>" class="modal-title text-success">Claves coincidentes en inventario CNC, seleccione un billet.</h5>
                    <button id="btnCerrarModalBillets_m<?= $i ?>" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <!-- contenedor del body -->
                <div class="modal-body d-flex col-12 flex-column flex-md-row justify-content-between" style="min-height:400px; overflow-y:auto;">
                    <div id="containerBodyModalBillets_m<?= $i ?>" 
                            style="overflow-y:auto; width:<?php if ($lider_usuario == 3 || $lider_usuario == 4 || $lider_usuario == 5) {echo '100';}else{echo '62';}?>%;">
                        <!-- Contenedor Busqueda Manual -->
                        <div id="containerBusquedaManual__m<?= $i ?>" class="d-flex flex-column mb-4 d-none">
                            <div class="d-flex align-items-center mb-3">
                                <h5 class="mb-0 fw-semibold">Buscar otros billets manualmente</h5>
                            </div>
                            
                            <div class="d-flex gap-2 px-3 align-items-end">
                                <div class="">
                                    <label for="inputBusquedaManualDI_m<?= $i ?>" class="form-label fw-medium">
                                        Diámetro Interior
                                    </label>
                                    <div class="input-group">
                                        <input id="inputBusquedaManualDI_m<?= $i ?>" 
                                            type="number" 
                                            class="input-text border-start-0" 
                                            placeholder="Ej: 45"
                                            min="0"
                                            step="1">
                                    </div>
                                </div>
                                
                                <div class="">
                                    <label for="inputBusquedaManualDE_m<?= $i ?>" class="form-label fw-medium">
                                        Diámetro Exterior
                                    </label>
                                    <div class="input-group">
                                        <input id="inputBusquedaManualDE_m<?= $i ?>" 
                                            type="number" 
                                            class="input-text border-start-0" 
                                            placeholder="Ej: 65"
                                            min="0"
                                            step="1">
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <button type="button" id="btnBuscarManualmente_m<?= $i ?>" class="btn btn-general w-100 h-100">
                                        <i class="bi bi-search me-1"></i>Buscar
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mt-2 px-3">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>Al seleccionar billets con esta función se indicará a sellos maquinados que la barra no fue preferencia del sistema y fue selección manual.
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
                                    <th scope="col">Lote</th>
                                </tr>
                            </thead>
                            <tbody>
        
                            </tbody>
                        </table>
                    </div>
                    <div id="container38_m<?= $i ?>" 
                            class="d-flex flex-column <?php if ($lider_usuario == 4 || $lider_usuario == 5 ) {echo 'd-none';}?>" 
                            style="<?php if ($lider_usuario != 3 && $lider_usuario != 4 && $lider_usuario != 5) {echo 'width:38%';} ?>;">
                        <button id="btnQuitarCircle_m<?= $i ?>" class="btn-close align-self-end d-none" style="padding-right:5%;"></button>
                        <table class="table table-bordered text-secondary table-dimensiones-necesarias <?php if ($lider_usuario == 3 || $lider_usuario == 4 || $lider_usuario == 5) {echo 'd-none';}?>" 
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
                            class="d-flex flex-column justify-content-center align-items-center <?php if ($lider_usuario == 3 || $lider_usuario == 4) {echo 'd-none';}?>">
                            <div class="d-flex justify-content-center align-items-start">
                            <svg 
                                id="circuloSvg_m<?= $i ?>"
                                width="<?= ($lider_usuario == 3 || $lider_usuario == 4) ? '330' : '260' ?>"
                                height="<?= ($lider_usuario == 3 || $lider_usuario == 4) ? '330' : '260' ?>"
                                viewBox="0 0 100 100">
                            </svg>
                            </div>
                            <div class="d-flex justify-content-center align-items-start" style="font-size:18px; font-weight:700;">
                                <p>Porcentaje de aprovechamiento: <span id="spanPorcentAprov_m<?= $i ?>">0.00</span>%</p>
                            </div>
                        </div>
 
                    </div>
                </div>
                <!-- contenedor del footer -->
                <div class="modal-footer justify-content-start">
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL DE SELECCIONAR CLAVE SIMULACION -->
    <div class="modal fade" id="modalBilletsSimulacion_m<?= $i ?>" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" >
        <!-- Contenedor del header, body y footer del modal -->
        <div class="modal-dialog" style="max-width: 95% !important;">
            <div class="modal-content" style="height:95%;">
                <!-- contenedor del titulo -->
                <div class="modal-header justify-content-between">
                    <h5 id="titleClavesCoincidentesSimulacion_m<?= $i ?>" class="modal-title text-success">Seleccione una clave para calcular costos</h5>
                    <button id="btnCerrarModalBilletsSimulacion_m<?= $i ?>" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <!-- contenedor del body -->
                <div class="modal-body d-flex col-12 justify-content-between" style="height:400px;">
                    <div id="containerBodyModalBilletsSimulacion_m<?= $i ?>" class="" style="overflow-y:auto; width:100%;">
                        <table id="tablaBilletsSimulacion_m<?= $i ?>" class="table table-bordered border border-2 tabla-billets">
                            <thead>
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Clave</th>
                                    <th scope="col">Aprovechamiento</th>
                                    <th scope="col">Medida</th>
                                    <th scope="col">Max. Length</th>
                                    <th scope="col">Material</th>
                                </tr>
                            </thead>
                            <tbody>
        
                            </tbody>
                        </table>
                    </div>
                    <div id="container38Simulacion_m<?= $i ?>" 
                            class="d-flex flex-column d-none" 
                            style="<?php if ($lider_usuario != 3 && $lider_usuario != 4 && $lider_usuario != 5) {echo 'width:38%';} ?>;">
                        <button id="btnQuitarCircleSimulacion_m<?= $i ?>" class="btn-close align-self-end d-none" style="padding-right:5%;"></button>

                        <div id="containerCircleBilletSimulacion_m<?= $i ?>" 
                            class="d-flex flex-column justify-content-center align-items-center <?php if ($lider_usuario == 3 || $lider_usuario == 4) {echo 'd-none';}?>">
                            <div class="d-flex justify-content-center align-items-start">
                            <svg 
                                id="circuloSvgSimulacion_m<?= $i ?>"
                                width="<?= ($lider_usuario == 3 || $lider_usuario == 4) ? '330' : '260' ?>"
                                height="<?= ($lider_usuario == 3 || $lider_usuario == 4) ? '330' : '260' ?>"
                                viewBox="0 0 100 100">
                            </svg>
                            </div>
                            <div class="d-flex justify-content-center align-items-start" style="font-size:18px; font-weight:700;">
                                <p>Porcentaje de aprovechamiento: <span id="spanPorcentAprovSimulacion_m<?= $i ?>">0.00</span>%</p>
                            </div>
                        </div>
 
                    </div>
                </div>
                <!-- contenedor del footer -->
                <div class="modal-footer justify-content-start">
                </div>
            </div>
        </div>
    </div>
<?php endfor; ?>