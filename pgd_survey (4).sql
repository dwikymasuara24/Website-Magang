-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 14 Des 2025 pada 07.21
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pgd_survey`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity_type`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'login', 'User berhasil login', '::1', '2025-12-09 03:58:04'),
(2, 1, 'export_excel', 'Export data survey ke Excel', '::1', '2025-12-09 04:12:27'),
(3, 1, 'export_pdf', 'Export data survey ke PDF', '::1', '2025-12-09 04:12:30'),
(4, 1, 'logout', 'User berhasil logout', '::1', '2025-12-09 04:16:15'),
(5, 2, 'login', 'User berhasil login', '::1', '2025-12-09 04:17:33'),
(6, 2, 'logout', 'User berhasil logout', '::1', '2025-12-09 04:18:32'),
(7, 1, 'login', 'User berhasil login', '::1', '2025-12-09 04:20:48'),
(8, 1, 'create_survey', 'Menambah survey: SRV-2025-001', '::1', '2025-12-09 05:04:21'),
(9, 1, 'create_survey', 'Menambah survey: SRV-2025-003', '::1', '2025-12-09 05:18:08'),
(10, 1, 'view_survey_detail', 'Melihat detail survey ID: 7 (SRV-2025-003)', '::1', '2025-12-09 05:22:18'),
(11, 1, 'logout', 'User berhasil logout', '::1', '2025-12-09 05:22:46'),
(12, 2, 'login', 'User berhasil login', '::1', '2025-12-09 05:22:55'),
(13, 2, 'view_survey_detail', 'Melihat detail survey ID: 7 (SRV-2025-003)', '::1', '2025-12-09 05:23:02'),
(14, 2, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 05:23:09'),
(15, 2, 'view_survey_detail', 'Melihat detail survey ID: 7 (SRV-2025-003)', '::1', '2025-12-09 05:26:50'),
(16, 2, 'export_pdf', 'Export data survey ke PDF', '::1', '2025-12-09 05:27:31'),
(17, 2, 'view_survey_detail', 'Melihat detail survey ID: 7 (SRV-2025-003)', '::1', '2025-12-09 05:33:15'),
(18, 2, 'view_survey_detail', 'Melihat detail survey ID: 7 (SRV-2025-003)', '::1', '2025-12-09 05:33:48'),
(19, 2, 'view_survey_detail', 'Melihat detail survey ID: 7 (SRV-2025-003)', '::1', '2025-12-09 05:34:48'),
(20, 2, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 05:35:05'),
(21, 2, 'view_survey_detail', 'Melihat detail survey ID: 1 (SRV-2024-001)', '::1', '2025-12-09 05:35:09'),
(22, 2, 'view_survey_detail', 'Melihat detail survey ID: 7 (SRV-2025-003)', '::1', '2025-12-09 06:15:11'),
(23, 2, 'delete_survey', 'Menghapus survey ID: 7', '::1', '2025-12-09 06:57:57'),
(24, 2, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 07:00:17'),
(25, 2, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 07:05:13'),
(26, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-001 ke PDF', '::1', '2025-12-09 07:05:18'),
(27, 2, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 07:05:38'),
(28, 2, 'create_survey', 'Menambah survey: SRV-2025-003', '::1', '2025-12-09 07:06:30'),
(29, 2, 'view_survey_detail', 'Melihat detail survey ID: 8 (SRV-2025-003)', '::1', '2025-12-09 07:06:33'),
(30, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-003 ke PDF', '::1', '2025-12-09 07:06:40'),
(31, 2, 'view_survey_detail', 'Melihat detail survey ID: 8 (SRV-2025-003)', '::1', '2025-12-09 07:09:48'),
(32, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-003 ke PDF', '::1', '2025-12-09 07:09:53'),
(33, 2, 'view_survey_detail', 'Melihat detail survey ID: 8 (SRV-2025-003)', '::1', '2025-12-09 07:13:01'),
(34, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-003 ke PDF', '::1', '2025-12-09 07:13:05'),
(35, 2, 'view_survey_detail', 'Melihat detail survey ID: 8 (SRV-2025-003)', '::1', '2025-12-09 07:13:46'),
(36, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-003 ke PDF', '::1', '2025-12-09 07:13:54'),
(37, 2, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 07:16:35'),
(38, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-001 ke PDF', '::1', '2025-12-09 07:16:40'),
(39, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-003 ke PDF', '::1', '2025-12-09 07:27:35'),
(40, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-003 ke PDF', '::1', '2025-12-09 07:34:37'),
(41, 2, 'export_pdf', 'Export data survey ke PDF', '::1', '2025-12-09 07:35:03'),
(42, 2, 'delete_survey', 'Menghapus survey ID: 8', '::1', '2025-12-09 07:35:45'),
(43, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-001 ke PDF', '::1', '2025-12-09 07:35:56'),
(44, 2, 'delete_survey', 'Menghapus survey ID: 8', '::1', '2025-12-09 07:48:06'),
(45, 2, 'delete_survey', 'Menghapus survey ID: 8', '::1', '2025-12-09 07:48:45'),
(46, 2, 'delete_survey', 'Menghapus survey ID: 8', '::1', '2025-12-09 07:49:18'),
(47, 2, 'delete_survey', 'Menghapus survey ID: 8', '::1', '2025-12-09 07:49:23'),
(48, 2, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 07:52:08'),
(49, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-001 ke PDF', '::1', '2025-12-09 07:52:14'),
(50, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-001 ke PDF', '::1', '2025-12-09 07:58:50'),
(51, 2, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 08:00:44'),
(52, 2, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 08:01:10'),
(53, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-001 ke PDF', '::1', '2025-12-09 08:01:18'),
(54, 2, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 08:02:00'),
(55, 2, 'export_pdf_detail', 'Export detail survey SRV-2025-001 ke PDF', '::1', '2025-12-09 08:04:57'),
(56, 2, 'logout', 'User berhasil logout', '::1', '2025-12-09 08:05:33'),
(57, 2, 'logout', 'User berhasil logout', '::1', '2025-12-09 08:05:34'),
(58, 1, 'login', 'User berhasil login', '::1', '2025-12-09 08:05:50'),
(59, 1, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 08:06:01'),
(60, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-001 ke PDF', '::1', '2025-12-09 08:06:10'),
(61, 1, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 08:14:29'),
(62, 1, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 08:14:48'),
(63, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-001 ke PDF', '::1', '2025-12-09 08:14:53'),
(64, 1, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 09:00:01'),
(65, 1, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-09 09:15:20'),
(66, 1, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '127.0.0.1', '2025-12-09 09:19:33'),
(67, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-001 ke PDF', '127.0.0.1', '2025-12-09 09:19:37'),
(68, 1, 'login', 'User berhasil login', '::1', '2025-12-10 08:49:05'),
(69, 1, 'login', 'User berhasil login', '::1', '2025-12-10 10:38:43'),
(70, 1, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-10 10:38:50'),
(71, 1, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-10 10:58:12'),
(72, 1, 'update_survey', 'Mengupdate survey: SRV-2025-001 (ID: 6)', '::1', '2025-12-10 10:58:55'),
(73, 1, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-10 10:59:00'),
(74, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-001 ke PDF', '::1', '2025-12-10 10:59:02'),
(75, 1, 'create_survey', 'Menambah survey: SRV-2025-002', '::1', '2025-12-10 11:08:31'),
(77, 1, 'login', 'User berhasil login', '::1', '2025-12-10 20:43:58'),
(78, 1, 'logout', 'User berhasil logout', '::1', '2025-12-10 20:51:40'),
(79, 1, 'login', 'User berhasil login', '::1', '2025-12-10 20:51:54'),
(84, 1, 'create_survey', 'Menambah survey: SRV-2025-007', '::1', '2025-12-10 21:10:13'),
(92, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-007 ke PDF', '::1', '2025-12-10 21:43:28'),
(93, 1, 'logout', 'User berhasil logout', '::1', '2025-12-10 21:44:38'),
(94, 1, 'login', 'User berhasil login', '::1', '2025-12-10 21:44:49'),
(96, 1, 'create_survey', 'Menambah survey: SRV-2025-008', '::1', '2025-12-10 21:46:04'),
(97, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 21:46:06'),
(98, 1, 'logout', 'User berhasil logout', '::1', '2025-12-10 21:51:15'),
(99, 1, 'login', 'User berhasil login', '::1', '2025-12-10 21:51:26'),
(100, 1, 'view_survey_detail', 'Melihat detail survey ID: 6 (SRV-2025-001)', '::1', '2025-12-10 21:51:57'),
(101, 1, 'logout', 'User berhasil logout', '::1', '2025-12-10 21:59:07'),
(102, 1, 'login', 'User berhasil login', '::1', '2025-12-10 21:59:15'),
(103, 1, 'delete_survey', 'Menghapus survey ID: 5', '::1', '2025-12-10 22:04:19'),
(104, 1, 'update_survey', 'Mengupdate survey: SRV-2025-008 (ID: 11)', '::1', '2025-12-10 22:04:34'),
(105, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 22:04:37'),
(106, 1, 'delete_survey', 'Menghapus survey ID: 4', '::1', '2025-12-10 22:04:46'),
(107, 1, 'delete_survey', 'Menghapus survey ID: 3', '::1', '2025-12-10 22:04:49'),
(108, 1, 'delete_survey', 'Menghapus survey ID: 2', '::1', '2025-12-10 22:09:36'),
(109, 1, 'delete_survey', 'Menghapus survey ID: 1', '::1', '2025-12-10 22:09:39'),
(110, 1, 'delete_survey', 'Menghapus survey ID: 6', '::1', '2025-12-10 22:09:42'),
(112, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 22:09:54'),
(113, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:09:58'),
(114, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 22:11:25'),
(115, 1, 'delete_survey', 'Menghapus survey ID: 10', '::1', '2025-12-10 22:11:43'),
(116, 1, 'delete_survey', 'Menghapus survey ID: 9', '::1', '2025-12-10 22:11:55'),
(117, 1, 'logout', 'User berhasil logout', '::1', '2025-12-10 22:19:27'),
(118, 1, 'login', 'User berhasil login', '::1', '2025-12-10 22:19:36'),
(119, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 22:19:43'),
(120, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:19:45'),
(121, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 22:23:26'),
(122, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:23:30'),
(123, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 22:24:08'),
(124, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:24:10'),
(125, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 22:37:24'),
(126, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 22:38:31'),
(127, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:38:33'),
(128, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 22:42:34'),
(129, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:42:36'),
(130, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 22:47:44'),
(131, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:47:48'),
(132, 1, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-10 22:51:37'),
(133, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:51:44'),
(134, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:52:41'),
(135, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:53:13'),
(136, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:53:18'),
(137, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:53:40'),
(138, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:53:58'),
(139, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:54:20'),
(140, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-10 22:54:42'),
(141, 1, 'create_survey', 'Menambah survey: SRV-2025-009', '::1', '2025-12-10 22:59:08'),
(142, 1, 'view_survey_detail', 'Melihat detail survey ID: 12 (SRV-2025-009)', '::1', '2025-12-10 22:59:12'),
(143, 1, 'update_survey', 'Mengupdate survey: SRV-2025-009 (ID: 12)', '::1', '2025-12-10 22:59:49'),
(144, 1, 'view_survey_detail', 'Melihat detail survey ID: 12 (SRV-2025-009)', '::1', '2025-12-10 22:59:54'),
(145, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-009 ke PDF', '::1', '2025-12-10 22:59:59'),
(146, 1, 'export_pdf_detail', 'Export detail survey SRV-2025-009 ke PDF', '::1', '2025-12-10 23:02:02'),
(147, 10, 'register', 'User baru berhasil mendaftar', '::1', '2025-12-11 00:16:26'),
(148, 10, 'login', 'User berhasil login', '::1', '2025-12-11 00:16:38'),
(149, 10, 'login', 'User berhasil login', '::1', '2025-12-11 00:16:51'),
(150, 10, 'login', 'User berhasil login', '::1', '2025-12-11 00:19:45'),
(151, 10, 'view_survey_detail', 'Melihat detail survey ID: 12 (SRV-2025-009)', '::1', '2025-12-11 00:19:56'),
(152, 10, 'export_pdf_detail', 'Export detail survey SRV-2025-009 ke PDF', '::1', '2025-12-11 00:20:00'),
(153, 10, 'update_survey', 'Mengupdate survey: SRV-2025-009 (ID: 12)', '::1', '2025-12-11 00:21:40'),
(154, 10, 'view_survey_detail', 'Melihat detail survey ID: 12 (SRV-2025-009)', '::1', '2025-12-11 00:21:43'),
(155, 10, 'logout', 'User berhasil logout', '::1', '2025-12-11 00:22:20'),
(156, 11, 'register', 'User baru berhasil mendaftar', '::1', '2025-12-11 00:38:09'),
(157, 11, 'login', 'User berhasil login', '::1', '2025-12-11 00:38:20'),
(158, 11, 'logout', 'User berhasil logout', '::1', '2025-12-11 00:39:37'),
(159, 12, 'register', 'User baru berhasil mendaftar', '::1', '2025-12-11 00:41:01'),
(160, 12, 'login', 'User berhasil login', '::1', '2025-12-11 00:41:10'),
(161, 12, 'update_survey', 'Mengupdate survey: SRV-2025-008 (ID: 11)', '::1', '2025-12-11 00:43:36'),
(162, 12, 'update_survey', 'Mengupdate survey: SRV-2025-008 (ID: 11)', '::1', '2025-12-11 00:45:16'),
(163, 12, 'update_survey', 'Mengupdate survey: SRV-2025-009 (ID: 12)', '::1', '2025-12-11 00:46:03'),
(164, 12, 'update_survey', 'Mengupdate survey: SRV-2025-009 (ID: 12)', '::1', '2025-12-11 00:47:49'),
(165, 12, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-11 00:58:01'),
(166, 12, 'update_survey', 'Mengupdate survey: SRV-2025-008 (ID: 11)', '::1', '2025-12-11 01:00:23'),
(167, 12, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-11 01:00:27'),
(168, 12, 'view_survey_detail', 'Melihat detail survey ID: 12 (SRV-2025-009)', '::1', '2025-12-11 02:28:39'),
(169, 12, 'view_survey_detail', 'Melihat detail survey ID: 11 (SRV-2025-008)', '::1', '2025-12-11 02:28:44'),
(170, 12, 'export_pdf_detail', 'Export detail survey SRV-2025-008 ke PDF', '::1', '2025-12-11 02:28:47'),
(171, 12, 'login', 'User berhasil login', '::1', '2025-12-14 06:19:12'),
(172, 12, 'logout', 'User berhasil logout', '::1', '2025-12-14 06:19:35'),
(173, 12, 'login', 'User berhasil login', '::1', '2025-12-14 06:19:49'),
(174, 12, 'view_survey_detail', 'Melihat detail survey ID: 12 (SRV-2025-009)', '::1', '2025-12-14 06:19:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `survey_kunjungan`
--

CREATE TABLE `survey_kunjungan` (
  `id` int(11) NOT NULL,
  `kode_survey` varchar(20) NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `nama_nasabah` varchar(100) NOT NULL,
  `nik_nasabah` varchar(20) NOT NULL,
  `alamat_nasabah` text NOT NULL,
  `no_telepon` varchar(15) DEFAULT NULL,
  `jenis_produk` enum('Kupedes','Mulia Multimate','Kredit Usaha Rakyat','Arrum Ekspress Loan','Amanah','Krasida') NOT NULL,
  `nilai_transaksi` decimal(15,2) DEFAULT NULL,
  `tujuan_kunjungan` enum('Survey Awal','Follow Up','Penagihan','Verifikasi Data','Lainnya') NOT NULL,
  `hasil_kunjungan` enum('Berhasil Ditemui','Tidak Ada di Tempat','Pindah Alamat','Menolak','Proses Lanjut') NOT NULL,
  `catatan_surveyor` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `foto_bukti` varchar(255) DEFAULT NULL,
  `foto_ktp` varchar(255) DEFAULT NULL,
  `foto_dokumentasi` varchar(255) DEFAULT NULL,
  `status_survey` enum('Draft','Submitted','Approved','Rejected') DEFAULT 'Draft',
  `surveyor_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `survey_kunjungan`
--

INSERT INTO `survey_kunjungan` (`id`, `kode_survey`, `tanggal_kunjungan`, `nama_nasabah`, `nik_nasabah`, `alamat_nasabah`, `no_telepon`, `jenis_produk`, `nilai_transaksi`, `tujuan_kunjungan`, `hasil_kunjungan`, `catatan_surveyor`, `latitude`, `longitude`, `foto_bukti`, `foto_ktp`, `foto_dokumentasi`, `status_survey`, `surveyor_id`, `created_at`, `updated_at`) VALUES
(11, 'SRV-2025-008', '2025-12-11', 'Dwi', '7101120405000211', 'Politeknik Dekat kanpus', '0898467124174', 'Amanah', 30000000.00, 'Follow Up', 'Berhasil Ditemui', 'Bertemu di rumah langsung', NULL, NULL, 'uploads/SRV-2025-008_bukti_1765413916.jpg', 'uploads/SRV-2025-008_ktp_1765413916.webp', 'uploads/SRV-2025-008_dokumentasi_1765413916.jpg', 'Approved', 1, '2025-12-10 21:46:04', '2025-12-11 01:00:23'),
(12, 'SRV-2025-009', '2025-12-11', 'Enjel Soewarno', '7171025100400001', 'Buha', '0895801557518', 'Kredit Usaha Rakyat', 2000000.00, 'Survey Awal', 'Berhasil Ditemui', 'ada di rumah', NULL, NULL, 'uploads/SRV-2025-009_bukti_1765412500.jpg', 'uploads/SRV-2025-009_ktp_1765413963.png', 'uploads/SRV-2025-009_dokumentasi_1765412500.jpg', 'Submitted', 1, '2025-12-10 22:59:08', '2025-12-11 00:47:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','surveyor') DEFAULT 'surveyor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@pegadaian.co.id', 'admin', '2025-12-09 03:51:06', '2025-12-09 03:51:06'),
(2, 'surveyor1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Surveyor Satu', 'surveyor1@pegadaian.co.id', 'surveyor', '2025-12-09 03:51:06', '2025-12-09 03:51:06'),
(10, 'syane', '$2y$10$a7yWWL2spMIgyShX09w5ueA1vTeMjy0nkNTlK1He6qBhYTwHo/n2u', 'Syane', 'syane@pegadaian.co.id', 'surveyor', '2025-12-11 00:16:26', '2025-12-11 00:16:26'),
(11, 'enjel', '$2y$10$kGF4i58NxCLNNnpIcA8a..6lKXolOnCSM2X4qsua2RgZBnutIrz.q', 'Enjel Soewarno', 'enjels@pegadaian.co.id', 'admin', '2025-12-11 00:38:09', '2025-12-11 00:38:09'),
(12, 'dwikka', '$2y$10$v825llOi/0Jh.kgAjOm5BOvq5VS/0Ek6sViBbeiNXd4D8kzcASUFK', 'Dwi', 'dwi@pegadaian.co.id', 'admin', '2025-12-11 00:41:01', '2025-12-11 00:41:01');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `survey_kunjungan`
--
ALTER TABLE `survey_kunjungan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_survey` (`kode_survey`),
  ADD KEY `surveyor_id` (`surveyor_id`),
  ADD KEY `idx_tanggal` (`tanggal_kunjungan`),
  ADD KEY `idx_nasabah` (`nama_nasabah`),
  ADD KEY `idx_status` (`status_survey`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT untuk tabel `survey_kunjungan`
--
ALTER TABLE `survey_kunjungan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `survey_kunjungan`
--
ALTER TABLE `survey_kunjungan`
  ADD CONSTRAINT `survey_kunjungan_ibfk_1` FOREIGN KEY (`surveyor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
