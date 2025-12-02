<?php include '../includes/auth_check.php'; ?>
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
                </div>

            </div>
        </section>

    </main>

</body>
</html>