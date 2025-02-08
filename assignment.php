<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require '../database/db_config.php';

// Get user details from session
$username = $_SESSION['username'];
$role = $_SESSION['role']; // Either 'teacher' or 'student'

// If form is submitted (student submitting assignment)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['assignment_file'])) {
    $student_id = $_SESSION['user_id'];
    $assignment_file = $_FILES['assignment_file'];
    $file_name = $assignment_file['name'];
    $file_tmp = $assignment_file['tmp_name'];

    $upload_dir = '../uploads/assignments/';
    $target_file = $upload_dir . basename($file_name);
    
    // Move the uploaded file to the desired directory
    if (move_uploaded_file($file_tmp, $target_file)) {
        // Insert record into the database
        $sql = "INSERT INTO assignments (student_id, file_name, file_path, submission_date) 
                VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $student_id, $file_name, $target_file);
        $stmt->execute();
        echo "Assignment submitted successfully!";
    } else {
        echo "Error uploading the assignment.";
    }
}

// Fetch all assignments for students or teachers
$sql_assignments = "SELECT * FROM assignments";
$result = $conn->query($sql_assignments);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments - Virtual Classroom</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <div id="google_translate_element"></div>
</head>
<body>
    <div class="activities-container">
        <h2>Assignments</h2>

        <?php if ($role == 'student'): ?>
            <h3>Submit Your Assignment</h3>
            <form action="assignment.php" method="POST" enctype="multipart/form-data">
                <label for="assignment_file">Choose Assignment File:</label>
                <input type="file" name="assignment_file" id="assignment_file" required><br><br>
                <button type="submit" class="btn">Submit Assignment</button>
            </form>
        <?php endif; ?>

        <h3>All Assignments</h3>
        <table>
            <tr>
                <th>Student ID</th>
                <th>Assignment File</th>
                <th>Submission Date</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                    <td><a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank"><?php echo htmlspecialchars($row['file_name']); ?></a></td>
                    <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
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
