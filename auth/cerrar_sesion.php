<?php
// 1. Cargar rutas si es necesario para la redirección final
// require_once(__DIR__ . '/../config/rutes.php');

// 2. Iniciar sesión para poder manipularla y luego borrarla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Limpiar todas las variables de sesión del array $_SESSION
$_SESSION = array();

// 4. DESTRUIR LA COOKIE DE SESIÓN (Crucial para seguridad)
// Esto elimina el rastro del ID de sesión en el navegador del usuario.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Destruir la sesión en el servidor
session_destroy();

// 6. Redirigir al index o login
header("Location: ../index.php");
exit;