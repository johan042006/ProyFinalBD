-- Inserción de datos de prueba para MoviApp

-- Asegurarse de que se está usando la base de datos correcta
USE `moviapp`;

-- Desactivar la verificación de claves foráneas para evitar errores de orden
SET FOREIGN_KEY_CHECKS=0;

-- Limpiar tablas existentes para evitar duplicados (opcional, pero recomendado para pruebas)
TRUNCATE TABLE `FORMAPAGO_FACTURA`;
TRUNCATE TABLE `FACTURA`;
TRUNCATE TABLE `RUTA_SERVICIO`;
TRUNCATE TABLE `SERVICIO`;
TRUNCATE TABLE `TELEFONO`;
TRUNCATE TABLE `VEHICULO`;
TRUNCATE TABLE `CONDUCTOR`;
TRUNCATE TABLE `CLIENTE`;
TRUNCATE TABLE `USUARIO`;

-- Reactivar la verificación de claves foráneas
SET FOREIGN_KEY_CHECKS=1;

-- Insertar usuarios (contraseña para todos es 'password123' hasheada)
INSERT INTO `USUARIO` (`id_usuario`, `nombre_usuario`, `contraseña`, `rol`, `estado`) VALUES
(1, 'admin', 'prueba123', 'administrador', 'activo'),
(101, 'cliente_ana', 'prueba1234', 'cliente', 'activo'),
(102, 'cliente_juan', 'prueba12345', 'cliente', 'activo'),
(103, 'cliente_sofia', 'prueba123456', 'cliente', 'activo'),
(201, 'conductor_carlos', 'prueba1234567', 'conductor', 'activo'),
(202, 'conductor_lucia', 'prueba12345678', 'conductor', 'activo');

-- Insertar clientes
INSERT INTO `CLIENTE` (`id_cliente`, `nombre`, `direccion`, `id_genero`, `id_nacionalidad`, `id_usuario`) VALUES
('1001', 'Ana Pérez', 'Calle Falsa 123', 2, 1, 101),
('1002', 'Juan Rodríguez', 'Avenida Siempreviva 742', 1, 2, 102),
('1003', 'Sofía Gómez', 'Boulevard de los Sueños Rotos 5', 2, 1, 103);

-- Insertar conductores
INSERT INTO `CONDUCTOR` (`id_conductor`, `nombre`, `direccion`, `fotografia`, `id_genero`, `id_nacionalidad`, `id_usuario`) VALUES
('C001', 'Carlos Ramírez', 'Calle 100 #20-30', NULL, 1, 1, 201),
('C002', 'Lucía Fernández', 'Carrera 15 #85-50', NULL, 2, 3, 202);

-- Insertar vehículos
INSERT INTO `VEHICULO` (`placa`, `marca`, `modelo`, `id_tipo`, `id_conductor_titular`, `estado`, `capacidad_acompaniantes`) VALUES
('XYZ789', 'Mazda', 2022, 1, 'C001', 'activo', 4),
('QWE456', 'Renault', 2021, 3, 'C002', 'activo', 3);

-- Insertar servicios (distribuidos en varios meses y con diferentes características)
-- Octubre 2025
INSERT INTO `SERVICIO` (`id_servicio`, `fecha_solicitud`, `estado`, `valor_total`, `id_cliente`, `id_conductor`, `placa_vehiculo`, `id_tipo`, `id_categoria`, `id_tarifa`) VALUES
(1, '2025-10-05 10:00:00', 'completado', 8000.00, '1001', 'C001', 'XYZ789', 1, 1, 1),
(2, '2025-10-15 14:30:00', 'completado', 12000.00, '1002', 'C002', 'QWE456', 2, 2, 1);

-- Noviembre 2025
INSERT INTO `SERVICIO` (`id_servicio`, `fecha_solicitud`, `estado`, `valor_total`, `id_cliente`, `id_conductor`, `placa_vehiculo`, `id_tipo`, `id_categoria`, `id_tarifa`) VALUES
(3, '2025-11-02 08:00:00', 'completado', 9500.00, '1001', 'C002', 'QWE456', 1, 2, 1),
(4, '2025-11-10 18:00:00', 'completado', 15000.00, '1003', 'C001', 'XYZ789', 1, 3, 1),
(5, '2025-11-20 12:00:00', 'completado', 25000.00, '1002', 'C002', 'QWE456', 2, 3, 1);

-- Diciembre 2025
INSERT INTO `SERVICIO` (`id_servicio`, `fecha_solicitud`, `estado`, `valor_total`, `id_cliente`, `id_conductor`, `placa_vehiculo`, `id_tipo`, `id_categoria`, `id_tarifa`) VALUES
(6, '2025-12-01 09:00:00', 'completado', 7500.00, '1003', 'C001', 'XYZ789', 1, 1, 1),
(7, '2025-12-03 11:00:00', 'en_ruta', 11000.00, '1001', 'C001', 'XYZ789', 2, 1, 1),
(8, '2025-12-04 16:00:00', 'solicitado', 18000.00, '1002', NULL, NULL, 1, 3, 1);

-- Insertar rutas para cada servicio
INSERT INTO `RUTA_SERVICIO` (`id_servicio`, `direccion_origen`, `direccion_destino`) VALUES
(1, 'Centro Comercial Andino', 'Parque de la 93'),
(2, 'Zona Industrial Montevideo', 'Restaurante El Cielo'),
(3, 'Universidad de los Andes', 'Aeropuerto El Dorado'),
(4, 'Teatro Mayor Julio Mario Santo Domingo', 'Casa de Ana Pérez'),
(5, 'Corabastos', 'Supermercado Éxito Salitre'),
(6, 'Museo del Oro', 'Planetario de Bogotá'),
(7, 'Oficina Cliente', 'Bodega Central'),
(8, 'Casa Juan Rodríguez', 'Clínica del Country');

-- Insertar facturas y formas de pago (los triggers ya deberían haber creado las facturas para servicios completados)
-- Estas inserciones son para asignar la forma de pago
-- Asumimos que los IDs de factura son secuenciales desde 1
INSERT INTO `FORMAPAGO_FACTURA` (`id_factura`, `id_pago`) VALUES
(1, 1), -- Servicio 1, Efectivo
(2, 2), -- Servicio 2, Tarjeta de Crédito
(3, 1), -- Servicio 3, Efectivo
(4, 2), -- Servicio 4, Tarjeta de Crédito
(5, 1), -- Servicio 5, Efectivo
(6, 2); -- Servicio 6, Tarjeta de Crédito

-- Nota: Los triggers se encargarán de crear las facturas para los servicios marcados como 'completado'
-- y de actualizar la 'fecha_fin' en la tabla SERVICIO.
-- Este script de inserción se puede ejecutar varias veces, pero se recomienda truncar las tablas
-- para mantener la consistencia de los datos de prueba.

-- Reactivar la verificación de claves foráneas (si se desactivó al principio)
SET FOREIGN_KEY_CHECKS=1;
