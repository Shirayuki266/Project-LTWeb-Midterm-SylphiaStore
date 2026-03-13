-- Create address tables for Vietnam locations
USE `Sylphia Shop`;

-- Provinces/Cities table
CREATE TABLE IF NOT EXISTS `provinces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Districts table
CREATE TABLE IF NOT EXISTS `districts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `province_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`province_id`) REFERENCES `provinces`(`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wards/Communes table
CREATE TABLE IF NOT EXISTS `wards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `district_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`district_id`) REFERENCES `districts`(`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add address fields to users table (only if they don't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'danh_sach_nguoi_dung' AND COLUMN_NAME = 'province_id') = 0,
    'ALTER TABLE `danh_sach_nguoi_dung` ADD COLUMN `province_id` int(11) DEFAULT NULL, ADD COLUMN `district_id` int(11) DEFAULT NULL, ADD COLUMN `ward_id` int(11) DEFAULT NULL, ADD COLUMN `street_address` varchar(255) DEFAULT NULL',
    'SELECT "Address columns already exist in users table"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add address column to orders table (only if it doesn't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'donhang' AND COLUMN_NAME = 'dia_chi') = 0,
    'ALTER TABLE `donhang` ADD COLUMN `dia_chi` varchar(500) DEFAULT NULL',
    'SELECT "Address column already exists in orders table"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Insert major Vietnamese provinces/cities (only if table is empty)
INSERT IGNORE INTO `provinces` (`name`, `code`) VALUES
('Hà Nội', 'HN'),
('Hồ Chí Minh', 'HCM'),
('Đà Nẵng', 'DN'),
('Hải Phòng', 'HP'),
('Cần Thơ', 'CT'),
('An Giang', 'AG'),
('Bà Rịa - Vũng Tàu', 'BRVT'),
('Bắc Giang', 'BG'),
('Bắc Kạn', 'BK'),
('Bạc Liêu', 'BL'),
('Bắc Ninh', 'BN'),
('Bến Tre', 'BT'),
('Bình Định', 'BD'),
('Bình Dương', 'BDU'),
('Bình Phước', 'BP'),
('Bình Thuận', 'BT'),
('Cà Mau', 'CM'),
('Cao Bằng', 'CB'),
('Đắk Lắk', 'DL'),
('Đắk Nông', 'DN'),
('Điện Biên', 'DB'),
('Đồng Nai', 'DNA'),
('Đồng Tháp', 'DT'),
('Gia Lai', 'GL'),
('Hà Giang', 'HG'),
('Hà Nam', 'HNA'),
('Hà Tĩnh', 'HT'),
('Hải Dương', 'HD'),
('Hậu Giang', 'HGI'),
('Hòa Bình', 'HB'),
('Hưng Yên', 'HY'),
('Khánh Hòa', 'KH'),
('Kiên Giang', 'KG'),
('Kon Tum', 'KT'),
('Lai Châu', 'LC'),
('Lâm Đồng', 'LD'),
('Lạng Sơn', 'LS'),
('Lào Cai', 'LCA'),
('Long An', 'LA'),
('Nam Định', 'ND'),
('Nghệ An', 'NA'),
('Ninh Bình', 'NB'),
('Ninh Thuận', 'NT'),
('Phú Thọ', 'PT'),
('Quảng Bình', 'QB'),
('Quảng Nam', 'QNA'),
('Quảng Ngãi', 'QN'),
('Quảng Ninh', 'QNI'),
('Quảng Trị', 'QT'),
('Sóc Trăng', 'ST'),
('Sơn La', 'SL'),
('Tây Ninh', 'TN'),
('Thái Bình', 'TB'),
('Thái Nguyên', 'TNG'),
('Thanh Hóa', 'TH'),
('Thừa Thiên Huế', 'TTH'),
('Tiền Giang', 'TG'),
('Trà Vinh', 'TV'),
('Tuyên Quang', 'TQ'),
('Vĩnh Long', 'VL'),
('Vĩnh Phúc', 'VP'),
('Yên Bái', 'YB');

-- Insert sample districts for major cities (you can expand this)
INSERT IGNORE INTO `districts` (`province_id`, `name`, `code`) VALUES
-- Hà Nội
(1, 'Quận Ba Đình', 'BAD'),
(1, 'Quận Hoàn Kiếm', 'HK'),
(1, 'Quận Tây Hồ', 'TH'),
(1, 'Quận Long Biên', 'LB'),
(1, 'Quận Cầu Giấy', 'CG'),
(1, 'Quận Đống Đa', 'DD'),
(1, 'Quận Hai Bà Trưng', 'HBT'),
(1, 'Quận Hoàng Mai', 'HM'),
(1, 'Quận Thanh Xuân', 'TX'),
(1, 'Huyện Sóc Sơn', 'SS'),
(1, 'Huyện Đông Anh', 'DA'),
(1, 'Huyện Gia Lâm', 'GL'),
-- Hồ Chí Minh
(2, 'Quận 1', 'Q1'),
(2, 'Quận 2', 'Q2'),
(2, 'Quận 3', 'Q3'),
(2, 'Quận 4', 'Q4'),
(2, 'Quận 5', 'Q5'),
(2, 'Quận 6', 'Q6'),
(2, 'Quận 7', 'Q7'),
(2, 'Quận 8', 'Q8'),
(2, 'Quận 9', 'Q9'),
(2, 'Quận 10', 'Q10'),
(2, 'Quận 11', 'Q11'),
(2, 'Quận 12', 'Q12'),
(2, 'Quận Bình Tân', 'BTAN'),
(2, 'Quận Bình Thạnh', 'BTH'),
(2, 'Quận Gò Vấp', 'GV'),
(2, 'Quận Phú Nhuận', 'PN'),
(2, 'Quận Tân Bình', 'TBIN'),
(2, 'Quận Tân Phú', 'TP'),
(2, 'Quận Thủ Đức', 'TD'),
(2, 'Huyện Bình Chánh', 'BC'),
(2, 'Huyện Cần Giờ', 'CGIO'),
(2, 'Huyện Củ Chi', 'CC'),
(2, 'Huyện Hóc Môn', 'HMON'),
(2, 'Huyện Nhà Bè', 'NBE');

-- Insert sample wards for some districts
INSERT IGNORE INTO `wards` (`district_id`, `name`, `code`) VALUES
-- Quận Hoàn Kiếm, Hà Nội
(2, 'Phường Hàng Bạc', 'HB'),
(2, 'Phường Hàng Bài', 'HBA'),
(2, 'Phường Hàng Bồ', 'HBO'),
(2, 'Phường Hàng Bông', 'HBON'),
(2, 'Phường Hàng Đào', 'HD'),
(2, 'Phường Hàng Gai', 'HG'),
(2, 'Phường Hàng Mã', 'HMA'),
(2, 'Phường Hàng Trống', 'HTR'),
-- Quận 1, HCM
(12, 'Phường Bến Nghé', 'BN'),
(12, 'Phường Bến Thành', 'BT'),
(12, 'Phường Cô Giang', 'CG'),
(12, 'Phường Cầu Kho', 'CK'),
(12, 'Phường Cầu Ông Lãnh', 'COL'),
(12, 'Phường Đa Kao', 'DK'),
(12, 'Phường Nguyễn Cư Trinh', 'NCT'),
(12, 'Phường Nguyễn Thái Bình', 'NTB');