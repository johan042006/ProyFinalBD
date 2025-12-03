-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 02-12-2025 a las 04:11:25
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS=0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `moviapp`
--
CREATE DATABASE IF NOT EXISTS `moviapp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `moviapp`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `CAT_CATEGORIA`
--

CREATE TABLE `CAT_CATEGORIA` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(50) NOT NULL,
  `porcentaje_recargo` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `CAT_GENERO`
--

CREATE TABLE `CAT_GENERO` (
  `id_genero` int(11) NOT NULL,
  `nombre_genero` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `CAT_NACIONALIDAD`
--

CREATE TABLE `CAT_NACIONALIDAD` (
  `id_nacionalidad` int(11) NOT NULL,
  `nombre_nacionalidad` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `CAT_PAGO`
--

CREATE TABLE `CAT_PAGO` (
  `id_pago` int(11) NOT NULL,
  `forma_pago` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `CLIENTE`
--

CREATE TABLE `CLIENTE` (
  `id_cliente` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `id_genero` int(11) NOT NULL,
  `id_nacionalidad` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `CONDUCTOR`
--

CREATE TABLE `CONDUCTOR` (
  `id_conductor` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(255) NOT NULL,
  `fotografia` varchar(255) DEFAULT NULL,
  `id_genero` int(11) NOT NULL,
  `id_nacionalidad` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `FACTURA`
--

CREATE TABLE `FACTURA` (
  `id_factura` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_emision` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `FORMAPAGO_FACTURA`
--

CREATE TABLE `FORMAPAGO_FACTURA` (
  `id_formapago_factura` int(11) NOT NULL,
  `id_factura` int(11) NOT NULL,
  `id_pago` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `RUTA_SERVICIO`
--

CREATE TABLE `RUTA_SERVICIO` (
  `id_ruta` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `direccion_origen` varchar(255) NOT NULL,
  `direccion_destino` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `SERVICIO`
--

CREATE TABLE `SERVICIO` (
  `id_servicio` int(11) NOT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_inicio` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `fecha_fin` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `estado` varchar(50) NOT NULL,
  `valor_total` decimal(10,2) NOT NULL CHECK (`valor_total` >= 0),
  `id_cliente` varchar(20) NOT NULL,
  `id_conductor` varchar(20) DEFAULT NULL,
  `placa_vehiculo` varchar(10) DEFAULT NULL,
  `id_tipo` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_tarifa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `TARIFA_BASE`
--

CREATE TABLE `TARIFA_BASE` (
  `id_tarifa` int(11) NOT NULL,
  `tarifa_base_valor` decimal(10,2) NOT NULL CHECK (`tarifa_base_valor` > 0),
  `fecha_vigencia` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `TELEFONO`
--

CREATE TABLE `TELEFONO` (
  `id_telefono` int(11) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `id_cliente` varchar(20) DEFAULT NULL,
  `id_conductor` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `TIPO_SERVICIO`
--

CREATE TABLE `TIPO_SERVICIO` (
  `id_tipo` int(11) NOT NULL,
  `tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `USUARIO`
--

CREATE TABLE `USUARIO` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(100) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `rol` varchar(50) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `VEHICULO`
--

CREATE TABLE `VEHICULO` (
  `placa` varchar(10) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `modelo` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `id_conductor_titular` varchar(20) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `capacidad_acompaniantes` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para las tablas
--

INSERT INTO `USUARIO` (`id_usuario`, `nombre_usuario`, `contraseña`, `rol`, `estado`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 'activo');

INSERT INTO `CAT_GENERO` (`id_genero`, `nombre_genero`) VALUES
(1, 'Masculino'),
(2, 'Femenino'),
(3, 'Otro');

INSERT INTO `CAT_NACIONALIDAD` (`id_nacionalidad`, `nombre_nacionalidad`) VALUES
(1, 'Colombiano'),
(2, 'Mexicano'),
(3, 'Argentino');

INSERT INTO `CAT_PAGO` (`id_pago`, `forma_pago`) VALUES
(1, 'Efectivo'),
(2, 'Tarjeta de Crédito');

INSERT INTO `TIPO_SERVICIO` (`id_tipo`, `tipo`) VALUES
(1, 'Pasajeros'),
(2, 'Alimentos'),
(3, 'Pasajeros y Alimentos');

INSERT INTO `CAT_CATEGORIA` (`id_categoria`, `nombre_categoria`, `porcentaje_recargo`) VALUES
(1, 'Normal', '0.00'),
(2, 'Especial', '15.00'),
(3, 'Urgente', '30.00');

INSERT INTO `TARIFA_BASE` (`id_tarifa`, `tarifa_base_valor`, `fecha_vigencia`) VALUES
(1, '5000.00', '2024-01-01');

INSERT INTO `CLIENTE` (`id_cliente`, `nombre`, `direccion`, `id_genero`, `id_nacionalidad`, `id_usuario`) VALUES
('11223344', 'Cliente de Prueba', 'Calle Falsa 123', 1, 1, 1);

--
-- Volcado de datos de prueba adicionales
--

-- Usuarios de prueba
INSERT INTO `USUARIO` (`id_usuario`, `nombre_usuario`, `contraseña`, `rol`, `estado`) VALUES
(2, 'conductor1', 'password', 'conductor', 'activo'),
(3, 'cliente1', 'password', 'cliente', 'activo');

-- Conductores de prueba
INSERT INTO `CONDUCTOR` (`id_conductor`, `nombre`, `direccion`, `fotografia`, `id_genero`, `id_nacionalidad`, `id_usuario`) VALUES
('C001', 'Carlos Driver', 'Av. Siempre Viva 742', 'uploads/fotos_conductores/default_driver.png', 1, 1, 2);

-- Clientes de prueba
INSERT INTO `CLIENTE` (`id_cliente`, `nombre`, `direccion`, `id_genero`, `id_nacionalidad`, `id_usuario`) VALUES
('CL001', 'Ana Clienta', 'Calle Luna, Calle Sol 1', 2, 1, 3);

-- Teléfonos de prueba
INSERT INTO `TELEFONO` (`id_telefono`, `numero`, `id_cliente`, `id_conductor`) VALUES
(1, '3001112233', NULL, 'C001'),
(2, '3104445566', 'CL001', NULL);

-- Vehículos de prueba
INSERT INTO `VEHICULO` (`placa`, `marca`, `modelo`, `id_tipo`, `id_conductor_titular`, `estado`, `capacidad_acompaniantes`) VALUES
('ABC123', 'Chevrolet', 2020, 1, 'C001', 'activo', 4);

-- Servicios de prueba
INSERT INTO `SERVICIO` (`id_servicio`, `fecha_solicitud`, `estado`, `valor_total`, `id_cliente`, `id_conductor`, `placa_vehiculo`, `id_tipo`, `id_categoria`, `id_tarifa`) VALUES
(1, '2025-12-01 10:00:00', 'solicitado', 5000.00, 'CL001', NULL, NULL, 1, 1, 1),
(2, '2025-12-01 11:00:00', 'asignado', 5750.00, 'CL001', 'C001', 'ABC123', 1, 2, 1),
(3, '2025-12-01 12:00:00', 'en_ruta', 6500.00, 'CL001', 'C001', 'ABC123', 2, 3, 1),
(4, '2025-12-01 13:00:00', 'completado', 5000.00, 'CL001', 'C001', 'ABC123', 1, 1, 1);

-- Rutas de servicio de prueba
INSERT INTO `RUTA_SERVICIO` (`id_ruta`, `id_servicio`, `direccion_origen`, `direccion_destino`) VALUES
(1, 1, 'Origen Cliente 1', 'Destino Cliente 1'),
(2, 2, 'Origen Cliente 2', 'Destino Cliente 2'),
(3, 3, 'Origen Cliente 3', 'Destino Cliente 3'),
(4, 4, 'Origen Cliente 4', 'Destino Cliente 4');

-- Facturas de prueba
INSERT INTO `FACTURA` (`id_factura`, `id_servicio`, `total`, `fecha_emision`) VALUES
(1, 4, 5000.00, '2025-12-01');

-- Formas de pago de factura de prueba
INSERT INTO `FORMAPAGO_FACTURA` (`id_formapago_factura`, `id_factura`, `id_pago`) VALUES
(1, 1, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `CAT_CATEGORIA`
--
ALTER TABLE `CAT_CATEGORIA`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre_categoria` (`nombre_categoria`);

--
-- Indices de la tabla `CAT_GENERO`
--
ALTER TABLE `CAT_GENERO`
  ADD PRIMARY KEY (`id_genero`),
  ADD UNIQUE KEY `nombre_genero` (`nombre_genero`);

--
-- Indices de la tabla `CAT_NACIONALIDAD`
--
ALTER TABLE `CAT_NACIONALIDAD`
  ADD PRIMARY KEY (`id_nacionalidad`),
  ADD UNIQUE KEY `nombre_nacionalidad` (`nombre_nacionalidad`);

--
-- Indices de la tabla `CAT_PAGO`
--
ALTER TABLE `CAT_PAGO`
  ADD PRIMARY KEY (`id_pago`),
  ADD UNIQUE KEY `forma_pago` (`forma_pago`);

--
-- Indices de la tabla `CLIENTE`
--
ALTER TABLE `CLIENTE`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_genero` (`id_genero`),
  ADD KEY `id_nacionalidad` (`id_nacionalidad`);

--
-- Indices de la tabla `CONDUCTOR`
--
ALTER TABLE `CONDUCTOR`
  ADD PRIMARY KEY (`id_conductor`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_genero` (`id_genero`),
  ADD KEY `id_nacionalidad` (`id_nacionalidad`);

--
-- Indices de la tabla `FACTURA`
--
ALTER TABLE `FACTURA`
  ADD PRIMARY KEY (`id_factura`),
  ADD UNIQUE KEY `id_servicio` (`id_servicio`),
  ADD KEY `idx_factura_servicio` (`id_servicio`);

--
-- Indices de la tabla `FORMAPAGO_FACTURA`
--
ALTER TABLE `FORMAPAGO_FACTURA`
  ADD PRIMARY KEY (`id_formapago_factura`),
  ADD KEY `id_factura` (`id_factura`),
  ADD KEY `id_pago` (`id_pago`);

--
-- Indices de la tabla `RUTA_SERVICIO`
--
ALTER TABLE `RUTA_SERVICIO`
  ADD PRIMARY KEY (`id_ruta`),
  ADD UNIQUE KEY `id_servicio` (`id_servicio`);

--
-- Indices de la tabla `SERVICIO`
--
ALTER TABLE `SERVICIO`
  ADD PRIMARY KEY (`id_servicio`),
  ADD KEY `placa_vehiculo` (`placa_vehiculo`),
  ADD KEY `id_tipo` (`id_tipo`),
  ADD KEY `id_categoria` (`id_categoria`),
  ADD KEY `id_tarifa` (`id_tarifa`),
  ADD KEY `idx_servicio_cliente` (`id_cliente`),
  ADD KEY `idx_servicio_conductor` (`id_conductor`);

--
-- Indices de la tabla `TARIFA_BASE`
--
ALTER TABLE `TARIFA_BASE`
  ADD PRIMARY KEY (`id_tarifa`),
  ADD UNIQUE KEY `fecha_vigencia` (`fecha_vigencia`);

--
-- Indices de la tabla `TELEFONO`
--
ALTER TABLE `TELEFONO`
  ADD PRIMARY KEY (`id_telefono`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_conductor` (`id_conductor`);

--
-- Indices de la tabla `TIPO_SERVICIO`
--
ALTER TABLE `TIPO_SERVICIO`
  ADD PRIMARY KEY (`id_tipo`),
  ADD UNIQUE KEY `tipo` (`tipo`);

--
-- Indices de la tabla `USUARIO`
--
ALTER TABLE `USUARIO`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`);

--
-- Indices de la tabla `VEHICULO`
--
ALTER TABLE `VEHICULO`
  ADD PRIMARY KEY (`placa`),
  ADD UNIQUE KEY `id_conductor_titular` (`id_conductor_titular`),
  ADD KEY `id_tipo` (`id_tipo`),
  ADD KEY `idx_vehiculo_titular` (`id_conductor_titular`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `CAT_CATEGORIA`
--
ALTER TABLE `CAT_CATEGORIA`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `CAT_GENERO`
--
ALTER TABLE `CAT_GENERO`
  MODIFY `id_genero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `CAT_NACIONALIDAD`
--
ALTER TABLE `CAT_NACIONALIDAD`
  MODIFY `id_nacionalidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `CAT_PAGO`
--
ALTER TABLE `CAT_PAGO`
  MODIFY `id_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `FACTURA`
--
ALTER TABLE `FACTURA`
  MODIFY `id_factura` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `FORMAPAGO_FACTURA`
--
ALTER TABLE `FORMAPAGO_FACTURA`
  MODIFY `id_formapago_factura` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `RUTA_SERVICIO`
--
ALTER TABLE `RUTA_SERVICIO`
  MODIFY `id_ruta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `SERVICIO`
--
ALTER TABLE `SERVICIO`
  MODIFY `id_servicio` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `TARIFA_BASE`
--
ALTER TABLE `TARIFA_BASE`
  MODIFY `id_tarifa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `TELEFONO`
--
ALTER TABLE `TELEFONO`
  MODIFY `id_telefono` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `TIPO_SERVICIO`
--
ALTER TABLE `TIPO_SERVICIO`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `USUARIO`
--
ALTER TABLE `USUARIO`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `CLIENTE`
--
ALTER TABLE `CLIENTE`
  ADD CONSTRAINT `CLIENTE_ibfk_1` FOREIGN KEY (`id_genero`) REFERENCES `CAT_GENERO` (`id_genero`),
  ADD CONSTRAINT `CLIENTE_ibfk_2` FOREIGN KEY (`id_nacionalidad`) REFERENCES `CAT_NACIONALIDAD` (`id_nacionalidad`),
  ADD CONSTRAINT `CLIENTE_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `USUARIO` (`id_usuario`);

--
-- Filtros para la tabla `CONDUCTOR`
--
ALTER TABLE `CONDUCTOR`
  ADD CONSTRAINT `CONDUCTOR_ibfk_1` FOREIGN KEY (`id_genero`) REFERENCES `CAT_GENERO` (`id_genero`),
  ADD CONSTRAINT `CONDUCTOR_ibfk_2` FOREIGN KEY (`id_nacionalidad`) REFERENCES `CAT_NACIONALIDAD` (`id_nacionalidad`),
  ADD CONSTRAINT `CONDUCTOR_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `USUARIO` (`id_usuario`);

--
-- Filtros para la tabla `FACTURA`
--
ALTER TABLE `FACTURA`
  ADD CONSTRAINT `FACTURA_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `SERVICIO` (`id_servicio`);

--
-- Filtros para la tabla `FORMAPAGO_FACTURA`
--
ALTER TABLE `FORMAPAGO_FACTURA`
  ADD CONSTRAINT `FORMAPAGO_FACTURA_ibfk_1` FOREIGN KEY (`id_factura`) REFERENCES `FACTURA` (`id_factura`),
  ADD CONSTRAINT `FORMAPAGO_FACTURA_ibfk_2` FOREIGN KEY (`id_pago`) REFERENCES `CAT_PAGO` (`id_pago`);

--
-- Filtros para la tabla `RUTA_SERVICIO`
--
ALTER TABLE `RUTA_SERVICIO`
  ADD CONSTRAINT `RUTA_SERVICIO_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `SERVICIO` (`id_servicio`);

--
-- Filtros para la tabla `SERVICIO`
--
ALTER TABLE `SERVICIO`
  ADD CONSTRAINT `SERVICIO_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `CLIENTE` (`id_cliente`),
  ADD CONSTRAINT `SERVICIO_ibfk_2` FOREIGN KEY (`id_conductor`) REFERENCES `CONDUCTOR` (`id_conductor`),
  ADD CONSTRAINT `SERVICIO_ibfk_3` FOREIGN KEY (`placa_vehiculo`) REFERENCES `VEHICULO` (`placa`),
  ADD CONSTRAINT `SERVICIO_ibfk_4` FOREIGN KEY (`id_tipo`) REFERENCES `TIPO_SERVICIO` (`id_tipo`),
  ADD CONSTRAINT `SERVICIO_ibfk_5` FOREIGN KEY (`id_categoria`) REFERENCES `CAT_CATEGORIA` (`id_categoria`),
  ADD CONSTRAINT `SERVICIO_ibfk_6` FOREIGN KEY (`id_tarifa`) REFERENCES `TARIFA_BASE` (`id_tarifa`);

--
-- Filtros para la tabla `TELEFONO`
--
ALTER TABLE `TELEFONO`
  ADD CONSTRAINT `TELEFONO_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `CLIENTE` (`id_cliente`),
  ADD CONSTRAINT `TELEFONO_ibfk_2` FOREIGN KEY (`id_conductor`) REFERENCES `CONDUCTOR` (`id_conductor`);

--
-- Filtros para la tabla `VEHICULO`
--
ALTER TABLE `VEHICULO`
  ADD CONSTRAINT `VEHICULO_ibfk_1` FOREIGN KEY (`id_tipo`) REFERENCES `TIPO_SERVICIO` (`id_tipo`),
  ADD CONSTRAINT `VEHICULO_ibfk_2` FOREIGN KEY (`id_conductor_titular`) REFERENCES `CONDUCTOR` (`id_conductor`);

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
DELIMITER //

CREATE TRIGGER trg_prevent_vehicle_assignment_to_inactive_driver
BEFORE INSERT ON VEHICULO
FOR EACH ROW
BEGIN
    DECLARE conductor_status VARCHAR(50);

    SELECT u.estado INTO conductor_status
    FROM CONDUCTOR c
    JOIN USUARIO u ON c.id_usuario = u.id_usuario
    WHERE c.id_conductor = NEW.id_conductor_titular;

    IF conductor_status = 'inactivo' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede asignar un vehículo a un conductor inactivo.';
    END IF;
END //

CREATE TRIGGER trg_prevent_vehicle_update_assignment_to_inactive_driver
BEFORE UPDATE ON VEHICULO
FOR EACH ROW
BEGIN
    DECLARE conductor_status VARCHAR(50);

    SELECT u.estado INTO conductor_status
    FROM CONDUCTOR c
    JOIN USUARIO u ON c.id_usuario = u.id_usuario
    WHERE c.id_conductor = NEW.id_conductor_titular;

    IF conductor_status = 'inactivo' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se puede reasignar un vehículo a un conductor inactivo.';
    END IF;
END //

DELIMITER ;

CREATE VIEW vista_resumen_servicios AS
SELECT
    s.id_servicio,
    s.fecha_solicitud,
    s.estado,
    s.valor_total,
    r.direccion_origen,
    r.direccion_destino,
    cl.nombre AS nombre_cliente,
    cl.id_cliente AS id_cliente_resumen,
    co.nombre AS nombre_conductor,
    co.id_conductor AS id_conductor_resumen,
    v.placa AS placa_vehiculo,
    v.marca AS marca_vehiculo,
    v.modelo AS modelo_vehiculo,
    ts.tipo AS tipo_servicio_nombre,
    cc.nombre_categoria AS categoria_nombre
FROM SERVICIO s
JOIN RUTA_SERVICIO r ON s.id_servicio = r.id_servicio
JOIN CLIENTE cl ON s.id_cliente = cl.id_cliente
LEFT JOIN CONDUCTOR co ON s.id_conductor = co.id_conductor
LEFT JOIN VEHICULO v ON s.placa_vehiculo = v.placa
LEFT JOIN TIPO_SERVICIO ts ON s.id_tipo = ts.id_tipo
LEFT JOIN CAT_CATEGORIA cc ON s.id_categoria = cc.id_categoria;