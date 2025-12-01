<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viaje Confirmado | MoviApp</title>
    
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/solicitud.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-confirmacion">
            
            <div class="layout-confirmacion">
                
                <div class="columna-info">
                    
                    <div class="tarjeta-conductor">
                        <div class="encabezado-conductor">
                            <img src="https://app.grapesjs.com/api/assets/random-image?query=%22driver%20portrait%22&w=128&h=128" alt="Foto Conductor" class="foto-conductor">
                            
                            <div class="info-texto">
                                <h3 class="nombre-conductor">Carlos Mendoza</h3>
                                <div class="info-rating">
                                    <img src="https://api.iconify.design/lucide-star.svg?color=%23f59e0b" width="14" height="14">
                                    <span>4.9 (320)</span>
                                    <span class="punto-separador">•</span>
                                    <span>Toyota Corolla</span>
                                </div>
                                <div class="info-rating">
                                    <span>Blanco</span>
                                    <span class="punto-separador">•</span>
                                    <span style="color: #0f172a; font-weight:600;">ABC-123</span>
                                </div>
                                <span class="etiqueta-llegada">Llegada en 7 min</span>
                            </div>
                        </div>

                        <div class="acciones-conductor">
                            <a href="home.php" class="boton-accion boton-cancelar">
                                Cancelar
                            </a>
                            
                            <button class="boton-accion">
                                <img src="https://api.iconify.design/lucide-phone.svg?color=%230f172a" width="16">
                                Llamar
                            </button>
                            
                            <button class="boton-accion">
                                <img src="https://api.iconify.design/lucide-message-circle.svg?color=%230f172a" width="16">
                                Chat
                            </button>
                        </div>
                    </div>

                    <div class="tarjeta-detalle">
                        <h3 class="titulo-detalle">Detalle del viaje</h3>
                        
                        <div class="fila-detalle">
                            <span class="etiqueta-detalle">Recogida</span>
                            <span class="valor-detalle">Calle 45 #12-30</span>
                        </div>
                        
                        <div class="fila-detalle">
                            <span class="etiqueta-detalle">Destino</span>
                            <span class="valor-detalle">Aeropuerto Int.</span>
                        </div>
                        
                        <div class="fila-detalle">
                            <span class="etiqueta-detalle">Método de pago</span>
                            <span class="valor-detalle">Efectivo</span>
                        </div>

                        <div class="fila-detalle">
                            <span class="etiqueta-detalle">Tarifa estimada</span>
                            <span class="valor-detalle" style="font-size:1.1rem;">$28.000</span>
                        </div>
                        
                        <div style="margin-top: 1rem;">
                            <a href="seguimiento.php" class="boton-primario">
                                Ver mapa en tiempo real
                            </a>
                        </div>
                    </div>

                </div>

                <div class="contenedor-mapa-confirmacion">
                    <img src="https://app.grapesjs.com/api/assets/random-image?query=%22navigation%20map%22&w=1280&h=800" alt="Mapa de ruta" class="imagen-mapa-ruta">
                </div>

            </div>

        </section>
    </main>

</body>
</html>