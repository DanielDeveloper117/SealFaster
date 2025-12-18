<?php

function rate_limit_path() {
    return __DIR__ . '/rate_limit.json';
}

// Cada IP tiene un contador y un timestamp
function rate_limit_allow() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = rate_limit_path();

    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }

    $data = json_decode(file_get_contents($file), true);
    if (!is_array($data)) $data = [];

    $now = time();
    $limit = 5; // intentos permitidos
    $window = 60; // en segundos

    if (!isset($data[$ip])) {
        $data[$ip] = ["count" => 1, "time" => $now];
    } else {
        // Reiniciar si se paso la ventana de tiempo
        if ($now - $data[$ip]["time"] > $window) {
            $data[$ip] = ["count" => 1, "time" => $now];
        } else {
            $data[$ip]["count"]++;
        }
    }

    file_put_contents($file, json_encode($data));

    return $data[$ip]["count"] <= $limit;
}
