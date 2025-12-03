<?php
// Uso:
// 1. Cambia el valor de $password_a_hashear por la contraseña que deseas hashear.
// 2. Guarda el archivo.
// 3. Abre este archivo en tu navegador (ej. http://localhost/ProyFinalBD/hash_password.php).
// 4. Copia el hash que se muestra en pantalla.
// 5. Pega el hash en la columna 'contraseña' de tu usuario en la tabla 'USUARIO' de la base de datos.
// 6. Elimina este archivo de tu servidor.

$password_a_hashear = 'tu_nueva_contraseña_aqui';

// No modifiques nada debajo de esta línea
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    die('Este script requiere PHP 7.4.0 o superior.');
}

if ($password_a_hashear === 'tu_nueva_contraseña_aqui') {
    echo "Por favor, edita este archivo y cambia el valor de la variable \$password_a_hashear por tu nueva contraseña.";
} else {
    $hash = password_hash($password_a_hashear, PASSWORD_DEFAULT);

    echo "<h1>Hash de Contraseña Generado</h1>";
    echo "<p>Tu contraseña a hashear es: <strong>" . htmlspecialchars($password_a_hashear, ENT_QUOTES, 'UTF-8') . "</strong></p>";
    echo "<p>El hash generado es:</p>";
    echo "<textarea readonly style='width: 100%; height: 60px; font-size: 1.2rem;'>" . htmlspecialchars($hash, ENT_QUOTES, 'UTF-8') . "</textarea>";
    echo "<p><strong>Instrucciones:</strong></p>";
    echo "<ol>";
    echo "<li>Copia el hash de arriba.</li>";
    echo "<li>Actualiza la columna <code>contraseña</code> en tu tabla <code>USUARIO</code> para el usuario <code>juan</code> con este hash.</li>";
    echo "<li><strong>MUY IMPORTANTE:</strong> Elimina este archivo (<code>hash_password.php</code>) de tu servidor después de usarlo.</li>";
    echo "</ol>";
}
?>