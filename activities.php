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
    <title>Activities Hub - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f8fc;
            margin: 0;
            padding: 0;
        }
        .activities-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .activities-container h2 {
            margin-bottom: 20px;
            color: #333;
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
    </style>
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="activities-container">
        <h2>Welcome to the Activities Hub, <?php echo htmlspecialchars($username); ?>!</h2>

        <div class="btn-container">
            <a href="assignment.php" class="btn">Assignments</a>
            <a href="quizzes.php" class="btn">Quizzes</a>
            <a href="polls_list.php" class="btn">Polls/Surveys</a>
            <div class="btn-container">
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
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
