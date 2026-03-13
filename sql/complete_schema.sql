-- Sylphia Shop **HOÀN CHỈNH** DB Schema cho đồ án Web bán hàng
-- Import phpMyAdmin: New DB `sylphia_shop` → Import this → Ready 100%!

DROP DATABASE IF EXISTS `sylphia_shop`;
CREATE DATABASE `sylphia_shop` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sylphia_shop`;

-- 1. Users & Auth
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL, -- bcrypt
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `phone` VARCHAR(20),
  `address_default` TEXT,
  `status` ENUM('active','locked') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100),
  `role` ENUM('superadmin','admin') DEFAULT 'admin'
) ENGINE=InnoDB;

-- 2. Categories & Products
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `image` VARCHAR(255),
  `status` ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB;

CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(12,2) NOT NULL,
  `discount_price` DECIMAL(12,2) DEFAULT 0,
  `image` VARCHAR(255),
  `category_id` INT,
  `rating` DECIMAL(3,2) DEFAULT 0,
  `status` ENUM('active','hidden','out_of_stock') DEFAULT 'active',
  `views` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 3. Inventory & Import (Nhập hàng)
CREATE TABLE `inventory` (
  `product_id` INT PRIMARY KEY,
  `stock` INT DEFAULT 0,
  `low_stock_alert` INT DEFAULT 10,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `imports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) UNIQUE,
  `supplier` VARCHAR(255),
  `total_cost` DECIMAL(12,2),
  `status` ENUM('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `import_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `import_id` INT,
  `product_id` INT,
  `cost_price` DECIMAL(12,2),
  `profit_margin` DECIMAL(5,2) DEFAULT 30,
  `quantity` INT,
  `sell_price` DECIMAL(12,2) GENERATED ALWAYS AS (`cost_price` * (1 + `profit_margin` / 100)) STORED,
  FOREIGN KEY (`import_id`) REFERENCES `imports`(`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB;

-- 4. Orders & Checkout
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) UNIQUE,
  `user_id` INT,
  `total` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending','confirmed','shipping','delivered','cancelled') DEFAULT 'pending',
  `payment_method` ENUM('cod','bank_transfer','vnpay','momo') DEFAULT 'cod',
  `shipping_address` TEXT,
  `note` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT,
  `product_id` INT,
  `quantity` INT,
  `price` DECIMAL(12,2),
  `subtotal` DECIMAL(12,2) GENERATED ALWAYS AS (`quantity` * `price`) STORED,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB;

-- 5. Cart (logged users)
CREATE TABLE `carts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNIQUE,
  `session_id` VARCHAR(255),
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `cart_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT,
  `product_id` INT,
  `quantity` INT DEFAULT 1,
  FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB;

-- 6. User Addresses
CREATE TABLE `addresses` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT,
  `name` VARCHAR(100),
  `phone` VARCHAR(20),
  `address` TEXT,
  `is_default` BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

-- 7. Prices History (Giá bán = vốn * (1 + llnn%))
CREATE TABLE `price_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT,
  `cost_price` DECIMAL(12,2),
  `profit_margin` DECIMAL(5,2), -- Lợi nhuận %
  `sell_price` DECIMAL(12,2),
  `updated_by` INT, -- admin_id
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
  FOREIGN KEY (`updated_by`) REFERENCES `admins`(`id`)
) ENGINE=InnoDB;

-- Indexes Perf
CREATE INDEX idx_order_user_status ON `orders` (user_id, status);
CREATE INDEX idx_product_cat ON `products` (category_id, status);
CREATE INDEX idx_inventory_stock ON `inventory` (stock);
CREATE INDEX idx_cart_user ON `cart_items` (cart_id);

-- TRIGGERS
DELIMITER //
CREATE TRIGGER tr_inventory_update
AFTER INSERT ON `order_items`
FOR EACH ROW 
UPDATE `inventory` SET stock = stock - NEW.quantity WHERE product_id = NEW.product_id;
//

DELIMITER ;

-- **DEMO DATA** (Import ready!)
INSERT INTO `categories` VALUES 
(1,'Điện thoại','Smartphones'), (2,'Laptop','Notebooks'), (3,'Phụ kiện','Accessories'), 
(4,'Tai nghe','Headphones'), (5,'Smartwatch','Wearables');

INSERT INTO `admins` VALUES 
(1,'admin','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin@sylphia.vn','superadmin'); -- pass: password

INSERT INTO `users` VALUES 
(1,'user1','$2y$10$...','user1@test.vn','0123456789','123 HCM','active',NOW());

INSERT INTO `products` VALUES 
(1,'iPhone 16 Pro',42990000,'iphone-16-pro-max.webp',1,'Flagship...',39990000,4.8,'active',0,NOW()),
(2,'Asus ROG',22990000,'asusrog.webp',2,'Gaming...',20990000,4.7,'active',0,NOW());

-- Inventory
INSERT INTO `inventory` VALUES 
(1,50,10), (2,25,5);

-- Imports sample
INSERT INTO `imports` VALUES (1,'PN001','Supplier A',100000000,'completed',NOW());
INSERT INTO `import_items` VALUES (1,1,1,32000000,30,10);

-- Orders sample
INSERT INTO `orders` VALUES (1,'DH001',1,85000000,'delivered','cod','123 HCM','OK',NOW());
INSERT INTO `order_items` VALUES (1,1,1,10,42990000);

-- Ready! Import → Test full đồ án.

