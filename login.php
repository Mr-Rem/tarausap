<?php
session_start();
include 'db.php';  // Include database connection
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Check login attempts
    $stmt = $conn->prepare("SELECT id, password, failed_attempts, last_attempt FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password, $failed_attempts, $last_attempt);
        $stmt->fetch();

        // Lock account for 10 minutes if failed attempts exceed limit
        if ($failed_attempts >= 5 && strtotime($last_attempt) > time() - 600) {
            die("Too many failed login attempts. Try again later.");
        }

        // Verify hashed password
        if (password_verify($password, $hashed_password)) {
            // Reset failed attempts
            $reset_attempts = $conn->prepare("UPDATE users SET failed_attempts = 0 WHERE username = ?");
            $reset_attempts->bind_param("s", $username);
            $reset_attempts->execute();
            $reset_attempts->close();

            // Secure session
            session_regenerate_id(true);
            $_SESSION["user_id"] = $user_id;
            $_SESSION["username"] = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
            
            header("Location: chat.php"); // Redirect to chat page
            exit();
        } else {
            // Increase failed attempt count
            $stmt = $conn->prepare("UPDATE users SET failed_attempts = failed_attempts + 1, last_attempt = NOW() WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            
            echo "Invalid username or password!";
        }
    } else {
        echo "Invalid username or password!";
    }
    
    $stmt->close();
}
$conn->close();
?>

