<?php
session_start();
require_once('../database/db_config.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$teacher_email = null;
if ($role == 'teacher') {
    $sql_email = "SELECT email FROM users WHERE id = ? AND role = 'teacher'";
    $stmt_email = $conn->prepare($sql_email);
    $stmt_email->bind_param("i", $user_id);
    $stmt_email->execute();
    $result_email = $stmt_email->get_result();
    if ($result_email->num_rows > 0) {
        $row_email = $result_email->fetch_assoc();
        $teacher_email = $row_email['email'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'generate_code' && $role == 'teacher') {
        if ($teacher_email) {
            $student_id = $_POST['student_id'];
            if ($student_id) {
                $sql_verify_student = "SELECT id FROM students WHERE id = ?";
                $stmt_verify_student = $conn->prepare($sql_verify_student);
                $stmt_verify_student->bind_param("i", $student_id);
                $stmt_verify_student->execute();
                $result_verify_student = $stmt_verify_student->get_result();

                if ($result_verify_student->num_rows == 0) {
                    $error_message = "Invalid Student ID. The student does not exist in the database.";
                } else {
                    $sql_check_existing = "SELECT * FROM chats WHERE teacher_email = ? AND student_id = ?";
                    $stmt_check_existing = $conn->prepare($sql_check_existing);
                    $stmt_check_existing->bind_param("si", $teacher_email, $student_id);
                    $stmt_check_existing->execute();
                    $result_check_existing = $stmt_check_existing->get_result();

                    if ($result_check_existing->num_rows > 0) {
                        $error_message = "A chat code already exists for this student.";
                    } else {
                        $chat_code = strtoupper(bin2hex(random_bytes(3)));
                        $sql = "INSERT INTO chats (chat_code, student_id, teacher_email) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sis", $chat_code, $student_id, $teacher_email);

                        if ($stmt->execute()) {
                            $success_message = "Chat Code Generated: " . $chat_code;
                        } else {
                            $error_message = "Failed to generate chat code.";
                        }
                    }
                }
            } else {
                $error_message = "Please enter a student ID.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f2f5;
            height: 100vh;
            overflow: hidden;
        }

        .chat-container {
            display: flex;
            height: 100vh;
            background-color: #fff;
        }

        .sidebar {
            width: 350px;
            background-color: #fff;
            border-right: 1px solid #e9edef;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            background-color: #f0f2f5;
            border-bottom: 1px solid #e9edef;
        }

        .chat-list {
            flex: 1;
            overflow-y: auto;
        }

        .chat-item {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #e9edef;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .chat-item:hover {
            background-color: #f0f2f5;
        }

        .chat-item.active {
            background-color: #f0f2f5;
        }

        .chat-avatar {
            width: 49px;
            height: 49px;
            border-radius: 50%;
            background-color: #128C7E;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }

        .chat-info {
            flex: 1;
        }

        .chat-name {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .chat-code {
            color: #667781;
            font-size: 13px;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: #efeae2;
        }

        .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #667781;
            padding: 20px;
            text-align: center;
        }

        .empty-state img {
            width: 250px;
            margin-bottom: 30px;
        }

        .empty-state h2 {
            font-size: 32px;
            font-weight: 300;
            margin-bottom: 20px;
            color: #41525d;
        }

        .empty-state p {
            font-size: 14px;
            max-width: 500px;
            line-height: 1.6;
        }

        .new-chat-form {
            padding: 15px;
            background-color: #fff;
            border-top: 1px solid #e9edef;
        }

        .new-chat-form input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e9edef;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .new-chat-form button {
            width: 100%;
            padding: 8px 12px;
            background-color: #128C7E;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .new-chat-form button:hover {
            background-color: #075E54;
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }

        .success-message {
            color: #28a745;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }

        iframe {
            flex: 1;
            width: 100%;
            border: none;
            background-color: white;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><?php echo ($role == 'teacher') ? "Teacher Chat" : "Student Chat"; ?></h2>
            </div>

            <?php if ($role == 'teacher'): ?>
                <div class="new-chat-form">
                    <form method="POST" action="chat.php">
                        <input type="hidden" name="action" value="generate_code">
                        <input type="text" id="student_id" name="student_id" placeholder="Enter Student ID" required>
                        <button type="submit">Generate Chat Code</button>
                    </form>
                    <?php if (isset($success_message)): ?>
                        <p class="success-message"><?php echo $success_message; ?></p>
                    <?php elseif (isset($error_message)): ?>
                        <p class="error-message"><?php echo $error_message; ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="chat-list">
                <?php
                if ($role == 'teacher') {
                    $sql_chats = "SELECT c.chat_code, c.student_id, u.username as student_name 
                                 FROM chats c 
                                 JOIN users u ON c.student_id = u.id 
                                 WHERE c.teacher_email = ?";
                    $stmt_chats = $conn->prepare($sql_chats);
                    $stmt_chats->bind_param("s", $teacher_email);
                } else {
                    $sql_chats = "SELECT c.chat_code, u.username as teacher_name 
                                 FROM chats c 
                                 JOIN users u ON c.teacher_email = u.email 
                                 WHERE c.student_id = ?";
                    $stmt_chats = $conn->prepare($sql_chats);
                    $stmt_chats->bind_param("i", $user_id);
                }
                
                $stmt_chats->execute();
                $result_chats = $stmt_chats->get_result();

                while ($chat = $result_chats->fetch_assoc()):
                    $name = $role == 'teacher' ? $chat['student_name'] : $chat['teacher_name'];
                    $initial = strtoupper(substr($name, 0, 1));
                ?>
                    <div class="chat-item" onclick="loadChat('<?php echo $chat['chat_code']; ?>')">
                        <div class="chat-avatar"><?php echo $initial; ?></div>
                        <div class="chat-info">
                            <div class="chat-name"><?php echo $name; ?></div>
                            <div class="chat-code">Code: <?php echo $chat['chat_code']; ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="main-content" id="main-content">
            <div class="empty-state">
                <img src="https://web.whatsapp.com/img/intro-connection-light_c98cc75f2aa905314d74375a975d2cf2.jpg" alt="Welcome">
                <h2>Virtual Classroom Chat</h2>
                <p>Select a chat from the sidebar to start messaging</p>
            </div>
        </div>
    </div>

    <script>
        function loadChat(chatCode) {
            const mainContent = document.getElementById('main-content');
            mainContent.innerHTML = `<iframe src="chat_room.php?chat_code=${chatCode}"></iframe>`;
            
            // Update active state in sidebar
            document.querySelectorAll('.chat-item').forEach(item => {
                item.classList.remove('active');
                if (item.querySelector('.chat-code').textContent.includes(chatCode)) {
                    item.classList.add('active');
                }
            });
        }
    </script>
</body>
</html>