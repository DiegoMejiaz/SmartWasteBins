-- phpMyAdmin SQL Dump
-- Versión completa y corregida para SmartBin

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Desactivar llaves foráneas para permitir la limpieza y recreación
--
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

CREATE DATABASE IF NOT EXISTS `bd_smartbin` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bd_smartbin`;

--
-- Limpieza preventiva de tablas
--
DROP TABLE IF EXISTS `canje`;
DROP TABLE IF EXISTS `sesion_token`;
DROP TABLE IF EXISTS `transaccion_puntos`;
DROP TABLE IF EXISTS `residuo`;
DROP TABLE IF EXISTS `cuenta_puntos`;
DROP TABLE IF EXISTS `smartbin`;
DROP TABLE IF EXISTS `usuario`;
DROP TABLE IF EXISTS `sensor`;
DROP TABLE IF EXISTS `ruta_recoleccion`;

START TRANSACTION;
--
-- Base de datos: `bd_smartbin`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(5) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `contrasena` varchar(255) DEFAULT NULL COMMENT 'Hash bcrypt (password_hash)',
  `rol` varchar(20) DEFAULT 'user',
  `qr_code` varchar(255) DEFAULT 'QR_GENERADO',
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuenta_puntos`
--

CREATE TABLE `cuenta_puntos` (
  `id_cuenta` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(5) DEFAULT NULL,
  `saldo_actual` int(11) DEFAULT 0,
  `fecha_actualizacion` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_cuenta`),
  UNIQUE KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `cuenta_puntos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `smartbin`
--

CREATE TABLE `smartbin` (
  `id_bin` int(11) NOT NULL AUTO_INCREMENT,
  `ubicacion` varchar(150) DEFAULT NULL,
  `capacidad_maxima` float DEFAULT 50.0,
  `nivel_actual` float DEFAULT 0.0,
  `estado` varchar(50) DEFAULT 'Activo',
  PRIMARY KEY (`id_bin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Volcado de datos iniciales para smartbin (Centro Histórico Actualizado)
INSERT INTO `smartbin` (`id_bin`, `ubicacion`, `capacidad_maxima`, `nivel_actual`, `estado`) VALUES
(1, 'Hotel Casa Don Luis', 50.0, 0.0, 'Activo'),
(2, 'Santo Domingo', 50.0, 0.0, 'Activo'),
(3, 'Sophia Hotel', 50.0, 0.0, 'Activo'),
(4, 'Casa Jaguar Hotel', 50.0, 0.0, 'Activo'),
(5, 'Hotel Casona del Porvenir', 50.0, 0.0, 'Activo'),
(6, 'Osh Hotel Cartagena', 50.0, 0.0, 'Activo'),
(7, 'Los Patios Hostel', 50.0, 0.0, 'Activo'),
(8, 'GHL Arsenal Hotel', 50.0, 0.0, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `residuo`
--

CREATE TABLE `residuo` (
  `id_residuo` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(5) DEFAULT NULL,
  `id_bin` int(11) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `peso` float DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_residuo`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_bin` (`id_bin`),
  CONSTRAINT `residuo_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  CONSTRAINT `residuo_ibfk_2` FOREIGN KEY (`id_bin`) REFERENCES `smartbin` (`id_bin`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transaccion_puntos`
--

CREATE TABLE `transaccion_puntos` (
  `id_transaccion` int(11) NOT NULL AUTO_INCREMENT,
  `id_cuenta` int(11) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_transaccion`),
  KEY `id_cuenta` (`id_cuenta`),
  CONSTRAINT `transaccion_puntos_ibfk_1` FOREIGN KEY (`id_cuenta`) REFERENCES `cuenta_puntos` (`id_cuenta`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `sesion_token` (sesiones de la app móvil)
-- Se guarda solo el hash SHA-256 del token, nunca el token en texto plano.
--

CREATE TABLE `sesion_token` (
  `token_hash` char(64) NOT NULL,
  `id_usuario` int(5) NOT NULL,
  `creado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expira` datetime NOT NULL,
  PRIMARY KEY (`token_hash`),
  KEY `id_usuario` (`id_usuario`),
  KEY `expira` (`expira`),
  CONSTRAINT `sesion_token_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Estructura de tabla para la tabla `canje` (pasarela de canje de puntos a Nequi / Daviplata)
--

CREATE TABLE `canje` (
  `id_canje` int(11) NOT NULL AUTO_INCREMENT,
  `referencia` varchar(24) NOT NULL,
  `id_usuario` int(5) NOT NULL,
  `id_transaccion` int(11) DEFAULT NULL,
  `metodo` varchar(20) NOT NULL,
  `telefono` varchar(10) NOT NULL,
  `puntos` int(11) NOT NULL,
  `valor_cop` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'APROBADO',
  `fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_canje`),
  UNIQUE KEY `referencia` (`referencia`),
  KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `canje_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

-- Restaurar la verificación de llaves foráneas
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
