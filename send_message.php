<?php
session_start();
include 'db.php';
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
if (!isset($_SESSION["user_id"])) {
    exit("Unauthorized");
}

$user_id = $_SESSION["user_id"];
$receiver_id = $_POST["receiver_id"];
$message = trim($_POST["message"]);

// Prevent empty messages
if ($message === '') {
    exit("Empty message");
}

// Store message in DB
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $user_id, $receiver_id, $message);
$stmt->execute();
$stmt->close();
?>
