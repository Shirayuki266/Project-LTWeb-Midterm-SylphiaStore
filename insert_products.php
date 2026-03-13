<?php
require_once 'includes/config.php';

// Product data generator - 200 items
$categories = [1,2,3,4,5]; // dien thoai, laptop, smartwatch, tai nghe, phu kien
$images = ['iphone-16-pro-max.webp', 'laptop-asus-tuf-gaming.webp', 'tainghe.webp', 'banphimco.webp', 'asusrog.webp', 'macbook.webp', 'dell.webp', 'oppo-find-x8-pro-.jpg', 'laptopnitro.webp', 'dtsony.webp']; // existing
$names = [
    // Phones 40
    ['iPhone 16 Pro Max',1], ['Samsung Galaxy S24 Ultra',1], ['Google Pixel 9 Pro',1], // ... repeat variations
    // Laptops 40
    ['ASUS TUF Gaming A15',2], ['Dell XPS 13',2], ['MacBook Pro M3',2],
    // etc.
];

echo "Inserting 200 products...\n";

$count = 0;
while ($count < 200) {
    $cat = $categories[array_rand($categories)];
    $img = $images[array_rand($images)];
    $name = generateName($cat);
    $gia = rand(500000, 50000000)/100;
    $rating = rand(30,50)/10;
    $giamgia = rand(0,30)/100 * $gia;
    $mota = 'Sản phẩm chất lượng cao...';

$sql = "INSERT INTO sanpham (ten, gia, hinh, loai, rating, giamgia, mota) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sdsidds', $name, $gia, $img, $cat, $rating, $giamgia, $mota);
    if (mysqli_stmt_execute($stmt)) $count++;
    mysqli_stmt_close($stmt);

}

function generateName($cat) {
    $templates = [
        1 => ['iPhone %d Pro', 'Samsung Galaxy %s', 'OPPO Find X%d', 'Xiaomi %dT Pro'],
        2 => ['ASUS %s Gaming', 'Dell Inspiron %d', 'HP Pavilion %d', 'Lenovo ThinkPad %s'],
        3 => ['Apple Watch Series %d', 'Samsung Galaxy Watch %d', 'Garmin Venu %d'],
        4 => ['Sony WH-%d', 'AirPods Pro %d', 'Bose QC %d'],
        5 => ['Logitech Mouse %s', 'Razer Keyboard %d', 'Case iPhone %d']
    ];
    $t = $templates[$cat][rand(0, count($templates[$cat])-1)];
    return sprintf($t, rand(10,16), ['S', 'Ultra', 'Pro'][rand(0,2)]);
}

echo "Inserted $count products.\n";
?>