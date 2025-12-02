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


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `moviapp`
--

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
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Volcado de datos para la tabla `TIPO_SERVICIO`
--
INSERT INTO `TIPO_SERVICIO` (`id_tipo`, `tipo`) VALUES
(1, 'Pasajeros'),
(2, 'Alimentos'),
(3, 'Pasajeros y Alimentos');

--
-- Volcado de datos para tablas de catálogo y ejemplo
--
INSERT INTO `CLIENTE` (`id_cliente`, `nombre`, `direccion`, `id_genero`, `id_nacionalidad`, `id_usuario`) VALUES
('11223344', 'Cliente de Prueba', 'Calle Falsa 123', 1, 1, 1);

INSERT INTO `CAT_CATEGORIA` (`id_categoria`, `nombre_categoria`, `porcentaje_recargo`) VALUES
(1, 'Normal', '0.00'),
(2, 'Especial', '15.00'),
(3, 'Urgente', '30.00');

INSERT INTO `TARIFA_BASE` (`id_tarifa`, `tarifa_base_valor`, `fecha_vigencia`) VALUES
(1, '5000.00', '2024-01-01');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
