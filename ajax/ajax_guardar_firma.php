<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

try {
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido. Solo se acepta POST.']);
        exit();
    }

    // Validar parámetros obligatorios
    if (!isset($_POST['id_requisicion'], $_POST['autoriza']) || !isset($_FILES['firma'])) {
        echo json_encode(['error' => 'Faltan parámetros obligatorios o archivo de firma.']);
        exit();
    }

    $id = $_POST['id_requisicion'];
    $autoriza = $_POST['autoriza'];
    $firmaFile = $_FILES['firma'];

    if (empty($id) || empty($autoriza) || $firmaFile['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'Error con los parámetros o con la carga del archivo.']);
        exit();
    }

    // Validar existencia de la requisición
    $check = $conn->prepare("SELECT COUNT(*) FROM requisiciones WHERE id_requisicion = :id");
    $check->bindParam(':id', $id, PDO::PARAM_INT);
    $check->execute();
    if (!$check->fetchColumn()) {
        echo json_encode(['error' => 'La requisición no existe.']);
        exit();
    }

    // Validar tipo MIME de la imagen subida
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($firmaFile['tmp_name']);
    if ($mime !== 'image/png') {
        echo json_encode(['error' => 'El archivo no es un PNG válido.']);
        exit();
    }

    // Crear carpeta si no existe
    $carpeta = ROOT_PATH . 'files/uploads/';
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0775, true);
    }

    if ($autoriza === "g") {
        $nombreArchivo = "firma_gerente_{$id}.png";
        $sql = "UPDATE requisiciones SET ruta_firma = :ruta WHERE id_requisicion = :id";
    } elseif ($autoriza === "a") {
        $nombreArchivo = "firma_admin_{$id}.png";
        $sql = "UPDATE requisiciones SET ruta_firma_admin = :ruta WHERE id_requisicion = :id";
    } elseif ($autoriza === "cnc") {
        $nombreArchivo = "firma_cnc_{$id}.png";
        $sql = "UPDATE requisiciones SET ruta_firma_cnc = :ruta WHERE id_requisicion = :id";
    } else {
        echo json_encode(['error' => 'Valor inesperado en el campo "autoriza".']);
        exit();
    }

    $rutaCompleta = $carpeta . $nombreArchivo;
    $rutaBD = 'files/uploads/' . $nombreArchivo;

    // Eliminar firma anterior si existe
    if (file_exists($rutaCompleta)) {
        unlink($rutaCompleta);
    }

    // Mover archivo temporal a destino final
    if (!move_uploaded_file($firmaFile['tmp_name'], $rutaCompleta)) {
        echo json_encode(['error' => 'Error al guardar la imagen en el servidor.']);
        exit();
    }

    // Actualizar ruta en la base de datos
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':ruta', $rutaBD);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Firma guardada correctamente.']);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Throwable $e) {
    echo json_encode(['error' => 'Error inesperado: ' . $e->getMessage()]);
} finally {
    $conn = null;
}
?>
