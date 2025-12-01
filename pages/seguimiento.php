<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguimiento de Viaje | MoviApp</title>
    
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/seguimiento.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-seguimiento">
            
            <div class="layout-seguimiento">
                
                <div class="contenedor-mapa-real">
                    <img src="https://app.grapesjs.com/api/assets/random-image?query=%22gps%20navigation%20map%22&w=1280&h=800" alt="Mapa en tiempo real" class="imagen-mapa-real">
                </div>

                <aside class="columna-lateral">
                    
                    <div class="tarjeta-cronologia">
                        <h2 class="texto-titulo titulo-seccion">Estado del viaje</h2>
                        
                        <ul class="lista-cronologia">
                            
                            <li class="item-cronologia">
                                <img src="https://api.iconify.design/lucide-car.svg?color=%230f172a" class="icono-estado">
                                <div class="contenido-estado">
                                    <span class="titulo-estado">Conductor en camino</span>
                                    <span class="subtitulo-estado">ETA: 5 min</span>
                                </div>
                            </li>

                            <li class="item-cronologia">
                                <img src="https://api.iconify.design/lucide-check-circle.svg?color=%2310b981" class="icono-estado icono-verde">
                                <div class="contenido-estado">
                                    <span class="titulo-estado">Llegó al punto</span>
                                    <span class="subtitulo-estado">Esperando al pasajero</span>
                                </div>
                            </li>

                            <li class="item-cronologia">
                                <img src="https://api.iconify.design/lucide-navigation.svg?color=%230f172a" class="icono-estado">
                                <div class="contenido-estado">
                                    <span class="titulo-estado">Viaje iniciado</span>
                                    <span class="subtitulo-estado">Ruta hacia el destino</span>
                                </div>
                            </li>

                            <li class="item-cronologia">
                                <img src="https://api.iconify.design/lucide-flag.svg?color=%2394a3b8" class="icono-estado">
                                <div class="contenido-estado">
                                    <span class="titulo-estado" style="color:#94a3b8">Finaliza viaje</span>
                                    <span class="subtitulo-estado">Destino final</span>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="tarjeta-ruta">
                        <h2 class="texto-titulo titulo-seccion">Ruta y duración</h2>
                        
                        <div class="info-ruta">
                            <div class="fila-ruta">
                                <span class="etiqueta-ruta">Duración estimada</span>
                                <span class="valor-ruta">22-28 min</span>
                            </div>
                            <div class="fila-ruta">
                                <span class="etiqueta-ruta">Distancia</span>
                                <span class="valor-ruta">9.4 km</span>
                            </div>
                            <div class="fila-ruta">
                                <span class="etiqueta-ruta">Tráfico</span>
                                <span class="valor-ruta" style="color: #f59e0b;">Medio</span>
                            </div>
                        </div>

                        <div class="contenedor-mapa-pequeno">
                            <img src="https://app.grapesjs.com/api/assets/random-image?query=%22route%20map%22&w=400&h=200" alt="Vista general de ruta" class="imagen-mapa-pequeno">
                        </div>

                        <div style="margin-top: 1.5rem;">
                            <a href="historial.php" class="boton-primario">
                                Finalizar Viaje (Demo)
                            </a>
                        </div>
                    </div>

                </aside>

            </div>
        </section>
    </main>

</body>
</html>