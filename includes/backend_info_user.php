<?php
$id_usuarioInfo = $_SESSION['id'];
$sqlUserInfo = "SELECT * FROM login WHERE id = :id_usuario";
$stmtUserInfo = $conn->prepare($sqlUserInfo);
$stmtUserInfo->bindParam(':id_usuario', $id_usuarioInfo);
$stmtUserInfo->execute();
$arregloUser = $stmtUserInfo->fetch(PDO::FETCH_ASSOC);
$usuarioUser = "";
if ($arregloUser) {
    // Clave de encriptacionn
    $clave_encriptacion = 'SRS2024#tides';

    // Desencriptar el nombre
    
    $usuario_encriptado = $arregloUser['usuario'];
    $usuarioUser = openssl_decrypt($usuario_encriptado, 'AES-128-ECB', $clave_encriptacion);
    
    $nombre_encriptado = $arregloUser['nombre'];
    $nombreUser = openssl_decrypt($nombre_encriptado, 'AES-128-ECB', $clave_encriptacion);

    $area_encriptado = $arregloUser['area'];
    $areaUser = openssl_decrypt($area_encriptado, 'AES-128-ECB', $clave_encriptacion);

    $rolUser = $arregloUser['rol'];
    $fecha_creacion = $arregloUser['fechalogin'];
    $hora_creacion = $arregloUser['horalogin'];

    $lider_usuario = $arregloUser['lider'];
    $tipo_usuario = "";
    switch($lider_usuario){
        case 1:
            $tipo_usuario = "Administrador";
        break;
        case 2:
            $tipo_usuario = "CNC";
        break;
        case 3:
            $tipo_usuario = "Vendedor";
        break;
        case 4:
            $tipo_usuario = "Comprador";
        break;
        case 6:
            $tipo_usuario = "Inventarios";
        break;
        case 0:
            $tipo_usuario = "Sistemas";
        break;
        default:
            $tipo_usuario = "Desconocidos";
        break;
    } 
    $rol_usuario = $arregloUser['rol'];
} else {
    $nombreUser = 'Usuario desconocido';
}
?>