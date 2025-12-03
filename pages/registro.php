<?php
session_start();
include '../includes/conexion.php';

// --- Lógica para obtener datos de catálogos ---
$generos = $pdo->query("SELECT id_genero, nombre_genero FROM CAT_GENERO")->fetchAll(PDO::FETCH_ASSOC);
$nacionalidades = $pdo->query("SELECT id_nacionalidad, nombre_nacionalidad FROM CAT_NACIONALIDAD")->fetchAll(PDO::FETCH_ASSOC);

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = $_POST['nombre_usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $rol = $_POST['rol'] ?? ''; // Obtener el rol del formulario

    // Datos comunes
    $id_persona = $_POST['id_persona'] ?? ''; // ID para cliente o conductor
    $nombre_completo = $_POST['nombre_completo'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $id_genero = $_POST['genero'] ?? '';
    $id_nacionalidad = $_POST['nacionalidad'] ?? '';
    $fotografia_path = null; // Solo para conductores

    // --- Validaciones ---
    if (empty($nombre_usuario) || empty($password) || empty($password_confirm) || empty($rol) || empty($id_persona) || empty($nombre_completo) || empty($direccion) || empty($telefono) || empty($id_genero) || empty($id_nacionalidad)) {
        $error_message = "Todos los campos son obligatorios.";
    } elseif ($password !== $password_confirm) {
        $error_message = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 8) {
        $error_message = "La contraseña debe tener al menos 8 caracteres.";
    } elseif (!in_array($rol, ['cliente', 'conductor'])) { // Validar roles permitidos
        $error_message = "El rol seleccionado no es válido.";
    }
    else {
        // Validaciones específicas de rol
        if ($rol === 'conductor' && (!isset($_FILES['fotografia']) || $_FILES['fotografia']['error'] !== UPLOAD_ERR_OK)) {
            $error_message = "La fotografía es obligatoria para los conductores.";
        }

        // Verificar si el nombre de usuario ya existe
        $stmt_check_user = $pdo->prepare("SELECT COUNT(*) FROM USUARIO WHERE nombre_usuario = ?");
        $stmt_check_user->execute([$nombre_usuario]);
        if ($stmt_check_user->fetchColumn() > 0) {
            $error_message = "El nombre de usuario '$nombre_usuario' ya está en uso. Por favor, elige otro.";
        }
        
        // Verificar si el ID de persona ya existe en CLIENTE o CONDUCTOR
        if ($rol === 'cliente') {
            $stmt_check_id = $pdo->prepare("SELECT COUNT(*) FROM CLIENTE WHERE id_cliente = ?");
            $stmt_check_id->execute([$id_persona]);
            if ($stmt_check_id->fetchColumn() > 0) {
                $error_message = "La identificación '$id_persona' ya está registrada como cliente.";
            }
        } elseif ($rol === 'conductor') {
            $stmt_check_id = $pdo->prepare("SELECT COUNT(*) FROM CONDUCTOR WHERE id_conductor = ?");
            $stmt_check_id->execute([$id_persona]);
            if ($stmt_check_id->fetchColumn() > 0) {
                $error_message = "La identificación '$id_persona' ya está registrada como conductor.";
            }
        }

        if (empty($error_message)) {
            // --- Inserción en la Base de Datos ---
            try {
                $pdo->beginTransaction();

                // 1. Insertar en la tabla USUARIO
                $stmt_insert_user = $pdo->prepare(
                    "INSERT INTO USUARIO (nombre_usuario, contraseña, rol, estado) VALUES (?, ?, ?, 'activo')"
                );
                $stmt_insert_user->execute([$nombre_usuario, $password, $rol]); // Contraseña en texto plano
                $id_usuario_nuevo = $pdo->lastInsertId();

                // 2. Manejar la subida de la foto si es conductor
                if ($rol === 'conductor' && isset($_FILES['fotografia']) && $_FILES['fotografia']['error'] == 0) {
                    $directorio_subida = '../uploads/fotos_conductores/';
                    $nombre_archivo = uniqid() . '_' . basename($_FILES['fotografia']['name']);
                    $ruta_completa = $directorio_subida . $nombre_archivo;
                    if (move_uploaded_file($_FILES['fotografia']['tmp_name'], $ruta_completa)) {
                        $fotografia_path = 'uploads/fotos_conductores/' . $nombre_archivo;
                    } else {
                        throw new Exception("Error al mover el archivo de fotografía.");
                    }
                }

                // 3. Insertar en la tabla CLIENTE o CONDUCTOR
                if ($rol === 'cliente') {
                    $stmt_insert_persona = $pdo->prepare(
                        "INSERT INTO CLIENTE (id_cliente, nombre, direccion, id_genero, id_nacionalidad, id_usuario) VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmt_insert_persona->execute([$id_persona, $nombre_completo, $direccion, $id_genero, $id_nacionalidad, $id_usuario_nuevo]);
                } elseif ($rol === 'conductor') {
                    $stmt_insert_persona = $pdo->prepare(
                        "INSERT INTO CONDUCTOR (id_conductor, nombre, direccion, fotografia, id_genero, id_nacionalidad, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt_insert_persona->execute([$id_persona, $nombre_completo, $direccion, $fotografia_path, $id_genero, $id_nacionalidad, $id_usuario_nuevo]);
                }

                // 4. Insertar en la tabla TELEFONO
                $stmt_insert_telefono = $pdo->prepare("INSERT INTO TELEFONO (numero, id_cliente, id_conductor) VALUES (?, ?, ?)");
                $stmt_insert_telefono->execute([$telefono, ($rol === 'cliente' ? $id_persona : null), ($rol === 'conductor' ? $id_persona : null)]);

                $pdo->commit();
                // Redirigir al login con mensaje de éxito
                header("Location: login.php?registro=exitoso");
                exit();

            } catch (PDOException $e) {
                $pdo->rollBack();
                $error_message = "Error al registrar el usuario: " . $e->getMessage();
            } catch (Exception $e) {
                $pdo->rollBack();
                $error_message = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario | MoviApp</title>
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/login.css">
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
        .form-group select,
        .form-group input[type="password"],
        .form-group input[type="file"] { /* Added file input type */
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
    <div class="contenedor-login">
        <main class="form-container">
            <div class="encabezado-login">
                <div class="contenedor-marca">
                    <img src="https://api.iconify.design/lucide-car.svg?color=%230f172a" alt="Logo MoviApp" class="icono-marca">
                    <span class="texto-titulo nombre-marca">MoviApp</span>
                </div>
                <h1 class="texto-titulo titulo-bienvenida">Crear una cuenta</h1>
                <p class="subtitulo-login">Ingresa tus datos para registrarte</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alerta-error" style="padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form action="registro.php" method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group form-full-width">
                        <label for="rol" class="etiqueta">Tipo de Cuenta</label>
                        <select name="rol" id="rol" class="campo-entrada" required>
                            <option value="">Selecciona un rol...</option>
                            <option value="cliente">Cliente</option>
                            <option value="conductor">Conductor</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="nombre_usuario" class="etiqueta">Nombre de Usuario (para iniciar sesión)</label>
                        <div class="contenedor-input">
                            <input type="text" id="nombre_usuario" name="nombre_usuario" class="campo-entrada" placeholder="Ej. jose_perez" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="etiqueta">Contraseña (mín. 8 caracteres)</label>
                        <div class="contenedor-input">
                            <input type="password" id="password" name="password" class="campo-entrada" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm" class="etiqueta">Confirmar Contraseña</label>
                        <div class="contenedor-input">
                            <input type="password" id="password_confirm" name="password_confirm" class="campo-entrada" placeholder="••••••••" required>
                        </div>
                    </div>

                    <hr class="form-full-width" style="margin: 2rem 0; border: none; border-top: 1px solid #e2e8f0;">
                    <h2 class="texto-titulo titulo-formulario form-full-width">Datos Personales</h2>

                    <div class="form-group">
                        <label for="id_persona" class="etiqueta">Identificación</label>
                        <div class="contenedor-input">
                            <input type="text" id="id_persona" name="id_persona" class="campo-entrada" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nombre_completo" class="etiqueta">Nombre Completo</label>
                        <div class="contenedor-input">
                            <input type="text" id="nombre_completo" name="nombre_completo" class="campo-entrada" required>
                        </div>
                    </div>
                    <div class="form-group form-full-width">
                        <label for="direccion" class="etiqueta">Dirección</label>
                        <div class="contenedor-input">
                            <input type="text" id="direccion" name="direccion" class="campo-entrada" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="telefono" class="etiqueta">Teléfono</label>
                        <div class="contenedor-input">
                            <input type="text" id="telefono" name="telefono" class="campo-entrada" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="genero" class="etiqueta">Género</label>
                        <select id="genero" name="genero" class="campo-entrada" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($generos as $genero): ?>
                                <option value="<?php echo htmlspecialchars($genero['id_genero']); ?>">
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
                                <option value="<?php echo htmlspecialchars($nacionalidad['id_nacionalidad']); ?>">
                                    <?php echo htmlspecialchars($nacionalidad['nombre_nacionalidad']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group form-full-width" id="campo-fotografia" style="display: none;">
                        <label for="fotografia" class="etiqueta">Fotografía (solo para conductores)</label>
                        <div class="contenedor-input">
                            <input type="file" id="fotografia" name="fotografia" accept="image/*" class="campo-entrada">
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="boton-primario">
                        Crear Cuenta
                    </button>
                </div>

                <div class="enlace-registro">
                    <p>¿Ya tienes una cuenta? <a href="login.php">Inicia Sesión</a></p>
                </div>
            </form>
        </main>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rolSelect = document.getElementById('rol');
            const campoFotografia = document.getElementById('campo-fotografia');

            function toggleFotografiaField() {
                if (rolSelect.value === 'conductor') {
                    campoFotografia.style.display = 'flex';
                    campoFotografia.querySelector('input').setAttribute('required', 'required');
                } else {
                    campoFotografia.style.display = 'none';
                    campoFotografia.querySelector('input').removeAttribute('required');
                }
            }

            rolSelect.addEventListener('change', toggleFotografiaField);
            toggleFotografiaField(); // Ejecutar al cargar la página por si hay un valor preseleccionado
        });
    </script>
</body>
</html>
