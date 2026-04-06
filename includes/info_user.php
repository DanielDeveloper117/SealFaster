<?php

$id_usuario2 = $_SESSION['id'];
$sql2 = "SELECT * FROM login WHERE id = :id_usuario";
$stmt = $conn->prepare($sql2);
$stmt->bindParam(':id_usuario', $id_usuario2);
$stmt->execute();
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
$usuario_desencriptado = "";
if ($resultado) {
    // Clave de encriptaci܇n
    $clave_encriptacion = $PASS_UNCRIPT ?? '';

    // Desencriptar el nombre
    $nombre_encriptado = $resultado['nombre'];
    $nombre_desencriptado = openssl_decrypt($nombre_encriptado, 'AES-128-ECB', $clave_encriptacion);

    $usuario_encriptado = $resultado['usuario'];
    $usuario_desencriptado = openssl_decrypt($usuario_encriptado, 'AES-128-ECB', $clave_encriptacion);

    $area_encriptado = $resultado['area'];
    $area_desencriptado = openssl_decrypt($area_encriptado, 'AES-128-ECB', $clave_encriptacion);
} else {
    $nombre_desencriptado = 'Usuario desconocido';
}
?> 
<h2 id="welcome-text">¡Bienvenido(a) <span><?php echo htmlspecialchars($nombre_desencriptado); ?>!</span></h4>
<p class="card-text">Nos alegra verte de nuevo.</p>
<h5>Area: <span><?php echo htmlspecialchars($area_desencriptado); ?></span></h5>
<h5>Usuario: <span><?php echo htmlspecialchars($usuario_desencriptado); ?></span></h5>

