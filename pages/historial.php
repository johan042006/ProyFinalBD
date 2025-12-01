<?php
// Simulamos datos de una base de datos (Array PHP)
$viajes = [
    [
        "fecha" => "12 Oct 2025 • 18:30",
        "origen" => "Calle 45 #12-30",
        "destino" => "Aeropuerto Internacional",
        "precio" => "$30.200",
        "estrellas" => 4
    ],
    [
        "fecha" => "10 Oct 2025 • 08:15",
        "origen" => "Centro Comercial",
        "destino" => "Universidad Nacional",
        "precio" => "$12.800",
        "estrellas" => 5
    ],
    [
        "fecha" => "05 Oct 2025 • 21:00",
        "origen" => "Restaurante La Plaza",
        "destino" => "Casa",
        "precio" => "$15.500",
        "estrellas" => 5
    ],
    [
        "fecha" => "01 Oct 2025 • 07:30",
        "origen" => "Casa",
        "destino" => "Oficina Centro",
        "precio" => "$18.000",
        "estrellas" => 3
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Viajes | MoviApp</title>
    
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/historial.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-historial">
            
            <div class="encabezado-historial">
                <h2 class="texto-titulo titulo-historial">Viajes Recientes</h2>
                
                <button class="boton-exportar">
                    <img src="https://api.iconify.design/lucide-file-down.svg?color=%230f172a" width="18">
                    <span>Exportar todo</span>
                </button>
            </div>

            <div class="grilla-historial">

                <?php foreach($viajes as $viaje): ?>
                    
                    <article class="tarjeta-viaje">
                        
                        <div class="cabecera-viaje">
                            <span class="fecha-viaje"><?php echo $viaje['fecha']; ?></span>
                            
                            <div class="rating-viaje">
                                <?php for($i = 0; $i < 5; $i++): ?>
                                    <?php if($i < $viaje['estrellas']): ?>
                                        <img src="https://api.iconify.design/lucide-star.svg?color=%23f59e0b" width="14">
                                    <?php else: ?>
                                        <img src="https://api.iconify.design/lucide-star.svg?color=%23cbd5e1" width="14">
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div class="ruta-viaje">
                            <span><?php echo $viaje['origen']; ?></span>
                            <span class="icono-flecha">→</span>
                            <span style="font-weight: 500;"><?php echo $viaje['destino']; ?></span>
                        </div>

                        <div class="pie-viaje">
                            <span class="costo-viaje"><?php echo $viaje['precio']; ?></span>
                            
                            <div class="acciones-viaje">
                                <button class="btn-detalle">Ver detalle</button>
                                <button class="btn-detalle">Factura</button>
                            </div>
                        </div>

                    </article>

                <?php endforeach; ?>
                </div>

        </section>
    </main>

</body>
</html>