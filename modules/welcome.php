<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/welcome.css'); ?>">
    <title>Inicio</title>

</head>
<body id="body1" style="padding-top: 0px !important;">

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>
<div></div>
<div id="containerMain">
    <div id="welcomeCard" class="d-flex justify-content-center">
      <div class="d-flex flex-column flex-md-row align-items-center">
        <div id="containerImg" class="col-md-4 text-center">
          <img src="../assets/img/general/sellocoti.png" class="img-fluid p-3 animate-img" alt="Bienvenida">
        </div>
        <div id="containerCardBody" class="">
          <div class="card-body">
            <?php include(ROOT_PATH . 'includes/info_user.php'); ?>
          </div>
        </div>
      </div>
    </div>
</div>

<?php include(ROOT_PATH . 'includes/footer.php'); ?>
    <script>
        $(document).ready(function() {
            // Activar animación de imagen después de 500ms
            setTimeout(() => {
                document.querySelector('.animate-img').classList.add('visible');
            }, 500);
            
            // Agregar efectos de parallax suaves
            let ticking = false;
            
            function updateParallax() {
                const scrolled = window.pageYOffset;
                const welcomeCard = document.getElementById('welcomeCard');
                
                if (welcomeCard) {
                    const speed = 0.5;
                    const yPos = -(scrolled * speed);
                    welcomeCard.style.transform = `translateY(${yPos}px)`;
                }
                
                ticking = false;
            }
            
            function requestTick() {
                if (!ticking) {
                    requestAnimationFrame(updateParallax);
                    ticking = true;
                }
            }
            
            window.addEventListener('scroll', requestTick);
            
            // Detectar cambios de tema y aplicar transiciones suaves
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            mediaQuery.addListener(handleThemeChange);
            
            function handleThemeChange(e) {
                document.body.style.transition = 'background 0.5s ease';
                
                // Reactivar animaciones con el nuevo tema
                setTimeout(() => {
                    const animElements = document.querySelectorAll('.animate-img, .card-body p, .card-body h5');
                    animElements.forEach(el => {
                        el.style.animation = 'none';
                        setTimeout(() => {
                            el.style.animation = '';
                        }, 50);
                    });
                }, 100);
            }
            
            // Agregar efectos de hover interactivos
            const welcomeCard = document.getElementById('welcomeCard');
            welcomeCard.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            welcomeCard.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
            
            // Simular carga de datos del usuario
            setTimeout(() => {
                const userInfo = document.querySelector('.card-body h5');
                if (userInfo) {
                    userInfo.style.background = 'linear-gradient(135deg, #55ad9b, #95D2B3)';
                    userInfo.style.webkitBackgroundClip = 'text';
                    userInfo.style.webkitTextFillColor = 'transparent';
                    userInfo.style.backgroundClip = 'text';
                }
            }, 2000);
            
            // Agregar micro-interacciones
            document.addEventListener('mousemove', (e) => {
                const cards = document.querySelectorAll('#welcomeCard');
                cards.forEach(card => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    
                    const rotateX = (y - centerY) / 20;
                    const rotateY = (centerX - x) / 20;
                    
                    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px)`;
                });
            });
            
            document.addEventListener('mouseleave', () => {
                const cards = document.querySelectorAll('#welcomeCard');
                cards.forEach(card => {
                    card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0px)';
                });
            });
        });
    </script>
<script>
    $(document).ready(function(){
        document.querySelector('.animate-img').classList.add('visible');
        <?php
             if($DEV_MODE === false){
                echo '
                $.ajax({
                    url: "../ajax/ajax_notificacion.php",
                    type: "POST",
                    data: { mensaje: "El usuario ha cargado welcome: '.$usuario_desencriptado.'" },
                    success: function(response) {
                        console.log("Notificacion enviada: ", response);
                    },
                    error: function(error) {
                        console.error("Error al enviar la notificacion: ", error);
                    }
                });
                
                ';
            }
        ?>

        // Verificar si ya existe la preferencia en localStorage    
        /*
        if (!localStorage.getItem("welcomeUpdate2ToastShown")) {
            Swal.fire({
                title: 'Actualización',
                text: 'Descripción de estatus de barras mejorado al cotizar. Los billets seleccionados en cotizaciones vigentes se apartaran con estatus "En cotización" automáticamente para evitar problemas de stock. Para liberar las barras antes de la vigencia archive la cotización.',
                icon: 'info',
                confirmButtonText: 'Entendido',
                width: '500px',
                padding: '10px',
                position: 'bottom-end',
                toast: true,
                //timer: 5000, // El modal desaparece automáticamente después de 5 segundos (opcional)
                showConfirmButton: true,
                showCloseButton: false,
                input: 'checkbox',
                inputPlaceholder: 'No mostrar nuevamente',
                inputAttributes: {
                id: 'noMostrarCheckbox'
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                // Guardar preferencia en localStorage
                localStorage.setItem("welcomeUpdate2ToastShown", "1");
                }
            });
        }
        */    
        /*
        if (!localStorage.getItem("welcomeUpdateSecureConfig")) {
            Swal.fire({
                title: 'Sugerencia',
                text: 'Para evitar conflictos con versiones anteriores del sistema, se sugiere limpiar/eliminar los datos de navegación (cache, cookies, etc.).',
                icon: 'info',
                confirmButtonText: 'Entendido',
                width: '500px',
                padding: '10px',
                position: 'bottom-end',
                toast: true,
                //timer: 5000, // El modal desaparece automáticamente después de 5 segundos (opcional)
                showConfirmButton: true,
                showCloseButton: false,
                input: 'checkbox',
                inputPlaceholder: 'No mostrar nuevamente',
                inputAttributes: {
                id: 'noMostrarCheckbox'
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                // Guardar preferencia en localStorage
                localStorage.setItem("welcomeUpdateSecureConfig", "1");
                }
            });
        }
        */
        <?php
             if($SEND_MAIL === false){
                echo "
                    if (!localStorage.getItem('welcomeUpdateNoEmail')) {
                        Swal.fire({
                            title: 'Aviso',
                            text: 'El envío de correos del sistema no estará disponible hasta nuevo aviso. No será posible notificar mediante correos automáticos.',
                            icon: 'info',
                            confirmButtonText: 'Entendido',
                            width: '500px',
                            padding: '10px',
                            position: 'bottom-end',
                            toast: true,
                            //timer: 5000, // El modal desaparece automáticamente después de 5 segundos (opcional)
                            showConfirmButton: true,
                            showCloseButton: false,
                            input: 'checkbox',
                            inputPlaceholder: 'No mostrar nuevamente',
                            inputAttributes: {
                            id: 'noMostrarCheckbox'
                            }
                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                            // Guardar preferencia en localStorage
                            localStorage.setItem('welcomeUpdateNoEmail', '1');
                            }
                        });
                    }
                ";
            }
        ?>
    });
</script>
</body>
</html>
