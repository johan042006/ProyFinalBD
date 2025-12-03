<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$rol = $_SESSION['user_role'];
$id_usuario = $_SESSION['user_id'];
$viajes = [];
$mensaje = '';

try {
    // Usamos la vista para simplificar la consulta
    $sql = "SELECT * FROM vista_resumen_servicios";

    if ($rol === 'conductor') {
        $stmt_get_id = $pdo->prepare("SELECT id_conductor FROM CONDUCTOR WHERE id_usuario = ?");
        $stmt_get_id->execute([$id_usuario]);
        $id_conductor_actual = $stmt_get_id->fetchColumn();

        if ($id_conductor_actual) {
            $sql .= " WHERE id_conductor_resumen = ?";
            $stmt = $pdo->prepare($sql . " ORDER BY fecha_solicitud DESC");
            $stmt->execute([$id_conductor_actual]);
        } else {
            $viajes = [];
        }
    } elseif ($rol === 'cliente') {
        $stmt_get_id = $pdo->prepare("SELECT id_cliente FROM CLIENTE WHERE id_usuario = ?");
        $stmt_get_id->execute([$id_usuario]);
        $id_cliente_actual = $stmt_get_id->fetchColumn();

        if ($id_cliente_actual) {
            $sql .= " WHERE id_cliente_resumen = ?";
            $stmt = $pdo->prepare($sql . " ORDER BY fecha_solicitud DESC");
            $stmt->execute([$id_cliente_actual]);
        } else {
            $viajes = [];
        }
    } else { // Para admin
        $stmt = $pdo->prepare($sql . " ORDER BY fecha_solicitud DESC");
        $stmt->execute();
    }

    if (isset($stmt)) {
        $viajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    $mensaje = "Error al cargar el historial: " . $e->getMessage();
}

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
                <h2 class="texto-titulo titulo-historial">Historial de Servicios</h2>
                <?php if ($rol === 'administrador'): ?>
                <button class="boton-exportar">
                    <img src="https://api.iconify.design/lucide-file-down.svg?color=%230f172a" width="18">
                    <span>Exportar todo</span>
                </button>
                <?php endif; ?>
            </div>

            <?php if (!empty($mensaje)): ?>
                <div style='padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; background-color: #fee2e2; color: #991b1b;'><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <div class="grilla-historial">

                <?php if (empty($viajes)): ?>
                    <div class="tarjeta-viaje" style="text-align: center; padding: 2rem;">
                        No hay viajes en tu historial.
                    </div>
                <?php else: ?>
                    <?php foreach($viajes as $viaje): ?>
                        <article class="tarjeta-viaje">
                            <div class="cabecera-viaje">
                                <span class="fecha-viaje"><?php echo date("d M Y \• H:i", strtotime($viaje['fecha_solicitud'])); ?></span>
                                <?php if ($rol === 'administrador'): ?>
                                    <span class="id-servicio">ID: #<?php echo $viaje['id_servicio']; ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="detalles-actores">
                                <div>Cliente: <strong><?php echo htmlspecialchars($viaje['nombre_cliente']); ?></strong></div>
                                <?php if ($rol === 'administrador'): ?>
                                    <div>Conductor: <strong><?php echo htmlspecialchars($viaje['nombre_conductor'] ?? 'No asignado'); ?></strong></div>
                                <?php endif; ?>
                            </div>

                            <div class="ruta-viaje">
                                <span><?php echo htmlspecialchars($viaje['direccion_origen']); ?></span>
                                <span class="icono-flecha">→</span>
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($viaje['direccion_destino']); ?></span>
                            </div>

                            <div class="pie-viaje">
                                <span class="costo-viaje">$<?php echo number_format($viaje['valor_total'], 0, ',', '.'); ?></span>
                                
                                <div class="acciones-viaje">
                                    <a href="seguimiento.php?service_id=<?php echo urlencode($viaje['id_servicio']); ?>" class="btn-detalle">Ver detalle</a>
                                    <?php if ($viaje['estado'] === 'completado'): ?>
                                        <a href="factura.php?id_servicio=<?php echo urlencode($viaje['id_servicio']); ?>" class="btn-detalle">Factura</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </section>
    </main>

</body>
</html>
