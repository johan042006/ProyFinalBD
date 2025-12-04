<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

if ($_SESSION['user_role'] !== 'cliente') {
    header("Location: home.php");
    exit();
}

$origen = $_GET['origen'] ?? '';
$destino = $_GET['destino'] ?? '';
$id_categoria = $_GET['id_categoria'] ?? '1'; // Default a 'Normal'

if (empty($origen) || empty($destino)) {
    header("Location: home.php");
    exit();
}

try {
    // Usamos la vista para obtener todos los conductores y su estado de disponibilidad
    $stmt = $pdo->prepare("
        SELECT 
            vcd.id_conductor, 
            vcd.nombre, 
            vcd.disponibilidad, 
            vcd.disponible_en,
            veh.marca, 
            veh.modelo, 
            veh.placa
        FROM VISTA_CONDUCTORES_DISPONIBILIDAD vcd
        JOIN VEHICULO veh ON vcd.id_conductor = veh.id_conductor_titular
        WHERE vcd.estado_usuario = 'activo'
        ORDER BY vcd.disponibilidad DESC, vcd.nombre ASC
    ");
    $stmt->execute();
    $conductores = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error al buscar conductores: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Conductor | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/historial.css"> <!-- Reutilizamos estilos -->
    <style>
        .conductor-ocupado {
            opacity: 0.6;
            background-color: #f8f9fa;
        }
        .pie-viaje {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .estado-conductor .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            display: inline-block;
        }
        .estado-conductor .badge-disponible { background-color: #dcfce7; color: #166534; }
        .estado-conductor .badge-ocupado { background-color: #fee2e2; color: #991b1b; }
        .estado-conductor .disponible-en {
            font-size: 0.8rem;
            color: #555;
            margin-left: 0.5rem;
        }
        .btn-primario-accion:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="contenido-principal">
        <section class="seccion-historial">
            <div class="encabezado-historial">
                <h2 class="texto-titulo titulo-historial">Conductores Disponibles</h2>
            </div>

            <?php if (empty($conductores)): ?>
                <div class="tarjeta-viaje" style="text-align: center; padding: 2rem;">
                    No hay conductores registrados o activos en el sistema.
                </div>
            <?php else: ?>
                <div class="grilla-historial">
                    <?php foreach ($conductores as $conductor): 
                        $estaDisponible = ($conductor['disponibilidad'] == 'Disponible');
                    ?>
                        <article class="tarjeta-viaje <?php echo !$estaDisponible ? 'conductor-ocupado' : ''; ?>">
                            <form action="solicitud.php" method="POST">
                                <input type="hidden" name="origen" value="<?php echo htmlspecialchars($origen); ?>">
                                <input type="hidden" name="destino" value="<?php echo htmlspecialchars($destino); ?>">
                                <input type="hidden" name="id_categoria" value="<?php echo htmlspecialchars($id_categoria); ?>">
                                <input type="hidden" name="id_conductor" value="<?php echo htmlspecialchars($conductor['id_conductor']); ?>">
                                
                                <div class="detalles-actores">
                                    <div>Conductor: <strong><?php echo htmlspecialchars($conductor['nombre']); ?></strong></div>
                                    <div>Vehículo: <strong><?php echo htmlspecialchars($conductor['marca'] . ' ' . $conductor['modelo']); ?></strong></div>
                                    <div>Placa: <strong><?php echo htmlspecialchars($conductor['placa']); ?></strong></div>
                                </div>

                                <div class="pie-viaje">
                                    <div class="estado-conductor">
                                        <?php if ($estaDisponible): ?>
                                            <span class="badge badge-disponible">Disponible</span>
                                        <?php else: ?>
                                            <span class="badge badge-ocupado">Ocupado</span>
                                            <small class="disponible-en">
                                                Libre aprox. <?php 
                                                    $fecha = new DateTime($conductor['disponible_en']);
                                                    echo $fecha->format('H:i'); 
                                                ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <button type="submit" class="btn-primario-accion" <?php echo !$estaDisponible ? 'disabled' : ''; ?>>
                                        <?php echo $estaDisponible ? 'Seleccionar' : 'No disponible'; ?>
                                    </button>
                                </div>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
