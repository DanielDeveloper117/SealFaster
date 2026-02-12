$(document).ready(function(){        
    window.NOMBRE_TABLA = $('table').attr("id");
    if(window.NOMBRE_TABLA == "inventarioTable"){
        $(`#${NOMBRE_TABLA}`).DataTable({
            "ordering": false, // Desactiva la capacidad de ordenar y quita los botones (flechas)
            "order": [],
            "searching": true, // función de búsqueda activada
            search: {
                return: false
            },
            "autoWidth": true,
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
            "pageLength": 30,
            "lengthMenu": [ [10, 20, 30, 40, 50, 100, 1000], [10, 20, 30, 40, 50, 100, 1000] ],
            "scrollY": "400px", // Altura del área de desplazamiento vertical
            "scrollX": true,
            dom: 'Blfrtip',
            buttons: [
                {
                    extend: 'excelHtml5',
                    title: 'Inventario CNC',
                    text: 'Exportar a Excel',
                    exportOptions: {
                        columns: ':visible',
                        modifier: {
                            search: 'none',
                            order: 'applied',
                            page: 'all'
                        }
                    }
                }
            ],
            initComplete: function () {
                var api = this.api();
                setTimeout(function () {
                    api.columns.adjust().draw();
                    if (typeof $ !== 'undefined') {
                        console.log($);
                        $("#overlay").addClass("d-none");
                    }
                }, 400);
          
            }
        });
    }else{
        $(`#${NOMBRE_TABLA}`).DataTable({
            "ordering": false, // Desactiva la capacidad de ordenar y quita los botones (flechas)
            "order": [],
            "searching": true, // función de búsqueda activada
            search: {
                return: false
            },
            "autoWidth": true,
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
            "pageLength": 30,
            "lengthMenu": [ [10, 20, 30, 40, 50, 100, 1000], [10, 20, 30, 40, 50, 100, 1000] ],
            "scrollY": "400px", // Altura del área de desplazamiento vertical
            "scrollX": true,
            initComplete: function () {
                var api = this.api();
                setTimeout(function () {
                    api.columns.adjust().draw();
                    if (typeof $ !== 'undefined') {
                        console.log($);
                        $("#overlay").addClass("d-none");
                    }
                }, 400);
            }
        });
    }

    var anchoVentanaInicial = window.innerWidth;
    var anchoPantallaInicial = screen.width;
    var zoomInicial = anchoVentanaInicial / anchoPantallaInicial * 100;

    function detectarZoom() {
        var anchoVentana = window.innerWidth;
        var anchoPantalla = screen.width;
        var zoom = anchoVentana / anchoPantalla * 100;

        if (zoom < 98 || zoom > 100) {
            Swal.fire({
                title: 'Recomendación de visualización',
                text: 'Para una correcta visualización de las tablas de datos, se recomienda poner el zoom al 100%.',
                icon: 'info',
                confirmButtonText: 'Entendido',
                width: '350px',  // Tamaño pequeño del modal
                padding: '10px',  // Relleno para que se vea agradable
                position: 'top-end', // Coloca el modal en la esquina superior derecha (puedes cambiarlo)
                toast: true, // Mostrar como un "toast", que es una notificación pequeña
                //timer: 5000, // El modal desaparece automáticamente después de 5 segundos (opcional)
                showConfirmButton: true // Mostrar el botón de confirmación
            });
        } else {
            if(zoomInicial !== zoom){
                window.location.reload(true);  // Recargar desde el servidor (sin usar la caché)
            }else{
            }
        }
    }
    ////////////// EVENTO ZOOM/REDIMENSION DEL NAVEGADOR
    window.addEventListener('resize', detectarZoom);
    detectarZoom();
});