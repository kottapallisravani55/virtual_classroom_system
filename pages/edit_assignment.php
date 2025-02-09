<?php
session_start();
require '../database/db_config.php';

// Check if the user is a teacher
if ($_SESSION['role'] != 'teacher') {
    die("Access Denied");
}

// Fetch the assignment details for editing
if (isset($_GET['id'])) {
    $assignment_id = intval($_GET['id']);
    $assignment_query = $conn->prepare("SELECT * FROM assignments WHERE id = ? AND uid = ?");
    $assignment_query->bind_param("ii", $assignment_id, $_SESSION['user_id']);
    $assignment_query->execute();
    $assignment_result = $assignment_query->get_result();

    if ($assignment_result->num_rows > 0) {
        $assignment = $assignment_result->fetch_assoc();
    } else {
        die("Assignment not found.");
    }
}

// Handle Update Action
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $deadline = isset($_POST['deadline']) ? trim($_POST['deadline']) : '';
    $assignment_id = intval($_POST['assignment_id']);

    if (empty($title) || empty($description) || empty($deadline)) {
        die("All fields are required.");
    }

    $deadline_date = date('Y-m-d H:i:s', strtotime($deadline));
    if ($deadline_date <= date('Y-m-d H:i:s')) {
        die("The deadline must be a future date.");
    }

    $update_query = $conn->prepare("UPDATE assignments SET title = ?, description = ?, deadline = ? WHERE id = ? AND uid = ?");
    $update_query->bind_param("sssii", $title, $description, $deadline_date, $assignment_id, $_SESSION['user_id']);
    $update_query->execute();

    if ($update_query) {
        header("Location: upload_assignment.php?status=updated");
        exit();
    } else {
        echo "Failed to update the assignment.";
    }
}
?>

<h2>Edit Assignment</h2>
<form action="edit_assignment.php" method="POST">
    <input type="hidden" name="assignment_id" value="<?= $assignment['id']; ?>">
    <label for="title">Title:</label>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($assignment['title']); ?>" required><br>

    <label for="description">Description:</label>
    <textarea id="description" name="description" required><?= htmlspecialchars($assignment['description']); ?></textarea><br>

    <label for="deadline">Deadline:</label>
    <input type="datetime-local" id="deadline" name="deadline" value="<?= date('Y-m-d\TH:i', strtotime($assignment['deadline'])); ?>" required><br>

    <button type="submit">Update</button>
</form>
