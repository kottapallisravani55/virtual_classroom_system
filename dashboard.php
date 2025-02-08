<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Get user details from session
$username = $_SESSION['username'];
$role = $_SESSION['role']; // Either 'teacher' or 'student'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($role == 'teacher') ? 'Teacher Dashboard' : 'Student Dashboard'; ?> - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f8fc;
            margin: 0;
            padding: 0;
        }
        .dashboard-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .dashboard-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .user-info {
            margin-bottom: 30px;
            font-size: 18px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-logout {
            background-color: #dc3545;
        }
        .btn-logout:hover {
            background-color: #c82333;
        }
        .btn-container {
            margin-top: 20px;
        }
        .btn-container a {
            margin: 0 10px;
        }
    </style>
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="dashboard-container">
        <h2>Welcome to the Virtual Classroom, <?php echo htmlspecialchars($username); ?>!</h2>
        
        <div class="user-info">
            <p>You are logged in as a <?php echo ($role == 'teacher') ? 'Teacher' : 'Student'; ?>.</p>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
        </div>

        <div class="btn-container">
        <a href="profile.php" class="btn">profile</a>
            <a href="study_materials.php" class="btn">Study Materials</a>
            <a href="view_live_classes.php" class="btn">Live Classes</a>
            <a href="activities.php" class="btn">Activities Hub</a>
            <a href="class_attendance.php" class="btn">Class Attendance</a>
            <br><br>
            <?php if ($role == 'student'): ?>
                <a href="bot.php" class="btn">Chatbot</a>
                <a href="complaint_form.php" class="btn">Complaints</a>
            <?php endif; ?>
            <a href="chat.php" class="btn">Chat</a>
            <?php if ($role == 'teacher'): ?>
                <a href="view_complaints.php" class="btn">Complaints</a>
            <?php endif; ?>
        </div>

        <div class="btn-container">
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
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
