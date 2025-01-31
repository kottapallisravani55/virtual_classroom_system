<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include '../database/db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_file'])) {
    $assignment_id = $_POST['assignment_id'];
    $file = $_FILES['file'];

    // Handle file upload
    $file_name = basename($file['name']);
    $target_dir = "../uploads/";
    $target_file = $target_dir . $file_name;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        $student_id = $_SESSION['user_id'];
        
        $query = "INSERT INTO submissions (student_id, assignment_id, file_path) 
                  VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $student_id, $assignment_id, $target_file);
        
        if ($stmt->execute()) {
            echo "Assignment submitted successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Error uploading file.";
    }
} else {
    $assignment_id = $_GET['assignment_id'];
    // Show the form to submit an assignment
    echo '<form action="submit_assignment.php" method="POST" enctype="multipart/form-data">';
    echo '<input type="hidden" name="assignment_id" value="' . $assignment_id . '">';
    echo 'Upload your assignment file: <input type="file" name="file" required>';
    echo '<button type="submit" name="submit_file">Submit Assignment</button>';
    echo '</form>';
}
?>
