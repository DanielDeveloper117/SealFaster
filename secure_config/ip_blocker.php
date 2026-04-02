<?php

function block_ip_path() {
    // Archivo de IPs bloqueadas: c:/xampp/secure_config/blocked_ips.json
    return __DIR__ . '/blocked_ips.json';
}

function block_ip_check() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = block_ip_path();

    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
        return true;
    }

    $blocked = json_decode(file_get_contents($file), true);
    if (!is_array($blocked)) $blocked = [];

    return !in_array($ip, $blocked);
}

function block_ip($ip = null) {
    if (!$ip) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    $file = block_ip_path();
    $blocked = [];

    if (file_exists($file)) {
        $blocked = json_decode(file_get_contents($file), true);
        if (!is_array($blocked)) $blocked = [];
    }

    if (!in_array($ip, $blocked)) {
        $blocked[] = $ip;
        file_put_contents($file, json_encode($blocked));
    }
}
