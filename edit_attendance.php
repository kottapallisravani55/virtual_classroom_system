<?php
session_start();

// Ensure the user is logged in as a teacher
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

require '../database/db_config.php'; // Adjust path if necessary

$attendance_id = $_GET['id'] ?? null;

if (!$attendance_id) {
    echo "Error: Invalid attendance ID.";
    exit;
}

// Fetch attendance record
$query = "SELECT a.*, s.username AS student_name FROM attendance a 
          JOIN students s ON a.student_id = s.id 
          WHERE a.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $attendance_id);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();

if (!$record) {
    echo "Error: Attendance record not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = $_POST['attendance_status'];

    // Update attendance status
    $update_query = "UPDATE attendance SET attendance_status = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("si", $new_status, $attendance_id);
    $update_stmt->execute();

    // Display success message and redirect back to history
    $_SESSION['success_message'] = "Attendance updated successfully.";
    header("Location: attendance_history.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance</title>
    <div id="google_translate_element"></div>
</head>
<body>
    <h1>Edit Attendance</h1>

    <!-- Display student and attendance details -->
    <p><strong>Student Name:</strong> <?php echo htmlspecialchars($record['student_name']); ?></p>
    <p><strong>Attendance Date:</strong> <?php echo $record['attendance_date']; ?></p>
    <p><strong>Subject:</strong> <?php echo htmlspecialchars($record['subject']); ?></p>

    <!-- Attendance edit form -->
    <form method="POST">
        <label>
            <input type="radio" name="attendance_status" value="present" 
            <?php echo $record['attendance_status'] === 'present' ? 'checked' : ''; ?>> Present
        </label><br>
        <label>
            <input type="radio" name="attendance_status" value="absent" 
            <?php echo $record['attendance_status'] === 'absent' ? 'checked' : ''; ?>> Absent
        </label><br>
        <button type="submit">Update Attendance</button>
    </form>

    <!-- Link back to attendance history -->
    <a href="attendance_history.php">Back to Attendance History</a>
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
