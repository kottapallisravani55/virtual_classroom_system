<?php
session_start();
include('../database/db_config.php'); // Include the database connection file

// Check if the user is a teacher
if ($_SESSION['role'] !== 'teacher') {
    header('Location: login.php');
    exit();
}

// Get the teacher's subject from their session (assuming this is stored during login)
$teacher_subject = $_SESSION['subject']; // Make sure `subject` is stored in the session during login

// Fetch attendance records based on the selected date
$attendance_data = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance_date'])) {
    $attendance_date = $_POST['attendance_date'];

    // Query to fetch attendance details for the teacher's subject
    $query = "SELECT a.date, s.username AS student_name, a.status, a.subject 
              FROM attendance a
              JOIN students s ON a.student_id = s.id
              WHERE a.date = ? AND a.subject = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $attendance_date, $teacher_subject); // Bind date and teacher's subject
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance_data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    <div id="google_translate_element"></div>
</head>
<body>
    <h3>View Attendance</h3>
    <a href="attendance_history.php">View Attendance History</a>
    <form action="view_attendance.php" method="POST">
        <label for="attendance_date">Select Date:</label>
        <input type="date" name="attendance_date" id="attendance_date" required>
        <button type="submit">View Attendance</button>
        
    </form>

    <?php if (!empty($attendance_data)): ?>
        <h4>Attendance Records for <?php echo htmlspecialchars($attendance_date); ?> (Subject: <?php echo htmlspecialchars($teacher_subject); ?>)</h4>
        <table border="1">
            <tr>
                <th>Date</th>
                <th>Student Name</th>
                <th>Subject</th>
                <th>Status</th>
            </tr>
            <?php foreach ($attendance_data as $record): ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['date']); ?></td>
                    <td><?php echo htmlspecialchars($record['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($record['subject']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($record['status'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <p>No records found for the selected date and subject.</p>
    <?php endif; ?>
    <script src="script.js"></script>
      <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement(
                {pageLanguage: 'en'},
                'google_translate_element'
            );
        } 
  </script>
  <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

    
</body>
</html>
