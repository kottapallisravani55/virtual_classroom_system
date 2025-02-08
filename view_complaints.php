<?php
session_start(); // Ensure session is started

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require '../database/db_config.php'; // Database connection

$user_id = $_SESSION['user_id']; // Assuming user ID is stored in session

// Fetch the role of the logged-in user from the users table
$sql_role = "SELECT role FROM users WHERE id = ?";
$stmt_role = $conn->prepare($sql_role);
$stmt_role->bind_param("i", $user_id);
$stmt_role->execute();
$stmt_role->store_result();
$stmt_role->bind_result($role);
$stmt_role->fetch();

// If the user is not a teacher, redirect to login page
if ($role !== 'teacher') {
    header("Location: login.php");
    exit;
}

// Initialize $complaints as an empty array to prevent errors when count is called
$complaints = [];

// Fetch all complaints for the logged-in teacher
$sql_complaints = "
    SELECT c.id, c.student_id, c.subject, c.message, c.created_at, s.username AS student_name
    FROM complaints c
    JOIN users s ON c.student_id = s.id
    JOIN teachers t ON t.subject = c.subject
    WHERE t.uid = ?
    ORDER BY c.created_at DESC
";

$stmt_complaints = $conn->prepare($sql_complaints);
$stmt_complaints->bind_param("i", $user_id); // Bind the teacher ID (which is the logged-in user ID)
$stmt_complaints->execute();
$result = $stmt_complaints->get_result();

// Check if complaints are found and store them in $complaints
if ($result->num_rows > 0) {
    $complaints = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // No complaints found
    $no_complaints_message = "No complaints received.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Complaints</title>
    <div id="google_translate_element"></div>
</head>
<body>
    <h1>Your Complaints</h1>

    <?php if (!empty($complaints)): ?>
        <table border="1">
            <tr>
                <th>Subject</th>
                <th>Complaint Message</th>
                <th>Student</th>
                <th>Date</th>
            </tr>
            <?php foreach ($complaints as $complaint): ?>
                <tr>
                    <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                    <td><?php echo htmlspecialchars($complaint['message']); ?></td>
                    <td><?php echo htmlspecialchars($complaint['student_name']); ?></td>
                    <td><?php echo htmlspecialchars($complaint['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p><?php echo $no_complaints_message; ?></p>
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
