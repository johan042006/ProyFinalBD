<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$mensaje_estado = '';
$conductor_perfil = null;

// Asegurarse de que solo los conductores puedan acceder a esta página
if ($_SESSION['user_role'] !== 'conductor') {
    header("Location: home.php"); // Redirigir si no es conductor
    exit();
}

$id_usuario_logueado = $_SESSION['user_id'];

// --- Lógica para obtener datos de catálogos ---
$generos = $pdo->query("SELECT id_genero, nombre_genero FROM CAT_GENERO")->fetchAll(PDO::FETCH_ASSOC);
$nacionalidades = $pdo->query("SELECT id_nacionalidad, nombre_nacionalidad FROM CAT_NACIONALIDAD")->fetchAll(PDO::FETCH_ASSOC);

// --- Lógica para manejar POST (Actualizar Perfil de Conductor) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_conductor = $_POST['id_conductor'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $id_genero = $_POST['genero'] ?? '';
    $id_nacionalidad = $_POST['nacionalidad'] ?? '';
    $fotografia_actual = $_POST['fotografia_actual'] ?? null; // Ruta de la foto actual

    // Validar que el ID del conductor que se intenta actualizar coincide con el del usuario logueado
    $stmt_check_id = $pdo->prepare("SELECT id_conductor FROM CONDUCTOR WHERE id_usuario = ?");
    $stmt_check_id->execute([$id_usuario_logueado]);
    $conductor_real_id = $stmt_check_id->fetchColumn();

    if ($id_conductor !== $conductor_real_id) {
        $mensaje_estado = "Error de seguridad: Intento de actualizar un perfil no autorizado.";
    } elseif (empty($id_conductor) || empty($nombre) || empty($direccion) || empty($telefono) || empty($id_genero) || empty($id_nacionalidad)) {
        $mensaje_estado = "Error: Todos los campos son obligatorios.";
    } else {
        try {
            $pdo->beginTransaction();

            $fotografia_path = $fotografia_actual; // Mantener la foto actual por defecto

            // Manejar la subida de una nueva foto
            if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == 0) {
                $directorio_subida = '../uploads/fotos_conductores/';
                $nombre_archivo = uniqid() . '_' . basename($_FILES['fotografia']['name']);
                $ruta_completa = $directorio_subida . $nombre_archivo;
                if (move_uploaded_file($_FILES['fotografia']['tmp_name'], $ruta_completa)) {
                    $fotografia_path = 'uploads/fotos_conductores/' . $nombre_archivo;
                    // Opcional: Eliminar la foto antigua si existe
                    if ($fotografia_actual && file_exists('../' . $fotografia_actual)) {
                        unlink('../' . $fotografia_actual);
                    }
                } else {
                    throw new Exception("Error al mover el archivo subido.");
                }
            }

            // Actualizar conductor
            $stmt = $pdo->prepare("UPDATE CONDUCTOR SET nombre = ?, direccion = ?, fotografia = ?, id_genero = ?, id_nacionalidad = ? WHERE id_conductor = ?");
            $stmt->execute([$nombre, $direccion, $fotografia_path, $id_genero, $id_nacionalidad, $id_conductor]);

            // Actualizar teléfono (asumiendo un solo teléfono por conductor)
            $stmt = $pdo->prepare("UPDATE TELEFONO SET numero = ? WHERE id_conductor = ?");
            $stmt->execute([$telefono, $id_conductor]);
            if ($stmt->rowCount() == 0) { // Si no existía un teléfono, insertarlo
                $stmt = $pdo->prepare("INSERT INTO TELEFONO (numero, id_conductor) VALUES (?, ?)");
                $stmt->execute([$telefono, $id_conductor]);
            }

            $pdo->commit();
            $mensaje_estado = "Perfil actualizado exitosamente.";
            // Redirigir para evitar reenvío del formulario y mostrar datos actualizados
            header("Location: perfil_conductor.php?status=success");
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

// --- Lógica para obtener datos del conductor logueado ---
$stmt = $pdo->prepare("SELECT c.*, t.numero AS telefono FROM CONDUCTOR c LEFT JOIN TELEFONO t ON c.id_conductor = t.id_conductor WHERE c.id_usuario = ?");
$stmt->execute([$id_usuario_logueado]);
$conductor_perfil = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$conductor_perfil) {
    $mensaje_estado = "Error: No se encontró el perfil de conductor asociado a tu cuenta.";
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
    <link rel="stylesheet" href="../styles/conductores.css"> <!-- Reutilizar estilos de conductores -->
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
        .current-photo {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }
        .current-photo img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #e2e8f0;
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
        <section class="seccion-perfil-conductor">
            <?php if ($conductor_perfil): ?>
                <div class="form-container">
                    <h1 class="texto-titulo">Mi Perfil de Conductor</h1>
                    <?php if (!empty($mensaje_estado)): ?>
                        <div class="alerta-estado"><?php echo $mensaje_estado; ?></div>
                    <?php endif; ?>
                    <form action="perfil_conductor.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_conductor" value="<?php echo htmlspecialchars($conductor_perfil['id_conductor']); ?>">
                        <input type="hidden" name="fotografia_actual" value="<?php echo htmlspecialchars($conductor_perfil['fotografia'] ?? ''); ?>">

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="id_conductor_display">Identificación</label>
                                <input type="text" id="id_conductor_display" value="<?php echo htmlspecialchars($conductor_perfil['id_conductor']); ?>" readonly class="campo-entrada">
                            </div>
                            <div class="form-group">
                                <label for="nombre">Nombre Completo</label>
                                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($conductor_perfil['nombre']); ?>" required class="campo-entrada">
                            </div>
                            <div class="form-group form-full-width">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($conductor_perfil['direccion']); ?>" required class="campo-entrada">
                            </div>
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($conductor_perfil['telefono'] ?? ''); ?>" required class="campo-entrada">
                            </div>
                            <div class="form-group">
                                <label for="genero">Género</label>
                                <select id="genero" name="genero" required class="campo-entrada">
                                    <?php foreach ($generos as $genero): ?>
                                        <option value="<?php echo htmlspecialchars($genero['id_genero']); ?>"
                                            <?php echo ($conductor_perfil['id_genero'] == $genero['id_genero']) ? 'selected' : ''; ?>>
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
                                            <?php echo ($conductor_perfil['id_nacionalidad'] == $nacionalidad['id_nacionalidad']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($nacionalidad['nombre_nacionalidad']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group form-full-width">
                                <label for="fotografia">Cambiar Fotografía</label>
                                <input type="file" id="fotografia" name="fotografia" accept="image/*" class="campo-entrada">
                                <?php if ($conductor_perfil['fotografia']): ?>
                                    <div class="current-photo">
                                        <img src="../<?php echo htmlspecialchars($conductor_perfil['fotografia']); ?>" alt="Foto actual">
                                        <span>Foto actual</span>
                                    </div>
                                <?php endif; ?>
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