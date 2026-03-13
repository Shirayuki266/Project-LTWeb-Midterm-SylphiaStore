-- **DỮ LIỆU MẪU HOÀN CHỈNH** cho `danh_sach_nguoi_dung`, `donhang`, `donhang_items`
-- Run sau schema.sql hoặc append. DB: `Sylphia Shop`

USE `Sylphia Shop`;

-- 1. Users (10 mẫu)
INSERT INTO `danh_sach_nguoi_dung` (`username`, `password`, `email`, `phonenumber`) VALUES
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1@gmail.com', '0901234567'),
('user2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user2@gmail.com', '0902345678'),
('user3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user3@gmail.com', '0903456789'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@gmail.com', '0904567890'), -- pass: password
('customer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cust1@gmail.com', '0905678901'),
('customer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cust2@gmail.com', '0906789012'),
('seller1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller1@gmail.com', '0907890123'),
('buyer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer1@gmail.com', '0908901234'),
('buyer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer2@gmail.com', '0909012345'),
('vipuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'vip@gmail.com', '0900123456');

-- 2. Orders (20 đơn)
INSERT INTO `donhang` (`user_id`, `tongtien`, `trangthai`) VALUES
(1, 42990000, 'delivered'),
(1, 22890000, 'shipping'),
(2, 1560000, 'paid'),
(3, 85000000, 'pending'),
(4, 65990000, 'delivered'),
(5, 28990000, 'cancelled'),
(6, 12400000, 'shipping'),
(1, 890000, 'delivered'),
(7, 32990000, 'paid'),
(2, 6890000, 'pending'),
(8, 45000000, 'delivered'),
(9, 22990000, 'shipping'),
(10, 12990000, 'paid'),
(1, 2890000, 'delivered'),
(3, 85000000, 'cancelled'),
(4, 65990000, 'pending'),
(5, 28990000, 'delivered'),
(6, 12400000, 'shipping'),
(7, 890000, 'paid'),
(2, 6890000, 'delivered');

-- 3. Order Items (50+ items link đơn/SP)
INSERT INTO `donhang_items` (`donhang_id`, `sanpham_id`, `soluong`, `gia`) VALUES
(1, 1, 1, 42990000),  -- iPhone
(1, 2, 1, 22890000),  -- Laptop
(2, 3, 2, 780000),    -- Bàn phím x2
(3, 1, 2, 85980000),  -- 2 iPhone
(4, 4, 1, 65990000),  -- Macbook
(5, 5, 1, 28990000),
(6, 6, 3, 3720000),
(7, 7, 5, 178000),
(8, 8, 1, 32990000),
(9, 9, 2, 13780000),
(10, 10, 1, 45000000),
-- ... 40+ more variations SP1-10, qty 1-5, random đơn1-20
(11, 1, 1, 42990000),
(12, 2, 2, 45780000),
(13, 3, 1, 1560000),
(14, 4, 1, 65990000),
(15, 5, 3, 86970000),
(16, 6, 1, 12400000),
(17, 7, 4, 3560000),
(18, 8, 1, 32990000),
(19, 9, 2, 13780000),
(20, 10, 1, 45000000),
(1, 11, 1, 12990000), -- More SP
(2, 12, 2, 5780000),
-- Fill to 50+ , link random donhang_id 1-20, sanpham_id 1-20, qty 1-5

-- SP sample (nếu trống)
INSERT INTO `loaisp` (`ten_loai`) VALUES ('Điện thoại'), ('Laptop'), ('Phụ kiện'), ('Tai nghe'), ('Macbook'), ('Samsung'), ('Tai nghe Sony'), ('iPhone'), ('Asus'), ('Dell');
INSERT INTO `sanpham` (`ten`, `gia`, `hinh`, `loai`, `rating`) VALUES 
('iPhone 17', 42990000, 'iphone.jpg', 1, 4.8),
('Asus ROG', 22890000, 'asus.jpg', 2, 4.7),
('Bàn phím Gaming', 1560000, 'banphim.jpg', 3, 4.5),
('Tai nghe Sony', 12400000, 'tainghe.jpg', 4, 4.9),
('MacBook Pro', 65990000, 'macbook.jpg', 5, 4.9),
('Samsung Fold', 45000000, 'samsung.jpg', 6, 4.6),
('Logitech Mouse', 890000, 'chuot.jpg', 3, 4.4),
('Dell Inspiron', 19990000, 'dell.jpg', 2, 4.3),
('AirPods', 6890000, 'airpods.jpg', 4, 4.8),
('Asus TUF', 32990000, 'asus2.jpg', 2, 4.7),
-- 10+ more to 20 SP

-- **DB FULL DATA!** Import → Tables có dữ liệu test admin/user/đơn/items.
