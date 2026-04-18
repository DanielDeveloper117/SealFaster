// ============================================================
//          ******** VARIABLES GLOBALES ********
// ============================================================
var anchoVentanaInicial = window.innerWidth;
var anchoPantallaInicial = screen.width;
var zoomInicial = anchoVentanaInicial / anchoPantallaInicial * 100;


// ============================================================
//              ******** FUNCIONES ********
// ============================================================
/**
 * Detects browser zoom and shows a recommendation toast.
 * Detecta el zoom del navegador y muestra un toast de recomendación.
 */
function detectarZoom() {
    // 1. Verificar si el usuario ya eligió ocultar este mensaje
    if (localStorage.getItem('hideZoomAdvice') === 'true') {
        return;
    }

    var anchoVentana = window.innerWidth;
    var anchoPantalla = screen.width;
    var zoom = (anchoVentana / anchoPantalla) * 100;

    // Solo mostrar si el zoom no está en el rango ideal (98% - 100%)
    if (zoom < 98 || zoom > 100) {
        Swal.fire({
            title: 'Recomendación de visualización',
            text: 'Para una correcta visualización de las tablas de datos, se recomienda poner el zoom al 100%.',
            icon: 'info',
            confirmButtonText: 'Entendido',
            width: '400px',
            padding: '10px',
            position: 'top-start',
            toast: true,
            showConfirmButton: true,
            // Usamos el input nativo de Swal para el checkbox
            input: 'checkbox',
            inputPlaceholder: 'No volver a mostrar',
            inputAttributes: {
                id: 'stopShowingZoom'
            }
        }).then((result) => {
            // result.value contendrá true si el checkbox fue marcado
            if (result.isConfirmed && result.value) {
                localStorage.setItem('hideZoomAdvice', 'true');
            }
        });
    } else {
        // Si el zoom regresa a la normalidad, recargar para ajustar DataTables
        if (typeof zoomInicial !== 'undefined' && zoomInicial !== zoom) {
            window.location.reload(true);
        }
    }
}


// ============================================================
//          ******** EVENTOS DEL DOM ********
// ============================================================ 
$(document).ready(function(){        
    // ============================================================
    //          ***** INICIALIZACIONES *****
    window.NOMBRE_TABLA = $('.mainTable').attr("id");
    console.log(window.NOMBRE_TABLA);
    // 1. Definimos la configuración BASE para todas las tablas
    var dtConfig = {
        "ordering": false,
        "order": [],
        "searching": true, // función de búsqueda activada
        search: {
            return: false
        },
        "autoWidth": false, // Cambiado a false para que scrollX funcione mejor
        "deferRender": true, // <--- CRUCIAL para no crashear
        "pageLength": 10,
        "lengthMenu": [[10, 20, 30, 40, 50, 100, 1000], [10, 20, 30, 40, 50, 100, 1000]],
        "scrollY": "300px",
        "scrollX": true,
        "language": { 
            "decimal" : "",
            "emptyTable":"No hay registros",
            "info": "Mostrando _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 de 0 registros",
            "infoFiltered": "(Se filtraron _MAX_ registros)",
            "infoPostFix":"",
            "thousands": ", ",
            "lengthMenu": "Mostrar _MENU_ registros",
            "loadingRecords":"Cargando...",
            "processing": "Procesando...",
            "search": "Buscar: ",
            "zeroRecords":"No se encontraron resultados.",
            "paginate":{
            "first":"<<",
            "last":">>",
            "next": "Siguiente",
            "previous": "Anterior"
            }
        },
        initComplete: function () {
            var api = this.api();
            setTimeout(function () {
                api.columns.adjust().draw();
                $("#overlay").addClass("d-none");
                if(window.NOMBRE_TABLA == "inventarioTable" || window.NOMBRE_TABLA == "cotizacionesTable"){
                    $(".badge-checkbox").removeClass("d-none");
                }
            }, 400);
        }
    };

    // TABLAS QUE TIENEN BOTON DE EXPORTAR TABLA A EXCEL
    // 2. Si es una tabla especial, añadimos los botones de Excel
    if(window.NOMBRE_TABLA == "inventarioTable" || window.NOMBRE_TABLA == "clavesTable"){
        dtConfig.dom = 'Blfrtip';
        dtConfig.buttons = [
            {
                extend: 'excelHtml5',
                title: 'Inventario CNC - Sealfaster',
                text: 'Exportar a Excel',
                exportOptions: { columns: ':visible' }
            }
        ];
    }

    // 3. Inicializamos una sola vez
    $(`#${NOMBRE_TABLA}`).DataTable(dtConfig);


    // Detector del zoom y resize
    detectarZoom();
    // Add hover and click effects to action buttons
    const actionButtons = document.querySelectorAll('.container-actions button');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-3px) scale(1.05)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
        
        // Add ripple effect on click
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.3)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.pointerEvents = 'none';
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    // Add CSS for ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    // ============================================================ 

    ////////////// EVENTO ZOOM/REDIMENSION DEL NAVEGADOR
    window.addEventListener('resize', detectarZoom);
});