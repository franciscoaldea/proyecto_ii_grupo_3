<?php
// --- CONEXIÓN A BASE DE DATOS ---
// Cambia estos datos a los de tu servidor MySQL
$host = 'localhost';
$db = 'cinee';       // nombre base de datos
$user = 'root';      // usuario MySQL
$pass = '';          // contraseña MySQL

$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("Error al conectar a la base de datos: " . $mysqli->connect_error);
}
?>


