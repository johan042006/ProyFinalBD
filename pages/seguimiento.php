<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$rol = $_SESSION['user_role'] ?? '';
$id_usuario_logueado = $_SESSION['user_id'] ?? '';
$mensaje_estado = '';
$servicios_pendientes = [];
$servicio_seleccionado = null;

// --- Control de Acceso ---
if ($rol !== 'conductor' && $rol !== 'cliente' && $rol !== 'administrador') {
    header("Location: home.php"); // Redirigir si no es un rol permitido
    exit();
}

// --- Mensajes de Sesión ---
if (isset($_SESSION['debug_mensaje'])) {
    $mensaje_debug = $_SESSION['debug_mensaje'];
    unset($_SESSION['debug_mensaje']);
}

try {
    // --- Obtener ID específico del rol (si no es admin) ---
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

    if (($rol === 'conductor' || $rol === 'cliente') && !$id_rol_especifico) {
        $mensaje_estado = "Error: No se encontró el perfil asociado a tu cuenta.";
    } else {
        // ... (el resto del código sigue igual)

        // --- Lógica para obtener la lista de servicios pendientes ---
        $sql_lista_servicios = "SELECT * FROM vista_resumen_servicios WHERE estado NOT IN ('completado', 'cancelado')";
        if ($rol === 'conductor') {
            $sql_lista_servicios .= " AND id_conductor_resumen = :id_rol_especifico";
        } elseif ($rol === 'cliente') {
            $sql_lista_servicios .= " AND id_cliente_resumen = :id_rol_especifico";
        }
        // Si es admin, no se añade filtro de ID, por lo que ve todos los servicios activos.
        $sql_lista_servicios .= " ORDER BY fecha_solicitud DESC";

        $stmt_servicios = $pdo->prepare($sql_lista_servicios);
        if ($rol !== 'administrador') {
            $stmt_servicios->bindParam(':id_rol_especifico', $id_rol_especifico);
        }
        $stmt_servicios->execute();
        $servicios_pendientes = $stmt_servicios->fetchAll(PDO::FETCH_ASSOC);

        // --- Lógica para seleccionar un servicio específico para ver el detalle ---
        if (isset($_GET['service_id']) && !empty($_GET['service_id'])) {
            $service_id_param = $_GET['service_id'];
            
            $sql_detalle_servicio = "SELECT * FROM vista_resumen_servicios WHERE id_servicio = :service_id";
            
            // Asegurarse de que el usuario tiene permiso para ver este servicio (si no es admin)
            if ($rol === 'conductor') {
                $sql_detalle_servicio .= " AND id_conductor_resumen = :id_rol_especifico";
            } elseif ($rol === 'cliente') {
                $sql_detalle_servicio .= " AND id_cliente_resumen = :id_rol_especifico";
            }

            $stmt_detalle = $pdo->prepare($sql_detalle_servicio);
            $stmt_detalle->bindParam(':service_id', $service_id_param);
            if ($rol !== 'administrador') {
                $stmt_detalle->bindParam(':id_rol_especifico', $id_rol_especifico);
            }
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
        $new_status = $_POST['update_status'] ?? ''; // Corregido: el valor viene de 'update_status'

        // Validar que el conductor logueado es el asignado a este servicio
        $stmt_check_assignment = $pdo->prepare("SELECT id_conductor, valor_total FROM SERVICIO WHERE id_servicio = ?");
        $stmt_check_assignment->execute([$service_id_to_update]);
        $servicio_info = $stmt_check_assignment->fetch(PDO::FETCH_ASSOC);
        $assigned_conductor_id = $servicio_info['id_conductor'];

        if ($assigned_conductor_id === $id_rol_especifico) {
            try {
                $pdo->beginTransaction();

                // 1. Actualizar el estado del servicio
                $stmt_update_status = $pdo->prepare("UPDATE SERVICIO SET estado = ? WHERE id_servicio = ?");
                $stmt_update_status->execute([$new_status, $service_id_to_update]);

                // 2. Si el nuevo estado es 'completado', generar la factura
                            if ($new_status === 'completado') {
                                $_SESSION['debug_mensaje'] = "Entrando al bloque 'completado'.";
                                $stmt_check_factura = $pdo->prepare("SELECT id_factura FROM FACTURA WHERE id_servicio = ?");
                                $stmt_check_factura->execute([$service_id_to_update]);
                                $factura_existente = $stmt_check_factura->fetchColumn();
                
                                                if ($factura_existente === false) {
                                                    $_SESSION['debug_mensaje'] .= " No existe factura, creando una nueva.";
                                                    $stmt_insert_factura = $pdo->prepare("INSERT INTO FACTURA (id_servicio, total, fecha_emision) VALUES (?, ?, CURDATE())");
                                                    $stmt_insert_factura->execute([$service_id_to_update, $servicio_info['valor_total']]);
                                                    
                                                    if ($stmt_insert_factura->rowCount() > 0) {
                                                        $_SESSION['debug_mensaje'] .= " Inserción de factura exitosa.";
                                                    } else {
                                                        $_SESSION['debug_mensaje'] .= " ¡Error! La inserción de la factura no afectó ninguna fila.";
                                                    }
                                                } else {
                                                    $_SESSION['debug_mensaje'] .= " Ya existe una factura, no se crea una nueva.";
                                                }
                                            }                
                            $pdo->commit();
                            $mensaje_estado = "Estado del servicio actualizado a '" . htmlspecialchars($new_status) . "'.";
                            
                            header("Location: seguimiento.php?service_id=" . urlencode($service_id_to_update));
                            exit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensaje_estado = "Error al actualizar el servicio. Código de error: " . $e->getCode() . ". Mensaje: " . $e->getMessage();
            }
        } else {
            $mensaje_estado = "Error: No tienes permiso para actualizar este servicio.";
        }
    }

} catch (Exception $e) {
    $mensaje_estado = "Error en la base de datos: " . $e->getMessage();
}
?>
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

            <?php if (!empty($mensaje_debug)): ?>
                <div class="alerta-estado" style="background-color: #fff3cd; color: #856404; border-color: #ffeeba; margin-bottom: 1rem;">
                    <strong>Mensaje de Depuración:</strong> <?php echo $mensaje_debug; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($mensaje_estado)): ?>
                <div class="alerta-estado">
                    <?php echo $mensaje_estado; ?>
                </div>
            <?php endif; ?>

            <?php if ($servicio_seleccionado): ?>
                <!-- VISTA DETALLADA (estilo dashboard) -->
                <div class="detalle-dashboard">
                    <a href="seguimiento.php" class="btn-volver-minimalista">&larr; Volver a la lista</a>
                    
                    <div class="header-detalle-dash">
                        <h2 class="titulo-detalle">Servicio #<?php echo htmlspecialchars($servicio_seleccionado['id_servicio']); ?></h2>
                        <span class="estado-servicio estado-<?php echo str_replace(' ', '_', strtolower($servicio_seleccionado['estado'])); ?>"><?php echo htmlspecialchars(ucfirst($servicio_seleccionado['estado'])); ?></span>
                    </div>

                    <div class="grid-detalle-dash">
                        <!-- Card de Ruta -->
                        <div class="tarjeta-detalle-dash">
                            <h3 class="titulo-tarjeta-dash">Ruta del Servicio</h3>
                            <div class="contenido-tarjeta-dash">
                                <p><strong>Origen:</strong> <?php echo htmlspecialchars($servicio_seleccionado['direccion_origen']); ?></p>
                                <p><strong>Destino:</strong> <?php echo htmlspecialchars($servicio_seleccionado['direccion_destino']); ?></p>
                            </div>
                        </div>

                        <!-- Card de Involucrados -->
                        <div class="tarjeta-detalle-dash">
                            <h3 class="titulo-tarjeta-dash">Involucrados</h3>
                            <div class="contenido-tarjeta-dash">
                                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($servicio_seleccionado['nombre_cliente']); ?></p>
                                <p><strong>Conductor:</strong> <?php echo htmlspecialchars($servicio_seleccionado['nombre_conductor'] ?? 'No asignado'); ?></p>
                            </div>
                        </div>

                        <!-- Card de Detalles del Servicio -->
                        <div class="tarjeta-detalle-dash">
                            <h3 class="titulo-tarjeta-dash">Detalles del Servicio</h3>
                            <div class="contenido-tarjeta-dash">
                                <p><strong>Vehículo:</strong> <?php echo htmlspecialchars($servicio_seleccionado['marca_vehiculo'] . ' (' . $servicio_seleccionado['placa_vehiculo'] . ')'); ?></p>
                                <p><strong>Tipo:</strong> <?php echo htmlspecialchars($servicio_seleccionado['tipo_servicio_nombre']); ?></p>
                                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($servicio_seleccionado['categoria_nombre']); ?></p>
                                <p><strong>Valor:</strong> $<?php echo number_format($servicio_seleccionado['valor_total'], 0, ',', '.'); ?></p>
                            </div>
                        </div>

                        <?php if ($rol === 'conductor'): ?>
                            <!-- Card de Acciones -->
                            <div class="tarjeta-detalle-dash">
                                <h3 class="titulo-tarjeta-dash">Actualizar Estado</h3>
                                <form action="seguimiento.php?service_id=<?php echo urlencode($servicio_seleccionado['id_servicio']); ?>" method="POST" class="acciones-form">
                                    <input type="hidden" name="service_id_to_update" value="<?php echo htmlspecialchars($servicio_seleccionado['id_servicio']); ?>">
                                    <button type="submit" name="update_status" value="en_ruta" class="btn-detalle">En Ruta</button>
                                    <button type="submit" name="update_status" value="en_origen" class="btn-detalle">En Origen</button>
                                    <button type="submit" name="update_status" value="en_destino" class="btn-detalle">En Destino</button>
                                    <button type="submit" name="update_status" value="completado" class="btn-primario-accion">Completado</button>
                                    <button type="submit" name="update_status" value="cancelado" class="btn-secundario-accion">Cancelar</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <!-- VISTA DE LISTA (estilo historial) -->
                <div class="encabezado-historial">
                    <h2 class="texto-titulo titulo-historial">Servicios Activos</h2>
                </div>

                <?php if (empty($servicios_pendientes)): ?>
                    <div class="tarjeta-viaje" style="text-align: center; padding: 2rem;">
                        No tienes servicios activos en este momento.
                    </div>
                <?php else: ?>
                    <div class="grilla-historial">
                        <?php foreach ($servicios_pendientes as $servicio): ?>
                            <a href="seguimiento.php?service_id=<?php echo urlencode($servicio['id_servicio']); ?>" class="tarjeta-link">
                                <article class="tarjeta-viaje">
                                    <div class="cabecera-viaje">
                                        <span class="fecha-viaje"><?php echo date("d M Y, H:i", strtotime($servicio['fecha_solicitud'])); ?></span>
                                        <span class="estado-servicio estado-<?php echo str_replace(' ', '_', strtolower($servicio['estado'])); ?>"><?php echo htmlspecialchars(ucfirst($servicio['estado'])); ?></span>
                                    </div>
                                    <div class="ruta-viaje">
                                        <span><?php echo htmlspecialchars($servicio['direccion_origen']); ?></span>
                                        <span class="icono-flecha">→</span>
                                        <span style="font-weight: 500;"><?php echo htmlspecialchars($servicio['direccion_destino']); ?></span>
                                    </div>
                                    <div class="pie-viaje">
                                        <span class="costo-viaje">$<?php echo number_format($servicio['valor_total'], 0, ',', '.'); ?></span>
                                        <span class="ver-detalle">Ver detalle &rarr;</span>
                                    </div>
                                </article>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>

</body>
</html>