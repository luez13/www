-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versión del servidor:         8.0.30 - MySQL Community Server - GPL
-- SO del servidor:              Win64
-- HeidiSQL Versión:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Volcando estructura de base de datos para pruebas
CREATE DATABASE IF NOT EXISTS `pruebas` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `pruebas`;

-- Volcando estructura para tabla pruebas.documentos
CREATE TABLE IF NOT EXISTS `documentos` (
  `id_doc` int NOT NULL AUTO_INCREMENT,
  `nombre_doc` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id_doc`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='lista de los documentos';

-- Volcando datos para la tabla pruebas.documentos: ~6 rows (aproximadamente)
INSERT INTO `documentos` (`id_doc`, `nombre_doc`) VALUES
	(1, 'titulo ingenieria informatica'),
	(2, 'titulo ingenieria en electronica'),
	(3, 'tecnico superior universitario informatico'),
	(4, 'tecnico superior universitario electronica'),
	(5, 'curso en mecanica'),
	(6, 'curso en medicina');

-- Volcando estructura para tabla pruebas.doc_ref
CREATE TABLE IF NOT EXISTS `doc_ref` (
  `user_id` int DEFAULT NULL,
  `document_id` int DEFAULT NULL,
  `token` varchar(10000) DEFAULT NULL,
  KEY `FK_doc_ref_usuario` (`user_id`),
  KEY `FK_doc_ref_documentos` (`document_id`),
  CONSTRAINT `FK_doc_ref_documentos` FOREIGN KEY (`document_id`) REFERENCES `documentos` (`id_doc`),
  CONSTRAINT `FK_doc_ref_usuario` FOREIGN KEY (`user_id`) REFERENCES `usuario` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='referencia de documentos';

-- Volcando datos para la tabla pruebas.doc_ref: ~7 rows (aproximadamente)
INSERT INTO `doc_ref` (`user_id`, `document_id`, `token`) VALUES
	(1, 1, '3954282be5a7353f3e3408e34b1f39aa0acd9e4086e93fbf93495995e1fe871a'),
	(1, 3, '7e2096fa47ec99fe8aa120f2dbb97158fe3e4dc22482eaddd76f5b5c8a26e1a4'),
	(2, 4, 'cf76d9f204e654a57bbfc712d3b661852c27cf9ee8010d93d5965b0edfe011ec'),
	(3, 5, '0b7e8b11b96975c8f15ea40ac7608713aed3dd11044f1ce05344342e3ba4df97'),
	(3, 2, '897e9580b874fc6a0bb46a47ebc4622a18916e81e47d100d817b5c75c8707546'),
	(3, 6, 'e2c27a470985dd9b51ac239edebdbb4c3b088f1ea7f7e41a98b8b8e5f38b19fb'),
	(3, 2, '897e9580b874fc6a0bb46a47ebc4622a18916e81e47d100d817b5c75c8707546'),
	(2, 5, '7f6833285e8e9c9ddeb67ad031e8101db5b127c0d54b707e8ef2d90fc6bedaa6');

-- Volcando estructura para tabla pruebas.usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cedula` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `apellido` varchar(50) DEFAULT NULL,
  `correo` varchar(50) DEFAULT NULL,
  `documento_ref` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='usuarios con sus datos';

-- Volcando datos para la tabla pruebas.usuario: ~2 rows (aproximadamente)
INSERT INTO `usuario` (`id`, `cedula`, `nombre`, `apellido`, `correo`, `documento_ref`) VALUES
	(1, '27087340', 'luis', 'gomez', 'luis@gmail.com', 1),
	(2, '12569865', 'jose', 'gomez', 'jose@gmail.com', 2),
	(3, '30252283', 'pedro', 'suarez', 'pedro@gmail.com', 3);

-- Volcando estructura para disparador pruebas.generar_hash
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `generar_hash` BEFORE INSERT ON `doc_ref` FOR EACH ROW BEGIN
  SET NEW.token = SHA2(CONCAT(NEW.user_id, NEW.document_id, NOW()), 256);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
