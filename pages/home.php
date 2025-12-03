<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Viaje | MoviApp</title>
    
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/home.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <?php if (!empty($mensaje_estado)): ?>
            <div class="alerta-estado" style="padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; text-align: center;">
                <?php echo $mensaje_estado; ?>
            </div>
        <?php endif; ?>
        <?php
        // Determinar el rol del usuario desde la sesión
        $rol = $_SESSION['user_role'] ?? 'cliente'; // Asumir 'cliente' si no hay rol

        // Cargar la vista correspondiente al rol
        switch ($rol) {
            case 'administrador':
                // Vista para el Administrador
                echo '
                <section class="seccion-home">
                    <div class="layout-home" style="justify-content: center;">
                        <div class="tarjeta-formulario" style="width: 100%; max-width: 800px; text-align: center;">
                            <h2 class="texto-titulo" style="margin-bottom: 1rem;">Panel de Administrador</h2>
                            <p class="subtitulo-login" style="margin-bottom: 2rem;">Bienvenido, ' . htmlspecialchars($_SESSION['user_name']) . '. Selecciona una opción para empezar.</p>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                                <a href="conductores.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-users.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Gestionar Conductores</span>
                                </a>
                                <a href="vehiculos.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-car.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Gestionar Vehículos</span>
                                </a>
                                <a href="reportes.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-bar-chart-3.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Ver Reportes</span>
                                </a>
                                <a href="pagos.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-dollar-sign.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Procesar Pagos</span>
                                </a>
                                <a href="historial.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-history.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Historial de Viajes</span>
                                </a>
                                <a href="configuracion.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-settings.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Configuración</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>';
                break;

            case 'conductor':
                // Vista para el Conductor
                echo '
                <section class="seccion-home">
                    <div class="layout-home" style="justify-content: center;">
                        <div class="tarjeta-formulario" style="width: 100%; max-width: 600px; text-align: center;">
                            <h2 class="texto-titulo" style="margin-bottom: 1rem;">Panel de Conductor</h2>
                            <p class="subtitulo-login" style="margin-bottom: 2rem;">Bienvenido, ' . htmlspecialchars($_SESSION['user_name']) . '. Aquí están tus opciones.</p>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                                <a href="seguimiento.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-map.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Ver Viajes Asignados</span>
                                </a>
                                <a href="historial.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-history.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Mi Historial</span>
                                </a>
                                <a href="registrar_vehiculo_conductor.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-car.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Registrar Vehículo</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>';
                break;

            default: // Para 'cliente' y cualquier otro rol no definido
                // Vista para el Cliente (el formulario de pedir viaje original)
                echo '
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
                                <form action="solicitud.php" method="GET">
                                    <div class="grupo-origen-destino">
                                        <div class="grupo-formulario">
                                            <label class="etiqueta">Origen</label>
                                            <div class="contenedor-input">
                                                <img src="https://api.iconify.design/lucide-map-pin.svg?color=%230f172a" class="icono-input">
                                                <input type="text" class="campo-entrada" placeholder="Ej. Calle 45 #12-30">
                                            </div>
                                            <div class="sugerencias-visuales">Casa • Trabajo • Último destino</div>
                                        </div>
                                        <div class="grupo-formulario">
                                            <label class="etiqueta">Destino</label>
                                            <div class="contenedor-input">
                                                <img src="https://api.iconify.design/lucide-flag.svg?color=%230f172a" class="icono-input">
                                                <input type="text" class="campo-entrada" placeholder="Ej. Aeropuerto">
                                            </div>
                                            <div class="sugerencias-visuales">Aeropuerto • Centro • Universidad</div>
                                        </div>
                                    </div>
                                    <div class="grupo-tipo-servicio">
                                        <label class="etiqueta">Servicio</label>
                                        <div class="opciones-servicio">
                                            <button type="button" class="boton-servicio seleccionado">Normal</button>
                                            <button type="button" class="boton-servicio">Urgente</button>
                                            <button type="button" class="boton-servicio">Premium</button>
                                        </div>
                                    </div>
                                    <div class="tarjeta-estimacion">
                                        <div class="fila-tarifa">
                                            <div style="display:flex; gap:0.5rem; align-items:center;">
                                                <img src="https://api.iconify.design/lucide-wallet.svg?color=%230f172a" width="20">
                                                <span>Estimado</span>
                                            </div>
                                            <span class="valor-tarifa">$25.000</span>
                                        </div>
                                        <div class="detalles-tarifa">18-24 min • 7.2 km</div>
                                    </div>
                                    <div class="botones-accion">
                                        <button type="submit" class="boton-primario">Solicitar viaje</button>
                                        <button type="button" class="boton-secundario">Programar</button>
                                    </div>
                                </form>
                            </div>
                        </div>                </div>
                </section>';
                break;
        }
        ?>
    </main>

</body>
</html>