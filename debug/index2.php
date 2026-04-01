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
  
  <title>Login</title>
</head>

<body translate="no">
  <!-- Header con logo y slogan -->
  <nav class="navbar navbar-expand-lg navbar-dark login-navbar">
    <div class="container-fluid">
      <a class="navbar-brand logo-animate" href="#">
        <img src="assets/img/general/logo-copia.png" alt="Logo" class="img-fluid navbar-logo">
      </a>
      <div class="navbar-text slogan-animate ms-auto">
        <span class="slogan-text">Somos Creadores de Soluciones</span>
      </div>
    </div>
  </nav>

  <!-- Background Slider con overlay -->
  <div class="background-slider-container">
    <div class="slider-overlay"></div>
    <div class="slider-images">
      <!-- Imágenes se cargan dinámicamente -->
    </div>
    <!-- Progress Bar -->
    <div class="slider-progress-container">
      <div class="slider-progress-bar"></div>
    </div>
  </div>

  <!-- Contenido Principal -->
  <main class="main-login-container">
    <div class="login-wrapper">
      <!-- Formulario con animaciones -->
      <div class="form-card animate-form">
        <!-- Icono de Usuario Animado -->
        <div class="user-icon-wrapper">
          <div class="user-icon-circle pulse-animation">
            <i class="bi bi-person-fill user-icon"></i>
          </div>
          <h1 class="login-title slide-in-title">Login</h1>
        </div>

        <!-- Efecto Shimer -->
        <div class="shimer-overlay"></div>
        
        <form id="formLogin" class="login-form" action="auth/login-script.php" method="POST">
          <div class="form-group input-container">
            <label for="inputUsuario">Usuario</label>
            <input id="inputUsuario" type="text" name="usuario" required class="input-field">
          </div>
          
          <div class="form-group input-container input-pass">
            <label for="inputPass">Password</label>
            <input id="inputPass" type="password" name="password" required class="input-field">
            <button id="btnEye" type="button" class="btn-toggle-password">
              <i class="bi bi-eye" id="iconEye"></i>
            </button>
          </div>
          
          <div class="form-group mt-4">
            <button id="btnIngresar" type="submit" class="btn btn-login btn-block">Continuar</button>
          </div>
        </form>
        
        <p class="register-info">Para registrar un usuario, contactar el área de sistemas.</p>
      </div>
    </div>
  </main>

  <footer class="login-footer animate-footer">
    <p>&copy; Sellos y Retenes de San Luis S.A. de C.V. Todos los derechos reservados.</p>
  </footer>

<script>
// Configuración del slider
const backgroundImages = [
  'assets/img/general/background_login.jpg',
  'assets/img/general/maquinado.jpg',
  'assets/img/general/sellos.png',
  'assets/img/general/midiendo.jpg'
];

let currentImageIndex = 0;
let slideInterval;
let parallaxInterval;

$(document).ready(function() {
  // Inicializar slider de fondo
  initBackgroundSlider();
  
  // Iniciar animaciones de entrada
  startEntranceAnimations();
  
  // Configurar eventos del formulario
  setupFormEvents();
  
  // Iniciar efecto parallax
  startParallaxEffect();
});

function initBackgroundSlider() {
  const sliderContainer = $('.slider-images');
  
  // Crear slides
  backgroundImages.forEach((image, index) => {
    const slide = $(`<div class="slide ${index === 0 ? 'active' : ''}"></div>`)
      .css('background-image', `url('${image}')`);
    sliderContainer.append(slide);
  });
  
  const slides = $('.slide');
  const progressBar = $('.slider-progress-bar');
  
  // Función para cambiar de slide
  function changeSlide() {
    const currentSlide = slides.eq(currentImageIndex);
    const nextIndex = (currentImageIndex + 1) % slides.length;
    const nextSlide = slides.eq(nextIndex);
    
    // Mostrar siguiente slide
    nextSlide.addClass('next');
    
    // Efecto fade out/in
    setTimeout(() => {
      currentSlide.removeClass('active');
      nextSlide.removeClass('next').addClass('active');
      
      // Resetear progress bar
      progressBar.css('transition', 'none');
      progressBar.css('width', '0%');
      
      setTimeout(() => {
        progressBar.css('transition', 'width 3.5s linear');
        progressBar.css('width', '100%');
      }, 10);
      
      currentImageIndex = nextIndex;
    }, 500); // Duración del fade
  }
  
  // Iniciar progress bar
  progressBar.css('width', '0%');
  setTimeout(() => {
    progressBar.css('width', '100%');
  }, 10);
  
  // Iniciar intervalo de cambio
  slideInterval = setInterval(changeSlide, 3500);
}

function startParallaxEffect() {
  parallaxInterval = setInterval(() => {
    const activeSlide = $('.slide.active');
    if (activeSlide.length) {
      // Generar movimiento aleatorio suave
      const moveX = Math.sin(Date.now() / 2000) * 1.5; // Movimiento horizontal suave
      const moveY = Math.cos(Date.now() / 3000) * 1.5; // Movimiento vertical suave
      const scale = 1.02 + Math.sin(Date.now() / 4000) * 0.02; // Zoom sutil
      
      activeSlide.css({
        'transform': `translate(${moveX}%, ${moveY}%) scale(${scale})`,
        'transition': 'transform 3.5s ease-out'
      });
    }
  }, 100);
}

function startEntranceAnimations() {
  // Animación de entrada escalonada
  setTimeout(() => {
    $('.logo-animate').addClass('animate-in');
  }, 300);
  
  setTimeout(() => {
    $('.slogan-animate').addClass('animate-in');
  }, 600);
  
  setTimeout(() => {
    $('.animate-form').addClass('visible');
  }, 900);
  
  setTimeout(() => {
    $('.user-icon-circle').addClass('animate-in');
  }, 1200);
  
  setTimeout(() => {
    $('.login-title').addClass('visible');
  }, 1500);
  
  setTimeout(() => {
    $('.animate-footer').addClass('visible');
  }, 1800);
}

function setupFormEvents() {
  // Evento para usuario CNC
  $("#inputUsuario").on("change input", function(){
    let usuario = $(this).val();
    if(usuario == "usercnc"){
      usuario = "usercnc@sellosyretenes.com";
      $(this).val(usuario);
    }
  });
  
  // Toggle password visibility
  $("#btnEye").on("click", function(){
    const input = $("#inputPass");
    const icon = $("#iconEye");
    
    if (input.attr('type') === 'password') {
      input.attr('type', 'text');
      icon.removeClass('bi-eye').addClass('bi-eye-slash');
    } else {
      input.attr('type', 'password');
      icon.removeClass('bi-eye-slash').addClass('bi-eye');
    }
  });
  
  // Efecto hover en formulario
  $(".form-card").hover(
    function() {
      $(this).addClass('card-hovered');
      $('.shimer-overlay').addClass('active');
    },
    function() {
      $(this).removeClass('card-hovered');
      $('.shimer-overlay').removeClass('active');
    }
  );
  
  // Prevenir submit si hay mantenimiento (descomentar si es necesario)
  /*
  $("#formLogin").on("submit", function(e) {
    e.preventDefault();
    alert("Servidor en mantenimiento, espere unos minutos.");
  });
  */
}
</script>

</body>
</html>