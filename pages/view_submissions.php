<?php
session_start();
require '../database/db_config.php';

// Check if the user is a teacher
if ($_SESSION['role'] != 'teacher') {
    die("Access Denied");
}

// Fetch teacher's subject using session user_id
$teacher_query = $conn->prepare("
    SELECT subject 
    FROM teachers 
    JOIN users ON teachers.uid = users.id 
    WHERE users.id = ?
");
$teacher_query->bind_param("i", $_SESSION['user_id']);
$teacher_query->execute();
$teacher_result = $teacher_query->get_result();

// Check if the teacher's subject is found
if ($teacher_result->num_rows > 0) {
    $teacher = $teacher_result->fetch_assoc();
    $subject = $teacher['subject'];

    // Fetch assignments related to the teacher's subject, including deadlines
    $assignments_query = $conn->prepare("
        SELECT title, description, file_path, uploaded_at, deadline 
        FROM assignments 
        WHERE subject = ?
        ORDER BY uploaded_at DESC
    ");
    $assignments_query->bind_param("s", $subject);
    $assignments_query->execute();
    $assignments_result = $assignments_query->get_result();
} else {
    die("Subject information not found for the teacher.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Submissions</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .container {
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Assignments for Subject: <?= htmlspecialchars($subject) ?></h2>
        <?php if ($assignments_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>File</th>
                        <th>Uploaded At</th>
                        <th>Deadline</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($assignment = $assignments_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($assignment['title']) ?></td>
                            <td><?= htmlspecialchars($assignment['description']) ?></td>
                            <td><a href="<?= htmlspecialchars($assignment['file_path']) ?>" target="_blank">View File</a></td>
                            <td><?= htmlspecialchars($assignment['uploaded_at']) ?></td>
                            <td><?= htmlspecialchars($assignment['deadline']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No assignments found for your subject.</p>
        <?php endif; ?>
    </div>
</body>
</html>
