<!-- Modal para localizar barra en requisiciones -->
<div class="modal fade" id="modalLocalizarBarra" tabindex="-1" aria-hidden="true" aria-labelledby="labelModalLocalizarBarra" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="labelModalLocalizarBarra">Localizar Barra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Loader -->
                <div id="localizarBarraLoader" class="text-center d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">Buscando barra, por favor espere...</p>
                </div>

                <!-- Contenedor de resultados -->
                <div id="localizarBarraResultado" class="d-none">
                    <!-- Caso: Barra no encontrada -->
                    <div id="barraNoEncontrada" class="alert alert-info d-none">
                        <h6 class="mb-3">
                            <i class="bi bi-exclamation-circle"></i> La barra no se encontró en ningún folio.
                        </h6>
                        <p class="mb-0">¿Desea liberar esta barra?</p>
                    </div>

                    <!-- Caso: Barra encontrada -->
                    <div id="barraEncontrada" class="d-none">
                        <div id="listaRequisiciones"></div>
                    </div>
                </div>

                <!-- Mensaje de error -->
                <div id="localizarBarraError" class="alert alert-danger d-none"></div>
            </div>
            <div class="modal-footer">
                <!-- Botones para barra no encontrada -->
                <div id="botonesBarraNoEncontrada" class="d-none w-100">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success flex-grow-1" id="btnLiberarBarraNoEncontrada">
                            <i class="bi bi-check-circle"></i> Liberar Barra
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>

                <!-- Botones para barra encontrada -->
                <div id="botonesBarraEncontrada" class="d-none w-100">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // ============================================================
    //          ******** VARIABLES GLOBALES ********
    // ============================================================
    // ============================================================
    //              ******** FUNCIONES ********
    // ============================================================
    // Función para construir tarjeta de requisición
    function construirCardRequisicion(req, lotePedimento, index) {
        var html = '<div class="card mb-3">';
        html += '  <div class="card-header">';
        html += '    <h6 class="mb-0"><strong>Folio:</strong> ' + (req.folio || 'No disponible') + '</h6>';
        html += '  </div>';
        html += '  <div class="card-body">';

        // Información básica
        html += '  <div class="row mb-3">';
        html += '    <div class="col-md-6">';
        html += '      <p><strong>ID Requisición:</strong> ' + (req.id_requisicion || 'No disponible') + '</p>';
        html += '      <p><strong>Estatus:</strong> <span class="badge bg-' + obtenerColorEstatus(req.estatus) + '">' + (req.estatus || 'No disponible') + '</span></p>';
        html += '      <p><strong>Vendedor:</strong> ' + (req.nombre_vendedor || 'No disponible') + '</p>';
        html += '    </div>';
        html += '    <div class="col-md-6">';
        html += '      <p><strong>Fecha Inserción:</strong> ' + formatearFecha(req.fecha_insercion) + '</p>';
        html += '      <p><strong>Comentario:</strong> ' + (req.comentario || 'No hay comentarios') + '</p>';
        html += '    </div>';
        html += '  </div>';

        // Información de autorización (si aplica)
        if (req.autorizo || req.fecha_autorizacion) {
            html += '  <div class="row mb-3 border-top pt-3">';
            html += '    <div class="col-md-6">';
            html += '      <p><strong>Autorizó:</strong> ' + (req.autorizo || 'No disponible') + '</p>';
            html += '    </div>';
            html += '    <div class="col-md-6">';
            html += '      <p><strong>Fecha Autorización:</strong> ' + formatearFecha(req.fecha_autorizacion) + '</p>';
            html += '    </div>';
            html += '  </div>';
        }

        // Información de producción (si aplica)
        if (req.maquina || req.operador_cnc || req.inicio_maquinado || req.fecha_entrega_barras) {
            html += '  <div class="row mb-3 border-top pt-3">';
            html += '    <div class="col-md-6">';
            html += '      <p><strong>Máquina:</strong> ' + (req.maquina || 'Sin asignar') + '</p>';
            html += '      <p><strong>Operador CNC:</strong> ' + (req.operador_cnc || 'Sin asignar') + '</p>';
            html += '    </div>';
            html += '    <div class="col-md-6">';
            html += '      <p><strong>Inicio Maquinado:</strong> ' + formatearFecha(req.inicio_maquinado) + '</p>';
            html += '      <p><strong>Fecha Entrega Barras:</strong> ' + formatearFecha(req.fecha_entrega_barras) + '</p>';
            html += '    </div>';
            html += '  </div>';
        }

        // Mensaje de acción según estatus
        html += '  <div class="alert alert-warning mt-3">';
        html += obtenerMensajeAccion(req.estatus, req.autorizo);
        html += ' En todo caso puede esperar a que la barra termine su ciclo natural del proceso de maquinado y retorno de billets para que vuelva a estar disponible automáticamente.'
        html += '  </div>';

        html += '  </div>';
        html += '</div>';

        return html;
    }
    // Función para obtener el color del badge según estatus
    function obtenerColorEstatus(estatus) {
        var colores = {
            'Autorizada': 'info',
            'Producción': 'info',
            'En producción': 'info',
            'Finalizada': 'info',
        };
        return colores[estatus] || 'secondary';
    }
    // Función para obtener el mensaje de acción según estatus
    function obtenerMensajeAccion(estatus, autorizo) {
        if (estatus === 'Autorizada') {
            return '<strong>Acción requerida:</strong> Este folio se encuentra en estatus <strong>Autorizada</strong>. Para liberar la barra, pida a <strong>' + 
                (autorizo || 'quien autorizó') + '</strong> que cancele el folio o si se trata de un reemplazo o barra extra, es posible liberarla rechazando la solicitud.';
        } else if (estatus === 'Producción' || estatus === 'En producción') {
            return '<strong>Acción requerida:</strong> Este folio se encuentra en estatus <strong>' + estatus + '</strong>. ' +
                'Para liberar la barra, pida a <strong>Gerencia de CNC</strong> que detenga la producción, posteriormente inventarios debe actualizar el nuevo stock de la barra para liberarla.';
        }else if (estatus === 'Finalizada') {
            return '<strong>Acción requerida:</strong> Este folio se encuentra en estatus <strong>' + estatus + '</strong>. ' +
                'Para que la barra esté disponible, <strong>Inventarios</strong> debe retornar las barras de la requisicion con el nuevo stock.';
        }else {
            return '<strong>Información:</strong> Estatus desconocido.';
        }
    }
    // Función para formatear fecha
    function formatearFecha(fecha) {
        if (!fecha || fecha === '0000-00-00' || fecha === '0000-00-00 00:00:00') {
            return 'No disponible';
        }
        try {
            var date = new Date(fecha);
            return date.toLocaleDateString('es-MX', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return 'No disponible';
        }
    }
    // Función para liberar la barra
    function liberarBarra(lotePedimento) {
        $.ajax({
            url: "../ajax/ajax_notificacion.php",
            type: "POST",
            data: { mensaje: "Se ha liberado una barra manualmente" },
            success: function(response) {
                console.log("Notificacion enviada: ", response);
            },
            error: function(error) {
                console.error("Error al enviar la notificacion: ", error);
            }
        });
        $.ajax({
            url: '../ajax/liberar_barra_inventario.php',
            type: 'POST',
            data: {
                lote_pedimento: lotePedimento
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    sweetAlertResponse('success', 'Éxito', 'La barra ha sido liberada correctamente.', 'self');
                    bootstrap.Modal.getInstance(document.getElementById('modalLocalizarBarra')).hide();
                    // Recargar tabla si existe
                    if (typeof $('#inventarioTable').DataTable !== 'undefined') {
                        $('#inventarioTable').DataTable().ajax.reload();
                    }
                    
                } else {
                    sweetAlertResponse('error', 'Error', response.mensaje || 'Error al liberar la barra', 'none');
                }
            },
            error: function(xhr, status, error) {
                sweetAlertResponse('error', 'Error', 'Error al procesar la solicitud: ' + error, 'none');
            }
        });
    }  


    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function(){
        // // Handle btn-localizacion click
        $(document).on('click', '.btn-localizacion', function() {
            var lotePedimento = $(this).data('lote');
            
            if (!lotePedimento) {
                sweetAlertResponse('error', 'Error', 'Lote/Pedimento no disponible', 'none');
                return;
            }

            // Abrir modal
            var modal = new bootstrap.Modal(document.getElementById('modalLocalizarBarra'));
            modal.show();

            // Mostrar loader
            $('#localizarBarraLoader').removeClass('d-none');
            $('#localizarBarraResultado').addClass('d-none');
            $('#localizarBarraError').addClass('d-none');
            $('#botonesBarraNoEncontrada').addClass('d-none');
            $('#botonesBarraEncontrada').addClass('d-none');

            // Hacer llamada AJAX
            $.ajax({
                url: '../ajax/localizar_barra.php',
                type: 'POST',
                data: {
                    lote_pedimento: lotePedimento
                },
                dataType: 'json',
                success: function(response) {
                    $('#localizarBarraLoader').addClass('d-none');
                    $('#localizarBarraResultado').removeClass('d-none');

                    if (response.error) {
                        $('#localizarBarraError').html(response.mensaje).removeClass('d-none');
                        return;
                    }

                    // Si no se encontró la barra
                    if (!response.encontrada) {
                        $('#barraNoEncontrada').removeClass('d-none');
                        $('#barraEncontrada').addClass('d-none');
                        $('#botonesBarraNoEncontrada').removeClass('d-none');
                        $('#botonesBarraEncontrada').addClass('d-none');

                        // Guardar datos para la acción de liberar
                        $('#btnLiberarBarraNoEncontrada').data('lote', lotePedimento);
                    } else {
                        // Si se encontró la barra
                        $('#barraNoEncontrada').addClass('d-none');
                        $('#barraEncontrada').removeClass('d-none');
                        $('#botonesBarraNoEncontrada').addClass('d-none');
                        $('#botonesBarraEncontrada').removeClass('d-none');

                        // Construir HTML con las requisiciones
                        var htmlRequisiciones = '';
                        response.requisiciones.forEach(function(req, index) {
                            htmlRequisiciones += construirCardRequisicion(req, lotePedimento, index);
                        });
                        $('#listaRequisiciones').html(htmlRequisiciones);
                    }
                },
                error: function(xhr, status, error) {
                    $('#localizarBarraLoader').addClass('d-none');
                    $('#localizarBarraResultado').removeClass('d-none');
                    $('#localizarBarraError').html('Error al buscar la barra: ' + error).removeClass('d-none');
                }
            });
        });
        // Handle btn-liberar-barra-no-encontrada
        $(document).on('click', '#btnLiberarBarraNoEncontrada', function() {
            var lotePedimento = $(this).data('lote');

            Swal.fire({
                title: 'Confirmar liberación',
                text: '¿Desea liberar esta barra marcándola como "Disponible para cotizar"? Se da por hecho que ya consultó existencia física',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, liberar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    liberarBarra(lotePedimento);
                }
            });
        });
    });

</script>