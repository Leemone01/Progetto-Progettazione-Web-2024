-- Progettazione Web 
DROP DATABASE if exists calo_615600; 
CREATE DATABASE calo_615600; 
USE calo_615600; 
-- MySQL dump 10.13  Distrib 5.7.28, for Win64 (x86_64)
--
-- Host: localhost    Database: calo_615600
-- ------------------------------------------------------
-- Server version	5.7.28

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `brano`
--

DROP TABLE IF EXISTS `brano`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `brano` (
  `sid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Song ID',
  `titolo` varchar(255) NOT NULL,
  `utente` int(11) NOT NULL COMMENT 'UID dell''utente che ha caricato il brano',
  `dataRilascio` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `immagineBrano` varchar(255) NOT NULL DEFAULT '../imgs/brano/default.png',
  `pathBrano` varchar(255) NOT NULL,
  `numeroMiPiace` int(11) NOT NULL DEFAULT '0' COMMENT 'Ridondanza per ottenere subito il numero di mi piace',
  `numeroAscolti` int(11) NOT NULL DEFAULT '0',
  `numeroCommenti` int(11) NOT NULL DEFAULT '0' COMMENT 'Ridondanza per ottenere subito il numero di commenti',
  `descrizioneBrano` varchar(140) NOT NULL DEFAULT '',
  PRIMARY KEY (`sid`),
  KEY `brano_ibfk_1` (`utente`),
  CONSTRAINT `brano_ibfk_1` FOREIGN KEY (`utente`) REFERENCES `utente` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brano`
--

LOCK TABLES `brano` WRITE;
/*!40000 ALTER TABLE `brano` DISABLE KEYS */;
INSERT INTO `brano` VALUES (1,'Serious Song.',2,'2024-04-08 09:31:54','../imgs/brano/default.png','../brani/1.mp3',1,21,1,'Una canzone seria.'),(2,'Allenamento con la chitarra',3,'2024-04-08 09:37:32','../imgs/brano/2.jpg','../brani/2.mp3',2,18,1,'Un mio allenamento con la chitarra :>'),(3,'Once in Paris',4,'2024-04-08 09:41:33','../imgs/brano/3.png','../brani/3.mp3',1,12,0,'Questo brano è ispirato al mio ultimo viaggio a Parigi, spero vi piaccia!'),(4,'Fire',5,'2024-04-08 13:10:39','../imgs/brano/4.jpg','../brani/4.mp3',1,12,0,'Questo è il miglior brano su questa piattaforma.'),(5,'aspe ma?',6,'2024-04-08 13:17:34','../imgs/brano/5.png','../brani/5.mp3',1,8,1,'Lasciate mi piace e commentate!'),(6,'The Beast in my Head',5,'2024-04-08 13:19:22','../imgs/brano/6.jpg','../brani/6.mp3',1,11,0,'Questo è il secondo miglior brano su questa piattaforma.'),(7,'The Beat of Nature',3,'2024-04-08 13:20:54','../imgs/brano/7.jpg','../brani/7.mp3',2,18,3,'Una melodia che mi fa sentire in sintonia con la natura...'),(8,'Sinister',5,'2024-04-08 13:21:43','../imgs/brano/8.jpg','../brani/8.mp3',1,8,0,'Questo è il terzo miglior brano su questa piattaforma.');
/*!40000 ALTER TABLE `brano` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commento`
--

DROP TABLE IF EXISTS `commento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `commento` (
  `utente` int(11) NOT NULL COMMENT 'UID dell''utente che ha lasciato il commento',
  `brano` int(11) NOT NULL COMMENT 'SID del brano su cui è stato lasciato il commento',
  `dataCommento` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `contenuto` varchar(140) NOT NULL,
  PRIMARY KEY (`utente`,`brano`,`dataCommento`),
  KEY `brano` (`brano`),
  CONSTRAINT `commento_ibfk_1` FOREIGN KEY (`utente`) REFERENCES `utente` (`uid`),
  CONSTRAINT `commento_ibfk_2` FOREIGN KEY (`brano`) REFERENCES `brano` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commento`
--

LOCK TABLES `commento` WRITE;
/*!40000 ALTER TABLE `commento` DISABLE KEYS */;
INSERT INTO `commento` VALUES (3,7,'2024-04-08 13:31:32','Grazie!'),(4,2,'2024-04-08 09:40:22','Molto carinaaa!'),(4,7,'2024-04-08 13:30:44','Molto rilassante!'),(5,5,'2024-04-08 13:26:08','Questo brano fa schifo!'),(5,7,'2024-04-08 13:53:21','I miei brani sono meglio!'),(6,1,'2024-04-08 13:23:51','Nooo mi hai rickrollato >:(');
/*!40000 ALTER TABLE `commento` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER aggiunta_commento
AFTER INSERT ON commento
FOR EACH ROW
	BEGIN
        UPDATE brano 
        SET numeroCommenti = numeroCommenti + 1
        WHERE sid = NEW.brano;
        
	END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `mipiace`
--

DROP TABLE IF EXISTS `mipiace`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mipiace` (
  `utente` int(11) NOT NULL COMMENT 'UID dell''utente che ha lasciato il mi piace',
  `brano` int(11) NOT NULL COMMENT 'SID del brano su cui è stato lasciato il mi piace',
  PRIMARY KEY (`utente`,`brano`),
  KEY `brano` (`brano`),
  CONSTRAINT `mipiace_ibfk_1` FOREIGN KEY (`utente`) REFERENCES `utente` (`uid`),
  CONSTRAINT `mipiace_ibfk_2` FOREIGN KEY (`brano`) REFERENCES `brano` (`sid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mipiace`
--

LOCK TABLES `mipiace` WRITE;
/*!40000 ALTER TABLE `mipiace` DISABLE KEYS */;
INSERT INTO `mipiace` VALUES (2,1),(2,2),(4,2),(6,3),(5,4),(2,5),(5,6),(2,7),(4,7),(5,8);
/*!40000 ALTER TABLE `mipiace` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER aggiunta_mipiace
AFTER INSERT ON mipiace
FOR EACH ROW
	BEGIN
        UPDATE brano 
        SET numeroMiPiace = numeroMiPiace + 1
        WHERE sid = NEW.brano;
        
	END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8 */ ;
/*!50003 SET character_set_results = utf8 */ ;
/*!50003 SET collation_connection  = utf8_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_ZERO_IN_DATE,NO_ZERO_DATE,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER rimozione_mipiace
AFTER DELETE ON mipiace
FOR EACH ROW
	BEGIN
        UPDATE brano 
        SET numeroMiPiace = numeroMiPiace - 1
        WHERE sid = OLD.brano;
        
	END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `utente`
--

DROP TABLE IF EXISTS `utente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `utente` (
  `uid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'User ID',
  `tipo` tinyint(4) NOT NULL COMMENT '0 se è un utente amministratore, 1 se è un un utente normale',
  `username` varchar(16) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `immagineUtente` varchar(255) NOT NULL DEFAULT '../imgs/profilo/default.png',
  `descrizioneUtente` varchar(140) NOT NULL DEFAULT '',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `Username` (`username`),
  UNIQUE KEY `Email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utente`
--

LOCK TABLES `utente` WRITE;
/*!40000 ALTER TABLE `utente` DISABLE KEYS */;
INSERT INTO `utente` VALUES (1,0,'admin','admin@admin.it','$2y$10$t6dsWakCu8hnV2qnh9U3JORbrZD.oJAl17XQuKHiSzQsy8ybyvGOi','../imgs/profilo/1.jpg',''),(2,1,'Simone','simone@email.it','$2y$10$6gLyha.C4pQ.72W0VqC/ueccZ/fuwlpvpDa/QZnGRsXt25qQiQDky','../imgs/profilo/2.png','Buongiorno mondo!'),(3,1,'Riccardo','riccardo@studenti.unipi.it','$2y$10$WIkB2rBH9QOje80qIqSnr.2T8ThU390e/UPK5MgCR6lWQXxExZ5I.','../imgs/profilo/3.jpg','Qui caricherò i miei brani suonati con la chitarra!'),(4,1,'Martina','martina@boh.com','$2y$10$plp7uUss8Uf0QP/j7alrWOCrrsM9g5h3ybHnhLBUNR0UYzYl9.F3G','../imgs/profilo/4.jpg','Ciaoooo <3'),(5,1,'Daniele','daniele@gmail.com','$2y$10$.28ixKHMi1Q7PRAHjr4fn.SSVlLHS4hPGwgLJUnT2J4wMNyDYCUgK','../imgs/profilo/5.jpg','I miei brani sono i migliori!'),(6,1,'Nicola','nicola@felice.it','$2y$10$nAVGntkdX.oNJ9lXcZRNAOb9PC54iuG6Xyk9b0YbjxnWOel1Gak/6','../imgs/profilo/6.jpg','Ascoltate i miei brani techno!');
/*!40000 ALTER TABLE `utente` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-04-11 19:22:01
