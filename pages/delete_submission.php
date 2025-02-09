<<<<<<< HEAD
<?php
session_start();
require '../database/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access Denied.");
}

$student_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['submission_id']) || empty($_POST['submission_id'])) {
        die("Invalid submission ID.");
    }

    $submission_id = intval($_POST['submission_id']);

    // Fetch the file path of the submission
    $query = $conn->prepare("SELECT file_path FROM submissions WHERE id = ? AND student_id = ?");
    $query->bind_param("ii", $submission_id, $student_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $submission = $result->fetch_assoc();
        $file_path = $submission['file_path'];

        // Delete the file
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Remove the submission record from the database
        $delete_stmt = $conn->prepare("DELETE FROM submissions WHERE id = ?");
        $delete_stmt->bind_param("i", $submission_id);

        if ($delete_stmt->execute()) {
            echo "Submission deleted successfully!";
        } else {
            echo "Error: " . $delete_stmt->error;
        }
    } else {
        die("Submission not found or you are not authorized to delete it.");
    }
}
?>
=======
<?php
session_start();
require '../database/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Access Denied.");
}

$student_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['submission_id']) || empty($_POST['submission_id'])) {
        die("Invalid submission ID.");
    }

    $submission_id = intval($_POST['submission_id']);

    // Fetch the file path of the submission
    $query = $conn->prepare("SELECT file_path FROM submissions WHERE id = ? AND student_id = ?");
    $query->bind_param("ii", $submission_id, $student_id);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $submission = $result->fetch_assoc();
        $file_path = $submission['file_path'];

        // Delete the file
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Remove the submission record from the database
        $delete_stmt = $conn->prepare("DELETE FROM submissions WHERE id = ?");
        $delete_stmt->bind_param("i", $submission_id);

        if ($delete_stmt->execute()) {
            echo "Submission deleted successfully!";
        } else {
            echo "Error: " . $delete_stmt->error;
        }
    } else {
        die("Submission not found or you are not authorized to delete it.");
    }
}
?>
>>>>>>> ee7c9565c28e3f015817e1645a6e2d0b3b949065
