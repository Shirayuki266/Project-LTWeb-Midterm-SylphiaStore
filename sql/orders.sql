-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th3 15, 2026 lúc 06:45 PM
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
-- Cơ sở dữ liệu: `sylphia_shop2`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('pending','confirmed','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `address`, `payment_method`, `status`, `created_at`, `total`) VALUES
(1, 1, 'Hà Nội', 'COD', '', '2026-03-15 12:58:48', 14400000.00),
(2, 2, 'TPHCM', 'bank', '', '2026-03-15 12:58:48', 5000000.00),
(3, 3, 'Đà Nẵng', 'COD', 'pending', '2026-03-15 12:58:48', 10800000.00),
(4, 4, 'Cần Thơ', 'bank', '', '2026-03-15 12:58:48', 6600000.00),
(5, 5, 'Hải Phòng', 'COD', '', '2026-03-15 12:58:48', 5900000.00),
(6, 6, 'Huế', 'COD', 'pending', '2026-03-15 12:58:48', 8400000.00),
(7, 7, 'Nha Trang', 'bank', '', '2026-03-15 12:58:48', 3750000.00),
(8, 8, 'Bình Dương', 'COD', '', '2026-03-15 12:58:48', 24000000.00),
(9, 9, 'Đồng Nai', 'bank', 'pending', '2026-03-15 12:58:48', 9600000.00),
(10, 10, 'Vũng Tàu', 'COD', '', '2026-03-15 12:58:48', 4200000.00),
(11, 1, 'Hà Nội', 'COD', '', '2026-03-15 12:58:48', 1040000.00),
(12, 2, 'TPHCM', 'bank', '', '2026-03-15 12:58:48', 336000.00),
(13, 3, 'Đà Nẵng', 'COD', 'pending', '2026-03-15 12:58:48', 280000.00),
(14, 4, 'Cần Thơ', 'bank', '', '2026-03-15 12:58:48', 325000.00),
(15, 5, 'Hải Phòng', 'COD', '', '2026-03-15 12:58:48', 260000.00),
(16, 6, 'Huế', 'COD', 'pending', '2026-03-15 12:58:48', 7200000.00),
(17, 7, 'Nha Trang', 'bank', '', '2026-03-15 12:58:48', 1040000.00),
(18, 8, 'Bình Dương', 'COD', '', '2026-03-15 12:58:48', 1500000.00),
(19, 9, 'Đồng Nai', 'bank', 'pending', '2026-03-15 12:58:48', 1080000.00),
(20, 10, 'Vũng Tàu', 'COD', '', '2026-03-15 12:58:48', 18000000.00);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_user_status` (`user_id`,`status`),
  ADD KEY `idx_orders_user_id` (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
