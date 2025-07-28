<?php for ($i = 1; $i <= $cantidadMateriales; $i++): ?>
    <form id="estimateForm_m<?= $i ?>" method="post"> 
        <!-- PRIMER FORMULARIO - MATERIAL 1 -->
        <section id="sectionContainerMaterial_m<?= $i ?>" class="section-container d-none">
            <?php
                $numeroMaterial="";
                if($cantidadMateriales == 1){
                        $numeroMaterial="0";
                }else{
                    if($i == 1){
                        $numeroMaterial="1";
                        echo '<h2>Material 1</h2>';
    
                    }
                    if($i > 1){
                        $numeroMaterial = $i;
                        echo '<h2>Material '.$i.'</h2>';
                    }
                }
            ?>
            <div class="d-flex col-12 flex-column flex-md-row justify-content-between align-items-md-start align-items-center">
                <?php
                    if (file_exists($imagePath)) {
                        $imgMaterial=$imageDir . $selloOriginal . '_'.$numeroMaterial.'.jpg';
                        echo '<div class="d-flex mb-2 mb-md-0 col-md-3 justify-content-center">';
                        echo '<a class="img-sello" href="tipo.php?tipo=' . $tipoButton . '" title="Click para cambiar el perfil">';
                        echo '<img id="imagenMaterial_m'. $i.'" src="'. $imgMaterial . '" alt="' . htmlspecialchars($selloOriginal) . '" class="border-gray img-fluid">';
                        echo '</a></div>';
                    } else {
                        echo "<h2>Imagen no encontrada</h2>";
                    }
                ?>
                <div class="controles pb-5 d-flex col-9 flex-column border-gray">
                    <h4>Selección de billets para el maquinado</h4>

                    <div class="d-flex col-12 justify-content-center">
                        <div id="pag1_m<?= $i ?>" class="d-flex col-12 flex-column flex-md-row justify-content-evenly mt-4" >
                      
                            <input type="hidden" id="diametro_interior_mm_m<?= $i ?>" class="input-readonly" step="0.01" min="0"  name="diametro_interior_mm"  required  placeholder="Ingrese las dimensiones">
                            <input type="hidden" id="diametro_interior_inch_m<?= $i ?>" class="input-readonly" step="0.0001" min="0"  name="diametro_interior_inch" required  placeholder="Ingrese las dimensiones">
                        
                            <input type="hidden" id="diametro_exterior_mm_m<?= $i ?>"step="0.01" min="0" class="input-readonly" name="diametro_exterior_mm" required  placeholder="Ingrese las dimensiones">
                            <input type="hidden" id="diametro_exterior_inch_m<?= $i ?>" step="0.0001" min="0" class="input-readonly" name="diametro_exterior_inch" required  placeholder="Ingrese las dimensiones">
                        
                            <input type="hidden" id="altura_mm_m<?= $i ?>" class="input-readonly" step="0.01" min="0" name="altura_mm" required placeholder="Ingrese las dimensiones">
                            <input type="hidden" id="altura_inch_m<?= $i ?>" class="input-readonly" step="0.0001" min="0"  name="altura_inch" required placeholder="Ingrese las dimensiones">
                    
                
                            <div class="col-12 col-md-4 d-flex flex-column">
                                
                                <div class="mb-2">
                                    <label for="selectorMaterial_m<?= $i ?>" class="form-label">Material</label>
                                    <select id="selectorMaterial_m<?= $i ?>" class="form-select" name="material" required>
                                        <option value="" disabled selected>Seleccione un material</option>
                                    </select>
                                </div>

                                <div class="mb-2 d-none">
                                    <label for="selectorProveedor_m<?= $i ?>" class="form-label">Proveedor</label>
                                    <select id="selectorProveedor_m<?= $i ?>" class="form-select" name="proveedor" required disabled>
                                        <option value="" disabled selected>Seleccione un proveedor</option>
                                    </select>
                                </div>
                                
                                <div class="d-flex flex-column mb-4">
                                    <label for="inputCantidad_m<?= $i ?>" class="form-label">Cantidad</label>
                                    <input id="inputCantidad_m<?= $i ?>" type="number" class="form-control" value="" name="cantidad" step="1" min="1" oninput="this.value = this.value.replace(/\D+/g, '')" placeholder="Cantidad de piezas">
                                </div>

                                <div id="containerErrorDimensiones_m<?= $i ?>" class="text-faltan">
                                    <span></span>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 d-flex flex-column">
                                <div class="d-flex flex-column align-items-start" style="height:100%;">
                                    <label for="btnBillets_m<?= $i ?>" class="form-label">Seleccionar claves de Billet</label>
                                    <button id="btnBillets_m<?= $i ?>" type="button" class="btn-disabled mb-2" data-bs-toggle="modal" data-bs-target="#modalBillets_m<?= $i ?>" disabled>Ver billets disponibles</button>
                                    <!-- <span>Desbaste añadido por pieza: 2.5 mm</span> -->
                                    <div class="d-flex flex-column">
                                        <div id="containerFaltanSiNo_m<?= $i ?>" class="text-faltan">
                                            <span>Milímetros necesarios: </span>
                                            <span id="spanMilimetrosNecesarios_m<?= $i ?>">0</span>
                                            <span>, Faltan: </span>
                                            <span id="spanSellosRestantes_m<?= $i ?>">0</span>
                                            <span> pz</span>
                                        </div>
                                        <div id="containerMilimetrosSobrantes_m<?= $i ?>" class="d-none mt-1">
                                            <span>Sobran: </span>
                                            <span id="spanMilimetrosSobrantes_m<?= $i ?>">0</span>
                                            <span> milímetros</span>
                                        </div>
                                    </div>
                                    <div class="mb-3" style="width:100%; overflow-x:auto;">
                                        <table id="miniTableBillets_m<?= $i ?>" class="table table-bordered border border-2 tabla-billets">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Clave</th>
                                                    <th scope="col">Lote/pedimento</th>
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
                                <div class="" style="min-height: max-content;width:100%; overflow-x:auto;">
                                    <table id="miniTableCostoBarra_m<?= $i ?>" class="table table-bordered border border-2 tabla-billets">
                                        <thead>
                                            <tr>
                                                <th scope="col">Clave</th>
                                                <th scope="col">Lote/pedimento</th>
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
                               
                                <input type="hidden" id="precioBarra_m<?= $i ?>" class="input-readonly" value="" name="precio_barra" step="0.01" min="0" placeholder="Calculo de costos de barra pendiente">
                                <input type="hidden" id="inputCostoMinimoUnidad_m<?= $i ?>" class="input-readonly" value="" name="costo_minimo_unidad">
                                <input type="hidden" id="inputCostoOperacion_m<?= $i ?>" class="input-readonly" value="" name="costo_operacion" step="0.01" min="0" placeholder="Intente con otra clave">
                                <input type="hidden" id="inputCostoHerramienta_m<?= $i ?>" class="input-readonly" value="" name="costo_herramienta" step="0.01" min="0" placeholder="Intente con otra clave">
                                <input type="hidden" id="inputCostoPreparacionDI_m<?= $i ?>" class="input-readonly" value="" name="costo_preparacion_di_barra" step="0.01" min="0" placeholder="Intente con otra clave">
                                <input type="hidden" id="inputCostoResorte_m<?= $i ?>" class="input-readonly" value="" name="costo_resorte" step="0.01" min="0" placeholder="No aplica">

                                <div class="d-flex flex-column flex-md-row gap-5 justify-content-between align-items-end" style="height:100%;">
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
                </div>
            </div>
        </section>
        <input type="hidden" name="id_usuario" value="<?php echo $_SESSION['id']; ?>">
        <input type="hidden" id="totalInput_m<?= $i ?>" name="total">
        <input type="hidden" id="colPrecio_m<?= $i ?>" name="col_precio">
        <input type="hidden" id="colMaxUsable_m<?= $i ?>" name="col_max_usable">
        <textarea id="inputClaves_m<?= $i ?>" class="d-none" name="claves" readonly></textarea>
        <textarea id="inputBillets_m<?= $i ?>" class="d-none" name="billets" readonly></textarea>
        <textarea id="inputBilletsString_m<?= $i ?>" class="d-none" name="billets_string" readonly></textarea>
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

        <input type="hidden" id="inputAlturaCaja_m<?= $i ?>" step="0.01" min="0" value="0.00" name="altura_caja_mm" required>
        <input type="hidden" id="inputAlturaEscalon_m<?= $i ?>" step="0.01" min="0" value="0.00" name="altura_escalon_mm" required>
        <input type="hidden" id="inputAlturaH2_m<?= $i ?>" step="0.01" min="0" value="0.00" name="altura_h2_mm" required>
        <input type="hidden" id="inputAlturaH3_m<?= $i ?>" step="0.01" min="0" value="0.00" name="altura_h3_mm" required>

    </form>

    <!-- MODAL DE SELECCIONAR CLAVES -->
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
                            style="overflow-y:auto; width:<?php if ($tipoUsuario == 3 || $tipoUsuario == 4 || $tipoUsuario == 5) {echo '100';}else{echo '62';}?>%;">
                        <table id="tablaBillets_m<?= $i ?>" class="table table-bordered border border-2 tabla-billets">
                            <thead>
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col">Clave</th>
                                    <th scope="col">Aprovechamiento</th>
                                    <th scope="col">Stock MM</th>
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
                <!-- contenedor del footer -->
                <div class="modal-footer justify-content-start">
                </div>
            </div>
        </div>
    </div>

<?php endfor; ?>