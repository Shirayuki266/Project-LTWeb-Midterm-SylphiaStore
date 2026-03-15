<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

$result = $conn->query("SELECT MIN(id) as id, name 
                        FROM categories 
                        GROUP BY name 
                        ORDER BY name");

echo json_encode($result->fetch_all(MYSQLI_ASSOC), JSON_UNESCAPED_UNICODE);
?>