<?php
// Start session
session_start();
include '../database/db_config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission for creating a poll
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question = $_POST['question'];
    $options = $_POST['options'];

    // Insert the poll into the database
    $poll_query = "INSERT INTO polls (question, status) VALUES (?, 'active')";
    $stmt = $conn->prepare($poll_query);
    $stmt->bind_param("s", $question);
    $stmt->execute();
    $poll_id = $stmt->insert_id;

    // Insert poll options
    foreach ($options as $option) {
        if (!empty($option)) {
            $option_query = "INSERT INTO poll_options (poll_id, option) VALUES (?, ?)";
            $stmt = $conn->prepare($option_query);
            $stmt->bind_param("is", $poll_id, $option);
            $stmt->execute();
        }
    }

    header("Location: polls_list.php");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Poll - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <script>
        // Function to add new options dynamically
        function addOption() {
            const optionsDiv = document.getElementById('options');
            const newOption = document.createElement('input');
            newOption.type = 'text';
            newOption.name = 'options[]';
            newOption.placeholder = 'Option';
            optionsDiv.appendChild(newOption);
        }
    </script>
    <div id="google_translate_element"></div>
</head>
<body>

<div class="poll-container">
    <h2>Create New Poll/Survey</h2>
    
    <form method="POST">
        <label for="question">Poll Question:</label><br>
        <input type="text" name="question" id="question" required><br><br>

        <div id="options">
            <label>Poll Options:</label><br>
            <input type="text" name="options[]" placeholder="Option 1" required><br>
            <input type="text" name="options[]" placeholder="Option 2" required><br>
        </div>
        <button type="button" onclick="addOption()">Add Option</button><br><br>

        <button type="submit">Create Poll</button>
    </form>
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
