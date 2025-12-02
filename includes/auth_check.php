<?php
session_start();

// Comprueba si existe una sesión de usuario.
// Si no, redirige a la página de login.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
