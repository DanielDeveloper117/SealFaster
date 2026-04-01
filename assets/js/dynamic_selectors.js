/**
 * dynamic_selectors.js
 * 
 * Script global para cargar dinámicamente las opciones de Materiales
 * y Proveedores desde la base de datos hacia los menús <select>.
 * Esto evita el "hardcoding" (textos fijos) en el código fuente frontal.
 */

$(document).ready(function () {

    /**
     * Función genérica para inyectar datos JSON en un <select>
     * manteniendo el valor que el usuario hubiera tenido editando.
     */
    function autoPoblarSelect(selectorDOM, endpointUrl, columnName) {
        $(selectorDOM).each(function () {
            var $select = $(this);

            // Si el select ya tiene un valor pre-seleccionado (ej. modo Edición), lo guardamos.
            var valorActual = $select.val() || $select.attr('data-current-val') || '';

            $.ajax({
                url: endpointUrl,
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    if (data.error || data.success === false) {
                        console.error('Error extrayendo listas para ' + selectorDOM + ':', data);
                        return;
                    }
                    
                    // Verificar si el select original tenía una opción de "Todos" (value="all")
                    var tieneOpcionTodos = $select.find('option[value="all"]').length > 0;
                    var textoTodos = tieneOpcionTodos ? $select.find('option[value="all"]').text() : "Todos";

                    var optionsHtml = ' <option value="" selected disabled>Seleccionar...</option>';
                    
                    if (tieneOpcionTodos) {
                        optionsHtml += '<option value="all">' + textoTodos + '</option>';
                    }

                    // Iterar resultados del distinct
                    data.forEach(function (row) {
                        var valor = row[columnName];
                        if (valor && valor.trim() !== '') {
                            optionsHtml += '<option value="' + valor + '">' + valor + '</option>';
                        }
                    });
                    
                    $select.html(optionsHtml);
                    
                    // Restaurar opción si existía
                    if (valorActual !== '') {
                        $select.val(valorActual);
                    }
                },
                error: function (xhr, status, err) {
                    console.error('AJAX Network Error en ' + selectorDOM + ':', err);
                }
            });
        });
    }

    // ─── BINDEO DE CLASES MÁGICAS ──────────────────────────────────────────────

    // 1. Materiales de inventario_cnc
    if ($('.selectorMaterialesInventario').length > 0) {
        autoPoblarSelect('.selectorMaterialesInventario', '../ajax/ajax_materiales_inventario.php', 'material');
    }

    // 2. Proveedores de inventario_cnc
    if ($('.selectorProveedoresInventario').length > 0) {
        autoPoblarSelect('.selectorProveedoresInventario', '../ajax/ajax_proveedores_inventario.php', 'proveedor');
    }

    // 3. Materiales de parametros (El endpoint original trae la key "material")
    if ($('.selectorMaterialesParametros').length > 0) {
        autoPoblarSelect('.selectorMaterialesParametros', '../ajax/ajax_materiales_parametros.php', 'material');
    }

    // 4. Proveedores de parametros
    if ($('.selectorProveedoresParametros').length > 0) {
        autoPoblarSelect('.selectorProveedoresParametros', '../ajax/ajax_proveedores_parametros.php', 'proveedor');
    }

});
