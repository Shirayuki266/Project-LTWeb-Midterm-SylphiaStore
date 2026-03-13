-- Update schema for inventory and pricing calculations
USE `Sylphia Shop`;

-- Add columns to sanpham for cost price, stock, profit margin
ALTER TABLE `sanpham` 
ADD COLUMN `gia_von` DECIMAL(12,2) DEFAULT 0,
ADD COLUMN `so_luong_ton` INT DEFAULT 0,
ADD COLUMN `ty_le_loi_nhuan` DECIMAL(5,2) DEFAULT 0.3;

-- Create table for import records
CREATE TABLE IF NOT EXISTS `phieu_nhap_hang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ma_phieu` varchar(50) NOT NULL,
  `ngay_nhap` date NOT NULL,
  `trang_thai` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ma_phieu` (`ma_phieu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create table for import details
CREATE TABLE IF NOT EXISTS `phieu_nhap_hang_chi_tiet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phieu_id` int(11) NOT NULL,
  `san_pham_id` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `gia_nhap` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`phieu_id`) REFERENCES `phieu_nhap_hang`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`san_pham_id`) REFERENCES `sanpham`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Procedure to import goods and update cost price
DELIMITER //

CREATE PROCEDURE sp_nhap_hang(
    IN p_sanpham_id INT,
    IN p_gia_nhap DECIMAL(12,2),
    IN p_so_luong_nhap INT
)
BEGIN
    DECLARE v_gia_von_hien_tai DECIMAL(12,2);
    DECLARE v_so_luong_ton_hien_tai INT;
    DECLARE v_ty_le_loi_nhuan DECIMAL(5,2);
    DECLARE v_gia_von_moi DECIMAL(12,2);
    DECLARE v_gia_ban_moi DECIMAL(12,2);

    -- Get current cost, stock, profit margin
    SELECT gia_von, so_luong_ton, ty_le_loi_nhuan 
    INTO v_gia_von_hien_tai, v_so_luong_ton_hien_tai, v_ty_le_loi_nhuan
    FROM sanpham WHERE id = p_sanpham_id;

    -- Calculate new cost price (average)
    SET v_gia_von_moi = (v_so_luong_ton_hien_tai * v_gia_von_hien_tai + p_so_luong_nhap * p_gia_nhap) / (v_so_luong_ton_hien_tai + p_so_luong_nhap);

    -- Calculate new selling price
    SET v_gia_ban_moi = v_gia_von_moi * (1 + v_ty_le_loi_nhuan);

    -- Update product
    UPDATE sanpham 
    SET gia_von = v_gia_von_moi,
        gia = v_gia_ban_moi,
        so_luong_ton = so_luong_ton + p_so_luong_nhap,
        giamgia = NULL
    WHERE id = p_sanpham_id;
END //

DELIMITER ;

-- Update existing products with default values
UPDATE sanpham SET gia_von = gia / 1.3, so_luong_ton = 100 WHERE gia_von = 0;

