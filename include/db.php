<?php
$host   = "localhost";
$dbname = "sems";
$user   = "root";
$pass   = "";
$port = "3366";

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
