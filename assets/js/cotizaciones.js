
$(document).ready(function() {
    // *************** FUNCIONES ****************
    // Función para cargar filtros desde la URL
    function cargarFiltrosActuales() {
        var urlParams = new URLSearchParams(window.location.search);
        
        // Cargar valores en el formulario
        $('#filtro_familia').val(urlParams.get('familia') || '');
        $('#filtro_tipo_medida').val(urlParams.get('tipo_medida') || '');
        $('#filtro_tipo_cliente').val(urlParams.get('tipo_cliente') || '');
        $('#filtro_fecha_inicio').val(urlParams.get('fecha_inicio') || '');
        $('#filtro_fecha_fin').val(urlParams.get('fecha_fin') || '');
        $('#archivadas').prop('checked', urlParams.get('archivadas') === '1');
        
    }
    // Función para mostrar filtros activos
    function mostrarFiltrosActivos() {
        var urlParams = new URLSearchParams(window.location.search);
        var filtrosActivos = [];
        
        // --- Familia ---
        const familiaSelect = document.getElementById('familia');
        if (urlParams.get('familia')) {
            const familias = {
                'rotary': 'Rotary (Rotativo)',
                'piston': 'Piston (Pistón)',
                'backup': 'Backup (Respaldo)',
                'guide': 'Guide (Guía)',
                'wipers': 'Wiper (Limpiador)',
                'rod': 'Rod (Vástago)'
            };
            let familiaValor = urlParams.get('familia');
            let familiaTexto = familias[familiaValor.toLowerCase()] || familiaValor;
            filtrosActivos.push('Familia: ' + familiaTexto);

            if (familiaSelect) familiaSelect.value = familiaValor;
        }
        
        // --- Tipo de medida ---
        const tipoMedidaSelect = document.getElementById('tipo_medida');
        if (urlParams.get('tipo_medida')) {
            filtrosActivos.push('Tipo: ' + urlParams.get('tipo_medida'));
            if (tipoMedidaSelect) tipoMedidaSelect.value = urlParams.get('tipo_medida');
        }

        // --- Tipo de cliente ---
        const tipoClienteSelect = document.getElementById('tipo_cliente');
        if (urlParams.get('tipo_cliente')) {
            filtrosActivos.push('Tipo cliente: ' + urlParams.get('tipo_cliente'));
            if (tipoClienteSelect) tipoClienteSelect.value = urlParams.get('tipo_cliente');
        }
        
        // --- Fechas ---
        const fechaInicioInput = document.getElementById('fecha_inicio');
        const fechaFinInput = document.getElementById('fecha_fin');
        if (urlParams.get('fecha_inicio')) {
            filtrosActivos.push('Desde: ' + urlParams.get('fecha_inicio'));
            if (fechaInicioInput) fechaInicioInput.value = urlParams.get('fecha_inicio');
        }
        if (urlParams.get('fecha_fin')) {
            filtrosActivos.push('Hasta: ' + urlParams.get('fecha_fin'));
            if (fechaFinInput) fechaFinInput.value = urlParams.get('fecha_fin');
        }

        if (urlParams.get('archivadas') === '0') {
            filtrosActivos.push('Solo activas');
        }

        if (urlParams.get('archivadas') === '1') {
            filtrosActivos.push('Solo archivadas');
        }

        if (!fechaInicioInput && !fechaFinInput) {
            // Solo mostrar default si no hay fechas específicas
            const defaultVal = urlParams.get('default') || '0';
            if (defaultVal === '1') filtrosActivos.push('Solo de hoy');
            if (defaultVal === '2') filtrosActivos.push('Esta semana');
            if (defaultVal === '3') filtrosActivos.push('Este mes');
        }
        
        // Mostrar los filtros activos
        if (filtrosActivos.length > 0) {
            var tagsHtml = filtrosActivos.map(function(filtro) {
                return '<span class="filtro-tag">' + filtro + '</span>';
            }).join(' ');
            
            $('#filtrosActivosList').html(tagsHtml);
            $('#filtrosActivosContainer').show();
            $('#btnFiltrosBusqueda').text(" Filtros de busqueda ("+filtrosActivos.length+")");
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
            // DATATABLE PARA COTIZACIONES FUSIONADAS
            $(`#cotizacionesTable`).DataTable({
                ordering: true,
                order: [[8, 'desc']],
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
        } else if (cot === 'f') {
            containerFusionadas.classList.remove('d-none');
            containerUnicas.classList.add('d-none');
            // DATATABLE PARA COTIZACIONES FUSIONADAS
            $(`#cotizacionesTableFusionadas`).DataTable({
                ordering: true,
                order: [[9, 'desc']],
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
        }
    }

    function cancelarAgrupacion() {
        // Obtener todos los parámetros GET de la URL
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.delete('agru');
        // Reconstruir la URL sin 'agru'
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.location.href = newUrl;
    }

    // ************ INICIALIZACION **************
    const urlInicial = new URL(window.location.href);
    const tabs = document.querySelectorAll('#cotTabs .nav-link');
    const containerUnicas = document.getElementById('containerUnicas');
    const containerFusionadas = document.getElementById('containerFusionadas');
    const params = new URLSearchParams(window.location.search);
    const cotActual = urlInicial.searchParams.get('cot') || 'u';

    if (!urlInicial.searchParams.has('cot')) {
        // Agregar cot=u sin eliminar otros parametros
        urlInicial.searchParams.set('cot', 'u');

        const savedDefault = localStorage.getItem("filtroDefault") || 0;
        if (savedDefault !== null) {
            // Buscar el input con ese valor
            const radio = document.querySelector(`input[name="default"][value="${savedDefault}"]`);
            if (radio) {
                radio.checked = true;
            }
        }
        // Solo si no hay fecha especifica y no hay default en la URL
        if (!urlInicial.searchParams.has('default')) {
            urlInicial.searchParams.set('default', savedDefault);
            window.location.replace(urlInicial.toString());
            return;
        }
        console.log("localstorage: ", savedDefault);

        history.replaceState({}, '', urlInicial.toString());
        
    }

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

    mostrarTablaPorCot(cotActual);
    mostrarFiltrosActivos();
    
    $("#overlay").addClass("d-none");

    // Verificar si ya existe la preferencia en localStorage
    // if (!localStorage.getItem("FiltrosActualizados")) {
    //     Swal.fire({
    //         title: 'Actualizacion',
    //         text: 'Filtros de busqueda actualizados. Nuevo filtro de carga por default de cotizaciones.',
    //         icon: 'info',
    //         confirmButtonText: 'Entendido',
    //         width: '400px',
    //         padding: '10px',
    //         position: 'bottom-end',
    //         toast: true,
    //         showConfirmButton: true,
    //         showCloseButton: false,
    //         input: 'checkbox',
    //         inputPlaceholder: 'No mostrar nuevamente',
    //         inputAttributes: {
    //         id: 'noMostrarCheckbox'
    //         }
    //     }).then((result) => {
    //         if (result.isConfirmed && result.value) {
    //             // Guardar preferencia en localStorage
    //             localStorage.setItem("FiltrosActualizados", "1");
    //         }
    //     });
    // }
    

    // ************** EVENTOS DEL DOM **************
    // Evento click para cambiar active y mostrar tabla correcta
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            // Cambiar active en tabs
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const url = new URL(this.href);
            const cot = url.searchParams.get('cot') || 'u';

            window.location.href = url.toString();
        });
    });
    // Cuando cambie el radio, guardarlo en localstorage
    document.querySelectorAll('input[name="default"]').forEach(radio => {
        radio.addEventListener('change', function () {
            localStorage.setItem("filtroDefault", this.value);
        });
    });
    // COMENZAR LA FUNCIONALIDAD DE FUSIONAR 
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
        // $.ajax({
        //     url: "../ajax/ajax_notificacion.php",
        //     type: "POST",
        //     data: { mensaje: "Filtros aplicados" },
        //     success: function(response) {
        //         console.log("Notificacion enviada: ", response);
        //     },
        //     error: function(error) {
        //         console.error("Error al enviar la notificacion: ", error);
        //     }
        // });
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
    $("#cotizacionesTable").on('click', ".btn-version-cotizacion", function () {
        let dataIdCotizacion = $(this).data('id-cotizacion');
        $("#inputIdCotizacionFormato").val(dataIdCotizacion);
    });
    // enviar el formulario de seleccionar version de formato pdf
    $("#formVersionCotizacion").on("submit", function (e) {
        e.preventDefault();

        const valorSeleccionado = $('#formVersionCotizacion input[name="formato"]:checked').val();

        if (valorSeleccionado === 'cliente') {
            $(this).attr("action", "../includes/functions/generar_cotizacion.php");
        } else if (valorSeleccionado === 'interno') {
            $(this).attr("action", "../includes/functions/generar_pdf.php");
        } else {
            alert("Selecciona una opción de formato.");
            return;
        }
        // $.ajax({
        //     url: "../ajax/ajax_notificacion.php",
        //     type: "POST",
        //     data: { mensaje: "Generar cotizacion "+ valorSeleccionado},
        //     success: function(response) {
        //         console.log("Notificacion enviada: ", response);
        //     },
        //     error: function(error) {
        //         console.error("Error al enviar la notificacion: ", error);
        //     }
        // });

        this.submit();
    });
    // CLICK para seleccionar versión del formato de cotización de las fusionadas
    $("#cotizacionesTableFusionadas").on('click', ".btn-version-cotizacionF", function () {
        let dataIdFusion = $(this).data('id-fusion');
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
        //$dataIdCotizacion=$(this).data('id-cotizacion');
        //$dataCorreoCliente=$(this).data('correo-cliente');

        //$("#inputIdCotizacion").val($dataIdCotizacion);
        //$("#spanIdCotizacion, #spanIdCotizacion2").text($dataIdCotizacion);
        //$("#inputAsuntoCorreo").val("Cotizacion de sello SRS. ID: "+ $dataIdCotizacion);
    });
    //CLICK A Enviar a produccion
    $(".btn-enviar-produccion").on('click', function(){
        $dataIdCotizacionProduccion=$(this).data('id-cotizacion');

        $("#inputCotizacionProduccion").val($dataIdCotizacionProduccion);
    });
    // CLICK para saber cual cotizacion archivar
    $("#cotizacionesTable").on('click', ".btn-archivar-cotizacion", function () {
        const dataIdCotizacionA = $(this).data('id-cotizacion');
        var dataArchivada = $(this).data('archivada');
        if(dataArchivada == 0){
            dataArchivada = 1;
            $("#infoArchivada").text("Si archiva la cotización no podrá usarla al crear nuevas requisiciones.");
        }else{
            dataArchivada = 0;
            $("#infoArchivada").text("Después de esta accion ya podrá usar la cotización al crear nuevas requisiciones.");
        }
        $("#inputArchivar").val(dataIdCotizacionA);
        $("#inputNextValor").val(dataArchivada);
    });
    // solicitud para archivar la cotizacion seleccionada
    $("#btnArchivar").on("click", function(){
        var idCotizacionArchivar = $("#inputArchivar").val();
        var nextValue = $("#inputNextValor").val();
        $(this).addClass("d-none");
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
        $.ajax({
            url: "../ajax/ajax_notificacion.php",
            type: "POST",
            data: { mensaje: "Cotizacion archivada"},
            success: function(response) {
                console.log("Notificacion enviada: ", response);
            },
            error: function(error) {
                console.error("Error al enviar la notificacion: ", error);
            }
        });
    });
    // CLICK para saber cual agrupacion archivar
    $("#cotizacionesTableFusionadas").on('click', ".btn-archivar-cotizacion2", function () {
        const dataIdFusionA = $(this).data('id-fusion');
        var dataArchivada2 = $(this).data('archivada');
        if(dataArchivada2 == 0){
            dataArchivada2 = 1;
            $("#infoArchivada2").text("Ninguna de las cotizaciones de esta agrupación podrá ser usada al crear nuevas requisiciones.");
        }else{
            dataArchivada2 = 0;
            $("#infoArchivada2").text("Despues de esta acción ya podrá usar las cotizaciones de la agrupación al crear nuevas requisiciones.");
        }
        $("#inputArchivar2").val(dataIdFusionA);
        $("#inputNextValor2").val(dataArchivada2);
    });
    // solicitud para archivar la agrupacion seleccionada
    $("#btnArchivar2").on("click", function(){
        var idFusionArchivar = $("#inputArchivar2").val();
        var nextValue2 = $("#inputNextValor2").val();
        $(this).addClass("d-none");
        $.ajax({
            url: '../ajax/archivar_fusion.php',
            method: 'POST',
            data: {
                id_fusion: idFusionArchivar,
                archivada: nextValue2
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
        $.ajax({
            url: "../ajax/ajax_notificacion.php",
            type: "POST",
            data: { mensaje: "Agrupación archivada"},
            success: function(response) {
                console.log("Notificacion enviada: ", response);
            },
            error: function(error) {
                console.error("Error al enviar la notificacion: ", error);
            }
        });
    });
    // CLICK para saber cual fusion romper
    $(".btn-romper-fusion").on('click', function () {
        const dataIdRomperFusion = $(this).data('id-fusion');
        $("#inputIdRomperFusion").val(dataIdRomperFusion);
    });
    // ROMPER la agrupacion de las cotizaciones
    $("#btnUnlink").on("click", function(){
        var idRomperFusion = $("#inputIdRomperFusion").val();
        $(this).addClass("d-none");
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
        $.ajax({
            url: "../ajax/ajax_notificacion.php",
            type: "POST",
            data: { mensaje: "Fusion destruida"},
            success: function(response) {
                console.log("Notificacion enviada: ", response);
            },
            error: function(error) {
                console.error("Error al enviar la notificacion: ", error);
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
        $(this).addClass("d-none");
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
        $.ajax({
            url: "../ajax/ajax_notificacion.php",
            type: "POST",
            data: { mensaje: "Ocurrio una fusion"},
            success: function(response) {
                console.log("Notificacion enviada: ", response);
            },
            error: function(error) {
                console.error("Error al enviar la notificacion: ", error);
            }
        });
    });
    // CLICK CERRAR MODAL 
    $(".btn-close").on("click", function(){
        $("#formEnviarCorreo, #formEnviarAProduccion")[0].reset();
    });
    // CANCELAR AGRUPACION
    $("#btnCancelFusion").on("click", function(){
        cancelarAgrupacion();
    });
    // AL CAMBIAR DE REMITENTE EN ENVIAR CORREO A CLIENTE
    $("#correoRemitente").on("change", function(){
        let valueRemitente =  $($(this)).val();
        if(valueRemitente == "cotizador"){
            $("#pAsunto").removeClass("d-none");
            $("#inputAsuntoCorreo").addClass("d-none");
            $("#pCuerpo").removeClass("d-none");
            $("#inputCuerpoCorreo").addClass("d-none");
        }else if(valueRemitente == "sesion"){
            $("#inputAsuntoCorreo").removeClass("d-none");
            $("#pAsunto").addClass("d-none");
            $("#inputCuerpoCorreo").removeClass("d-none");
            $("#pCuerpo").addClass("d-none");
        }
    });

    var anchoVentanaInicial = window.innerWidth;
    var anchoPantallaInicial = screen.width;
    var zoomInicial = anchoVentanaInicial / anchoPantallaInicial * 100;

    function detectarZoom() {
        var anchoVentana = window.innerWidth;
        var anchoPantalla = screen.width;
        var zoom = anchoVentana / anchoPantalla * 100;

        if ((zoom < 98 || zoom > 100) && anchoPantalla>= 991) {
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
    if(anchoPantallaInicial<= 991){
        $("#btnFiltrosBusqueda").html('<i class="bi bi-funnel"></i>');
        $("#btnInitFusionar").html('<i class="bi bi-link" style="font-size:20px !important;"></i>');
        $("#btnEnviarCorreo").html('<i class="bi bi-envelope"></i>');

    }
});
