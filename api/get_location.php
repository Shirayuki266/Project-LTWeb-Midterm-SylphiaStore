<?php
// api/get_location.php
require_once 'db.php';
require_once 'address.php'; 

header('Content-Type: application/json');
$address = new Address($conn);
$action = $_GET['action'] ?? '';

if ($action === 'get_provinces') {
    echo json_encode($address->getProvinces());
} 
elseif ($action === 'get_wards') {
    // Lấy danh sách xã theo tỉnh
    $pCode = $_GET['province_code'] ?? ''; 
    echo json_encode($address->getWardsByProvince($pCode));
}
?>