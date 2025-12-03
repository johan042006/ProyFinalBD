<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

// --- Verificación de Rol ---
if ($_SESSION['user_role'] !== 'administrador') {
    die("Acceso denegado. Esta página es solo para administradores.");
}

// --- Filtro de Fecha Global ---
$fecha_inicio = $_GET['fecha_inicio'] ?? null;
$fecha_fin = $_GET['fecha_fin'] ?? null;
$mensaje_estado = '';

if ($fecha_inicio && $fecha_fin && $fecha_inicio > $fecha_fin) {
    $mensaje_estado = "Error: La fecha de inicio no puede ser posterior a la fecha de fin.";
}

// --- Preparar condiciones de fecha para las consultas ---
$condicion_fecha_servicio = "";
$params_servicio = [];
if ($fecha_inicio) {
    $condicion_fecha_servicio .= " AND s.fecha_solicitud >= :fecha_inicio ";
    $params_servicio[':fecha_inicio'] = $fecha_inicio . ' 00:00:00';
}
if ($fecha_fin) {
    $condicion_fecha_servicio .= " AND s.fecha_solicitud <= :fecha_fin ";
    $params_servicio[':fecha_fin'] = $fecha_fin . ' 23:59:59';
}

$condicion_fecha_factura = "";
$params_factura = [];
if ($fecha_inicio) {
    $condicion_fecha_factura .= " AND f.fecha_emision >= :fecha_inicio ";
    $params_factura[':fecha_inicio'] = $fecha_inicio;
}
if ($fecha_fin) {
    $condicion_fecha_factura .= " AND f.fecha_emision <= :fecha_fin ";
    $params_factura[':fecha_fin'] = $fecha_fin;
}


try {
    // --- Reporte 1: Valores Totales de Servicios (basado en facturas) ---
    $sql1 = "SELECT ts.tipo AS tipo_servicio, cc.nombre_categoria AS categoria, SUM(f.total) AS valor_total_acumulado
             FROM FACTURA f
             JOIN SERVICIO s ON f.id_servicio = s.id_servicio
             JOIN TIPO_SERVICIO ts ON s.id_tipo = ts.id_tipo
             JOIN CAT_CATEGORIA cc ON s.id_categoria = cc.id_categoria
             WHERE 1=1 $condicion_fecha_servicio
             GROUP BY ts.tipo, cc.nombre_categoria ORDER BY ts.tipo, cc.nombre_categoria";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->execute($params_servicio);
    $reporte_valores_totales = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // --- Reporte 2: Cantidad de Servicios por Mes ---
    $sql2 = "SELECT ts.tipo AS tipo_servicio, DATE_FORMAT(s.fecha_solicitud, '%Y-%m') AS mes, COUNT(s.id_servicio) AS cantidad_servicios
             FROM SERVICIO s
             JOIN TIPO_SERVICIO ts ON s.id_tipo = ts.id_tipo
             WHERE 1=1 $condicion_fecha_servicio
             GROUP BY ts.tipo, mes ORDER BY mes, ts.tipo";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute($params_servicio);
    $reporte_cantidad_mes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // --- Reporte 3: Clientes con más Servicios (basado en facturas) ---
    $sql3 = "SELECT c.nombre AS nombre_cliente, COUNT(s.id_servicio) AS cantidad_servicios, SUM(f.total) AS gasto_total
             FROM CLIENTE c
             JOIN SERVICIO s ON c.id_cliente = s.id_cliente
             JOIN FACTURA f ON s.id_servicio = f.id_servicio
             WHERE 1=1 $condicion_fecha_servicio
             GROUP BY c.id_cliente, c.nombre ORDER BY gasto_total DESC LIMIT 5";
    $stmt3 = $pdo->prepare($sql3);
    $stmt3->execute($params_servicio);
    $reporte_top_clientes = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // --- Reporte 4: Valores Totales por Medio de Pago ---
    $sql4 = "SELECT cp.forma_pago, SUM(f.total) AS valor_total_pagos
             FROM FORMAPAGO_FACTURA fpf
             JOIN FACTURA f ON fpf.id_factura = f.id_factura
             JOIN CAT_PAGO cp ON fpf.id_pago = cp.id_pago
             WHERE 1=1 $condicion_fecha_factura
             GROUP BY cp.forma_pago ORDER BY valor_total_pagos DESC";
    $stmt4 = $pdo->prepare($sql4);
    $stmt4->execute($params_factura);
    $reporte_medio_pago = $stmt4->fetchAll(PDO::FETCH_ASSOC);
    
    // --- Reporte 5: Ingresos Anuales ---
    $sql5 = "SELECT DATE_FORMAT(fecha_emision, '%Y') as anio, SUM(total) as total_anual 
             FROM FACTURA 
             GROUP BY anio ORDER BY anio DESC";
    $stmt5 = $pdo->prepare($sql5);
    $stmt5->execute();
    $reporte_ingresos_anuales = $stmt5->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $mensaje_estado = "Error en la base de datos al generar los reportes: " . $e->getMessage();
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-reportes">
            <div class="encabezado-reportes">
                <h2 class="texto-titulo titulo-historial">Dashboard de Reportes</h2>
                <form action="reportes.php" method="GET" class="form-filtro-fecha">
                    <input type="date" name="fecha_inicio" class="campo-fecha" value="<?php echo htmlspecialchars($fecha_inicio); ?>">
                    <input type="date" name="fecha_fin" class="campo-fecha" value="<?php echo htmlspecialchars($fecha_fin); ?>">
                    <button type="submit" class="boton-filtrar">Filtrar</button>
                </form>
            </div>

            <?php if (!empty($mensaje_estado)): ?>
                <div class="alerta-estado"><?php echo $mensaje_estado; ?></div>
            <?php endif; ?>

            <div class="grid-reportes">
                <!-- Reporte 1: Valores Totales por Servicio -->
                <div class="tarjeta-reporte">
                    <h3 class="titulo-reporte">Valores por Tipo y Categoría</h3>
                    <div class="contenedor-grafico">
                        <canvas id="graficoValoresTotales"></canvas>
                    </div>
                </div>

                <!-- Reporte 2: Cantidad de Servicios por Mes -->
                <div class="tarjeta-reporte">
                    <h3 class="titulo-reporte">Servicios por Mes</h3>
                    <div class="contenedor-grafico">
                        <canvas id="graficoCantidadMes"></canvas>
                    </div>
                </div>

                <!-- Reporte 3: Top 5 Clientes -->
                <div class="tarjeta-reporte">
                    <h3 class="titulo-reporte">Top 5 Clientes por Gasto</h3>
                    <div class="contenedor-grafico">
                        <canvas id="graficoTopClientes"></canvas>
                    </div>
                </div>

                <!-- Reporte 4: Medios de Pago -->
                <div class="tarjeta-reporte">
                    <h3 class="titulo-reporte">Distribución por Medio de Pago</h3>
                    <div class="contenedor-grafico">
                        <canvas id="graficoMedioPago"></canvas>
                    </div>
                </div>

                <!-- Reporte 5: Ingresos Anuales -->
                <div class="tarjeta-reporte">
                    <h3 class="titulo-reporte">Ingresos Anuales</h3>
                    <div class="contenedor-grafico">
                        <canvas id="graficoIngresosAnuales"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Gráfico 1: Valores Totales por Servicio ---
        const dataValores = <?php echo json_encode($reporte_valores_totales); ?>;
        if (dataValores.length > 0) {
            const ctx1 = document.getElementById('graficoValoresTotales').getContext('2d');
            const categorias = [...new Set(dataValores.map(item => item.categoria))];
            const tipos = [...new Set(dataValores.map(item => item.tipo_servicio))];
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: tipos,
                    datasets: categorias.map((cat, i) => ({
                        label: cat,
                        data: tipos.map(tipo => dataValores.find(d => d.tipo_servicio === tipo && d.categoria === cat)?.valor_total_acumulado || 0),
                        backgroundColor: `rgba(${50 + i * 80}, 162, 235, 0.7)`
                    }))
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // --- Gráfico 2: Cantidad de Servicios por Mes ---
        const dataCantidad = <?php echo json_encode($reporte_cantidad_mes); ?>;
        if (dataCantidad.length > 0) {
            const ctx2 = document.getElementById('graficoCantidadMes').getContext('2d');
            const meses = [...new Set(dataCantidad.map(item => item.mes))].sort();
            const tipos = [...new Set(dataCantidad.map(item => item.tipo_servicio))];
            new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: meses,
                    datasets: tipos.map((tipo, i) => ({
                        label: tipo,
                        data: meses.map(mes => dataCantidad.find(d => d.mes === mes && d.tipo_servicio === tipo)?.cantidad_servicios || 0),
                        borderColor: `rgba(${50 + i * 150}, 99, 132, 1)`,
                        tension: 0.1,
                        fill: false
                    }))
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // --- Gráfico 3: Top 5 Clientes ---
        const dataClientes = <?php echo json_encode($reporte_top_clientes); ?>;
        if (dataClientes.length > 0) {
            const ctx3 = document.getElementById('graficoTopClientes').getContext('2d');
            new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: dataClientes.map(d => d.nombre_cliente),
                    datasets: [{
                        label: 'Gasto Total',
                        data: dataClientes.map(d => d.gasto_total),
                        backgroundColor: 'rgba(75, 192, 192, 0.7)'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
            });
        }

        // --- Gráfico 4: Medios de Pago ---
        const dataPagos = <?php echo json_encode($reporte_medio_pago); ?>;
        if (dataPagos.length > 0) {
            const ctx4 = document.getElementById('graficoMedioPago').getContext('2d');
            new Chart(ctx4, {
                type: 'doughnut',
                data: {
                    labels: dataPagos.map(d => d.forma_pago),
                    datasets: [{
                        data: dataPagos.map(d => d.valor_total_pagos),
                        backgroundColor: ['rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(255, 206, 86, 0.7)']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // --- Gráfico 5: Ingresos Anuales ---
        const dataIngresosAnuales = <?php echo json_encode($reporte_ingresos_anuales); ?>;
        if (dataIngresosAnuales.length > 0) {
            const ctx5 = document.getElementById('graficoIngresosAnuales').getContext('2d');
            new Chart(ctx5, {
                type: 'bar',
                data: {
                    labels: dataIngresosAnuales.map(d => d.anio),
                    datasets: [{
                        label: 'Total Anual',
                        data: dataIngresosAnuales.map(d => d.total_anual),
                        backgroundColor: 'rgba(255, 159, 64, 0.7)'
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
    });
    </script>

</body>
</html>
