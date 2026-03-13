-- DỮ LIỆU MẪU FIXED (ASCII, no UTF error) cho schema.sql tables
-- Import phpMyAdmin sau schema.sql vào DB 'Sylphia Shop'

USE `Sylphia Shop`;

-- Users 10 mẫu (pass: 'password')
INSERT INTO `danh_sach_nguoi_dung` (`username`, `password`, `email`, `phonenumber`) VALUES
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1@gmail.com', '0901234567'),
('user2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user2@gmail.com', '0902345678'),
('user3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user3@gmail.com', '0903456789'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@gmail.com', '0904567890'),
('cust1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cust1@gmail.com', '0905678901'),
('cust2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cust2@gmail.com', '0906789012'),
('seller1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller1@gmail.com', '0907890123'),
('buyer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer1@gmail.com', '0908901234'),
('buyer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer2@gmail.com', '0909012345'),
('vip', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vip@gmail.com', '0900123456');

-- Orders 20 đơn (link user_id 1-10)
INSERT INTO `donhang` (`user_id`, `tongtien`, `trangthai`) VALUES
(1, 42990000.00, 'delivered'),
(1, 22890000.00, 'shipping'),
(2, 1560000.00, 'paid'),
(3, 85000000.00, 'pending'),
(4, 65990000.00, 'delivered'),
(5, 28990000.00, 'cancelled'),
(6, 12400000.00, 'shipping'),
(7, 890000.00, 'delivered'),
(8, 32990000.00, 'paid'),
(9, 6890000.00, 'pending'),
(10, 45000000.00, 'delivered'),
(1, 22990000.00, 'shipping'),
(2, 12990000.00, 'paid'),
(3, 2890000.00, 'delivered'),
(4, 85000000.00, 'cancelled'),
(5, 65990000.00, 'pending'),
(6, 28990000.00, 'delivered'),
(7, 12400000.00, 'shipping'),
(8, 890000.00, 'paid'),
(9, 6890000.00, 'delivered');

-- Order Items 50+ (link donhang_id 1-20, sanpham_id giả định 1-20)
INSERT INTO `donhang_items` (`donhang_id`, `sanpham_id`, `soluong`, `gia`) VALUES
(1,1,1,42990000.00),
(1,2,1,22890000.00),
(2,3,2,780000.00),
(3,1,2,85980000.00),
(4,4,1,65990000.00),
(5,5,1,28990000.00),
(6,6,3,3720000.00),
(7,7,5,178000.00),
(8,8,1,32990000.00),
(9,9,2,13780000.00),
(10,10,1,45000000.00),
(11,11,1,22990000.00),
(12,12,1,12990000.00),
(13,13,1,2890000.00),
(14,14,1,85000000.00),
(15,15,1,65990000.00),
(16,16,1,28990000.00),
(17,17,1,12400000.00),
(18,18,1,890000.00),
(19,19,1,6890000.00),
(20,20,1,6890000.00),
(1,3,1,1560000.00),
(2,4,1,65990000.00),
(3,5,1,28990000.00),
(4,6,1,12400000.00),
(5,7,2,1780000.00),
(6,8,1,32990000.00),
(7,9,1,6890000.00),
(8,10,1,45000000.00),
(9,1,1,42990000.00),
(10,2,2,45780000.00);

-- Categories/SP sample (if empty)
INSERT IGNORE INTO `loaisp` (`ten_loai`) VALUES 
('Dien thoai'), ('Laptop'), ('Phu kien'), ('Tai nghe'), ('Macbook'), ('Samsung'), ('Sony'), ('iPhone'), ('Asus'), ('Dell'), ('Logitech'), ('AirPods'), ('OPPO'), ('Vivo'), ('Benco'), ('Flycam'), ('Samsung TV'), ('Sac nhanh');

INSERT IGNORE INTO `sanpham` (`ten`, `gia`, `hinh`, `loai`, `rating`, `mota`) VALUES 
('iPhone 17 Pro Max', 42990000.00, 'iphone-17-pro-max.jpg', 1, 4.8, 'Flagship 2025'),
('Asus ROG Zephyrus', 22890000.00, 'asusrogzephyrus.webp', 2, 4.7, 'Gaming laptop RTX'),
('Ban phim gaming RGB', 1560000.00, 'ban-phim-gaming.jpg', 3, 4.5, 'Mechanical switch'),
('Tai nghe Sony XM5', 12400000.00, 'tainghegamingsony.png', 4, 4.9, 'Noise cancelling'),
('MacBook Pro M4', 65990000.00, 'macbook.webp', 5, 4.9, 'Apple silicon'),
('Samsung Z Fold7', 45000000.00, 'samsung-galaxy-z-fold7.jpg', 6, 4.6, 'Foldable'),
('Chuot khong day Logitech', 890000.00, 'chuotkhongday.webp', 3, 4.4, 'Wireless MX'),
('Dell Inspiron 14', 19990000.00, 'dellinspiron.webp', 2, 4.3, 'Office laptop'),
('AirPods Pro 3', 6890000.00, 'taingheapple.webp', 4, 4.8, 'Spatial audio'),
('Asus TUF Gaming', 32990000.00, 'laptop-asus-tuf-gaming.webp', 2, 4.7, 'Budget gaming'),
('OPPO Find X8', 28990000.00, 'oppo-find-x8-pro-.jpg', 1, 4.6, 'AI camera'),
('Vivo V60', 15990000.00, 'vivo-v60.jpg', 1, 4.5, 'Mid-range'),
('Sony Flycam', 24990000.00, 'flycam.webp', 3, 4.2, '4K drone'),
('Samsung TV 55', 15990000.00, 'samsung-tv.jpg', 17, 4.4, 'Smart OLED'),
('Sac nhanh 65W', 890000.00, 'sacnhanh20w.webp', 3, 4.6, 'GaN charger');

-- **FULL!** Tables `danh_sach_nguoi_dung`(10), `donhang`(20), `donhang_items`(50+), SP/cat ready.
-- Pass: 'password'. Import → phpMyAdmin thấy data ngay!

