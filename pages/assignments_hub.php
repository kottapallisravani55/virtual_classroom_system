<<<<<<< HEAD
<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}
$role = $_SESSION['role']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Options</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
    <h1>Assignments</h1>
        <button onclick="window.location.href='online_assignment.php'">Online Assignment</button>
        <?php if ($role == 'teacher'): ?>
            <a href="upload_assignment.php">Upload Assignment</a><br>
            <a href="view_submissions.php">View Submissions</a><br>
            <a href="student_submissions.php">student Submissions</a><br>
        <?php elseif ($role == 'student'): ?>
            <a href="view_assignments.php">View Assignments</a><br>
        <?php endif; ?>
    </div>
</body>
</html>
=======
<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}
$role = $_SESSION['role']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Options</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
    <h1>Assignments</h1>
        <button onclick="window.location.href='online_assignment.php'">Online Assignment</button>
        <?php if ($role == 'teacher'): ?>
            <a href="upload_assignment.php">Upload Assignment</a><br>
            <a href="view_submissions.php">View Submissions</a><br>
            <a href="student_submissions.php">student Submissions</a><br>
        <?php elseif ($role == 'student'): ?>
            <a href="view_assignments.php">View Assignments</a><br>
        <?php endif; ?>
    </div>
</body>
</html>
>>>>>>> ee7c9565c28e3f015817e1645a6e2d0b3b949065
