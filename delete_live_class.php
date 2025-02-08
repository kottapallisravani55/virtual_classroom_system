<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

include '../database/db_config.php';

// Check if class_id is passed via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['class_id'])) {
    $class_id = intval($_POST['class_id']); // Get class ID from POST request
    $teacher_id = $_SESSION['user_id']; // Get logged-in teacher's ID

    // Verify if the logged-in teacher created the live class
    $check_query = "SELECT * FROM live_classes WHERE id = ? AND teacher_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $class_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Delete the class if it exists and belongs to the teacher
        $delete_query = "DELETE FROM live_classes WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $class_id);

        if ($delete_stmt->execute()) {
            $_SESSION['success_message'] = "Live class deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error deleting the live class. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "Unauthorized action or class not found.";
    }

    $stmt->close();
    $conn->close();

    // Redirect back to the live classes page
    header("Location: view_live_classes.php");
    exit();
} else {
    // Redirect back if no valid POST request
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: view_live_classes.php");
    exit();
}
?>
