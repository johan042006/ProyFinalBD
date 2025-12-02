<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Acceso denegado.");
}

$placa_editar = $_GET['placa'] ?? null;
if (!$placa_editar) {
    header("Location: vehiculos.php");
    exit();
}

$mensaje = '';

// Manejar el envío del formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_vehiculo'])) {
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $id_tipo = $_POST['id_tipo'];
    $id_conductor_titular = $_POST['id_conductor_titular'];

    try {
        $stmt = $pdo->prepare(
            "UPDATE VEHICULO SET marca = ?, modelo = ?, id_tipo = ?, id_conductor_titular = ? WHERE placa = ?"
        );
        $stmt->execute([$marca, $modelo, $id_tipo, $id_conductor_titular, $placa_editar]);
        $_SESSION['mensaje_exito'] = "Vehículo actualizado exitosamente.";
        header("Location: vehiculos.php");
        exit();
    } catch (PDOException $e) {
        $mensaje = "<div class='alerta alerta-error'>Error al actualizar el vehículo: " . $e->getMessage() . "</div>";
    }
}

// Obtener los datos actuales del vehículo y las listas para los dropdowns
try {
    $stmt = $pdo->prepare("SELECT * FROM VEHICULO WHERE placa = ?");
    $stmt->execute([$placa_editar]);
    $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vehiculo) {
        header("Location: vehiculos.php");
        exit();
    }

    $tipos_servicio = $pdo->query("SELECT * FROM TIPO_SERVICIO ORDER BY tipo")->fetchAll(PDO::FETCH_ASSOC);
    $conductores_activos = $pdo->query(
        "SELECT c.id_conductor, c.nombre FROM CONDUCTOR c JOIN USUARIO u ON c.id_usuario = u.id_usuario WHERE u.estado = 'activo' ORDER BY c.nombre"
    )->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al consultar la base de datos: " . $e->getMessage());
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
    <link rel="stylesheet" href="../styles/conductores.css"> <!-- Reutilizamos estilos -->
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <div class="formulario-agregar" style="display:block; max-width: 800px;">
            <h2 class="titulo-seccion">Editar Vehículo</h2>
            <p class="subtitulo-login">Modifica los datos del vehículo con placa <?php echo htmlspecialchars($vehiculo['placa']); ?></p>
            <hr style="border:none; border-top:1px solid #e2e8f0; margin: 1.5rem 0;">

            <?php echo $mensaje; ?>

            <form method="POST" action="editar_vehiculo.php?placa=<?php echo htmlspecialchars($placa_editar); ?>" class="formulario-grid">
                <div class="campo-formulario">
                    <label for="placa_display">Placa</label>
                    <input type="text" id="placa_display" value="<?php echo htmlspecialchars($vehiculo['placa']); ?>" disabled>
                </div>
                <div class="campo-formulario">
                    <label for="marca">Marca</label>
                    <input type="text" id="marca" name="marca" value="<?php echo htmlspecialchars($vehiculo['marca']); ?>" required>
                </div>
                <div class="campo-formulario">
                    <label for="modelo">Modelo (Año)</label>
                    <input type="number" id="modelo" name="modelo" min="1980" max="<?php echo date('Y') + 1; ?>" value="<?php echo htmlspecialchars($vehiculo['modelo']); ?>" required>
                </div>
                <div class="campo-formulario">
                    <label for="id_tipo">Tipo de Servicio</label>
                    <select id="id_tipo" name="id_tipo" required>
                        <?php foreach ($tipos_servicio as $tipo): ?>
                            <option value="<?php echo $tipo['id_tipo']; ?>" <?php echo ($vehiculo['id_tipo'] == $tipo['id_tipo']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tipo['tipo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo-formulario" style="grid-column: 1 / -1;">
                    <label for="id_conductor_titular">Conductor Titular</label>
                    <select id="id_conductor_titular" name="id_conductor_titular" required>
                        <?php foreach ($conductores_activos as $conductor): ?>
                            <option value="<?php echo $conductor['id_conductor']; ?>" <?php echo ($vehiculo['id_conductor_titular'] == $conductor['id_conductor']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($conductor['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="acciones-formulario">
                    <a href="vehiculos.php" class="btn-accion">Cancelar</a>
                    <button type="submit" name="editar_vehiculo" class="boton-agregar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
