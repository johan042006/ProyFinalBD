<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

// --- Verificación de Rol ---
if ($_SESSION['user_role'] !== 'administrador') {
    die("Acceso denegado. Esta página es solo para administradores.");
}

$mensaje_estado = ''; // Inicializar mensaje de estado para el formulario de reportes
$reporte_resultados = [];
$reporte_titulo = '';

if (isset($_GET['tipo_reporte']) && !empty($_GET['tipo_reporte'])) {
    $tipo_reporte = $_GET['tipo_reporte'];
    $fecha_inicio = $_GET['fecha_inicio'] ?? null;
    $fecha_fin = $_GET['fecha_fin'] ?? null;

    // Validar fechas si están presentes
    if ($fecha_inicio && $fecha_fin && $fecha_inicio > $fecha_fin) {
        $mensaje_estado = "Error: La fecha de inicio no puede ser posterior a la fecha de fin.";
    } else {
        $condicion_fecha = '';
        if ($fecha_inicio && $fecha_fin) {
            $condicion_fecha = " AND s.fecha_solicitud BETWEEN :fecha_inicio AND :fecha_fin ";
        } elseif ($fecha_inicio) {
            $condicion_fecha = " AND s.fecha_solicitud >= :fecha_inicio ";
        } elseif ($fecha_fin) {
            $condicion_fecha = " AND s.fecha_solicitud <= :fecha_fin ";
        }

        try {
            switch ($tipo_reporte) {
                case 'valores_totales_servicio':
                    $reporte_titulo = "Valores Totales de Servicios por Tipo y Categoría";
                    $stmt = $pdo->prepare("
                        SELECT 
                            ts.tipo AS tipo_servicio,
                            cc.nombre_categoria AS categoria,
                            SUM(s.valor_total) AS valor_total_acumulado
                        FROM SERVICIO s
                        JOIN TIPO_SERVICIO ts ON s.id_tipo = ts.id_tipo
                        JOIN CAT_CATEGORIA cc ON s.id_categoria = cc.id_categoria
                        WHERE 1=1 " . $condicion_fecha . "
                        GROUP BY ts.tipo, cc.nombre_categoria
                        ORDER BY ts.tipo, cc.nombre_categoria
                    ");
                    break;

                case 'cantidad_servicios_mes':
                    $reporte_titulo = "Cantidad de Servicios por Tipo y Mes";
                    $stmt = $pdo->prepare("
                        SELECT
                            ts.tipo AS tipo_servicio,
                            DATE_FORMAT(s.fecha_solicitud, '%Y-%m') AS mes,
                            COUNT(s.id_servicio) AS cantidad_servicios
                        FROM SERVICIO s
                        JOIN TIPO_SERVICIO ts ON s.id_tipo = ts.id_tipo
                        WHERE 1=1 " . $condicion_fecha . "
                        GROUP BY ts.tipo, mes
                        ORDER BY mes, ts.tipo
                    ");
                    break;

                case 'clientes_periodo':
                    $reporte_titulo = "Clientes que han solicitado servicios en un período";
                    $stmt = $pdo->prepare("
                        SELECT
                            c.id_cliente,
                            c.nombre AS nombre_cliente,
                            COUNT(s.id_servicio) AS cantidad_servicios_solicitados,
                            SUM(s.valor_total) AS gasto_total
                        FROM CLIENTE c
                        JOIN SERVICIO s ON c.id_cliente = s.id_cliente
                        WHERE 1=1 " . $condicion_fecha . "
                        GROUP BY c.id_cliente, c.nombre
                        ORDER BY gasto_total DESC
                    ");
                    break;

                case 'valores_totales_pago':
                    $reporte_titulo = "Valores Totales de Servicios por Medio de Pago";
                    $stmt = $pdo->prepare("
                        SELECT
                            cp.forma_pago,
                            SUM(f.total) AS valor_total_pagos
                        FROM FORMAPAGO_FACTURA fpf
                        JOIN FACTURA f ON fpf.id_factura = f.id_factura
                        JOIN CAT_PAGO cp ON fpf.id_pago = cp.id_pago
                        JOIN SERVICIO s ON f.id_servicio = s.id_servicio
                        WHERE 1=1 " . $condicion_fecha . "
                        GROUP BY cp.forma_pago
                        ORDER BY valor_total_pagos DESC
                    ");
                    break;

                default:
                    $mensaje_estado = "Tipo de reporte no válido.";
                    break;
            }

            if (isset($stmt)) {
                if ($fecha_inicio) $stmt->bindValue(':fecha_inicio', $fecha_inicio . ' 00:00:00');
                if ($fecha_fin) $stmt->bindValue(':fecha_fin', $fecha_fin . ' 23:59:59');
                $stmt->execute();
                $reporte_resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

        } catch (PDOException $e) {
            $mensaje_estado = "Error en la base de datos al generar el reporte: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes y Dashboard | MoviApp</title>
    
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/reportes.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-reportes">
            <h2 class="titulo-dashboard">Generador de Reportes</h2>

            <div class="tarjeta-formulario" style="max-width: 800px; margin: 2rem auto;">
                <h3 class="texto-titulo titulo-formulario">Seleccionar Reporte</h3>
                <form action="reportes.php" method="GET">
                    <div class="grupo-formulario">
                        <label for="tipo_reporte" class="etiqueta">Tipo de Reporte</label>
                        <select id="tipo_reporte" name="tipo_reporte" class="campo-entrada" required>
                            <option value="">Seleccione un tipo de reporte...</option>
                            <option value="valores_totales_servicio">Valores Totales de Servicios por Tipo y Categoría</option>
                            <option value="cantidad_servicios_mes">Cantidad de Servicios por Tipo y Mes</option>
                            <option value="clientes_periodo">Clientes que han solicitado servicios en un período</option>
                            <option value="valores_totales_pago">Valores Totales de Servicios por Medio de Pago</option>
                        </select>
                    </div>
                    <div class="grupo-formulario">
                        <label for="fecha_inicio" class="etiqueta">Fecha de Inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="campo-entrada" value="<?php echo htmlspecialchars($_GET['fecha_inicio'] ?? ''); ?>">
                    </div>
                    <div class="grupo-formulario">
                        <label for="fecha_fin" class="etiqueta">Fecha de Fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="campo-entrada" value="<?php echo htmlspecialchars($_GET['fecha_fin'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="boton-primario">Generar Reporte</button>
                </form>
            </div>

            <div id="resultados-reporte" class="tarjeta-formulario" style="margin: 2rem auto; display: <?php echo isset($_GET['tipo_reporte']) ? 'block' : 'none'; ?>;">
                <h3 class="texto-titulo titulo-formulario">Resultados del Reporte <?php echo htmlspecialchars($reporte_titulo); ?></h3>
                <?php if (!empty($mensaje_estado)): ?>
                    <div class="alerta-estado" style="background-color: #fee2e2; color: #991b1b; border-color: #fecaca;"><?php echo $mensaje_estado; ?></div>
                <?php elseif (!empty($reporte_resultados)): ?>
                    <div class="contenedor-tabla-reportes">
                        <table class="tabla-clientes"> <!-- Reutilizamos la clase tabla-clientes para el estilo -->
                            <thead>
                                <tr>
                                    <?php foreach (array_keys($reporte_resultados[0]) as $columna): ?>
                                        <th><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $columna))); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reporte_resultados as $fila): ?>
                                    <tr>
                                        <?php foreach ($fila as $valor): ?>
                                            <td><?php echo htmlspecialchars($valor); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No se encontraron resultados para el reporte seleccionado.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

</body>
</html>
