<?php
require_once '../api/db.php';
require_once '../api/address.php';

header('Content-Type: application/json');

$address = new Address($conn);

if ($_GET['action'] === 'get_districts' && isset($_GET['province_id'])) {
    $districts = $address->getDistricts((int)$_GET['province_id']);
    echo json_encode($districts);
} elseif ($_GET['action'] === 'get_wards' && isset($_GET['district_id'])) {
    $wards = $address->getWards((int)$_GET['district_id']);
    echo json_encode($wards);
} elseif ($_GET['action'] === 'get_full_address' && isset($_GET['province_id']) && isset($_GET['district_id']) && isset($_GET['ward_id'])) {
    $streetAddress = $_GET['street'] ?? '';
    $fullAddress = $address->getFullAddress((int)$_GET['province_id'], (int)$_GET['district_id'], (int)$_GET['ward_id'], $streetAddress);
    echo $fullAddress;
} else {
    echo json_encode(['error' => 'Invalid action']);
}
?>