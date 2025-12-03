<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$rol = $_SESSION['user_role'] ?? '';
$id_usuario_logueado = $_SESSION['user_id'] ?? '';
$mensaje_estado = '';
$factura = null;
$servicio = null;

$id_servicio_param = $_GET['id_servicio'] ?? '';

// --- Lógica de Pago ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pagar_factura'])) {
    $id_factura_a_pagar = $_POST['id_factura'];
    $id_pago_seleccionado = $_POST['id_pago'];

    try {
        $pdo->beginTransaction();
        $stmt_pagar = $pdo->prepare("UPDATE FACTURA SET estado_pago = 'pagado' WHERE id_factura = ?");
        $stmt_pagar->execute([$id_factura_a_pagar]);

        $stmt_insert_pago = $pdo->prepare("INSERT INTO FORMAPAGO_FACTURA (id_factura, id_pago) VALUES (?, ?)");
        $stmt_insert_pago->execute([$id_factura_a_pagar, $id_pago_seleccionado]);

        $pdo->commit();
        $_SESSION['mensaje_exito_factura'] = "Factura pagada con éxito.";
        header("Location: factura.php?id_servicio=" . $id_servicio_param);
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensaje_estado = "Error al procesar el pago: " . $e->getMessage();
    }
}

if (isset($_SESSION['mensaje_exito_factura'])) {
    $mensaje_estado = $_SESSION['mensaje_exito_factura'];
    unset($_SESSION['mensaje_exito_factura']);
}

// --- Carga de datos ---
$formas_pago_disponibles = $pdo->query("SELECT * FROM CAT_PAGO")->fetchAll(PDO::FETCH_ASSOC);

if (empty($id_servicio_param)) {
    $mensaje_estado = "Error: ID de servicio no proporcionado.";
} else {
    try {
        $stmt_servicio = $pdo->prepare("
            SELECT s.*, r.direccion_origen, r.direccion_destino, cl.nombre AS nombre_cliente, co.nombre AS nombre_conductor,
                   ts.tipo AS tipo_servicio_nombre, cc.nombre_categoria AS categoria_nombre,
                   f.id_factura, f.total AS total_factura, f.fecha_emision, f.estado_pago
            FROM SERVICIO s
            JOIN RUTA_SERVICIO r ON s.id_servicio = r.id_servicio
            JOIN CLIENTE cl ON s.id_cliente = cl.id_cliente
            LEFT JOIN CONDUCTOR co ON s.id_conductor = co.id_conductor
            LEFT JOIN TIPO_SERVICIO ts ON s.id_tipo = ts.id_tipo
            LEFT JOIN CAT_CATEGORIA cc ON s.id_categoria = cc.id_categoria
            LEFT JOIN FACTURA f ON s.id_servicio = f.id_servicio
            WHERE s.id_servicio = ?
        ");
        $stmt_servicio->execute([$id_servicio_param]);
        $servicio = $stmt_servicio->fetch(PDO::FETCH_ASSOC);

        if (!$servicio) {
            $mensaje_estado = "Error: Servicio no encontrado.";
        } elseif ($servicio['id_factura'] === null) {
            $mensaje_estado = "La factura para este servicio aún no ha sido generada.";
        } else {
            $factura = [
                'id_factura' => $servicio['id_factura'],
                'total' => $servicio['total_factura'],
                'fecha_emision' => $servicio['fecha_emision'],
                'estado_pago' => $servicio['estado_pago']
            ];
        }
    } catch (Exception $e) {
        $mensaje_estado = "Error en la base de datos: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #<?php echo htmlspecialchars($factura['id_factura'] ?? 'N/A'); ?> | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/factura.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-factura">
            <?php if (!empty($mensaje_estado)): ?>
                <div class="alerta-estado <?php echo strpos($mensaje_estado, 'Error') !== false ? 'alerta-error' : 'alerta-exito'; ?>">
                    <?php echo $mensaje_estado; ?>
                </div>
            <?php endif; ?>

            <?php if ($factura && $servicio): ?>
                <div class="acciones-imprimir no-print">
                    <button onclick="window.print()" class="boton-primario">Imprimir o Guardar como PDF</button>
                </div>
                <div class="factura-container">
                    <header class="factura-header-pro">
                        <div class="logo-empresa">
                            <h1>MoviApp</h1>
                            <p>Aventureros S.A.</p>
                        </div>
                        <div class="datos-factura">
                            <h2>Factura</h2>
                            <p><span>Factura #:</span> <?php echo htmlspecialchars($factura['id_factura']); ?></p>
                            <p><span>Fecha:</span> <?php echo date("d/m/Y", strtotime($factura['fecha_emision'])); ?></p>
                        </div>
                    </header>

                    <section class="factura-info-clientes">
                        <div class="info-box">
                            <h3>Facturado a</h3>
                            <p><?php echo htmlspecialchars($servicio['nombre_cliente']); ?></p>
                        </div>
                    </section>

                    <section class="factura-items-pro">
                        <table>
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th class="text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        Servicio de Transporte (<?php echo htmlspecialchars($servicio['tipo_servicio_nombre']); ?>)
                                        <small>Ruta: <?php echo htmlspecialchars($servicio['direccion_origen']); ?> &rarr; <?php echo htmlspecialchars($servicio['direccion_destino']); ?></small>
                                    </td>
                                    <td class="text-right">$<?php echo number_format($factura['total'], 2, ',', '.'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </section>

                    <section class="factura-total-final">
                        <div class="total-pro">
                            <span>Total</span>
                            <span>$<?php echo number_format($factura['total'], 2, ',', '.'); ?></span>
                        </div>
                        <div class="estado-pago-final">
                            <span>Estado:</span>
                            <span class="estado-pago-pro estado-<?php echo strtolower($factura['estado_pago']); ?>">
                                <?php echo ucfirst($factura['estado_pago']); ?>
                            </span>
                        </div>
                    </section>

                    <?php if ($rol === 'cliente' && $factura['estado_pago'] === 'pendiente'): ?>
                        <div class="acciones-factura no-print">
                            <h3>Realizar Pago</h3>
                            <form method="POST" action="factura.php?id_servicio=<?php echo $id_servicio_param; ?>">
                                <input type="hidden" name="id_factura" value="<?php echo $factura['id_factura']; ?>">
                                <div class="campo-formulario">
                                    <label for="id_pago">Seleccionar Método de Pago</label>
                                    <select name="id_pago" id="id_pago" required>
                                        <?php foreach ($formas_pago_disponibles as $forma): ?>
                                            <option value="<?php echo $forma['id_pago']; ?>"><?php echo htmlspecialchars($forma['forma_pago']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" name="pagar_factura" class="boton-primario">Pagar $<?php echo number_format($factura['total'], 2, ',', '.'); ?></button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <footer class="factura-footer-pro">
                        <p>Gracias por elegir MoviApp.</p>
                    </footer>
                </div>
                <a href="historial.php" class="btn-volver no-print">Volver al Historial</a>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>