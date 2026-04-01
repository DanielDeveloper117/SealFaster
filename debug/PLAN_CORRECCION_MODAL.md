# Plan de Corrección - Modal Maquinado de Sellos

## Problemas Identificados

1. **Cierre duplicado del modal** - Hay etiquetas de cierre duplicadas en línea 37-40
2. **Renderización de imágenes** - Las imágenes no se renderizan correctamente
   - UN componente: debe terminar en _0
   - MÚLTIPLES: deben empezar en _1 (evitando el _0)
3. **Bug de barras no mostradas** - "No hay barras registradas" aunque hay datos
   - Backend retorna barras correctamente pero frontend no las muestra

## Solución Implementada

### 1. Mantener Estructura HTML Original
- Mantener el HTML de modal_maquinado_sellos2.php intacto
- No cambiar divs ni clases principales
- Usar jQuery para renderizar dinámicamente

### 2. Usar jQuery en lugar de JavaScript Vanilla
- Evento `.on('show.bs.modal')` de jQuery
- $.ajax() para peticiones al backend
- Métodos jQuery para manipulación del DOM

### 3. Arreglar Renderización de Imágenes
- Si `cantidad_componentes == 1`: usar `_0.jpg`
- Si `cantidad_componentes > 1`: usar `_1.jpg`, `_2.jpg`, etc (comenzar en 1)
- Usar la misma lógica del archivo `perfiles.php`

### 4. Debuggear Bug de Barras
- Revisar que get_maquinado_sellos.php retorne barras correctamente
- Debuggear la estructura de datos en JavaScript
- Console.log para verificar

## Estructura HTML a Mantener

```html
<!-- Modal que se rellena dinámicamente -->
<div class="modal fade" id="modalMaquinadoSellos" ...>
    <div class="modal-dialog modal-xl mms-dialog">
        <div class="modal-content mms-content">
            <!-- HEADER -->
            <div class="modal-header">
                ...
            </div>
            <!-- BODY - se rellena con jQuery -->
            <div class="modal-body mms-body">
                <!-- Contenido dinámico aquí -->
            </div>
            <!-- FOOTER -->
            <div class="modal-footer border-top pt-3">
                ...
            </div>
        </div>
    </div>
</div>
```

## Cambios Específicos en JavaScript

1. Usar `$(document).ready()` y `$("#modalMaquinadoSellos").on("show.bs.modal")`
2. Cambiar `fetch()` por `$.ajax()`
3. Renderizar componentes con jQuery (`.html()`, `.append()`)
4. Arreglar la lógica de índices de imágenes

## Archivos a Modificar

- **modal_maquinado_sellos.php** - Reescribir con jQuery
- **get_maquinado_sellos.php** - Revisar si hay bug en backend (probablemente no)
- **modal_maquinado_sellos2.php** - Dejar como respaldo

## Testing

Luego de implementar:
1. Abrir modal y verificar que carga cotizaciones
2. Verificar imágenes se renderizan correctamente
3. Verificar que barras se muestren (no mensaje "No hay barras")
4. Ver en console.log los datos retornados por AJAX
