<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$rol = $_SESSION['user_role'] ?? '';
$view_mode = '';
$mensaje = '';

// --- LÓGICA DE MANEJO DE FORMULARIOS (SOLO ADMIN) ---
if ($rol == 'administrador' && $_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- AGREGAR NUEVO CONDUCTOR Y USUARIO ---
    if (isset($_POST['agregar_conductor'])) {
        $id_conductor = $_POST['id_conductor']; // Este es el DNI/Cédula
        $nombre = $_POST['nombre'];
        $direccion = $_POST['direccion'];
        $id_genero = $_POST['id_genero'];
        $id_nacionalidad = $_POST['id_nacionalidad'];
        $fotografia_path = null;
        $telefono = $_POST['telefono']; // Add this line

        // El nombre de usuario será su número de identificación
        $nombre_usuario = $id_conductor;
        $default_password = password_hash('password123', PASSWORD_DEFAULT);

        $pdo->beginTransaction();

        try {
            // 1. Crear el registro en la tabla USUARIO
            $stmt_user = $pdo->prepare(
                "INSERT INTO USUARIO (nombre_usuario, contraseña, rol, estado) VALUES (?, ?, 'conductor', 'activo')"
            );
            $stmt_user->execute([$nombre_usuario, $default_password]);
            $id_usuario_nuevo = $pdo->lastInsertId();

            // 2. Manejar la subida de la foto
            if (isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == 0) {
                $directorio_subida = '../uploads/fotos_conductores/';
                $nombre_archivo = uniqid() . '_' . basename($_FILES['fotografia']['name']);
                $ruta_completa = $directorio_subida . $nombre_archivo;
                if (move_uploaded_file($_FILES['fotografia']['tmp_name'], $ruta_completa)) {
                    $fotografia_path = 'uploads/fotos_conductores/' . $nombre_archivo;
                } else {
                    throw new Exception("Error al mover el archivo subido.");
                }
            }

            // 3. Crear el registro en la tabla CONDUCTOR
            $stmt_driver = $pdo->prepare(
                "INSERT INTO CONDUCTOR (id_conductor, nombre, direccion, fotografia, id_genero, id_nacionalidad, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt_driver->execute([$id_conductor, $nombre, $direccion, $fotografia_path, $id_genero, $id_nacionalidad, $id_usuario_nuevo]);

            // 4. Insertar teléfono
            $stmt_telefono = $pdo->prepare("INSERT INTO TELEFONO (numero, id_conductor) VALUES (?, ?)");
            $stmt_telefono->execute([$telefono, $id_conductor]);

            $pdo->commit();
            $_SESSION['mensaje_exito'] = "Conductor agregado exitosamente. Su usuario es '$nombre_usuario' y la contraseña temporal es 'password123'.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['mensaje_error'] = "Error al agregar el conductor: " . $e->getMessage();
        }
        header("Location: conductores.php");
        exit();
    }

    // --- DESACTIVAR USUARIO DEL CONDUCTOR ---
    if (isset($_POST['eliminar_conductor'])) {
        $id_usuario_eliminar = $_POST['id_usuario'];
        try {
            $stmt = $pdo->prepare("UPDATE USUARIO SET estado = 'inactivo' WHERE id_usuario = ?");
            $stmt->execute([$id_usuario_eliminar]);
            $_SESSION['mensaje_exito'] = "Conductor desactivado exitosamente.";
        } catch (PDOException $e) {
            $_SESSION['mensaje_error'] = "Error al desactivar el conductor: " . $e->getMessage();
        }
        header("Location: conductores.php");
        exit();
    }
}

// --- LÓGICA DE CONSULTA DE DATOS (SEGÚN ROL) ---
if ($rol == 'administrador') {
    $view_mode = 'admin_list';
    try {
        // Usamos la nueva vista para obtener la lista y disponibilidad de los conductores
        $stmt_conductores = $pdo->query(
            "SELECT id_conductor, nombre, direccion, fotografia, nombre_genero, nombre_nacionalidad, id_usuario, telefono, disponibilidad, disponible_en
             FROM VISTA_CONDUCTORES_DISPONIBILIDAD
             WHERE estado_usuario = 'activo'
             ORDER BY nombre ASC"
        );
        $conductores = $stmt_conductores->fetchAll(PDO::FETCH_ASSOC);

        // Estas consultas siguen siendo necesarias para el formulario de "Agregar Conductor"
        $generos = $pdo->query("SELECT * FROM CAT_GENERO")->fetchAll(PDO::FETCH_ASSOC);
        $nacionalidades = $pdo->query("SELECT * FROM CAT_NACIONALIDAD")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al consultar la base de datos: " . $e->getMessage());
    }
} elseif ($rol == 'conductor') {
    $view_mode = 'driver_profile';
    try {
        $stmt = $pdo->prepare(
            "SELECT c.id_conductor, c.nombre, c.direccion, c.fotografia, g.nombre_genero, n.nombre_nacionalidad, u.estado, t.numero AS telefono
             FROM CONDUCTOR c
             JOIN USUARIO u ON c.id_usuario = u.id_usuario
             JOIN CAT_GENERO g ON c.id_genero = g.id_genero
             JOIN CAT_NACIONALIDAD n ON c.id_nacionalidad = n.id_nacionalidad
             LEFT JOIN TELEFONO t ON c.id_conductor = t.id_conductor
             WHERE c.id_usuario = ?"
        );
        $stmt->execute([$_SESSION['user_id']]);
        $conductor_perfil = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error al consultar tu perfil: " . $e->getMessage());
    }
} else {
    $view_mode = 'access_denied';
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
    <title>Conductores | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/conductores.css">
    <style>
        .formulario-agregar, .tarjeta-perfil, .acceso-denegado { background-color: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem; margin: 2rem auto; max-width: 1200px; }
        .formulario-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .campo-formulario { display: flex; flex-direction: column; }
        .campo-formulario label { margin-bottom: 0.5rem; font-weight: 600; color: #334155; }
        .campo-formulario input, .campo-formulario select { padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem; font-size: 1rem; }
        .acciones-formulario { grid-column: 1 / -1; display: flex; justify-content: flex-end; gap: 1rem; }
        .alerta { padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; border: 1px solid transparent; }
        .alerta-exito { background-color: #dcfce7; color: #166534; border-color: #bbf7d0; }
        .alerta-error { background-color: #fee2e2; color: #991b1b; border-color: #fecaca; }
        .perfil-layout { display: flex; gap: 2rem; flex-wrap: wrap; }
        .perfil-foto { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; }
        .perfil-info { flex: 1; }
        .perfil-info h3 { margin-top: 0; }
        .perfil-info p { margin: 0.5rem 0; line-height: 1.6; }
        .perfil-info strong { color: #334155; }
        .btn-rojo { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .btn-rojo:hover { background-color: #fca5a5; color: #7f1d1d; }
        .badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; display: inline-block; }
        .badge-disponible { background-color: #dcfce7; color: #166534; }
        .badge-ocupado { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-conductores">

            <?php if ($view_mode == 'admin_list'): ?>
                <div class="encabezado-tabla">
                    <h2 class="titulo-seccion">Directorio de Conductores Activos</h2>
                    <button id="btn-mostrar-formulario" class="boton-agregar">+ Agregar conductor</button>
                </div>
                <?php echo $mensaje; ?>
                <div id="formulario-agregar" class="formulario-agregar" style="display:none;">
                    <h3 class="titulo-seccion" style="grid-column: 1 / -1; margin-bottom: 1rem;">Registrar Nuevo Conductor</h3>
                    <form method="POST" action="conductores.php" enctype="multipart/form-data" class="formulario-grid">
                        <div class="campo-formulario"><label for="id_conductor">Identificación (Será su nombre de usuario)</label><input type="text" id="id_conductor" name="id_conductor" required></div>
                        <div class="campo-formulario"><label for="nombre">Nombre Completo</label><input type="text" id="nombre" name="nombre" required></div>
                        <div class="campo-formulario"><label for="direccion">Dirección</label><input type="text" id="direccion" name="direccion" required></div>
                        <div class="campo-formulario"><label for="telefono">Teléfono</label><input type="text" id="telefono" name="telefono" required></div>
                        <div class="campo-formulario"><label for="fotografia">Fotografía</label><input type="file" id="fotografia" name="fotografia" accept="image/*"></div>
                        <div class="campo-formulario"><label for="id_genero">Género</label><select id="id_genero" name="id_genero" required><option value="">Seleccione...</option><?php foreach ($generos as $genero): ?><option value="<?php echo $genero['id_genero']; ?>"><?php echo htmlspecialchars($genero['nombre_genero']); ?></option><?php endforeach; ?></select></div>
                        <div class="campo-formulario"><label for="id_nacionalidad">Nacionalidad</label><select id="id_nacionalidad" name="id_nacionalidad" required><option value="">Seleccione...</option><?php foreach ($nacionalidades as $nacionalidad): ?><option value="<?php echo $nacionalidad['id_nacionalidad']; ?>"><?php echo htmlspecialchars($nacionalidad['nombre_nacionalidad']); ?></option><?php endforeach; ?></select></div>
                        <div class="acciones-formulario"><button type="button" id="btn-cancelar" class="btn-accion">Cancelar</button><button type="submit" name="agregar_conductor" class="boton-agregar">Guardar Conductor</button></div>
                    </form>
                </div>
                <div class="contenedor-tabla">
                    <table class="tabla-conductores">
                        <thead><tr><th class="celda-header">Foto</th><th class="celda-header">Nombre</th><th class="celda-header">Disponibilidad</th><th class="celda-header">Teléfono</th><th class="celda-header">Disponible en</th><th class="celda-header">Acciones</th></tr></thead>
                        <tbody>
                            <?php foreach($conductores as $conductor): ?>
                                <tr>
                                    <td class="celda"><img src="../<?php echo htmlspecialchars($conductor['fotografia'] ?? 'https://api.iconify.design/iconoir-profile-circle.svg?color=%2364748b'); ?>" alt="Foto" class="foto-tabla"></td>
                                    <td class="celda texto-destacado"><?php echo htmlspecialchars($conductor['nombre']); ?></td>
                                    <td class="celda">
                                        <span class="badge <?php echo $conductor['disponibilidad'] == 'Disponible' ? 'badge-disponible' : 'badge-ocupado'; ?>">
                                            <?php echo htmlspecialchars($conductor['disponibilidad']); ?>
                                        </span>
                                    </td>
                                    <td class="celda"><?php echo htmlspecialchars($conductor['telefono'] ?? 'N/A'); ?></td>
                                    <td class="celda">
                                        <?php 
                                        if ($conductor['disponibilidad'] == 'Ocupado' && !empty($conductor['disponible_en'])) {
                                            // Formatear la fecha para que sea más legible
                                            $fecha = new DateTime($conductor['disponible_en']);
                                            echo $fecha->format('d/m/Y H:i');
                                        } else {
                                            echo 'Ahora mismo';
                                        }
                                        ?>
                                    </td>
                                    <td class="celda">
                                        <div class="grupo-acciones">
                                            <a href="editar_conductor.php?id=<?php echo htmlspecialchars($conductor['id_conductor']); ?>" class="btn-accion">Editar</a>
                                            <form method="POST" action="conductores.php" onsubmit="return confirm('¿Estás seguro de que quieres desactivar a este conductor?');" style="display:inline;">
                                                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($conductor['id_usuario']); ?>">
                                                <button type="submit" name="eliminar_conductor" class="btn-accion btn-rojo">Desactivar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($view_mode == 'driver_profile'): ?>
                <div class="tarjeta-perfil">
                    <h2 class="titulo-seccion">Mi Perfil de Conductor</h2>
                    <hr style="border:none; border-top:1px solid #e2e8f0; margin: 1.5rem 0;">
                    <?php if ($conductor_perfil): ?>
                        <div class="perfil-layout">
                            <img src="../<?php echo htmlspecialchars($conductor_perfil['fotografia'] ?? 'https://api.iconify.design/iconoir-profile-circle.svg?color=%2364748b&width=150&height=150'); ?>" alt="Foto de perfil" class="perfil-foto">
                            <div class="perfil-info">
                                <h3><?php echo htmlspecialchars($conductor_perfil['nombre']); ?></h3>
                                <p><strong>Identificación:</strong> <?php echo htmlspecialchars($conductor_perfil['id_conductor']); ?></p>
                                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($conductor_perfil['direccion']); ?></p>
                                <p><strong>Género:</strong> <?php echo htmlspecialchars($conductor_perfil['nombre_genero']); ?></p>
                                <p><strong>Nacionalidad:</strong> <?php echo htmlspecialchars($conductor_perfil['nombre_nacionalidad']); ?></p>
                                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($conductor_perfil['telefono'] ?? 'N/A'); ?></p>
                                <p><strong>Estado de la cuenta:</strong> <span class="badge <?php echo $conductor_perfil['estado'] == 'activo' ? 'badge-disponible' : 'badge-ocupado'; ?>"><?php echo htmlspecialchars(ucfirst($conductor_perfil['estado'])); ?></span></p>
                                <br>
                                <a href="perfil_conductor.php" class="btn-accion">Editar mi información</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>No se pudo encontrar tu perfil de conductor asociado a tu cuenta de usuario.</p>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="acceso-denegado"><h2 class="titulo-seccion">Acceso Denegado</h2><p>No tienes permiso para ver esta página.</p></div>
            <?php endif; ?>

        </section>
    </main>

    <script>
        <?php if ($rol == 'administrador'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const btnMostrar = document.getElementById('btn-mostrar-formulario');
            const btnCancelar = document.getElementById('btn-cancelar');
            const formulario = document.getElementById('formulario-agregar');
            if (btnMostrar) { btnMostrar.addEventListener('click', function() { formulario.style.display = 'block'; this.style.display = 'none'; }); }
            if (btnCancelar) { btnCancelar.addEventListener('click', function() { formulario.style.display = 'none'; btnMostrar.style.display = 'inline-block'; }); }
        });
        <?php endif; ?>
    </script>

</body>
</html>