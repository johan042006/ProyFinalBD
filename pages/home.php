<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';
$rol = $_SESSION['user_role'] ?? 'cliente';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/home.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <?php if (!empty($_SESSION['mensaje_error'])): ?>
            <div class="alerta-estado" style="background-color: #fee2e2; color: #991b1b; border-color: #fecaca; text-align: center; margin-bottom: 1.5rem;">
                <?php echo $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?>
            </div>
        <?php endif; ?>

        <?php switch ($rol):
            case 'administrador': ?>
                <section class="seccion-home">
                    <div class="layout-home" style="justify-content: center;">
                        <div class="tarjeta-formulario" style="width: 100%; max-width: 800px; text-align: center;">
                            <h2 class="texto-titulo" style="margin-bottom: 1rem;">Panel de Administrador</h2>
                            <p class="subtitulo-login" style="margin-bottom: 2rem;">Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Selecciona una opción para empezar.</p>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                                <a href="conductores.php" class="boton-primario">Gestionar Conductores</a>
                                <a href="vehiculos.php" class="boton-primario">Gestionar Vehículos</a>
                                <a href="reportes.php" class="boton-primario">Ver Reportes</a>
                                <a href="pagos.php" class="boton-primario">Gestionar Pagos</a>
                                <a href="historial.php" class="boton-primario">Historial de Viajes</a>
                                <a href="configuracion.php" class="boton-primario">Configuración</a>
                            </div>
                        </div>
                    </div>
                </section>
                <?php break; ?>

            <?php case 'conductor': ?>
                <section class="seccion-home">
                    <div class="layout-home" style="justify-content: center;">
                        <div class="tarjeta-formulario" style="width: 100%; max-width: 600px; text-align: center;">
                            <h2 class="texto-titulo" style="margin-bottom: 1rem;">Panel de Conductor</h2>
                            <p class="subtitulo-login" style="margin-bottom: 2rem;">Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. Aquí están tus opciones.</p>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                                <a href="seguimiento.php" class="boton-primario">Ver Viajes Asignados</a>
                                <a href="historial.php" class="boton-primario">Mi Historial</a>
                                <a href="perfil_conductor.php" class="boton-primario">Mi Perfil y Vehículo</a>
                            </div>
                        </div>
                    </div>
                </section>
                <?php break; ?>

            <?php default: // Cliente ?>
                <section class="seccion-home">
                    <div class="layout-home">
                        <div class="contenedor-mapa">
                            <div class="wrapper-mapa">
                                <img src="https://app.grapesjs.com/api/assets/random-image?query=%22city%20map%22&w=1280&h=720" alt="Mapa de la ciudad" class="imagen-mapa-placeholder">
                            </div>
                        </div>
                        <div class="contenedor-formulario-viaje">
                            <div class="tarjeta-formulario">
                                <h2 class="texto-titulo titulo-formulario">Pedir un viaje</h2>
                                <form action="seleccionar_conductor.php" method="GET">
                                    <input type="hidden" name="id_categoria" id="id_categoria" value="1">
                                    <div class="grupo-origen-destino">
                                        <div class="grupo-formulario">
                                            <label class="etiqueta">Origen</label>
                                            <input type="text" name="origen" class="campo-entrada" placeholder="Ej. Calle 45 #12-30" required>
                                        </div>
                                        <div class="grupo-formulario">
                                            <label class="etiqueta">Destino</label>
                                            <input type="text" name="destino" class="campo-entrada" placeholder="Ej. Aeropuerto" required>
                                        </div>
                                    </div>
                                    <div class="grupo-tipo-servicio">
                                        <label class="etiqueta">Categoría del Servicio</label>
                                        <div class="opciones-servicio">
                                            <button type="button" class="boton-servicio seleccionado" data-categoria="1">Normal</button>
                                            <button type="button" class="boton-servicio" data-categoria="2">Especial</button>
                                            <button type="button" class="boton-servicio" data-categoria="3">Urgente</button>
                                        </div>
                                    </div>
                                    <div class="botones-accion">
                                        <button type="submit" class="boton-primario">Buscar Conductor</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
                <?php break; ?>
        <?php endswitch; ?>
    </main>

    <?php if ($rol === 'cliente'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const botonesServicio = document.querySelectorAll('.boton-servicio');
            const categoriaInput = document.getElementById('id_categoria');

            if (botonesServicio.length > 0 && categoriaInput) {
                botonesServicio.forEach(boton => {
                    boton.addEventListener('click', function() {
                        botonesServicio.forEach(b => b.classList.remove('seleccionado'));
                        this.classList.add('seleccionado');
                        categoriaInput.value = this.dataset.categoria;
                    });
                });
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>