<?php
session_start();
include 'db.php';

if (!isset($_SESSION["user_id"])) {
    exit("Unauthorized");
}

$user_id = $_SESSION["user_id"];
$receiver_id = $_POST["receiver_id"] ?? null;

if (!$receiver_id) {
    exit("No receiver selected.");
}

// Fetch messages with timestamps
$stmt = $conn->prepare("
    SELECT messages.sender_id, messages.receiver_id, messages.message, messages.created_at, 
           sender.profile_pic AS sender_pic, 
           receiver.profile_pic AS receiver_pic
    FROM messages 
    JOIN users AS sender ON messages.sender_id = sender.id
    JOIN users AS receiver ON messages.receiver_id = receiver.id
    WHERE (messages.sender_id = ? AND messages.receiver_id = ?) 
       OR (messages.sender_id = ? AND messages.receiver_id = ?) 
    ORDER BY messages.created_at ASC
");
$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit("No messages found.");
}

while ($row = $result->fetch_assoc()) {
    $sender_pic = !empty($row["sender_pic"]) ? $row["sender_pic"] : "default.jpg";
    $receiver_pic = !empty($row["receiver_pic"]) ? $row["receiver_pic"] : "default.jpg";
    $timestamp = date("h:i A", strtotime($row["created_at"])); // Format: 12:30 PM

    if ($row["sender_id"] == $user_id) {
        // Sent messages (right side)
        echo "<div class='message sent' onclick='toggleTimestamp(this)'>
                <div class='message-content'>" . htmlspecialchars($row["message"], ENT_QUOTES, 'UTF-8') . "</div>
                <span class='timestamp'>$timestamp</span>
                <img src='uploads/$sender_pic' alt='Your Profile' class='profile-pic'>
              </div>";
    } else {
        // Received messages (left side)
        echo "<div class='message received' onclick='toggleTimestamp(this)'>
                <img src='uploads/$sender_pic' alt='Sender Profile' class='profile-pic'>
                <div class='message-content'>" . htmlspecialchars($row["message"], ENT_QUOTES, 'UTF-8') . "</div>
                <span class='timestamp'>$timestamp</span>
              </div>";
    }
}

$stmt->close();
?>
