-- Ekspor struktur nilaimhsw + 9 data asli sesuai snapshot database lokal.
-- NIM, nama, nilai, dan path foto asli dipertahankan. Untuk instalasi baru.
CREATE DATABASE IF NOT EXISTS mahasiswa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mahasiswa;

-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: mahasiswa
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `nilaimhsw`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nilaimhsw` (
  `nim` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `namalengkap` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nilaiakhir` decimal(5,2) NOT NULL,
  `nilaihuruf` char(1) COLLATE utf8mb4_unicode_ci NOT NULL,
  `predikat` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`nim`),
  KEY `idx_nilaiakhir` (`nilaiakhir`,`nim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nilaimhsw`
--

LOCK TABLES `nilaimhsw` WRITE;
/*!40000 ALTER TABLE `nilaimhsw` DISABLE KEYS */;
INSERT INTO `nilaimhsw` (`nim`, `namalengkap`, `file_path`, `nilaiakhir`, `nilaihuruf`, `predikat`, `created_date`, `modified_date`) VALUES ('3202316001','Ferry Faisal',NULL,70.00,'B','Memuaskan','2026-10-13 03:59:19','2026-10-15 07:04:25'),('3202316002','Ferry',NULL,78.50,'B','Memuaskan','2026-10-13 03:59:19','2026-10-13 04:23:35'),('3202316003','Habil',NULL,60.75,'B','Memuaskan','2026-10-13 03:59:19','2026-10-13 04:23:38'),('3202316004','Ikna','uploads/mahasiswa/4a166a659c8846e43e83995242a2ed6a.jpg',87.90,'A','Sangat Memuaskan','2026-10-13 03:59:19','2026-10-21 15:32:09'),('3202316005','Aqil',NULL,77.75,'B','Memuaskan','2026-10-13 03:59:19','2026-10-13 04:13:33'),('3202316006','Freky',NULL,79.82,'B','Memuaskan','2026-10-13 03:59:19','2026-10-13 03:59:19'),('3202316018','Fadil Rahman','uploads/mahasiswa/a4640af2d3d1baba3d13ee5925c5dd5c.jpg',88.00,'A','Sangat Memuaskan','2026-10-13 04:23:57','2026-10-21 15:31:52'),('3202316024','pudidil','uploads/mahasiswa/0e59826562d3796d8e2d52326696b481.png',99.00,'A','Sangat Memuaskan','2026-10-13 06:28:45','2026-10-21 15:31:17'),('3202316043','ryan',NULL,78.00,'B','Memuaskan','2026-10-13 07:11:19','2026-10-13 07:11:19');
/*!40000 ALTER TABLE `nilaimhsw` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-26 22:19:39
