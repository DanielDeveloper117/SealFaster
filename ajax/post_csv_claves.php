<?php
// ============================================================
// post_csv_claves.php
// Endpoint AJAX fraccionado para la carga masiva de claves SRS.
// Maneja 4 acciones: 'upload', 'process_chunk', 'finish', 'cancel'.
// ============================================================
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
require_once(ROOT_PATH . 'includes/functions/actualizar_inventario.php');
include(ROOT_PATH . 'includes/backend_info_user.php');

$uid = (int)$_SESSION['id'];

// ¡CRÍTICO PARA QUE FUNCIONE EL BOTÓN CANCELAR Y PETICIONES PARALELAS!
// Libera el candado de sesión que PHP impone por defecto.
session_write_close(); 

$log_dir = ROOT_PATH . 'debug';
if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
ini_set('error_log', $log_dir . '/csv_claves_debug.log');
header('Content-Type: application/json');

if ($tipo_usuario !== 'Administrador' && $tipo_usuario !== 'Sistemas') {
    echo json_encode(['success' => false, 'message' => 'Sin permisos para realizar esta acción.']);
    exit;
}

$action = $_POST['action'] ?? '';

// --- ACCIÓN: UPLOAD ---
if ($action === 'upload') {
    if (!isset($_FILES['csv_precios']) || $_FILES['csv_precios']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Error al recibir el archivo.']);
        exit;
    }
    
    $ext = strtolower(pathinfo($_FILES['csv_precios']['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        echo json_encode(['success' => false, 'message' => 'El archivo debe tener extensión .csv.']);
        exit;
    }

    $tmp_dir = ROOT_PATH . 'debug/tmp_uploads';
    if (!is_dir($tmp_dir)) mkdir($tmp_dir, 0755, true);
    
    $hash = md5(uniqid(rand(), true));
    $dest = $tmp_dir . '/csv_' . $hash . '.csv';
    
    if (move_uploaded_file($_FILES['csv_precios']['tmp_name'], $dest)) {
        // Contar líneas válidas
        $lines = 0;
        $f = fopen($dest, 'r');
        fgetcsv($f); // saltar header
        while(fgetcsv($f) !== false) { $lines++; }
        fclose($f);
        
        echo json_encode([
            'success' => true, 
            'upload_id' => $hash, 
            'total_lines' => $lines
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo guardar el archivo temporal.']);
    }
    exit;
}

// --- ACCIÓN: CANCEL ---
if ($action === 'cancel') {
    $upload_id = $_POST['upload_id'] ?? '';
    if (!$upload_id) exit;
    $file = ROOT_PATH . 'debug/tmp_uploads/csv_' . $upload_id . '.csv';
    if (file_exists($file)) unlink($file);
    echo json_encode(['success' => true]);
    exit;
}

// --- ACCIÓN: PROCESS_CHUNK ---
if ($action === 'process_chunk') {
    $upload_id = $_POST['upload_id'] ?? '';
    $start_line = (int)($_POST['start_line'] ?? 1);
    $chunk_size = (int)($_POST['chunk_size'] ?? 500);

    $file = ROOT_PATH . 'debug/tmp_uploads/csv_' . $upload_id . '.csv';
    if (!file_exists($file)) {
        echo json_encode(['success' => false, 'message' => 'El archivo temporal no existe o expiró.']);
        exit;
    }

    $f = fopen($file, 'r');
    fgetcsv($f); // Saltar header
    
    $datos = [];
    $errores_chunk = [];
    $current_line = 1;
    
    // Avanzar hasta start_line saltando líneas previamente procesadas
    while ($current_line < $start_line && fgetcsv($f) !== false) {
        $current_line++;
    }

    // Leer chunk
    $read_count = 0;
    while ($read_count < $chunk_size && ($fila = fgetcsv($f)) !== false) {
        $read_count++;
        $actual_line = $start_line + $read_count - 1;
        
        if (count($fila) < 8) continue;

        $claveRaw = trim($fila[0] ?? '');
        $clave = preg_replace('/\s+/', '', $claveRaw);
        
        // Validación Restrictiva Estricta: La fila DEBE tener clave.
        if ($clave === '') {
            $errores_chunk[] = "Línea " . ($actual_line + 1) . ": Debe contener obligatoriamente una clave principal.";
            continue;
        }

        $clave_alterna = preg_replace('/\s+/', '', trim($fila[1] ?? ''));

        $datos[$clave] = [
            'clave'         => $clave,
            'clave_alterna' => $clave_alterna,
            'precio'        => floatval($fila[2] ?? 0),
            'max_usable'    => intval($fila[3]   ?? 0),
            'interior'      => preg_replace('/\s+/', '', trim($fila[4] ?? '')),
            'exterior'      => preg_replace('/\s+/', '', trim($fila[5] ?? '')),
            'proveedor'     => trim($fila[6] ?? ''),
            'material'      => trim($fila[7] ?? ''),
            'tipo'          => trim($fila[8] ?? ''),
            'usuario_id'    => $uid,
            'linea'         => $actual_line + 1, // +1 por el encabezado original
        ];
    }
    fclose($f);

    if (!empty($errores_chunk)) {
        echo json_encode(['success' => false, 'message' => 'Errores en datos obligatorios.', 'errores' => $errores_chunk]);
        exit;
    }

    if (empty($datos)) {
        echo json_encode(['success' => true, 'insertados' => 0, 'actualizados' => 0, 'claves_procesadas' => []]);
        exit;
    }

    // --- Validación ultra-rápida (Solo usando index Clave) ---
    $claves_csv     = array_keys($datos);
    $existentes_bd  = [];

    try {
        $placeholders = implode(',', array_fill(0, count($claves_csv), '?'));
        // IMPORTANTE: Ya no usamos "OR clave_alterna IN (...)" para evitar Full Table Scan.
        $stmtEx = $conn->prepare("SELECT id, clave FROM parametros WHERE clave IN ($placeholders)");
        $stmtEx->execute($claves_csv);
        foreach ($stmtEx->fetchAll(PDO::FETCH_ASSOC) as $reg) {
            $clave_bd = trim($reg['clave']);
            $existentes_bd[$clave_bd] = (int)$reg['id'];
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error BD val.', 'error_detail' => $e->getMessage()]);
        exit;
    }

    // --- Ejecución en tabla parámetros ---
    $insertados = 0;
    $actualizados = 0;
    $claves_procesadas_chunk = [];

    // Recolectar lo que se procesará para el script final (ambas claves si las hay)
    foreach ($datos as $v) {
        $claves_procesadas_chunk[] = [
            'clave' => $v['clave'], 
            'clave_alterna' => $v['clave_alterna']
        ];
    }
    
    try {
        $conn->beginTransaction();
        
        $ids_a_actualizar = [];
        $lote_id_map = [];
        foreach ($claves_csv as $clave) {
            if (isset($existentes_bd[$clave])) {
                $id_e = $existentes_bd[$clave];
                $ids_a_actualizar[] = $id_e;
                $lote_id_map[$id_e] = $clave;
            }
        }

        if (!empty($ids_a_actualizar)) {
            $params = [];
            $fields = ['clave'=>'clave', 'clave_alterna'=>'clave_alterna', 'precio'=>'precio', 'max_usable'=>'max_usable', 'interior'=>'interior', 'exterior'=>'exterior', 'proveedor'=>'proveedor','material'=>'material','tipo'=>'tipo','usuario_id'=>'usuario_id'];
            $case_sql = "";
            foreach ($fields as $field_name => $db_field) {
                $case_sql .= "$db_field = CASE id ";
                foreach ($ids_a_actualizar as $id) {
                    $c = $lote_id_map[$id];
                    $case_sql .= "WHEN ? THEN ? ";
                    $params[] = $id; $params[] = $datos[$c][$field_name];
                }
                $case_sql .= "END, ";
            }
            $case_sql .= "updated_at = NOW() ";
            $sql_upd = "UPDATE parametros SET $case_sql WHERE id IN (".implode(',', array_fill(0, count($ids_a_actualizar), '?')).")";
            foreach ($ids_a_actualizar as $id) $params[] = $id;
            
            $stmtUpd = $conn->prepare($sql_upd);
            $stmtUpd->execute($params);
            $actualizados = count($ids_a_actualizar);
        }

        $claves_nuevas = array_diff($claves_csv, array_keys($existentes_bd));
        if (!empty($claves_nuevas)) {
            $values = []; $insert_params = []; $idx = 0;
            foreach ($claves_nuevas as $c) {
                $v = $datos[$c];
                $values[] = "(:clave$idx, :ca$idx, :pr$idx, :mu$idx, :int$idx, :ext$idx, :prov$idx, :mat$idx, :tipo$idx, :uid$idx, NOW(), NOW())";
                $insert_params[":clave$idx"]=$c; $insert_params[":ca$idx"]=$v['clave_alterna']; $insert_params[":pr$idx"]=$v['precio'];
                $insert_params[":mu$idx"]=$v['max_usable']; $insert_params[":int$idx"]=(int)$v['interior']; $insert_params[":ext$idx"]=(int)$v['exterior'];
                $insert_params[":prov$idx"]=$v['proveedor']; $insert_params[":mat$idx"]=$v['material']; $insert_params[":tipo$idx"]=$v['tipo'];
                $insert_params[":uid$idx"]=$v['usuario_id'];
                $idx++;
            }
            $stmtIns = $conn->prepare("INSERT INTO parametros (clave, clave_alterna, precio, max_usable, interior, exterior, proveedor, material, tipo, usuario_id, created_at, updated_at) VALUES " . implode(', ', $values));
            $stmtIns->execute($insert_params);
            $insertados = (int)$stmtIns->rowCount();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'insertados' => $insertados, 'actualizados' => $actualizados, 'claves_procesadas' => $claves_procesadas_chunk]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al procesar chunk BD', 'error_detail' => $e->getMessage()]);
    }
    exit;
}

// --- ACCIÓN: FINISH ---
if ($action === 'finish') {
    $upload_id = $_POST['upload_id'] ?? '';
    // sync_clave, sync_alterna, sync_ambas
    $sync_mode = $_POST['sync_mode'] ?? 'sync_clave'; 
    $claves_procesadas = json_decode($_POST['claves_procesadas'] ?? '[]', true);
    
    $file = ROOT_PATH . 'debug/tmp_uploads/csv_' . $upload_id . '.csv';
    $registros_inventario_actualizados = 0;
    
    try {
        if (!empty($claves_procesadas) && $sync_mode !== 'sync_nada') {
            $resultado_sync = sincronizarInventarioPorClaves($conn, $claves_procesadas, $sync_mode);
            $registros_inventario_actualizados = $resultado_sync['total_actualizados'] ?? 0;
        }
        
        if (file_exists($file)) unlink($file);
        
        echo json_encode([
            'success' => true,
            'message' => "Proceso completado. Inventario actualizados: $registros_inventario_actualizados.",
            'inventario_actualizados' => $registros_inventario_actualizados
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar sync final.', 'error_detail' => $e->getMessage()]);
    }
    exit;
}
?>
