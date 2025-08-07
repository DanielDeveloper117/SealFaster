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
    <script src="<?= controlCache('../assets/js/modal_add_billet.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-table.css'); ?>">

    <title>Inventario CNC</title>

</head>
<body class="scroll-disablado">

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Funciones para inventario CNC</h1>
            <div class="d-flex flex-row justify-content-between col-12 gap-5 mt-5">
                <button type="button" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalConsultar">Buscar por material</button>
                <button type="button" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalClave">Buscar por clave</button>
                <a href="<?php if($tipoUsuario == 2 || $tipoUsuario == 1){echo 'inventario.php';}else{echo 'inventario_vn.php';}?>" 
                    class="btn-general" target="_blank" style="text-decoration:none;">Todo el inventario</a>
                <button type="button" id="btnAgregar2" class="btn-general <?php if($tipoUsuario != 2 && $tipoUsuario != 0 && $tipoUsuario != 1){echo 'd-none';}?>" 
                    data-bs-toggle="modal" data-bs-target="#modalInventario">Agregar Registro</button>
            </div>
            <div class="d-flex flex-row justify-content-between col-12 gap-5 mt-3">
                <a href="inventario.php?pendientes" 
                   class="btn-general <?php if($tipoUsuario != 2 && $tipoUsuario != 0 && $tipoUsuario != 1){echo 'd-none';}?>"
                   target="_blank">Claves pendientes</a>
                <button type="button" 
                        class="btn-general <?php if($tipoUsuario != 2 && $tipoUsuario != 0 && $tipoUsuario != 1){echo 'd-none';}?>" 
                        data-bs-toggle="modal" data-bs-target="#modalClavesValidas">Claves validas</button>
                <button type="button" class="btn-general invisible" data-bs-toggle="modal" data-bs-target="#modalX">Funcion</button>
                <button type="button" class="btn-general invisible" data-bs-toggle="modal" data-bs-target="#modalX">Funcion</button>

            </div>
        </div>
    </div>
</section>

<!-- Modal para crear query material y proveedor -->
<div class="modal fade" id="modalConsultar" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Consultar el inventario CNC</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php
                        if($tipoUsuario == 2 || $tipoUsuario == 1){
                            echo 'inventario.php';
                        }else{
                            echo 'inventario_vn.php';
                        }
                    ?>" method="GET" target="_blank">                         
                    <div id="containerSelectorMaterial" class="mb-4">
                        <label for="selectorMaterial" class="lbl-general">Material</label>
                        <select id="selectorMaterial" class="form-select" name="material" required >
                            <option value="" disabled selected>Seleccionar</option>
                        </select>
                    </div> 
                    <div id="containerSelectorProveedor" class="mb-4">
                        <label for="selectorProveedor" class="lbl-general">Proveedor</label>
                        <select id="selectorProveedor" class="form-select" name="proveedor">
                            <option value="all" selected>Todos</option>
                        </select>
                    </div> 

                    <button type="submit" class="btn-general">Consultar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para buscar por clave -->
<div class="modal fade" id="modalClave" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Consultar clave del inventario CNC</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php
                        if($tipoUsuario == 2 || $tipoUsuario == 1){
                            echo 'inventario.php';
                        }else{
                            echo 'inventario_vn.php';
                        }
                    ?>" method="GET" target="_blank">                        
                    <div class="mb-3">
                        <label for="inputClave" class="lbl-general">Clave</label>
                        <input type="text" class="input-text" id="inputClave" name="clave" required>
                    </div>

                    <button type="submit" class="btn-general">Consultar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear query claves validas -->
<div class="modal fade" id="modalClavesValidas" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 65% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Consultar claves validas existentes</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form >                         
                    <div id="containerSelectorProveedor" class="mb-4">
                        <label for="selectorP" class="lbl-general">Proveedor</label>
                        <select id="selectorP" class="selector" name="proveedor" required >
                            <option value="" disabled selected>Seleccionar</option>
                            <option value="SKF" >SKF</option>
                            <option value="SLM" >SLM</option>
                            <option value="TRYGONAL" >TRYGONAL</option>
                            <option value="CARVIFLON" >CARVIFLON</option>
                        </select>
                    </div> 
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputI" class="lbl-general">Medida interior</label>
                            <input id="inputI" type="number" class="input-text"  name="interior" placeholder="" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputE" class="lbl-general">Medida exterior</label>
                            <input id="inputE" type="number" class="input-text"  name="exterior" placeholder="" required>
                        </div>
                    </div>

                    <button id="btnConsutarClavesValidas" type="button" class="btn-general">Consultar</button>
                </form>
                <div>
                    <table id="tablaClavesValidas" class="table table-bordered mt-3" style="border:1px solid #495057;">
                        <thead class="table-active">
                            <tr>
                                <th>Clave</th>
                                <th>Proveedor</th>
                                <th>Tipo</th>
                                <th>Material</th>
                                <th>Interior</th>
                                <th>Exterior</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="6">Llene el formulario para consultar claves validas.</td></tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include(ROOT_PATH . 'includes/modal_add_billet.php'); ?>

<script>
    $(document).ready(function(){
        //const modalConsultar = new bootstrap.Modal(document.getElementById("modalConsultar"));

        // COINSULTA AJAX PARA MATERIALES
        $.ajax({
            url: '../ajax/ajax_materiales.php', 
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.length > 0) {
                    $.each(data, function(index, item) {
                        $("#selectorMaterial").append(
                            `
                            <option value="${item.material}">${item.material}</option>
                            `
                        );
                    });
                } else {
                }
            },
            error: function() {
                console.error('Error al realizar la petición AJAX');
            }
        });

        $("#btnConsutarClavesValidas").on("click", function(){
            $("#tablaClavesValidas tbody").empty();
            $(`#tablaClavesValidas tbody`).append(`<tr><td colspan="6">Cargando...</td></tr>`);

            let proveedorCCV = $("#selectorP").val();
            let interiorCCV = $("#inputI").val();
            let exteriorCCV = $("#inputE").val();
            // COINSULTA AJAX PARA TRAER LAC CLAVES VALIDAS
            $.ajax({
                url: '../ajax/claves_validas.php', 
                type: 'GET',
                dataType: 'json',
                data: {
                    proveedor: proveedorCCV,
                    interior: interiorCCV,
                    exterior: exteriorCCV
                },
                success: function(data) {
                    if (data.length > 0) {
                        $("#tablaClavesValidas tbody").empty();
                        $.each(data, function(index, item) {
                            $("#tablaClavesValidas tbody").append(
                                `
                                <tr>
                                    <td>${item.clave}</td>
                                    <td>${item.proveedor}</td>
                                    <td>${item.tipo}</td>
                                    <td>${item.material}</td>
                                    <td>${item.interior}</td>
                                    <td>${item.exterior}</td>
                                </tr>
                                `
                            );
                        });
                    } else {
                        $("#tablaClavesValidas tbody").empty();
                        $(`#tablaClavesValidas tbody`).append(`<tr><td colspan="6">No se encontraron resultados coincidentes.</td></tr>`);

                    }
                },
                error: function() {
                    $("#tablaClavesValidas tbody").empty();
                    $(`#tablaClavesValidas tbody`).append(`<tr><td colspan="6" style="color:#dc3545;">Hubo un problema al consultar</td></tr>`);
                    console.error('Error al realizar la petición AJAX');
                }
            });

            // $.ajax({
            //     url: "../ajax/ajax_notificacion.php",
            //     type: "POST",
            //     data: { mensaje: "Se ha consultado a claves validas" },
            //     success: function(response) {
            //         console.log("Notificacion enviada: ", response);
            //     },
            //     error: function(error) {
            //         console.error("Error al enviar la notificacion: ", error);
            //     }
            // });
        });

    });
</script>
</body>
</html>








