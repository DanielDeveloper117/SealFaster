// ============================================================
// VARIABLES GLOBALES
// ============================================================
// ============================================================
// FUNCIONES
// ============================================================
// JavaScript para mostrar filtros activos
function mostrarFiltrosActivos() {
    const filtros = [];
    const container = document.getElementById('filtrosActivosContainer');
    const list = document.getElementById('filtrosActivosList');
    
    // Estatus
    const estatusSelect = document.getElementById('estatus');
    if (estatusSelect.value) {
        const text = estatusSelect.options[estatusSelect.selectedIndex].text;
        filtros.push(`<span class="filtro-tag">Estatus: ${text}</span>`);
    }

    // Sucursal
    const filtroSucursal = document.getElementById('filtroSucursal');
    if (filtroSucursal.value) {
        const text = filtroSucursal.options[filtroSucursal.selectedIndex].text;
        filtros.push(`<span class="filtro-tag">Origen/Sucursal: ${text}</span>`);
    }
    
    // Revision
    const revisionSelect = document.getElementById('revision');
    if (revisionSelect && revisionSelect.value) {
        const text = revisionSelect.options[revisionSelect.selectedIndex].text;
        filtros.push(`<span class="filtro-tag">Revisión: ${text}</span>`);
    }

    // Fechas
    const fechaInicio = document.getElementById('fecha_inicio').value;
    const fechaFin = document.getElementById('fecha_fin').value;
    if (fechaInicio && fechaFin) {
        filtros.push(`<span class="filtro-tag">Fecha: ${fechaInicio} a ${fechaFin}</span>`);
    } else if (fechaInicio) {
        filtros.push(`<span class="filtro-tag">Desde: ${fechaInicio}</span>`);
    } else if (fechaFin) {
        filtros.push(`<span class="filtro-tag">Hasta: ${fechaFin}</span>`);
    }
    
    // Default
    const defaultRadios = document.querySelectorAll('input[name="default"]:checked');
    if (defaultRadios.length > 0 && defaultRadios[0].value !== '1') {
        const labels = {
            '0': 'Todas',
            '2': 'Esta semana',
            '3': 'Este mes'
        };
        if (labels[defaultRadios[0].value]) {
            filtros.push(`<span class="filtro-tag">${labels[defaultRadios[0].value]}</span>`);
        }
    }
    
    // Orden
    const ordenSelect = document.getElementById('orden');
    if (ordenSelect.value === 'asc') {
        filtros.push(`<span class="filtro-tag">Orden: Ascendente</span>`);
    }
    
    // Mostrar u ocultar contenedor
    if (filtros.length > 0) {
        list.innerHTML = filtros.join('');
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

// Función para limpiar filtros
function limpiarTodosFiltros() {
    document.getElementById('formFiltros').reset();
    document.getElementById('estatus').value = '';
    document.getElementById('filtroSucursal').value = '';
    document.getElementById('fecha_inicio').value = '';
    document.getElementById('fecha_fin').value = '';
    if(document.getElementById('revision')) document.getElementById('revision').value = '';
    document.querySelector('input[name="default"][value="2"]').checked = true;
    document.getElementById('orden').value = 'des';
    
    // Actualizar vista de filtros activos
    mostrarFiltrosActivos();
}

// Función para calcular desbaste según material
function calcularDesbaste(material) {
    console.log(material);
    const materialesBlandos = [
        'H-ECOPUR', 'ECOSIL', 'ECORUBBER 1', 'ECORUBBER 2',
        'ECORUBBER 3', 'ECOPUR'
    ];

    const materialesDuros = [
        'ECOTAL', 'ECOMID', 'ECOFLON 1', 'ECOFLON 2', 'ECOFLON 3'
    ];

    if (materialesBlandos.includes(material.toUpperCase())) {
        return 2.00;
    } else if (materialesDuros.includes(material.toUpperCase())) {
        return 2.50;
    } else {
        return 2.50; // Por defecto
    }
}
// Cuando CNC desea editar las medidas cuando por ejemplo eran medidas muestra
function ajaxTraerCotizaciones(idRequisicion) {
    $.ajax({
        url: '../ajax/traer_medidas_cotizaciones.php',
        type: 'get',
        data: {
            id_requisicion: idRequisicion
        },
        dataType: 'json',
        success: function (data) {
            $('#modalEditarMedidas .modal-body').empty(); // Corrige selector

            if (data.length > 0) {
                $.each(data, function (index, item) {
                    $('#modalEditarMedidas .modal-body').append(`
                        <div style="width:100%; margin-bottom:20px;">
                            <h5 class="modal-title">Id cotización: <span>${item.id_cotizacion}</span></h5>
                            <table class="tabla-medidas table table-bordered border border-2 tabla-billets" data-id_cotizacion="${item.id_cotizacion}">
                                <thead>
                                    <tr>
                                        <th>DI MM</th>
                                        <th>DI INCH</th>
                                        <th>DE MM</th>
                                        <th>DE INCH</th>
                                        <th>A MM</th>
                                        <th>A INCH</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="number" class="input-text di_sello" name="di_sello" value="${item.di_sello || ''}" step="0.01" min="0"></td>
                                        <td><input type="number" class="input-text di_sello_inch" name="di_sello_inch" value="${item.di_sello_inch || ''}" step="0.0001" min="0"></td>
                                        <td><input type="number" class="input-text de_sello" name="de_sello" value="${item.de_sello || ''}" step="0.01" min="0"></td>
                                        <td><input type="number" class="input-text de_sello_inch" name="de_sello_inch" value="${item.de_sello_inch || ''}" step="0.0001" min="0"></td>
                                        <td><input type="number" class="input-text a_sello" name="a_sello" value="${item.a_sello || ''}" step="0.01" min="0"></td>
                                        <td><input type="number" class="input-text a_sello_inch" name="a_sello_inch" value="${item.a_sello_inch || ''}" step="0.0001" min="0"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `);
                });
            } else {
                $('#modalEditarMedidas .modal-body').append('<p>No hay cotizaciones disponibles para esta requisición.</p>');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error al realizar la petición AJAX:', error);
            $('#modalEditarMedidas .modal-body').append('<h5>Error en ajax</h5>');
            sweetAlertResponse("error", "Error", "Error al consultar cotizaciones: " + error, "none");
        }
    });
}
// ============================================================
// EVENTOS DEL DOM
// ============================================================ 
$(document).ready(function () {
    mostrarFiltrosActivos();
    
    // Actualizar filtros activos cuando cambien los campos
    document.getElementById('estatus').addEventListener('change', mostrarFiltrosActivos);
    document.getElementById('filtroSucursal').addEventListener('change', mostrarFiltrosActivos);
    document.getElementById('fecha_inicio').addEventListener('change', mostrarFiltrosActivos);
    document.getElementById('fecha_fin').addEventListener('change', mostrarFiltrosActivos);
    if(document.getElementById('revision')) document.getElementById('revision').addEventListener('change', mostrarFiltrosActivos);
    document.querySelectorAll('input[name="default"]').forEach(radio => {
        radio.addEventListener('change', mostrarFiltrosActivos);
    });
    document.getElementById('orden').addEventListener('change', mostrarFiltrosActivos);
    // ESCUCHAR INPUTS DE MEDIDAS EN MODAL EDITAR MEDIDAS
    $('#modalEditarMedidas').on('input', 'input[type="number"]', function () {
        const $input = $(this);
        const clase = $input.attr('class').split(' ').find(c => c !== 'input-text'); // Evita 'input-text' genérica
        const valueRaw = $input.val();
        const value = parseFloat(valueRaw);

        if (!clase || isNaN(value)) {
            return;
        }

        let claseRelacionada = '';
        let valorConvertido = 0;

        if (clase.includes('_inch')) {
            claseRelacionada = clase.replace('_inch', '');
            valorConvertido = value * 25.4;
        } else {
            claseRelacionada = clase + '_inch';
            valorConvertido = value / 25.4;
        }

        // Buscar input relacionado dentro de la misma tabla, por clase exacta
        const $tabla = $input.closest('table');
        const $inputRelacionado = $tabla.find(`input.${claseRelacionada}`);

        if ($inputRelacionado.length == 0) {
            return;
        }

        const decimales = claseRelacionada.includes('_inch') ? 4 : 2;
        $inputRelacionado.val(valorConvertido.toFixed(decimales));
    });
    // CLICK EDITAR MEDIDAS
    $('#productionTable').on('click', '.btn-editar-medidas', function () {
        $dataIdRequisicion = $(this).data('id-requisicion');

        ajaxTraerCotizaciones($dataIdRequisicion);
    });
    // BOTON GUARDAR LAS MEDIDAS ACTUALIZADAS
    $('#btnGuardarMedidas').on('click', function () {
        const promesas = [];

        $('.tabla-medidas').each(function () {
            const $tabla = $(this);
            const id_cotizacion = $tabla.data('id_cotizacion');
            if (!id_cotizacion) return;

            const datos = {
                id_cotizacion: id_cotizacion
            };

            $tabla.find('input').each(function () {
                const name = $(this).attr('name');
                const value = $(this).val();
                datos[name] = value;
            });

            // Convertir AJAX a promesa y agregarla al array
            const promesa = new Promise((resolve, reject) => {
                $.ajax({
                    url: '../ajax/actualizar_medidas.php',
                    method: 'POST',
                    data: datos,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            console.log(`Cotización ${id_cotizacion} actualizada correctamente`);
                            resolve(id_cotizacion);
                        } else {
                            console.warn(`Error en ${id_cotizacion}: ${response.message}`);
                            reject(`Error en ${id_cotizacion}: ${response.message}`);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error(`Error AJAX en cotización ${id_cotizacion}:`, error);
                        reject(`Error AJAX en cotización ${id_cotizacion}`);
                    }
                });
            });

            promesas.push(promesa);
        });

        // Ejecutar todas las promesas y manejar el resultado global
        Promise.allSettled(promesas).then(resultados => {
            const fallos = resultados.filter(r => r.status === 'rejected');

            if (fallos.length == 0) {
                Swal.fire({
                    title: 'Proceso exitoso',
                    text: 'Medidas guardadas correctamente.',
                    icon: 'success',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#55AD9B',
                });
            } else {
                const errores = fallos.map(f => f.reason).join('\n');
                Swal.fire({
                    title: 'Ocurrió un problema',
                    text: 'Hubo un error al actualizar alguna cotización. ' + errores + 'Si el problema persiste, contacte el área de sistemas.',
                    icon: 'error',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Ok',
                    confirmButtonColor: '#55AD9B',
                });
            }
        });

    });


    // BOTON DE ABRIR EL MODAL DE INICIAR MAQUINADO CNC, TRAER MAQUINAS
    $("#productionTable").on('click', ".btn-iniciar-maquinado", function () {
        let idRequisicion = $(this).data('id-requisicion');

        $.ajax({
            url: '../ajax/maquinas.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data) {

                    $('#inputMaquina').html(`<option value="" selected disabled>Seleccione máquina</option>`);
                    data.forEach(element => {
                        $(`#inputMaquina`).append(
                            `<option value="${element.rol}">${element.rol}</option>`
                        );
                    });

                } else {
                    sweetAlertResponse("warning", "Hubo un problema", data.message, "self");
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al actualizar registro. " + error, "self");
            }
        });

        $('#modalGuardarOperador').modal('show');
        $("#inputIdRequisicionOperador").val(idRequisicion);
    });
    // CLICK SUBMIT A GUARDAR EL OPERADOR CNC
    $("#btnGuardarOperador").on('click', function () {
        let inputMaquina = $("#inputMaquina").val();
        let inputOperadorCNC = $("#inputOperadorCNC").val();
        let inputIdRequisicionOperador = $("#inputIdRequisicionOperador").val();

        if (!inputMaquina) {
            sweetAlertResponse("warning", "Faltan datos", "Seleccione una máquina CNC", "none");
            return;
        }

        $(this).addClass("d-none");
        $.ajax({
            url: '../ajax/guardar_operadorcnc.php',
            type: 'POST',
            data: {
                maquina: inputMaquina,
                operador_cnc: inputOperadorCNC,
                id_requisicion: inputIdRequisicionOperador
            },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "self");
                    $('#modalGuardarOperador').modal('hide');
                    //$("#ContainerQR").css("filter", "blur(0px)");
                } else {
                    sweetAlertResponse("warning", "Hubo un problema", data.message, "self");
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al actualizar registro. " + error, "self");
            }
        });
    });



    // CLICK A DETENER REQUISICION
    $('#productionTable').on('click', '.btn-detener', function () {
        $dataIdRequisicion = $(this).data('id-requisicion');

        $("#inputRequisicionDetener").val($dataIdRequisicion);
    });
    // CLICK SUBMIT A DETENER LA PRODUCCION DE LA REQUISICION
    $("#btnConfirmarDetener").on('click', function () {
        let inputRazonDetener = $("#inputRazonDetener").val();
        let justificacionDetener = $("#justificacionDetener").val();
        let inputRequisicionDetener = $("#inputRequisicionDetener").val();

        if (!inputRazonDetener) {
            sweetAlertResponse("warning", "Faltan datos", "Seleccione una razón del selector", "none");
            return;
        }
        if (!justificacionDetener || justificacionDetener.length < 10) {
            sweetAlertResponse("warning", "Faltan datos", "Ingrese una justificación de mínimo 10 caracteres", "none");
            return;
        }
        if (!inputRequisicionDetener) {
            sweetAlertResponse("warning", "Faltan datos", "Falta el id de requisición. Contactar a sistemas.", "none");
            return;
        }
        $(this).addClass("d-none");
        $.ajax({
            url: '../ajax/detener_produccion.php',
            type: 'POST',
            data: {
                razon: inputRazonDetener,
                justificacion: justificacionDetener,
                id_requisicion: inputRequisicionDetener
            },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "self");

                } else {
                    sweetAlertResponse("warning", "Hubo un problema", data.message, "self");
                }
            },
            error: function (xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                sweetAlertResponse("error", "Error", "Error al actualizar registro. " + error, "self");
            }
        });
    });

        // Traer las barras pendientes por autorizar
    function cargarTablaBarrasPendientes(idRequisicion) {
        $.ajax({
            url: '../ajax/barras_pendientes_autorizar.php',
            type: 'get',
            data: { 
                id_requisicion: idRequisicion
            },
            dataType: 'json',
            success: function(data) {
                $('#tableBarrasPendientes tbody').empty();

                if (data.success && data.billets && data.billets.length > 0) {
                    $.each(data.billets, function(index, billet) {
                        const tieneRemplazo = billet.situacion === "remplazo";
                        const tieneEliminacion = billet.situacion === "eliminacion";
                        const tieneJustificacionRemplazo = billet.justificacion_remplazo && billet.justificacion_remplazo.trim() !== '';
                        const tieneJustificacionExtra = billet.justificacion_extra && billet.justificacion_extra.trim() !== '';
                        const tieneJustificacionEliminacion = billet.justificacion_eliminacion && billet.justificacion_eliminacion.trim() !== '';

                        // Decidir cuál justificación mostrar según la situación solicitada.
                        let mostrarJustificacionTipo = null; // 'extra' | 'remplazo' | 'eliminacion' | null
                        let mostrarJustificacionTexto = '';
                        
                        if (billet.situacion === 'remplazo') {
                            if (tieneJustificacionRemplazo) {
                                mostrarJustificacionTipo = 'remplazo';
                                mostrarJustificacionTexto = billet.justificacion_remplazo;
                            }
                        } else if (billet.situacion === 'eliminacion') {
                            if (tieneJustificacionEliminacion) {
                                mostrarJustificacionTipo = 'eliminacion';
                                mostrarJustificacionTexto = billet.justificacion_eliminacion;
                            }
                        } else if (billet.situacion === 'extra') {
                            if (tieneJustificacionExtra) {
                                mostrarJustificacionTipo = 'extra';
                                mostrarJustificacionTexto = billet.justificacion_extra;
                            }
                        }
                        
                        // Determinar texto del botón y tooltip
                        let textoBoton = "Autorizar acción";
                        let textoBotonRechazo = "Rechazar acción";
                        let textoSmall = "Acción pendiente";
                        let iconoClase = "bi-check-circle";

                        if (tieneRemplazo) {
                            textoBoton = "Autorizar reemplazo de barra";
                            textoBotonRechazo = "Rechazar reemplazo de barra";
                            textoSmall = "Remplazo de barra";
                            iconoClase = "bi-arrow-left-right";
                        } else if (tieneEliminacion) {
                            textoBoton = "Autorizar eliminación de barra";
                            textoBotonRechazo = "Rechazar eliminación de barra";
                            textoSmall = "Eliminación de barra";
                            iconoClase = "bi-trash";
                        } else {
                            // Extra
                            textoBoton = "Autorizar barra extra";
                            textoBotonRechazo = "Rechazar barra extra";
                            textoSmall = "Barra extra";
                            iconoClase = "bi-plus-circle";
                        }
                        
                        $('#tableBarrasPendientes tbody').append(`
                            <tr class="data-row" data-id-control="${billet.id_control}">
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <button type="button" class="btn-auth btn-sm btn-autorizar-barra"
                                                data-id-requisicion="${data.id_requisicion}"
                                                data-id-control="${billet.id_control}"
                                                data-accion="${billet.situacion}"
                                                title="${textoBoton}">
                                            <i class="bi ${iconoClase}"></i> Autorizar
                                        </button>
                                        <button type="button" class="btn-cancel btn-sm btn-rechazar-barra"
                                                data-id-requisicion="${data.id_requisicion}"
                                                data-id-control="${billet.id_control}"
                                                data-accion="${billet.situacion}"
                                                title="${textoBotonRechazo}">
                                            <i class="bi bi-x-octagon"></i> Rechazar
                                        </button>
                                        <small>${textoSmall}</small>
                                    </div>
                                </td>

                                <td>
                                    <input type="text" class="form-control form-control-sm input-disabled perfil_sello" 
                                        value="${billet.perfil_sello || ''}" readonly>
                                </td>
                                
                                <td>
                                    <input type="text" class="form-control form-control-sm input-disabled material" 
                                        value="${billet.material || ''}" readonly>
                                </td>
                                
                                <td>
                                    ${billet.clave_remplazo && billet.clave_remplazo.trim() !== ''
                                        ? `<div class="d-flex flex-column gap-1">
                                            <input type="text" class="form-control form-control-sm input-disabled clave" 
                                                value="${billet.clave || ''}" readonly>
                                            <small class="text-muted">Reemplazar por:</small>
                                            <input type="text" class="form-control form-control-sm input-disabled clave_remplazo" 
                                                value="${billet.clave_remplazo}" readonly>
                                        </div>`
                                        : `<input type="text" class="form-control form-control-sm input-disabled clave" 
                                                value="${billet.clave || ''}" readonly>`
                                    }
                                </td>

                                <td>
                                    ${billet.lp_remplazo && billet.lp_remplazo.trim() !== ''
                                        ? `<div class="d-flex flex-column gap-1">
                                            <input type="text" class="form-control form-control-sm input-disabled lote_pedimento" 
                                                value="${billet.lote_pedimento || ''}" readonly>
                                            <small class="text-muted">Reemplazar por:</small>
                                            <input type="text" class="form-control form-control-sm input-disabled lp_remplazo" 
                                                value="${billet.lp_remplazo}" readonly>
                                        </div>`
                                        : `<input type="text" class="form-control form-control-sm input-disabled lote_pedimento" 
                                                value="${billet.lote_pedimento || ''}" readonly>`
                                    }
                                </td>

                                <td>
                                    ${billet.medida_remplazo && billet.medida_remplazo.trim() !== ''
                                        ? `<div class="d-flex flex-column gap-1">
                                            <input type="text" class="form-control form-control-sm input-disabled medida" 
                                                value="${billet.medida || ''}" readonly>
                                            <small class="text-muted">Reemplazar por:</small>
                                            <input type="text" class="form-control form-control-sm input-disabled medida_remplazo" 
                                                value="${billet.medida_remplazo}" readonly>
                                        </div>`
                                        : `<input type="text" class="form-control form-control-sm input-disabled medida" 
                                                value="${billet.medida || ''}" readonly>`
                                    }
                                </td>

                                <td>
                                    <input type="text" class="form-control form-control-sm input-disabled pz_teoricas" 
                                        value="${billet.pz_teoricas || ''}" readonly>
                                </td>
                                
                                <td>
                                    <input type="text" class="form-control form-control-sm input-disabled altura_pz" 
                                        value="${billet.altura_pz || ''}" readonly>
                                </td>
                            </tr>
                            
                            ${mostrarJustificacionTipo ? `
                            <tr class="row-justificacion">
                                <td colspan="8">
                                    <div class="p-2">
                                        <small class="text-muted d-block mb-1">
                                            ${mostrarJustificacionTipo === 'extra' ? 'Justificación de barra extra para' : 'Justificación para'} <strong>${billet.lote_pedimento || ''}:</strong>
                                        </small>
                                        <input type="text" class="form-control form-control-sm input-disabled ${mostrarJustificacionTipo === 'extra' ? 'justificacion_extra' : 'justificacion_remplazo'}" 
                                            value="${mostrarJustificacionTexto}" readonly>
                                    </div>
                                </td>
                            </tr>
                            ` : ''}
                        `);
                    });
                    
                } else {
                    $('#tableBarrasPendientes tbody').append(`
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                No hay barras pendientes de autorización para esta requisición.
                            </td>
                        </tr>
                    `);
                }
                
                console.log('Fuente de datos:', data.fuente);
            },
            error: function(xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                console.error('Respuesta del servidor:', xhr.responseText);
                sweetAlertResponse("error", "Error", "Error al consultar las barras pendientes: " + error, "none");
            }
        });
    }

    // VER LA TABLA DE BARRAS DE CONTROL DE ALMACEN PARA ENTREGAR
    $(document).on('click', '.btn-barras-pendientes', function(){
        const idRequisicionEntrega = $(this).data('id_requisicion');
        
        $('#modalTableBarrasPendientes .title-form form input').val(idRequisicionEntrega);
        $('#modalTableBarrasPendientes .title-form form button').text(idRequisicionEntrega);
        cargarTablaBarrasPendientes(idRequisicionEntrega);
    });

    // CLICK para mostrar modal de confirmar autorización de barra
    $(document).on('click', '.btn-autorizar-barra', function(){
        const idRequisicion = $(this).data('id-requisicion');
        const idControl = $(this).data('id-control');
        const accion = $(this).data('accion');

        console.log('Autorizar barra', { idRequisicion, idControl, accion });
        if (accion === 'remplazo') {
            $("#modalAutorizarBarra .modal-body p").text("¿Está seguro de autorizar el remplazo de la barra?");
        } else if (accion === 'eliminacion') {
            $("#modalAutorizarBarra .modal-body p").text("¿Está seguro de autorizar la eliminación de la barra?");
        } else {
            $("#modalAutorizarBarra .modal-body p").text("¿Está seguro de autorizar la barra extra?");
        }
        // Pasar valores a los inputs ocultos del modal
        $('#autorizarIdRequisicion').val(idRequisicion);
        $('#autorizarIdControl').val(idControl);
        $('#autorizarAccion').val(accion);

        // Mostrar modal
        $('#modalAutorizarBarra').modal('show');
    });

    // SI AUTORIZAR EL REMPLAZO DE BARRA O LA BARRA EXTRA
    $("#btnConfirmAutorizarBarra").on("click", function(){
        let autorizarIdRequisicion = $("#autorizarIdRequisicion").val();
        let autorizarIdControl = $("#autorizarIdControl").val();
        let autorizarAccion = $("#autorizarAccion").val();

        $("#btnConfirmAutorizarBarra").addClass("d-none");
    
        $.ajax({
            url: '../ajax/autorizar_accion_barra.php',
            type: 'POST',
            data: { 
                id_requisicion: autorizarIdRequisicion,
                id_control: autorizarIdControl,
                accion: autorizarAccion
            },
            dataType: 'json',
            success: function(data) {
                if (data.success) {
                    // Mostrar mensaje de éxito pero mantener el modal abierto
                    sweetAlertResponse("success", "Proceso exitoso", data.message, "none");
                    $("#formAutorizarBarra")[0].reset();
                    // Ocultar el renglón correspondiente en la tabla de barras pendientes
                    try {
                        var selector = '#tableBarrasPendientes tbody tr.data-row[data-id-control="' + autorizarIdControl + '"]';
                        var $row = $(selector);
                        if ($row.length) {
                            $row.hide();
                            // Si existe una fila de justificación justo después, ocultarla también
                            var $next = $row.next('tr.row-justificacion');
                            if ($next.length) {
                                $next.hide();
                            }
                        }
                    } catch (err) {
                        console.error('Error ocultando renglón de barra pendiente:', err);
                    }
                    if(data.no_hay_pendientes){
                        $('#tableBarrasPendientes tbody').append(`
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    No hay barras pendientes de autorización para esta requisición.
                                </td>
                            </tr>
                        `);
                    }
                } else {
                    sweetAlertResponse("warning", "Advertencia", data.message, "none");
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al realizar la petición AJAX:', error);
                console.error('Respuesta del servidor:', xhr.responseText); // Muestra el error enviado por PHP
                sweetAlertResponse("error", "Error", "Ocurrió un error al autorizar la barra", "none");
            },
            complete: function(){
                // Re-habilitar el botón siempre
                $("#btnConfirmAutorizarBarra").removeClass("d-none");
                $('#modalAutorizarBarra').modal('hide');
                
            }
        });

    });

    // CLICK para mostrar modal de rechazar barra
    $(document).on('click', '.btn-rechazar-barra', function(){
        const idRequisicion = $(this).data('id-requisicion');
        const idControl = $(this).data('id-control');
        const accion = $(this).data('accion');

        // Pasar valores a los inputs ocultos del modal de rechazo
        $('#idRequisicionRechazo').val(idRequisicion);
        $('#inputControlRechazo').val(idControl);
        $('#inputAccionRechazo').val(accion);
        // Limpiar textarea previa
        $('#inputRazonRechazo').val('');

        // Mostrar modal
        $('#modalRechazarBarra').modal('show');
    });

    // Enviar rechazo de barra
    $('#btnEnviarRechazo').on('click', function(e){
        e.preventDefault();

        var id_requisicion = $('#idRequisicionRechazo').val();
        var id_control = $('#inputControlRechazo').val();
        var accion = $('#inputAccionRechazo').val();
        var razon = $('#inputRazonRechazo').val().trim();

        if (!razon) {
            sweetAlertResponse('warning', 'Campo requerido', 'Por favor ingresa la razón del rechazo.', 'none');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).text('Enviando...');

        $.ajax({
            url: '../ajax/rechazar_barra.php',
            method: 'POST',
            data: {
                id_requisicion: id_requisicion,
                id_control: id_control,
                accion: accion,
                razon: razon
            },
            dataType: 'json',
            success: function(resp) {
                if (resp && resp.success) {
                    sweetAlertResponse('success', 'Rechazo enviado', resp.message || 'La barra fue rechazada correctamente.', 'none');
                    // Ocultar renglón correspondiente en la tabla de pendientes
                    try {
                        var selector = '#tableBarrasPendientes tbody tr.data-row[data-id-control="' + id_control + '"]';
                        var $row = $(selector);
                        if ($row.length) {
                            $row.hide();
                            var $next = $row.next('tr.row-justificacion');
                            if ($next.length) $next.hide();
                        }
                    } catch (err) {
                        console.error('Error ocultando renglón tras rechazo:', err);
                    }
                    if(resp.no_hay_pendientes){
                        $('#tableBarrasPendientes tbody').append(`
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    No hay barras pendientes de autorización para esta requisición.
                                </td>
                            </tr>
                        `);
                    }
                    // reset form
                    $('#formRechazarBarra')[0].reset();
                    // ocultar modal de rechazo (mantener modal de pendientes abierto)
                    $('#modalRechazarBarra').modal('hide');
                } else {
                    sweetAlertResponse('warning', 'Advertencia', resp.message || 'No se pudo procesar el rechazo.', 'none');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error AJAX al rechazar barra:', xhr.responseText || error);
                sweetAlertResponse('error', 'Error', 'Ocurrió un error al enviar el rechazo.', 'self');
            },
            complete: function() {
                $btn.prop('disabled', false).text('Enviar');
            }
        });
    });
});
