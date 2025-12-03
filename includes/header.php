<?php
// Asumimos que auth_check.php ya ha iniciado la sesión
$pagina_actual = basename($_SERVER['PHP_SELF']);
$rol = (string)($_SESSION['user_role'] ?? '');
$id_usuario_logueado = $_SESSION['user_id'] ?? '';
?>
<header class="barra-navegacion">
    <div class="contenedor-nav">
        
        <div class="nav-marca">
            <img src="https://api.iconify.design/lucide-car.svg?color=%230f172a" alt="Icono" class="nav-icono">
            <span class="texto-titulo nav-nombre">MoviApp</span>
        </div>

        <nav class="nav-tabs-escritorio">
            <ul class="lista-tabs">
                <?php if ($rol == 'administrador'): ?>
                    <li><a href="home.php" class="enlace-tab <?php echo ($pagina_actual == 'home.php') ? 'activo' : ''; ?>">Home</a></li>
                    <li><a href="clientes.php" class="enlace-tab <?php echo ($pagina_actual == 'clientes.php') ? 'activo' : ''; ?>">Clientes</a></li>
                    <li><a href="conductores.php" class="enlace-tab <?php echo ($pagina_actual == 'conductores.php') ? 'activo' : ''; ?>">Conductores</a></li>
                    <li><a href="vehiculos.php" class="enlace-tab <?php echo ($pagina_actual == 'vehiculos.php') ? 'activo' : ''; ?>">Vehículos</a></li>
                    <li><a href="historial.php" class="enlace-tab <?php echo ($pagina_actual == 'historial.php') ? 'activo' : ''; ?>">Historial</a></li>
                    <li><a href="seguimiento.php" class="enlace-tab <?php echo ($pagina_actual == 'seguimiento.php') ? 'activo' : ''; ?>">Seguimiento</a></li>
                    <li><a href="pagos.php" class="enlace-tab <?php echo ($pagina_actual == 'pagos.php') ? 'activo' : ''; ?>">Pagos</a></li>
                    <li><a href="reportes.php" class="enlace-tab <?php echo ($pagina_actual == 'reportes.php') ? 'activo' : ''; ?>">Reportes</a></li>
                    <li><a href="configuracion.php" class="enlace-tab <?php echo ($pagina_actual == 'configuracion.php') ? 'activo' : ''; ?>">Configuración</a></li>
                <?php else: ?>
                    <!-- Enlaces Comunes para otros roles -->
                    <li><a href="home.php" class="enlace-tab <?php echo ($pagina_actual == 'home.php') ? 'activo' : ''; ?>">Home</a></li>
                    <li><a href="historial.php" class="enlace-tab <?php echo ($pagina_actual == 'historial.php') ? 'activo' : ''; ?>">Historial</a></li>
                    <li><a href="seguimiento.php" class="enlace-tab <?php echo ($pagina_actual == 'seguimiento.php') ? 'activo' : ''; ?>">Seguimiento</a></li>
                    <?php if ($rol === 'conductor'): ?>
                        <li><a href="perfil_conductor.php" class="enlace-tab <?php echo ($pagina_actual == 'perfil_conductor.php') ? 'activo' : ''; ?>">Mi Perfil</a></li>
                    <?php elseif ($rol === 'cliente'): ?>
                        <li><a href="perfil_cliente.php" class="enlace-tab <?php echo ($pagina_actual == 'perfil_cliente.php') ? 'activo' : ''; ?>">Mi Perfil</a></li>
                    <?php endif; ?>
                    <li><a href="configuracion.php" class="enlace-tab <?php echo ($pagina_actual == 'configuracion.php') ? 'activo' : ''; ?>">Configuración</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div>
            <a href="logout.php" class="accion-nav">Cerrar Sesión</a>
        </div>
    </div>
</header>