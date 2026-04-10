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
                    text: 'Hubo un error al actualizar alguna cotización. ' + errores + ' Si el problema persiste, contacte el área de sistemas.',
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

});
