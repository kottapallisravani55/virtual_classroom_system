<?php
session_start();
require_once('../database/db_config.php'); // Include the database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['chat_code'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role']; // 'teacher' or 'student'
$chat_code = $_SESSION['chat_code']; // Get chat code from session

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    // Insert message into the database with sender_id and chat_code
    $sql = "INSERT INTO chats (chat_code, message, sender_id, sent_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $chat_code, $message, $user_id);

    if ($stmt->execute()) {
        header("Location: chat_room.php"); // Redirect to chat room after sending message
        exit();
    } else {
        echo "Error: Unable to send the message.";
    }
} else {
    echo "Message content is required.";
}
?>
