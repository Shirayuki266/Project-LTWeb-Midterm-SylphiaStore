<?php
require_once 'api/db.php';
require_once 'api/address.php';

try {
    $addr = new Address($conn);
    $provinces = $addr->getProvinces();
    echo '✅ Address system working! Found ' . count($provinces) . ' provinces.' . PHP_EOL;

    // Find Hà Nội
    $hanoiId = null;
    foreach ($provinces as $prov) {
        if ($prov['name'] === 'Hà Nội') {
            $hanoiId = $prov['id'];
            break;
        }
    }
    echo 'Hà Nội ID: ' . $hanoiId . PHP_EOL;

    // Test districts
    $districts = $addr->getDistricts($hanoiId);
    echo 'Hà Nội has ' . count($districts) . ' districts.' . PHP_EOL;

    if (count($districts) > 0) {
        echo 'First district: ' . $districts[0]['name'] . PHP_EOL;
    }

    // Test full address
    $fullAddr = $addr->getFullAddress($hanoiId, 2, 2, '123 Đường ABC');
    echo 'Sample address: ' . $fullAddr . PHP_EOL;

} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage() . PHP_EOL;
}

$conn->close();
?>