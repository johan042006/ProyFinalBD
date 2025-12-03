<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

if ($_SESSION['user_role'] !== 'administrador') {
    die("Acceso denegado. Solo los administradores pueden crear servicios.");
}

$mensaje = '';

// --- LÓGICA DE MANEJO DE FORMULARIO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['crear_servicio'])) {
    // Recoger datos del formulario
    $id_cliente = $_POST['id_cliente'];
    $id_conductor = $_POST['id_conductor'];
    $placa_vehiculo = $_POST['placa_vehiculo'];
    $id_tipo = $_POST['id_tipo'];
    $id_categoria = $_POST['id_categoria'];
    $direccion_origen = $_POST['direccion_origen'];
    $direccion_destino = $_POST['direccion_destino'];
    $estado_inicial = 'solicitado';

    $pdo->beginTransaction();

    try {
        // 1. Calcular el valor total del servicio
        $stmt_tarifa = $pdo->query("SELECT tarifa_base_valor FROM TARIFA_BASE ORDER BY fecha_vigencia DESC LIMIT 1");
        $tarifa_base = $stmt_tarifa->fetchColumn();

        $stmt_categoria = $pdo->prepare("SELECT porcentaje_recargo FROM CAT_CATEGORIA WHERE id_categoria = ?");
        $stmt_categoria->execute([$id_categoria]);
        $porcentaje_recargo = $stmt_categoria->fetchColumn();

        if ($tarifa_base === false || $porcentaje_recargo === false) {
            throw new Exception("No se pudo calcular la tarifa. Verifica que existan tarifas base y categorías.");
        }

        $valor_total = $tarifa_base * (1 + ($porcentaje_recargo / 100));
        
        // Se asume que la tarifa base usada es la más reciente.
        $stmt_tarifa_id = $pdo->query("SELECT id_tarifa FROM TARIFA_BASE ORDER BY fecha_vigencia DESC LIMIT 1");
        $id_tarifa_actual = $stmt_tarifa_id->fetchColumn();

        // 2. Insertar en la tabla SERVICIO
        $stmt_servicio = $pdo->prepare(
            "INSERT INTO SERVICIO (fecha_solicitud, estado, valor_total, id_cliente, id_conductor, placa_vehiculo, id_tipo, id_categoria, id_tarifa) 
             VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt_servicio->execute([$estado_inicial, $valor_total, $id_cliente, $id_conductor, $placa_vehiculo, $id_tipo, $id_categoria, $id_tarifa_actual]);
        $id_servicio_nuevo = $pdo->lastInsertId();

        // 3. Insertar en la tabla RUTA_SERVICIO
        $stmt_ruta = $pdo->prepare(
            "INSERT INTO RUTA_SERVICIO (id_servicio, direccion_origen, direccion_destino) VALUES (?, ?, ?)"
        );
        $stmt_ruta->execute([$id_servicio_nuevo, $direccion_origen, $direccion_destino]);

        // 4. Insertar en la tabla FACTURA
        $stmt_factura = $pdo->prepare(
            "INSERT INTO FACTURA (id_servicio, total, fecha_emision) VALUES (?, ?, CURDATE())"
        );
        $stmt_factura->execute([$id_servicio_nuevo, $valor_total]);
        $id_factura_nueva = $pdo->lastInsertId();

        // 5. Insertar en la tabla FORMAPAGO_FACTURA
        $formas_pago_seleccionadas = $_POST['formas_pago'] ?? [];
        if (!empty($formas_pago_seleccionadas)) {
            foreach ($formas_pago_seleccionadas as $id_pago) {
                $stmt_formapago = $pdo->prepare(
                    "INSERT INTO FORMAPAGO_FACTURA (id_factura, id_pago) VALUES (?, ?)"
                );
                $stmt_formapago->execute([$id_factura_nueva, $id_pago]);
            }
        } else {
            // Opcional: Manejar caso donde no se selecciona forma de pago
            // Por ejemplo, lanzar una excepción o establecer un mensaje de error
            throw new Exception("Debe seleccionar al menos una forma de pago.");
        }

        // 6. Confirmar transacción
        $pdo->commit();
        $_SESSION['mensaje_exito'] = "Servicio #$id_servicio_nuevo creado exitosamente con un valor de $valor_total. Factura #$id_factura_nueva generada.";
        header("Location: historial.php"); // Redirigir al historial para ver el nuevo servicio
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensaje_error'] = "Error al crear el servicio: " . $e->getMessage();
        header("Location: solicitud.php");
        exit();
    }
}

// --- LÓGICA DE CONSULTA DE DATOS PARA EL FORMULARIO ---
try {
    $clientes = $pdo->query("SELECT id_cliente, nombre FROM CLIENTE ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $conductores = $pdo->query("SELECT c.id_conductor, c.nombre FROM CONDUCTOR c JOIN USUARIO u ON c.id_usuario = u.id_usuario WHERE u.estado = 'activo' ORDER BY c.nombre")->fetchAll(PDO::FETCH_ASSOC);
    $vehiculos = $pdo->query("SELECT placa, marca, modelo FROM VEHICULO WHERE estado = 'activo' ORDER BY marca")->fetchAll(PDO::FETCH_ASSOC);
    $categorias = $pdo->query("SELECT * FROM CAT_CATEGORIA ORDER BY nombre_categoria")->fetchAll(PDO::FETCH_ASSOC);
    $tipos_servicio = $pdo->query("SELECT * FROM TIPO_SERVICIO ORDER BY tipo")->fetchAll(PDO::FETCH_ASSOC);
    $formas_pago = $pdo->query("SELECT * FROM CAT_PAGO ORDER BY forma_pago")->fetchAll(PDO::FETCH_ASSOC); // Add this line
} catch (PDOException $e) {
    die("Error al cargar datos para el formulario: " . $e->getMessage());
}

// Manejo de mensajes de sesión
if (isset($_SESSION['mensaje_exito'])) {
    $mensaje = "<div class='alerta alerta-exito'>" . $_SESSION['mensaje_exito'] . "</div>";
    unset($_SESSION['mensaje_exito']);
}
if (isset($_SESSION['mensaje_error'])) {
    $mensaje = "<div class='alerta alerta-error'>" . $_SESSION['mensaje_error'] . "</div>";
    unset($_SESSION['mensaje_error']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Servicio | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/conductores.css"> <!-- Reutilizamos estilos de formularios -->
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <div class="formulario-agregar" style="display:block; max-width: 900px;">
            <h2 class="titulo-seccion">Registrar Nuevo Servicio</h2>
            <hr style="border:none; border-top:1px solid #e2e8f0; margin: 1.5rem 0;">

            <?php echo $mensaje; ?>

            <form method="POST" action="solicitud.php" class="formulario-grid" style="grid-template-columns: 1fr 1fr;">
                
                <div class="campo-formulario" style="grid-column: 1 / -1;">
                    <label for="id_cliente">Cliente</label>
                    <select id="id_cliente" name="id_cliente" required><option value="">Seleccione un cliente...</option><?php foreach ($clientes as $item): ?><option value="<?php echo $item['id_cliente']; ?>"><?php echo htmlspecialchars($item['nombre']); ?></option><?php endforeach; ?></select>
                </div>

                <div class="campo-formulario">
                    <label for="direccion_origen">Dirección de Origen</label>
                    <input type="text" id="direccion_origen" name="direccion_origen" required>
                </div>
                <div class="campo-formulario">
                    <label for="direccion_destino">Dirección de Destino</label>
                    <input type="text" id="direccion_destino" name="direccion_destino" required>
                </div>

                <div class="campo-formulario">
                    <label for="id_conductor">Conductor Asignado</label>
                    <select id="id_conductor" name="id_conductor" required><option value="">Seleccione un conductor...</option><?php foreach ($conductores as $item): ?><option value="<?php echo $item['id_conductor']; ?>"><?php echo htmlspecialchars($item['nombre']); ?></option><?php endforeach; ?></select>
                </div>
                <div class="campo-formulario">
                    <label for="placa_vehiculo">Vehículo</label>
                    <select id="placa_vehiculo" name="placa_vehiculo" required><option value="">Seleccione un vehículo...</option><?php foreach ($vehiculos as $item): ?><option value="<?php echo $item['placa']; ?>"><?php echo htmlspecialchars($item['marca'] . ' ' . $item['modelo'] . ' (' . $item['placa'] . ')'); ?></option><?php endforeach; ?></select>
                </div>

                <div class="campo-formulario">
                    <label for="id_tipo">Tipo de Servicio</label>
                    <select id="id_tipo" name="id_tipo" required><option value="">Seleccione...</option><?php foreach ($tipos_servicio as $item): ?><option value="<?php echo $item['id_tipo']; ?>"><?php echo htmlspecialchars($item['tipo']); ?></option><?php endforeach; ?></select>
                </div>
                <div class="campo-formulario">
                    <label for="id_categoria">Categoría del Servicio</label>
                    <select id="id_categoria" name="id_categoria" required><option value="">Seleccione...</option><?php foreach ($categorias as $item): ?><option value="<?php echo $item['id_categoria']; ?>"><?php echo htmlspecialchars($item['nombre_categoria']); ?></option><?php endforeach; ?></select>
                </div>

                <div class="campo-formulario" style="grid-column: 1 / -1;">
                    <label class="etiqueta">Formas de Pago</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 0.5rem;">
                        <?php foreach ($formas_pago as $forma): ?>
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" name="formas_pago[]" value="<?php echo htmlspecialchars($forma['id_pago']); ?>">
                                <?php echo htmlspecialchars($forma['forma_pago']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="acciones-formulario" style="grid-column: 1 / -1;">
                    <a href="home.php" class="btn-accion">Cancelar</a>
                    <button type="submit" name="crear_servicio" class="boton-agregar">Crear Servicio</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
