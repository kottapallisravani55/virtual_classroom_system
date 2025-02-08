<?php
session_start(); // Ensure session is started

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require '../database/db_config.php'; // Database connection

$user_id = $_SESSION['user_id']; // Assuming user ID is stored in session

// Fetch all teachers from the users table (assuming the subject is stored in the teachers table)
$sql_teachers = "SELECT id, username FROM users WHERE role = 'teacher'";
$stmt_teachers = $conn->prepare($sql_teachers);
$stmt_teachers->execute();
$teachers_result = $stmt_teachers->get_result();

// If form is submitted, insert complaint
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_id = $_POST['teacher_id'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Insert the complaint into the database
    $sql_insert_complaint = "INSERT INTO complaints (student_id, teacher_id, subject, message, created_at) 
                             VALUES (?, ?, ?, ?, NOW())";
    $stmt_insert_complaint = $conn->prepare($sql_insert_complaint);
    $stmt_insert_complaint->bind_param("iiss", $user_id, $teacher_id, $subject, $message);
    $stmt_insert_complaint->execute();

    // Redirect after submission
    header("Location: complaint_form.php?status=success");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Complaint</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // AJAX function to fetch subject based on teacher selection
        $(document).ready(function() {
            $('#teacher_id').change(function() {
                var teacher_username = $(this).val(); // Get selected teacher's username
                
                // Make AJAX request to fetch subject
                if (teacher_username) {
                    $.ajax({
                        url: 'fetch_subjects.php',
                        type: 'POST',
                        data: { teacher_username: teacher_username },
                        success: function(data) {
                            $('#subject').val(data); // Set the subject dynamically in the input field
                        }
                    });
                } else {
                    $('#subject').val(''); // Reset subject if no teacher is selected
                }
            });
        });
    </script>
    <div id="google_translate_element"></div>
</head>
<body>
    <h1>Submit Your Complaint</h1>

    <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
        <p>Complaint submitted successfully!</p>
    <?php endif; ?>

    <form action="complaint_form.php" method="POST">
        <label for="teacher_id">Select Teacher:</label>
        <select name="teacher_id" id="teacher_id" required>
            <option value="">--Select Teacher--</option>
            <?php while ($teacher = $teachers_result->fetch_assoc()): ?>
                <option value="<?php echo htmlspecialchars($teacher['username']); ?>">
                    <?php echo htmlspecialchars($teacher['username']); ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="subject">Subject:</label>
        <input type="text" name="subject" id="subject" required readonly><br><br>

        <label for="message">Complaint Message:</label>
        <textarea name="message" id="message" rows="4" required></textarea><br><br>

        <button type="submit">Submit Complaint</button>
    </form>
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
