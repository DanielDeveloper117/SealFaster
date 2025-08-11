<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');
session_start();
try{
    header('Content-Type: application/json');

    $id_usuario = $_SESSION['id'];
    
    // Preparar la consulta
    $sqlCotizaciones = "SELECT * FROM cotizacion_materiales WHERE id_usuario = :id  AND archivada = 0 GROUP BY id_cotizacion ORDER BY fecha DESC, hora DESC";
    $stmtCotizaciones = $conn->prepare($sqlCotizaciones);
    $stmtCotizaciones->bindParam(':id', $id_usuario);
    $stmtCotizaciones->execute();
    $arregloSelectCotizaciones = $stmtCotizaciones->fetchAll(PDO::FETCH_ASSOC);

    // Devolver los resultados en formato JSON
    echo json_encode($arregloSelectCotizaciones);

} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    $conn = null; // Cerrar la conexión
}
?>