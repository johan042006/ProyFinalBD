<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$mensaje_estado = '';
$cliente_a_editar = null;

// --- Lógica para obtener datos de catálogos ---
$generos = $pdo->query("SELECT id_genero, nombre_genero FROM CAT_GENERO")->fetchAll(PDO::FETCH_ASSOC);
$nacionalidades = $pdo->query("SELECT id_nacionalidad, nombre_nacionalidad FROM CAT_NACIONALIDAD")->fetchAll(PDO::FETCH_ASSOC);

// --- Lógica para manejar POST (Añadir/Editar Cliente) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = $_POST['id_cliente'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $id_genero = $_POST['genero'] ?? '';
    $id_nacionalidad = $_POST['nacionalidad'] ?? '';
    $accion = $_POST['accion'] ?? 'añadir'; // 'añadir' o 'editar'

    if (empty($id_cliente) || empty($nombre) || empty($direccion) || empty($telefono) || empty($id_genero) || empty($id_nacionalidad)) {
        $mensaje_estado = "Error: Todos los campos son obligatorios.";
    } else {
        try {
            $pdo->beginTransaction();

            if ($accion == 'añadir') {
                // Insertar nuevo cliente
                $stmt = $pdo->prepare("INSERT INTO CLIENTE (id_cliente, nombre, direccion, id_genero, id_nacionalidad) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$id_cliente, $nombre, $direccion, $id_genero, $id_nacionalidad]);

                // Insertar teléfono
                $stmt = $pdo->prepare("INSERT INTO TELEFONO (numero, id_cliente) VALUES (?, ?)");
                $stmt->execute([$telefono, $id_cliente]);

                $mensaje_estado = "Cliente añadido exitosamente.";
            } elseif ($accion == 'editar') {
                // Actualizar cliente existente
                $stmt = $pdo->prepare("UPDATE CLIENTE SET nombre = ?, direccion = ?, id_genero = ?, id_nacionalidad = ? WHERE id_cliente = ?");
                $stmt->execute([$nombre, $direccion, $id_genero, $id_nacionalidad, $id_cliente]);

                // Actualizar teléfono (asumiendo un solo teléfono por cliente por ahora)
                $stmt = $pdo->prepare("UPDATE TELEFONO SET numero = ? WHERE id_cliente = ?");
                $stmt->execute([$telefono, $id_cliente]);
                // Si no existe, se podría insertar, pero para simplificar asumimos que ya existe o se maneja en la inserción inicial
                if ($stmt->rowCount() == 0) {
                    $stmt = $pdo->prepare("INSERT INTO TELEFONO (numero, id_cliente) VALUES (?, ?)");
                    $stmt->execute([$telefono, $id_cliente]);
                }

                $mensaje_estado = "Cliente actualizado exitosamente.";
            }
            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje_estado = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

// --- Lógica para manejar GET (Editar/Eliminar/Listar Clientes) ---
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'edit' && isset($_GET['id'])) {
        $id_cliente_editar = $_GET['id'];
        $stmt = $pdo->prepare("SELECT c.*, t.numero AS telefono FROM CLIENTE c LEFT JOIN TELEFONO t ON c.id_cliente = t.id_cliente WHERE c.id_cliente = ?");
        $stmt->execute([$id_cliente_editar]);
        $cliente_a_editar = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cliente_a_editar) {
            $mensaje_estado = "Error: Cliente no encontrado para editar.";
        }
    } elseif ($_GET['action'] == 'delete' && isset($_GET['id'])) {
        $id_cliente_eliminar = $_GET['id'];
        try {
            $pdo->beginTransaction();
            // Eliminar teléfonos asociados
            $stmt = $pdo->prepare("DELETE FROM TELEFONO WHERE id_cliente = ?");
            $stmt->execute([$id_cliente_eliminar]);

            // Eliminar cliente
            $stmt = $pdo->prepare("DELETE FROM CLIENTE WHERE id_cliente = ?");
            $stmt->execute([$id_cliente_eliminar]);
            $pdo->commit();
            $mensaje_estado = "Cliente eliminado exitosamente.";
        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje_estado = "Error al eliminar cliente: " . $e->getMessage();
        }
    }
}

// --- Lógica para listar todos los clientes ---
$clientes = $pdo->query("
    SELECT 
        c.id_cliente, 
        c.nombre, 
        c.direccion, 
        t.numero AS telefono, 
        g.nombre_genero AS genero, 
        n.nombre_nacionalidad AS nacionalidad
    FROM CLIENTE c
    LEFT JOIN TELEFONO t ON c.id_cliente = t.id_cliente
    LEFT JOIN CAT_GENERO g ON c.id_genero = g.id_genero
    LEFT JOIN CAT_NACIONALIDAD n ON c.id_nacionalidad = n.id_nacionalidad
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/clientes.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }
        .form-container h1, .form-container h2 {
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
        <section class="seccion-clientes">
            <div class="contenedor-clientes">
                <h1 class="texto-titulo">Gestión de Clientes</h1>
                <p class="subtitulo-login">Aquí puedes ver, añadir, editar o eliminar clientes.</p>

                <?php if (!empty($mensaje_estado)): ?>
                    <div class="alerta-estado" style="padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc;">
                        <?php echo $mensaje_estado; ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario para Añadir/Editar Cliente -->
                <div class="form-container">
                    <h2 class="texto-titulo titulo-formulario"><?php echo $cliente_a_editar ? 'Editar Cliente' : 'Añadir Nuevo Cliente'; ?></h2>
                    <form action="clientes.php" method="POST">
                        <?php if ($cliente_a_editar): ?>
                            <input type="hidden" name="accion" value="editar">
                            <input type="hidden" name="id_cliente_original" value="<?php echo htmlspecialchars($cliente_a_editar['id_cliente']); ?>">
                        <?php else: ?>
                            <input type="hidden" name="accion" value="añadir">
                        <?php endif; ?>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="id_cliente" class="etiqueta">Identificación</label>
                                <input type="text" id="id_cliente" name="id_cliente" class="campo-entrada" value="<?php echo htmlspecialchars($cliente_a_editar['id_cliente'] ?? ''); ?>" <?php echo $cliente_a_editar ? 'readonly' : ''; ?> required>
                            </div>
                            <div class="form-group">
                                <label for="nombre" class="etiqueta">Nombre Completo</label>
                                <input type="text" id="nombre" name="nombre" class="campo-entrada" value="<?php echo htmlspecialchars($cliente_a_editar['nombre'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group form-full-width">
                                <label for="direccion" class="etiqueta">Dirección</label>
                                <input type="text" id="direccion" name="direccion" class="campo-entrada" value="<?php echo htmlspecialchars($cliente_a_editar['direccion'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="telefono" class="etiqueta">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" class="campo-entrada" value="<?php echo htmlspecialchars($cliente_a_editar['telefono'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="genero" class="etiqueta">Género</label>
                                <select id="genero" name="genero" class="campo-entrada" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($generos as $genero): ?>
                                        <option value="<?php echo htmlspecialchars($genero['id_genero']); ?>"
                                            <?php echo ($cliente_a_editar && $cliente_a_editar['id_genero'] == $genero['id_genero']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($genero['nombre_genero']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nacionalidad" class="etiqueta">Nacionalidad</label>
                                <select id="nacionalidad" name="nacionalidad" class="campo-entrada" required>
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($nacionalidades as $nacionalidad): ?>
                                        <option value="<?php echo htmlspecialchars($nacionalidad['id_nacionalidad']); ?>"
                                            <?php echo ($cliente_a_editar && $cliente_a_editar['id_nacionalidad'] == $nacionalidad['id_nacionalidad']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($nacionalidad['nombre_nacionalidad']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="boton-primario">Guardar Cliente</button>
                            <?php if ($cliente_a_editar): ?>
                                <a href="clientes.php" class="boton-secundario">Cancelar Edición</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Tabla de Clientes Existentes -->
                <div class="tarjeta-formulario" style="margin: 2rem auto;">
                    <h2 class="texto-titulo titulo-formulario">Clientes Existentes</h2>
                    <table class="tabla-clientes">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Dirección</th>
                                <th>Teléfono</th>
                                <th>Género</th>
                                <th>Nacionalidad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clientes)): ?>
                                <tr><td colspan="7">No hay clientes registrados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cliente['id_cliente']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['direccion']); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['telefono'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['genero'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($cliente['nacionalidad'] ?? 'N/A'); ?></td>
                                        <td>
                                            <a href="clientes.php?action=edit&id=<?php echo htmlspecialchars($cliente['id_cliente']); ?>" class="boton-accion editar">Editar</a>
                                            <a href="clientes.php?action=delete&id=<?php echo htmlspecialchars($cliente['id_cliente']); ?>" class="boton-accion eliminar" onclick="return confirm('¿Está seguro de eliminar este cliente?');">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </main>
</body>
</html>