<?php
include '../includes/auth_check.php';
include '../includes/conexion.php';

// --- Verificación de Rol ---
if ($_SESSION['user_role'] !== 'cliente') {
    header("Location: home.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: home.php");
    exit();
}

$id_usuario_logueado = $_SESSION['user_id'];
$id_conductor = $_POST['id_conductor'] ?? '';
$direccion_origen = $_POST['origen'] ?? '';
$direccion_destino = $_POST['destino'] ?? '';
$id_categoria = $_POST['id_categoria'] ?? 1; // Default a 'Normal'

if (empty($id_conductor) || empty($direccion_origen) || empty($direccion_destino)) {
    $_SESSION['mensaje_error'] = "Faltan datos para crear la solicitud.";
    header("Location: home.php");
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Obtener datos del cliente y del conductor/vehículo
    $stmt_cliente = $pdo->prepare("SELECT id_cliente FROM CLIENTE WHERE id_usuario = ?");
    $stmt_cliente->execute([$id_usuario_logueado]);
    $id_cliente = $stmt_cliente->fetchColumn();

    $stmt_vehiculo = $pdo->prepare("SELECT placa FROM VEHICULO WHERE id_conductor_titular = ?");
    $stmt_vehiculo->execute([$id_conductor]);
    $placa_vehiculo = $stmt_vehiculo->fetchColumn();

    if (!$id_cliente || !$placa_vehiculo) {
        throw new Exception("No se pudieron verificar los datos del cliente o del vehículo.");
    }

    // 2. Calcular valor total con la categoría seleccionada
    $stmt_tarifa = $pdo->query("SELECT tarifa_base_valor, id_tarifa FROM TARIFA_BASE ORDER BY fecha_vigencia DESC LIMIT 1");
    $tarifa_info = $stmt_tarifa->fetch(PDO::FETCH_ASSOC);
    $tarifa_base = $tarifa_info['tarifa_base_valor'] ?? 5000;
    $id_tarifa_actual = $tarifa_info['id_tarifa'] ?? 1;

    $stmt_categoria = $pdo->prepare("SELECT porcentaje_recargo FROM CAT_CATEGORIA WHERE id_categoria = ?");
    $stmt_categoria->execute([$id_categoria]);
    $porcentaje_recargo = $stmt_categoria->fetchColumn();
    
    $valor_total = $tarifa_base * (1 + ($porcentaje_recargo / 100));

    // 3. Asumir tipo de servicio y estado 'asignado'
    $id_tipo_default = 1; // Pasajeros
    $estado_inicial = 'asignado';

    // 4. Insertar en la tabla SERVICIO
    $stmt_servicio = $pdo->prepare(
        "INSERT INTO SERVICIO (fecha_solicitud, estado, valor_total, id_cliente, id_conductor, placa_vehiculo, id_tipo, id_categoria, id_tarifa) 
         VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt_servicio->execute([$estado_inicial, $valor_total, $id_cliente, $id_conductor, $placa_vehiculo, $id_tipo_default, $id_categoria, $id_tarifa_actual]);
    $id_servicio_nuevo = $pdo->lastInsertId();

    // 5. Insertar en la tabla RUTA_SERVICIO
    $stmt_ruta = $pdo->prepare(
        "INSERT INTO RUTA_SERVICIO (id_servicio, direccion_origen, direccion_destino) VALUES (?, ?, ?)"
    );
    $stmt_ruta->execute([$id_servicio_nuevo, $direccion_origen, $direccion_destino]);

    // 6. Confirmar transacción
    $pdo->commit();

    // 7. Redirigir a la página de seguimiento
    header("Location: seguimiento.php?service_id=" . $id_servicio_nuevo);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensaje_error'] = "Error al crear la solicitud: " . $e->getMessage();
    header("Location: home.php");
    exit();
}
?>
