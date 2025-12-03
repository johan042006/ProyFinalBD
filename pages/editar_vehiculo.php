<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$mensaje_estado = '';
$vehiculo_a_editar = null;
$placa_vehiculo_editar = $_GET['placa'] ?? '';

// Asegurarse de que solo los administradores puedan acceder a esta página
if ($_SESSION['user_role'] !== 'administrador') {
    header("Location: home.php"); // Redirigir si no es administrador
    exit();
}

// --- Lógica para obtener datos de catálogos ---
$tipos_servicio = $pdo->query("SELECT id_tipo, tipo FROM TIPO_SERVICIO ORDER BY tipo")->fetchAll(PDO::FETCH_ASSOC);
$conductores_activos = $pdo->query(
    "SELECT c.id_conductor, c.nombre FROM CONDUCTOR c JOIN USUARIO u ON c.id_usuario = u.id_usuario WHERE u.estado = 'activo' ORDER BY c.nombre"
)->fetchAll(PDO::FETCH_ASSOC);

// --- Lógica para manejar POST (Actualizar Vehículo) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $placa = $_POST['placa'] ?? '';
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $id_tipo = $_POST['id_tipo'] ?? '';
    $id_conductor_titular = $_POST['id_conductor_titular'] ?? '';

    if (empty($placa) || empty($marca) || empty($modelo) || empty($id_tipo) || empty($id_conductor_titular)) {
        $mensaje_estado = "Error: Todos los campos son obligatorios.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE VEHICULO SET marca = ?, modelo = ?, id_tipo = ?, id_conductor_titular = ? WHERE placa = ?");
            $stmt->execute([$marca, $modelo, $id_tipo, $id_conductor_titular, $placa]);

            $mensaje_estado = "Vehículo actualizado exitosamente.";
            // Redirigir para evitar reenvío del formulario y mostrar datos actualizados
            header("Location: editar_vehiculo.php?placa=" . urlencode($placa) . "&status=success");
            exit();

        } catch (PDOException $e) {
            $mensaje_estado = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

// --- Lógica para obtener datos del vehículo a editar ---
if ($placa_vehiculo_editar) {
    $stmt = $pdo->prepare("SELECT * FROM VEHICULO WHERE placa = ?");
    $stmt->execute([$placa_vehiculo_editar]);
    $vehiculo_a_editar = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$vehiculo_a_editar) {
        $mensaje_estado = "Error: Vehículo no encontrado.";
        $placa_vehiculo_editar = null; // Invalidar ID para no mostrar formulario de edición vacío
    }
} else {
    $mensaje_estado = "Error: Placa de vehículo no proporcionada.";
}

// Mostrar mensaje de éxito si viene de una redirección
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $mensaje_estado = "Vehículo actualizado exitosamente.";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Vehículo | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/vehiculos.css"> <!-- Reutilizar estilos de vehículos -->
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .form-container h1 {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        .form-full-width {
            grid-column: 1 / -1;
        }
        .form-actions {
            grid-column: 1 / -1;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #334155;
        }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select {
            padding: 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            font-size: 1rem;
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
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-editar-vehiculo">
            <?php if ($placa_vehiculo_editar && $vehiculo_a_editar): ?>
                <div class="form-container">
                    <h1 class="texto-titulo">Editar Vehículo: <?php echo htmlspecialchars($vehiculo_a_editar['placa']); ?></h1>
                    <?php if (!empty($mensaje_estado)): ?>
                        <div class="alerta-estado"><?php echo $mensaje_estado; ?></div>
                    <?php endif; ?>
                    <form action="editar_vehiculo.php?placa=<?php echo urlencode($placa_vehiculo_editar); ?>" method="POST">
                        <input type="hidden" name="placa" value="<?php echo htmlspecialchars($vehiculo_a_editar['placa']); ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="placa_display">Placa</label>
                                <input type="text" id="placa_display" value="<?php echo htmlspecialchars($vehiculo_a_editar['placa']); ?>" readonly class="campo-entrada">
                            </div>
                            <div class="form-group">
                                <label for="marca">Marca</label>
                                <input type="text" id="marca" name="marca" value="<?php echo htmlspecialchars($vehiculo_a_editar['marca']); ?>" required class="campo-entrada">
                            </div>
                            <div class="form-group">
                                <label for="modelo">Modelo (Año)</label>
                                <input type="number" id="modelo" name="modelo" value="<?php echo htmlspecialchars($vehiculo_a_editar['modelo']); ?>" min="1980" max="<?php echo date('Y') + 1; ?>" required class="campo-entrada">
                            </div>
                            <div class="form-group">
                                <label for="id_tipo">Tipo de Servicio</label>
                                <select id="id_tipo" name="id_tipo" required class="campo-entrada">
                                    <?php foreach ($tipos_servicio as $tipo): ?>
                                        <option value="<?php echo htmlspecialchars($tipo['id_tipo']); ?>"
                                            <?php echo ($vehiculo_a_editar['id_tipo'] == $tipo['id_tipo']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tipo['tipo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group form-full-width">
                                <label for="id_conductor_titular">Conductor Titular</label>
                                <select id="id_conductor_titular" name="id_conductor_titular" required class="campo-entrada">
                                    <?php foreach ($conductores_activos as $conductor): ?>
                                        <option value="<?php echo htmlspecialchars($conductor['id_conductor']); ?>"
                                            <?php echo ($vehiculo_a_editar['id_conductor_titular'] == $conductor['id_conductor']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($conductor['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <a href="vehiculos.php" class="boton-secundario">Cancelar</a>
                            <button type="submit" class="boton-primario">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="form-container">
                    <h1 class="texto-titulo">Error</h1>
                    <p class="alerta-estado" style="background-color: #fee2e2; color: #991b1b; border-color: #fecaca;">No se pudo cargar la información del vehículo.</p>
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="vehiculos.php" class="boton-primario" style="width: auto; display: inline-block;">Volver a Vehículos</a>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>