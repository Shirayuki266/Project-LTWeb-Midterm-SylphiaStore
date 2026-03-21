<?php
// api/address.php
require_once __DIR__ . '/db.php'; 

class Address {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    // 1. Lấy danh sách Tỉnh/Thành
    public function getProvinces() {
        $result = $this->db->query("SELECT code, name FROM provinces ORDER BY name ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // 2. Lấy Phường/Xã TRỰC TIẾP từ mã Tỉnh (Bỏ qua Huyện)
    public function getWardsByProvince($provinceCode) {
        // Câu lệnh SQL tìm trong bảng wards nơi có province_code khớp
        $stmt = $this->db->prepare("SELECT code, name FROM wards WHERE province_code = ? ORDER BY name ASC");
        $stmt->bind_param("s", $provinceCode);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 3. Hàm nối chuỗi địa chỉ (Chỉ còn Tỉnh và Xã)
    public function getFullAddressShort($pCode, $wCode, $street = '') {
        $addressParts = [];
        if ($wCode) {
            $stmt = $this->db->prepare("
                SELECT w.name as ward, p.name as province
                FROM wards w
                JOIN provinces p ON w.province_code = p.code
                WHERE w.code = ?
            ");
            $stmt->bind_param("s", $wCode);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            
            if ($res) {
                if (!empty(trim($street))) $addressParts[] = trim($street);
                $addressParts[] = $res['ward'];
                $addressParts[] = $res['province'];
            }
        }
        return implode(', ', $addressParts);
    }
}
?>