<?php

$DB_TIPO = 'mysql'; 
$DB_HOST = 'localhost'; // Normalmente es 'localhost' si usas XAMPP/WAMP/etc.
$DB_NOMBRE = 'moviapp'; // Nombre de tu base de datos.
$DB_USUARIO = 'root'; // Usuario de la BD. CÁMBIALO para producción.
$DB_PASSWORD = ''; // Contraseña de la BD. CÁMBIALA para producción.
$DB_CHARSET = 'utf8mb4';

$dsn = "$DB_TIPO:host=$DB_HOST;dbname=$DB_NOMBRE;charset=$DB_CHARSET";

try {
    
    $pdo = new PDO($dsn, $DB_USUARIO, $DB_PASSWORD);
    
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    
} catch (PDOException $e) {
    
    die("Error de conexión a la BD: " . $e->getMessage()); 
}

?> 