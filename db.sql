-- db.sql
CREATE DATABASE juntavecinos CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE juntavecinos;

-- Tabla: socios
DROP TABLE IF EXISTS `socios`;
CREATE TABLE `socios` (
  `RUT` varchar(12) NOT NULL,
  `Nombre` varchar(45) NOT NULL,
  `Apellido1` varchar(50) NOT NULL,
  `Apellido2` varchar(50) DEFAULT NULL,
  `Fecha_nacimiento` date DEFAULT NULL,
  `Ocupación` varchar(65) DEFAULT NULL,
  `Domicilio` varchar(125) DEFAULT NULL,
  `Fecha_ingreso` varchar(45) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'activo',
  PRIMARY KEY (`RUT`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Tabla: usuario (admin)
DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `rut_usuario` varchar(12) PRIMARY KEY,
  `nombre` varchar(45),
  `contraseña` varchar(15),
  `rol` varchar(25)
);

-- Tabla: cuotas
DROP TABLE IF EXISTS `pagos`;
CREATE TABLE `pagos` (
  `idPagos` INT AUTO_INCREMENT PRIMARY KEY,
  `RUT` varchar(12),
  `Monto` INT,
  `Fecha_pago` DATE,
  `Estado_pago` varchar(20) DEFAULT 'pendiente',
  FOREIGN KEY (`RUT`) REFERENCES `socios`(`RUT`)
);

-- Tabla: reservas
DROP TABLE IF EXISTS `reserva`;
CREATE TABLE `reserva` (
  `IDReserva` INT AUTO_INCREMENT PRIMARY KEY,
  `fecha_reserva` DATE,
  `hora_inicio` TIME,
  `hora_fin` TIME,
  `tipo_reserva` varchar(50),
  `estado_reserva` varchar(20) DEFAULT 'pendiente',
  `RUT` varchar(12),
  FOREIGN KEY (`RUT`) REFERENCES `socios`(`RUT`)
);

-- Tabla: eventos
DROP TABLE IF EXISTS `eventos`;
CREATE TABLE `eventos` (
  `ID_evento` INT AUTO_INCREMENT PRIMARY KEY,
  `Nombre_evento` varchar(100),
  `Descripción` varchar(200),
  `Fecha_evento` DATE,
  `Estado` varchar(15) DEFAULT 'realizado'
);

-- Tabla: imágenes
DROP TABLE IF EXISTS `imagenes`;
CREATE TABLE `imagenes` (
  `ID_imagen` INT AUTO_INCREMENT PRIMARY KEY,
  `ID_evento` INT,
  `nombre_imagen` varchar(200),
  `ruta` varchar(250),
  FOREIGN KEY (`ID_evento`) REFERENCES `eventos`(`ID_evento`)
);

-- Tabla: directiva
DROP TABLE IF EXISTS `directiva`;
CREATE TABLE `directiva` (
  `rut_admin` varchar(12) PRIMARY KEY,
  `nombre` varchar(45),
  `cargo` varchar(25)
);

-- Datos de prueba
INSERT INTO `usuario` (`rut_usuario`, `nombre`, `contraseña`, `rol`) 
VALUES ('11.111.111-1', 'Admin', '12345', 'admin');

INSERT INTO `socios` (`RUT`, `Nombre`, `Apellido1`, `estado`) 
VALUES ('12.345.678-9', 'Juan', 'Pérez', 'activo');

INSERT INTO `directiva` (`rut_admin`, `nombre`, `cargo`) 
VALUES ('11.111.111-1', 'María López', 'Presidenta');