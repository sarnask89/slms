/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.2-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: slmsdb
-- ------------------------------------------------------
-- Server version	11.8.2-MariaDB-1 from Debian

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Current Database: `slmsdb`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `slmsdb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_uca1400_ai_ci */;

USE `slmsdb`;

--
-- Table structure for table `access_level_permissions`
--

DROP TABLE IF EXISTS `access_level_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_level_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `access_level_id` int(11) NOT NULL,
  `section` varchar(50) NOT NULL,
  `action` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_permission` (`access_level_id`,`section`,`action`),
  CONSTRAINT `access_level_permissions_ibfk_1` FOREIGN KEY (`access_level_id`) REFERENCES `access_levels` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_level_permissions`
--

LOCK TABLES `access_level_permissions` WRITE;
/*!40000 ALTER TABLE `access_level_permissions` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `access_level_permissions` VALUES
(70,3,'dashboard','view','2025-07-20 00:03:59'),
(71,3,'clients','view','2025-07-20 00:03:59'),
(72,3,'devices','view','2025-07-20 00:03:59'),
(73,3,'networks','view','2025-07-20 00:03:59'),
(74,3,'services','view','2025-07-20 00:03:59'),
(75,3,'financial','view','2025-07-20 00:03:59'),
(76,3,'monitoring','view','2025-07-20 00:03:59'),
(77,3,'users','view','2025-07-20 00:03:59'),
(78,3,'system','view','2025-07-20 00:03:59'),
(79,4,'dashboard','view','2025-07-20 00:03:59'),
(80,4,'clients','view','2025-07-20 00:03:59'),
(81,4,'devices','view','2025-07-20 00:03:59'),
(82,4,'networks','view','2025-07-20 00:03:59'),
(83,4,'services','view','2025-07-20 00:03:59'),
(84,4,'financial','view','2025-07-20 00:03:59'),
(85,4,'monitoring','view','2025-07-20 00:03:59'),
(86,2,'dashboard','view','2025-07-20 00:56:56'),
(87,2,'clients','view','2025-07-20 00:56:56'),
(88,2,'clients','export','2025-07-20 00:56:56'),
(89,2,'devices','view','2025-07-20 00:56:56'),
(90,2,'devices','add','2025-07-20 00:56:56'),
(91,2,'devices','edit','2025-07-20 00:56:56'),
(92,2,'devices','monitor','2025-07-20 00:56:56'),
(93,2,'services','view','2025-07-20 00:56:56'),
(94,2,'services','add','2025-07-20 00:56:56'),
(95,2,'services','edit','2025-07-20 00:56:56'),
(96,2,'services','assign','2025-07-20 00:56:56'),
(97,2,'financial','view','2025-07-20 00:56:56'),
(98,2,'financial','add','2025-07-20 00:56:56'),
(99,2,'financial','edit','2025-07-20 00:56:56'),
(100,2,'financial','export','2025-07-20 00:56:56'),
(101,2,'monitoring','view','2025-07-20 00:56:56'),
(102,2,'monitoring','alerts','2025-07-20 00:56:56'),
(103,2,'monitoring','reports','2025-07-20 00:56:56'),
(104,2,'users','view','2025-07-20 00:56:56'),
(105,2,'system','view','2025-07-20 00:56:56'),
(149,1,'dashboard','view','2025-07-25 01:41:01'),
(150,1,'dashboard','customize','2025-07-25 01:41:01'),
(151,1,'dashboard','export','2025-07-25 01:41:01'),
(152,1,'clients','view','2025-07-25 01:41:01'),
(153,1,'clients','add','2025-07-25 01:41:01'),
(154,1,'clients','edit','2025-07-25 01:41:01'),
(155,1,'clients','delete','2025-07-25 01:41:01'),
(156,1,'clients','export','2025-07-25 01:41:01'),
(157,1,'devices','view','2025-07-25 01:41:01'),
(158,1,'devices','add','2025-07-25 01:41:01'),
(159,1,'devices','edit','2025-07-25 01:41:01'),
(160,1,'devices','delete','2025-07-25 01:41:01'),
(161,1,'devices','monitor','2025-07-25 01:41:01'),
(162,1,'devices','configure','2025-07-25 01:41:01'),
(163,1,'networks','view','2025-07-25 01:41:01'),
(164,1,'networks','add','2025-07-25 01:41:01'),
(165,1,'networks','edit','2025-07-25 01:41:01'),
(166,1,'networks','delete','2025-07-25 01:41:01'),
(167,1,'networks','dhcp','2025-07-25 01:41:01'),
(168,1,'services','view','2025-07-25 01:41:01'),
(169,1,'services','add','2025-07-25 01:41:01'),
(170,1,'services','edit','2025-07-25 01:41:01'),
(171,1,'services','delete','2025-07-25 01:41:01'),
(172,1,'services','assign','2025-07-25 01:41:01'),
(173,1,'financial','view','2025-07-25 01:41:01'),
(174,1,'financial','add','2025-07-25 01:41:01'),
(175,1,'financial','edit','2025-07-25 01:41:01'),
(176,1,'financial','delete','2025-07-25 01:41:01'),
(177,1,'financial','export','2025-07-25 01:41:01'),
(178,1,'monitoring','view','2025-07-25 01:41:01'),
(179,1,'monitoring','configure','2025-07-25 01:41:01'),
(180,1,'monitoring','alerts','2025-07-25 01:41:01'),
(181,1,'monitoring','reports','2025-07-25 01:41:01'),
(182,1,'users','view','2025-07-25 01:41:01'),
(183,1,'users','add','2025-07-25 01:41:01'),
(184,1,'users','edit','2025-07-25 01:41:01'),
(185,1,'users','delete','2025-07-25 01:41:01'),
(186,1,'users','permissions','2025-07-25 01:41:01'),
(187,1,'system','view','2025-07-25 01:41:01'),
(188,1,'system','configure','2025-07-25 01:41:01'),
(189,1,'system','backup','2025-07-25 01:41:01'),
(190,1,'system','logs','2025-07-25 01:41:01'),
(191,1,'system','maintenance','2025-07-25 01:41:01');
/*!40000 ALTER TABLE `access_level_permissions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `access_levels`
--

DROP TABLE IF EXISTS `access_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `access_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `access_levels_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `access_levels`
--

LOCK TABLES `access_levels` WRITE;
/*!40000 ALTER TABLE `access_levels` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `access_levels` VALUES
(1,'Administrator','Full system access with all permissions',1,'2025-07-20 00:03:59','2025-07-20 00:03:59'),
(2,'Manager','Manager access with write permissions to most modules',1,'2025-07-20 00:03:59','2025-07-20 00:56:56'),
(3,'User','Standard user access with read permissions',1,'2025-07-20 00:03:59','2025-07-20 00:03:59'),
(4,'Viewer','Read-only access to basic modules',1,'2025-07-20 00:03:59','2025-07-20 00:03:59');
/*!40000 ALTER TABLE `access_levels` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `admin_users`
--

DROP TABLE IF EXISTS `admin_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `admin_users` VALUES
(1,'admin','$2y$12$9.nnJUVrkItJ5Bt29qc7oezhPU07IoiXehf0J0t9syerZNqPAQWnu','admin@example.com','admin','2025-07-16 05:18:45','2025-07-16 05:18:46');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bridge_access`
--

DROP TABLE IF EXISTS `bridge_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bridge_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac_address` varchar(20) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `user_role` varchar(20) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT NULL,
  `status` enum('active','expired') DEFAULT 'active',
  `expired_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bridge_access`
--

LOCK TABLES `bridge_access` WRITE;
/*!40000 ALTER TABLE `bridge_access` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bridge_access` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bridge_connection_logs`
--

DROP TABLE IF EXISTS `bridge_connection_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bridge_connection_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac_address` varchar(20) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `user_role` varchar(20) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bridge_connection_logs`
--

LOCK TABLES `bridge_connection_logs` WRITE;
/*!40000 ALTER TABLE `bridge_connection_logs` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bridge_connection_logs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bridge_filter_rules`
--

DROP TABLE IF EXISTS `bridge_filter_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bridge_filter_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac_address` varchar(20) NOT NULL,
  `action` varchar(20) DEFAULT NULL,
  `rule_data` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `removed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bridge_filter_rules`
--

LOCK TABLES `bridge_filter_rules` WRITE;
/*!40000 ALTER TABLE `bridge_filter_rules` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bridge_filter_rules` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bridge_mangle_rules`
--

DROP TABLE IF EXISTS `bridge_mangle_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bridge_mangle_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac_address` varchar(20) NOT NULL,
  `action` varchar(20) DEFAULT NULL,
  `rule_data` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `removed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bridge_mangle_rules`
--

LOCK TABLES `bridge_mangle_rules` WRITE;
/*!40000 ALTER TABLE `bridge_mangle_rules` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bridge_mangle_rules` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `bridge_nat_rules`
--

DROP TABLE IF EXISTS `bridge_nat_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bridge_nat_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mac_address` varchar(20) NOT NULL,
  `action` varchar(20) DEFAULT NULL,
  `rule_data` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `removed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bridge_nat_rules`
--

LOCK TABLES `bridge_nat_rules` WRITE;
/*!40000 ALTER TABLE `bridge_nat_rules` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `bridge_nat_rules` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `captive_portal_users`
--

DROP TABLE IF EXISTS `captive_portal_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `captive_portal_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user','guest') DEFAULT 'user',
  `max_bandwidth` int(11) DEFAULT NULL,
  `allowed_domains` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `captive_portal_users`
--

LOCK TABLES `captive_portal_users` WRITE;
/*!40000 ALTER TABLE `captive_portal_users` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `captive_portal_users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `categories` VALUES
(1,'Electronics','Electronic devices and gadgets'),
(2,'Clothing','Apparel and fashion items'),
(3,'Books','Books and publications'),
(4,'Electronics','Electronic devices and gadgets'),
(5,'Clothing','Apparel and fashion items'),
(6,'Books','Books and publications');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Poland',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cities`
--

LOCK TABLES `cities` WRITE;
/*!40000 ALTER TABLE `cities` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `cities` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `client_services`
--

DROP TABLE IF EXISTS `client_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `client_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','suspended','cancelled') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `client_services_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `client_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `client_services`
--

LOCK TABLES `client_services` WRITE;
/*!40000 ALTER TABLE `client_services` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `client_services` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `pesel` varchar(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `nip` varchar(10) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `pesel` (`pesel`),
  KEY `idx_clients_email` (`email`),
  KEY `idx_clients_status` (`status`),
  KEY `idx_clients_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `column_config`
--

DROP TABLE IF EXISTS `column_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `column_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(50) NOT NULL,
  `column_name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `visible` tinyint(1) DEFAULT 1,
  `order_position` int(11) DEFAULT 0,
  `width` varchar(20) DEFAULT NULL,
  `sortable` tinyint(1) DEFAULT 1,
  `filterable` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_table_column` (`table_name`,`column_name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `column_config`
--

LOCK TABLES `column_config` WRITE;
/*!40000 ALTER TABLE `column_config` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `column_config` VALUES
(1,'clients','id','ID',1,1,NULL,1,0,'2025-07-20 01:44:29'),
(2,'clients','first_name','Imię',1,2,NULL,1,1,'2025-07-20 01:44:29'),
(3,'clients','last_name','Nazwisko',1,3,NULL,1,1,'2025-07-20 01:44:29'),
(4,'clients','phone','Telefon',1,4,NULL,0,1,'2025-07-20 01:44:29'),
(5,'clients','email','Email',1,5,NULL,0,1,'2025-07-20 01:44:29'),
(6,'clients','status','Status',1,6,NULL,1,1,'2025-07-20 01:44:29'),
(7,'clients','created_at','Data dodania',1,7,NULL,1,0,'2025-07-20 01:44:29'),
(8,'devices','id','ID',1,1,NULL,1,0,'2025-07-20 01:44:29'),
(9,'devices','name','Nazwa',1,2,NULL,1,1,'2025-07-20 01:44:29'),
(10,'devices','type','Typ',1,3,NULL,1,1,'2025-07-20 01:44:29'),
(11,'devices','ip_address','Adres IP',1,4,NULL,0,1,'2025-07-20 01:44:29'),
(12,'devices','status','Status',1,5,NULL,1,1,'2025-07-20 01:44:29'),
(13,'devices','location','Lokalizacja',1,6,NULL,0,1,'2025-07-20 01:44:29'),
(14,'devices','last_seen','Ostatnio widziany',1,7,NULL,1,0,'2025-07-20 01:44:29'),
(15,'networks','id','ID',1,1,NULL,1,0,'2025-07-20 01:44:29'),
(16,'networks','name','Nazwa',1,2,NULL,1,1,'2025-07-20 01:44:29'),
(17,'networks','network_address','Adres sieci',1,3,NULL,0,1,'2025-07-20 01:44:29'),
(18,'networks','gateway','Brama',1,4,NULL,0,1,'2025-07-20 01:44:29'),
(19,'networks','vlan_id','VLAN ID',1,5,NULL,1,1,'2025-07-20 01:44:29'),
(20,'networks','description','Opis',1,6,NULL,0,1,'2025-07-20 01:44:29');
/*!40000 ALTER TABLE `column_config` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `column_configs`
--

DROP TABLE IF EXISTS `column_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `column_configs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(50) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_label` varchar(255) NOT NULL,
  `field_type` varchar(50) NOT NULL DEFAULT 'text',
  `is_visible` tinyint(1) DEFAULT 1,
  `is_searchable` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `options` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_module_field` (`module_name`,`field_name`),
  KEY `idx_module_name` (`module_name`),
  KEY `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `column_configs`
--

LOCK TABLES `column_configs` WRITE;
/*!40000 ALTER TABLE `column_configs` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `column_configs` VALUES
(1,'clients','id','identyfikator klienta','text',1,1,1,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(2,'clients','name','nazwa klienta','text',1,1,2,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(3,'clients','altname','alternatywna nazwa klienta','text',0,1,3,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(4,'clients','address','adres','textarea',1,1,4,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(5,'clients','post_name','nazwa korespondencyjna','text',0,1,5,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(6,'clients','post_address','adres korespondencyjny','textarea',0,1,6,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(7,'clients','location_name','nazwa lokalizacji','text',0,1,7,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(8,'clients','location_address','adres lokalizacyjny','textarea',0,1,8,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(9,'clients','email','e-mail','email',1,1,9,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(10,'clients','bankaccount','alternatywny rachunek bankowy','text',0,1,10,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(11,'clients','ten','NIP','text',1,1,11,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(12,'clients','ssn','PESEL','text',0,1,12,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(13,'clients','additional_info','informacje dodatkowe','textarea',0,1,13,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(14,'clients','notes','notatki','textarea',0,1,14,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(15,'clients','documentmemo','notatka na dokumentach','textarea',0,1,15,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24'),
(16,'clients','contact_info','informacje kontaktowe','text',0,1,16,NULL,'2025-07-24 23:37:24','2025-07-24 23:37:24');
/*!40000 ALTER TABLE `column_configs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `dashboard_config`
--

DROP TABLE IF EXISTS `dashboard_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dashboard_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashboard_config`
--

LOCK TABLES `dashboard_config` WRITE;
/*!40000 ALTER TABLE `dashboard_config` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `dashboard_config` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `dashboard_menu`
--

DROP TABLE IF EXISTS `dashboard_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dashboard_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(100) NOT NULL,
  `label` varchar(100) NOT NULL,
  `visible` tinyint(1) DEFAULT 1,
  `order_index` int(11) DEFAULT 0,
  `menu_level` varchar(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=658 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dashboard_menu`
--

LOCK TABLES `dashboard_menu` WRITE;
/*!40000 ALTER TABLE `dashboard_menu` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `dashboard_menu` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `demo_users`
--

DROP TABLE IF EXISTS `demo_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `demo_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `demo_users`
--

LOCK TABLES `demo_users` WRITE;
/*!40000 ALTER TABLE `demo_users` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `demo_users` VALUES
(1,'John Doe','john@example.com','555-0101','2025-07-16 04:56:02'),
(2,'Jane Smith','jane@example.com','555-0102','2025-07-16 04:56:02'),
(3,'Bob Johnson','bob@example.com','555-0103','2025-07-16 04:56:02'),
(4,'Alice Brown','alice@example.com','555-0104','2025-07-16 04:56:02'),
(5,'Charlie Wilson','charlie@example.com','555-0105','2025-07-16 04:56:02');
/*!40000 ALTER TABLE `demo_users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `devices`
--

DROP TABLE IF EXISTS `devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` enum('router','switch','server','access_point','firewall','other') DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `ip_address` varchar(15) DEFAULT NULL,
  `mac_address` varchar(17) DEFAULT NULL,
  `location` text DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `network_id` int(11) DEFAULT NULL,
  `status` enum('online','offline','maintenance') DEFAULT 'offline',
  `last_seen` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_devices_client` (`client_id`),
  KEY `idx_devices_network` (`network_id`),
  KEY `idx_devices_status` (`status`),
  CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  CONSTRAINT `devices_ibfk_2` FOREIGN KEY (`network_id`) REFERENCES `networks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `devices`
--

LOCK TABLES `devices` WRITE;
/*!40000 ALTER TABLE `devices` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `devices` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `discovered_devices`
--

DROP TABLE IF EXISTS `discovered_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `discovered_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `sys_name` varchar(255) DEFAULT NULL,
  `sys_descr` text DEFAULT NULL,
  `method` enum('SNMP','MNDP') NOT NULL,
  `discovered_at` timestamp NULL DEFAULT current_timestamp(),
  `imported` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip_method` (`ip_address`,`method`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discovered_devices`
--

LOCK TABLES `discovered_devices` WRITE;
/*!40000 ALTER TABLE `discovered_devices` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `discovered_devices` VALUES
(1,'10.0.222.223','','MNDP:   (MAC: )','MNDP','2025-07-19 14:27:18',0),
(2,'10.0.222.36','','MNDP:  MikroTik (MAC: F4:1E:57:14:94:E6)','MNDP','2025-07-19 14:27:18',0),
(3,'10.0.222.17','','MNDP:  CSS-CIESLI7 (MAC: D4:01:C3:BB:B3:DA)','MNDP','2025-07-19 14:27:18',0),
(4,'10.0.222.6','','MNDP:  CRS-5S-4SP (MAC: 78:9A:18:25:58:4D)','MNDP','2025-07-19 14:27:18',0),
(5,'10.0.222.236','','MNDP:  MikroTik (MAC: 18:FD:74:90:A2:7B)','MNDP','2025-07-19 14:27:18',0),
(6,'10.0.222.86','','MNDP:  MikroTik (MAC: 90:E2:BA:F1:FB:F5)','MNDP','2025-07-19 14:27:19',0),
(7,'10.0.222.1','','MNDP:  R1-Epix (MAC: 64:D1:54:3B:20:5F)','MNDP','2025-07-19 14:27:19',0),
(8,'217.117.132.129','','MNDP:  MikroTik (MAC: D4:CA:6D:41:F2:28)','MNDP','2025-07-19 14:27:19',0),
(9,'10.0.222.8','','MNDP:  MikroTik (MAC: 18:FD:74:EA:E7:91)','MNDP','2025-07-19 14:27:19',0),
(10,'10.0.222.33','','MNDP:  CRS-5S-1C-CIESLI3 (MAC: D4:01:C3:6B:D8:28)','MNDP','2025-07-19 14:27:20',0),
(11,'10.0.222.3','','MNDP:  R3-Firewall (MAC: B8:69:F4:91:99:75)','MNDP','2025-07-19 14:27:20',0);
/*!40000 ALTER TABLE `discovered_devices` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `interface_stats`
--

DROP TABLE IF EXISTS `interface_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `interface_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `interface_name` varchar(64) NOT NULL,
  `rx_bytes` bigint(20) unsigned NOT NULL,
  `tx_bytes` bigint(20) unsigned NOT NULL,
  `rx_packets` bigint(20) unsigned DEFAULT 0,
  `tx_packets` bigint(20) unsigned DEFAULT 0,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`,`interface_name`,`timestamp`)
) ENGINE=InnoDB AUTO_INCREMENT=393 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `interface_stats`
--

LOCK TABLES `interface_stats` WRITE;
/*!40000 ALTER TABLE `interface_stats` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `interface_stats` VALUES
(1,41,'lo',0,0,0,0,'2025-07-19 18:32:42'),
(2,41,'ether1::[CRS-24S]',0,0,0,0,'2025-07-19 18:32:42'),
(3,41,'ether4::[ CRS-24G-2S + ]',0,0,0,0,'2025-07-19 18:32:42'),
(4,41,'ether5::[CRS-24S(+)]',0,0,0,0,'2025-07-19 18:32:42'),
(5,41,'ether6:::[CCR-1036-2S(+)(R1)]',0,0,0,0,'2025-07-19 18:32:42'),
(6,41,'ether7',0,0,0,0,'2025-07-19 18:32:42'),
(7,41,'bridge1',0,0,0,0,'2025-07-19 18:32:42'),
(8,41,'V222->R1',0,0,0,0,'2025-07-19 18:32:42'),
(9,41,'V861',0,0,0,0,'2025-07-19 18:32:42'),
(10,41,'V222->S6',0,0,0,0,'2025-07-19 18:32:42'),
(11,41,'BR_MGM',0,0,0,0,'2025-07-19 18:32:42'),
(12,41,'V1604',0,0,0,0,'2025-07-19 18:32:42'),
(13,41,'V222->S24',0,0,0,0,'2025-07-19 18:32:42'),
(14,41,'V222-S8',0,0,0,0,'2025-07-19 18:32:42'),
(15,41,'V120-24G',0,0,0,0,'2025-07-19 18:32:42'),
(16,41,'V111',0,0,0,0,'2025-07-19 18:32:42'),
(17,41,'ospf_lo',0,0,0,0,'2025-07-19 18:32:42'),
(18,41,'V130-24G',0,0,0,0,'2025-07-19 18:32:42'),
(19,41,'BR_V130',0,0,0,0,'2025-07-19 18:32:42'),
(20,41,'bridge2',0,0,0,0,'2025-07-19 18:32:42'),
(21,41,'v1011-oltsmg',0,0,0,0,'2025-07-19 18:32:42'),
(22,41,'BR_KRO4_MIC25',0,0,0,0,'2025-07-19 18:32:42'),
(23,41,'bridge3',0,0,0,0,'2025-07-19 18:32:42'),
(24,41,'V1076',0,0,0,0,'2025-07-19 18:32:42'),
(25,41,'BRV1007-CIESLI3',0,0,0,0,'2025-07-19 18:32:42'),
(26,41,'V1305',0,0,0,0,'2025-07-19 18:32:42'),
(27,41,'V1303',0,0,0,0,'2025-07-19 18:32:42'),
(28,41,'BRV1305',0,0,0,0,'2025-07-19 18:32:42'),
(29,41,'BRV1303-CIESLI3',0,0,0,0,'2025-07-19 18:32:42'),
(30,41,'V1301',0,0,0,0,'2025-07-19 18:32:42'),
(31,41,'V1302',0,0,0,0,'2025-07-19 18:32:42'),
(32,41,'BRV1301-CIESLI3',0,0,0,0,'2025-07-19 18:32:42'),
(33,41,'BRV1302-CIESLI3',0,0,0,0,'2025-07-19 18:32:42'),
(34,41,'BR_V120',0,0,0,0,'2025-07-19 18:32:42'),
(35,41,'BRV224-KROLA4:MIC25',0,0,0,0,'2025-07-19 18:32:42'),
(36,41,'ether8[PROXY]',0,0,0,0,'2025-07-19 18:32:42'),
(37,41,'V1204',0,0,0,0,'2025-07-19 18:32:42'),
(38,41,'V2416(10.24.16.0)',0,0,0,0,'2025-07-19 18:32:42'),
(39,41,'V2412(10.24.12.0)',0,0,0,0,'2025-07-19 18:32:42'),
(40,41,'V2420(10.24.20.0)',0,0,0,0,'2025-07-19 18:32:42'),
(41,41,'br1204',0,0,0,0,'2025-07-19 18:32:42'),
(42,41,'br1212',0,0,0,0,'2025-07-19 18:32:42'),
(43,41,'br1220',0,0,0,0,'2025-07-19 18:32:42'),
(44,41,'br1216',0,0,0,0,'2025-07-19 18:32:42'),
(45,41,'BR_V130_2-SFP',0,0,0,0,'2025-07-19 18:32:42'),
(46,41,'V1209',0,0,0,0,'2025-07-19 18:32:42'),
(47,41,'br1209',0,0,0,0,'2025-07-19 18:32:42'),
(48,41,'br1201',0,0,0,0,'2025-07-19 18:32:42'),
(49,41,'V2421(10.0.3.0)',0,0,0,0,'2025-07-19 18:32:42'),
(50,41,'br1221',0,0,0,0,'2025-07-19 18:32:42'),
(51,41,'V1205',0,0,0,0,'2025-07-19 18:32:42'),
(52,41,'V1601',0,0,0,0,'2025-07-19 18:32:42'),
(53,41,'V1612',0,0,0,0,'2025-07-19 18:32:42'),
(54,41,'V1613',0,0,0,0,'2025-07-19 18:32:42'),
(55,41,'V1611',0,0,0,0,'2025-07-19 18:32:42'),
(56,41,'V1304',0,0,0,0,'2025-07-19 18:32:42'),
(57,41,'V1614',0,0,0,0,'2025-07-19 18:32:42'),
(58,41,'V1615',0,0,0,0,'2025-07-19 18:32:42'),
(59,41,'V1202',0,0,0,0,'2025-07-19 18:32:42'),
(60,41,'V1203',0,0,0,0,'2025-07-19 18:32:42'),
(61,41,'V1206',0,0,0,0,'2025-07-19 18:32:42'),
(62,41,'V1207',0,0,0,0,'2025-07-19 18:32:42'),
(63,41,'V1210',0,0,0,0,'2025-07-19 18:32:42'),
(64,41,'V1211',0,0,0,0,'2025-07-19 18:32:42'),
(65,41,'V1213',0,0,0,0,'2025-07-19 18:32:42'),
(66,41,'V1214',0,0,0,0,'2025-07-19 18:32:42'),
(67,41,'V1217',0,0,0,0,'2025-07-19 18:32:42'),
(68,41,'V1218',0,0,0,0,'2025-07-19 18:32:42'),
(69,41,'V1219',0,0,0,0,'2025-07-19 18:32:42'),
(70,41,'V1222',0,0,0,0,'2025-07-19 18:32:42'),
(71,41,'V1223',0,0,0,0,'2025-07-19 18:32:42'),
(72,41,'V1224',0,0,0,0,'2025-07-19 18:32:42'),
(73,41,'V120',0,0,0,0,'2025-07-19 18:32:42'),
(74,41,'V100',0,0,0,0,'2025-07-19 18:32:42'),
(75,41,'V100-24G',0,0,0,0,'2025-07-19 18:32:42'),
(76,41,'V219',0,0,0,0,'2025-07-19 18:32:42'),
(77,41,'BRIDGE_HS_UPOMNIENIE',0,0,0,0,'2025-07-19 18:32:42'),
(78,41,'V219-24S',0,0,0,0,'2025-07-19 18:32:42'),
(79,41,'V219-24G',0,0,0,0,'2025-07-19 18:32:42'),
(80,41,'V212',0,0,0,0,'2025-07-19 18:32:42'),
(81,41,'BRv212-iptv',0,0,0,0,'2025-07-19 18:32:42'),
(82,41,'V3333-PPP8',0,0,0,0,'2025-07-19 18:32:42'),
(83,41,'V3333-PPP24',0,0,0,0,'2025-07-19 18:32:42'),
(84,41,'V3333-PPP6',0,0,0,0,'2025-07-19 18:32:42'),
(85,41,'v119 prx',0,0,0,0,'2025-07-19 18:32:42'),
(86,41,'!!!!!proxy',0,0,0,0,'2025-07-19 18:32:42'),
(87,41,'vlan666',0,0,0,0,'2025-07-19 18:32:42'),
(88,41,'V661-BR3',0,0,0,0,'2025-07-19 18:32:42'),
(89,41,'bridge4',0,0,0,0,'2025-07-19 18:32:42'),
(90,41,'aaaa',0,0,0,0,'2025-07-19 18:32:42'),
(91,41,'<pppoe-Parole>',0,0,0,0,'2025-07-19 18:32:42'),
(92,41,'<pppoe-Sajur>',0,0,0,0,'2025-07-19 18:32:42'),
(93,41,'<pppoe-Janiszewska>',0,0,0,0,'2025-07-19 18:32:42'),
(94,41,'<pppoe-Szlachcinski>',0,0,0,0,'2025-07-19 18:32:42'),
(95,41,'<pppoe-00:30:4F:23:F6:F1>',0,0,0,0,'2025-07-19 18:32:42'),
(96,41,'<pppoe-Pepco>',0,0,0,0,'2025-07-19 18:32:42'),
(97,41,'<pppoe-Lohmatowa>',0,0,0,0,'2025-07-19 18:32:42'),
(98,41,'<pppoe- 00:15:6D:C2:73:07>',0,0,0,0,'2025-07-19 18:32:42'),
(99,41,'lo',0,0,0,0,'2025-07-19 18:35:26'),
(100,41,'ether1::[CRS-24S]',0,0,0,0,'2025-07-19 18:35:26'),
(101,41,'ether4::[ CRS-24G-2S + ]',0,0,0,0,'2025-07-19 18:35:26'),
(102,41,'ether5::[CRS-24S(+)]',0,0,0,0,'2025-07-19 18:35:26'),
(103,41,'ether6:::[CCR-1036-2S(+)(R1)]',0,0,0,0,'2025-07-19 18:35:26'),
(104,41,'ether7',0,0,0,0,'2025-07-19 18:35:26'),
(105,41,'bridge1',0,0,0,0,'2025-07-19 18:35:26'),
(106,41,'V222->R1',0,0,0,0,'2025-07-19 18:35:26'),
(107,41,'V861',0,0,0,0,'2025-07-19 18:35:26'),
(108,41,'V222->S6',0,0,0,0,'2025-07-19 18:35:26'),
(109,41,'BR_MGM',0,0,0,0,'2025-07-19 18:35:26'),
(110,41,'V1604',0,0,0,0,'2025-07-19 18:35:26'),
(111,41,'V222->S24',0,0,0,0,'2025-07-19 18:35:26'),
(112,41,'V222-S8',0,0,0,0,'2025-07-19 18:35:26'),
(113,41,'V120-24G',0,0,0,0,'2025-07-19 18:35:26'),
(114,41,'V111',0,0,0,0,'2025-07-19 18:35:26'),
(115,41,'ospf_lo',0,0,0,0,'2025-07-19 18:35:26'),
(116,41,'V130-24G',0,0,0,0,'2025-07-19 18:35:26'),
(117,41,'BR_V130',0,0,0,0,'2025-07-19 18:35:26'),
(118,41,'bridge2',0,0,0,0,'2025-07-19 18:35:26'),
(119,41,'v1011-oltsmg',0,0,0,0,'2025-07-19 18:35:26'),
(120,41,'BR_KRO4_MIC25',0,0,0,0,'2025-07-19 18:35:26'),
(121,41,'bridge3',0,0,0,0,'2025-07-19 18:35:26'),
(122,41,'V1076',0,0,0,0,'2025-07-19 18:35:26'),
(123,41,'BRV1007-CIESLI3',0,0,0,0,'2025-07-19 18:35:26'),
(124,41,'V1305',0,0,0,0,'2025-07-19 18:35:26'),
(125,41,'V1303',0,0,0,0,'2025-07-19 18:35:26'),
(126,41,'BRV1305',0,0,0,0,'2025-07-19 18:35:26'),
(127,41,'BRV1303-CIESLI3',0,0,0,0,'2025-07-19 18:35:26'),
(128,41,'V1301',0,0,0,0,'2025-07-19 18:35:26'),
(129,41,'V1302',0,0,0,0,'2025-07-19 18:35:26'),
(130,41,'BRV1301-CIESLI3',0,0,0,0,'2025-07-19 18:35:26'),
(131,41,'BRV1302-CIESLI3',0,0,0,0,'2025-07-19 18:35:26'),
(132,41,'BR_V120',0,0,0,0,'2025-07-19 18:35:26'),
(133,41,'BRV224-KROLA4:MIC25',0,0,0,0,'2025-07-19 18:35:26'),
(134,41,'ether8[PROXY]',0,0,0,0,'2025-07-19 18:35:26'),
(135,41,'V1204',0,0,0,0,'2025-07-19 18:35:26'),
(136,41,'V2416(10.24.16.0)',0,0,0,0,'2025-07-19 18:35:26'),
(137,41,'V2412(10.24.12.0)',0,0,0,0,'2025-07-19 18:35:26'),
(138,41,'V2420(10.24.20.0)',0,0,0,0,'2025-07-19 18:35:26'),
(139,41,'br1204',0,0,0,0,'2025-07-19 18:35:26'),
(140,41,'br1212',0,0,0,0,'2025-07-19 18:35:26'),
(141,41,'br1220',0,0,0,0,'2025-07-19 18:35:26'),
(142,41,'br1216',0,0,0,0,'2025-07-19 18:35:26'),
(143,41,'BR_V130_2-SFP',0,0,0,0,'2025-07-19 18:35:26'),
(144,41,'V1209',0,0,0,0,'2025-07-19 18:35:26'),
(145,41,'br1209',0,0,0,0,'2025-07-19 18:35:26'),
(146,41,'br1201',0,0,0,0,'2025-07-19 18:35:26'),
(147,41,'V2421(10.0.3.0)',0,0,0,0,'2025-07-19 18:35:26'),
(148,41,'br1221',0,0,0,0,'2025-07-19 18:35:26'),
(149,41,'V1205',0,0,0,0,'2025-07-19 18:35:26'),
(150,41,'V1601',0,0,0,0,'2025-07-19 18:35:26'),
(151,41,'V1612',0,0,0,0,'2025-07-19 18:35:26'),
(152,41,'V1613',0,0,0,0,'2025-07-19 18:35:26'),
(153,41,'V1611',0,0,0,0,'2025-07-19 18:35:26'),
(154,41,'V1304',0,0,0,0,'2025-07-19 18:35:26'),
(155,41,'V1614',0,0,0,0,'2025-07-19 18:35:26'),
(156,41,'V1615',0,0,0,0,'2025-07-19 18:35:26'),
(157,41,'V1202',0,0,0,0,'2025-07-19 18:35:26'),
(158,41,'V1203',0,0,0,0,'2025-07-19 18:35:26'),
(159,41,'V1206',0,0,0,0,'2025-07-19 18:35:26'),
(160,41,'V1207',0,0,0,0,'2025-07-19 18:35:26'),
(161,41,'V1210',0,0,0,0,'2025-07-19 18:35:26'),
(162,41,'V1211',0,0,0,0,'2025-07-19 18:35:26'),
(163,41,'V1213',0,0,0,0,'2025-07-19 18:35:26'),
(164,41,'V1214',0,0,0,0,'2025-07-19 18:35:26'),
(165,41,'V1217',0,0,0,0,'2025-07-19 18:35:26'),
(166,41,'V1218',0,0,0,0,'2025-07-19 18:35:26'),
(167,41,'V1219',0,0,0,0,'2025-07-19 18:35:26'),
(168,41,'V1222',0,0,0,0,'2025-07-19 18:35:26'),
(169,41,'V1223',0,0,0,0,'2025-07-19 18:35:26'),
(170,41,'V1224',0,0,0,0,'2025-07-19 18:35:26'),
(171,41,'V120',0,0,0,0,'2025-07-19 18:35:26'),
(172,41,'V100',0,0,0,0,'2025-07-19 18:35:26'),
(173,41,'V100-24G',0,0,0,0,'2025-07-19 18:35:26'),
(174,41,'V219',0,0,0,0,'2025-07-19 18:35:26'),
(175,41,'BRIDGE_HS_UPOMNIENIE',0,0,0,0,'2025-07-19 18:35:26'),
(176,41,'V219-24S',0,0,0,0,'2025-07-19 18:35:26'),
(177,41,'V219-24G',0,0,0,0,'2025-07-19 18:35:26'),
(178,41,'V212',0,0,0,0,'2025-07-19 18:35:26'),
(179,41,'BRv212-iptv',0,0,0,0,'2025-07-19 18:35:26'),
(180,41,'V3333-PPP8',0,0,0,0,'2025-07-19 18:35:26'),
(181,41,'V3333-PPP24',0,0,0,0,'2025-07-19 18:35:26'),
(182,41,'V3333-PPP6',0,0,0,0,'2025-07-19 18:35:26'),
(183,41,'v119 prx',0,0,0,0,'2025-07-19 18:35:26'),
(184,41,'!!!!!proxy',0,0,0,0,'2025-07-19 18:35:26'),
(185,41,'vlan666',0,0,0,0,'2025-07-19 18:35:26'),
(186,41,'V661-BR3',0,0,0,0,'2025-07-19 18:35:26'),
(187,41,'bridge4',0,0,0,0,'2025-07-19 18:35:26'),
(188,41,'aaaa',0,0,0,0,'2025-07-19 18:35:26'),
(189,41,'<pppoe-Parole>',0,0,0,0,'2025-07-19 18:35:26'),
(190,41,'<pppoe-Sajur>',0,0,0,0,'2025-07-19 18:35:26'),
(191,41,'<pppoe-Janiszewska>',0,0,0,0,'2025-07-19 18:35:26'),
(192,41,'<pppoe-Szlachcinski>',0,0,0,0,'2025-07-19 18:35:26'),
(193,41,'<pppoe-00:30:4F:23:F6:F1>',0,0,0,0,'2025-07-19 18:35:26'),
(194,41,'<pppoe-Pepco>',0,0,0,0,'2025-07-19 18:35:26'),
(195,41,'<pppoe-Lohmatowa>',0,0,0,0,'2025-07-19 18:35:26'),
(196,41,'<pppoe- 00:15:6D:C2:73:07>',0,0,0,0,'2025-07-19 18:35:26'),
(197,41,'lo',0,0,0,0,'2025-07-19 23:30:04'),
(198,41,'ether1::[CRS-24S]',0,0,0,0,'2025-07-19 23:30:04'),
(199,41,'ether4::[ CRS-24G-2S + ]',0,0,0,0,'2025-07-19 23:30:04'),
(200,41,'ether5::[CRS-24S(+)]',0,0,0,0,'2025-07-19 23:30:04'),
(201,41,'ether6:::[CCR-1036-2S(+)(R1)]',0,0,0,0,'2025-07-19 23:30:04'),
(202,41,'ether7',0,0,0,0,'2025-07-19 23:30:04'),
(203,41,'bridge1',0,0,0,0,'2025-07-19 23:30:04'),
(204,41,'V222->R1',0,0,0,0,'2025-07-19 23:30:04'),
(205,41,'V861',0,0,0,0,'2025-07-19 23:30:04'),
(206,41,'V222->S6',0,0,0,0,'2025-07-19 23:30:04'),
(207,41,'BR_MGM',0,0,0,0,'2025-07-19 23:30:04'),
(208,41,'V1604',0,0,0,0,'2025-07-19 23:30:04'),
(209,41,'V222->S24',0,0,0,0,'2025-07-19 23:30:04'),
(210,41,'V222-S8',0,0,0,0,'2025-07-19 23:30:04'),
(211,41,'V120-24G',0,0,0,0,'2025-07-19 23:30:04'),
(212,41,'V111',0,0,0,0,'2025-07-19 23:30:04'),
(213,41,'ospf_lo',0,0,0,0,'2025-07-19 23:30:04'),
(214,41,'V130-24G',0,0,0,0,'2025-07-19 23:30:04'),
(215,41,'BR_V130',0,0,0,0,'2025-07-19 23:30:04'),
(216,41,'bridge2',0,0,0,0,'2025-07-19 23:30:04'),
(217,41,'v1011-oltsmg',0,0,0,0,'2025-07-19 23:30:04'),
(218,41,'BR_KRO4_MIC25',0,0,0,0,'2025-07-19 23:30:04'),
(219,41,'bridge3',0,0,0,0,'2025-07-19 23:30:04'),
(220,41,'V1076',0,0,0,0,'2025-07-19 23:30:04'),
(221,41,'BRV1007-CIESLI3',0,0,0,0,'2025-07-19 23:30:04'),
(222,41,'V1305',0,0,0,0,'2025-07-19 23:30:04'),
(223,41,'V1303',0,0,0,0,'2025-07-19 23:30:04'),
(224,41,'BRV1305',0,0,0,0,'2025-07-19 23:30:04'),
(225,41,'BRV1303-CIESLI3',0,0,0,0,'2025-07-19 23:30:04'),
(226,41,'V1301',0,0,0,0,'2025-07-19 23:30:04'),
(227,41,'V1302',0,0,0,0,'2025-07-19 23:30:04'),
(228,41,'BRV1301-CIESLI3',0,0,0,0,'2025-07-19 23:30:04'),
(229,41,'BRV1302-CIESLI3',0,0,0,0,'2025-07-19 23:30:04'),
(230,41,'BR_V120',0,0,0,0,'2025-07-19 23:30:04'),
(231,41,'BRV224-KROLA4:MIC25',0,0,0,0,'2025-07-19 23:30:04'),
(232,41,'ether8[PROXY]',0,0,0,0,'2025-07-19 23:30:04'),
(233,41,'V1204',0,0,0,0,'2025-07-19 23:30:04'),
(234,41,'V2416(10.24.16.0)',0,0,0,0,'2025-07-19 23:30:04'),
(235,41,'V2412(10.24.12.0)',0,0,0,0,'2025-07-19 23:30:04'),
(236,41,'V2420(10.24.20.0)',0,0,0,0,'2025-07-19 23:30:04'),
(237,41,'br1204',0,0,0,0,'2025-07-19 23:30:04'),
(238,41,'br1212',0,0,0,0,'2025-07-19 23:30:04'),
(239,41,'br1220',0,0,0,0,'2025-07-19 23:30:04'),
(240,41,'br1216',0,0,0,0,'2025-07-19 23:30:04'),
(241,41,'BR_V130_2-SFP',0,0,0,0,'2025-07-19 23:30:04'),
(242,41,'V1209',0,0,0,0,'2025-07-19 23:30:04'),
(243,41,'br1209',0,0,0,0,'2025-07-19 23:30:04'),
(244,41,'br1201',0,0,0,0,'2025-07-19 23:30:04'),
(245,41,'V2421(10.0.3.0)',0,0,0,0,'2025-07-19 23:30:04'),
(246,41,'br1221',0,0,0,0,'2025-07-19 23:30:04'),
(247,41,'V1205',0,0,0,0,'2025-07-19 23:30:04'),
(248,41,'V1601',0,0,0,0,'2025-07-19 23:30:04'),
(249,41,'V1612',0,0,0,0,'2025-07-19 23:30:04'),
(250,41,'V1613',0,0,0,0,'2025-07-19 23:30:04'),
(251,41,'V1611',0,0,0,0,'2025-07-19 23:30:04'),
(252,41,'V1304',0,0,0,0,'2025-07-19 23:30:04'),
(253,41,'V1614',0,0,0,0,'2025-07-19 23:30:04'),
(254,41,'V1615',0,0,0,0,'2025-07-19 23:30:04'),
(255,41,'V1202',0,0,0,0,'2025-07-19 23:30:04'),
(256,41,'V1203',0,0,0,0,'2025-07-19 23:30:04'),
(257,41,'V1206',0,0,0,0,'2025-07-19 23:30:04'),
(258,41,'V1207',0,0,0,0,'2025-07-19 23:30:04'),
(259,41,'V1210',0,0,0,0,'2025-07-19 23:30:04'),
(260,41,'V1211',0,0,0,0,'2025-07-19 23:30:04'),
(261,41,'V1213',0,0,0,0,'2025-07-19 23:30:04'),
(262,41,'V1214',0,0,0,0,'2025-07-19 23:30:04'),
(263,41,'V1217',0,0,0,0,'2025-07-19 23:30:04'),
(264,41,'V1218',0,0,0,0,'2025-07-19 23:30:04'),
(265,41,'V1219',0,0,0,0,'2025-07-19 23:30:04'),
(266,41,'V1222',0,0,0,0,'2025-07-19 23:30:04'),
(267,41,'V1223',0,0,0,0,'2025-07-19 23:30:04'),
(268,41,'V1224',0,0,0,0,'2025-07-19 23:30:04'),
(269,41,'V120',0,0,0,0,'2025-07-19 23:30:04'),
(270,41,'V100',0,0,0,0,'2025-07-19 23:30:04'),
(271,41,'V100-24G',0,0,0,0,'2025-07-19 23:30:04'),
(272,41,'V219',0,0,0,0,'2025-07-19 23:30:04'),
(273,41,'BRIDGE_HS_UPOMNIENIE',0,0,0,0,'2025-07-19 23:30:04'),
(274,41,'V219-24S',0,0,0,0,'2025-07-19 23:30:04'),
(275,41,'V219-24G',0,0,0,0,'2025-07-19 23:30:04'),
(276,41,'V212',0,0,0,0,'2025-07-19 23:30:04'),
(277,41,'BRv212-iptv',0,0,0,0,'2025-07-19 23:30:04'),
(278,41,'V3333-PPP8',0,0,0,0,'2025-07-19 23:30:04'),
(279,41,'V3333-PPP24',0,0,0,0,'2025-07-19 23:30:04'),
(280,41,'V3333-PPP6',0,0,0,0,'2025-07-19 23:30:04'),
(281,41,'v119 prx',0,0,0,0,'2025-07-19 23:30:04'),
(282,41,'!!!!!proxy',0,0,0,0,'2025-07-19 23:30:04'),
(283,41,'vlan666',0,0,0,0,'2025-07-19 23:30:04'),
(284,41,'V661-BR3',0,0,0,0,'2025-07-19 23:30:04'),
(285,41,'bridge4',0,0,0,0,'2025-07-19 23:30:04'),
(286,41,'aaaa',0,0,0,0,'2025-07-19 23:30:04'),
(287,41,'<pppoe-Parole>',0,0,0,0,'2025-07-19 23:30:04'),
(288,41,'<pppoe-Sajur>',0,0,0,0,'2025-07-19 23:30:04'),
(289,41,'<pppoe-Janiszewska>',0,0,0,0,'2025-07-19 23:30:04'),
(290,41,'<pppoe-Szlachcinski>',0,0,0,0,'2025-07-19 23:30:04'),
(291,41,'<pppoe-00:30:4F:23:F6:F1>',0,0,0,0,'2025-07-19 23:30:04'),
(292,41,'<pppoe-Pepco>',0,0,0,0,'2025-07-19 23:30:04'),
(293,41,'<pppoe-Lohmatowa>',0,0,0,0,'2025-07-19 23:30:04'),
(294,41,'<pppoe- 00:15:6D:C2:73:07>',0,0,0,0,'2025-07-19 23:30:04'),
(295,41,'lo',0,0,0,0,'2025-07-20 02:58:50'),
(296,41,'ether1::[CRS-24S]',0,0,0,0,'2025-07-20 02:58:50'),
(297,41,'ether4::[ CRS-24G-2S + ]',0,0,0,0,'2025-07-20 02:58:50'),
(298,41,'ether5::[CRS-24S(+)]',0,0,0,0,'2025-07-20 02:58:50'),
(299,41,'ether6:::[CCR-1036-2S(+)(R1)]',0,0,0,0,'2025-07-20 02:58:50'),
(300,41,'ether7',0,0,0,0,'2025-07-20 02:58:50'),
(301,41,'bridge1',0,0,0,0,'2025-07-20 02:58:50'),
(302,41,'V222->R1',0,0,0,0,'2025-07-20 02:58:50'),
(303,41,'V861',0,0,0,0,'2025-07-20 02:58:50'),
(304,41,'V222->S6',0,0,0,0,'2025-07-20 02:58:50'),
(305,41,'BR_MGM',0,0,0,0,'2025-07-20 02:58:50'),
(306,41,'V1604',0,0,0,0,'2025-07-20 02:58:51'),
(307,41,'V222->S24',0,0,0,0,'2025-07-20 02:58:51'),
(308,41,'V222-S8',0,0,0,0,'2025-07-20 02:58:51'),
(309,41,'V120-24G',0,0,0,0,'2025-07-20 02:58:51'),
(310,41,'V111',0,0,0,0,'2025-07-20 02:58:51'),
(311,41,'ospf_lo',0,0,0,0,'2025-07-20 02:58:51'),
(312,41,'V130-24G',0,0,0,0,'2025-07-20 02:58:51'),
(313,41,'BR_V130',0,0,0,0,'2025-07-20 02:58:51'),
(314,41,'bridge2',0,0,0,0,'2025-07-20 02:58:51'),
(315,41,'v1011-oltsmg',0,0,0,0,'2025-07-20 02:58:51'),
(316,41,'BR_KRO4_MIC25',0,0,0,0,'2025-07-20 02:58:51'),
(317,41,'bridge3',0,0,0,0,'2025-07-20 02:58:51'),
(318,41,'V1076',0,0,0,0,'2025-07-20 02:58:51'),
(319,41,'BRV1007-CIESLI3',0,0,0,0,'2025-07-20 02:58:51'),
(320,41,'V1305',0,0,0,0,'2025-07-20 02:58:51'),
(321,41,'V1303',0,0,0,0,'2025-07-20 02:58:51'),
(322,41,'BRV1305',0,0,0,0,'2025-07-20 02:58:51'),
(323,41,'BRV1303-CIESLI3',0,0,0,0,'2025-07-20 02:58:51'),
(324,41,'V1301',0,0,0,0,'2025-07-20 02:58:51'),
(325,41,'V1302',0,0,0,0,'2025-07-20 02:58:51'),
(326,41,'BRV1301-CIESLI3',0,0,0,0,'2025-07-20 02:58:51'),
(327,41,'BRV1302-CIESLI3',0,0,0,0,'2025-07-20 02:58:51'),
(328,41,'BR_V120',0,0,0,0,'2025-07-20 02:58:51'),
(329,41,'BRV224-KROLA4:MIC25',0,0,0,0,'2025-07-20 02:58:51'),
(330,41,'ether8[PROXY]',0,0,0,0,'2025-07-20 02:58:51'),
(331,41,'V1204',0,0,0,0,'2025-07-20 02:58:51'),
(332,41,'V2416(10.24.16.0)',0,0,0,0,'2025-07-20 02:58:51'),
(333,41,'V2412(10.24.12.0)',0,0,0,0,'2025-07-20 02:58:51'),
(334,41,'V2420(10.24.20.0)',0,0,0,0,'2025-07-20 02:58:51'),
(335,41,'br1204',0,0,0,0,'2025-07-20 02:58:51'),
(336,41,'br1212',0,0,0,0,'2025-07-20 02:58:51'),
(337,41,'br1220',0,0,0,0,'2025-07-20 02:58:51'),
(338,41,'br1216',0,0,0,0,'2025-07-20 02:58:51'),
(339,41,'BR_V130_2-SFP',0,0,0,0,'2025-07-20 02:58:51'),
(340,41,'V1209',0,0,0,0,'2025-07-20 02:58:51'),
(341,41,'br1209',0,0,0,0,'2025-07-20 02:58:51'),
(342,41,'br1201',0,0,0,0,'2025-07-20 02:58:51'),
(343,41,'V2421(10.0.3.0)',0,0,0,0,'2025-07-20 02:58:51'),
(344,41,'br1221',0,0,0,0,'2025-07-20 02:58:51'),
(345,41,'V1205',0,0,0,0,'2025-07-20 02:58:51'),
(346,41,'V1601',0,0,0,0,'2025-07-20 02:58:51'),
(347,41,'V1612',0,0,0,0,'2025-07-20 02:58:51'),
(348,41,'V1613',0,0,0,0,'2025-07-20 02:58:51'),
(349,41,'V1611',0,0,0,0,'2025-07-20 02:58:51'),
(350,41,'V1304',0,0,0,0,'2025-07-20 02:58:51'),
(351,41,'V1614',0,0,0,0,'2025-07-20 02:58:51'),
(352,41,'V1615',0,0,0,0,'2025-07-20 02:58:51'),
(353,41,'V1202',0,0,0,0,'2025-07-20 02:58:51'),
(354,41,'V1203',0,0,0,0,'2025-07-20 02:58:51'),
(355,41,'V1206',0,0,0,0,'2025-07-20 02:58:51'),
(356,41,'V1207',0,0,0,0,'2025-07-20 02:58:51'),
(357,41,'V1210',0,0,0,0,'2025-07-20 02:58:51'),
(358,41,'V1211',0,0,0,0,'2025-07-20 02:58:51'),
(359,41,'V1213',0,0,0,0,'2025-07-20 02:58:51'),
(360,41,'V1214',0,0,0,0,'2025-07-20 02:58:51'),
(361,41,'V1217',0,0,0,0,'2025-07-20 02:58:51'),
(362,41,'V1218',0,0,0,0,'2025-07-20 02:58:51'),
(363,41,'V1219',0,0,0,0,'2025-07-20 02:58:51'),
(364,41,'V1222',0,0,0,0,'2025-07-20 02:58:51'),
(365,41,'V1223',0,0,0,0,'2025-07-20 02:58:51'),
(366,41,'V1224',0,0,0,0,'2025-07-20 02:58:51'),
(367,41,'V120',0,0,0,0,'2025-07-20 02:58:51'),
(368,41,'V100',0,0,0,0,'2025-07-20 02:58:51'),
(369,41,'V100-24G',0,0,0,0,'2025-07-20 02:58:51'),
(370,41,'V219',0,0,0,0,'2025-07-20 02:58:51'),
(371,41,'BRIDGE_HS_UPOMNIENIE',0,0,0,0,'2025-07-20 02:58:51'),
(372,41,'V219-24S',0,0,0,0,'2025-07-20 02:58:51'),
(373,41,'V219-24G',0,0,0,0,'2025-07-20 02:58:51'),
(374,41,'V212',0,0,0,0,'2025-07-20 02:58:51'),
(375,41,'BRv212-iptv',0,0,0,0,'2025-07-20 02:58:51'),
(376,41,'V3333-PPP8',0,0,0,0,'2025-07-20 02:58:51'),
(377,41,'V3333-PPP24',0,0,0,0,'2025-07-20 02:58:51'),
(378,41,'V3333-PPP6',0,0,0,0,'2025-07-20 02:58:51'),
(379,41,'v119 prx',0,0,0,0,'2025-07-20 02:58:51'),
(380,41,'!!!!!proxy',0,0,0,0,'2025-07-20 02:58:51'),
(381,41,'vlan666',0,0,0,0,'2025-07-20 02:58:51'),
(382,41,'V661-BR3',0,0,0,0,'2025-07-20 02:58:51'),
(383,41,'bridge4',0,0,0,0,'2025-07-20 02:58:51'),
(384,41,'aaaa',0,0,0,0,'2025-07-20 02:58:51'),
(385,41,'<pppoe-Parole>',0,0,0,0,'2025-07-20 02:58:51'),
(386,41,'<pppoe-Sajur>',0,0,0,0,'2025-07-20 02:58:51'),
(387,41,'<pppoe-Janiszewska>',0,0,0,0,'2025-07-20 02:58:51'),
(388,41,'<pppoe-Szlachcinski>',0,0,0,0,'2025-07-20 02:58:51'),
(389,41,'<pppoe-00:30:4F:23:F6:F1>',0,0,0,0,'2025-07-20 02:58:51'),
(390,41,'<pppoe-Pepco>',0,0,0,0,'2025-07-20 02:58:51'),
(391,41,'<pppoe-Lohmatowa>',0,0,0,0,'2025-07-20 02:58:51'),
(392,41,'<pppoe- 00:15:6D:C2:73:07>',0,0,0,0,'2025-07-20 02:58:51');
/*!40000 ALTER TABLE `interface_stats` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `internet_packages`
--

DROP TABLE IF EXISTS `internet_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `internet_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `internet_package` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internet_packages`
--

LOCK TABLES `internet_packages` WRITE;
/*!40000 ALTER TABLE `internet_packages` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `internet_packages` VALUES
(1,'Internet Podstawowy','100/10 Mbps',39.99,'2025-07-18 23:51:01'),
(2,'Internet Standard','200/20 Mbps',59.99,'2025-07-18 23:51:01'),
(3,'Internet Premium','500/50 Mbps',89.99,'2025-07-18 23:51:01'),
(4,'Internet Ultra','1000/100 Mbps',129.99,'2025-07-18 23:51:01'),
(5,'Internet Podstawowy','100/10 Mbps',39.99,'2025-07-18 23:52:07'),
(6,'Internet Standard','200/20 Mbps',59.99,'2025-07-18 23:52:07'),
(7,'Internet Premium','500/50 Mbps',89.99,'2025-07-18 23:52:07'),
(8,'Internet Ultra','1000/100 Mbps',129.99,'2025-07-18 23:52:07'),
(9,'Internet Podstawowy','100/10 Mbps',39.99,'2025-07-19 00:09:42'),
(10,'Internet Standard','200/20 Mbps',59.99,'2025-07-19 00:09:42'),
(11,'Internet Premium','500/50 Mbps',89.99,'2025-07-19 00:09:42'),
(12,'Internet Ultra','1000/100 Mbps',129.99,'2025-07-19 00:09:42'),
(13,'Internet Podstawowy','100/10 Mbps',39.99,'2025-07-19 00:12:52'),
(14,'Internet Standard','200/20 Mbps',59.99,'2025-07-19 00:12:52'),
(15,'Internet Premium','500/50 Mbps',89.99,'2025-07-19 00:12:52'),
(16,'Internet Ultra','1000/100 Mbps',129.99,'2025-07-19 00:12:52'),
(17,'Internet Podstawowy','100/10 Mbps',39.99,'2025-07-19 13:14:15'),
(18,'Internet Standard','200/20 Mbps',59.99,'2025-07-19 13:14:15'),
(19,'Internet Premium','500/50 Mbps',89.99,'2025-07-19 13:14:15'),
(20,'Internet Ultra','1000/100 Mbps',129.99,'2025-07-19 13:14:15');
/*!40000 ALTER TABLE `internet_packages` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `invoice_items`
--

DROP TABLE IF EXISTS `invoice_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_items`
--

LOCK TABLES `invoice_items` WRITE;
/*!40000 ALTER TABLE `invoice_items` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `invoice_items` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` varchar(50) NOT NULL DEFAULT '',
  `client_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `invoice_number` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoices`
--

LOCK TABLES `invoices` WRITE;
/*!40000 ALTER TABLE `invoices` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `invoices` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `layout_settings`
--

DROP TABLE IF EXISTS `layout_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `layout_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','boolean','integer','json') DEFAULT 'string',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `layout_settings`
--

LOCK TABLES `layout_settings` WRITE;
/*!40000 ALTER TABLE `layout_settings` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `layout_settings` VALUES
(1,'menu_position','left','string','2025-07-20 01:44:29','2025-07-20 01:44:29'),
(2,'show_logo','1','boolean','2025-07-20 01:44:29','2025-07-20 01:44:29'),
(3,'primary_color','#007bff','string','2025-07-20 01:44:29','2025-07-20 01:44:29'),
(4,'secondary_color','#6c757d','string','2025-07-20 01:44:29','2025-07-20 01:44:29'),
(5,'font_family','Segoe UI, Tahoma, Geneva, Verdana, sans-serif','string','2025-07-20 01:44:29','2025-07-20 01:44:29'),
(6,'font_size','14px','string','2025-07-20 01:44:29','2025-07-20 01:44:29'),
(7,'custom_css','','string','2025-07-20 01:44:29','2025-07-20 01:44:29'),
(8,'footer_text','© 2024 sLMS System. Wszystkie prawa zastrzeżone.','string','2025-07-20 01:44:29','2025-07-20 01:44:29'),
(9,'auto_refresh_interval','300000','integer','2025-07-20 01:44:29','2025-07-20 01:44:29'),
(10,'enable_keyboard_shortcuts','1','boolean','2025-07-20 01:44:29','2025-07-20 01:44:29');
/*!40000 ALTER TABLE `layout_settings` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `menu_config`
--

DROP TABLE IF EXISTS `menu_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`config`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_config`
--

LOCK TABLES `menu_config` WRITE;
/*!40000 ALTER TABLE `menu_config` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `menu_config` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(100) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'bi-circle',
  `type` enum('link','script') DEFAULT 'link',
  `script` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `enabled` tinyint(1) DEFAULT 1,
  `options` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_menu_position` (`position`),
  KEY `idx_menu_enabled` (`enabled`),
  KEY `idx_menu_parent` (`parent_id`),
  CONSTRAINT `menu_items_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `menu_items` VALUES
(1,'Panel główny','index.php','bi-house','link',NULL,NULL,1,1,NULL),
(2,'Klienci','modules/clients.php','bi-people','link',NULL,NULL,2,1,NULL),
(3,'Urządzenia','modules/devices.php','bi-pc-display','link',NULL,NULL,3,1,NULL),
(4,'Urządzenia szkieletowe','modules/skeleton_devices.php','bi-hdd-network','link',NULL,NULL,4,1,NULL),
(5,'Sieci','modules/networks.php','bi-diagram-3','link',NULL,NULL,5,1,NULL),
(6,'Usługi','modules/services.php','bi-gear','link',NULL,NULL,6,1,NULL),
(7,'Taryfy','modules/tariffs.php','bi-currency-dollar','link',NULL,NULL,7,1,NULL),
(8,'Telewizja','modules/tv_packages.php','bi-tv','link',NULL,NULL,8,1,NULL),
(9,'Internet','modules/internet_packages.php','bi-wifi','link',NULL,NULL,9,1,NULL),
(10,'Faktury','modules/invoices.php','bi-receipt','link',NULL,NULL,10,1,NULL),
(11,'Płatności','modules/payments.php','bi-credit-card','link',NULL,NULL,11,1,NULL),
(12,'Użytkownicy','modules/users.php','bi-person-badge','link',NULL,NULL,12,1,NULL),
(13,'Podręcznik','modules/manual.php','bi-book','link',NULL,NULL,13,1,NULL),
(14,'Administracja','admin_menu.php','bi-tools','link',NULL,NULL,99,1,NULL);
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `network_alerts`
--

DROP TABLE IF EXISTS `network_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `network_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `interface_name` varchar(64) NOT NULL,
  `alert_type` varchar(32) NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`,`interface_name`,`timestamp`),
  KEY `alert_type` (`alert_type`,`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `network_alerts`
--

LOCK TABLES `network_alerts` WRITE;
/*!40000 ALTER TABLE `network_alerts` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `network_alerts` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `networks`
--

DROP TABLE IF EXISTS `networks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `networks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `subnet` varchar(50) NOT NULL,
  `network_address` varchar(18) NOT NULL,
  `gateway` varchar(15) DEFAULT NULL,
  `dns_servers` text DEFAULT NULL,
  `dhcp_range_start` varchar(15) DEFAULT NULL,
  `dhcp_range_end` varchar(15) DEFAULT NULL,
  `vlan_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `device_interface` varchar(100) DEFAULT NULL,
  `device_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `networks`
--

LOCK TABLES `networks` WRITE;
/*!40000 ALTER TABLE `networks` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `networks` VALUES
(1,'Sieć główna','','192.168.1.0/24','192.168.1.1','8.8.8.8,8.8.4.4','192.168.1.100','192.168.1.200',NULL,'Główna sieć biurowa',NULL,NULL,'2025-07-20 01:44:29','2025-07-25 00:15:28'),
(2,'Sieć gości','','192.168.2.0/24','192.168.2.1','8.8.8.8,8.8.4.4','192.168.2.100','192.168.2.200',NULL,'Sieć dla gości',NULL,NULL,'2025-07-20 01:44:29','2025-07-25 00:15:28'),
(3,'Sieć IoT','','192.168.3.0/24','192.168.3.1','8.8.8.8,8.8.4.4','192.168.3.100','192.168.3.200',NULL,'Sieć dla urządzeń IoT',NULL,NULL,'2025-07-20 01:44:29','2025-07-25 00:15:28'),
(4,'Sieć zarządzania','','10.0.0.0/24','10.0.0.1','8.8.8.8,8.8.4.4','10.0.0.100','10.0.0.200',NULL,'Sieć zarządzania urządzeniami',NULL,NULL,'2025-07-20 01:44:29','2025-07-25 00:15:28'),
(5,'DHCP Network 10.0.0.0/24','10.0.0.0/24','10.0.0.0','10.0.0.1',NULL,NULL,NULL,NULL,'Interface: BR_V120, Gateway: 10.0.0.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BR_V120',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(6,'DHCP Network 10.0.3.0/24','10.0.3.0/24','10.0.3.0','10.0.3.1',NULL,NULL,NULL,NULL,'Interface: br1221, Gateway: 10.0.3.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','br1221',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(7,'DHCP Network 10.0.7.0/24','10.0.7.0/24','10.0.7.0','10.0.7.1',NULL,NULL,NULL,NULL,'Interface: BRV1305, Gateway: 10.0.7.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BRV1305',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(8,'DHCP Network 10.0.17.0/24','10.0.17.0/24','10.0.17.0','10.0.17.1',NULL,NULL,NULL,NULL,'Interface: BRV1305, Gateway: 10.0.17.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BRV1305',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(9,'DHCP Network 10.0.18.0/24','10.0.18.0/24','10.0.18.0','10.0.18.1',NULL,NULL,NULL,NULL,'Interface: BRV1305, Gateway: 10.0.18.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BRV1305',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(10,'DHCP Network 10.0.19.0/24','10.0.19.0/24','10.0.19.0','10.0.19.1',NULL,NULL,NULL,NULL,'Interface: BRV1305, Gateway: 10.0.19.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BRV1305',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(11,'DHCP Network 10.0.20.0/24','10.0.20.0/24','10.0.20.0','10.0.20.1',NULL,NULL,NULL,NULL,'Interface: BRV1305, Gateway: 10.0.20.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BRV1305',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(12,'DHCP Network 10.0.31.0/24','10.0.31.0/24','10.0.31.0','10.0.31.1',NULL,NULL,NULL,NULL,'Interface: BRV1305, Gateway: 10.0.31.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BRV1305',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(13,'DHCP Network 10.0.32.0/24','10.0.32.0/24','10.0.32.0','10.0.32.1',NULL,NULL,NULL,NULL,'Interface: BRV1305, Gateway: 10.0.32.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BRV1305',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(14,'DHCP Network 10.0.76.0/24','10.0.76.0/24','10.0.76.0','10.0.76.1',NULL,NULL,NULL,NULL,'Interface: V1076, Gateway: 10.0.76.1, DNS1: 194.104.159.1, DNS2: N/A, Domain: N/A','V1076',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(15,'DHCP Network 10.1.0.0/16','10.1.0.0/16','10.1.0.0','10.1.0.1',NULL,NULL,NULL,NULL,'Interface: BRV1305, Gateway: 10.1.0.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BRV1305',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(16,'DHCP Network 10.5.50.0/24','10.5.50.0/24','10.5.50.0','10.5.50.1',NULL,NULL,NULL,NULL,'Interface: bridge3, Gateway: 10.5.50.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','bridge3',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(17,'DHCP Network 10.24.1.0/24','10.24.1.0/24','10.24.1.0','10.24.1.1',NULL,NULL,NULL,NULL,'Interface: V1212, Gateway: 10.24.1.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','V1212',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(18,'DHCP Network 10.24.4.0/24','10.24.4.0/24','10.24.4.0','10.24.4.1',NULL,NULL,NULL,NULL,'Interface: br1204, Gateway: 10.24.4.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','br1204',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(19,'DHCP Network 10.24.9.0/24','10.24.9.0/24','10.24.9.0','10.24.9.1',NULL,NULL,NULL,NULL,'Interface: br1209, Gateway: 10.24.9.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','br1209',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(20,'DHCP Network 10.24.12.0/24','10.24.12.0/24','10.24.12.0','10.24.12.1',NULL,NULL,NULL,NULL,'Interface: br1212, Gateway: 10.24.12.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','br1212',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(21,'DHCP Network 10.24.16.0/24','10.24.16.0/24','10.24.16.0','10.24.16.1',NULL,NULL,NULL,NULL,'Interface: br1216, Gateway: 10.24.16.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','br1216',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(22,'DHCP Network 10.24.20.0/24','10.24.20.0/24','10.24.20.0','10.24.20.1',NULL,NULL,NULL,NULL,'Interface: br1220, Gateway: 10.24.20.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','br1220',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(23,'DHCP Network 10.24.21.0/24','10.24.21.0/24','10.24.21.0','10.24.21.1',NULL,NULL,NULL,NULL,'Interface: br1221, Gateway: 10.24.21.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','br1221',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(24,'DHCP Network 10.213.211.0/24','10.213.211.0/24','10.213.211.0','10.213.211.1',NULL,NULL,NULL,NULL,'Interface: BR_V130_2-SFP, Gateway: 10.213.211.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BR_V130_2-SFP',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(25,'DHCP Network 10.214.211.0/24','10.214.211.0/24','10.214.211.0','10.214.211.1',NULL,NULL,NULL,NULL,'Interface: BRV1305, Gateway: 10.214.211.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BRV1305',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(26,'DHCP Network 172.16.11.0/24','172.16.11.0/24','172.16.11.0','172.16.11.1',NULL,NULL,NULL,NULL,'Interface: , Gateway: 172.16.11.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(27,'DHCP Network 192.168.7.0/24','192.168.7.0/24','192.168.7.0','192.168.7.1',NULL,NULL,NULL,NULL,'Interface: BRV1305, Gateway: 192.168.7.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BRV1305',41,'2025-07-25 04:47:14','2025-07-25 04:47:14'),
(28,'DHCP Network 192.168.8.0/24','192.168.8.0/24','192.168.8.0','192.168.8.1',NULL,NULL,NULL,NULL,'Interface: BRV1305, Gateway: 192.168.8.1, DNS1: 194.204.159.1, DNS2: N/A, Domain: N/A','BRV1305',41,'2025-07-25 04:47:14','2025-07-25 04:47:14');
/*!40000 ALTER TABLE `networks` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `order_date` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `supplier_id` (`supplier_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `orders` VALUES
(1,'ORD001',1,1,5,'2025-07-16 05:59:55'),
(2,'ORD002',2,1,10,'2025-07-16 05:59:55'),
(3,'ORD003',3,2,50,'2025-07-16 05:59:55'),
(4,'ORD004',4,3,25,'2025-07-16 05:59:55'),
(5,'ORD001',1,1,5,'2025-07-16 06:05:14'),
(6,'ORD002',2,1,10,'2025-07-16 06:05:14'),
(7,'ORD003',3,2,50,'2025-07-16 06:05:14'),
(8,'ORD004',4,3,25,'2025-07-16 06:05:14');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','transfer','card','other') NOT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `products` VALUES
(1,'Laptop',999.99,1,'2025-07-16 05:59:55'),
(2,'Smartphone',599.99,1,'2025-07-16 05:59:55'),
(3,'T-Shirt',29.99,2,'2025-07-16 05:59:55'),
(4,'Programming Book',49.99,3,'2025-07-16 05:59:55'),
(5,'Laptop',999.99,1,'2025-07-16 06:05:14'),
(6,'Smartphone',599.99,1,'2025-07-16 06:05:14'),
(7,'T-Shirt',29.99,2,'2025-07-16 06:05:14'),
(8,'Programming Book',49.99,3,'2025-07-16 06:05:14');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `client_id` int(11) NOT NULL,
  `service_type` enum('internet','tv') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `services_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `skeleton_devices`
--

DROP TABLE IF EXISTS `skeleton_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `skeleton_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `mac_address` varchar(17) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','maintenance') DEFAULT 'active',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `api_username` varchar(100) DEFAULT NULL,
  `api_password` varchar(255) DEFAULT NULL,
  `api_port` int(11) DEFAULT 8728,
  `api_ssl` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`),
  UNIQUE KEY `mac_address` (`mac_address`)
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `skeleton_devices`
--

LOCK TABLES `skeleton_devices` WRITE;
/*!40000 ALTER TABLE `skeleton_devices` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `skeleton_devices` VALUES
(41,'x86','router','10.0.222.86',NULL,'Maciejowskiego 4','x86','MikroTik','active','','2025-07-18 22:56:35','2025-07-18 23:22:32','sarna','Loveganja151!',8728,0),
(113,'Router Główny','router','192.168.1.1','00:11:22:33:44:01','Serwerownia','RB4011','MikroTik','active','Główny router szkieletowy','2025-07-19 13:11:39','2025-07-19 13:11:39',NULL,NULL,8728,0),
(114,'Switch Dystrybucyjny 1','switch','192.168.1.2','00:11:22:33:44:02','Piętro 1','CRS326','MikroTik','active','Przełącznik dystrybucyjny piętro 1','2025-07-19 13:11:39','2025-07-19 13:11:39',NULL,NULL,8728,0),
(115,'Switch Dystrybucyjny 2','switch','192.168.1.3','00:11:22:33:44:03','Piętro 2','CRS326','MikroTik','active','Przełącznik dystrybucyjny piętro 2','2025-07-19 13:11:39','2025-07-19 13:11:39',NULL,NULL,8728,0),
(116,'Switch Dostępowy 1','switch','192.168.1.4','00:11:22:33:44:04','Sala 101','CSS326','MikroTik','active','Przełącznik dostępowy sala 101','2025-07-19 13:11:39','2025-07-19 13:11:39',NULL,NULL,8728,0),
(117,'Kontroler WiFi','controller','192.168.1.6','00:11:22:33:44:06','Pokój IT','cAP ac','MikroTik','active','Kontroler sieci bezprzewodowej','2025-07-19 13:11:39','2025-07-19 13:11:39',NULL,NULL,8728,0),
(118,'Zapora sieciowa','firewall','192.168.1.7','00:11:22:33:44:07','Pokój bezpieczeństwa','hAP ac²','MikroTik','active','Zapora sieciowa','2025-07-19 13:11:39','2025-07-19 13:11:39',NULL,NULL,8728,0),
(119,'UPS 1','ups','192.168.1.9','00:11:22:33:44:09','Serwerownia','Smart-UPS 1500','APC','active','Zasilacz UPS główny','2025-07-19 13:11:39','2025-07-19 13:11:39',NULL,NULL,8728,0),
(122,'Test Router','mikrotik','192.168.88.1',NULL,NULL,NULL,NULL,'active',NULL,'2025-07-25 03:41:23','2025-07-25 03:41:23','admin','password',443,1);
/*!40000 ALTER TABLE `skeleton_devices` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `snmp_graph_data`
--

DROP TABLE IF EXISTS `snmp_graph_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `snmp_graph_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_ip` varchar(45) NOT NULL,
  `oid` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `polled_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `device_ip` (`device_ip`,`oid`,`polled_at`)
) ENGINE=InnoDB AUTO_INCREMENT=196 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `snmp_graph_data`
--

LOCK TABLES `snmp_graph_data` WRITE;
/*!40000 ALTER TABLE `snmp_graph_data` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `snmp_graph_data` VALUES
(1,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.1.0','0','2025-07-19 17:04:14'),
(2,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.2.0','0','2025-07-19 17:04:14'),
(3,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.1.0','0','2025-07-19 17:04:14'),
(4,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.2.0','0','2025-07-19 17:04:14'),
(5,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.3.0','0','2025-07-19 17:04:14'),
(6,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.4.0','0','2025-07-19 17:04:14'),
(7,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.1','Counter32: 1211219678','2025-07-19 17:04:14'),
(8,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.1','Counter32: 1211219678','2025-07-19 17:04:14'),
(9,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.2','Counter32: 141040686','2025-07-19 17:04:14'),
(10,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.2','Counter32: 5038568','2025-07-19 17:04:14'),
(11,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.5','Counter32: 3220201098','2025-07-19 17:04:14'),
(12,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.5','Counter32: 2972459453','2025-07-19 17:04:14'),
(13,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.6','Counter32: 1787359785','2025-07-19 17:04:14'),
(14,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.6','Counter32: 2135239859','2025-07-19 17:04:14'),
(15,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.7','Counter32: 3974696085','2025-07-19 17:04:14'),
(16,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.7','Counter32: 865773641','2025-07-19 17:04:14'),
(17,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.8','Counter32: 3494141893','2025-07-19 17:04:14'),
(18,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.8','Counter32: 552611247','2025-07-19 17:04:14'),
(19,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.1','Counter32: 15199','2025-07-19 17:04:14'),
(20,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.1','Counter32: 0','2025-07-19 17:04:14'),
(21,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.2','Counter32: 0','2025-07-19 17:04:14'),
(22,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.2','Counter32: 0','2025-07-19 17:04:14'),
(23,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.1','1','2025-07-19 17:04:14'),
(24,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.2','1','2025-07-19 17:04:14'),
(25,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.1.0','0','2025-07-19 17:40:37'),
(26,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.2.0','0','2025-07-19 17:40:37'),
(27,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.1.0','0','2025-07-19 17:40:37'),
(28,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.2.0','0','2025-07-19 17:40:37'),
(29,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.3.0','0','2025-07-19 17:40:37'),
(30,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.4.0','0','2025-07-19 17:40:37'),
(31,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.1','Counter32: 1983559152','2025-07-19 17:40:37'),
(32,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.1','Counter32: 1983559152','2025-07-19 17:40:37'),
(33,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.2','Counter32: 142153655','2025-07-19 17:40:37'),
(34,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.2','Counter32: 5071126','2025-07-19 17:40:37'),
(35,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.5','Counter32: 311914057','2025-07-19 17:40:37'),
(36,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.5','Counter32: 1662478454','2025-07-19 17:40:37'),
(37,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.6','Counter32: 347452429','2025-07-19 17:40:37'),
(38,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.6','Counter32: 3877809376','2025-07-19 17:40:37'),
(39,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.7','Counter32: 1760301291','2025-07-19 17:40:37'),
(40,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.7','Counter32: 4199432245','2025-07-19 17:40:37'),
(41,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.8','Counter32: 1195175471','2025-07-19 17:40:37'),
(42,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.8','Counter32: 4170154941','2025-07-19 17:40:37'),
(43,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.1','Counter32: 15199','2025-07-19 17:40:37'),
(44,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.1','Counter32: 0','2025-07-19 17:40:37'),
(45,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.2','Counter32: 0','2025-07-19 17:40:37'),
(46,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.2','Counter32: 0','2025-07-19 17:40:37'),
(47,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.1','1','2025-07-19 17:40:37'),
(48,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.2','1','2025-07-19 17:40:37'),
(49,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.1.0','0','2025-07-19 17:57:08'),
(50,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.2.0','0','2025-07-19 17:57:08'),
(51,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.1.0','0','2025-07-19 17:57:08'),
(52,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.2.0','0','2025-07-19 17:57:08'),
(53,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.3.0','0','2025-07-19 17:57:08'),
(54,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.4.0','0','2025-07-19 17:57:08'),
(55,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.1','Counter32: 2369619309','2025-07-19 17:57:08'),
(56,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.1','Counter32: 2369619309','2025-07-19 17:57:08'),
(57,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.2','Counter32: 142639144','2025-07-19 17:57:08'),
(58,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.2','Counter32: 5085844','2025-07-19 17:57:08'),
(59,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.5','Counter32: 796376982','2025-07-19 17:57:08'),
(60,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.5','Counter32: 4193003955','2025-07-19 17:57:08'),
(61,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.6','Counter32: 2075555295','2025-07-19 17:57:08'),
(62,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.6','Counter32: 365822096','2025-07-19 17:57:08'),
(63,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.7','Counter32: 1743522769','2025-07-19 17:57:08'),
(64,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.7','Counter32: 1733841172','2025-07-19 17:57:08'),
(65,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.8','Counter32: 3677080812','2025-07-19 17:57:08'),
(66,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.8','Counter32: 4083119591','2025-07-19 17:57:08'),
(67,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.1','Counter32: 15199','2025-07-19 17:57:08'),
(68,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.1','Counter32: 0','2025-07-19 17:57:08'),
(69,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.2','Counter32: 0','2025-07-19 17:57:08'),
(70,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.2','Counter32: 0','2025-07-19 17:57:08'),
(71,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.1','1','2025-07-19 17:57:08'),
(72,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.2','1','2025-07-19 17:57:08'),
(73,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.1.0','0','2025-07-19 18:06:41'),
(74,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.2.0','0','2025-07-19 18:06:41'),
(75,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.1.0','0','2025-07-19 18:06:41'),
(76,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.2.0','0','2025-07-19 18:06:41'),
(77,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.3.0','0','2025-07-19 18:06:41'),
(78,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.4.0','0','2025-07-19 18:06:41'),
(79,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.1','Counter32: 2587514264','2025-07-19 18:06:41'),
(80,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.1','Counter32: 2587514264','2025-07-19 18:06:41'),
(81,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.2','Counter32: 142915783','2025-07-19 18:06:41'),
(82,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.2','Counter32: 5094318','2025-07-19 18:06:41'),
(83,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.5','Counter32: 1048113427','2025-07-19 18:06:41'),
(84,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.5','Counter32: 2493602558','2025-07-19 18:06:41'),
(85,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.6','Counter32: 3137336070','2025-07-19 18:06:41'),
(86,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.6','Counter32: 2200039222','2025-07-19 18:06:41'),
(87,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.7','Counter32: 1727992224','2025-07-19 18:06:41'),
(88,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.7','Counter32: 3026284477','2025-07-19 18:06:41'),
(89,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.8','Counter32: 604003342','2025-07-19 18:06:41'),
(90,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.8','Counter32: 4184603235','2025-07-19 18:06:41'),
(91,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.1','Counter32: 15199','2025-07-19 18:06:41'),
(92,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.1','Counter32: 0','2025-07-19 18:06:41'),
(93,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.2','Counter32: 0','2025-07-19 18:06:41'),
(94,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.2','Counter32: 0','2025-07-19 18:06:41'),
(95,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.1','1','2025-07-19 18:06:41'),
(96,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.2','1','2025-07-19 18:06:41'),
(97,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.1.0','0','2025-07-19 18:08:02'),
(98,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.2.0','0','2025-07-19 18:08:02'),
(99,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.1.0','0','2025-07-19 18:08:02'),
(100,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.2.0','0','2025-07-19 18:08:02'),
(101,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.3.0','0','2025-07-19 18:08:02'),
(102,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.4.0','0','2025-07-19 18:08:02'),
(103,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.1','Counter32: 2609764090','2025-07-19 18:08:02'),
(104,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.1','Counter32: 2609764090','2025-07-19 18:08:02'),
(105,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.2','Counter32: 142959824','2025-07-19 18:08:02'),
(106,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.2','Counter32: 5095656','2025-07-19 18:08:02'),
(107,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.5','Counter32: 1091573472','2025-07-19 18:08:02'),
(108,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.5','Counter32: 3599938043','2025-07-19 18:08:02'),
(109,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.6','Counter32: 3269583964','2025-07-19 18:08:02'),
(110,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.6','Counter32: 548158575','2025-07-19 18:08:02'),
(111,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.7','Counter32: 1212471397','2025-07-19 18:08:02'),
(112,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.7','Counter32: 3674590738','2025-07-19 18:08:02'),
(113,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.8','Counter32: 781755354','2025-07-19 18:08:02'),
(114,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.8','Counter32: 570434326','2025-07-19 18:08:02'),
(115,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.1','Counter32: 15199','2025-07-19 18:08:02'),
(116,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.1','Counter32: 0','2025-07-19 18:08:02'),
(117,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.2','Counter32: 0','2025-07-19 18:08:02'),
(118,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.2','Counter32: 0','2025-07-19 18:08:02'),
(119,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.1','1','2025-07-19 18:08:02'),
(120,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.2','1','2025-07-19 18:08:03'),
(121,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.1.0','INTEGER: 0','2025-07-19 18:19:08'),
(122,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.2.0','INTEGER: 0','2025-07-19 18:19:08'),
(123,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.1.0','INTEGER: 0','2025-07-19 18:19:08'),
(124,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.1.0','0','2025-07-20 00:45:30'),
(125,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.2.0','0','2025-07-20 00:45:30'),
(126,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.1.0','0','2025-07-20 00:45:30'),
(127,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.2.0','0','2025-07-20 00:45:30'),
(128,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.3.0','0','2025-07-20 00:45:30'),
(129,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.4.0','0','2025-07-20 00:45:30'),
(130,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.1','Counter32: 2201768842','2025-07-20 00:45:30'),
(131,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.1','Counter32: 2201768842','2025-07-20 00:45:30'),
(132,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.2','Counter32: 154880855','2025-07-20 00:45:30'),
(133,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.2','Counter32: 5450226','2025-07-20 00:45:30'),
(134,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.5','Counter32: 1140528030','2025-07-20 00:45:30'),
(135,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.5','Counter32: 3924535722','2025-07-20 00:45:30'),
(136,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.6','Counter32: 1011757759','2025-07-20 00:45:30'),
(137,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.6','Counter32: 3444847186','2025-07-20 00:45:30'),
(138,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.7','Counter32: 878563784','2025-07-20 00:45:30'),
(139,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.7','Counter32: 1196258413','2025-07-20 00:45:30'),
(140,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.8','Counter32: 1786343384','2025-07-20 00:45:30'),
(141,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.8','Counter32: 3420766173','2025-07-20 00:45:30'),
(142,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.1','Counter32: 15199','2025-07-20 00:45:30'),
(143,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.1','Counter32: 0','2025-07-20 00:45:30'),
(144,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.2','Counter32: 0','2025-07-20 00:45:30'),
(145,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.2','Counter32: 0','2025-07-20 00:45:30'),
(146,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.1','1','2025-07-20 00:45:30'),
(147,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.2','1','2025-07-20 00:45:30'),
(148,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.1.0','0','2025-07-20 00:59:42'),
(149,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.2.0','0','2025-07-20 00:59:42'),
(150,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.1.0','0','2025-07-20 00:59:42'),
(151,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.2.0','0','2025-07-20 00:59:42'),
(152,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.3.0','0','2025-07-20 00:59:42'),
(153,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.4.0','0','2025-07-20 00:59:42'),
(154,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.1','2276629972','2025-07-20 00:59:42'),
(155,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.1','2276629972','2025-07-20 00:59:42'),
(156,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.2','155266179','2025-07-20 00:59:42'),
(157,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.2','5462714','2025-07-20 00:59:42'),
(158,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.5','1216809160','2025-07-20 00:59:42'),
(159,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.5','2283259116','2025-07-20 00:59:42'),
(160,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.6','1200416033','2025-07-20 00:59:42'),
(161,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.6','3922828867','2025-07-20 00:59:42'),
(162,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.7','1045751353','2025-07-20 00:59:42'),
(163,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.7','3482322305','2025-07-20 00:59:42'),
(164,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.8','2205891365','2025-07-20 00:59:42'),
(165,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.8','3357819352','2025-07-20 00:59:42'),
(166,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.1','15199','2025-07-20 00:59:42'),
(167,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.1','0','2025-07-20 00:59:42'),
(168,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.2','0','2025-07-20 00:59:42'),
(169,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.2','0','2025-07-20 00:59:42'),
(170,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.1','1','2025-07-20 00:59:42'),
(171,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.2','1','2025-07-20 00:59:42'),
(172,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.1.0','0','2025-07-20 01:59:28'),
(173,'10.0.222.86','1.3.6.1.4.1.14988.1.1.7.2.0','0','2025-07-20 01:59:28'),
(174,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.1.0','0','2025-07-20 01:59:28'),
(175,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.2.0','0','2025-07-20 01:59:28'),
(176,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.3.0','0','2025-07-20 01:59:28'),
(177,'10.0.222.86','1.3.6.1.4.1.14988.1.1.3.4.0','0','2025-07-20 01:59:28'),
(178,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.1','Counter32: 2649191027','2025-07-20 01:59:28'),
(179,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.1','Counter32: 2649191027','2025-07-20 01:59:28'),
(180,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.2','Counter32: 156898247','2025-07-20 01:59:28'),
(181,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.2','Counter32: 5516234','2025-07-20 01:59:28'),
(182,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.5','Counter32: 1550274201','2025-07-20 01:59:28'),
(183,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.5','Counter32: 4030703668','2025-07-20 01:59:28'),
(184,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.6','Counter32: 2114265109','2025-07-20 01:59:28'),
(185,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.6','Counter32: 921579689','2025-07-20 01:59:28'),
(186,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.7','Counter32: 151098926','2025-07-20 01:59:28'),
(187,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.7','Counter32: 4196532506','2025-07-20 01:59:28'),
(188,'10.0.222.86','1.3.6.1.2.1.2.2.1.10.8','Counter32: 3697720589','2025-07-20 01:59:28'),
(189,'10.0.222.86','1.3.6.1.2.1.2.2.1.16.8','Counter32: 1939912809','2025-07-20 01:59:28'),
(190,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.1','Counter32: 15199','2025-07-20 01:59:28'),
(191,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.1','Counter32: 0','2025-07-20 01:59:28'),
(192,'10.0.222.86','1.3.6.1.2.1.2.2.1.13.2','Counter32: 0','2025-07-20 01:59:28'),
(193,'10.0.222.86','1.3.6.1.2.1.2.2.1.19.2','Counter32: 0','2025-07-20 01:59:28'),
(194,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.1','1','2025-07-20 01:59:28'),
(195,'10.0.222.86','1.3.6.1.2.1.2.2.1.8.2','1','2025-07-20 01:59:28');
/*!40000 ALTER TABLE `snmp_graph_data` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `streets`
--

DROP TABLE IF EXISTS `streets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `streets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `city_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `city_id` (`city_id`),
  CONSTRAINT `streets_ibfk_1` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `streets`
--

LOCK TABLES `streets` WRITE;
/*!40000 ALTER TABLE `streets` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `streets` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `suppliers` VALUES
(1,'TechCorp','tech@techcorp.com'),
(2,'FashionPlus','contact@fashionplus.com'),
(3,'BookWorld','orders@bookworld.com'),
(4,'TechCorp','tech@techcorp.com'),
(5,'FashionPlus','contact@fashionplus.com'),
(6,'BookWorld','orders@bookworld.com');
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tariffs`
--

DROP TABLE IF EXISTS `tariffs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tariffs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `upload_speed` int(11) NOT NULL,
  `download_speed` int(11) NOT NULL,
  `tv_included` tinyint(1) DEFAULT 0,
  `internet_included` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tariffs`
--

LOCK TABLES `tariffs` WRITE;
/*!40000 ALTER TABLE `tariffs` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tariffs` VALUES
(1,'Podstawowy',10,100,0,1,'2025-07-18 23:51:01'),
(2,'Standard',20,200,0,1,'2025-07-18 23:51:01'),
(3,'Premium',50,500,1,1,'2025-07-18 23:51:01'),
(4,'Ultra',100,1000,1,1,'2025-07-18 23:51:01'),
(5,'Podstawowy',10,100,0,1,'2025-07-18 23:52:07'),
(6,'Standard',20,200,0,1,'2025-07-18 23:52:07'),
(7,'Premium',50,500,1,1,'2025-07-18 23:52:07'),
(8,'Ultra',100,1000,1,1,'2025-07-18 23:52:07'),
(9,'Podstawowy',10,100,0,1,'2025-07-19 00:09:42'),
(10,'Standard',20,200,0,1,'2025-07-19 00:09:42'),
(11,'Premium',50,500,1,1,'2025-07-19 00:09:42'),
(12,'Ultra',100,1000,1,1,'2025-07-19 00:09:42'),
(13,'Podstawowy',10,100,0,1,'2025-07-19 00:12:52'),
(14,'Standard',20,200,0,1,'2025-07-19 00:12:52'),
(15,'Premium',50,500,1,1,'2025-07-19 00:12:52'),
(16,'Ultra',100,1000,1,1,'2025-07-19 00:12:52'),
(17,'Podstawowy',10,100,0,1,'2025-07-19 13:14:15'),
(18,'Standard',20,200,0,1,'2025-07-19 13:14:15'),
(19,'Premium',50,500,1,1,'2025-07-19 13:14:15'),
(20,'Ultra',100,1000,1,1,'2025-07-19 13:14:15');
/*!40000 ALTER TABLE `tariffs` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `themes`
--

DROP TABLE IF EXISTS `themes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`config`)),
  `is_active` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `themes`
--

LOCK TABLES `themes` WRITE;
/*!40000 ALTER TABLE `themes` DISABLE KEYS */;
set autocommit=0;
/*!40000 ALTER TABLE `themes` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `tv_packages`
--

DROP TABLE IF EXISTS `tv_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tv_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `tv_package` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tv_packages`
--

LOCK TABLES `tv_packages` WRITE;
/*!40000 ALTER TABLE `tv_packages` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `tv_packages` VALUES
(1,'Podstawowy TV','Kanały podstawowe',29.99,'2025-07-18 23:51:01'),
(2,'Standard TV','Kanały podstawowe + sport',49.99,'2025-07-18 23:51:01'),
(3,'Premium TV','Wszystkie kanały + HBO',79.99,'2025-07-18 23:51:01'),
(4,'Ultra TV','Wszystkie kanały + premium',99.99,'2025-07-18 23:51:01'),
(5,'Podstawowy TV','Kanały podstawowe',29.99,'2025-07-18 23:52:07'),
(6,'Standard TV','Kanały podstawowe + sport',49.99,'2025-07-18 23:52:07'),
(7,'Premium TV','Wszystkie kanały + HBO',79.99,'2025-07-18 23:52:07'),
(8,'Ultra TV','Wszystkie kanały + premium',99.99,'2025-07-18 23:52:07'),
(9,'Podstawowy TV','Kanały podstawowe',29.99,'2025-07-19 00:09:42'),
(10,'Standard TV','Kanały podstawowe + sport',49.99,'2025-07-19 00:09:42'),
(11,'Premium TV','Wszystkie kanały + HBO',79.99,'2025-07-19 00:09:42'),
(12,'Ultra TV','Wszystkie kanały + premium',99.99,'2025-07-19 00:09:42'),
(13,'Podstawowy TV','Kanały podstawowe',29.99,'2025-07-19 00:12:52'),
(14,'Standard TV','Kanały podstawowe + sport',49.99,'2025-07-19 00:12:52'),
(15,'Premium TV','Wszystkie kanały + HBO',79.99,'2025-07-19 00:12:52'),
(16,'Ultra TV','Wszystkie kanały + premium',99.99,'2025-07-19 00:12:52'),
(17,'Podstawowy TV','Kanały podstawowe',29.99,'2025-07-19 13:14:15'),
(18,'Standard TV','Kanały podstawowe + sport',49.99,'2025-07-19 13:14:15'),
(19,'Premium TV','Wszystkie kanały + HBO',79.99,'2025-07-19 13:14:15'),
(20,'Ultra TV','Wszystkie kanały + premium',99.99,'2025-07-19 13:14:15');
/*!40000 ALTER TABLE `tv_packages` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_activity_log`
--

DROP TABLE IF EXISTS `user_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_activity_log`
--

LOCK TABLES `user_activity_log` WRITE;
/*!40000 ALTER TABLE `user_activity_log` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `user_activity_log` VALUES
(1,1,'login','Successful login','10.0.222.223','2025-07-19 23:33:32'),
(2,1,'login','Successful login','::1','2025-07-20 00:23:47'),
(3,1,'login','Successful login','10.0.222.223','2025-07-20 00:41:30'),
(4,1,'user_updated','Updated user: admin (ID: 1)','10.0.222.223','2025-07-20 00:55:33'),
(5,1,'logout','User logged out','10.0.222.223','2025-07-20 02:37:10'),
(6,1,'login','Successful login','10.0.222.223','2025-07-20 02:46:17'),
(7,1,'login','Successful login','10.0.222.223','2025-07-20 03:24:17'),
(8,1,'login','Successful login','127.0.0.1','2025-07-20 03:45:53'),
(9,1,'login','Successful login','10.0.222.223','2025-07-20 04:09:53'),
(10,1,'login','Successful login','10.0.222.223','2025-07-20 04:10:01'),
(11,1,'login','Successful login','10.0.222.223','2025-07-20 04:10:36'),
(12,1,'login','Successful login','10.0.222.223','2025-07-20 04:10:48'),
(13,1,'login','Successful login','10.0.222.223','2025-07-20 04:13:16'),
(14,1,'login','Successful login','10.0.222.223','2025-07-20 04:14:10'),
(15,1,'login','Successful login','10.0.222.223','2025-07-20 04:14:32'),
(16,3,'login','Successful login','10.0.222.223','2025-07-20 04:15:03'),
(17,3,'login','Successful login','10.0.222.223','2025-07-20 04:15:19'),
(18,1,'auto_login','Automatic login - full access mode','10.0.222.223','2025-07-20 05:18:10'),
(19,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-24 23:39:33'),
(20,1,'logout','User logged out','127.0.0.1','2025-07-24 23:45:56'),
(21,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-24 23:45:56'),
(22,1,'logout','User logged out','127.0.0.1','2025-07-25 00:07:27'),
(23,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 00:07:27'),
(24,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(25,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(26,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(27,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(28,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(29,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(30,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(31,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(32,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(33,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(34,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(35,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(36,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(37,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(38,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(39,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(40,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(41,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(42,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(43,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(44,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(45,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(46,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(47,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(48,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(49,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:46:26'),
(50,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:47:12'),
(51,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 01:53:21'),
(52,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 02:08:56'),
(53,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 03:32:57'),
(54,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 04:39:25'),
(55,1,'auto_login','Automatic login - full access mode','127.0.0.1','2025-07-25 15:07:01');
/*!40000 ALTER TABLE `user_activity_log` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `user_permissions`
--

DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `module` varchar(50) NOT NULL,
  `permission` enum('read','write','admin') DEFAULT 'read',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_module` (`user_id`,`module`),
  CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_permissions`
--

LOCK TABLES `user_permissions` WRITE;
/*!40000 ALTER TABLE `user_permissions` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `user_permissions` VALUES
(1,10,'dashboard','read','2025-07-19 23:31:40'),
(2,10,'clients','read','2025-07-19 23:31:40'),
(3,10,'devices','read','2025-07-19 23:31:40'),
(4,10,'networks','read','2025-07-19 23:31:40'),
(5,10,'services','read','2025-07-19 23:31:40');
/*!40000 ALTER TABLE `user_permissions` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT 'user',
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `access_level_id` int(11) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_username` (`username`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_active` (`is_active`),
  KEY `access_level_id` (`access_level_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`access_level_id`) REFERENCES `access_levels` (`id`),
  CONSTRAINT `users_ibfk_2` FOREIGN KEY (`access_level_id`) REFERENCES `access_levels` (`id`),
  CONSTRAINT `users_ibfk_3` FOREIGN KEY (`access_level_id`) REFERENCES `access_levels` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `users` VALUES
(1,'admin','','','$2y$12$JsdfcAO7G6xF16nCHBznQ.1mDy5EnF56wNR0uS0IOtYWh3YMoNFXy','admin@slms.local','admin','Administrator','Systemu',1,'2025-07-20 01:44:29','2025-07-25 00:04:21',1,NULL),
(2,'user','','','$2y$12$mIno8bUFn9jeBnR0nsdPQ.jUQk4VY5dax93KwwAmuzTi2UyrvTFIC','user@slms.local','user','Użytkownik','Testowy',1,'2025-07-20 01:44:29','2025-07-20 04:13:13',NULL,NULL),
(3,'manager','','','$2y$12$oxuAalXnwfBrrzreGZsAgOd8HbHbZzlZMqpFZISZ.OjAEfuHpUe9S','manager@slms.local','manager','Kierownik','Działu',1,'2025-07-20 01:44:29','2025-07-20 04:13:12',NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
commit;

--
-- Table structure for table `vlans`
--

DROP TABLE IF EXISTS `vlans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vlans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vlan_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `network_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `vlan_id` (`vlan_id`),
  KEY `network_id` (`network_id`),
  CONSTRAINT `vlans_ibfk_1` FOREIGN KEY (`network_id`) REFERENCES `networks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vlans`
--

LOCK TABLES `vlans` WRITE;
/*!40000 ALTER TABLE `vlans` DISABLE KEYS */;
set autocommit=0;
INSERT INTO `vlans` VALUES
(1,10,'VLAN Główna','Główna sieć VLAN',NULL,'2025-07-20 01:44:29'),
(2,20,'VLAN Goście','Sieć VLAN dla gości',NULL,'2025-07-20 01:44:29'),
(3,30,'VLAN IoT','Sieć VLAN dla urządzeń IoT',NULL,'2025-07-20 01:44:29'),
(4,40,'VLAN Zarządzanie','Sieć VLAN zarządzania',NULL,'2025-07-20 01:44:29');
/*!40000 ALTER TABLE `vlans` ENABLE KEYS */;
UNLOCK TABLES;
commit;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-07-25 18:20:33
