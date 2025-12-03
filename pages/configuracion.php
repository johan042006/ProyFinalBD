<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

$rol = $_SESSION['user_role'] ?? '';
$id_usuario_logueado = $_SESSION['user_id'] ?? '';
$mensaje_estado = '';

$nombre_completo = '';
$telefono_movil = '';
$email_usuario = $_SESSION['user_name'] ?? ''; // El nombre de usuario es el "email" para login
$capacidad_acompaniantes = ''; // Solo para conductores

// --- Lógica para obtener datos del usuario logueado ---
try {
    if ($rol === 'cliente') {
        $stmt = $pdo->prepare("SELECT c.nombre, t.numero AS telefono FROM CLIENTE c LEFT JOIN TELEFONO t ON c.id_cliente = t.id_cliente WHERE c.id_usuario = ?");
        $stmt->execute([$id_usuario_logueado]);
        $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($datos_usuario) {
            $nombre_completo = $datos_usuario['nombre'];
            $telefono_movil = $datos_usuario['telefono'];
        }
    } elseif ($rol === 'conductor') {
        $stmt = $pdo->prepare("SELECT c.nombre, t.numero AS telefono, v.capacidad_acompaniantes FROM CONDUCTOR c LEFT JOIN TELEFONO t ON c.id_conductor = t.id_conductor LEFT JOIN VEHICULO v ON c.id_conductor = v.id_conductor_titular WHERE c.id_usuario = ?");
        $stmt->execute([$id_usuario_logueado]);
        $datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($datos_usuario) {
            $nombre_completo = $datos_usuario['nombre'];
            $telefono_movil = $datos_usuario['telefono'];
            $capacidad_acompaniantes = $datos_usuario['capacidad_acompaniantes'] ?? '';
        }
    } elseif ($rol === 'administrador') {
        // Para el administrador, solo mostramos el nombre de usuario y un placeholder de teléfono
        $nombre_completo = $_SESSION['user_name'];
        $telefono_movil = 'N/A'; // El admin no tiene un perfil de CLIENTE/CONDUCTOR asociado directamente
    }
} catch (Exception $e) {
    $mensaje_estado = "Error al cargar los datos de configuración: " . $e->getMessage();
}

// --- Lógica para manejar POST (Actualizar Configuración) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nuevo_nombre = $_POST['nombre_completo'] ?? '';
    $nuevo_telefono = $_POST['telefono_movil'] ?? '';
    $nueva_capacidad = $_POST['capacidad_acompaniantes'] ?? ''; // Solo para conductores

    if (empty($nuevo_nombre) || empty($nuevo_telefono)) {
        $mensaje_estado = "Error: Nombre y Teléfono son obligatorios.";
    } else {
        try {
            $pdo->beginTransaction();

            if ($rol === 'cliente') {
                $stmt_update_cliente = $pdo->prepare("UPDATE CLIENTE SET nombre = ? WHERE id_usuario = ?");
                $stmt_update_cliente->execute([$nuevo_nombre, $id_usuario_logueado]);

                $stmt_update_telefono = $pdo->prepare("UPDATE TELEFONO SET numero = ? WHERE id_cliente = (SELECT id_cliente FROM CLIENTE WHERE id_usuario = ?)");
                $stmt_update_telefono->execute([$nuevo_telefono, $id_usuario_logueado]);
                if ($stmt_update_telefono->rowCount() == 0) {
                    $stmt_insert_telefono = $pdo->prepare("INSERT INTO TELEFONO (numero, id_cliente) VALUES (?, (SELECT id_cliente FROM CLIENTE WHERE id_usuario = ?))");
                    $stmt_insert_telefono->execute([$nuevo_telefono, $id_usuario_logueado]);
                }
            } elseif ($rol === 'conductor') {
                $stmt_update_conductor = $pdo->prepare("UPDATE CONDUCTOR SET nombre = ? WHERE id_usuario = ?");
                $stmt_update_conductor->execute([$nuevo_nombre, $id_usuario_logueado]);

                $stmt_update_telefono = $pdo->prepare("UPDATE TELEFONO SET numero = ? WHERE id_conductor = (SELECT id_conductor FROM CONDUCTOR WHERE id_usuario = ?)");
                $stmt_update_telefono->execute([$nuevo_telefono, $id_usuario_logueado]);
                if ($stmt_update_telefono->rowCount() == 0) {
                    $stmt_insert_telefono = $pdo->prepare("INSERT INTO TELEFONO (numero, id_conductor) VALUES (?, (SELECT id_conductor FROM CONDUCTOR WHERE id_usuario = ?))");
                    $stmt_insert_telefono->execute([$nuevo_telefono, $id_usuario_logueado]);
                }

                // Actualizar capacidad de acompañantes si es conductor
                if (!empty($nueva_capacidad)) {
                    // Asumimos que la capacidad se guarda en la tabla VEHICULO del conductor titular
                    // Esto es una simplificación, ya que un conductor puede tener varios vehículos.
                    // Para este caso, actualizamos el vehículo principal del conductor.
                    $stmt_update_capacidad = $pdo->prepare("UPDATE VEHICULO SET capacidad_acompaniantes = ? WHERE id_conductor_titular = (SELECT id_conductor FROM CONDUCTOR WHERE id_usuario = ?)");
                    $stmt_update_capacidad->execute([$nueva_capacidad, $id_usuario_logueado]);
                }
            }
            // Para el administrador, no hay datos de perfil CLIENTE/CONDUCTOR que actualizar aquí.

            $pdo->commit();
            $mensaje_estado = "Configuración actualizada exitosamente.";
            header("Location: configuracion.php?status=success");
            exit();

        } catch (PDOException $e) {
            $pdo->rollBack();
            $mensaje_estado = "Error en la base de datos al actualizar la configuración: " . $e->getMessage();
        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje_estado = "Error: " . $e->getMessage();
        }
    }
}

// Mostrar mensaje de éxito si viene de una redirección
if (isset($_GET['status']) && $_GET['status'] == 'success') {
    $mensaje_estado = "Configuración actualizada exitosamente.";
}

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración | MoviApp</title>
    
    <link rel="stylesheet" href="../styles/global.css">
    <link rel="stylesheet" href="../styles/header.css">
    <link rel="stylesheet" href="../styles/configuracion.css">
</head>
<body>

    <?php include '../includes/header.php'; ?>

    <main class="contenido-principal">
        <section class="seccion-configuracion">
            
            <div class="tarjeta-configuracion">
                <h2 class="titulo-config">Mi Perfil y Preferencias</h2>

                <?php if (!empty($mensaje_estado)): ?>
                    <div class="alerta-estado" style="padding: 1rem; margin-bottom: 1rem; border-radius: 0.5rem; background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; text-align: center;">
                        <?php echo $mensaje_estado; ?>
                    </div>
                <?php endif; ?>

                <form class="form-perfil" action="configuracion.php" method="POST">
                    
                    <div class="grupo-input-config">
                        <label class="etiqueta-config">Nombre completo</label>
                        <input type="text" name="nombre_completo" class="input-config" value="<?php echo htmlspecialchars($nombre_completo); ?>">
                    </div>

                    <div class="grupo-input-config">
                        <label class="etiqueta-config">Teléfono móvil</label>
                        <input type="tel" name="telefono_movil" class="input-config" value="<?php echo htmlspecialchars($telefono_movil); ?>">
                    </div>

                    <div class="grupo-input-config ancho-completo">
                        <label class="etiqueta-config">Nombre de Usuario</label>
                        <input type="text" class="input-config" value="<?php echo htmlspecialchars($email_usuario); ?>" style="background-color: #f1f5f9; color:#64748b;" readonly>
                        <small style="color: #94a3b8; font-size: 0.8rem; margin-top:0.25rem;">El nombre de usuario no se puede cambiar.</small>
                    </div>

                    <?php if ($rol === 'conductor'): ?>
                    <div class="grupo-input-config">
                        <label class="etiqueta-config">Capacidad acompañantes</label>
                        <input type="number" name="capacidad_acompaniantes" class="input-config" value="<?php echo htmlspecialchars($capacidad_acompaniantes); ?>" min="1" max="4">
                    </div>
                    <?php endif; ?>

                    <div class="grupo-input-config ancho-completo">
                        <label class="etiqueta-config">Notificaciones</label>
                        <div class="grupo-notificaciones">
                            
                            <label class="opcion-checkbox">
                                <input type="checkbox" class="input-check" checked>
                                <span class="texto-check">Notificaciones Push (Móvil)</span>
                            </label>

                            <label class="opcion-checkbox">
                                <input type="checkbox" class="input-check" checked>
                                <span class="texto-check">Correos electrónicos promocionales</span>
                            </label>

                            <label class="opcion-checkbox">
                                <input type="checkbox" class="input-check">
                                <span class="texto-check">Mensajes de texto (SMS)</span>
                            </label>

                        </div>
                    </div>

                    <div class="acciones-config">
                        <button type="button" class="btn-cancelar">Cancelar</button>
                        <button type="submit" class="btn-guardar">Guardar cambios</button>
                    </div>

                </form>
            </div>

        </section>
    </main>

</body>
</html>