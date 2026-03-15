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
-- Cấu trúc bảng cho bảng `import_invoices`
--

CREATE TABLE `import_invoices` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('draft','completed') DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `import_invoices`
--

INSERT INTO `import_invoices` (`id`, `created_at`, `total_amount`, `status`) VALUES
(1, '2026-03-15 12:59:05', 20000000.00, 'completed'),
(2, '2026-03-15 12:59:05', 15000000.00, 'completed'),
(3, '2026-03-15 12:59:05', 10000000.00, ''),
(4, '2026-03-15 12:59:05', 8000000.00, 'completed'),
(5, '2026-03-15 12:59:05', 12000000.00, 'completed'),
(6, '2026-03-15 12:59:05', 9000000.00, ''),
(7, '2026-03-15 12:59:05', 11000000.00, 'completed'),
(8, '2026-03-15 12:59:05', 13000000.00, 'completed'),
(9, '2026-03-15 12:59:05', 7000000.00, ''),
(10, '2026-03-15 12:59:05', 6000000.00, 'completed'),
(11, '2026-03-15 12:59:05', 9000000.00, 'completed'),
(12, '2026-03-15 12:59:05', 8500000.00, 'completed'),
(13, '2026-03-15 12:59:05', 7600000.00, ''),
(14, '2026-03-15 12:59:05', 6400000.00, 'completed'),
(15, '2026-03-15 12:59:05', 5400000.00, 'completed'),
(16, '2026-03-15 12:59:05', 4700000.00, ''),
(17, '2026-03-15 12:59:05', 3900000.00, 'completed'),
(18, '2026-03-15 12:59:05', 3200000.00, 'completed'),
(19, '2026-03-15 12:59:05', 2100000.00, ''),
(20, '2026-03-15 12:59:05', 1800000.00, 'completed');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `import_invoices`
--
ALTER TABLE `import_invoices`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `import_invoices`
--
ALTER TABLE `import_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
