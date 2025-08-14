
$(document).ready(function() {
    // *************** FUNCIONES ****************
    // Función para cargar filtros desde la URL
    function cargarFiltrosActuales() {
        var urlParams = new URLSearchParams(window.location.search);
        
        // Cargar valores en el formulario
        $('#filtro_familia').val(urlParams.get('filtro_familia') || '');
        $('#filtro_tipo_medida').val(urlParams.get('filtro_tipo_medida') || '');
        $('#filtro_fecha_inicio').val(urlParams.get('fecha_inicio') || '');
        $('#filtro_fecha_fin').val(urlParams.get('fecha_fin') || '');
        $('#archivadas').prop('checked', urlParams.get('archivadas') === '1');
    }
    // Función para mostrar filtros activos
    function mostrarFiltrosActivos() {
        var urlParams = new URLSearchParams(window.location.search);
        var filtrosActivos = [];
        
        // Verificar cada filtro y crear etiquetas
        if (urlParams.get('filtro_familia')) {
            var familias = {
                'rotary': 'Rotary (Rotativo)',
                'piston': 'Piston (Pistón)',
                'backup': 'Backup (Respaldo)',
                'guide': 'Guide (Guía)',
                'wipers': 'Wiper (Limpiador)',
                'rod': 'Rod (Vástago)'
            };
            var familiaTexto = urlParams.get('filtro_familia') || familias[urlParams.get('filtro_familia')];
            filtrosActivos.push('Familia: ' + familiaTexto);
        }
        
        if (urlParams.get('filtro_tipo_medida')) {
            filtrosActivos.push('Tipo: ' + urlParams.get('filtro_tipo_medida'));
        }
        
        if (urlParams.get('fecha_inicio')) {
            filtrosActivos.push('Desde: ' + urlParams.get('fecha_inicio'));
        }
        
        if (urlParams.get('fecha_fin')) {
            filtrosActivos.push('Hasta: ' + urlParams.get('fecha_fin'));
        }

        if (urlParams.get('archivadas') === '0') {
            filtrosActivos.push('Solo activas');
        }

        if (urlParams.get('archivadas') === '1') {
            filtrosActivos.push('Solo archivadas');
        }
        
        // Mostrar los filtros activos
        if (filtrosActivos.length > 0) {
            var tagsHtml = filtrosActivos.map(function(filtro) {
                return '<span class="filtro-tag">' + filtro + '</span>';
            }).join(' ');
            
            $('#filtrosActivosList').html(tagsHtml);
            $('#filtrosActivosContainer').show();
        } else {
            $('#filtrosActivosContainer').hide();
        }
    }
    // Función global para limpiar todos los filtros
    window.limpiarTodosFiltros = function() {
        if (confirm('¿Estás seguro de que deseas limpiar todos los filtros?')) {
            window.location.href = window.location.pathname;
        }
    };   

    // Función para crear href con el parámetro cot modificado
    function crearHrefConCot(valorCot) {
        const nuevaUrl = new URL(urlInicial);
        nuevaUrl.searchParams.set('cot', valorCot);
        return nuevaUrl.toString();
    }

    // Función para mostrar/ocultar contenedores según cot
    function mostrarTablaPorCot(cot) {
        if (cot === 'u') {
            containerUnicas.classList.remove('d-none');
            containerFusionadas.classList.add('d-none');
        } else if (cot === 'f') {
            containerFusionadas.classList.remove('d-none');
            containerUnicas.classList.add('d-none');
        }
    }

    function cancelarAgrupacion() {
        // Obtener todos los parámetros GET de la URL
        const urlParams = new URLSearchParams(window.location.search);

        // Eliminar el parámetro 'agru'
        urlParams.delete('agru');

        // Reconstruir la URL sin 'agru'
        const newUrl = window.location.pathname + '?' + urlParams.toString();

        // Recargar la página con la nueva URL
        window.location.href = newUrl;
    }

    // ************ INICIALIZACION **************
    const urlInicial = new URL(window.location.href);
    if (!urlInicial.searchParams.has('cot')) {
        // Agregar cot=u sin eliminar otros parametros
        urlInicial.searchParams.set('cot', 'u');
        window.location.replace(urlInicial.toString());
    }

    const tabs = document.querySelectorAll('#cotTabs .nav-link');
    const containerUnicas = document.getElementById('containerUnicas');
    const containerFusionadas = document.getElementById('containerFusionadas');

    const params = new URLSearchParams(window.location.search);
    if (params.get('agru') === '1') {
        document.querySelectorAll('.btn-check-cute').forEach(el => {
            el.classList.remove('d-none');
            // Forzar reflow para que la transición se aplique correctamente
            void el.offsetWidth;
            el.classList.add('show-cute');
        });
        const bar = document.getElementById('agrupacionBar');
        if (bar) {
            bar.classList.remove('d-none');
            // Forzar reflow para activar animación
            void bar.offsetWidth;
            bar.classList.add('show-bar');
        }
    }


    // Inicializar tabs y mostrar la tabla correcta al cargar
    const cotActual = urlInicial.searchParams.get('cot') || 'u';

    // Set active tab según cotActual
    tabs.forEach((tab, i) => {
        const cotValue = i === 0 ? 'u' : 'f';
        tab.href = crearHrefConCot(cotValue);
        if (cotValue === cotActual) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    // Mostrar la tabla correcta según el parámetro cot en la URL
    mostrarTablaPorCot(cotActual);

    // Evento click para cambiar active y mostrar tabla correcta
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            // Cambiar active en tabs
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            // Obtener cot del href clickeado (puedes usar dataset también)
            const url = new URL(this.href);
            const cot = url.searchParams.get('cot') || 'u';

            // Mostrar/ocultar tablas
            mostrarTablaPorCot(cot);

            // Cambiar URL sin recargar (opcional, si no quieres recargar)
            //window.history.replaceState(null, '', url.toString());

            // Si quieres recargar para aplicar filtros, usa:
            window.location.href = url.toString();
        });
    });

    // Mostrar filtros activos al cargar la página
    mostrarFiltrosActivos();
    



    // ************** EVENTOS DEL DOM **************
    document.getElementById('btnInitFusionar').addEventListener('click', function(e) {
        e.preventDefault();
        let url = new URL(window.location.href);
        url.searchParams.set('agru', '1');
        url.searchParams.set('archivadas', '0');
        window.location.href = url.toString();
    });

    // Animación “pop” al (des)chequear y hook listo para tu lógica
    document.addEventListener('change', (e) => {
        if (!e.target.classList.contains('btn-check-cute')) return;

        // animación breve
        e.target.classList.add('pop');
        setTimeout(() => e.target.classList.remove('pop'), 220);

        // aquí puedes disparar tu lógica con el id de cotización:
        // const id = e.target.dataset.idCotizacion;
        // const checked = e.target.checked;
    });

    // Cargar filtros activos al abrir el modal
    $('#modalFiltrosBusqueda').on('show.bs.modal', function() {
        cargarFiltrosActuales();
        mostrarFiltrosActivos();
    });
    // Validación del formulario
    $('#formFiltros').on('submit', function(e) {
        var fechaInicio = $('#filtro_fecha_inicio').val();
        var fechaFin = $('#filtro_fecha_fin').val();
        
        // Validar que la fecha de inicio no sea mayor que la fecha de fin
        if (fechaInicio && fechaFin && fechaInicio > fechaFin) {
            e.preventDefault();
            alert('La fecha de inicio no puede ser mayor que la fecha de fin.');
            $('#filtro_fecha_inicio').focus();
            return false;
        }
        
        // El formulario se enviará normalmente si pasa la validación
        console.log('Formulario válido, enviando...');
    });
    // Limpiar formulario de filtros
    $('#btnLimpiarFormulario').on('click', function() {
        $('#formFiltros')[0].reset();
        $('#filtrosActivosContainer').hide();
    });
    // Auto-completar fecha fin cuando se selecciona fecha inicio
    $('#filtro_fecha_inicio').on('change', function() {
        var fechaInicio = $(this).val();
        var fechaFin = $('#filtro_fecha_fin').val();
        
        if (fechaInicio && !fechaFin) {
            // Sugerir la fecha actual como fecha fin
            var hoy = new Date().toISOString().split('T')[0];
            $('#filtro_fecha_fin').val(hoy);
        }
    });
    // CLICK para seleccionar versión del formato de cotización
    $(".btn-version-cotizacion").on('click', function () {
        const dataIdCotizacion = $(this).data('id-cotizacion');
        $("#inputIdCotizacionFormato").val(dataIdCotizacion);
    });
    // enviar el formulario de seleccionar version de formato pdf
    $("#formVersionCotizacion").on("submit", function (e) {
        e.preventDefault(); // evita que el form se envíe inmediatamente

        const valorSeleccionado = $('#formVersionCotizacion input[name="formato"]:checked').val();

        if (valorSeleccionado === 'cliente') {
            $(this).attr("action", "../includes/functions/generar_cotizacion.php");
        } else if (valorSeleccionado === 'interno') {
            $(this).attr("action", "../includes/functions/generar_pdf.php");
        } else {
            alert("Selecciona una opción de formato.");
            return;
        }

        // Ahora sí, enviar el formulario con el action actualizado
        this.submit();
    });
    // CLICK para seleccionar versión del formato de cotización de las fusionadas
    $(".btn-version-cotizacionF").on('click', function () {
        const dataIdFusion = $(this).data('id-fusion');
        $("#inputIdCotizacionFormatoF").val(dataIdFusion);
    });
    // enviar el formulario de seleccionar version de formato pdf de las fusionadas 
    $("#formVersionCotizacionF").on("submit", function (e) {
        e.preventDefault(); // evita que el form se envíe inmediatamente

        const valorSeleccionado2 = $('#formVersionCotizacionF input[name="formato"]:checked').val();

        if (valorSeleccionado2 === 'cliente') {
            $(this).attr("action", "../includes/functions/generar_cotizacion_f.php");
        } else if (valorSeleccionado2 === 'interno') {
            $(this).attr("action", "../includes/functions/generar_pdf_f.php");
        } else {
            alert("Selecciona una opción de formato.");
            return;
        }

        // Ahora sí, enviar el formulario con el action actualizado
        this.submit();
    });
    //CLICK A Enviar correo modal
    $(".btn-enviar-correo").on('click', function(){
        //$dataId=$("#btnValidar").data('id');//lo toma y lo almacena en cache
        $dataIdCotizacion=$(this).data('id-cotizacion');
        $dataCorreoCliente=$(this).data('correo-cliente');

        $("#inputIdCotizacion").val($dataIdCotizacion);
        $("#spanIdCotizacion, #spanIdCotizacion2").text($dataIdCotizacion);
    });
    //CLICK A Enviar a produccion
    $(".btn-enviar-produccion").on('click', function(){
        $dataIdCotizacionProduccion=$(this).data('id-cotizacion');

        $("#inputCotizacionProduccion").val($dataIdCotizacionProduccion);
    });
    // CLICK para archivar una cotizacion
    $(".btn-archivar-cotizacion").on('click', function () {
        const dataIdCotizacionA = $(this).data('id-cotizacion');
        var dataArchivada = $(this).data('archivada');
        if(dataArchivada == 0){
            dataArchivada = 1;
            $("#infoArchivada").text("Si archiva la cotización no podrá usarla al crear nuevas requisiciones.");
        }else{
            dataArchivada = 0;
            $("#infoArchivada").text("Ya podrá usar la cotizacion al crear nuevas requisiciones.");
        }
        $("#inputArchivar").val(dataIdCotizacionA);
        $("#inputNextValor").val(dataArchivada);
    });
    // archivar una cotizacion dinamicamente
    $("#btnArchivar").on("click", function(){
        var idCotizacionArchivar = $("#inputArchivar").val();
        var nextValue = $("#inputNextValor").val();
        $.ajax({
            url: '../ajax/archivar.php',
            method: 'POST',
            data: {
                id_cotizacion: idCotizacionArchivar,
                archivada: nextValue
            },
            success: function(data) {
                if (data.success) {
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                } else {
                    sweetAlertResponse("warning", "Advertencia", data.error, "self");
                }
            },
            error: function () {
                sweetAlertResponse("error", "Error", "Ocurrio algo inesperado", "none");
                console.error("Error al consultar el estatus de autorización.");
            }
        });
    });
    // CLICK para archivar una cotizacion
    $(".btn-romper-fusion").on('click', function () {
        const dataIdRomperFusion = $(this).data('id-fusion');
        $("#inputIdRomperFusion").val(dataIdRomperFusion);
    });
    // ROMPER la agrupacion de las cotizaciones
    $("#btnUnlink").on("click", function(){
        var idRomperFusion = $("#inputIdRomperFusion").val();
        $.ajax({
            url: '../ajax/romper_fusion.php',
            method: 'POST',
            data: {
                id_fusion: idRomperFusion,
            },
            success: function(data) {
                if (data.success) {
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                } else {
                    sweetAlertResponse("warning", "Advertencia", data.error, "self");
                }
            },
            error: function () {
                sweetAlertResponse("error", "Error", "Ocurrio algo inesperado ", "none");
                console.error("Error al consultar el estatus de autorización.");
            }
        });
    });
    // CONTINUAR A FUSIONAR LAS COTIZACIONES SELECCIONADAS
    $("#btnContinuarAgrupar").on("click", function(){

        // Crear arreglo con las id_cotizacion seleccionadas
        let cotizacionesSeleccionadas = [];
        $(".btn-check-cute:checked").each(function(){
            cotizacionesSeleccionadas.push($(this).attr("val"));
        });

        if(cotizacionesSeleccionadas.length === 0){
            sweetAlertResponse("warning", "Advertencia", "No seleccionaste ninguna cotización.", "none");
            return;
        }
        if(cotizacionesSeleccionadas.length < 2){
            sweetAlertResponse("warning", "Advertencia", "Selecciona mínimo 2 cotizaciones.", "none");
            return;
        }

        // Enviar las id_cotizacion seleccionadas al backend
        $.ajax({
            url: '../ajax/fusionar_cotizaciones.php',
            method: 'POST',
            data: {
                ids_cotizaciones: cotizacionesSeleccionadas
            },
            success: function(data) {
                if (data.success) {
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "cotizaciones.php?cot=f");
                } else {
                    sweetAlertResponse("warning", "Advertencia", data.error, "cotizaciones.php");
                }
            },
            error: function () {
                sweetAlertResponse("error", "Error", "Ocurrió algo inesperado al procesar.", "none");
                console.error("Error al enviar las cotizaciones seleccionadas.");
            }
        });
    });

    // CLICK CERRAR MODAL 
    $(".btn-close").on("click", function(){
        $("#formEnviarCorreo, #formEnviarAProduccion")[0].reset();
    });
    // DATATABLE PARA COTIZACIONES FUSIONADAS
    $(`#cotizacionesTableFusionadas`).DataTable({
        ordering: true, //botones de ordenacion de las columnas
        "orderable": true,
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
            }, 400);
        }
    });
    // CANCELAR AGRUPACION
    $("#btnCancelFusion").on("click", function(){
        cancelarAgrupacion();
    });
});
