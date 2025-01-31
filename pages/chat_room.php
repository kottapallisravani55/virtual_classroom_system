<?php
session_start();
require_once('../database/db_config.php'); // Include the database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role']; // 'teacher' or 'student'

// Check if chat_code is set in session or from GET request (if the user is accessing via a link)
if (isset($_SESSION['chat_code'])) {
    $chat_code = $_SESSION['chat_code']; // Fetch chat code from session
} elseif (isset($_GET['chat_code'])) {
    $chat_code = $_GET['chat_code']; // Fetch chat code from GET request
    $_SESSION['chat_code'] = $chat_code; // Store chat_code in session for future use
} else {
    // If chat_code is not set, redirect to the appropriate page
    header("Location: chat.php");
    exit();
}

// Fetch messages for the chat code
$sql_messages = "SELECT c.id, c.message, c.sent_at, c.sender_id, u.role AS sender_role, u.username AS sender_username 
                 FROM chats c
                 JOIN users u ON c.sender_id = u.id
                 WHERE c.chat_code = ? ORDER BY c.sent_at";
$stmt_messages = $conn->prepare($sql_messages);
$stmt_messages->bind_param("s", $chat_code);
$stmt_messages->execute();
$result_messages = $stmt_messages->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Room - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <div class="container">
        <h2>Chat Room</h2>
        
        <div class="chat-container">
            <?php
            while ($row = $result_messages->fetch_assoc()):
                $sender_role = $row['sender_role'];
                $sender_username = $row['sender_username'];
                $sender_id = $row['sender_id'];
                $message = $row['message'];
                $sent_at = $row['sent_at'];
            ?>
                <div class="message <?php echo $sender_role; ?>">
                    <p><strong><?php echo ucfirst($sender_role); ?> (<?php echo $sender_username; ?>, ID: <?php echo $sender_id; ?>):</strong></p>
                    <p><?php echo $message; ?></p>
                    <p><em>Sent at: <?php echo $sent_at; ?></em></p>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Form to send a message -->
        <form method="POST" action="send_message.php">
            <textarea name="message" required placeholder="Type your message here..."></textarea>
            <button type="submit">Send Message</button>
        </form>
    </div>
</body>
</html>
