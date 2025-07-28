                // Crear estructura del modal
                const modalHtml = `
                    <div class="modal fade" id="devModal" tabindex="-1" aria-labelledby="devModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                        <div class="modal-body text-center">
                            <p class="fs-5">Este perfil está en desarrollo, disculpe las molestias.</p>
                            <button type="button" class="btn btn-primary mt-3" id="goBackBtn">Regresar</button>
                        </div>
                        </div>
                    </div>
                    </div>
                `;

                // Insertar el modal al final del body
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                // Esperar a que el DOM inserte el modal para luego inicializarlo
                const modalElement = document.getElementById('devModal');

                // Crear instancia de Bootstrap Modal
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: false
                });

                // Mostrar modal
                modal.show();

                // Función para regresar
                function goBack() {
                    history.back();
                }

                // Botón "Regresar"
                modalElement.querySelector('#goBackBtn').addEventListener('click', goBack);

                // Interceptar cualquier intento de cerrar el modal y forzar ir atrás
                modalElement.addEventListener('hide.bs.modal', function (e) {
                    e.preventDefault();
                    goBack();
                });