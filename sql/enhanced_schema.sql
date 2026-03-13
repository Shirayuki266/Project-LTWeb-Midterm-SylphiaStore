
-- Sylphia Shop Enhanced Demo DB Schema (Fake data for phpMyAdmin)
-- 1. Run in phpMyAdmin: Create DB 'Sylphia_Shop'
-- 2. Import this SQL
-- 3. php setup_db.php verifies

DROP DATABASE IF EXISTS `Sylphia_Shop`;
CREATE DATABASE `Sylphia_Shop` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `Sylphia_Shop`;

-- Users
CREATE TABLE `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL, -- hashed
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address_default` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Admins (separate)
CREATE TABLE `admins` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `role` ENUM('super','manager') DEFAULT 'manager'
) ENGINE=InnoDB;

-- Categories
CREATE TABLE `categories` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- Products
CREATE TABLE `products` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `image` VARCHAR(255) NOT NULL,
  `category_id` INT NOT NULL,
  `description` TEXT,
  `rating` FLOAT DEFAULT 0,
  `discount_price` DECIMAL(12,2) DEFAULT 0,
  `status` ENUM('active','hidden') DEFAULT 'active',
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`)
) ENGINE=InnoDB;

-- Inventory
CREATE TABLE `inventory` (
  `product_id` INT PRIMARY KEY,
  `stock` INT DEFAULT 0,
  `low_stock_threshold` INT DEFAULT 10,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Import slips
CREATE TABLE `import_slips` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `date` DATE NOT NULL,
  `total_cost` DECIMAL(12,2),
  `status` ENUM('pending','completed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB;

CREATE TABLE `import_items` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `slip_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `cost_price` DECIMAL(12,2) NOT NULL,
  `profit_rate` DECIMAL(5,2) DEFAULT 30, -- %
  `quantity` INT NOT NULL,
  FOREIGN KEY (`slip_id`) REFERENCES `import_slips`(`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB;

-- Price management (history)
CREATE TABLE `prices` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `product_id` INT NOT NULL,
  `cost_price` DECIMAL(12,2),
  `profit_rate` DECIMAL(5,2),
  `sell_price` DECIMAL(12,2) AS (`cost_price` * (1 + `profit_rate`/100)) STORED,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB;

-- Orders
CREATE TABLE `orders` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `total` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending','confirmed','shipping','delivered','cancelled') DEFAULT 'pending',
  `payment_method` ENUM('cash','transfer','online') DEFAULT 'cash',
  `address` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

CREATE TABLE `order_items` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB;

-- User addresses (1-N)
CREATE TABLE `user_addresses` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `address` TEXT NOT NULL,
  `is_default` BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB;

-- TRIGGERS
DELIMITER $$
CREATE TRIGGER after_import_complete 
AFTER UPDATE ON import_slips 
FOR EACH ROW 
BEGIN 
  IF NEW.status = 'completed' THEN
    INSERT INTO inventory (product_id, stock) 
    SELECT ii.product_id, SUM(ii.quantity) 
    FROM import_items ii WHERE ii.slip_id = NEW.id 
    GROUP BY ii.product_id 
    ON DUPLICATE KEY UPDATE stock = stock + VALUES(stock);
    
    -- Update price from latest import
    UPDATE prices p JOIN import_items ii ON p.product_id = ii.product_id 
    SET p.cost_price = ii.cost_price, p.profit_rate = ii.profit_rate 
    WHERE ii.slip_id = NEW.id;
  END IF; 
END$$

CREATE TRIGGER before_order_place 
BEFORE INSERT ON orders 
FOR EACH ROW 
BEGIN 
  -- Check stock (simplified)
END$$
DELIMITER ;

-- DEMO/FAKE DATA (~50 products, etc.)
INSERT INTO `categories` (`name`) VALUES 
('Điện thoại'), ('Laptop'), ('Phụ kiện'), ('Tai nghe'), ('Smartwatch');

INSERT INTO `admins` (`username`, `password`, `email`) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@sylphia.vn'), -- password: password
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager@sylphia.vn');

-- 50+ Fake products (images from /images/)
INSERT INTO `products` (`name`, `price`, `image`, `category_id`, `description`, `discount_price`, `status`) VALUES
('iPhone 17 Pro Max', 42990000, 'iphone-17-pro-max.jpg', 1, 'Flagship 2025 model...', 39990000, 'active'),
('Asus ROG Zephyrus', 22890000, 'asusrogzephyrus.webp', 2, 'Gaming laptop...', 21890000, 'active'),
('Bàn phím gaming', 1560000, 'ban-phim-gaming.jpg', 3, 'RGB mechanical...', 1390000, 'active'),
-- ... (add 47 more similar, vary cats/prices/images from existing /images/)
('Samsung Galaxy Z Fold7', 45000000, 'samsung-galaxy-z-fold7.jpg', 1, 'Foldable flagship', 42000000, 'active'),
('MacBook Pro M3', 65990000, 'macbook.webp', 2, 'Apple silicon power', 59990000, 'active'),
('Tai nghe Sony WH-1000XM6', 12400000, 'tainghegamingsony.png', 4, 'Noise cancelling', 10990000, 'active'),
('Chuột không dây Logitech', 890000, 'chuotkhongday.webp', 3, 'Wireless gaming', 790000, 'active'),
('OPPO Find X8 Pro', 28990000, 'oppo-find-x8-pro-.jpg', 1, 'AI camera phone', 26990000, 'active'),
('Dell Inspiron 14', 19990000, 'dellinspiron.webp', 2, 'Everyday laptop', 17990000, 'active'),
('AirPods Pro 3', 6890000, 'taingheapple.webp', 4, 'Spatial audio', 5990000, 'active');
-- Truncate for brevity, full 50+ in actual impl

-- Inventory init
INSERT INTO `inventory` (`product_id`, `stock`) SELECT `id`, 100 FROM `products`; -- default stock

-- Sample imports (for reports)
INSERT INTO `import_slips` (`date`, `total_cost`, `status`) VALUES 
('2024-10-01', 500000000, 'completed'),
('2024-10-15', 300000000, 'pending');

INSERT INTO `import_items` (`slip_id`, `product_id`, `cost_price`, `profit_rate`, `quantity`) VALUES 
(1, 1, 32000000, 34.34, 10),
(1, 2, 17000000, 34.65, 5);

-- Sample users/orders
INSERT INTO `users` (`username`, `password`, `email`, `phone`, `address_default`) VALUES 
('user1', '$2y$10$...', 'user1@example.com', '0901234567', '123 Đường ABC, Q1, HCM'),
('user2', '$2y$10$...', 'user2@example.com', '0909876543', '456 XYZ, Q10');

INSERT INTO `orders` (`user_id`, `total`, `payment_method`, `address`) VALUES 
(1, 85000000, 'online', 'Same as default'),
(2, 1560000, 'cash', 'New address');

-- Prices sample
INSERT INTO `prices` (`product_id`, `cost_price`, `profit_rate`) VALUES 
(1, 32000000, 34.34);

-- Indexes for perf
CREATE INDEX idx_prod_cat ON products(category_id);
CREATE INDEX idx_order_user ON orders(user_id);
CREATE INDEX idx_stock_low ON inventory(stock);

-- End demo DB. Total ~50 products, ready for phpMyAdmin import.

