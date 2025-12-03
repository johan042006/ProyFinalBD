<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$rol = $_SESSION['user_role'] ?? '';
if ($rol !== 'administrador' && $rol !== 'cliente') {
    die("Acceso denegado.");
}

$mensaje = '';

// Lógica para el Administrador
if ($rol === 'administrador') {
    // Añadir nuevo método de pago
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nuevo_metodo'])) {
        $nuevo_metodo = trim($_POST['nuevo_metodo']);
        if (!empty($nuevo_metodo)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO CAT_PAGO (forma_pago) VALUES (?)");
                $stmt->execute([$nuevo_metodo]);
                $mensaje = "Nuevo método de pago '$nuevo_metodo' añadido con éxito.";
            } catch (PDOException $e) {
                $mensaje = "Error al añadir el método de pago: " . $e->getMessage();
            }
        }
    }

    // Obtener métodos de pago existentes
    $stmt = $pdo->query("SELECT forma_pago FROM CAT_PAGO ORDER BY id_pago");
    $metodos_pago = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Lógica para el Cliente
if ($rol === 'cliente') {
    $saldo_actual = 0; // Simulación
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/pagos.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-pagos">
            
            <?php if ($rol === 'cliente'): ?>
                <!-- VISTA PARA EL CLIENTE -->
                <h2 class="titulo-pagos">Mis Métodos de Pago</h2>
                <p id="mensaje-tarjeta" class="alerta-estado" style="display: none; background-color: #dcfce7; color: #166534; border-color: #a7f3d0;"></p>
                <div class="grid-metodos-pago">
                    <article class="tarjeta-metodo">
                        <h3 class="titulo-metodo">Tarjeta Bancaria</h3>
                        <p class="desc-metodo">Agrega una tarjeta para pagos automáticos.</p>
                        <form class="form-tarjeta" id="form-tarjeta">
                            <input type="text" class="campo-entrada" placeholder="0000 0000 0000 0000" required>
                            <input type="text" class="campo-entrada" placeholder="Nombre del titular" required>
                            <div class="fila-flexible">
                                <input type="text" class="campo-entrada" placeholder="MM/AA" required>
                                <input type="text" class="campo-entrada" placeholder="CVV" required>
                            </div>
                            <button type="submit" class="boton-primario">Guardar Tarjeta</button>
                        </form>
                    </article>
                    <article class="tarjeta-metodo">
                        <h3 class="titulo-metodo">Pago en Efectivo</h3>
                        <p class="desc-metodo">Paga al conductor al finalizar el viaje.</p>
                        <label class="contenedor-toggle">
                            <input type="checkbox" class="input-toggle" checked> <div class="visual-toggle"></div>
                            <span class="texto-toggle">Habilitar efectivo</span>
                        </label>
                    </article>
                </div>

            <?php elseif ($rol === 'administrador'): ?>
                <!-- VISTA PARA EL ADMINISTRADOR -->
                <h2 class="titulo-pagos">Gestionar Métodos de Pago</h2>
                <?php if ($mensaje): ?>
                    <div class="alerta-estado" style="background-color: #dbeafe; color: #1e40af;"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                <div class="grid-admin-pagos">
                    <div class="tarjeta-metodo">
                        <h3 class="titulo-metodo">Métodos de Pago Activos</h3>
                        <ul class="lista-metodos">
                            <?php foreach ($metodos_pago as $metodo): ?>
                                <li><?php echo htmlspecialchars($metodo); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="tarjeta-metodo">
                        <h3 class="titulo-metodo">Añadir Nuevo Método</h3>
                        <form method="POST" action="pagos.php">
                            <div class="grupo-input">
                                <label for="nuevo_metodo" class="etiqueta">Nombre del método</label>
                                <input type="text" id="nuevo_metodo" name="nuevo_metodo" class="campo-entrada" required>
                            </div>
                            <button type="submit" class="boton-primario">Añadir Método</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

        </section>
    </main>

    <?php if ($rol === 'cliente'): ?>
    <script>
        document.getElementById('form-tarjeta').addEventListener('submit', function(e) {
            e.preventDefault();
            const mensaje = document.getElementById('mensaje-tarjeta');
            mensaje.textContent = 'Tarjeta guardada con éxito (simulación).';
            mensaje.style.display = 'block';
            setTimeout(() => {
                mensaje.style.display = 'none';
            }, 3000);
            this.reset();
        });
    </script>
    <?php endif; ?>

</body>
</html>
