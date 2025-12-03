<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$mensaje_estado = '';

// Asegurarse de que solo los conductores puedan acceder a esta página
if ($_SESSION['user_role'] !== 'conductor') {
    header("Location: home.php"); // Redirigir si no es conductor
    exit();
}

$id_usuario_logueado = $_SESSION['user_id'];

// Obtener el id_conductor del usuario logueado
$stmt_get_id_conductor = $pdo->prepare("SELECT id_conductor FROM CONDUCTOR WHERE id_usuario = ?");
$stmt_get_id_conductor->execute([$id_usuario_logueado]);
$id_conductor_titular = $stmt_get_id_conductor->fetchColumn();

if (!$id_conductor_titular) {
    $mensaje_estado = "Error: No se encontró el perfil de conductor asociado a tu cuenta.";
}

// --- Lógica para obtener datos de catálogos ---
$tipos_servicio = $pdo->query("SELECT id_tipo, tipo FROM TIPO_SERVICIO ORDER BY tipo")->fetchAll(PDO::FETCH_ASSOC);

// --- Lógica para manejar POST (Registrar Vehículo) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registrar_vehiculo'])) {
    $placa = strtoupper($_POST['placa'] ?? '');
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $id_tipo = $_POST['id_tipo'] ?? '';

    if (empty($placa) || empty($marca) || empty($modelo) || empty($id_tipo)) {
        $mensaje_estado = "Error: Todos los campos son obligatorios.";
    } elseif (!$id_conductor_titular) {
        $mensaje_estado = "Error: No se pudo asociar el vehículo a tu perfil de conductor.";
    } else {
        try {
            // Verificar si la placa ya existe
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM VEHICULO WHERE placa = ?");
            $stmt_check->execute([$placa]);
            if ($stmt_check->fetchColumn() > 0) {
                $mensaje_estado = "Error: La placa '$placa' ya está registrada.";
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO VEHICULO (placa, marca, modelo, id_tipo, id_conductor_titular, estado) VALUES (?, ?, ?, ?, ?, 'activo')"
                );
                $stmt->execute([$placa, $marca, $modelo, $id_tipo, $id_conductor_titular]);
                $mensaje_estado = "Vehículo registrado exitosamente.";
                // Redirigir para evitar reenvío del formulario
                header("Location: perfil_conductor.php?status=vehiculo_registrado");
                exit();
            }
        } catch (PDOException $e) {
            $mensaje_estado = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

// Mostrar mensaje de éxito si viene de una redirección
if (isset($_GET['status']) && $_GET['status'] == 'vehiculo_registrado') {
    $mensaje_estado = "Vehículo registrado exitosamente.";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Vehículo | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/vehiculos.css"> <!-- Reutilizar estilos de vehículos -->
    <style>
        .form-container {
            max-width: 600px;
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
        <section class="seccion-registrar-vehiculo">
            <div class="form-container">
                <h1 class="texto-titulo">Registrar Nuevo Vehículo</h1>
                <?php if (!empty($mensaje_estado)): ?>
                    <div class="alerta-estado"><?php echo $mensaje_estado; ?></div>
                <?php endif; ?>
                <?php if ($id_conductor_titular): ?>
                    <form action="registrar_vehiculo_conductor.php" method="POST">
                        <input type="hidden" name="registrar_vehiculo" value="1">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="placa">Placa</label>
                                <input type="text" id="placa" name="placa" required class="campo-entrada" style="text-transform:uppercase;">
                            </div>
                            <div class="form-group">
                                <label for="marca">Marca</label>
                                <input type="text" id="marca" name="marca" required class="campo-entrada">
                            </div>
                            <div class="form-group">
                                <label for="modelo">Modelo (Año)</label>
                                <input type="number" id="modelo" name="modelo" min="1980" max="<?php echo date('Y') + 1; ?>" required class="campo-entrada">
                            </div>
                            <div class="form-group">
                                <label for="id_tipo">Tipo de Servicio</label>
                                <select id="id_tipo" name="id_tipo" required class="campo-entrada">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($tipos_servicio as $tipo): ?>
                                        <option value="<?php echo htmlspecialchars($tipo['id_tipo']); ?>">
                                            <?php echo htmlspecialchars($tipo['tipo']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <a href="perfil_conductor.php" class="boton-secundario">Cancelar</a>
                            <button type="submit" class="boton-primario">Registrar Vehículo</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="alerta-estado" style="background-color: #fee2e2; color: #991b1b; border-color: #fecaca;">No se pudo cargar tu perfil de conductor para registrar un vehículo.</p>
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="home.php" class="boton-primario" style="width: auto; display: inline-block;">Volver al Inicio</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
</body>
</html>