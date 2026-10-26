-- Ekspor database demo UTS: struktur nilaimhsw + 16 mahasiswa dengan avatar.
-- Untuk instalasi baru. Untuk database lama gunakan setup.sql, lalu seed.sql.
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
INSERT INTO `nilaimhsw` (`nim`, `namalengkap`, `file_path`, `nilaiakhir`, `nilaihuruf`, `predikat`, `created_date`, `modified_date`) VALUES ('9999000001','Andi Saputra','assets/img/avatar.png',87.90,'A','Sangat Memuaskan','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000002','Budi Santoso','assets/img/avatar2.png',72.50,'B','Memuaskan','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000003','Citra Lestari','assets/img/avatar3.png',95.00,'A','Sangat Memuaskan','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000004','Dewi Anggraini','assets/img/avatar4.png',58.00,'C','Cukup','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000005','Eko Prasetyo','assets/img/avatar5.png',43.75,'D','Kurang','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000006','Fitri Handayani','assets/img/avatar.png',80.50,'B','Memuaskan','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000007','Gilang Ramadhan','assets/img/avatar2.png',34.50,'E','Tidak Lulus','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000008','Hana Putri','assets/img/avatar3.png',90.25,'A','Sangat Memuaskan','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000009','Indra Wijaya','assets/img/avatar4.png',66.00,'B','Memuaskan','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000010','Joko Susilo','assets/img/avatar5.png',50.50,'D','Kurang','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000011','Kartika Sari','assets/img/avatar.png',76.25,'B','Memuaskan','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000012','Lina Marlina','assets/img/avatar2.png',60.50,'C','Cukup','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000013','Maya Permata','assets/img/avatar3.png',83.00,'A','Sangat Memuaskan','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000014','Nanda Fajar','assets/img/avatar4.png',29.00,'E','Tidak Lulus','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000015','Oki Setiawan','assets/img/avatar5.png',72.50,'B','Memuaskan','2026-10-26 15:06:23','2026-10-26 15:06:23'),('9999000016','Putri Amelia','assets/img/avatar.png',99.00,'A','Sangat Memuaskan','2026-10-26 15:06:23','2026-10-26 15:06:23');
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

-- Dump completed on 2026-10-26 22:06:23
