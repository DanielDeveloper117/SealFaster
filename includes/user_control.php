<?php
    $selfFile = basename($_SERVER['PHP_SELF']);

    $id_usuario = $_SESSION['id'];
    $sql = "SELECT lider FROM login WHERE id = :id_usuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    $servidorEnMantenimientoAdvertencia = false; // Cambiar a true para activar la advertencia de mantenimiento
    $servidorEnMantenimiento = false; // Cambiar a true para activar el modo de mantenimiento

    if ($servidorEnMantenimientoAdvertencia) { 
        echo "<script type='text/javascript'>
                $(document).ready(function(){
                    Swal.fire({
                        title: 'Aviso',
                        text: 'Actualización programada a las 10:45 AM. Asegurece de guardar su trabajo para evitar perdida de datos.',
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
    if ($resultado !== false && isset($resultado['lider'])) {
        $tipoUsuario = $resultado['lider'];
        if ($tipoUsuario == 1) {
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "filtros_inventario_cnc.php",
                "inventario.php",
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
                "users.php"
            ]);
            if(!in_array($selfFile, $arrayPermitidos)){
                $accesoRestringido = True;
            }else{
                $accesoRestringido = False;
            }
        }else if($tipoUsuario == 2){
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "filtros_inventario_cnc.php",
                "inventario_vn.php",
                "produccion_cnc.php"
            ]);
            if (!in_array($selfFile, $arrayPermitidos)) {
                $accesoRestringido = True;
            } else {
                $accesoRestringido = False;
            }
        }else if ($tipoUsuario == 3) {
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "selectTipoSello.php",
                "tipo.php",
                "estimador.php",
                "filtros_inventario_cnc.php",
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

        }else if($tipoUsuario == 0){
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "filtros_inventario_cnc.php",
                "inventario_vn.php",
                "selectTipoSello.php",
                "tipo.php",
                "estimador.php",
                "cotizaciones.php",
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

        }else if($tipoUsuario == 5){
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

        }else if($tipoUsuario == 4){
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "precios_compras.php"
            ]);
            if(!in_array($selfFile, $arrayPermitidos)){
                $accesoRestringido = True;
            }else{
                //header de compras
                $accesoRestringido = False; 
            }

        }else if($tipoUsuario == 6){
            $arrayPermitidos = array_merge($arrayPermitidos, [
                "filtros_inventario_cnc.php",
                "inventario.php",
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
        include(ROOT_PATH . 'includes/headers/header.php');
    }
?>