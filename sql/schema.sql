-- Sylphia Shop Database Schema
-- Run in phpMyAdmin or mysql CLI: mysql -u root -p `Sylphia Shop` < schema.sql

USE `Sylphia Shop`;

-- Users table
CREATE TABLE IF NOT EXISTS `danh_sach_nguoi_dung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `phonenumber` varchar(20) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Categories
CREATE TABLE IF NOT EXISTS `loaisp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ten_loai` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `loaisp` (`ten_loai`) VALUES 
('Điện thoại'), ('Laptop'), ('Smartwatch'), ('Tai nghe'), ('Phụ kiện');

-- Products
CREATE TABLE IF NOT EXISTS `sanpham` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ten` varchar(255) NOT NULL,
  `gia` decimal(12,2) NOT NULL,
  `hinh` varchar(255) NOT NULL,
  `loai` int(11) NOT NULL,
  `rating` float DEFAULT 0,
  `giamgia` decimal(12,2) DEFAULT 0,
  `mota` text,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`loai`) REFERENCES `loaisp`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders (for checkout)
CREATE TABLE IF NOT EXISTS `donhang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tongtien` decimal(12,2) NOT NULL,
  `trangthai` enum('pending','paid','shipped','delivered') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `danh_sach_nguoi_dung`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items
CREATE TABLE IF NOT EXISTS `donhang_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `donhang_id` int(11) NOT NULL,
  `sanpham_id` int(11) NOT NULL,
  `soluong` int(11) NOT NULL,
  `gia` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`donhang_id`) REFERENCES `donhang`(`id`),
  FOREIGN KEY (`sanpham_id`) REFERENCES `sanpham`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

