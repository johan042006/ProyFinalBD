<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$mensaje_estado = '';
$cliente_perfil = null;

// Asegurarse de que solo los clientes (o usuarios sin rol específico de conductor/admin) puedan acceder a esta página
// Asumimos que un usuario sin rol 'administrador' o 'conductor' es un 'cliente' por defecto para esta vista.
if ($_SESSION['user_role'] === 'administrador' || $_SESSION['user_role'] === 'conductor') {
    header("Location: home.php"); // Redirigir si es administrador o conductor
    exit();
}

$id_usuario_logueado = $_SESSION['user_id'];

// --- Lógica para obtener datos de catálogos ---
$generos = $pdo->query("SELECT id_genero, nombre_genero FROM CAT_GENERO")->fetchAll(PDO::FETCH_ASSOC);
$nacionalidades = $pdo->query("SELECT id_nacionalidad, nombre_nacionalidad FROM CAT_NACIONALIDAD")->fetchAll(PDO::FETCH_ASSOC);

// --- Lógica para manejar POST (Actualizar Perfil de Cliente) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = $_POST['id_cliente'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $id_genero = $_POST['genero'] ?? '';
    $id_nacionalidad = $_POST['nacionalidad'] ?? '';

    // Validar que el ID del cliente que se intenta actualizar coincide con el del usuario logueado
    $stmt_check_id = $pdo->prepare("SELECT id_cliente FROM CLIENTE WHERE id_usuario = ?");
    $stmt_check_id->execute([$id_usuario_logueado]);
    $cliente_real_id = $stmt_check_id->fetchColumn();

    if ($id_cliente !== $cliente_real_id) {
        $mensaje_estado = "Error de seguridad: Intento de actualizar un perfil no autorizado.";
    } elseif (empty($id_cliente) || empty($nombre) || empty($direccion) || empty($telefono) || empty($id_genero) || empty($id_nacionalidad)) {
        $mensaje_estado = "Error: Todos los campos son obligatorios.";
    } else {
        try {
            $pdo->beginTransaction();

            // Actualizar cliente
            $stmt = $pdo->prepare("UPDATE CLIENTE SET nombre = ?, direccion = ?, id_genero = ?, id_nacionalidad = ? WHERE id_cliente = ?");
            $stmt->execute([$nombre, $direccion, $id_genero, $id_nacionalidad, $id_cliente]);

            // Actualizar teléfono (asumiendo un solo teléfono por cliente)
            $stmt = $pdo->prepare("UPDATE TELEFONO SET numero = ? WHERE id_cliente = ?");
            $stmt->execute([$telefono, $id_cliente]);
            if ($stmt->rowCount() == 0) { // Si no existía un teléfono, insertarlo
                $stmt = $pdo->prepare("INSERT INTO TELEFONO (numero, id_cliente) VALUES (?, ?)");
                $stmt->execute([$telefono, $id_cliente]);
            }

            $pdo->commit();
            $mensaje_estado = "Perfil actualizado exitosamente.";
            // Redirigir para evitar reenvío del formulario y mostrar datos actualizados
            header("Location: perfil_cliente.php?status=success");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje_estado = "Error en la base de datos: " . $e->getMessage();
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje_estado = "Error: " . $e->getMessage();
        }
    }
}

// --- Lógica para obtener datos del cliente logueado ---
$stmt = $pdo->prepare("SELECT c.*, t.numero AS telefono FROM CLIENTE c LEFT JOIN TELEFONO t ON c.id_cliente = t.id_cliente WHERE c.id_usuario = ?");
$stmt->execute([$id_usuario_logueado]);
$cliente_perfil = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente_perfil) {
    $mensaje_estado = "Error: No se encontró el perfil de cliente asociado a tu cuenta.";
}

// Mostrar mensaje de éxito si viene de una redirección
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $mensaje_estado = "Perfil actualizado exitosamente.";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/clientes.css"> <!-- Reutilizar estilos de clientes -->
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
        <section class="seccion-perfil-cliente">
            <?php if ($cliente_perfil): ?>
                <div class="form-container">
                    <h1 class="texto-titulo">Mi Perfil de Cliente</h1>
                    <?php if (!empty($mensaje_estado)): ?>
                        <div class="alerta-estado"><?php echo $mensaje_estado; ?></div>
                    <?php endif; ?>
                    <form action="perfil_cliente.php" method="POST">
                        <input type="hidden" name="id_cliente" value="<?php echo htmlspecialchars($cliente_perfil['id_cliente']); ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="id_cliente_display">Identificación</label>
                                <input type="text" id="id_cliente_display" value="<?php echo htmlspecialchars($cliente_perfil['id_cliente']); ?>" readonly class="campo-entrada">
                            </div>
                            <div class="form-group">
                                <label for="nombre">Nombre Completo</label>
                                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente_perfil['nombre']); ?>" required class="campo-entrada">
                            </div>
                            <div class="form-group form-full-width">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($cliente_perfil['direccion']); ?>" required class="campo-entrada">
                            </div>
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente_perfil['telefono'] ?? ''); ?>" required class="campo-entrada">
                            </div>
                            <div class="form-group">
                                <label for="genero">Género</label>
                                <select id="genero" name="genero" required class="campo-entrada">
                                    <?php foreach ($generos as $genero): ?>
                                        <option value="<?php echo htmlspecialchars($genero['id_genero']); ?>"
                                            <?php echo ($cliente_perfil['id_genero'] == $genero['id_genero']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($genero['nombre_genero']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="nacionalidad">Nacionalidad</label>
                                <select id="nacionalidad" name="nacionalidad" required class="campo-entrada">
                                    <?php foreach ($nacionalidades as $nacionalidad): ?>
                                        <option value="<?php echo htmlspecialchars($nacionalidad['id_nacionalidad']); ?>"
                                            <?php echo ($cliente_perfil['id_nacionalidad'] == $nacionalidad['id_nacionalidad']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($nacionalidad['nombre_nacionalidad']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <a href="home.php" class="boton-secundario">Volver</a>
                            <button type="submit" class="boton-primario">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="form-container">
                    <h1 class="texto-titulo">Error</h1>
                    <p class="alerta-estado" style="background-color: #fee2e2; color: #991b1b; border-color: #fecaca;">No se pudo cargar la información de tu perfil.</p>
                    <div style="text-align: center; margin-top: 2rem;">
                        <a href="home.php" class="boton-primario" style="width: auto; display: inline-block;">Volver al Inicio</a>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>