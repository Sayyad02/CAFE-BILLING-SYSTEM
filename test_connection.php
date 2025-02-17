<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "ali";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
} else {
    echo "✅ Database connected successfully!";
}
$conn->close();
?>
