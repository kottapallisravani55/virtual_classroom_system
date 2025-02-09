<?php
session_start();
require '../database/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access Denied.");
}

$student_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['assignment_file']) || $_FILES['assignment_file']['error'] !== UPLOAD_ERR_OK || 
        !isset($_POST['assignment_id']) || empty($_POST['assignment_id'])) {
        die("Please provide a file and an assignment ID.");
    }

    $assignment_id = intval($_POST['assignment_id']);
    $original_file_name = $_FILES['assignment_file']['name'];
    $file_tmp = $_FILES['assignment_file']['tmp_name'];
    $upload_dir = "../uploads/submissions/";

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate a unique ID for the file
    $unique_id = uniqid();
    $file_extension = pathinfo($original_file_name, PATHINFO_EXTENSION);
    $unique_file_name = $unique_id . "_" . $student_id . "_" . $assignment_id . "." . $file_extension;

    // Define the file path
    $file_path = $upload_dir . $unique_file_name;

    // Check if the student has already submitted for this assignment
    $check_query = $conn->prepare("SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
    $check_query->bind_param("ii", $assignment_id, $student_id);
    $check_query->execute();
    $check_result = $check_query->get_result();

    if ($check_result->num_rows > 0) {
        die("You have already submitted a file for this assignment. Please delete the existing submission to upload a new one.");
    }

    // Fetch the assignment details, including the deadline
    $assignment_query = $conn->prepare("SELECT subject, deadline FROM assignments WHERE id = ?");
    $assignment_query->bind_param("i", $assignment_id);
    $assignment_query->execute();
    $assignment_result = $assignment_query->get_result();

    if ($assignment_result->num_rows > 0) {
        $assignment_data = $assignment_result->fetch_assoc();
        $subject = $assignment_data['subject'];
        $deadline = $assignment_data['deadline'];

        // Check if the current time is before the deadline
        if (new DateTime() > new DateTime($deadline)) {
            die("The submission deadline has passed. You cannot submit this assignment.");
        }

        if (move_uploaded_file($file_tmp, $file_path)) {
            // Save submission details in the database
            $stmt = $conn->prepare("INSERT INTO submissions (assignment_id, student_id, file_path, submitted_at, subject) VALUES (?, ?, ?, NOW(), ?)");
            $stmt->bind_param("iiss", $assignment_id, $student_id, $file_path, $subject);

            if ($stmt->execute()) {
                echo "Assignment submitted successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
        } else {
            echo "Failed to upload the file.";
        }
    } else {
        die("Invalid assignment ID.");
    }
}
?>
