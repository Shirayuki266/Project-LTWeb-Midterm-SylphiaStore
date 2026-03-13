<?php
require_once __DIR__ . '/../api/db.php';

class Address {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getProvinces() {
        $result = $this->conn->query("SELECT id, name, code FROM provinces ORDER BY name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getDistricts($provinceId) {
        $stmt = $this->conn->prepare("SELECT id, name, code FROM districts WHERE province_id = ? ORDER BY name");
        $stmt->bind_param("i", $provinceId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getWards($districtId) {
        $stmt = $this->conn->prepare("SELECT id, name, code FROM wards WHERE district_id = ? ORDER BY name");
        $stmt->bind_param("i", $districtId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getFullAddress($provinceId, $districtId, $wardId, $streetAddress = '') {
        $address = [];

        if ($wardId) {
            $stmt = $this->conn->prepare("
                SELECT w.name as ward, d.name as district, p.name as province
                FROM wards w
                JOIN districts d ON w.district_id = d.id
                JOIN provinces p ON d.province_id = p.id
                WHERE w.id = ?
            ");
            $stmt->bind_param("i", $wardId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            if ($result) {
                $address[] = $result['ward'];
                $address[] = $result['district'];
                $address[] = $result['province'];
            }
        }

        if ($streetAddress) {
            array_unshift($address, $streetAddress);
        }

        return implode(', ', $address);
    }
}
?>