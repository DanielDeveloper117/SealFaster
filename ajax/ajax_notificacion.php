<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mensaje = isset($_POST['mensaje']) ? $_POST['mensaje'] : 'Evento no reconocido';

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

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo json_encode(["status" => $httpCode, "response" => $response]);
}
?>
