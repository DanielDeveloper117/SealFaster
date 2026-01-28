/**
 * Middleware Unificado de Detección de Cambios en Requisiciones
 * 
 * Funciona tanto para Ventas Nacionales (VN) como para CNC/Inventarios
 * por que:
 * 1. Lee la tabla por su ID (#productionTable) que DataTables mantiene intacto
 * 2. Extrae datos de atributos data-* en los elementos <tr>
 * 3. Envía IDs específicos al backend (sin lógica de roles)
 * 4. Backend compara estatus sin filtros por usuario
 * 
 * NOTA IMPORTANTE: DataTables modifica el HTML renderizando múltiples <table>
 * Por eso usamos el ID (#productionTable) en lugar de clases, ya que el ID
 * se mantiene en el elemento original y es único.
 * 
 * Requiere:
 * - HTML: <table id="productionTable"> con <tr data-id-requisicion="X" data-estatus="Y">
 * - Backend: ajax/detectar_cambios_requisiciones_unificado.php
 */

(function() {
    'use strict';

    // ========== VARIABLES GLOBALES ==========
    let estadoRequisicionesInicial = [];
    let middleware_activo = false;
    let middlewareIntervalId = null;

    // ========== FUNCIONES PRINCIPALES ==========

    /**
     * Capturar el estado actual de la tabla del DOM
     * Lee la tabla con clase "tabla-requisiciones" y extrae datos de los atributos data-*
     */
    function capturarEstadoInicial() {
        try {
            estadoRequisicionesInicial = [];
            
            // Buscar la tabla original por ID (DataTables mantiene el ID intacto)
            // Usamos #productionTable en lugar de .tabla-requisiciones porque
            // DataTables crea múltiples tablas y replica las clases, pero mantiene el ID
            const tabla = document.querySelector('#productionTable');
            if (!tabla) {
                console.warn('No se encontró tabla con id "productionTable"');
                return;
            }

            // Obtener todos los <tr> del tbody (DataTables puede mover el tbody)
            // Si DataTables movió el tbody a otra tabla, buscamos en toda la página
            let filas = tabla.querySelectorAll('tbody tr');
            
            // Si la tabla original no tiene tbody (DataTables lo movió), buscar en cualquier tbody
            if (filas.length === 0) {
                filas = document.querySelectorAll('tbody tr[data-id-requisicion]');
            }
            
            filas.forEach(fila => {
                const idRequisicion = fila.getAttribute('data-id-requisicion');
                const estatus = fila.getAttribute('data-estatus');
                
                if (idRequisicion && estatus) {
                    estadoRequisicionesInicial.push({
                        id_requisicion: idRequisicion,
                        estatus: estatus
                    });
                }
            });

            console.log('Estado inicial capturado. Total requisiciones:', estadoRequisicionesInicial.length);
        } catch (e) {
            console.warn('Error en capturarEstadoInicial:', e);
        }
    }

    /**
     * Iniciar el middleware de detección de cambios
     * - Espera 10 segundos a que DataTables cargue completamente
     * - Captura estado inicial
     * - Inicia polling cada 20 segundos
     */
    function iniciarMiddlewareDeteccionCambios() {
        if (middleware_activo) return; // Evitar múltiples instancias
        middleware_activo = true;

        // Esperar 10 segundos a que la tabla se cargue completamente
        setTimeout(function() {
            capturarEstadoInicial();
            
            // Iniciar verificación cada 20 segundos
            middlewareIntervalId = setInterval(function() {
                verificarCambiosRequisiciones();
            }, 20000); // 20 segundos

            console.log('Middleware de detección de cambios iniciado');
        }, 10000); // Esperar 10 segundos para que la tabla cargue
    }

    /**
     * Detener el middleware
     */
    function detenerMiddlewareDeteccionCambios() {
        if (middlewareIntervalId) {
            clearInterval(middlewareIntervalId);
            middlewareIntervalId = null;
            middleware_activo = false;
            console.log('Middleware de detección de cambios detenido');
        }
    }

    /**
     * Verificar cambios comparando estado actual con backend
     * Envía solo los IDs y estatuses del estado inicial al backend
     * Backend retorna cambios sin ninguna lógica de roles
     */
    function verificarCambiosRequisiciones() {
        try {
            if (estadoRequisicionesInicial.length === 0) {
                return; // No hay nada que verificar
            }

            // Enviar IDs y estatus actual al backend para comparación
            $.ajax({
                url: '../ajax/detectar_cambios_requisiciones_unificado.php',
                type: 'POST',
                data: {
                    'estadoActual': JSON.stringify(estadoRequisicionesInicial)
                },
                dataType: 'json',
                timeout: 10000,
                success: function(response) {
                    if (response.tipo_cambio) {
                        procesarCambiosDetectados(response);
                    }
                },
                error: function(xhr, status, error) {
                    console.warn('Error en middleware detectar cambios:', error);
                }
            });

        } catch (e) {
            console.warn('Error en verificarCambiosRequisiciones:', e);
        }
    }

    /**
     * Procesar cambios detectados
     * Actualiza el estado y muestra notificación sutil
     */
    function procesarCambiosDetectados(respuesta) {
        // Actualizar estado actual con respuesta del backend
        estadoRequisicionesInicial = respuesta.estado_actual;

        // Mostrar toast (ahora es un div simple, no interfiere con SweetAlert)
        mostrarToastCambios(respuesta.mensaje);
        
        // Mostrar notificación del navegador
        mostrarNotificacionNavegador(respuesta.mensaje);
    }

    /**
     * Mostrar notificación del navegador (fuera del área visible)
     * @param {string} mensaje - Mensaje a mostrar
     */
    function mostrarNotificacionNavegador(mensaje) {
        // Notificación del navegador (si está permitida)
        // NOTA: Las notificaciones del navegador dependen de:
        // - Permisos del navegador (debe estar en granted)
        // - Permisos del SO (Windows/Mac/Linux) - puede ser que el SO las bloquee
        // - El navegador está enfocado o no (algunos navegadores no muestran si la pestaña está activa)
        if ('Notification' in window && Notification.permission === 'granted') {
            try {
                // Obtener la URL base correctamente (funciona desde cualquier ruta)
                const urlBase = window.location.origin;
                const iconUrl = urlBase + '/sealfaster/assets/img/general/Logopng-01.png';
                
                new Notification('Cambio de Estatus', {
                    body: mensaje,
                    icon: iconUrl,
                    badge: iconUrl,
                    tag: 'cambios-requisiciones',
                    requireInteraction: false
                });
                console.log('Notificación del navegador enviada');
            } catch (e) {
                console.warn('Error al mostrar notificación del navegador:', e);
            }
        }
    }

    /**
     * Crear el contenedor de notificaciones si no existe
     */
    function inicializarContenedorNotificaciones() {
        if (!document.getElementById('middleware-toast-container')) {
            const container = document.createElement('div');
            container.id = 'middleware-toast-container';
            container.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 9999;
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }
    }

    /**
     * Mostrar notificación tipo toast (NO usa SweetAlert, es un div simple)
     * No interfiere con otros modales de SweetAlert
     */
    function mostrarToastCambios(mensaje) {
        inicializarContenedorNotificaciones();
        
        const container = document.getElementById('middleware-toast-container');
        const toast = document.createElement('div');
        const toastId = 'toast-' + Date.now();
        
        toast.id = toastId;
        toast.style.cssText = `
            background: #3085d6;
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            margin-top: 10px;
            animation: slideIn 0.3s ease-out;
            pointer-events: auto;
            cursor: pointer;
            font-size: 14px;
            max-width: 300px;
            word-wrap: break-word;
        `;
        
        // Crear contenido del toast
        const contenido = document.createElement('div');
        contenido.innerHTML = `
            <div style="font-weight: 600; margin-bottom: 8px;">Cambio de Estatus</div>
            <div style="font-size: 13px; margin-bottom: 10px;">${mensaje}</div>
            <button id="${toastId}-btn" style="
                background: white;
                color: #3085d6;
                border: none;
                padding: 6px 12px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
                font-size: 12px;
                transition: opacity 0.2s;
            ">Actualizar</button>
        `;
        
        toast.appendChild(contenido);
        container.appendChild(toast);
        
        // Añadir estilos de animación al documento si no existen
        if (!document.getElementById('middleware-toast-styles')) {
            const style = document.createElement('style');
            style.id = 'middleware-toast-styles';
            style.textContent = `
                @keyframes slideIn {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                @keyframes slideOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Evento de clic en el botón "Actualizar"
        document.getElementById(toastId + '-btn').addEventListener('click', function() {
            toast.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => toast.remove(), 300);
            location.reload();
        });
        
        // Auto-cerrar después de 8 segundos
        setTimeout(function() {
            if (document.getElementById(toastId)) {
                toast.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => {
                    if (document.getElementById(toastId)) {
                        toast.remove();
                    }
                }, 300);
            }
        }, 15000);
    }

    // ========== EXPOSER EN WINDOW ==========
    // Para poder llamar desde afuera si es necesario
    window.middlewareDeteccionCambios = {
        iniciar: iniciarMiddlewareDeteccionCambios,
        detener: detenerMiddlewareDeteccionCambios,
        capturarEstado: capturarEstadoInicial,
        verificar: verificarCambiosRequisiciones
    };

    // ========== AUTO-INICIAR ==========
    // Iniciar automáticamente cuando el documento esté listo
    $(document).ready(function() {
        // Solicitar permiso para notificaciones
        if ('Notification' in window) {
            if (Notification.permission === 'default') {
                Notification.requestPermission().then(function(permission) {
                    if (permission === 'granted') {
                        console.log('Permisos de notificación concedidos');
                    }
                });
            } else if (Notification.permission === 'granted') {
                console.log('Notificaciones ya están permitidas');
            } else {
                console.log('Notificaciones bloqueadas por el usuario');
            }
        } else {
            console.warn('Notification API no disponible en este navegador');
        }

        // Iniciar middleware
        iniciarMiddlewareDeteccionCambios();
    });

})();
