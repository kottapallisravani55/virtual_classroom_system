<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

include '../../database/db_config.php';

// Ensure that the subject is assigned to the teacher
if (!isset($_SESSION['subject']) || empty($_SESSION['subject'])) {
    // Fetch subject if it's not in session
    $teacher_username = $_SESSION['username'];
    $query = "SELECT subject FROM teachers WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $teacher_username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['subject'] = $row['subject'];  // Store subject in session if not set
    } else {
        die("Error: Subject not assigned to this teacher.");
    }
}

$subject = $_SESSION['subject']; // Now we have the subject from session

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['material_file'])) {
    $file_name = $_FILES['material_file']['name'];
    $file_tmp = $_FILES['material_file']['tmp_name'];
    $upload_dir = '../../assets/uploads/'; // Directory to store uploaded files

    // Validate file type
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    $allowed_extensions = ['pdf', 'docx', 'pptx'];

    if (!in_array(strtolower($file_ext), $allowed_extensions)) {
        echo "Invalid file type. Only PDF, DOCX, and PPTX are allowed.";
        exit();
    }

    // Clean the original file name by replacing spaces with underscores and converting to lowercase
    $clean_file_name = strtolower(str_replace(' ', '_', pathinfo($file_name, PATHINFO_FILENAME)));

    // Generate a new file name by combining subject and cleaned file name
    $new_file_name = $subject . '_' . $clean_file_name . '_' . uniqid() . '.' . $file_ext;

    // Move the uploaded file to the server
    if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
        // Insert the file into the study materials table with the teacher's subject
        $query = "INSERT INTO study_materials (file_name, subject) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $new_file_name, $subject);

        if ($stmt->execute()) {
            $_SESSION['message'] = "File uploaded successfully.";
            header("Location: ../../pages/study_materials.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Failed to upload file.";
    }
} else {
    echo "No file selected.";
}
?>
