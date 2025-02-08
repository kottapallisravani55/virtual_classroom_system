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

$teacher_email = null;
if ($role == 'teacher') {
    // Get teacher email from the users table where role is 'teacher'
    $sql_email = "SELECT email FROM users WHERE id = ? AND role = 'teacher'";
    $stmt_email = $conn->prepare($sql_email);
    $stmt_email->bind_param("i", $user_id);
    $stmt_email->execute();
    $result_email = $stmt_email->get_result();
    if ($result_email->num_rows > 0) {
        $row_email = $result_email->fetch_assoc();
        $teacher_email = $row_email['email'];
    } else {
        $error_message = "Failed to retrieve teacher email. Please contact support.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'generate_code' && $role == 'teacher') {
        if ($teacher_email) {
            // Check if student_id is provided
            $student_id = $_POST['student_id'];
            if ($student_id) {
                // Verify if the student ID exists in the students table
                $sql_verify_student = "SELECT id FROM students WHERE id = ?";
                $stmt_verify_student = $conn->prepare($sql_verify_student);
                $stmt_verify_student->bind_param("i", $student_id);
                $stmt_verify_student->execute();
                $result_verify_student = $stmt_verify_student->get_result();

                if ($result_verify_student->num_rows == 0) {
                    $error_message = "Invalid Student ID. The student does not exist in the database.";
                } else {
                    // Check if a chat code already exists for this teacher and student pair
                    $sql_check_existing = "SELECT * FROM chats WHERE teacher_email = ? AND student_id = ?";
                    $stmt_check_existing = $conn->prepare($sql_check_existing);
                    $stmt_check_existing->bind_param("si", $teacher_email, $student_id);
                    $stmt_check_existing->execute();
                    $result_check_existing = $stmt_check_existing->get_result();

                    if ($result_check_existing->num_rows > 0) {
                        $error_message = "A chat code already exists for this student.";
                    } else {
                        // Generate a new chat code
                        $chat_code = strtoupper(bin2hex(random_bytes(3))); // Generate a random 6-character code

                        // Insert chat code into the database
                        $sql = "INSERT INTO chats (chat_code, student_id, teacher_email) VALUES (?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("sis", $chat_code, $student_id, $teacher_email);

                        if ($stmt->execute()) {
                            $success_message = "Chat Code Generated for Student ID " . $student_id . ": " . $chat_code;
                        } else {
                            $error_message = "Failed to generate chat code. Please try again.";
                        }
                    }
                }
            } else {
                $error_message = "Please enter a student ID.";
            }
        }
    }

    if (isset($_POST['chat_code']) && $role == 'student') {
        $chat_code = $_POST['chat_code'];

        $sql = "SELECT * FROM chats WHERE chat_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $chat_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error_message = "Invalid Chat Code. Please try again.";
        } else {
            $_SESSION['chat_code'] = $chat_code; // Store chat code in session
            header("Location: chat_room.php"); // Redirect to chat room
            exit();
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
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="container">
        <h2><?php echo ($role == 'teacher') ? "Teacher Chat" : "Student Chat"; ?></h2>

        <?php if ($role == 'teacher'): ?>
            <!-- Form for teacher to generate chat codes -->
            <form method="POST" action="chat.php">
                <input type="hidden" name="action" value="generate_code">
                <label for="student_id">Student ID:</label>
                <input type="text" id="student_id" name="student_id" required>
                <button type="submit">Generate Chat Code</button>
            </form>
            <?php if (isset($success_message)): ?>
                <p style="color: green;"><?php echo $success_message; ?></p>
            <?php elseif (isset($error_message)): ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <!-- Display the list of chat codes generated by the teacher -->
            <h3>Generated Chat Codes:</h3>
            <table border="1">
                <tr>
                    <th>Chat Code</th>
                    <th>Student ID</th>
                    <th>Action</th>
                </tr>
                <?php
                $sql_chat_codes = "SELECT chat_code, student_id FROM chats WHERE teacher_email = ?";
                $stmt_chat_codes = $conn->prepare($sql_chat_codes);
                $stmt_chat_codes->bind_param("s", $teacher_email);
                $stmt_chat_codes->execute();
                $result_chat_codes = $stmt_chat_codes->get_result();

                while ($row = $result_chat_codes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['chat_code']; ?></td>
                        <td><?php echo $row['student_id']; ?></td>
                        <td>
                            <button onclick="openChat('<?php echo $row['chat_code']; ?>')">Join Chat</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <!-- Chat iframe -->
            <h3>Chat Room</h3>
            <iframe id="chatIframe" src="" style="width: 100%; height: 500px; border: none;"></iframe>
        <?php endif; ?>

        <?php if ($role == 'student'): ?>
            <!-- Display chat codes assigned to the student -->
            <h3>Your Assigned Chat Codes:</h3>
            <table border="1">
                <tr>
                    <th>Chat Code</th>
                    <th>Teacher Email</th>
                </tr>
                <?php
                $sql_student_codes = "SELECT chat_code, teacher_email FROM chats WHERE student_id = ?";
                $stmt_student_codes = $conn->prepare($sql_student_codes);
                $stmt_student_codes->bind_param("i", $user_id);
                $stmt_student_codes->execute();
                $result_student_codes = $stmt_student_codes->get_result();

                while ($row = $result_student_codes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['chat_code']; ?></td>
                        <td><?php echo $row['teacher_email']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <!-- Form for student to enter chat code -->
            <form method="POST" action="chat.php">
                <label for="chat_code">Enter Chat Code:</label>
                <input type="text" id="chat_code" name="chat_code" required>
                <button type="submit">Join Chat</button>
            </form>
            <?php if (isset($error_message)): ?>
                <p style="color: red;"><?php echo $error_message; ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        function openChat(chatCode) {
            document.getElementById('chatIframe').src = "chat_room.php?chat_code=" + chatCode;
        }
    </script>
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