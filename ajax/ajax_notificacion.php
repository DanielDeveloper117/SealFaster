<?php
// Cargar configuración
require_once(__DIR__ . '/../config/rutes.php');
require_once(ROOT_PATH . 'config/config.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : 'Evento no reconocido';
    
    // Convertir a booleano si es string
    if (is_string($DEV_MODE)) {
        $DEV_MODE = filter_var($DEV_MODE, FILTER_VALIDATE_BOOLEAN);
    }
    
    // Opción 1: Verificar solo$DEV_MODE
    if ($DEV_MODE === true) {
        // Modo desarrollo - simular envío
        $simulatedResponse = [
            "status" => 200,
            "response" => [
                "id" => "dev-sim-" . time(),
                "recipients" => 0,
                "external_id" => null,
                "errors" => null
            ],
            "mode" => "development",
            "message" => "Notificación simulada (APP_DEV_MODE=true)",
            "timestamp" => date('Y-m-d H:i:s')
        ];
        
        header('Content-Type: application/json');
        echo json_encode($simulatedResponse);
        exit;
    }
    
    // Modo producción - enviar notificación real
    $url = 'https://api.onesignal.com/notifications?c=push';
    $appId = '7e4843fb-2466-4180-9343-74ef25a3ac82';
    $authKey = 'MzI4MzhjMmYtMTFlYS00NWQ1LWFmNWQtNGI1NzFkYWU5NGRj';

    $data = array(
        'app_id' => $appId,
        'filters' => array(
            array(
                'field' => 'tag',
                'key' => '5',
                'relation' => '=',
                'value' => 'gerentes'
            )
        ),
        'headings' => array('en' => 'Actividad en el cotizador'),
        'contents' => array('en' => $mensaje)
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Basic ' . $authKey,
        'Content-Type: application/json',
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    // Opcional: Timeout para evitar bloqueos
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    $result = [
        "status" => $httpCode,
        "mode" => "production",
        "timestamp" => date('Y-m-d H:i:s')
    ];
    
    if ($error) {
        $result["error"] = $error;
        $result["success"] = false;
    } else {
        $result["response"] = json_decode($response, true);
        $result["success"] = ($httpCode >= 200 && $httpCode < 300);
    }

    header('Content-Type: application/json');
    echo json_encode($result);
}
?>