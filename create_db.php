<?php
// Script to create database 'Sylphia Shop' - One-time use
$host = 'localhost';
$user = 'root';
$pass = '';  // Default XAMPP root password is empty. Change if different.

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$db_name = '`Sylphia Shop`';

$sql = "CREATE DATABASE IF NOT EXISTS " . $db_name;

if ($conn->query($sql) === TRUE) {
    echo "Database 'Sylphia Shop' created successfully (or already exists).<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

$conn->close();
echo "<p>Done. Delete this file after use for security.</p>";
?>