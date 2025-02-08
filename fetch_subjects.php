<?php
require '../database/db_config.php'; // Database connection

// Check if teacher_username is passed via POST
if (isset($_POST['teacher_username'])) {
    $teacher_username = $_POST['teacher_username'];

    // Query to get the subject of the selected teacher
    $sql_subject = "SELECT subject FROM teachers WHERE username = ?";
    $stmt_subject = $conn->prepare($sql_subject);
    $stmt_subject->bind_param("s", $teacher_username);
    $stmt_subject->execute();
    $result_subject = $stmt_subject->get_result();

    // If subject is found, return it, otherwise return an empty string
    if ($result_subject->num_rows > 0) {
        $row = $result_subject->fetch_assoc();
        echo $row['subject'];
    } else {
        echo ''; // No subject found
    }
}
?>
