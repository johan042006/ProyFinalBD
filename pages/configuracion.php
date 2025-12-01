<?php
    // Simulamos los datos actuales del usuario (traídos de BD)
    $nombre_usuario = "Carlos Estudiante";
    $telefono_usuario = "+57 300 123 4567";
    $email_usuario = "carlos@universidad.edu.co";
    $capacidad_pasajeros = 1;
?>

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

                <form class="form-perfil" action="home.php" method="POST">
                    
                    <div class="grupo-input-config">
                        <label class="etiqueta-config">Nombre completo</label>
                        <input type="text" class="input-config" value="<?php echo $nombre_usuario; ?>">
                    </div>

                    <div class="grupo-input-config">
                        <label class="etiqueta-config">Teléfono móvil</label>
                        <input type="tel" class="input-config" value="<?php echo $telefono_usuario; ?>">
                    </div>

                    <div class="grupo-input-config ancho-completo">
                        <label class="etiqueta-config">Correo electrónico</label>
                        <input type="email" class="input-config" value="<?php echo $email_usuario; ?>" style="background-color: #f1f5f9; color:#64748b;" readonly>
                        <small style="color: #94a3b8; font-size: 0.8rem; margin-top:0.25rem;">El correo no se puede cambiar.</small>
                    </div>

                    <div class="grupo-input-config">
                        <label class="etiqueta-config">Capacidad acompañantes</label>
                        <input type="number" class="input-config" value="<?php echo $capacidad_pasajeros; ?>" min="1" max="4">
                    </div>

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