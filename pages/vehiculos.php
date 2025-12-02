<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

if ($_SESSION['user_role'] !== 'admin') {
    die("Acceso denegado. Solo los administradores pueden gestionar vehículos.");
}

$mensaje = '';

// --- LÓGICA DE MANEJO DE FORMULARIOS ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- AGREGAR VEHÍCULO ---
    if (isset($_POST['agregar_vehiculo'])) {
        $placa = strtoupper($_POST['placa']);
        $marca = $_POST['marca'];
        $modelo = $_POST['modelo'];
        $id_tipo = $_POST['id_tipo'];
        $id_conductor_titular = $_POST['id_conductor_titular'];

        try {
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM VEHICULO WHERE placa = ?");
            $stmt_check->execute([$placa]);
            if ($stmt_check->fetchColumn() > 0) {
                $_SESSION['mensaje_error'] = "La placa '$placa' ya está registrada.";
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO VEHICULO (placa, marca, modelo, id_tipo, id_conductor_titular, estado) VALUES (?, ?, ?, ?, ?, 'activo')"
                );
                $stmt->execute([$placa, $marca, $modelo, $id_tipo, $id_conductor_titular]);
                $_SESSION['mensaje_exito'] = "Vehículo agregado exitosamente.";
            }
        } catch (PDOException $e) {
            $_SESSION['mensaje_error'] = "Error al agregar el vehículo: " . $e->getMessage();
        }
        header("Location: vehiculos.php");
        exit();
    }

    // --- ELIMINAR (DESACTIVAR) VEHÍCULO ---
    if (isset($_POST['eliminar_vehiculo'])) {
        $placa_eliminar = $_POST['placa'];
        try {
            $stmt = $pdo->prepare("UPDATE VEHICULO SET estado = 'inactivo' WHERE placa = ?");
            $stmt->execute([$placa_eliminar]);
            $_SESSION['mensaje_exito'] = "Vehículo desactivado exitosamente.";
        } catch (PDOException $e) {
            $_SESSION['mensaje_error'] = "Error al desactivar el vehículo: " . $e->getMessage();
        }
        header("Location: vehiculos.php");
        exit();
    }
}

// --- LÓGICA DE CONSULTA DE DATOS ---
try {
    $stmt_vehiculos = $pdo->query(
        "SELECT v.placa, v.marca, v.modelo, ts.tipo as tipo_servicio, c.nombre as nombre_conductor
         FROM VEHICULO v
         JOIN TIPO_SERVICIO ts ON v.id_tipo = ts.id_tipo
         JOIN CONDUCTOR c ON v.id_conductor_titular = c.id_conductor
         WHERE v.estado = 'activo'
         ORDER BY v.marca, v.modelo"
    );
    $vehiculos = $stmt_vehiculos->fetchAll(PDO::FETCH_ASSOC);

    $tipos_servicio = $pdo->query("SELECT * FROM TIPO_SERVICIO ORDER BY tipo")->fetchAll(PDO::FETCH_ASSOC);
    $conductores_activos = $pdo->query(
        "SELECT c.id_conductor, c.nombre FROM CONDUCTOR c JOIN USUARIO u ON c.id_usuario = u.id_usuario WHERE u.estado = 'activo' ORDER BY c.nombre"
    )->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error al consultar la base de datos: " . $e->getMessage());
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
    <title>Vehículos | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/vehiculos.css">
    <link rel="stylesheet" href="../styles/conductores.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-vehiculos">
            
            <div class="encabezado-vehiculos">
                <h2 class="titulo-vehiculos">Flota de Vehículos Activos</h2>
                <button id="btn-mostrar-formulario" class="boton-nuevo-vehiculo">Añadir vehículo</button>
            </div>

            <?php echo $mensaje; ?>

            <div id="formulario-agregar" class="formulario-agregar" style="display:none;">
                <h3 class="titulo-seccion">Registrar Nuevo Vehículo</h3>
                <form method="POST" action="vehiculos.php" class="formulario-grid">
                    <div class="campo-formulario"><label for="placa">Placa</label><input type="text" id="placa" name="placa" required style="text-transform:uppercase;"></div>
                    <div class="campo-formulario"><label for="marca">Marca</label><input type="text" id="marca" name="marca" required></div>
                    <div class="campo-formulario"><label for="modelo">Modelo (Año)</label><input type="number" id="modelo" name="modelo" min="1980" max="<?php echo date('Y') + 1; ?>" required></div>
                    <div class="campo-formulario"><label for="id_tipo">Tipo de Servicio</label><select id="id_tipo" name="id_tipo" required><option value="">Seleccione...</option><?php foreach ($tipos_servicio as $tipo): ?><option value="<?php echo $tipo['id_tipo']; ?>"><?php echo htmlspecialchars($tipo['tipo']); ?></option><?php endforeach; ?></select></div>
                    <div class="campo-formulario" style="grid-column: 1 / -1;"><label for="id_conductor_titular">Conductor Titular</label><select id="id_conductor_titular" name="id_conductor_titular" required><option value="">Seleccione un conductor...</option><?php foreach ($conductores_activos as $conductor): ?><option value="<?php echo $conductor['id_conductor']; ?>"><?php echo htmlspecialchars($conductor['nombre']); ?></option><?php endforeach; ?></select></div>
                    <div class="acciones-formulario"><button type="button" id="btn-cancelar" class="btn-accion">Cancelar</button><button type="submit" name="agregar_vehiculo" class="boton-agregar">Guardar Vehículo</button></div>
                </form>
            </div>

            <div class="contenedor-tabla-vehiculos">
                <table class="tabla-flota">
                    <thead><tr><th class="th-flota">Placa</th><th class="th-flota">Marca / Modelo</th><th class="th-flota">Conductor Asignado</th><th class="th-flota">Tipo Servicio</th><th class="th-flota">Acciones</th></tr></thead>
                    <tbody>
                        <?php if (empty($vehiculos)): ?>
                            <tr><td colspan="5" style="text-align: center; padding: 2rem;">No hay vehículos activos registrados.</td></tr>
                        <?php else: ?>
                            <?php foreach($vehiculos as $vehiculo): ?>
                                <tr class="fila-flota">
                                    <td class="td-flota"><span class="texto-placa"><?php echo htmlspecialchars($vehiculo['placa']); ?></span></td>
                                    <td class="td-flota"><div class="texto-marca"><?php echo htmlspecialchars($vehiculo['marca']); ?></div><div class="texto-modelo"><?php echo htmlspecialchars($vehiculo['modelo']); ?></div></td>
                                    <td class="td-flota"><?php echo htmlspecialchars($vehiculo['nombre_conductor']); ?></td>
                                    <td class="td-flota"><span class="etiqueta-servicio servicio-normal"><?php echo htmlspecialchars($vehiculo['tipo_servicio']); ?></span></td>
                                    <td class="td-flota">
                                        <div class="acciones-vehiculo">
                                            <a href="editar_vehiculo.php?placa=<?php echo htmlspecialchars($vehiculo['placa']); ?>" class="btn-accion-v" title="Editar"><img src="https://api.iconify.design/lucide-pencil.svg?color=%2364748b" width="16"></a>
                                            <form method="POST" action="vehiculos.php" onsubmit="return confirm('¿Estás seguro de que quieres desactivar este vehículo?');" style="display:inline;">
                                                <input type="hidden" name="placa" value="<?php echo htmlspecialchars($vehiculo['placa']); ?>">
                                                <button type="submit" name="eliminar_vehiculo" class="btn-accion-v btn-eliminar" title="Eliminar"><img src="https://api.iconify.design/lucide-trash-2.svg?color=%23ef4444" width="16"></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnMostrar = document.getElementById('btn-mostrar-formulario');
            const btnCancelar = document.getElementById('btn-cancelar');
            const formulario = document.getElementById('formulario-agregar');
            if (btnMostrar) { btnMostrar.addEventListener('click', function() { formulario.style.display = 'block'; this.style.display = 'none'; }); }
            if (btnCancelar) { btnCancelar.addEventListener('click', function() { formulario.style.display = 'none'; btnMostrar.style.display = 'inline-block'; }); }
        });
    </script>

</body>
</html>