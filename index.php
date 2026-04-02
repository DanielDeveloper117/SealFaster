<?php
// 1. Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 2. Forzar que el navegador no guarde el HTML en cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Fecha en el pasado

// 3. Cargar configuraciones y funciones
require_once(__DIR__ . '/config/rutes.php');
require_once(ROOT_PATH . '/includes/functions/control_cache.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="<?= controlCache('./assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('./assets/dependencies/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= controlCache('./assets/dependencies/jquery.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('./assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('./assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <script src="<?= controlCache('./assets/js/alerts_sweet_alert.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('./assets/css/styles-login.css'); ?>">
  
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
        
        <form id="formLogin" class="d-flex flex-column">
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

            <input type="hidden" name="phone_number" value="">
            
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
    
    // 4. NUEVO: Manejo del formulario con AJAX
    $("#formLogin").on("submit", function(e){
        e.preventDefault(); // Prevenir envío tradicional
        
        const usuario = $("#inputUsuario").val().trim();
        const password = $("#inputPass").val().trim();
        
        // Validación básica
        if(!usuario || !password) {
            sweetAlertResponse('warning', 'Advertencia', 'Por favor, complete todos los campos.');
            return;
        }
        
        // Deshabilitar botón para evitar múltiples envíos
        const btnIngresar = $("#btnIngresar");
        const originalText = btnIngresar.html();
        btnIngresar.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
        
        // Enviar datos por AJAX
        $.ajax({
            url: 'auth/login-script.php',
            type: 'POST',
            dataType: 'json',
            data: {
                usuario: usuario,
                password: password
            },
            success: function(response) {
                if(response.status === 'success') {
                    // Login exitoso - redirigir
                    //sweetAlertResponse(response.status, response.title, response.message, response.redirect);
                    window.location.href =  response.redirect;
                } else {
                    // Error en login
                    sweetAlertResponse(response.status, response.title, response.message);
                    btnIngresar.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la solicitud:", error);
                sweetAlertResponse('error', 'Error', 'Error de conexión con el servidor. Intente nuevamente');
                btnIngresar.prop('disabled', false).html(originalText);
            }
        });
    });
    var anchoVentanaInicial = window.innerWidth;
    var anchoPantallaInicial = screen.width;
    var zoomInicial = anchoVentanaInicial / anchoPantallaInicial * 100;

    function detectarZoom() {
        var anchoVentana = window.innerWidth;
        var anchoPantalla = screen.width;
        var zoom = anchoVentana / anchoPantalla * 100;

        if (zoom > 100) {
            $(".container").css("scale","0.6").css("margin-top", "-12%");
            console.log("123");
        } else {
            if(zoomInicial !== zoom){
                window.location.reload(true);  // Recargar desde el servidor (sin usar la caché)
            }else{
            }
        }
    }
    ////////////// EVENTO ZOOM/REDIMENSION DEL NAVEGADOR
    window.addEventListener('resize', detectarZoom);
    detectarZoom();
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