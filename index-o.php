<?php
require_once(__DIR__ . '/config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= controlCache('assets/css/styles-login.css'); ?>">
  
  <title>Login | Sellos y Retenes</title>
</head>

<body translate="no">

  <div class="background-slider">
    <div class="slider-progress">
      <div class="progress-bar"></div>
    </div>
    
    <div class="slide-overlay"></div>
    
    <div class="slides-container">
      </div>
  </div>

  <header class="login-header d-none d-md-block">
    <div class="container-fluid">
      <div class="d-flex justify-content-between align-items-center">
        <div class="logo-container">
          <img src="assets/img/general/Logopng-01.png" alt="Logo" class="img-fluid" style="max-height: 70px;">
        </div>
        
        <div class="slogan-container">
          <div class="text-slogan">Somos Creadores de Soluciones</div>
        </div>
      </div>
    </div>
  </header>

  <div class="main-content">
    <div class="container d-flex justify-content-center">
      
      <div class="form-container  col-11 col-sm-8 col-md-6 col-lg-4 col-xl-4 col-xxl-3">
        
        <div class="user-icon-container text-center">
            <div class="user-icon-circle">
                <i class="bi bi-person-circle"></i>
            </div>
            <h2 class="login-title">Login</h2>
        </div>
        
        <form id="formLogin" class="d-flex flex-column" action="auth/login-script.php" method="POST">
          <div class="input-container mt-3">
            <label>Usuario</label>
            <input id="inputUsuario" class="mb-2" type="text" name="usuario" required autocomplete="off">
          </div>
          
          <div class="input-container input-pass mt-2">
            <label>Password</label>
            <input id="inputPass" class="mb-2" type="password" name="password" required>
            <button id="btnEye" type="button" class="btn-toggle-password" tabindex="-1">
              <i class="bi bi-eye" id="iconEye"></i>
            </button>
          </div>
          
          <div class="d-grid mt-3">
            <button id="btnIngresar" type="submit" class="btn btn-login py-2 rounded-pill shadow-sm">Continuar</button>
          </div>
        </form>

        <p class="mb-0 mt-2 text-center" style="font-size: 0.75rem;">
            Para registrar un usuario, contactar el área de sistemas.
        </p>
      </div>
    </div>
  </div>
  
  <footer>
    <p class="p">&copy; Sellos y Retenes de San Luis S.A. de C.V. Todos los derechos reservados.</p>
  </footer>

<script>
// Array de imágenes (Configuración)
const backgroundImages = [
  'assets/img/general/background_login.jpg', // Imagen Default
  'assets/img/general/sellos.png',
  'assets/img/general/midiendo.jpg',
  'assets/img/general/maquinado2.jpg',
  'assets/img/general/sello12.jpg',
  'assets/img/general/maquinado.jpg',
  'assets/img/general/sello14.jpg',
  'assets/img/general/sello13.jpg',
];

$(document).ready(function(){
    // 1. Inicializar Slider
    initBackgroundSlider();
    
    // 2. Lógica del Input Usuario (Tu lógica original preservada)
    $("#inputUsuario").on("change input", function(){
        let usuario = $(this).val();
        if(usuario == "usercnc"){
            usuario = "usercnc@sellosyretenes.com";
        }
        $(this).val(usuario);
    });
  
    // 3. Toggle Password
    $("#btnEye").on("click", function(){
        togglePassword();
    });
});

function togglePassword() {
    const input = document.getElementById("inputPass");
    const icon = document.getElementById("iconEye");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    }
}

// --- Lógica del Slider Espectacular ---
function initBackgroundSlider() {
    const container = $('.slides-container');
    const progressBar = $('.progress-bar');
    const duration = 3500; // 3.5 segundos por slide
    let currentIndex = 0;

    // Crear elementos DOM para las slides
    backgroundImages.forEach((url, index) => {
        const slide = $('<div class="slide"></div>');
        slide.css('background-image', `url(${url})`);
        if(index === 0) slide.addClass('active');
        container.append(slide);
    });

    const slides = $('.slide');
    
    // Función para animar una slide (Efecto Ken Burns Aleatorio)
    function animateSlide(slideElement) {
        // Generar valores aleatorios para movimiento suave
        // Random values for smooth movement (Zoom 1.0 -> 1.1 + Pan)
        const scale = 1.1 + (Math.random() * 0.1); // Escala entre 1.1 y 1.2
        const xDir = (Math.random() > 0.5 ? 1 : -1) * (Math.random() * 5); // +/- 0% a 5%
        const yDir = (Math.random() > 0.5 ? 1 : -1) * (Math.random() * 5);
        
        // Aplicar transición CSS dinámica
        slideElement.css({
            'transition': `transform ${duration + 500}ms linear, opacity 500ms ease`,
            'transform': `scale(${scale}) translate(${xDir}%, ${yDir}%)`
        });
    }

    // Iniciar animación en la primera slide
    animateSlide(slides.eq(0));
    runProgressBar();

    // Intervalo de cambio
    setInterval(() => {
        const currentSlide = slides.eq(currentIndex);
        const nextIndex = (currentIndex + 1) % slides.length;
        const nextSlide = slides.eq(nextIndex);

        // 1. Resetear slide actual (Fade out)
        currentSlide.removeClass('active');
        // Reset transform después del fade para que esté lista la próxima vez
        setTimeout(() => {
            currentSlide.css({'transform': 'scale(1) translate(0,0)', 'transition': 'none'});
        }, 500);

        // 2. Preparar siguiente slide (Fade in + Start Move)
        // Forzamos un reflow para asegurar que el transform inicie desde 0
        nextSlide.css({'transform': 'scale(1) translate(0,0)', 'transition': 'none'});
        nextSlide[0].offsetHeight; // Trigger reflow

        nextSlide.addClass('active');
        animateSlide(nextSlide);

        // 3. Reiniciar Barra de Progreso
        runProgressBar();

        currentIndex = nextIndex;
    }, duration);

    function runProgressBar() {
        progressBar.css('transition', 'none');
        progressBar.css('width', '0%');
        
        // Pequeño timeout para permitir que el navegador registre el ancho 0%
        setTimeout(() => {
            progressBar.css({
                'transition': `width ${duration}ms linear`,
                'width': '100%'
            });
        }, 50);
    }
}
</script>
</body>
</html>