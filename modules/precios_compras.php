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
    <script src="<?= controlCache('../assets/js/datatable_init.js'); ?>"></script>
    <script src="<?= controlCache('../assets/js/alerts_sweet_alert.js'); ?>"></script>
    <link rel="stylesheet" href="<?php echo controlCache('../assets/css/styles-table.css'); ?>">

    <title>Claves</title>

</head>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['action']) && isset($_POST['id'])) {
        $action = $_POST['action'];
        $id = $_POST['id'];
    
        if ($action === 'insert') {
            $clave = $_POST['clave'];
            $material = $_POST['material'];
            $proveedor = $_POST['proveedor'];
            $tipo = $_POST['tipo'];
            $interior = $_POST['interior'];
            $exterior = $_POST['exterior'];
            $max_usable = $_POST['max_usable'];
            $precio = $_POST['precio'];
            $dolar = $_POST['dolar'];
            $precioPorcent = $precio * 0.23;
            $precio = ($precio + $precioPorcent) * $dolar; 
            // +0.26%) (DOLAR)
            // Consulta SQL para insertar en la tabla 'parametros'
            $sql = "INSERT INTO parametros (Clave, material, proveedor, tipo, interior, exterior, max_usable, precio) 
                    VALUES (:clave, :material, :proveedor, :tipo, :interior, :exterior, :max_usable, :precio)";
    
            // Preparar y ejecutar la consulta
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':clave', $clave);
            $stmt->bindParam(':material', $material);
            $stmt->bindParam(':proveedor', $proveedor);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':interior', $interior);
            $stmt->bindParam(':exterior', $exterior);
            $stmt->bindParam(':max_usable', $max_usable);
            $stmt->bindParam(':precio', $precio);

            if ($stmt->execute()) {
                if (is_numeric($max_usable) && is_numeric($precio) && $max_usable > 0 && $precio > 0) {
                    $updateInventario = $conn->prepare("UPDATE inventario_cnc SET estatus = 'Habilitado' WHERE clave = :clave");
                } else {
                    $updateInventario = $conn->prepare("UPDATE inventario_cnc SET estatus = 'Deshabilitado' WHERE clave = :clave");
                }
                $updateInventario->bindParam(':clave', $clave);
                $updateInventario->execute();
                echo '<script>$(document).ready(function(){
                sweetAlertResponse("success", "Proceso exitoso", "Registro agregado correctamete.", "self");
                });</script>';
            } else {
                echo '<script>$(document).ready(function(){
                sweetAlertResponse("error", "Error", "Error al intentar agregar registro.", "self");
                });</script>';
            }

        } elseif ($action === 'update') {
            $clave = $_POST['clave'];
            $material = $_POST['material'];
            $proveedor = $_POST['proveedor'];
            $tipo = $_POST['tipo'];
            $interior = $_POST['interior'];
            $exterior = $_POST['exterior'];
            $max_usable = $_POST['max_usable'];
            $precio = $_POST['precio'];
            $dolar = $_POST['dolar'];
            $precioPorcent = $precio * 0.23;
            $precio = ($precio + $precioPorcent) * $dolar; 
    
            $sql = "UPDATE parametros
            SET Clave = :clave,
                material = :material,
                proveedor = :proveedor,
                tipo = :tipo,
                interior = :interior,
                exterior = :exterior,
                max_usable = :max_usable,
                precio = :precio
            WHERE id = :id";
    
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':clave', $clave);
            $stmt->bindParam(':material', $material);
            $stmt->bindParam(':proveedor', $proveedor);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':interior', $interior);
            $stmt->bindParam(':exterior', $exterior);
            $stmt->bindParam(':max_usable', $max_usable);
            $stmt->bindParam(':precio', $precio);

            if ($stmt->execute()) {
                if (is_numeric($max_usable) && is_numeric($precio) && $max_usable > 0 && $precio > 0) {
                    $updateInventario = $conn->prepare("UPDATE inventario_cnc SET estatus = 'Habilitado' WHERE clave = :clave");
                } else {
                    $updateInventario = $conn->prepare("UPDATE inventario_cnc SET estatus = 'Deshabilitado' WHERE clave = :clave");
                }
                $updateInventario->bindParam(':clave', $clave);
                $updateInventario->execute();
                echo '<script>$(document).ready(function(){
                sweetAlertResponse("success", "Proceso exitoso", "Registro actualizado correctamete.", "self");
                });</script>';
            } else {
                echo '<script>$(document).ready(function(){
                sweetAlertResponse("error", "Error", "Error al intentar actualizar el registro.", "self");
                });</script>';
            }

        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            $clave = $_POST['clave'];

            $sql = "DELETE FROM parametros WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                $updateInventario = $conn->prepare("UPDATE inventario_cnc SET estatus = 'Deshabilitado' WHERE clave = :clave");
                $updateInventario->bindParam(':clave', $clave);
                $updateInventario->execute();
                echo '<script>$(document).ready(function(){
                sweetAlertResponse("success", "Proceso exitoso", "Registro eliminado correctamete.", "self");
                });</script>';
            } else {
                echo '<script>$(document).ready(function(){
                sweetAlertResponse("error", "Error", "Error al intentar eliminar registro.", "self");
                });</script>';
            }
        }
    } else {
    }
    
}


// Verifica si se recibió el parametro 'material' mediante GET
if (isset($_GET['material']) && !empty($_GET['material'])) {
    $material = $_GET['material'];

    $sqlPrecios = "SELECT * FROM parametros WHERE material = :material";
    $stmtPrecios = $conn->prepare($sqlPrecios);
    $stmtPrecios->bindParam(':material', $material, PDO::PARAM_STR);
    $stmtPrecios->execute();
    $arregloSelectPrecios = $stmtPrecios->fetchAll(PDO::FETCH_ASSOC);

}else if (isset($_GET['clave']) && !empty($_GET['clave'])) {
    $clave = $_GET['clave'];

    $sqlPrecios = "SELECT * FROM parametros WHERE clave = :clave";
    $stmtPrecios = $conn->prepare($sqlPrecios);
    $stmtPrecios->bindParam(':clave', $clave, PDO::PARAM_STR);
    $stmtPrecios->execute();
    $arregloSelectPrecios = $stmtPrecios->fetchAll(PDO::FETCH_ASSOC);

}else{
    $arregloSelectPrecios = [];
    $sqlPrecios = "SELECT * FROM parametros WHERE precio <= 0.00 OR max_usable <= 0";
    $stmtPrecios = $conn->prepare($sqlPrecios);
    $stmtPrecios->execute();
    $arregloSelectPrecios = $stmtPrecios->fetchAll(PDO::FETCH_ASSOC);
}

?>
<body class="scroll-disablado">
    
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Funciones para claves</h1>
            <div class="d-flex flex-row justify-content-start">
                <button type="button" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalConsultar" style="margin-right:5%;">Buscar por material</button>
                <button type="button" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalClave" style="margin-right:5%;">Buscar por clave</button>
                <button type="button" id="btnAgregar" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalAgregarEditar">Agregar Registro</button>
            </div>
        </div>
        <h5>Agregar el precio y maximo usable de las siguientes claves</h5>
        <div class="mt-4 table-container">
            <table id="parametrosTable" class="table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Acciones</th>
                        <th>Clave</th>
                        <th>Proveedor</th>
                        <th>Tipo</th>
                        <th>Material</th>
                        <th>Interior</th>
                        <th>Exterior</th>
                        <th>Max. Length</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arregloSelectPrecios as $row):?>
                    <tr>
                        <td class="d-fex flex-column">
                            <div class="d-flex flex-column">
                                <button class="btn-general edit-btn mb-1" 
                                    data-id="<?php echo $row['id']; ?>"
                                    data-clave="<?php echo $row['clave']; ?>"
                                    data-proveedor="<?php echo $row['proveedor']; ?>"
                                    data-tipo="<?php echo $row['tipo']; ?>"
                                    data-material="<?php echo $row['material']; ?>"
                                    data-interior="<?php echo $row['interior']; ?>"
                                    data-exterior="<?php echo $row['exterior']; ?>"
                                    data-max_usable="<?php echo $row['max_usable']; ?>"
                                    data-precio="<?php echo $row['precio']; ?>"
                                    >Editar</button>
                                <form class="form-delete" action="" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="clave" value="<?php echo $row['clave']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-eliminar delete-btn">Eliminar</button>
                                </form>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['clave']); ?></td>
                        <td><?php echo htmlspecialchars($row['proveedor']); ?></td>
                        <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($row['material']); ?></td>
                        <td><?php echo htmlspecialchars($row['interior']); ?></td>
                        <td><?php echo htmlspecialchars($row['exterior']); ?></td>
                        <td><?php echo htmlspecialchars($row['max_usable']); ?></td>
                        <td><?php echo htmlspecialchars($row['precio']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

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
                            <label for="inputClave" class="lbl-general">Clave</label>
                            <input id="inputClave" type="text" class="input-text"  name="clave" placeholder="" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputMaterial" class="lbl-general">Material</label>
                            <select id="inputMaterial" class="selector" name="material" required >
                                <option value="" disabled selected>Seleccionar</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputProveedor" class="lbl-general">Proveedor</label>
                            <select id="inputProveedor" class="selector" name="proveedor" required >
                                <option value="" disabled selected>Seleccionar</option>
                            </select>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputTipoMaterial" class="lbl-general">Tipo</label>
                            <select id="inputTipoMaterial" class="selector" name="tipo" required >
                                <option value="" disabled selected>Seleccionar</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputInterior" class="lbl-general">Medida interior</label>
                            <input id="inputInterior" type="number" class="input-text"  name="interior" placeholder="" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputExterior" class="lbl-general">Medida exterior</label>
                            <input id="inputExterior" type="number" class="input-text"  name="exterior" placeholder="" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputMaxUsable" class="lbl-general">Max. Length</label>
                            <input id="inputMaxUsable" type="number" class="input-text"  name="max_usable" placeholder="" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputPrecio" class="lbl-general">Precio Billet</label>
                            <input id="inputPrecio" type="number" class="input-text"  min="0" step="0.01" name="precio" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputParidad" class="lbl-general">Paridad (Dolar)</label>
                            <input id="inputParidad" type="number" class="input-text"  min="0" step="0.01" name="dolar" required>
                        </div>
                    </div>

                    <button id="btnGuardar" type="submit" class="btn-general">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear query material -->
<div class="modal fade show" id="modalConsultar" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Consultar precios por material</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="precios_compras.php" method="GET" target="_blank">                         
                    <div id="containerSelectorMaterial" class="mb-4">
                        <label for="selectorMaterial" class="lbl-general">Material</label>
                        <select id="selectorMaterial" class="selector" name="material" required >
                            <option value="" disabled selected>Seleccionar</option>
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
                <h5 class="modal-title" id="modalLabel">Consultar clave en precios</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="precios_compras.php" method="GET" target="_blank">                        
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

<script>
    $(document).ready(function(){
        // CLICK A EDITAR UN REGISTRO
        $('#parametrosTable').on('click', '.edit-btn', function() {
            var dataId = $(this).data('id');
            $dataProveedor=$(this).attr('data-proveedor');
            $dataTipo=$(this).attr('data-tipo');
            $dataMaterial=$(this).attr('data-material');
            $dataInterior=$(this).attr('data-interior');
            $dataExterior=$(this).attr('data-exterior');
            $dataMaxUsable=$(this).attr('data-max_usable');
            $dataClave=$(this).attr('data-clave');
            $dataPrecio=$(this).attr('data-precio');

            $('#inputId').val(dataId);
            $('#inputProveedor').val($dataProveedor);
            $('#inputTipoMaterial').val($dataTipo);
            $('#inputMaterial').val($dataMaterial);
            $('#inputInterior').val($dataInterior);
            $('#inputExterior').val($dataExterior);
            $('#inputMaxUsable').val($dataMaxUsable);
            $('#inputClave').val($dataClave);
            $('#inputPrecio').val($dataPrecio);

            $('#inputAction').val('update');
            $('#modalAgregarEditar').modal('show');
            $("#titleModalAddEdit").text("Editar registro");
        });

        // CAMBIAR A add AL CLICK AGREGAR REGISTRO
        $("#btnAgregar").on("click", function(){
            $('#modalAgregarEditar').modal('show');
            $('#inputAction').val('insert');
            $("#titleModalAddEdit").text("Agregar registro");
        });

        // RESETEAR EL FORMULARIO AL CERRAR
        $("#btnCloseModal").on("click", function(){
            $("#formPost")[0].reset();
        });

        const modalConsultar = new bootstrap.Modal(document.getElementById("modalConsultar"));
        
        // CONSULTA AJAX PARA MATERIALES DESDE PARAMETROS
        $.ajax({
            url: '../ajax/ajax_materiales_parametros.php', 
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Verifica que la respuesta tenga datos
                if (data.length > 0) {
                    $.each(data, function(index, item) {
                        $("#selectorMaterial, #inputMaterial").append(
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

        // CONSULTA AJAX PARA PROVEEDORES DESDE PARAMETROS
        $.ajax({
            url: '../ajax/ajax_proveedores_parametros.php', 
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Verifica que la respuesta tenga datos
                if (data.length > 0) {
                    $.each(data, function(index, item) {
                        $("#inputProveedor").append(
                            `
                            <option value="${item.proveedor}">${item.proveedor}</option>
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

        // CONSULTA AJAX PARA PROVEEDORES DESDE PARAMETROS
        $.ajax({
            url: '../ajax/ajax_tiposmateriales_parametros.php', 
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                // Verifica que la respuesta tenga datos
                if (data.length > 0) {
                    $.each(data, function(index, item) {
                        $("#inputTipoMaterial").append(
                            `
                            <option value="${item.tipo}">${item.tipo}</option>
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
    });
</script>
</body>
</html>

