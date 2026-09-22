<?php
$host = "localhost";
$user = "root";
$password = "";      // Por defecto en XAMPP viene vacío
$database = "revista_digital";   // Nombre de tu base de datos en MySQL

// Crear la conexión
$conexion = mysqli_connect($host, $user, $password, $database);

// Comprobar la conexión
if (!$conexion) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Configurar el conjunto de caracteres a UTF-8 (para evitar problemas con tildes y eñes)
mysqli_set_charset($conexion, "utf8");
?>