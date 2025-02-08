<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../../login.php");
    exit();
}

include '../database/db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $file_id = $_POST['file_id'];
    $file_name = $_POST['file_name'];

    // Path to the file
    $file_path = "../uploads/" . $file_name;

    // Delete the file from the server
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    // Delete the record from the database
    $query = "DELETE FROM study_materials WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $file_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Material deleted successfully.";
    } else {
        $_SESSION['message'] = "Failed to delete material.";
    }

    // Redirect back to the study materials page
    header("Location: ../../pages/study_materials.php");
    exit();
}
?>
