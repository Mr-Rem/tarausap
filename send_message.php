<?php
session_start();
include 'db.php';

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
