document.addEventListener('DOMContentLoaded', () => {

    // ============================================================
    // REFERENCIAS
    // ============================================================
    const burgerBtn     = document.getElementById('burgerBtn');
    const navbar        = document.getElementById('navbar');
    const userMenuEl    = document.querySelector('.user-menu');
    const userTrigger   = document.getElementById('userIcon');
    const userDropdown  = document.getElementById('userDropdown');
    const enlace        = document.getElementById('enlaceCotizaciones');
    const guia          = document.getElementById('imgGuia');

    // ============================================================
    // ENLACE DE COTIZACIONES — lógica original preservada
    // ============================================================
    if (enlace) {
        const url = new URL(enlace.href, window.location.origin);
        if (!url.searchParams.has('cot')) {
            url.searchParams.set('cot', 'u');
        }
        let savedDefault = localStorage.getItem('filtroDefault') || '1';
        if (savedDefault === '0') savedDefault = '1';
        url.searchParams.set('default', savedDefault);
        enlace.href = url.toString();
    }

    // ============================================================
    // BURGER BUTTON — abrir/cerrar panel mobile
    // ============================================================
    burgerBtn.addEventListener('click', () => {
        const isActive = navbar.classList.toggle('menu-active');
        burgerBtn.classList.toggle('bi-list', !isActive);
        burgerBtn.classList.toggle('bi-x-lg', isActive);
        burgerBtn.setAttribute('aria-expanded', isActive);
    });

    // ============================================================
    // DROPDOWNS DE GRUPOS (nav-group)
    // ============================================================
    const navGroups = document.querySelectorAll('.nav-group');

    navGroups.forEach(group => {
        const trigger = group.querySelector('.nav-group-trigger');
        if (!trigger) return;

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = group.classList.contains('open');

            // Cerrar todos los otros grupos
            navGroups.forEach(g => {
                if (g !== group) {
                    g.classList.remove('open');
                    const t = g.querySelector('.nav-group-trigger');
                    if (t) t.setAttribute('aria-expanded', 'false');
                }
            });

            // Toggle del grupo actual
            group.classList.toggle('open', !isOpen);
            trigger.setAttribute('aria-expanded', !isOpen);
        });
    });

    // ============================================================
    // MENÚ DE USUARIO
    // ============================================================
    userTrigger.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = userDropdown.classList.toggle('open');
        userMenuEl.classList.toggle('open', isOpen);
        userTrigger.setAttribute('aria-expanded', isOpen);
    });

    // ============================================================
    // CIERRE AL CLICK EXTERNO
    // ============================================================
    document.addEventListener('click', (e) => {
        // Cerrar dropdowns de grupos
        if (!e.target.closest('.nav-group')) {
            navGroups.forEach(g => {
                g.classList.remove('open');
                const t = g.querySelector('.nav-group-trigger');
                if (t) t.setAttribute('aria-expanded', 'false');
            });
        }

        // Cerrar menú de usuario
        if (!e.target.closest('.user-menu')) {
            userDropdown.classList.remove('open');
            userMenuEl.classList.remove('open');
            userTrigger.setAttribute('aria-expanded', 'false');
        }
    });

    // ============================================================
    // CERRAR NAVBAR MOBILE AL HACER CLICK EN UN ENLACE SIMPLE
    // ============================================================
    document.querySelectorAll('.nav-item-simple').forEach(link => {
        link.addEventListener('click', () => {
            if (navbar.classList.contains('menu-active')) {
                navbar.classList.remove('menu-active');
                burgerBtn.classList.add('bi-list');
                burgerBtn.classList.remove('bi-x-lg');
                burgerBtn.setAttribute('aria-expanded', 'false');
            }
        });
    });

    // ============================================================
    // RESALTAR ENLACE ACTIVO SEGÚN URL ACTUAL
    // ============================================================
    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-item-simple').forEach(link => {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').replace('../modules/', ''))) {
            link.classList.add('active-option');
        }
    });

    // ============================================================
    // ANIMACIONES DEL MAIN — lógica original preservada
    // ============================================================
    $('.main-animated').fadeIn(1000, function () {
        $('.sello-img').addClass('slide-in');

        $('.sello-img').on('transitionend', function () {
            $('.img-speach').addClass('fade-in');

            $('.img-speach').on('transitionend', function () {
                typeWriter($('.speech-bubble'), 10);
            });
            $('.img-speach').on('transitionend', function () {
                typeWriter($('.speech-bubble2'), 10);
            });
        });
    });

    function typeWriter(element, speed) {
        let text = element.text();
        element.text('');
        element.css('opacity', 1);
        let i = 0;

        function typeChar() {
            if (i < text.length) {
                element.append(text.charAt(i));
                i++;
                setTimeout(typeChar, speed);
            } else {
                element.addClass('typewriter');
            }
        }
        typeChar();
    }

    // ============================================================
    // GUÍA DE USUARIO — lógica original preservada
    // ============================================================
    if (guia) {
        guia.addEventListener('click', () => {
            $.ajax({
                url: '../ajax/ajax_notificacion.php',
                type: 'POST',
                data: { mensaje: 'Click a guia' },
                success: function(response) {
                    console.log('Notificacion enviada: ', response);
                },
                error: function(error) {
                    console.error('Error al enviar la notificacion: ', error);
                }
            });
        });
    }

});