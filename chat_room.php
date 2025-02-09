<?php
session_start();
require_once('../database/db_config.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if (isset($_GET['chat_code'])) {
    $chat_code = $_GET['chat_code'];
    $_SESSION['chat_code'] = $chat_code;
} elseif (isset($_SESSION['chat_code'])) {
    $chat_code = $_SESSION['chat_code'];
} else {
    header("Location: chat.php");
    exit();
}

$sql_messages = "SELECT c.id, c.message, c.sent_at, c.sender_id, u.role AS sender_role, u.username AS sender_username 
                 FROM chats c
                 JOIN users u ON c.sender_id = u.id
                 WHERE c.chat_code = ? ORDER BY c.sent_at ASC";
$stmt_messages = $conn->prepare($sql_messages);
$stmt_messages->bind_param("s", $chat_code);
$stmt_messages->execute();
$result_messages = $stmt_messages->get_result();

$sql_chat_details = "SELECT * FROM chats WHERE chat_code = ?";
$stmt_chat_details = $conn->prepare($sql_chat_details);
$stmt_chat_details->bind_param("s", $chat_code);
$stmt_chat_details->execute();
$chat_details = $stmt_chat_details->get_result()->fetch_assoc();

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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #efeae2;
            background-image: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAyBAMAAADsEZWCAAAAG1BMVEVHcEzx8fHw8PDw8PDw8PDw8PDw8PDw8PDw8PAFoUL/AAAACHRSTlMABTf98pH42TzwpvcAAABCSURBVDjLY2AYBaNg2AIWQQYGRkEGBhYhJDYzEwODIBMDgwgjdr4QMl8IyRZBIYQtQkjmMQshsVmQzRsE/zYKRgEAq0oR1RCfpZQAAAAASUVORK5CYII=");
        }

        .chat-header {
            background-color: #f0f2f5;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #e9edef;
        }

        .chat-header h2 {
            margin-left: 15px;
            font-size: 16px;
            color: #111b21;
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .message {
            max-width: 65%;
            margin-bottom: 12px;
            padding: 6px 7px 8px 9px;
            border-radius: 7.5px;
            position: relative;
            font-size: 14.2px;
            line-height: 19px;
            color: #111b21;
        }

        .message.teacher {
            background-color: #d9fdd3;
            margin-left: auto;
            border-top-right-radius: 0;
        }

        .message.student {
            background-color: #ffffff;
            margin-right: auto;
            border-top-left-radius: 0;
        }

        .message strong {
            display: block;
            font-size: 13px;
            color: #667781;
            margin-bottom: 2px;
        }

        .message em {
            display: block;
            font-size: 11px;
            color: #667781;
            margin-top: 4px;
            font-style: normal;
        }

        .message-form {
            background-color: #f0f2f5;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .message-form textarea {
            flex: 1;
            padding: 9px 12px;
            border: 1px solid #e9edef;
            border-radius: 8px;
            resize: none;
            height: 42px;
            font-family: inherit;
            font-size: 15px;
            line-height: 20px;
        }

        .message-form button {
            background-color: #00a884;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .message-form button:hover {
            background-color: #008f72;
        }

        .message-form button svg {
            width: 24px;
            height: 24px;
        }
    </style>
</head>
<body>
    <div class="chat-header">
        <h2>Chat Room (Code: <?php echo $chat_code; ?>)</h2>
    </div>
    
    <div class="messages-container">
        <?php if ($result_messages->num_rows > 0): ?>
            <?php while ($row = $result_messages->fetch_assoc()): ?>
                <div class="message <?php echo $row['sender_role']; ?>">
                    <strong><?php echo $row['sender_username']; ?></strong>
                    <?php echo htmlspecialchars($row['message']); ?>
                    <em><?php echo date('h:i A', strtotime($row['sent_at'])); ?></em>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align: center; color: #8696a0; padding: 20px;">
                No messages yet. Start the conversation!
            </div>
        <?php endif; ?>
    </div>

    <form class="message-form" method="POST" action="send_message.php">
        <input type="hidden" name="chat_code" value="<?php echo $chat_code; ?>">
        <textarea name="message" placeholder="Type a message" required></textarea>
        <button type="submit">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M22 2L11 13" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>
    </form>

    <script>
        // Auto-scroll to bottom on page load
        window.onload = function() {
            const messagesContainer = document.querySelector('.messages-container');
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        };

        // Auto-expand textarea
        const textarea = document.querySelector('textarea');
        textarea.addEventListener('input', function() {
            this.style.height = '42px';
            this.style.height = (this.scrollHeight) + 'px';
        });
    </script>
</body>
</html>