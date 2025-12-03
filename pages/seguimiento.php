<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$rol = $_SESSION['user_role'] ?? '';
$id_usuario_logueado = $_SESSION['user_id'] ?? '';
$mensaje_estado = '';
$servicios_pendientes = [];
$servicio_seleccionado = null;

// --- Control de Acceso ---
if ($rol !== 'conductor' && $rol !== 'cliente') {
    header("Location: home.php"); // Redirigir si no es conductor ni cliente
    exit();
}

try {
    // --- Obtener ID específico del rol ---
    $id_rol_especifico = null;
    if ($rol === 'conductor') {
        $stmt = $pdo->prepare("SELECT id_conductor FROM CONDUCTOR WHERE id_usuario = ?");
        $stmt->execute([$id_usuario_logueado]);
        $id_rol_especifico = $stmt->fetchColumn();
    } elseif ($rol === 'cliente') {
        $stmt = $pdo->prepare("SELECT id_cliente FROM CLIENTE WHERE id_usuario = ?");
        $stmt->execute([$id_usuario_logueado]);
        $id_rol_especifico = $stmt->fetchColumn();
    }

    if (!$id_rol_especifico) {
        $mensaje_estado = "Error: No se encontró el perfil asociado a tu cuenta.";
    } else {
        // --- Lógica para obtener servicios pendientes/activos ---
        $sql_base_servicios = "
            SELECT 
                s.id_servicio,
                s.fecha_solicitud,
                s.estado,
                s.valor_total,
                r.direccion_origen,
                r.direccion_destino,
                cl.nombre AS nombre_cliente,
                co.nombre AS nombre_conductor,
                v.placa AS placa_vehiculo,
                v.marca AS marca_vehiculo,
                v.modelo AS modelo_vehiculo,
                ts.tipo AS tipo_servicio_nombre,
                cc.nombre_categoria AS categoria_nombre
            FROM SERVICIO s
            JOIN RUTA_SERVICIO r ON s.id_servicio = r.id_servicio
            JOIN CLIENTE cl ON s.id_cliente = cl.id_cliente
            LEFT JOIN CONDUCTOR co ON s.id_conductor = co.id_conductor
            LEFT JOIN VEHICULO v ON s.placa_vehiculo = v.placa
            LEFT JOIN TIPO_SERVICIO ts ON s.id_tipo = ts.id_tipo
            LEFT JOIN CAT_CATEGORIA cc ON s.id_categoria = cc.id_categoria
        ";

        // --- Lógica para obtener la lista de servicios pendientes ---
        $sql_lista_servicios = $sql_base_servicios . " WHERE s.estado NOT IN ('completado', 'cancelado')";
        if ($rol === 'conductor') {
            $sql_lista_servicios .= " AND s.id_conductor = :id_rol_especifico";
        } elseif ($rol === 'cliente') {
            $sql_lista_servicios .= " AND s.id_cliente = :id_rol_especifico";
        }
        $sql_lista_servicios .= " ORDER BY s.fecha_solicitud DESC";

        $stmt_servicios = $pdo->prepare($sql_lista_servicios);
        $stmt_servicios->bindParam(':id_rol_especifico', $id_rol_especifico);
        $stmt_servicios->execute();
        $servicios_pendientes = $stmt_servicios->fetchAll(PDO::FETCH_ASSOC);

        // --- Lógica para seleccionar un servicio específico para ver el detalle ---
        if (isset($_GET['service_id']) && !empty($_GET['service_id'])) {
            $service_id_param = $_GET['service_id'];
            
            $sql_detalle_servicio = $sql_base_servicios . " WHERE s.id_servicio = :service_id";
            
            // Asegurarse de que el usuario tiene permiso para ver este servicio
            if ($rol === 'conductor') {
                $sql_detalle_servicio .= " AND s.id_conductor = :id_rol_especifico";
            } elseif ($rol === 'cliente') {
                $sql_detalle_servicio .= " AND s.id_cliente = :id_rol_especifico";
            }

            $stmt_detalle = $pdo->prepare($sql_detalle_servicio);
            $stmt_detalle->bindParam(':service_id', $service_id_param);
            $stmt_detalle->bindParam(':id_rol_especifico', $id_rol_especifico);
            $stmt_detalle->execute();
            $servicio_seleccionado = $stmt_detalle->fetch(PDO::FETCH_ASSOC);

            if (!$servicio_seleccionado) {
                $mensaje_estado = "Error: Servicio no encontrado o no tienes permiso para verlo.";
            }
        }
    }

    // --- Lógica para que el conductor actualice el estado del servicio ---
    if ($rol === 'conductor' && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
        $service_id_to_update = $_POST['service_id_to_update'] ?? '';
        $new_status = $_POST['new_status'] ?? '';

        // Validar que el conductor logueado es el asignado a este servicio
        $stmt_check_assignment = $pdo->prepare("SELECT id_conductor FROM SERVICIO WHERE id_servicio = ?");
        $stmt_check_assignment->execute([$service_id_to_update]);
        $assigned_conductor_id = $stmt_check_assignment->fetchColumn();

        if ($assigned_conductor_id === $id_rol_especifico) {
            $stmt_update_status = $pdo->prepare("UPDATE SERVICIO SET estado = ? WHERE id_servicio = ?");
            $stmt_update_status->execute([$new_status, $service_id_to_update]);
            $mensaje_estado = "Estado del servicio actualizado a '" . htmlspecialchars($new_status) . "'.";
            // Recargar la página para mostrar el estado actualizado
            header("Location: seguimiento.php?service_id=" . urlencode($service_id_to_update));
            exit();
        } else {
            $mensaje_estado = "Error: No tienes permiso para actualizar este servicio.";
        }
    }

} catch (Exception $e) {
    $mensaje_estado = "Error en la base de datos: " . $e->getMessage();
}
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

    <?php if (!empty($mensaje_estado)): ?>
        <div class="alerta-estado" style="padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; text-align: center;">
            <?php echo $mensaje_estado; ?>
        </div>
    <?php endif; ?>

    <main class="contenido-principal">
        <section class="seccion-seguimiento">
            <h2 class="titulo-seccion">Seguimiento de Servicios</h2>
            <p class="subtitulo-seccion">
                <?php if ($rol === 'conductor'): ?>
                    Aquí puedes ver y gestionar tus servicios asignados.
                <?php elseif ($rol === 'cliente'): ?>
                    Aquí puedes ver el estado de tus servicios solicitados.
                <?php endif; ?>
            </p>

            <?php if (!empty($mensaje_estado)): ?>
                <div class="alerta-estado" style="padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc;">
                    <?php echo $mensaje_estado; ?>
                </div>
            <?php endif; ?>

            <?php if ($servicio_seleccionado): ?>
                <!-- Vista de Detalle de Servicio -->
                <div class="detalle-servicio-grid">
                    <div class="detalle-servicio-info">
                        <h3>Detalles del Servicio #<?php echo htmlspecialchars($servicio_seleccionado['id_servicio']); ?></h3>
                        <p><strong>Estado:</strong> <span class="estado-servicio estado-<?php echo str_replace(' ', '_', strtolower($servicio_seleccionado['estado'])); ?>"><?php echo htmlspecialchars(ucfirst($servicio_seleccionado['estado'])); ?></span></p>
                        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($servicio_seleccionado['nombre_cliente']); ?></p>
                        <p><strong>Conductor:</strong> <?php echo htmlspecialchars($servicio_seleccionado['nombre_conductor'] ?? 'No asignado'); ?></p>
                        <p><strong>Vehículo:</strong> <?php echo htmlspecialchars($servicio_seleccionado['marca_vehiculo'] . ' ' . $servicio_seleccionado['modelo_vehiculo'] . ' (' . $servicio_seleccionado['placa_vehiculo'] . ')'); ?></p>
                        <p><strong>Tipo de Servicio:</strong> <?php echo htmlspecialchars($servicio_seleccionado['tipo_servicio_nombre']); ?></p>
                        <p><strong>Categoría:</strong> <?php echo htmlspecialchars($servicio_seleccionado['categoria_nombre']); ?></p>
                        <p><strong>Origen:</strong> <?php echo htmlspecialchars($servicio_seleccionado['direccion_origen']); ?></p>
                        <p><strong>Destino:</strong> <?php echo htmlspecialchars($servicio_seleccionado['direccion_destino']); ?></p>
                        <p><strong>Valor Total:</strong> $<?php echo number_format($servicio_seleccionado['valor_total'], 0, ',', '.'); ?></p>
                        <p><strong>Solicitado:</strong> <?php echo date("d M Y H:i", strtotime($servicio_seleccionado['fecha_solicitud'])); ?></p>

                        <?php if ($rol === 'conductor'): ?>
                            <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid #e2e8f0;">
                            <h3>Actualizar Estado</h3>
                            <form action="seguimiento.php?service_id=<?php echo urlencode($servicio_seleccionado['id_servicio']); ?>" method="POST" class="detalle-servicio-acciones">
                                <input type="hidden" name="service_id_to_update" value="<?php echo htmlspecialchars($servicio_seleccionado['id_servicio']); ?>">
                                <button type="submit" name="update_status" value="en_ruta" class="boton-primario">En Ruta</button>
                                <button type="submit" name="update_status" value="en_origen" class="boton-primario" style="background-color: #f59e0b;">En Origen</button>
                                <button type="submit" name="update_status" value="en_destino" class="boton-primario" style="background-color: #10b981;">En Destino</button>
                                <button type="submit" name="update_status" value="completado" class="boton-primario" style="background-color: #065f46;">Completado</button>
                                <button type="submit" name="update_status" value="cancelado" class="boton-secundario" style="background-color: #ef4444; color: white;">Cancelar</button>
                            </form>
                        <?php endif; ?>
                        <div style="margin-top: 1.5rem; text-align: center;">
                            <a href="seguimiento.php" class="boton-secundario" style="width: auto; display: inline-block;">Volver a la lista de servicios</a>
                        </div>
                    </div>
                    <div class="detalle-servicio-mapa">
                        <!-- Placeholder para el mapa estático -->
                        <img src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo urlencode($servicio_seleccionado['direccion_origen']); ?>&zoom=12&size=600x400&markers=color:red%7Clabel:O%7C<?php echo urlencode($servicio_seleccionado['direccion_origen']); ?>&markers=color:blue%7Clabel:D%7C<?php echo urlencode($servicio_seleccionado['direccion_destino']); ?>&path=color:0x0000ff|weight:5|<?php echo urlencode($servicio_seleccionado['direccion_origen']); ?>|<?php echo urlencode($servicio_seleccionado['direccion_destino']); ?>&key=YOUR_GOOGLE_MAPS_API_KEY" alt="Mapa de la ruta" style="width:100%; height:100%; object-fit: cover;">
                        <p style="position: absolute; background: rgba(255,255,255,0.8); padding: 0.5rem; border-radius: 0.5rem;">Mapa de la ruta (Origen a Destino)</p>
                    </div>
                </div>
            <?php else: ?>
                <!-- Vista de Lista de Servicios Pendientes -->
                <?php if (empty($servicios_pendientes)): ?>
                    <p class="subtitulo-seccion">No tienes servicios pendientes o en curso.</p>
                <?php else: ?>
                    <div class="lista-servicios">
                        <?php foreach ($servicios_pendientes as $servicio): ?>
                            <div class="tarjeta-servicio">
                                <h3>Servicio #<?php echo htmlspecialchars($servicio['id_servicio']); ?></h3>
                                <p><strong>Origen:</strong> <?php echo htmlspecialchars($servicio['direccion_origen']); ?></p>
                                <p><strong>Destino:</strong> <?php echo htmlspecialchars($servicio['direccion_destino']); ?></p>
                                <p><strong>Estado:</strong> <span class="estado-servicio estado-<?php echo str_replace(' ', '_', strtolower($servicio['estado'])); ?>"><?php echo htmlspecialchars(ucfirst($servicio['estado'])); ?></span></p>
                                <p><strong>Valor:</strong> $<?php echo number_format($servicio['valor_total'], 0, ',', '.'); ?></p>
                                <a href="seguimiento.php?service_id=<?php echo urlencode($servicio['id_servicio']); ?>" class="btn-ver-seguimiento">Ver Seguimiento</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>

</body>
</html>