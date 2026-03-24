<?php
// setup_db.php - Create/Setup Demo DB for Sylphia Shop
// Run: http://localhost/web b/setup_db.php or php setup_db.php
// Uses phpMyAdmin DB 'Sylphia_Shop' convention. Imports enhanced_schema.sql fake data.

echo "<h2>Sylphia Shop - Demo DB Setup</h2>";

// DB conn (XAMPP defaults)
$host = 'localhost';
$user = 'root';
$pass = '';
$database = 'SylphiaShop';

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create DB if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($database);
echo "<p>✅ DB '$database' ready.</p>";

// Load enhanced schema
$schema_file = 'sql/enhanced_schema.sql';
if (file_exists($schema_file)) {
    $sql = file_get_contents($schema_file);
    // Multi-query safe
    if ($conn->multi_query($sql)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        echo "<p>✅ Imported enhanced_schema.sql (tables, triggers, ~50 fake products/users/orders/imports).</p>";
    } else {
        echo "<p>❌ Schema import failed: " . $conn->error . "</p>";
    }
} else {
    echo "<p>❌ $schema_file not found.</p>";
}

// Verify key tables/data
$tables = ['users', 'admins', 'categories', 'products', 'inventory', 'orders'];
foreach ($tables as $table) {
    $count = $conn->query("SELECT COUNT(*) as cnt FROM `$table`")->fetch_assoc()['cnt'];
    echo "<p>✅ Table '$table': $count rows.</p>";
}

// Sample queries for demo
echo "<h3>Demo Data Preview:</h3>";
echo "<pre>";
$res = $conn->query("SELECT name, price, stock FROM products LIMIT 5");
while ($row = $res->fetch_assoc()) {
    printf("%s (%.0fđ) - Stock: %d\n", $row['name'], $row['price'], $row['stock']);
}
echo "</pre>";

$conn->close();
echo "<p><strong>Done! Visit <a href='user/index.php'>User Site</a> or <a href='admin/login.php'>Admin</a>. Import manual in phpMyAdmin if needed.</strong></p>";
?>