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
    <!-- <link rel="stylesheet" href="<?= controlCache('../assets/css/styles-table.css'); ?>">    -->
    <link rel="stylesheet" href="<?= controlCache('../assets/css/datatable1.css"'); ?>"> 

    <title>Precios</title>

</head>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_FILES['csv_precios']) && $_FILES['csv_precios']['error'] == 0) {
        $archivo_csv = $_FILES['csv_precios']['tmp_name'];
        $extension = pathinfo($_FILES['csv_precios']['name'], PATHINFO_EXTENSION);
        if (strtolower($extension) != 'csv') {
            echo "<script>Swal.fire({title:'Error', text:'El archivo debe ser un CSV.', icon:'error'});</script>";
            exit();
        }

        $datos = [];
        if (($handle = fopen($archivo_csv, 'r')) !== FALSE) {
            fgetcsv($handle, 1000, ','); // Saltar encabezado
            while (($fila = fgetcsv($handle, 1000, ',')) !== FALSE) {
                if (count($fila) >= 8) {
                    $clave = trim($fila[0]);
                    $datos[$clave] = [
                        'precio' => floatval($fila[1]),
                        'max_usable' => intval($fila[2]),
                        'interior' => trim($fila[3]),
                        'exterior' => trim($fila[4]),
                        'proveedor' => trim($fila[5]),
                        'material' => trim($fila[6]),
                        'tipo' => trim($fila[7])
                    ];
                }
            }
            fclose($handle);
        }

        $conn->beginTransaction();
        try {
            $lotes = array_chunk($datos, 500, true);
            foreach ($lotes as $lote) {
                $claves = array_keys($lote);
                $placeholders = implode(',', array_fill(0, count($claves), '?'));

                // Paso 1: Obtener las claves que ya existen en BD
                $stmt_exist = $conn->prepare("SELECT clave FROM parametros WHERE clave IN ($placeholders)");
                $stmt_exist->execute($claves);
                $claves_existentes = $stmt_exist->fetchAll(PDO::FETCH_COLUMN);
                $claves_existentes = array_map('trim', $claves_existentes);

                // Paso 2: Actualizar registros existentes con CASE
                if (count($claves_existentes) > 0) {
                    $sql = "UPDATE parametros SET 
                            precio = CASE ";
                    $sql2 = "max_usable = CASE ";
                    $sql3 = "interior = CASE ";
                    $sql4 = "exterior = CASE ";
                    $sql5 = "proveedor = CASE ";
                    $sql6 = "material = CASE ";
                    $sql7 = "tipo = CASE ";
                    $where = [];

                    foreach ($claves_existentes as $i => $clave) {
                        $k = ":clave$i";
                        $sql .= "WHEN clave = $k THEN :precio$i ";
                        $sql2 .= "WHEN clave = $k THEN :max$i ";
                        $sql3 .= "WHEN clave = $k THEN :int$i ";
                        $sql4 .= "WHEN clave = $k THEN :ext$i ";
                        $sql5 .= "WHEN clave = $k THEN :prov$i ";
                        $sql6 .= "WHEN clave = $k THEN :mat$i ";
                        $sql7 .= "WHEN clave = $k THEN :tipo$i ";
                        $where[] = $k;
                    }

                    $sql .= "END, ";
                    $sql2 .= "END, ";
                    $sql3 .= "END, ";
                    $sql4 .= "END, ";
                    $sql5 .= "END, ";
                    $sql6 .= "END, ";
                    $sql7 .= "END ";
                    $final_sql = $sql . $sql2 . $sql3 . $sql4 . $sql5 . $sql6 . $sql7;
                    $final_sql .= "WHERE clave IN (" . implode(",", $where) . ")";

                    $stmt_update = $conn->prepare($final_sql);
                    foreach ($claves_existentes as $i => $clave) {
                        $valores = $datos[$clave];
                        $stmt_update->bindValue(":clave$i", $clave);
                        $stmt_update->bindValue(":precio$i", $valores['precio']);
                        $stmt_update->bindValue(":max$i", $valores['max_usable']);
                        $stmt_update->bindValue(":int$i", $valores['interior']);
                        $stmt_update->bindValue(":ext$i", $valores['exterior']);
                        $stmt_update->bindValue(":prov$i", $valores['proveedor']);
                        $stmt_update->bindValue(":mat$i", $valores['material']);
                        $stmt_update->bindValue(":tipo$i", $valores['tipo']);
                    }
                    $stmt_update->execute();
                }

                // Paso 3: Insertar claves nuevas
                $claves_nuevas = array_diff(array_keys($lote), $claves_existentes);
                if (count($claves_nuevas) > 0) {
                    $stmt_insert = $conn->prepare("
                        INSERT INTO parametros 
                        (clave, precio, max_usable, interior, exterior, proveedor, material, tipo)
                        VALUES (:clave, :precio, :max_usable, :interior, :exterior, :proveedor, :material, :tipo)
                    ");
                    foreach ($claves_nuevas as $clave) {
                        $valores = $datos[$clave];
                        $stmt_insert->execute([
                            ':clave' => $clave,
                            ':precio' => $valores['precio'],
                            ':max_usable' => $valores['max_usable'],
                            ':interior' => $valores['interior'],
                            ':exterior' => $valores['exterior'],
                            ':proveedor' => $valores['proveedor'],
                            ':material' => $valores['material'],
                            ':tipo' => $valores['tipo'],
                        ]);
                    }
                }
            }

            $conn->commit();
            echo "<script>
                $(document).ready(function(){
                    Swal.fire({
                        title: 'Proceso exitoso',
                        text: 'Datos cargados correctamente.',
                        icon: 'success'
                    }).then(() => window.location.href = './precios.php');
                });
            </script>";
        } catch (Exception $e) {
            $conn->rollBack();
            echo "<script>
                $(document).ready(function(){
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al procesar el archivo: " . $e->getMessage() . "',
                        icon: 'error'
                    }).then(() => window.location.href = './precios.php');
                });
            </script>";
        }
    }

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
            // Validar que la clave no exista previamente
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM parametros WHERE Clave = :clave");
            $stmtCheck->bindParam(':clave', $clave);
            $stmtCheck->execute();
            $existeClave = $stmtCheck->fetchColumn();

            if ($existeClave > 0) {
                echo "<script type='text/javascript'>
                        $(document).ready(function(){
                            Swal.fire({
                                title: 'Clave duplicada',
                                text: 'La clave ingresada ya existe en la base de datos. Verifique e intente nuevamente.',
                                icon: 'warning',
                                confirmButtonText: 'Entendido',
                                confirmButtonColor: '#ffc107',
                                showCloseButton: true,
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            }).then((result) => {
                                if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
                                    window.location.href = './precios.php';
                                }
                            });
                        });
                    </script>";
                exit;
            }
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
                echo "<script type='text/javascript'>
                        $(document).ready(function(){
                            Swal.fire({
                                title: 'Proceso exitoso',
                                text: 'Registro agregado correctamete.',
                                icon: 'success', // Puede ser 'success', 'error', 'warning', 'info', 'question'
                                confirmButtonText: 'Ok',
                                confirmButtonColor: '#17a2b8',
                                showCloseButton: true,
                                allowOutsideClick: false, 
                                allowEscapeKey: false  
                            }).then((result) => {
                                if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
                                    window.location.href = './precios.php?clave=".$clave."';
                                }
                            });
                        });
                    </script>";
            } else {
                echo "<script type='text/javascript'>
                        $(document).ready(function(){
                            Swal.fire({
                                title: 'Error',
                                text: 'Error al intentar agregar registro. Si el problema persiste contacte al area de sistemas.',
                                icon: 'error',
                                confirmButtonText: 'Ok',
                                confirmButtonColor: '#dc3545',
                                showCloseButton: true,
                                allowOutsideClick: false, 
                                allowEscapeKey: false  
                            }).then((result) => {
                                if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
                                    window.location.href = './precios.php';
                                }
                            });
                        });
                    </script>";
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

            // Validar que no exista otra fila con la misma clave
            $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM parametros WHERE Clave = :clave AND id != :id");
            $stmtCheck->bindParam(':clave', $clave);
            $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtCheck->execute();
            $existeClave = $stmtCheck->fetchColumn();

            if ($existeClave > 0) {
                echo "<script type='text/javascript'>
                        $(document).ready(function(){
                            Swal.fire({
                                title: 'Clave duplicada',
                                text: 'La clave ingresada ya existe en otro registro. No se puede duplicar.',
                                icon: 'warning',
                                confirmButtonText: 'Ok',
                                confirmButtonColor: '#ffc107',
                                showCloseButton: true,
                                allowOutsideClick: false,
                                allowEscapeKey: false
                            });
                        });
                    </script>";
                exit;
            }
    
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
                echo "<script type='text/javascript'>
                        $(document).ready(function(){
                            Swal.fire({
                                title: 'Proceso exitoso',
                                text: 'Registro actualizado correctamete.',
                                icon: 'success', // Puede ser 'success', 'error', 'warning', 'info', 'question'
                                confirmButtonText: 'Ok',
                                confirmButtonColor: '#17a2b8',
                                showCloseButton: true,
                                allowOutsideClick: false, 
                                allowEscapeKey: false  
                            }).then((result) => {
                                if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
                                    window.location.href = './precios.php?clave=".$clave."';
                                }
                            });
                        });
                    </script>";
            } else {
                echo "<script type='text/javascript'>
                        $(document).ready(function(){
                            Swal.fire({
                                title: 'Error',
                                text: 'Error al intentar actualizar registro. Si el problema persiste contacte al area de sistemas.',
                                icon: 'error',
                                confirmButtonText: 'Ok',
                                confirmButtonColor: '#dc3545',
                                showCloseButton: true,
                                allowOutsideClick: false, 
                                allowEscapeKey: false  
                            }).then((result) => {
                                if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
                                    window.location.href = './precios.php';
                                }
                            });
                        });
                    </script>";
            }

        } elseif ($action === 'delete') {
            $id = $_POST['id'];

            $sql = "DELETE FROM parametros WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                echo "<script type='text/javascript'>
                        $(document).ready(function(){
                            Swal.fire({
                                title: 'Proceso exitoso',
                                text: 'Registro eliminado correctamete.',
                                icon: 'success', // Puede ser 'success', 'error', 'warning', 'info', 'question'
                                confirmButtonText: 'Ok',
                                confirmButtonColor: '#17a2b8',
                                showCloseButton: true,
                                allowOutsideClick: false, 
                                allowEscapeKey: false  
                            }).then((result) => {
                                if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
                                    window.location.href = './precios.php';
                                }
                            });
                        });
                    </script>";
            } else {
                echo "<script type='text/javascript'>
                        $(document).ready(function(){
                            Swal.fire({
                                title: 'Error',
                                text: 'Error al intentar eliminar registro. Contacte al area de sistemas.',
                                icon: 'error',
                                confirmButtonText: 'Ok',
                                confirmButtonColor: '#dc3545',
                                showCloseButton: true,
                                allowOutsideClick: false, 
                                allowEscapeKey: false  
                            }).then((result) => {
                                if (result.isConfirmed || result.dismiss === Swal.DismissReason.close || result.dismiss === Swal.DismissReason.overlay) {
                                    window.location.href = './precios.php';
                                }
                            });
                        });   
                    </script>";
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
}

?>
<body class="scroll-disablado">
    
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>

<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Funciones para cambio de precios</h1>
            <div class="d-flex flex-row justify-content-start">
                <button type="button" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalConsultar" style="margin-right:5%;">Buscar por material</button>
                <button type="button" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalClave" style="margin-right:5%;">Buscar por clave</button>
                <button type="button" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalCsv" style="margin-right:5%;">Subir .csv de precios</button>
                <button type="button" id="btnAgregar" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalAgregarEditar">Agregar Registro</button>
            </div>
        </div>
        <div class="mt-4 table-container <?php if(empty($arregloSelectPrecios)){echo 'd-none';}?>">
            <table id="parametrosTable" class="table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Acciones</th>
                        <th>Proveedor</th>
                        <th>Tipo</th>
                        <th>Material</th>
                        <th>Interior</th>
                        <th>Exterior</th>
                        <th>Max. Length</th>
                        <th>Clave</th>
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
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-eliminar delete-btn">Eliminar</button>
                                </form>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['proveedor']); ?></td>
                        <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($row['material']); ?></td>
                        <td><?php echo htmlspecialchars($row['interior']); ?></td>
                        <td><?php echo htmlspecialchars($row['exterior']); ?></td>
                        <td><?php echo htmlspecialchars($row['max_usable']); ?></td>
                        <td><?php echo htmlspecialchars($row['clave']); ?></td>
                        <td><?php echo htmlspecialchars($row['precio']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal subir csv -->
<div class="modal fade" id="modalCsv" tabindex="-1" aria-hidden="false" aria-labelledby="label-modal-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Suba el documento en formato .csv</h5>
                <button type="button" class="btn-close btnCerrar" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" enctype="multipart/form-data"> 
                    <div class="modal-body text-center">
                        <p>Ejemplo del formato correcto</p>
                        <img src="../assets/img/general/formato_csv.jpg" class="img-fluid" alt="Wiper Especial">
                    </div>                       
                    <div class="d-flex flex-column mb-3">
                        <label for="inputArchivoCSV" class="lbl-general">Seleccionar</label>
                        <input type="file" id="inputArchivoCSV" class="input-file" name="csv_precios" accept=".csv" required>
                    </div>       

                    <button type="submit" class="btn-general">Subir</button>
                </form>
            </div>
        </div>
    </div>
</div>

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
                            <label for="inputClave" class="lbl-general">Clave*</label>
                            <input id="inputClave" type="text" class="input-text"  name="clave" placeholder="" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputMaterial" class="lbl-general">Material*</label>
                            <select id="inputMaterial" class="selector" name="material" required >
                                <option value="" disabled selected>Seleccionar</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputProveedor" class="lbl-general">Proveedor*</label>
                            <select id="inputProveedor" class="selector" name="proveedor" required >
                                <option value="" disabled selected>Seleccionar</option>
                            </select>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputTipoMaterial" class="lbl-general">Tipo*</label>
                            <select id="inputTipoMaterial" class="selector" name="tipo" required >
                                <option value="" disabled selected>Seleccionar</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputInterior" class="lbl-general">Medida interior*</label>
                            <input id="inputInterior" type="number" class="input-text"  name="interior" placeholder="" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputExterior" class="lbl-general">Medida exterior*</label>
                            <input id="inputExterior" type="number" class="input-text"  name="exterior" placeholder="" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <div class="" style="width:48%;">
                            <label for="inputMaxUsable" class="lbl-general">Max. Length*</label>
                            <input id="inputMaxUsable" type="number" class="input-text"  name="max_usable" placeholder="" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputPrecio" class="lbl-general">Precio*</label>
                            <input id="inputPrecio" type="number" class="input-text"  min="0" step="0.01" name="precio" required>
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
                <form action="" method="GET" target="">                         
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
                <form action="" method="GET">                        
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

