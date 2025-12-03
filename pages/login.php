<?php
session_start();

// Si ya hay una sesión activa, redirigir a home.php
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}

include '../includes/conexion.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($nombre_usuario) || empty($password)) {
        $error_message = "Por favor, ingresa tu usuario y contraseña.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM USUARIO WHERE nombre_usuario = ?");
        $stmt->execute([$nombre_usuario]);
        $usuario = $stmt->fetch();

        if ($usuario && $password === $usuario['contraseña']) {
            if ($usuario['estado'] == 'activo') {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $usuario['id_usuario'];
                $_SESSION['user_name'] = $usuario['nombre_usuario'];
                $_SESSION['user_role'] = $usuario['rol'];
                header("Location: home.php");
                exit();
            } else {
                $error_message = "Tu cuenta ha sido desactivada. Contacta al administrador.";
            }
        } else {
            $error_message = "Usuario o contraseña incorrectos.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>
    <div class="contenedor-login">
        <main class="tarjeta-login">
            <div class="encabezado-login">
                <div class="contenedor-marca">
                    <img src="https://api.iconify.design/lucide-car.svg?color=%230f172a" alt="Logo MoviApp" class="icono-marca">
                    <span class="texto-titulo nombre-marca">MoviApp</span>
                </div>
                <h1 class="texto-titulo titulo-bienvenida">¡Hola de nuevo!</h1>
                <p class="subtitulo-login">Ingresa tus datos para continuar</p>
            </div>

            <div class="alerta-error" style="padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; background-color: #ffedd5; color: #9a3412; border: 1px solid #fecaca; font-weight: bold; text-align: center;">
                ADVERTENCIA DE SEGURIDAD: Este sistema de login utiliza contraseñas en texto plano. NO ES SEGURO.
            </div>
            <?php if (!empty($error_message)): ?>
                <div class="alerta-error" style="padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="grupo-formulario">
                    <label for="nombre_usuario" class="etiqueta">Nombre de Usuario</label>
                    <div class="contenedor-input">
                        <img src="https://api.iconify.design/lucide-user.svg?color=%230f172a" class="icono-input" alt="icono usuario">
                        <input type="text" id="nombre_usuario" name="nombre_usuario" class="campo-entrada" placeholder="Tu nombre de usuario" required>
                    </div>
                </div>

                <div class="grupo-formulario">
                    <label for="password" class="etiqueta">Contraseña</label>
                    <div class="contenedor-input">
                        <img src="https://api.iconify.design/lucide-lock.svg?color=%230f172a" class="icono-input" alt="icono candado">
                        <input type="password" id="password" name="password" class="campo-entrada" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="boton-primario">
                    Iniciar Sesión
                </button>

                <div class="enlace-registro">
                    <p>¿No tienes una cuenta? <a href="registro.php">Regístrate</a></p>
                </div>
            </form>
        </main>
    </div>
</body>
</html>