<?php
    require_once(ROOT_PATH . 'includes/backend_info_user.php');
    $selfFile = basename($_SERVER['PHP_SELF']);
    $servidorEnMantenimientoAdvertencia = false; // Cambiar a true para activar la advertencia de mantenimiento
    $horaProgramada = "1:00 PM";
    $servidorEnMantenimiento = false; // Cambiar a true para activar el modo de mantenimiento

    //$urlValida = "https://sellosyretenes.com/sealfaster";
    //$urlValida = "http://localhost/cotizador/";
    $urlActual = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    /*
    if (strpos($urlActual, $urlValida) === false) {
        echo "<script type='text/javascript'>
                $(document).ready(function(){
                    Swal.fire({
                        title: 'Nueva ubicacion disponible',
                        html: 'El enlace al sistema que estas usando ya no es la correcto.<br>' +
                            'Haz clic en el boton para ir al nuevo sitio.',
                        icon: 'warning',
                        confirmButtonText: 'Ir al nuevo sitio',
                        confirmButtonColor: '#55AD9B',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if(result.isConfirmed){
                            window.location.href = '$urlValida';
                        }
                    });
                });
            </script>";
        exit(); // Detener la carga de la pagina actual
    }
    */
    if ($servidorEnMantenimientoAdvertencia) { 
        echo "<script type='text/javascript'>
                $(document).ready(function(){
                    Swal.fire({
                        title: 'Aviso',
                        text: 'Actualización programada a las ".$horaProgramada.". Asegurece de guardar su trabajo para evitar perdida de datos.',
                        icon: 'info',
                        width: '600px',
                        padding: '10px',
                        timer: 4000,
                        position: 'bottom',
                        toast: true,
                        showConfirmButton: false,
                        //confirmButtonText: 'Ok',
                        //confirmButtonColor: '#55AD9B',
                        showCloseButton: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        
                    });
                });
            </script>";
        
    }
    if ($servidorEnMantenimiento && $id_usuario != 71) { // ID 71 es el administrador
        echo "<script type='text/javascript'>
                $(document).ready(function(){
                    Swal.fire({
                        title: 'Aviso',
                        text: 'El servidor se encuentra en mantenimiento. Actualización en curso para darte una mejor exteriencia. Por favor, espere unos minutos e intente de nuevo.',
                        icon: 'info',
                        confirmButtonText: 'Ok',
                        confirmButtonColor: '#55AD9B',
                        showCloseButton: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        window.location.href = '../auth/cerrar_sesion.php';
                    });
                });
            </script>";
        exit();
    }

    $tipoUsuario = 999;
    $arrayPermitidos = [
        "welcome.php",
        "configuracion.php"
    ];

    $accesoRestringido = False;
    if (!empty($rol_usuario) && !empty($tipo_usuario)) {
        $tipoUsuario = $tipo_usuario;
        if ($tipoUsuario == "Administrador") {
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "filtros_inventario_cnc.php",
                "inventario.php",
                "traspasos.php",
                "barras_venta.php",
                "almacenes.php",
                "selectTipoSello.php",
                "tipo.php",
                "estimador.php",
                "cotizaciones.php",
                "produccion_vn.php",
                "produccion_cnc.php",
                "parametros_cotizador.php",
                "precios.php",
                "precios_compras.php",
                "claves_alternas.php",
                "desencriptar.php",
                "users.php"
            ]);
            if(!in_array($selfFile, $arrayPermitidos)){
                $accesoRestringido = True;
            }else{
                $accesoRestringido = False;
            }
            
        }else if($tipoUsuario == "CNC"){
            if($rol_usuario == "Gerente"){
                $arrayPermitidos = array_merge($arrayPermitidos, [
                    "filtros_inventario_cnc.php",
                    "inventario_vn.php",
                    "almacenes.php",
                    "selectTipoSello.php",
                    "tipo.php",
                    "estimador.php",
                    "cotizaciones.php",
                    "produccion_cnc.php"
                ]);
            }else{
                $arrayPermitidos = array_merge($arrayPermitidos, [
                    "filtros_inventario_cnc.php",
                    "almacenes.php",
                    "inventario_vn.php",
                    "produccion_cnc.php"
                ]);
            }
            if (!in_array($selfFile, $arrayPermitidos)) {
                $accesoRestringido = True;
            } else {
                $accesoRestringido = False;
            }
        }else if ($tipoUsuario == "Vendedor") {
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "selectTipoSello.php",
                "tipo.php",
                "estimador.php",
                "filtros_inventario_cnc.php",
                "almacenes.php",
                "cotizaciones.php",
                "produccion_vn.php",
                "inventario_vn.php"
            ]);
            if(!in_array($selfFile, $arrayPermitidos)){
                $accesoRestringido = True;
            }else{
                //header de ventas
                $accesoRestringido = False;
            }

        }else if($tipoUsuario == "Sistemas"){
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "filtros_inventario_cnc.php",
                "inventario.php",
                "traspasos.php",
                "barras_venta.php",
                "almacenes.php",
                "selectTipoSello.php",
                "tipo.php",
                "estimador.php",
                "cotizaciones.php",
                "produccion_vn.php",
                "produccion_cnc.php",
                "parametros_cotizador.php",
                "precios.php",
                "precios_compras.php",
                "claves_alternas.php",
                "desencriptar.php",
                "users.php"
            ]);
            if(!in_array($selfFile, $arrayPermitidos)){
                $accesoRestringido = True;
            }else{
                //header de general/lider/sistemas
                $accesoRestringido = False; 
            }

        }else if($tipoUsuario == "Cliente Externo"){
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "selectTipoSello.php",
                "tipo.php",
                "estimador.php",
                "cotizaciones.php"
            ]);
            if(!in_array($selfFile, $arrayPermitidos)){
                $accesoRestringido = True;
            }else{
                //header de cliente externo
                $accesoRestringido = False; 
            }

        }else if($tipoUsuario == "Compras"){
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "precios_compras.php"
            ]);
            if(!in_array($selfFile, $arrayPermitidos)){
                $accesoRestringido = True;
            }else{
                //header de compras
                $accesoRestringido = False; 
            }

        }else if($tipoUsuario == "Inventarios"){
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "filtros_inventario_cnc.php",
                "inventario.php",
                "traspasos.php",
                "barras_venta.php",
                "almacenes.php",
                "produccion_cnc.php"
            ]);
            if(!in_array($selfFile, $arrayPermitidos)){
                $accesoRestringido = True;
            }else{
                //header de inventarios
                $accesoRestringido = False; 
            }
        }else{
            echo "<script type='text/javascript'>
                    $(document).ready(function(){
                        Swal.fire({
                            title: 'Aviso',
                            text: 'La sesion ha expirado, inicie sesion nuevamente.',
                            icon: 'warning',
                            confirmButtonText: 'Ok',
                            confirmButtonColor: '#dc3545',
                            showCloseButton: true,
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then((result) => {
                            window.location.href = '../auth/cerrar_sesion.php';
                        });
                    });
                </script>";
        }
    } else {
        echo "<script type='text/javascript'>
                $(document).ready(function(){
                    Swal.fire({
                        title: 'Aviso',
                        text: 'La sesion ha expirado, inicie sesion nuevamente2.".$rol_usuario.$lider_usuario.$tipoUsuario."',
                        icon: 'warning',
                        confirmButtonText: 'Ok',
                        confirmButtonColor: '#dc3545',
                        showCloseButton: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        window.location.href = '../auth/cerrar_sesion.php';
                    });
                });
            </script>";
    }
    if($accesoRestringido == True){
        echo "<script type='text/javascript'>
                $(document).ready(function(){
                    Swal.fire({
                        title: 'Aviso',
                        text: 'Acceso denegado a esta pagina, no cuenta con permisos necesarios. Inicie sesion nuevamente.',
                        icon: 'warning',
                        confirmButtonText: 'Ok',
                        confirmButtonColor: '#dc3545',
                        showCloseButton: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        window.location.href = '../auth/cerrar_sesion.php';
                    });
                });
            </script>";
    }else{
        include(ROOT_PATH . 'includes/headers/header2.php');
    }
?>