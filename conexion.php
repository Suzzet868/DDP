<?php
$host = "sql308.infinityfree.com";
$user = "if0_42999897";
$password = "Solo456yo123"; // Escribe aquí la contraseña de tu cuenta de InfinityFree
$database = "if0_42999897_revista_digital";

// Crear la conexión
$conexion = mysqli_connect($host, $user, $password, $database);

// Comprobar la conexión
if (!$conexion) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Configurar el conjunto de caracteres a UTF-8
mysqli_set_charset($conexion, "utf8mb4");
?>
