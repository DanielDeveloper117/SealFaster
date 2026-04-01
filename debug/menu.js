document.addEventListener('DOMContentLoaded', () => {
  const burgerBtn = document.getElementById('burgerBtn');
  const navbar = document.getElementById('navbar');
  const userIcon = document.getElementById('userIcon');
  const userDropdown = document.getElementById('userDropdown');

  const enlace = document.getElementById('enlaceCotizaciones');

  const guia = document.getElementById('imgGuia');

  if (enlace) {
    const url = new URL(enlace.href, window.location.origin);
    // asegurar que cot siempre esté presente
    if (!url.searchParams.has('cot')) {
      url.searchParams.set('cot', 'u');
    }
    // Obtener default desde localStorage
    const savedDefault = localStorage.getItem('filtroDefault') || '0';

    // Crear URL actualizada
    url.searchParams.set('default', savedDefault);

    // Asignar el nuevo href al enlace
    enlace.href = url.toString();
  }

  burgerBtn.addEventListener('click', () => {
    navbar.classList.toggle('menu-active');
    burgerBtn.classList.toggle('bi-list');
    burgerBtn.classList.toggle('bi-x-lg');
  });

  userIcon.addEventListener('click', (e) => {
    e.stopPropagation();
    userDropdown.style.display = userDropdown.style.display === 'flex' ? 'none' : 'flex';
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.user-menu')) {
      userDropdown.style.display = 'none';
    }
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

  // $("#navbar a").on("click", function(e){
  //   e.preventDefault();
  //   window.location = "../auth/cerrar_sesion.php";
  // });
  if(guia){
    guia.addEventListener('click', () => {
        $.ajax({
            url: "../ajax/ajax_notificacion.php",
            type: "POST",
            data: { mensaje: "Click a guia" },
            success: function(response) {
                console.log("Notificacion enviada: ", response);
            },
            error: function(error) {
                console.error("Error al enviar la notificacion: ", error);
            }
        });
    });
  }
});
