/**
 * Módulo de Operaciones de Inventario
 * Gestiona la selección múltiple de barras y operaciones en masa en el inventario CNC
 */

$(document).ready(function() {
    // ============================================================
    // VARIABLES GLOBALES
    // ============================================================
    const urlInicial = new URL(window.location.href);
    const params = new URLSearchParams(window.location.search);
    const operActual = urlInicial.searchParams.get('oper') || '0';
    
    let selectedLotes = []; // Array para almacenar los lotes pedimento seleccionados
    if (params.get('oper') === '1') {
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
    // ============================================================
    // FUNCIONES AUXILIARES
    // ============================================================

    /**
     * Crea una URL con el parámetro 'oper' modificado
     * @param {string} valorOper - El valor del parámetro oper
     * @returns {string} URL modificada
     */
    function crearHrefConOper(valorOper) {
        const nuevaUrl = new URL(urlInicial);
        nuevaUrl.searchParams.set('oper', valorOper);
        return nuevaUrl.toString();
    }

    /**
     * Actualiza la visibilidad de los checkboxes según el modo de operación
     */
    function mostrarCheckboxesPorOper(oper) {
        const checkboxes = document.querySelectorAll('.btn-check-cute');

        if (oper === '1') {
            checkboxes.forEach(checkbox => {
                checkbox.classList.remove('d-none');
            });
        } else {
            checkboxes.forEach(checkbox => {
                checkbox.classList.add('d-none');
            });
        }
    }

    /**
     * Recopila los lotes pedimento de las barras seleccionadas
     * @returns {Array} Array de lotes pedimento seleccionados
     */
    function obtenerLotesSeleccionados() {
        const lotes = [];
        document.querySelectorAll('.btn-check-cute:checked').forEach(checkbox => {
            const lp = checkbox.getAttribute('data-lp');
            if (lp) {
                lotes.push(lp);
            }
        });
        return lotes;
    }

    /**
     * Recopila los IDs de las barras seleccionadas
     * @returns {Array} Array de IDs seleccionados
     */
    function obtenerIdsSeleccionados() {
        const ids = [];
        document.querySelectorAll('.btn-check-cute:checked').forEach(checkbox => {
            const id = checkbox.getAttribute('val');
            if (id) {
                ids.push(id);
            }
        });
        return ids;
    }

    /**
     * Obtiene el almacen_id de la primera barra seleccionada
     * @returns {string|null} ID del almacén o null si no hay barras seleccionadas
     */
    function obtenerAlmacenIdSeleccionado() {
        const checkbox = document.querySelector('.btn-check-cute:checked');
        if (checkbox) {
            // El almacen_id debe estar en el atributo data-almacen-id
            return checkbox.getAttribute('data-almacen-id');
        }
        return null;
    }

    /**
     * Actualiza la visibilidad de la barra de operación
     */
    function actualizarBarraOperacion() {
        const checkboxesChecked = document.querySelectorAll('.btn-check-cute:checked').length;
        const agrupacionBar = document.getElementById('agrupacionBar');

        if (checkboxesChecked > 0) {
            agrupacionBar.classList.remove('d-none');
        } else {
            //agrupacionBar.classList.add('d-none');
        }
    }

    /**
     * Cancela la operación en masa
     */
    function cancelarOperacion() {
        // Obtener todos los parámetros GET de la URL
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.delete('oper');
        // Reconstruir la URL sin 'oper'
        const newUrl = window.location.pathname + '?' + urlParams.toString();
        window.location.href = newUrl;
    }

    // ============================================================
    // INICIALIZACIÓN
    // ============================================================

    // Inicializar la vista según el parámetro 'oper'
    if (!urlInicial.searchParams.has('oper')) {
        urlInicial.searchParams.set('oper', '0');
        history.replaceState({}, '', urlInicial.toString());
    }

    // Mostrar/ocultar checkboxes según el estado actual
    mostrarCheckboxesPorOper(operActual);

    // Si estamos en modo de operación, mostrar la barra si hay selecciones
    if (operActual === '1') {
        // Esperar a que la tabla esté lista antes de actualizar
        setTimeout(() => {
            //actualizarBarraOperacion();
        }, 500);
    }

    // ============================================================
    // EVENTOS DEL DOM
    // ============================================================

    /**
     * Evento: Click en btnInitOperacion para iniciar el modo de operación
     */
    $('#btnInitOperacion').on('click', function(e) {
        e.preventDefault();
        let url = new URL(window.location.href);
        url.searchParams.set('oper', '1');
        window.location.href = url.toString();
    });

    /**
     * Evento: Cambio en los checkboxes para seleccionar/deseleccionar barras
     */
    $(document).on('change', '.btn-check-cute', function() {
        // Agregar animación "pop"
        $(this).addClass('pop');
        setTimeout(() => {
            $(this).removeClass('pop');
        }, 220);

        // Actualizar la barra de operación
        actualizarBarraOperacion();
    });

    /**
     * Evento: Click en btnContinuarOperacion para proceder con la operación
     */
    $('#btnContinuarOperacion').on('click', function(e) {
        e.preventDefault();

        // Obtener los datos seleccionados
        selectedLotes = obtenerLotesSeleccionados();
        const selectedIds = obtenerIdsSeleccionados();
        const almacenId = obtenerAlmacenIdSeleccionado();

        if (selectedIds.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin selección',
                text: 'Por favor, seleccione al menos una barra',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        if (!almacenId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo determinar el almacén de origen',
                confirmButtonText: 'Entendido'
            });
            return;
        }

        // Guardar los datos seleccionados en sessionStorage para usar en el modal
        sessionStorage.setItem('inventarioOperacionIds', JSON.stringify(selectedIds));
        sessionStorage.setItem('inventarioOperacionLotes', JSON.stringify(selectedLotes));
        sessionStorage.setItem('inventarioOperacionAlmacenId', almacenId);

        // Establecer el almacen_id en el formulario
        $('#inputOrigenId').val(almacenId);

        // Abrir el modal
        const modalOperacionInventario = new bootstrap.Modal(document.getElementById('modalOperacionInventario'), {
            backdrop: 'static',
            keyboard: false
        });
        modalOperacionInventario.show();
    });

    /**
     * Evento: Click en btnCancelOperacion para cancelar la operación
     */
    $('#btnCancelOperacion').on('click', function(e) {
        e.preventDefault();
        cancelarOperacion();
    });

    /**
     * Evento: Cuando se cierra el modal, limpiar la selección
     */
    $('#modaOperacionlInventario').on('hidden.bs.modal', function() {
        // Si no se confirmó la operación, regresa al estado normal
        if (operActual === '1') {
            // El usuario vuelve a la lista de selección
        }
    });

    /**
     * Evento: Al cargar la página con parámetro ?oper=1, activar los checkboxes
     */
    if (operActual === '1') {
        document.querySelectorAll('.btn-check-cute').forEach(el => {
            el.style.display = 'inline-block';
        });
    }

});
