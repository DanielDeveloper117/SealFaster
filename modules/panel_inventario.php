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
    <title>Inventario CNC</title>
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/sweetalert2.min.css'); ?>">
    <script src="<?= controlCache('../assets/dependencies/sweetalert2@11.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>"></script>
    <link href="<?= controlCache('../assets/dependencies/datatables.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/datatables.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css'); ?>">
    
    <!-- Estilos específicos para el menú de funciones -->
    <style>
        /* ===== ESTILOS NUEVOS PARA EL MENÚ DE FUNCIONES ===== */
        /* Contenedor principal mejorado */
        .dashboard-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid var(--border-color);
            box-shadow: 
                0 20px 40px var(--shadow-color),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            padding: 2rem;
            position: relative;
            overflow: hidden;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        
        .dashboard-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--glow-color), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        /* Título mejorado */
        .dashboard-title {
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(180deg, var(--glow-color), #95D2B3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0 0 2rem 0;
            position: relative;
            text-shadow: 0px 0px 10px rgba(85, 173, 155, 0.5);
            animation: titleGlow 4s cubic-bezier(0.4, 0, 1, 1) infinite alternate;
            text-align: center;
        }
        
        /* Grid de funciones mejorado */
        .functions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        /* Tarjetas de función */
        .function-card {
            background: var(--surface-bg);
            border-radius: 15px;
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .function-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(85, 173, 155, 0.2);
            border-color: var(--glow-color);
        }
    
        
        /* Iconos de función */
        .function-icon {
            font-size: 2.5rem;
            color: var(--glow-color);
            margin-bottom: 1rem;
            text-align: center;
        }
        
        /* Títulos de función */
        .function-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
            text-align: center;
        }
        
        /* Descripciones de función */
        .function-description {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            flex-grow: 1;
            text-align: center;
        }
        
        /* Botones de función */
        .function-button {
            width: 100%;
            text-align: center;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: var(--glow-color);
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            box-shadow: 0 2px 4px rgba(85, 173, 155, 0.2);
            text-decoration: none;
            display: block;
        }
        
        .function-button:hover {
            background-color: #95D2B3;
            box-shadow: 0 4px 8px rgba(85, 173, 155, 1.2);
            transform: translateY(-3px);
            color: white;
            text-decoration: none;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Secciones del dashboard */
        .dashboard-section {
            margin-bottom: 2.5rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .functions-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-title {
                font-size: 1.8rem;
            }
            
            .dashboard-container {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
        
        /* Estados de función deshabilitada */
        .function-disabled {
            opacity: 0.6;
            pointer-events: none;
        }
        
        /* ===== FIN DE ESTILOS NUEVOS ===== */
    </style>
</head>
<body class="scroll-disablado">

<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<!-- Contenedor principal del dashboard -->
<section class="section-table flex-column d-flex col-12 justify-content-center align-items-center">
    <div class="col-11 dashboard-container">
        <h1 class="dashboard-title">Panel de funciones para inventario CNC</h1>
        
        <!-- Sección de funciones principales -->
        <div class="dashboard-section">
            <h2 class="section-title">Funciones Principales</h2>
            <div class="functions-grid">
                <!-- Búsqueda por Material -->
                <div class="function-card">
                    <div class="function-icon">
                        <i class="bi bi-card-list"></i>
                    </div>
                    <h3 class="function-title">Búsqueda por Material</h3>
                    <p class="function-description">Consulta el inventario filtrando por tipo de material y proveedor específico.</p>
                    <button type="button" class="function-button" data-bs-toggle="modal" data-bs-target="#modalConsultar">
                        Ver filtros
                    </button>
                </div>
                
                <!-- Búsqueda por Clave -->
                <div class="function-card">
                    <div class="function-icon">
                        <i class="bi bi-input-cursor-text"></i>
                    </div>
                    <h3 class="function-title">Búsqueda por Clave</h3>
                    <p class="function-description">Ingresa la clave y busca todos los registros coincidentes en el Inventario CNC.</p>
                    <button type="button" class="function-button" data-bs-toggle="modal" data-bs-target="#modalClave">
                        Digitar clave
                    </button>
                </div>

                <!-- Búsqueda por lote-->
                <div class="function-card">
                    <div class="function-icon">
                        <i class="bi bi-database"></i>
                    </div>
                    <h3 class="function-title">Buscar Lote</h3>
                    <p class="function-description">Ingresa un lote específico.</p>
                    <button type="button" class="function-button" data-bs-toggle="modal" data-bs-target="#modalLP">
                        Digitar lote
                    </button>
                </div>
                
                <!-- Inventario Completo -->
                <div class="function-card">
                    <div class="function-icon">
                        <i class="bi bi-table"></i>
                    </div>
                    <h3 class="function-title">Inventario Completo</h3>
                    <p class="function-description">Cargar todos los registros de un almacen en una nueva pestaña. Esto puede tardar demasiado.</p>
                    <button type="button" class="function-button" data-bs-toggle="modal" data-bs-target="#modalQueAlmacen">
                        Ver almacenes
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Sección de funciones administrativas -->
        <div class="dashboard-section <?php if($tipoUsuario == "CNC" || $tipoUsuario == "Vendedor"){echo 'd-none';}?>">
            <h2 class="section-title">Funciones Administrativas</h2>
            <div class="functions-grid">
                <!-- Agregar Registro -->
                <div class="function-card ">
                    
                    <div class="function-icon">
                        <i class="bi bi-plus-circle"></i>
                    </div>
                    <h3 class="function-title">Agregar Registro</h3>
                    <p class="function-description">Llena el formulario y agrega nuevas barras al inventario CNC.</p>
                    <button type="button" class="function-button" data-bs-toggle="modal" data-bs-target="#modalInventario">
                        Abrir formulario
                    </button>
                </div>

                <!-- Claves Válidas -->
                <div class="function-card ">
                    
                    <div class="function-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <h3 class="function-title">Claves Válidas</h3>
                    <p class="function-description">Consulta las claves válidas existentes según proveedor y medidas.</p>
                    <button type="button" class="function-button" data-bs-toggle="modal" data-bs-target="#modalClavesValidas">
                        Abrir buscador
                    </button>
                </div>   

                <!-- Claves Pendientes -->
                <div class="function-card ">
                    
                    <div class="function-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h3 class="function-title">Claves Pendientes</h3>
                    <p class="function-description">Revisa las claves que requieren ser corregidas para habilitar las barras al cotizar. La tabla podría tardar en cargar.</p>
                    <a href="inventario.php?pendientes" class="function-button" target="_blank">
                        Cargar tabla<i class="bi bi-arrow-up-right mx-2"></i>
                    </a>
                </div>
                 <!-- Barras Archivadas -->
                <div class="function-card">
                    <div class="function-icon">
                        <i class="bi bi-archive"></i>
                    </div>
                    <h3 class="function-title">Barras archivadas y pendientes</h3>
                    <p class="function-description">Consultar las barras archivadas y pendientes de autorización del inventario CNC en una nueva pestaña.</p>
                    <a href="<?php if($tipoUsuario == "CNC" || $tipoUsuario == "Vendedor"){echo 'inventario_vn.php';}else{echo 'inventario.php?archivados';}?>" 
                       class="function-button" target="_blank">
                        Consultar<i class="bi bi-arrow-up-right mx-2"></i>
                    </a>
                </div>               

                <!-- Agregar Almacen -->
                 <?php if ((($tipo_usuario === "Inventarios" && $rol_usuario == "Gerente") 
                            || ($tipo_usuario === "Administrador") 
                            || ($tipo_usuario == "Sistemas"))): ?>
                    <div class="function-card ">
                        
                        <div class="function-icon">
                            <i class="bi bi-building-add"></i>
                        </div>
                        <h3 class="function-title">Agregar Almacén</h3>
                        <p class="function-description">Llena el formulario y agrega un nuevo almacén al sistema.</p>
                        <button type="button" class="btnAgregarAlmacen function-button" data-bs-toggle="modal" data-bs-target="#modalAlmacen">
                            Abrir formulario
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Modal para crear query material y proveedor -->
<div class="modal fade" id="modalConsultar" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Consultar el inventario CNC</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php
                        if($tipoUsuario == "CNC" || $tipoUsuario == "Vendedor"){
                            echo 'inventario_vn.php';
                        }else{
                            echo 'inventario.php';
                        }
                    ?>" method="GET" target="_blank" id="formMaterial"> 
                    
                    <div class="mb-3">
                        <label for="inputAlmacenId1" class="form-label fw-bold">Almacén <span class="text-danger">*</span></label>
                        <select id="inputAlmacenId1" class="inputAlmacenIdClass selector" name="origen" required>
                            <option value="" disabled selected>Seleccionar un almacén</option>
                        </select>
                    </div>                        
                    <div id="containerSelectorMaterial" class="mb-3">
                        <label for="selectorMaterial" class="lbl-general">Material <span class="text-danger">*</span></label>
                        <select id="selectorMaterial" class="form-select selectorMaterialesInventario" name="material" required >
                            <option value="" disabled selected>Seleccionar...</option>
                        </select>
                    </div> 
                    <div id="containerSelectorProveedor" class="mb-3">
                        <label for="selectorProveedor" class="lbl-general">Proveedor</label>
                        <select id="selectorProveedor" class="form-select selectorProveedoresInventario" name="proveedor">
                            <option value="all" selected>Todos</option>
                        </select>
                    </div> 

                    <button type="submit" class="btn-general">Consultar<i class="bi bi-arrow-up-right mx-2"></i></button>
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
                <h5 class="modal-title">Consultar clave del inventario CNC</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php
                        if($tipoUsuario == "CNC" || $tipoUsuario == "Vendedor"){
                            echo 'inventario_vn.php';
                        }else{
                            echo 'inventario.php';
                        }
                    ?>" method="GET" target="_blank" id="formClave">
                    <div class="mb-3">
                        <label for="inputAlmacenId2" class="form-label fw-bold">Almacén <span class="text-danger">*</span></label>
                        <select id="inputAlmacenId2" class="inputAlmacenIdClass selector" name="origen" required>
                            <option value="" disabled selected>Seleccionar un almacén</option>
                        </select>
                    </div>                                
                    <div class="mb-3">
                        <label for="inputClave" class="lbl-general">Clave <span class="text-danger">*</span></label>
                        <input type="text" class="input-text" id="inputClave" name="clave" required>
                    </div>

                    <button type="submit" class="btn-general">Consultar<i class="bi bi-arrow-up-right mx-2"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para buscar por lote-->
<div class="modal fade" id="modalLP" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Consultar un lote del inventario CNC</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php
                        if($tipo_usuario == "CNC" || $tipo_usuario == "Vendedor"){
                            echo 'inventario_vn.php';
                        }else{
                            echo 'inventario.php';
                        }
                    ?>" method="GET" target="_blank" id="formLP">                        
                    <div class="mb-3">
                        <label for="inputLP" class="lbl-general">Ingrese Lote <span class="text-danger">*</span></label>
                        <input type="text" class="input-text" id="inputLP" name="lp" required>
                    </div>

                    <button type="submit" class="btn-general">Consultar<i class="bi bi-arrow-up-right mx-2"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para consultar claves existentes de parametros -->
<div class="modal fade" id="modalClavesValidas" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" style="max-width: 65% !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Consultar claves validas existentes</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formClavesValidas">                         
                    <div id="containerSelectorProveedor" class="mb-4">
                        <label for="selectorP" class="lbl-general">Proveedor</label>
                        <select id="selectorP" class="selector selectorProveedoresParametros" name="proveedor" required >
                            <option value="" disabled selected>Seleccionar...</option>
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
                <div style="overflow-x:auto;">
                    <table id="tablaClavesValidas" class="table table-bordered mt-3 tabla-billets" style="border:1px solid #495057;">
                        <thead class="table-active">
                            <tr>
                                <th>Clave</th>
                                <th>Clave alterna</th>
                                <th>Proveedor</th>
                                <th>Material</th>
                                <th>Tipo</th>
                                <th>Interior</th>
                                <th>Exterior</th>
                                <th>Disponibilidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="7">Llene el formulario para consultar claves validas.</td></tr>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para preguntar que almacen -->
<div class="modal fade" id="modalQueAlmacen" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Consultar todo el inventario CNC</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="<?php
                        if($tipoUsuario == "CNC" || $tipoUsuario == "Vendedor"){
                            echo 'inventario_vn.php';
                        }else{
                            echo 'inventario.php';
                        }
                    ?>" method="GET" target="_blank" id="formClave">
                    <input type="hidden" name="data" value="all">
                    <div class="mb-3">
                        <label for="inputAlmacenId2" class="form-label fw-bold">Almacén <span class="text-danger">*</span></label>
                        <select id="inputAlmacenId2" class="inputAlmacenIdClass selector" name="origen" required>
                            <option value="" disabled selected>Seleccionar un almacén</option>
                        </select>
                    </div>     

                    <button type="submit" class="btn-general">Consultar<i class="bi bi-arrow-up-right mx-2"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include(ROOT_PATH . 'includes/modales_actions_billet.php'); ?>
<script src="<?= controlCache('../assets/js/modales_actions_billet.js'); ?>"></script>
<?php include(ROOT_PATH . 'includes/modal_almacen.php'); ?>

<script>
    // ============================================================
    // VARIABLES GLOBALES
    // ============================================================
    let almacenesDisponibles = [];

    // ============================================================
    // FUNCIONES
    // ============================================================
    /**
     * Carga los almacenes disponibles desde el backend
     */
    function cargarAlmacenes() {
        $.ajax({
            url: '../ajax/ajax_almacenes.php',
            type: 'GET',
            data: {
                excluir: "0"
            },
            dataType: 'json',
            success: function(data) {
                if (data.success && data.almacenes) {
                    almacenesDisponibles = data.almacenes;
                    llenarSelectorAlmacenes();
                } else {
                    console.error('Error cargando almacenes:', data.message);
                    sweetAlertResponse("warning", "Ocurrió un problema", data.message, "none");
                }
            },
            error: function(xhr, status, error) {
                // Aquí 'data' NO existe. Debes usar los argumentos de esta función.
                console.error('Error de red/servidor:', error);
                
                // Intentamos obtener un mensaje del servidor si es que mandó algo
                let mensajeError = "No se pudo conectar con el servidor.";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    mensajeError = xhr.responseJSON.message;
                }
                sweetAlertResponse("warning", "Ocurrió un problema" , mensajeError, "none");
            }
        });
    }
    /**
     * Llena el selector de almacen con los almacenes disponibles
     */
    function llenarSelectorAlmacenes() {
        const selector = $('.inputAlmacenIdClass');      
        selector.html("");  
        selector.append(`<option value="" disabled selected>Seleccionar almacén</option>`);
        if (almacenesDisponibles.length > 0) {
            almacenesDisponibles.forEach(almacen => {
                selector.append(`<option value="${almacen.id}">${almacen.almacen} - ${almacen.descripcion}</option>`);
            });
        }
    }

    // ============================================================
    // EVENTOS DEL DOM
    // ============================================================  
    $(document).ready(function(){

        cargarAlmacenes();

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
                        let disponibilidad = "";
                        $.each(data, function(index, item) {
                            if(!item.precio || item.precio == 0.00){
                                disponibilidad = "No disponible, le falta el precio.";
                            }else{
                                disponibilidad = "Disponible.";
                            }
                            $("#tablaClavesValidas tbody").append(
                                `
                                <tr>
                                    <td>${item.clave}</td>
                                    <td>${item.clave_alterna}</td>
                                    <td>${item.proveedor}</td>
                                    <td>${item.material}</td>
                                    <td>${item.tipo}</td>
                                    <td>${item.interior}</td>
                                    <td>${item.exterior}</td>
                                    <td>${disponibilidad}</td>
                                </tr>
                                `
                            );
                        });
                    } else {
                        $("#tablaClavesValidas tbody").empty();
                        $(`#tablaClavesValidas tbody`).append(`<tr><td colspan="7">No se encontraron resultados coincidentes.</td></tr>`);

                    }
                },
                error: function() {
                    $("#tablaClavesValidas tbody").empty();
                    $(`#tablaClavesValidas tbody`).append(`<tr><td colspan="7" style="color:#dc3545;">Hubo un problema al consultar</td></tr>`);
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

        // $.ajax({
        //     url: "../ajax/ajax_notificacion.php",
        //     type: "POST",
        //     data: { mensaje: "Se ha cargado el panel de funciones de inventario CNC" },
        //     success: function(response) {
        //         console.log("Notificacion enviada: ", response);
        //     },
        //     error: function(error) {
        //         console.error("Error al enviar la notificacion: ", error);
        //     }
        // });
    });
</script>
</body>
</html>