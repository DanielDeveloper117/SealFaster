<?php
/**
 * Sealfaster - Robust Session Manager
 * Este archivo debe incluirse al principio de cada script protegido.
 */

class SessionManager {
    public static function check() {
        // 1. Iniciar sesión con flags de seguridad (Soporte para PHP 7.3+)
        if (session_status() === PHP_SESSION_NONE) {
            session_start([
                'cookie_httponly' => true, // Impide que JS acceda a la cookie
                'cookie_secure' => isset($_SERVER['HTTPS']), // Solo via HTTPS
                'use_only_cookies' => true,
            ]);
        }
        if (!defined('ACCESO_PERMITIDO')) {
            // Si alguien entra por URL, esto lo detiene de inmediato
            header('HTTP/1.1 403 Forbidden');
            die("Error: Acceso no permitido / Direct access is forbidden.");
            self::redirect();
        }
        // 2. ¿Existe el ID de usuario?
        if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
            self::redirect();
        }

        // 3. Verificar Inactividad (Timeout de 2 horas)
        $timeout = 7200; // 2 horas en segundos
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
            self::redirect();
        }
        $_SESSION['last_activity'] = time();

        // 4. Protección contra Secuestro de Sesión (Session Hijacking)
        // Verificamos que la IP y el Navegador no hayan cambiado repentinamente
        $fingerprint = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
        if (!isset($_SESSION['fingerprint'])) {
            $_SESSION['fingerprint'] = $fingerprint;
        } elseif ($_SESSION['fingerprint'] !== $fingerprint) {
            self::redirect(); // Alguien clonó la cookie en otra IP/PC
        }
    }

    private static function redirect() {
        // Destruir todo rastro de sesión antes de redirigir
        session_unset();
        session_destroy();
        
        // Ajustar la ruta según tu estructura de carpetas
        header("Location: ../index.php"); 
        exit();
    }
}

// Ejecución automática al importar
SessionManager::check();