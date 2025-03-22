<?php
$host = "localhost";  // Change to your InfinityFree database host
$username = "root";
$password = "";
$database = "tara_usap";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
