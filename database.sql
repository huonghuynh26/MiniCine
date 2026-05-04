-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 08:40 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `minicine`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblbookingitems`
--

CREATE TABLE `tblbookingitems` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `seat_id` smallint(5) UNSIGNED NOT NULL,
  `price` decimal(12,0) NOT NULL,
  `original_price` decimal(12,0) NOT NULL,
  `flash_sale_applied` tinyint(1) NOT NULL DEFAULT 0,
  `discount_pct` tinyint(3) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblbookingitems`
--

INSERT INTO `tblbookingitems` (`id`, `booking_id`, `seat_id`, `price`, `original_price`, `flash_sale_applied`, `discount_pct`) VALUES
(1, 1, 35, 130000, 130000, 0, 0),
(2, 1, 36, 130000, 130000, 0, 0),
(3, 1, 37, 130000, 130000, 0, 0),
(4, 1, 38, 130000, 130000, 0, 0),
(5, 2, 19, 130000, 130000, 0, 0),
(6, 2, 20, 130000, 130000, 0, 0),
(7, 2, 21, 130000, 130000, 0, 0),
(8, 2, 22, 130000, 130000, 0, 0),
(9, 2, 27, 130000, 130000, 0, 0),
(10, 2, 28, 130000, 130000, 0, 0),
(11, 2, 29, 130000, 130000, 0, 0),
(12, 2, 30, 130000, 130000, 0, 0),
(13, 3, 35, 130000, 130000, 0, 0),
(14, 3, 36, 130000, 130000, 0, 0),
(15, 3, 37, 130000, 130000, 0, 0),
(16, 4, 26, 63000, 90000, 1, 30),
(17, 4, 27, 91000, 130000, 1, 30),
(18, 4, 28, 91000, 130000, 1, 30),
(19, 4, 29, 91000, 130000, 1, 30),
(20, 5, 11, 90000, 90000, 0, 0),
(21, 6, 20, 130000, 130000, 0, 0),
(22, 6, 21, 130000, 130000, 0, 0),
(23, 6, 22, 130000, 130000, 0, 0),
(24, 6, 23, 90000, 90000, 0, 0),
(25, 6, 24, 90000, 90000, 0, 0),
(26, 7, 18, 90000, 90000, 0, 0),
(27, 7, 19, 130000, 130000, 0, 0),
(28, 7, 49, 200000, 200000, 0, 0),
(29, 7, 50, 200000, 200000, 0, 0),
(30, 8, 13, 90000, 90000, 0, 0),
(31, 9, 31, 90000, 90000, 0, 0),
(32, 10, 41, 90000, 90000, 0, 0),
(33, 10, 42, 90000, 90000, 0, 0),
(34, 10, 43, 90000, 90000, 0, 0),
(35, 10, 44, 90000, 90000, 0, 0),
(36, 10, 45, 90000, 90000, 0, 0),
(37, 10, 46, 90000, 90000, 0, 0),
(38, 10, 47, 90000, 90000, 0, 0),
(39, 11, 27, 130000, 130000, 0, 0),
(40, 11, 28, 130000, 130000, 0, 0),
(41, 11, 37, 130000, 130000, 0, 0),
(42, 11, 39, 90000, 90000, 0, 0),
(43, 12, 77, 63000, 90000, 1, 30),
(44, 12, 78, 63000, 90000, 1, 30),
(45, 12, 79, 91000, 130000, 1, 30),
(46, 12, 80, 91000, 130000, 1, 30),
(47, 12, 81, 91000, 130000, 1, 30),
(48, 12, 82, 91000, 130000, 1, 30),
(49, 12, 83, 63000, 90000, 1, 30),
(50, 13, 34, 45000, 90000, 1, 50),
(51, 13, 35, 65000, 130000, 1, 50),
(52, 13, 36, 65000, 130000, 1, 50),
(53, 13, 37, 65000, 130000, 1, 50),
(54, 13, 38, 65000, 130000, 1, 50),
(55, 14, 124, 65000, 130000, 1, 50),
(56, 14, 125, 65000, 130000, 1, 50);

-- --------------------------------------------------------

--
-- Table structure for table `tblbookings`
--

CREATE TABLE `tblbookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `show_id` int(10) UNSIGNED NOT NULL,
  `total_price` decimal(12,0) NOT NULL DEFAULT 0,
  `payment_status` enum('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `qr_code` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblbookings`
--

INSERT INTO `tblbookings` (`id`, `user_id`, `show_id`, `total_price`, `payment_status`, `qr_code`, `created_at`) VALUES
(1, 4, 1, 520000, 'paid', 'MC-242220AA441C', '2026-04-23 14:32:12'),
(2, 4, 1, 1040000, 'paid', 'MC-F4B3042F6CD7', '2026-04-23 16:18:41'),
(3, 3, 2, 390000, 'paid', 'MC-66296D0AD02F', '2026-04-30 13:12:33'),
(4, 3, 2, 336000, 'paid', 'MC-169D4179E06C', '2026-04-30 13:21:33'),
(5, 3, 2, 90000, 'paid', 'MC-2FC518516752', '2026-04-30 13:49:54'),
(6, 4, 2, 570000, 'paid', 'MC-CAA5F57DBC37', '2026-04-30 14:18:33'),
(7, 3, 2, 620000, 'paid', 'MC-F6E5086F5CEE', '2026-04-30 14:18:46'),
(8, 4, 2, 90000, 'paid', 'MC-21B01CF94382', '2026-04-30 14:20:23'),
(9, 3, 2, 90000, 'paid', 'MC-C0B56550542D', '2026-04-30 14:34:49'),
(10, 3, 2, 630000, 'paid', 'MC-09E369537F32', '2026-04-30 14:41:42'),
(11, 3, 3, 480000, 'paid', 'MC-EC35E90D1EF1', '2026-04-30 14:48:28'),
(12, 4, 4, 553000, 'paid', 'MC-605622711FA7', '2026-05-04 13:01:53'),
(13, 3, 4, 305000, 'paid', 'MC-C4C78AB9749D', '2026-05-04 13:38:09'),
(14, 4, 6, 130000, 'paid', 'MC-C96E492773F4', '2026-05-04 13:39:21');

-- --------------------------------------------------------

--
-- Table structure for table `tblflashsales`
--

CREATE TABLE `tblflashsales` (
  `id` int(10) UNSIGNED NOT NULL,
  `show_id` int(10) UNSIGNED NOT NULL,
  `discount_pct` tinyint(3) UNSIGNED NOT NULL DEFAULT 30,
  `trigger_type` enum('pre2h','post15m','manual') NOT NULL DEFAULT 'pre2h',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblflashsales`
--

INSERT INTO `tblflashsales` (`id`, `show_id`, `discount_pct`, `trigger_type`, `is_active`) VALUES
(2, 4, 30, 'pre2h', 1),
(4, 5, 30, 'pre2h', 1),
(6, 6, 30, 'pre2h', 1),
(8, 4, 50, 'post15m', 1),
(9, 5, 50, 'post15m', 1),
(10, 6, 50, 'post15m', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblmovies`
--

CREATE TABLE `tblmovies` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `duration_min` smallint(5) UNSIGNED NOT NULL DEFAULT 90,
  `poster_url` varchar(255) DEFAULT NULL,
  `trailer_url` varchar(255) DEFAULT NULL,
  `status` enum('showing','upcoming','ended') NOT NULL DEFAULT 'upcoming',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblmovies`
--

INSERT INTO `tblmovies` (`id`, `title`, `description`, `genre`, `duration_min`, `poster_url`, `trailer_url`, `status`, `created_at`) VALUES
(1, 'Avengers: Doomsday', 'Trận chiến cuối cùng của các siêu anh hùng', 'Hành động, Khoa học viễn tưởng', 150, 'poster_69e6fd213df56.webp', '', 'showing', '2026-04-21 10:42:13'),
(3, 'Mission Impossible 8', 'Ethan Hunt trở lại với nhiệm vụ không thể', 'Hành động, Gián điệp', 140, NULL, '', 'showing', '2026-04-21 10:42:13'),
(4, 'Frozen 2', 'Lạnh giá con tym', 'Hoạt hình', 120, 'poster_69e6fc43870ca.jpg', 'https://youtu.be/Zi4LMpSDccc?si=aHgYk_0LDqmIRF4M', 'showing', '2026-04-21 11:25:39');

-- --------------------------------------------------------

--
-- Table structure for table `tblpointslog`
--

CREATE TABLE `tblpointslog` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED DEFAULT NULL,
  `points_delta` int(11) NOT NULL,
  `reason` varchar(120) NOT NULL DEFAULT '',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblpointslog`
--

INSERT INTO `tblpointslog` (`id`, `user_id`, `booking_id`, `points_delta`, `reason`, `created_at`) VALUES
(1, 4, 1, 35, 'Đặt ghế vip - Booking #1', '2026-04-23 14:32:12'),
(2, 4, 1, 35, 'Đặt ghế vip - Booking #1', '2026-04-23 14:32:12'),
(3, 4, 1, 35, 'Đặt ghế vip - Booking #1', '2026-04-23 14:32:12'),
(4, 4, 1, 35, 'Đặt ghế vip - Booking #1', '2026-04-23 14:32:12'),
(5, 4, 2, 35, 'Đặt ghế vip - Booking #2', '2026-04-23 16:18:41'),
(6, 4, 2, 35, 'Đặt ghế vip - Booking #2', '2026-04-23 16:18:41'),
(7, 4, 2, 35, 'Đặt ghế vip - Booking #2', '2026-04-23 16:18:41'),
(8, 4, 2, 35, 'Đặt ghế vip - Booking #2', '2026-04-23 16:18:41'),
(9, 4, 2, 35, 'Đặt ghế vip - Booking #2', '2026-04-23 16:18:41'),
(10, 4, 2, 35, 'Đặt ghế vip - Booking #2', '2026-04-23 16:18:41'),
(11, 4, 2, 35, 'Đặt ghế vip - Booking #2', '2026-04-23 16:18:41'),
(12, 4, 2, 35, 'Đặt ghế vip - Booking #2', '2026-04-23 16:18:41'),
(13, 3, 3, 35, 'Đặt ghế vip - Booking #3', '2026-04-30 13:12:33'),
(14, 3, 3, 35, 'Đặt ghế vip - Booking #3', '2026-04-30 13:12:33'),
(15, 3, 3, 35, 'Đặt ghế vip - Booking #3', '2026-04-30 13:12:33'),
(16, 3, 4, 10, 'Đặt ghế standard - Booking #4', '2026-04-30 13:21:33'),
(17, 3, 4, 35, 'Đặt ghế vip - Booking #4', '2026-04-30 13:21:33'),
(18, 3, 4, 35, 'Đặt ghế vip - Booking #4', '2026-04-30 13:21:33'),
(19, 3, 4, 35, 'Đặt ghế vip - Booking #4', '2026-04-30 13:21:33'),
(20, 3, 5, 10, 'Đặt ghế standard - Booking #5', '2026-04-30 13:49:54'),
(21, 4, 6, 35, 'Đặt ghế vip - Booking #6', '2026-04-30 14:18:33'),
(22, 4, 6, 35, 'Đặt ghế vip - Booking #6', '2026-04-30 14:18:33'),
(23, 4, 6, 35, 'Đặt ghế vip - Booking #6', '2026-04-30 14:18:33'),
(24, 4, 6, 10, 'Đặt ghế standard - Booking #6', '2026-04-30 14:18:33'),
(25, 4, 6, 10, 'Đặt ghế standard - Booking #6', '2026-04-30 14:18:33'),
(26, 3, 7, 10, 'Đặt ghế standard - Booking #7', '2026-04-30 14:18:46'),
(27, 3, 7, 35, 'Đặt ghế vip - Booking #7', '2026-04-30 14:18:46'),
(28, 3, 7, 20, 'Đặt ghế couple - Booking #7', '2026-04-30 14:18:46'),
(29, 3, 7, 20, 'Đặt ghế couple - Booking #7', '2026-04-30 14:18:46'),
(30, 4, 8, 10, 'Đặt ghế standard - Booking #8', '2026-04-30 14:20:23'),
(31, 3, 9, 10, 'Đặt ghế standard - Booking #9', '2026-04-30 14:34:49'),
(32, 3, 10, 10, 'Đặt ghế standard - Booking #10', '2026-04-30 14:41:42'),
(33, 3, 10, 10, 'Đặt ghế standard - Booking #10', '2026-04-30 14:41:42'),
(34, 3, 10, 10, 'Đặt ghế standard - Booking #10', '2026-04-30 14:41:42'),
(35, 3, 10, 10, 'Đặt ghế standard - Booking #10', '2026-04-30 14:41:42'),
(36, 3, 10, 10, 'Đặt ghế standard - Booking #10', '2026-04-30 14:41:42'),
(37, 3, 10, 10, 'Đặt ghế standard - Booking #10', '2026-04-30 14:41:42'),
(38, 3, 10, 10, 'Đặt ghế standard - Booking #10', '2026-04-30 14:41:42'),
(39, 3, 11, 35, 'Đặt ghế vip - Booking #11', '2026-04-30 14:48:28'),
(40, 3, 11, 35, 'Đặt ghế vip - Booking #11', '2026-04-30 14:48:28'),
(41, 3, 11, 35, 'Đặt ghế vip - Booking #11', '2026-04-30 14:48:28'),
(42, 3, 11, 10, 'Đặt ghế standard - Booking #11', '2026-04-30 14:48:28'),
(43, 4, 12, 10, 'Đặt ghế standard - Booking #12', '2026-05-04 13:01:53'),
(44, 4, 12, 10, 'Đặt ghế standard - Booking #12', '2026-05-04 13:01:53'),
(45, 4, 12, 35, 'Đặt ghế vip - Booking #12', '2026-05-04 13:01:53'),
(46, 4, 12, 35, 'Đặt ghế vip - Booking #12', '2026-05-04 13:01:53'),
(47, 4, 12, 35, 'Đặt ghế vip - Booking #12', '2026-05-04 13:01:53'),
(48, 4, 12, 35, 'Đặt ghế vip - Booking #12', '2026-05-04 13:01:53'),
(49, 4, 12, 10, 'Đặt ghế standard - Booking #12', '2026-05-04 13:01:53'),
(50, 3, 13, 10, 'Đặt ghế standard - Booking #13', '2026-05-04 13:38:09'),
(51, 3, 13, 35, 'Đặt ghế vip - Booking #13', '2026-05-04 13:38:09'),
(52, 3, 13, 35, 'Đặt ghế vip - Booking #13', '2026-05-04 13:38:09'),
(53, 3, 13, 35, 'Đặt ghế vip - Booking #13', '2026-05-04 13:38:09'),
(54, 3, 13, 35, 'Đặt ghế vip - Booking #13', '2026-05-04 13:38:09'),
(55, 4, 14, 35, 'Đặt ghế vip - Booking #14', '2026-05-04 13:39:21'),
(56, 4, 14, 35, 'Đặt ghế vip - Booking #14', '2026-05-04 13:39:21');

-- --------------------------------------------------------

--
-- Table structure for table `tblprices`
--

CREATE TABLE `tblprices` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `seat_type` enum('standard','vip','couple') NOT NULL,
  `price` decimal(12,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblprices`
--

INSERT INTO `tblprices` (`id`, `seat_type`, `price`) VALUES
(1, 'standard', 90000),
(2, 'vip', 130000),
(3, 'couple', 200000);

-- --------------------------------------------------------

--
-- Table structure for table `tblrooms`
--

CREATE TABLE `tblrooms` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `floor` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblrooms`
--

INSERT INTO `tblrooms` (`id`, `floor`, `name`) VALUES
(1, 1, 'Phòng 1'),
(2, 2, 'Phòng 2'),
(3, 3, 'Phòng 3');

-- --------------------------------------------------------

--
-- Table structure for table `tblseats`
--

CREATE TABLE `tblseats` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `room_id` tinyint(3) UNSIGNED NOT NULL,
  `row` char(1) NOT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL,
  `type` enum('standard','vip','couple') NOT NULL DEFAULT 'standard'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblseats`
--

INSERT INTO `tblseats` (`id`, `room_id`, `row`, `number`, `type`) VALUES
(1, 1, 'A', 1, 'standard'),
(2, 1, 'A', 2, 'standard'),
(3, 1, 'A', 3, 'standard'),
(4, 1, 'A', 4, 'standard'),
(5, 1, 'A', 5, 'standard'),
(6, 1, 'A', 6, 'standard'),
(7, 1, 'A', 7, 'standard'),
(8, 1, 'A', 8, 'standard'),
(9, 1, 'B', 1, 'standard'),
(10, 1, 'B', 2, 'standard'),
(11, 1, 'B', 3, 'standard'),
(12, 1, 'B', 4, 'standard'),
(13, 1, 'B', 5, 'standard'),
(14, 1, 'B', 6, 'standard'),
(15, 1, 'B', 7, 'standard'),
(16, 1, 'B', 8, 'standard'),
(17, 1, 'C', 1, 'standard'),
(18, 1, 'C', 2, 'standard'),
(19, 1, 'C', 3, 'vip'),
(20, 1, 'C', 4, 'vip'),
(21, 1, 'C', 5, 'vip'),
(22, 1, 'C', 6, 'vip'),
(23, 1, 'C', 7, 'standard'),
(24, 1, 'C', 8, 'standard'),
(25, 1, 'D', 1, 'standard'),
(26, 1, 'D', 2, 'standard'),
(27, 1, 'D', 3, 'vip'),
(28, 1, 'D', 4, 'vip'),
(29, 1, 'D', 5, 'vip'),
(30, 1, 'D', 6, 'vip'),
(31, 1, 'D', 7, 'standard'),
(32, 1, 'D', 8, 'standard'),
(33, 1, 'E', 1, 'standard'),
(34, 1, 'E', 2, 'standard'),
(35, 1, 'E', 3, 'vip'),
(36, 1, 'E', 4, 'vip'),
(37, 1, 'E', 5, 'vip'),
(38, 1, 'E', 6, 'vip'),
(39, 1, 'E', 7, 'standard'),
(40, 1, 'E', 8, 'standard'),
(41, 1, 'F', 1, 'standard'),
(42, 1, 'F', 2, 'standard'),
(43, 1, 'F', 3, 'standard'),
(44, 1, 'F', 4, 'standard'),
(45, 1, 'F', 5, 'standard'),
(46, 1, 'F', 6, 'standard'),
(47, 1, 'F', 7, 'standard'),
(48, 1, 'F', 8, 'standard'),
(49, 1, 'G', 1, 'couple'),
(50, 1, 'G', 2, 'couple'),
(51, 1, 'G', 3, 'couple'),
(52, 1, 'G', 4, 'couple'),
(53, 2, 'A', 1, 'standard'),
(54, 2, 'A', 2, 'standard'),
(55, 2, 'A', 3, 'standard'),
(56, 2, 'A', 4, 'standard'),
(57, 2, 'A', 5, 'standard'),
(58, 2, 'A', 6, 'standard'),
(59, 2, 'A', 7, 'standard'),
(60, 2, 'A', 8, 'standard'),
(61, 2, 'B', 1, 'standard'),
(62, 2, 'B', 2, 'standard'),
(63, 2, 'B', 3, 'standard'),
(64, 2, 'B', 4, 'standard'),
(65, 2, 'B', 5, 'standard'),
(66, 2, 'B', 6, 'standard'),
(67, 2, 'B', 7, 'standard'),
(68, 2, 'B', 8, 'standard'),
(69, 2, 'C', 1, 'standard'),
(70, 2, 'C', 2, 'standard'),
(71, 2, 'C', 3, 'vip'),
(72, 2, 'C', 4, 'vip'),
(73, 2, 'C', 5, 'vip'),
(74, 2, 'C', 6, 'vip'),
(75, 2, 'C', 7, 'standard'),
(76, 2, 'C', 8, 'standard'),
(77, 2, 'D', 1, 'standard'),
(78, 2, 'D', 2, 'standard'),
(79, 2, 'D', 3, 'vip'),
(80, 2, 'D', 4, 'vip'),
(81, 2, 'D', 5, 'vip'),
(82, 2, 'D', 6, 'vip'),
(83, 2, 'D', 7, 'standard'),
(84, 2, 'D', 8, 'standard'),
(85, 2, 'E', 1, 'standard'),
(86, 2, 'E', 2, 'standard'),
(87, 2, 'E', 3, 'vip'),
(88, 2, 'E', 4, 'vip'),
(89, 2, 'E', 5, 'vip'),
(90, 2, 'E', 6, 'vip'),
(91, 2, 'E', 7, 'standard'),
(92, 2, 'E', 8, 'standard'),
(93, 2, 'F', 1, 'standard'),
(94, 2, 'F', 2, 'standard'),
(95, 2, 'F', 3, 'standard'),
(96, 2, 'F', 4, 'standard'),
(97, 2, 'F', 5, 'standard'),
(98, 2, 'F', 6, 'standard'),
(99, 2, 'F', 7, 'standard'),
(100, 2, 'F', 8, 'standard'),
(101, 2, 'G', 1, 'couple'),
(102, 2, 'G', 2, 'couple'),
(103, 2, 'G', 3, 'couple'),
(104, 2, 'G', 4, 'couple'),
(105, 3, 'A', 1, 'standard'),
(106, 3, 'A', 2, 'standard'),
(107, 3, 'A', 3, 'standard'),
(108, 3, 'A', 4, 'standard'),
(109, 3, 'A', 5, 'standard'),
(110, 3, 'A', 6, 'standard'),
(111, 3, 'A', 7, 'standard'),
(112, 3, 'A', 8, 'standard'),
(113, 3, 'B', 1, 'standard'),
(114, 3, 'B', 2, 'standard'),
(115, 3, 'B', 3, 'standard'),
(116, 3, 'B', 4, 'standard'),
(117, 3, 'B', 5, 'standard'),
(118, 3, 'B', 6, 'standard'),
(119, 3, 'B', 7, 'standard'),
(120, 3, 'B', 8, 'standard'),
(121, 3, 'C', 1, 'standard'),
(122, 3, 'C', 2, 'standard'),
(123, 3, 'C', 3, 'vip'),
(124, 3, 'C', 4, 'vip'),
(125, 3, 'C', 5, 'vip'),
(126, 3, 'C', 6, 'vip'),
(127, 3, 'C', 7, 'standard'),
(128, 3, 'C', 8, 'standard'),
(129, 3, 'D', 1, 'standard'),
(130, 3, 'D', 2, 'standard'),
(131, 3, 'D', 3, 'vip'),
(132, 3, 'D', 4, 'vip'),
(133, 3, 'D', 5, 'vip'),
(134, 3, 'D', 6, 'vip'),
(135, 3, 'D', 7, 'standard'),
(136, 3, 'D', 8, 'standard'),
(137, 3, 'E', 1, 'standard'),
(138, 3, 'E', 2, 'standard'),
(139, 3, 'E', 3, 'vip'),
(140, 3, 'E', 4, 'vip'),
(141, 3, 'E', 5, 'vip'),
(142, 3, 'E', 6, 'vip'),
(143, 3, 'E', 7, 'standard'),
(144, 3, 'E', 8, 'standard'),
(145, 3, 'F', 1, 'standard'),
(146, 3, 'F', 2, 'standard'),
(147, 3, 'F', 3, 'standard'),
(148, 3, 'F', 4, 'standard'),
(149, 3, 'F', 5, 'standard'),
(150, 3, 'F', 6, 'standard'),
(151, 3, 'F', 7, 'standard'),
(152, 3, 'F', 8, 'standard'),
(153, 3, 'G', 1, 'couple'),
(154, 3, 'G', 2, 'couple'),
(155, 3, 'G', 3, 'couple'),
(156, 3, 'G', 4, 'couple');

-- --------------------------------------------------------

--
-- Table structure for table `tblseatstatus`
--

CREATE TABLE `tblseatstatus` (
  `id` int(10) UNSIGNED NOT NULL,
  `show_id` int(10) UNSIGNED NOT NULL,
  `seat_id` smallint(5) UNSIGNED NOT NULL,
  `status` enum('available','held','booked') NOT NULL DEFAULT 'available',
  `version_number` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `held_until` datetime DEFAULT NULL,
  `held_by` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblseatstatus`
--

INSERT INTO `tblseatstatus` (`id`, `show_id`, `seat_id`, `status`, `version_number`, `held_until`, `held_by`) VALUES
(1, 1, 1, 'available', 0, NULL, NULL),
(2, 1, 2, 'available', 0, NULL, NULL),
(3, 1, 3, 'available', 0, NULL, NULL),
(4, 1, 4, 'available', 0, NULL, NULL),
(5, 1, 5, 'available', 0, NULL, NULL),
(6, 1, 6, 'available', 0, NULL, NULL),
(7, 1, 7, 'available', 0, NULL, NULL),
(8, 1, 8, 'available', 0, NULL, NULL),
(9, 1, 9, 'available', 0, NULL, NULL),
(10, 1, 10, 'available', 2, NULL, NULL),
(11, 1, 11, 'available', 2, NULL, NULL),
(12, 1, 12, 'available', 0, NULL, NULL),
(13, 1, 13, 'available', 0, NULL, NULL),
(14, 1, 14, 'available', 0, NULL, NULL),
(15, 1, 15, 'available', 0, NULL, NULL),
(16, 1, 16, 'available', 0, NULL, NULL),
(17, 1, 17, 'available', 2, NULL, NULL),
(18, 1, 18, 'available', 4, NULL, NULL),
(19, 1, 19, 'booked', 9, NULL, NULL),
(20, 1, 20, 'booked', 9, NULL, NULL),
(21, 1, 21, 'booked', 9, NULL, NULL),
(22, 1, 22, 'booked', 9, NULL, NULL),
(23, 1, 23, 'available', 0, NULL, NULL),
(24, 1, 24, 'available', 0, NULL, NULL),
(25, 1, 25, 'available', 0, NULL, NULL),
(26, 1, 26, 'available', 0, NULL, NULL),
(27, 1, 27, 'booked', 9, NULL, NULL),
(28, 1, 28, 'booked', 9, NULL, NULL),
(29, 1, 29, 'booked', 9, NULL, NULL),
(30, 1, 30, 'booked', 9, NULL, NULL),
(31, 1, 31, 'available', 0, NULL, NULL),
(32, 1, 32, 'available', 0, NULL, NULL),
(33, 1, 33, 'available', 0, NULL, NULL),
(34, 1, 34, 'available', 0, NULL, NULL),
(35, 1, 35, 'booked', 3, NULL, NULL),
(36, 1, 36, 'booked', 3, NULL, NULL),
(37, 1, 37, 'booked', 3, NULL, NULL),
(38, 1, 38, 'booked', 3, NULL, NULL),
(39, 1, 39, 'available', 0, NULL, NULL),
(40, 1, 40, 'available', 0, NULL, NULL),
(41, 1, 41, 'available', 0, NULL, NULL),
(42, 1, 42, 'available', 0, NULL, NULL),
(43, 1, 43, 'available', 0, NULL, NULL),
(44, 1, 44, 'available', 0, NULL, NULL),
(45, 1, 45, 'available', 0, NULL, NULL),
(46, 1, 46, 'available', 0, NULL, NULL),
(47, 1, 47, 'available', 0, NULL, NULL),
(48, 1, 48, 'available', 0, NULL, NULL),
(49, 1, 49, 'available', 0, NULL, NULL),
(50, 1, 50, 'available', 0, NULL, NULL),
(51, 1, 51, 'available', 0, NULL, NULL),
(52, 1, 52, 'available', 0, NULL, NULL),
(53, 2, 1, 'available', 2, NULL, NULL),
(54, 2, 2, 'available', 2, NULL, NULL),
(55, 2, 3, 'available', 2, NULL, NULL),
(56, 2, 4, 'available', 2, NULL, NULL),
(57, 2, 5, 'available', 2, NULL, NULL),
(58, 2, 6, 'available', 2, NULL, NULL),
(59, 2, 7, 'available', 2, NULL, NULL),
(60, 2, 8, 'available', 2, NULL, NULL),
(61, 2, 9, 'available', 0, NULL, NULL),
(62, 2, 10, 'available', 2, NULL, NULL),
(63, 2, 11, 'booked', 1, NULL, NULL),
(64, 2, 12, 'available', 0, NULL, NULL),
(65, 2, 13, 'booked', 1, NULL, NULL),
(66, 2, 14, 'available', 10, NULL, NULL),
(67, 2, 15, 'available', 0, NULL, NULL),
(68, 2, 16, 'available', 0, NULL, NULL),
(69, 2, 17, 'available', 2, NULL, NULL),
(70, 2, 18, 'booked', 43, NULL, NULL),
(71, 2, 19, 'booked', 41, NULL, NULL),
(72, 2, 20, 'booked', 25, NULL, NULL),
(73, 2, 21, 'booked', 15, NULL, NULL),
(74, 2, 22, 'booked', 15, NULL, NULL),
(75, 2, 23, 'booked', 3, NULL, NULL),
(76, 2, 24, 'booked', 1, NULL, NULL),
(77, 2, 25, 'available', 0, NULL, NULL),
(78, 2, 26, 'booked', 6, NULL, NULL),
(79, 2, 27, 'booked', 6, NULL, NULL),
(80, 2, 28, 'booked', 4, NULL, NULL),
(81, 2, 29, 'booked', 4, NULL, NULL),
(82, 2, 30, 'available', 6, NULL, NULL),
(83, 2, 31, 'booked', 1, NULL, NULL),
(84, 2, 32, 'available', 0, NULL, NULL),
(85, 2, 33, 'available', 0, NULL, NULL),
(86, 2, 34, 'available', 0, NULL, NULL),
(87, 2, 35, 'booked', 7, NULL, NULL),
(88, 2, 36, 'booked', 7, NULL, NULL),
(89, 2, 37, 'booked', 7, NULL, NULL),
(90, 2, 38, 'available', 2, NULL, NULL),
(91, 2, 39, 'available', 4, NULL, NULL),
(92, 2, 40, 'available', 2, NULL, NULL),
(93, 2, 41, 'booked', 3, NULL, NULL),
(94, 2, 42, 'booked', 13, NULL, NULL),
(95, 2, 43, 'booked', 13, NULL, NULL),
(96, 2, 44, 'booked', 13, NULL, NULL),
(97, 2, 45, 'booked', 13, NULL, NULL),
(98, 2, 46, 'booked', 13, NULL, NULL),
(99, 2, 47, 'booked', 3, NULL, NULL),
(100, 2, 48, 'available', 4, NULL, NULL),
(101, 2, 49, 'booked', 1, NULL, NULL),
(102, 2, 50, 'booked', 1, NULL, NULL),
(103, 2, 51, 'available', 0, NULL, NULL),
(104, 2, 52, 'available', 0, NULL, NULL),
(116, 3, 1, 'available', 0, NULL, NULL),
(117, 3, 2, 'available', 0, NULL, NULL),
(118, 3, 3, 'available', 0, NULL, NULL),
(119, 3, 4, 'available', 0, NULL, NULL),
(120, 3, 5, 'available', 0, NULL, NULL),
(121, 3, 6, 'available', 0, NULL, NULL),
(122, 3, 7, 'available', 0, NULL, NULL),
(123, 3, 8, 'available', 0, NULL, NULL),
(124, 3, 9, 'available', 0, NULL, NULL),
(125, 3, 10, 'available', 0, NULL, NULL),
(126, 3, 11, 'available', 0, NULL, NULL),
(127, 3, 12, 'available', 0, NULL, NULL),
(128, 3, 13, 'available', 0, NULL, NULL),
(129, 3, 14, 'available', 0, NULL, NULL),
(130, 3, 15, 'available', 0, NULL, NULL),
(131, 3, 16, 'available', 0, NULL, NULL),
(132, 3, 17, 'available', 0, NULL, NULL),
(133, 3, 18, 'available', 0, NULL, NULL),
(134, 3, 19, 'available', 0, NULL, NULL),
(135, 3, 20, 'available', 0, NULL, NULL),
(136, 3, 21, 'available', 0, NULL, NULL),
(137, 3, 22, 'available', 0, NULL, NULL),
(138, 3, 23, 'available', 0, NULL, NULL),
(139, 3, 24, 'available', 0, NULL, NULL),
(140, 3, 25, 'available', 0, NULL, NULL),
(141, 3, 26, 'available', 0, NULL, NULL),
(142, 3, 27, 'booked', 1, NULL, NULL),
(143, 3, 28, 'booked', 1, NULL, NULL),
(144, 3, 29, 'available', 0, NULL, NULL),
(145, 3, 30, 'available', 0, NULL, NULL),
(146, 3, 31, 'available', 0, NULL, NULL),
(147, 3, 32, 'available', 0, NULL, NULL),
(148, 3, 33, 'available', 0, NULL, NULL),
(149, 3, 34, 'available', 0, NULL, NULL),
(150, 3, 35, 'available', 0, NULL, NULL),
(151, 3, 36, 'available', 0, NULL, NULL),
(152, 3, 37, 'booked', 1, NULL, NULL),
(153, 3, 38, 'available', 2, NULL, NULL),
(154, 3, 39, 'booked', 1, NULL, NULL),
(155, 3, 40, 'available', 0, NULL, NULL),
(156, 3, 41, 'available', 0, NULL, NULL),
(157, 3, 42, 'available', 0, NULL, NULL),
(158, 3, 43, 'available', 0, NULL, NULL),
(159, 3, 44, 'available', 0, NULL, NULL),
(160, 3, 45, 'available', 0, NULL, NULL),
(161, 3, 46, 'available', 0, NULL, NULL),
(162, 3, 47, 'available', 0, NULL, NULL),
(163, 3, 48, 'available', 0, NULL, NULL),
(164, 3, 49, 'available', 0, NULL, NULL),
(165, 3, 50, 'available', 0, NULL, NULL),
(166, 3, 51, 'available', 0, NULL, NULL),
(167, 3, 52, 'available', 0, NULL, NULL),
(168, 4, 53, 'available', 0, NULL, NULL),
(169, 4, 54, 'available', 0, NULL, NULL),
(170, 4, 55, 'available', 0, NULL, NULL),
(171, 4, 56, 'available', 0, NULL, NULL),
(172, 4, 57, 'available', 0, NULL, NULL),
(173, 4, 58, 'available', 0, NULL, NULL),
(174, 4, 59, 'available', 0, NULL, NULL),
(175, 4, 60, 'available', 0, NULL, NULL),
(176, 4, 61, 'available', 0, NULL, NULL),
(177, 4, 62, 'available', 0, NULL, NULL),
(178, 4, 63, 'available', 0, NULL, NULL),
(179, 4, 64, 'available', 0, NULL, NULL),
(180, 4, 65, 'available', 0, NULL, NULL),
(181, 4, 66, 'available', 0, NULL, NULL),
(182, 4, 67, 'available', 0, NULL, NULL),
(183, 4, 68, 'available', 0, NULL, NULL),
(184, 4, 69, 'available', 4, NULL, NULL),
(185, 4, 70, 'available', 4, NULL, NULL),
(186, 4, 71, 'available', 4, NULL, NULL),
(187, 4, 72, 'available', 4, NULL, NULL),
(188, 4, 73, 'available', 4, NULL, NULL),
(189, 4, 74, 'available', 4, NULL, NULL),
(190, 4, 75, 'available', 4, NULL, NULL),
(191, 4, 76, 'available', 0, NULL, NULL),
(192, 4, 77, 'booked', 3, NULL, NULL),
(193, 4, 78, 'booked', 3, NULL, NULL),
(194, 4, 79, 'booked', 3, NULL, NULL),
(195, 4, 80, 'booked', 3, NULL, NULL),
(196, 4, 81, 'booked', 5, NULL, NULL),
(197, 4, 82, 'booked', 3, NULL, NULL),
(198, 4, 83, 'booked', 5, NULL, NULL),
(199, 4, 84, 'available', 0, NULL, NULL),
(200, 4, 85, 'available', 0, NULL, NULL),
(201, 4, 86, 'available', 2, NULL, NULL),
(202, 4, 87, 'available', 2, NULL, NULL),
(203, 4, 88, 'available', 2, NULL, NULL),
(204, 4, 89, 'available', 2, NULL, NULL),
(205, 4, 90, 'available', 2, NULL, NULL),
(206, 4, 91, 'available', 0, NULL, NULL),
(207, 4, 92, 'available', 0, NULL, NULL),
(208, 4, 93, 'available', 0, NULL, NULL),
(209, 4, 94, 'available', 0, NULL, NULL),
(210, 4, 95, 'available', 0, NULL, NULL),
(211, 4, 96, 'available', 0, NULL, NULL),
(212, 4, 97, 'available', 0, NULL, NULL),
(213, 4, 98, 'available', 0, NULL, NULL),
(214, 4, 99, 'available', 0, NULL, NULL),
(215, 4, 100, 'available', 0, NULL, NULL),
(216, 4, 101, 'available', 0, NULL, NULL),
(217, 4, 102, 'available', 0, NULL, NULL),
(218, 4, 103, 'available', 0, NULL, NULL),
(219, 4, 104, 'available', 0, NULL, NULL),
(231, 5, 53, 'available', 0, NULL, NULL),
(232, 5, 54, 'available', 0, NULL, NULL),
(233, 5, 55, 'available', 0, NULL, NULL),
(234, 5, 56, 'available', 0, NULL, NULL),
(235, 5, 57, 'available', 0, NULL, NULL),
(236, 5, 58, 'available', 0, NULL, NULL),
(237, 5, 59, 'available', 0, NULL, NULL),
(238, 5, 60, 'available', 0, NULL, NULL),
(239, 5, 61, 'available', 0, NULL, NULL),
(240, 5, 62, 'available', 0, NULL, NULL),
(241, 5, 63, 'available', 0, NULL, NULL),
(242, 5, 64, 'available', 0, NULL, NULL),
(243, 5, 65, 'available', 0, NULL, NULL),
(244, 5, 66, 'available', 0, NULL, NULL),
(245, 5, 67, 'available', 0, NULL, NULL),
(246, 5, 68, 'available', 0, NULL, NULL),
(247, 5, 69, 'available', 0, NULL, NULL),
(248, 5, 70, 'available', 0, NULL, NULL),
(249, 5, 71, 'available', 0, NULL, NULL),
(250, 5, 72, 'available', 0, NULL, NULL),
(251, 5, 73, 'available', 0, NULL, NULL),
(252, 5, 74, 'available', 0, NULL, NULL),
(253, 5, 75, 'available', 0, NULL, NULL),
(254, 5, 76, 'available', 0, NULL, NULL),
(255, 5, 77, 'available', 0, NULL, NULL),
(256, 5, 78, 'available', 0, NULL, NULL),
(257, 5, 79, 'available', 0, NULL, NULL),
(258, 5, 80, 'available', 0, NULL, NULL),
(259, 5, 81, 'available', 0, NULL, NULL),
(260, 5, 82, 'available', 0, NULL, NULL),
(261, 5, 83, 'available', 0, NULL, NULL),
(262, 5, 84, 'available', 0, NULL, NULL),
(263, 5, 85, 'available', 0, NULL, NULL),
(264, 5, 86, 'available', 0, NULL, NULL),
(265, 5, 87, 'available', 0, NULL, NULL),
(266, 5, 88, 'available', 0, NULL, NULL),
(267, 5, 89, 'available', 0, NULL, NULL),
(268, 5, 90, 'available', 0, NULL, NULL),
(269, 5, 91, 'available', 0, NULL, NULL),
(270, 5, 92, 'available', 0, NULL, NULL),
(271, 5, 93, 'available', 0, NULL, NULL),
(272, 5, 94, 'available', 0, NULL, NULL),
(273, 5, 95, 'available', 0, NULL, NULL),
(274, 5, 96, 'available', 0, NULL, NULL),
(275, 5, 97, 'available', 0, NULL, NULL),
(276, 5, 98, 'available', 0, NULL, NULL),
(277, 5, 99, 'available', 0, NULL, NULL),
(278, 5, 100, 'available', 0, NULL, NULL),
(279, 5, 101, 'available', 0, NULL, NULL),
(280, 5, 102, 'available', 0, NULL, NULL),
(281, 5, 103, 'available', 0, NULL, NULL),
(282, 5, 104, 'available', 0, NULL, NULL),
(294, 4, 34, 'booked', 3, NULL, NULL),
(295, 4, 44, 'available', 2, NULL, NULL),
(296, 4, 35, 'booked', 5, NULL, NULL),
(297, 4, 45, 'available', 2, NULL, NULL),
(298, 4, 20, 'available', 2, NULL, NULL),
(299, 4, 42, 'available', 2, NULL, NULL),
(300, 4, 36, 'booked', 3, NULL, NULL),
(301, 4, 27, 'available', 10, NULL, NULL),
(302, 4, 28, 'available', 8, NULL, NULL),
(303, 6, 105, 'available', 0, NULL, NULL),
(304, 6, 106, 'available', 0, NULL, NULL),
(305, 6, 107, 'available', 0, NULL, NULL),
(306, 6, 108, 'available', 0, NULL, NULL),
(307, 6, 109, 'available', 0, NULL, NULL),
(308, 6, 110, 'available', 0, NULL, NULL),
(309, 6, 111, 'available', 0, NULL, NULL),
(310, 6, 112, 'available', 0, NULL, NULL),
(311, 6, 113, 'available', 0, NULL, NULL),
(312, 6, 114, 'available', 0, NULL, NULL),
(313, 6, 115, 'available', 0, NULL, NULL),
(314, 6, 116, 'available', 0, NULL, NULL),
(315, 6, 117, 'available', 0, NULL, NULL),
(316, 6, 118, 'available', 0, NULL, NULL),
(317, 6, 119, 'available', 0, NULL, NULL),
(318, 6, 120, 'available', 0, NULL, NULL),
(319, 6, 121, 'available', 0, NULL, NULL),
(320, 6, 122, 'available', 0, NULL, NULL),
(321, 6, 123, 'available', 0, NULL, NULL),
(322, 6, 124, 'booked', 1, NULL, NULL),
(323, 6, 125, 'booked', 1, NULL, NULL),
(324, 6, 126, 'available', 0, NULL, NULL),
(325, 6, 127, 'available', 0, NULL, NULL),
(326, 6, 128, 'available', 0, NULL, NULL),
(327, 6, 129, 'available', 0, NULL, NULL),
(328, 6, 130, 'available', 0, NULL, NULL),
(329, 6, 131, 'available', 0, NULL, NULL),
(330, 6, 132, 'available', 0, NULL, NULL),
(331, 6, 133, 'available', 0, NULL, NULL),
(332, 6, 134, 'available', 0, NULL, NULL),
(333, 6, 135, 'available', 0, NULL, NULL),
(334, 6, 136, 'available', 0, NULL, NULL),
(335, 6, 137, 'available', 0, NULL, NULL),
(336, 6, 138, 'available', 0, NULL, NULL),
(337, 6, 139, 'available', 2, NULL, NULL),
(338, 6, 140, 'available', 0, NULL, NULL),
(339, 6, 141, 'available', 0, NULL, NULL),
(340, 6, 142, 'available', 0, NULL, NULL),
(341, 6, 143, 'available', 0, NULL, NULL),
(342, 6, 144, 'available', 0, NULL, NULL),
(343, 6, 145, 'available', 0, NULL, NULL),
(344, 6, 146, 'available', 0, NULL, NULL),
(345, 6, 147, 'available', 0, NULL, NULL),
(346, 6, 148, 'available', 0, NULL, NULL),
(347, 6, 149, 'available', 0, NULL, NULL),
(348, 6, 150, 'available', 0, NULL, NULL),
(349, 6, 151, 'available', 0, NULL, NULL),
(350, 6, 152, 'available', 0, NULL, NULL),
(351, 6, 153, 'available', 0, NULL, NULL),
(352, 6, 154, 'available', 0, NULL, NULL),
(353, 6, 155, 'available', 0, NULL, NULL),
(354, 6, 156, 'available', 0, NULL, NULL),
(366, 4, 10, 'available', 2, NULL, NULL),
(367, 4, 5, 'available', 2, NULL, NULL),
(368, 4, 37, 'booked', 1, NULL, NULL),
(369, 4, 38, 'booked', 1, NULL, NULL),
(370, 4, 25, 'available', 2, NULL, NULL),
(371, 4, 26, 'available', 4, NULL, NULL),
(372, 4, 29, 'available', 4, NULL, NULL),
(373, 4, 30, 'available', 4, NULL, NULL),
(374, 4, 31, 'available', 4, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblshows`
--

CREATE TABLE `tblshows` (
  `id` int(10) UNSIGNED NOT NULL,
  `movie_id` int(10) UNSIGNED NOT NULL,
  `room_id` tinyint(3) UNSIGNED NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblshows`
--

INSERT INTO `tblshows` (`id`, `movie_id`, `room_id`, `start_time`, `end_time`) VALUES
(1, 1, 1, '2026-04-23 14:32:00', '2026-04-23 17:02:00'),
(2, 4, 1, '2026-04-30 15:30:00', '2026-04-30 17:30:00'),
(3, 1, 1, '2026-04-30 17:30:00', '2026-04-30 20:00:00'),
(4, 4, 1, '2026-05-04 13:00:00', '2026-05-04 15:00:00'),
(5, 3, 2, '2026-05-04 12:00:00', '2026-05-04 14:20:00'),
(6, 3, 3, '2026-05-04 13:19:00', '2026-05-04 15:39:00');

-- --------------------------------------------------------

--
-- Table structure for table `tblusers`
--

CREATE TABLE `tblusers` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(120) NOT NULL DEFAULT '',
  `role` enum('customer','admin') NOT NULL DEFAULT 'customer',
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verify_token` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `total_points` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblusers`
--

INSERT INTO `tblusers` (`id`, `email`, `password_hash`, `full_name`, `role`, `email_verified`, `verify_token`, `reset_token`, `reset_expires`, `total_points`, `created_at`) VALUES
(1, 'admin@minicine.vn', '$2y$12$2M.duKrvcXSp4otbq53q8Oomfbnu9NXGlmiymIBFezMKuKRnrswtG', 'Quản trị viên', 'admin', 1, NULL, NULL, NULL, 0, '2026-04-21 10:42:12'),
(3, 'huong.ngocc26@gmail.com', '$2y$10$Fq8dkZ8wwkZv1ZFQgfUvqetFxCBnEo7MPAlw2sQZPj1.vdNfNcjvC', 'Huynh Huong', 'customer', 1, NULL, NULL, NULL, 660, '2026-04-21 11:15:54'),
(4, 'cavaca.2614@gmail.com', '$2y$10$v1EUdFmU1xcoDffAYDJFW.7dd4N89bvkod3O5sncZ.3gOuaxSdszu', 'Tuyết Nhung', 'customer', 1, NULL, NULL, NULL, 795, '2026-04-23 14:25:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblbookingitems`
--
ALTER TABLE `tblbookingitems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `seat_id` (`seat_id`);

--
-- Indexes for table `tblbookings`
--
ALTER TABLE `tblbookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `show_id` (`show_id`);

--
-- Indexes for table `tblflashsales`
--
ALTER TABLE `tblflashsales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_show_trigger` (`show_id`,`trigger_type`);

--
-- Indexes for table `tblmovies`
--
ALTER TABLE `tblmovies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblpointslog`
--
ALTER TABLE `tblpointslog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `tblprices`
--
ALTER TABLE `tblprices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seat_type` (`seat_type`);

--
-- Indexes for table `tblrooms`
--
ALTER TABLE `tblrooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblseats`
--
ALTER TABLE `tblseats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_seat` (`room_id`,`row`,`number`);

--
-- Indexes for table `tblseatstatus`
--
ALTER TABLE `tblseatstatus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_show_seat` (`show_id`,`seat_id`),
  ADD KEY `seat_id` (`seat_id`);

--
-- Indexes for table `tblshows`
--
ALTER TABLE `tblshows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `tblusers`
--
ALTER TABLE `tblusers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblbookingitems`
--
ALTER TABLE `tblbookingitems`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `tblbookings`
--
ALTER TABLE `tblbookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tblflashsales`
--
ALTER TABLE `tblflashsales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tblmovies`
--
ALTER TABLE `tblmovies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tblpointslog`
--
ALTER TABLE `tblpointslog`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `tblprices`
--
ALTER TABLE `tblprices`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tblrooms`
--
ALTER TABLE `tblrooms`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tblseats`
--
ALTER TABLE `tblseats`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT for table `tblseatstatus`
--
ALTER TABLE `tblseatstatus`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=375;

--
-- AUTO_INCREMENT for table `tblshows`
--
ALTER TABLE `tblshows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblbookingitems`
--
ALTER TABLE `tblbookingitems`
  ADD CONSTRAINT `tblbookingitems_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `tblbookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblbookingitems_ibfk_2` FOREIGN KEY (`seat_id`) REFERENCES `tblseats` (`id`);

--
-- Constraints for table `tblbookings`
--
ALTER TABLE `tblbookings`
  ADD CONSTRAINT `tblbookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tblusers` (`id`),
  ADD CONSTRAINT `tblbookings_ibfk_2` FOREIGN KEY (`show_id`) REFERENCES `tblshows` (`id`);

--
-- Constraints for table `tblflashsales`
--
ALTER TABLE `tblflashsales`
  ADD CONSTRAINT `tblflashsales_ibfk_1` FOREIGN KEY (`show_id`) REFERENCES `tblshows` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblpointslog`
--
ALTER TABLE `tblpointslog`
  ADD CONSTRAINT `tblpointslog_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tblusers` (`id`),
  ADD CONSTRAINT `tblpointslog_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `tblbookings` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tblseats`
--
ALTER TABLE `tblseats`
  ADD CONSTRAINT `tblseats_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `tblrooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblseatstatus`
--
ALTER TABLE `tblseatstatus`
  ADD CONSTRAINT `tblseatstatus_ibfk_1` FOREIGN KEY (`show_id`) REFERENCES `tblshows` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblseatstatus_ibfk_2` FOREIGN KEY (`seat_id`) REFERENCES `tblseats` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblshows`
--
ALTER TABLE `tblshows`
  ADD CONSTRAINT `tblshows_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `tblmovies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblshows_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `tblrooms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
