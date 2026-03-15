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
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `profit_percent` decimal(5,2) DEFAULT 20.00,
  `price` decimal(10,2) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `cost` decimal(10,2) DEFAULT 0.00,
  `profit_pct` decimal(5,2) DEFAULT 20.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `category_id`, `description`, `unit`, `image`, `stock`, `cost_price`, `profit_percent`, `price`, `status`, `created_at`, `cost`, `profit_pct`) VALUES
(1, 'iPhone 13 128GB', 1, 'Apple A15 Bionic', 'cái', 'https://cdn.tgdd.vn/Products/Images/42/223602/iphone-13-midnight-2-600x600.jpg', 50, 12000000.00, 20.00, 14400000.00, 1, '2026-03-15 12:58:21', 12000000.00, 20.00),
(2, 'Samsung Galaxy A14 5G', 2, 'Pin 5000mAh', 'cái', 'https://cdn.mediamart.vn/images/product/dien-thoai-samsung-galaxy-a14-5g-a146p-4128g-dm_41ac575a.webp', 60, 4000000.00, 25.00, 5000000.00, 1, '2026-03-15 12:58:21', 4000000.00, 25.00),
(3, 'Xiaomi Redmi Note 12', 3, 'Snapdragon', 'cái', 'https://cdn.tgdd.vn/Products/Images/42/259286/xiaomi-redmi-note-12-600x600.jpg', 40, 4500000.00, 20.00, 5400000.00, 1, '2026-03-15 12:58:21', 4500000.00, 20.00),
(4, 'OPPO A78', 4, 'Camera đẹp', 'cái', 'https://cdn.tgdd.vn/Products/Images/42/299631/oppo-a78-5g-thumb-600x600.jpg', 30, 5500000.00, 20.00, 6600000.00, 1, '2026-03-15 12:58:21', 5500000.00, 20.00),
(5, 'Vivo Y36', 5, 'Thiết kế đẹp', 'cái', 'https://cdn.tgdd.vn/Products/Images/42/307203/vivo-y36-black-thumbnew-600x600.jpg', 45, 5000000.00, 18.00, 5900000.00, 1, '2026-03-15 12:58:21', 5000000.00, 18.00),
(6, 'Realme C55', 6, 'Sạc nhanh', 'cái', 'https://cdn2.fptshop.com.vn/unsafe/512x0/filters:format(webp):quality(75)/2023_3_11_638141305319388581_realme-c55-vang-3.jpg', 35, 3500000.00, 20.00, 4200000.00, 1, '2026-03-15 12:58:21', 3500000.00, 20.00),
(8, 'Macbook Air M1', 8, 'Laptop Apple', 'cái', 'https://cdn.tgdd.vn/Files/2022/06/18/1440664/so-sanh-macbook-air-m1-va-macbook-air-m2-dau-la-1.jpg', 15, 20000000.00, 20.00, 24000000.00, 1, '2026-03-15 12:58:21', 20000000.00, 20.00),
(9, 'iPad Gen 9', 9, 'Tablet Apple', 'cái', 'https://cdn.tgdd.vn/Products/Images/522/250730/iPad-9-wifi-den-600x600.jpg', 18, 8000000.00, 20.00, 9600000.00, 1, '2026-03-15 12:58:21', 8000000.00, 20.00),
(20, 'Samsung Galaxy S23', 2, 'Flagship Samsung', 'cái', 'https://cdn.tgdd.vn/Products/Images/42/264060/samsung-galaxy-s23-600x600.jpg', 22, 15000000.00, 20.00, 18000000.00, 1, '2026-03-15 12:58:21', 15000000.00, 20.00);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_products_cost_status` (`cost`,`status`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
