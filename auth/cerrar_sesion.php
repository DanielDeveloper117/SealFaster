<?php
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

// Iniciar la sesión si no está iniciada
session_start();
// FUNCION CHISMOSA PARA GUARDAR ENCRIPTADAMENTE LO QUE VA HACIENDO EL USUARIO EN log_usuarios
$sql_get_username = "SELECT usuario FROM login WHERE id = ?";
$stmt_username = $conn->prepare($sql_get_username);
$stmt_username->execute([$_SESSION['id']]);
$username_row = $stmt_username->fetch(PDO::FETCH_ASSOC);

if ($username_row) {
    $username = $username_row['usuario'];
    $sql_log = "INSERT INTO log_usuarios (Usuario, Accion, Instruccion) VALUES (?, 'Ha cerrado sesión', '')";
    $stmt_log = $conn->prepare($sql_log);
    $stmt_log->execute([$username]); 
    
} else {
    // Si no se encuentra un usuario con el ID de sesión proporcionado
    echo "No se encontró un usuario con el ID de sesión proporcionado.";
}
//  FIN  FUNCION CHISMOSA PARA GUARDAR ENCRIPTADAMENTE LO QUE VA HACIENDO EL USUARIO EN log_usuarios
// Destruir la sesión
session_destroy();

// Redirigir a index.php
header("Location: ../index.php");
exit; // Asegurar que el script se detenga después de la redirección
?>