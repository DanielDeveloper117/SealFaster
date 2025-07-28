$(document).ready(function () {
    $('#btnBurguer').on('click', function () {
        const $header = $('header');

        // Alterna la clase para abrir/cerrar menú
        $header.toggleClass('menu-open');

        // Opcional: cambiar ícono a X cuando esté abierto
        if ($header.hasClass('menu-open')) {
            $(this).removeClass('bi-list').addClass('bi-x-lg');
        } else {
            $(this).removeClass('bi-x-lg').addClass('bi-list');
        }
    });

    $('#buttonUser').on('click', function (e) {
        e.stopPropagation();
        $('#dropdownUser').toggle();
    });

    // Cerrar el menú si se hace clic fuera
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#containerUser').length) {
            $('#dropdownUser').hide();
        }
    });
    // Animar el header al cargar la página
    $('header').addClass('slide-down');
    // Esperar que termine la animación del header antes de mover img-sealfaster
    $('header').on('transitionend', function () {
        // Activar la animación de la imagen de SealFaster
        $('.img-sealfaster-container').addClass('slide-in');

        // Añadir la animación de la luz reflejada al cargar la página
        $('.img-sealfaster-container').each(function () {
            var $container = $(this);

            // Añadir la clase para activar la animación de luz
            $container.find('.light-reflection').css('animation', 'light-move 0.1s linear infinite');
        });
    });
    // Aparece el contenedor principal
    $('.main-animated').fadeIn(1000, function () {
        // Deslizar sello-img
        $('.sello-img').addClass('slide-in');

        // Mostrar img-speach después de sello-img
        $('.sello-img').on('transitionend', function () {
            $('.img-speach').addClass('fade-in');

            // Mostrar el texto tipo máquina después de img-speach
            $('.img-speach').on('transitionend', function () {
                typeWriter($('.speech-bubble'), 10); // 50ms por caracter
            });
            $('.img-speach').on('transitionend', function () {
                typeWriter($('.speech-bubble2'), 10); // 50ms por caracter
            });
        });
    });

    // Función para el efecto de escritura
    function typeWriter(element, speed) {
        let text = element.text(); // Obtiene el texto
        element.text(''); // Borra el texto para empezar desde cero
        element.css('opacity', 1); // Asegura que el texto sea visible
        let i = 0;

        function typeChar() {
            if (i < text.length) {
                element.append(text.charAt(i)); // Añade un carácter al texto
                i++;
                setTimeout(typeChar, speed);
            } else {
                element.addClass('typewriter'); // Clase final para restaurar normalidad
            }
        }
        typeChar(); // Inicia la animación de escritura
    }
});