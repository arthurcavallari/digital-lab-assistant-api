USE `13pra11`;
-- MySQL dump 10.13  Distrib 5.5.16, for Win32 (x86)
--
-- Host: localhost    Database: 13pra11
-- ------------------------------------------------------
-- Server version	5.5.16

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
-- Table structure for table `banned_users`
--

DROP TABLE IF EXISTS `banned_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banned_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `flag_id` int(11) NOT NULL,
  `banned_by_user_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_idx` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `deleted_laboratories`
--

DROP TABLE IF EXISTS `deleted_laboratories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `deleted_laboratories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `laboratory_id` int(11) NOT NULL,
  `flag_id` int(11) NOT NULL,
  `deleted_by_user_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `labId_idx` (`laboratory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `image_uploads`
--

DROP TABLE IF EXISTS `image_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `image_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `w_id` varchar(45) DEFAULT NULL,
  `checksum` varchar(120) DEFAULT NULL,
  `dateTimeLastUpdated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `submission_id` int(11) DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `laboratories`
--

DROP TABLE IF EXISTS `laboratories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `laboratories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `owner_user_id` int(11) NOT NULL,
  `title` varchar(80) NOT NULL,
  `authorFirstName` varchar(60) NOT NULL,
  `authorLastName` varchar(60) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `topic` varchar(100) NOT NULL,
  `area` varchar(100) NOT NULL,
  `organisation` varchar(80) DEFAULT NULL,
  `pages` int(11) NOT NULL DEFAULT '1',
  `isPublished` tinyint(1) NOT NULL,
  `dateTimeCreated` datetime NOT NULL,
  `dateTimeLastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dateTimePublished` datetime DEFAULT NULL,
  `lastWidgetCounter` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `authorId_idx` (`owner_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `laboratory_fields`
--

DROP TABLE IF EXISTS `laboratory_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `laboratory_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `w_id` varchar(45) NOT NULL,
  `laboratory_id` int(11) NOT NULL,
  `fieldType` int(11) NOT NULL,
  `posZ` int(11) NOT NULL DEFAULT '0',
  `posX` int(11) NOT NULL DEFAULT '0',
  `posY` int(11) NOT NULL DEFAULT '0',
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `pageNumber` int(11) NOT NULL,
  `label` varchar(120) DEFAULT NULL,
  `value` longblob,
  `readOnly` tinyint(1) NOT NULL DEFAULT '0',
  `table_id` int(11) DEFAULT NULL COMMENT 'references to a table block',
  `tableCellRow` int(11) DEFAULT NULL COMMENT 'specific to blocks linked to a table',
  `tableCellColumn` int(11) DEFAULT NULL COMMENT 'specific to blocks linked to a table',
  `tableRowCount` int(11) DEFAULT NULL COMMENT 'property of the table block',
  `tableColumnCount` int(11) DEFAULT NULL COMMENT 'property of the table block',
  `timerType` tinyint(4) DEFAULT NULL,
  `isStoppable` tinyint(1) DEFAULT NULL,
  `isPausable` tinyint(1) DEFAULT NULL,
  `frameWidth` int(11) DEFAULT NULL,
  `frameHeight` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `labId_idx` (`laboratory_id`),
  KEY `fk_fields_idx` (`table_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `laboratory_sessions`
--

DROP TABLE IF EXISTS `laboratory_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `laboratory_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `startDateTime` datetime NOT NULL,
  `endDateTime` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_doing_lab_idx` (`user_id`),
  KEY `fk_session_of_lab_idx` (`lab_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `submission_fields`
--

DROP TABLE IF EXISTS `submission_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submission_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_id` int(11) NOT NULL,
  `field_id` varchar(45) NOT NULL,
  `value` longblob,
  `assessmentNotes` blob,
  PRIMARY KEY (`id`),
  KEY `submissionId_idx` (`submission_id`),
  KEY `field_idx` (`field_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `submissions`
--

DROP TABLE IF EXISTS `submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `laboratory_id` int(11) NOT NULL,
  `authorFirstName` varchar(60) NOT NULL,
  `authorLastName` varchar(60) NOT NULL,
  `dateTimeCreated` datetime NOT NULL,
  `dateTimeLastUpdated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isSubmitted` tinyint(1) NOT NULL DEFAULT '0',
  `dateTimeSubmitted` datetime DEFAULT NULL,
  `dateTimeAssessed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `authorId_idx` (`user_id`),
  KEY `labId_idx` (`laboratory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tableinfo`
--

DROP TABLE IF EXISTS `tableinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tableinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) NOT NULL COMMENT '0 - row.... 1- col',
  `title` varchar(30) NOT NULL COMMENT 'col or row title',
  PRIMARY KEY (`id`),
  KEY `fk_table_has_tableInfo_idx` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_favourites`
--

DROP TABLE IF EXISTS `user_favourites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_favourites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `favouritedUser_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId_idx` (`user_id`),
  KEY `favouritedUserId_idx` (`favouritedUser_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_flags`
--

DROP TABLE IF EXISTS `user_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_flags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `laboratory_id` int(11) NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `reviewed` tinyint(4) DEFAULT '0',
  `reviewer_user_id` int(11) DEFAULT NULL,
  `reviewer_notes` varchar(255) DEFAULT NULL,
  `reviewer_action` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `userId_idx` (`user_id`),
  KEY `labId_idx` (`laboratory_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_reset_code`
--

DROP TABLE IF EXISTS `user_reset_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_reset_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `resetcode` varchar(80) NOT NULL,
  `expiry_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(80) NOT NULL,
  `ip` varchar(80) NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`,`user_id`,`session_id`),
  UNIQUE KEY `session_id_UNIQUE` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstName` varchar(60) DEFAULT NULL,
  `lastName` varchar(60) DEFAULT NULL,
  `organisation` varchar(80) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `country` varchar(60) DEFAULT NULL,
  `isAdmin` tinyint(1) NOT NULL DEFAULT '0',
  `dateTimeRegistered` datetime NOT NULL,
  `dateTimeLastUpdated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_UNIQUE` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@example.com','5f4dcc3b5aa765d61d8327deb882cf99','John','Smith','Company','admin@example.com','Australia',1,'" . date("Y-m-d H:i:s") . "','0000-00-00 00:00:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-10-20 14:23:16
