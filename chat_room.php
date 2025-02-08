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

// Check if chat_code is provided via GET request
if (isset($_GET['chat_code'])) {
    $chat_code = $_GET['chat_code'];
    $_SESSION['chat_code'] = $chat_code; // Store chat_code in session
} elseif (isset($_SESSION['chat_code'])) {
    $chat_code = $_SESSION['chat_code'];
} else {
    // Redirect to the chat list if no chat_code is provided
    header("Location: chat.php");
    exit();
}

// Fetch messages for the given chat_code
$sql_messages = "SELECT c.id, c.message, c.sent_at, c.sender_id, u.role AS sender_role, u.username AS sender_username 
                 FROM chats c
                 JOIN users u ON c.sender_id = u.id
                 WHERE c.chat_code = ? ORDER BY c.sent_at ASC";
$stmt_messages = $conn->prepare($sql_messages);
$stmt_messages->bind_param("s", $chat_code);
$stmt_messages->execute();
$result_messages = $stmt_messages->get_result();

// Fetch chat details
$sql_chat_details = "SELECT * FROM chats WHERE chat_code = ?";
$stmt_chat_details = $conn->prepare($sql_chat_details);
$stmt_chat_details->bind_param("s", $chat_code);
$stmt_chat_details->execute();
$chat_details = $stmt_chat_details->get_result()->fetch_assoc();

// If no chat details are found, redirect
if (!$chat_details) {
    header("Location: chat.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Room - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .chat-container {
            border: 1px solid #ccc;
            padding: 10px;
            height: 400px;
            overflow-y: scroll;
            margin-bottom: 20px;
        }
        .message {
            margin-bottom: 10px;
            padding: 5px;
            border-radius: 5px;
        }
        .teacher {
            background-color: #d1e7dd;
        }
        .student {
            background-color: #f8d7da;
        }
        .message strong {
            font-size: 0.9em;
            color: #333;
        }
        .message em {
            font-size: 0.8em;
            color: #666;
        }
        textarea {
            width: 100%;
            height: 100px;
            margin-bottom: 10px;
        }
        .student-list {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        .student-list a {
            text-decoration: none;
            color: #007bff;
        }
        .student-list a:hover {
            text-decoration: underline;
        }
    </style>
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="container">
        <h2>Chat Room (Chat Code: <?php echo $chat_code; ?>)</h2>
        
        <div class="chat-container">
            <?php if ($result_messages->num_rows > 0): ?>
                <?php while ($row = $result_messages->fetch_assoc()): ?>
                    <div class="message <?php echo $row['sender_role']; ?>">
                        <p><strong><?php echo ucfirst($row['sender_role']); ?> (<?php echo $row['sender_username']; ?>):</strong></p>
                        <p><?php echo htmlspecialchars($row['message']); ?></p>
                        <p><em>Sent at: <?php echo $row['sent_at']; ?></em></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No messages yet. Start the conversation!</p>
            <?php endif; ?>
        </div>

        <!-- Form to send a message -->
        <form method="POST" action="send_message.php">
            <input type="hidden" name="chat_code" value="<?php echo $chat_code; ?>">
            <textarea name="message" required placeholder="Type your message here..."></textarea>
            <button type="submit">Send Message</button>
        </form>

        <!-- Link to switch between chats -->
        <?php if ($role === 'teacher'): ?>
            <div class="student-list">
                <h3>Switch to another student's chat:</h3>
                <?php
                $sql_students = "SELECT DISTINCT c.chat_code, u.username AS student_username 
                                 FROM chats c 
                                 JOIN users u ON c.student_id = u.id 
                                 WHERE c.teacher_email = (SELECT email FROM users WHERE id = ?)";
                $stmt_students = $conn->prepare($sql_students);
                $stmt_students->bind_param("i", $user_id);
                $stmt_students->execute();
                $result_students = $stmt_students->get_result();

                while ($student_chat = $result_students->fetch_assoc()): ?>
                    <p>
                        <a href="chat_room.php?chat_code=<?php echo $student_chat['chat_code']; ?>">
                            <?php echo $student_chat['student_username']; ?> (Chat Code: <?php echo $student_chat['chat_code']; ?>)
                        </a>
                    </p>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
    <script src="script.js"></script>
      <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement(
                {pageLanguage: 'en'},
                'google_translate_element'
            );
        } 
  </script>
  <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

</body>
</html>