<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$rol = $_SESSION['user_role'] ?? 'cliente'; // Asumir 'cliente' si no hay rol
$id_usuario_logueado = $_SESSION['user_id'] ?? '';
$mensaje_estado = '';

// --- Lógica para obtener datos de catálogos para el formulario de cliente ---
$tipos_servicio = $pdo->query("SELECT id_tipo, tipo FROM TIPO_SERVICIO ORDER BY tipo")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $pdo->query("SELECT id_categoria, nombre_categoria, porcentaje_recargo FROM CAT_CATEGORIA ORDER BY nombre_categoria")->fetchAll(PDO::FETCH_ASSOC);

// --- Lógica para manejar POST (Pedir un viaje como cliente) ---
if ($rol === 'cliente' && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['solicitar_viaje'])) {
    $id_cliente = null;
    $stmt_get_id_cliente = $pdo->prepare("SELECT id_cliente FROM CLIENTE WHERE id_usuario = ?");
    $stmt_get_id_cliente->execute([$id_usuario_logueado]);
    $id_cliente = $stmt_get_id_cliente->fetchColumn();

    if (!$id_cliente) {
        $mensaje_estado = "Error: No se encontró el perfil de cliente asociado a tu cuenta.";
    } else {
        $direccion_origen = $_POST['direccion_origen'] ?? '';
        $direccion_destino = $_POST['direccion_destino'] ?? '';
        $id_tipo_servicio = $_POST['id_tipo_servicio'] ?? '';
        $id_categoria_servicio = $_POST['id_categoria_servicio'] ?? '';

        if (empty($direccion_origen) || empty($direccion_destino) || empty($id_tipo_servicio) || empty($id_categoria_servicio)) {
            $mensaje_estado = "Error: Todos los campos de la solicitud son obligatorios.";
        } else {
            try {
                $pdo->beginTransaction();

                // 1. Calcular el valor total del servicio
                $stmt_tarifa = $pdo->query("SELECT tarifa_base_valor FROM TARIFA_BASE ORDER BY fecha_vigencia DESC LIMIT 1");
                $tarifa_base = $stmt_tarifa->fetchColumn();

                $porcentaje_recargo = 0;
                foreach ($categorias as $cat) {
                    if ($cat['id_categoria'] == $id_categoria_servicio) {
                        $porcentaje_recargo = $cat['porcentaje_recargo'];
                        break;
                    }
                }

                if ($tarifa_base === false) {
                    throw new Exception("No se pudo obtener la tarifa base.");
                }

                $valor_total = $tarifa_base * (1 + ($porcentaje_recargo / 100));
                
                $stmt_tarifa_id = $pdo->query("SELECT id_tarifa FROM TARIFA_BASE ORDER BY fecha_vigencia DESC LIMIT 1");
                $id_tarifa_actual = $stmt_tarifa_id->fetchColumn();

                // 2. Insertar en la tabla SERVICIO
                $stmt_servicio = $pdo->prepare(
                    "INSERT INTO SERVICIO (fecha_solicitud, estado, valor_total, id_cliente, id_conductor, placa_vehiculo, id_tipo, id_categoria, id_tarifa) 
                     VALUES (NOW(), 'solicitado', ?, ?, NULL, NULL, ?, ?, ?)"
                );
                $stmt_servicio->execute([$valor_total, $id_cliente, $id_tipo_servicio, $id_categoria_servicio, $id_tarifa_actual]);
                $id_servicio_nuevo = $pdo->lastInsertId();

                // 3. Insertar en la tabla RUTA_SERVICIO
                $stmt_ruta = $pdo->prepare(
                    "INSERT INTO RUTA_SERVICIO (id_servicio, direccion_origen, direccion_destino) VALUES (?, ?, ?)"
                );
                $stmt_ruta->execute([$id_servicio_nuevo, $direccion_origen, $direccion_destino]);

                $pdo->commit();
                $_SESSION['mensaje_exito'] = "Servicio #$id_servicio_nuevo solicitado exitosamente. Valor estimado: $" . number_format($valor_total, 0, ',', '.');
                header("Location: seguimiento.php?service_id=" . urlencode($id_servicio_nuevo));
                exit();

            } catch (PDOException $e) {
                $pdo->rollBack();
                $mensaje_estado = "Error en la base de datos al solicitar el servicio: " . $e->getMessage();
            } catch (Exception $e) {
                $pdo->rollBack();
                $mensaje_estado = "Error: " . $e->getMessage();
            }
        }
    }
}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Viaje | MoviApp</title>
    
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/home.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <?php if (!empty($mensaje_estado)): ?>
            <div class="alerta-estado" style="padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; text-align: center;">
                <?php echo $mensaje_estado; ?>
            </div>
        <?php endif; ?>
        <?php
        // Determinar el rol del usuario desde la sesión
        $rol = $_SESSION['user_role'] ?? 'cliente'; // Asumir 'cliente' si no hay rol

        // Cargar la vista correspondiente al rol
        switch ($rol) {
            case 'administrador':
                // Vista para el Administrador
                echo '
                <section class="seccion-home">
                    <div class="layout-home" style="justify-content: center;">
                        <div class="tarjeta-formulario" style="width: 100%; max-width: 800px; text-align: center;">
                            <h2 class="texto-titulo" style="margin-bottom: 1rem;">Panel de Administrador</h2>
                            <p class="subtitulo-login" style="margin-bottom: 2rem;">Bienvenido, ' . htmlspecialchars($_SESSION['user_name']) . '. Selecciona una opción para empezar.</p>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                                <a href="conductores.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-users.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Gestionar Conductores</span>
                                </a>
                                <a href="vehiculos.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-car.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Gestionar Vehículos</span>
                                </a>
                                <a href="reportes.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-bar-chart-3.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Ver Reportes</span>
                                </a>
                                <a href="pagos.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-dollar-sign.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Procesar Pagos</span>
                                </a>
                                <a href="historial.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-history.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Historial de Viajes</span>
                                </a>
                                <a href="configuracion.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-settings.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Configuración</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>';
                break;

            case 'conductor':
                // Vista para el Conductor
                echo '
                <section class="seccion-home">
                    <div class="layout-home" style="justify-content: center;">
                        <div class="tarjeta-formulario" style="width: 100%; max-width: 600px; text-align: center;">
                            <h2 class="texto-titulo" style="margin-bottom: 1rem;">Panel de Conductor</h2>
                            <p class="subtitulo-login" style="margin-bottom: 2rem;">Bienvenido, ' . htmlspecialchars($_SESSION['user_name']) . '. Aquí están tus opciones.</p>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                                <a href="seguimiento.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-map.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Ver Viajes Asignados</span>
                                </a>
                                <a href="historial.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-history.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Mi Historial</span>
                                </a>
                                <a href="registrar_vehiculo_conductor.php" class="boton-primario" style="display: flex; flex-direction: column; align-items: center; padding: 1rem; height: auto;">
                                    <img src="https://api.iconify.design/lucide-car.svg?color=%23ffffff" style="width: 32px; height: 32px; margin-bottom: 0.5rem;">
                                    <span>Registrar Vehículo</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </section>';
                break;

            default: // Para 'cliente' y cualquier otro rol no definido
                // Vista para el Cliente (el formulario de pedir viaje original)
                echo '
                <section class="seccion-home">
                    <div class="layout-home">
                        <div class="contenedor-mapa">
                            <div class="wrapper-mapa">
                                <img src="https://app.grapesjs.com/api/assets/random-image?query=%22city%20map%22&w=1280&h=720" alt="Mapa de la ciudad" class="imagen-mapa-placeholder">
                            </div>
                        </div>
                        <div class="contenedor-formulario-viaje">
                            <div class="tarjeta-formulario">
                                <h2 class="texto-titulo titulo-formulario">Pedir un viaje</h2>
                                <form action="home.php" method="POST">
                                    <input type="hidden" name="solicitar_viaje" value="1">
                                    <div class="grupo-origen-destino">
                                        <div class="grupo-formulario">
                                            <label class="etiqueta">Origen</label>
                                            <div class="contenedor-input">
                                                <img src="https://api.iconify.design/lucide-map-pin.svg?color=%230f172a" class="icono-input">
                                                <input type="text" id="direccion_origen" name="direccion_origen" class="campo-entrada" placeholder="Ej. Calle 45 #12-30" required>
                                            </div>
                                            <div class="sugerencias-visuales">Casa • Trabajo • Último destino</div>
                                        </div>
                                        <div class="grupo-formulario">
                                            <label class="etiqueta">Destino</label>
                                            <div class="contenedor-input">
                                                <img src="https://api.iconify.design/lucide-flag.svg?color=%230f172a" class="icono-input">
                                                <input type="text" id="direccion_destino" name="direccion_destino" class="campo-entrada" placeholder="Ej. Aeropuerto" required>
                                            </div>
                                            <div class="sugerencias-visuales">Aeropuerto • Centro • Universidad</div>
                                        </div>
                                    </div>
                                    <div class="grupo-formulario">
                                        <label class="etiqueta">Tipo de Servicio</label>
                                        <select id="id_tipo_servicio" name="id_tipo_servicio" class="campo-entrada" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($tipos_servicio as $tipo): ?>
                                                <option value="<?php echo htmlspecialchars($tipo['id_tipo']); ?>">
                                                    <?php echo htmlspecialchars($tipo['tipo']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="grupo-formulario">
                                        <label class="etiqueta">Categoría del Servicio</label>
                                        <select id="id_categoria_servicio" name="id_categoria_servicio" class="campo-entrada" required>
                                            <option value="">Seleccione...</option>
                                            <?php foreach ($categorias as $categoria): ?>
                                                <option value="<?php echo htmlspecialchars($categoria['id_categoria']); ?>">
                                                    <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="tarjeta-estimacion">
                                        <div class="fila-tarifa">
                                            <div style="display:flex; gap:0.5rem; align-items:center;">
                                                <img src="https://api.iconify.design/lucide-wallet.svg?color=%230f172a" width="20">
                                                <span>Estimado</span>
                                            </div>
                                            <span class="valor-tarifa">$<?php // El valor se calculará en el backend ?></span>
                                        </div>
                                        <div class="detalles-tarifa">El valor exacto se confirmará al asignar el servicio.</div>
                                    </div>
                                    <div class="botones-accion">
                                        <button type="submit" class="boton-primario">Solicitar viaje</button>
                                    </div>
                                </form>
                            </div>
                        </div>                </div>
                </section>';
                break;
        }
        ?>
    </main>

</body>
</html>