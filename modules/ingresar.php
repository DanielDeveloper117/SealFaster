<?php
// Incluir el archivo de conexión a la base de datos
require 'db/config.php';

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: cerrar_sesion.php");
    exit;
} else {
   // echo 'Usuario autenticado con ID: '.$_SESSION['id'];
}

    $id_usuario=$_SESSION['id'];
    $sql = "SELECT lider FROM login WHERE id = :id_usuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);



//------------------CONSULTA INSERT INTO
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["query_tipo"])) {
    $tipoQuery=$_POST["query_tipo"];

    if($tipoQuery=="insertar"){
        $tipo = $_POST['tipo'];
        $modelo = $_POST['modelo'];
        $precio_base = $_POST['precio_base'];
    
        $sql = "INSERT INTO modelos (tipo, modelo, precio_base) VALUES (:tipo, :modelo, :precio_base)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':precio_base', $precio_base);
        if ($stmt->execute()) {
            echo '<script>alert("Registro agregado exitosamente.");</script>';
        } else {
            echo '<script>alert("Error al agregar registro.");</script>';
    
        }

    }else if($tipoQuery=="actualizar"){
        $idModelo = $_POST["id_modelo"]; // con ese id es para seleccionar cual registro
        $tipo = $_POST['tipo'];
        $modelo = $_POST['modelo'];
        $precio_base = $_POST['precio_base'];

        $sql = "UPDATE modelos SET tipo = :tipo, modelo = :modelo, precio_base = :precio_base WHERE id_modelo = :id_modelo";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':modelo', $modelo);
        $stmt->bindParam(':precio_base', $precio_base);
        $stmt->bindParam(':id_modelo', $idModelo);

        if ($stmt->execute()) {
            echo '<script>alert("Registro actualizado exitosamente.");</script>';
        } else {
            echo '<script>alert("Error al actualizar el registro.");</script>';
        }
    }
}

//--------------------CONSULTA DELETE FROM
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["queryTemporal"])) {
        $id_modelo = $_POST['id_modelo'];

        $sqlDelete = "DELETE FROM modelos WHERE id_modelo = :id_modelo";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bindParam(':id_modelo',$id_modelo );
        $stmtDelete->execute();

        if ($stmtDelete->execute()) {
            echo '<script>alert("Registro eliminado exitosamente.");</script>';
        } else {
            echo '<script>alert("Error al eliminar el registro.");</script>';

        }

}


// Obtener los datos de la tabla 'modelos'
$sqlModelos = "SELECT id_modelo, tipo, modelo, precio_base FROM modelos";
$stmtModelos = $conn->prepare($sqlModelos);
$stmtModelos->execute();
$arregloSelectModelos = $stmtModelos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= controlCache('../assets/dependencies/font-awesome.min.css'); ?>">
    <link rel="stylessheet" href="<?= controlCache('../assets/dependencies/css2.css'); ?>">
    <link href="<?= controlCache('../assets/dependencies/bootstrap.min.css'); ?>" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="<?= controlCache('../assets/dependencies/jquery.min.js'); ?>"></script>
    <script src="<?= controlCache('../assets/dependencies/bootstrap.bundle.min.js'); ?>" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="<?= controlCache('../assets/dependencies/datatables.min.css'); ?>" rel="stylesheet">
    <script src="<?= controlCache('../assets/dependencies/datatables.min.js'); ?>"></script>

    <link rel="stylesheet" href="css/styles-formulario.css">

    <title>Perfiles sellos</title>
    <script>
        $(document).ready(function(){
          var table=  $('#modelosTable').DataTable({
                "scrollY": "280px", // Altura del área de desplazamiento vertical
                "scrollX": true,
                "lengthChange": true, 
                "pageLength": 100,
                "paging": true,
                ordering: true, //botones de ordenacion de las columnas
            "orderable": true,
            "order": [[0, "desc"]],
            "searching": true, // función de búsqueda activada
            search: {
               return: false
            },
            "language": { 
              "decimal" : "",
              "emptyTable":"No hay registros",
              //"info": "Mostrando _END_ de _TOTAL_ registros",
              "info": " _TOTAL_ resultados",
              "infoEmpty": "No se encontraron resultados.",
              //"infoEmpty": "Mostrando 0 de 0 registros",
              "infoFiltered": " ",
              //"infoFiltered": "(Se filtraron _MAX_ registros)",
              "infoPostFix":"",
              "thousands": ", ",
              "lengthMenu": "Mostrar _MENU_ registros",
              "loadingRecords":"Cargando...",
              "processing": "Procesando...",
              "search": "Buscar: ",
              "zeroRecords":"No se encontraron resultados.",
              "paginate":{
                "first":"<<",
                "last":">>",
                "next": "Siguiente",
                "previous": "Anterior"
              }
            },
            "lengthMenu": [ [10, 20, 30, 40, 50, 100, 1000], [10, 20, 30, 40, 50, 100, 1000] ],
            "scrollCollapse": true, // Colapso del scroll cuando no es necesario
            "autoWidth": false

            });
        });
    </script>
</head>
<body>
<?php
    if ($resultado !== false && isset($resultado['lider'])) {
        $tipoLider = $resultado['lider'];
        if ($resultado['lider'] == 1) {
            //header de director
            include('header-lider.php');

        }else if($resultado['lider'] == 2){
            //header de cnc
            include('header_cnc.php');

        }else if ($resultado['lider'] == 3) {
            //header de ventas
            include('header_vn.php');

        }else if($resultado['lider'] == 0){
            //header de general/lider/sistemas
            include('header-general.php');

        }else{
            echo 'No hay header para este usuario.';
        }
    } else {
        echo 'No se encontró el id.';
    }
?>
<div class="titulo mt-3">
    <h1>Parametros sellos</h1>
</div>

<div class="titulo mt-3">
    <button type="button" class="btn-general mb-3 mt-2" style="width:30%;" data-bs-toggle="modal" data-bs-target="#openModalModelos"  id="btnModalModelos" >Administrar</button>
</div>

<div class=" mt-3">
    <h4 style="width:90%;margin-left: 4%;" id="agregarEditar">Agregar nuevo registro</h1>
</div>

<section class="d-flex flex-row col-12 justify-content-center align-items-center">
    <div class="d-flex col-11 flex-row form-container">
        <form id="dynamic-form" class="w-100 d-flex justify-content-center flex-column form-general" style="overflow-x:auto; " method="post" action="" >

            <table class="table table-bordered border border-2" style="width: 100%;">
                <thead>
                    <tr>
                        <th scope="col">Tipo</th>
                        <th scope="col">Modelo</th>
                        <th scope="col">Precio Base</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    <!-- Primer conjunto de campos -->
                    <tr id="row-inputs">
                        <td><input id="tipo" class="input-texto" type="text" name="tipo" required></td>
                        <td><input id="modelo" class="input-texto" type="text" name="modelo" required></td>
                        <td><input id="precio_base" class="input-texto" type="number" step="0.01" name="precio_base" required></td>
                    </tr>
                </tbody>
            </table>
            <input id="idModelo" type="hidden" name="id_modelo" value="">
            <input id="queryTipo" type="hidden" name="query_tipo" value="insertar">
            
            <div class="d-flex col-12 justify-content-end mt-4">
                <div class="col-7 d-flex justify-content-end" style="margin-bottom:40px; padding-right: 4%;">
                    <button type="button" id="btnCancelar" class="btn-eliminar col-3" data-target="guardar" style="width:20%; display:none;" >Cancelar</button>
                    <div style="width:1%;"></div>
                    <button type="submit" id="btnGuardarDatos" class="btn-general col-3" data-target="guardar" style="margin-left:20px; width:30%;">Guardar</button>
                </div>
            </div>

        </form>
    </div>
</section>




<!------------------------ MODAL PERFILES DE SELLOS ----------------------------------------------------------->
<div class="modal fade" id="openModalModelos" tabindex="-1" aria-hidden="true" aria-labelledby="label-modal-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-primary">Perfiles de sellos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar" id="btnCloseModalModelo" title="Cancelar seleccion" ></button>
            </div>
            <!-- contenedor del body -->
            <div class="modal-body">
                <div class="d-flex flex-column justify-content-center align-items-center">
                    <div class="col-11 align-items-center flex-column">
                        <div id="tableContainer" class="col-12 mb-4" style="display:block;">
                            <div class="border border-black" style="overflow-x:auto;">
                                <table id="modelosTable" class="table-striped table-bordered" style="width:100%;">
                                    <thead>
                                    <tr>
                                        <th scope="col">Tipo</th>
                                        <th scope="col">Modelo</th>
                                        <th scope="col">Precio Base</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                        foreach ($arregloSelectModelos as $modelo) {
                                            // Usa $idRenglon para asignar un ID único a cada fila
                                            echo "<tr data-idRenglon='" . $modelo['id_modelo'] . "' style='cursor:pointer;'>";

                                            echo "<td  id='idCampoTipo'>" . htmlspecialchars($modelo['tipo']) . "</td>";
                                            echo "<td  id='idCampoModelo'>" . htmlspecialchars($modelo['modelo']) . "</td>";
                                            echo "<td  id='idCampoPrecioBase'>" . htmlspecialchars($modelo['precio_base']) . "</td>";

                                            echo "</tr>";

                                        }
                                        ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- contenedor del footer -->
            <div class="modal-footer justify-content-center" >
                <!-- <button type="submit" class="btn-general" data-bs-dismiss="modal" style="width: 20%;" id="btnEnviar">Enviar y generar PDF</button> -->
                <div class="d-flex col-11 justify-content-end">
                    <button type="button" id="btnDeleteRow" class="btn-disabled" style="width: 20%;" data-eliminar="" data-reference="" disabled>Eliminar</button>
                    <div style="width:4%;"></div>
                    <button type="button" id="btnEditar" class="btn-disabled"  style="width: 20%;" data-editar="" data-reference="" disabled>Editar</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ///////////////////////////////////FORMULARIO TEMPORAL -->
 <form id="formTemporal" action="" method="POST">
    <input id="inputTemporalQuery" hidden name="queryTemporal" value="">
    <input id="inputTemporal" hidden name="id_modelo" value="">
 </form>
<!-- ------------------------------------------------------------------------------------------------------- -->
<script>
    $(document).ready(function() {
        window.ordenada=false;
        console.log(ordenada);
        $(".dt-search").css('margin-right','10px');
        //-----------------------------------EVENTO CLICK EN VER TABLA
        //-------------------ORDENAR LOS HEADERS DE LA TABLA CON CLICK
        $("#btnModalModelos").on('click', function() {
            console.log("click al modal");
            if(ordenada==false){
                setTimeout(() => {
                    $("[data-dt-column='1']").click();
                    console.log("click a serie");
                }, 800);
                setTimeout(() => {
                    $("[data-dt-column='1']").click();
                    console.log("click a serie otra vez");
                }, 1200);
                ordenada = true;
            }
            console.log(ordenada);
        });
    
        // --------------------------------EVENTO SELECCIONAR UN RENGLON EN LA TABLA
        $("#modelosTable tbody").on("click", 'tr', function() {
            // Quitar la clase de selección de todas las filas
            $("#modelosTable tbody tr").removeClass("selected-row");
            // Agregar la clase de selección a la fila clickeada
            $(this).addClass("selected-row");
            let rowId = $(this).attr('data-idRenglon');
    
            // Obtener el contenido de la fila seleccionada
            window.rowObjeto = {
                rowId: rowId,
                tipo: "",
                modelo: "",
                precioBase: "",
            };
            $(this).find("#idCampoTipo").each(function(index) {
                rowObjeto.tipo = $(this).text(); 
            });
            $(this).find("#idCampoModelo").each(function(index) {
                rowObjeto.modelo = $(this).text(); 
            });
            $(this).find("#idCampoPrecioBase").each(function(index) {
                rowObjeto.precioBase = $(this).text(); 
            });
            // Mostrar los datos en la consola  
            console.log(rowObjeto);

            console.log(rowObjeto.rowId);
    
            $("#btnDeleteRow").attr("disabled", false);
            $("#btnDeleteRow").removeClass("btn-disabled");
            $("#btnDeleteRow").addClass("btn-eliminar");
            $("#btnDeleteRow").attr("data-eliminar", rowObjeto.rowId);

            $("#btnEditar").attr("disabled", false);
            $("#btnEditar").removeClass("btn-disabled");
            $("#btnEditar").addClass("btn-general");
            $("#btnEditar").attr("data-editar", rowObjeto.rowId);
        });

        // ---------------------------EVENTO CLICK ELIMINAR REGISTRO
        $("#btnDeleteRow").on("click", function() {
            rowObjeto.rowId = $(this).attr("data-eliminar");

            if (!rowObjeto.rowId) {
                alert("No hay fila seleccionada para eliminar.");
                return;
            }else{
                $("#modelosTable tbody tr.selected-row").attr("name", rowObjeto.modelo);

                $("#modelosTable tbody tr.selected-row").remove();
                // ------------cambiar atributos al formulario temporal para eliminar registro
                $("#inputTemporalQuery").val("delete");

                $("#inputTemporal").val(rowObjeto.rowId);
                setTimeout(() => {
                    $("#formTemporal").submit()
                }, 300);
            }

        });

        //-----------------------------EVENTO DE EDITAR REGISTRO
        $("#btnEditar").on('click', function(){
            console.log(rowObjeto);
            $("#idModelo").val(rowObjeto.rowId);
            $("#tipo").val(rowObjeto.tipo);
            $("#modelo").val(rowObjeto.modelo);
            $("#precio_base").val(rowObjeto.precioBase);
            $("#queryTipo").val("actualizar");
            console.log($("#idModelo").val());

            console.log($("#queryTipo").val());
            setTimeout(() => {
                $("#btnCloseModalModelo").click();
            }, 300);

            $("#agregarEditar").text("Editar registro seleccionado");
            $("#btnGuardarDatos").text("Actualizar");
            $("#btnGuardarDatos").attr('data-target', 'actualizar');

            $("#btnCancelar").css('display', 'block');

        });

        //---------------------CLICK A CANCELAR EDICION DEL RENGLON
        $("#btnCancelar").on("click", function(){
            window.location.href = "ingresar.php";
            //location.reload(true);
        });

    });
</script>

</body>
</html>