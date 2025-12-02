<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$id_conductor_a_editar = $_GET['id'] ?? null;
if (!$id_conductor_a_editar) {
    header("Location: conductores.php");
    exit();
}

// --- Lógica de Autorización ---
$rol_usuario_actual = $_SESSION['user_role'];
$id_usuario_actual = $_SESSION['user_id'];
$permitido = false;

if ($rol_usuario_actual === 'admin') {
    $permitido = true;
} else {
    // Si es conductor, solo puede editar su propio perfil.
    // Buscamos el id_usuario que corresponde al id_conductor de la URL.
    $stmt_auth = $pdo->prepare("SELECT id_usuario FROM CONDUCTOR WHERE id_conductor = ?");
    $stmt_auth->execute([$id_conductor_a_editar]);
    $id_usuario_del_perfil = $stmt_auth->fetchColumn();

    if ($id_usuario_del_perfil == $id_usuario_actual) {
        $permitido = true;
    }
}

if (!$permitido) {
    die("Acceso denegado. No tienes permiso para editar este perfil.");
}
// --- Fin de Lógica de Autorización ---

$id_conductor = $id_conductor_a_editar; // Continuamos usando $id_conductor en el resto del script
$mensaje = '';

// Manejar el envío del formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_conductor'])) {
    $nombre = $_POST['nombre'];
    $direccion = $_POST['direccion'];
    $id_genero = $_POST['id_genero'];
    $id_nacionalidad = $_POST['id_nacionalidad'];
    $fotografia_actual = $_POST['fotografia_actual'];
    $fotografia_path = $fotografia_actual;

    // Manejo de la nueva fotografía si se sube una
    if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == 0) {
        $directorio_subida = '../uploads/fotos_conductores/';
        $nombre_archivo = uniqid() . '_' . basename($_FILES['fotografia']['name']);
        $ruta_completa = $directorio_subida . $nombre_archivo;
        if (move_uploaded_file($_FILES['fotografia']['tmp_name'], $ruta_completa)) {
            $fotografia_path = 'uploads/fotos_conductores/' . $nombre_archivo;
            // Opcional: borrar la foto anterior si existe
            if ($fotografia_actual && file_exists('../' . $fotografia_actual)) {
                unlink('../' . $fotografia_actual);
            }
        } else {
            $_SESSION['mensaje_error'] = "Error al mover el nuevo archivo de foto.";
        }
    }

    if (!isset($_SESSION['mensaje_error'])) {
        try {
            $stmt = $pdo->prepare(
                "UPDATE CONDUCTOR SET nombre = ?, direccion = ?, fotografia = ?, id_genero = ?, id_nacionalidad = ? WHERE id_conductor = ?"
            );
            $stmt->execute([$nombre, $direccion, $fotografia_path, $id_genero, $id_nacionalidad, $id_conductor]);
            $_SESSION['mensaje_exito'] = "Conductor actualizado exitosamente.";
            header("Location: conductores.php");
            exit();
        } catch (PDOException $e) {
            $mensaje = "<div class='alerta alerta-error'>Error al actualizar el conductor: " . $e->getMessage() . "</div>";
        }
    }
}

// Obtener los datos actuales del conductor para pre-llenar el formulario
try {
    $stmt = $pdo->prepare("SELECT * FROM CONDUCTOR WHERE id_conductor = ?");
    $stmt->execute([$id_conductor]);
    $conductor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$conductor) {
        header("Location: conductores.php");
        exit();
    }

    $generos = $pdo->query("SELECT * FROM CAT_GENERO")->fetchAll(PDO::FETCH_ASSOC);
    $nacionalidades = $pdo->query("SELECT * FROM CAT_NACIONALIDAD")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar la base de datos: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Conductor | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/conductores.css">
    <style>
        .formulario-editar { background-color: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem; margin: 2rem auto; max-width: 800px; }
        .formulario-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .campo-formulario { display: flex; flex-direction: column; }
        .campo-formulario label { margin-bottom: 0.5rem; font-weight: 600; color: #334155; }
        .campo-formulario input, .campo-formulario select { padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-size: 1rem; }
        .acciones-formulario { grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;}
        .alerta { padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; border: 1px solid transparent; }
        .alerta-error { background-color: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .foto-preview { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin-top: 1rem; }
    </style>
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <div class="formulario-editar">
            <h2 class="titulo-seccion">Editar Conductor</h2>
            <p class="subtitulo-login">Modifica los datos de <?php echo htmlspecialchars($conductor['nombre']); ?></p>
            <hr style="border:none; border-top:1px solid #e2e8f0; margin: 1.5rem 0;">

            <?php echo $mensaje; ?>

            <form method="POST" action="editar_conductor.php?id=<?php echo htmlspecialchars($id_conductor); ?>" enctype="multipart/form-data" class="formulario-grid">
                <input type="hidden" name="fotografia_actual" value="<?php echo htmlspecialchars($conductor['fotografia']); ?>">
                
                <div class="campo-formulario">
                    <label for="id_conductor_display">Identificación</label>
                    <input type="text" id="id_conductor_display" value="<?php echo htmlspecialchars($conductor['id_conductor']); ?>" disabled>
                </div>
                <div class="campo-formulario">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($conductor['nombre']); ?>" required>
                </div>
                <div class="campo-formulario" style="grid-column: 1 / -1;">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($conductor['direccion']); ?>" required>
                </div>
                <div class="campo-formulario">
                    <label for="id_genero">Género</label>
                    <select id="id_genero" name="id_genero" required>
                        <?php foreach ($generos as $genero): ?>
                            <option value="<?php echo $genero['id_genero']; ?>" <?php echo ($conductor['id_genero'] == $genero['id_genero']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($genero['nombre_genero']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo-formulario">
                    <label for="id_nacionalidad">Nacionalidad</label>
                    <select id="id_nacionalidad" name="id_nacionalidad" required>
                        <?php foreach ($nacionalidades as $nacionalidad): ?>
                            <option value="<?php echo $nacionalidad['id_nacionalidad']; ?>" <?php echo ($conductor['id_nacionalidad'] == $nacionalidad['id_nacionalidad']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($nacionalidad['nombre_nacionalidad']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo-formulario" style="grid-column: 1 / -1;">
                    <label for="fotografia">Cambiar Fotografía (opcional)</label>
                    <input type="file" id="fotografia" name="fotografia" accept="image/*">
                    <?php if ($conductor['fotografia']): ?>
                        <img src="../<?php echo htmlspecialchars($conductor['fotografia']); ?>" alt="Foto actual" class="foto-preview">
                    <?php endif; ?>
                </div>

                <div class="acciones-formulario">
                    <a href="conductores.php" class="btn-accion">Cancelar</a>
                    <button type="submit" name="editar_conductor" class="boton-agregar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
