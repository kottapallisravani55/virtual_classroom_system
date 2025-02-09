<?php
session_start();
require '../database/db_config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Access Denied.");
}

$teacher_id = $_SESSION['user_id'];

// Fetch assignments uploaded by the logged-in teacher
$query = $conn->prepare("
    SELECT DISTINCT a.id AS assignment_id, a.title AS assignment_title, a.subject, a.uploaded_at 
    FROM assignments a
    INNER JOIN submissions s ON a.id = s.assignment_id
    WHERE a.uid = ? AND a.subject = s.subject
");
$query->bind_param("i", $teacher_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    echo "<h2>Assignments Uploaded by You</h2>";
    echo "<table border='1'>
            <tr>
                <th>Assignment ID</th>
                <th>Title</th>
                <th>Subject</th>
                <th>Uploaded At</th>
                <th>View Submissions</th>
            </tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['assignment_id']}</td>
                <td>{$row['assignment_title']}</td>
                <td>{$row['subject']}</td>
                <td>{$row['uploaded_at']}</td>
                <td><a href='student_submissions.php?assignment_id={$row['assignment_id']}&subject={$row['subject']}'>View Submissions</a></td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "No assignments found.";
}

// Fetch submissions for a specific assignment
if (isset($_GET['assignment_id']) && isset($_GET['subject'])) {
    $assignment_id = intval($_GET['assignment_id']);
    $subject = $_GET['subject'];

    $submission_query = $conn->prepare("
        SELECT DISTINCT s.student_id, st.username AS student_name, s.file_path, s.submitted_at 
        FROM submissions s
        INNER JOIN students st ON s.student_id = st.id
        WHERE s.assignment_id = ? AND s.subject = ?
    ");
    $submission_query->bind_param("is", $assignment_id, $subject);
    $submission_query->execute();
    $submission_result = $submission_query->get_result();

    if ($submission_result->num_rows > 0) {
        echo "<h2>Submissions for Assignment ID: {$assignment_id} (Subject: {$subject})</h2>";
        echo "<table border='1'>
                <tr>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>File</th>
                    <th>Submitted At</th>
                </tr>";
        while ($row = $submission_result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['student_id']}</td>
                    <td>{$row['student_name']}</td>
                    <td><a href='{$row['file_path']}' target='_blank'>Download</a></td>
                    <td>{$row['submitted_at']}</td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "No submissions found for this assignment.";
    }
}
?>
