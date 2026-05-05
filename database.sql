-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 06, 2026 lúc 01:44 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `minicine`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblbookingitems`
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
-- Đang đổ dữ liệu cho bảng `tblbookingitems`
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
(60, 18, 14, 45000, 90000, 1, 50),
(61, 19, 15, 45000, 90000, 1, 50),
(62, 20, 3, 45000, 90000, 1, 50),
(63, 21, 30, 65000, 130000, 1, 50),
(64, 22, 29, 65000, 130000, 1, 50),
(65, 23, 59, 45000, 90000, 1, 50),
(66, 23, 60, 45000, 90000, 1, 50),
(67, 24, 58, 45000, 90000, 1, 50),
(68, 25, 88, 65000, 130000, 1, 50),
(69, 25, 89, 65000, 130000, 1, 50),
(70, 25, 102, 200000, 200000, 0, 0),
(71, 26, 80, 65000, 130000, 1, 50),
(72, 26, 81, 65000, 130000, 1, 50),
(73, 27, 60, 63000, 90000, 1, 30),
(74, 28, 59, 63000, 90000, 1, 30);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblbookings`
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
-- Đang đổ dữ liệu cho bảng `tblbookings`
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
(18, 1, 9, 45000, 'paid', 'MC-77550E129804', '2026-05-05 13:26:26'),
(19, 1, 9, 45000, 'paid', 'MC-DD08F94DA157', '2026-05-05 13:35:20'),
(20, 5, 9, 45000, 'paid', 'MC-96559C40E3B0', '2026-05-05 14:00:50'),
(21, 1, 9, 65000, 'paid', 'MC-DAEBCFDA33CF', '2026-05-05 14:06:44'),
(22, 5, 9, 65000, 'paid', 'MC-4A2CBE4E68AE', '2026-05-05 14:14:59'),
(23, 5, 10, 90000, 'paid', 'MC-AC9847169659', '2026-05-05 14:21:05'),
(24, 5, 10, 45000, 'paid', 'MC-FBE53CD68A4A', '2026-05-05 14:31:57'),
(25, 1, 10, 330000, 'paid', 'MC-5589965DA49C', '2026-05-05 14:42:40'),
(26, 5, 10, 130000, 'paid', 'MC-5397746BA107', '2026-05-05 14:43:56'),
(27, 5, 12, 53000, 'paid', 'MC-5A49C9C31831', '2026-05-05 21:38:29'),
(28, 5, 12, 63000, 'paid', 'MC-3859D2E9C35E', '2026-05-05 21:45:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblflashsales`
--

CREATE TABLE `tblflashsales` (
  `id` int(10) UNSIGNED NOT NULL,
  `show_id` int(10) UNSIGNED NOT NULL,
  `discount_pct` tinyint(3) UNSIGNED NOT NULL DEFAULT 30,
  `trigger_type` enum('pre2h','post15m','manual') NOT NULL DEFAULT 'pre2h',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tblflashsales`
--

INSERT INTO `tblflashsales` (`id`, `show_id`, `discount_pct`, `trigger_type`, `is_active`) VALUES
(2, 4, 30, 'pre2h', 1),
(8, 4, 50, 'post15m', 1),
(14, 8, 30, 'pre2h', 1),
(15, 8, 50, 'post15m', 1),
(16, 9, 30, 'pre2h', 1),
(17, 9, 50, 'post15m', 1),
(18, 10, 30, 'pre2h', 1),
(19, 10, 50, 'post15m', 1),
(20, 11, 30, 'pre2h', 1),
(21, 11, 50, 'post15m', 1),
(22, 12, 30, 'pre2h', 1),
(23, 12, 50, 'post15m', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblmovies`
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
  `age_rating` enum('P','T13','T16','T18') NOT NULL DEFAULT 'P',
  `avg_rating` decimal(3,1) NOT NULL DEFAULT 0.0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tblmovies`
--

INSERT INTO `tblmovies` (`id`, `title`, `description`, `genre`, `duration_min`, `poster_url`, `trailer_url`, `status`, `age_rating`, `avg_rating`, `created_at`) VALUES
(1, 'Avengers: Doomsday', 'Trận chiến cuối cùng của các siêu anh hùng', '', 150, 'poster_69e6fd213df56.webp', '', 'upcoming', 'P', 0.0, '2026-04-21 10:42:13'),
(4, 'Frozen 2', 'Lạnh giá con trym', 'Kinh Dị, Tâm Lý, Âm Nhạc', 121, 'poster_69e6fc43870ca.jpg', 'https://youtu.be/Zi4LMpSDccc?si=aHgYk_0LDqmIRF4M', 'showing', 'T13', 5.5, '2026-04-21 11:25:39'),
(8, 'Heo Năm Móng', 'Truyền thuyết Cô Năm Hợi được truyền miệng qua nhiều thế hệ, như một lời nhắc về sự tái sinh đầy nghiệt ngã: linh hồn chết oan hoặc mang nhiều nghiệp quả nên mắc kẹt trong thân xác loài vật, mang theo ký ức và oán lệnh chưa thể hóa giải. \r\n\r\nVà giữa những hoa văn Khmer cổ kính và ánh đỏ linh thiêng, Sa - nữ chính của Heo Năm Móng hiện lên như một linh hồn đang đứng giữa hai cõi, như thể hai số phận đang chồng lên nhau. Liệu Sa còn điều gì vướng mắc ở cõi trần? Hay chính cô là kẻ từng gây nên tội lỗi, để rồi phải gánh chịu nghiệp báo?', 'Kinh Dị', 103, 'poster_69f9bce35d63e.jpg', 'https://www.youtube.com/watch?v=V_p3qpDUuz4', 'upcoming', 'T18', 0.0, '2026-05-05 16:48:19'),
(9, 'Trùm Sò', 'Ở Làng Sứa Đỏ - một ngôi làng nhỏ xa xôi heo hút, hạn hán triền miên, người dân ai cũng nghèo cũng khổ, chỉ riêng Trùm Sò là giàu nứt đố đổ vách. Ghét nỗi là gã sống không tình không nghĩa, chỉ biết đến tiền, tiền và tiền. Gã còn ki bo, bủn xỉn với cả chính bản thân mình: ăn không dám ăn, mặc không dám mặc, chẳng chơi chẳng yêu cũng chẳng chịu cưới ai. Ngoài bà mẹ già lú lẫn thì chớ hòng ai bòn được của gã một cắc nào.  \r\n\r\nMột ngày nọ, Trùm Sò bị mất số vàng lớn. Từ đây, cũng vì tiếc tiền mà Trùm Sò dần bị cuốn vào một vụ cướp kinh thiên động địa chưa từng có cùng với hai người bạn thuở nhỏ là góa phụ Thị Hến và lãng tử giang hồ Tôm Hùm.', 'Hài', 105, 'poster_69f9bf3497bd3.webp', 'https://www.youtube.com/watch?v=PaMk-Ahcwuk', 'showing', 'P', 0.0, '2026-05-05 16:58:12'),
(10, 'Phí Phông: Quỷ Máu Rừng Thiêng', 'Chuyện phim theo chân hai anh em Còn (Kiều Minh Tuấn) và Dương (Đoàn Minh Anh) - hai pháp sự tập sự lên núi cứu người mẹ đang bị lời nguyền “Phí Phông” đánh gục. Cùng lúc đó, trong bản sâu cũng xảy ra nhiều cái chết ghê rợn. Mọi nghi ngờ đổ dồn về hai mẹ con Mon (Diệp Bảo Ngọc) và Lua (Nina Nutthacha Padovan), những người mang đặc tính y hệt “Phí Phông”: xinh đẹp, làn da trắng và chỉ di truyền từ mẹ sang con. Thế nhưng, vẫn còn những bí mật động trời bị chôn vùi trong chốn rừng thiêng nước độc, cuốn hai anh em Còn và Dương vào cuộc truy lùng “Phí Phông” không hồi kết.', 'Kinh Dị', 120, 'poster_69f9c0f8ca04b.jpg', 'https://www.youtube.com/watch?v=AFkKZXbzHdI', 'showing', 'T16', 6.0, '2026-05-05 17:05:44'),
(11, 'Đại Tiệc Trăng Máu 8', 'Đại Tiệc Trăng Máu 8 theo chân một vị đạo diễn hay bị coi thường (Vân Sơn đóng) trong dự án thử thách nhất đời ông: thực hiện một bộ phim dài 35 phút chỉ với một cú máy. Hàng loạt tình huống dở khóc dở cười xảy ra khi các diễn viên liên tục gây chuyện “khó đỡ”. Thế nhưng, việc hoàn thành tác phẩm là cơ hội cuối cùng để ông giành lại sự tôn trọng từ cô con gái đam mê nghệ thuật. \r\n\r\nQuy tụ dàn sao đình đám nhất điện ảnh Việt, Đại Tiệc Trăng Máu 8 ấn định lịch khai tiệc vào 24.04.2026, chiếu xuyên Đại lễ Giỗ Tổ Hùng Vương & 30.04.', 'Hài, Kinh Dị, Tâm Lý', 130, 'poster_69f9c1e33a91e.jpg', 'https://www.youtube.com/watch?v=nNaAawAEW9I', 'upcoming', 'T16', 0.0, '2026-05-05 17:09:39'),
(12, 'Anh Hùng', 'Câu chuyện phim theo chân Hùng (Thái Hòa) - người cha đơn thân kiêm tài xế taxi và đồng nghiệp hãng xe là Tuấn (Võ Tấn Phát) bị cuốn vào một phi vụ lừa đảo từ thiện tiền tỉ trong khi sinh mạng cô con gái nhỏ của anh đang nằm gọn trong tay tử thần.', 'Tâm Lý, Gia Đình', 122, 'poster_69f9c39fa11f2.webp', 'https://www.youtube.com/watch?v=9fgmlmYme18', 'showing', 'T13', 0.0, '2026-05-05 17:17:03');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblpointslog`
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
-- Đang đổ dữ liệu cho bảng `tblpointslog`
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
(55, 4, NULL, 35, 'Đặt ghế vip - Booking #14', '2026-05-04 13:39:21'),
(56, 4, NULL, 35, 'Đặt ghế vip - Booking #14', '2026-05-04 13:39:21'),
(57, 3, NULL, 35, 'Đặt ghế vip - Booking #15', '2026-05-05 10:54:16'),
(58, 3, NULL, -600, 'Đổi điểm giảm giá - Booking #15', '2026-05-05 10:54:16'),
(59, 4, NULL, 20, 'Đặt ghế couple - Booking #16', '2026-05-05 10:58:15'),
(60, 4, NULL, 20, 'Đặt ghế couple - Booking #17', '2026-05-05 11:00:08'),
(61, 1, 18, 10, 'Đặt ghế standard - Booking #18', '2026-05-05 13:26:26'),
(62, 1, 19, 10, 'Đặt ghế standard - Booking #19', '2026-05-05 13:35:20'),
(63, 5, 20, 10, 'Đặt ghế standard - Booking #20', '2026-05-05 14:00:50'),
(64, 1, 21, 35, 'Đặt ghế vip - Booking #21', '2026-05-05 14:06:44'),
(65, 5, 22, 35, 'Đặt ghế vip - Booking #22', '2026-05-05 14:14:59'),
(66, 5, 23, 10, 'Đặt ghế standard - Booking #23', '2026-05-05 14:21:05'),
(67, 5, 23, 10, 'Đặt ghế standard - Booking #23', '2026-05-05 14:21:05'),
(68, 5, 24, 10, 'Đặt ghế standard - Booking #24', '2026-05-05 14:31:57'),
(69, 1, 25, 35, 'Đặt ghế vip - Booking #25', '2026-05-05 14:42:40'),
(70, 1, 25, 35, 'Đặt ghế vip - Booking #25', '2026-05-05 14:42:40'),
(71, 1, 25, 20, 'Đặt ghế couple - Booking #25', '2026-05-05 14:42:40'),
(72, 5, 26, 35, 'Đặt ghế vip - Booking #26', '2026-05-05 14:43:56'),
(73, 5, 26, 35, 'Đặt ghế vip - Booking #26', '2026-05-05 14:43:56'),
(74, 5, 27, 10, 'Đặt ghế standard - Booking #27', '2026-05-05 21:38:29'),
(75, 5, 27, -100, 'Đổi điểm giảm giá - Booking #27', '2026-05-05 21:38:29'),
(76, 5, 28, 10, 'Đặt ghế standard - Booking #28', '2026-05-05 21:45:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblprices`
--

CREATE TABLE `tblprices` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `seat_type` enum('standard','vip','couple') NOT NULL,
  `price` decimal(12,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tblprices`
--

INSERT INTO `tblprices` (`id`, `seat_type`, `price`) VALUES
(1, 'standard', 90000),
(2, 'vip', 130000),
(3, 'couple', 200000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblratings`
--

CREATE TABLE `tblratings` (
  `id` int(10) UNSIGNED NOT NULL,
  `movie_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(2) UNSIGNED NOT NULL DEFAULT 5,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tblratings`
--

INSERT INTO `tblratings` (`id`, `movie_id`, `user_id`, `rating`, `created_at`) VALUES
(1, 4, 1, 10, '2026-05-05 16:29:47'),
(2, 4, 5, 1, '2026-05-05 16:31:54'),
(3, 10, 1, 6, '2026-05-05 17:36:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblrooms`
--

CREATE TABLE `tblrooms` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `floor` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tblrooms`
--

INSERT INTO `tblrooms` (`id`, `floor`, `name`) VALUES
(1, 1, 'Phòng 1'),
(2, 2, 'Phòng 2'),
(3, 3, 'Phòng 3');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblseats`
--

CREATE TABLE `tblseats` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `room_id` tinyint(3) UNSIGNED NOT NULL,
  `row` char(1) NOT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL,
  `type` enum('standard','vip','couple') NOT NULL DEFAULT 'standard'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tblseats`
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
-- Cấu trúc bảng cho bảng `tblseatstatus`
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
-- Đang đổ dữ liệu cho bảng `tblseatstatus`
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
(294, 4, 34, 'booked', 3, NULL, NULL),
(295, 4, 44, 'available', 2, NULL, NULL),
(296, 4, 35, 'booked', 5, NULL, NULL),
(297, 4, 45, 'available', 2, NULL, NULL),
(298, 4, 20, 'available', 2, NULL, NULL),
(299, 4, 42, 'available', 2, NULL, NULL),
(300, 4, 36, 'booked', 3, NULL, NULL),
(301, 4, 27, 'available', 10, NULL, NULL),
(302, 4, 28, 'available', 8, NULL, NULL),
(366, 4, 10, 'available', 2, NULL, NULL),
(367, 4, 5, 'available', 2, NULL, NULL),
(368, 4, 37, 'booked', 1, NULL, NULL),
(369, 4, 38, 'booked', 1, NULL, NULL),
(370, 4, 25, 'available', 2, NULL, NULL),
(371, 4, 26, 'available', 4, NULL, NULL),
(372, 4, 29, 'available', 4, NULL, NULL),
(373, 4, 30, 'available', 4, NULL, NULL),
(374, 4, 31, 'available', 4, NULL, NULL),
(438, 8, 1, 'available', 0, NULL, NULL),
(439, 8, 2, 'available', 0, NULL, NULL),
(440, 8, 3, 'available', 0, NULL, NULL),
(441, 8, 4, 'available', 0, NULL, NULL),
(442, 8, 5, 'available', 0, NULL, NULL),
(443, 8, 6, 'available', 0, NULL, NULL),
(444, 8, 7, 'available', 0, NULL, NULL),
(445, 8, 8, 'available', 0, NULL, NULL),
(446, 8, 9, 'available', 0, NULL, NULL),
(447, 8, 10, 'available', 0, NULL, NULL),
(448, 8, 11, 'available', 0, NULL, NULL),
(449, 8, 12, 'available', 0, NULL, NULL),
(450, 8, 13, 'available', 0, NULL, NULL),
(451, 8, 14, 'available', 0, NULL, NULL),
(452, 8, 15, 'available', 0, NULL, NULL),
(453, 8, 16, 'available', 0, NULL, NULL),
(454, 8, 17, 'available', 0, NULL, NULL),
(455, 8, 18, 'available', 0, NULL, NULL),
(456, 8, 19, 'available', 0, NULL, NULL),
(457, 8, 20, 'available', 0, NULL, NULL),
(458, 8, 21, 'available', 0, NULL, NULL),
(459, 8, 22, 'available', 0, NULL, NULL),
(460, 8, 23, 'available', 0, NULL, NULL),
(461, 8, 24, 'available', 0, NULL, NULL),
(462, 8, 25, 'available', 0, NULL, NULL),
(463, 8, 26, 'available', 0, NULL, NULL),
(464, 8, 27, 'available', 0, NULL, NULL),
(465, 8, 28, 'available', 0, NULL, NULL),
(466, 8, 29, 'available', 0, NULL, NULL),
(467, 8, 30, 'available', 0, NULL, NULL),
(468, 8, 31, 'available', 0, NULL, NULL),
(469, 8, 32, 'available', 0, NULL, NULL),
(470, 8, 33, 'available', 0, NULL, NULL),
(471, 8, 34, 'available', 0, NULL, NULL),
(472, 8, 35, 'available', 0, NULL, NULL),
(473, 8, 36, 'available', 0, NULL, NULL),
(474, 8, 37, 'available', 0, NULL, NULL),
(475, 8, 38, 'available', 0, NULL, NULL),
(476, 8, 39, 'available', 0, NULL, NULL),
(477, 8, 40, 'available', 0, NULL, NULL),
(478, 8, 41, 'available', 0, NULL, NULL),
(479, 8, 42, 'available', 0, NULL, NULL),
(480, 8, 43, 'available', 0, NULL, NULL),
(481, 8, 44, 'available', 0, NULL, NULL),
(482, 8, 45, 'available', 0, NULL, NULL),
(483, 8, 46, 'available', 0, NULL, NULL),
(484, 8, 47, 'available', 0, NULL, NULL),
(485, 8, 48, 'available', 0, NULL, NULL),
(486, 8, 49, 'available', 0, NULL, NULL),
(487, 8, 50, 'available', 0, NULL, NULL),
(488, 8, 51, 'available', 0, NULL, NULL),
(489, 8, 52, 'available', 0, NULL, NULL),
(501, 9, 1, 'available', 0, NULL, NULL),
(502, 9, 2, 'available', 0, NULL, NULL),
(503, 9, 3, 'booked', 3, NULL, NULL),
(504, 9, 4, 'available', 0, NULL, NULL),
(505, 9, 5, 'available', 0, NULL, NULL),
(506, 9, 6, 'available', 0, NULL, NULL),
(507, 9, 7, 'available', 0, NULL, NULL),
(508, 9, 8, 'available', 2, NULL, NULL),
(509, 9, 9, 'available', 0, NULL, NULL),
(510, 9, 10, 'available', 0, NULL, NULL),
(511, 9, 11, 'available', 0, NULL, NULL),
(512, 9, 12, 'available', 2, NULL, NULL),
(513, 9, 13, 'available', 4, NULL, NULL),
(514, 9, 14, 'booked', 1, NULL, NULL),
(515, 9, 15, 'booked', 1, NULL, NULL),
(516, 9, 16, 'available', 2, NULL, NULL),
(517, 9, 17, 'available', 0, NULL, NULL),
(518, 9, 18, 'available', 0, NULL, NULL),
(519, 9, 19, 'available', 0, NULL, NULL),
(520, 9, 20, 'available', 0, NULL, NULL),
(521, 9, 21, 'available', 2, NULL, NULL),
(522, 9, 22, 'available', 6, NULL, NULL),
(523, 9, 23, 'available', 0, NULL, NULL),
(524, 9, 24, 'available', 0, NULL, NULL),
(525, 9, 25, 'available', 0, NULL, NULL),
(526, 9, 26, 'available', 0, NULL, NULL),
(527, 9, 27, 'available', 2, NULL, NULL),
(528, 9, 28, 'available', 2, NULL, NULL),
(529, 9, 29, 'booked', 3, NULL, NULL),
(530, 9, 30, 'booked', 1, NULL, NULL),
(531, 9, 31, 'available', 0, NULL, NULL),
(532, 9, 32, 'available', 0, NULL, NULL),
(533, 9, 33, 'available', 0, NULL, NULL),
(534, 9, 34, 'available', 2, NULL, NULL),
(535, 9, 35, 'available', 2, NULL, NULL),
(536, 9, 36, 'available', 4, NULL, NULL),
(537, 9, 37, 'available', 2, NULL, NULL),
(538, 9, 38, 'available', 4, NULL, NULL),
(539, 9, 39, 'available', 0, NULL, NULL),
(540, 9, 40, 'available', 0, NULL, NULL),
(541, 9, 41, 'available', 0, NULL, NULL),
(542, 9, 42, 'available', 0, NULL, NULL),
(543, 9, 43, 'available', 0, NULL, NULL),
(544, 9, 44, 'available', 0, NULL, NULL),
(545, 9, 45, 'available', 0, NULL, NULL),
(546, 9, 46, 'available', 0, NULL, NULL),
(547, 9, 47, 'available', 0, NULL, NULL),
(548, 9, 48, 'available', 0, NULL, NULL),
(549, 9, 49, 'available', 0, NULL, NULL),
(550, 9, 50, 'available', 0, NULL, NULL),
(551, 9, 51, 'available', 0, NULL, NULL),
(552, 9, 52, 'available', 0, NULL, NULL),
(564, 10, 53, 'available', 0, NULL, NULL),
(565, 10, 54, 'available', 0, NULL, NULL),
(566, 10, 55, 'available', 2, NULL, NULL),
(567, 10, 56, 'available', 4, NULL, NULL),
(568, 10, 57, 'available', 2, NULL, NULL),
(569, 10, 58, 'booked', 3, NULL, NULL),
(570, 10, 59, 'booked', 1, NULL, NULL),
(571, 10, 60, 'booked', 7, NULL, NULL),
(572, 10, 61, 'available', 0, NULL, NULL),
(573, 10, 62, 'available', 0, NULL, NULL),
(574, 10, 63, 'available', 0, NULL, NULL),
(575, 10, 64, 'available', 0, NULL, NULL),
(576, 10, 65, 'available', 0, NULL, NULL),
(577, 10, 66, 'available', 2, NULL, NULL),
(578, 10, 67, 'available', 4, NULL, NULL),
(579, 10, 68, 'available', 2, NULL, NULL),
(580, 10, 69, 'available', 0, NULL, NULL),
(581, 10, 70, 'available', 0, NULL, NULL),
(582, 10, 71, 'available', 2, NULL, NULL),
(583, 10, 72, 'available', 2, NULL, NULL),
(584, 10, 73, 'available', 6, NULL, NULL),
(585, 10, 74, 'available', 2, NULL, NULL),
(586, 10, 75, 'available', 0, NULL, NULL),
(587, 10, 76, 'available', 0, NULL, NULL),
(588, 10, 77, 'available', 0, NULL, NULL),
(589, 10, 78, 'available', 4, NULL, NULL),
(590, 10, 79, 'available', 4, NULL, NULL),
(591, 10, 80, 'booked', 7, NULL, NULL),
(592, 10, 81, 'booked', 7, NULL, NULL),
(593, 10, 82, 'available', 4, NULL, NULL),
(594, 10, 83, 'available', 0, NULL, NULL),
(595, 10, 84, 'available', 2, NULL, NULL),
(596, 10, 85, 'available', 0, NULL, NULL),
(597, 10, 86, 'available', 6, NULL, NULL),
(598, 10, 87, 'available', 8, NULL, NULL),
(599, 10, 88, 'booked', 9, NULL, NULL),
(600, 10, 89, 'booked', 9, NULL, NULL),
(601, 10, 90, 'available', 6, NULL, NULL),
(602, 10, 91, 'available', 0, NULL, NULL),
(603, 10, 92, 'available', 0, NULL, NULL),
(604, 10, 93, 'available', 0, NULL, NULL),
(605, 10, 94, 'available', 0, NULL, NULL),
(606, 10, 95, 'available', 0, NULL, NULL),
(607, 10, 96, 'available', 0, NULL, NULL),
(608, 10, 97, 'available', 0, NULL, NULL),
(609, 10, 98, 'available', 0, NULL, NULL),
(610, 10, 99, 'available', 0, NULL, NULL),
(611, 10, 100, 'available', 0, NULL, NULL),
(612, 10, 101, 'available', 0, NULL, NULL),
(613, 10, 102, 'booked', 5, NULL, NULL),
(614, 10, 103, 'available', 0, NULL, NULL),
(615, 10, 104, 'available', 0, NULL, NULL),
(628, 11, 1, 'available', 0, NULL, NULL),
(629, 11, 2, 'available', 0, NULL, NULL),
(630, 11, 3, 'available', 0, NULL, NULL),
(631, 11, 4, 'available', 0, NULL, NULL),
(632, 11, 5, 'available', 0, NULL, NULL),
(633, 11, 6, 'available', 0, NULL, NULL),
(634, 11, 7, 'available', 0, NULL, NULL),
(635, 11, 8, 'available', 0, NULL, NULL),
(636, 11, 9, 'available', 0, NULL, NULL),
(637, 11, 10, 'available', 0, NULL, NULL),
(638, 11, 11, 'available', 0, NULL, NULL),
(639, 11, 12, 'available', 0, NULL, NULL),
(640, 11, 13, 'available', 0, NULL, NULL),
(641, 11, 14, 'available', 0, NULL, NULL),
(642, 11, 15, 'available', 0, NULL, NULL),
(643, 11, 16, 'available', 0, NULL, NULL),
(644, 11, 17, 'available', 0, NULL, NULL),
(645, 11, 18, 'available', 0, NULL, NULL),
(646, 11, 19, 'available', 0, NULL, NULL),
(647, 11, 20, 'available', 0, NULL, NULL),
(648, 11, 21, 'available', 0, NULL, NULL),
(649, 11, 22, 'available', 0, NULL, NULL),
(650, 11, 23, 'available', 0, NULL, NULL),
(651, 11, 24, 'available', 0, NULL, NULL),
(652, 11, 25, 'available', 0, NULL, NULL),
(653, 11, 26, 'available', 0, NULL, NULL),
(654, 11, 27, 'available', 0, NULL, NULL),
(655, 11, 28, 'available', 0, NULL, NULL),
(656, 11, 29, 'available', 0, NULL, NULL),
(657, 11, 30, 'available', 0, NULL, NULL),
(658, 11, 31, 'available', 0, NULL, NULL),
(659, 11, 32, 'available', 0, NULL, NULL),
(660, 11, 33, 'available', 0, NULL, NULL),
(661, 11, 34, 'available', 0, NULL, NULL),
(662, 11, 35, 'available', 0, NULL, NULL),
(663, 11, 36, 'available', 0, NULL, NULL),
(664, 11, 37, 'available', 0, NULL, NULL),
(665, 11, 38, 'available', 0, NULL, NULL),
(666, 11, 39, 'available', 0, NULL, NULL),
(667, 11, 40, 'available', 0, NULL, NULL),
(668, 11, 41, 'available', 0, NULL, NULL),
(669, 11, 42, 'available', 0, NULL, NULL),
(670, 11, 43, 'available', 0, NULL, NULL),
(671, 11, 44, 'available', 0, NULL, NULL),
(672, 11, 45, 'available', 0, NULL, NULL),
(673, 11, 46, 'available', 0, NULL, NULL),
(674, 11, 47, 'available', 0, NULL, NULL),
(675, 11, 48, 'available', 0, NULL, NULL),
(676, 11, 49, 'available', 0, NULL, NULL),
(677, 11, 50, 'available', 0, NULL, NULL),
(678, 11, 51, 'available', 0, NULL, NULL),
(679, 11, 52, 'available', 0, NULL, NULL),
(691, 12, 53, 'available', 0, NULL, NULL),
(692, 12, 54, 'available', 0, NULL, NULL),
(693, 12, 55, 'available', 0, NULL, NULL),
(694, 12, 56, 'available', 0, NULL, NULL),
(695, 12, 57, 'available', 0, NULL, NULL),
(696, 12, 58, 'available', 0, NULL, NULL),
(697, 12, 59, 'booked', 1, NULL, NULL),
(698, 12, 60, 'booked', 3, NULL, NULL),
(699, 12, 61, 'available', 0, NULL, NULL),
(700, 12, 62, 'available', 0, NULL, NULL),
(701, 12, 63, 'available', 0, NULL, NULL),
(702, 12, 64, 'available', 0, NULL, NULL),
(703, 12, 65, 'available', 0, NULL, NULL),
(704, 12, 66, 'available', 0, NULL, NULL),
(705, 12, 67, 'available', 0, NULL, NULL),
(706, 12, 68, 'available', 0, NULL, NULL),
(707, 12, 69, 'available', 0, NULL, NULL),
(708, 12, 70, 'available', 0, NULL, NULL),
(709, 12, 71, 'available', 0, NULL, NULL),
(710, 12, 72, 'available', 0, NULL, NULL),
(711, 12, 73, 'available', 0, NULL, NULL),
(712, 12, 74, 'available', 0, NULL, NULL),
(713, 12, 75, 'available', 0, NULL, NULL),
(714, 12, 76, 'available', 0, NULL, NULL),
(715, 12, 77, 'available', 0, NULL, NULL),
(716, 12, 78, 'available', 0, NULL, NULL),
(717, 12, 79, 'available', 0, NULL, NULL),
(718, 12, 80, 'available', 0, NULL, NULL),
(719, 12, 81, 'available', 0, NULL, NULL),
(720, 12, 82, 'available', 0, NULL, NULL),
(721, 12, 83, 'available', 0, NULL, NULL),
(722, 12, 84, 'available', 0, NULL, NULL),
(723, 12, 85, 'available', 0, NULL, NULL),
(724, 12, 86, 'available', 0, NULL, NULL),
(725, 12, 87, 'available', 0, NULL, NULL),
(726, 12, 88, 'available', 0, NULL, NULL),
(727, 12, 89, 'available', 0, NULL, NULL),
(728, 12, 90, 'available', 0, NULL, NULL),
(729, 12, 91, 'available', 0, NULL, NULL),
(730, 12, 92, 'available', 0, NULL, NULL),
(731, 12, 93, 'available', 0, NULL, NULL),
(732, 12, 94, 'available', 0, NULL, NULL),
(733, 12, 95, 'available', 0, NULL, NULL),
(734, 12, 96, 'available', 0, NULL, NULL),
(735, 12, 97, 'available', 0, NULL, NULL),
(736, 12, 98, 'available', 0, NULL, NULL),
(737, 12, 99, 'available', 0, NULL, NULL),
(738, 12, 100, 'available', 0, NULL, NULL),
(739, 12, 101, 'available', 0, NULL, NULL),
(740, 12, 102, 'available', 0, NULL, NULL),
(741, 12, 103, 'available', 0, NULL, NULL),
(742, 12, 104, 'available', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblshows`
--

CREATE TABLE `tblshows` (
  `id` int(10) UNSIGNED NOT NULL,
  `movie_id` int(10) UNSIGNED NOT NULL,
  `room_id` tinyint(3) UNSIGNED NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tblshows`
--

INSERT INTO `tblshows` (`id`, `movie_id`, `room_id`, `start_time`, `end_time`) VALUES
(1, 1, 1, '2026-04-23 14:32:00', '2026-04-23 17:02:00'),
(2, 4, 1, '2026-04-30 15:30:00', '2026-04-30 17:30:00'),
(3, 1, 1, '2026-04-30 17:30:00', '2026-04-30 20:00:00'),
(4, 4, 1, '2026-05-04 13:00:00', '2026-05-04 15:00:00'),
(8, 4, 1, '2026-05-05 00:54:00', '2026-05-05 02:55:00'),
(9, 4, 1, '2026-05-05 12:53:00', '2026-05-05 14:54:00'),
(10, 4, 2, '2026-05-05 14:00:00', '2026-05-05 16:01:00'),
(11, 8, 1, '2026-05-05 22:00:00', '2026-05-05 23:43:00'),
(12, 10, 2, '2026-05-05 22:00:00', '2026-05-06 00:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tblusers`
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
-- Đang đổ dữ liệu cho bảng `tblusers`
--

INSERT INTO `tblusers` (`id`, `email`, `password_hash`, `full_name`, `role`, `email_verified`, `verify_token`, `reset_token`, `reset_expires`, `total_points`, `created_at`) VALUES
(1, 'admin@minicine.vn', '$2y$12$2M.duKrvcXSp4otbq53q8Oomfbnu9NXGlmiymIBFezMKuKRnrswtG', 'Quản trị viên', 'admin', 1, NULL, NULL, NULL, 145, '2026-04-21 10:42:12'),
(3, 'huong.ngocc26@gmail.com', '$2y$10$Fq8dkZ8wwkZv1ZFQgfUvqetFxCBnEo7MPAlw2sQZPj1.vdNfNcjvC', 'Huynh Huong', 'customer', 1, NULL, NULL, NULL, 95, '2026-04-21 11:15:54'),
(4, 'cavaca.2614@gmail.com', '$2y$10$v1EUdFmU1xcoDffAYDJFW.7dd4N89bvkod3O5sncZ.3gOuaxSdszu', 'Tuyết Nhung', 'customer', 1, NULL, NULL, NULL, 835, '2026-04-23 14:25:59'),
(5, 'yunsootv@gmail.com', '$2y$10$Dy0EZbpzDSadrbnmkoTna.YlE7Zj7DHHuqNj79XDTTBDmOj1cexHS', 'BeNhun', 'customer', 1, NULL, NULL, NULL, 65, '2026-05-05 11:02:58');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `tblbookingitems`
--
ALTER TABLE `tblbookingitems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `seat_id` (`seat_id`);

--
-- Chỉ mục cho bảng `tblbookings`
--
ALTER TABLE `tblbookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `show_id` (`show_id`);

--
-- Chỉ mục cho bảng `tblflashsales`
--
ALTER TABLE `tblflashsales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_show_trigger` (`show_id`,`trigger_type`);

--
-- Chỉ mục cho bảng `tblmovies`
--
ALTER TABLE `tblmovies`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tblpointslog`
--
ALTER TABLE `tblpointslog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Chỉ mục cho bảng `tblprices`
--
ALTER TABLE `tblprices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seat_type` (`seat_type`);

--
-- Chỉ mục cho bảng `tblratings`
--
ALTER TABLE `tblratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_movie_user` (`movie_id`,`user_id`);

--
-- Chỉ mục cho bảng `tblrooms`
--
ALTER TABLE `tblrooms`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tblseats`
--
ALTER TABLE `tblseats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_seat` (`room_id`,`row`,`number`);

--
-- Chỉ mục cho bảng `tblseatstatus`
--
ALTER TABLE `tblseatstatus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_show_seat` (`show_id`,`seat_id`),
  ADD KEY `seat_id` (`seat_id`);

--
-- Chỉ mục cho bảng `tblshows`
--
ALTER TABLE `tblshows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movie_id` (`movie_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Chỉ mục cho bảng `tblusers`
--
ALTER TABLE `tblusers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `tblbookingitems`
--
ALTER TABLE `tblbookingitems`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT cho bảng `tblbookings`
--
ALTER TABLE `tblbookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT cho bảng `tblflashsales`
--
ALTER TABLE `tblflashsales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `tblmovies`
--
ALTER TABLE `tblmovies`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `tblpointslog`
--
ALTER TABLE `tblpointslog`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT cho bảng `tblprices`
--
ALTER TABLE `tblprices`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `tblratings`
--
ALTER TABLE `tblratings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `tblrooms`
--
ALTER TABLE `tblrooms`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `tblseats`
--
ALTER TABLE `tblseats`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=157;

--
-- AUTO_INCREMENT cho bảng `tblseatstatus`
--
ALTER TABLE `tblseatstatus`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=754;

--
-- AUTO_INCREMENT cho bảng `tblshows`
--
ALTER TABLE `tblshows`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `tblbookingitems`
--
ALTER TABLE `tblbookingitems`
  ADD CONSTRAINT `tblbookingitems_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `tblbookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblbookingitems_ibfk_2` FOREIGN KEY (`seat_id`) REFERENCES `tblseats` (`id`);

--
-- Các ràng buộc cho bảng `tblbookings`
--
ALTER TABLE `tblbookings`
  ADD CONSTRAINT `tblbookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tblusers` (`id`),
  ADD CONSTRAINT `tblbookings_ibfk_2` FOREIGN KEY (`show_id`) REFERENCES `tblshows` (`id`);

--
-- Các ràng buộc cho bảng `tblflashsales`
--
ALTER TABLE `tblflashsales`
  ADD CONSTRAINT `tblflashsales_ibfk_1` FOREIGN KEY (`show_id`) REFERENCES `tblshows` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tblpointslog`
--
ALTER TABLE `tblpointslog`
  ADD CONSTRAINT `tblpointslog_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tblusers` (`id`),
  ADD CONSTRAINT `tblpointslog_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `tblbookings` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `tblseats`
--
ALTER TABLE `tblseats`
  ADD CONSTRAINT `tblseats_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `tblrooms` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tblseatstatus`
--
ALTER TABLE `tblseatstatus`
  ADD CONSTRAINT `tblseatstatus_ibfk_1` FOREIGN KEY (`show_id`) REFERENCES `tblshows` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblseatstatus_ibfk_2` FOREIGN KEY (`seat_id`) REFERENCES `tblseats` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `tblshows`
--
ALTER TABLE `tblshows`
  ADD CONSTRAINT `tblshows_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `tblmovies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblshows_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `tblrooms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
