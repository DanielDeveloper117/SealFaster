<?php
// ============================================================
// post_csv_barras.php
// Endpoint AJAX para la carga masiva de barras al inventario CNC
// desde un archivo CSV. Procesamiento en un solo request.
// Retorna 5 recolectores: ISSUES, DUPLICATE, NEW_CLAVE, SUCCESS, ERROR.
// ============================================================
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'auth/session_manager.php');
require_once(ROOT_PATH . 'config/config.php');
include(ROOT_PATH . 'includes/backend_info_user.php');

$log_dir = ROOT_PATH . 'debug';
if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
ini_set('error_log', $log_dir . '/csv_barras_debug.log');

header('Content-Type: application/json');

// ── Permisos ─────────────────────────────────────────────────
if (!in_array($tipo_usuario, ['Administrador', 'Sistemas', 'Inventarios'])) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos para realizar esta acción.']);
    exit;
}

// ── Validar almacen_id ───────────────────────────────────────
$almacen_id = isset($_POST['almacen_id']) ? (int)$_POST['almacen_id'] : 0;
if ($almacen_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Debe seleccionar un almacén válido.']);
    exit;
}

// Verificar que el almacén exista
$stmtAlm = $conn->prepare("SELECT id FROM almacenes WHERE id = ?");
$stmtAlm->execute([$almacen_id]);
if (!$stmtAlm->fetch()) {
    echo json_encode(['success' => false, 'message' => 'El almacén seleccionado no existe.']);
    exit;
}

// ── Validar archivo CSV ──────────────────────────────────────
if (!isset($_FILES['csv_barras']) || $_FILES['csv_barras']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Error al recibir el archivo.']);
    exit;
}

$ext = strtolower(pathinfo($_FILES['csv_barras']['name'], PATHINFO_EXTENSION));
if ($ext !== 'csv') {
    echo json_encode(['success' => false, 'message' => 'El archivo debe tener extensión .csv.']);
    exit;
}

// ── Leer CSV ─────────────────────────────────────────────────
$tmp_file = $_FILES['csv_barras']['tmp_name'];

// Leer contenido y eliminar BOM si existe
$raw_content = file_get_contents($tmp_file);
$raw_content = preg_replace('/^\xEF\xBB\xBF/', '', $raw_content);
$tmp_clean = tempnam(sys_get_temp_dir(), 'csv_barras_');
file_put_contents($tmp_clean, $raw_content);

$f = fopen($tmp_clean, 'r');
if (!$f) {
    echo json_encode(['success' => false, 'message' => 'No se pudo abrir el archivo.']);
    exit;
}

// Saltar header
fgetcsv($f);

$rows = [];
$line_num = 1; // 1 = primera fila de datos (después del header)
while (($fila = fgetcsv($f)) !== false) {
    $line_num++;
    // Ignorar filas completamente vacías
    $fila_limpia = array_filter($fila, function($v) { return trim($v) !== ''; });
    if (empty($fila_limpia)) continue;

    $rows[] = [
        'linea'          => $line_num,
        'proveedor'      => trim($fila[0] ?? ''),
        'material'       => trim($fila[1] ?? ''),
        'clave'          => trim($fila[2] ?? ''),
        'medida'         => trim($fila[3] ?? ''),
        'max_usable'     => trim($fila[4] ?? ''),
        'stock'          => trim($fila[5] ?? ''),
        'lote_pedimento' => trim($fila[6] ?? ''),
    ];
}
fclose($f);
@unlink($tmp_clean);

if (empty($rows)) {
    echo json_encode(['success' => false, 'message' => 'El archivo no contiene registros válidos (verifique el formato).']);
    exit;
}

// ══════════════════════════════════════════════════════════════
//                   INICIALIZAR RECOLECTORES
// ══════════════════════════════════════════════════════════════
$ISSUES_COLLECTOR    = [];
$DUPLICATE_COLLECTOR = [];
$NEW_CLAVE_COLLECTOR = [];
$SUCCESS_COLLECTOR   = [];
$ERROR_COLLECTOR     = [];

// ══════════════════════════════════════════════════════════════
//           CONSULTAS BATCH DE VALIDACIÓN
// ══════════════════════════════════════════════════════════════
try {
    // 1. Proveedores existentes en inventario_cnc
    $stmt = $conn->query("SELECT DISTINCT UPPER(TRIM(proveedor)) AS prov FROM inventario_cnc WHERE proveedor IS NOT NULL AND proveedor != ''");
    $proveedores_set = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $p) {
        $proveedores_set[$p] = true;
    }

    // 2. Materiales existentes en inventario_cnc
    $stmt = $conn->query("SELECT DISTINCT UPPER(TRIM(material)) AS mat FROM inventario_cnc WHERE material IS NOT NULL AND material != ''");
    $materiales_set = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $m) {
        $materiales_set[$m] = true;
    }

    // 3. Lotes existentes en inventario_cnc (para detección de duplicados)
    $stmt = $conn->query("SELECT DISTINCT lote_pedimento FROM inventario_cnc WHERE lote_pedimento IS NOT NULL AND lote_pedimento != ''");
    $lotes_existentes_set = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $l) {
        $lotes_existentes_set[$l] = true;
    }

    // 4. Consultar claves en parametros (clave principal y clave_alterna)
    $csv_claves_unicas = [];
    foreach ($rows as $r) {
        $c = preg_replace('/\s+/', '', $r['clave']);
        if ($c !== '') $csv_claves_unicas[$c] = true;
    }
    $csv_claves_unicas = array_keys($csv_claves_unicas);

    // Mapa: clave_buscada => ['found' => true, 'precio' => float]
    $clave_info_map = [];

    if (!empty($csv_claves_unicas)) {
        // Paso A: Buscar por clave principal (usa índice)
        $ph = implode(',', array_fill(0, count($csv_claves_unicas), '?'));
        $stmt = $conn->prepare("SELECT clave, precio FROM parametros WHERE REPLACE(clave, CHAR(0), '') IN ($ph)");
        $stmt->execute($csv_claves_unicas);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $reg) {
            $c = trim(str_replace("\0", '', $reg['clave']));
            $clave_info_map[$c] = ['found' => true, 'precio' => floatval($reg['precio'])];
        }

        // Paso B: Para claves no encontradas, buscar por clave_alterna
        $no_encontradas = array_diff($csv_claves_unicas, array_keys($clave_info_map));
        if (!empty($no_encontradas)) {
            $ph2 = implode(',', array_fill(0, count($no_encontradas), '?'));
            $stmt2 = $conn->prepare("SELECT clave_alterna, precio FROM parametros WHERE REPLACE(clave_alterna, CHAR(0), '') IN ($ph2)");
            $stmt2->execute(array_values($no_encontradas));
            foreach ($stmt2->fetchAll(PDO::FETCH_ASSOC) as $reg) {
                $ca = trim(str_replace("\0", '', $reg['clave_alterna']));
                $clave_info_map[$ca] = ['found' => true, 'precio' => floatval($reg['precio'])];
            }
        }
    }

} catch (Exception $e) {
    echo json_encode([
        'success'    => false,
        'message'    => 'Error al consultar datos de validación.',
        'error_detail' => $e->getMessage()
    ]);
    exit;
}

// ══════════════════════════════════════════════════════════════
//           PROCESAR CADA FILA DEL CSV
// ══════════════════════════════════════════════════════════════
$lotes_csv_vistos = []; // Detectar duplicados internos en el CSV
$filas_a_insertar = []; // Filas que pasaron todas las validaciones

foreach ($rows as $row) {
    $proveedor      = $row['proveedor'];
    $material       = $row['material'];
    $clave          = preg_replace('/\s+/', '', $row['clave']);
    $medida         = $row['medida'];
    $max_usable_raw = $row['max_usable'];
    $stock_raw      = $row['stock'];
    $lote           = $row['lote_pedimento'];
    $linea          = $row['linea'];

    // Datos base para los recolectores
    $base_data = [
        'clave'     => $clave,
        'lote'      => $lote,
        'proveedor' => $proveedor,
        'material'  => $material,
        'medida'    => $medida,
    ];

    // ── PUNTO 1: Validaciones de formato ─────────────────────

    // 1a. Proveedor no vacío y existente
    if ($proveedor === '') {
        $ISSUES_COLLECTOR[] = array_merge($base_data, [
            'estatus'    => 'Ignorado',
            'comentario' => "Línea $linea: El campo proveedor está vacío.",
        ]);
        continue;
    }
    if (!isset($proveedores_set[strtoupper($proveedor)])) {
        $ISSUES_COLLECTOR[] = array_merge($base_data, [
            'estatus'    => 'Ignorado',
            'comentario' => "Línea $linea: No se encontró proveedor coincidente \"$proveedor\".",
        ]);
        continue;
    }

    // 1b. Material no vacío y existente
    if ($material === '') {
        $ISSUES_COLLECTOR[] = array_merge($base_data, [
            'estatus'    => 'Ignorado',
            'comentario' => "Línea $linea: El campo material está vacío.",
        ]);
        continue;
    }
    if (!isset($materiales_set[strtoupper($material)])) {
        $ISSUES_COLLECTOR[] = array_merge($base_data, [
            'estatus'    => 'Ignorado',
            'comentario' => "Línea $linea: No se encontró material coincidente \"$material\".",
        ]);
        continue;
    }

    // 1c. Clave no vacía
    if ($clave === '') {
        $ISSUES_COLLECTOR[] = array_merge($base_data, [
            'estatus'    => 'Ignorado',
            'comentario' => "Línea $linea: El campo clave está vacío.",
        ]);
        continue;
    }

    // 1d. Medida con formato X/Y (números con decimales opcionales)
    if (!preg_match('/^\d+(\.\d+)?\/\d+(\.\d+)?$/', $medida)) {
        $ISSUES_COLLECTOR[] = array_merge($base_data, [
            'estatus'    => 'Ignorado',
            'comentario' => "Línea $linea: Formato de medida inválido \"$medida\". Se esperaba formato X/Y (ej. 50/60).",
        ]);
        continue;
    }

    // 1e. max_usable numérico
    if (!is_numeric($max_usable_raw)) {
        $ISSUES_COLLECTOR[] = array_merge($base_data, [
            'estatus'    => 'Ignorado',
            'comentario' => "Línea $linea: Max usable no es numérico \"$max_usable_raw\".",
        ]);
        continue;
    }

    // 1f. stock numérico
    if (!is_numeric($stock_raw)) {
        $ISSUES_COLLECTOR[] = array_merge($base_data, [
            'estatus'    => 'Ignorado',
            'comentario' => "Línea $linea: Stock no es numérico \"$stock_raw\".",
        ]);
        continue;
    }

    // 1g. lote_pedimento no vacío
    if ($lote === '') {
        $ISSUES_COLLECTOR[] = array_merge($base_data, [
            'estatus'    => 'Ignorado',
            'comentario' => "Línea $linea: El campo lote/pedimento está vacío.",
        ]);
        continue;
    }

    // ── PUNTO 2: Verificar duplicados ────────────────────────

    // 2a. Lote ya existe en inventario_cnc
    if (isset($lotes_existentes_set[$lote])) {
        $DUPLICATE_COLLECTOR[] = array_merge($base_data, [
            'estatus'    => 'Ignorado',
            'comentario' => "Línea $linea: El lote \"$lote\" ya existe en el inventario CNC.",
        ]);
        continue;
    }

    // 2b. Lote duplicado dentro del mismo CSV
    if (isset($lotes_csv_vistos[$lote])) {
        $DUPLICATE_COLLECTOR[] = array_merge($base_data, [
            'estatus'    => 'Ignorado',
            'comentario' => "Línea $linea: El lote \"$lote\" está duplicado dentro del archivo CSV.",
        ]);
        continue;
    }
    $lotes_csv_vistos[$lote] = true;

    // ── Extraer interior / exterior de medida ────────────────
    $partes_medida = explode('/', $medida);
    $interior = $partes_medida[0];
    $exterior = $partes_medida[1];

    $max_usable = floatval($max_usable_raw);
    $stock      = floatval($stock_raw);

    // ── PUNTOS 3 y 4: Determinar estatus según clave ────────
    if (isset($clave_info_map[$clave])) {
        $precio = $clave_info_map[$clave]['precio'];
        if ($precio > 0.00) {
            // PUNTO 4: Clave existe y tiene precio
            $estatus    = 'Disponible para cotizar';
            $comentario = "Línea $linea: Clave encontrada con precio. Barra lista para cotizar.";
            $collector  = 'SUCCESS';
        } else {
            // PUNTO 3: Clave existe pero sin precio
            $estatus    = 'Clave nueva pendiente';
            $comentario = "Línea $linea: La clave \"$clave\" existe pero no tiene precio asignado (precio \$0.00). No será posible cotizar con esta barra.";
            $collector  = 'NEW_CLAVE';
        }
    } else {
        // PUNTO 3: Clave no encontrada en parametros
        $estatus    = 'Clave nueva pendiente';
        $comentario = "Línea $linea: La clave \"$clave\" no fue encontrada, requiere validación. No será posible cotizar con esta barra.";
        $collector  = 'NEW_CLAVE';
    }

    // Agregar al recolector correspondiente
    $collector_data = array_merge($base_data, [
        'estatus'    => $estatus,
        'comentario' => $comentario,
    ]);

    if ($collector === 'SUCCESS') {
        $SUCCESS_COLLECTOR[] = $collector_data;
    } else {
        $NEW_CLAVE_COLLECTOR[] = $collector_data;
    }

    // Preparar datos para inserción
    $filas_a_insertar[] = [
        'almacen_id'     => $almacen_id,
        'proveedor'      => $proveedor,
        'material'       => $material,
        'clave'          => $clave,
        'medida'         => $medida,
        'max_usable'     => $max_usable,
        'pre_stock'      => $stock,
        'stock'          => $stock,
        'lote_pedimento' => $lote,
        'interior'       => $interior,
        'exterior'       => $exterior,
        'estatus'        => $estatus,
    ];
}

// ══════════════════════════════════════════════════════════════
//           INSERCIÓN MASIVA EN inventario_cnc
// ══════════════════════════════════════════════════════════════
if (!empty($filas_a_insertar)) {
    try {
        $conn->beginTransaction();

        $stmtIns = $conn->prepare(
            "INSERT INTO inventario_cnc 
             (almacen_id, proveedor, material, Clave, Medida, max_usable, pre_stock, stock, lote_pedimento, interior, exterior, estatus, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        foreach ($filas_a_insertar as $data) {
            $stmtIns->execute([
                $data['almacen_id'],
                $data['proveedor'],
                $data['material'],
                $data['clave'],
                $data['medida'],
                $data['max_usable'],
                $data['pre_stock'],
                $data['stock'],
                $data['lote_pedimento'],
                $data['interior'],
                $data['exterior'],
                $data['estatus'],
            ]);
        }

        $conn->commit();

        error_log(date('[Y-m-d H:i:s] ') . "[post_csv_barras] INSERT exitoso: " . count($filas_a_insertar) . " registros insertados en inventario_cnc." . PHP_EOL, 3, $log_dir . '/csv_barras_debug.log');

    } catch (Exception $e) {
        $conn->rollBack();
        error_log(date('[Y-m-d H:i:s] ') . "[post_csv_barras] ERROR INSERT: " . $e->getMessage() . PHP_EOL, 3, $log_dir . '/csv_barras_debug.log');

        // Mover SUCCESS y NEW_CLAVE al ERROR_COLLECTOR
        foreach ($SUCCESS_COLLECTOR as $item) {
            $ERROR_COLLECTOR[] = array_merge($item, [
                'estatus'    => 'Error',
                'comentario' => 'Error al insertar en BD: ' . $e->getMessage(),
            ]);
        }
        foreach ($NEW_CLAVE_COLLECTOR as $item) {
            $ERROR_COLLECTOR[] = array_merge($item, [
                'estatus'    => 'Error',
                'comentario' => 'Error al insertar en BD: ' . $e->getMessage(),
            ]);
        }
        $SUCCESS_COLLECTOR   = [];
        $NEW_CLAVE_COLLECTOR = [];
    }
}

// ══════════════════════════════════════════════════════════════
//           RESPUESTA JSON
// ══════════════════════════════════════════════════════════════
echo json_encode([
    'success'              => true,
    'ISSUES_COLLECTOR'     => $ISSUES_COLLECTOR,
    'DUPLICATE_COLLECTOR'  => $DUPLICATE_COLLECTOR,
    'NEW_CLAVE_COLLECTOR'  => $NEW_CLAVE_COLLECTOR,
    'SUCCESS_COLLECTOR'    => $SUCCESS_COLLECTOR,
    'ERROR_COLLECTOR'      => $ERROR_COLLECTOR,
]);
?>
