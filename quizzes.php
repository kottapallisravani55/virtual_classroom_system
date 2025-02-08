<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require '../database/db_config.php';

// Get user details from session
$username = $_SESSION['username'];
$role = $_SESSION['role']; // Either 'teacher' or 'student'

// Fetch quizzes
$sql_quizzes = "SELECT * FROM quizzes";
$result = $conn->query($sql_quizzes);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quizzes - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="activities-container">
        <h2>Quizzes</h2>

        <?php if ($role == 'teacher'): ?>
            <h3>Create a Quiz</h3>
            <form action="create_quiz.php" method="POST">
                <label for="quiz_title">Quiz Title:</label>
                <input type="text" name="quiz_title" id="quiz_title" required><br><br>
                <button type="submit" class="btn">Create Quiz</button>
            </form>
        <?php endif; ?>

        <h3>Available Quizzes</h3>
        <table>
            <tr>
                <th>Title</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['quiz_title']); ?></td>
                    <td><a href="take_quiz.php?quiz_id=<?php echo $row['id']; ?>" class="btn">Take Quiz</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
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
