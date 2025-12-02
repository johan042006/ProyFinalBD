<?php
// Asumimos que auth_check.php ya ha iniciado la sesión
$pagina_actual = basename($_SERVER['PHP_SELF']);
$rol = $_SESSION['user_role'] ?? '';
?>
<header class="barra-navegacion">
    <div class="contenedor-nav">
        
        <div class="nav-marca">
            <img src="https://api.iconify.design/lucide-car.svg?color=%230f172a" alt="Icono" class="nav-icono">
            <span class="texto-titulo nav-nombre">MoviApp</span>
        </div>

        <nav class="nav-tabs-escritorio">
            <ul class="lista-tabs">
                <!-- Enlaces Comunes -->
                <li><a href="home.php" class="enlace-tab <?php echo ($pagina_actual == 'home.php') ? 'activo' : ''; ?>">Home</a></li>

                <?php if ($rol == 'admin'): ?>
                    <!-- Enlaces solo para Admin -->
                    <li><a href="solicitud.php" class="enlace-tab <?php echo ($pagina_actual == 'solicitud.php') ? 'activo' : ''; ?>">Solicitud</a></li>
                    <li><a href="seguimiento.php" class="enlace-tab <?php echo ($pagina_actual == 'seguimiento.php') ? 'activo' : ''; ?>">Seguimiento</a></li>
                    <li><a href="historial.php" class="enlace-tab <?php echo ($pagina_actual == 'historial.php') ? 'activo' : ''; ?>">Historial</a></li>
                    <li><a href="conductores.php" class="enlace-tab <?php echo ($pagina_actual == 'conductores.php') ? 'activo' : ''; ?>">Conductores</a></li>
                    <li><a href="vehiculos.php" class="enlace-tab <?php echo ($pagina_actual == 'vehiculos.php') ? 'activo' : ''; ?>">Vehículos</a></li>
                    <li><a href="pagos.php" class="enlace-tab <?php echo ($pagina_actual == 'pagos.php') ? 'activo' : ''; ?>">Pagos</a></li>
                    <li><a href="reportes.php" class="enlace-tab <?php echo ($pagina_actual == 'reportes.php') ? 'activo' : ''; ?>">Reportes</a></li>
                    <li><a href="configuracion.php" class="enlace-tab <?php echo ($pagina_actual == 'configuracion.php') ? 'activo' : ''; ?>">Configuración</a></li>
                <?php elseif ($rol == 'conductor'): ?>
                    <!-- Enlaces solo para Conductor -->
                    <li><a href="seguimiento.php" class="enlace-tab <?php echo ($pagina_actual == 'seguimiento.php') ? 'activo' : ''; ?>">Servicios Asignados</a></li>
                    <li><a href="historial.php" class="enlace-tab <?php echo ($pagina_actual == 'historial.php') ? 'activo' : ''; ?>">Mi Historial</a></li>
                    <li><a href="conductores.php" class="enlace-tab <?php echo ($pagina_actual == 'conductores.php') ? 'activo' : ''; ?>">Mi Perfil</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div>
            <a href="logout.php" class="accion-nav">Cerrar Sesión</a>
        </div>
    </div>
</header>