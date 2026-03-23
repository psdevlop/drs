/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.2.2-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: drs
-- ------------------------------------------------------
-- Server version	12.2.2-MariaDB-ubu2404

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
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `daily_reports`
--

DROP TABLE IF EXISTS `daily_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_reports` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `task_id` bigint(20) unsigned DEFAULT NULL,
  `report_date` date NOT NULL,
  `summary` text NOT NULL,
  `tasks_completed` text DEFAULT NULL,
  `tasks_in_progress` text DEFAULT NULL,
  `challenges` text DEFAULT NULL,
  `plan_for_tomorrow` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `daily_reports_user_id_report_date_unique` (`user_id`,`report_date`),
  KEY `daily_reports_task_id_foreign` (`task_id`),
  CONSTRAINT `daily_reports_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE SET NULL,
  CONSTRAINT `daily_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_reports`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `daily_reports` WRITE;
/*!40000 ALTER TABLE `daily_reports` DISABLE KEYS */;
INSERT INTO `daily_reports` VALUES
(1,4,3,'2026-03-16','Worked on configuring the server.',NULL,NULL,'Server database support','Continue to work on it.','2026-03-16 00:25:58','2026-03-16 00:27:11'),
(2,3,3,'2026-03-16','Worked on configuring the database and files',NULL,NULL,'Server migration','Testing','2026-03-16 00:32:02','2026-03-16 00:32:02'),
(3,5,6,'2026-03-16','Translated the home page',NULL,NULL,'none','translate remaining pages','2026-03-16 01:52:32','2026-03-16 01:52:32'),
(5,5,11,'2026-03-19','calendar work in progress',NULL,NULL,'none',NULL,'2026-03-19 04:24:37','2026-03-19 04:29:56'),
(6,3,NULL,'2026-03-19','worked on translation',NULL,NULL,'none',NULL,'2026-03-19 04:26:38','2026-03-19 04:30:18'),
(7,3,14,'2026-03-20','test',NULL,NULL,'test','test','2026-03-20 01:57:30','2026-03-20 01:57:30');
/*!40000 ALTER TABLE `daily_reports` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_at_available_at_index` (`queue`,`reserved_at`,`available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES
(1,'0001_01_01_000000_create_users_table',1),
(2,'0001_01_01_000001_create_cache_table',1),
(3,'0001_01_01_000002_create_jobs_table',1),
(4,'2026_03_15_120431_add_role_to_users_table',2),
(5,'2026_03_15_120432_create_tasks_table',2),
(6,'2026_03_15_120433_create_daily_reports_table',2),
(7,'2026_03_15_130000_add_start_date_assigned_to_progress_to_tasks_table',3),
(8,'2026_03_16_060409_add_task_id_to_daily_reports_table',4),
(9,'2026_03_16_065700_add_avatar_to_users_table',5),
(10,'2026_03_19_051133_add_super_admin_role_to_users_table',6),
(11,'2026_03_19_053538_add_expected_end_date_to_tasks_table',7),
(12,'2026_03_19_055717_create_task_attachments_table',8),
(13,'2026_03_20_073057_create_notifications_table',9);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES
(1,3,'task_assigned','New Task Assigned','You have been assigned the task \"Test notification\" by Yan.','http://127.0.0.1:8000/tasks/14','2026-03-20 01:53:40','2026-03-20 01:53:25','2026-03-20 01:53:40'),
(2,1,'task_created','New Task Created','Yan created a new task \"Test notification\".','http://127.0.0.1:8000/tasks/14',NULL,'2026-03-20 01:53:25','2026-03-20 01:53:25'),
(3,6,'task_created','New Task Created','Yan created a new task \"Test notification\".','http://127.0.0.1:8000/tasks/14',NULL,'2026-03-20 01:53:25','2026-03-20 01:53:25'),
(4,7,'task_updated','Task Updated','PS updated the task \"Test notification\".','http://127.0.0.1:8000/tasks/14','2026-03-20 01:56:49','2026-03-20 01:56:29','2026-03-20 01:56:49'),
(5,3,'task_updated','Task Updated','PS updated the task \"Test notification\".','http://127.0.0.1:8000/tasks/14','2026-03-20 01:57:35','2026-03-20 01:56:29','2026-03-20 01:57:35'),
(6,1,'report_submitted','New Report Submitted','PS submitted a daily report for Mar 20, 2026.','http://127.0.0.1:8000/reports/7',NULL,'2026-03-20 01:57:30','2026-03-20 01:57:30'),
(7,6,'report_submitted','New Report Submitted','PS submitted a daily report for Mar 20, 2026.','http://127.0.0.1:8000/reports/7',NULL,'2026-03-20 01:57:30','2026-03-20 01:57:30'),
(8,7,'report_submitted','New Report Submitted','PS submitted a daily report for Mar 20, 2026.','http://127.0.0.1:8000/reports/7','2026-03-20 01:57:58','2026-03-20 01:57:30','2026-03-20 01:57:58'),
(9,3,'task_updated','Task Updated','Yan updated the task \"Test notification\".','http://127.0.0.1:8000/tasks/14','2026-03-20 01:59:55','2026-03-20 01:59:44','2026-03-20 01:59:55');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES
('yGikHjg7Vhqzqs0s5usKbCsZOxakMSz9MMKua12V',3,'127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRW9QcVRSd3NNMU5mMFA5Y3hFS1J0Mjd1REdGNTZraEwySmxNeFl1ZCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTIxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvY2FsZW5kYXIvcmVwb3J0cy9kYXRhP2VuZD0yMDI2LTA0LTEyVDAwJTNBMDAlM0EwMCUyQjA1JTNBNDUmc3RhcnQ9MjAyNi0wMy0wMVQwMCUzQTAwJTNBMDAlMkIwNSUzQTQ1IjtzOjU6InJvdXRlIjtzOjIxOiJjYWxlbmRhci5yZXBvcnRzLmRhdGEiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozO30=',1773999052);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `task_attachments`
--

DROP TABLE IF EXISTS `task_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_attachments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` bigint(20) unsigned NOT NULL,
  `type` enum('file','image','link') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_attachments_task_id_foreign` (`task_id`),
  CONSTRAINT `task_attachments_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_attachments`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `task_attachments` WRITE;
/*!40000 ALTER TABLE `task_attachments` DISABLE KEYS */;
INSERT INTO `task_attachments` VALUES
(1,9,'file','task-attachments/files/HY8mwqUcKjxMq7TpCNnruzyVXvlhIC3rC27I7Uky.txt','FRD_DRS.md',NULL,'2026-03-19 00:18:39','2026-03-19 00:18:39'),
(2,9,'file','task-attachments/files/TSGWNUcSYThxs6OUTv78UGXHbwmSjXYCdM8CdtKG.txt','korean_web_style_report.md',NULL,'2026-03-19 00:18:39','2026-03-19 00:18:39'),
(3,9,'image','task-attachments/images/6piSeinxbnvfzwpQtRkXeb5AC0fCMvKqaEEuEit8.png','Screenshot from 2026-03-11 12-07-26.png',NULL,'2026-03-19 00:18:39','2026-03-19 00:18:39'),
(4,9,'image','task-attachments/images/faIbEk2gZRGGWt6fRLl2j8lYdYPCaX42I4ew0xN3.png','Screenshot from 2026-03-11 13-39-59.png',NULL,'2026-03-19 00:34:21','2026-03-19 00:34:21'),
(5,10,'file','task-attachments/files/5SxVDq57GbqDlQOg05cDCXUFlDJrXYiWtVdGcpud.txt','FRD_DRS.md',NULL,'2026-03-19 01:03:47','2026-03-19 01:03:47'),
(6,10,'file','task-attachments/files/XgQarzPbFoDiTh0phvD6fLzERvEL32zjQ8xLIqOP.txt','korean_web_style_report (2).md',NULL,'2026-03-19 01:03:47','2026-03-19 01:03:47'),
(7,11,'file','task-attachments/files/e5OKa6v7h8TpNItPjQ8YYnfUqQSReU9z0VVbIyP1.png','Screenshot from 2026-03-11 12-07-26.png',NULL,'2026-03-19 04:22:14','2026-03-19 04:22:14'),
(8,11,'file','task-attachments/files/NNIrLHKC16gLco15KdgJTT5P4QSF7l5wdueMRmZl.png','Screenshot from 2026-03-11 13-39-59.png',NULL,'2026-03-19 04:22:14','2026-03-19 04:22:14'),
(9,13,'image','task-attachments/images/C53pmBRDfiv4nNfZIfHn2Dxf7jsjDnCLqVHurY9o.png','Screenshot from 2026-03-19 12-58-11.png',NULL,'2026-03-20 01:42:01','2026-03-20 01:42:01'),
(10,13,'link',NULL,NULL,'https://abc.com','2026-03-20 01:42:01','2026-03-20 01:42:01'),
(11,13,'link',NULL,NULL,'https://xyz.net','2026-03-20 01:42:01','2026-03-20 01:42:01');
/*!40000 ALTER TABLE `task_attachments` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `assigned_to` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `expected_end_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `progress` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_user_id_foreign` (`user_id`),
  KEY `tasks_assigned_to_foreign` (`assigned_to`),
  CONSTRAINT `tasks_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tasks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES
(1,1,NULL,'DRS system','Create daily report system',NULL,'in_progress',0,'low',NULL,'2026-03-16','2026-03-15 06:30:36','2026-03-15 06:30:36'),
(2,3,NULL,'Test','test',NULL,'pending',0,'medium',NULL,NULL,'2026-03-15 06:46:51','2026-03-15 06:46:51'),
(3,4,3,'DRS system','in progress',NULL,'in_progress',5,'medium',NULL,NULL,'2026-03-16 00:09:22','2026-03-16 00:09:22'),
(4,1,4,'Task 1','TEST',NULL,'in_progress',5,'medium','2026-03-16','2026-03-18','2026-03-16 00:11:25','2026-03-16 00:15:35'),
(5,1,3,'Fix bug',NULL,NULL,'pending',90,'high','2026-03-16',NULL,'2026-03-16 01:24:23','2026-03-17 05:07:12'),
(6,1,5,'Translate the website',NULL,NULL,'completed',100,'medium','2026-03-16','2026-03-17','2026-03-16 01:51:10','2026-03-19 03:52:28'),
(7,1,3,'DRS update','make some changes as mentioned','2026-03-21','in_progress',5,'medium','2026-03-19',NULL,'2026-03-18 23:54:46','2026-03-18 23:55:33'),
(9,1,3,'Task 1','test','2026-03-22','in_progress',5,'medium',NULL,NULL,'2026-03-19 00:18:39','2026-03-19 00:37:07'),
(10,6,3,'Test','test','2026-03-22','in_progress',5,'medium',NULL,NULL,'2026-03-19 01:03:47','2026-03-19 01:17:22'),
(11,1,5,'Calendar function','add calendar','2026-03-20','in_progress',10,'medium',NULL,NULL,'2026-03-19 04:22:14','2026-03-19 04:23:59'),
(13,7,3,'Study the provided links','Study the websites and the technology used for the provided websites.','2026-03-22','in_progress',0,'medium',NULL,NULL,'2026-03-20 01:42:01','2026-03-20 01:52:50'),
(14,7,3,'Test notification','test test','2026-03-21','in_progress',5,'medium',NULL,NULL,'2026-03-20 01:53:25','2026-03-20 01:59:44');
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'Admin User','admin@drs.com',NULL,'super_admin','2026-03-15 06:27:33','$2y$12$dol5Er9.oPIpEo8wSPeLhOmpMyMb24IM5q74lCbZxwZmE8QXr8pTG','pOlAqlDTZraYhYEoSL02myQ11QfNBvh1ZO4VS2dLGo847ZkrVoCJurMwt4ky','2026-03-15 06:27:34','2026-03-18 23:36:22'),
(2,'Test User','user@drs.com',NULL,'user','2026-03-15 06:27:34','$2y$12$32bK/UP66CEbmVtnDP1B5e6eQsEsrUw53b/TMMZCDSrf5VAeOIu7m','hk8FiNAEQ3','2026-03-15 06:27:34','2026-03-15 06:27:34'),
(3,'PS','psdevlop@gmail.com',NULL,'user',NULL,'$2y$12$wypS445Nz8RW6PtJIMgIt..wfufcD37yA7xYaFLIbbIbJeq9GzbrO','QEZmDG0RUDKwQ6BvceiAtnGkts3JRNJE7UCEYzD6s2VM8ZNB6UiPAedeNnOB','2026-03-15 06:32:38','2026-03-16 01:47:41'),
(4,'RK','rk@gmail.com',NULL,'user',NULL,'$2y$12$NJvQmJ4WxQolE1gEe6V9XeYeiIw4T0QzLcdMQ8dCaV1PmgbJu/8C.',NULL,'2026-03-16 00:08:42','2026-03-16 00:08:42'),
(5,'Rose','rose@crupee.com',NULL,'user',NULL,'$2y$12$lyyILBZspRjB60bWUOwf9OwangpWsA1z.UhjwlnN0yQAtY3QcWZQq',NULL,'2026-03-16 01:50:45','2026-03-16 01:50:45'),
(6,'smile','smile@drs.com',NULL,'admin',NULL,'$2y$12$aH3mYH7xqEj2EnTACvVCm.Tcxg/s/LR0QVgiFi2Z37A93y5.I6bpu',NULL,'2026-03-18 23:38:50','2026-03-18 23:38:50'),
(7,'Yan','yan@drs.com',NULL,'admin',NULL,'$2y$12$rI5lYC5JyQNI9hUU.b05c.aCRm.oCCWrRtBinXnbnIxkTt95eEZEG','FA1EtdUZ6ChOQcDW9DHmR32X9qr3BmIRnn21QJKJjPX1fyDPWXITTMuUOe9d','2026-03-19 01:19:05','2026-03-19 01:19:05');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-03-20 16:19:00
