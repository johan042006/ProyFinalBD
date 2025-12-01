<?php
// Datos simulados de la flota
$vehiculos = [
    [
        "id" => 101,
        "placa" => "ABC-123",
        "marca" => "Toyota",
        "modelo" => "Corolla 2023",
        "conductor" => "Carlos Mendoza",
        "servicio" => "Normal"
    ],
    [
        "id" => 102,
        "placa" => "XYZ-987",
        "marca" => "Chevrolet",
        "modelo" => "Spark GT",
        "conductor" => "Laura Pérez",
        "servicio" => "Normal"
    ],
    [
        "id" => 103,
        "placa" => "PRE-555",
        "marca" => "BMW",
        "modelo" => "Serie 3",
        "conductor" => "Roberto Alcañiz",
        "servicio" => "Premium"
    ],
    [
        "id" => 104,
        "placa" => "URG-911",
        "marca" => "Yamaha",
        "modelo" => "MT-09 (Moto)",
        "conductor" => "Felipe Rapido",
        "servicio" => "Urgente"
    ],
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehículos | MoviApp</title>
    
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/vehiculos.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-vehiculos">
            
            <div class="encabezado-vehiculos">
                <h2 class="titulo-vehiculos">Flota de Vehículos</h2>
                <button class="boton-nuevo-vehiculo">Añadir vehículo</button>
            </div>

            <div class="contenedor-tabla-vehiculos">
                <table class="tabla-flota">
                    <thead class="head-flota">
                        <tr>
                            <th class="th-flota">Placa</th>
                            <th class="th-flota">Marca / Modelo</th>
                            <th class="th-flota">Conductor Asignado</th>
                            <th class="th-flota">Tipo Servicio</th>
                            <th class="th-flota">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <?php foreach($vehiculos as $carro): ?>
                            <tr class="fila-flota">
                                
                                <td class="td-flota">
                                    <span class="texto-placa"><?php echo $carro['placa']; ?></span>
                                </td>
                                
                                <td class="td-flota">
                                    <div class="texto-marca"><?php echo $carro['marca']; ?></div>
                                    <div class="texto-modelo"><?php echo $carro['modelo']; ?></div>
                                </td>
                                
                                <td class="td-flota">
                                    <?php echo $carro['conductor']; ?>
                                </td>
                                
                                <td class="td-flota">
                                    <?php 
                                        $clase_servicio = "";
                                        if($carro['servicio'] == 'Premium') {
                                            $clase_servicio = "servicio-premium";
                                        } elseif($carro['servicio'] == 'Urgente') {
                                            $clase_servicio = "servicio-urgente";
                                        } else {
                                            $clase_servicio = "servicio-normal";
                                        }
                                    ?>
                                    <span class="etiqueta-servicio <?php echo $clase_servicio; ?>">
                                        <?php echo $carro['servicio']; ?>
                                    </span>
                                </td>
                                
                                <td class="td-flota">
                                    <div class="acciones-vehiculo">
                                        <button class="btn-accion-v" title="Ver detalle">
                                            <img src="https://api.iconify.design/lucide-eye.svg?color=%2364748b" width="16">
                                        </button>
                                        <button class="btn-accion-v" title="Editar">
                                            <img src="https://api.iconify.design/lucide-pencil.svg?color=%2364748b" width="16">
                                        </button>
                                        <button class="btn-accion-v btn-eliminar" title="Eliminar">
                                            <img src="https://api.iconify.design/lucide-trash-2.svg?color=%23ef4444" width="16">
                                        </button>
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