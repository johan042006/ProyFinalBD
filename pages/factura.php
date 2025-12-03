<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$rol = $_SESSION['user_role'] ?? '';
$id_usuario_logueado = $_SESSION['user_id'] ?? '';
$mensaje_estado = '';
$factura = null;
$servicio = null;
$formas_pago_factura = [];

$id_servicio_param = $_GET['id_servicio'] ?? '';

if (empty($id_servicio_param)) {
    $mensaje_estado = "Error: ID de servicio no proporcionado.";
} else {
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

        // --- Obtener detalles del servicio y la factura ---
        $stmt_servicio = $pdo->prepare("
            SELECT 
                s.*,
                r.direccion_origen,
                r.direccion_destino,
                cl.nombre AS nombre_cliente,
                co.nombre AS nombre_conductor,
                v.placa AS placa_vehiculo,
                v.marca AS marca_vehiculo,
                v.modelo AS modelo_vehiculo,
                ts.tipo AS tipo_servicio_nombre,
                cc.nombre_categoria AS categoria_nombre,
                f.id_factura,
                f.total AS total_factura,
                f.fecha_emision
            FROM SERVICIO s
            JOIN RUTA_SERVICIO r ON s.id_servicio = r.id_servicio
            JOIN CLIENTE cl ON s.id_cliente = cl.id_cliente
            LEFT JOIN CONDUCTOR co ON s.id_conductor = co.id_conductor
            LEFT JOIN VEHICULO v ON s.placa_vehiculo = v.placa
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
            $mensaje_estado = "Error: No se encontró una factura para este servicio.";
        } else {
            // --- Verificación de Seguridad ---
            $permitir_acceso = false;
            if ($rol === 'administrador') {
                $permitir_acceso = true;
            } elseif ($rol === 'conductor' && $servicio['id_conductor'] === $id_rol_especifico) {
                $permitir_acceso = true;
            } elseif ($rol === 'cliente' && $servicio['id_cliente'] === $id_rol_especifico) {
                $permitir_acceso = true;
            }

            if (!$permitir_acceso) {
                $mensaje_estado = "Error: No tienes permiso para ver esta factura.";
                $servicio = null; // Limpiar datos si no hay permiso
            } else {
                $factura = [
                    'id_factura' => $servicio['id_factura'],
                    'total' => $servicio['total_factura'],
                    'fecha_emision' => $servicio['fecha_emision']
                ];

                // --- Obtener formas de pago de la factura ---
                $stmt_formas_pago = $pdo->prepare("
                    SELECT cp.forma_pago 
                    FROM FORMAPAGO_FACTURA fpf
                    JOIN CAT_PAGO cp ON fpf.id_pago = cp.id_pago
                    WHERE fpf.id_factura = ?
                ");
                $stmt_formas_pago->execute([$factura['id_factura']]);
                $formas_pago_factura = $stmt_formas_pago->fetchAll(PDO::FETCH_ASSOC);
            }
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?php echo htmlspecialchars($factura['id_factura'] ?? 'N/A'); ?> | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/factura.css">
    <style>
        .factura-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            font-family: 'Inter', sans-serif;
            color: #334155;
        }
        .factura-header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
        }
        .factura-header h1 {
            font-size: 2.5rem;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }
        .factura-header p {
            font-size: 1rem;
            color: #64748b;
        }
        .factura-details, .factura-items, .factura-summary {
            margin-bottom: 2rem;
        }
        .factura-details div {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        .factura-details strong {
            color: #0f172a;
        }
        .factura-items table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .factura-items th, .factura-items td {
            border: 1px solid #e2e8f0;
            padding: 0.75rem;
            text-align: left;
        }
        .factura-items th {
            background-color: #f1f5f9;
            font-weight: 600;
            color: #0f172a;
        }
        .factura-summary div {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 1.1rem;
        }
        .factura-summary .total {
            font-weight: 700;
            color: #0f172a;
            font-size: 1.5rem;
            border-top: 2px solid #0f172a;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        .factura-footer {
            text-align: center;
            margin-top: 3rem;
            color: #64748b;
            font-size: 0.9rem;
        }
        .alerta-estado {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
            text-align: center;
        }
        .btn-volver {
            display: block;
            width: fit-content;
            margin: 2rem auto 0;
            padding: 0.75rem 1.5rem;
            background-color: #0f172a;
            color: white;
            border-radius: 0.5rem;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.2s;
        }
        .btn-volver:hover {
            background-color: #1e293b;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-factura">
            <?php if (!empty($mensaje_estado)): ?>
                <div class="alerta-estado" style="background-color: #fee2e2; color: #991b1b; border-color: #fecaca;">
                    <?php echo $mensaje_estado; ?>
                </div>
                <a href="historial.php" class="btn-volver">Volver al Historial</a>
            <?php elseif ($factura && $servicio): ?>
                <div class="factura-container">
                    <div class="factura-header">
                        <h1>Factura #<?php echo htmlspecialchars($factura['id_factura']); ?></h1>
                        <p>Fecha de Emisión: <?php echo date("d M Y", strtotime($factura['fecha_emision'])); ?></p>
                    </div>

                    <div class="factura-details">
                        <div>
                            <span>Cliente:</span>
                            <strong><?php echo htmlspecialchars($servicio['nombre_cliente']); ?></strong>
                        </div>
                        <div>
                            <span>Conductor:</span>
                            <strong><?php echo htmlspecialchars($servicio['nombre_conductor'] ?? 'N/A'); ?></strong>
                        </div>
                        <div>
                            <span>Vehículo:</span>
                            <strong><?php echo htmlspecialchars($servicio['marca_vehiculo'] . ' ' . $servicio['modelo_vehiculo'] . ' (' . $servicio['placa_vehiculo'] . ')') ?? 'N/A'; ?></strong>
                        </div>
                        <div>
                            <span>Origen:</span>
                            <strong><?php echo htmlspecialchars($servicio['direccion_origen']); ?></strong>
                        </div>
                        <div>
                            <span>Destino:</span>
                            <strong><?php echo htmlspecialchars($servicio['direccion_destino']); ?></strong>
                        </div>
                        <div>
                            <span>Tipo de Servicio:</span>
                            <strong><?php echo htmlspecialchars($servicio['tipo_servicio_nombre']); ?></strong>
                        </div>
                        <div>
                            <span>Categoría:</span>
                            <strong><?php echo htmlspecialchars($servicio['categoria_nombre']); ?></strong>
                        </div>
                    </div>

                    <div class="factura-items">
                        <table>
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Servicio de Transporte #<?php echo htmlspecialchars($servicio['id_servicio']); ?></td>
                                    <td>1</td>
                                    <td>$<?php echo number_format($servicio['valor_total'], 0, ',', '.'); ?></td>
                                    <td>$<?php echo number_format($servicio['valor_total'], 0, ',', '.'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="factura-summary">
                        <div>
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($servicio['valor_total'], 0, ',', '.'); ?></span>
                        </div>
                        <div>
                            <span>Impuestos (0%):</span>
                            <span>$0</span>
                        </div>
                        <div class="total">
                            <span>Total Factura:</span>
                            <span>$<?php echo number_format($factura['total'], 0, ',', '.'); ?></span>
                        </div>
                        <div>
                            <span>Formas de Pago:</span>
                            <span>
                                <?php 
                                if (!empty($formas_pago_factura)) {
                                    echo implode(', ', array_column($formas_pago_factura, 'forma_pago'));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="factura-footer">
                        <p>Gracias por usar MoviApp.</p>
                    </div>
                </div>
                <a href="historial.php" class="btn-volver">Volver al Historial</a>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>