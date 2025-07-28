<?php
$servername = "localhost";
$username = "root";
$password = "";
//$username = "sellosyr_Desarrollo";
//$password = "#SRSsellos2024#";
$dbname = "sellosyr_sellosctd";

try {
    // Configuración de la conexión a la base de datos con UTF-8
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    //echo "Connection failed: " . $e->getMessage();
}
?>