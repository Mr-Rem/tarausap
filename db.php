<?php
$host = "sql106.infinityfree.com";  // Change to your InfinityFree database host
$username = "if0_38503942";
$password = "ESorSC5ZzK";
$database = "if0_38503942_XXX";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
