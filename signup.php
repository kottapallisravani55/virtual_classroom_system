<?php
// Include database connection
include '../database/db_config.php';

// Initialize variables
$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user inputs
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = $_POST['role']; // Teacher or Student
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : null; // Subject for teachers

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error = "All fields are required.";
    } elseif ($role === 'teacher' && empty($subject)) {
        $error = "Please specify the subject you teach.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif ($role === 'student' && !preg_match('/^n[0-9]{2}[0-9]{4}@rguktn.ac.in$/', $email)) {
        $error = "Student email must follow the format: n<batch><id>@rguktn.ac.in.";
    } elseif ($role === 'teacher' && preg_match('/^n[0-9]{2}[0-9]{4}@rguktn.ac.in$/', $email)) {
        $error = "Teachers cannot use student emails.";
    } else {
        // Check if the email already exists
        $check_email_query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_email_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            // Register the user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $conn->begin_transaction();

            try {
                // Insert user into the 'users' table
                $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
                $stmt->execute();

                // Get the last inserted ID (user ID)
                $user_id = $conn->insert_id;

                if ($role === 'teacher') {
                    // Insert teacher data into the 'teachers' table
                    $teacher_query = "INSERT INTO teachers (username, email, subject, uid) VALUES (?, ?, ?, ?)";
                    $teacher_stmt = $conn->prepare($teacher_query);
                    $teacher_stmt->bind_param("sssi", $username, $email, $subject, $user_id);
                    $teacher_stmt->execute();
                } elseif ($role === 'student') {
                    // Extract the student ID from the email
                    preg_match('/^n([0-9]{2})([0-9]{4})@rguktn.ac.in$/', $email, $matches);
                    $batch = $matches[1];
                    $student_id = $matches[2];

                    // Insert student data into the 'students' table
                    $student_query = "INSERT INTO students (id, username, email) VALUES (?, ?, ?)";
                    $student_stmt = $conn->prepare($student_query);
                    $student_stmt->bind_param("iss", $student_id, $username, $email);
                    $student_stmt->execute();
                }

                $conn->commit();

                // Redirect to the login page after successful registration
                header("Location: login.php");
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                $error = "An error occurred. Please try again.";
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
    <title>Sign Up - Virtual Classroom</title>
    <link rel="stylesheet" href="styles_signup.css">

    <script>
        function toggleSubjectField() {
            const role = document.querySelector('select[name="role"]').value;
            const subjectGroup = document.getElementById('subject-group');
            if (role === 'teacher') {
                subjectGroup.style.display = 'block';
            } else {
                subjectGroup.style.display = 'none';
            }
        }
    </script>
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="signup-container">
        <h2>Sign Up for Virtual Classroom</h2>
        <form action="signup.php" method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            </div>
            <div class="form-group">
                <label for="role">Select Role:</label>
                <select name="role" onchange="toggleSubjectField()" required>
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                </select>
            </div>
            <div class="form-group" id="subject-group" style="display: none;">
                <input type="text" name="subject" placeholder="Subject You Teach">
            </div>
            <button type="submit" class="btn">Sign Up</button>
            <?php if ($success): ?>
                <p class="success"><?php echo $success; ?></p>
            <?php elseif ($error): ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
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
