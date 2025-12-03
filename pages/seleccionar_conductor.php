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
    // Encontrar conductores que NO están en un servicio activo ('asignado' o 'en_ruta')
    $stmt = $pdo->prepare("
        SELECT c.id_conductor, c.nombre, v.marca, v.modelo, v.placa
        FROM CONDUCTOR c
        JOIN VEHICULO v ON c.id_conductor = v.id_conductor_titular
        WHERE c.id_conductor NOT IN (
            SELECT s.id_conductor FROM SERVICIO s WHERE s.estado IN ('asignado', 'en_ruta') AND s.id_conductor IS NOT NULL
        )
        AND c.id_usuario IN (SELECT id_usuario FROM USUARIO WHERE estado = 'activo')
    ");
    $stmt->execute();
    $conductores_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error al buscar conductores disponibles: " . $e->getMessage());
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
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <main class="contenido-principal">
        <section class="seccion-historial">
            <div class="encabezado-historial">
                <h2 class="texto-titulo titulo-historial">Conductores Disponibles</h2>
            </div>

            <?php if (empty($conductores_disponibles)): ?>
                <div class="tarjeta-viaje" style="text-align: center; padding: 2rem;">
                    No hay conductores disponibles en este momento. Inténtalo más tarde.
                </div>
            <?php else: ?>
                <div class="grilla-historial">
                    <?php foreach ($conductores_disponibles as $conductor): ?>
                        <article class="tarjeta-viaje">
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
                                    <span class="costo-viaje"></span> <!-- Se podría mostrar una estimación de llegada -->
                                    <button type="submit" class="btn-primario-accion">Seleccionar</button>
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
