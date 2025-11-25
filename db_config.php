<?php
// --- CONFIGURACIÓN DE LA BASE DE DATOS (AVENTUREROS S.A.) ---

// Basado en la recomendación del proyecto, usaremos MySQL como ejemplo.
// Si usas PostgreSQL, cambia 'mysql' por 'pgsql'.
$DB_TIPO = 'mysql'; 
$DB_HOST = 'localhost'; // Normalmente es 'localhost' si usas XAMPP/WAMP/etc.
$DB_NOMBRE = 'aventureros_sa'; // Nombre de tu base de datos.
$DB_USUARIO = 'root'; // Usuario de la BD. CÁMBIALO para producción.
$DB_PASSWORD = ''; // Contraseña de la BD. CÁMBIALA para producción.
$DB_CHARSET = 'utf8mb4';

// Cadena de Conexión (DSN)
$dsn = "$DB_TIPO:host=$DB_HOST;dbname=$DB_NOMBRE;charset=$DB_CHARSET";

try {
    // 1. Crear una instancia de PDO (establecer la conexión)
    $pdo = new PDO($dsn, $DB_USUARIO, $DB_PASSWORD);
    
    // 2. Configurar el modo de error para que PDO lance excepciones
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // echo "Conexión a la base de datos exitosa."; // Puedes descomentar esto para probar
    
} catch (PDOException $e) {
    // 3. Manejar cualquier error de conexión
    // En un entorno real, NO muestres $e->getMessage() al usuario.
    die("Error de conexión a la BD: " . $e->getMessage()); 
}

// Nota: La variable $pdo contiene el objeto que usarás para todas las consultas SQL.
?>