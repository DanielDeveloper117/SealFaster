<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

header('Content-Type: application/json');
// Obtener los parámetros de DataTables
$start = $_POST['start'];  // El índice de la página
$length = $_POST['length'];  // El número de registros por página

// Crear la consulta SQL con la paginación
$sql = "SELECT id, clave, medida, proveedor, material, max_usable, stock, lote_pedimento
        FROM inventario_cnc 
        LIMIT :start, :length";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':start', $start, PDO::PARAM_INT);
$stmt->bindParam(':length', $length, PDO::PARAM_INT);
$stmt->execute();

// Obtener los datos
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar los datos y agregar los estilos
$formattedData = [];
foreach ($data as $row) {
    $stock = $row['stock'];
    $usableStyle = "";
    $usableText = "";

    // Iluminar si stock es menor a 15
    if ($stock < 15) {
        $usableStyle = "#ff00002e";
        $usableText = "No usable";
    } else {
        $usableText = "Usable";
    }

    // Añadir los valores procesados a la fila
    $formattedData[] = [
        "id" => $row['id'],
        "clave" => $row['clave'],
        "medida" => $row['medida'],
        "proveedor" => $row['proveedor'],
        "material" => $row['material'],
        "max_usable" => $row['max_usable'],
        "stock" => $row['stock'],
        "usableText" => $usableText,
        "lote_pedimento" => $row['lote_pedimento'],
        "usableStyle" => $usableStyle
    ];
}

// Obtener el total de registros (sin paginación)
$sqlCount = "SELECT COUNT(*) FROM inventario_cnc";
$stmtCount = $conn->prepare($sqlCount);
$stmtCount->execute();
$totalRecords = $stmtCount->fetchColumn();

// Devolver los datos en formato JSON para DataTables
echo json_encode([
    "draw" => $_POST['draw'],  // Para asegurar que la respuesta corresponda con la solicitud
    "recordsTotal" => $totalRecords,  // Total de registros sin filtros
    "recordsFiltered" => $totalRecords,  // Total de registros filtrados (en este caso es igual al total)
    "data" => $formattedData  // Los datos con los estilos y valores calculados
]);
?>

