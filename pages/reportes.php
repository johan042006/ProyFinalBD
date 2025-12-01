<?php
    // Simulamos datos estadísticos que vendrían de la Base de Datos
    $viajes_hoy = 42;
    $ingresos_totales = 1250000;
    $conductores_activos = 18;
    $promedio_hora = 6;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Dashboard | MoviApp</title>
    
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/reportes.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-reportes">
            
            <h2 class="titulo-dashboard">Resumen General</h2>

            <div class="grid-stats">
                
                <article class="tarjeta-stat">
                    <div class="icono-stat-contenedor bg-azul">
                        <img src="https://api.iconify.design/lucide-route.svg?color=%232563eb" class="icono-stat">
                    </div>
                    <div class="info-stat">
                        <span class="etiqueta-stat">Viajes hoy</span>
                        <span class="valor-stat"><?php echo $viajes_hoy; ?></span>
                    </div>
                </article>

                <article class="tarjeta-stat">
                    <div class="icono-stat-contenedor bg-verde">
                        <img src="https://api.iconify.design/lucide-coins.svg?color=%23166534" class="icono-stat">
                    </div>
                    <div class="info-stat">
                        <span class="etiqueta-stat">Ingresos aprox.</span>
                        <span class="valor-stat">$<?php echo number_format($ingresos_totales, 0, ',', '.'); ?></span>
                    </div>
                </article>

                <article class="tarjeta-stat">
                    <div class="icono-stat-contenedor bg-naranja">
                        <img src="https://api.iconify.design/lucide-users.svg?color=%239a3412" class="icono-stat">
                    </div>
                    <div class="info-stat">
                        <span class="etiqueta-stat">Conductores activos</span>
                        <span class="valor-stat"><?php echo $conductores_activos; ?></span>
                    </div>
                </article>

                <article class="tarjeta-stat">
                    <div class="icono-stat-contenedor bg-morado">
                        <img src="https://api.iconify.design/lucide-clock.svg?color=%237e22ce" class="icono-stat">
                    </div>
                    <div class="info-stat">
                        <span class="etiqueta-stat">Viajes por hora</span>
                        <span class="valor-stat">~<?php echo $promedio_hora; ?></span>
                    </div>
                </article>

            </div>

            <div class="grid-graficos">
                
                <article class="tarjeta-grafico">
                    <div class="cabecera-grafico">
                        <h3 class="titulo-grafico">Actividad por Hora</h3>
                        <button class="btn-filtro">Hoy</button>
                    </div>
                    <div class="area-grafico-placeholder">
                        [ Gráfico de Barras: Viajes vs Hora ]
                    </div>
                </article>

                <article class="tarjeta-grafico">
                    <div class="cabecera-grafico">
                        <h3 class="titulo-grafico">Ingresos Semanales</h3>
                        <button class="btn-filtro">Semana</button>
                    </div>
                    <div class="area-grafico-placeholder">
                        [ Gráfico de Línea: Dinero vs Días ]
                    </div>
                </article>

            </div>

        </section>
    </main>

</body>
</html>