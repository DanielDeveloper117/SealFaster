<style>
    #tablaCotizacionMateriales {
    table-layout: fixed; 
    width: 100%; 
    }
    #tablaCotizacionMateriales th:nth-child(1),
    #tablaCotizacionMateriales td:nth-child(1) {
    width: 5%; 
    font-size:10px !important;
    }
    #tablaCotizacionMateriales th:nth-child(2),
    #tablaCotizacionMateriales td:nth-child(2) {
    width: 13%;  
    }
    #tablaCotizacionMateriales th:nth-child(3),
    #tablaCotizacionMateriales td:nth-child(3) {
    width: 10%;  
    }
    #tablaCotizacionMateriales th:nth-child(4),
    #tablaCotizacionMateriales td:nth-child(4) {
    width: 8%; 
    }
    #tablaCotizacionMateriales th:nth-child(5),
    #tablaCotizacionMateriales td:nth-child(5) {
    width: 28%; 
    }
    #tablaCotizacionMateriales th:nth-child(6),
    #tablaCotizacionMateriales td:nth-child(6) {
    width: 12%;  
    }
    #tablaCotizacionMateriales th:nth-child(7),
    #tablaCotizacionMateriales td:nth-child(7) {
    width: 11%; 
    }
    #tablaCotizacionMateriales th:nth-child(8),
    #tablaCotizacionMateriales td:nth-child(8) {
    width: 12%; 
    }
</style>
<table id="tablaCotizacionMateriales" class="table table-bordered" style="border:1px solid #495057;">
    <thead>
        <tr>
            <th>#</th>
            <th>Material</th>
            <th>Proveedor</th>
            <th>Cantidad</th>
            <th>Billet(s)</th>
            <th>Total unitarios</th>
            <th>Descuentos</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php for ($i = 1; $i <= $cantidadMateriales; $i++): ?>
            <tr id="rowM<?= $i ?>" class="">
                <td>
                    <input type="number" id="inputTablaNumeroMaterialM<?= $i ?>" class="input-span2" value="<?= $i ?>" placeholder="-" readonly tabindex="-1">
                </td>
                <td>
                    <input type="text" id="inputTablaMaterialM<?= $i ?>" class="input-span2" placeholder="-" readonly tabindex="-1">
                </td>
                <td>
                    <input type="text" id="inputTablaProveedorM<?= $i ?>" class="input-span2" placeholder="-" readonly tabindex="-1">
                </td>
                <td>
                    <input type="number" id="inputTablaCantidadM<?= $i ?>" class="input-span2" placeholder="-" readonly tabindex="-1">
                </td>                                                            
                <td>
                    <textarea id="inputTablaClaveM<?= $i ?>" class="input-span2" placeholder="-" readonly tabindex="-1" style="height:auto; pointer-events:all !important;"></textarea> 
                </td>
                <td>
                    <div class="d-flex align-items-baseline">
                        <span class="input-span3">$</span>
                        <input type="number" id="inputTablaTotalUnitariosM<?= $i ?>" class="input-span2" placeholder="-" readonly tabindex="-1">
                    </div>
                </td>  
                <td>
                    <div class="d-flex align-items-baseline">
                        <span class="input-span3">$</span>
                        <input type="number" id="inputTablaTotalDescuentosM<?= $i ?>" class="input-span2" placeholder="-" readonly tabindex="-1">
                    </div>
                </td>    
                <td>
                    <div class="d-flex align-items-baseline">
                        <span class="input-span3">$</span>
                        <input type="number" id="inputTablaTotalM<?= $i ?>" class="input-span2" placeholder="-" readonly tabindex="-1">
                    </div>
                </td>
            </tr>
        <?php endfor; ?>
    </tbody>
</table>