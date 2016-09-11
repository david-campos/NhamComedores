-- David Campos Rodríguez
-- Base de Datos de ComedoresUSC en MySQL
-- Script de generación

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/********************************************************
    CUESTIONES DE ADMINISTRACION DE PLATOS Y COMEDORES
 ********************************************************/

/* Table Universidades */
CREATE TABLE `Universidades` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(40) UNIQUE NOT NULL,
  `ciudad` varchar(30) UNIQUE NOT NULL,
  `img` varchar(20) NOT NULL,
  
  PRIMARY KEY (`_id`)
)
COMMENT "Universidades participantes en la aplicación.";

/* Table Comedores */
CREATE TABLE `Comedores` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `universidad` int(10) unsigned NOT NULL,
  `nombre` varchar(50) UNIQUE NOT NULL,
  `horaInicio` time NOT NULL COMMENT "Hora de inicio del servicio de comedor",
  `horaFin` time NOT NULL COMMENT "Hora de fin del servicio de comedor",
  `coordLat` double NOT NULL,
  `coordLon` double NOT NULL,
  `telefono` char(9) UNIQUE NOT NULL,
  `nombreContacto` varchar(20) DEFAULT NULL,
  `direccion` varchar(120) NOT NULL,
  `hAperturaIni` time NOT NULL COMMENT "Hora de apertura del comedor",
  `hAperturaFin` time NOT NULL COMMENT "Hora de cierre del comedor",
  `diaInicioApertura`
    set('lunes','martes','miercoles','jueves','viernes','sabado','domingo')
    NOT NULL DEFAULT 'lunes',
  `diaFinApertura`
    set('lunes','martes','miercoles','jueves','viernes','sabado','domingo')
    NOT NULL DEFAULT 'sabado',
  `promocion` text NOT NULL,
  `codigo` char(128) NOT NULL,
  `salt` char(128) NOT NULL,
  `loginName` varchar(20) NOT NULL,
  
  PRIMARY KEY (`_id`),
  
  FOREIGN KEY (`universidad`) REFERENCES `Universidades`(`_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
)
COMMENT "Comedores disponibles, su información de login y de contacto";

/* Table TiposMenu */
CREATE TABLE `TiposMenu` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) NOT NULL,
  `precio` decimal(4,2) NOT NULL,
  `id_comedor` int(10) unsigned NOT NULL,
  
  PRIMARY KEY (`_id`),
  
  FOREIGN KEY (`id_comedor`) REFERENCES `Comedores`(`_id`)
    ON UPDATE CASCADE ON DELETE CASCADE
)
COMMENT "Tipos de menús que sirven los comedores, con su precio correspondiente";

/* Table ElementosMenu */
CREATE TABLE `ElementosMenu` (
  `_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(20) UNIQUE NOT NULL,
  `tipo` enum('plato','bebida','extra') NOT NULL,
  
  PRIMARY KEY (`_id`)
)
COMMENT "Elementos que conforman los menús ofrecidos en los comedores";

/* Rellenamos los ElementosMenu con elementos disponibles para los comedores*/
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Primer plato','plato');
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Segundo plato','plato');
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Segundo plato (2)','plato');
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Postre','plato');
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Agua','bebida');
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Refresco','bebida');
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Cerveza','bebida');
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Vino','bebida');
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Combinado','bebida');
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Pan','extra');
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Café','extra');
INSERT INTO `ElementosMenu`(`nombre`,`tipo`) VALUES('Chupito','extra');

/* Table Tienen*/
CREATE TABLE `Tienen` (
  `id_tipoMen` int(10) unsigned NOT NULL,
  `id_elemMen` int(10) unsigned NOT NULL,
  
  PRIMARY KEY (`id_tipoMen`,`id_elemMen`),
  
  FOREIGN KEY (`id_tipoMen`) REFERENCES `TiposMenu`(`_id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_elemMen`) REFERENCEs `ElementosMenu`(`_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
)
COMMENT "Relación entre tipos de menús y elementos que los mismos incluyen";

/* Table Platos */
CREATE TABLE `Platos` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text NOT NULL,
  `tipo` char(5) NOT NULL,
  
  PRIMARY KEY (`_id`)
)
COMMENT "Platos ofrecidos por los comedores";

/* Table MisPlatos */
CREATE TABLE `MisPlatos` (
  `comedor` int(10) unsigned NOT NULL,
  `prestado` tinyint(1) NOT NULL DEFAULT '0',
  `plato` int(10) unsigned NOT NULL,
  
  PRIMARY KEY (`comedor`,`plato`),
  
  FOREIGN KEY (`comedor`) REFERENCES `Comedores`(`_id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`plato`) REFERENCES `Platos`(`_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
)
COMMENT "Relación de los comedores con los platos que han servido o piensan servir";

/* Table Tener */
CREATE TABLE `Tener` (
  `id_comedor` int(10) unsigned NOT NULL,
  `fecha` date NOT NULL,
  `agotado` tinyint(1) NOT NULL DEFAULT '0',
  `id_plato` int(10) unsigned NOT NULL,
  
  PRIMARY KEY (`id_comedor`,`fecha`,`id_plato`),
  
  FOREIGN KEY (`id_comedor`) REFERENCES `Comedores`(`_id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`id_plato`) REFERENCES `Platos`(`_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
)
COMMENT "Relación de los comedores con los platos que sirven en una fecha concreta";

/*****************************************************
   CUESTIONES DE SEGURIDAD Y CONTROL DE LA PAGINA WEB
 *****************************************************/
/* Table UserAgents */
CREATE TABLE `UserAgents` (
  `_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `string` text NOT NULL,
  
  PRIMARY KEY (`_id`)
)
COMMENT "Agentes de usuario enviados por los navegadores";

/* Table Conexiones */
CREATE TABLE `Conexiones` (
  `comedor_id` int(10) unsigned NOT NULL,
  `userAgent_id` int(10) unsigned NOT NULL,
  `loginString` char(128) NOT NULL,
  `lastActivity` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `initialAddress` char(45) NOT NULL,
  `initialTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `terminated` tinyint(1) NOT NULL DEFAULT '0',
  `forced` tinyint(1) NOT NULL DEFAULT '0',
  
  PRIMARY KEY (`comedor_id`,`initialTimestamp`),
  
  FOREIGN KEY (`comedor_id`) REFERENCES `Comedores`(`_id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (`userAgent_id`) REFERENCES `UserAgents`(`_id`)
    ON UPDATE CASCADE ON DELETE RESTRICT
)
COMMENT "Conexiones activas y terminadas a la sección de administración";

/* Table IntentosLogin */
CREATE TABLE `IntentosLogin` (
  `comedor` int(10) unsigned NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`comedor`,`time`)
)
COMMENT "Intentos de login fallidos de un comedor, permite detectar ataques por fuerza bruta";