<?php
// Database connection settings
$servername = "sql100.infinityfree.com";  // Servidor de la base de datos
$username = "if0_37560293";               // Usuario de la base de datos
$password = "dirzonal3";                  // Contraseña de la base de datos
$dbname = "if0_37560293_Users";           // Nombre de la base de datos

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer el conjunto de caracteres a UTF-8 para evitar problemas de codificación
$conn->set_charset("utf8");

?>
