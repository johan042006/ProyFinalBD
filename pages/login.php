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

            <form action="home.php" method="GET"> 
                
                <div class="grupo-formulario">
                    <label for="email" class="etiqueta">Correo electrónico</label>
                    <div class="contenedor-input">
                        <img src="https://api.iconify.design/lucide-mail.svg?color=%230f172a" class="icono-input" alt="icono correo">
                        <input type="email" id="email" name="email" class="campo-entrada" placeholder="ejemplo@correo.com" required>
                    </div>
                </div>

                <div class="grupo-formulario">
                    <label for="password" class="etiqueta">Contraseña</label>
                    <div class="contenedor-input">
                        <img src="https://api.iconify.design/lucide-lock.svg?color=%230f172a" class="icono-input" alt="icono candado">
                        <input type="password" id="password" name="password" class="campo-entrada" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="opciones-formulario">
                    <label class="recordarme">
                        <input type="checkbox">
                        <span>Recordarme</span>
                    </label>
                    <a href="#" class="enlace-olvido">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="boton-primario">
                    Iniciar Sesión
                </button>

            </form>

            <div class="pie-login">
                ¿No tienes una cuenta? <a href="#" class="enlace-registro">Regístrate gratis</a>
            </div>

        </main>
    </div>

</body>
</html>