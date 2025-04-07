-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-03-2025 a las 22:53:52
-- Versión del servidor: 10.4.27-MariaDB
-- Versión de PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `alen_almacen`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salida_refactor`
--

CREATE TABLE `salida_refactor` (
  `Id` int(11) NOT NULL,
  `Id_Cliente` int(11) NOT NULL,
  `Nombre_Cliente` text NOT NULL,
  `Id_Status` int(11) NOT NULL,
  `Estado` text NOT NULL,
  `Id_Sucursal` int(11) NOT NULL,
  `Sucursal` text NOT NULL,
  `Urgencia` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `salida_refactor`
--

INSERT INTO `salida_refactor` (`Id`, `Id_Cliente`, `Nombre_Cliente`, `Id_Status`, `Estado`, `Id_Sucursal`, `Sucursal`, `Urgencia`) VALUES
(23, 2438, 'ARCELORMITTAL PORTUARIOS S.A. DE C.V.', 26, 'Envios', 1, 'Guadalajara', 'No'),
(24, 2717, 'A & S PROYECTOS METALMECANICOS S.R.L. DE C.V.', 27, 'Completado', 1, 'Guadalajara', 'No'),
(25, 2717, 'A & S PROYECTOS METALMECANICOS S.R.L. DE C.V.', 27, 'Completado', 1, 'Guadalajara', 'No'),
(26, 3084, 'DUAL TALLERES METAL MECANICA', 25, 'A Ruta', 1, 'Guadalajara', 'No'),
(27, 583, 'INSTRUMENTACION Y REFACCIONES INDUSTRIALESSA DE CV', 25, 'A Ruta', 1, 'Guadalajara', 'No'),
(28, 1229, 'BANCO INTERACCIONES S.A. FID. 10075', 27, 'Completado', 1, 'Guadalajara', 'No'),
(29, 2438, 'ARCELORMITTAL PORTUARIOS S.A. DE C.V.', 25, 'A Ruta', 1, 'Guadalajara', 'No'),
(30, 1565, 'INGENIO AZUCARERO MODELO SA DE CV', 22, 'Facturación', 1, 'Guadalajara', 'No'),
(31, 3144, 'RESIDENCIAL PERLA', 26, 'Logistica', 1, 'Guadalajara', 'Si'),
(32, 2717, 'A & S PROYECTOS METALMECANICOS S.R.L. DE C.V.', 23, 'Facturacion', 1, 'Guadalajara', 'No'),
(33, 2972, 'ALGORITMO AUTOMATIZACION', 21, 'Entrega', 1, 'Guadalajara', 'No');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `salida_refactor`
--
ALTER TABLE `salida_refactor`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `salida_refactor`
--
ALTER TABLE `salida_refactor`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
