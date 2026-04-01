<div class="modal fade" id="modalVerComponente" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Componente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="infoComponente" class="">
                    <h4 id="verPerfilNombre" class="text-success fw-bold m-0"></h4>
                    <p id="verFamiliaNombre" class="text-muted mb-1"></p>
                </div>
                <div id="containerImagenComponente" style="min-height: 250px;" class="d-flex align-items-center justify-content-center border rounded bg-light p-3">
                    <img id="imgComponente" src="" alt="Componente" class="img-fluid rounded shadow-sm" style="max-height: 500px; display:none;">
                    <div id="spinnerCargandoImagen" class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
                <div id="errorImagenComponente" class="alert alert-warning d-none mt-3">
                    <i class="bi bi-exclamation-triangle-fill"></i> No se encontró una imagen para este componente.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    // ============================================================
    //          ******** EVENTOS DEL DOM ********
    // ============================================================ 
    $(document).ready(function () {
        // Evento para ver la imagen del componente en un modal
        $(document).on('click', '.btn-ver-componente', function() {
            const idCotizacion = $(this).data('id-cotizacion');
            const componente = $(this).data('componente');
            const perfilNombre = $(this).data('perfil') || '';
            
            
            // Si no hay id_cotizacion, no podemos buscar en cotizacion_materiales
            if (!idCotizacion || idCotizacion === "") {
                Swal.fire({
                    title: "Sin información",
                    text: "No se pudo visualizar el componente o es de una barra extra.",
                    icon: "info",
                    confirmButtonText: "Entendido"
                });
                return;
            }

            // Inicializar y mostrar modal
            const modalVerComponenteEl = document.getElementById('modalVerComponente');
            if (!modalVerComponenteEl) return;
            
            const modalVerComponente = new bootstrap.Modal(modalVerComponenteEl);
            modalVerComponente.show();

            // Resetear UI del modal
            $('#imgComponente').hide().attr('src', '');
            $('#spinnerCargandoImagen').show();
            $('#errorImagenComponente').addClass('d-none');
            $('#verPerfilNombre').text(perfilNombre || 'Cargando...');
            $('#verFamiliaNombre').text('');

            // Petición AJAX
            $.ajax({
                url: '../ajax/obtener_imagen_componente.php',
                type: 'GET',
                data: { 
                    id_cotizacion: idCotizacion, 
                    componente: componente 
                },
                dataType: 'json',
                success: function(data) {
                    $('#spinnerCargandoImagen').hide();
                    if (data.success) {
                        if (data.img && data.img !== "") {
                            // Pre-cargar imagen para suavidad
                            const img = new Image();
                            img.onload = function() {
                                $('#imgComponente').attr('src', data.img).fadeIn();
                            };
                            img.onerror = function() {
                                $('#spinnerCargandoImagen').hide();
                                $('#errorImagenComponente').removeClass('d-none').html('<i class="bi bi-exclamation-triangle-fill"></i> Error al cargar la imagen. Verifique la ruta.');
                            };
                            img.src = data.img;
                        } else {
                            $('#errorImagenComponente').removeClass('d-none').html('<i class="bi bi-info-circle"></i> El componente no tiene una imagen asociada en la base de datos.');
                        }
                        $('#verPerfilNombre').text(data.perfil || perfilNombre || 'Componente');
                        $('#verFamiliaNombre').text(data.familia || '');
                    } else {
                        $('#errorImagenComponente').removeClass('d-none').text(data.error || 'Error al obtener la imagen.');
                        $('#verPerfilNombre').text('Error');
                    }
                },
                error: function(xhr, status, error) {
                    $('#spinnerCargandoImagen').hide();
                    $('#errorImagenComponente').removeClass('d-none').text('Error de conexión al servidor: ' + error);
                    $('#verPerfilNombre').text('Error');
                }
            });
        });
    });
</script>
