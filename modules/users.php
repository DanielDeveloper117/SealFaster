<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'includes/functions/control_cache.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../auth/cerrar_sesion.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/v/dt/dt-2.0.0/datatables.min.js"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <!-- <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-table.css'); ?>">    -->
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css"'); ?>"> 

    <title>Usuarios</title>

</head>
<?php
function generarCodigoVerificacion() {
    return mt_rand(100000, 999999);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['action']) && isset($_POST['id'])) {
     
        $action = $_POST['action'];
        $id = $_POST['id'];

        set_error_handler(function($severity, $message, $file, $line) {
            throw new ErrorException($message, 0, $severity, $file, $line);
        });

        // Generar código de verificación
        $codigoVerificacion = generarCodigoVerificacion();
        
        if ($action === 'insert') {
            try {
                $usuario = $_POST['usuario'];
                $password = $_POST['password'];
                $nombre = $_POST['nombre'];
                $area = $_POST['area'];
                $lider = $_POST['lider'];
                $activo = $_POST['activo'];
                $rol = $_POST['rol'];

                // Validar lider
                if (!in_array($lider, ['0','1','2','3','4','5','6'])) {
                    echo '<script>document.addEventListener("DOMContentLoaded", function () {
                        sweetAlertResponse("error", "Error", "Error, datos no validos", "self");
                    });</script>';
                    exit;     
                }

                $lider = (int)$lider;

                // Encriptacion
                $clave_encriptacion = $PASS_UNCRIPT ?? '';
                $usuario_encriptado = openssl_encrypt($usuario, 'AES-128-ECB', $clave_encriptacion);
                $password_encriptada = openssl_encrypt($password, 'AES-128-ECB', $clave_encriptacion);
                $nombre_encriptado = openssl_encrypt($nombre, 'AES-128-ECB', $clave_encriptacion);
                $area_encriptada = openssl_encrypt($area, 'AES-128-ECB', $clave_encriptacion);

                // 1. Validar duplicado
                $check = $conn->prepare("SELECT COUNT(*) FROM login WHERE usuario = :usuario");
                $check->bindParam(':usuario', $usuario_encriptado, PDO::PARAM_STR);
                $check->execute();
                $existe = $check->fetchColumn();

                if ($existe > 0) {
                    echo '<script>document.addEventListener("DOMContentLoaded", function () {
                        sweetAlertResponse("warning", "Duplicado", "El usuario ya existe, ingrese un correo diferente.", "self");
                    });</script>';
                    exit;
                }

                // 2. Insertar si no existe
                $stmt = $conn->prepare("INSERT INTO login 
                    (usuario, password, nombre, area, fechalogin, horalogin, activo, lider, codigoVerificacion, rol) 
                    VALUES (:usuario, :password, :nombre, :area, CURDATE(), CURTIME(), :activo, :lider, :codigoVerificacion, :rol)");
                $stmt->bindParam(':usuario', $usuario_encriptado, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password_encriptada, PDO::PARAM_STR);
                $stmt->bindParam(':nombre', $nombre_encriptado, PDO::PARAM_STR);
                $stmt->bindParam(':area', $area_encriptada, PDO::PARAM_STR);
                $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
                $stmt->bindParam(':lider', $lider, PDO::PARAM_INT);
                $stmt->bindParam(':codigoVerificacion', $codigoVerificacion, PDO::PARAM_STR);
                $stmt->bindParam(':rol', $rol, PDO::PARAM_STR);
                $stmt->execute();
                
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("success", "Proceso exitoso", "Registro agregado correctamente.", "self");
                    });</script>';
                    
                exit;
            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("error", "Error", "Error al intentar agregar registro: ' . addslashes($e->getMessage()) . '", "self");
                });</script>';
                exit;
            }
        }elseif ($action === 'update') {
            try{
                $usuario = $_POST['usuario'];
                $password = $_POST['password'];
                $nombre = $_POST['nombre'];
                $area = $_POST['area'];
                $lider = $_POST['lider'];
                $activo = $_POST['activo'];
                $rol = $_POST['rol'];
    
                // Verificar el valor de lider antes de continuar
                if ($lider !== '0' && $lider !== '1'&& $lider !== '2' && $lider !== '3' && $lider !== '4' && $lider !== '5' && $lider !== '6') {
                    echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("error", "Error", "Error, datos no validos", "self");
                    });</script>';
                    exit;     
                }
    
                // Convertir el valor de lider a entero
                $lider = (int)$lider;
                // Encriptación
                $clave_encriptacion = $PASS_UNCRIPT ?? '';
                $usuario_encriptado = openssl_encrypt($usuario, 'AES-128-ECB', $clave_encriptacion);
                $password_encriptada = openssl_encrypt($password, 'AES-128-ECB', $clave_encriptacion);
                $nombre_encriptado = openssl_encrypt($nombre, 'AES-128-ECB', $clave_encriptacion);
                $area_encriptada = openssl_encrypt($area, 'AES-128-ECB', $clave_encriptacion);
                
                // Actualización en la tabla login
                $stmt = $conn->prepare("UPDATE login SET 
                    usuario = :usuario, 
                    password = :password, 
                    nombre = :nombre, 
                    area = :area, 
                    activo = :activo, 
                    lider = :lider,
                    rol = :rol
                    WHERE id = :id");
    
                $stmt->bindParam(':usuario', $usuario_encriptado, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password_encriptada, PDO::PARAM_STR);
                $stmt->bindParam(':nombre', $nombre_encriptado, PDO::PARAM_STR);
                $stmt->bindParam(':area', $area_encriptada, PDO::PARAM_STR);
                $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
                $stmt->bindParam(':lider', $lider, PDO::PARAM_INT);
                $stmt->bindParam(':rol', $rol, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute(); 
    
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("success", "Proceso exitoso", "Registro actualizado correctamente.", "self");
                });</script>';
                exit;

            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                    sweetAlertResponse("error", "Error", "Error al intentar actualizar el registro' . addslashes($e->getMessage()) . '", "self");
                });</script>';
                exit;
            }


        } elseif ($action === 'delete') {
            try{
                // Eliminación en la tabla login
                $stmt = $conn->prepare("DELETE FROM login WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->execute();
    
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("success", "Proceso exitoso", "Registro eliminado correctamete. ", "self");
                });</script>';
                exit; 

            } catch (Throwable $e) {
                echo '<script>document.addEventListener("DOMContentLoaded", function () {
                sweetAlertResponse("error", "Error", "Error al intentar eliminar registro'. addslashes($e->getMessage()).'", "self");
                });</script>';
                exit; 
            }
        }
    } else {
    }

}
    $sqlUsers = "SELECT * FROM login ORDER  BY fechalogin DESC";
    $stmtUsers = $conn->prepare($sqlUsers);
    $stmtUsers->execute();
    $arregloSelectUsers = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);
?>
<body class="scroll-disablado">
    
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Administracion de usuarios</h1>
            <div class="d-flex col-4 flex-row justify-content-start">
                <button type="button" id="btnAgregar" class="btn-general " data-bs-toggle="modal" data-bs-target="#modalAgregarEditar">Agregar Registro</button>
            </div>
        </div>
        <div class="mt-4 table-container">
            <table id="usersTable" class="mainTable table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Acciones</th>
                        <th>Id</th>
                        <th>Usuario/correo</th>
                        <th>Nombre</th>
                        <th>Tipo usuario</th>
                        <th>Área</th>
                        <th>Rol</th>
                        <th>Estatus</th>
                        <th>Fecha creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arregloSelectUsers as $row):?>
                    <?php 
                        // Clave de encriptaci܇on
                        $clave_encriptacion = $PASS_UNCRIPT ?? '';

                        $usuario_encriptado = $row['usuario'];
                        $row['usuario'] = openssl_decrypt($usuario_encriptado, 'AES-128-ECB', $clave_encriptacion);
                        // Desencriptar el nombre
                        $nombre_encriptado = $row['nombre'];
                        $row['nombre'] = openssl_decrypt($nombre_encriptado, 'AES-128-ECB', $clave_encriptacion);

                        $area_encriptado = $row['area'];
                        $row['area'] = openssl_decrypt($area_encriptado, 'AES-128-ECB', $clave_encriptacion);

                        $pass_encriptado = $row['password'];
                        $row['password'] = openssl_decrypt($pass_encriptado, 'AES-128-ECB', $clave_encriptacion);

                        $tipoUsuarioFrontend = "";
                        $estatusFrontend = "";
                        switch($row['lider']){
                            case 0:
                                $tipoUsuarioFrontend = "SISTEMAS";
                            break;
                            case 1:
                                $tipoUsuarioFrontend = "ADMIN";
                            break;
                            case 2:
                                $tipoUsuarioFrontend = "CNC";
                            break;
                            case 3:
                                $tipoUsuarioFrontend = "VENTAS";
                            break;
                            case 4:
                                $tipoUsuarioFrontend = "COMPRAS";
                            break;
                            case 6:
                                $tipoUsuarioFrontend = "INVENTARIOS";
                            break;
                            default:
                                $tipoUsuarioFrontend = "Desconocido";
                            break;
                        }
                        switch($row['activo']){
                            case 0:
                                $estatusFrontend = "Desactivado";
                            break;
                            case 1:
                                $estatusFrontend = "Activado";
                            break;
                            default:
                                $estatusFrontend = "Desconocido";
                            break;
                        }
                    ?>
                    <tr>
                        <td class="d-fex flex-column">
                            <div class="d-flex flex-column">
                                <button class="btn-general edit-btn mb-1" 
                                    data-id="<?= $row['id']; ?>"
                                    data-usuario="<?= $row['usuario']; ?>"
                                    data-nombre="<?= $row['nombre']; ?>"
                                    data-lider="<?= $row['lider']; ?>"
                                    data-area="<?= $row['area']; ?>"
                                    data-rol="<?= $row['rol']; ?>"
                                    data-pass="<?= $row['password']; ?>"
                                    data-activo="<?= $row['activo']; ?>"
                                    >Editar</button>
                                <form class="form-delete" action="" method="POST">
                                    <input type="hidden" name="id" value="<?= $row['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-eliminar delete-btn">Eliminar</button>
                                </form>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($row['id']); ?></td>
                        <td><?= htmlspecialchars($row['usuario']); ?></td>
                        <td><?= htmlspecialchars($row['nombre']); ?></td>
                        <td><?= htmlspecialchars($tipoUsuarioFrontend); ?></td>
                        <td><?= htmlspecialchars($row['area']); ?></td>
                        <td><?= htmlspecialchars($row['rol']??"No definido"); ?></td>
                        <td><?= htmlspecialchars($estatusFrontend); ?></td>
                        <td><?= htmlspecialchars($row['fechalogin']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<style>
    .pass-container .btn{
        height:-webkit-fill-available;
        border: 1px solid #bbb;
    }
    .pass-container .btn i{
        color: #bbb;
    }
    .pass-container .btn:hover{
        border: 1px solid #55AD9B;
    }
    .pass-container .btn:hover i{
        color: #55AD9B;
    }
</style>
<!-- Modal para agregar/editar registro -->
<div class="modal fade" id="modalAgregarEditar" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="titleModalAddEdit" class="modal-title" id="modalLabel"></h5>
                <button id="btnCloseModal" type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formPost" action="" method="POST">                        
                    <input type="hidden" id="inputId" name="id">
                    <input type="hidden" id="inputAction" name="action">

                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputUser" class="lbl-general">Usuario/correo</label>
                            <input id="inputUser" type="email" class="input-text" name="usuario" placeholder="" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputNombre" class="lbl-general">Nombre</label>
                            <input id="inputNombre" type="text" class="input-text" name="nombre" placeholder="" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputTipo" class="lbl-general">Tipo de usuario</label>
                            <select id="inputTipo" class="selector" name="lider" required >
                                <option value="" selected disabled>Seleccione permisos</option>
                                <option value="0">SISTEMAS</option>
                                <option value="1">ADMIN</option>
                                <option value="2">CNC</option>
                                <option value="3">VENTAS</option>
                                <option value="4">COMPRAS</option>
                                <option value="6">INVENTARIOS</option>
                                <option value="5">EXTERNO</option>
                            </select>
                        </div>
                        <div class="pass-container" style="width:48%;">
                            <label for="inputPass" class="lbl-general">Password</label>
                            <div class="d-flex col-12 justify-content-evenly align-items-center gap-1">
                                <button id="btnCopyPass" type="button" class="btn" title="Copiar password"><i class="bi bi-clipboard"></i></button>
                                <input id="inputPass" type="password" class="input-text" name="password" placeholder="" required>
                                <button id="btnShowPass" type="button" class="btn" title="Ver password"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">

                        <div class="" style="width:48%;">
                            <label for="inputArea" class="lbl-general">Area</label>
                            <select id="inputArea" class="selector" name="area" required >
                                <option value="" selected disabled>Seleccione área</option>
                            </select>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputRol" class="lbl-general">Rol en el área</label>
                            <select id="inputRol" class="selector" name="rol" required >
                                <option value="" selected disabled>Seleccione un rol</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputEstatus" class="lbl-general">Estatus</label>
                            <select id="inputEstatus" class="selector" name="activo" required >
                                <option value="" disabled selected>Seleccionar</option>
                                <option value="0">Desactivado</option>
                                <option value="1">Activado</option>
                            </select>
                        </div>
                    </div>

                    <button id="btnGuardar" type="submit" class="btn-general">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function(){
        // CLICK A EDITAR UN REGISTRO
        $('#usersTable').on('click', '.edit-btn', function() {
            $("#formPost")[0].reset(); // Limpia el formulario antes de usarlo

            const data = {
                id: $(this).data('id'),
                usuario: $(this).data('usuario'),
                nombre: $(this).data('nombre'),
                area: $(this).data('area'),
                pass: $(this).data('pass'),
                activo: $(this).data('activo'),
                rol: $(this).data('rol'),
                tipo: $(this).data('lider')
            };

            $('#inputAction').val('update');
            $("#titleModalAddEdit").text("Editar registro");
            $('#modalAgregarEditar').modal('show');

            // Establece el tipo (esto dispara el change que carga las áreas/roles)
            $('#inputTipo').val(data.tipo).trigger('change');

            // Espera a que el cambio en #inputTipo termine de regenerar los selects
            setTimeout(() => {
                $('#inputId').val(data.id);
                $('#inputUser').val(data.usuario);
                $('#inputNombre').val(data.nombre);
                $('#inputPass').val(data.pass);
                $('#inputEstatus').val(data.activo);

                // Selecciona las opciones de area y rol si existen
                $('#inputArea').val(data.area);
                $('#inputRol').val(data.rol);
            }, 500); // suficiente para que se regeneren los selects
        });

        // CAMBIAR A add AL CLICK AGREGAR REGISTRO
        $("#btnAgregar").on("click", function(){
            $('#modalAgregarEditar').modal('show');
            $('#inputAction').val('insert');
            $("#titleModalAddEdit").text("Agregar registro");
        });

        // SELECTORES DE USUARIO
        $("#inputTipo").on("change", function(){
            let inputTipo = $("#inputTipo").val();
            let inputArea = $("#inputArea").val();
            if(inputTipo == "0"){
                $("#inputArea").html(`<option value="" selected disabled>Seleccione área</option>
                                <option value="Sistemas">Sistemas</option>`);
                $("#inputRol").html(`<option value="" selected disabled>Seleccione un rol</option>
                                <option value="Gerente">Gerente</option>
                                <option value="Auxiliar">No gerente</option>`);
            }if(inputTipo == "1"){
                $("#inputArea").html(`<option value="" selected disabled>Seleccione área</option>
                                <option value="Direccion">Direccion</option>
                                <option value="Sistemas">Sistemas</option>`);
                $("#inputRol").html(`<option value="" selected disabled>Seleccione un rol</option>
                                <option value="Gerente">Gerente</option>
                                <option value="Auxiliar">Auxiliar</option>`);
            }if(inputTipo == "2"){
                $("#inputArea").html(`<option value="" selected disabled>Seleccione área</option>
                                <option value="Sellos Maquinados">Sellos Maquinados</option>
                                `);
                $("#inputRol").html(`<option value="" selected disabled>Seleccione un rol</option>
                                <option value="Gerente">Gerente</option>
                                <option value="Auxiliar">No gerente</option>
                                <option value="Máquina 1">Máquina 1</option>
                                <option value="Máquina 2">Máquina 2</option>
                                <option value="Máquina 3">Máquina 3</option>
                                <option value="Máquina 4">Máquina 4</option>
                                <option value="Máquina 5">Máquina 5</option>`);
            }if(inputTipo == "3"){
                $("#inputArea").html(`<option value="" selected disabled>Seleccione área</option>
                                <option value="Ventas Nacionales">Ventas Nacionales</option>
                                <option value="Ventas Internacionales">Ventas Internacionales</option>
                                <option value="Ventas Industriales">Ventas Industriales</option>
                                <option value="Sucursal Industrias">Sucursal Industrias</option>
                                <option value="Sucursal Monterrey">Sucursal Monterrey</option>
                                <option value="Sucursal Queretaro">Sucursal Queretaro</option>
                                <option value="Sucursal Saltillo">Sucursal Saltillo</option>
                                <option value="Sucursal Toluca">Sucursal Toluca</option>
                                <option value="Sucursal Veracruz">Sucursal Veracruz</option>
                                <option value="Taller">Taller</option>
                                `);
                $("#inputRol").html(`<option value="" selected disabled>Seleccione un rol</option>
                                <option value="Gerente">Gerente</option>
                                <option value="Auxiliar">No gerente</option>`);
            }if(inputTipo == "4"){
                $("#inputArea").html(`<option value="" selected disabled>Seleccione área</option>
                                <option value="Compras">Compras</option>`);
                $("#inputRol").html(`<option value="" selected disabled>Seleccione un rol</option>
                                <option value="Gerente">Gerente</option>
                                <option value="Auxiliar">No gerente</option>`);
            }if(inputTipo == "5"){
                $("#inputArea").html(`<option value="" selected disabled>Seleccione área</option>
                                <option value="Cliente Externo">Cliente Externo</option>`);
                $("#inputRol").html(`<option value="" selected disabled>Seleccione un rol</option>
                                <option value="Externo">Externo</option>`);
            }if(inputTipo == "6"){
                $("#inputArea").html(`<option value="" selected disabled>Seleccione área</option>
                                <option value="Inventarios">Inventarios</option>`);
                $("#inputRol").html(`<option value="" selected disabled>Seleccione un rol</option>
                                <option value="Gerente">Gerente</option>
                                <option value="Auxiliar">No gerente</option>`);
            }else{
                $("#inputArea").val("");
                $("#inputRol").val("");
            }
        
        });
        // $("#inputArea").on("change", function(){
        //     let inputArea = $("#inputArea").val();
        //     if(inputArea == "Ingenieria"){
        //         $("#inputRol").html(`<option value="" selected disabled>Seleccione un rol</option>
        //                         <option value="Ingenieria">Ingenieria</option>`);
        //     }else{
        //         //$("#inputTipo").trigger("change");
        //     }
        // });
        // FIN SELECTORES DE USUARIO

        // --- 1. Toggle Password Visibility (Ver/Ocultar Contraseña) ---
        $('#btnShowPass').on('click', function() {
            // Target the input field and the icon inside the button
            // Seleccionamos el input y el ícono dentro del botón
            const $input = $('#inputPass');
            const $icon = $(this).find('i');

            // Check current input type
            // Verificamos el tipo de input actual
            if ($input.attr('type') === 'password') {
                // Switch to text to show password
                // Cambiamos a texto para mostrar la contraseña
                $input.attr('type', 'text');
                
                // Update icon to 'eye-slash' (closed eye)
                // Actualizamos el ícono a 'ojo tachado'
                $icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                // Switch back to password to hide it
                // Regresamos a tipo password para ocultarla
                $input.attr('type', 'password');
                
                // Revert icon to 'eye'
                // Revertimos el ícono al 'ojo' normal
                $icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });

        // --- 2. Copy to Clipboard (Copiar al Portapapeles) ---
        $('#btnCopyPass').on('click', function() {
            const password = $('#inputPass').val();
            const $btn = $(this);
            const $icon = $btn.find('i');
            const originalIconClass = 'bi-clipboard';

            // Validate if password is not empty
            // Validar si la contraseña no está vacía
            if (!password) return;

            // Use the Clipboard API
            // Usar la API del Portapapeles
            navigator.clipboard.writeText(password).then(function() {
                // --- UX Feedback (Feedback Visual) ---
                
                // Change icon to checkmark to indicate success
                // Cambiar ícono a palomita para indicar éxito
                $icon.removeClass(originalIconClass).addClass('bi-check-lg');
                $btn.css('border-color', '#198754'); // Bootstrap success color (green)
                $icon.css('color', '#198754');

                // Revert back after 1.5 seconds
                // Revertir cambios después de 1.5 segundos
                setTimeout(function() {
                    $icon.removeClass('bi-check-lg').addClass(originalIconClass);
                    // Remove inline styles to return to CSS defaults
                    // Remover estilos en línea para volver a los defaults de CSS
                    $btn.attr('style', ''); 
                    $icon.attr('style', '');
                }, 1500);

            }).catch(function(err) {
                console.error('Error copying to clipboard: ', err);
            });
        });

        // --- 3. Security Reset (Reset de Seguridad) ---
        // Ensure password is hidden again when modal closes
        // Asegurar que la contraseña se oculte de nuevo al cerrar el modal
        $('#modalAgregarEditar').on('hidden.bs.modal', function () {
            $('#inputPass').attr('type', 'password');
            $('#btnShowPass i').removeClass('bi-eye-slash').addClass('bi-eye');
        });

        // RESETEAR EL FORMULARIO AL CERRAR
        $("#btnCloseModal").on("click", function(){
            $("#formPost")[0].reset();
        });
    });
</script>
</body>
</html>

