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

    <title>Claves pendientes</title>

</head>
<?php
// Función para mapear material de parametros a inventario_cnc
function mapearMaterial($materialParametros) {
    $mapeoMateriales = [
        "PU ROJO" => "H-ECOPUR",
        "SILICON" => "ECOSIL", 
        "NITRILO" => "ECORUBBER 1",
        "VITON" => "ECORUBBER 2",
        "EPDM" => "ECORUBBER 3",
        "PU VERDE" => "ECOPUR",
        "ECOTAL" => "ECOTAL",
        "ECOMID" => "ECOMID",
        "VIRGEN" => "ECOFLON 1",
        "NIKEL" => "ECOFLON 2",
        "MOLLY" => "ECOFLON 2",
        "BRONCE" => "ECOFLON 3"
    ];
    
    foreach ($mapeoMateriales as $patron => $materialMapeado) {
        if (stripos($materialParametros, $patron) !== false) {
            return $materialMapeado;
        }
    }
    
    return $materialParametros; // Si no encuentra coincidencia, mantener original
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (isset($_FILES['csv_alternas']) && $_FILES['csv_alternas']['error'] == 0) {
        // Aumentar límites de ejecución para archivos grandes
        set_time_limit(300); // 5 minutos
        ini_set('memory_limit', '512M');
        
        $archivo_csv = $_FILES['csv_alternas']['tmp_name'];
        $extension = pathinfo($_FILES['csv_alternas']['name'], PATHINFO_EXTENSION);
        if (strtolower($extension) != 'csv') {
            echo "<script>Swal.fire({title:'Error', text:'El archivo debe ser un CSV.', icon:'error'});</script>";
            exit();
        }

        // Estadísticas
        $estadisticas = [
            'total_registros' => 0,
            'insertados' => 0,
            'actualizados' => 0,
            'excluidos' => 0,
            'inventario_actualizado' => 0,
            'errores' => 0
        ];

        $datos = [];
        if (($handle = fopen($archivo_csv, 'r')) !== FALSE) {
            // Leer encabezado para validar columnas
            $encabezados = fgetcsv($handle, 1000, ',');
            if (!$encabezados || !in_array('clave_alterna', $encabezados) || !in_array('clave_srs', $encabezados)) {
                echo "<script>Swal.fire({title:'Error', text:'El CSV debe contener las columnas clave_alterna y clave_srs.', icon:'error'});</script>";
                fclose($handle);
                exit();
            }

            // Obtener índices de las columnas
            $idx_clave_alterna = array_search('clave_alterna', $encabezados);
            $idx_clave_srs = array_search('clave_srs', $encabezados);
            
            while (($fila = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $estadisticas['total_registros']++;
                
                if (count($fila) >= max($idx_clave_alterna, $idx_clave_srs) + 1) {
                    $clave_alterna = preg_replace('/\s+/', '', trim($fila[$idx_clave_alterna]));
                    $clave_srs = preg_replace('/\s+/', '', trim($fila[$idx_clave_srs]));
                    
                    if (!empty($clave_alterna) && !empty($clave_srs)) {
                        $datos[$clave_alterna] = $clave_srs; // Usar clave_alterna como key para evitar duplicados
                    } else {
                        $estadisticas['excluidos']++;
                    }
                } else {
                    $estadisticas['excluidos']++;
                }
            }
            fclose($handle);
        }

        $conn->beginTransaction();
        try {
            // PROCESAMIENTO MASIVO OPTIMIZADO
            
            // Paso 1: Obtener todas las claves SRS válidas de parametros en una sola consulta
            $claves_srs_csv = array_unique(array_values($datos));
            $placeholders_srs = str_repeat('?,', count($claves_srs_csv) - 1) . '?';
            
            $sqlClavesValidas = "SELECT clave, interior, exterior, proveedor, material, max_usable 
                                FROM parametros 
                                WHERE Clave IN ($placeholders_srs) 
                                AND max_usable != 0 AND precio != 0.00";
            $stmtClavesValidas = $conn->prepare($sqlClavesValidas);
            $stmtClavesValidas->execute($claves_srs_csv);
            $clavesValidasData = $stmtClavesValidas->fetchAll(PDO::FETCH_ASSOC);
            
            // Crear array de claves SRS válidas con sus datos
            $clavesValidas = [];
            foreach ($clavesValidasData as $row) {
                $clavesValidas[$row['clave']] = $row;
            }
            
            // Paso 2: Filtrar datos - solo mantener registros con clave_srs válida
            $datosFiltrados = [];
            foreach ($datos as $clave_alterna => $clave_srs) {
                if (isset($clavesValidas[$clave_srs])) {
                    $datosFiltrados[$clave_alterna] = $clave_srs;
                } else {
                    $estadisticas['excluidos']++;
                }
            }
            
            // Paso 3: Obtener claves_alternas existentes en una sola consulta
            $claves_alternas_csv = array_keys($datosFiltrados);
            $placeholders_alternas = str_repeat('?,', count($claves_alternas_csv) - 1) . '?';
            
            $sqlExistentes = "SELECT id_alterna, clave_alterna, clave_srs FROM claves_alternas WHERE clave_alterna IN ($placeholders_alternas)";
            $stmtExistentes = $conn->prepare($sqlExistentes);
            $stmtExistentes->execute($claves_alternas_csv);
            $existentesData = $stmtExistentes->fetchAll(PDO::FETCH_ASSOC);
            
            // Crear array de existentes
            $existentes = [];
            foreach ($existentesData as $row) {
                $existentes[$row['clave_alterna']] = $row;
            }
            
            // Paso 4: Separar en inserts y updates
            $inserts = [];
            $updates = [];
            
            foreach ($datosFiltrados as $clave_alterna => $clave_srs) {
                if (isset($existentes[$clave_alterna])) {
                    $updates[] = [
                        'id' => $existentes[$clave_alterna]['id_alterna'],
                        'clave_alterna' => $clave_alterna,
                        'clave_srs' => $clave_srs
                    ];
                } else {
                    $inserts[] = [
                        'clave_alterna' => $clave_alterna,
                        'clave_srs' => $clave_srs
                    ];
                }
            }
            
            // Paso 5: Procesar INSERTS masivamente
            if (!empty($inserts)) {
                $sqlInsert = "INSERT INTO claves_alternas (clave_alterna, clave_srs, fecha_registro) VALUES ";
                $values = [];
                $params = [];
                
                foreach ($inserts as $insert) {
                    $values[] = "(?, ?, NOW())";
                    $params[] = $insert['clave_alterna'];
                    $params[] = $insert['clave_srs'];
                }
                
                $sqlInsert .= implode(', ', $values);
                $stmtInsert = $conn->prepare($sqlInsert);
                if ($stmtInsert->execute($params)) {
                    $estadisticas['insertados'] = count($inserts);
                }
            }
            
            // Paso 6: Procesar UPDATES masivamente con CASE
            if (!empty($updates)) {
                $sqlUpdate = "UPDATE claves_alternas SET clave_srs = CASE ";
                $whereIds = [];
                $paramsUpdate = [];
                
                foreach ($updates as $i => $update) {
                    $sqlUpdate .= "WHEN id_alterna = ? THEN ? ";
                    $paramsUpdate[] = $update['id'];
                    $paramsUpdate[] = $update['clave_srs'];
                    $whereIds[] = $update['id'];
                }
                
                $sqlUpdate .= "END WHERE id_alterna IN (" . str_repeat('?,', count($whereIds) - 1) . "?)";
                $paramsUpdate = array_merge($paramsUpdate, $whereIds);
                
                $stmtUpdate = $conn->prepare($sqlUpdate);
                if ($stmtUpdate->execute($paramsUpdate)) {
                    $estadisticas['actualizados'] = count($updates);
                }
            }
            
            // Paso 7: ACTUALIZAR INVENTARIO_CNC masivamente
            if (!empty($datosFiltrados)) {
                // Primero obtener datos de parametros para las claves_srs que necesitamos
                $claves_srs_necesarias = array_unique(array_values($datosFiltrados));
                $placeholders_srs_necesarias = str_repeat('?,', count($claves_srs_necesarias) - 1) . '?';
                
                $sqlParametros = "SELECT clave, interior, exterior, proveedor, material, max_usable 
                                FROM parametros 
                                WHERE clave IN ($placeholders_srs_necesarias)";
                $stmtParametros = $conn->prepare($sqlParametros);
                $stmtParametros->execute($claves_srs_necesarias);
                $parametrosData = $stmtParametros->fetchAll(PDO::FETCH_ASSOC);
                
                $parametrosMap = [];
                foreach ($parametrosData as $row) {
                    $parametrosMap[$row['clave']] = [
                        'interior' => $row['interior'],
                        'exterior' => $row['exterior'],
                        'Medida' => $row['interior'] . '/' . $row['exterior'],
                        'proveedor' => $row['proveedor'],
                        'material' => mapearMaterial($row['material']),
                        'max_usable' => $row['max_usable']
                    ];
                }
                
                // Actualizar inventario_cnc en lotes
                $lotesInventario = array_chunk($datosFiltrados, 100, true);
                
                foreach ($lotesInventario as $lote) {
                    $sqlUpdateInventario = "UPDATE inventario_cnc SET 
                        Clave = CASE ";
                    
                    $whereClaves = [];
                    $paramsInventario = [];
                    
                    foreach ($lote as $clave_alterna => $clave_srs) {
                        if (isset($parametrosMap[$clave_srs])) {
                            $data = $parametrosMap[$clave_srs];
                            $sqlUpdateInventario .= "WHEN Clave = ? THEN ? ";
                            $paramsInventario[] = $clave_alterna;
                            $paramsInventario[] = $clave_srs;
                            $whereClaves[] = $clave_alterna;
                        }
                    }
                    
                    if (!empty($whereClaves)) {
                        $sqlUpdateInventario .= "END,
                            interior = CASE ";
                        
                        foreach ($lote as $clave_alterna => $clave_srs) {
                            if (isset($parametrosMap[$clave_srs])) {
                                $data = $parametrosMap[$clave_srs];
                                $sqlUpdateInventario .= "WHEN Clave = ? THEN ? ";
                                $paramsInventario[] = $clave_alterna;
                                $paramsInventario[] = $data['interior'];
                            }
                        }
                        
                        $sqlUpdateInventario .= "END,
                            exterior = CASE ";
                        
                        foreach ($lote as $clave_alterna => $clave_srs) {
                            if (isset($parametrosMap[$clave_srs])) {
                                $data = $parametrosMap[$clave_srs];
                                $sqlUpdateInventario .= "WHEN Clave = ? THEN ? ";
                                $paramsInventario[] = $clave_alterna;
                                $paramsInventario[] = $data['exterior'];
                            }
                        }
                        
                        $sqlUpdateInventario .= "END,
                            Medida = CASE ";
                        
                        foreach ($lote as $clave_alterna => $clave_srs) {
                            if (isset($parametrosMap[$clave_srs])) {
                                $data = $parametrosMap[$clave_srs];
                                $sqlUpdateInventario .= "WHEN Clave = ? THEN ? ";
                                $paramsInventario[] = $clave_alterna;
                                $paramsInventario[] = $data['Medida'];
                            }
                        }
                        
                        $sqlUpdateInventario .= "END,
                            proveedor = CASE ";
                        
                        foreach ($lote as $clave_alterna => $clave_srs) {
                            if (isset($parametrosMap[$clave_srs])) {
                                $data = $parametrosMap[$clave_srs];
                                $sqlUpdateInventario .= "WHEN Clave = ? THEN ? ";
                                $paramsInventario[] = $clave_alterna;
                                $paramsInventario[] = $data['proveedor'];
                            }
                        }
                        
                        $sqlUpdateInventario .= "END,
                            material = CASE ";
                        
                        foreach ($lote as $clave_alterna => $clave_srs) {
                            if (isset($parametrosMap[$clave_srs])) {
                                $data = $parametrosMap[$clave_srs];
                                $sqlUpdateInventario .= "WHEN Clave = ? THEN ? ";
                                $paramsInventario[] = $clave_alterna;
                                $paramsInventario[] = $data['material'];
                            }
                        }
                        
                        $sqlUpdateInventario .= "END,
                            max_usable = CASE ";
                        
                        foreach ($lote as $clave_alterna => $clave_srs) {
                            if (isset($parametrosMap[$clave_srs])) {
                                $data = $parametrosMap[$clave_srs];
                                $sqlUpdateInventario .= "WHEN Clave = ? THEN ? ";
                                $paramsInventario[] = $clave_alterna;
                                $paramsInventario[] = $data['max_usable'];
                            }
                        }
                        
                        $sqlUpdateInventario .= "END,
                            estatus = 'Disponible para cotizar',
                            updated_at = NOW()
                        WHERE Clave IN (" . str_repeat('?,', count($whereClaves) - 1) . "?)";
                        
                        $paramsInventario = array_merge($paramsInventario, $whereClaves);
                        
                        $stmtUpdateInventario = $conn->prepare($sqlUpdateInventario);
                        if ($stmtUpdateInventario->execute($paramsInventario)) {
                            $estadisticas['inventario_actualizado'] += $stmtUpdateInventario->rowCount();
                        }
                    }
                }
            }

            $conn->commit();
            
            // Mensaje de estadísticas
            $mensajeEstadisticas = "
                <div style='text-align: left;'>
                    <h4>Resumen del Proceso</h4>
                    <p><strong>Total de registros en CSV:</strong> {$estadisticas['total_registros']}</p>
                    <p><strong>Registros insertados:</strong> {$estadisticas['insertados']}</p>
                    <p><strong>Registros actualizados:</strong> {$estadisticas['actualizados']}</p>
                    <p><strong>Registros en inventario actualizados:</strong> {$estadisticas['inventario_actualizado']}</p>
                    <p><strong>Registros excluidos:</strong> {$estadisticas['excluidos']}</p>
                    <hr>
                    <p><small>Nota: Los registros se excluyen cuando la clave SRS no existe en parámetros o no cumple con las condiciones (max_usable ≠ 0 AND precio ≠ 0.00)</small></p>
                </div>
            ";
            
            echo "<script>
                $(document).ready(function(){
                    Swal.fire({
                        title: 'Proceso Completado',
                        html: `$mensajeEstadisticas`,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        width: '600px'
                    }).then(() => {
                        window.location.href = './claves_alternas.php';
                    });
                });
            </script>";
            
        } catch (Exception $e) {
            $conn->rollBack();
            echo "<script>
                $(document).ready(function(){
                    Swal.fire({
                        title: 'Error en el Proceso',
                        text: 'Error al procesar el archivo: " . addslashes($e->getMessage()) . "',
                        icon: 'error'
                    }).then(() => {
                        window.location.href = './claves_alternas.php';
                    });
                });
            </script>";
        }
    }


    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $id = $_POST['id'] ?? null;

        
        if ($action === 'insert' || $action === 'update') {
            // Limpiar espacios
            $clave_alterna = preg_replace('/\s+/', '', trim($_POST['clave_alterna'] ?? ''));
            $clave_srs = preg_replace('/\s+/', '', trim($_POST['clave_srs'] ?? ''));
    
            // Validaciones básicas
            if (empty($clave_alterna) || empty($clave_srs)) {
                echo '<script>$(document).ready(function(){
                    sweetAlertResponse("warning", "Campos requeridos", "Todos los campos son obligatorios.", "self");
                });</script>';
                exit;
            }
            // Validar que clave_alterna no sea duplicada
            $sqlCheckAlterna = "SELECT COUNT(*) FROM claves_alternas WHERE clave_alterna = :clave_alterna";
            if ($action === 'update') {
                $sqlCheckAlterna .= " AND id_alterna != :id";
            }
            
            $stmtCheckAlterna = $conn->prepare($sqlCheckAlterna);
            $stmtCheckAlterna->bindParam(':clave_alterna', $clave_alterna);
            if ($action === 'update') {
                $stmtCheckAlterna->bindParam(':id', $id, PDO::PARAM_INT);
            }
            $stmtCheckAlterna->execute();
            $existeAlterna = $stmtCheckAlterna->fetchColumn();

            if ($existeAlterna > 0) {
                echo '<script>$(document).ready(function(){
                    sweetAlertResponse("warning", "Clave duplicada", "La clave alterna ya existe en la base de datos.", "self");
                });</script>';
                exit;
            }

            // Validar que clave_srs exista en parametros con condiciones
            $sqlCheckSRS = "SELECT * FROM parametros WHERE clave = :clave_srs AND max_usable != 0 AND precio != 0.00";
            $stmtCheckSRS = $conn->prepare($sqlCheckSRS);
            $stmtCheckSRS->bindParam(':clave_srs', $clave_srs);
            $stmtCheckSRS->execute();
            $parametroData = $stmtCheckSRS->fetch(PDO::FETCH_ASSOC);

            if (!$parametroData) {
                echo '<script>$(document).ready(function(){
                    sweetAlertResponse("warning", "Clave SRS inválida", "La clave SRS no existe en parametros o no tiene precio o le falta maximo usable", "self");
                });</script>';
                exit;
            }

            // Realizar inserción/actualización en claves_alternas
            if ($action === 'insert') {
                $sql = "INSERT INTO claves_alternas (clave_alterna, clave_srs, fecha_registro) 
                        VALUES (:clave_alterna, :clave_srs, NOW())";
                $stmt = $conn->prepare($sql);
            } else {
                $sql = "UPDATE claves_alternas 
                        SET clave_alterna = :clave_alterna, clave_srs = :clave_srs 
                        WHERE id_alterna = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            }

            $stmt->bindParam(':clave_alterna', $clave_alterna);
            $stmt->bindParam(':clave_srs', $clave_srs);

            if ($stmt->execute()) {
                // ACTUALIZAR INVENTARIO_CNC
                try {
                    // Obtener datos de parametros
                    $interior = $parametroData['interior'];
                    $exterior = $parametroData['exterior'];
                    $medida = $interior . '/' . $exterior;
                    $proveedor = $parametroData['proveedor'];
                    $materialParametros = $parametroData['material'];
                    $max_usable = $parametroData['max_usable'];
                    
                    // Mapear material
                    $materialInventario = mapearMaterial($materialParametros);

                    // Actualizar inventario_cnc
                    $sqlUpdateInventario = "UPDATE inventario_cnc 
                                           SET Clave = :clave_srs,
                                               interior = :interior,
                                               exterior = :exterior,
                                               medida = :medida,
                                               proveedor = :proveedor,
                                               material = :material,
                                               max_usable = :max_usable,
                                               estatus = 'Disponible para cotizar',
                                               updated_at = NOW()
                                           WHERE Clave = :clave_alterna";
                    
                    $stmtUpdateInventario = $conn->prepare($sqlUpdateInventario);
                    $stmtUpdateInventario->bindParam(':clave_srs', $clave_srs);
                    $stmtUpdateInventario->bindParam(':interior', $interior);
                    $stmtUpdateInventario->bindParam(':exterior', $exterior);
                    $stmtUpdateInventario->bindParam(':medida', $medida);
                    $stmtUpdateInventario->bindParam(':proveedor', $proveedor);
                    $stmtUpdateInventario->bindParam(':material', $materialInventario);
                    $stmtUpdateInventario->bindParam(':max_usable', $max_usable);
                    $stmtUpdateInventario->bindParam(':clave_alterna', $clave_alterna);
                    $stmtUpdateInventario->execute();

                    $mensaje = $action === 'update' ? 'Registro actualizado correctamente.' : 'Registro agregado correctamente.';
                    echo '<script>$(document).ready(function(){
                        sweetAlertResponse("success", "Proceso exitoso", "'.$mensaje.'", "self");
                    });</script>';
                    exit;
                    
                } catch (Exception $e) {
                    // Si falla la actualización del inventario, igual se considera éxito
                    $mensaje = $action === 'update' ? 'Registro actualizado correctamente.' : 'Registro agregado correctamente.';
                    echo '<script>$(document).ready(function(){
                        sweetAlertResponse("success", "Proceso exitoso", "'.$mensaje.'", "self");
                    });</script>';
                    exit;
                }
            } else {
                echo '<script>$(document).ready(function(){
                    sweetAlertResponse("error", "Error", "Error al intentar procesar el registro.", "self");
                });</script>';
                exit;
            }

        } elseif ($action === 'delete') {
            $id_alterna = $_POST['id_alterna'] ?? null;
            
            if (!$id_alterna) {
                echo '<script>$(document).ready(function(){
                    sweetAlertResponse("error", "Error", "ID requerido para eliminar.", "self");
                });</script>';
                exit;
            }

            $sql = "DELETE FROM claves_alternas WHERE id_alterna = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id_alterna, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                echo '<script>$(document).ready(function(){
                    sweetAlertResponse("success", "Proceso exitoso", "Registro eliminado correctamente.", "self");
                });</script>';
                exit;
            } else {
                echo '<script>$(document).ready(function(){
                    sweetAlertResponse("error", "Error", "Error al intentar eliminar registro.", "self");
                });</script>';
                exit;
            }
        }
    }
}
if(isset($_GET["pendientes"])){
    $arregloSelectClavesAlternas = [];
    $sqlPrecios = "SELECT * FROM claves_alternas WHERE clave_srs IS NULL ORDER BY fecha_registro DESC";
    $stmtClavesAlternas = $conn->prepare($sqlPrecios);
    $stmtClavesAlternas->execute();
    $arregloSelectClavesAlternas = $stmtClavesAlternas->fetchAll(PDO::FETCH_ASSOC);
}else{
    $arregloSelectClavesAlternas = [];
    $sqlPrecios = "SELECT * FROM claves_alternas ORDER BY fecha_registro DESC";
    $stmtClavesAlternas = $conn->prepare($sqlPrecios);
    $stmtClavesAlternas->execute();
    $arregloSelectClavesAlternas = $stmtClavesAlternas->fetchAll(PDO::FETCH_ASSOC);
}
?>

<body class="scroll-disablado">
    
<?php include(ROOT_PATH . 'includes/user_control.php'); ?>
<div id="overlay">
    <div class="loading-message">
        <span>Cargando datos de inventario, por favor, espere...</span>    
    </div>
</div>
<section class="section-table flex-column mb-5 d-flex col-12 justify-content-center align-items-center">
    <div class="col-11">
        <div class="titulo mt-3 mb-3">
            <h1>Claves alternas</h1>
            <div class="d-flex flex-row justify-content-start col-8 gap-2">
                <button type="button" id="btnAgregar" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalAgregarEditar">Agregar Registro</button>
                <button type="button" id="btnPendientes" class="btn-general">Pendientes</button>
                <button type="button" class="btn-general" data-bs-toggle="modal" data-bs-target="#modalCsv" style="margin-right:5%;">Subir .csv de claves alternas</button>
            </div>
        </div>
        <div class="mt-4 table-container">
            <table id="parametrosTable" class="table table-striped table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Acciones</th>
                        <th>Clave alterna</th>
                        <th>Clave SRS</th>
                        <th>Fecha registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arregloSelectClavesAlternas as $row):?>
                    <tr>
                        <td class="d-fex flex-column">
                            <div class="d-flex flex-column">
                                <button class="btn-general edit-btn mb-1" 
                                    data-id="<?php echo $row['id_alterna']; ?>"
                                    data-clave_alterna="<?php echo $row['clave_alterna']; ?>"
                                    data-clave_srs="<?php echo $row['clave_srs']; ?>"
                                    >Editar</button>
                                <form class="form-delete" action="" method="POST">
                                    <input type="hidden" name="id_alterna" value="<?php echo $row['id_alterna']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn-eliminar delete-btn">Eliminar</button>
                                </form>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($row['clave_alterna']); ?></td>
                        <td><?php echo htmlspecialchars($row['clave_srs']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_registro']); ?></td>
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
                            <label for="inputClaveAlterna" class="lbl-general">Clave alterna*</label>
                            <input id="inputClaveAlterna" type="text" class="input-text"  name="clave_alterna" placeholder="" required>
                        </div>
                        <div class="" style="width:48%;">
                            <label for="inputClaveSRS" class="lbl-general">Clave SRS*</label>
                            <input id="inputClaveSRS" type="text" class="input-text"  name="clave_srs" placeholder="" required>
                        </div>
                    </div>

                    <button id="btnGuardar" type="submit" class="btn-general">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>

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
                        <img src="../assets/img/general/claves_alternas_formato_csv.jpg" class="img-fluid" alt="Formato CSV">
                    </div>                       
                    <div class="d-flex flex-column mb-3">
                        <label for="inputArchivoCSV" class="lbl-general">Seleccionar</label>
                        <input type="file" id="inputArchivoCSV" class="input-file" name="csv_alternas" accept=".csv" required>
                    </div>       

                    <button type="submit" class="btn-general">Subir</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $("#btnPendientes").on("click", function() {
            // Obtener la URL base sin parámetros
            let baseUrl = window.location.origin + window.location.pathname;
                
            // Recargar siempre con el parámetro pendientes=1
            window.location.href = baseUrl + '?pendientes';
        });
        // CLICK A EDITAR UN REGISTRO
        $('#parametrosTable').on('click', '.edit-btn', function() {
            var dataId = $(this).data('id');
            var dataClaveAlterna = $(this).data('clave_alterna');
            var dataClaveSRS = $(this).data('clave_srs');

            $('#inputId').val(dataId);
            $('#inputClaveAlterna').val(dataClaveAlterna);
            $('#inputClaveSRS').val(dataClaveSRS);

            $('#inputAction').val('update');
            $('#modalAgregarEditar').modal('show');
            $("#titleModalAddEdit").text("Editar registro");
        });

        // CAMBIAR A add AL CLICK AGREGAR REGISTRO
        $("#btnAgregar").on("click", function(){
            $('#modalAgregarEditar').modal('show');
            $('#inputAction').val('insert');
            $("#titleModalAddEdit").text("Agregar registro");
            $("#formPost")[0].reset();
        });

        // RESETEAR EL FORMULARIO AL CERRAR
        $("#btnCloseModal").on("click", function(){
            $("#formPost")[0].reset();
        });

        // PREVENIR ENVÍO DE FORMULARIO DE ELIMINACIÓN CON JS PARA MEJOR UX
        $('.form-delete').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.off('submit').submit();
                }
            });
        });
        
    });
</script>
</body>
</html>