// MODIFICAR INTERIOR Y EXTERIOR, de inventario_cnc toma medida di/de para actualizar columnas de interior y exterior
$.ajax({
    url: 'ajax_script_medidas.php', 
    type: 'GET',
    dataType: 'json',
    success: function(data) {
        // Verifica que la respuesta tenga datos
        if (data.success) {
            alert("Se han insertado las medidas correctamente");
        } else {
            alert("Hubo un problema al insertar las medidas");
        }
    },
    error: function() {
        console.error('Error al realizar la petición AJAX');
    }
});
    
// MODIFICAR LOS PRECIOS DE INVENTARIO_CNC, los precios de fastseal(parametros) pasan a inventario_cnc
$.ajax({
    url: 'ajax_script_precios.php', 
    type: 'POST', // Cambiado de GET a POST
    dataType: 'json',
    success: function(data) {
        if (data.success) {
            console.log(data.message);
            alert("Precios en inventario actualizados correctamente");
        } else {
            console.error(data.message);
            alert("Precios en inventario no actualizados");
        }
    },
    error: function(xhr, status, error) {
        console.error('Error al realizar la petición AJAX:', error);
    }
});