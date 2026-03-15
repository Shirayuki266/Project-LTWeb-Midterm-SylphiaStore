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
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_default` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profit_pct` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `phone`, `address_default`, `role`, `status`, `created_at`, `profit_pct`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'admin@sylphia.vn', '0901234567', 'Hà Nội', 'admin', 1, '2026-03-15 12:50:54', 0.00),
(2, 'manager', 'e10adc3949ba59abbe56e057f20f883e', 'manager@sylphia.vn', '0901234568', 'TP.HCM', 'admin', 1, '2026-03-15 12:50:54', 0.00),
(3, 'nguyenvana', 'e10adc3949ba59abbe56e057f20f883e', 'nguyenvana@email.com', '0902345678', '123 Nguyễn Trãi, Hà Nội', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(4, 'tranvanb', 'e10adc3949ba59abbe56e057f20f883e', 'tranvanb@email.com', '0912345678', '456 Lê Lợi, TP.HCM', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(5, 'lethic', 'e10adc3949ba59abbe56e057f20f883e', 'lethic@email.com', '0923456789', '789 Hai Bà Trưng, Đà Nẵng', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(6, 'phamvand', 'e10adc3949ba59abbe56e057f20f883e', 'phamvand@email.com', '0934567890', '101 Trần Phú, Nha Trang', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(7, 'hoangthie', 'e10adc3949ba59abbe56e057f20f883e', 'hoangthie@email.com', '0945678901', '202 Nguyễn Huệ, Cần Thơ', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(8, 'buiquang', 'e10adc3949ba59abbe56e057f20f883e', 'buiquang@email.com', '0956789012', '303 Bùi Thị Xuân, Huế', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(9, 'doanthit', 'e10adc3949ba59abbe56e057f20f883e', 'doanthit@email.com', '0967890123', '404 Đồng Khởi, Vũng Tàu', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(10, 'vuphuc', 'e10adc3949ba59abbe56e057f20f883e', 'vuphuc@email.com', '0978901234', '505 Võ Văn Tần, Bình Dương', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(11, 'nguyenminh', 'e10adc3949ba59abbe56e057f20f883e', 'nguyenminh@email.com', '0989012345', '606 Cách Mạng, Đồng Nai', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(12, 'tranthanh', 'e10adc3949ba59abbe56e057f20f883e', 'tranthanh@email.com', '0990123456', '707 Lý Thường Kiệt, Long An', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(13, 'letuan', 'e10adc3949ba59abbe56e057f20f883e', 'letuan@email.com', '0901122334', '808 Nguyễn Văn Linh, Cà Mau', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(14, 'phamlan', 'e10adc3949ba59abbe56e057f20f883e', 'phamlan@email.com', '0911122334', '909 Phạm Văn Đồng, An Giang', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(15, 'hoanghiep', 'e10adc3949ba59abbe56e057f20f883e', 'hoanghiep@email.com', '0922233445', '1010 Trường Chinh, Kiên Giang', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(16, 'buitien', 'e10adc3949ba59abbe56e057f20f883e', 'buitien@email.com', '0933344556', '1111 Hùng Vương, Bạc Liêu', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(17, 'doantu', 'e10adc3949ba59abbe56e057f20f883e', 'doantu@email.com', '0944455667', '1212 Lê Thánh Tôn, Sóc Trăng', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(18, 'vukhanh', 'e10adc3949ba59abbe56e057f20f883e', 'vukhanh@email.com', '0955566778', '1313 Nguyễn Đình Chiểu, Trà Vinh', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(19, 'nguyenthuy', 'e10adc3949ba59abbe56e057f20f883e', 'nguyenthuy@email.com', '0966677889', '1414 Phan Bội Châu, Vĩnh Long', 'customer', 1, '2026-03-15 12:50:54', 0.00),
(21, 'bao1234', '$2y$10$uPVp7bSm8cYlfn61bFw6w.L7eB6QfTEKDNME88yw1Ia5Z93oOw636', 'bao13@gmail.com', '0889800760', 'vvv', 'customer', 1, '2026-03-15 13:59:07', 0.00);

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
