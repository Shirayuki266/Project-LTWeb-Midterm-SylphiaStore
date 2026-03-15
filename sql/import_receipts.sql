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
-- Cấu trúc bảng cho bảng `import_receipts`
--

CREATE TABLE `import_receipts` (
  `id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('draft','completed') DEFAULT 'draft'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `import_receipts`
--

INSERT INTO `import_receipts` (`id`, `created_at`, `status`) VALUES
(1, '2026-03-15 12:46:00', 'completed'),
(2, '2026-03-15 13:05:09', 'completed'),
(3, '2026-03-15 13:05:09', 'completed'),
(4, '2026-03-15 13:05:09', 'completed'),
(5, '2026-03-15 13:05:09', 'completed'),
(6, '2026-03-15 13:05:09', 'completed'),
(7, '2026-03-15 13:05:09', 'completed'),
(8, '2026-03-15 13:05:09', 'completed'),
(9, '2026-03-15 13:05:09', 'completed'),
(10, '2026-03-15 13:05:09', 'completed');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `import_receipts`
--
ALTER TABLE `import_receipts`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `import_receipts`
--
ALTER TABLE `import_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
