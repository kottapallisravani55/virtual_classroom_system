<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../database/db_config.php';

    $class_id = $_POST['class_id'];

    if (isset($_FILES['class_video']) && $_FILES['class_video']['error'] == UPLOAD_ERR_OK) {
        $video_name = basename($_FILES['class_video']['name']);
        $target_dir = "../uploads/videos/";
        $target_file = $target_dir . $video_name;

        // Ensure the directory exists
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (move_uploaded_file($_FILES['class_video']['tmp_name'], $target_file)) {
            // Update the database with the video path
            $query = "UPDATE live_classes SET video_path = ? WHERE id = ? AND teacher_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sii", $video_name, $class_id, $_SESSION['user_id']);

            if ($stmt->execute()) {
                echo "Video uploaded successfully!";
                header("Location: view_live_classes.php");
                exit();
            } else {
                echo "Failed to update database: " . $stmt->error;
            }
        } else {
            echo "Failed to upload video.";
        }
    } else {
        echo "Error with the uploaded file.";
    }
}
?>
