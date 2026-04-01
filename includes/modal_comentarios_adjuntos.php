<!-- Modal para comentarios y adjuntos -->
<div class="modal fade" id="modalComentariosAdjuntos" tabindex="-1" aria-labelledby="modalComentariosAdjuntosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalComentariosAdjuntosLabel">
                    Comentarios y archivos adjuntos
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Contenedor principal -->
                <div id="contenedorComentariosAdjuntos">
                    <!-- Los registros existentes se cargarán aquí -->
                </div>
                <div id="containerNoHay" class="text-center mb-2">
                    <small class="fst-italic text-secondary">No hay comentarios adjuntos</small>
                </div>
                <!-- Formulario para agregar nuevo (inicialmente oculto) -->
                <?php if(($tipo_usuario == "CNC" && $rol_usuario == "Gerente") 
                        || $tipo_usuario == "Administrador" 
                        || $tipo_usuario == "Vendedor"
                        || $tipo_usuario == "Sistemas"): ?>
                    <div id="formularioNuevo" class="d-none">
                        <div class="card border-secondary-subtle border-2 mb-3">
                            <div class="card-body">
                                <form id="formComentarioAdjunto">
                                    <input type="hidden" id="inputIdCotizacionAdicion" name="id_cotizacion">
                                    <!-- Selector para origen "requi" (inicialmente oculto) -->
                                    <div id="selectorCotizacionContainer" class="mb-3 d-none">
                                        <label for="selectCotizacionRequisicion" class="form-label fw-bold">Seleccionar cotización *</label>
                                        <select class="form-select" id="selectCotizacionRequisicion" name="id_cotizacion_requi" required>
                                            <option value="" selected disabled>Selecciona una cotización...</option>
                                            <!-- Las opciones se llenarán dinámicamente -->
                                        </select>
                                        <div class="form-text">Selecciona a qué cotización de la requisición pertenece este comentario</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="inputComentarioAdjunto" class="form-label fw-bold">Comentario *</label>
                                        <textarea class="form-control" id="inputComentarioAdjunto" name="comentario" rows="2" 
                                                maxlength="98" required></textarea>
                                        <div class="form-text text-end">
                                            <span id="contadorCaracteres">0/98</span> caracteres
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="inputAdjunto" class="form-label fw-bold">Archivo adjunto</label>
                                        <div id="areaAdjunto">
                                            <button type="button" class="input-file-simple w-100" id="btnSeleccionarArchivo">
                                                <i class="bi bi-paperclip"></i> Añadir adjunto
                                            </button>
                                            
                                            <input type="file" class="d-none" id="inputAdjunto" name="nombre_archivo" required>
                                            <div id="archivoSeleccionado" class="d-none mt-2 p-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span id="nombreArchivo" class="text-secondary"></span>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnQuitarArchivo">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn-eliminar" id="btnCancelarFormulario">Cancelar</button>
                                        <button type="button" class="btn-general" id="btnAgregarRegistro">Agregar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botón para agregar nuevo comentario -->
                    <div id="botonAgregarInicial" class="text-center">
                        <div class="border-dashed p-4 rounded cursor-pointer" id="btnAgregarComentario">
                            <i class="bi bi-plus-lg fs-1 text-muted"></i>
                            <div class="text-muted">Agregar</div>
                        </div>
                    </div>
                <?php endif;?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-general" data-bs-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    let idCotizacionActual = '';
    let origenComentariosAdjuntos = '';
    let idRequisicionActual = '';
    let esMia = "0";


    // ============================================================
    //              ******** FUNCIONES ********
    // =========================================================== 
    // Función para cargar registros existentes - CORREGIDA
    function cargarRegistrosComentarios(origen, idRequisicion) {
        if (origen == 'coti') {
            $.ajax({
                url: '../ajax/cargar_comentarios_adjuntos.php',
                type: 'GET',
                data: {
                    origen: 'coti',
                    id_cotizacion: idCotizacionActual // ← Usar la variable global
                },
                dataType: 'json',
                success: function(response) {
                    mostrarRegistros(response.registros || []);
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar registros:', error);
                    mostrarRegistros([]);
                }
            });
        } else {
            $.ajax({
                url: '../ajax/cargar_comentarios_adjuntos.php',
                type: 'GET',
                data: {
                    origen: 'requi',
                    id_requisicion: idRequisicion // ← Usar el parámetro
                },
                dataType: 'json',
                success: function(response) {
                    mostrarRegistros(response.registros || []);
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar registros:', error);
                    mostrarRegistros([]);
                }
            });
        }
    }
    // Función para mostrar los registros en el modal
    function mostrarRegistros(registros) {
        const contenedor = $('#contenedorComentariosAdjuntos');
        contenedor.empty();
        
        if (registros.length == 0) {
            // No hay registros, mostrar solo el botón de agregar
            if(esMia == "0"){
                $("#botonAgregarInicial").addClass("d-none");
            }else{
                $("#botonAgregarInicial").removeClass("d-none");
            }
            $('#containerNoHay').removeClass('d-none');
            $('#formularioNuevo').addClass('d-none');
            return;
        }else{
            $('#containerNoHay').addClass('d-none');
        }
        
        // Mostrar registros existentes
        registros.forEach(registro => {
            const elementoRegistro = crearElementoRegistro(registro);
            contenedor.append(elementoRegistro);
        });
        
        // Mostrar botón para agregar nuevo (debajo de los registros)
        if(esMia == "0"){
            $("#botonAgregarInicial").addClass("d-none");
        }else{
            $("#botonAgregarInicial").removeClass("d-none");
        }
        $('#formularioNuevo').addClass('d-none');
    }

    // Función para crear el HTML de un registro
    function crearElementoRegistro(registro) {
        const archivoHTML = registro.ruta_adjunto ? 
            `<div class="mt-2">
                <a href="${"../"+registro.ruta_adjunto}" class="archivo-adjunto" target="_blank">
                    <i class="bi bi-paperclip"></i> ${obtenerNombreArchivo(registro.ruta_adjunto)}
                </a>
            </div>` : '';

        let mostrarEliminar = "";
        if(esMia == "0"){
            mostrarEliminar = 'd-none';
        }
        
        return `
            <div class="registro-comentario">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="col-11 flex-grow-1">
                        <h5 class="mb-1">Id cotización: ${registro.id_cotizacion || ''}</h5>
                        <p class="mb-1 text-break">${registro.comentario || ''}</p>
                        ${archivoHTML}
                        <small class="text-muted">${registro.fecha_creacion || ''}</small>
                    </div>
                    <button type="button" class="btn-eliminar btn-eliminar-registro ${mostrarEliminar}" data-id="${registro.id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
    }

    // Función para extraer nombre del archivo de la ruta
    function obtenerNombreArchivo(ruta) {
        return ruta.split('/').pop() || 'archivo';
    }

    // Función para mostrar el formulario nuevo
    function mostrarFormularioNuevo() {
        if (origenComentariosAdjuntos == 'coti') {
            // Origen cotización: usar campo oculto
            $('#inputIdCotizacionAdicion').val(idCotizacionActual);
            $('#selectorCotizacionContainer').addClass('d-none');
            $('#inputIdCotizacionAdicion').prop('disabled', false);
            $('#selectCotizacionRequisicion').prop('disabled', true);
            
        } else if (origenComentariosAdjuntos == 'requi') {
            // Origen requisición: mostrar selector y cargar opciones
            $('#inputIdCotizacionAdicion').val('');
            $('#selectorCotizacionContainer').removeClass('d-none');
            $('#inputIdCotizacionAdicion').prop('disabled', true);
            $('#selectCotizacionRequisicion').prop('disabled', false);
            
            // Cargar las cotizaciones de la requisición
            cargarCotizacionesRequisicion();
        }
        
        $('#formularioNuevo').removeClass('d-none');
        $('#botonAgregarInicial').addClass('d-none');
        $('#inputComentarioAdjunto').focus();

        // Resetear contador
        $('#contadorCaracteres').text('0/98').removeClass('text-warning text-danger');
    }
    // Función para cargar las cotizaciones de la requisición
    function cargarCotizacionesRequisicion() {
        const $selector = $('#selectCotizacionRequisicion');

        $selector.empty().append('<option value="" selected disabled>Selecciona una cotización...</option>');
        
        $.ajax({
            url: '../ajax/cargar_comentarios_adjuntos.php',
            type: 'GET',
            data: {
                origen: 'requi',
                id_requisicion: idRequisicionActual,
                solo_cotizaciones: true
            },
            dataType: 'json',
            success: function(response) {
                const cotizaciones = response.registros || [];
                
                console.log('IDs únicos encontrados:', cotizaciones); // Para debug
                
                // Llenar el selector con IDs únicos
                cotizaciones.forEach(id_cotizacion => {
                    $selector.append(`<option value="${id_cotizacion}">${id_cotizacion}</option>`);
                });
                
                // Si no hay cotizaciones, mostrar mensaje
                if (cotizaciones.length == 0) {
                    $selector.append('<option value="" disabled>No hay cotizaciones disponibles</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar cotizaciones:', error);
                $selector.append('<option value="" disabled>Error al cargar cotizaciones</option>');
            }
        });
    }

    // Función para ocultar el formulario nuevo
    function ocultarFormularioNuevo() {
        $('#formularioNuevo').addClass('d-none');
        $('#botonAgregarInicial').removeClass('d-none');
        $('#selectorCotizacionContainer').addClass('d-none');
        resetearFormulario();
    }

    // Función para resetear el formulario
    function resetearFormulario() {
        $('#formComentarioAdjunto')[0].reset();
        $('#archivoSeleccionado').addClass('d-none');
        $('#btnSeleccionarArchivo').removeClass('d-none');
        $('#contadorCaracteres').text('0/98').removeClass('text-warning text-danger');
    }

    // Función para agregar nuevo comentario/adjunto
    function agregarComentarioAdjunto() {
        let idCotizacionSeleccionada = '';
        
        // Determinar de dónde obtener el ID de cotización
        if (origenComentariosAdjuntos == 'coti') {
            idCotizacionSeleccionada = $('#inputIdCotizacionAdicion').val();
        } else if (origenComentariosAdjuntos == 'requi') {
            idCotizacionSeleccionada = $('#selectCotizacionRequisicion').val();
            
            if (!idCotizacionSeleccionada) {
                alert('Por favor selecciona una cotización');
                return;
            }
        }
        
        // Validaciones básicas
        const comentario = $('#inputComentarioAdjunto').val().trim();
        const archivo = $('#inputAdjunto')[0].files[0];
        
        if (!comentario) {
            alert('El comentario es requerido');
            return;
        }
        
        // if (!archivo) {
        //     alert('El archivo adjunto es requerido');
        //     return;
        // }
        
            // VALIDACIÓN DE LONGITUD MÁXIMA (98 caracteres)
        if (comentario.length > 98) {
            alert('El comentario no puede exceder los 98 caracteres. Actual: ' + comentario.length + ' caracteres');
            $('#inputComentarioAdjunto').focus();
            return;
        }
        $.ajax({
            url: "../ajax/ajax_notificacion.php",
            type: "POST",
            data: { mensaje: "Se ha enviado un nuevo comentario/adjunto"},
            success: function(response) {
                console.log("Notificacion enviada: ", response);
            },
            error: function(error) {
                console.error("Error al enviar la notificacion: ", error);
            }
        });
        // Crear FormData
        const formData = new FormData();
        formData.append('id_cotizacion', idCotizacionSeleccionada);
        formData.append('comentario', comentario);
        formData.append('nombre_archivo', archivo);
        
        // Mostrar loading state
        $('#btnAgregarRegistro').prop('disabled', true).text('Agregando...');
        
        $.ajax({
            url: '../ajax/agregar_comentario_adjunto.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                $('#btnAgregarRegistro').prop('disabled', false).text('Agregar');
                
                if (response.success) {
                    resetearFormulario();
                    ocultarFormularioNuevo();
                    cargarRegistrosComentarios(origenComentariosAdjuntos, idRequisicionActual);
                } else {
                    alert('Error: ' + (response.error || 'No se pudo agregar el registro'));
                }
            },
            error: function(xhr, status, error) {
                $('#btnAgregarRegistro').prop('disabled', false).text('Agregar');
                alert('Error de conexión: ' + error);
            }
        });
    }

    // Función para eliminar registro
    function eliminarRegistro(idRegistro) {
        if (!confirm('¿Estás seguro de que quieres eliminar este registro?')) {
            return;
        }
        
        $.ajax({
            url: '../ajax/eliminar_comentario_adjunto.php',
            type: 'POST',
            data: {
                id: idRegistro
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    cargarRegistrosComentarios(origenComentariosAdjuntos, idRequisicionActual); // Recargar lista
                } else {
                    alert('Error: ' + (response.error || 'No se pudo eliminar el registro'));
                }
            },
            error: function(xhr, status, error) {
                alert('Error de conexión: ' + error);
            }
        });
    }


    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ===========================================================  
    $(document).ready(function() {
        // 1. Al hacer click en el botón principal del modal
        $(document).on('click', '.btn-modal-comentarios-adjuntos', function() {
            // Resetear variables globales
            idCotizacionActual = '';
            origenComentariosAdjuntos = '';
            idRequisicionActual = '';
            
            // Obtener datos del botón clickeado
            const $boton = $(this);
            esMia = $boton.data('es-mia') || "0";
            origenComentariosAdjuntos = $boton.data('origen');
            
            // Validar origen
            if (origenComentariosAdjuntos !== 'coti' && origenComentariosAdjuntos !== 'requi') {
                alert('Error: Origen no válido');
                return;
            }
            
            if (origenComentariosAdjuntos == 'coti') {
                // Obtener ID de cotización
                idCotizacionActual = $('#inputIdCotizacion').val() || $boton.data('id_cotizacion');
                
                if (!idCotizacionActual) {
                    alert('Error: No se encontró el ID de cotización');
                    return;
                }
                
                console.log('Modal abierto desde COTIZACIÓN:', idCotizacionActual);
                
            } else if (origenComentariosAdjuntos == 'requi') {
                // Obtener ID de requisición
                idRequisicionActual = $boton.data('id_requisicion');
                
                if (!idRequisicionActual) {
                    alert('Error: No se encontró el ID de requisición');
                    return;
                }
                
                console.log('Modal abierto desde REQUISICIÓN:', idRequisicionActual);
            }
            
            // Mostrar el modal
            $('#modalComentariosAdjuntos').modal('show');
            
            // Cargar los registros existentes
            cargarRegistrosComentarios(origenComentariosAdjuntos, idRequisicionActual);
        });
        // 2. Al hacer click en el botón "Agregar"
        $(document).on('click', '#btnAgregarComentario', function() {
            mostrarFormularioNuevo();
        });
        // 3. Al hacer click en "Cancelar" del formulario
        $(document).on('click', '#btnCancelarFormulario', function() {
            ocultarFormularioNuevo();
            resetearFormulario();
        });
        // 4. Manejo de archivos adjuntos
        $(document).on('click', '#btnSeleccionarArchivo', function() {
            $('#inputAdjunto').click();
        });
        
        $('#inputAdjunto').on('change', function() {
            const file = this.files[0];
            if (file) {
                $('#nombreArchivo').text(file.name);
                $('#archivoSeleccionado').removeClass('d-none');
                $('#btnSeleccionarArchivo').addClass('d-none');
            }
        });
        
        $(document).on('click', '#btnQuitarArchivo', function() {
            $('#inputAdjunto').val('');
            $('#archivoSeleccionado').addClass('d-none');
            $('#btnSeleccionarArchivo').removeClass('d-none');
        });
        // 5. Envío del formulario
        $('#btnAgregarRegistro').on('click', function(e) {
            //e.preventDefault();
            agregarComentarioAdjunto();
        });
        // 6. Eliminar registro
        $(document).on('click', '.btn-eliminar-registro', function() {
            const idRegistro = $(this).data('id');
            eliminarRegistro(idRegistro);
        });
        // Versión mejorada del contador con cambios visuales en el textarea
        $('#inputComentarioAdjunto').on('input', function() {
            const $this = $(this);
            const longitud = $this.val().length;
            const maximo = 98;
            const $contador = $('#contadorCaracteres');
            
            $contador.text(longitud + '/' + maximo);
            
            // Remover todas las clases primero
            $this.removeClass('warning danger');
            $contador.removeClass('text-warning text-danger');
            
            // Aplicar estilos según la longitud
            if (longitud == 0) {
                // Estado normal
            } else if (longitud > 80 && longitud < maximo) {
                // Advertencia (amarillo)
                $this.addClass('warning');
                $contador.addClass('text-warning');
            } else if (longitud >= maximo) {
                // Peligro (rojo)
                $this.addClass('danger');
                $contador.addClass('text-danger');
            }
        });
    });

</script>

<style>
    /* Estilos para el modal */
    .border-dashed {
        border: 2px dashed #dee2e6;
        transition: all 0.3s ease;
    }

    .border-dashed:hover {
        border-color: #55AD9B;
        background-color: #f8f9fa;
    }

    .border-solid {
        border: 1px solid #dee2e6;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    /* Estilos para los registros existentes */
    .registro-comentario {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #f8f9fa;
    }

    /* Loading state para el botón */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Mejorar la visualización de archivos */
    .archivo-adjunto {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        color: #0d6efd;
        background-color: #e9ecef;
        border-radius: 0.375rem;
        text-decoration: none;
        border: 1px solid #dee2e6;
    }

    .archivo-adjunto:hover {
        text-decoration: underline;
        background-color: #dee2e6;
    }

    .input-file-simple {
        width: 100%;
        text-align: center;
        background-color: #fff;
        padding: 12px;
        border: none;
        border-radius: 5px;
        color: #000;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 0px 1px 1px #55AD9B;
    }
    .input-file-simple:hover {
        background-color: #95D2B3; 
        color: #fff;
    }
    #modalComentariosAdjuntos .modal-body textarea, #modalComentariosAdjuntos .modal-body select  {
        border: solid 1px #55AD9B;
    }
    #archivoSeleccionado{
        border: solid 1px #55AD9B;
        border-radius: 5px;
        background-color: #e9f7f0;
    }
    /* Estilos para el contador de caracteres */
    .text-warning {
        color: #ffc107 !important;
        font-weight: bold;
    }

    .text-danger {
        color: #dc3545 !important;
        font-weight: bold;
    }

    .form-text {
        font-size: 0.875em;
        margin-top: 0.25rem;
    }

    /* Estilo cuando está cerca del límite */
    #inputComentarioAdjunto.warning {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }

    /* Estilo cuando excede el límite */
    #inputComentarioAdjunto.danger {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    .comentarios-wrapper {
        position: relative;
        display: inline-block;
    }

    .badge-comentarios {
        position: absolute;
        top: -6px;
        right: -6px;
        background-color: #0d6efd; /* azul bootstrap */
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        min-width: 18px;
        height: 18px;
        line-height: 1;
        text-align: center;
        align-content: center;
        border-radius: 100%;
        border: 1px solid white;
        pointer-events: none;
    }

</style>