<?php
// Datos simulados de conductores
$conductores = [
    [
        "id" => 1,
        "nombre" => "Laura Pérez",
        "doc" => "CC 1.023.456.789",
        "vehiculo" => "Chevrolet Spark",
        "placa" => "XYZ-987",
        "estado" => "Disponible",
        "foto" => "https://app.grapesjs.com/api/assets/random-image?query=%22woman%20driver%22&w=80&h=80"
    ],
    [
        "id" => 2,
        "nombre" => "Juan Gómez",
        "doc" => "CE 908.776.543",
        "vehiculo" => "Renault Logan",
        "placa" => "JKL-456",
        "estado" => "Ocupado",
        "foto" => "https://app.grapesjs.com/api/assets/random-image?query=%22man%20driver%22&w=80&h=80"
    ],
    [
        "id" => 3,
        "nombre" => "Carlos Mendoza",
        "doc" => "CC 80.123.456",
        "vehiculo" => "Toyota Corolla",
        "placa" => "ABC-123",
        "estado" => "Ocupado",
        "foto" => "https://app.grapesjs.com/api/assets/random-image?query=%22driver%22&w=80&h=80"
    ],
    [
        "id" => 4,
        "nombre" => "Ana María Rios",
        "doc" => "CC 52.987.654",
        "vehiculo" => "Kia Picanto",
        "placa" => "MNO-901",
        "estado" => "Disponible",
        "foto" => "https://app.grapesjs.com/api/assets/random-image?query=%22young%20woman%22&w=80&h=80"
    ]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conductores | MoviApp</title>
    
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/conductores.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-conductores">
            
            <div class="encabezado-tabla">
                <h2 class="titulo-seccion">Directorio de Conductores</h2>
                <button class="boton-agregar">+ Agregar conductor</button>
            </div>

            <div class="contenedor-tabla">
                <table class="tabla-conductores">
                    <thead class="tabla-head">
                        <tr>
                            <th class="celda-header">Foto</th>
                            <th class="celda-header">Nombre</th>
                            <th class="celda-header">Documento</th>
                            <th class="celda-header">Vehículo / Placa</th>
                            <th class="celda-header">Estado</th>
                            <th class="celda-header">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php foreach($conductores as $conductor): ?>
                            <tr class="fila-tabla">
                                
                                <td class="celda">
                                    <img src="<?php echo $conductor['foto']; ?>" alt="Foto" class="foto-tabla">
                                </td>
                                
                                <td class="celda">
                                    <div class="texto-destacado"><?php echo $conductor['nombre']; ?></div>
                                </td>
                                
                                <td class="celda">
                                    <span class="texto-secundario"><?php echo $conductor['doc']; ?></span>
                                </td>
                                
                                <td class="celda">
                                    <div class="texto-destacado"><?php echo $conductor['vehiculo']; ?></div>
                                    <div class="texto-secundario"><?php echo $conductor['placa']; ?></div>
                                </td>
                                
                                <td class="celda">
                                    <?php if($conductor['estado'] == 'Disponible'): ?>
                                        <span class="badge badge-disponible">Disponible</span>
                                    <?php else: ?>
                                        <span class="badge badge-ocupado">Ocupado</span>
                                    <?php endif; ?>
                                </td>
                                
                                <td class="celda">
                                    <div class="grupo-acciones">
                                        <button class="btn-accion">Ver</button>
                                        <button class="btn-accion">Editar</button>
                                        <button class="btn-accion btn-rojo">Suspender</button>
                                    </div>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                        
                    </tbody>
                </table>
            </div>

        </section>
    </main>

</body>
</html>