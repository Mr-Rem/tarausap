<?php
session_start();
include 'db.php'; // Database connection

header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username = htmlspecialchars($_SESSION["username"], ENT_QUOTES, 'UTF-8');

// Fetch users for chat
$users_stmt = $conn->prepare("SELECT id, username FROM users WHERE id != ?");
$users_stmt->bind_param("i", $user_id);
$users_stmt->execute();
$users_result = $users_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tara Usap! - Chat</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; }
        .chat-container { width: 500px; margin: auto; }
        .messages { border: 1px solid #ccc; height: 300px; overflow-y: scroll; padding: 10px; }
        .input-container { margin-top: 10px; }
        .input-container input { width: 80%; padding: 5px; }
        .input-container button { padding: 5px; }
        .chat-container {
    width: 100%;
    max-width: 600px;
    margin: auto;
}

.message {
    display: flex;
    align-items: center;
    margin: 10px 0;
    max-width: 80%;
}

.message.sent {
    justify-content: flex-end;
    text-align: right;
}

.message.received {
    justify-content: flex-start;
    text-align: left;
}

.message-content {
    background-color: #f1f0f0;
    padding: 10px;
    border-radius: 10px;
    max-width: 70%;
    word-wrap: break-word;
}

.sent .message-content {
    background-color: #dcf8c6; /* Light green for sent messages */
}

.received .message-content {
    background-color: #ffffff; /* White for received messages */
    border: 1px solid #ccc;
}

.profile-pic {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin: 0 10px;
}
.timestamp {
    display: none; /* Hide by default */
    font-size: 12px;
    color: gray;
    margin-top: 5px;
}

    </style>
</head>
<body>
    <h2>Welcome, <?php echo $username; ?>!</h2>
    
    <label for="receiver">Chat with:</label>
    <select id="receiver">
        <?php while ($row = $users_result->fetch_assoc()): ?>
            <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8'); ?></option>
        <?php endwhile; ?>
    </select>

    <div class="chat-container">
        <div class="messages" id="chat-box"></div>
        <div class="input-container">
            <input type="text" id="message" placeholder="Type a message...">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <script>
        function loadMessages() {
            let receiver_id = $('#receiver').val();
            $.post('fetch_messages.php', { receiver_id: receiver_id }, function(data) {
                $('#chat-box').html(data);
                $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
            });
        }

        function sendMessage() {
            let receiver_id = $('#receiver').val();
            let message = $('#message').val();
            if (message.trim() === '') return;

            $.post('send_message.php', { receiver_id: receiver_id, message: message }, function() {
                $('#message').val('');
                loadMessages();
            });
        }

        $(document).ready(function() {
            loadMessages();
            setInterval(loadMessages, 3000); // Refresh messages every 3 seconds
        });

        $('#receiver').change(loadMessages);

        function toggleTimestamp(messageElement) {
            let timestamp = messageElement.querySelector(".timestamp");
            if (timestamp.style.display === "none" || timestamp.style.display === "") {
                timestamp.style.display = "block";
            } else {
                timestamp.style.display = "none";
            }
        }
    </script>

</body>
</html>
