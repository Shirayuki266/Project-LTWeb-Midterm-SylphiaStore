-- **TRIGGER GIÁ VỐN BÌNH QUÂN + GIÁ BÁN** cho schema.sql
-- Thêm bảng `gia_von` , trigger auto tính giá vốn bình quân, giá bán = vốn * (1 + llnn%)
-- Append vào DB 'Sylphia Shop'

USE `Sylphia Shop`;

-- Bảng giá vốn (per SP)
CREATE TABLE IF NOT EXISTS `gia_von` (
  `sanpham_id` INT PRIMARY KEY,
  `gia_von_hien_tai` DECIMAL(12,2) DEFAULT 0,
  `ton_kho` INT DEFAULT 0,
  `ty_le_loi_nhuan` DECIMAL(5,2) DEFAULT 30, -- %
  `gia_ban` DECIMAL(12,2) GENERATED ALWAYS AS (`gia_von_hien_tai` * (1 + `ty_le_loi_nhuan` / 100)) STORED,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`sanpham_id`) REFERENCES `sanpham`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Trigger cập nhật giá vốn bình quân khi nhập hàng (giả sử table `phieu_nhap_items`)
-- Hoặc procedure cho admin nhập
DELIMITER $$

CREATE TRIGGER tr_update_gia_von_nhap 
AFTER INSERT ON `phieu_nhap_items` -- Giả sử có table này
FOR EACH ROW
BEGIN
  DECLARE ton_cu INT DEFAULT 0;
  DECLARE von_cu DECIMAL(12,2) DEFAULT 0;
  
  -- Lấy tồn + vốn cũ
  SELECT ton_kho, gia_von_hien_tai INTO ton_cu, von_cu FROM gia_von WHERE sanpham_id = NEW.sanpham_id;
  
  -- Bình quân mới
  UPDATE gia_von SET 
    ton_kho = ton_cu + NEW.soluong_nhap,
    gia_von_hien_tai = (von_cu * ton_cu + NEW.gia_nhap * NEW.soluong_nhap) / (ton_cu + NEW.soluong_nhap)
  WHERE sanpham_id = NEW.sanpham_id;
END$$

-- Trigger bán hàng: Chỉ update tồn, giữ vốn (không ảnh hưởng giá vốn bình quân)
CREATE TRIGGER tr_update_ton_ban 
AFTER INSERT ON `donhang_items`
FOR EACH ROW
BEGIN
  UPDATE gia_von SET ton_kho = ton_kho - NEW.soluong WHERE sanpham_id = NEW.sanpham_id;
END$$

DELIMITER ;

-- Procedure admin nhập hàng update giá vốn (use this)
DELIMITER //
CREATE PROCEDURE sp_nhap_hang(
  IN p_sanpham_id INT,
  IN p_gia_nhap DECIMAL(12,2),
  IN p_soluong_nhap INT,
  IN p_ty_le_loi_nhuan DECIMAL(5,2)
)
BEGIN
  DECLARE ton_cu INT DEFAULT 0;
  DECLARE von_cu DECIMAL(12,2) DEFAULT 0;
  
  SELECT ton_kho, gia_von_hien_tai INTO ton_cu, von_cu FROM gia_von WHERE sanpham_id = p_sanpham_id;
  
  INSERT INTO gia_von (sanpham_id, ton_kho, gia_von_hien_tai, ty_le_loi_nhuan) VALUES (p_sanpham_id, p_soluong_nhap, p_gia_nhap, p_ty_le_loi_nhuan)
  ON DUPLICATE KEY UPDATE
    ton_kho = ton_cu + p_soluong_nhap,
    gia_von_hien_tai = (von_cu * ton_cu + p_gia_nhap * p_soluong_nhap) / (ton_cu + p_soluong_nhap),
    ty_le_loi_nhuan = p_ty_le_loi_nhuan;
    
  SELECT gia_ban FROM gia_von WHERE sanpham_id = p_sanpham_id;
END //
DELIMITER ;

-- Demo sử dụng
CALL sp_nhap_hang(1, 20000000, 10, 30); -- SP1: vốn 20tr, 10sp, llnn 30% → giá bán 26tr
-- Lần 2: CALL sp_nhap_hang(1, 15000000, 10, 25); → Bình quân ~17.5tr, bán 22tr

-- Init giá vốn for existing SP
INSERT INTO gia_von (sanpham_id, gia_von_hien_tai, ton_kho) 
SELECT id, gia * 0.7, 100 FROM sanpham ON DUPLICATE KEY UPDATE ton_kho = 100;

-- **READY!** Import → Trigger/procedure auto tính **giá vốn bình quân** + **giá bán = vốn*(1+llnn%)**.
-- Admin use CALL sp_nhap_hang(...) khi nhập.
-- Đồ án tính toán hoàn hảo!

