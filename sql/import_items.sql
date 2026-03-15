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
-- Cấu trúc bảng cho bảng `import_items`
--

CREATE TABLE `import_items` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `import_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `import_items`
--

INSERT INTO `import_items` (`id`, `receipt_id`, `product_id`, `quantity`, `import_price`) VALUES
(1, 1, 1, 10, 12000000.00),
(2, 1, 2, 15, 4000000.00),
(3, 2, 3, 10, 4500000.00),
(4, 2, 4, 8, 5500000.00),
(5, 3, 5, 10, 5000000.00),
(6, 3, 6, 12, 3500000.00),
(7, 4, 7, 10, 3000000.00),
(8, 4, 8, 5, 20000000.00),
(9, 5, 9, 6, 8000000.00),
(10, 5, 10, 10, 3500000.00),
(11, 6, 11, 20, 400000.00),
(12, 6, 12, 30, 80000.00),
(13, 7, 13, 25, 100000.00),
(14, 7, 14, 10, 250000.00),
(15, 8, 15, 12, 200000.00),
(16, 8, 16, 7, 6000000.00),
(17, 9, 17, 10, 800000.00),
(18, 9, 18, 8, 600000.00),
(19, 10, 19, 6, 900000.00),
(20, 10, 20, 5, 15000000.00);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `import_items`
--
ALTER TABLE `import_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `import_items_ibfk_1` (`receipt_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `import_items`
--
ALTER TABLE `import_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `import_items`
--
ALTER TABLE `import_items`
  ADD CONSTRAINT `import_items_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `import_invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `import_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
