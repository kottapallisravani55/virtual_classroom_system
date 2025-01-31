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
        } elseif ($role === 'teacher') {
            // Check if the subject is already assigned to a teacher
            $check_subject_query = "SELECT * FROM teachers WHERE subject = ?";
            $stmt = $conn->prepare($check_subject_query);
            $stmt->bind_param("s", $subject);
            $stmt->execute();
            $subject_result = $stmt->get_result();

            if ($subject_result->num_rows > 0) {
                $error = "This subject is already assigned to a teacher.";
            } else {
                // Register the teacher
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $conn->begin_transaction();

                try {
                    // Insert user into the 'users' table
                    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
                    $stmt->execute();

                    // Generate a unique 5-digit UID
                    $uid = random_int(10000, 99999);

                    // Insert into the 'teachers' table with uid
                    $teacher_query = "INSERT INTO teachers (uid, username, email, subject) VALUES (?, ?, ?, ?)";
                    $teacher_stmt = $conn->prepare($teacher_query);
                    $teacher_stmt->bind_param("isss", $uid, $username, $email, $subject);
                    $teacher_stmt->execute();

                    $conn->commit();

                    // Redirect to the login page after successful registration
                    header("Location: login.php");
                    exit;
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "An error occurred. Please try again.";
                }
            }
        } elseif ($role === 'student') {
            // Extract the student ID from the email
            preg_match('/^n([0-9]{2})([0-9]{4})@rguktn.ac.in$/', $email, $matches);
            $batch = $matches[1];
            $student_id = $matches[2];

            // Register the student
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $conn->begin_transaction();

            try {
                // Insert user into the 'users' table with the student ID
                $query = "INSERT INTO users (id, username, email, password, role) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issss", $student_id, $username, $email, $hashed_password, $role);
                $stmt->execute();

                // Insert into the 'students' table
                $student_query = "INSERT INTO students (id, username, email) VALUES (?, ?, ?)";
                $student_stmt = $conn->prepare($student_query);
                $student_stmt->bind_param("iss", $student_id, $username, $email);
                $student_stmt->execute();

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
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        /* Styling for the signup page */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f8fc;
            margin: 0;
            padding: 0;
        }
        .signup-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .signup-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #007bff;
            outline: none;
        }
        .btn {
            display: inline-block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .success {
            color: green;
            margin-top: 10px;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }
        #subject-group {
            display: none;
        }
    </style>
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
            <div class="form-group" id="subject-group">
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
</body>
</html>
