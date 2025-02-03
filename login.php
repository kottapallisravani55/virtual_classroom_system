<?php
// Start session at the very beginning of the script
session_start();

// Include database connection
include '../database/db_config.php';

$login_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get login inputs and sanitize
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        $login_error = "Please enter both username and password.";
    } else {
        // Query to check if the username exists
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, store session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // Store user role (e.g., 'teacher', 'student')

                // If the user is a teacher, fetch the subject from the teachers table
                if ($user['role'] == 'teacher') {
                    $query = "SELECT subject FROM teachers WHERE username = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $_SESSION['subject'] = $row['subject'];  // Store subject in session
                    } else {
                        $_SESSION['message'] = "Error: Subject not found for this teacher.";
                    }
                }

                // Redirect to the dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $login_error = "Invalid username or password.";
            }
        } else {
            $login_error = "No account found with that username.";
        }
    }
}
?>
<link rel="stylesheet" href="styles_login.css">

<div class="login-container">

    <h2>Login to Virtual Classroom</h2>
    <form action="login.php" method="POST">
        <div class="form-group">
            <input type="text" name="username" placeholder="Username" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="btn">Login</button>
        <?php if ($login_error): ?>
            <p class="error"><?php echo $login_error; ?></p>
        <?php endif; ?>
    </form>
    <p>Don't have an account?  <a href="signup.php" > Sign up here</a>.</p>
</div>
