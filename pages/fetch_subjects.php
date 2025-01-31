<?php
require '../database/db_config.php'; // Database connection

if (isset($_POST['teacher_id'])) {
    $teacher_id = $_POST['teacher_id']; // Get teacher ID from the POST request

    // Fetch the subject for the selected teacher
    $sql_subject = "SELECT subject FROM teacher_subjects WHERE teacher_id = ?";
    $stmt_subject = $conn->prepare($sql_subject);
    $stmt_subject->bind_param("i", $teacher_id); // "i" means integer
    $stmt_subject->execute();
    $stmt_subject->store_result();
    $stmt_subject->bind_result($subject);

    if ($stmt_subject->fetch()) {
        // Return the subject for the teacher
        echo $subject;
    } else {
        // If no subject is found, return an empty string
        echo '';
    }
}
?>
