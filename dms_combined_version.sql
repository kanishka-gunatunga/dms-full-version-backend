-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 26, 2026 at 05:44 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dms_combined_version`
--

-- --------------------------------------------------------

--
-- Table structure for table `ad_credential`
--

DROP TABLE IF EXISTS `ad_credential`;
CREATE TABLE IF NOT EXISTS `ad_credential` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tenant_id` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `client_id` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `client_secret` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `ad_credential`
--

INSERT INTO `ad_credential` (`id`, `tenant_id`, `client_id`, `client_secret`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, NULL, NULL, NULL, '2026-04-30 07:28:57', '2026-04-30 07:28:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attributes`
--

DROP TABLE IF EXISTS `attributes`;
CREATE TABLE IF NOT EXISTS `attributes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` int DEFAULT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `attributes`
--

INSERT INTO `attributes` (`id`, `category`, `attributes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 33, '[]', '2026-06-19 06:12:41', '2026-06-19 06:12:41', NULL),
(2, 34, '[]', '2026-06-19 06:16:07', '2026-06-19 06:16:07', NULL),
(3, 35, '[]', '2026-06-19 06:53:11', '2026-06-19 06:53:11', NULL),
(4, 1, '[]', '2026-06-19 06:53:36', '2026-06-19 06:53:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bulk_uploads`
--

DROP TABLE IF EXISTS `bulk_uploads`;
CREATE TABLE IF NOT EXISTS `bulk_uploads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `file_path` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bulk_uploads_excel`
--

DROP TABLE IF EXISTS `bulk_uploads_excel`;
CREATE TABLE IF NOT EXISTS `bulk_uploads_excel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `upload_file` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `category` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `sector_category` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `file_path` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `extension` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `row_from` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `row_to` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `storage` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bulk_uploads_excel_confirmed`
--

DROP TABLE IF EXISTS `bulk_uploads_excel_confirmed`;
CREATE TABLE IF NOT EXISTS `bulk_uploads_excel_confirmed` (
  `id` int NOT NULL AUTO_INCREMENT,
  `excel_id` int DEFAULT NULL,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `type` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `category` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `sector_category` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `storage` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `meta_tags` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `file_path` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_category` text CHARACTER SET utf32 COLLATE utf32_unicode_ci NOT NULL,
  `category_name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `template` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `ftp_account` int DEFAULT NULL,
  `signing_roles` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `signing_users` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `status` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `parent_category`, `category_name`, `description`, `template`, `ftp_account`, `signing_roles`, `signing_users`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'none', 'Default Category', 'null', 'category_template_1_1782292216.xlsx', 1, '[\"1\"]', NULL, 'active', '2026-06-24 09:13:31', '2026-06-24 09:10:22', NULL),
(33, 'none', 'Test', NULL, 'category_template_33_1781849561.xlsx', NULL, NULL, NULL, 'active', '2026-06-19 06:12:46', '2026-06-19 06:12:46', NULL),
(34, '1', 'Test 2', NULL, 'category_template_34_1781849767.xlsx', NULL, NULL, NULL, 'active', '2026-06-19 06:16:07', '2026-06-19 06:16:07', NULL),
(35, '33', 'Test 3', NULL, 'category_template_35_1781851991.xlsx', NULL, '[]', '[]', 'active', '2026-06-19 06:53:11', '2026-06-19 06:53:11', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `category_sector`
--

DROP TABLE IF EXISTS `category_sector`;
CREATE TABLE IF NOT EXISTS `category_sector` (
  `sector_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`sector_id`,`category_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `category_sector`
--

INSERT INTO `category_sector` (`sector_id`, `category_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `company_profile`
--

DROP TABLE IF EXISTS `company_profile`;
CREATE TABLE IF NOT EXISTS `company_profile` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `logo` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `banner` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `storage` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `key` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `secret` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `bucket` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `region` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `enable_external_file_view` int DEFAULT NULL,
  `enable_ad_login` int DEFAULT NULL,
  `preview_file_extension` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `send_all_to_gpt` int DEFAULT NULL,
  `send_all_to_pinecone` int DEFAULT NULL,
  `set_page_limit` int DEFAULT NULL,
  `pages_count` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `company_profile`
--

INSERT INTO `company_profile` (`id`, `title`, `logo`, `banner`, `storage`, `key`, `secret`, `bucket`, `region`, `enable_external_file_view`, `enable_ad_login`, `preview_file_extension`, `send_all_to_gpt`, `send_all_to_pinecone`, `set_page_limit`, `pages_count`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'DigiTrust - Document Management System', '1762148284-logo-2.png', '1745497185-banner-.png', 'Local Disk (Default)', '2312', 'asd2312', 'fdsfads', 'asd2342', 1, 0, 'tif', 0, 0, 0, 100, '2024-12-12 03:19:02', '2026-02-18 10:35:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `type` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `category` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `sector_category` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `storage` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `meta_tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `file_path` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `document_preview` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `is_archived` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `is_indexed` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `uploaded_method` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `expiration_date` date DEFAULT NULL,
  `indexed_or_encrypted` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `signed_locked_by` int DEFAULT NULL,
  `signed_lock_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `name`, `type`, `category`, `sector_category`, `storage`, `description`, `meta_tags`, `file_path`, `document_preview`, `is_archived`, `is_indexed`, `uploaded_method`, `attributes`, `expiration_date`, `indexed_or_encrypted`, `signed_locked_by`, `signed_lock_expires_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'FT-HNB NM-VD05.06.2026 (1).pdf', 'pdf', '1', '1', 'Local Disk (Default)', NULL, '[\"Sample\"]', 'documents/1781848737-5f673204-89ab-44d4-8a55-5eb08c66ce35.pdf', 'uploads/document_previews/default.png', NULL, NULL, 'direct', '[]', NULL, 'yes', NULL, NULL, '2026-06-19 05:59:02', '2026-06-19 07:57:22', NULL),
(2, 'file_example_JPG_100kB.jpg', 'jpg', '1', '1', '1', 'Fund Wise', '[\"PAR 19.05.2026\"]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867469-1e78c99a-e4eb-4a40-9aa2-7b81f25530fa.jpg', 'uploads/document_previews/file_example_JPG_100kB_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:11:09', '2026-06-19 11:11:16', NULL),
(3, 'file_example_MP3_700KB.mp3', 'mp3', '1', '1', '1', NULL, '[]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867469-9005ee40-8a31-4a5d-90e5-7d3a4cccb772.mp3', 'uploads/document_previews/file_example_MP3_700KB_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:11:09', '2026-06-19 11:11:16', NULL),
(4, 'file_example_MP4_480_1_5MG.mp4', 'mp4', '1', '1', '1', NULL, '[]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867470-585c704c-a12d-4605-8906-c51a80b2dacb.mp4', 'uploads/document_previews/file_example_MP4_480_1_5MG_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:11:10', '2026-06-19 11:11:16', NULL),
(5, 'file_example_PNG_500kB.png', 'png', '1', '1', '1', NULL, '[]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867470-9d45f0c6-4365-498a-a36c-8f64ee6334d9.png', 'uploads/document_previews/file_example_PNG_500kB_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:11:10', '2026-06-19 11:11:17', NULL),
(6, 'file_example_XLS_50.xls', 'xls', '1', '1', '1', NULL, '[]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867471-db3b77f4-803b-4e21-8554-f3a0dc5de28d.xls', 'uploads/document_previews/file_example_XLS_50_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:11:11', '2026-06-19 11:11:17', NULL),
(7, 'file_example_XLSX_50.xlsx', 'xlsx', '1', '1', '1', NULL, '[]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867471-6aa0be26-d70c-4df2-b4c1-fe9efc4c1db2.xlsx', 'uploads/document_previews/file_example_XLSX_50_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:11:11', '2026-06-19 11:23:54', NULL),
(8, 'file-sample_100kB.doc', 'doc', '1', '1', '1', NULL, '[]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867472-0219c217-fbc8-4eb1-9d1b-a916e5bb996f.doc', 'uploads/document_previews/file-sample_100kB_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:11:12', '2026-06-19 11:11:18', NULL),
(9, 'file-sample_500kB.docx', 'docx', '1', '1', '1', NULL, '[]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867472-6d0a76bf-6fa9-4a76-9ebd-98a2f0e3aafa.docx', 'uploads/document_previews/file-sample_500kB_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:11:12', '2026-06-19 11:11:18', NULL),
(10, 'sample-local-pdf.pdf', 'pdf', '1', '1', '1', NULL, '[]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867473-0bb270c3-831b-46f6-9187-be9f26858aae.pdf', 'uploads/document_previews/sample-local-pdf_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:11:13', '2026-06-19 11:23:55', NULL),
(11, 'file_example_JPG_100kB.jpg', 'jpg', '1', '1', '1', 'Fund Wise', '[\"PAR 19.05.2026\"]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868373-69cf53a3-a05a-4ace-9336-70c9c9600a3d.jpg', 'uploads/document_previews/file_example_JPG_100kB_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:26:13', '2026-06-19 11:26:17', NULL),
(12, 'file_example_MP3_700KB.mp3', 'mp3', '1', '1', '1', 'Fund Wise', '[\"PAR 19.05.2026\"]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868373-ccb13736-4bb8-411c-ae35-51dafd3bf20d.mp3', 'uploads/document_previews/file_example_MP3_700KB_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:26:13', '2026-06-19 11:26:17', NULL),
(13, 'file_example_MP4_480_1_5MG.mp4', 'mp4', '1', '1', '1', 'Fund Wise', '[\"PAR 19.05.2026\"]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868374-70eddcc2-4820-4687-9811-e1d324bb2890.mp4', 'uploads/document_previews/file_example_MP4_480_1_5MG_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:26:14', '2026-06-19 11:26:18', NULL),
(14, 'file_example_PNG_500kB.png', 'png', '1', '1', '1', 'Fund Wise', '[\"PAR 19.05.2026\"]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868374-c9fac410-8c48-41a4-8aae-0b04bf51962a.png', 'uploads/document_previews/file_example_PNG_500kB_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:26:14', '2026-06-19 11:26:18', NULL),
(15, 'file_example_XLS_50.xls', 'xls', '1', '1', '1', 'Fund Wise', '[\"PAR 19.05.2026\"]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868375-ce100f0b-6a11-440a-97b4-4831558d6649.xls', 'uploads/document_previews/file_example_XLS_50_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:26:15', '2026-06-19 11:26:18', NULL),
(16, 'file_example_XLSX_50.xlsx', 'xlsx', '1', '1', '1', 'Fund Wise', '[\"PAR 19.05.2026\"]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868375-e56ee8b0-7329-4bb5-80a3-8567bd98fcd8.xlsx', 'uploads/document_previews/file_example_XLSX_50_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:26:15', '2026-06-19 11:26:18', NULL),
(17, 'file-sample_100kB.doc', 'doc', '1', '1', '1', 'Fund Wise', '[\"PAR 19.05.2026\"]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868376-df696ca8-ea44-4fc7-b0c5-cde0a9c6449b.doc', 'uploads/document_previews/file-sample_100kB_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:26:16', '2026-06-19 11:26:19', NULL),
(18, 'file-sample_500kB.docx', 'docx', '1', '1', '1', 'Fund Wise', '[\"PAR 19.05.2026\"]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868376-3d8d2c6c-194a-434a-9212-3db8acd1aec1.docx', 'uploads/document_previews/file-sample_500kB_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:26:16', '2026-06-19 11:26:19', NULL),
(19, 'sample-local-pdf.pdf', 'pdf', '1', '1', '1', 'Fund Wise', '[\"PAR 19.05.2026\"]', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868376-ddeefbc6-f96c-40cc-ba8d-25f8fd1ab2c6.pdf', 'uploads/document_previews/sample-local-pdf_preview.tif', NULL, NULL, 'ftp', NULL, NULL, 'yes', NULL, NULL, '2026-06-19 11:26:16', '2026-06-19 11:26:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `document_audit_trial`
--

DROP TABLE IF EXISTS `document_audit_trial`;
CREATE TABLE IF NOT EXISTS `document_audit_trial` (
  `id` int NOT NULL AUTO_INCREMENT,
  `operation` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `type` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `user` int DEFAULT NULL,
  `changed_source` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `date_time` datetime DEFAULT NULL,
  `assigned_roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `assigned_users` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `document_audit_trial`
--

INSERT INTO `document_audit_trial` (`id`, `operation`, `type`, `user`, `changed_source`, `date_time`, `assigned_roles`, `assigned_users`, `created_at`, `updated_at`) VALUES
(1, 'document added', 'document', 1, '1', '2026-06-19 11:29:02', '[]', '[]', '2026-06-19 05:59:02', '2026-06-19 05:59:02'),
(2, 'new category added', 'category', 1, '33', '2026-06-19 11:42:46', NULL, NULL, '2026-06-19 06:12:46', '2026-06-19 06:12:46'),
(3, 'new category added', 'category', 1, '34', '2026-06-19 11:46:07', NULL, NULL, '2026-06-19 06:16:07', '2026-06-19 06:16:07'),
(4, 'new category added', 'category', 1, '35', '2026-06-19 12:23:11', NULL, NULL, '2026-06-19 06:53:11', '2026-06-19 06:53:11'),
(5, 'category details updated', 'category', 1, '1', '2026-06-19 12:23:36', NULL, NULL, '2026-06-19 06:53:36', '2026-06-19 06:53:36'),
(6, 'role details updated', 'role', 1, '1', '2026-06-19 12:45:06', NULL, NULL, '2026-06-19 07:15:06', '2026-06-19 07:15:06'),
(7, 'role details updated', 'role', 1, '1', '2026-06-19 12:45:24', NULL, NULL, '2026-06-19 07:15:24', '2026-06-19 07:15:24'),
(8, 'document viewed', 'document', 1, '1', '2026-06-19 13:20:05', NULL, NULL, '2026-06-19 07:50:08', '2026-06-19 07:50:08'),
(9, 'document viewed', 'document', 1, '1', '2026-06-19 13:25:07', NULL, NULL, '2026-06-19 07:55:07', '2026-06-19 07:55:07'),
(10, 'document signed', 'document', 1, '1', '2026-06-19 13:25:17', NULL, NULL, '2026-06-19 07:55:17', '2026-06-19 07:55:17'),
(11, 'document viewed', 'document', 1, '1', '2026-06-19 13:25:31', NULL, NULL, '2026-06-19 07:55:32', '2026-06-19 07:55:32'),
(12, 'document viewed', 'document', 1, '1', '2026-06-19 13:26:21', NULL, NULL, '2026-06-19 07:56:21', '2026-06-19 07:56:21'),
(13, 'document viewed', 'document', 1, '1', '2026-06-19 13:27:05', NULL, NULL, '2026-06-19 07:57:05', '2026-06-19 07:57:05'),
(14, 'document signed', 'document', 1, '1', '2026-06-19 13:27:22', NULL, NULL, '2026-06-19 07:57:22', '2026-06-19 07:57:22'),
(15, 'document viewed', 'document', 1, '1', '2026-06-19 13:27:32', NULL, NULL, '2026-06-19 07:57:32', '2026-06-19 07:57:32'),
(16, 'document added', 'document', 1, '2', '2026-06-19 16:41:09', NULL, NULL, '2026-06-19 11:11:09', '2026-06-19 11:11:09'),
(17, 'document added', 'document', 1, '3', '2026-06-19 16:41:09', NULL, NULL, '2026-06-19 11:11:09', '2026-06-19 11:11:09'),
(18, 'document added', 'document', 1, '4', '2026-06-19 16:41:10', NULL, NULL, '2026-06-19 11:11:10', '2026-06-19 11:11:10'),
(19, 'document added', 'document', 1, '5', '2026-06-19 16:41:10', NULL, NULL, '2026-06-19 11:11:10', '2026-06-19 11:11:10'),
(20, 'document added', 'document', 1, '6', '2026-06-19 16:41:11', NULL, NULL, '2026-06-19 11:11:11', '2026-06-19 11:11:11'),
(21, 'document added', 'document', 1, '7', '2026-06-19 16:41:11', NULL, NULL, '2026-06-19 11:11:11', '2026-06-19 11:11:11'),
(22, 'document added', 'document', 1, '8', '2026-06-19 16:41:12', NULL, NULL, '2026-06-19 11:11:12', '2026-06-19 11:11:12'),
(23, 'document added', 'document', 1, '9', '2026-06-19 16:41:12', NULL, NULL, '2026-06-19 11:11:12', '2026-06-19 11:11:12'),
(24, 'document added', 'document', 1, '10', '2026-06-19 16:41:13', NULL, NULL, '2026-06-19 11:11:13', '2026-06-19 11:11:13'),
(25, 'document added', 'document', 1, '11', '2026-06-19 16:56:13', NULL, NULL, '2026-06-19 11:26:13', '2026-06-19 11:26:13'),
(26, 'document added', 'document', 1, '12', '2026-06-19 16:56:13', NULL, NULL, '2026-06-19 11:26:13', '2026-06-19 11:26:13'),
(27, 'document added', 'document', 1, '13', '2026-06-19 16:56:14', NULL, NULL, '2026-06-19 11:26:14', '2026-06-19 11:26:14'),
(28, 'document added', 'document', 1, '14', '2026-06-19 16:56:14', NULL, NULL, '2026-06-19 11:26:14', '2026-06-19 11:26:14'),
(29, 'document added', 'document', 1, '15', '2026-06-19 16:56:15', NULL, NULL, '2026-06-19 11:26:15', '2026-06-19 11:26:15'),
(30, 'document added', 'document', 1, '16', '2026-06-19 16:56:15', NULL, NULL, '2026-06-19 11:26:15', '2026-06-19 11:26:15'),
(31, 'document added', 'document', 1, '17', '2026-06-19 16:56:16', NULL, NULL, '2026-06-19 11:26:16', '2026-06-19 11:26:16'),
(32, 'document added', 'document', 1, '18', '2026-06-19 16:56:16', NULL, NULL, '2026-06-19 11:26:16', '2026-06-19 11:26:16'),
(33, 'document added', 'document', 1, '19', '2026-06-19 16:56:16', NULL, NULL, '2026-06-19 11:26:16', '2026-06-19 11:26:16'),
(34, 'document viewed', 'document', 1, '19', '2026-06-23 13:11:31', NULL, NULL, '2026-06-23 07:41:33', '2026-06-23 07:41:33'),
(35, 'category details updated', 'category', 1, '1', '2026-06-24 14:40:22', NULL, NULL, '2026-06-24 09:10:22', '2026-06-24 09:10:22'),
(36, 'user details updated', 'user', 1, '1', '2026-06-24 14:40:46', NULL, NULL, '2026-06-24 09:10:46', '2026-06-24 09:10:46');

-- --------------------------------------------------------

--
-- Table structure for table `document_comments`
--

DROP TABLE IF EXISTS `document_comments`;
CREATE TABLE IF NOT EXISTS `document_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `date_time` datetime DEFAULT NULL,
  `user` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_shared_links`
--

DROP TABLE IF EXISTS `document_shared_links`;
CREATE TABLE IF NOT EXISTS `document_shared_links` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int DEFAULT NULL,
  `has_expire_date` int DEFAULT NULL,
  `expire_date_time` datetime DEFAULT NULL,
  `has_password` int DEFAULT NULL,
  `password` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `allow_download` int DEFAULT NULL,
  `link` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_shared_roles`
--

DROP TABLE IF EXISTS `document_shared_roles`;
CREATE TABLE IF NOT EXISTS `document_shared_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int DEFAULT NULL,
  `role` int DEFAULT NULL,
  `is_time_limited` int DEFAULT NULL,
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `is_downloadable` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_shared_users`
--

DROP TABLE IF EXISTS `document_shared_users`;
CREATE TABLE IF NOT EXISTS `document_shared_users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int DEFAULT NULL,
  `user` int DEFAULT NULL,
  `is_time_limited` int DEFAULT NULL,
  `start_date_time` datetime DEFAULT NULL,
  `end_date_time` datetime DEFAULT NULL,
  `is_downloadable` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_signatures`
--

DROP TABLE IF EXISTS `document_signatures`;
CREATE TABLE IF NOT EXISTS `document_signatures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `document_signatures`
--

INSERT INTO `document_signatures` (`id`, `document_id`, `user_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 1, 1, '2026-06-19 07:57:22', '2026-06-19 07:57:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

DROP TABLE IF EXISTS `document_versions`;
CREATE TABLE IF NOT EXISTS `document_versions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int DEFAULT NULL,
  `type` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `file_path` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `date_time` datetime DEFAULT NULL,
  `user` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `document_versions`
--

INSERT INTO `document_versions` (`id`, `document_id`, `type`, `file_path`, `date_time`, `user`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'pdf', 'documents/1781848737-5f673204-89ab-44d4-8a55-5eb08c66ce35.pdf', '2026-06-19 11:29:02', 1, '2026-06-19 05:59:02', '2026-06-19 05:59:02', NULL),
(2, 2, 'jpg', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867469-1e78c99a-e4eb-4a40-9aa2-7b81f25530fa.jpg', '2026-06-19 16:41:09', 1, '2026-06-19 11:11:09', '2026-06-19 11:11:09', NULL),
(3, 3, 'mp3', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867469-9005ee40-8a31-4a5d-90e5-7d3a4cccb772.mp3', '2026-06-19 16:41:09', 1, '2026-06-19 11:11:09', '2026-06-19 11:11:09', NULL),
(4, 4, 'mp4', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867470-585c704c-a12d-4605-8906-c51a80b2dacb.mp4', '2026-06-19 16:41:10', 1, '2026-06-19 11:11:10', '2026-06-19 11:11:10', NULL),
(5, 5, 'png', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867470-9d45f0c6-4365-498a-a36c-8f64ee6334d9.png', '2026-06-19 16:41:10', 1, '2026-06-19 11:11:10', '2026-06-19 11:11:10', NULL),
(6, 6, 'xls', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867471-db3b77f4-803b-4e21-8554-f3a0dc5de28d.xls', '2026-06-19 16:41:11', 1, '2026-06-19 11:11:11', '2026-06-19 11:11:11', NULL),
(7, 7, 'xlsx', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867471-6aa0be26-d70c-4df2-b4c1-fe9efc4c1db2.xlsx', '2026-06-19 16:41:11', 1, '2026-06-19 11:11:11', '2026-06-19 11:11:11', NULL),
(8, 8, 'doc', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867472-0219c217-fbc8-4eb1-9d1b-a916e5bb996f.doc', '2026-06-19 16:41:12', 1, '2026-06-19 11:11:12', '2026-06-19 11:11:12', NULL),
(9, 9, 'docx', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867472-6d0a76bf-6fa9-4a76-9ebd-98a2f0e3aafa.docx', '2026-06-19 16:41:12', 1, '2026-06-19 11:11:12', '2026-06-19 11:11:12', NULL),
(10, 10, 'pdf', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781867473-0bb270c3-831b-46f6-9187-be9f26858aae.pdf', '2026-06-19 16:41:13', 1, '2026-06-19 11:11:13', '2026-06-19 11:11:13', NULL),
(11, 11, 'jpg', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868373-69cf53a3-a05a-4ace-9336-70c9c9600a3d.jpg', '2026-06-19 16:56:13', 1, '2026-06-19 11:26:13', '2026-06-19 11:26:13', NULL),
(12, 12, 'mp3', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868373-ccb13736-4bb8-411c-ae35-51dafd3bf20d.mp3', '2026-06-19 16:56:13', 1, '2026-06-19 11:26:13', '2026-06-19 11:26:13', NULL),
(13, 13, 'mp4', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868374-70eddcc2-4820-4687-9811-e1d324bb2890.mp4', '2026-06-19 16:56:14', 1, '2026-06-19 11:26:14', '2026-06-19 11:26:14', NULL),
(14, 14, 'png', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868374-c9fac410-8c48-41a4-8aae-0b04bf51962a.png', '2026-06-19 16:56:14', 1, '2026-06-19 11:26:14', '2026-06-19 11:26:14', NULL),
(15, 15, 'xls', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868375-ce100f0b-6a11-440a-97b4-4831558d6649.xls', '2026-06-19 16:56:15', 1, '2026-06-19 11:26:15', '2026-06-19 11:26:15', NULL),
(16, 16, 'xlsx', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868375-e56ee8b0-7329-4bb5-80a3-8567bd98fcd8.xlsx', '2026-06-19 16:56:15', 1, '2026-06-19 11:26:15', '2026-06-19 11:26:15', NULL),
(17, 17, 'doc', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868376-df696ca8-ea44-4fc7-b0c5-cde0a9c6449b.doc', '2026-06-19 16:56:16', 1, '2026-06-19 11:26:16', '2026-06-19 11:26:16', NULL),
(18, 18, 'docx', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868376-3d8d2c6c-194a-434a-9212-3db8acd1aec1.docx', '2026-06-19 16:56:16', 1, '2026-06-19 11:26:16', '2026-06-19 11:26:16', NULL),
(19, 19, 'pdf', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP\\1781868376-ddeefbc6-f96c-40cc-ba8d-25f8fd1ab2c6.pdf', '2026-06-19 16:56:16', 1, '2026-06-19 11:26:16', '2026-06-19 11:26:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ftp_accounts`
--

DROP TABLE IF EXISTS `ftp_accounts`;
CREATE TABLE IF NOT EXISTS `ftp_accounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `host` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `port` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `username` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `password` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `root_path` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `is_default` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `ftp_accounts`
--

INSERT INTO `ftp_accounts` (`id`, `name`, `host`, `port`, `username`, `password`, `root_path`, `is_default`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'FTP 1', 'local ', '', '', '', 'D:\\KODE TECH\\DMS LATEST WITH LICENCE\\FTP', NULL, '2024-12-18 04:46:53', '2024-12-18 05:36:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `licenses`
--

DROP TABLE IF EXISTS `licenses`;
CREATE TABLE IF NOT EXISTS `licenses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload_json` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `signature` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fingerprint` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `last_validated_at` datetime DEFAULT NULL,
  `valid` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `license_checks`
--

DROP TABLE IF EXISTS `license_checks`;
CREATE TABLE IF NOT EXISTS `license_checks` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `checked_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_audits`
--

DROP TABLE IF EXISTS `login_audits`;
CREATE TABLE IF NOT EXISTS `login_audits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `date_time` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `ip_address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `status` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `latitude` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `longitude` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `login_audits`
--

INSERT INTO `login_audits` (`id`, `email`, `date_time`, `ip_address`, `status`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(1, 'defaultadmin@gmail.com', '2026-06-18 14:12:39', '127.0.0.1', 'success', NULL, NULL, '2026-06-18 08:42:39', '2026-06-18 08:42:39'),
(2, 'defaultadmin@gmail.com', '2026-06-18 14:36:50', '127.0.0.1', 'success', NULL, NULL, '2026-06-18 09:06:50', '2026-06-18 09:06:50'),
(3, 'defaultadmin@gmail.com', '2026-06-18 14:53:29', '127.0.0.1', 'success', NULL, NULL, '2026-06-18 09:23:29', '2026-06-18 09:23:29'),
(4, 'defaultadmin@gmail.com', '2026-06-19 15:59:48', '127.0.0.1', 'success', NULL, NULL, '2026-06-19 10:29:48', '2026-06-19 10:29:48'),
(5, 'defaultadmin@gmail.com', '2026-06-23 12:11:00', '127.0.0.1', 'success', NULL, NULL, '2026-06-23 06:41:00', '2026-06-23 06:41:00'),
(6, 'defaultadmin@gmail.com', '2026-06-24 14:37:34', '127.0.0.1', 'success', NULL, NULL, '2026-06-24 09:07:34', '2026-06-24 09:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_11_11_075104_create_oauth_auth_codes_table', 2),
(5, '2024_11_11_075105_create_oauth_access_tokens_table', 2),
(6, '2024_11_11_075106_create_oauth_refresh_tokens_table', 2),
(7, '2024_11_11_075107_create_oauth_clients_table', 2),
(8, '2024_11_11_075108_create_oauth_personal_access_clients_table', 2),
(9, '2025_02_10_123656_create_searchable_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `oauth_access_tokens`
--

DROP TABLE IF EXISTS `oauth_access_tokens`;
CREATE TABLE IF NOT EXISTS `oauth_access_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_access_tokens_user_id_index` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_access_tokens`
--

INSERT INTO `oauth_access_tokens` (`id`, `user_id`, `client_id`, `name`, `scopes`, `revoked`, `created_at`, `updated_at`, `expires_at`) VALUES
('da3ef71927e2cfdd85f71c38ebf86eb5fbe2be0fb4e3f2072dade1260e8158dfbd6fe289cf1cd419', 1, 1, 'Web Token', '[]', 0, '2026-06-18 08:42:43', '2026-06-18 08:42:44', '2027-06-18 14:12:43'),
('bb96ef59f234eb2b2520a430b9291e610ae93807ba7fe4a11d4d9e5c045b33646025a351d505b681', 1, 1, 'Web Token', '[]', 0, '2026-06-18 09:06:50', '2026-06-18 09:06:50', '2027-06-18 14:36:50'),
('d1908ee6b282ecca434dd6e0ed1a223fb789a74842e9be05cb1160ca6f3d3fc7d37e179738ee6c14', 1, 1, 'Web Token', '[]', 0, '2026-06-18 09:23:29', '2026-06-18 09:23:29', '2027-06-18 14:53:29'),
('e9fa175db22498f34123f7545260524dd862eab24ced42e5114595bd8dc8cf0981e96c9c8ac74f6d', 1, 1, 'Web Token', '[]', 0, '2026-06-19 10:29:51', '2026-06-19 10:29:52', '2027-06-19 15:59:51'),
('d472070f50e6970d78ba9f12cecf760bc538397503341545e441639cbb2bd844fe57c37fd2e6c206', 1, 1, 'Web Token', '[]', 0, '2026-06-23 06:41:02', '2026-06-23 06:41:03', '2027-06-23 12:11:02'),
('f7d41a475c785198ca9db990be66e514281c989bc39b71436d889da99a8f920e0afef9839869224d', 1, 1, 'Web Token', '[]', 0, '2026-06-24 09:07:36', '2026-06-24 09:07:37', '2027-06-24 14:37:36');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_auth_codes`
--

DROP TABLE IF EXISTS `oauth_auth_codes`;
CREATE TABLE IF NOT EXISTS `oauth_auth_codes` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `scopes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_auth_codes_user_id_index` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_clients`
--

DROP TABLE IF EXISTS `oauth_clients`;
CREATE TABLE IF NOT EXISTS `oauth_clients` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `personal_access_client` tinyint(1) NOT NULL,
  `password_client` tinyint(1) NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_clients_user_id_index` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_clients`
--

INSERT INTO `oauth_clients` (`id`, `user_id`, `name`, `secret`, `provider`, `redirect`, `personal_access_client`, `password_client`, `revoked`, `created_at`, `updated_at`) VALUES
(1, NULL, 'HNB Access', 'HRatSjWmNmWo5CSAUwr4NRFZIj3bs5pR0kJDpDRb', NULL, 'http://localhost', 1, 0, 0, '2026-04-06 12:21:00', '2026-04-06 12:21:00');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_personal_access_clients`
--

DROP TABLE IF EXISTS `oauth_personal_access_clients`;
CREATE TABLE IF NOT EXISTS `oauth_personal_access_clients` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `oauth_personal_access_clients`
--

INSERT INTO `oauth_personal_access_clients` (`id`, `client_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-04-06 12:21:00', '2026-04-06 12:21:00');

-- --------------------------------------------------------

--
-- Table structure for table `oauth_refresh_tokens`
--

DROP TABLE IF EXISTS `oauth_refresh_tokens`;
CREATE TABLE IF NOT EXISTS `oauth_refresh_tokens` (
  `id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `revoked` tinyint(1) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `oauth_refresh_tokens_access_token_id_index` (`access_token_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_histories`
--

DROP TABLE IF EXISTS `password_histories`;
CREATE TABLE IF NOT EXISTS `password_histories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `password` text COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reminder`
--

DROP TABLE IF EXISTS `reminder`;
CREATE TABLE IF NOT EXISTS `reminder` (
  `id` int NOT NULL AUTO_INCREMENT,
  `selected_user` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `date_and_time` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `start_date` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci COMMENT 'Repeat reminder',
  `end_date` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci COMMENT 'Repeat reminder',
  `subject` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `frequency` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `document` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `send_email` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

DROP TABLE IF EXISTS `reminders`;
CREATE TABLE IF NOT EXISTS `reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `document_id` int DEFAULT NULL,
  `subject` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `message` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `date_time` datetime DEFAULT NULL,
  `is_repeat` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `send_email` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `frequency` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `end_date_time` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `start_date_time` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `frequency_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `users` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `needs_approval` int DEFAULT NULL,
  `is_admin` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `permissions`, `needs_approval`, `is_admin`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Admin', '[{\"group\":\"Dashboard\",\"items\":[\"View Dashboard\"]},{\"group\":\"All Documents\",\"items\":[\"View Documents\",\"Create Document\",\"Edit Document\",\"Delete Document\",\"Archive Document\",\"Add Reminder\",\"Share Document\",\"Download Document\",\"Send Email\",\"Manage Sharable Link\",\"AI Options\",\"Upload New Version file\",\"Version History\",\"Comment\",\"Remove From Search\"]},{\"group\":\"Assigned Documents\",\"items\":[\"Create Document\",\"Edit Document\",\"Share Document\",\"Upload New Version\",\"Delete Document\",\"Send Email\",\"Manage Sharable Link\",\"Upload New Version file\",\"Version History\",\"Comment\",\"Remove From Search\",\"Download\",\"Add Reminder\",\"Archive\",\"View Documents\"]},{\"group\":\"Archived Documents\",\"items\":[\"View Documents\",\"Restore Document\",\"Delete Document\"]},{\"group\":\"Advanced Search\",\"items\":[\"Advanced Search\"]},{\"group\":\"Deep Search\",\"items\":[\"Deep Search\",\"Add Indexing\",\"Remove Indexing\"]},{\"group\":\"Document Categories\",\"items\":[\"Manage Document Category\"]},{\"group\":\"Bulk Upload\",\"items\":[\"View Bulk Upload\",\"Delete Bulk Upload\",\"Create Bulk Upload\",\"Edit Bulk Upload\"]},{\"group\":\"Attributes\",\"items\":[\"View Attributes\",\"Add Attributes\",\"Edit Attributes\",\"Delete Attributes\"]},{\"group\":\"Sectors\",\"items\":[\"Manage Sectors\"]},{\"group\":\"Documents Audit Trail\",\"items\":[\"View Document Audit Trail\"]},{\"group\":\"User\",\"items\":[\"View Users\",\"Create User\",\"Edit User\",\"Delete User\",\"Reset Password\",\"Assign User Role\",\"Assign Permission\"]},{\"group\":\"Role\",\"items\":[\"View Roles\",\"Create Role\",\"Edit Role\",\"Delete Role\"]},{\"group\":\"Settings\",\"items\":[\"Manage Languages\",\"Storage Settings\",\"Manage Company Profile\"]},{\"group\":\"Reminder\",\"items\":[\"View Reminders\",\"Create Reminder\",\"Edit Reminder\",\"Delete Reminder\"]},{\"group\":\"Login Audits\",\"items\":[\"View Login Audit Logs\"]},{\"group\":\"Page Helpers\",\"items\":[\"Manage Page Helper\"]},{\"group\":\"Approve Documents\",\"items\":[\"Approve Documents\",\"Approved Document History\"]},{\"group\":\"Signatures\",\"items\":[\"Sign Approval\",\"Sign Requests\"]}]', 0, 1, '2024-11-11 05:54:36', '2026-06-19 07:15:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `searchable`
--

DROP TABLE IF EXISTS `searchable`;
CREATE TABLE IF NOT EXISTS `searchable` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `searchable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `searchable_id` bigint UNSIGNED NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `searchable_searchable_type_searchable_id_unique` (`searchable_type`,`searchable_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sectors`
--

DROP TABLE IF EXISTS `sectors`;
CREATE TABLE IF NOT EXISTS `sectors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_sector` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `sector_name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `sectors`
--

INSERT INTO `sectors` (`id`, `parent_sector`, `sector_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'none', 'Default Sector', '2026-04-06 12:33:36', '2026-04-06 12:33:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('gaHOaJRws8bpfH0CABf2LNtRs8NRbTh3lE70rRgR', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibHVmeHM5blNxRmRRa29uWm5sZDBLS0dCbVpsTTFwNzhsMkt3Sm40MCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1781848114);

-- --------------------------------------------------------

--
-- Table structure for table `slide_backend`
--

DROP TABLE IF EXISTS `slide_backend`;
CREATE TABLE IF NOT EXISTS `slide_backend` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `first_name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `last_name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `mobile_no` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `smtp_details`
--

DROP TABLE IF EXISTS `smtp_details`;
CREATE TABLE IF NOT EXISTS `smtp_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `host` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `port` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `user_name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `password` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `from_name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `encryption` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `is_default` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `supervisors` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `user_type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `mfa_enabled` tinyint(1) DEFAULT NULL,
  `mfa_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mfa_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `failed_attempts` int DEFAULT NULL,
  `lockout_until` timestamp NULL DEFAULT NULL,
  `must_change_password` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=1555 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `email_verified_at`, `password`, `remember_token`, `role`, `supervisors`, `user_type`, `mfa_enabled`, `mfa_secret`, `mfa_recovery_codes`, `password_changed_at`, `failed_attempts`, `lockout_until`, `must_change_password`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'defaultadmin@gmail.com', NULL, '$2y$12$mc4F52wtQ7WxrK9Txxzqf.7DqwajyyDxA6D1wzmr1jxf9FFpD2PfG', NULL, '[\"1\"]', NULL, 'normal', 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, '2026-06-24 09:10:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

DROP TABLE IF EXISTS `user_details`;
CREATE TABLE IF NOT EXISTS `user_details` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `first_name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `last_name` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `mobile_no` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `sector` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `signature` text COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1554 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`id`, `user_id`, `first_name`, `last_name`, `mobile_no`, `sector`, `signature`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 1, 'Default', 'Admin', '0742421231', '[\"1\"]', 'signatures/1781852717_1_signature.png', '2024-11-11 03:18:18', '2026-06-24 09:10:46', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
