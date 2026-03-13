<?php
// DB Connection
// Supports multiple common demo database names (legacy + enhanced schema).
$host = 'localhost';
$user = 'root';
$pass = '';
$dbNames = [
    getenv('DB_NAME') ?: null,
    'Sylphia Shop',
    'SylphiaShop',
    'sylphia_shop',
];

$conn = null;
foreach ($dbNames as $dbname) {
    if (!$dbname) continue;
    $conn = @mysqli_connect($host, $user, $pass, $dbname);
    if ($conn && !$conn->connect_error) {
        break;
    }
}

if (!$conn || $conn->connect_error) {
    die("Connection failed: " . ($conn ? $conn->connect_error : 'Unknown database'));
}

mysqli_set_charset($conn, 'utf8mb4');
?>